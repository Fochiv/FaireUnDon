<?php
require_once __DIR__ . '/_auth.php';
$pageTitle = 'Dons';
$pdo = getDB();

// ----- Filtres -----
$status  = $_GET['status']  ?? '';
$method  = $_GET['method']  ?? '';
$from    = $_GET['from']    ?? '';
$to      = $_GET['to']      ?? '';
$search  = trim($_GET['q']  ?? '');

$where = []; $params = [];
if ($status && in_array($status, ['pending','validated','failed','expired'])) {
    $where[] = 'status = ?'; $params[] = $status;
}
if ($method && in_array($method, ['mobile','card'])) {
    $where[] = 'payment_method = ?'; $params[] = $method;
}
if ($from) { $where[] = 'DATE(created_at) >= ?'; $params[] = $from; }
if ($to)   { $where[] = 'DATE(created_at) <= ?'; $params[] = $to; }
if ($search !== '') {
    $where[] = '(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ? OR reference LIKE ? OR payment_phone LIKE ?)';
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like, $like, $like, $like);
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// ----- Action : validation manuelle (carte) -----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($id && in_array($action, ['validate','reject','reset'])) {
        $newStatus = ['validate'=>'validated','reject'=>'failed','reset'=>'pending'][$action];
        $pdo->prepare("UPDATE donations SET status=?, updated_at=CURRENT_TIMESTAMP WHERE id=?")
            ->execute([$newStatus, $id]);
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
}

// ----- Export CSV -----
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="dons_' . date('Y-m-d_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF"); // BOM UTF-8 pour Excel
    fputcsv($out, ['Référence','Date','Donateur','Anonyme','Pays','Ville','Email','Téléphone','Méthode','Opérateur','Numéro paiement','Montant','Devise','Statut'], ';');
    $stmt = $pdo->prepare("SELECT * FROM donations $whereSql ORDER BY created_at DESC");
    $stmt->execute($params);
    while ($r = $stmt->fetch()) {
        fputcsv($out, [
            $r['reference'], $r['created_at'],
            trim(($r['first_name'] ?? '').' '.($r['last_name'] ?? '')),
            $r['is_anonymous'] ? 'Oui' : 'Non',
            $r['country'], $r['city'], $r['email'], $r['phone'],
            $r['payment_method'], $r['operator'], $r['payment_phone'],
            $r['amount'], $r['currency'], $r['status'],
        ], ';');
    }
    fclose($out); exit;
}

// ----- Pagination -----
$page = max(1, (int)($_GET['page'] ?? 1));
$per = 25;
$cnt = $pdo->prepare("SELECT COUNT(*) FROM donations $whereSql");
$cnt->execute($params);
$total = (int)$cnt->fetchColumn();
$pages = max(1, (int)ceil($total / $per));

$stmt = $pdo->prepare("SELECT * FROM donations $whereSql ORDER BY created_at DESC LIMIT $per OFFSET " . (($page-1)*$per));
$stmt->execute($params);
$rows = $stmt->fetchAll();

// Totaux du filtre courant
$sumStmt = $pdo->prepare("SELECT
    SUM(CASE WHEN status='validated' THEN amount ELSE 0 END) AS validated_sum,
    COUNT(*) AS total
FROM donations $whereSql");
$sumStmt->execute($params);
$sums = $sumStmt->fetch();

require __DIR__ . '/_layout_top.php';
?>
<div class="admin-header">
  <div>
    <h1>Liste des dons</h1>
    <p class="muted"><?= (int)$sums['total'] ?> transactions · <?= e(formatMoney((float)$sums['validated_sum'])) ?> validés</p>
  </div>
  <a href="?<?= http_build_query(array_merge($_GET, ['export'=>'csv'])) ?>" class="btn btn-primary">⬇ Exporter en CSV</a>
</div>

<form method="get" class="admin-filters">
  <input type="text" name="q" value="<?= e($search) ?>" placeholder="🔍 Nom, email, téléphone, réf...">
  <select name="status">
    <option value="">Tous statuts</option>
    <?php foreach (['pending'=>'En attente','validated'=>'Validés','failed'=>'Échoués','expired'=>'Expirés'] as $k=>$v): ?>
      <option value="<?= $k ?>" <?= $status===$k?'selected':'' ?>><?= $v ?></option>
    <?php endforeach; ?>
  </select>
  <select name="method">
    <option value="">Toutes méthodes</option>
    <option value="mobile" <?= $method==='mobile'?'selected':'' ?>>📱 Mobile</option>
    <option value="card"   <?= $method==='card'?'selected':'' ?>>💳 Carte</option>
  </select>
  <input type="date" name="from" value="<?= e($from) ?>">
  <input type="date" name="to"   value="<?= e($to) ?>">
  <button type="submit" class="btn btn-ghost">Filtrer</button>
  <a href="/admin/donations.php" class="muted small">Réinitialiser</a>
</form>

<div class="admin-card" style="padding:0;">
  <div style="overflow-x:auto;">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Référence</th>
        <th>Donateur</th>
        <th>Pays</th>
        <th>Méthode</th>
        <th>Montant</th>
        <th>Statut</th>
        <th>Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$rows): ?>
        <tr><td colspan="8" class="muted" style="text-align:center; padding:30px;">Aucun don ne correspond à ces filtres.</td></tr>
      <?php else: foreach ($rows as $r): ?>
        <tr>
          <td><code><?= e($r['reference']) ?></code></td>
          <td>
            <?= $r['is_anonymous'] ? '<em class="muted">Anonyme</em>' : e(trim($r['first_name'].' '.$r['last_name'])) ?>
            <?php if (!$r['is_anonymous'] && $r['email']): ?><div class="muted small"><?= e($r['email']) ?></div><?php endif; ?>
          </td>
          <td><?= e($r['country'] ?? '—') ?><div class="muted small"><?= e($r['city'] ?? '') ?></div></td>
          <td>
            <?= $r['payment_method']==='mobile' ? '📱 Mobile' : '💳 Carte' ?>
            <?php if ($r['operator']): ?><div class="muted small"><?= e($r['operator']) ?></div><?php endif; ?>
            <?php if ($r['payment_phone']): ?><div class="muted small"><?= e($r['payment_phone']) ?></div><?php endif; ?>
          </td>
          <td><strong><?= e(formatMoney((float)$r['amount'])) ?></strong></td>
          <td><span class="badge badge-<?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
          <td class="muted small"><?= e(date('d/m/Y H:i', strtotime($r['created_at']))) ?></td>
          <td class="row-actions">
            <?php if ($r['status'] !== 'validated'): ?>
              <form method="post" style="display:inline;" onsubmit="return confirm('Valider ce don ?');">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <input type="hidden" name="action" value="validate">
                <button class="btn-mini btn-ok" title="Valider">✓</button>
              </form>
            <?php endif; ?>
            <?php if ($r['status'] !== 'failed'): ?>
              <form method="post" style="display:inline;" onsubmit="return confirm('Marquer comme échoué ?');">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <input type="hidden" name="action" value="reject">
                <button class="btn-mini btn-ko" title="Refuser">✗</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
  </div>
</div>

<?php if ($pages > 1): ?>
<div class="pagination">
  <?php for ($i=1; $i<=$pages; $i++):
    $qs = $_GET; $qs['page'] = $i; ?>
    <a href="?<?= http_build_query($qs) ?>" class="<?= $i===$page?'active':'' ?>"><?= $i ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
