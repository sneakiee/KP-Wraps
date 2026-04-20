/**
 * Name: Real MaJiK (Team 47)
 * Created: March 23, 2026
 * Description: Implements contact form validation and asynchronous message submission.
 */

// handle the contact form submission
/**
 * Validates and sends the contact form message, then restores the button state.
 * @returns {Promise<void>}
 */
async function sendMsg() {
  const name    = document.getElementById('cName').value.trim();
  const email   = document.getElementById('cEmail').value.trim();
  const subject = document.getElementById('cSubject').value.trim();
  const body    = document.getElementById('cBody').value.trim();
  const okEl    = document.getElementById('alertOk');
  const errEl   = document.getElementById('alertErr');
  // clear any old alert messages
  [okEl,errEl].forEach(a=>{a.classList.remove('show');a.textContent='';});
  // validate all fields are filled in
  if (!name||!email||!subject||!body){errEl.textContent='Please fill in all fields.';errEl.classList.add('show');return;}
  // validate email format
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){errEl.textContent='Please enter a valid email.';errEl.classList.add('show');return;}
  // disable the button while sending
  const btn=document.getElementById('sendBtn'); btn.disabled=true; btn.textContent='Sending…';
  const fd=new FormData(); fd.append('name',name);fd.append('email',email);fd.append('subject',subject);fd.append('body',body);
  try{
    const res=await fetch('send_message.php',{method:'POST',body:fd});
    const data=await res.json();
    if(data.success){
      // show success and clear the form
      okEl.textContent="Message sent! We'll get back to you within 24 hours.";okEl.classList.add('show');
      document.getElementById('cName').value='';document.getElementById('cEmail').value='';
      document.getElementById('cSubject').value='';document.getElementById('cBody').value='';
    }
    else{errEl.textContent=(data.error||'Could not send message.');errEl.classList.add('show');}
  }catch{errEl.textContent='Network error — please try again.';errEl.classList.add('show');}
  // re-enable the button no matter what
  finally{btn.disabled=false;btn.textContent='Send Message';}
}

document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('sendBtn').addEventListener('click', sendMsg);
});
