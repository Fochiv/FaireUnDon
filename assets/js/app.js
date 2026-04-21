// ============================================================
// UNICEF — JS public commun (thème, menu, animations)
// ============================================================

(function () {
  // --- Bascule de thème (sombre/clair) avec persistance via cookie ---
  const root = document.documentElement;
  const btn = document.getElementById('themeToggle');
  if (btn) {
    btn.addEventListener('click', () => {
      const next = root.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
      root.setAttribute('data-theme', next);
      document.cookie = 'theme=' + next + '; path=/; max-age=' + 60 * 60 * 24 * 365;
    });
  }

  // --- Menu burger mobile ---
  const burger = document.getElementById('burger');
  const nav = document.querySelector('.main-nav');
  if (burger && nav) {
    burger.addEventListener('click', () => nav.classList.toggle('open'));
  }

  // --- Animation des barres de progression ---
  document.querySelectorAll('.progress').forEach((p) => {
    const fill = p.querySelector('span');
    const target = parseFloat(p.dataset.value || '0');
    requestAnimationFrame(() => { fill.style.width = Math.min(100, target) + '%'; });
  });

  // --- Compteurs animés ---
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
})();
