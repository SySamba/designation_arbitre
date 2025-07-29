<?php
require_once 'auth_check.php';
require_once 'config/database.php';
require_once 'classes/MatchManager.php';

header('Content-Type: application/json');

$matchManager = new MatchManager($pdo);

if ($_POST) {
    $data = [
        'date_match' => $_POST['date_match'] ?? '',
        'heure_match' => $_POST['heure_match'] ?? '',
        'equipe_a_id' => $_POST['equipe_a_id'] ?? '',
        'equipe_b_id' => $_POST['equipe_b_id'] ?? '',
        'arbitre_id' => $_POST['arbitre_id'] ?? '',
        'assistant_1_id' => $_POST['assistant_1_id'] ?? '',
        'assistant_2_id' => $_POST['assistant_2_id'] ?? '',
        'officiel_4_id' => $_POST['officiel_4_id'] ?? '',
        'assesseur_id' => $_POST['assesseur_id'] ?? ''
    ];
    
    $resultat = [
        'success' => true,
        'message' => '',
        'avertissements' => []
    ];
    
    // Vérifier les conflits de rôles
    $conflits_roles = $matchManager->verifierConflitsRoles($data);
    if (!empty($conflits_roles)) {
        $resultat['success'] = false;
        $resultat['message'] = 'Erreur de conflit: ' . implode(', ', $conflits_roles);
        echo json_encode($resultat);
        exit;
    }
    
    // Vérifier les contraintes pour chaque arbitre
    $arbitres_a_verifier = [
        'arbitre_id' => 'Arbitre Principal',
        'assistant_1_id' => 'Assistant 1',
        'assistant_2_id' => 'Assistant 2',
        'officiel_4_id' => '4ème Officiel',
        'assesseur_id' => 'Assesseur'
    ];
    
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
                $resultat['success'] = false;
                $resultat['message'] = 'Erreur de contrainte: ' . implode(', ', $verification['raisons']);
                echo json_encode($resultat);
                exit;
            }
            
            // Ajouter les avertissements
            if (!empty($verification['avertissements'])) {
                $resultat['avertissements'] = array_merge($resultat['avertissements'], $verification['avertissements']);
            }
        }
    }
    
    echo json_encode($resultat);
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
?> 