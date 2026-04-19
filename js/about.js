/**
 * Name: Real MaJiK (Team 47)
 * Created: March 23, 2026
 * Description: Implements interactive before/after slider behavior on the about page.
 */

// Before/after drag slider
(function() {
  const wrapper = document.getElementById('baWrapper');
  const before  = wrapper.querySelector('.ba-before');
  const divider = document.getElementById('baDivider');
  const handle  = document.getElementById('baHandle');
  let dragging  = false;

  // update the clip position based on where the user dragged to
  /**
   * purpose of function
   * @param {param} x
   * @returns return
   */
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
