// ============================================================
// UNICEF — Suivi temps réel du paiement (polling 3s + countdown)
// ============================================================
(function () {
  const ref = window.__REF;
  const timeout = window.__TIMEOUT || 480;
  let elapsed = window.__ELAPSED || 0;

  const cdMin = document.getElementById('cdMinutes');
  const cdSec = document.getElementById('cdSeconds');
  const statusText = document.getElementById('statusText');
  const statusBox = document.getElementById('statusBox');
  const countdown = document.getElementById('countdown');

  function updateCountdown() {
    const remain = Math.max(0, timeout - elapsed);
    const m = Math.floor(remain / 60);
    const s = remain % 60;
    cdMin.textContent = String(m).padStart(2, '0');
    cdSec.textContent = String(s).padStart(2, '0');
    if (remain <= 60) countdown.classList.add('warning');
    if (remain === 0) {
      statusBox.classList.add('failed');
      statusText.textContent = '⏰ Délai expiré. Veuillez réessayer.';
      setTimeout(() => { window.location.href = '/don.php?expired=1'; }, 4000);
      clearInterval(cdInt);
      clearInterval(pollInt);
    }
  }
  const cdInt = setInterval(() => { elapsed++; updateCountdown(); }, 1000);
  updateCountdown();

  // Polling toutes les 3 secondes
  async function check() {
    try {
      const r = await fetch('/api/status.php?ref=' + encodeURIComponent(ref), { cache: 'no-store' });
      const j = await r.json();
      if (!j.ok) return;

      if (j.status === 'validated') {
        statusBox.classList.add('success');
        statusText.textContent = '✓ Paiement validé ! Redirection...';
        clearInterval(cdInt); clearInterval(pollInt);
        setTimeout(() => { window.location.href = '/merci.php?ref=' + encodeURIComponent(ref); }, 1200);
      } else if (j.status === 'failed') {
        statusBox.classList.add('failed');
        statusText.textContent = '✗ Paiement échoué. Veuillez réessayer.';
        clearInterval(cdInt); clearInterval(pollInt);
        setTimeout(() => { window.location.href = '/don.php?failed=1'; }, 3500);
      } else if (j.status === 'expired') {
        statusBox.classList.add('failed');
        statusText.textContent = '⏰ Délai expiré.';
        clearInterval(cdInt); clearInterval(pollInt);
        setTimeout(() => { window.location.href = '/don.php?expired=1'; }, 3500);
      }
    } catch (e) { /* on retentera */ }
  }
  const pollInt = setInterval(check, 3000);
  check(); // appel initial
})();
