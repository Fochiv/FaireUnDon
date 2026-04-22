<?php
// =====================================================================
// PAGE DE SUIVI — Compteur 8 minutes + polling automatique
// =====================================================================
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/lang.php';

$ref = $_GET['ref'] ?? '';
if (!$ref) { header('Location: /don.php'); exit; }

$stmt = getDB()->prepare("SELECT * FROM donations WHERE reference = ?");
$stmt->execute([$ref]);
$don = $stmt->fetch();
if (!$don) { header('Location: /don.php'); exit; }

$pageTitle = 'Paiement en cours — UNICEF';
require __DIR__ . '/includes/header.php';
?>

<section class="paiement-page">
  <div class="container narrow">
    <div class="paiement-card">

      <div class="pulse-circle">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="5" y="2" width="14" height="20" rx="2"/>
          <line x1="12" y1="18" x2="12.01" y2="18"/>
        </svg>
      </div>

      <h1>Validez le paiement sur votre téléphone</h1>
      <p class="muted">
        Une demande de paiement de
        <strong><?= e(formatMoney((float)$don['amount'])) ?></strong>
        vient d'être envoyée au numéro <strong><?= e($don['payment_phone']) ?></strong>.
        Composez le code PIN sur votre téléphone pour confirmer.
      </p>

      <!-- Compteur dégressif -->
      <div class="countdown" id="countdown">
        <span id="cdMinutes">08</span>:<span id="cdSeconds">00</span>
      </div>
      <div class="countdown-label">Temps restant pour valider</div>

      <!-- Statut en direct -->
      <div class="status-box" id="statusBox">
        <span class="dot"></span>
        <span id="statusText">En attente de validation...</span>
      </div>

      <div class="ref-box">
        Référence : <code><?= e($ref) ?></code>
      </div>

      <div class="paiement-help">
        <h4>📌 Vous n'avez pas reçu la demande ?</h4>
        <ul>
          <li>Vérifiez que votre téléphone capte le réseau.</li>
          <li>Composez <code>#150*50#</code> (Orange) ou <code>*126#</code> (MTN) pour valider manuellement.</li>
          <li>En cas de problème, contactez-nous sur <a href="<?= e(CONTACT_TELEGRAM_LINK) ?>" target="_blank">Telegram</a>.</li>
        </ul>
      </div>

    </div>
  </div>
</section>

<script>
  window.__REF = <?= json_encode($ref) ?>;
  window.__TIMEOUT = <?= (int) PAYMENT_TIMEOUT ?>;
  // Temps déjà écoulé depuis la création
  window.__ELAPSED = <?= max(0, time() - strtotime($don['created_at'])) ?>;
</script>
<script src="/assets/js/paiement.js"></script>

<?php require __DIR__ . '/includes/footer.php'; ?>
