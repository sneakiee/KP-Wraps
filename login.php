<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
    exit;
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['success' => false, 'error' => 'Email and password are required.']);
    exit;
}

$stmt = $pdo->prepare('SELECT user_id, first_name, password_hash, role FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user['password_hash'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid email or password.']);
    exit;
}

$_SESSION['user_id']    = $user['user_id'];
$_SESSION['first_name'] = $user['first_name'];
$_SESSION['role']       = $user['role'];

$redirect = $user['role'] === 'admin' ? 'admin.php' : 'about.php';
echo json_encode(['success' => true, 'redirect' => $redirect]);
