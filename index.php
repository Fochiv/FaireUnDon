<?php
// =====================================================================
// PAGE D'ACCUEIL — UNICEF
// Hero impactant, transparence financière en temps réel, réalisations
// =====================================================================
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/lang.php';

$pageTitle = 'UNICEF — ' . t('hero.title');
$stats     = getStats();
$countries = getCountries();
$projTitle = getSetting('current_project_title', '');
$projDesc  = getSetting('current_project_desc', '');

require __DIR__ . '/includes/header.php';
?>

<!-- ====================== HERO ====================== -->
<section class="hero">
  <div class="container hero-grid">
    <div>
      <span class="hero-badge"><span class="dot"></span> <span data-i18n="hero.badge"><?= t('hero.badge') ?></span></span>
      <h1><span data-i18n="hero.title"><?= t('hero.title') ?></span> <span class="grad">🤍</span></h1>
      <p class="lead" data-i18n="hero.subtitle"><?= t('hero.subtitle') ?></p>
      <div class="hero-ctas">
        <a href="/don.php" class="btn btn-primary">
          <span data-i18n="hero.cta"><?= t('hero.cta') ?></span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
        </a>
        <a href="#realisations" class="btn btn-ghost" data-i18n="hero.cta_secondary"><?= t('hero.cta_secondary') ?></a>
      </div>
    </div>
    <div class="hero-image">
      <img src="/assets/img/countries/orph2.jpg" alt="Enfants africains touchés par la famine">
      <div class="overlay-text">
        <h3>📍 <span data-i18n="project.current"><?= t('project.current') ?></span></h3>
        <p><?= e($projTitle) ?></p>
      </div>
    </div>
  </div>

  <!-- ====== TRANSPARENCE / STATS ====== -->
  <div class="container" style="position:relative;">
    <div class="transparency">
      <div class="transparency-head">
        <div>
          <h2 data-i18n="transparency.title"><?= t('transparency.title') ?></h2>
          <p class="muted" data-i18n="transparency.desc"><?= t('transparency.desc') ?></p>
        </div>
        <a href="/don.php" class="btn btn-success">+ <span data-i18n="hero.cta"><?= t('hero.cta') ?></span></a>
      </div>
      <div class="kpis">
        <div class="kpi primary">
          <div class="label" data-i18n="stats.collected"><?= t('stats.collected') ?></div>
          <div class="value" data-count="<?= (int) $stats['total_collected'] ?>" data-format="money">0 XAF</div>
        </div>
        <div class="kpi">
          <div class="label" data-i18n="stats.goal"><?= t('stats.goal') ?></div>
          <div class="value"><?= e(formatMoney($stats['goal'])) ?></div>
        </div>
        <div class="kpi accent">
          <div class="label" data-i18n="stats.donors"><?= t('stats.donors') ?></div>
          <div class="value" data-count="<?= (int) $stats['donors_count'] ?>">0</div>
        </div>
        <div class="kpi">
          <div class="label" data-i18n="stats.percent"><?= t('stats.percent') ?></div>
          <div class="value"><?= number_format($stats['percent'], 1, ',', ' ') ?> %</div>
        </div>
      </div>
      <div class="progress" data-value="<?= $stats['percent'] ?>"><span></span></div>
      <div class="progress-meta">
        <span><?= e(formatMoney($stats['total_collected'])) ?></span>
        <span><?= e(formatMoney($stats['goal'])) ?></span>
      </div>
    </div>
  </div>
</section>

