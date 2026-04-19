<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
    header('Location: index.html'); exit;
}
require_once __DIR__ . '/db_reviews.php';

$current_user_id   = $_SESSION['user_id']   ?? null;
$current_user_name = $_SESSION['first_name'] ?? 'You';

$per_page = 5;
$page     = max(1, (int)($_GET['page'] ?? 1));

$db = get_db();

$count_result = $db->query('SELECT COUNT(*) as cnt FROM reviews WHERE is_approved = 1');
$total_rows   = $count_result ? (int)$count_result->fetch_assoc()['cnt'] : 0;
$total_pages  = max(1, (int)ceil($total_rows / $per_page));
$page         = min($page, $total_pages);
$offset       = ($page - 1) * $per_page;

$avg_result = $db->query('SELECT AVG(rating) as avg_r, COUNT(*) as cnt FROM reviews WHERE is_approved = 1');
$avg_data   = $avg_result ? $avg_result->fetch_assoc() : ['avg_r' => 0, 'cnt' => 0];
$avg_rating = round((float)($avg_data['avg_r'] ?? 0), 1);
$total_cnt  = (int)($avg_data['cnt'] ?? 0);

$reviews_result = $db->query(
    "SELECT r.review_id, r.rating, r.title, r.body, r.created_at,
            u.first_name, u.last_name
     FROM reviews r
     JOIN users u ON r.user_id = u.user_id
     WHERE r.is_approved = 1
     ORDER BY r.created_at DESC
     LIMIT $per_page OFFSET $offset"
);
$reviews = $reviews_result ? $reviews_result->fetch_all(MYSQLI_ASSOC) : [];

