<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false,'error'=>'Not logged in.']); exit; }
require_once 'db_reviews.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false,'error'=>'Invalid request.']); exit; }

$rating = (int)($_POST['rating'] ?? 0);
$title  = trim($_POST['title']  ?? '');
$body   = trim($_POST['body']   ?? '');

if ($rating < 1 || $rating > 5)  { echo json_encode(['success'=>false,'error'=>'Invalid rating.']); exit; }
if (!$title)                      { echo json_encode(['success'=>false,'error'=>'Title is required.']); exit; }
if (!$body)                       { echo json_encode(['success'=>false,'error'=>'Review body is required.']); exit; }

$db   = get_db();
$stmt = $db->prepare('INSERT INTO reviews (user_id, rating, title, body, is_approved) VALUES (?, ?, ?, ?, 0)');
$stmt->bind_param('iiss', $_SESSION['user_id'], $rating, $title, $body);

if ($stmt->execute()) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'error'=>'Could not save review.']);
}
