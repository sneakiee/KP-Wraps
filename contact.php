<?php
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
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * { box-sizing:border-box; margin:0; padding:0; }
    body { min-height:100vh; font-family:'DM Sans',sans-serif;
      background:radial-gradient(circle at 15% 15%,rgba(106,182,255,.15),transparent 35%),
                 radial-gradient(circle at 85% 10%,rgba(78,205,196,.2),transparent 32%),
                 linear-gradient(180deg,#05070d 0%,#091221 100%); color:#f6f8ff; }
    .shell { width:min(700px,100%); margin:0 auto; padding:0 1rem 3rem; }
    .hero { text-align:center; padding:2.5rem 0 2rem; }
    .hero h1 { font-family:'Bebas Neue',sans-serif; font-size:clamp(1.8rem,5vw,2.8rem); }
    .hero p { color:#b5bccf; margin-top:.5rem; }
    .card { background:#ffffff; border-radius:14px; padding:2rem; color:#1a1f2e; box-shadow:0 14px 30px rgba(0,0,0,.28); }
    label { display:block; font-size:.82rem; font-weight:600; color:#444; margin-bottom:.3rem; }
    input, textarea, select {
      width:100%; padding:.65rem .9rem; border:1.5px solid #d0cdc8; border-radius:8px;
      font-size:.9rem; font-family:inherit; margin-bottom:.85rem; background:#fff; color:#1a1f2e;
    }
    input:focus, textarea:focus { outline:none; border-color:#49a2e6; }
    textarea { resize:vertical; min-height:120px; }
    .row2 { display:grid; grid-template-columns:1fr 1fr; gap:.75rem; }
    .submit-btn { background:#49a2e6; color:#fff; border:none; border-radius:8px; padding:.75rem 1.8rem; font-size:.95rem; font-weight:700; cursor:pointer; font-family:inherit; transition:background .2s; }
    .submit-btn:hover { background:#2d86c9; }
    .submit-btn:disabled { background:#aaa; cursor:not-allowed; }
    .alert { margin-top:.8rem; padding:.65rem .9rem; border-radius:8px; font-size:.88rem; font-weight:600; display:none; }
    .alert.show { display:block; }
    .alert.ok { background:#e8f8f0; color:#27ae60; }
    .alert.err { background:#fdecea; color:#c0392b; }
    @media(max-width:480px){ .row2{grid-template-columns:1fr} }
  </style>
</head>
<body>
<?php include 'nav.php'; ?>
<div class="shell">
  <div class="hero">
    <h1>Get in Touch</h1>
    <p>Questions, quotes, or just want to chat about your build? We're here.</p>
  </div>
  <div class="card">
    <div class="row2">
      <div><label>Your Name</label><input type="text" id="cName" placeholder="Jane Doe"></div>
      <div><label>Email</label><input type="email" id="cEmail" placeholder="you@example.com"></div>
    </div>
    <label>Subject</label>
    <input type="text" id="cSubject" placeholder="e.g. Quote for a full wrap on my truck">
    <label>Message</label>
    <textarea id="cBody" placeholder="Tell us about your vehicle and what you're looking for…"></textarea>
    <button class="submit-btn" id="sendBtn" onclick="sendMsg()">Send Message</button>
    <div class="alert ok" id="alertOk"></div>
    <div class="alert err" id="alertErr"></div>
  </div>
</div>
<script>
async function sendMsg() {
  const name    = document.getElementById('cName').value.trim();
  const email   = document.getElementById('cEmail').value.trim();
  const subject = document.getElementById('cSubject').value.trim();
  const body    = document.getElementById('cBody').value.trim();
  const okEl    = document.getElementById('alertOk');
  const errEl   = document.getElementById('alertErr');
  [okEl,errEl].forEach(a=>{a.classList.remove('show');a.textContent='';});
  if (!name||!email||!subject||!body){errEl.textContent='Please fill in all fields.';errEl.classList.add('show');return;}
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){errEl.textContent='Please enter a valid email.';errEl.classList.add('show');return;}
  const btn=document.getElementById('sendBtn'); btn.disabled=true; btn.textContent='Sending…';
  const fd=new FormData(); fd.append('name',name);fd.append('email',email);fd.append('subject',subject);fd.append('body',body);
  try{
    const res=await fetch('send_message.php',{method:'POST',body:fd});
    const data=await res.json();
    if(data.success){okEl.textContent="Message sent! We'll get back to you within 24 hours.";okEl.classList.add('show');document.getElementById('cName').value='';document.getElementById('cEmail').value='';document.getElementById('cSubject').value='';document.getElementById('cBody').value='';}
    else{errEl.textContent=(data.error||'Could not send message.');errEl.classList.add('show');}
  }catch{errEl.textContent='Network error — please try again.';errEl.classList.add('show');}
  finally{btn.disabled=false;btn.textContent='Send Message';}
}
</script>
</body>
</html>
