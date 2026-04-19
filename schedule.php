<?php
// start session and redirect if not logged in as a client
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
    header('Location: index.html'); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KP Wraps — Schedule</title>
  <!-- google fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* css variables for reusable colours and styles */
    :root {
      --bg: #070a10; --panel: #ffffff; --text: #1a1f2e; --muted: #6a6f7e;
      --accent: #49a2e6; --danger: #d75a44; --shadow: 0 14px 30px rgba(0,0,0,0.28); --radius: 14px;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    /* dark gradient background */
    body {
      min-height: 100vh;
      font-family: 'DM Sans', sans-serif;
      background: radial-gradient(circle at 15% 15%, rgba(106,182,255,0.15), transparent 35%),
                  radial-gradient(circle at 85% 10%, rgba(78,205,196,0.2), transparent 32%),
                  linear-gradient(180deg, #05070d 0%, #091221 100%);
      color: #f6f8ff;
    }
    .shell { width: min(1100px,100%); margin: 0 auto; padding: 0 1rem 3rem; }
    .hero { text-align: center; padding: 2rem 0 1.25rem; }
    .hero h1 { font-family: 'Bebas Neue', sans-serif; font-size: clamp(1.6rem,4vw,2.2rem); }
    .hero p { color: #b5bccf; margin-top: .4rem; }

    /* calendar header with month name and nav arrows */
    .cal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
    .cal-header h2 { font-family: 'Bebas Neue', sans-serif; font-size: 1.4rem; letter-spacing: .05em; }
    .cal-nav { background: none; border: 1.5px solid #3a3f50; color: #aaa; border-radius: 8px; padding: .4rem .9rem; cursor: pointer; font-size: 1rem; transition: all .2s; }
    .cal-nav:hover { border-color: var(--accent); color: var(--accent); }

    /* 7-column grid for the calendar days */
    .cal-grid { display: grid; grid-template-columns: repeat(7,1fr); gap: 6px; }
    .cal-day-name { text-align: center; font-size: .75rem; font-weight: 700; color: #6a6f7e; padding: .4rem 0; text-transform: uppercase; }
    .cal-day {
      aspect-ratio: 1; display: flex; align-items: center; justify-content: center;
      border-radius: 10px; font-size: .88rem; cursor: pointer; border: 1.5px solid transparent;
      transition: all .18s; background: #131824;
    }
    .cal-day:empty { background: transparent; cursor: default; }
    /* past days are greyed out and not clickable */
    .cal-day.past { color: #3a3f50; cursor: not-allowed; }
    /* today gets a blue border */
    .cal-day.today { border-color: var(--accent); color: var(--accent); font-weight: 700; }
    /* selected day gets a solid blue background */
    .cal-day.selected { background: var(--accent); color: #070a10; font-weight: 700; }
    .cal-day:not(.past):not(:empty):hover:not(.selected) { border-color: var(--accent); color: var(--accent); }

    .section-title { font-family: 'Bebas Neue', sans-serif; font-size: 1.2rem; letter-spacing: .05em; margin-bottom: .75rem; color: #e0e4f0; }

    /* grid of time slot buttons */
    .slots-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: .5rem; margin-bottom: 1.5rem; }
    .slot {
      padding: .55rem; text-align: center; border-radius: 8px; font-size: .85rem; font-weight: 600;
      cursor: pointer; border: 1.5px solid transparent; transition: all .18s;
    }
    /* available slots are teal/blue */
    .slot.available { background: #0d2b2a; border-color: var(--accent); color: var(--accent); }
    .slot.available:hover { background: var(--accent); color: #070a10; }
    .slot.available.selected { background: var(--accent); color: #070a10; }
    /* booked slots are red and not clickable */
    .slot.booked { background: #2b0f0a; border-color: var(--danger); color: var(--danger); cursor: not-allowed; opacity: .7; }
    .slot-hint { color: #6a6f7e; font-size: .82rem; margin-bottom: 1rem; }

    /* booking confirmation card shown when a slot is selected */
    .booking-card {
      background: var(--panel); border-radius: var(--radius); padding: 1.3rem 1.5rem;
      color: var(--text); box-shadow: var(--shadow);
    }
    .booking-card h3 { font-family: 'Bebas Neue', sans-serif; font-size: 1.4rem; margin-bottom: .8rem; }
    .booking-meta { display: flex; flex-wrap: wrap; gap: 1.2rem; margin-bottom: 1rem; font-size: .9rem; color: #555; }
    .booking-meta span strong { color: var(--text); }
    .service-select { margin-bottom: 1rem; }
    .service-select label { font-size: .82rem; font-weight: 600; color: #555; display: block; margin-bottom: .3rem; }
    .service-select select {
      border: 1.5px solid #c9ceda; border-radius: 8px; padding: .45rem .6rem;
      font-size: .9rem; font-family: inherit; color: #1a1f2e; background: #fff; width: 100%;
    }
    /* confirm button */
    .confirm-btn {
      background: var(--accent); color: #fff; border: none; border-radius: 8px;
      padding: .7rem 1.6rem; font-size: .95rem; font-weight: 700; cursor: pointer; font-family: inherit;
      transition: background .2s;
    }
    .confirm-btn:hover { background: #2d86c9; }
    .confirm-btn:disabled { background: #aaa; cursor: not-allowed; }
    /* success and error messages after booking */
    .booking-msg { margin-top: .75rem; font-size: .88rem; font-weight: 600; }
    .booking-msg.ok { color: #27ae60; }
    .booking-msg.err { color: #c0392b; }

    /* the user's appointments list */
    .my-appts { background: var(--panel); border-radius: var(--radius); padding: 1.3rem 1.5rem; color: var(--text); box-shadow: var(--shadow); }
    .my-appts h3 { font-family: 'Bebas Neue', sans-serif; font-size: 1.3rem; margin-bottom: .8rem; }
    .appt-row { display: flex; align-items: center; justify-content: space-between; padding: .6rem 0; border-bottom: 1px solid #e0ddd8; gap: 1rem; flex-wrap: wrap; }
    .appt-row:last-child { border-bottom: none; }
    .appt-info { font-size: .88rem; }
    .appt-info strong { display: block; margin-bottom: .1rem; }
    /* red cancel button on each appointment */
    .cancel-btn { background: #c0392b; color: #fff; border: none; border-radius: 6px; padding: .35rem .8rem; font-size: .82rem; font-weight: 600; cursor: pointer; font-family: inherit; }
    .cancel-btn:hover { background: #a93226; }
    .no-appts { color: #888; font-size: .9rem; }

    /* two column layout for the booking card and appointments list */
    .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem; }
    /* stack to single column on small screens */
    @media(max-width:750px){ .grid2 { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
<?php include 'nav.php'; // include the navbar ?>
<div class="shell">
  <div class="hero">
    <h1>Book an Appointment</h1>
    <p>Pick a date and time that works for you.</p>
  </div>

  <!-- Calendar navigation -->
  <div class="cal-header">
    <button class="cal-nav" id="prevMonth">&#8592;</button>
    <h2 id="calTitle"></h2>
    <button class="cal-nav" id="nextMonth">&#8594;</button>
  </div>
  <!-- calendar days are rendered here by JS -->
  <div class="cal-grid" id="calGrid"></div>

  <!-- Time slots shown after picking a date -->
  <div style="margin-top:1.5rem">
    <p class="section-title" id="slotsTitle">Select a date to see available times</p>
    <!-- legend for the slot colours -->
    <div id="slotHint" class="slot-hint" style="display:none">
      <span style="display:inline-block;width:12px;height:12px;background:#49a2e6;border-radius:3px;margin-right:4px"></span>Available &nbsp;
      <span style="display:inline-block;width:12px;height:12px;background:#d75a44;border-radius:3px;margin-right:4px"></span>Booked
    </div>
    <!-- slot buttons are rendered here by JS -->
    <div class="slots-grid" id="slotsGrid"></div>
  </div>

  <!-- Bottom row: booking card + my appointments side by side -->
  <div class="grid2">
    <!-- Booking card - hidden until a slot is selected -->
    <div class="booking-card" id="bookingCard" style="display:none">
      <h3>Confirm Booking</h3>
      <div class="booking-meta">
        <span><strong id="bc-date">—</strong>Date</span>
        <span><strong id="bc-time">—</strong>Time</span>
      </div>
      <!-- service dropdown -->
      <div class="service-select">
        <label for="serviceSelect">Service</label>
        <select id="serviceSelect">
          <option>Wrap Consultation</option>
          <option>Full Wrap</option>
          <option>Partial Wrap</option>
          <option>Roof Wrap</option>
          <option>Paint Correction</option>
          <option>Full Detail</option>
          <option>Interior Detail</option>
          <option>Chrome Delete</option>
        </select>
      </div>
      <button class="confirm-btn" id="confirmBtn" onclick="confirmBooking()">Confirm Appointment</button>
      <p class="booking-msg" id="bookingMsg"></p>
    </div>

    <!-- My appointments list -->
    <div class="my-appts" id="myAppts">
      <h3>My Appointments</h3>
      <div id="apptList"><p class="no-appts">Loading…</p></div>
    </div>
  </div>
</div>

<script>
// track the current view date and selections
const today = new Date();
let viewYear = today.getFullYear();
let viewMonth = today.getMonth();
let selectedDate = null;
let selectedSlot = null;

const DAY_NAMES = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
const MONTH_NAMES = ['January','February','March','April','May','June','July','August','September','October','November','December'];

// format a date into YYYY-MM-DD string
function formatDate(y, m, d) {
  return `${y}-${String(m+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
}

// build and render the calendar grid for the current month
function renderCal() {
  document.getElementById('calTitle').textContent = `${MONTH_NAMES[viewMonth]} ${viewYear}`;
  const grid = document.getElementById('calGrid');
  // add day name headers
  grid.innerHTML = DAY_NAMES.map(d => `<div class="cal-day-name">${d}</div>`).join('');
  // add empty cells before the first day of the month
  const first = new Date(viewYear, viewMonth, 1).getDay();
  for (let i = 0; i < first; i++) grid.innerHTML += '<div class="cal-day"></div>';
  // add a cell for each day in the month
  const days = new Date(viewYear, viewMonth+1, 0).getDate();
  for (let d = 1; d <= days; d++) {
    const dateStr = formatDate(viewYear, viewMonth, d);
    const cellDate = new Date(viewYear, viewMonth, d);
    // check if the day is in the past
    const isPast = cellDate < new Date(today.getFullYear(), today.getMonth(), today.getDate());
    const isToday = d === today.getDate() && viewMonth === today.getMonth() && viewYear === today.getFullYear();
    const isSel = dateStr === selectedDate;
    let cls = 'cal-day';
    if (isPast) cls += ' past';
    if (isToday) cls += ' today';
    if (isSel) cls += ' selected';
    // past days don't get a click handler
    grid.innerHTML += `<div class="${cls}" ${!isPast ? `onclick="pickDate('${dateStr}')"` : ''}>${d}</div>`;
  }
}

// called when a date is clicked, loads the available time slots for that day
async function pickDate(dateStr) {
  selectedDate = dateStr;
  selectedSlot = null;
  // hide the booking card until a slot is picked
  document.getElementById('bookingCard').style.display = 'none';
  document.getElementById('bookingMsg').textContent = '';
  renderCal();
  document.getElementById('slotsTitle').textContent = `Available times for ${dateStr}`;
  document.getElementById('slotHint').style.display = 'block';
  document.getElementById('slotsGrid').innerHTML = '<div style="color:#aaa;font-size:.85rem">Loading…</div>';
  try {
    // fetch which slots are already booked for this date
    const res = await fetch(`schedule_api.php?action=slots&date=${dateStr}`);
    const data = await res.json();
    renderSlots(data.booked || []);
  } catch { document.getElementById('slotsGrid').innerHTML = '<div style="color:#d75a44">Could not load slots.</div>'; }
}

// render the time slot buttons from 9am to 5pm in 30 min intervals
function renderSlots(booked) {
  const times = [];
  for (let h = 9; h < 17; h++) {
    for (let m = 0; m < 60; m += 30) {
      const label = `${h > 12 ? h-12 : h}:${String(m).padStart(2,'0')} ${h >= 12 ? 'PM' : 'AM'}`;
      const val = `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}`;
      times.push({ label, val });
    }
  }
  // render each slot, marking booked ones as unclickable
  document.getElementById('slotsGrid').innerHTML = times.map(({ label, val }) => {
    const isBooked = booked.includes(val);
    const isSel = val === selectedSlot;
    return `<div class="slot ${isBooked ? 'booked' : 'available' + (isSel ? ' selected' : '')}"
      ${!isBooked ? `onclick="pickSlot('${val}','${label}')"` : ''}>${label}</div>`;
  }).join('');
}

// called when a time slot is clicked, shows the booking confirmation card
function pickSlot(val, label) {
  selectedSlot = val;
  // deselect any previously selected slot
  document.getElementById('slotsGrid').querySelectorAll('.slot.available').forEach(el => el.classList.remove('selected'));
  event.target.classList.add('selected');
  // fill in the date and time in the booking card
  document.getElementById('bc-date').textContent = selectedDate;
  document.getElementById('bc-time').textContent = label;
  document.getElementById('bookingCard').style.display = 'block';
  document.getElementById('bookingMsg').textContent = '';
}

// submit the booking to the backend
async function confirmBooking() {
  if (!selectedDate || !selectedSlot) return;
  const btn = document.getElementById('confirmBtn');
  const msg = document.getElementById('bookingMsg');
  btn.disabled = true; btn.textContent = 'Booking…';
  const fd = new FormData();
  fd.append('action','book'); fd.append('date', selectedDate);
  fd.append('slot', selectedSlot); fd.append('service', document.getElementById('serviceSelect').value);
  try {
    const res = await fetch('schedule_api.php', { method: 'POST', body: fd });
    const data = await res.json();
    msg.className = 'booking-msg ' + (data.success ? 'ok' : 'err');
    msg.textContent = data.success ? 'Appointment confirmed!' : (data.error || 'Booking failed.');
    // on success reload appointments and refresh the slots
    if (data.success) { loadMyAppts(); pickDate(selectedDate); selectedSlot = null; document.getElementById('bookingCard').style.display='none'; }
  } catch { msg.className='booking-msg err'; msg.textContent='Network error.'; }
  finally { btn.disabled=false; btn.textContent='Confirm Appointment'; }
}

// cancel an appointment after confirming with the user
async function cancelAppt(id) {
  if (!confirm('Cancel this appointment?')) return;
  const fd = new FormData(); fd.append('action','cancel'); fd.append('id', id);
  const res = await fetch('schedule_api.php', { method: 'POST', body: fd });
  const data = await res.json();
  if (data.success) loadMyAppts();
  else alert(data.error || 'Could not cancel.');
}

// fetch and render the logged in user's appointments
async function loadMyAppts() {
  try {
    const res = await fetch('schedule_api.php?action=my_appointments');
    const data = await res.json();
    const list = document.getElementById('apptList');
    if (!data.appointments || data.appointments.length === 0) {
      list.innerHTML = '<p class="no-appts">No upcoming appointments.</p>'; return;
    }
    // render each appointment with a cancel button if it's still booked
    list.innerHTML = data.appointments.map(a => `
      <div class="appt-row">
        <div class="appt-info">
          <strong>${a.appointment_date} at ${a.time_slot}</strong>
          ${a.service} &mdash; <span style="color:${a.status==='booked'?'#27ae60':'#c0392b'}">${a.status}</span>
        </div>
        ${a.status==='booked' ? `<button class="cancel-btn" onclick="cancelAppt(${a.id})">Cancel</button>` : ''}
      </div>`).join('');
  } catch { document.getElementById('apptList').innerHTML='<p class="no-appts">Could not load appointments.</p>'; }
}

// navigate between months
document.getElementById('prevMonth').onclick = () => { viewMonth--; if (viewMonth < 0) { viewMonth=11; viewYear--; } renderCal(); };
document.getElementById('nextMonth').onclick = () => { viewMonth++; if (viewMonth > 11) { viewMonth=0; viewYear++; } renderCal(); };

// render the calendar and load appointments on page load
renderCal();
loadMyAppts();
</script>
</body>
</html>