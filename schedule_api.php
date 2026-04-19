<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

require 'db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'slots') {
    $date = $_GET['date'] ?? '';
    if (!$date) { echo json_encode(['booked' => []]); exit; }
    try {
        $stmt = $pdo->prepare("SELECT time_slot FROM appointments WHERE appointment_date = ? AND status = 'booked'");
        $stmt->execute([$date]);
        echo json_encode(['booked' => $stmt->fetchAll(PDO::FETCH_COLUMN)]);
    } catch (Exception $e) {
        echo json_encode(['booked' => [], 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'book') {
    $date    = $_POST['date']    ?? '';
    $slot    = $_POST['slot']    ?? '';
    $service = $_POST['service'] ?? 'Wrap Consultation';
    if (!$date || !$slot) { echo json_encode(['success' => false, 'error' => 'Missing date or time.']); exit; }
    try {
        $check = $pdo->prepare("SELECT id FROM appointments WHERE appointment_date = ? AND time_slot = ? AND status = 'booked'");
        $check->execute([$date, $slot]);
        if ($check->fetch()) { echo json_encode(['success' => false, 'error' => 'That slot is already booked.']); exit; }
        $ins = $pdo->prepare("INSERT INTO appointments (user_id, appointment_date, time_slot, service) VALUES (?, ?, ?, ?)");
        $ins->execute([$_SESSION['user_id'], $date, $slot, $service]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'cancel') {
    $id = (int)($_POST['id'] ?? 0);
    try {
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'my_appointments') {
    try {
        $stmt = $pdo->prepare("SELECT id, appointment_date, time_slot, service, status FROM appointments WHERE user_id = ? ORDER BY appointment_date DESC, time_slot ASC LIMIT 20");
        $stmt->execute([$_SESSION['user_id']]);
        echo json_encode(['appointments' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (Exception $e) {
        echo json_encode(['appointments' => [], 'error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['error' => 'Unknown action']);
