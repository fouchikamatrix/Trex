<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trex moudir  - Réclamation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --accent: #4895ef;
            --dark: #1b263b;
            --light: #f8f9fa;
            --success: #4cc9f0;
            --warning: #f8961e;
            --danger: #f72585;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f0f2f5;
            color: var(--dark);
            overflow-x: hidden;
        }
        
        .dashboard {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }
        
        /* Sidebar styles (same as dashboard) */
        .sidebar {
            background: linear-gradient(180deg, var(--primary), var(--secondary));
            color: white;
            padding: 1.5rem 0;
            box-shadow: 5px 0 15px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 10;
        }
        
        .logo {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1.5rem;
        }
        
        .logo h2 {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        .logo p {
            font-size: 0.8rem;
            opacity: 0.8;
            margin-top: 0.3rem;
        }
        
        .nav-menu {
            padding: 0 1rem;
        }
        
        .nav-item {
            margin-bottom: 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .nav-item.active {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.8rem 1rem;
            color: white;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
        }
        
        .nav-link i {
            margin-right: 0.8rem;
            font-size: 1rem;
            width: 20px;
            text-align: center;
        }
        
        /* Main content styles */
        .main-content {
            padding: 1.5rem;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .page-title h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
        }
        
        .user-menu {
            display: flex;
            align-items: center;
        }
        
        .user-menu .language {
            margin-right: 1.5rem;
        }
        
        .user-menu select {
            padding: 0.3rem 0.5rem;
            border-radius: 5px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            background: white;
            color: var(--dark);
        }
        
        .user-profile {
            display: flex;
            align-items: center;
        }
        
        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 0.8rem;
            object-fit: cover;
            border: 2px solid var(--accent);
        }
        
        .user-name {
            font-weight: 600;
            margin-right: 0.8rem;
        }
        
        /* Complaint form styles */
        .complaint-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
        }
        
        .complaint-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(to bottom, var(--primary), var(--accent));
        }
        
        .form-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--dark);
            position: relative;
            padding-left: 1rem;
        }
        
        .form-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(to right, var(--primary), var(--accent));
            border-radius: 3px;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.95rem;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(0, 0, 0, 0.02);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
            background: white;
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .submit-btn {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.4);
        }
        
        .submit-btn i {
            margin-right: 8px;
        }
        
        .form-note {
            font-size: 0.85rem;
            color: #666;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        /* Floating label effect */
        .float-label {
            position: relative;
        }
        
        .float-label label {
            position: absolute;
            top: 12px;
            left: 15px;
            color: #999;
            transition: all 0.3s ease;
            pointer-events: none;
            background: white;
            padding: 0 5px;
        }
        
        .float-label input:focus + label,
        .float-label input:not(:placeholder-shown) + label {
            top: -10px;
            left: 10px;
            font-size: 0.8rem;
            color: var(--primary);
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease forwards;
        }
        
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                display: none;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar (same as dashboard) -->
        <div class="sidebar">
            <div class="logo">
                <h2>Trex moudir </h2>
                <p>Gestion d'énergie intelligente</p>
            </div>
            
            <div class="nav-menu">
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        Tableau de bord
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-bolt"></i>
                        Électricité
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-exclamation-circle"></i>
                        Réclamations
                    </a>
                </div>
                <div class="nav-item active">
                    <a href="#" class="nav-link">
                        <i class="fas fa-file-alt"></i>
                        Formulaire
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-file-invoice-dollar"></i>
                        Factures
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-comments"></i>
                        Communication
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-robot"></i>
                        ChatBot
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="page-title">
                    <h1>Formulaire de réclamation</h1>
                </div>
                
                <div class="user-menu">
                    <div class="language">
                        <select>
                            <option value="fr">FR</option>
                            <option value="en">EN</option>
                        </select>
                    </div>
                    
                    <div class="user-profile">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="User">
                        <span class="user-name">Jean Dupont</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
            </div>
            
            <!-- Complaint Form -->
            <div class="complaint-card fade-in">
                <h2 class="form-title">Envoyer une réclamation</h2>
                
                <form>
                    <div class="form-row">
                        <div class="form-group fade-in delay-1">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" id="nom" class="form-control" placeholder="Votre nom">
                        </div>
                        
                        <div class="form-group fade-in delay-1">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input type="text" id="prenom" class="form-control" placeholder="Votre prénom">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group fade-in delay-2">
                            <label for="compteur" class="form-label">N° compteur</label>
                            <input type="text" id="compteur" class="form-control" placeholder="Numéro de compteur">
                        </div>
                        
                        <div class="form-group fade-in delay-2">
                            <label for="reference" class="form-label">Référence client</label>
                            <input type="text" id="reference" class="form-control" placeholder="Votre référence">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group fade-in delay-3">
                            <label for="telephone" class="form-label">Téléphone</label>
                            <input type="tel" id="telephone" class="form-control" placeholder="Votre numéro">
                        </div>
                        
                        <div class="form-group fade-in delay-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" class="form-control" placeholder="votre@email.com">
                        </div>
                    </div>
                    
                    <div class="form-group fade-in delay-4">
                        <label for="adresse" class="form-label">Adresse</label>
                        <input type="text" id="adresse" class="form-control" placeholder="Votre adresse complète">
                    </div>
                    
                    <div class="form-group fade-in delay-4">
                        <label for="reclamation" class="form-label">Détails de la réclamation</label>
                        <textarea id="reclamation" class="form-control" placeholder="Décrivez votre réclamation en détail..."></textarea>
                    </div>
                    
                    <div class="fade-in delay-4">
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-paper-plane"></i> Envoyer la réclamation
                        </button>
                        
                        <p class="form-note">
                            Nous traiterons votre réclamation dans les plus brefs délais. Vous recevrez un accusé de réception par email avec un numéro de suivi.
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>