<!-- ====================== RÉALISATIONS PAR PAYS ====================== -->
<section class="section" id="realisations">
  <div class="container">
    <div class="section-head">
      <h2 data-i18n="reach.title"><?= t('reach.title') ?></h2>
      <p data-i18n="reach.subtitle"><?= t('reach.subtitle') ?></p>
    </div>
    <div class="countries-grid">
      <?php
      // Banque d'images Unsplash pour chaque pays — visages d'enfants en difficulté
      // Mapping pays → photo locale (12 pays, 10 photos uniques distribuées)
      $imgs = [
        'ET' => '/assets/img/countries/orph10.jpg',
        'CD' => '/assets/img/countries/orph2.jpg',
        'NE' => '/assets/img/countries/orph5.jpg',
        'TD' => '/assets/img/countries/orph6.jpg',
        'BF' => '/assets/img/countries/orph3.jpg',
        'SS' => '/assets/img/countries/orph8.jpg',
        'CI' => '/assets/img/countries/orph9.jpg',
        'CM' => '/assets/img/countries/orph7.jpg',
        'ML' => '/assets/img/countries/orph4.jpg',
        'SO' => '/assets/img/countries/orph6.jpg',
        'MG' => '/assets/img/countries/orph10.jpg',
        'SN' => '/assets/img/countries/orph9.jpg',
      ];
      foreach ($countries as $c):
        $img = $c['image_url'] ?: ($imgs[$c['code']] ?? '/assets/img/countries/orph2.jpg');
      ?>
        <div class="country-card">
          <div class="img-wrap">
            <span class="flag"><?= e($c['code']) ?></span>
            <img src="<?= e($img) ?>" alt="<?= e($c['name']) ?>" loading="lazy">
          </div>
          <div class="body">
            <h3><?= e($c['name']) ?></h3>
            <div class="helped"><?= number_format((int)$c['people_helped'], 0, ',', ' ') ?> <span data-i18n="reach.people"><?= t('reach.people') ?></span></div>
            <p><?= e($c['description']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ====================== PARTAGE ====================== -->
<section class="section" style="padding-top:0;">
  <div class="container">
    <div class="transparency" style="text-align:center;">
      <h2 style="margin:0 0 6px;" data-i18n="share.title"><?= t('share.title') ?></h2>
      <p class="muted" data-i18n="share.subtitle"><?= t('share.subtitle') ?></p>
      <div class="share-row" style="justify-content:center; margin-top:18px;">
        <?php
          $url = urlencode(SITE_URL);
          $msg = urlencode("Aidez-moi à sauver des enfants de la famine. Chaque don compte 🙏 " . SITE_URL);
        ?>
        <a class="share-btn whatsapp" target="_blank" rel="noopener" href="https://wa.me/?text=<?= $msg ?>" aria-label="WhatsApp">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.52 3.48A11.78 11.78 0 0 0 12.06 0C5.5 0 .15 5.34.15 11.9c0 2.1.55 4.14 1.6 5.94L0 24l6.32-1.66a11.86 11.86 0 0 0 5.74 1.46h.01c6.56 0 11.9-5.34 11.9-11.9 0-3.18-1.24-6.17-3.45-8.42z"/></svg>
          <span>WhatsApp</span>
        </a>
        <a class="share-btn facebook" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?= $url ?>" aria-label="Facebook">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.51 1.49-3.9 3.78-3.9 1.1 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.45 2.89h-2.33v6.99A10 10 0 0 0 22 12z"/></svg>
          <span>Facebook</span>
        </a>
        <a class="share-btn telegram" target="_blank" rel="noopener" href="https://t.me/share/url?url=<?= $url ?>&text=<?= $msg ?>" aria-label="Telegram">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.24 3.64 11.95c-.88-.25-.89-.86.2-1.3l15.97-6.16c.73-.33 1.43.18 1.15 1.3l-2.72 12.81c-.19.91-.74 1.13-1.5.71l-4.14-3.06-2 1.94c-.23.23-.42.42-.86.42z"/></svg>
          <span>Telegram</span>
        </a>
        <a class="share-btn tiktok" target="_blank" rel="noopener" href="https://www.tiktok.com/" aria-label="TikTok">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5.8 20.1a6.34 6.34 0 0 0 10.86-4.43V8.85a8.16 8.16 0 0 0 4.77 1.52V6.92a4.85 4.85 0 0 1-1.84-.23z"/></svg>
          <span>TikTok</span>
        </a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
