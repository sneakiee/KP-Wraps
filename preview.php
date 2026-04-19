<?php
/**
 * Name: Real MaJiK (Team 47)
 * Created: March 23, 2026
 * Description: Displays the wrap selector interface with vehicle, color, and quote preview tools.
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
  <title>KP Wraps — Wrap Selector</title>
  <!-- google fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/preview.css">
</head>
<body>
<?php include 'nav.php'; // include the navbar ?>
<div class="shell">
  <div class="hero">
    <h1>Wrap Selector &amp; Quote Estimator</h1>
    <p>Choose your vehicle type and wrap color to get an instant price estimate.</p>
  </div>

  <h2 class="section-title">Step 1 — Choose Your Vehicle</h2>
  <!-- vehicle type cards -->
  <div class="vehicle-grid" id="vehicleGrid">
    <div class="vehicle-card active" data-vehicle="sedan">
      <div class="vehicle-icon"></div><h3>Sedan</h3>
    </div>
    <div class="vehicle-card" data-vehicle="suv">
      <div class="vehicle-icon"></div><h3>SUV / Crossover</h3>
    </div>
    <div class="vehicle-card" data-vehicle="truck">
      <div class="vehicle-icon"></div><h3>Truck</h3>
    </div>
    <div class="vehicle-card" data-vehicle="coupe">
      <div class="vehicle-icon"></div><h3>Coupe / Sports</h3>
    </div>
  </div>

  <h2 class="section-title">Step 2 — Choose Your Wrap Color</h2>
  <!-- colour swatches -->
  <div class="swatch-row" id="swatchRow">
    <div class="swatch-btn active" data-color="black">
      <div class="swatch-dot" style="background:#1a1a1a"></div><span>Gloss Black</span>
    </div>
    <div class="swatch-btn" data-color="white">
      <div class="swatch-dot" style="background:#f0f0f0;border-color:#ccc"></div><span>Gloss White</span>
    </div>
    <div class="swatch-btn" data-color="red">
      <div class="swatch-dot" style="background:#c0392b"></div><span>Gloss Red</span>
    </div>
    <div class="swatch-btn" data-color="blue">
      <div class="swatch-dot" style="background:#2980b9"></div><span>Gloss Blue</span>
    </div>
    <div class="swatch-btn" data-color="gold">
      <div class="swatch-dot" style="background:#d4a017"></div><span>Satin Gold</span>
    </div>
    <div class="swatch-btn" data-color="green">
      <div class="swatch-dot" style="background:#27ae60"></div><span>Matte Green</span>
    </div>
  </div>

  <!-- Real car photo preview -->
  <h2 class="section-title">Preview</h2>
  <div class="preview-area">
    <!-- image swaps out when vehicle or colour changes -->
    <img id="carImg" src="images/car-colors/black sedan.webp" alt="Car wrap preview"
         style="width:100%;max-height:320px;object-fit:contain;border-radius:10px;transition:opacity .25s;">
  </div>

  <!-- Quote panel -->
  <div class="quote-panel">
    <div>
      <h3>Instant Quote Estimator</h3>
      <div class="quote-meta">
        <!-- coverage dropdown affects the price multiplier -->
        <span>Coverage:
          <select id="coverageSelect">
            <option value="full">Full Wrap</option>
            <option value="partial">Partial Wrap (70%)</option>
            <option value="roof">Roof Only (35%)</option>
          </select>
        </span>
        <span>Vehicle: <strong id="qVehicle">Sedan</strong></span>
        <span>Color: <strong id="qColor">Gloss Black</strong></span>
      </div>
      <button class="book-quote-btn" id="bookQuoteBtn">Book This Wrap →</button>
    </div>
    <!-- live price estimate updated on every selection change -->
    <div class="estimate" id="qEstimate">$2,200</div>
  </div>
</div>

<script src="js/preview.js"></script>
</body>
</html>