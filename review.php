<?php
/**
 * Name: Real MaJiK (Team 47)
 * Created: March 23, 2026
 * Description: Displays approved reviews and provides the form to submit new client reviews.
 */
// start session and redirect if not logged in as a client
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
    header('Location: index.html'); exit;
}
require_once __DIR__ . '/db_reviews.php';

// grab the current user's id and name from the session
$current_user_id   = $_SESSION['user_id']   ?? null;
$current_user_name = $_SESSION['first_name'] ?? 'You';

// set how many reviews to show per page
$per_page = 5;
// get the current page from the url, default to 1
$page     = max(1, (int)($_GET['page'] ?? 1));

$db = get_db();

// count how many approved reviews there are total
$count_result = $db->query('SELECT COUNT(*) as cnt FROM reviews WHERE is_approved = 1');
$total_rows   = $count_result ? (int)$count_result->fetch_assoc()['cnt'] : 0;
// figure out how many pages we need
$total_pages  = max(1, (int)ceil($total_rows / $per_page));
// make sure we don't go past the last page
$page         = min($page, $total_pages);
// calculate where to start grabbing reviews from
$offset       = ($page - 1) * $per_page;

// get the average rating and total count of approved reviews
$avg_result = $db->query('SELECT AVG(rating) as avg_r, COUNT(*) as cnt FROM reviews WHERE is_approved = 1');
$avg_data   = $avg_result ? $avg_result->fetch_assoc() : ['avg_r' => 0, 'cnt' => 0];
// round the average to 1 decimal place
$avg_rating = round((float)($avg_data['avg_r'] ?? 0), 1);
$total_cnt  = (int)($avg_data['cnt'] ?? 0);

// grab the reviews for the current page joined with user info
$reviews_result = $db->query(
    "SELECT r.review_id, r.rating, r.title, r.body, r.created_at,
            u.first_name, u.last_name
     FROM reviews r
     JOIN users u ON r.user_id = u.user_id
     WHERE r.is_approved = 1
     ORDER BY r.created_at DESC
     LIMIT $per_page OFFSET $offset"
);
// store as an array, empty array if nothing comes back
$reviews = $reviews_result ? $reviews_result->fetch_all(MYSQLI_ASSOC) : [];

// helper function to turn a number into filled and empty stars
/**
 * Builds a 5-star display string for a numeric review rating.
 * @param int $n Rating value from 0 to 5.
 * @return string Rendered star string.
 */
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
  <!-- google fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/review.css">
</head>
<body>
<?php include 'nav.php'; // include the navbar ?>
<div class="shell">
  <div class="hero">
    <h1>Customer Reviews</h1>
    <p>See what our clients say — and share your own experience.</p>
  </div>

  <!-- only show the average badge if there are approved reviews -->
  <?php if ($total_cnt > 0): ?>
  <div class="avg-wrap">
    <div class="avg-badge">
      <span class="stars"><?= str_repeat('★', round($avg_rating)) ?></span>
      <span class="num"><?= $avg_rating ?></span>
      <span class="cnt"><?= $total_cnt ?> review<?= $total_cnt !== 1 ? 's' : '' ?></span>
    </div>
  </div>
  <?php endif; ?>

  <!-- show message if no reviews, otherwise loop and display them -->
  <?php if (empty($reviews)): ?>
    <p class="no-reviews">No approved reviews yet. Be the first!</p>
  <?php else: ?>
    <?php foreach ($reviews as $r): ?>
    <div class="review-card">
      <div class="rc-header">
        <!-- stars and date at the top of each card -->
        <span class="rc-stars"><?= stars((int)$r['rating']) ?></span>
        <span class="rc-date"><?= htmlspecialchars(date('M j, Y', strtotime($r['created_at']))) ?></span>
      </div>
      <div class="rc-title"><?= htmlspecialchars($r['title']) ?></div>
      <div class="rc-body"><?= nl2br(htmlspecialchars($r['body'])) ?></div>
      <!-- only show first letter of last name for privacy -->
      <div class="rc-author">— <?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name'][0] . '.') ?></div>
    </div>
    <?php endforeach; ?>

    <!-- only show pagination if there's more than one page -->
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
    <!-- clickable stars for picking a rating -->
    <div class="star-picker" id="starPicker">
      <?php for ($i = 1; $i <= 5; $i++): ?>
        <span data-val="<?= $i ?>">★</span>
      <?php endfor; ?>
    </div>
    <!-- hidden input stores the selected rating value -->
    <input type="hidden" id="ratingVal" value="0">
    <label class="field-label">Review Title</label>
    <input type="text" id="revTitle" placeholder="e.g. Amazing work!">
    <label class="field-label">Your Review</label>
    <textarea id="revBody" placeholder="Tell us about your experience…"></textarea>
    <button class="submit-btn" id="submitBtn">Submit Review</button>
    <!-- success and error messages shown after submission -->
    <div class="alert ok" id="alertOk"></div>
    <div class="alert err" id="alertErr"></div>
  </div>
</div>

<script src="js/review.js"></script>
</body>
</html>