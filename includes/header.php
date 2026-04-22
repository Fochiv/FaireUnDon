<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/lang.php';
$pageTitle = $pageTitle ?? SITE_NAME;
?>
<!DOCTYPE html>
<html lang="<?= currentLang() ?>" data-theme="<?= e($_COOKIE['theme'] ?? 'dark') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="UNICEF — Aidons ensemble les enfants victimes de la famine au Cameroun et en Afrique subsaharienne.">
    <link rel="icon" type="image/png" href="/assets/img/logo/unicef-color.png">
    <link rel="apple-touch-icon" href="/assets/img/logo/unicef-color.png">
    <link rel="stylesheet" href="/assets/css/style.css">
    <script>
      // Applique le thème AVANT le rendu pour éviter le flash blanc
      (function(){
        var t = document.cookie.match(/(?:^|; )theme=([^;]+)/);
        if (t) document.documentElement.setAttribute('data-theme', t[1]);
        var l = document.cookie.match(/(?:^|; )lang=([^;]+)/);
        if (l) document.documentElement.setAttribute('lang', l[1]);
      })();
    </script>
    <script><?= dumpTranslationsJS() ?></script>
</head>
<body>
<header class="site-header">
  <div class="container header-inner">
    <a href="/" class="brand">
      <img src="/assets/img/logo/unicef-color.png" alt="UNICEF" class="brand-logo">
      <span class="brand-text">UNICEF</span>
    </a>
    <nav class="main-nav" aria-label="Navigation principale">
      <a href="/" class="<?= ($_SERVER['SCRIPT_NAME'] === '/index.php') ? 'active' : '' ?>" data-i18n="nav.home"><?= t('nav.home') ?></a>
      <a href="/#realisations" data-i18n="nav.realisations"><?= t('nav.realisations') ?></a>
      <a href="/don.php" class="nav-cta" data-i18n="nav.donate"><?= t('nav.donate') ?></a>
      <a href="/#contact" data-i18n="nav.contact"><?= t('nav.contact') ?></a>
    </nav>
    <div class="header-actions">
      <button id="themeToggle" class="icon-btn" title="<?= t('theme.toggle') ?>" aria-label="<?= t('theme.toggle') ?>">
        <svg class="i-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
        <svg class="i-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
      </button>
      <button id="langToggle" class="lang-btn" data-i18n="lang.toggle"><?= t('lang.toggle') ?></button>
      <button class="burger" id="burger" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>
<main>
