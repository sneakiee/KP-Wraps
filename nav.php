<?php
/**
 * Name: Real MaJiK (Team 47)
 * Created: March 23, 2026
 * Description: Renders the shared navigation bar used across authenticated client pages.
 */
// get the current filename to highlight the active nav link
$current_page = basename($_SERVER['PHP_SELF']);
?>
<link rel="stylesheet" href="css/nav.css">
<nav class="kp-nav">
  <!-- logo links back to the schedule page -->
  <a href="schedule.php" class="kp-nav-logo">
    <img src="images/logos/inverted logo.png" alt="KP Wraps" style="height:38px;display:block;">
  </a>
  <ul class="kp-nav-links">
    <!-- each link gets the active class if it matches the current page -->
    <li><a href="about.php" class="<?= $current_page === 'about.php' ? 'active' : '' ?>">About</a></li>
    <li><a href="schedule.php" class="<?= $current_page === 'schedule.php' ? 'active' : '' ?>">Schedule</a></li>
    <li><a href="preview.php" class="<?= $current_page === 'preview.php' ? 'active' : '' ?>">Wrap Selector</a></li>
    <li><a href="review.php" class="<?= $current_page === 'review.php' ? 'active' : '' ?>">Reviews</a></li>
    <li><a href="contact.php" class="<?= $current_page === 'contact.php' ? 'active' : '' ?>">Contact</a></li>
    <li><a href="logout.php" class="kp-nav-logout">Logout</a></li>
  </ul>
</nav>