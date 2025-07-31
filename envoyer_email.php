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
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 0;
                background: white;
                font-size: 12pt;
                line-height: 1.2;
            }
            .header {
                text-align: center;
                margin-bottom: 3px;
                background: white;
                padding: 5px;
                border: 1px solid #ddd;
            }
            .header h1 {
                margin: 0;
                font-size: 14pt;
                font-weight: bold;
                text-transform: uppercase;
            }
            .header h2 {
                margin: 0;
                font-size: 12pt;
                font-weight: bold;
            }
            .season {
                font-size: 12pt;
                font-weight: bold;
                text-align: center;
                margin: 2px 0;
            }
            .designation-table {
                width: 95%;
                border-collapse: collapse;
                margin-top: 2px;
                margin-left: auto;
                margin-right: auto;
                background: white;
                border: 1px solid #333;
            }
            .designation-table th {
                border: 1px solid #333;
                padding: 1px;
                background: #f8f9fa;
                color: black;
                font-weight: bold;
                text-align: center;
                font-size: 12pt;
                vertical-align: middle;
            }
            .designation-table td {
                border: 1px solid #333;
                padding: 0;
                text-align: center;
                font-size: 11pt;
                vertical-align: middle;
                background: white;
            }
            .date-terrain {
                text-align: center;
                width: 20%;
            }
            .rencontre {
                text-align: center;
                width: 30%;
            }
            .arbitres {
                text-align: center;
                width: 40%;
            }
            .scra {
                text-align: center;
                width: 10%;
            }
            .teams {
                font-weight: bold;
                font-size: 14pt;
                text-align: center;
                margin: 0;
                line-height: 1.1;
            }
            .date-time {
                font-size: 14pt;
                color: #333;
                font-weight: normal;
                text-align: center;
                margin: 0;
                line-height: 1.1;
            }
            .terrain {
                font-size: 14pt;
                color: #666;
                font-weight: normal;
                text-align: center;
                margin: 0;
                line-height: 1.1;
            }
            .arbitre-list {
                text-align: center;
                font-size: 14pt;
            }
            .arbitre-item {
                margin-bottom: 1px;
                font-weight: normal;
                text-align: left;
                padding-left: 3px;
                line-height: 1.1;
                font-size: 13pt;
            }
            .arbitre-item strong {
                color: black;
                margin-right: 3px;
                font-weight: normal;
            }
        </style>
    </head>
    <body>
        <div class='header'>
            <div style='display: flex; align-items: center; justify-content: center; margin-bottom: 3px;'>
                <div>
                    <h1>FÉDÉRATION SÉNÉGALAISE DE FOOTBALL</h1>
                    <h2>COMMISSION CENTRALE DES ARBITRES</h2>
                    <h2>COMMISSION DE DESIGNATION S/CRA DAKAR</h2>
                    <div class='season'>2025-2026</div>
                </div>
            </div>
            <div style='text-align: center; margin-top: 3px; background: white; padding: 3px; border: 1px solid #ddd;'>
                <h3 style='margin: 0; font-size: 10pt; font-weight: bold; color: black;'>Compétition : " . htmlspecialchars($match['nom_competition']) . "</h3>
            </div>
        </div>

        <table class='designation-table'>
            <thead>
                <tr>
                    <th class='date-terrain'>DATES/TERRAIN</th>
                    <th class='rencontre'>RENCONTRE</th>
                    <th class='arbitres'>ARBITRE/ASSISTANTS</th>
                    <th class='scra'>S/CRA</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class='date-terrain'>
                        <div class='date-time'>" . date('d-m-Y', strtotime($match['date_match'])) . "</div>
                        <div class='date-time'>" . $match['heure_match'] . "</div>
                        <div class='terrain'>Ville : " . htmlspecialchars($match['ville']) . "</div>
                        <div class='terrain'>Stade : " . htmlspecialchars($match['stade']) . "</div>
                    </td>
                    <td class='rencontre'>
                        <div class='teams'>" . htmlspecialchars($match['equipe_a_nom']) . "</div>
                        <div class='teams'>Vs</div>
                        <div class='teams'>" . htmlspecialchars($match['equipe_b_nom']) . "</div>
                        <div class='terrain'>Tour : " . htmlspecialchars($match['tour']) . "</div>
                    </td>
                    <td class='arbitres'>
                        <div class='arbitre-list'>";
    
    // Générer les noms d'arbitres avec labels
    $arbitres_roles = [
        'arbitre_id' => 'AR',
        'assistant_1_id' => 'AA1',
        'assistant_2_id' => 'AA2',
        'officiel_4_id' => '4ème',
        'assesseur_id' => 'ASS'
    ];

    $arbitres_data = [
        'arbitre_id' => ['nom' => $match['arbitre_nom'], 'prenom' => $match['arbitre_prenom']],
        'assistant_1_id' => ['nom' => $match['assistant1_nom'], 'prenom' => $match['assistant1_prenom']],
        'assistant_2_id' => ['nom' => $match['assistant2_nom'], 'prenom' => $match['assistant2_prenom']],
        'officiel_4_id' => ['nom' => $match['officiel4_nom'], 'prenom' => $match['officiel4_prenom']],
        'assesseur_id' => ['nom' => $match['assesseur_nom'], 'prenom' => $match['assesseur_prenom']]
    ];

    // Générer les noms d'arbitres avec labels
    foreach ($arbitres_data as $field => $arbitre) {
        $role_label = $arbitres_roles[$field];
        if ($arbitre['nom']) {
            $message .= "<div class='arbitre-item'><strong>" . $role_label . " :</strong> " . htmlspecialchars($arbitre['nom'] . ' ' . $arbitre['prenom']) . "</div>";
        } else {
            $message .= "<div class='arbitre-item'><strong>" . $role_label . " :</strong> -</div>";
        }
    }

    $message .= "
                        </div>
                    </td>
                    <td class='scra'>
                        <div style='font-size: 10pt; font-weight: bold;'>DAKAR</div>
                    </td>
                </tr>
            </tbody>
        </table>
        <p style='text-align: center; margin-top: 10px;'><strong>Veuillez confirmer votre disponibilité.</strong></p>
        <p style='text-align: center;'>Cordialement,<br>Commission de Désignation S/CRA Dakar</p>
    </body>
    </html>";
    

    
    // En-têtes pour l'email HTML
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "From: Commission de Désignation <noreply@scra-dakar.com>\r\n";
    $headers .= "Reply-To: noreply@scra-dakar.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    // Envoyer l'email à tous les destinataires
    $success = true;
    $errors = [];
    
    foreach ($emails as $index => $email) {
        $to = $email;
        
        // Construire le message HTML simple
        $body = "Cher(e) " . $noms[$index] . ",\n\n" . $message;
        
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