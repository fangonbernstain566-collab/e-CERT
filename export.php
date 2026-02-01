<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { exit(); }

require 'db.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="CIT_Participants_'.date('Y-m-d').'.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Name', 'Email', 'Status', 'Date Registered']);

$stmt = $pdo->query("SELECT id, name, email, status, created_at FROM participants ORDER BY id ASC");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row);
}
fclose($output);
exit();