<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="kp-nav">
  <a href="schedule.php" class="kp-nav-logo">
    <img src="images/logos/inverted logo.png" alt="KP Wraps" style="height:38px;display:block;">
  </a>
  <ul class="kp-nav-links">
    <li><a href="about.php" class="<?= $current_page === 'about.php' ? 'active' : '' ?>">About</a></li>
    <li><a href="schedule.php" class="<?= $current_page === 'schedule.php' ? 'active' : '' ?>">Schedule</a></li>
    <li><a href="preview.php" class="<?= $current_page === 'preview.php' ? 'active' : '' ?>">Wrap Selector</a></li>
    <li><a href="review.php" class="<?= $current_page === 'review.php' ? 'active' : '' ?>">Reviews</a></li>
    <li><a href="contact.php" class="<?= $current_page === 'contact.php' ? 'active' : '' ?>">Contact</a></li>
    <li><a href="logout.php" class="kp-nav-logout">Logout</a></li>
  </ul>
</nav>
<style>
  .kp-nav {
    background: #111;
    border-bottom: 1px solid #2a2a2a;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: .85rem 2.5rem;
    position: sticky;
    top: 0;
    z-index: 1000;
  }
  .kp-nav-logo {
    font-family: 'Bebas Neue', 'Trebuchet MS', sans-serif;
    font-size: 1.55rem;
    color: #49a2e6;
    letter-spacing: .1em;
    text-decoration: none;
  }
  .kp-nav-links {
    display: flex;
    gap: 2rem;
    list-style: none;
    margin: 0;
    padding: 0;
  }
  .kp-nav-links a {
    color: #aaa;
    text-decoration: none;
    font-size: .88rem;
    font-weight: 500;
    transition: color .2s;
  }
  .kp-nav-links a:hover,
  .kp-nav-links a.active { color: #49a2e6; }
  .kp-nav-links .kp-nav-logout { color: #888; }
  .kp-nav-links .kp-nav-logout:hover { color: #e05c5c; }
  @media(max-width:700px){
    .kp-nav { padding: .7rem 1rem; flex-wrap: wrap; gap: .5rem; }
    .kp-nav-links { gap: 1rem; flex-wrap: wrap; }
  }
</style>
