// ============================================================
// UNICEF — JS public commun
// Thème (dark/light), traduction instantanée FR/EN, animations
// ============================================================

(function () {
  const root = document.documentElement;

  // ============ THÈME ============
  const themeBtn = document.getElementById('themeToggle');
  if (themeBtn) {
    themeBtn.addEventListener('click', () => {
      const next = root.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
      root.setAttribute('data-theme', next);
      document.cookie = 'theme=' + next + '; path=/; max-age=' + 60 * 60 * 24 * 365;
    });
  }

  // ============ LANGUE — bascule instantanée sans rechargement ============
  function applyLang(lang) {
    if (!window.__I18N || !window.__I18N[lang]) return;
    window.__LANG = lang;
    document.documentElement.setAttribute('lang', lang);
    document.cookie = 'lang=' + lang + '; path=/; max-age=' + 60 * 60 * 24 * 365;

    const dict = window.__I18N[lang];
    document.querySelectorAll('[data-i18n]').forEach((el) => {
      const key = el.getAttribute('data-i18n');
      if (dict[key] !== undefined) el.textContent = dict[key];
    });
    document.querySelectorAll('[data-i18n-attr]').forEach((el) => {
      // format: "attr:key,attr:key"
      el.getAttribute('data-i18n-attr').split(',').forEach((pair) => {
        const [attr, key] = pair.split(':');
        if (dict[key] !== undefined) el.setAttribute(attr, dict[key]);
      });
    });
    // Notifie les autres scripts (formulaire, etc.)
    document.dispatchEvent(new CustomEvent('langchange', { detail: { lang } }));
  }

  const langBtn = document.getElementById('langToggle');
  if (langBtn) {
    langBtn.addEventListener('click', () => {
      const next = (window.__LANG || 'fr') === 'fr' ? 'en' : 'fr';
      applyLang(next);
    });
  }

  // ============ ANIMATIONS — barres de progression ============
  document.querySelectorAll('.progress').forEach((p) => {
    const fill = p.querySelector('span');
    const target = parseFloat(p.dataset.value || '0');
    requestAnimationFrame(() => { fill.style.width = Math.min(100, target) + '%'; });
  });

  // ============ ANIMATIONS — compteurs ============
  const animateCount = (el) => {
    const target = parseFloat(el.dataset.count || '0');
    const dur = 1400;
    const start = performance.now();
    const step = (t) => {
      const p = Math.min(1, (t - start) / dur);
      const eased = 1 - Math.pow(1 - p, 3);
      const val = Math.floor(target * eased);
      el.textContent = el.dataset.format === 'money'
        ? val.toLocaleString('fr-FR') + ' XAF'
        : val.toLocaleString('fr-FR');
      if (p < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  };
  const io = new IntersectionObserver((entries) => {
    entries.forEach((en) => {
      if (en.isIntersecting) { animateCount(en.target); io.unobserve(en.target); }
    });
  }, { threshold: 0.3 });
  document.querySelectorAll('[data-count]').forEach((el) => io.observe(el));

  // ============ MENU MOBILE ============
  const burger = document.getElementById('burger');
  const nav = document.querySelector('.main-nav');
  if (burger && nav) burger.addEventListener('click', () => nav.classList.toggle('open'));
})();
