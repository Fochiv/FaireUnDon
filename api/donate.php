<?php
// =====================================================================
// API — Création d'un don et initiation du paiement
// POST /api/donate.php
// =====================================================================
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

function out($data, int $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(['ok' => false, 'error' => 'Méthode non autorisée'], 405);

// Récupération + nettoyage des données
$donor_type   = $_POST['donor_type']   ?? 'identified';
$amount       = (float) ($_POST['amount'] ?? 0);
$method       = $_POST['payment_method'] ?? 'mobile';
$is_anon      = ($donor_type === 'anonymous') ? 1 : 0;

if ($amount < 100) out(['ok' => false, 'error' => 'Montant invalide (minimum 100 XAF).'], 400);
if (!in_array($method, ['mobile', 'card'])) out(['ok' => false, 'error' => 'Méthode de paiement invalide.'], 400);

$first_name = $is_anon ? null : trim($_POST['first_name'] ?? '');
$last_name  = $is_anon ? null : trim($_POST['last_name']  ?? '');
$country    = $is_anon ? null : trim($_POST['country']    ?? '');
$city       = $is_anon ? null : trim($_POST['city']       ?? '');
$phone      = $is_anon ? null : trim($_POST['phone']      ?? '');
$email      = $is_anon ? null : trim($_POST['email']      ?? '');

$operator     = ($method === 'mobile') ? trim($_POST['operator']      ?? '') : null;
$payment_phone= ($method === 'mobile') ? trim($_POST['payment_phone'] ?? '') : null;
$pay_country  = ($method === 'mobile') ? trim($_POST['pay_country']   ?? '') : null;

if (!$is_anon && (!$first_name || !$last_name)) out(['ok' => false, 'error' => 'Nom et prénom requis.'], 400);
if ($method === 'mobile' && (!$operator || !$payment_phone || !$pay_country)) {
    out(['ok' => false, 'error' => 'Pays, opérateur et numéro requis pour le paiement mobile.'], 400);
}

// Création de la transaction en base
$reference = generateReference();
$pdo = getDB();
$stmt = $pdo->prepare("
    INSERT INTO donations
        (is_anonymous, first_name, last_name, country, city, phone, email,
         amount, currency, payment_method, operator, payment_phone, reference, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->execute([
    $is_anon, $first_name, $last_name, $country ?: $pay_country, $city, $phone, $email,
    $amount, 'XAF', $method, $operator, $payment_phone, $reference,
    $method === 'card' ? 'pending' : 'pending'
]);

// Si paiement par carte, on s'arrête ici — la validation est manuelle (Telegram)
if ($method === 'card') {
    out(['ok' => true, 'method' => 'card', 'reference' => $reference]);
}

// =====================================================================
// Initiation du paiement mobile via l'API (côté serveur uniquement)
// =====================================================================
$payload = [
    'amount'      => $amount,
    'currency'    => 'XAF',
    'country'     => $pay_country,
    'operator'    => $operator,
    'phone'       => $payment_phone,
    'reference'   => $reference,
    'description' => 'Don UNICEF — ' . $reference,
    'callback_url'=> (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                     . '://' . $_SERVER['HTTP_HOST'] . '/api/webhook.php',
];

$ch = curl_init(PAY_API_BASE . '/v1/collect');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . PAY_API_KEY,
        'Content-Type: application/json',
    ],
    CURLOPT_TIMEOUT        => 20,
]);
$body = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);
curl_close($ch);

// On accepte que l'API soit indisponible en dev — la transaction reste "pending"
$providerRef = null;
if ($body) {
    $resp = json_decode($body, true);
    $providerRef = $resp['transaction_id'] ?? $resp['id'] ?? null;
    if ($providerRef) {
        $pdo->prepare("UPDATE donations SET provider_ref = ? WHERE reference = ?")
            ->execute([$providerRef, $reference]);
    }
}

out([
    'ok'        => true,
    'method'    => 'mobile',
    'reference' => $reference,
    'provider'  => $providerRef,
    'timeout'   => PAYMENT_TIMEOUT,
]);
