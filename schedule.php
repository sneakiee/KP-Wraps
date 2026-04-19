<?php
/**
 * Name: Real MaJiK (Team 47)
 * Created: March 23, 2026
 * Description: Displays the scheduling interface for booking and managing client appointments.
 */
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
  <link rel="stylesheet" href="css/schedule.css">
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
      <button class="confirm-btn" id="confirmBtn">Confirm Appointment</button>
      <p class="booking-msg" id="bookingMsg"></p>
    </div>

    <!-- My appointments list -->
    <div class="my-appts" id="myAppts">
      <h3>My Appointments</h3>
      <div id="apptList"><p class="no-appts">Loading…</p></div>
    </div>
  </div>
</div>

<script src="js/schedule.js"></script>
</body>
</html>