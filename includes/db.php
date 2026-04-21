<?php
// Connexion PDO universelle : SQLite (Replit) ou MySQL (WampServer)
require_once __DIR__ . '/config.php';

function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    try {
        if (DB_DRIVER === 'mysql') {
            // --- MySQL / WampServer ---
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } else {
            // --- SQLite (par défaut sur Replit) ---
            // Crée automatiquement le fichier et la base au premier lancement
            if (!file_exists(DB_SQLITE_PATH)) {
                @mkdir(dirname(DB_SQLITE_PATH), 0775, true);
            }
            $pdo = new PDO('sqlite:' . DB_SQLITE_PATH);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->exec('PRAGMA foreign_keys = ON;');

            // Initialise le schéma si la base est vide
            $check = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='donations'")->fetch();
            if (!$check) {
                initSqliteSchema($pdo);
            }
        }
    } catch (PDOException $e) {
        die('Erreur de connexion à la base de données : ' . htmlspecialchars($e->getMessage()));
    }

    return $pdo;
}

function initSqliteSchema(PDO $pdo): void {
    $sql = file_get_contents(__DIR__ . '/../db/schema_sqlite.sql');
    $pdo->exec($sql);

    // Insère le compte admin par défaut
    $hash = password_hash(ADMIN_DEFAULT_PASSWORD, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
    $stmt->execute([ADMIN_DEFAULT_LOGIN, $hash]);

    // Paramètres par défaut (objectif de collecte, etc.)
    $defaults = [
        ['goal_amount', '50000000'],          // 50 millions XAF
        ['current_project_title', 'Aide alimentaire d\'urgence — Extrême-Nord Cameroun'],
        ['current_project_desc',  'Distribution de repas chauds, lait infantile et kits de survie pour 12 000 enfants menacés par la famine.'],
    ];
    $stmt = $pdo->prepare("INSERT INTO settings (key_name, value) VALUES (?, ?)");
    foreach ($defaults as $d) $stmt->execute($d);

    // Données de réalisations dans des pays africains
    $countries = [
        ['CI', 'Côte d\'Ivoire',    180000, 'Programmes nutritionnels dans les régions du Nord touchées par la sécheresse.'],
        ['NE', 'Niger',             220000, 'Distribution alimentaire d\'urgence et soins aux enfants malnutris.'],
        ['ML', 'Mali',              165000, 'Aide humanitaire dans les zones de conflit et camps de déplacés.'],
        ['BF', 'Burkina Faso',      195000, 'Centres de récupération nutritionnelle pour enfants sévèrement malnutris.'],
        ['TD', 'Tchad',             210000, 'Soutien aux familles dans le bassin du lac Tchad en crise alimentaire.'],
        ['SN', 'Sénégal',           135000, 'Programmes de cantines scolaires et distributions vivres.'],
        ['CD', 'RD Congo',          240000, 'Aide aux populations déplacées dans l\'Est du pays.'],
        ['ET', 'Éthiopie',          280000, 'Réponse à la sécheresse historique et insécurité alimentaire.'],
        ['SS', 'Soudan du Sud',     190000, 'Aide d\'urgence aux familles affectées par la famine.'],
        ['MG', 'Madagascar',        150000, 'Lutte contre la famine dans le Sud frappé par la sécheresse climatique.'],
        ['CM', 'Cameroun',          175000, 'Programmes prioritaires pour les enfants de l\'Extrême-Nord et zones anglophones.'],
        ['SO', 'Somalie',           160000, 'Aide d\'urgence dans les zones touchées par la famine et le conflit.'],
    ];
    $stmt = $pdo->prepare("INSERT INTO countries (code, name, people_helped, description) VALUES (?, ?, ?, ?)");
    foreach ($countries as $c) $stmt->execute($c);
}
