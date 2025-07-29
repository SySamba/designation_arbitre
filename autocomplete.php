<?php
require_once 'config/database.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? '';
$term = $_GET['term'] ?? '';

if (empty($type)) {
    echo json_encode([]);
    exit;
}

// Si pas de terme, on récupère toutes les données
if (empty($term)) {
    $term = '%';
} else {
    $term = '%' . $term . '%';
}
$results = [];

try {
    if ($type === 'arbitres') {
        $sql = "SELECT id, nom, prenom FROM arbitres 
                WHERE fonction = 'Arbitre' AND (nom LIKE ? OR prenom LIKE ?) 
                ORDER BY nom, prenom 
                LIMIT 50";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$term, $term]);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = [
                'id' => $row['id'],
                'value' => $row['nom'] . ' ' . $row['prenom'],
                'label' => $row['nom'] . ' ' . $row['prenom']
            ];
        }
    } elseif ($type === 'assesseurs') {
        $sql = "SELECT id, nom, prenom FROM arbitres 
                WHERE fonction = 'Assesseur' AND (nom LIKE ? OR prenom LIKE ?) 
                ORDER BY nom, prenom 
                LIMIT 50";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$term, $term]);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = [
                'id' => $row['id'],
                'value' => $row['nom'] . ' ' . $row['prenom'],
                'label' => $row['nom'] . ' ' . $row['prenom']
            ];
        }
    } elseif ($type === 'equipes') {
        $sql = "SELECT id, nom, ville FROM equipes 
                WHERE nom LIKE ? OR ville LIKE ? 
                ORDER BY nom, ville 
                LIMIT 50";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$term, $term]);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = [
                'id' => $row['id'],
                'value' => $row['nom'] . ' (' . $row['ville'] . ')',
                'label' => $row['nom'] . ' (' . $row['ville'] . ')'
            ];
        }
    }
} catch (Exception $e) {
    $results = [];
}

echo json_encode($results);
?> 