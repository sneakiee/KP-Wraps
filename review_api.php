<?php
/**
 * Name: Real MaJiK (Team 47)
 * Created: March 23, 2026
 * Description: Processes review submission requests from authenticated clients.
 */
// start session and return json
session_start();
header('Content-Type: application/json');
// must be logged in to submit a review
if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false,'error'=>'Not logged in.']); exit; }
require_once 'db_reviews.php';

// only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false,'error'=>'Invalid request.']); exit; }

// grab the submitted review fields
$rating = (int)($_POST['rating'] ?? 0);
$title  = trim($_POST['title']  ?? '');
$body   = trim($_POST['body']   ?? '');

// validate all the fields before saving
if ($rating < 1 || $rating > 5)  { echo json_encode(['success'=>false,'error'=>'Invalid rating.']); exit; }
if (!$title)                      { echo json_encode(['success'=>false,'error'=>'Title is required.']); exit; }
if (!$body)                       { echo json_encode(['success'=>false,'error'=>'Review body is required.']); exit; }

// insert the review with is_approved = 0, admin has to approve it first
$db   = get_db();
$stmt = $db->prepare('INSERT INTO reviews (user_id, rating, title, body, is_approved) VALUES (?, ?, ?, ?, 0)');
$stmt->bind_param('iiss', $_SESSION['user_id'], $rating, $title, $body);

if ($stmt->execute()) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'error'=>'Could not save review.']);
}