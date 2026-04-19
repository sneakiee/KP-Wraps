<?php
/**
 * Name: Real MaJiK (Team 47)
 * Created: March 23, 2026
 * Description: Displays the about page content and before/after showcase for signed-in clients.
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
  <title>KP Wraps — About</title>
  <!-- google fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/about.css">
</head>
<body>
<?php include 'nav.php'; // include the navbar ?>
<div class="shell">
  <div class="hero">
    <h1>KP Wraps &amp; Detailing</h1>
    <p>Hamilton's premier vehicle wrap and detailing studio. We transform your ride with precision craftsmanship and premium materials.</p>
  </div>

  <h2 class="section-title">Before &amp; After</h2>
  <!-- before and after image comparison slider -->
  <div class="ba-section">
    <div class="ba-wrapper" id="baWrapper">
      <img class="ba-img ba-before" src="images/before-after/before.jpeg" alt="Before wrap">
      <img class="ba-img ba-after"  src="images/before-after/after.jpeg"  alt="After wrap">
      <div class="ba-divider" id="baDivider"></div>
      <div class="ba-handle"  id="baHandle">&#8596;</div>
      <span class="ba-label ba-label-before">Before</span>
      <span class="ba-label ba-label-after">After</span>
    </div>
  </div>

  <h2 class="section-title">Our Mission</h2>
  <div class="mission">
    <p>At KP Wraps, we believe your vehicle is an extension of your identity. Our mission is to deliver flawless, long-lasting wraps and detailing services that turn heads and protect your investment.</p>
    <p>Founded by Parmeet Riar, we combine years of hands-on experience with the highest-quality vinyl films from 3M and Avery Dennison. Every project — from a roof wrap to a full colour change — is treated with the same meticulous attention to detail.</p>
    <p>We serve Hamilton, Burlington, Oakville, and the surrounding GTA. Book a consultation today and let's bring your vision to life.</p>
  </div>

  <h2 class="section-title">Our Services</h2>
  <!-- service cards grid -->
  <div class="services-grid">
    <div class="service-card">
      <h3>Full Colour Change Wrap</h3>
      <div class="price">From $2,200</div>
      <p>Complete vehicle colour change using premium cast vinyl. Includes all body panels, bumpers, and mirrors.</p>
    </div>
    <div class="service-card">
      <h3>Partial Wrap</h3>
      <div class="price">From $1,400</div>
      <p>Target specific panels — hood, roof, trunk, or doors. Great for accents and two-tone designs.</p>
    </div>
    <div class="service-card">
      <h3>Roof Wrap</h3>
      <div class="price">From $350</div>
      <p>Add a contrasting roof colour or matte finish. One of the most popular accent upgrades.</p>
    </div>
    <div class="service-card">
      <h3>Chrome Delete</h3>
      <div class="price">From $299</div>
      <p>Blackout or colour-match all chrome trim pieces for a clean, modern look.</p>
    </div>
    <div class="service-card">
      <h3>Full Detail</h3>
      <div class="price">From $199</div>
      <p>Interior and exterior detail — hand wash, clay bar, leather conditioning, vacuum, and glass treatment.</p>
    </div>
    <div class="service-card">
      <h3>Paint Correction</h3>
      <div class="price">From $299</div>
      <p>Single or multi-stage machine polish to remove swirls, scratches, and oxidation before wrapping.</p>
    </div>
  </div>

  <!-- call to action buttons -->
  <div class="cta">
    <a href="schedule.php" class="cta-btn">Book an Appointment</a>
    <a href="contact.php" class="cta-btn outline">Get in Touch</a>
  </div>
</div>
<script src="js/about.js"></script>
</body>
</html>