<?php
// 1. Clear file system cache and boost limits
clearstatcache();
ini_set('memory_limit', '256M');
set_time_limit(60); 

require 'db.php';
require 'vendor/autoload.php';

if(file_exists('config.php')) {
    include 'config.php';
} else {
    $event_name = "CIT Seminar"; 
}

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name  = strtoupper(trim(htmlspecialchars($_POST['name'])));
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $uniqueId = "CIT-" . strtoupper(bin2hex(random_bytes(3)));

    try {
        // Database logic
        $check = $pdo->prepare("SELECT COUNT(*) FROM participants WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetchColumn() > 0) {
            header("Location: index.php?status=error&message=" . urlencode("Email already registered!"));
            exit();
        }

        $sql = "INSERT INTO participants (full_name, email, certificate_id, status) VALUES (?, ?, ?, 'Pending')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $email, $uniqueId]);

        // PDF Generation
        $imagePath = 'assets/CIT(1).png'; 
        if (!file_exists($imagePath)) {
            throw new Exception("Certificate template not found.");
        }
        $imageData = base64_encode(file_get_contents($imagePath));
        $base64Image = 'data:image/png;base64,' . $imageData;

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        
        $html = "
        <style>
            @page { margin: 0; }
            body { margin: 0; padding: 0; font-family: 'Helvetica', sans-serif; }
            .cert-container { position: relative; width: 100%; height: 100%; }
            .bg-image { width: 100%; height: 100%; position: absolute; top: 0; left: 0; z-index: -1; }
            .name-overlay {
                position: absolute; 
                top: 36%; /* Matches the registration preview position */
                left: 0; 
                width: 100%;
                text-align: center; 
                font-size: 44px; 
                font-weight: bold;
                color: black; /* Changed to black to suit the blue design */
                text-transform: uppercase;
            }
        </style>
        <div class='cert-container'>
            <img src='$base64Image' class='bg-image'>
            <div class='name-overlay'>$name</div>
        </div>";

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $pdf_content = $dompdf->output();

        // Email logic
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
        $mail->Subject = "Your Certificate for $event_name";
        $mail->Body    = "Hello $name,\n\nCongratulations! Your certificate is attached.\n\nID: $uniqueId";
        
        $mail->addStringAttachment($pdf_content, "Certificate_$name.pdf");
        $mail->send();

        $pdo->prepare("UPDATE participants SET status = 'Sent' WHERE email = ?")->execute([$email]);

        header("Location: index.php?status=success");
        exit();

    } catch (Exception $e) {
        die("System Error: " . $e->getMessage());
    }
}
?>