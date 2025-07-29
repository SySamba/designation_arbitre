<?php
require_once 'auth_check.php';
require_once 'config/database.php';
require_once 'classes/ArbitreManager.php';

$arbitreManager = new ArbitreManager($pdo);
$message = '';
$message_type = '';

// Traitement des actions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'ajouter_arbitre':
                // Gestion de l'upload de photo
                $photo = null;
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
                    $upload_dir = 'photos_arbitres/';
                    $file_extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                    
                    if (in_array($file_extension, $allowed_extensions)) {
                        $photo_name = uniqid() . '.' . $file_extension;
                        $photo_path = $upload_dir . $photo_name;
                        
                        if (move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path)) {
                            $photo = $photo_name;
                        }
                    }
                }
                
                $data = [
                    'nom' => $_POST['nom'],
                    'prenom' => $_POST['prenom'],
                    'adresse' => $_POST['adresse'],
                    'email' => $_POST['email'],
                    'telephone' => $_POST['telephone'],
                    'fonction' => $_POST['fonction'],
                    'photo' => $photo
                ];
                
                $resultat = $arbitreManager->ajouterArbitre($data);
                
                if ($resultat['success']) {
                    $message = $resultat['message'];
                    $message_type = 'success';
                } else {
                    $message = $resultat['message'];
                    $message_type = 'error';
                }
                break;
                
            case 'modifier_arbitre':
                $arbitre_id = $_POST['arbitre_id'];
                
                // Gestion de l'upload de photo
                $photo = $_POST['photo_actuelle'] ?? null;
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
                    $upload_dir = 'photos_arbitres/';
                    $file_extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                    
                    if (in_array($file_extension, $allowed_extensions)) {
                        $photo_name = uniqid() . '.' . $file_extension;
                        $photo_path = $upload_dir . $photo_name;
                        
                        if (move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path)) {
                            // Supprimer l'ancienne photo si elle existe
                            if ($photo && file_exists($upload_dir . $photo)) {
                                unlink($upload_dir . $photo);
                            }
                            $photo = $photo_name;
                        }
                    }
                }
                
                $data = [
                    'nom' => $_POST['nom'],
                    'prenom' => $_POST['prenom'],
                    'adresse' => $_POST['adresse'],
                    'email' => $_POST['email'],
                    'telephone' => $_POST['telephone'],
                    'fonction' => $_POST['fonction'],
                    'photo' => $photo
                ];
                
                $resultat = $arbitreManager->modifierArbitre($arbitre_id, $data);
                
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
    $resultat = $arbitreManager->supprimerArbitre($_GET['id']);
    
    if ($resultat['success']) {
        $message = $resultat['message'];
        $message_type = 'success';
    } else {
        $message = $resultat['message'];
        $message_type = 'error';
    }
}

// Récupération des arbitres
$arbitres = $arbitreManager->getArbitres();

