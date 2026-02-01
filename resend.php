<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { exit("Access Denied"); }

require 'db.php';
require 'vendor/autoload.php';
include 'config.php'; // Pulls the dynamic event name

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM participants WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if (!$user) { throw new Exception("Participant not found."); }

        $name = $user['name'];
        $email = $user['email'];

        // 1. Generate PDF
        $imagePath = realpath('assets/CIT(1).png');
        $imageData = base64_encode(file_get_contents($imagePath));
        $base64Image = 'data:image/png;base64,' . $imageData;

        $options = new Options();
        $dompdf = new Dompdf($options);
        $html = "<style>@page { margin: 0; } body { margin: 0; font-family: 'Helvetica', sans-serif; } .name-overlay { position: absolute; top: 275px; left: 0; width: 100%; text-align: center; font-size: 48px; font-weight: bold; color: #ffffff; text-transform: uppercase; }</style><img src='$base64Image' style='width:100%; height:100%; position:absolute;'><div class='name-overlay'>$name</div>";

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $pdf_content = $dompdf->output();

        // 2. Send Email
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'studentsystemotpbot@gmail.com'; 
        $mail->Password   = 'todc qrvo whkb xxvw';   
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('studentsystemotpbot@gmail.com', 'CIT Department'); 
        $mail->addAddress($email, $name);
        $mail->Subject = "RESENT: Certificate of Recognition - $event_name";
        $mail->Body    = "Hello $name,\n\nAs requested, we are resending your certificate for the $event_name.";
        $mail->addStringAttachment($pdf_content, "Certificate_$name.pdf");

        $mail->send();

        header("Location: admin.php?status=resent");
        exit();

    } catch (Exception $e) {
        header("Location: admin.php?status=error&message=" . urlencode($e->getMessage()));
        exit();
    }
}
?>