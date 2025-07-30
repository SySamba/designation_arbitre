<?php
require_once 'auth_check.php';
require_once 'config/database.php';
require_once 'classes/MatchManager.php';
require_once 'classes/ArbitreManager.php';
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

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
    
    // Générer le PDF
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'Arial');
    
    $dompdf = new Dompdf($options);
    
    // Générer le contenu HTML pour le PDF
    $html = '
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Désignation d\'Arbitrage</title>
        <style>
            @page {
                size: A4 landscape;
                margin: 0.2cm;
            }
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 0;
                background: white;
                font-size: 10pt;
                line-height: 1.2;
            }
            .header {
                margin-bottom: 2px;
                background: white;
                padding: 2px;
                border: 1px solid #ddd;
            }
            .header h1 {
                margin: 0;
                font-size: 12pt;
                font-weight: bold;
                text-transform: uppercase;
            }
            .header h2 {
                margin: 0;
                font-size: 10pt;
                font-weight: bold;
            }
            .season {
                font-size: 10pt;
                font-weight: bold;
                margin: 0;
            }
            .designation-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 2px;
                background: white;
                border: 1px solid #333;
            }
            .designation-table th {
                border: 1px solid #333;
                padding: 0;
                background: #f8f9fa;
                color: black;
                font-weight: bold;
                font-size: 10pt;
                vertical-align: middle;
            }
            .designation-table td {
                border: 1px solid #333;
                padding: 0;
                font-size: 9pt;
                vertical-align: middle;
                background: white;
            }
            .teams {
                font-size: 12pt;
                margin: 0;
                line-height: 1;
            }
            .date-time {
                font-size: 12pt;
                color: #333;
                margin: 0;
                line-height: 1;
            }
            .terrain {
                font-size: 12pt;
                color: #666;
                margin: 0;
                line-height: 1;
            }
            .arbitre-list {
                font-size: 12pt;
                line-height: 1;
            }
            .arbitre-item {
                margin-bottom: 0;
                padding-left: 0;
                line-height: 1;
            }
            .arbitre-item strong {
                color: black;
                margin-right: 0;
                font-weight: normal;
            }
            .logo {
                height: 60px;
                margin-right: 10px;
            }
            .competition-title {
                margin-top: 0;
                background: white;
                padding: 1px;
                border: 1px solid #ddd;
                font-size: 12pt;
                font-weight: bold;
                color: black;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <div style="display: flex; align-items: center; margin-bottom: 0;">
                <img src="' . __DIR__ . '/logo.jpg" alt="Logo" class="logo">
                <div>
                    <h1>FÉDÉRATION SÉNÉGALAISE DE FOOTBALL</h1>
                    <h2>COMMISSION CENTRALE DES ARBITRES</h2>
                    <h2>COMMISSION DE DESIGNATION S/CRA DAKAR</h2>
                    <div class="season">2025-2026</div>
                </div>
            </div>
            <div class="competition-title">Compétition : ' . htmlspecialchars($match['nom_competition']) . '</div>
        </div>

        <table class="designation-table">
            <thead>
                <tr>
                    <th>DATES/TERRAIN</th>
                    <th>RENCONTRE</th>
                    <th>ARBITRE/ASSISTANTS</th>
                    <th>S/CRA</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="date-time">' . date('d-m-Y', strtotime($match['date_match'])) . '</div>
                        <div class="date-time">' . $match['heure_match'] . '</div>
                        <div class="terrain">Ville : ' . htmlspecialchars($match['ville']) . '</div>
                        <div class="terrain">Stade : ' . htmlspecialchars($match['stade']) . '</div>
                    </td>
                    <td>
                        <div class="teams">' . htmlspecialchars($match['equipe_a_nom']) . '</div>
                        <div class="teams">Vs</div>
                        <div class="teams">' . htmlspecialchars($match['equipe_b_nom']) . '</div>
                        <div class="terrain">Tour : ' . htmlspecialchars($match['tour']) . '</div>
                    </td>
                    <td>
                        <div class="arbitre-list">';

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

    foreach ($arbitres_data as $field => $arbitre) {
        $role_label = $arbitres_roles[$field];
        if ($arbitre['nom']) {
            $html .= '<div class="arbitre-item"><strong>' . $role_label . ' :</strong> ' . htmlspecialchars($arbitre['nom'] . ' ' . $arbitre['prenom']) . '</div>';
        } else {
            $html .= '<div class="arbitre-item"><strong>' . $role_label . ' :</strong> -</div>';
        }
    }

    $html .= '
                        </div>
                    </td>
                    <td>
                        <div style="font-size: 10pt; font-weight: bold;">DAKAR</div>
                    </td>
                </tr>
            </tbody>
        </table>
    </body>
    </html>';

    // Charger le HTML dans DOMPDF
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    // Générer le nom du fichier PDF
    $timestamp = date('Y-m-d_H-i-s');
    $pdf_filename = "designation_{$match_id}_{$timestamp}.pdf";
    $pdf_path = "pdfs/" . $pdf_filename;
    
    // Créer le dossier pdfs s'il n'existe pas
    if (!is_dir('pdfs')) {
        mkdir('pdfs', 0755, true);
    }
    
    // Sauvegarder le PDF
    file_put_contents($pdf_path, $dompdf->output());
    
    // Préparer le message WhatsApp
    $message = "🏆 *FÉDÉRATION SÉNÉGALAISE DE FOOTBALL*\n🏟️ *COMMISSION CENTRALE DES ARBITRES*\n📋 *COMMISSION DE DESIGNATION S/CRA DAKAR 2025-2026*\n\n*DÉSIGNATION D'ARBITRAGE*\n\n📅 *Date:* " . date('d/m/Y', strtotime($match['date_match'])) . "\n⏰ *Heure:* " . $match['heure_match'] . "\n🏙️ *Ville:* " . $match['ville'] . "\n🏟️ *Stade:* " . $match['stade'] . "\n\n⚽ *RENCONTRE:*\n" . $match['equipe_a_nom'] . " vs " . $match['equipe_b_nom'] . "\n🏆 *Tour :* " . $match['tour'] . "\n\n👨‍⚖️ *OFFICIELS DÉSIGNÉS:*\n";
    
    // Ajouter les officiels avec labels
    if ($match['arbitre_nom']) {
        $message .= "🟢 *AR:* " . $match['arbitre_nom'] . " " . $match['arbitre_prenom'] . "\n";
    }
    if ($match['assistant1_nom']) {
        $message .= "🔵 *AA1:* " . $match['assistant1_nom'] . " " . $match['assistant1_prenom'] . "\n";
    }
    if ($match['assistant2_nom']) {
        $message .= "🔵 *AA2:* " . $match['assistant2_nom'] . " " . $match['assistant2_prenom'] . "\n";
    }
    if ($match['officiel4_nom']) {
        $message .= "🟡 *4ème:* " . $match['officiel4_nom'] . " " . $match['officiel4_prenom'] . "\n";
    }
    if ($match['assesseur_nom']) {
        $message .= "🟠 *ASS:* " . $match['assesseur_nom'] . " " . $match['assesseur_prenom'] . "\n";
    }
    
    $message .= "\n✅ *Veuillez confirmer votre disponibilité.*\n\nCordialement,\nCommission de Désignation S/CRA Dakar";
    
    // Encoder le message pour l'URL WhatsApp
    $message_encoded = urlencode($message);
    
    // Générer les liens WhatsApp pour chaque destinataire
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
            'lien' => "https://wa.me/" . $numero . "?text=" . $message_encoded,
            'pdf_url' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $pdf_path
        ];
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'PDF généré et liens WhatsApp créés pour ' . count($telephones) . ' destinataire(s)',
        'destinataires' => $whatsapp_links,
        'message_text' => $message,
        'pdf_path' => $pdf_path,
        'pdf_url' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $pdf_path
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}
?> 