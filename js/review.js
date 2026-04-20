/**
 * Name: Real MaJiK (Team 47)
 * Created: March 23, 2026
 * Description: Implements review rating selection and asynchronous review submission behavior.
 */

// track the currently selected rating
let rating = 0;
// set rating when a star is clicked
/**
 * Sets the review rating and refreshes the star display.
 * @param {number} n Rating from 1 to 5.
 * @returns {void}
 */
function setRating(n) { rating = n; updateStars(); }
// highlight stars on hover
/**
 * Highlights stars up to the hovered value.
 * @param {number} n Hovered star count.
 * @returns {void}
 */
function hoverRating(n) { document.querySelectorAll('#starPicker span').forEach((s,i) => { s.classList.toggle('hovered', i < n); }); }
// remove hover highlight when mouse leaves
/**
 * Clears the hover state from all review stars.
 * @returns {void}
 */
function unhoverRating() { document.querySelectorAll('#starPicker span').forEach(s => s.classList.remove('hovered')); }
// update which stars look selected based on the current rating
/**
 * Synchronizes the star UI and hidden rating input with the current rating value.
 * @returns {void}
 */
function updateStars() { document.querySelectorAll('#starPicker span').forEach((s,i) => { s.classList.toggle('selected', i < rating); }); document.getElementById('ratingVal').value = rating; }

// handle the review form submission
/**
 * Validates and submits a review to the backend, then resets the form on success.
 * @returns {Promise<void>}
 */
async function submitReview() {
  const title = document.getElementById('revTitle').value.trim();
  const body  = document.getElementById('revBody').value.trim();
  const okEl  = document.getElementById('alertOk');
  const errEl = document.getElementById('alertErr');
  // clear old alert messages
  [okEl, errEl].forEach(a => { a.classList.remove('show'); a.textContent=''; });
  // validate everything is filled in before submitting
  if (rating === 0) { errEl.textContent='Please select a star rating.'; errEl.classList.add('show'); return; }
  if (!title)       { errEl.textContent='Please enter a title.'; errEl.classList.add('show'); return; }
  if (!body)        { errEl.textContent='Please write your review.'; errEl.classList.add('show'); return; }
  // disable the button while waiting for the response
  const btn = document.getElementById('submitBtn');
  btn.disabled = true; btn.textContent = 'Submitting…';
  // build the form data to send to the backend
  const fd = new FormData();
  fd.append('rating', rating); fd.append('title', title); fd.append('body', body);
  try {
    const res  = await fetch('review_api.php', { method:'POST', body:fd });
    const data = await res.json();
    if (data.success) {
      // show success message and reset the form
      okEl.textContent = 'Thank you! Your review has been submitted for approval.';
      okEl.classList.add('show');
      document.getElementById('revTitle').value = '';
      document.getElementById('revBody').value = '';
      rating = 0; updateStars();
    } else { errEl.textContent = (data.error || 'Submission failed.'); errEl.classList.add('show'); }
  } catch { errEl.textContent = 'Network error.'; errEl.classList.add('show'); }
  // re-enable the button no matter what
  finally { btn.disabled=false; btn.textContent='Submit Review'; }
}

/**
 * Binds star hover/click behavior and the submit button on the review page.
 * @returns {void}
 */
function setupReviewListeners() {
  const stars = document.querySelectorAll('#starPicker span[data-val]');
  stars.forEach(star => {
    const val = Number(star.dataset.val);
    star.addEventListener('click', () => setRating(val));
    star.addEventListener('mouseenter', () => hoverRating(val));
  });

  document.getElementById('starPicker').addEventListener('mouseleave', unhoverRating);
  document.getElementById('submitBtn').addEventListener('click', submitReview);
}

document.addEventListener('DOMContentLoaded', setupReviewListeners);
