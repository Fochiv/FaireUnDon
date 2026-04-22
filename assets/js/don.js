// ============================================================
// UNICEF — Logique du formulaire de don (3 étapes)
// ============================================================
(function () {
  const form = document.getElementById('donForm');
  if (!form) return;

  // ---------- Stepper ----------
  function showStep(n) {
    form.querySelectorAll('.step-pane').forEach((p) => { p.hidden = (parseInt(p.dataset.pane,10) !== n); });
    document.querySelectorAll('.stepper .step').forEach((s) => {
      const sn = parseInt(s.dataset.step,10);
      s.classList.toggle('active', sn === n);
      s.classList.toggle('done', sn < n);
    });
    if (n === 3) buildSummary();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
  form.addEventListener('click', (e) => {
    const next = e.target.closest('[data-next]');
    const prev = e.target.closest('[data-prev]');
    if (next) {
      const n = parseInt(next.dataset.next, 10);
      if (validateStep(n - 1)) showStep(n);
    } else if (prev) {
      showStep(parseInt(prev.dataset.prev, 10));
    }
  });

  // ---------- Type de donateur ----------
  const idFields = document.getElementById('identifiedFields');
  form.querySelectorAll('input[name="donor_type"]').forEach((r) => {
    r.addEventListener('change', () => {
      const isAnon = form.querySelector('input[name="donor_type"]:checked').value === 'anonymous';
      idFields.style.display = isAnon ? 'none' : '';
      idFields.querySelectorAll('input,select').forEach((el) => { el.required = !isAnon; });
    });
  });

  // ---------- Montants rapides ----------
  const amtInput = document.getElementById('amountInput');
  document.querySelectorAll('.quick-amt').forEach((b) => {
    b.addEventListener('click', () => {
      amtInput.value = b.dataset.amt;
      document.querySelectorAll('.quick-amt').forEach(x => x.classList.remove('selected'));
      b.classList.add('selected');
    });
  });

  // ---------- Méthode de paiement ----------
  const mobileBlock = document.getElementById('mobileBlock');
  const cardBlock   = document.getElementById('cardBlock');
  form.querySelectorAll('input[name="payment_method"]').forEach((r) => {
    r.addEventListener('change', () => {
      const m = form.querySelector('input[name="payment_method"]:checked').value;
      mobileBlock.hidden = (m !== 'mobile');
      cardBlock.hidden   = (m !== 'card');
      mobileBlock.querySelectorAll('input,select').forEach((el) => { el.required = (m === 'mobile'); });
    });
  });

  // ---------- Pays → Opérateurs ----------
  const countrySel = document.getElementById('payCountry');
  const operSel    = document.getElementById('payOperator');
  countrySel?.addEventListener('change', () => {
    const opt = countrySel.selectedOptions[0];
    const ops = opt && opt.dataset.operators ? JSON.parse(opt.dataset.operators) : [];
    operSel.innerHTML = '<option value="">— Choisir —</option>';
    ops.forEach((o) => {
      const op = document.createElement('option');
      op.value = o; op.textContent = o; operSel.appendChild(op);
    });
    operSel.disabled = ops.length === 0;
  });

  // ---------- Copier la carte ----------
  document.getElementById('copyCard')?.addEventListener('click', () => {
    navigator.clipboard.writeText(window.__PAY_CARD.replace(/\s/g,''))
      .then(() => alert('Numéro de carte copié !'));
  });

  // ---------- Validation ----------
  function validateStep(n) {
    const pane = form.querySelector('[data-pane="' + n + '"]');
    if (!pane) return true;
    const inputs = pane.querySelectorAll('input, select');
    for (const el of inputs) {
      if (el.offsetParent === null) continue; // ignoré si caché
      if (el.required && !el.value) {
        el.focus();
        el.classList.add('invalid');
        setTimeout(() => el.classList.remove('invalid'), 1500);
        return false;
      }
    }
    if (n === 1) {
      const v = parseFloat(amtInput.value || '0');
      if (v < 100) { amtInput.focus(); amtInput.classList.add('invalid'); setTimeout(()=>amtInput.classList.remove('invalid'),1500); return false; }
    }
    return true;
  }

  // ---------- Récapitulatif ----------
  function buildSummary() {
    const data = new FormData(form);
    const isAnon = data.get('donor_type') === 'anonymous';
    const method = data.get('payment_method');
    const rows = [];
    rows.push(['Type de don', isAnon ? '🕊️ Don anonyme' : '👤 Don identifié']);
    if (!isAnon) {
      rows.push(['Donateur', `${data.get('first_name')||''} ${data.get('last_name')||''}`]);
      rows.push(['Pays / Ville', `${data.get('country')||''} — ${data.get('city')||''}`]);
      if (data.get('email')) rows.push(['Email', data.get('email')]);
    }
    rows.push(['Méthode', method === 'mobile' ? '📱 Paiement mobile' : '💳 Virement carte bancaire']);
    if (method === 'mobile') {
      rows.push(['Opérateur', data.get('operator') || '—']);
      rows.push(['Numéro de paiement', data.get('payment_phone') || '—']);
    }
    rows.push(['Montant', `<strong style="color:var(--primary);font-size:20px">${parseFloat(data.get('amount')||0).toLocaleString('fr-FR')} XAF</strong>`]);
    document.getElementById('summary').innerHTML =
      rows.map(([k,v]) => `<div class="sum-row"><span>${k}</span><span>${v}</span></div>`).join('');
  }

  // ---------- Soumission ----------
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!validateStep(1) || !validateStep(2)) return;
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.textContent = '⏳ Traitement en cours...';

    const data = new FormData(form);
    try {
      const res = await fetch('/api/donate.php', { method: 'POST', body: data });
      const json = await res.json();
      if (!json.ok) throw new Error(json.error || 'Erreur inconnue');

      if (json.method === 'card') {
        // Pour la carte : page de remerciement directe (validation manuelle ensuite)
        window.location.href = '/merci.php?ref=' + encodeURIComponent(json.reference) + '&pending=1';
      } else {
        // Mobile : redirection vers la page de suivi temps réel
        window.location.href = '/paiement.php?ref=' + encodeURIComponent(json.reference);
      }
    } catch (err) {
      alert('❌ ' + err.message);
      btn.disabled = false;
      btn.textContent = '✓ Valider mon don';
    }
  });
})();
