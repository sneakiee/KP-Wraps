/**
 * Name: Real MaJiK (Team 47)
 * Created: March 23, 2026
 * Description: Implements admin dashboard interactions, data loading, and action handlers.
 */

// Section switching
/**
 * purpose of function
 * @param {param} name
 * @param {param} btn
 * @returns return
 */
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

/**
 * purpose of function
 * @returns return
 */
function setupAdminListeners() {
  document.querySelectorAll('.sidebar-btn[data-section]').forEach(btn => {
    btn.addEventListener('click', () => showSection(btn.dataset.section, btn));
  });

  const clientSearch = document.getElementById('clientSearch');
  if (clientSearch) {
    clientSearch.addEventListener('input', e => filterClients(e.target.value));
  }

  const openAddClientModalBtn = document.getElementById('openAddClientModalBtn');
  if (openAddClientModalBtn) {
    openAddClientModalBtn.addEventListener('click', () => {
      document.getElementById('addClientModal').classList.add('open');
    });
  }

  const createClientBtn = document.getElementById('createClientBtn');
  if (createClientBtn) {
    createClientBtn.addEventListener('click', addClient);
  }

  const cancelAddClientModalBtn = document.getElementById('cancelAddClientModalBtn');
  if (cancelAddClientModalBtn) {
    cancelAddClientModalBtn.addEventListener('click', () => {
      document.getElementById('addClientModal').classList.remove('open');
    });
  }

  const closeViewClientModalBtn = document.getElementById('closeViewClientModalBtn');
  if (closeViewClientModalBtn) {
    closeViewClientModalBtn.addEventListener('click', () => {
      document.getElementById('viewClientModal').classList.remove('open');
    });
  }

  const addTransactionBtn = document.getElementById('addTransactionBtn');
  if (addTransactionBtn) {
    addTransactionBtn.addEventListener('click', addTransaction);
  }

  const scheduleFilter = document.getElementById('scheduleFilter');
  if (scheduleFilter) {
    scheduleFilter.addEventListener('change', loadSchedule);
  }

  const clientTable = document.getElementById('clientTable');
  if (clientTable) {
    clientTable.addEventListener('click', e => {
      const viewBtn = e.target.closest('.view-client-btn');
      if (viewBtn) {
        viewClient(viewBtn.dataset.clientId);
      }
    });
  }

  const reviewsPanel = document.getElementById('reviewsPanel');
  if (reviewsPanel) {
    reviewsPanel.addEventListener('click', e => {
      const actionBtn = e.target.closest('[data-review-id][data-review-action]');
      if (actionBtn) {
        moderateReview(actionBtn.dataset.reviewId, actionBtn.dataset.reviewAction);
      }
    });
  }

  const scheduleTable = document.getElementById('scheduleTable');
  if (scheduleTable) {
    scheduleTable.addEventListener('click', e => {
      const cancelBtn = e.target.closest('.cancel-appt-btn[data-appt-id]');
      if (cancelBtn) {
        cancelAppt(cancelBtn.dataset.apptId);
      }
    });
  }

  const messagesPanel = document.getElementById('messagesPanel');
  if (messagesPanel) {
    messagesPanel.addEventListener('click', e => {
      const markReadBtn = e.target.closest('.mark-read-btn[data-message-id]');
      if (markReadBtn) {
        markRead(markReadBtn.dataset.messageId);
      }
    });
  }
}

// Client search
/**
 * purpose of function
 * @param {param} q
 * @returns return
 */
function filterClients(q) {
  q = q.toLowerCase();
  document.querySelectorAll('#clientTable tbody tr').forEach(r => {
    r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}

// Add client
/**
 * purpose of function
 * @returns return
 */
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
/**
 * purpose of function
 * @param {param} id
 * @returns return
 */
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
/**
 * purpose of function
 * @returns return
 */
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
        ${parseInt(r.is_approved) === 0 ? `<button class="teal-btn" data-review-id="${r.review_id}" data-review-action="approve">✓ Approve</button>` : '<span style="color:#27ae60;font-size:.82rem;font-weight:600">✓ Approved</span>'}
        <button class="red-btn" data-review-id="${r.review_id}" data-review-action="delete">✕ Delete</button>
      </div>
    </div>`).join('');
}
/**
 * purpose of function
 * @param {param} id
 * @param {param} action
 * @returns return
 */
async function moderateReview(id,action) {
  const fd=new FormData();fd.append('action',action);fd.append('id',id);
  await fetch('admin_api.php',{method:'POST',body:fd});
  loadReviews();
}

// Schedule
/**
 * purpose of function
 * @returns return
 */
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
      <td>${a.status==='booked'?`<button class="red-btn cancel-appt-btn" data-appt-id="${a.id}">Cancel</button>`:''}</td>
    </tr>`).join('')+'</tbody></table>';
}
/**
 * purpose of function
 * @param {param} id
 * @returns return
 */
async function cancelAppt(id) {
  if(!confirm('Cancel this appointment?'))return;
  const fd=new FormData();fd.append('action','cancel_appt');fd.append('id',id);
  await fetch('admin_api.php',{method:'POST',body:fd});
  loadSchedule();
}

// Transactions
/**
 * purpose of function
 * @returns return
 */
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
/**
 * purpose of function
 * @returns return
 */
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
/**
 * purpose of function
 * @returns return
 */
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
      ${!m.is_read?`<button class="outline-btn mark-read-btn" style="margin-top:.6rem;font-size:.78rem" data-message-id="${m.message_id}">Mark as Read</button>`:''}
    </div>`).join('');
}
/**
 * purpose of function
 * @param {param} id
 * @returns return
 */
async function markRead(id) {
  const fd=new FormData();fd.append('action','mark_read');fd.append('id',id);
  await fetch('admin_api.php',{method:'POST',body:fd});
  loadMessages();
}

document.addEventListener('DOMContentLoaded', setupAdminListeners);
