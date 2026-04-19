<?php
/**
 * Name: Real MaJiK (Team 47)
 * Created: March 23, 2026
 * Description: Processes login requests and returns authentication results for users.
 */
// start session and set response type to json
session_start();
header('Content-Type: application/json');
require 'db.php';

// only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
    exit;
}

// grab and sanitize the submitted fields
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// make sure both fields are filled in
if (!$email || !$password) {
    echo json_encode(['success' => false, 'error' => 'Email and password are required.']);
    exit;
}

// look up the user by email
$stmt = $pdo->prepare('SELECT user_id, first_name, password_hash, role FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// if no user found or password doesn't match, return error
if (!$user || !password_verify($password, $user['password_hash'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid email or password.']);
    exit;
}

// save the user's info to the session
$_SESSION['user_id']    = $user['user_id'];
$_SESSION['first_name'] = $user['first_name'];
$_SESSION['role']       = $user['role'];

// send admins to the admin panel, everyone else to the about page
$redirect = $user['role'] === 'admin' ? 'admin.php' : 'about.php';
echo json_encode(['success' => true, 'redirect' => $redirect]);