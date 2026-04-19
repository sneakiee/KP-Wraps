<?php
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
  <style>
    *{box-sizing:border-box;margin:0;padding:0;}
    body{min-height:100vh;font-family:'DM Sans',sans-serif;background:#f0f4f8;color:#1a1f2e;display:flex;}

    /* Sidebar */
    .sidebar{width:220px;min-height:100vh;background:#ffffff;border-right:1px solid #d8d4cc;display:flex;flex-direction:column;padding:1.5rem 1rem;position:sticky;top:0;flex-shrink:0;}
    .sidebar-logo{font-family:'Bebas Neue',sans-serif;font-size:1.6rem;color:#49a2e6;letter-spacing:.1em;margin-bottom:.2rem;}
    .sidebar-sub{font-size:.75rem;color:#888;margin-bottom:1.8rem;}
    .sidebar-nav{display:flex;flex-direction:column;gap:.4rem;flex:1;}
    .sidebar-btn{display:flex;align-items:center;justify-content:space-between;padding:.6rem .9rem;border-radius:8px;text-decoration:none;font-size:.88rem;font-weight:600;color:#555;border:1.5px solid transparent;transition:all .2s;cursor:pointer;background:none;}
    .sidebar-btn:hover{border-color:#49a2e6;color:#49a2e6;}
    .sidebar-btn.active{background:#49a2e6;color:#fff;border-color:#49a2e6;}
    .badge{background:#d75a44;color:#fff;border-radius:20px;padding:.1rem .45rem;font-size:.72rem;font-weight:700;}
    .sidebar-logout{margin-top:auto;padding-top:1rem;}
    .sidebar-logout a{display:block;text-align:center;padding:.55rem;border-radius:8px;border:1.5px solid #d0cdc8;color:#888;text-decoration:none;font-size:.85rem;transition:all .2s;}
    .sidebar-logout a:hover{border-color:#d75a44;color:#d75a44;}

    /* Main */
    .main{flex:1;padding:2rem;overflow:auto;}
    .page-title{font-family:'Bebas Neue',sans-serif;font-size:1.8rem;margin-bottom:1.2rem;letter-spacing:.04em;}

    /* Cards / panels */
    .panel{background:#fff;border-radius:12px;box-shadow:0 4px 16px rgba(0,0,0,.07);padding:1.3rem 1.5rem;margin-bottom:1.5rem;}
    .panel-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem;}
    .panel-title{font-family:'Bebas Neue',sans-serif;font-size:1.2rem;letter-spacing:.04em;}

    /* Search */
    .search-bar{padding:.5rem .8rem;border:1.5px solid #d0cdc8;border-radius:8px;font-family:inherit;font-size:.88rem;width:220px;}
    .search-bar:focus{outline:none;border-color:#49a2e6;}

    /* Table */
    table{width:100%;border-collapse:collapse;font-size:.88rem;}
    th{text-align:left;padding:.5rem .75rem;color:#888;font-weight:600;font-size:.78rem;text-transform:uppercase;border-bottom:2px solid #f0f4f8;}
    td{padding:.6rem .75rem;border-bottom:1px solid #f0ece4;}
    tr:last-child td{border-bottom:none;}
    tr:hover td{background:#fafbfc;}
    .teal-btn{background:#49a2e6;color:#fff;border:none;border-radius:6px;padding:.3rem .75rem;font-size:.82rem;font-weight:600;cursor:pointer;font-family:inherit;}
    .teal-btn:hover{background:#2d86c9;}
    .red-btn{background:#d75a44;color:#fff;border:none;border-radius:6px;padding:.3rem .75rem;font-size:.82rem;font-weight:600;cursor:pointer;font-family:inherit;}
    .red-btn:hover{background:#b84836;}
    .outline-btn{background:transparent;border:1.5px solid #49a2e6;color:#49a2e6;border-radius:6px;padding:.3rem .75rem;font-size:.82rem;font-weight:600;cursor:pointer;font-family:inherit;}
    .outline-btn:hover{background:#49a2e6;color:#fff;}

    /* Modal */
    .modal-bg{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;display:none;align-items:center;justify-content:center;}
    .modal-bg.open{display:flex;}
    .modal{background:#ffffff;border-radius:14px;padding:2rem;width:min(440px,95vw);box-shadow:0 20px 60px rgba(0,0,0,.3);}
    .modal h3{font-family:'Bebas Neue',sans-serif;font-size:1.3rem;margin-bottom:1rem;}
    .modal label{font-size:.82rem;font-weight:600;color:#444;display:block;margin-bottom:.3rem;}
    .modal input,.modal select{width:100%;padding:.6rem .8rem;border:1.5px solid #d0cdc8;border-radius:8px;font-size:.9rem;font-family:inherit;margin-bottom:.8rem;background:#fff;color:#1a1f2e;}
    .modal-btns{display:flex;gap:.75rem;margin-top:.5rem;}
    .modal-msg{font-size:.85rem;font-weight:600;margin-top:.5rem;}
    .modal-msg.ok{color:#27ae60;} .modal-msg.err{color:#c0392b;}

    /* Sections hidden by default */
    .section{display:none;}
    .section.active{display:block;}

    /* Review cards */
    .rev-card{background:#fafbfc;border-radius:10px;padding:1rem 1.2rem;margin-bottom:.75rem;border:1px solid #f0f4f8;}
    .rev-card-header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;margin-bottom:.4rem;}
    .stars{color:#f5c542;}
    .rev-body{font-size:.88rem;color:#444;margin:.4rem 0;}
    .rev-meta{font-size:.78rem;color:#888;}
    .rev-btns{display:flex;gap:.5rem;margin-top:.6rem;}

    /* Schedule table */
    .status-booked{color:#27ae60;font-weight:600;}
    .status-cancelled{color:#c0392b;font-weight:600;}

    /* Messages */
    .msg-card{background:#fafbfc;border-radius:10px;padding:1rem 1.2rem;margin-bottom:.75rem;border:1px solid #f0f4f8;}
    .msg-card.unread{border-left:4px solid #49a2e6;}
    .msg-subject{font-weight:700;font-size:.95rem;margin-bottom:.2rem;}
    .msg-from{font-size:.8rem;color:#888;margin-bottom:.5rem;}
    .msg-body-text{font-size:.88rem;color:#333;line-height:1.6;}

    /* Transactions */
    .tx-form{display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:.6rem;align-items:end;margin-bottom:1rem;}
    .tx-form select,.tx-form input{margin-bottom:0;}
    .tx-form input{padding:.55rem .8rem;border:1.5px solid #d0cdc8;border-radius:8px;font-family:inherit;font-size:.88rem;}
    .tx-form select{padding:.55rem .8rem;border:1.5px solid #d0cdc8;border-radius:8px;font-family:inherit;font-size:.88rem;}
    .charge{color:#c0392b;} .payment{color:#27ae60;} .refund{color:#e67e22;}

    @media(max-width:700px){
      body{flex-direction:column;}
      .sidebar{width:100%;min-height:auto;flex-direction:row;flex-wrap:wrap;padding:1rem;}
      .sidebar-nav{flex-direction:row;flex-wrap:wrap;}
      .sidebar-logout{margin-top:0;padding-top:0;}
      .tx-form{grid-template-columns:1fr 1fr;}
    }
  </style>
</head>
<body>
<!-- Sidebar -->
<aside class="sidebar">
  <img src="images/logos/logo.jpeg" alt="KP Wraps" style="width:160px;border-radius:8px;margin-bottom:1rem;">
  <div class="sidebar-sub">Admin Panel</div>
  <nav class="sidebar-nav">
    <button class="sidebar-btn active" onclick="showSection('clients',this)">Clients</button>
    <button class="sidebar-btn" onclick="showSection('reviews',this)">
      Reviews <?php if($pending_reviews>0):?><span class="badge"><?=$pending_reviews?></span><?php endif;?>
    </button>
    <button class="sidebar-btn" onclick="showSection('schedule',this)">Schedule</button>
    <button class="sidebar-btn" onclick="showSection('transactions',this)">Transactions</button>
    <button class="sidebar-btn" onclick="showSection('messages',this)">
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
          <input class="search-bar" type="text" placeholder="Search clients…" oninput="filterClients(this.value)">
          <button class="teal-btn" onclick="document.getElementById('addClientModal').classList.add('open')">+ Add Client</button>
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
            <td><button class="teal-btn" onclick="viewClient(<?=$c['user_id']?>)">View</button></td>
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
        <input type="date" id="scheduleFilter" class="search-bar" onchange="loadSchedule()">
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
        <button class="teal-btn" style="height:38px" onclick="addTransaction()">Add</button>
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
      <button class="teal-btn" onclick="addClient()">Create Account</button>
      <button class="outline-btn" onclick="document.getElementById('addClientModal').classList.remove('open')">Cancel</button>
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
      <button class="outline-btn" onclick="document.getElementById('viewClientModal').classList.remove('open')">Close</button>
    </div>
  </div>
</div>

<script>
// Section switching
function showSection(name, btn) {
  document.querySelectorAll('.section').forEach(s=>s.classList.remove('active'));
  document.getElementById('sec-'+name).classList.add('active');
  document.querySelectorAll('.sidebar-btn').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  if (name==='reviews') loadReviews();
  if (name==='schedule') loadSchedule();
  if (name==='transactions') loadTransactions();
  if (name==='messages') loadMessages();
}

// Client search
function filterClients(q) {
  q = q.toLowerCase();
  document.querySelectorAll('#clientTable tbody tr').forEach(r => {
    r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}

// Add client
async function addClient() {
  const first=document.getElementById('ac-first').value.trim();
  const last=document.getElementById('ac-last').value.trim();
  const email=document.getElementById('ac-email').value.trim();
  const phone=document.getElementById('ac-phone').value.trim();
  const pass=document.getElementById('ac-pass').value;
  const msg=document.getElementById('acMsg');
  msg.className='modal-msg';
  if(!first||!last||!email||!pass){msg.textContent='Please fill all fields.';msg.className='modal-msg err';return;}
  const fd=new FormData();
  fd.append('first_name',first);fd.append('last_name',last);fd.append('email',email);fd.append('phone',phone);fd.append('password',pass);
  const res=await fetch('register.php',{method:'POST',body:fd});
  const data=await res.json();
  if(data.success){msg.className='modal-msg ok';msg.textContent='Client added!';setTimeout(()=>location.reload(),1200);}
  else{msg.textContent=data.error||'Failed.';msg.className='modal-msg err';}
}

// View client
async function viewClient(id) {
  document.getElementById('vc-body').innerHTML='<p style="color:#aaa">Loading…</p>';
  document.getElementById('viewClientModal').classList.add('open');
  const res=await fetch('admin_api.php?action=client_profile&id='+id);
  const data=await res.json();
  if(!data.client){document.getElementById('vc-body').innerHTML='<p style="color:#c0392b">Could not load client.</p>';return;}
  const c=data.client;
  document.getElementById('vc-name').textContent=c.first_name+' '+c.last_name;
  let html=`<p><strong>Email:</strong> ${c.email}</p><p><strong>Phone:</strong> ${c.phone||'—'}</p><p><strong>Joined:</strong> ${c.created_at}</p><hr style="margin:.8rem 0;border-color:#e0ddd8">`;
  html+='<strong>Appointments</strong>';
  if(data.appointments.length===0){html+='<p style="color:#aaa">None.</p>';}
  else{html+=data.appointments.map(a=>`<p>${a.appointment_date} ${a.time_slot} — ${a.service} <span style="color:${a.status==='booked'?'#27ae60':'#c0392b'}">(${a.status})</span></p>`).join('');}
  html+='<hr style="margin:.8rem 0;border-color:#e0ddd8"><strong>Transactions</strong>';
  if(data.transactions.length===0){html+='<p style="color:#aaa">None.</p>';}
  else{html+=data.transactions.map(t=>`<p>${t.description} — <span class="${t.type}">$${parseFloat(t.amount).toFixed(2)} (${t.type})</span> <small style="color:#aaa">${t.created_at}</small></p>`).join('');}
  document.getElementById('vc-body').innerHTML=html;
}

// Reviews
async function loadReviews() {
  const res=await fetch('admin_api.php?action=reviews');
  const data=await res.json();
  const panel=document.getElementById('reviewsPanel');
  if(!data.reviews||data.reviews.length===0){panel.innerHTML='<p style="color:#aaa">No pending reviews.</p>';return;}
  panel.innerHTML='<div class="panel-title" style="margin-bottom:1rem">Pending Approval ('+data.reviews.filter(r=>!r.is_approved).length+')</div>'+
    data.reviews.map(r=>`
    <div class="rev-card">
      <div class="rev-card-header">
        <span class="stars">${'★'.repeat(r.rating)}${'☆'.repeat(5-r.rating)}</span>
        <span style="font-size:.78rem;color:#888">${r.created_at} — ${r.first_name} ${r.last_name}</span>
      </div>
      <div style="font-weight:700">${r.title}</div>
      <div class="rev-body">${r.body}</div>
      <div class="rev-btns">
        ${parseInt(r.is_approved) === 0 ? `<button class="teal-btn" onclick="moderateReview(${r.review_id},'approve')">✓ Approve</button>` : '<span style="color:#27ae60;font-size:.82rem;font-weight:600">✓ Approved</span>'}
        <button class="red-btn" onclick="moderateReview(${r.review_id},'delete')">✕ Delete</button>
      </div>
    </div>`).join('');
}
async function moderateReview(id,action) {
  const fd=new FormData();fd.append('action',action);fd.append('id',id);
  await fetch('admin_api.php',{method:'POST',body:fd});
  loadReviews();
}

// Schedule
async function loadSchedule() {
  const date=document.getElementById('scheduleFilter').value;
  const url='admin_api.php?action=schedule'+(date?'&date='+date:'');
  const res=await fetch(url);
  const data=await res.json();
  const el=document.getElementById('scheduleTable');
  if(!data.appointments||data.appointments.length===0){el.innerHTML='<p style="color:#aaa">No appointments found.</p>';return;}
  el.innerHTML='<table><thead><tr><th>Client</th><th>Date</th><th>Time</th><th>Service</th><th>Status</th><th></th></tr></thead><tbody>'+
    data.appointments.map(a=>`<tr>
      <td>${a.first_name} ${a.last_name}</td>
      <td>${a.appointment_date}</td>
      <td>${a.time_slot}</td>
      <td>${a.service}</td>
      <td class="status-${a.status}">${a.status}</td>
      <td>${a.status==='booked'?`<button class="red-btn" onclick="cancelAppt(${a.id})">Cancel</button>`:''}</td>
    </tr>`).join('')+'</tbody></table>';
}
async function cancelAppt(id) {
  if(!confirm('Cancel this appointment?'))return;
  const fd=new FormData();fd.append('action','cancel_appt');fd.append('id',id);
  await fetch('admin_api.php',{method:'POST',body:fd});
  loadSchedule();
}

// Transactions
async function loadTransactions() {
  const res=await fetch('admin_api.php?action=transactions');
  const data=await res.json();
  const el=document.getElementById('txTable');
  if(!data.transactions||data.transactions.length===0){el.innerHTML='<p style="color:#aaa">No transactions yet.</p>';return;}
  el.innerHTML='<table><thead><tr><th>Client</th><th>Description</th><th>Amount</th><th>Type</th><th>Date</th></tr></thead><tbody>'+
    data.transactions.map(t=>`<tr>
      <td>${t.first_name} ${t.last_name}</td>
      <td>${t.description}</td>
      <td class="${t.type}">$${parseFloat(t.amount).toFixed(2)}</td>
      <td>${t.type}</td>
      <td style="color:#aaa;font-size:.8rem">${t.created_at}</td>
    </tr>`).join('')+'</tbody></table>';
}
async function addTransaction() {
  const uid=document.getElementById('txClient').value;
  const desc=document.getElementById('txDesc').value.trim();
  const amount=document.getElementById('txAmount').value;
  const type=document.getElementById('txType').value;
  const msg=document.getElementById('txMsg');
  msg.className='modal-msg';
  if(!desc||!amount){msg.textContent='Description and amount are required.';msg.className='modal-msg err';return;}
  const fd=new FormData();fd.append('action','add_transaction');fd.append('user_id',uid);fd.append('description',desc);fd.append('amount',amount);fd.append('type',type);
  const res=await fetch('admin_api.php',{method:'POST',body:fd});
  const data=await res.json();
  if(data.success){msg.textContent='Transaction added!';msg.className='modal-msg ok';document.getElementById('txDesc').value='';document.getElementById('txAmount').value='';loadTransactions();}
  else{msg.textContent=data.error||'Failed.';msg.className='modal-msg err';}
}

// Messages
async function loadMessages() {
  const res=await fetch('admin_api.php?action=messages');
  const data=await res.json();
  const panel=document.getElementById('messagesPanel');
  if(!data.messages||data.messages.length===0){panel.innerHTML='<p style="color:#aaa">No messages yet.</p>';return;}
  panel.innerHTML=data.messages.map(m=>`
    <div class="msg-card ${m.is_read?'':'unread'}">
      <div class="msg-subject">${m.subject}</div>
      <div class="msg-from">From: ${m.name} &lt;${m.email}&gt; — <span style="color:#aaa">${m.created_at}</span></div>
      <div class="msg-body-text">${m.body}</div>
      ${!m.is_read?`<button class="outline-btn" style="margin-top:.6rem;font-size:.78rem" onclick="markRead(${m.message_id})">Mark as Read</button>`:''}
    </div>`).join('');
}
async function markRead(id) {
  const fd=new FormData();fd.append('action','mark_read');fd.append('id',id);
  await fetch('admin_api.php',{method:'POST',body:fd});
  loadMessages();
}
</script>
</body>
</html>
