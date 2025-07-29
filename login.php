<?php
session_start();

$error = '';

// Traitement de la connexion
if ($_POST) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Vérification des identifiants
    if ($email === 'sambasy837@gmail.com' && $password === 'admin123') {
        $_SESSION['user_id'] = 1;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = 'Administrateur';
        
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Email ou mot de passe incorrect';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Système de Désignation d'Arbitres</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Effet de particules animées */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="40" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="80" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="60" cy="10" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="10" cy="60" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="90" cy="90" r="1" fill="rgba(255,255,255,0.1)"/></svg>');
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            padding: 2rem;
            width: 100%;
            max-width: 480px;
            position: relative;
            z-index: 10;
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideInUp 0.8s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .login-icon {
            font-size: 3.5rem;
            background: linear-gradient(135deg, #3498db, #2980b9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.8rem;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .login-title {
            color: #2c3e50;
            font-weight: 800;
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .login-subtitle {
            color: #7f8c8d;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 1.2rem;
            position: relative;
        }

        .form-label {
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-size: 1rem;
            display: block;
        }

        .input-group {
            position: relative;
            margin-bottom: 0.3rem;
        }

        .input-group-text {
            background: linear-gradient(135deg, #3498db, #2980b9);
            border: none;
            color: white;
            border-radius: 15px 0 0 15px;
            padding: 1rem 1.2rem;
            font-size: 1.1rem;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 0 15px 15px 0;
            padding: 1rem 1.2rem;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.3rem rgba(52, 152, 219, 0.25);
            background: white;
            transform: translateY(-2px);
        }

        .form-control::placeholder {
            color: #95a5a6;
            font-weight: 500;
        }

        .btn-login {
            background: linear-gradient(135deg, #3498db, #2980b9);
            border: none;
            border-radius: 15px;
            padding: 1.2rem 2rem;
            font-weight: 700;
            font-size: 1.1rem;
            color: white;
            width: 100%;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 25px rgba(52, 152, 219, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #2980b9, #3498db);
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(52, 152, 219, 0.4);
        }

        .btn-login:active {
            transform: translateY(-1px);
        }

        .alert {
            border-radius: 15px;
            border: none;
            padding: 1.2rem 1.5rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .alert-danger {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            box-shadow: 0 8px 25px rgba(231, 76, 60, 0.3);
        }

        .credentials-info {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
            border-radius: 15px;
            padding: 1rem;
            margin-top: 1.2rem;
            font-size: 0.95rem;
            box-shadow: 0 8px 25px rgba(243, 156, 18, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .credentials-info strong {
            font-weight: 700;
            font-size: 1rem;
        }

        .credentials-info br {
            margin-bottom: 0.3rem;
        }

        /* Effet de brillance sur le container */
        .login-container::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #3498db, #2980b9, #3498db);
            border-radius: 25px;
            z-index: -1;
            opacity: 0.3;
            animation: borderGlow 3s ease-in-out infinite;
        }

        @keyframes borderGlow {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.6; }
        }

        /* Responsive */
        @media (max-width: 576px) {
            .login-container {
                margin: 1rem;
                padding: 2.5rem;
            }
            
            .login-title {
                font-size: 1.8rem;
            }
            
            .login-icon {
                font-size: 4rem;
            }
        }

        /* Effet de focus amélioré */
        .input-group:focus-within .input-group-text {
            background: linear-gradient(135deg, #2980b9, #3498db);
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-icon">
                <i class="fas fa-whistle"></i>
            </div>
            <h1 class="login-title">Connexion</h1>
            <p class="login-subtitle">Système de Désignation d'Arbitres</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                           placeholder="Votre adresse email" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Mot de passe</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Votre mot de passe" required>
                </div>
            </div>

            <button type="submit" class="btn btn-login">
                <i class="fas fa-sign-in-alt"></i>
                Se connecter
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 