<?php
// Garde d'authentification — à inclure en haut de chaque page admin protégée
require_once __DIR__ . '/../includes/functions.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: /admin/login.php');
    exit;
}
