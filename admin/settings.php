<?php
require_once __DIR__ . '/_auth.php';
$pageTitle = 'Paramètres';
$pdo = getDB();
$msg = ''; $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = $_POST['section'] ?? '';

    if ($section === 'project') {
        setSetting('goal_amount',           (string)max(1, (int)($_POST['goal_amount'] ?? 0)));
        setSetting('current_project_title', trim($_POST['project_title'] ?? ''));
        setSetting('current_project_desc',  trim($_POST['project_desc']  ?? ''));
        $msg = 'Paramètres du projet mis à jour.';
    }

    if ($section === 'password') {
        $cur = $_POST['current'] ?? ''; $new = $_POST['new'] ?? ''; $conf = $_POST['confirm'] ?? '';
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch();
        if (!$admin || !password_verify($cur, $admin['password_hash'])) {
            $err = 'Mot de passe actuel incorrect.';
        } elseif (strlen($new) < 8) {
            $err = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
        } elseif ($new !== $conf) {
            $err = 'La confirmation ne correspond pas.';
        } else {
            $hash = password_hash($new, PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE admins SET password_hash=? WHERE id=?")
                ->execute([$hash, $_SESSION['admin_id']]);
            $msg = 'Mot de passe mis à jour.';
        }
    }
}

$goal  = (int) getSetting('goal_amount', '1000000');
$title = getSetting('current_project_title', '');
$desc  = getSetting('current_project_desc', '');

require __DIR__ . '/_layout_top.php';
?>
<div class="admin-header"><div><h1>Paramètres</h1><p class="muted">Configuration de l'objectif, du projet en cours et de votre compte</p></div></div>

<?php if ($msg): ?><div class="alert alert-ok">✓ <?= e($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-error">✗ <?= e($err) ?></div><?php endif; ?>

<div class="admin-grid-2">

  <!-- Objectif + Projet en cours -->
  <div class="admin-card">
    <h3>🎯 Objectif & Projet en cours</h3>
    <form method="post">
      <input type="hidden" name="section" value="project">
      <div class="field">
        <label>Objectif de collecte (XAF)</label>
        <input type="number" name="goal_amount" min="1000" step="1000" value="<?= e($goal) ?>" required>
      </div>
      <div class="field">
        <label>Titre du projet en cours</label>
        <input type="text" name="project_title" value="<?= e($title) ?>" required>
      </div>
      <div class="field">
        <label>Description du projet</label>
        <textarea name="project_desc" rows="4" required><?= e($desc) ?></textarea>
      </div>
      <button class="btn btn-primary">Enregistrer</button>
    </form>
  </div>

  <!-- Mot de passe -->
  <div class="admin-card">
    <h3>🔒 Changer le mot de passe</h3>
    <form method="post" autocomplete="off">
      <input type="hidden" name="section" value="password">
      <div class="field"><label>Mot de passe actuel</label><input type="password" name="current" required></div>
      <div class="field"><label>Nouveau mot de passe (min. 8 caractères)</label><input type="password" name="new" required minlength="8"></div>
      <div class="field"><label>Confirmer</label><input type="password" name="confirm" required minlength="8"></div>
      <button class="btn btn-primary">Mettre à jour</button>
    </form>
  </div>

</div>

<div class="admin-card">
  <h3>💳 Carte bancaire affichée pour les virements</h3>
  <p class="muted">Numéro actuellement affiché aux donateurs : <code><?= e(CARD_NUMBER) ?></code></p>
  <p class="muted small">Pour modifier : éditer la constante <code>CARD_NUMBER</code> dans <code>includes/config.php</code>.</p>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
