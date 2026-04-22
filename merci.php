<?php
// =====================================================================
// PAGE DE REMERCIEMENT — Don validé avec succès
// =====================================================================
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/lang.php';

$ref     = $_GET['ref']     ?? '';
$pending = !empty($_GET['pending']); // pour les virements carte (validation manuelle)

if (!$ref) { header('Location: /'); exit; }
$stmt = getDB()->prepare("SELECT * FROM donations WHERE reference = ?");
$stmt->execute([$ref]);
$don = $stmt->fetch();
if (!$don) { header('Location: /'); exit; }

$pageTitle = 'Merci pour votre don — UNICEF';
require __DIR__ . '/includes/header.php';
?>

<section class="merci-page">
  <div class="container narrow">
    <div class="merci-card">

      <?php if ($pending): ?>
        <div class="check-icon pending">⏳</div>
        <h1 style="color: var(--warn);">Don enregistré, en attente de validation</h1>
        <p>Merci <?= $don['is_anonymous'] ? '' : '<strong>' . e($don['first_name']) . '</strong>' ?> !
           Votre virement par carte sera vérifié manuellement.</p>
      <?php else: ?>
        <div class="check-icon">✓</div>
        <h1 style="color: var(--accent);">Votre transaction a été validée avec succès</h1>
        <p class="lead">
          Merci<?= $don['is_anonymous'] ? '' : ', <strong>' . e($don['first_name']) . '</strong>' ?> ❤️<br>
          Grâce à vous, des enfants pourront manger aujourd'hui.
        </p>
      <?php endif; ?>

      <div class="recap">
        <div class="recap-row"><span>Montant</span><strong><?= e(formatMoney((float)$don['amount'])) ?></strong></div>
        <div class="recap-row"><span>Référence</span><code><?= e($ref) ?></code></div>
        <div class="recap-row"><span>Date</span><span><?= e(date('d/m/Y H:i', strtotime($don['created_at']))) ?></span></div>
        <div class="recap-row"><span>Méthode</span><span><?= $don['payment_method'] === 'mobile' ? '📱 Mobile' : '💳 Virement carte' ?></span></div>
      </div>

      <div class="merci-share">
        <h3>Partagez pour aider encore plus 🙏</h3>
        <p class="muted">Un partage = une vie potentiellement sauvée.</p>
        <?php
          $url = urlencode(SITE_URL);
          $msg = urlencode("Je viens de soutenir UNICEF pour lutter contre la famine. Faites comme moi 🙏 " . SITE_URL);
        ?>
        <div class="share-row" style="justify-content:center; margin-top:14px;">
          <a class="share-btn whatsapp" target="_blank" rel="noopener" href="https://wa.me/?text=<?= $msg ?>">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.52 3.48A11.78 11.78 0 0 0 12.06 0C5.5 0 .15 5.34.15 11.9c0 2.1.55 4.14 1.6 5.94L0 24l6.32-1.66a11.86 11.86 0 0 0 5.74 1.46h.01c6.56 0 11.9-5.34 11.9-11.9 0-3.18-1.24-6.17-3.45-8.42z"/></svg>
            <span>WhatsApp</span>
          </a>
          <a class="share-btn facebook" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?= $url ?>">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.51 1.49-3.9 3.78-3.9 1.1 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.45 2.89h-2.33v6.99A10 10 0 0 0 22 12z"/></svg>
            <span>Facebook</span>
          </a>
          <a class="share-btn telegram" target="_blank" rel="noopener" href="https://t.me/share/url?url=<?= $url ?>&text=<?= $msg ?>">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.24 3.64 11.95c-.88-.25-.89-.86.2-1.3l15.97-6.16c.73-.33 1.43.18 1.15 1.3l-2.72 12.81c-.19.91-.74 1.13-1.5.71l-4.14-3.06-2 1.94c-.23.23-.42.42-.86.42z"/></svg>
            <span>Telegram</span>
          </a>
        </div>
      </div>

      <div style="text-align:center; margin-top:30px;">
        <a href="/" class="btn btn-ghost">← Retour à l'accueil</a>
      </div>

    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
