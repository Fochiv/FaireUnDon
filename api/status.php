<?php
// =====================================================================
// API — Vérification du statut d'un paiement (polling toutes les 3s)
// GET /api/status.php?ref=...
// =====================================================================
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

$ref = $_GET['ref'] ?? '';
if (!$ref) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'ref manquante']); exit; }

$pdo = getDB();
$stmt = $pdo->prepare("SELECT * FROM donations WHERE reference = ?");
$stmt->execute([$ref]);
$don = $stmt->fetch();
if (!$don) { http_response_code(404); echo json_encode(['ok'=>false,'error'=>'Référence inconnue']); exit; }

// Si la transaction est déjà finalisée, on retourne le statut courant
if (in_array($don['status'], ['validated','failed','expired'])) {
    echo json_encode(['ok'=>true,'status'=>$don['status'],'reference'=>$ref]);
    exit;
}

// Sinon on interroge le fournisseur de paiement
if ($don['provider_ref']) {
    $ch = curl_init(PAY_API_BASE . '/v1/transactions/' . urlencode($don['provider_ref']));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . PAY_API_KEY],
        CURLOPT_TIMEOUT        => 8,
    ]);
    $body = curl_exec($ch);
    curl_close($ch);
    if ($body) {
        $r = json_decode($body, true);
        $remoteStatus = strtolower($r['status'] ?? '');
        // Mapping des statuts vers nos états internes
        $map = [
            'success'=>'validated', 'successful'=>'validated', 'validated'=>'validated', 'completed'=>'validated', 'paid'=>'validated',
            'failed'=>'failed', 'cancelled'=>'failed', 'rejected'=>'failed',
        ];
        $newStatus = $map[$remoteStatus] ?? null;
        if ($newStatus) {
            $pdo->prepare("UPDATE donations SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE reference = ?")
                ->execute([$newStatus, $ref]);
            $don['status'] = $newStatus;
        }
    }
}

// Vérification du timeout (8 minutes)
$created = strtotime($don['created_at']);
if ($don['status'] === 'pending' && (time() - $created) > PAYMENT_TIMEOUT) {
    $pdo->prepare("UPDATE donations SET status='expired' WHERE reference=?")->execute([$ref]);
    $don['status'] = 'expired';
}

echo json_encode([
    'ok'        => true,
    'status'    => $don['status'],
    'reference' => $ref,
    'elapsed'   => time() - $created,
]);
