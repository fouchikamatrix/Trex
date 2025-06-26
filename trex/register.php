<?php
session_start();
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $id_card = trim($_POST['id_card_number'] ?? '');
    $reference = trim($_POST['reference'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $gas_counter = trim($_POST['gas_counter_number'] ?? '');
    $gas_counter_type = $_POST['gas_counter_type'] ?? '';
    $electric_counter = trim($_POST['electric_counter_number'] ?? '');
    $counter_type = $_POST['electric_counter_type'] ?? '';
    $client_type = $_POST['client_type'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($name) || empty($last_name) || empty($id_card) || empty($reference) || 
        empty($email) || empty($phone) || empty($gas_counter) || empty($gas_counter_type) || 
        empty($electric_counter) || empty($counter_type) || empty($client_type) || 
        empty($password) || empty($confirm_password)) {
        $error = 'Veuillez remplir tous les champs.';
    } elseif ($password !== $confirm_password) {
        $error = 'Les mots de passe ne correspondent pas.';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Veuillez entrer une adresse email valide.';
    } else {
        try {
            // Check if ID card number already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE id_card_number = ?");
            $stmt->execute([$id_card]);
            if ($stmt->fetch()) {
                $error = 'Numéro de carte d\'identité déjà enregistré.';
            } else {
                // Hash password and insert user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("
                    INSERT INTO users (name, last_name, id_card_number, reference, email, phone, 
                                     gas_counter_number, gas_counter_type, electric_counter_number, 
                                     electric_counter_type, client_type, password) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $name, $last_name, $id_card, $reference, $email, $phone,
                    $gas_counter, $gas_counter_type, $electric_counter, $counter_type, 
                    $client_type, $hashed_password
                ]);
                
                $success = 'Compte créé avec succès ! Redirection vers la connexion...';
                // Clear form data after successful registration
                $_POST = [];
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'Le numéro de carte d\'identité existe déjà.';
            } else {
                $error = 'Erreur de base de données. Veuillez réessayer.';
            }
        }
    }
}

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gaztronik - Créer un compte</title>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gas-gradient: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            --electricity-gradient: linear-gradient(135deg, #4dabf7 0%, #2196f3 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --shadow-light: 0 8px 32px rgba(0, 0, 0, 0.1);
            --shadow-medium: 0 12px 40px rgba(0, 0, 0, 0.15);
            --shadow-heavy: 0 20px 60px rgba(0, 0, 0, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background Particles */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 107, 53, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(77, 171, 247, 0.3) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
            z-index: -1;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-20px) rotate(120deg); }
            66% { transform: translateY(20px) rotate(240deg); }
        }

        .auth-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: var(--shadow-heavy);
            width: 100%;
            max-width: 700px;
            max-height: 95vh;
            overflow-y: auto;
            padding: 50px 40px;
            border: 1px solid var(--glass-border);
            position: relative;
            overflow-x: hidden;
            animation: slideUp 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .auth-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, 
                rgba(102, 126, 234, 0.1) 0%, 
                rgba(118, 75, 162, 0.1) 25%,
                rgba(255, 107, 53, 0.1) 50%,
                rgba(77, 171, 247, 0.1) 75%,
                rgba(102, 126, 234, 0.1) 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            z-index: -1;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .logo {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            z-index: 2;
        }

        .logo-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 15px;
        }

        .logo-icon {
            font-size: 3.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
            z-index: 2;
        }

        .logo-pulse {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 70px;
            height: 70px;
            background: var(--primary-gradient);
            border-radius: 50%;
            opacity: 0.3;
            animation: pulse 3s ease-in-out infinite;
        }

        @keyframes pulse {
            0% { transform: translate(-50%, -50%) scale(0.8); opacity: 0.7; }
            50% { transform: translate(-50%, -50%) scale(1.2); opacity: 0.3; }
            100% { transform: translate(-50%, -50%) scale(0.8); opacity: 0.7; }
        }

        .logo h1 {
            font-size: 2.2rem;
            font-weight: 800;
            font-family: 'Poppins', sans-serif;
            margin-bottom: 8px;
        }

        .volt {
            background: var(--electricity-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .gaz {
            background: var(--gas-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .logo p {
            color: #1a1a1a;
            font-size: 1rem;
            font-weight: 500;
        }

        .form-section {
            margin-bottom: 30px;
        }

        .section-title {
            color: #1a1a1a;
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-family: 'Poppins', sans-serif;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-row {
            display: flex;
            gap: 20px;
        }

        .form-row .form-group {
            flex: 1;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1a1a1a;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #404040;
            font-size: 1rem;
            z-index: 2;
        }

        input, select {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            color: #1a1a1a;
            font-weight: 500;
        }

        select {
            padding-left: 45px;
        }

        input::placeholder {
            color: rgba(26, 26, 26, 0.5);
        }

        input:focus, select:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.4);
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        select option {
            background: #333;
            color: white;
        }

        .btn {
            width: 100%;
            padding: 18px;
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 20px;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-family: 'Poppins', sans-serif;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-heavy);
        }

        .btn:active {
            transform: translateY(-1px);
        }

        .switch-form {
            text-align: center;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 2;
        }

        .switch-form p {
            color: #2d2d2d;
            margin-bottom: 10px;
        }

        .switch-form a {
            color: #1a1a1a;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
            padding: 8px 16px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: inline-block;
        }

        .switch-form a:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: var(--shadow-light);
        }

        .error {
            background: rgba(239, 68, 68, 0.1);
            backdrop-filter: blur(10px);
            color: #dc2626;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            border: 1px solid rgba(239, 68, 68, 0.3);
            text-align: center;
            font-weight: 500;
            animation: shake 0.5s ease-in-out;
        }

        .success {
            background: rgba(34, 197, 94, 0.1);
            backdrop-filter: blur(10px);
            color: #16a34a;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            border: 1px solid rgba(34, 197, 94, 0.3);
            text-align: center;
            font-weight: 500;
            animation: slideDown 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        @keyframes slideDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: translate(-50%, -50%) rotate(0deg); }
            to { transform: translate(-50%, -50%) rotate(360deg); }
        }

        /* Custom Scrollbar */
        .auth-container::-webkit-scrollbar {
            width: 8px;
        }

        .auth-container::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        .auth-container::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 4px;
        }

        .auth-container::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        .form-group-stack {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group-stack .form-group {
    margin-bottom: 0;
}

.form-row {
    display: flex;
    gap: 25px;
    align-items: flex-start;
}

.form-row .form-group {
    flex: 1;
}

/* Specific styling for meter section */
.meter-section .form-row {
    gap: 30px;
}

.meter-section .form-group-stack:first-child {
    flex: 0.9;
}

.meter-section .form-group-stack:last-child {
    flex: 1.1;
}

/* Ensure consistent field heights */
.form-group {
    margin-bottom: 20px;
    position: relative;
    min-height: 85px;
}

.form-group label {
    height: 20px;
    display: flex;
    align-items: center;
    margin-bottom: 8px;
}

.input-wrapper {
    position: relative;
    height: 57px;
}

input, select {
    width: 100%;
    height: 57px;
    padding: 15px 15px 15px 45px;
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    font-size: 0.95rem;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    color: #1a1a1a;
    font-weight: 500;
    box-sizing: border-box;
}

/* Mobile responsive adjustments */
@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
        gap: 0;
    }
    
    .form-group-stack {
        flex: 1;
        gap: 20px;
        margin-top: 20px;
    }
    
    .meter-section .form-group-stack:first-child,
    .meter-section .form-group-stack:last-child {
        flex: 1;
    }
    
    .form-group {
        min-height: auto;
    }
}

        /* Mobile responsive */
        @media (max-width: 768px) {
            .auth-container {
                padding: 40px 25px;
                margin: 15px;
                border-radius: 20px;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .logo h1 {
                font-size: 1.8rem;
            }

            .logo-icon {
                font-size: 3rem;
            }

            .section-title {
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .auth-container {
                padding: 30px 20px;
            }

            input, select, .btn {
                padding: 14px 14px 14px 40px;
                font-size: 0.9rem;
            }

            .logo h1 {
                font-size: 1.6rem;
            }

            .logo-icon {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="logo">
            <div class="logo-wrapper">
                <i class="fas fa-bolt logo-icon"></i>
                <div class="logo-pulse"></div>
            </div>
            <h1><span class="volt">Gaz</span><span class="gaz">Tronik</span></h1>
            <p>Créez votre compte</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="error">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="registerForm">
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-user"></i>
                    Informations personnelles
                </h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Prénom</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" id="name" name="name" required 
                                   placeholder="Entrez votre prénom"
                                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Nom de famille</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" id="last_name" name="last_name" required 
                                   placeholder="Entrez votre nom de famille"
                                   value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="id_card_number">Numéro de carte d'identité</label>
                    <div class="input-wrapper">
                        <i class="fas fa-id-card input-icon"></i>
                        <input type="text" id="id_card_number" name="id_card_number" required 
                               placeholder="Entrez votre numéro de carte d'identité"
                               value="<?php echo htmlspecialchars($_POST['id_card_number'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="reference">Numéro de référence</label>
                    <div class="input-wrapper">
                        <i class="fas fa-hashtag input-icon"></i>
                        <input type="text" id="reference" name="reference" required 
                               placeholder="Entrez votre numéro de référence"
                               value="<?php echo htmlspecialchars($_POST['reference'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-address-book"></i>
                    Informations de contact
                </h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Adresse email</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" id="email" name="email" required 
                                   placeholder="Entrez votre email"
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="phone">Numéro de téléphone</label>
                        <div class="input-wrapper">
                            <i class="fas fa-phone input-icon"></i>
                            <input type="tel" id="phone" name="phone" required 
                                   placeholder="Entrez votre numéro de téléphone"
                                   value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section meter-section">
    <h3 class="section-title">
        <i class="fas fa-tachometer-alt"></i>
        Informations des compteurs
    </h3>
    
    <div class="form-row">
        <div class="form-group-stack">
            <div class="form-group">
                <label for="gas_counter_number">Numéro compteur gaz</label>
                <div class="input-wrapper">
                    <i class="fas fa-fire input-icon"></i>
                    <input type="text" id="gas_counter_number" name="gas_counter_number" required 
                           placeholder="Entrez le numéro du compteur gaz"
                           value="<?php echo htmlspecialchars($_POST['gas_counter_number'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="gas_counter_type">Type de compteur gaz</label>
                <div class="input-wrapper">
                    <i class="fas fa-cog input-icon"></i>
                    <select id="gas_counter_type" name="gas_counter_type" required>
                        <option value="">Sélectionner le type de compteur</option>
                        <option value="linky" <?php echo (($_POST['gas_counter_type'] ?? '') === 'linky') ? 'selected' : ''; ?>>Linky</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="form-group-stack">
            <div class="form-group">
                <label for="electric_counter_number">Numéro compteur électrique</label>
                <div class="input-wrapper">
                    <i class="fas fa-bolt input-icon"></i>
                    <input type="text" id="electric_counter_number" name="electric_counter_number" required 
                           placeholder="Entrez le numéro du compteur électrique"
                           value="<?php echo htmlspecialchars($_POST['electric_counter_number'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="client_type">Type de client</label>
                <div class="input-wrapper">
                    <i class="fas fa-building input-icon"></i>
                    <select id="client_type" name="client_type" required>
                        <option value="">Sélectionner le type de client</option>
                        <option value="residentiel" <?php echo (($_POST['client_type'] ?? '') === 'residentiel') ? 'selected' : ''; ?>>Résidentiel</option>
                        <option value="industriel" <?php echo (($_POST['client_type'] ?? '') === 'industriel') ? 'selected' : ''; ?>>Industriel</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="electric_counter_type">Type de compteur électrique</label>
                <div class="input-wrapper">
                    <i class="fas fa-cog input-icon"></i>
                    <select id="electric_counter_type" name="electric_counter_type" required>
                        <option value="">Sélectionner le type de compteur</option>
                        <option value="classic" <?php echo (($_POST['electric_counter_type'] ?? '') === 'classic') ? 'selected' : ''; ?>>Classique</option>
                        <option value="electronic" <?php echo (($_POST['electric_counter_type'] ?? '') === 'electronic') ? 'selected' : ''; ?>>Électronique</option>
                        <option value="linky" <?php echo (($_POST['electric_counter_type'] ?? '') === 'linky') ? 'selected' : ''; ?>>Linky</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-lock"></i>
                    Sécurité
                </h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="password" name="password" required 
                                   placeholder="Créez un mot de passe">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="confirm_password" name="confirm_password" required 
                                   placeholder="Confirmez votre mot de passe">
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn" id="registerBtn">
                <i class="fas fa-user-plus"></i>
                Créer un compte Gaztronik
            </button>
        </form>

        <div class="switch-form">
            <p>Vous avez déjà un compte ?</p>
            <a href="login.php">
                <i class="fas fa-sign-in-alt"></i>
                Se connecter ici
            </a>
        </div>
    </div>

    <script>
        // Auto-redirect to login page after successful registration
        <?php if (!empty($success)): ?>
        setTimeout(function() {
            window.location.href = 'login.php';
        }, 3000);
        <?php endif; ?>

        // Add loading state to form submission
        document.getElementById('registerForm').addEventListener('submit', function() {
            const btn = document.getElementById('registerBtn');
            btn.classList.add('loading');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Création du compte...';
        });

        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.style.borderColor = 'rgba(239, 68, 68, 0.5)';
            } else {
                this.style.borderColor = 'rgba(34, 197, 94, 0.5)';
            }
        });

        // Add floating animation to inputs
        document.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });

        // Add ripple effect to button
        document.querySelector('.btn').addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.style.position = 'absolute';
            ripple.style.borderRadius = '50%';
            ripple.style.background = 'rgba(255, 255, 255, 0.3)';
            ripple.style.transform = 'scale(0)';
            ripple.style.animation = 'ripple 0.6s linear';
            ripple.style.pointerEvents = 'none';
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });

        // Add CSS for ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
