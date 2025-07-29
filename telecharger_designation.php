<?php
require_once 'auth_check.php';
require_once 'config/database.php';
require_once 'classes/MatchManager.php';

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

// Générer le contenu HTML du PDF
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
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-size: 10pt;
            line-height: 1.2;
            min-height: 100vh;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
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
        .header h3 {
            margin: 2px 0 0 0;
            font-size: 10pt;
            font-weight: normal;
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
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        .designation-table th {
            border: 1px solid #2c3e50;
            padding: 12px 8px;
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 11pt;
            vertical-align: middle;
            position: relative;
            overflow: hidden;
        }
        .designation-table td {
            border: 1px solid #e0e0e0;
            padding: 10px 8px;
            text-align: center;
            font-size: 11pt;
            vertical-align: middle;
            background: rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
        }
        .date-terrain {
            text-align: center;
            width: 15%;
        }
        .rencontre {
            text-align: center;
            width: 25%;
        }
        .off {
            text-align: center;
            width: 15%;
        }
        .arbitres {
            text-align: center;
            width: 35%;
        }
        .scra {
            text-align: center;
            width: 10%;
        }
        .scra div {
            font-size: 10pt;
            font-weight: bold;
            text-align: center;
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
        .off-badges {
            display: flex;
            flex-direction: column;
            gap: 3px;
            align-items: center;
        }
        .off-badge {
            font-size: 10pt;
            font-weight: bold;
        }
        .arbitre-list {
            text-align: center;
            font-size: 12pt;
        }
        .arbitre-item {
            margin-bottom: 4px;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
            <img src="logo.jpg" alt="Logo" style="height: 60px; margin-right: 15px;">
            <div>
                <h1>FÉDÉRATION SÉNÉGALAISE DE FOOTBALL</h1>
                <h2>COMMISSION CENTRALE DES ARBITRES</h2>
                <h2>COMMISSION DE DESIGNATION S/CRA DAKAR</h2>
                <div class="season">2025-2026</div>
            </div>
        </div>
    </div>

    <table class="designation-table">
        <thead>
            <tr>
                <th class="date-terrain">DATES/TERRAIN</th>
                <th class="rencontre">RENCONTRE</th>
                <th class="off">OFF.</th>
                <th class="arbitres">ARBITRE/ASSISTANTS</th>
                <th class="scra">S/CRA</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="date-terrain">
                    <div class="date-time">' . date('d-m-Y', strtotime($match['date_match'])) . '</div>
                    <div class="date-time">' . $match['heure_match'] . '</div>
                    <div class="terrain">' . htmlspecialchars($match['stade']) . '</div>
                </td>
                <td class="rencontre">
                    <div class="teams">' . htmlspecialchars($match['equipe_a_nom']) . '</div>
                    <div class="teams">Vs</div>
                    <div class="teams">' . htmlspecialchars($match['equipe_b_nom']) . '</div>
                </td>
                <td class="off">
                    <div class="off-badges">';

// Générer les badges OFF et les noms d'arbitres
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

// Générer les badges OFF
foreach ($arbitres_roles as $field => $role) {
    $html .= '<div class="off-badge">' . $role . '</div>';
}

$html .= '
                    </div>
                </td>
                <td class="arbitres">
                    <div class="arbitre-list">';

// Générer les noms d'arbitres
foreach ($arbitres_data as $field => $arbitre) {
    if ($arbitre['nom']) {
        $html .= '<div class="arbitre-item">' . htmlspecialchars($arbitre['nom'] . ' ' . $arbitre['prenom']) . '</div>';
    } else {
        $html .= '<div class="arbitre-item">-</div>';
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

// Définir les en-têtes pour le téléchargement
header('Content-Type: text/html; charset=utf-8');
header('Content-Disposition: attachment; filename="designation_' . $match_id . '.html"');

// Afficher le contenu HTML
echo $html;
?> 