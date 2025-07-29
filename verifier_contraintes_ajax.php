<?php
require_once 'config/database.php';
require_once 'classes/MatchManager.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$matchManager = new MatchManager($pdo);

// Récupérer les données du formulaire
$data = [
    'ligue_id' => $_POST['ligue_id'] ?? 1,
    'nom_competition' => $_POST['nom_competition'] ?? '',
    'ville' => $_POST['ville'] ?? '',
    'stade' => $_POST['stade'] ?? '',
    'tour' => $_POST['tour'] ?? '',
    'date_match' => $_POST['date_match'] ?? '',
    'heure_match' => $_POST['heure_match'] ?? '',
    'equipe_a_id' => $_POST['equipe_a_id'] ?? '',
    'equipe_b_id' => $_POST['equipe_b_id'] ?? '',
    'arbitre_id' => $_POST['arbitre_id'] ?: null,
    'assistant_1_id' => $_POST['assistant_1_id'] ?: null,
    'assistant_2_id' => $_POST['assistant_2_id'] ?: null,
    'officiel_4_id' => $_POST['officiel_4_id'] ?: null,
    'assesseur_id' => $_POST['assesseur_id'] ?: null,
    'publier' => $_POST['publier'] ?? 'Non'
];

// Vérifier les conflits de rôles
$conflits_roles = $matchManager->verifierConflitsRoles($data);
if (!empty($conflits_roles)) {
    echo json_encode([
        'success' => false,
        'message' => implode(', ', $conflits_roles)
    ]);
    exit;
}

// Vérifier les contraintes de disponibilité
$arbitres_a_verifier = [
    'arbitre_id' => 'Arbitre Principal',
    'assistant_1_id' => 'Assistant 1',
    'assistant_2_id' => 'Assistant 2',
    'officiel_4_id' => '4ème Officiel',
    'assesseur_id' => 'Assesseur'
];

$erreurs = [];
$avertissements = [];

foreach ($arbitres_a_verifier as $field => $role) {
    if ($data[$field]) {
        $verification = $matchManager->verifierDisponibiliteArbitre(
            $data[$field], 
            0, 
            $role, 
            $data['date_match'], 
            $data['heure_match'], 
            $data['equipe_a_id'], 
            $data['equipe_b_id']
        );
        
        if (!$verification['disponible']) {
            $erreurs = array_merge($erreurs, $verification['raisons']);
        }
        
        if (!empty($verification['avertissements'])) {
            $avertissements = array_merge($avertissements, $verification['avertissements']);
        }
    }
}

// Supprimer les doublons
$erreurs = array_unique($erreurs);
$avertissements = array_unique($avertissements);

if (!empty($erreurs)) {
    echo json_encode([
        'success' => false,
        'message' => implode(', ', $erreurs)
    ]);
} else {
    $response = [
        'success' => true,
        'message' => 'Contraintes vérifiées avec succès'
    ];
    
    if (!empty($avertissements)) {
        $response['avertissements'] = $avertissements;
        $response['message'] .= ' (avec avertissements)';
    }
    
    echo json_encode($response);
}
?> 