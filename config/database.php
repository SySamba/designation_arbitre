<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'u588247422_arbitre');
define('DB_USER', 'u588247422_arbitre');
define('DB_PASS', 'Dakar2025@');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?> 