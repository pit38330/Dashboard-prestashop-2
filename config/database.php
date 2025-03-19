<?php
// Configuration de la connexion à la BDD PrestaShop
define('DB_HOST', 'HOST');
define('DB_USER', 'USER');
define('DB_PASSWORD', 'PASSWORD');
define('DB_NAME', 'NAME');
define('DB_PREFIX', 'ps_'); // Préfixe des tables PrestaShop

// Créer la connexion
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion: " . $conn->connect_error);
}

// Définir l'encodage
$conn->set_charset("utf8");
?>