// Récupération d'un arbitre pour modification
$arbitre_modifier = null;
if (isset($_GET['action']) && $_GET['action'] == 'modifier' && isset($_GET['id'])) {
    $arbitre_modifier = $arbitreManager->getArbitreById($_GET['id']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Arbitres</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --info-color: #17a2b8;
            --light-bg: #ecf0f1;
            --dark-bg: #34495e;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --border-radius: 12px;
            --box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container-fluid {
            padding: 2rem;
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
            transition: var(--transition);
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: var(--transition);
        }

        .card-header:hover::before {
            left: 100%;
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
            transition: var(--transition);
            border: none;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: var(--transition);
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2980b9, var(--secondary-color));
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.3);
        }

        .btn-secondary {
            background: linear-gradient(135deg, var(--text-light), #95a5a6);
            color: white;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #95a5a6, var(--text-light));
            transform: translateY(-2px);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color), #229954);
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #229954, var(--success-color));
            transform: translateY(-2px);
        }

        .alert {
            border-radius: var(--border-radius);
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .alert-success {
            background: linear-gradient(135deg, var(--success-color), #229954);
            color: white;
        }

        .alert-danger {
            background: linear-gradient(135deg, var(--accent-color), #c0392b);
            color: white;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: var(--transition);
            background: rgba(255, 255, 255, 0.9);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
            background: white;
        }
        
        /* Styles pour la validation du téléphone */
        .form-control.is-valid {
            border-color: var(--success-color);
            box-shadow: 0 0 0 0.2rem rgba(39, 174, 96, 0.25);
        }
        
        .form-control.is-invalid {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(231, 76, 60, 0.25);
        }
        
        .form-text {
            font-size: 0.875rem;
            color: var(--text-light);
        }

        .form-control::placeholder {
            color: var(--text-light);
        }

        .table {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            border: none;
        }

        .table th {
            font-size: 1.1rem;
            font-weight: 600;
            text-align: center;
            color: white;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 1.2rem 1rem;
            position: relative;
            overflow: hidden;
        }

        .table th::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: var(--transition);
        }

        .table th:hover::before {
            left: 100%;
        }

        .table td {
            vertical-align: middle;
            border: none;
            border-bottom: 1px solid #f0f0f0;
            padding: 1.2rem 1rem;
            font-size: 1rem;
            transition: var(--transition);
        }

        .table tbody tr {
            transition: var(--transition);
        }

        .table tbody tr:nth-of-type(odd) {
            background-color: rgba(255, 255, 255, 0.8);
        }

        .table tbody tr:nth-of-type(even) {
            background-color: rgba(248, 249, 250, 0.8);
        }

        .table tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.1);
            transform: scale(1.01);
        }

        .table tbody tr:hover td {
            border-bottom-color: var(--secondary-color);
        }

        .badge {
            font-size: 0.9rem;
            font-weight: 600;
            padding: 0.5rem 0.8rem;
            border-radius: 6px;
            transition: var(--transition);
        }

        .badge.bg-success {
            background: linear-gradient(135deg, var(--success-color), #229954) !important;
        }

        .badge.bg-secondary {
            background: linear-gradient(135deg, var(--text-light), #95a5a6) !important;
        }

        .badge.bg-primary {
            background: linear-gradient(135deg, var(--secondary-color), #2980b9) !important;
        }

        .badge.bg-warning {
            background: linear-gradient(135deg, var(--warning-color), #e67e22) !important;
        }

        .badge.bg-info {
            background: linear-gradient(135deg, var(--info-color), #138496) !important;
        }

        .btn-group-vertical .btn {
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            border-radius: 6px;
            transition: var(--transition);
        }

        .btn-group-vertical .btn:hover {
            transform: translateY(-2px);
        }

        .btn-outline-primary {
            border: 2px solid var(--secondary-color);
            color: var(--secondary-color);
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: var(--secondary-color);
            color: white;
            border-color: var(--secondary-color);
        }

        .btn-outline-info {
            border: 2px solid var(--info-color);
            color: var(--info-color);
            background: transparent;
        }

        .btn-outline-info:hover {
            background: var(--info-color);
            color: white;
            border-color: var(--info-color);
        }

        .btn-outline-danger {
            border: 2px solid var(--accent-color);
            color: var(--accent-color);
            background: transparent;
        }

        .btn-outline-danger:hover {
            background: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
        }

        /* Navigation améliorée */
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
        }

        .nav-link {
            font-weight: 500;
            transition: var(--transition);
            border-radius: 6px;
            margin: 0 0.2rem;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        /* Animations d'entrée */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            animation: fadeInUp 0.6s ease-out;
        }

        .card:nth-child(2) {
            animation-delay: 0.1s;
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
            border-top: none;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark mb-4">
            <div class="container-fluid">
                <a class="navbar-brand" href="dashboard.php">
                    <i class="fas fa-whistle"></i> 
                    <span style="margin-left: 0.5rem;">Système de Désignation d'Arbitres</span>
                </a>
                <div class="navbar-nav ms-auto">
                                                    <a class="nav-link active" href="arbitres.php">
                                    <i class="fas fa-user-tie"></i> 
                                    <span style="margin-left: 0.3rem;">Arbitres</span>
                                </a>
                                <a class="nav-link" href="equipes.php">
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

                <!-- Formulaire d'ajout/modification d'arbitre -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-<?php echo $arbitre_modifier ? 'edit' : 'plus-circle'; ?>"></i> 
                            <span style="margin-left: 0.5rem;"><?php echo $arbitre_modifier ? 'Modifier un Arbitre' : 'Ajouter un Arbitre'; ?></span>
                        </h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="<?php echo $arbitre_modifier ? 'modifier_arbitre' : 'ajouter_arbitre'; ?>">
                            <?php if ($arbitre_modifier): ?>
                                <input type="hidden" name="arbitre_id" value="<?php echo $arbitre_modifier['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="nom" class="form-label">Nom</label>
                                    <input type="text" class="form-control" name="nom" 
                                           value="<?php echo $arbitre_modifier ? $arbitre_modifier['nom'] : ''; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="prenom" class="form-label">Prénom</label>
                                    <input type="text" class="form-control" name="prenom" 
                                           value="<?php echo $arbitre_modifier ? $arbitre_modifier['prenom'] : ''; ?>" required>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" 
                                           value="<?php echo $arbitre_modifier ? $arbitre_modifier['email'] : ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="telephone" class="form-label">Téléphone (Sénégal)</label>
                                    <input type="tel" class="form-control" name="telephone" 
                                           pattern="7[0-9]{8}" 
                                           placeholder="7XXXXXXXX" 
                                           title="Numéro sénégalais: 7 suivi de 8 chiffres"
                                           value="<?php echo $arbitre_modifier ? $arbitre_modifier['telephone'] : ''; ?>">
                                    <small class="form-text text-muted">Format: 7XXXXXXXX (ex: 701234567)</small>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <label for="fonction" class="form-label">Fonction</label>
                                    <select class="form-select" name="fonction" required onchange="togglePhotoField(this)">
                                        <option value="Arbitre" <?php echo ($arbitre_modifier && $arbitre_modifier['fonction'] == 'Arbitre') ? 'selected' : ''; ?>>Arbitre</option>
                                        <option value="Assesseur" <?php echo ($arbitre_modifier && $arbitre_modifier['fonction'] == 'Assesseur') ? 'selected' : ''; ?>>Assesseur</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="photo" class="form-label">Photo (Arbitres uniquement)</label>
                                    <input type="file" class="form-control" name="photo" accept="image/*" 
                                           onchange="togglePhotoField(this)">
                                    <?php if ($arbitre_modifier && $arbitre_modifier['photo']): ?>
                                        <input type="hidden" name="photo_actuelle" value="<?php echo $arbitre_modifier['photo']; ?>">
                                        <small class="form-text text-muted">Photo actuelle: <?php echo $arbitre_modifier['photo']; ?></small>
                                    <?php endif; ?>
                                    <small class="form-text text-muted">Formats acceptés: JPG, PNG, GIF</small>
                                </div>
                                <div class="col-md-4">
                                    <label for="adresse" class="form-label">Adresse</label>
                                    <input type="text" class="form-control" name="adresse" 
                                           value="<?php echo $arbitre_modifier ? $arbitre_modifier['adresse'] : ''; ?>">
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> 
                                        <?php echo $arbitre_modifier ? 'Modifier' : 'Ajouter'; ?> l'Arbitre
                                    </button>
                                    <?php if ($arbitre_modifier): ?>
                                        <a href="arbitres.php" class="btn btn-secondary ms-2">
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

                <!-- Liste des arbitres -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-list"></i> 
                            <span style="margin-left: 0.5rem;">Liste des Arbitres</span>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Photo</th>
                                        <th>Nom</th>
                                        <th>Prénom</th>
                                        <th>Fonction</th>
                                        <th>Email</th>
                                        <th>Téléphone</th>
                                        <th>Adresse</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($arbitres as $arbitre): ?>
                                        <tr>
                                            <td>
                                                <?php if ($arbitre['fonction'] == 'Arbitre' && $arbitre['photo']): ?>
                                                    <img src="photos_arbitres/<?php echo $arbitre['photo']; ?>" 
                                                         alt="Photo de <?php echo $arbitre['nom'] . ' ' . $arbitre['prenom']; ?>" 
                                                         class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-secondary text-white rounded d-flex align-items-center justify-content-center" 
                                                         style="width: 50px; height: 50px;">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><strong><?php echo $arbitre['nom']; ?></strong></td>
                                            <td><?php echo $arbitre['prenom']; ?></td>
                                            <td>
                                                <span class="badge <?php echo $arbitre['fonction'] == 'Arbitre' ? 'bg-primary' : 'bg-warning'; ?>">
                                                    <?php echo $arbitre['fonction']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($arbitre['email']): ?>
                                                    <a href="mailto:<?php echo $arbitre['email']; ?>">
                                                        <?php echo $arbitre['email']; ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Non renseigné</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($arbitre['telephone']): ?>
                                                    <a href="tel:<?php echo $arbitre['telephone']; ?>">
                                                        <?php echo $arbitre['telephone']; ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Non renseigné</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($arbitre['adresse']): ?>
                                                    <?php echo $arbitre['adresse']; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Non renseignée</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="?action=modifier&id=<?php echo $arbitre['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <!-- <a href="historique_arbitre.php?id=<?php echo $arbitre['id']; ?>" class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-history"></i>
                                                    </a> -->
                                                    <a href="?action=supprimer&id=<?php echo $arbitre['id']; ?>" class="btn btn-sm btn-outline-danger" 
                                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet arbitre ?')">
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
    
    <script>
        // Fonction pour gérer l'affichage du champ photo selon la fonction
        function togglePhotoField(selectElement) {
            const photoField = document.querySelector('input[name="photo"]');
            const photoLabel = document.querySelector('label[for="photo"]');
            
            if (selectElement.value === 'Arbitre') {
                photoField.style.display = 'block';
                photoLabel.style.display = 'block';
                photoField.required = true;
            } else {
                photoField.style.display = 'none';
                photoLabel.style.display = 'none';
                photoField.required = false;
                photoField.value = '';
            }
        }
        
        // Validation du numéro de téléphone sénégalais
        document.addEventListener('DOMContentLoaded', function() {
            // Initialiser l'affichage du champ photo
            const fonctionSelect = document.querySelector('select[name="fonction"]');
            if (fonctionSelect) {
                togglePhotoField(fonctionSelect);
            }
            
            const telephoneInput = document.querySelector('input[name="telephone"]');
            
            if (telephoneInput) {
                telephoneInput.addEventListener('input', function(e) {
                    // Nettoyer le numéro (garder seulement les chiffres)
                    let value = e.target.value.replace(/[^0-9]/g, '');
                    
                    // Limiter à 9 chiffres
                    if (value.length > 9) {
                        value = value.substring(0, 9);
                    }
                    
                    // Mettre à jour la valeur
                    e.target.value = value;
                    
                    // Validation en temps réel
                    if (value.length > 0) {
                        if (value.length === 9 && value.startsWith('7')) {
                            e.target.classList.remove('is-invalid');
                            e.target.classList.add('is-valid');
                        } else {
                            e.target.classList.remove('is-valid');
                            e.target.classList.add('is-invalid');
                        }
                    } else {
                        e.target.classList.remove('is-valid', 'is-invalid');
                    }
                });
                
                // Validation au soumission du formulaire
                const form = telephoneInput.closest('form');
                form.addEventListener('submit', function(e) {
                    const telephone = telephoneInput.value;
                    
                    if (telephone.length > 0 && (telephone.length !== 9 || !telephone.startsWith('7'))) {
                        e.preventDefault();
                        alert('Le numéro de téléphone doit commencer par 7 et contenir exactement 9 chiffres (ex: 701234567)');
                        telephoneInput.focus();
                        return false;
                    }
                });
            }
        });
    </script>
</body>
</html> 