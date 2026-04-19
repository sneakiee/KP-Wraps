<?php
/**
 * Name: Real MaJiK (Team 47)
 * Created: March 23, 2026
 * Description: Processes contact form messages and stores them for admin review.
 */
// start session and return json
session_start();
header('Content-Type: application/json');
// must be logged in to send a message
if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false,'error'=>'Not logged in.']); exit; }
require 'db.php';

// only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false,'error'=>'Invalid request.']); exit; }

// grab and sanitize the form fields
$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');
$subject = trim($_POST['subject'] ?? '');
$body    = trim($_POST['body']    ?? '');

// make sure nothing is empty
if (!$name || !$email || !$subject || !$body) { echo json_encode(['success'=>false,'error'=>'All fields are required.']); exit; }
// validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { echo json_encode(['success'=>false,'error'=>'Invalid email.']); exit; }

// insert the message, is_read defaults to 0 so it shows as unread in admin
$stmt = $pdo->prepare('INSERT INTO messages (name, email, subject, body) VALUES (?,?,?,?)');
$stmt->execute([$name, $email, $subject, $body]);
echo json_encode(['success'=>true]);