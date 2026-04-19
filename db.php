<?php
// db credentials for the PDO connection used everywhere except reviews
$host   = 'localhost';
$dbname = 'kpwraps';
$user   = 'root';
$pass   = '';

try {
    // connect with PDO so we can use prepared statements
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    // throw exceptions on error instead of silently failing
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // stop and return a json error if the connection fails
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
}