<?php
require_once 'auth_check.php';
require_once 'config/database.php';
require_once 'classes/EquipeManager.php';

$equipeManager = new EquipeManager($pdo);
$message = '';
$message_type = '';

// Traitement des actions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'ajouter_equipe':
                $data = [
                    'nom' => $_POST['nom'],
                    'ville' => $_POST['ville']
                ];
                
                $resultat = $equipeManager->ajouterEquipe($data);
                
                if ($resultat['success']) {
                    $message = $resultat['message'];
                    $message_type = 'success';
                } else {
                    $message = $resultat['message'];
                    $message_type = 'error';
                }
                break;
                
            case 'modifier_equipe':
                $equipe_id = $_POST['equipe_id'];
                $data = [
                    'nom' => $_POST['nom'],
                    'ville' => $_POST['ville']
                ];
                
                $resultat = $equipeManager->modifierEquipe($equipe_id, $data);
                
                if ($resultat['success']) {
                    $message = $resultat['message'];
                    $message_type = 'success';
                } else {
                    $message = $resultat['message'];
                    $message_type = 'error';
                }
                break;
        }
    }
}

// Traitement de la suppression
if (isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id'])) {
    $resultat = $equipeManager->supprimerEquipe($_GET['id']);
    
    if ($resultat['success']) {
        $message = $resultat['message'];
        $message_type = 'success';
    } else {
        $message = $resultat['message'];
        $message_type = 'error';
    }
}

// Récupération des équipes
$equipes = $equipeManager->getEquipes();

// Récupération d'une équipe pour modification
$equipe_modifier = null;
if (isset($_GET['action']) && $_GET['action'] == 'modifier' && isset($_GET['id'])) {
    $equipe_modifier = $equipeManager->getEquipeById($_GET['id']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Équipes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container-fluid {
            padding: 2rem;
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .card-header {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            color: white;
            border: none;
            padding: 1.5rem;
        }

        .card-header h4 {
            margin: 0;
            font-weight: 600;
            font-size: 1.3rem;
        }

        .card-body {
            padding: 2rem;
        }

        .btn {
            border-radius: 8px;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1e3a8a);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #7f8c8d, #95a5a6);
            color: white;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #95a5a6, #7f8c8d);
            transform: translateY(-2px);
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: linear-gradient(135deg, #27ae60, #229954);
            color: white;
        }

        .alert-danger {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.75rem 1rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .table th {
            font-size: 1.1rem;
            font-weight: 600;
            text-align: center;
            color: white;
            background: linear-gradient(135deg, #2c3e50, #3498db);
            border: none;
            padding: 1.2rem 1rem;
        }

        .table td {
            vertical-align: middle;
            border: none;
            border-bottom: 1px solid #f0f0f0;
            padding: 1.2rem 1rem;
            font-size: 1rem;
        }

        .table tbody tr:nth-of-type(odd) {
            background-color: rgba(255, 255, 255, 0.8);
        }

        .table tbody tr:nth-of-type(even) {
            background-color: rgba(248, 249, 250, 0.8);
        }

        .table tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.1);
        }

        .btn-outline-primary {
            border: 2px solid #3498db;
            color: #3498db;
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }

        .btn-outline-danger {
            border: 2px solid #e74c3c;
            color: #e74c3c;
            background: transparent;
        }

        .btn-outline-danger:hover {
            background: #e74c3c;
            color: white;
            border-color: #e74c3c;
        }

        /* Navigation */
        .navbar {
            background: linear-gradient(135deg, #2c3e50, #3498db) !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
        }

        .nav-link {
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 6px;
            margin: 0 0.2rem;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container-fluid {
                padding: 1rem;
            }
            
            .card-body {
                padding: 1.5rem;
            }
            
            .table th,
            .table td {
                padding: 0.8rem 0.5rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark mb-4">
            <div class="container-fluid">
                <a class="navbar-brand" href="dashboard.php">
                    <img src="logo.jpg" alt="Logo" style="height: 40px; margin-right: 10px;">
                    <span style="margin-left: 0.5rem;">Système de Désignation d'Arbitres</span>
                </a>
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="arbitres.php">
                        <i class="fas fa-user-tie"></i> 
                        <span style="margin-left: 0.3rem;">Arbitres</span>
                    </a>
                    <a class="nav-link active" href="equipes.php">
                        <i class="fas fa-users"></i> 
                        <span style="margin-left: 0.3rem;">Équipes</span>
                    </a>
                    <a class="nav-link" href="logout.php" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">
                        <i class="fas fa-sign-out-alt"></i> 
                        <span style="margin-left: 0.3rem;">Déconnexion</span>
                    </a>
                </div>
            </div>
        </nav>

        <div class="row">
            <div class="col-12">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Formulaire d'ajout/modification d'équipe -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-<?php echo $equipe_modifier ? 'edit' : 'plus-circle'; ?>"></i> 
                            <span style="margin-left: 0.5rem;"><?php echo $equipe_modifier ? 'Modifier une Équipe' : 'Ajouter une Équipe'; ?></span>
                        </h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="<?php echo $equipe_modifier ? 'modifier_equipe' : 'ajouter_equipe'; ?>">
                            <?php if ($equipe_modifier): ?>
                                <input type="hidden" name="equipe_id" value="<?php echo $equipe_modifier['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="nom" class="form-label">Nom de l'équipe</label>
                                    <input type="text" class="form-control" name="nom" 
                                           value="<?php echo $equipe_modifier ? $equipe_modifier['nom'] : ''; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="ville" class="form-label">Zone</label>
                                    <input type="text" class="form-control" name="ville" 
                                           value="<?php echo $equipe_modifier ? $equipe_modifier['ville'] : ''; ?>" required>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> 
                                        <?php echo $equipe_modifier ? 'Modifier' : 'Ajouter'; ?> l'Équipe
                                    </button>
                                    <?php if ($equipe_modifier): ?>
                                        <a href="equipes.php" class="btn btn-secondary ms-2">
                                            <i class="fas fa-times"></i> Annuler
                                        </a>
                                    <?php else: ?>
                                        <button type="reset" class="btn btn-secondary ms-2">
                                            <i class="fas fa-undo"></i> Réinitialiser
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Liste des équipes -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-list"></i> 
                            <span style="margin-left: 0.5rem;">Liste des Équipes</span>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Zone</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($equipes as $equipe): ?>
                                        <tr>
                                            <td><strong><?php echo $equipe['nom']; ?></strong></td>
                                            <td>
                                                <i class="fas fa-map-marker-alt text-primary"></i>
                                                <?php echo $equipe['ville']; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="?action=modifier&id=<?php echo $equipe['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    
                                                    <!-- <a href="historique_equipe.php?id=<?php echo $equipe['id']; ?>" class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-history"></i>
                                                    </a> -->
                                                    <a href="?action=supprimer&id=<?php echo $equipe['id']; ?>" class="btn btn-sm btn-outline-danger" 
                                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette équipe ?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 