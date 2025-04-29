<?php
// Database configuration
$host = 'localhost';
$dbname = 'trading_helper';
$username = 'root';
$password = '';

// MT5 configuration
$mt5_server = 'FBS-Demo';
$mt5_login = '100429092';
$mt5_password = 'cj,!9J>>';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function log_message($pdo, $level, $message) {
    $stmt = $pdo->prepare("INSERT INTO logs (log_level, message, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$level, $message]);
}
?>