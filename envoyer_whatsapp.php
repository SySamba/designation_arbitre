<?php
require_once 'auth_check.php';
require_once 'config/database.php';
require_once 'classes/MatchManager.php';
require_once 'classes/ArbitreManager.php';

header('Content-Type: application/json');

// Récupérer les données JSON
$input = json_decode(file_get_contents('php://input'), true);
$match_id = $input['match_id'] ?? null;

if (!$match_id) {
    echo json_encode(['success' => false, 'message' => 'ID du match manquant']);
    exit;
}

try {
    $matchManager = new MatchManager($pdo);
    $arbitreManager = new ArbitreManager($pdo);
    
    // Récupérer les détails du match
    $match = $matchManager->getMatchById($match_id);
    
    if (!$match) {
        echo json_encode(['success' => false, 'message' => 'Match non trouvé']);
        exit;
    }
    
    // Récupérer les numéros de téléphone des arbitres et assesseurs
    $telephones = [];
    $noms = [];
    
    // Arbitre principal
    if ($match['arbitre_id']) {
        $arbitre = $arbitreManager->getArbitreById($match['arbitre_id']);
        if ($arbitre && $arbitre['telephone']) {
            $telephones[] = $arbitre['telephone'];
            $noms[] = $arbitre['nom'] . ' ' . $arbitre['prenom'] . ' (Arbitre Principal)';
        }
    }
    
    // Assistant 1
    if ($match['assistant_1_id']) {
        $assistant1 = $arbitreManager->getArbitreById($match['assistant_1_id']);
        if ($assistant1 && $assistant1['telephone']) {
            $telephones[] = $assistant1['telephone'];
            $noms[] = $assistant1['nom'] . ' ' . $assistant1['prenom'] . ' (Assistant 1)';
        }
    }
    
    // Assistant 2
    if ($match['assistant_2_id']) {
        $assistant2 = $arbitreManager->getArbitreById($match['assistant_2_id']);
        if ($assistant2 && $assistant2['telephone']) {
            $telephones[] = $assistant2['telephone'];
            $noms[] = $assistant2['nom'] . ' ' . $assistant2['prenom'] . ' (Assistant 2)';
        }
    }
    
    // 4ème officiel
    if ($match['officiel_4_id']) {
        $officiel4 = $arbitreManager->getArbitreById($match['officiel_4_id']);
        if ($officiel4 && $officiel4['telephone']) {
            $telephones[] = $officiel4['telephone'];
            $noms[] = $officiel4['nom'] . ' ' . $officiel4['prenom'] . ' (4ème Officiel)';
        }
    }
    
    // Assesseur
    if ($match['assesseur_id']) {
        $assesseur = $arbitreManager->getArbitreById($match['assesseur_id']);
        if ($assesseur && $assesseur['telephone']) {
            $telephones[] = $assesseur['telephone'];
            $noms[] = $assesseur['nom'] . ' ' . $assesseur['prenom'] . ' (Assesseur)';
        }
    }
    
    if (empty($telephones)) {
        echo json_encode(['success' => false, 'message' => 'Aucun numéro de téléphone trouvé pour les arbitres/assesseurs']);
        exit;
    }
    
    // Préparer le message WhatsApp
    $message = "FEDERATION SENEGALAISE DE FOOTBALL\nCOMMISSION CENTRALE DES ARBITRES\nCOMMISSION DE DESIGNATION S/CRA DAKAR 2025-2026\n\nDESIGNATION D'ARBITRAGE\n\nDate: " . date('d/m/Y', strtotime($match['date_match'])) . "\nHeure: " . $match['heure_match'] . "\nVille: " . $match['ville'] . "\nStade: " . $match['stade'] . "\n\nRENCONTRE:\n" . $match['equipe_a_nom'] . " vs " . $match['equipe_b_nom'] . "\nTour: " . $match['tour'] . "\n\nOFFICIELS DESIGNES:\n";
    
    // Ajouter les officiels avec labels
    if ($match['arbitre_nom']) {
        $message .= "AR: " . $match['arbitre_nom'] . " " . $match['arbitre_prenom'] . "\n";
    }
    if ($match['assistant1_nom']) {
        $message .= "AA1: " . $match['assistant1_nom'] . " " . $match['assistant1_prenom'] . "\n";
    }
    if ($match['assistant2_nom']) {
        $message .= "AA2: " . $match['assistant2_nom'] . " " . $match['assistant2_prenom'] . "\n";
    }
    if ($match['officiel4_nom']) {
        $message .= "4eme: " . $match['officiel4_nom'] . " " . $match['officiel4_prenom'] . "\n";
    }
    if ($match['assesseur_nom']) {
        $message .= "ASS: " . $match['assesseur_nom'] . " " . $match['assesseur_prenom'] . "\n";
    }
    
    $message .= "\nVeuillez confirmer votre disponibilite.\n\nCordialement,\nCommission de Designation S/CRA Dakar";
    
    // Encoder le message pour l'URL WhatsApp
    $message_encoded = urlencode($message);
    
    // Créer des liens WhatsApp individuels pour chaque destinataire
    $whatsapp_links = [];
    foreach ($telephones as $index => $telephone) {
        // Formater le numéro pour WhatsApp (ajouter l'indicatif du Sénégal si nécessaire)
        $numero = $telephone;
        if (strlen($numero) == 9 && $numero[0] == '7') {
            $numero = '221' . $numero; // Indicatif Sénégal
        }
        
        $whatsapp_links[] = [
            'numero' => $numero,
            'nom' => $noms[$index],
            'lien' => "https://wa.me/" . $numero . "?text=" . $message_encoded
        ];
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Liens WhatsApp générés pour ' . count($telephones) . ' destinataire(s)',
        'destinataires' => $whatsapp_links,
        'message_text' => $message
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}
?> 