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
  <title>KP Wraps — Wrap Selector</title>
  <!-- google fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* css variables for reusable colours */
    :root {
      --bg: #070a10; --panel: #ffffff; --text: #1a1f2e; --accent: #49a2e6;
      --danger: #d75a44; --shadow: 0 14px 30px rgba(0,0,0,0.28); --radius: 14px;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    /* dark gradient background */
    body { min-height: 100vh; font-family: 'DM Sans', sans-serif;
      background: radial-gradient(circle at 15% 15%, rgba(106,182,255,0.15), transparent 35%),
                  radial-gradient(circle at 85% 10%, rgba(78,205,196,0.2), transparent 32%),
                  linear-gradient(180deg, #05070d 0%, #091221 100%); color: #f6f8ff; }
    .shell { width: min(1100px,100%); margin: 0 auto; padding: 0 1rem 3rem; }
    .hero { text-align: center; padding: 2rem 0 1.5rem; }
    .hero h1 { font-family: 'Bebas Neue', sans-serif; font-size: clamp(1.6rem,4vw,2.2rem); }
    .hero p { color: #b5bccf; margin-top: .4rem; }
    .section-title { font-family: 'Bebas Neue', sans-serif; font-size: 1.2rem; letter-spacing: .05em; margin-bottom: .75rem; }

    /* Vehicle selector - 4 cards in a row */
    .vehicle-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 1rem; margin-bottom: 2rem; }
    .vehicle-card {
      background: #131824; border: 2px solid transparent; border-radius: var(--radius);
      padding: 1.2rem .8rem; text-align: center; cursor: pointer; transition: all .2s;
    }
    .vehicle-card:hover { border-color: var(--accent); }
    /* active card gets a teal border and darker background */
    .vehicle-card.active { border-color: var(--accent); background: #0d2b2a; }
    .vehicle-icon { font-size: 2.5rem; margin-bottom: .5rem; }
    .vehicle-card h3 { font-size: .95rem; font-weight: 600; margin-bottom: .2rem; }
    .vehicle-card p { font-size: .78rem; color: #6a6f7e; }

    /* Car photo preview area */
    .preview-area {
      background: #131824; border-radius: var(--radius); padding: 2rem;
      margin-bottom: 2rem; text-align: center; border: 1.5px solid #2a2f3f; position: relative; overflow: hidden;
    }
    .car-svg { width: 100%; max-width: 520px; transition: all .3s; }
    .car-svg path, .car-svg rect, .car-svg ellipse { transition: fill .3s; }

    /* Color swatch grid - 6 per row */
    .swatch-row { display: grid; grid-template-columns: repeat(6,1fr); gap: .75rem; margin-bottom: 2rem; }
    .swatch-btn {
      border-radius: 10px; padding: .75rem .5rem; text-align: center; cursor: pointer;
      border: 2px solid transparent; transition: all .2s; background: #131824;
    }
    .swatch-btn:hover { border-color: var(--accent); }
    /* active swatch gets a blue border and glow */
    .swatch-btn.active { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(78,205,196,.3); }
    /* circular colour dot inside each swatch */
    .swatch-dot { width: 36px; height: 36px; border-radius: 50%; margin: 0 auto .4rem; border: 2px solid rgba(255,255,255,.15); }
    .swatch-btn span { font-size: .78rem; color: #b5bccf; font-weight: 600; }

    /* Quote panel at the bottom */
    .quote-panel {
      background: var(--panel); border-radius: var(--radius); padding: 1.2rem 1.5rem;
      color: var(--text); display: flex; justify-content: space-between; align-items: center;
      gap: 1rem; box-shadow: var(--shadow); flex-wrap: wrap;
    }
    .quote-panel h3 { font-family: 'Bebas Neue', sans-serif; font-size: 1.4rem; margin-bottom: .3rem; }
    .quote-meta { display: flex; flex-wrap: wrap; gap: 1.2rem; color: #555; font-size: .9rem; align-items: center; }
    select { border: 1.5px solid #c9ceda; border-radius: 8px; padding: .4rem .6rem; font-family: inherit; font-size: .9rem; }
    /* price estimate box in red/orange */
    .estimate {
      background: #f6e5dd; border: 2px solid var(--danger); color: #8a2f1e;
      border-radius: 12px; padding: .8rem 1.2rem; min-width: 140px;
      text-align: center; font-weight: 700; font-size: 2rem; white-space: nowrap;
    }
    .book-quote-btn {
      background: var(--accent); color: #fff; border: none; border-radius: 8px;
      padding: .65rem 1.4rem; font-size: .9rem; font-weight: 700; cursor: pointer;
      font-family: inherit; transition: background .2s; margin-top: .5rem;
    }
    .book-quote-btn:hover { background: #2d86c9; }
    /* stack grid on smaller screens */
    @media(max-width:800px){ .vehicle-grid{grid-template-columns:repeat(2,1fr)} .swatch-row{grid-template-columns:repeat(3,1fr)} }
    @media(max-width:500px){ .swatch-row{grid-template-columns:repeat(2,1fr)} .estimate{font-size:1.4rem} }
  </style>
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
    <div class="vehicle-card active" data-vehicle="sedan" onclick="selectVehicle(this,'sedan')">
      <div class="vehicle-icon"></div><h3>Sedan</h3>
    </div>
    <div class="vehicle-card" data-vehicle="suv" onclick="selectVehicle(this,'suv')">
      <div class="vehicle-icon"></div><h3>SUV / Crossover</h3>
    </div>
    <div class="vehicle-card" data-vehicle="truck" onclick="selectVehicle(this,'truck')">
      <div class="vehicle-icon"></div><h3>Truck</h3>
    </div>
    <div class="vehicle-card" data-vehicle="coupe" onclick="selectVehicle(this,'coupe')">
      <div class="vehicle-icon"></div><h3>Coupe / Sports</h3>
    </div>
  </div>

  <h2 class="section-title">Step 2 — Choose Your Wrap Color</h2>
  <!-- colour swatches -->
  <div class="swatch-row" id="swatchRow">
    <div class="swatch-btn active" data-color="black" onclick="selectColor(this,'black')">
      <div class="swatch-dot" style="background:#1a1a1a"></div><span>Gloss Black</span>
    </div>
    <div class="swatch-btn" data-color="white" onclick="selectColor(this,'white')">
      <div class="swatch-dot" style="background:#f0f0f0;border-color:#ccc"></div><span>Gloss White</span>
    </div>
    <div class="swatch-btn" data-color="red" onclick="selectColor(this,'red')">
      <div class="swatch-dot" style="background:#c0392b"></div><span>Gloss Red</span>
    </div>
    <div class="swatch-btn" data-color="blue" onclick="selectColor(this,'blue')">
      <div class="swatch-dot" style="background:#2980b9"></div><span>Gloss Blue</span>
    </div>
    <div class="swatch-btn" data-color="gold" onclick="selectColor(this,'gold')">
      <div class="swatch-dot" style="background:#d4a017"></div><span>Satin Gold</span>
    </div>
    <div class="swatch-btn" data-color="green" onclick="selectColor(this,'green')">
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
          <select id="coverageSelect" onchange="updateQuote()">
            <option value="full">Full Wrap</option>
            <option value="partial">Partial Wrap (70%)</option>
            <option value="roof">Roof Only (35%)</option>
          </select>
        </span>
        <span>Vehicle: <strong id="qVehicle">Sedan</strong></span>
        <span>Color: <strong id="qColor">Gloss Black</strong></span>
      </div>
      <button class="book-quote-btn" onclick="location.href='schedule.php'">Book This Wrap →</button>
    </div>
    <!-- live price estimate updated on every selection change -->
    <div class="estimate" id="qEstimate">$2,200</div>
  </div>
</div>

<script>
// base prices per colour in CAD
const BASE_PRICES  = { black:2200, white:2300, red:2450, blue:2400, gold:2700, green:2350 };
// multipliers per vehicle type
const V_MULT       = { sedan:1.0, suv:1.12, truck:1.2, coupe:0.95 };
// multipliers per coverage option
const COV_MULT     = { full:1, partial:0.7, roof:0.35 };

// Map vehicle+color to actual image file
const CAR_IMAGES = {
  'sedan-black':  'images/car-colors/black sedan.webp',
  'sedan-white':  'images/car-colors/white sedan.webp',
  'sedan-red':    'images/car-colors/red sedan.jpg',
  'sedan-blue':   'images/car-colors/blue sedan.avif',
  'sedan-gold':   'images/car-colors/gold sedan.jpg',
  'sedan-green':  'images/car-colors/green sedan.jpg',
  'suv-black':    'images/car-colors/black suv.webp',
  'suv-white':    'images/car-colors/white suv.webp',
  'suv-red':      'images/car-colors/red suv.webp',
  'suv-blue':     'images/car-colors/blue suv.webp',
  'suv-gold':     'images/car-colors/gold suv.webp',
  'suv-green':    'images/car-colors/green suv.webp',
  'truck-black':  'images/car-colors/black truck.jpg',
  'truck-white':  'images/car-colors/white truck.avif',
  'truck-red':    'images/car-colors/red truck.jpg',
  'truck-blue':   'images/car-colors/blue truck.avif',
  'truck-gold':   'images/car-colors/gold truck.jpg',
  'truck-green':  'images/car-colors/green truck.jpg',
  'coupe-black':  'images/car-colors/black coupe.webp',
  'coupe-white':  'images/car-colors/white coupe.jpg',
  'coupe-red':    'images/car-colors/red coupe.jpg',
  'coupe-blue':   'images/car-colors/blue coupe.webp',
  'coupe-gold':   'images/car-colors/gold coupe.jpg',
  'coupe-green':  'images/car-colors/green coupe.jpg',
};

// display labels for colours and vehicle types
const COLOR_LABELS = { black:'Gloss Black', white:'Gloss White', red:'Gloss Red', blue:'Gloss Blue', gold:'Satin Gold', green:'Matte Green' };
const V_LABELS     = { sedan:'Sedan', suv:'SUV / Crossover', truck:'Truck', coupe:'Coupe / Sports' };

// track currently selected vehicle and colour
let selVehicle = 'sedan', selColor = 'black';

// fade out the current image, swap the src, then fade back in
function updateCarImage() {
  const img = document.getElementById('carImg');
  const key = selVehicle + '-' + selColor;
  // Fade out, swap src, fade in
  img.style.opacity = '0';
  setTimeout(() => {
    img.src = CAR_IMAGES[key] || '';
    img.style.opacity = '1';
  }, 200);
}

// called when a vehicle card is clicked
function selectVehicle(el, v) {
  document.querySelectorAll('.vehicle-card').forEach(c => c.classList.remove('active'));
  el.classList.add('active'); selVehicle = v;
  document.getElementById('qVehicle').textContent = V_LABELS[v];
  updateCarImage(); updateQuote();
}
// called when a colour swatch is clicked
function selectColor(el, c) {
  document.querySelectorAll('.swatch-btn').forEach(b => b.classList.remove('active'));
  el.classList.add('active'); selColor = c;
  document.getElementById('qColor').textContent = COLOR_LABELS[c];
  updateCarImage(); updateQuote();
}
// recalculate the price based on colour, vehicle and coverage selection
function updateQuote() {
  const cov = document.getElementById('coverageSelect').value;
  const price = Math.round(BASE_PRICES[selColor] * V_MULT[selVehicle] * COV_MULT[cov]);
  // format as Canadian dollars
  document.getElementById('qEstimate').textContent = new Intl.NumberFormat('en-CA',{style:'currency',currency:'CAD',maximumFractionDigits:0}).format(price);
}
// calculate the initial price on page load
updateQuote();
</script>
</body>
</html>