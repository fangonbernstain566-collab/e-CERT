<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { exit("Unauthorized"); }

require 'db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM participants WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: admin.php?status=deleted");
exit();
?>