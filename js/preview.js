/**
 * Name: Real MaJiK (Team 47)
 * Created: March 23, 2026
 * Description: Implements wrap selector state management, image updates, and quote calculations.
 */

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
/**
 * purpose of function
 * @returns return
 */
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
/**
 * purpose of function
 * @param {param} el
 * @param {param} v
 * @returns return
 */
function selectVehicle(el, v) {
  document.querySelectorAll('.vehicle-card').forEach(c => c.classList.remove('active'));
  el.classList.add('active'); selVehicle = v;
  document.getElementById('qVehicle').textContent = V_LABELS[v];
  updateCarImage(); updateQuote();
}
// called when a colour swatch is clicked
/**
 * purpose of function
 * @param {param} el
 * @param {param} c
 * @returns return
 */
function selectColor(el, c) {
  document.querySelectorAll('.swatch-btn').forEach(b => b.classList.remove('active'));
  el.classList.add('active'); selColor = c;
  document.getElementById('qColor').textContent = COLOR_LABELS[c];
  updateCarImage(); updateQuote();
}
// recalculate the price based on colour, vehicle and coverage selection
/**
 * purpose of function
 * @returns return
 */
function updateQuote() {
  const cov = document.getElementById('coverageSelect').value;
  const price = Math.round(BASE_PRICES[selColor] * V_MULT[selVehicle] * COV_MULT[cov]);
  // format as Canadian dollars
  document.getElementById('qEstimate').textContent = new Intl.NumberFormat('en-CA',{style:'currency',currency:'CAD',maximumFractionDigits:0}).format(price);
}

/**
 * purpose of function
 * @returns return
 */
function setupPreviewListeners() {
  document.querySelectorAll('.vehicle-card[data-vehicle]').forEach(card => {
    card.addEventListener('click', () => selectVehicle(card, card.dataset.vehicle));
  });

  document.querySelectorAll('.swatch-btn[data-color]').forEach(btn => {
    btn.addEventListener('click', () => selectColor(btn, btn.dataset.color));
  });

  document.getElementById('coverageSelect').addEventListener('change', updateQuote);
  document.getElementById('bookQuoteBtn').addEventListener('click', () => {
    location.href = 'schedule.php';
  });
}

document.addEventListener('DOMContentLoaded', () => {
  setupPreviewListeners();
  updateQuote();
});
