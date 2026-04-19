<?php
/**
 * Name: Real MaJiK (Team 47)
 * Created: March 23, 2026
 * Description: Provides admin-side API endpoints for dashboard data retrieval and moderation actions.
 */
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error'=>'Unauthorized']); exit;
}
require 'db.php';
require_once 'db_reviews.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// --- Client profile ---
if ($action === 'client_profile') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT user_id, first_name, last_name, email, phone, created_at FROM users WHERE user_id=? AND role="client"');
    $stmt->execute([$id]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    // Try fetching appointments — handle different column names gracefully
    $appointments = [];
    try {
        $appts = $pdo->prepare('SELECT appointment_date, time_slot, service, status FROM appointments WHERE user_id=? ORDER BY appointment_date DESC LIMIT 10');
        $appts->execute([$id]);
        $appointments = $appts->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        try {
            $appts = $pdo->prepare('SELECT appointment_date, appointment_time as time_slot, service_type as service, status FROM appointments WHERE user_id=? ORDER BY appointment_date DESC LIMIT 10');
            $appts->execute([$id]);
            $appointments = $appts->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e2) {
            $appointments = [];
        }
    }

    $transactions = [];
    try {
        $txs = $pdo->prepare('SELECT description, amount, type, created_at FROM transactions WHERE user_id=? ORDER BY created_at DESC LIMIT 10');
        $txs->execute([$id]);
        $transactions = $txs->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $transactions = [];
    }

    echo json_encode(['client'=>$client,'appointments'=>$appointments,'transactions'=>$transactions]); exit;
}

// --- Reviews ---
if ($action === 'reviews') {
    $db = get_db();
    $res = $db->query('SELECT r.review_id,r.rating,r.title,r.body,r.is_approved,r.created_at,u.first_name,u.last_name FROM reviews r JOIN users u ON r.user_id=u.user_id ORDER BY r.is_approved ASC, r.created_at DESC LIMIT 50');
    echo json_encode(['reviews'=>$res?$res->fetch_all(MYSQLI_ASSOC):[]]); exit;
}
if ($action === 'approve') {
    $id = (int)($_POST['id'] ?? 0);
    $db = get_db();
    $stmt = $db->prepare('UPDATE reviews SET is_approved = 1 WHERE review_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    echo json_encode(['success' => true, 'affected' => $db->affected_rows]);
    exit;
}
if ($action === 'delete') {
    $id=(int)($_POST['id']??0);
    $db=get_db(); $db->query("DELETE FROM reviews WHERE review_id=$id");
    echo json_encode(['success'=>true]); exit;
}

// --- Schedule ---
if ($action === 'schedule') {
    $date = $_GET['date'] ?? '';
    try {
        $sql = 'SELECT a.id, a.appointment_date, a.time_slot, a.service, a.status, u.first_name, u.last_name
                FROM appointments a JOIN users u ON a.user_id=u.user_id';
        if ($date) { $stmt=$pdo->prepare($sql.' WHERE a.appointment_date=? ORDER BY a.time_slot ASC'); $stmt->execute([$date]); }
        else { $stmt=$pdo->query($sql.' ORDER BY a.appointment_date DESC, a.time_slot ASC LIMIT 50'); }
        echo json_encode(['appointments'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (Exception $e) {
        echo json_encode(['appointments'=>[], 'error'=>$e->getMessage()]);
    }
    exit;
}
if ($action === 'cancel_appt') {
    $id=(int)($_POST['id']??0);
    $stmt=$pdo->prepare("UPDATE appointments SET status='cancelled' WHERE id=?"); $stmt->execute([$id]);
    echo json_encode(['success'=>true]); exit;
}

// --- Transactions ---
if ($action === 'transactions') {
    $rows=$pdo->query('SELECT t.transaction_id,t.description,t.amount,t.type,t.created_at,u.first_name,u.last_name FROM transactions t JOIN users u ON t.user_id=u.user_id ORDER BY t.created_at DESC LIMIT 100')->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['transactions'=>$rows]); exit;
}
if ($action === 'add_transaction') {
    $uid=(int)($_POST['user_id']??0);
    $desc=trim($_POST['description']??'');
    $amount=(float)($_POST['amount']??0);
    $type=$_POST['type']??'charge';
    if(!$desc||!$amount){echo json_encode(['success'=>false,'error'=>'Missing fields.']);exit;}
    $stmt=$pdo->prepare('INSERT INTO transactions (user_id,description,amount,type) VALUES (?,?,?,?)');
    $stmt->execute([$uid,$desc,$amount,$type]);
    echo json_encode(['success'=>true]); exit;
}

// --- Messages ---
if ($action === 'messages') {
    $rows=$pdo->query('SELECT message_id,name,email,subject,body,is_read,created_at FROM messages ORDER BY is_read ASC, created_at DESC LIMIT 50')->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['messages'=>$rows]); exit;
}
if ($action === 'mark_read') {
    $id=(int)($_POST['id']??0);
    $pdo->prepare('UPDATE messages SET is_read=1 WHERE message_id=?')->execute([$id]);
    echo json_encode(['success'=>true]); exit;
}

echo json_encode(['error'=>'Unknown action']);
