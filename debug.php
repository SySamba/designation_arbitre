<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug - Diagnostic du Système</h1>";

// Test 1: Vérification de PHP
echo "<h2>1. Test PHP</h2>";
echo "Version PHP: " . phpversion() . "<br>";
echo "Extensions PDO: " . (extension_loaded('pdo') ? '✅ Installée' : '❌ Non installée') . "<br>";
echo "Extensions PDO MySQL: " . (extension_loaded('pdo_mysql') ? '✅ Installée' : '❌ Non installée') . "<br>";

// Test 2: Test de connexion à la base de données
echo "<h2>2. Test de Connexion Base de Données</h2>";
try {
    require_once 'config/database.php';
    echo "✅ Connexion à la base de données réussie<br>";
    echo "Base de données: " . DB_NAME . "<br>";
    echo "Hôte: " . DB_HOST . "<br>";
    
    // Test de requête simple
    $sql = "SELECT 1 as test";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch();
    echo "✅ Requête de test réussie<br>";
    
} catch (Exception $e) {
    echo "❌ Erreur de connexion: " . $e->getMessage() . "<br>";
}

// Test 3: Vérification des fichiers
echo "<h2>3. Vérification des Fichiers</h2>";
$files = [
    'config/database.php',
    'auth_check.php',
    'login.php',
    'dashboard.php',
    'classes/MatchManager.php',
    'classes/ArbitreManager.php',
    'classes/EquipeManager.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file existe<br>";
    } else {
        echo "❌ $file n'existe pas<br>";
    }
}

// Test 4: Test des classes
echo "<h2>4. Test des Classes</h2>";
try {
    require_once 'classes/MatchManager.php';
    echo "✅ Classe MatchManager chargée<br>";
} catch (Exception $e) {
    echo "❌ Erreur MatchManager: " . $e->getMessage() . "<br>";
}

try {
    require_once 'classes/ArbitreManager.php';
    echo "✅ Classe ArbitreManager chargée<br>";
} catch (Exception $e) {
    echo "❌ Erreur ArbitreManager: " . $e->getMessage() . "<br>";
}

try {
    require_once 'classes/EquipeManager.php';
    echo "✅ Classe EquipeManager chargée<br>";
} catch (Exception $e) {
    echo "❌ Erreur EquipeManager: " . $e->getMessage() . "<br>";
}

echo "<h2>5. Liens de Test</h2>";
echo "<a href='login.php'>Test Page de Connexion</a><br>";
echo "<a href='test_access.php'>Test Accès Simple</a><br>";
?> 