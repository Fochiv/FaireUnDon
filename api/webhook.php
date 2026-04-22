<?php
// =====================================================================
// Webhook — Réception des notifications de l'API de paiement
// POST /api/webhook.php
// =====================================================================
require_once __DIR__ . '/../includes/functions.php';

$body = file_get_contents('php://input');
$data = json_decode($body, true) ?: $_POST;

// Récupère soit la référence interne soit l'ID fournisseur
$reference   = $data['reference']      ?? $data['external_reference'] ?? null;
$providerRef = $data['transaction_id'] ?? $data['id']                 ?? null;
$status      = strtolower($data['status'] ?? '');

if (!$reference && !$providerRef) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Référence absente']);
    exit;
}

$map = [
    'success'=>'validated','successful'=>'validated','validated'=>'validated','completed'=>'validated','paid'=>'validated',
    'failed'=>'failed','cancelled'=>'failed','rejected'=>'failed',
];
$newStatus = $map[$status] ?? null;
if (!$newStatus) {
    http_response_code(202);
    echo json_encode(['ok' => true, 'note' => 'Statut ignoré : ' . $status]);
    exit;
}

$pdo = getDB();
if ($reference) {
    $pdo->prepare("UPDATE donations SET status=?, provider_ref=COALESCE(provider_ref,?), updated_at=CURRENT_TIMESTAMP WHERE reference=?")
        ->execute([$newStatus, $providerRef, $reference]);
} else {
    $pdo->prepare("UPDATE donations SET status=?, updated_at=CURRENT_TIMESTAMP WHERE provider_ref=?")
        ->execute([$newStatus, $providerRef]);
}

echo json_encode(['ok' => true]);
