<?php
require_once 'auth_check.php';
require_once 'config/database.php';
require_once 'classes/ArbitreManager.php';

$arbitreManager = new ArbitreManager($pdo);
$message = '';
$message_type = '';

// Traitement de l'ajout d'arbitre
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'ajouter_arbitre') {
    // Gestion de l'upload de photo
    $photo = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $upload_dir = 'photos_arbitres/';
        
        // Créer le dossier s'il n'existe pas
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
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
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Arbitre - Système de Désignation d'Arbitres</title>
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

        .photo-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid var(--secondary-color);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="arbitres.php">
                <i class="fas fa-arrow-left me-2"></i>
                Retour aux Arbitres
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-home me-1"></i>
                    Accueil
                </a>
                <a class="nav-link" href="arbitres.php">
                    <i class="fas fa-user-tie me-1"></i>
                    Arbitres
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
            <div class="col-lg-8">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Formulaire d'ajout d'arbitre -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-plus-circle"></i> 
                            <span style="margin-left: 0.5rem;">Ajouter un Arbitre</span>
                        </h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="ajouter_arbitre">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="nom" class="form-label">Nom</label>
                                    <input type="text" class="form-control" name="nom" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="prenom" class="form-label">Prénom</label>
                                    <input type="text" class="form-control" name="prenom" required>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email">
                                </div>
                                <div class="col-md-6">
                                    <label for="telephone" class="form-label">Téléphone (Sénégal)</label>
                                    <input type="tel" class="form-control" name="telephone" 
                                           pattern="7[0-9]{8}" 
                                           placeholder="7XXXXXXXX" 
                                           title="Numéro sénégalais: 7 suivi de 8 chiffres">
                                    <small class="form-text text-muted">Format: 7XXXXXXXX (ex: 701234567)</small>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <label for="fonction" class="form-label">Fonction</label>
                                    <select class="form-select" name="fonction" required onchange="togglePhotoField(this)">
                                        <option value="">Sélectionner une fonction...</option>
                                        <option value="Arbitre">Arbitre</option>
                                        <option value="Assesseur">Assesseur</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="photo" class="form-label">Photo (Arbitres uniquement)</label>
                                    <input type="file" class="form-control" name="photo" accept="image/*" 
                                           onchange="previewPhoto(this)" id="photoField" disabled>
                                    <small class="form-text text-muted">Formats acceptés: JPG, PNG, GIF</small>
                                    <div id="photoPreview" class="mt-2" style="display: none;">
                                        <img id="previewImg" class="photo-preview" alt="Aperçu">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="adresse" class="form-label">Adresse</label>
                                    <input type="text" class="form-control" name="adresse">
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save"></i> 
                                        <span style="margin-left: 0.5rem;">Ajouter l'Arbitre</span>
                                    </button>
                                    <a href="arbitres.php" class="btn btn-secondary btn-lg ms-3">
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
    <script>
        function togglePhotoField(select) {
            const photoField = document.getElementById('photoField');
            if (select.value === 'Arbitre') {
                photoField.disabled = false;
            } else {
                photoField.disabled = true;
                photoField.value = '';
                document.getElementById('photoPreview').style.display = 'none';
            }
        }

        function previewPhoto(input) {
            const preview = document.getElementById('photoPreview');
            const previewImg = document.getElementById('previewImg');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html> 