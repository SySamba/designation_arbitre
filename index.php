<?php
session_start();
require_once 'config/database.php';
require_once 'classes/MatchManager.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$matchManager = new MatchManager($pdo);
$matches = $matchManager->getAllMatches();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Système de Désignation d'Arbitres - Accueil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-bg: #ecf0f1;
            --dark-bg: #2c3e50;
            --text-dark: #2c3e50;
            --text-light: #ffffff;
            --border-radius: 8px;
            --box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: rgba(44, 62, 80, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: bold;
            color: var(--text-light) !important;
        }

        .nav-link {
            color: var(--text-light) !important;
            transition: var(--transition);
        }

        .nav-link:hover {
            color: var(--accent-color) !important;
            transform: translateY(-2px);
        }

        .main-container {
            padding: 2rem 0;
        }

        .welcome-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2rem;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
        }

        .stats-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: var(--transition);
            border-left: 4px solid var(--accent-color);
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--accent-color);
        }

        .stats-label {
            color: var(--text-dark);
            font-weight: 500;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }

        .action-btn {
            background: linear-gradient(135deg, var(--accent-color), #2980b9);
            color: white;
            border: none;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            transition: var(--transition);
            box-shadow: var(--box-shadow);
            font-weight: 500;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            color: white;
            text-decoration: none;
        }

        .action-btn i {
            font-size: 1.5rem;
        }

        .matches-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2rem;
            margin-top: 2rem;
        }

        .section-title {
            color: var(--text-dark);
            font-weight: bold;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .table {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .table th {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 1rem;
            font-weight: 600;
        }

        .table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .match-teams {
            font-weight: bold;
            color: var(--text-dark);
            font-size: 1rem;
        }

        .match-info {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 4px;
        }

        .logout-btn {
            background: var(--danger-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .logout-btn:hover {
            background: #c0392b;
            color: white;
        }

        @media (max-width: 768px) {
            .action-buttons {
                grid-template-columns: 1fr;
            }
            
            .stats-card {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-whistle me-2"></i>
                Système de Désignation d'Arbitres
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-1"></i>
                    Dashboard
                </a>
                <a class="nav-link" href="arbitres.php">
                    <i class="fas fa-users me-1"></i>
                    Arbitres
                </a>
                <a class="nav-link" href="equipes.php">
                    <i class="fas fa-shield-alt me-1"></i>
                    Équipes
                </a>
                <a class="nav-link logout-btn" href="logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>
                    Déconnexion
                </a>
            </div>
        </div>
    </nav>

    <div class="container main-container">
        <!-- Section de bienvenue et statistiques -->
        <div class="welcome-card">
            <h1 class="text-center mb-4">
                <i class="fas fa-home me-2"></i>
                Bienvenue dans le Système de Désignation d'Arbitres
            </h1>
            
            <div class="row">
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <div class="stats-number"><?php echo count($matches); ?></div>
                        <div class="stats-label">Matches Programmes</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <div class="stats-number">
                            <?php 
                            $today = date('Y-m-d');
                            $todayMatches = array_filter($matches, function($match) use ($today) {
                                return $match['date_match'] == $today;
                            });
                            echo count($todayMatches);
                            ?>
                        </div>
                        <div class="stats-label">Matches Aujourd'hui</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <div class="stats-number">
                            <?php 
                            $thisWeek = array_filter($matches, function($match) {
                                $matchDate = strtotime($match['date_match']);
                                $weekStart = strtotime('monday this week');
                                $weekEnd = strtotime('sunday this week');
                                return $matchDate >= $weekStart && $matchDate <= $weekEnd;
                            });
                            echo count($thisWeek);
                            ?>
                        </div>
                        <div class="stats-label">Matches Cette Semaine</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <div class="stats-number">
                            <?php 
                            $pendingMatches = array_filter($matches, function($match) {
                                return $match['date_match'] >= date('Y-m-d');
                            });
                            echo count($pendingMatches);
                            ?>
                        </div>
                        <div class="stats-label">Matches à Venir</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Boutons d'action rapide -->
        <div class="action-buttons">
            <a href="dashboard.php" class="action-btn">
                <i class="fas fa-plus-circle"></i>
                <span>Ajouter un Match</span>
            </a>
            <a href="telecharger_multiple.php" class="action-btn">
                <i class="fas fa-download"></i>
                <span>Télécharger Désignations</span>
            </a>
            <a href="arbitres.php" class="action-btn">
                <i class="fas fa-user-plus"></i>
                <span>Gérer les Arbitres</span>
            </a>
            <a href="equipes.php" class="action-btn">
                <i class="fas fa-shield-alt"></i>
                <span>Gérer les Équipes</span>
            </a>
        </div>

        <!-- Section des matches récents -->
        <div class="matches-section">
            <h2 class="section-title">
                <i class="fas fa-calendar-alt"></i>
                Matches Récents
            </h2>
            
            <?php if (empty($matches)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Aucun match programmé pour le moment.</p>
                    <a href="dashboard.php" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        Ajouter le premier match
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Heure</th>
                                <th>Rencontre</th>
                                <th>Lieu</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Afficher les 10 derniers matches
                            $recentMatches = array_slice($matches, 0, 10);
                            foreach ($recentMatches as $match): 
                            ?>
                            <tr>
                                <td>
                                    <div class="match-info">
                                        <?php echo date('d/m/Y', strtotime($match['date_match'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="match-info">
                                        <?php echo $match['heure_match']; ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="match-teams">
                                        <?php echo htmlspecialchars($match['equipe_a_nom']); ?> 
                                        <span class="text-muted">vs</span> 
                                        <?php echo htmlspecialchars($match['equipe_b_nom']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="match-info">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?php echo htmlspecialchars($match['ville']); ?>
                                    </div>
                                    <div class="match-info">
                                        <i class="fas fa-futbol me-1"></i>
                                        <?php echo htmlspecialchars($match['stade']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="telecharger_designation.php?id=<?php echo $match['id']; ?>" 
                                           class="btn btn-sm btn-primary" 
                                           title="Télécharger">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <a href="envoyer_email.php?id=<?php echo $match['id']; ?>" 
                                           class="btn btn-sm btn-success" 
                                           title="Envoyer Email">
                                            <i class="fas fa-envelope"></i>
                                        </a>
                                        <a href="modifier_match.php?id=<?php echo $match['id']; ?>" 
                                           class="btn btn-sm btn-warning" 
                                           title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (count($matches) > 10): ?>
                    <div class="text-center mt-3">
                        <a href="dashboard.php" class="btn btn-outline-primary">
                            <i class="fas fa-list me-1"></i>
                            Voir tous les matches
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 