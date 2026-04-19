/**
 * Name: Real MaJiK (Team 47)
 * Created: March 23, 2026
 * Description: Implements calendar rendering, slot selection, booking, and appointment management logic.
 */

// track the current view date and selections
const today = new Date();
let viewYear = today.getFullYear();
let viewMonth = today.getMonth();
let selectedDate = null;
let selectedSlot = null;

const DAY_NAMES = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
const MONTH_NAMES = ['January','February','March','April','May','June','July','August','September','October','November','December'];

// format a date into YYYY-MM-DD string
/**
 * purpose of function
 * @param {param} y
 * @param {param} m
 * @param {param} d
 * @returns return
 */
function formatDate(y, m, d) {
  return `${y}-${String(m+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
}

// build and render the calendar grid for the current month
/**
 * purpose of function
 * @returns return
 */
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
    // past days are rendered but not selectable
    grid.innerHTML += `<div class="${cls}" ${!isPast ? `data-date="${dateStr}"` : ''}>${d}</div>`;
  }
}

// called when a date is clicked, loads the available time slots for that day
/**
 * purpose of function
 * @param {param} dateStr
 * @returns return
 */
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
/**
 * purpose of function
 * @param {param} booked
 * @returns return
 */
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
      ${!isBooked ? `data-slot-val="${val}" data-slot-label="${label}"` : ''}>${label}</div>`;
  }).join('');
}

// called when a time slot is clicked, shows the booking confirmation card
/**
 * purpose of function
 * @param {param} val
 * @param {param} label
 * @returns return
 */
function pickSlot(val, label, slotEl) {
  selectedSlot = val;
  // deselect any previously selected slot
  document.getElementById('slotsGrid').querySelectorAll('.slot.available').forEach(el => el.classList.remove('selected'));
  slotEl.classList.add('selected');
  // fill in the date and time in the booking card
  document.getElementById('bc-date').textContent = selectedDate;
  document.getElementById('bc-time').textContent = label;
  document.getElementById('bookingCard').style.display = 'block';
  document.getElementById('bookingMsg').textContent = '';
}

// submit the booking to the backend
/**
 * purpose of function
 * @returns return
 */
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
/**
 * purpose of function
 * @param {param} id
 * @returns return
 */
async function cancelAppt(id) {
  if (!confirm('Cancel this appointment?')) return;
  const fd = new FormData(); fd.append('action','cancel'); fd.append('id', id);
  const res = await fetch('schedule_api.php', { method: 'POST', body: fd });
  const data = await res.json();
  if (data.success) loadMyAppts();
  else alert(data.error || 'Could not cancel.');
}

// fetch and render the logged in user's appointments
/**
 * purpose of function
 * @returns return
 */
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
        ${a.status==='booked' ? `<button class="cancel-btn js-cancel-appt" data-appt-id="${a.id}">Cancel</button>` : ''}
      </div>`).join('');
  } catch { document.getElementById('apptList').innerHTML='<p class="no-appts">Could not load appointments.</p>'; }
}

/**
 * purpose of function
 * @returns return
 */
function setupScheduleListeners() {
  document.getElementById('prevMonth').addEventListener('click', () => {
    viewMonth--;
    if (viewMonth < 0) { viewMonth = 11; viewYear--; }
    renderCal();
  });

  document.getElementById('nextMonth').addEventListener('click', () => {
    viewMonth++;
    if (viewMonth > 11) { viewMonth = 0; viewYear++; }
    renderCal();
  });

  document.getElementById('calGrid').addEventListener('click', e => {
    const dayCell = e.target.closest('.cal-day[data-date]');
    if (dayCell) {
      pickDate(dayCell.dataset.date);
    }
  });

  document.getElementById('slotsGrid').addEventListener('click', e => {
    const slot = e.target.closest('.slot.available[data-slot-val]');
    if (slot) {
      pickSlot(slot.dataset.slotVal, slot.dataset.slotLabel, slot);
    }
  });

  document.getElementById('confirmBtn').addEventListener('click', confirmBooking);

  document.getElementById('apptList').addEventListener('click', e => {
    const cancelBtn = e.target.closest('.js-cancel-appt[data-appt-id]');
    if (cancelBtn) {
      cancelAppt(cancelBtn.dataset.apptId);
    }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  setupScheduleListeners();
  renderCal();
  loadMyAppts();
});
