<?php
// 1. Connection Variables from InfinityFree 'MySQL Details'
$host = 'sql105.infinityfree.com'; // <--- Check your dashboard for the EXACT 'sql' number
$db   = 'if0_41039692_cert_system';       // <--- Your Database Name created in MySQL Databases
$user = 'if0_41039692';            // <--- Your MySQL Username
$pass = 'mbwx4AKueMao';   // <--- Your main InfinityFree account password

try {
    // 2. Updated DSN specifically for MySQL
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 10 // Increased timeout for free hosting
    ]);
} catch (PDOException $e) {
    // 3. Simple error message
    die("Database connection failed: " . $e->getMessage());
}
?>