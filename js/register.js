/**
 * Name: Real MaJiK (Team 47)
 * Created: March 23, 2026
 * Description: Implements registration form validation and account creation submission flow.
 */

// handle the registration form submission
/**
 * Validates the registration form and creates a new account through the backend.
 * @returns {Promise<void>}
 */
async function doRegister() {
  const first    = document.getElementById('first_name').value.trim();
  const last     = document.getElementById('last_name').value.trim();
  const email    = document.getElementById('email').value.trim();
  const phone    = document.getElementById('phone').value.trim();
  const password = document.getElementById('password').value;
  const msg      = document.getElementById('msg');
  msg.className  = 'msg';
  // validate all required fields are filled in
  if (!first || !last || !email || !password) { msg.textContent = 'Please fill in all required fields.'; return; }
  // validate email format
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { msg.textContent = 'Please enter a valid email.'; return; }
  // password must be 8+ chars and include a number
  if (password.length < 8 || !/\d/.test(password)) { msg.textContent = 'Password must be 8+ chars and include a number.'; return; }
  const fd = new FormData();
  fd.append('first_name', first); fd.append('last_name', last);
  fd.append('email', email); fd.append('phone', phone); fd.append('password', password);
  try {
    const res  = await fetch('register.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
      // show success message then redirect to login after a short delay
      msg.className = 'msg ok';
      msg.textContent = 'Account created! Redirecting to login…';
      setTimeout(() => location.href = 'index.html', 1200);
    } else {
      msg.textContent = data.error || 'Registration failed.';
    }
  } catch { msg.textContent = 'Network error. Please try again.'; }
}
// allow pressing enter to submit the form
document.addEventListener('keydown', e => { if (e.key === 'Enter') doRegister(); });

document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('registerBtn').addEventListener('click', doRegister);
  document.getElementById('backToLoginBtn').addEventListener('click', () => {
    location.href = 'index.html';
  });
});
