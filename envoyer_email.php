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
    
    // Récupérer les emails des arbitres et assesseurs
    $emails = [];
    $noms = [];
    
    // Arbitre principal
    if ($match['arbitre_id']) {
        $arbitre = $arbitreManager->getArbitreById($match['arbitre_id']);
        if ($arbitre && $arbitre['email']) {
            $emails[] = $arbitre['email'];
            $noms[] = $arbitre['nom'] . ' ' . $arbitre['prenom'] . ' (Arbitre Principal)';
        }
    }
    
    // Assistant 1
    if ($match['assistant_1_id']) {
        $assistant1 = $arbitreManager->getArbitreById($match['assistant_1_id']);
        if ($assistant1 && $assistant1['email']) {
            $emails[] = $assistant1['email'];
            $noms[] = $assistant1['nom'] . ' ' . $assistant1['prenom'] . ' (Assistant 1)';
        }
    }
    
    // Assistant 2
    if ($match['assistant_2_id']) {
        $assistant2 = $arbitreManager->getArbitreById($match['assistant_2_id']);
        if ($assistant2 && $assistant2['email']) {
            $emails[] = $assistant2['email'];
            $noms[] = $assistant2['nom'] . ' ' . $assistant2['prenom'] . ' (Assistant 2)';
        }
    }
    
    // 4ème officiel
    if ($match['officiel_4_id']) {
        $officiel4 = $arbitreManager->getArbitreById($match['officiel_4_id']);
        if ($officiel4 && $officiel4['email']) {
            $emails[] = $officiel4['email'];
            $noms[] = $officiel4['nom'] . ' ' . $officiel4['prenom'] . ' (4ème Officiel)';
        }
    }
    
    // Assesseur
    if ($match['assesseur_id']) {
        $assesseur = $arbitreManager->getArbitreById($match['assesseur_id']);
        if ($assesseur && $assesseur['email']) {
            $emails[] = $assesseur['email'];
            $noms[] = $assesseur['nom'] . ' ' . $assesseur['prenom'] . ' (Assesseur)';
        }
    }
    
    if (empty($emails)) {
        echo json_encode(['success' => false, 'message' => 'Aucun email trouvé pour les arbitres/assesseurs']);
        exit;
    }
    
    // Préparer le contenu de l'email
    $sujet = "Désignation - " . $match['equipe_a_nom'] . " vs " . $match['equipe_b_nom'];
    
    $message = "
    <html>
    <head>
        <title>Désignation d'Arbitrage</title>
    </head>
    <body>
        <h2>FÉDÉRATION SÉNÉGALAISE DE FOOTBALL</h2>
        <h3>COMMISSION CENTRALE DES ARBITRES</h3>
        <h3>COMMISSION DE DESIGNATION S/CRA DAKAR 2025-2026</h3>
        
        <h4>DÉSIGNATION D'ARBITRAGE</h4>
        
        <table border='1' cellpadding='0' cellspacing='0' style='border-collapse: collapse; width: 100%; font-size: 12px; line-height: 1.2;'>
            <tr>
                <th style='padding: 2px; font-size: 11px;'>Date/Terrain</th>
                <th style='padding: 2px; font-size: 11px;'>Rencontre</th>
                <th style='padding: 2px; font-size: 11px;'>Arbitre/Assistants</th>
            </tr>
            <tr>
                <td style='padding: 1px; font-size: 10px;'><strong>" . date('d-m-Y', strtotime($match['date_match'])) . "</strong><br><strong>" . $match['heure_match'] . "</strong><br><strong>Ville : " . $match['ville'] . "</strong><br><strong>Stade : " . $match['stade'] . "</strong></td>
                <td style='padding: 1px; font-size: 10px;'><strong>" . $match['equipe_a_nom'] . "</strong><br><strong>VS</strong><br><strong>" . $match['equipe_b_nom'] . "</strong><br><strong>Tour : " . $match['tour'] . "</strong></td>
                <td style='padding: 1px; font-size: 10px;'>";
    
    // Ajouter les noms des officiels avec labels
    if ($match['arbitre_nom']) {
        $message .= "<strong>AR : " . $match['arbitre_nom'] . " " . $match['arbitre_prenom'] . "</strong>";
        
        // Ajouter la photo de l'arbitre principal s'il en a une
        if ($match['arbitre_id']) {
            $arbitre = $arbitreManager->getArbitreById($match['arbitre_id']);
            if ($arbitre && $arbitre['photo']) {
                $photo_path = 'photos_arbitres/' . $arbitre['photo'];
                error_log("Tentative d'ajout de photo pour arbitre ID {$match['arbitre_id']}: $photo_path");
                if (file_exists($photo_path)) {
                    $message .= " <img src='cid:arbitre_photo' style='width: 40px; height: 40px; border-radius: 50%; object-fit: cover; vertical-align: middle; margin-left: 10px; border: 2px solid #ddd;'>";
                    error_log("Photo ajoutée avec succès: $photo_path");
                } else {
                    error_log("Photo non trouvée: $photo_path");
                }
            } else {
                error_log("Arbitre ID {$match['arbitre_id']} n'a pas de photo: " . ($arbitre['photo'] ?? 'null'));
            }
        }
        $message .= "<br>";
    }
    if ($match['assistant1_nom']) {
        $message .= "<strong>AA1 : " . $match['assistant1_nom'] . " " . $match['assistant1_prenom'] . "</strong><br>";
    }
    if ($match['assistant2_nom']) {
        $message .= "<strong>AA2 : " . $match['assistant2_nom'] . " " . $match['assistant2_prenom'] . "</strong><br>";
    }
    if ($match['officiel4_nom']) {
        $message .= "<strong>4ème : " . $match['officiel4_nom'] . " " . $match['officiel4_prenom'] . "</strong><br>";
    }
    if ($match['assesseur_nom']) {
        $message .= "<strong>ASS : " . $match['assesseur_nom'] . " " . $match['assesseur_prenom'] . "</strong><br>";
    }
    
    $message .= "</td></tr></table><p><strong>Veuillez confirmer votre disponibilité.</strong></p><p>Cordialement,<br>Commission de Désignation S/CRA Dakar</p>
    </body>
    </html>";
    
    // Préparer les pièces jointes (photo de l'arbitre principal)
    $attachments = [];
    if ($match['arbitre_id']) {
        $arbitre = $arbitreManager->getArbitreById($match['arbitre_id']);
        if ($arbitre && $arbitre['photo']) {
            $photo_path = 'photos_arbitres/' . $arbitre['photo'];
            if (file_exists($photo_path)) {
                $attachments[] = [
                    'path' => $photo_path,
                    'name' => 'arbitre_photo.jpg',
                    'cid' => 'arbitre_photo'
                ];
            }
        }
    }
    
    // En-têtes pour l'email HTML avec pièces jointes
    $boundary = md5(time());
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "From: Commission de Désignation <noreply@scra-dakar.com>\r\n";
    $headers .= "Reply-To: noreply@scra-dakar.com\r\n";
    $headers .= "Content-Type: multipart/related; boundary=\"" . $boundary . "\"\r\n";
    
    // Envoyer l'email à tous les destinataires
    $success = true;
    $errors = [];
    
    foreach ($emails as $index => $email) {
        $to = $email;
        
        // Construire le message multipart avec pièces jointes
        $body = "--" . $boundary . "\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $body .= "Cher(e) " . $noms[$index] . ",\n\n" . $message . "\r\n\r\n";
        
        // Ajouter les pièces jointes
        foreach ($attachments as $attachment) {
            if (file_exists($attachment['path'])) {
                $body .= "--" . $boundary . "\r\n";
                $body .= "Content-Type: image/jpeg; name=\"" . $attachment['name'] . "\"\r\n";
                $body .= "Content-Transfer-Encoding: base64\r\n";
                $body .= "Content-ID: <" . $attachment['cid'] . ">\r\n";
                $body .= "Content-Disposition: inline; filename=\"" . $attachment['name'] . "\"\r\n\r\n";
                $body .= chunk_split(base64_encode(file_get_contents($attachment['path']))) . "\r\n";
            }
        }
        
        $body .= "--" . $boundary . "--\r\n";
        
        if (!mail($to, $sujet, $body, $headers)) {
            $success = false;
            $errors[] = "Erreur d'envoi à " . $email;
        }
    }
    
    if ($success) {
        echo json_encode([
            'success' => true, 
            'message' => 'Email envoyé avec succès à ' . count($emails) . ' destinataire(s)',
            'destinataires' => $noms
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Erreurs lors de l\'envoi : ' . implode(', ', $errors)
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}
?> 