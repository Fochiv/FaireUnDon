<?php
require_once __DIR__ . '/_auth.php';
$pageTitle = 'Tableau de bord';

$pdo = getDB();
$stats = getStats();

// KPIs détaillés
$kpis = $pdo->query("
    SELECT
        COUNT(*) AS total_tx,
        SUM(CASE WHEN status='validated' THEN 1 ELSE 0 END) AS ok_count,
        SUM(CASE WHEN status='pending'   THEN 1 ELSE 0 END) AS pending_count,
        SUM(CASE WHEN status='failed'    THEN 1 ELSE 0 END) AS failed_count,
        SUM(CASE WHEN status='expired'   THEN 1 ELSE 0 END) AS expired_count,
        AVG(CASE WHEN status='validated' THEN amount END)   AS avg_amount,
        MAX(CASE WHEN status='validated' THEN amount END)   AS max_amount
    FROM donations
")->fetch();

// Évolution sur les 30 derniers jours
$dateLimit = (DB_DRIVER === 'mysql')
    ? "DATE_SUB(NOW(), INTERVAL 30 DAY)"
    : "datetime('now','-30 days')";
$daily = $pdo->query("
    SELECT DATE(created_at) AS d,
           COUNT(*) AS n,
           SUM(CASE WHEN status='validated' THEN amount ELSE 0 END) AS amt
    FROM donations
    WHERE created_at >= $dateLimit
    GROUP BY DATE(created_at)
    ORDER BY d ASC
")->fetchAll();

// Répartition par méthode
$byMethod = $pdo->query("
    SELECT payment_method, COUNT(*) AS n, SUM(amount) AS amt
    FROM donations WHERE status='validated'
    GROUP BY payment_method
")->fetchAll();

// Top 5 pays donateurs
$byCountry = $pdo->query("
    SELECT COALESCE(country,'?') AS country, COUNT(*) AS n, SUM(amount) AS amt
    FROM donations WHERE status='validated'
    GROUP BY country ORDER BY amt DESC LIMIT 5
")->fetchAll();

// Derniers dons
$recent = $pdo->query("
    SELECT * FROM donations ORDER BY created_at DESC LIMIT 8
")->fetchAll();

require __DIR__ . '/_layout_top.php';
?>
<div class="admin-header">
  <div>
    <h1>Tableau de bord</h1>
    <p class="muted"><?= date('l j F Y — H:i') ?></p>
  </div>
  <a href="/" target="_blank" class="btn btn-ghost">🌍 Voir le site public</a>
</div>

<!-- Cartes KPI -->
<div class="kpi-grid">
  <div class="kpi-card kpi-primary">
    <div class="kpi-label">Total collecté</div>
    <div class="kpi-value"><?= e(formatMoney($stats['total_collected'])) ?></div>
    <div class="kpi-sub"><?= number_format($stats['percent'],1,',',' ') ?>% de l'objectif</div>
  </div>
  <div class="kpi-card">
    <div class="kpi-label">Donateurs</div>
    <div class="kpi-value"><?= number_format($stats['donors_count'],0,',',' ') ?></div>
    <div class="kpi-sub"><?= (int)$kpis['ok_count'] ?> dons validés</div>
  </div>
  <div class="kpi-card">
    <div class="kpi-label">Don moyen</div>
    <div class="kpi-value"><?= e(formatMoney((float)($kpis['avg_amount'] ?? 0))) ?></div>
    <div class="kpi-sub">Max : <?= e(formatMoney((float)($kpis['max_amount'] ?? 0))) ?></div>
  </div>
  <div class="kpi-card">
    <div class="kpi-label">En attente</div>
    <div class="kpi-value"><?= (int)$kpis['pending_count'] ?></div>
    <div class="kpi-sub"><?= (int)$kpis['failed_count'] ?> échecs · <?= (int)$kpis['expired_count'] ?> expirés</div>
  </div>
</div>

<!-- Progression vers l'objectif -->
<div class="admin-card">
  <h3>Progression vers l'objectif</h3>
  <div class="progress-large">
    <div class="progress-bar" style="width: <?= $stats['percent'] ?>%"></div>
  </div>
  <div class="progress-meta">
    <span><?= e(formatMoney($stats['total_collected'])) ?></span>
    <span class="muted">/ <?= e(formatMoney($stats['goal'])) ?></span>
  </div>
</div>

<!-- Graphique évolution + répartition -->
<div class="admin-grid-2">
  <div class="admin-card">
    <h3>📈 Évolution des dons (30 derniers jours)</h3>
    <canvas id="chartDaily" height="180"></canvas>
  </div>
  <div class="admin-card">
    <h3>💳 Répartition par méthode</h3>
    <?php if (!$byMethod): ?>
      <p class="muted">Aucun don validé pour le moment.</p>
    <?php else: ?>
      <div class="method-list">
        <?php $totalAmt = array_sum(array_column($byMethod,'amt')) ?: 1; foreach($byMethod as $m): ?>
          <div class="method-row">
            <div class="method-name">
              <?= $m['payment_method']==='mobile' ? '📱 Paiement mobile' : '💳 Carte bancaire' ?>
              <span class="muted"> · <?= (int)$m['n'] ?> dons</span>
            </div>
            <div class="method-amt"><?= e(formatMoney((float)$m['amt'])) ?></div>
            <div class="method-bar"><div style="width:<?= ($m['amt']/$totalAmt)*100 ?>%"></div></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Top pays + derniers dons -->
<div class="admin-grid-2">
  <div class="admin-card">
    <h3>🌍 Top 5 — Pays donateurs</h3>
    <?php if (!$byCountry): ?>
      <p class="muted">Aucune donnée.</p>
    <?php else: ?>
      <table class="admin-table">
        <thead><tr><th>Pays</th><th>Dons</th><th>Montant</th></tr></thead>
        <tbody>
        <?php foreach($byCountry as $c): ?>
          <tr>
            <td><strong><?= e($c['country']) ?></strong></td>
            <td><?= (int)$c['n'] ?></td>
            <td><?= e(formatMoney((float)$c['amt'])) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
  <div class="admin-card">
    <h3>🕒 Derniers dons</h3>
    <?php if (!$recent): ?>
      <p class="muted">Aucun don pour le moment.</p>
    <?php else: ?>
      <table class="admin-table">
        <thead><tr><th>Donateur</th><th>Montant</th><th>Statut</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach($recent as $r): ?>
          <tr>
            <td><?= $r['is_anonymous'] ? '<em class="muted">Anonyme</em>' : e(trim($r['first_name'].' '.$r['last_name'])) ?></td>
            <td><strong><?= e(formatMoney((float)$r['amount'])) ?></strong></td>
            <td><span class="badge badge-<?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
            <td class="muted small"><?= e(date('d/m H:i', strtotime($r['created_at']))) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <div style="margin-top:10px;"><a href="/admin/donations.php" class="muted small">Voir tous les dons →</a></div>
    <?php endif; ?>
  </div>
</div>

<script>
  // Graphique en barres natif (sans librairie)
  (function(){
    const data = <?= json_encode($daily) ?>;
    const cv = document.getElementById('chartDaily');
    if (!cv) return;
    const ctx = cv.getContext('2d');
    const W = cv.width = cv.offsetWidth, H = cv.height = 220;
    ctx.clearRect(0,0,W,H);
    if (!data.length) {
      ctx.fillStyle = '#9ca3af'; ctx.font = '14px system-ui';
      ctx.textAlign='center'; ctx.fillText('Aucune donnée pour les 30 derniers jours', W/2, H/2);
      return;
    }
    const max = Math.max(...data.map(d => +d.amt || 0), 1);
    const padL=40, padB=24, padT=10, padR=10;
    const cw = W - padL - padR, ch = H - padT - padB;
    const bw = cw / data.length;
    // Grid
    ctx.strokeStyle = 'rgba(255,255,255,0.06)';
    for (let i=0; i<=4; i++) {
      const y = padT + (ch/4)*i;
      ctx.beginPath(); ctx.moveTo(padL, y); ctx.lineTo(W-padR, y); ctx.stroke();
      ctx.fillStyle='#9ca3af'; ctx.font='10px system-ui'; ctx.textAlign='right';
      ctx.fillText(Math.round(max*(1-i/4)).toLocaleString('fr-FR'), padL-6, y+3);
    }
    // Bars
    data.forEach((d,i) => {
      const v = +d.amt || 0;
      const h = (v/max) * ch;
      const x = padL + bw*i + 2;
      const y = padT + ch - h;
      const grad = ctx.createLinearGradient(0,y,0,padT+ch);
      grad.addColorStop(0,'#7c5cff'); grad.addColorStop(1,'#22d3a8');
      ctx.fillStyle = grad;
      ctx.fillRect(x, y, Math.max(2, bw-4), h);
    });
    // X labels (toutes les 5 dates)
    ctx.fillStyle='#9ca3af'; ctx.font='10px system-ui'; ctx.textAlign='center';
    data.forEach((d,i) => {
      if (i % Math.ceil(data.length/6) !== 0) return;
      const x = padL + bw*i + bw/2;
      ctx.fillText(d.d.slice(5), x, H-8);
    });
  })();
</script>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
