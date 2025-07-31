<?php
require_once 'auth_check.php';
require_once 'config/database.php';
require_once 'classes/MatchManager.php';
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$matchManager = new MatchManager($pdo);

// Vérifier si des matchs ont été sélectionnés
if (!isset($_POST['match_ids']) || empty($_POST['match_ids'])) {
    header('Location: telecharger_multiple.php?error=no_selection');
    exit;
}

$match_ids = $_POST['match_ids'];

// Vérifier que tous les IDs sont valides
foreach ($match_ids as $match_id) {
    if (!is_numeric($match_id)) {
        header('Location: telecharger_multiple.php?error=invalid_id');
        exit;
    }
}

// Récupérer tous les matchs sélectionnés
$matchs = [];
foreach ($match_ids as $match_id) {
    $match = $matchManager->getMatchById($match_id);
    if ($match) {
        $matchs[] = $match;
    }
}

if (empty($matchs)) {
    header('Location: telecharger_multiple.php?error=no_valid_matches');
    exit;
}

// Configuration DOMPDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);

// Générer le contenu HTML pour tous les matchs
$html = '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Désignations d\'Arbitrage - Multiple</title>
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
            margin-bottom: 0;
            background: white;
            padding: 1px;
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
            width: 75%;
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
            font-size: 10pt;
            vertical-align: middle;
        }
        .designation-table td {
            border: 1px solid #333;
            padding: 1px;
            font-size: 9pt;
            vertical-align: middle;
            background: white;
        }

        .teams {
            font-size: 12pt;
            font-weight: bold;
            margin: 0;
            line-height: 1.1;
        }
        .date-time {
            font-size: 12pt;
            color: #333;
            font-weight: normal;
            margin: 0;
            line-height: 1.1;
        }
        .terrain {
            font-size: 12pt;
            color: #666;
            font-weight: normal;
            margin: 0;
            line-height: 1.1;
        }
        .arbitre-list {
            font-size: 12pt;
            line-height: 1.1;
        }
        .arbitre-item {
            margin-bottom: 1px;
            padding-left: 3px;
            line-height: 1.1;
            font-weight: normal;
        }
        .arbitre-item strong {
            color: black;
            margin-right: 3px;
            font-weight: normal;
        }
        .logo {
            height: 60px;
            margin-right: 10px;
        }
        .competition-title {
            margin-top: 2px;
            background: white;
            padding: 3px;
            border: 1px solid #ddd;
            font-size: 10pt;
            font-weight: bold;
            color: black;
        }
    </style>
</head>
<body>';

// En-tête unique pour tous les matchs
$html .= '
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
    <div class="competition-title">Compétition : ' . htmlspecialchars($matchs[0]['nom_competition']) . ' - Tour : ' . htmlspecialchars($matchs[0]['tour']) . '</div>
</div>

<table class="designation-table">
    <thead>
        <tr>
            <th class="date-terrain">DATES/TERRAIN</th>
            <th class="rencontre">RENCONTRE</th>
            <th class="arbitres">ARBITRE/ASSISTANTS</th>
            <th class="scra">S/CRA</th>
        </tr>
    </thead>
    <tbody>';

// Générer les lignes pour tous les matchs
foreach ($matchs as $match) {
    $html .= '
        <tr>
            <td class="date-terrain">
                <div class="date-time">' . date('d-m-Y', strtotime($match['date_match'])) . '</div>
                <div class="date-time">' . $match['heure_match'] . '</div>
                <div class="terrain">Ville : ' . htmlspecialchars($match['ville']) . '</div>
                <div class="terrain">Stade : ' . htmlspecialchars($match['stade']) . '</div>
            </td>
            <td class="rencontre">
                <div class="teams">' . htmlspecialchars($match['equipe_a_nom']) . '</div>
                <div class="teams">Vs</div>
                <div class="teams">' . htmlspecialchars($match['equipe_b_nom']) . '</div>
                <div class="terrain">Tour : ' . htmlspecialchars($match['tour']) . '</div>
            </td>
            <td class="arbitres">
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

    // Générer les noms d'arbitres avec labels
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
            <td class="scra">
                <div style="font-size: 10pt; font-weight: bold;">DAKAR</div>
            </td>
        </tr>';
}

$html .= '
    </tbody>
</table>
</body>
</html>';

// Charger le HTML dans DOMPDF
$dompdf->loadHtml($html);

// Rendre le PDF
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Générer un nom de fichier unique
$timestamp = date('Y-m-d_H-i-s');
$filename = "designations_multiple_{$timestamp}.pdf";

// Définir les en-têtes pour le téléchargement PDF
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Sortir le PDF
echo $dompdf->output();
?> 