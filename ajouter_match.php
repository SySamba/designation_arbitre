<?php
require_once 'auth_check.php';
require_once 'config/database.php';
require_once 'classes/MatchManager.php';
require_once 'classes/ArbitreManager.php';
require_once 'classes/EquipeManager.php';

$matchManager = new MatchManager($pdo);
$arbitreManager = new ArbitreManager($pdo);
$equipeManager = new EquipeManager($pdo);

$message = '';
$message_type = '';

// Traitement de l'ajout de match
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'ajouter_match') {
    $data = [
        'ligue_id' => $_POST['ligue_id'] ?? 1,
        'nom_competition' => $_POST['nom_competition'],
        'ville' => $_POST['ville'],
        'stade' => $_POST['stade'],
        'tour' => $_POST['tour'],
        'date_match' => $_POST['date_match'],
        'heure_match' => $_POST['heure_match'],
        'equipe_a_id' => $_POST['equipe_a_id'],
        'equipe_b_id' => $_POST['equipe_b_id'],
        'arbitre_id' => $_POST['arbitre_id'] ?: null,
        'assistant_1_id' => $_POST['assistant_1_id'] ?: null,
        'assistant_2_id' => $_POST['assistant_2_id'] ?: null,
        'officiel_4_id' => $_POST['officiel_4_id'] ?: null,
        'assesseur_id' => $_POST['assesseur_id'] ?: null,
        'publier' => $_POST['publier']
    ];
    
    $resultat = $matchManager->ajouterMatch($data);
    
    if ($resultat['success']) {
        $message = $resultat['message'];
        $message_type = 'success';
    } else {
        $message = $resultat['message'];
        $message_type = 'error';
    }
}

// Récupération des données
$arbitres = $arbitreManager->getArbitres();
$equipes = $equipeManager->getEquipes();

// Récupérer les ligues
$sql = "SELECT * FROM ligues ORDER BY nom";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$ligues = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Match - Système de Désignation d'Arbitres</title>
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
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-arrow-left me-2"></i>
                Retour au Dashboard
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
            <div class="col-lg-10">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Formulaire d'ajout de match -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-plus-circle"></i> 
                            <span style="margin-left: 0.5rem;">Ajouter un Match</span>
                        </h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" id="matchForm">
                            <input type="hidden" name="action" value="ajouter_match">
                            
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="ligue_id" class="form-label">Nom de la Compétition</label>
                                    <select name="ligue_id" id="ligue_id" class="form-select" required>
                                        <?php foreach ($ligues as $ligue): ?>
                                            <option value="<?php echo $ligue['id']; ?>" <?php echo $ligue['nom'] == 'CNP' ? 'selected' : ''; ?>>
                                                <?php echo $ligue['nom']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-9">
                                    <label for="nom_competition" class="form-label">Ligue</label>
                                    <input type="text" class="form-control" name="nom_competition" required>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <label for="ville" class="form-label">Zone</label>
                                    <input type="text" class="form-control" name="ville" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="stade" class="form-label">Stade</label>
                                    <input type="text" class="form-control" name="stade" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="tour" class="form-label">Tour</label>
                                    <input type="text" class="form-control" name="tour" required>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="date_match" class="form-label">Date</label>
                                    <input type="date" class="form-control" name="date_match" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="heure_match" class="form-label">Heure</label>
                                    <input type="time" class="form-control" name="heure_match" required>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="equipe_a_id" class="form-label">Équipe A</label>
                                    <select name="equipe_a_id" id="equipe_a_id" class="form-select" required>
                                        <option value="">Sélectionner une équipe...</option>
                                        <?php foreach ($equipes as $equipe): ?>
                                            <option value="<?php echo $equipe['id']; ?>">
                                                <?php echo htmlspecialchars($equipe['nom']); ?> (<?php echo htmlspecialchars($equipe['ville']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="equipe_b_id" class="form-label">Équipe B</label>
                                    <select name="equipe_b_id" id="equipe_b_id" class="form-select" required>
                                        <option value="">Sélectionner une équipe...</option>
                                        <?php foreach ($equipes as $equipe): ?>
                                            <option value="<?php echo $equipe['id']; ?>">
                                                <?php echo htmlspecialchars($equipe['nom']); ?> (<?php echo htmlspecialchars($equipe['ville']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <hr class="my-4">

                            <h5 style="color: var(--primary-color); font-weight: 600; margin-bottom: 1.5rem;">
                                <i class="fas fa-user-tie"></i> 
                                <span style="margin-left: 0.5rem;">Désignation des Arbitres</span>
                            </h5>

                            <div class="row">
                                <div class="col-md-6">
                                    <label for="arbitre_id" class="form-label">Arbitre Principal</label>
                                    <select name="arbitre_id" id="arbitre_id" class="form-select" required>
                                        <option value="">Sélectionner un arbitre...</option>
                                        <?php foreach ($arbitres as $arbitre): ?>
                                            <option value="<?php echo $arbitre['id']; ?>">
                                                <?php echo htmlspecialchars($arbitre['prenom'] . ' ' . $arbitre['nom']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="assistant_1_id" class="form-label">Assistant Arbitre 1</label>
                                    <select name="assistant_1_id" id="assistant_1_id" class="form-select">
                                        <option value="">Sélectionner un arbitre...</option>
                                        <?php foreach ($arbitres as $arbitre): ?>
                                            <option value="<?php echo $arbitre['id']; ?>">
                                                <?php echo htmlspecialchars($arbitre['prenom'] . ' ' . $arbitre['nom']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="assistant_2_id" class="form-label">Assistant Arbitre 2</label>
                                    <select name="assistant_2_id" id="assistant_2_id" class="form-select">
                                        <option value="">Sélectionner un arbitre...</option>
                                        <?php foreach ($arbitres as $arbitre): ?>
                                            <option value="<?php echo $arbitre['id']; ?>">
                                                <?php echo htmlspecialchars($arbitre['prenom'] . ' ' . $arbitre['nom']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="officiel_4_id" class="form-label">4ème Officiel</label>
                                    <select name="officiel_4_id" id="officiel_4_id" class="form-select">
                                        <option value="">Sélectionner un arbitre...</option>
                                        <?php foreach ($arbitres as $arbitre): ?>
                                            <option value="<?php echo $arbitre['id']; ?>">
                                                <?php echo htmlspecialchars($arbitre['prenom'] . ' ' . $arbitre['nom']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="assesseur_id" class="form-label">Assesseur d'Arbitres</label>
                                    <select name="assesseur_id" id="assesseur_id" class="form-select">
                                        <option value="">Sélectionner un assesseur...</option>
                                        <?php foreach ($arbitres as $arbitre): ?>
                                            <?php if ($arbitre['fonction'] == 'Assesseur'): ?>
                                                <option value="<?php echo $arbitre['id']; ?>">
                                                    <?php echo htmlspecialchars($arbitre['prenom'] . ' ' . $arbitre['nom']); ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="publier" class="form-label">Publier</label>
                                    <select name="publier" id="publier" class="form-select" required>
                                        <option value="Non">Non</option>
                                        <option value="Oui">Oui</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        Si "Oui", les arbitres recevront un email de désignation
                                    </small>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save"></i> 
                                        <span style="margin-left: 0.5rem;">Enregistrer le Match</span>
                                    </button>
                                    <a href="dashboard.php" class="btn btn-secondary btn-lg ms-3">
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