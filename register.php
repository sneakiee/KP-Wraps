<?php
header('Content-Type: application/json');
require 'db.php';

// only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
    exit;
}

// grab and sanitize all the form fields
$first    = trim($_POST['first_name'] ?? '');
$last     = trim($_POST['last_name']  ?? '');
$email    = trim($_POST['email']      ?? '');
$phone    = trim($_POST['phone']      ?? '');
$password = $_POST['password']        ?? '';

// make sure required fields aren't empty
if (!$first || !$last || !$email || !$password) {
    echo json_encode(['success' => false, 'error' => 'All fields are required.']);
    exit;
}
// validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid email address.']);
    exit;
}
// password must be 8+ chars and contain at least one number
if (strlen($password) < 8 || !preg_match('/\d/', $password)) {
    echo json_encode(['success' => false, 'error' => 'Password must be 8+ characters and include a number.']);
    exit;
}

// Check duplicate email
// check if someone already has an account with this email
$check = $pdo->prepare('SELECT user_id FROM users WHERE email = ?');
$check->execute([$email]);
if ($check->fetch()) {
    echo json_encode(['success' => false, 'error' => 'An account with that email already exists.']);
    exit;
}

// hash the password before saving, never store plain text
$hash = password_hash($password, PASSWORD_DEFAULT);
// insert the new user with the role set to client
$stmt = $pdo->prepare('INSERT INTO users (email, password_hash, first_name, last_name, phone, role) VALUES (?, ?, ?, ?, ?, \'client\')');
$stmt->execute([$email, $hash, $first, $last, $phone]);

echo json_encode(['success' => true]);