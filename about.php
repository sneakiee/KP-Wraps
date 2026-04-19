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
  <title>KP Wraps — About</title>
  <!-- google fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * { box-sizing:border-box; margin:0; padding:0; }
    /* dark gradient background */
    body { min-height:100vh; font-family:'DM Sans',sans-serif;
      background:radial-gradient(circle at 15% 15%,rgba(106,182,255,.15),transparent 35%),
                 radial-gradient(circle at 85% 10%,rgba(78,205,196,.2),transparent 32%),
                 linear-gradient(180deg,#05070d 0%,#091221 100%); color:#f6f8ff; }
    /* centers and limits the page width */
    .shell { width:min(1000px,100%); margin:0 auto; padding:0 1rem 3rem; }
    .hero { text-align:center; padding:3rem 0 2rem; }
    .hero h1 { font-family:'Bebas Neue',sans-serif; font-size:clamp(2rem,6vw,3.5rem); letter-spacing:.05em; }
    .hero p { color:#b5bccf; font-size:1.05rem; max-width:600px; margin:.8rem auto 0; line-height:1.7; }
    .section-title { font-family:'Bebas Neue',sans-serif; font-size:1.6rem; letter-spacing:.05em; margin-bottom:1.2rem; }

    /* Before/after slider */
    .ba-section { margin-bottom: 2.5rem; }
    /* draggable wrapper that clips the before image */
    .ba-wrapper {
      position: relative; width: 100%; max-width: 800px; margin: 0 auto;
      border-radius: 14px; overflow: hidden; cursor: ew-resize;
      box-shadow: 0 14px 30px rgba(0,0,0,.4); user-select: none;
    }
    .ba-img { display: block; width: 100%; height: 420px; object-fit: cover; }
    /* before image is clipped to the left half by default */
    .ba-before { position: absolute; top: 0; left: 0; width: 100%; height: 100%; clip-path: inset(0 50% 0 0); }
    /* white divider line between before and after */
    .ba-divider { position: absolute; top: 0; left: 50%; width: 2px; height: 100%; background: #fff; pointer-events: none; }
    /* blue drag handle circle */
    .ba-handle {
      position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%);
      width: 44px; height: 44px; background: #49a2e6; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.2rem; color: #fff; font-weight: 700; box-shadow: 0 2px 12px rgba(0,0,0,.4);
      pointer-events: none;
    }
    /* before and after labels */
    .ba-label {
      position: absolute; top: 14px; padding: .3rem .8rem; background: rgba(0,0,0,.55);
      color: #fff; border-radius: 20px; font-size: .8rem; font-weight: 700; pointer-events: none;
    }
    .ba-label-before { left: 14px; }
    .ba-label-after  { right: 14px; }
    /* shrink image height on small screens */
    @media(max-width:600px){ .ba-img,.ba-before{ height:240px; } }

    /* Mission section - white card */
    .mission { background:#ffffff; border-radius:14px; padding:2rem; color:#1a1f2e; margin-bottom:2.5rem; box-shadow:0 14px 30px rgba(0,0,0,.28); }
    .mission p { font-size:.95rem; line-height:1.8; color:#333; }
    .mission p + p { margin-top:.8rem; }

    /* Services grid - auto fills columns */
    .services-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:1.2rem; margin-bottom:2.5rem; }
    .service-card { background:#131824; border:1.5px solid #2a2f3f; border-radius:14px; padding:1.3rem 1.2rem; transition:border-color .2s; }
    .service-card:hover { border-color:#49a2e6; }
    .service-icon { font-size:2rem; margin-bottom:.6rem; }
    .service-card h3 { font-size:1rem; font-weight:700; margin-bottom:.3rem; color:#f6f8ff; }
    /* price shown in blue */
    .service-card .price { color:#49a2e6; font-weight:700; font-size:.9rem; margin-bottom:.5rem; }
    .service-card p { font-size:.83rem; color:#8a90a0; line-height:1.6; }

    /* CTA buttons at the bottom */
    .cta { text-align:center; padding:2rem 0; }
    .cta-btn { background:#49a2e6; color:#fff; border:none; border-radius:8px; padding:.85rem 2rem; font-size:1rem; font-weight:700; cursor:pointer; font-family:inherit; text-decoration:none; display:inline-block; transition:background .2s; margin:.4rem; }
    .cta-btn:hover { background:#2d86c9; }
    /* outline variant for the secondary button */
    .cta-btn.outline { background:transparent; border:2px solid #49a2e6; color:#49a2e6; }
    .cta-btn.outline:hover { background:#49a2e6; color:#fff; }
  </style>
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
<script>
// Before/after drag slider
(function() {
  const wrapper = document.getElementById('baWrapper');
  const before  = wrapper.querySelector('.ba-before');
  const divider = document.getElementById('baDivider');
  const handle  = document.getElementById('baHandle');
  let dragging  = false;

  // update the clip position based on where the user dragged to
  function setPos(x) {
    const rect = wrapper.getBoundingClientRect();
    // clamp the percentage between 0 and 1
    let pct = Math.max(0, Math.min(1, (x - rect.left) / rect.width));
    const p = (pct * 100).toFixed(1) + '%';
    // clip the before image and move the divider and handle
    before.style.clipPath  = `inset(0 ${(100 - pct*100).toFixed(1)}% 0 0)`;
    divider.style.left     = p;
    handle.style.left      = p;
  }

  // mouse drag events
  wrapper.addEventListener('mousedown',  e => { dragging = true; setPos(e.clientX); });
  window.addEventListener('mousemove',   e => { if (dragging) setPos(e.clientX); });
  window.addEventListener('mouseup',     ()  => { dragging = false; });
  // touch drag events for mobile
  wrapper.addEventListener('touchstart', e => { dragging = true; setPos(e.touches[0].clientX); }, {passive:true});
  window.addEventListener('touchmove',   e => { if (dragging) setPos(e.touches[0].clientX); }, {passive:true});
  window.addEventListener('touchend',    ()  => { dragging = false; });
})();
</script>
</body>
</html>