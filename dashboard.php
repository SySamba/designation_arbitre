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

// Traitement de la suppression
if (isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id'])) {
    $resultat = $matchManager->supprimerMatch($_GET['id']);
    
    if ($resultat['success']) {
        $message = $resultat['message'];
        $message_type = 'success';
    } else {
        $message = $resultat['message'];
        $message_type = 'error';
    }
}

// Récupération des données
$matchs = $matchManager->getMatchs();
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
    <title>Système de Désignation d'Arbitres</title>
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

        * {
            box-sizing: border-box;
        }

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
            background: rgba(255, 255, 255, 0.9);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            background: white;
        }
        
        /* Styles pour les listes déroulantes */
        .form-select {
            padding-right: 1rem;
        }
        
        /* Styles pour les options des listes déroulantes */
        .form-select option {
            padding: 0.5rem;
            font-weight: 500;
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
            background: #f8f9fa;
            color: black;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            padding: 12px 8px;
            border: 1px solid #dee2e6;
        }



        .table td {
            background: #ffffff;
            border: 1px solid #dee2e6;
            vertical-align: middle;
            padding: 12px 8px;
            color: black;
        }

        .table tbody tr {
            /* Pas d'animation */
        }

        .table tbody tr:nth-of-type(odd) {
            background-color: #ffffff;
        }

        .table tbody tr:nth-of-type(even) {
            background-color: #f8f9fa;
        }

        .table tbody tr:hover {
            background-color: #e9ecef;
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
    </style>
    <script>
        // Fonction pour vérifier les contraintes en temps réel
        function verifierContraintes() {
            const form = document.getElementById('matchForm');
            const formData = new FormData(form);
            
            // Vérifier les conflits de rôles
            const arbitres = [];
            const roles = ['arbitre_id', 'assistant_1_id', 'assistant_2_id', 'officiel_4_id', 'assesseur_id'];
            
            roles.forEach(role => {
                const value = formData.get(role);
                if (value && value !== '') {
                    arbitres.push(value);
                }
            });
            
            // Vérifier les doublons
            const doublons = arbitres.filter((item, index) => arbitres.indexOf(item) !== index);
            if (doublons.length > 0) {
                alert('Erreur: Un arbitre ne peut pas être sélectionné pour plusieurs rôles dans le même match');
                return false;
            }
            
            // Vérifier que les équipes sont différentes
            const equipeA = formData.get('equipe_a_id');
            const equipeB = formData.get('equipe_b_id');
            if (equipeA && equipeB && equipeA === equipeB) {
                alert('Erreur: Une équipe ne peut pas jouer contre elle-même');
                return false;
            }
            
            // Vérifier les contraintes de disponibilité
            const dateMatch = formData.get('date_match');
            const heureMatch = formData.get('heure_match');
            
            if (dateMatch && heureMatch) {
                // Faire une requête AJAX pour vérifier les contraintes
                fetch('verifier_contraintes_ajax.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        alert('Erreur de contrainte: ' + data.message);
                        return false;
                    }
                    
                    // Afficher les avertissements s'il y en a
                    if (data.avertissements && data.avertissements.length > 0) {
                        let message = 'Avertissements:\n';
                        data.avertissements.forEach(avertissement => {
                            message += '• ' + avertissement + '\n';
                        });
                        message += '\nVoulez-vous continuer quand même ?';
                        
                        if (!confirm(message)) {
                            return false;
                        }
                    }
                    
                    // Si tout est OK, soumettre le formulaire
                    form.submit();
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    // En cas d'erreur, soumettre quand même
                    form.submit();
                });
            } else {
                form.submit();
            }
            
            return false;
        }
        
        // Fonction pour charger les arbitres par fonction
        function loadArbitresByFunction(selectId, fonction) {
            const select = document.getElementById(selectId);
            
            fetch(`get_arbitres_by_function.php?fonction=${encodeURIComponent(fonction)}`)
                .then(response => response.json())
                .then(data => {
                    // Garder l'option par défaut
                    const defaultOption = select.querySelector('option[value=""]');
                    select.innerHTML = '';
                    if (defaultOption) {
                        select.appendChild(defaultOption);
                    }
                    
                    data.forEach(arbitre => {
                        const option = document.createElement('option');
                        option.value = arbitre.id;
                        option.textContent = `${arbitre.nom} ${arbitre.prenom}`;
                        select.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des arbitres:', error);
                });
        }
        
        // Fonction pour charger les équipes
        function loadEquipes(selectId) {
            const select = document.getElementById(selectId);
            
            fetch('autocomplete.php?type=equipes&term=')
                        .then(response => response.json())
                        .then(data => {
                    // Garder l'option par défaut
                    const defaultOption = select.querySelector('option[value=""]');
                    select.innerHTML = '';
                    if (defaultOption) {
                        select.appendChild(defaultOption);
                    }
                    
                    data.forEach(equipe => {
                        const option = document.createElement('option');
                        option.value = equipe.id;
                        option.textContent = `${equipe.nom} (${equipe.ville})`;
                        select.appendChild(option);
                    });
                        })
                        .catch(error => {
                    console.error('Erreur lors du chargement des équipes:', error);
                });
        }
        
        // Fonction pour charger les options dans les listes déroulantes
        function loadSelectOptions(selectId, type) {
            const select = document.getElementById(selectId);
            
            fetch(`autocomplete.php?type=${type}&term=`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        // Garder l'option par défaut
                        const defaultOption = select.querySelector('option[value=""]');
                        select.innerHTML = '';
                        if (defaultOption) {
                            select.appendChild(defaultOption);
                        }
                        
                        data.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.id;
                            option.textContent = item.label;
                            select.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des options:', error);
                });
        }
        

        
        // Initialiser les listes déroulantes
        document.addEventListener('DOMContentLoaded', function() {
            // Code d'initialisation si nécessaire
        });
    </script>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark mb-4">
            <div class="container-fluid">
                <a class="navbar-brand" href="dashboard.php">
                    <img src="logo.jpg" alt="Logo" style="height: 40px; margin-right: 10px;">
                    <span style="margin-left: 0.5rem;">Système de Désignation d'Arbitres</span>
                </a>
                                            <div class="navbar-nav ms-auto">
                                <a class="nav-link" href="telecharger_multiple.php">
                                    <i class="fas fa-download"></i> 
                                    <span style="margin-left: 0.3rem;">Téléchargement Multiple</span>
                                </a>
                                <a class="nav-link" href="arbitres.php">
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

                <!-- Boutons d'action rapide -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <a href="telecharger_multiple.php" class="btn btn-primary w-100">
                                            <i class="fas fa-download me-2"></i>
                                            Télécharger Désignations
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <a href="arbitres.php" class="btn btn-info w-100">
                                            <i class="fas fa-user-tie me-2"></i>
                                            Gérer les Arbitres
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <a href="equipes.php" class="btn btn-success w-100">
                                            <i class="fas fa-users me-2"></i>
                                            Gérer les Équipes
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <a href="ajouter_match.php" class="btn btn-warning w-100">
                                            <i class="fas fa-plus me-2"></i>
                                            Ajouter un Match
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>







                <!-- Liste des matchs -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-list"></i> 
                            <span style="margin-left: 0.5rem;">Liste des Matchs</span>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" style="font-size: 0.9rem;">
                                <thead>
                                    <tr>
                                        <th style="width: 5%; padding: 4px;">N°</th>
                                        <th style="width: 15%; padding: 4px;">Date/Terrain</th>
                                        <th style="width: 25%; padding: 4px;">Rencontre</th>
                                        <th style="width: 35%; padding: 4px;">Arbitre/Assistants</th>
                                        <th style="width: 8%; padding: 4px;">Publier</th>
                                        <th style="width: 12%; padding: 4px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($matchs as $index => $match): ?>
                                        <tr>
                                            <td class="text-center" style="padding: 4px;">
                                                <strong style="font-size: 1rem; color: black;"><?php echo $index + 1; ?></strong>
                                            </td>
                                            <td style="padding: 4px;">
                                                <strong style="font-size: 1rem; color: black;"><?php echo date('d/m/Y', strtotime($match['date_match'])); ?></strong><br><span style="font-size: 0.9rem; color: #666;"><?php echo $match['heure_match']; ?></span><br><span style="font-size: 0.9rem; color: black;">Zone : <?php echo $match['ville']; ?></span><br><span style="font-size: 0.9rem; color: black;">Stade : <?php echo $match['stade']; ?></span>
                                            </td>
                                            <td style="padding: 4px; text-align: center;">
                                                <div><strong style="font-size: 1.1rem; color: black; font-weight: bold;"><?php echo $match['equipe_a_nom']; ?></strong><br><span style="font-size: 0.9rem; color: #666;">VS</span><br><strong style="font-size: 1.1rem; color: black; font-weight: bold;"><?php echo $match['equipe_b_nom']; ?></strong><br><span style="font-size: 0.9rem; color: black;">Tour : <?php echo $match['tour']; ?></span></div>
                                            </td>
                                            <td style="padding: 4px;">
                                                <div style="font-size: 0.9rem;">
                                                        <?php if ($match['arbitre_nom']): ?>
                                                        <div style="display: flex; align-items: center; margin-bottom: 1px;">
                                                            <span style="color: black; margin-right: 5px;">AR :</span>
                                                            <span style="color: black;"><?php echo $match['arbitre_prenom'] . ' ' . $match['arbitre_nom']; ?></span>
                                                            <?php
                                                            // Récupérer la photo de l'arbitre principal
                                                            $arbitre_photo = null;
                                                            if ($match['arbitre_id']) {
                                                                $arbitre_query = "SELECT photo FROM arbitres WHERE id = ?";
                                                                $arbitre_stmt = $pdo->prepare($arbitre_query);
                                                                $arbitre_stmt->execute([$match['arbitre_id']]);
                                                                $arbitre_data = $arbitre_stmt->fetch(PDO::FETCH_ASSOC);
                                                                $arbitre_photo = $arbitre_data['photo'] ?? null;
                                                            }
                                                            ?>
                                                            <?php if ($arbitre_photo): ?>
                                                                <img src="photos_arbitres/<?php echo $arbitre_photo; ?>"
                                                                     alt="Photo de <?php echo $match['arbitre_nom'] . ' ' . $match['arbitre_prenom']; ?>"
                                                                     style="width: 25px; height: 25px; object-fit: cover; border-radius: 50%; margin-left: 5px;">
                                                        <?php else: ?>
                                                                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center"
                                                                     style="width: 25px; height: 25px; margin-left: 5px; font-size: 10px;">
                                                                    <i class="fas fa-user"></i>
                                                    </div>
                                                        <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if ($match['assistant1_nom']): ?>
                                                        <div style="color: black; margin-bottom: 1px;">
                                                            <span style="color: black; margin-right: 5px;">AA1 :</span>
                                                           <?php echo $match['assistant1_prenom'] . ' ' . $match['assistant1_nom']; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if ($match['assistant2_nom']): ?>
                                                        <div style="color: black; margin-bottom: 1px;">
                                                            <span style="color: black; margin-right: 5px;">AA2 :</span>
                                                          <?php echo $match['assistant2_prenom'] . ' ' . $match['assistant2_nom']; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if ($match['officiel4_nom']): ?>
                                                        <div style="color: black; margin-bottom: 1px;">
                                                            <span style="color: black; margin-right: 5px;">4ème :</span>
                                                         <?php echo $match['officiel4_prenom'] . ' ' . $match['officiel4_nom']; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if ($match['assesseur_nom']): ?>
                                                        <div style="color: black; margin-bottom: 1px;">
                                                            <span style="color: black; margin-right: 5px;">ASS :</span>
                                                         <?php echo $match['assesseur_prenom'] . ' ' . $match['assesseur_nom']; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!$match['arbitre_nom'] && !$match['assistant1_nom'] && !$match['assistant2_nom'] && !$match['officiel4_nom'] && !$match['assesseur_nom']): ?>
                                                        <span class="text-muted" style="font-size: 0.9rem;">Aucun officiel désigné</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="text-center" style="padding: 4px;">
                                                <span class="badge bg-<?php echo $match['publier'] == 'Oui' ? 'success' : 'secondary'; ?>">
                                                    <?php echo $match['publier']; ?>
                                                </span>
                                            </td>

                                            <td style="padding: 4px;">
                                                <div class="btn-group" role="group">
                                                    <a href="modifier_match.php?id=<?php echo $match['id']; ?>" class="btn btn-sm btn-outline-primary" title="Modifier" style="padding: 4px 8px; font-size: 0.8rem;">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="telecharger_designation.php?id=<?php echo $match['id']; ?>" class="btn btn-sm btn-outline-info" title="Télécharger PDF" style="padding: 4px 8px; font-size: 0.8rem;">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                                            onclick="envoyerEmail(<?php echo $match['id']; ?>)" title="Envoyer par email" style="padding: 4px 8px; font-size: 0.8rem;">
                                                        <i class="fas fa-envelope"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                                            onclick="envoyerWhatsApp(<?php echo $match['id']; ?>)" title="Envoyer par WhatsApp" style="padding: 4px 8px; font-size: 0.8rem;">
                                                        <i class="fab fa-whatsapp"></i>
                                                    </button>
                                                    <a href="?action=supprimer&id=<?php echo $match['id']; ?>" class="btn btn-sm btn-outline-danger" 
                                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce match ?')" title="Supprimer" style="padding: 4px 8px; font-size: 0.8rem;">
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
        // Fonction pour envoyer la désignation par email
        function envoyerEmail(matchId) {
            if (confirm('Voulez-vous envoyer la désignation par email aux arbitres et assesseurs ?')) {
                // Afficher un indicateur de chargement
                const button = event.target.closest('button');
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';
                button.disabled = true;
                
                fetch('envoyer_email.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        match_id: matchId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Email envoyé avec succès !');
                    } else {
                        alert('Erreur lors de l\'envoi : ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Erreur lors de l\'envoi : ' + error.message);
                })
                .finally(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
            }
        }
        
        // Fonction pour envoyer la désignation par WhatsApp
        function envoyerWhatsApp(matchId) {
            if (confirm('Voulez-vous ouvrir WhatsApp pour envoyer la désignation aux arbitres et assesseurs ?')) {
                // Afficher un indicateur de chargement
                const button = event.target.closest('button');
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Préparation...';
                button.disabled = true;
                
                fetch('envoyer_whatsapp.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        match_id: matchId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Ouvrir tous les liens WhatsApp en même temps
                        data.destinataires.forEach(destinataire => {
                            window.open(destinataire.lien, '_blank');
                        });
                        
                        // Afficher la liste des destinataires
                        let destinataires_list = data.destinataires.map(d => d.nom).join('\n');
                        alert('Liens WhatsApp ouverts pour ' + data.destinataires.length + ' destinataire(s)\n\nDestinataires:\n' + destinataires_list);
                    } else {
                        alert('Erreur lors de la préparation : ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Erreur lors de la préparation : ' + error.message);
                })
                .finally(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
            }
        }
        
        // Fonction pour vérifier les doublons d'arbitres
        function checkDuplicates() {
            const selectedValues = new Set();
            const duplicates = [];
            const arbitreSelects = ['arbitre_id', 'assistant_1_id', 'assistant_2_id', 'officiel_4_id'];
            
            arbitreSelects.forEach(selectId => {
                const select = document.getElementById(selectId);
                const value = select.value;
                if (value && value !== '') {
                    if (selectedValues.has(value)) {
                        duplicates.push(selectId);
                    } else {
                        selectedValues.add(value);
                    }
                }
            });
            
            // Réinitialiser les styles
            arbitreSelects.forEach(selectId => {
                const select = document.getElementById(selectId);
                const searchInput = selectId.replace('_id', '_search');
                const searchElement = document.getElementById(searchInput);
                if (select) {
                    select.classList.remove('is-invalid');
                }
                if (searchElement) {
                    searchElement.classList.remove('is-invalid');
                }
            });
            
            // Marquer les doublons
            duplicates.forEach(selectId => {
                const select = document.getElementById(selectId);
                const searchInput = selectId.replace('_id', '_search');
                const searchElement = document.getElementById(searchInput);
                if (select) {
                    select.classList.add('is-invalid');
                }
                if (searchElement) {
                    searchElement.classList.add('is-invalid');
                }
            });
            
            return duplicates.length === 0;
        }
        
        // Initialisation au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            // Initialiser l'autocomplétion pour les équipes
            initAutocomplete('equipe_a_search', 'equipe_a_id', 'equipe_a_suggestions', 'equipes');
            initAutocomplete('equipe_b_search', 'equipe_b_id', 'equipe_b_suggestions', 'equipes');
            
            // Initialiser l'autocomplétion pour les arbitres
            initAutocomplete('arbitre_search', 'arbitre_id', 'arbitre_suggestions', 'arbitres');
            initAutocomplete('assistant_1_search', 'assistant_1_id', 'assistant_1_suggestions', 'arbitres');
            initAutocomplete('assistant_2_search', 'assistant_2_id', 'assistant_2_suggestions', 'arbitres');
            initAutocomplete('officiel_4_search', 'officiel_4_id', 'officiel_4_suggestions', 'arbitres');
            initAutocomplete('assesseur_search', 'assesseur_id', 'assesseur_suggestions', 'assesseurs');
            
            // Ajouter les événements de changement pour les contraintes
            const arbitreSelects = ['arbitre_id', 'assistant_1_id', 'assistant_2_id', 'officiel_4_id'];
            arbitreSelects.forEach(selectId => {
                const select = document.getElementById(selectId);
                if (select) {
                    select.addEventListener('change', checkDuplicates);
                }
            });
        });
        
        function resetForm() {
            document.querySelector('form').reset();
            
            // Réinitialiser les listes déroulantes
            const selectInputs = [
                'equipe_a_id', 'equipe_b_id', 
                'arbitre_id', 'assistant_1_id', 'assistant_2_id', 
                'officiel_4_id', 'assesseur_id'
            ];
            
            selectInputs.forEach(selectId => {
                const select = document.getElementById(selectId);
                if (select) {
                    select.value = '';
                    select.classList.remove('is-invalid');
                }
            });
        }
        
        // Validation pour éviter qu'une équipe joue contre elle-même
        document.addEventListener('DOMContentLoaded', function() {
            const equipeBSelect = document.getElementById('equipe_b_id');
            const equipeASelect = document.getElementById('equipe_a_id');
            const equipeBSearch = document.getElementById('equipe_b_search');
            const equipeASearch = document.getElementById('equipe_a_search');
            
            if (equipeBSelect) {
                equipeBSelect.addEventListener('change', function() {
                    const equipeA = equipeASelect.value;
            const equipeB = this.value;
            
            if (equipeA && equipeB && equipeA === equipeB) {
                alert('Une équipe ne peut pas jouer contre elle-même !');
                this.value = '';
                        if (equipeBSearch) {
                            equipeBSearch.value = '';
                        }
            }
        });
            }
        
            if (equipeASelect) {
                equipeASelect.addEventListener('change', function() {
            const equipeA = this.value;
                    const equipeB = equipeBSelect.value;
            
            if (equipeA && equipeB && equipeA === equipeB) {
                alert('Une équipe ne peut pas jouer contre elle-même !');
                        equipeBSelect.value = '';
                        if (equipeBSearch) {
                            equipeBSearch.value = '';
                        }
                    }
                });
            }
        });
    </script>
</body>
</html> 