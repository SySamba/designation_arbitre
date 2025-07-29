<?php
require_once 'auth_check.php';
require_once 'config/database.php';
require_once 'classes/MatchManager.php';
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$matchManager = new MatchManager($pdo);

$match_id = $_GET['id'] ?? null;
if (!$match_id) {
    header('Location: dashboard.php');
    exit;
}

$match = $matchManager->getMatchById($match_id);
if (!$match) {
    header('Location: dashboard.php');
    exit;
}

// Configuration DOMPDF
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
            margin: 1.5cm;
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
            text-align: center;
            margin-bottom: 15px;
            background: white;
            padding: 20px;
            border: 1px solid #ddd;
        }
        .header h1 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 2px 0 0 0;
            font-size: 12pt;
            font-weight: bold;
        }
        .season {
            font-size: 12pt;
            font-weight: bold;
            text-align: center;
            margin: 10px 0;
        }
        .designation-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
            border: 1px solid #333;
        }
        .designation-table th {
            border: 1px solid #333;
            padding: 6px 4px;
            background: #f8f9fa;
            color: black;
            font-weight: bold;
            text-align: center;
            font-size: 10pt;
            vertical-align: middle;
        }
        .designation-table td {
            border: 1px solid #333;
            padding: 4px 3px;
            text-align: center;
            font-size: 9pt;
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
            font-size: 12pt;
            text-align: center;
        }
        .date-time {
            font-size: 12pt;
            color: #333;
            font-weight: bold;
            text-align: center;
        }
        .terrain {
            font-size: 12pt;
            color: #666;
            font-weight: bold;
            text-align: center;
        }
        .arbitre-list {
            text-align: center;
            font-size: 12pt;
        }
        .arbitre-item {
            margin-bottom: 6px;
            font-weight: bold;
            text-align: left;
            padding-left: 10px;
        }
        .arbitre-item strong {
            color: black;
            margin-right: 8px;
        }
        .logo {
            height: 60px;
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
            <img src="' . __DIR__ . '/logo.jpg" alt="Logo" class="logo">
            <div>
                <h1>FÉDÉRATION SÉNÉGALAISE DE FOOTBALL</h1>
                <h2>COMMISSION CENTRALE DES ARBITRES</h2>
                <h2>COMMISSION DE DESIGNATION S/CRA DAKAR</h2>
                <div class="season">2025-2026</div>
            </div>
        </div>
        <div style="text-align: center; margin-top: 15px; background: white; padding: 10px; border: 1px solid #ddd;">
            <h3 style="margin: 0; font-size: 12pt; font-weight: bold; color: black;">Compétition : ' . htmlspecialchars($match['nom_competition']) . '</h3>
        </div>
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
        <tbody>
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
            </tr>
        </tbody>
    </table>
</body>
</html>';

// Charger le HTML dans DOMPDF
$dompdf->loadHtml($html);

// Rendre le PDF
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Définir les en-têtes pour le téléchargement PDF
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="designation_' . $match_id . '.pdf"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Sortir le PDF
echo $dompdf->output();
?> 