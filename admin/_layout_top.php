<?php
// En-tête commune à toutes les pages admin protégées
$current = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="fr" data-theme="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? 'Admin') ?> — UNICEF</title>
<link rel="icon" type="image/png" href="/assets/img/logo/unicef-color.png">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="admin-body">
<aside class="admin-sidebar">
  <div class="admin-brand">
    <img src="/assets/img/logo/unicef-color.png" alt="UNICEF">
    <span>Admin</span>
  </div>
  <nav class="admin-nav">
    <a href="/admin/index.php"     class="<?= $current==='index.php'?'active':'' ?>">📊 Tableau de bord</a>
    <a href="/admin/donations.php" class="<?= $current==='donations.php'?'active':'' ?>">💰 Dons</a>
    <a href="/admin/settings.php"  class="<?= $current==='settings.php'?'active':'' ?>">⚙️ Paramètres</a>
    <a href="/admin/logout.php" class="logout">🚪 Déconnexion</a>
  </nav>
  <div class="admin-foot">
    Connecté : <strong><?= e($_SESSION['admin_username'] ?? '') ?></strong>
  </div>
</aside>
<main class="admin-main">
