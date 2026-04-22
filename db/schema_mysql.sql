-- ============================================================
-- Schéma MySQL pour WampServer
-- Importer dans phpMyAdmin après avoir créé la base "unicef_dons"
-- ============================================================

CREATE DATABASE IF NOT EXISTS `unicef_dons` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `unicef_dons`;

-- Comptes administrateurs
CREATE TABLE IF NOT EXISTS `admins` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `username`      VARCHAR(64) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Paramètres clé/valeur (objectif, projet en cours, etc.)
CREATE TABLE IF NOT EXISTS `settings` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `key_name`   VARCHAR(100) NOT NULL UNIQUE,
    `value`      TEXT NOT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pays bénéficiaires (réalisations)
CREATE TABLE IF NOT EXISTS `countries` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `code`          VARCHAR(5) NOT NULL UNIQUE,
    `name`          VARCHAR(100) NOT NULL,
    `people_helped` INT NOT NULL DEFAULT 0,
    `description`   TEXT,
    `image_url`     VARCHAR(255),
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dons reçus
CREATE TABLE IF NOT EXISTS `donations` (
    `id`             INT AUTO_INCREMENT PRIMARY KEY,
    `is_anonymous`   TINYINT(1) NOT NULL DEFAULT 0,
    `first_name`     VARCHAR(100),
    `last_name`      VARCHAR(100),
    `country`        VARCHAR(100),
    `city`           VARCHAR(100),
    `phone`          VARCHAR(30),
    `email`          VARCHAR(150),
    `amount`         DECIMAL(12,2) NOT NULL,
    `currency`       VARCHAR(5) DEFAULT 'XAF',
    `payment_method` VARCHAR(20) NOT NULL,
    `operator`       VARCHAR(60),
    `payment_phone`  VARCHAR(30),
    `reference`      VARCHAR(80) UNIQUE,
    `provider_ref`   VARCHAR(100),
    `status`         VARCHAR(20) NOT NULL DEFAULT 'pending',
    `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status` (`status`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Compte admin par défaut : login "admin" / mdp "Admin@2024"
INSERT INTO `admins` (`username`, `password_hash`) VALUES
('admin', '$2y$12$x116Ww5yOgIMeyUKp75gtukry9Sysuzo/8UW.LyBDX6ZQbYkJcp2q')
ON DUPLICATE KEY UPDATE username = username;

-- Paramètres par défaut
INSERT INTO `settings` (`key_name`, `value`) VALUES
('goal_amount', '1000000'),
('current_project_title', 'Aide alimentaire d\'urgence — Extrême-Nord Cameroun'),
('current_project_desc',  'Distribution de repas chauds, lait infantile et kits de survie pour 12 000 enfants menacés par la famine.')
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- Données initiales : 12 pays bénéficiaires
INSERT INTO `countries` (`code`,`name`,`people_helped`,`description`) VALUES
('ET','Éthiopie',280000,'Réponse humanitaire face à une sécheresse prolongée ayant fortement affecté les cultures et l\'accès à l\'alimentation dans plusieurs régions rurales.'),
('CD','RD Congo',240000,'Soutien alimentaire et nutritionnel apporté aux populations déplacées dans l\'Est du pays, touchées par des conflits persistants.'),
('NE','Niger',220000,'Interventions d\'urgence comprenant la distribution de vivres et la prise en charge des enfants souffrant de malnutrition aiguë.'),
('TD','Tchad',210000,'Assistance alimentaire aux familles vulnérables dans le bassin du lac Tchad, confronté à des crises climatiques et sécuritaires.'),
('BF','Burkina Faso',195000,'Mise en place de centres de récupération nutritionnelle pour les enfants sévèrement malnutris dans les zones à forte insécurité.'),
('SS','Soudan du Sud',190000,'Aide alimentaire d\'urgence destinée aux communautés touchées par des épisodes de famine liés aux conflits et aux conditions climatiques extrêmes.'),
('CI','Côte d\'Ivoire',180000,'Déploiement de programmes nutritionnels dans les régions du Nord, avec distribution de vivres aux populations vulnérables.'),
('CM','Cameroun',175000,'Actions ciblées dans l\'Extrême-Nord, le Nord-Ouest et le Sud-Ouest pour soutenir les enfants et familles affectés par les crises humanitaires.'),
('ML','Mali',165000,'Interventions humanitaires dans les zones de conflit et dans les camps de déplacés internes.'),
('SO','Somalie',160000,'Aide d\'urgence dans les régions touchées par des sécheresses sévères et une insécurité alimentaire chronique.'),
('MG','Madagascar',150000,'Programmes de lutte contre la famine dans le Sud du pays, affecté par des sécheresses répétées.'),
('SN','Sénégal',135000,'Mise en place de cantines scolaires et distribution de vivres pour améliorer la sécurité alimentaire des enfants.')
ON DUPLICATE KEY UPDATE name = VALUES(name);
