<?php
require_once __DIR__ . '/../includes/functions.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    $stmt = getDB()->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$u]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($p, $admin['password_hash'])) {
        $_SESSION['admin_id']       = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        header('Location: /admin/index.php');
        exit;
    }
    $error = 'Identifiants incorrects.';
}
?>
<!doctype html>
<html lang="fr" data-theme="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Administration — UNICEF</title>
<link rel="icon" type="image/png" href="/assets/img/logo/unicef-color.png">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="admin-login-body">
<div class="login-wrap">
  <div class="login-card">
    <img src="/assets/img/logo/unicef-color.png" alt="UNICEF" class="login-logo">
    <h1>Espace Administrateur</h1>
    <p class="muted">Connexion réservée à l'administration UNICEF</p>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
      <div class="field">
        <label>Identifiant</label>
        <input type="text" name="username" required autofocus>
      </div>
      <div class="field">
        <label>Mot de passe</label>
        <input type="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%; margin-top:10px;">
        Se connecter →
      </button>
    </form>

    <div style="text-align:center; margin-top:18px;">
      <a href="/" class="muted small">← Retour au site public</a>
    </div>
  </div>
</div>
</body>
</html>
