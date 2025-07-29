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

// Récupération du match à modifier
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

// Traitement de la modification
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'modifier_match') {
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
    
    $resultat = $matchManager->modifierMatch($match_id, $data);
    
    if ($resultat['success']) {
        $message = $resultat['message'];
        $message_type = 'success';
        // Recharger les données du match
        $match = $matchManager->getMatchById($match_id);
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
    <title>Modifier un Match</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .btn-primary {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
        }
        .alert {
            border-radius: 10px;
        }
        .form-label {
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.php">
                    <i class="fas fa-whistle"></i> Système de Désignation d'Arbitres
                </a>
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="arbitres.php">
                        <i class="fas fa-user-tie"></i> Arbitres
                    </a>
                    <a class="nav-link" href="equipes.php">
                        <i class="fas fa-users"></i> Équipes
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

                <!-- Formulaire de modification de match -->
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0">
                            <i class="fas fa-edit"></i> 
                            Modifier le Match
                        </h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="modifier_match">
                            
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="ligue_id" class="form-label">Ligue</label>
                                    <select name="ligue_id" id="ligue_id" class="form-select" required>
                                        <?php foreach ($ligues as $ligue): ?>
                                            <option value="<?php echo $ligue['id']; ?>" <?php echo $match['ligue_id'] == $ligue['id'] ? 'selected' : ''; ?>>
                                                <?php echo $ligue['nom']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-9">
                                    <label for="nom_competition" class="form-label">Nom de la Compétition</label>
                                    <input type="text" class="form-control" name="nom_competition" 
                                           value="<?php echo htmlspecialchars($match['nom_competition']); ?>" required>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <label for="ville" class="form-label">Ville</label>
                                    <input type="text" class="form-control" name="ville" 
                                           value="<?php echo htmlspecialchars($match['ville']); ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="stade" class="form-label">Stade</label>
                                    <input type="text" class="form-control" name="stade" 
                                           value="<?php echo htmlspecialchars($match['stade']); ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="tour" class="form-label">Tour</label>
                                    <input type="text" class="form-control" name="tour" 
                                           value="<?php echo htmlspecialchars($match['tour']); ?>" required>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="date_match" class="form-label">Date</label>
                                    <input type="date" class="form-control" name="date_match" 
                                           value="<?php echo $match['date_match']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="heure_match" class="form-label">Heure</label>
                                    <input type="time" class="form-control" name="heure_match" 
                                           value="<?php echo $match['heure_match']; ?>" required>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="equipe_a_id" class="form-label">Équipe A</label>
                                    <select name="equipe_a_id" id="equipe_a_id" class="form-select" required>
                                        <option value="">Sélectionner une équipe</option>
                                        <?php foreach ($equipes as $equipe): ?>
                                            <option value="<?php echo $equipe['id']; ?>" <?php echo $match['equipe_a_id'] == $equipe['id'] ? 'selected' : ''; ?>>
                                                <?php echo $equipe['nom']; ?> (<?php echo $equipe['ville']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="equipe_b_id" class="form-label">Équipe B</label>
                                    <select name="equipe_b_id" id="equipe_b_id" class="form-select" required>
                                        <option value="">Sélectionner une équipe</option>
                                        <?php foreach ($equipes as $equipe): ?>
                                            <option value="<?php echo $equipe['id']; ?>" <?php echo $match['equipe_b_id'] == $equipe['id'] ? 'selected' : ''; ?>>
                                                <?php echo $equipe['nom']; ?> (<?php echo $equipe['ville']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <hr class="my-4">

                            <h5><i class="fas fa-user-tie"></i> Désignation des Arbitres</h5>

                            <div class="row">
                                <div class="col-md-6">
                                    <label for="arbitre_id" class="form-label">Arbitre Principal</label>
                                    <select name="arbitre_id" id="arbitre_id" class="form-select">
                                        <option value="">Sélectionner un arbitre</option>
                                        <?php foreach ($arbitres as $arbitre): ?>
                                            <option value="<?php echo $arbitre['id']; ?>" <?php echo $match['arbitre_id'] == $arbitre['id'] ? 'selected' : ''; ?>>
                                                <?php echo $arbitre['nom'] . ' ' . $arbitre['prenom']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="assistant_1_id" class="form-label">Assistant Arbitre 1</label>
                                    <select name="assistant_1_id" id="assistant_1_id" class="form-select">
                                        <option value="">Sélectionner un arbitre</option>
                                        <?php foreach ($arbitres as $arbitre): ?>
                                            <option value="<?php echo $arbitre['id']; ?>" <?php echo $match['assistant_1_id'] == $arbitre['id'] ? 'selected' : ''; ?>>
                                                <?php echo $arbitre['nom'] . ' ' . $arbitre['prenom']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <label for="assistant_2_id" class="form-label">Assistant Arbitre 2</label>
                                    <select name="assistant_2_id" id="assistant_2_id" class="form-select">
                                        <option value="">Sélectionner un arbitre</option>
                                        <?php foreach ($arbitres as $arbitre): ?>
                                            <option value="<?php echo $arbitre['id']; ?>" <?php echo $match['assistant_2_id'] == $arbitre['id'] ? 'selected' : ''; ?>>
                                                <?php echo $arbitre['nom'] . ' ' . $arbitre['prenom']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="officiel_4_id" class="form-label">4ème Officiel</label>
                                    <select name="officiel_4_id" id="officiel_4_id" class="form-select">
                                        <option value="">Sélectionner un arbitre</option>
                                        <?php foreach ($arbitres as $arbitre): ?>
                                            <option value="<?php echo $arbitre['id']; ?>" <?php echo $match['officiel_4_id'] == $arbitre['id'] ? 'selected' : ''; ?>>
                                                <?php echo $arbitre['nom'] . ' ' . $arbitre['prenom']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <label for="assesseur_id" class="form-label">Assesseur d'Arbitres</label>
                                    <select name="assesseur_id" id="assesseur_id" class="form-select">
                                        <option value="">Sélectionner un arbitre</option>
                                        <?php foreach ($arbitres as $arbitre): ?>
                                            <option value="<?php echo $arbitre['id']; ?>" <?php echo $match['assesseur_id'] == $arbitre['id'] ? 'selected' : ''; ?>>
                                                <?php echo $arbitre['nom'] . ' ' . $arbitre['prenom']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="publier" class="form-label">Publier</label>
                                    <select name="publier" id="publier" class="form-select" required>
                                        <option value="Non" <?php echo $match['publier'] == 'Non' ? 'selected' : ''; ?>>Non</option>
                                        <option value="Oui" <?php echo $match['publier'] == 'Oui' ? 'selected' : ''; ?>>Oui</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        Si "Oui", les arbitres recevront un email de désignation
                                    </small>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-warning btn-lg">
                                        <i class="fas fa-save"></i> Modifier le Match
                                    </button>
                                    <a href="dashboard.php" class="btn btn-secondary btn-lg ms-2">
                                        <i class="fas fa-arrow-left"></i> Retour
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
        // Validation pour éviter qu'une équipe joue contre elle-même
        document.getElementById('equipe_b_id').addEventListener('change', function() {
            const equipeA = document.getElementById('equipe_a_id').value;
            const equipeB = this.value;
            
            if (equipeA && equipeB && equipeA === equipeB) {
                alert('Une équipe ne peut pas jouer contre elle-même !');
                this.value = '';
            }
        });
        
        document.getElementById('equipe_a_id').addEventListener('change', function() {
            const equipeA = this.value;
            const equipeB = document.getElementById('equipe_b_id').value;
            
            if (equipeA && equipeB && equipeA === equipeB) {
                alert('Une équipe ne peut pas jouer contre elle-même !');
                document.getElementById('equipe_b_id').value = '';
            }
        });
    </script>
</body>
</html> 