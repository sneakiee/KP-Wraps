/**
 * Name: Real MaJiK (Team 47)
 * Created: March 23, 2026
 * Description: Implements login form validation, submission, and redirect flow.
 */

// handle the login form submission
/**
 * Submits the login form, shows the server response, and redirects on success.
 * @returns {Promise<void>}
 */
async function doLogin() {
  const email    = document.getElementById('email').value.trim();
  const password = document.getElementById('password').value;
  const msg      = document.getElementById('msg');
  msg.className  = 'msg';
  // make sure both fields are filled in
  if (!email || !password) { msg.textContent = 'Please enter your email and password.'; return; }
  const fd = new FormData();
  fd.append('email', email);
  fd.append('password', password);
  try {
    const res  = await fetch('login.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
      // show welcome message then redirect to the page login.php sends back
      msg.className = 'msg ok';
      msg.textContent = 'Welcome back! Redirecting…';
      setTimeout(() => location.href = data.redirect, 800);
    } else {
      msg.textContent = data.error || 'Login failed.';
    }
  } catch { msg.textContent = 'Network error. Please try again.'; }
}
// allow pressing enter to submit the login form
document.addEventListener('keydown', e => { if (e.key === 'Enter') doLogin(); });

document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('loginBtn').addEventListener('click', doLogin);
  document.getElementById('goRegisterBtn').addEventListener('click', () => {
    location.href = 'register.html';
  });
});
