<?php
/**
 * Name: Real MaJiK (Team 47)
 * Created: March 23, 2026
 * Description: Displays the client contact page and message submission interface.
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
  <title>KP Wraps — Contact</title>
  <!-- google fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/contact.css">
</head>
<body>
<?php include 'nav.php'; // include the navbar ?>
<div class="shell">
  <div class="hero">
    <h1>Get in Touch</h1>
    <p>Questions, quotes, or just want to chat about your build? We're here.</p>
  </div>
  <div class="card">
    <!-- name and email side by side -->
    <div class="row2">
      <div><label>Your Name</label><input type="text" id="cName" placeholder="Jane Doe"></div>
      <div><label>Email</label><input type="email" id="cEmail" placeholder="you@example.com"></div>
    </div>
    <label>Subject</label>
    <input type="text" id="cSubject" placeholder="e.g. Quote for a full wrap on my truck">
    <label>Message</label>
    <textarea id="cBody" placeholder="Tell us about your vehicle and what you're looking for…"></textarea>
    <button class="submit-btn" id="sendBtn">Send Message</button>
    <!-- success and error messages shown after submission -->
    <div class="alert ok" id="alertOk"></div>
    <div class="alert err" id="alertErr"></div>
  </div>
</div>
<script src="js/contact.js"></script>
</body>
</html>