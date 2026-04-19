<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false,'error'=>'Not logged in.']); exit; }
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false,'error'=>'Invalid request.']); exit; }

$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');
$subject = trim($_POST['subject'] ?? '');
$body    = trim($_POST['body']    ?? '');

if (!$name || !$email || !$subject || !$body) { echo json_encode(['success'=>false,'error'=>'All fields are required.']); exit; }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { echo json_encode(['success'=>false,'error'=>'Invalid email.']); exit; }

$stmt = $pdo->prepare('INSERT INTO messages (name, email, subject, body) VALUES (?,?,?,?)');
$stmt->execute([$name, $email, $subject, $body]);
echo json_encode(['success'=>true]);
