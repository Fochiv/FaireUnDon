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
-- Le hash bcrypt ci-dessous correspond à "Admin@2024"
INSERT INTO `admins` (`username`, `password_hash`) VALUES
('admin', '$2y$10$abcdefghijklmnopqrstuv') -- À remplacer : générer avec password_hash() au premier accès
ON DUPLICATE KEY UPDATE username = username;

-- Paramètres par défaut
INSERT INTO `settings` (`key_name`, `value`) VALUES
('goal_amount', '50000000'),
('current_project_title', 'Aide alimentaire d\'urgence — Extrême-Nord Cameroun'),
('current_project_desc',  'Distribution de repas chauds, lait infantile et kits de survie pour 12 000 enfants menacés par la famine.')
ON DUPLICATE KEY UPDATE value = VALUES(value);
