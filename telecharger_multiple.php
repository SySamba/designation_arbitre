<?php
require_once 'auth_check.php';
require_once 'config/database.php';
require_once 'classes/MatchManager.php';

$matchManager = new MatchManager($pdo);

// Récupérer tous les tours disponibles
$tours = $matchManager->getTours();

// Récupérer les matchs du tour sélectionné
$matchs = [];
$tour_selectionne = '';
if (isset($_GET['tour']) && !empty($_GET['tour'])) {
    $tour_selectionne = $_GET['tour'];
    $matchs = $matchManager->getMatchsByTour($tour_selectionne);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Téléchargement Multiple - Système de Désignation d'Arbitres</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e3a8a;
            --secondary-color: #3b82f6;
            --accent-color: #dc2626;
            --success-color: #059669;
            --warning-color: #d97706;
            --info-color: #0891b2;
            --light-bg: #f1f5f9;
            --dark-bg: #1e293b;
            --text-dark: #1e293b;
            --text-light: #64748b;
            --border-radius: 12px;
            --box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            background: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-dark);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
            box-shadow: var(--box-shadow);
        }

        .header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: none;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, var(--info-color), var(--secondary-color));
            color: white;
            padding: 20px;
            border: none;
        }

        .card-body {
            padding: 25px;
        }

        .form-select {
            border-radius: var(--border-radius);
            border: 2px solid #e2e8f0;
            padding: 12px 15px;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        }

        .btn {
            border-radius: var(--border-radius);
            padding: 12px 25px;
            font-weight: 600;
            transition: var(--transition);
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color), #10b981);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .table {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
        }

        .table th {
            background: var(--dark-bg);
            color: white;
            border: none;
            padding: 15px;
            font-weight: 600;
        }

        .table td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f8fafc;
        }

        .form-check-input {
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }

        .form-check-input:checked {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }

        .match-info {
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .match-teams {
            font-weight: 600;
            color: var(--text-dark);
        }

        .match-date {
            font-weight: 600;
            color: var(--primary-color);
        }

        .back-btn {
            background: var(--text-light);
            color: white;
            margin-bottom: 20px;
        }

        .back-btn:hover {
            background: var(--text-dark);
            color: white;
        }

        .selection-info {
            background: linear-gradient(135deg, var(--info-color), var(--secondary-color));
            color: white;
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }

        .no-matches {
            text-align: center;
            padding: 50px;
            color: var(--text-light);
        }

        .no-matches i {
            font-size: 3rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-download me-3"></i>Téléchargement Multiple</h1>
            <p>Sélectionnez un tour et choisissez les matchs à télécharger</p>
        </div>

        <!-- Bouton retour -->
        <a href="dashboard.php" class="btn back-btn">
            <i class="fas fa-arrow-left me-2"></i>Retour au Dashboard
        </a>

        <!-- Formulaire de sélection du tour -->
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-filter me-2"></i>Sélection du Tour</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="" id="tourForm">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="tour" class="form-label">Sélectionner un tour :</label>
                            <select name="tour" id="tour" class="form-select" required>
                                <option value="">-- Choisir un tour --</option>
                                <?php foreach ($tours as $tour): ?>
                                    <option value="<?= htmlspecialchars($tour['tour']) ?>" 
                                            <?= ($tour_selectionne == $tour['tour']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tour['tour']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Afficher les matchs
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php if (!empty($tour_selectionne)): ?>
            <!-- Informations de sélection -->
            <div class="selection-info">
                <h4><i class="fas fa-info-circle me-2"></i>Tour sélectionné : <?= htmlspecialchars($tour_selectionne) ?></h4>
                <p class="mb-0">Sélectionnez les matchs que vous souhaitez télécharger</p>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php 
                    switch ($_GET['error']) {
                        case 'no_selection':
                            echo 'Aucun match sélectionné. Veuillez sélectionner au moins un match.';
                            break;
                        case 'invalid_id':
                            echo 'ID de match invalide.';
                            break;
                        case 'no_valid_matches':
                            echo 'Aucun match valide trouvé.';
                            break;
                        default:
                            echo 'Une erreur est survenue.';
                    }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (empty($matchs)): ?>
                <!-- Aucun match trouvé -->
                <div class="card">
                    <div class="card-body">
                        <div class="no-matches">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h4>Aucun match trouvé</h4>
                            <p>Aucun match n'a été programmé pour le tour "<?= htmlspecialchars($tour_selectionne) ?>"</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Liste des matchs -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="mb-0"><i class="fas fa-list me-2"></i>Matchs du Tour</h3>
                        <div>
                            <button type="button" class="btn btn-outline-light me-2" onclick="selectAll()">
                                <i class="fas fa-check-double me-1"></i>Tout sélectionner
                            </button>
                            <button type="button" class="btn btn-outline-light" onclick="deselectAll()">
                                <i class="fas fa-times me-1"></i>Tout désélectionner
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="downloadForm" action="telecharger_multiple_pdf.php" method="POST">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="50">
                                                <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleAll()">
                                            </th>
                                            <th>Date/Heure</th>
                                            <th>Rencontre</th>
                                            <th>Lieu</th>
                                            <th>Arbitres</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($matchs as $match): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" class="form-check-input match-checkbox" 
                                                           name="match_ids[]" value="<?= $match['id'] ?>">
                                                </td>
                                                <td>
                                                    <div class="match-date">
                                                        <?= date('d/m/Y', strtotime($match['date_match'])) ?>
                                                    </div>
                                                    <div class="match-info">
                                                        <?= $match['heure_match'] ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="match-teams">
                                                        <?= htmlspecialchars($match['equipe_a_nom']) ?> vs <?= htmlspecialchars($match['equipe_b_nom']) ?>
                                                    </div>
                                                    <div class="match-info">
                                                        <?= htmlspecialchars($match['nom_competition']) ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="match-info">
                                                        <strong>Ville :</strong> <?= htmlspecialchars($match['ville']) ?>
                                                    </div>
                                                    <div class="match-info">
                                                        <strong>Stade :</strong> <?= htmlspecialchars($match['stade']) ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if ($match['arbitre_nom']): ?>
                                                        <div class="match-info">
                                                            <strong>AR:</strong> <?= htmlspecialchars($match['arbitre_nom'] . ' ' . $match['arbitre_prenom']) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if ($match['assistant1_nom']): ?>
                                                        <div class="match-info">
                                                            <strong>AA1:</strong> <?= htmlspecialchars($match['assistant1_nom'] . ' ' . $match['assistant1_prenom']) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if ($match['assistant2_nom']): ?>
                                                        <div class="match-info">
                                                            <strong>AA2:</strong> <?= htmlspecialchars($match['assistant2_nom'] . ' ' . $match['assistant2_prenom']) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if ($match['officiel4_nom']): ?>
                                                        <div class="match-info">
                                                            <strong>4ème:</strong> <?= htmlspecialchars($match['officiel4_nom'] . ' ' . $match['officiel4_prenom']) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if ($match['assesseur_nom']): ?>
                                                        <div class="match-info">
                                                            <strong>ASS:</strong> <?= htmlspecialchars($match['assesseur_nom'] . ' ' . $match['assesseur_prenom']) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= $match['statut'] == 'Programmé' ? 'primary' : ($match['statut'] == 'En cours' ? 'warning' : ($match['statut'] == 'Terminé' ? 'success' : 'secondary')) ?>">
                                                        <?= $match['statut'] ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-success btn-lg" id="downloadBtn" disabled>
                                    <i class="fas fa-download me-2"></i>Télécharger les matchs sélectionnés
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fonction pour sélectionner tous les matchs
        function selectAll() {
            document.querySelectorAll('.match-checkbox').forEach(checkbox => {
                checkbox.checked = true;
            });
            document.getElementById('selectAll').checked = true;
            updateDownloadButton();
        }

        // Fonction pour désélectionner tous les matchs
        function deselectAll() {
            document.querySelectorAll('.match-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            document.getElementById('selectAll').checked = false;
            updateDownloadButton();
        }

        // Fonction pour basculer la sélection de tous les matchs
        function toggleAll() {
            const selectAllCheckbox = document.getElementById('selectAll');
            const matchCheckboxes = document.querySelectorAll('.match-checkbox');
            
            matchCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            
            updateDownloadButton();
        }

        // Fonction pour mettre à jour l'état du bouton de téléchargement
        function updateDownloadButton() {
            const checkedBoxes = document.querySelectorAll('.match-checkbox:checked');
            const downloadBtn = document.getElementById('downloadBtn');
            
            if (checkedBoxes.length > 0) {
                downloadBtn.disabled = false;
                downloadBtn.innerHTML = `<i class="fas fa-download me-2"></i>Télécharger ${checkedBoxes.length} match(s) sélectionné(s)`;
            } else {
                downloadBtn.disabled = true;
                downloadBtn.innerHTML = `<i class="fas fa-download me-2"></i>Télécharger les matchs sélectionnés`;
            }
        }

        // Écouter les changements sur les checkboxes
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.match-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', updateDownloadButton);
            });
            
            // Mettre à jour l'état initial du bouton
            updateDownloadButton();
        });

        // Validation du formulaire
        document.getElementById('downloadForm').addEventListener('submit', function(e) {
            const checkedBoxes = document.querySelectorAll('.match-checkbox:checked');
            
            if (checkedBoxes.length === 0) {
                e.preventDefault();
                alert('Veuillez sélectionner au moins un match à télécharger.');
                return false;
            }
        });
    </script>
</body>
</html> 