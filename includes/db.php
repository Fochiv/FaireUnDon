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
        ['goal_amount', '1000000'],          // 1 million XAF
        ['current_project_title', 'Aide alimentaire d\'urgence — Extrême-Nord Cameroun'],
        ['current_project_desc',  'Distribution de repas chauds, lait infantile et kits de survie pour 12 000 enfants menacés par la famine.'],
    ];
    $stmt = $pdo->prepare("INSERT INTO settings (key_name, value) VALUES (?, ?)");
    foreach ($defaults as $d) $stmt->execute($d);

    // Données de réalisations dans des pays africains (ordonnées par nombre)
    $countries = [
        ['ET', 'Éthiopie',       280000, 'Réponse humanitaire face à une sécheresse prolongée ayant fortement affecté les cultures et l\'accès à l\'alimentation dans plusieurs régions rurales.'],
        ['CD', 'RD Congo',       240000, 'Soutien alimentaire et nutritionnel apporté aux populations déplacées dans l\'Est du pays, touchées par des conflits persistants.'],
        ['NE', 'Niger',          220000, 'Interventions d\'urgence comprenant la distribution de vivres et la prise en charge des enfants souffrant de malnutrition aiguë.'],
        ['TD', 'Tchad',          210000, 'Assistance alimentaire aux familles vulnérables dans le bassin du lac Tchad, confronté à des crises climatiques et sécuritaires.'],
        ['BF', 'Burkina Faso',   195000, 'Mise en place de centres de récupération nutritionnelle pour les enfants sévèrement malnutris dans les zones à forte insécurité.'],
        ['SS', 'Soudan du Sud',  190000, 'Aide alimentaire d\'urgence destinée aux communautés touchées par des épisodes de famine liés aux conflits et aux conditions climatiques extrêmes.'],
        ['CI', 'Côte d\'Ivoire', 180000, 'Déploiement de programmes nutritionnels dans les régions du Nord, avec distribution de vivres aux populations vulnérables.'],
        ['CM', 'Cameroun',       175000, 'Actions ciblées dans l\'Extrême-Nord, le Nord-Ouest et le Sud-Ouest pour soutenir les enfants et familles affectés par les crises humanitaires.'],
        ['ML', 'Mali',           165000, 'Interventions humanitaires dans les zones de conflit et dans les camps de déplacés internes.'],
        ['SO', 'Somalie',        160000, 'Aide d\'urgence dans les régions touchées par des sécheresses sévères et une insécurité alimentaire chronique.'],
        ['MG', 'Madagascar',     150000, 'Programmes de lutte contre la famine dans le Sud du pays, affecté par des sécheresses répétées.'],
        ['SN', 'Sénégal',        135000, 'Mise en place de cantines scolaires et distribution de vivres pour améliorer la sécurité alimentaire des enfants.'],
    ];
    $stmt = $pdo->prepare("INSERT INTO countries (code, name, people_helped, description) VALUES (?, ?, ?, ?)");
    foreach ($countries as $c) $stmt->execute($c);
}