function stars(int $n): string {
    return str_repeat('★', $n) . str_repeat('☆', 5 - $n);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KP Wraps — Reviews</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root { --accent:#49a2e6; --danger:#d75a44; --radius:14px; --shadow:0 14px 30px rgba(0,0,0,.28); }
    * { box-sizing:border-box; margin:0; padding:0; }
    body { min-height:100vh; font-family:'DM Sans',sans-serif;
      background:radial-gradient(circle at 15% 15%,rgba(106,182,255,.15),transparent 35%),
                 radial-gradient(circle at 85% 10%,rgba(78,205,196,.2),transparent 32%),
                 linear-gradient(180deg,#05070d 0%,#091221 100%); color:#f6f8ff; }
    .shell { width:min(900px,100%); margin:0 auto; padding:0 1rem 3rem; }
    .hero { text-align:center; padding:2rem 0 1.5rem; }
    .hero h1 { font-family:'Bebas Neue',sans-serif; font-size:clamp(1.6rem,4vw,2.2rem); }
    .hero p { color:#b5bccf; margin-top:.4rem; }

    .avg-badge { display:inline-flex; align-items:center; gap:.6rem; background:#131824; border:1.5px solid #2a2f3f; border-radius:40px; padding:.5rem 1.2rem; margin:0 auto 1.5rem; }
    .avg-badge .stars { color:#f5c542; font-size:1.2rem; }
    .avg-badge .num { font-size:1.4rem; font-weight:700; }
    .avg-badge .cnt { color:#6a6f7e; font-size:.85rem; }
    .avg-wrap { text-align:center; }

    .review-card { background:#ffffff; border-radius:var(--radius); padding:1.3rem 1.5rem; color:#1a1f2e; margin-bottom:1rem; box-shadow:var(--shadow); }
    .rc-header { display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:.5rem; margin-bottom:.6rem; }
    .rc-stars { color:#f5c542; font-size:1.1rem; }
    .rc-date { font-size:.78rem; color:#888; }
    .rc-title { font-weight:700; font-size:1rem; margin-bottom:.4rem; }
    .rc-body { font-size:.9rem; color:#333; line-height:1.6; margin-bottom:.6rem; }
    .rc-author { font-size:.8rem; color:#666; font-style:italic; }

    .pagination { display:flex; gap:.5rem; justify-content:center; margin-top:1.5rem; flex-wrap:wrap; }
    .pagination a { background:#131824; border:1.5px solid #2a2f3f; color:#aaa; padding:.4rem .9rem; border-radius:8px; text-decoration:none; font-size:.85rem; transition:all .2s; }
    .pagination a:hover,.pagination a.active { border-color:var(--accent); color:var(--accent); }

    /* Submit form */
    .submit-section { background:#ffffff; border-radius:var(--radius); padding:1.5rem; color:#1a1f2e; margin-top:2rem; box-shadow:var(--shadow); }
    .submit-section h2 { font-family:'Bebas Neue',sans-serif; font-size:1.4rem; margin-bottom:1rem; }
    .star-picker { display:flex; gap:.3rem; margin-bottom:.8rem; }
    .star-picker span { font-size:2rem; cursor:pointer; color:#ccc; transition:color .1s; }
    .star-picker span.selected,.star-picker span.hovered { color:#f5c542; }
    label.field-label { font-size:.82rem; font-weight:600; color:#444; display:block; margin-bottom:.3rem; }
    input[type=text], textarea {
      width:100%; padding:.65rem .9rem; border:1.5px solid #d0cdc8; border-radius:8px;
      font-size:.9rem; font-family:inherit; margin-bottom:.85rem; background:#fff; color:#1a1f2e;
    }
    input[type=text]:focus, textarea:focus { outline:none; border-color:var(--accent); }
    textarea { resize:vertical; min-height:90px; }
    .submit-btn { background:var(--accent); color:#fff; border:none; border-radius:8px; padding:.7rem 1.6rem; font-size:.95rem; font-weight:700; cursor:pointer; font-family:inherit; transition:background .2s; }
    .submit-btn:hover { background:#2d86c9; }
    .submit-btn:disabled { background:#aaa; cursor:not-allowed; }
    .alert { margin-top:.75rem; padding:.6rem .9rem; border-radius:8px; font-size:.88rem; font-weight:600; display:none; }
    .alert.show { display:block; }
    .alert.ok { background:#e8f8f0; color:#27ae60; }
    .alert.err { background:#fdecea; color:#c0392b; }

    .no-reviews { color:#6a6f7e; text-align:center; padding:2rem; }
  </style>
</head>
<body>
<?php include 'nav.php'; ?>
<div class="shell">
  <div class="hero">
    <h1>Customer Reviews</h1>
    <p>See what our clients say — and share your own experience.</p>
  </div>

  <?php if ($total_cnt > 0): ?>
  <div class="avg-wrap">
    <div class="avg-badge">
      <span class="stars"><?= str_repeat('★', round($avg_rating)) ?></span>
      <span class="num"><?= $avg_rating ?></span>
      <span class="cnt"><?= $total_cnt ?> review<?= $total_cnt !== 1 ? 's' : '' ?></span>
    </div>
  </div>
  <?php endif; ?>

  <?php if (empty($reviews)): ?>
    <p class="no-reviews">No approved reviews yet. Be the first!</p>
  <?php else: ?>
    <?php foreach ($reviews as $r): ?>
    <div class="review-card">
      <div class="rc-header">
        <span class="rc-stars"><?= stars((int)$r['rating']) ?></span>
        <span class="rc-date"><?= htmlspecialchars(date('M j, Y', strtotime($r['created_at']))) ?></span>
      </div>
      <div class="rc-title"><?= htmlspecialchars($r['title']) ?></div>
      <div class="rc-body"><?= nl2br(htmlspecialchars($r['body'])) ?></div>
      <div class="rc-author">— <?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name'][0] . '.') ?></div>
    </div>
    <?php endforeach; ?>

    <?php if ($total_pages > 1): ?>
    <div class="pagination">
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  <?php endif; ?>

  <!-- Submit a review -->
  <div class="submit-section">
    <h2>Leave a Review</h2>
    <div class="star-picker" id="starPicker">
      <?php for ($i = 1; $i <= 5; $i++): ?>
        <span data-val="<?= $i ?>" onclick="setRating(<?= $i ?>)"
              onmouseenter="hoverRating(<?= $i ?>)" onmouseleave="unhoverRating()">★</span>
      <?php endfor; ?>
    </div>
    <input type="hidden" id="ratingVal" value="0">
    <label class="field-label">Review Title</label>
    <input type="text" id="revTitle" placeholder="e.g. Amazing work!">
    <label class="field-label">Your Review</label>
    <textarea id="revBody" placeholder="Tell us about your experience…"></textarea>
    <button class="submit-btn" id="submitBtn" onclick="submitReview()">Submit Review</button>
    <div class="alert ok" id="alertOk"></div>
    <div class="alert err" id="alertErr"></div>
  </div>
</div>

<script>
let rating = 0;
function setRating(n) { rating = n; updateStars(); }
function hoverRating(n) { document.querySelectorAll('#starPicker span').forEach((s,i) => { s.classList.toggle('hovered', i < n); }); }
function unhoverRating() { document.querySelectorAll('#starPicker span').forEach(s => s.classList.remove('hovered')); }
function updateStars() { document.querySelectorAll('#starPicker span').forEach((s,i) => { s.classList.toggle('selected', i < rating); }); document.getElementById('ratingVal').value = rating; }

async function submitReview() {
  const title = document.getElementById('revTitle').value.trim();
  const body  = document.getElementById('revBody').value.trim();
  const okEl  = document.getElementById('alertOk');
  const errEl = document.getElementById('alertErr');
  [okEl, errEl].forEach(a => { a.classList.remove('show'); a.textContent=''; });
  if (rating === 0) { errEl.textContent='Please select a star rating.'; errEl.classList.add('show'); return; }
  if (!title)       { errEl.textContent='Please enter a title.'; errEl.classList.add('show'); return; }
  if (!body)        { errEl.textContent='Please write your review.'; errEl.classList.add('show'); return; }
  const btn = document.getElementById('submitBtn');
  btn.disabled = true; btn.textContent = 'Submitting…';
  const fd = new FormData();
  fd.append('rating', rating); fd.append('title', title); fd.append('body', body);
  try {
    const res  = await fetch('review_api.php', { method:'POST', body:fd });
    const data = await res.json();
    if (data.success) {
      okEl.textContent = 'Thank you! Your review has been submitted for approval.';
      okEl.classList.add('show');
      document.getElementById('revTitle').value = '';
      document.getElementById('revBody').value = '';
      rating = 0; updateStars();
    } else { errEl.textContent = (data.error || 'Submission failed.'); errEl.classList.add('show'); }
  } catch { errEl.textContent = 'Network error.'; errEl.classList.add('show'); }
  finally { btn.disabled=false; btn.textContent='Submit Review'; }
}
</script>
</body>
</html>
