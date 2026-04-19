<?php
/**
 * Name: Real MaJiK (Team 47)
 * Created: March 23, 2026
 * Description: Renders the admin dashboard interface for managing clients, reviews, schedule, transactions, and messages.
 */
session_start();
require 'db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.html'); exit;
}

// Fetch clients
$clients = $pdo->query("SELECT user_id, first_name, last_name, email, phone, created_at FROM users WHERE role='client' ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Counts for badge indicators
$pending_reviews = (int)$pdo->query("SELECT COUNT(*) FROM reviews WHERE is_approved=0")->fetchColumn();
$unread_messages = (int)$pdo->query("SELECT COUNT(*) FROM messages WHERE is_read=0")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KP Wraps — Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<!-- Sidebar -->
<aside class="sidebar">
  <img src="images/logos/logo.jpeg" alt="KP Wraps" style="width:160px;border-radius:8px;margin-bottom:1rem;">
  <div class="sidebar-sub">Admin Panel</div>
  <nav class="sidebar-nav">
    <button class="sidebar-btn active" data-section="clients">Clients</button>
    <button class="sidebar-btn" data-section="reviews">
      Reviews <?php if($pending_reviews>0):?><span class="badge"><?=$pending_reviews?></span><?php endif;?>
    </button>
    <button class="sidebar-btn" data-section="schedule">Schedule</button>
    <button class="sidebar-btn" data-section="transactions">Transactions</button>
    <button class="sidebar-btn" data-section="messages">
      Messages <?php if($unread_messages>0):?><span class="badge"><?=$unread_messages?></span><?php endif;?>
    </button>
  </nav>
  <div class="sidebar-logout"><a href="logout.php">Logout</a></div>
</aside>

<!-- Main content -->
<main class="main">

  <!-- CLIENTS -->
  <div class="section active" id="sec-clients">
    <div class="page-title">Client Management</div>
    <div class="panel">
      <div class="panel-header">
        <span class="panel-title">All Clients</span>
        <div style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap;">
          <input class="search-bar" id="clientSearch" type="text" placeholder="Search clients…">
          <button class="teal-btn" id="openAddClientModalBtn">+ Add Client</button>
        </div>
      </div>
      <table id="clientTable">
        <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Joined</th><th></th></tr></thead>
        <tbody>
          <?php foreach($clients as $c): ?>
          <tr>
            <td><?=htmlspecialchars($c['first_name'].' '.$c['last_name'])?></td>
            <td><?=htmlspecialchars($c['email'])?></td>
            <td><?=htmlspecialchars($c['phone']??'—')?></td>
            <td><?=date('M j, Y', strtotime($c['created_at']))?></td>
            <td><button class="teal-btn view-client-btn" data-client-id="<?=$c['user_id']?>">View</button></td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($clients)):?><tr><td colspan="5" style="color:#aaa;text-align:center;padding:1.5rem">No clients yet.</td></tr><?php endif;?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- REVIEWS -->
  <div class="section" id="sec-reviews">
    <div class="page-title">Review Moderation</div>
    <div class="panel" id="reviewsPanel"><p style="color:#aaa">Loading reviews…</p></div>
  </div>

  <!-- SCHEDULE -->
  <div class="section" id="sec-schedule">
    <div class="page-title">All Appointments</div>
    <div class="panel">
      <div class="panel-header">
        <span class="panel-title">Upcoming &amp; Recent Bookings</span>
        <input type="date" id="scheduleFilter" class="search-bar">
      </div>
      <div id="scheduleTable"><p style="color:#aaa">Loading schedule…</p></div>
    </div>
  </div>

  <!-- TRANSACTIONS -->
  <div class="section" id="sec-transactions">
    <div class="page-title">Transactions</div>
    <div class="panel">
      <div class="panel-header"><span class="panel-title">Add Transaction</span></div>
      <div class="tx-form">
        <div>
          <label style="font-size:.78rem;font-weight:600;color:#666;display:block;margin-bottom:.3rem">Client</label>
          <select id="txClient">
            <?php foreach($clients as $c):?>
            <option value="<?=$c['user_id']?>"><?=htmlspecialchars($c['first_name'].' '.$c['last_name'])?></option>
            <?php endforeach;?>
          </select>
        </div>
        <div>
          <label style="font-size:.78rem;font-weight:600;color:#666;display:block;margin-bottom:.3rem">Description</label>
          <input type="text" id="txDesc" placeholder="e.g. Full wrap — Toyota Camry">
        </div>
        <div>
          <label style="font-size:.78rem;font-weight:600;color:#666;display:block;margin-bottom:.3rem">Amount ($)</label>
          <input type="number" id="txAmount" placeholder="2450" min="0" step="0.01">
        </div>
        <div>
          <label style="font-size:.78rem;font-weight:600;color:#666;display:block;margin-bottom:.3rem">Type</label>
          <select id="txType"><option value="charge">Charge</option><option value="payment">Payment</option><option value="refund">Refund</option></select>
        </div>
        <button class="teal-btn" id="addTransactionBtn" style="height:38px">Add</button>
      </div>
      <p class="modal-msg" id="txMsg"></p>
      <div id="txTable"><p style="color:#aaa">Loading transactions…</p></div>
    </div>
  </div>

  <!-- MESSAGES -->
  <div class="section" id="sec-messages">
    <div class="page-title">Contact Messages</div>
    <div class="panel" id="messagesPanel"><p style="color:#aaa">Loading messages…</p></div>
  </div>

</main>

<!-- Add Client Modal -->
<div class="modal-bg" id="addClientModal">
  <div class="modal">
    <h3>Add New Client</h3>
    <label>First Name</label><input type="text" id="ac-first" placeholder="Jane">
    <label>Last Name</label><input type="text" id="ac-last" placeholder="Doe">
    <label>Email</label><input type="email" id="ac-email" placeholder="jane@example.com">
    <label>Phone</label><input type="tel" id="ac-phone" placeholder="905-555-0100">
    <label>Password</label><input type="password" id="ac-pass" placeholder="Temporary password">
    <div class="modal-btns">
      <button class="teal-btn" id="createClientBtn">Create Account</button>
      <button class="outline-btn" id="cancelAddClientModalBtn">Cancel</button>
    </div>
    <p class="modal-msg" id="acMsg"></p>
  </div>
</div>

<!-- View Client Modal -->
<div class="modal-bg" id="viewClientModal">
  <div class="modal" style="max-width:560px">
    <h3 id="vc-name">Client Profile</h3>
    <div id="vc-body" style="font-size:.88rem;color:#444;line-height:1.8;max-height:60vh;overflow-y:auto"></div>
    <div class="modal-btns" style="margin-top:1rem">
      <button class="outline-btn" id="closeViewClientModalBtn">Close</button>
    </div>
  </div>
</div>

<script src="js/admin.js"></script>
</body>
</html>
