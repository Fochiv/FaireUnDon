<?php
// Fonctions utilitaires partagées par toute l'application
require_once __DIR__ . '/db.php';

/** Échappement HTML court */
function e(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

/** Récupère un paramètre depuis la table settings */
function getSetting(string $key, ?string $default = null): ?string {
    $stmt = getDB()->prepare("SELECT value FROM settings WHERE key_name = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? $row['value'] : $default;
}

/** Met à jour ou insère un paramètre */
function setSetting(string $key, string $value): void {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id FROM settings WHERE key_name = ?");
    $stmt->execute([$key]);
    if ($stmt->fetch()) {
        $pdo->prepare("UPDATE settings SET value = ? WHERE key_name = ?")->execute([$value, $key]);
    } else {
        $pdo->prepare("INSERT INTO settings (key_name, value) VALUES (?, ?)")->execute([$key, $value]);
    }
}

/** Statistiques globales pour la page d'accueil et l'admin */
function getStats(): array {
    $pdo = getDB();
    $row = $pdo->query("
        SELECT
            COALESCE(SUM(CASE WHEN status='validated' THEN amount ELSE 0 END), 0) AS total_collected,
            COUNT(CASE WHEN status='validated' THEN 1 END) AS donors_count
        FROM donations
    ")->fetch();

    $goal = (float) getSetting('goal_amount', '50000000');
    $total = (float) $row['total_collected'];
    $percent = $goal > 0 ? min(100, ($total / $goal) * 100) : 0;

    return [
        'total_collected' => $total,
        'goal'            => $goal,
        'donors_count'    => (int) $row['donors_count'],
        'percent'         => round($percent, 2),
    ];
}

/** Formate un montant en FCFA */
function formatMoney(float $amount, string $currency = 'XAF'): string {
    return number_format($amount, 0, ',', ' ') . ' ' . $currency;
}

/** Génère une référence unique pour une transaction */
function generateReference(): string {
    return 'UNI-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
}

/** Liste des pays bénéficiaires */
function getCountries(): array {
    return getDB()->query("SELECT * FROM countries ORDER BY people_helped DESC")->fetchAll();
}
