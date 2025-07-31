<?php
require_once 'auth_check.php';
require_once 'config/database.php';
require_once 'classes/EquipeManager.php';

$equipeManager = new EquipeManager($pdo);
$message = '';
$message_type = '';

// Traitement de l'ajout d'équipe
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'ajouter_equipe') {
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
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Équipe - Système de Désignation d'Arbitres</title>
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
            background: linear-gradient(135deg, var(--light-bg), #e2e8f0);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: none;
            backdrop-filter: blur(10px);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            border: none;
            border-radius: var(--border-radius);
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4);
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: var(--transition);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="equipes.php">
                <i class="fas fa-arrow-left me-2"></i>
                Retour aux Équipes
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-1"></i>
                    Dashboard
                </a>
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>
                    Déconnexion
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Formulaire d'ajout d'équipe -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-plus-circle"></i> 
                            <span style="margin-left: 0.5rem;">Ajouter une Équipe</span>
                        </h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="ajouter_equipe">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="nom" class="form-label">Nom de l'équipe</label>
                                    <input type="text" class="form-control" name="nom" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="ville" class="form-label">Zone</label>
                                    <input type="text" class="form-control" name="ville" required>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save"></i> 
                                        <span style="margin-left: 0.5rem;">Ajouter l'Équipe</span>
                                    </button>
                                    <a href="equipes.php" class="btn btn-secondary btn-lg ms-3">
                                        <i class="fas fa-times"></i> 
                                        <span style="margin-left: 0.5rem;">Annuler</span>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 