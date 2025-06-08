<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trex moudir  - Tableau de bord</title>
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
        
        /* Sidebar styles */
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
        
        /* Dashboard widgets */
        .widgets {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .widget {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .widget:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .widget::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(to bottom, var(--primary), var(--accent));
        }
        
        .widget-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .widget-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        .widget-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }
        
        .widget-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(45deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .widget-description {
            font-size: 0.9rem;
            color: #666;
        }
        
        .widget-trend {
            display: flex;
            align-items: center;
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }
        
        .trend-up {
            color: var(--success);
        }
        
        .trend-down {
            color: var(--danger);
        }
        
        /* History table */
        .history-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .card-actions button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .card-actions button:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        th {
            font-weight: 600;
            color: var(--dark);
            background: rgba(0, 0, 0, 0.02);
        }
        
        tr:hover {
            background: rgba(0, 0, 0, 0.02);
        }
        
        .badge {
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge-success {
            background: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }
        
        /* Meter reading card */
        .reading-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }
        
        .reading-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100%;
            background: linear-gradient(45deg, rgba(67, 97, 238, 0.05), transparent);
            transform: skewX(-15deg);
        }
        
        .reading-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .reading-title {
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .reading-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .reading-date {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .reading-comparison {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .reading-btn {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .reading-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.4);
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
            
            .widgets {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <h2>Trex moudir </h2>
                <p>Gestion d'énergie intelligente</p>
            </div>
            
            <div class="nav-menu">
                <div class="nav-item active">
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
                        <i class="fas fa-home"></i>
                        Résidentiel
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-industry"></i>
                        Industriel
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-history"></i>
                        Historique
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
                        <i class="fas fa-chart-line"></i>
                        Estimation
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-chart-pie"></i>
                        Consommation
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
                        <i class="fas fa-exclamation-circle"></i>
                        Réclamations
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
                    <h1>Tableau de bord</h1>
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
            
            <!-- Widgets -->
            <div class="widgets">
                <div class="widget fade-in">
                    <div class="widget-header">
                        <div class="widget-title">Consommation actuelle</div>
                        <div class="widget-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                    </div>
                    <div class="widget-value">3,2 Kwh</div>
                    <div class="widget-description">En temps réel</div>
                    <div class="widget-trend trend-up">
                        <i class="fas fa-arrow-up"></i>
                        <span>12% vs hier</span>
                    </div>
                </div>
                
                <div class="widget fade-in delay-1">
                    <div class="widget-header">
                        <div class="widget-title">Coût estimé</div>
                        <div class="widget-icon">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>
                    <div class="widget-value">45,70 €</div>
                    <div class="widget-description">Ce mois-ci</div>
                    <div class="widget-trend trend-down">
                        <i class="fas fa-arrow-down"></i>
                        <span>5% vs mois dernier</span>
                    </div>
                </div>
                
                <div class="widget fade-in delay-2">
                    <div class="widget-header">
                        <div class="widget-title">Émissions CO₂</div>
                        <div class="widget-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                    </div>
                    <div class="widget-value">12,4 kg</div>
                    <div class="widget-description">Économies ce mois</div>
                    <div class="widget-trend trend-up">
                        <i class="fas fa-arrow-up"></i>
                        <span>8% mieux que la moyenne</span>
                    </div>
                </div>
            </div>
            
            <!-- History Table -->
            <div class="history-card fade-in delay-3">
                <div class="card-header">
                    <div class="card-title">Historique de consommation</div>
                    <div class="card-actions">
                        <button>Exporter les données</button>
                    </div>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Consommation</th>
                            <th>Statut</th>
                            <th>Coût</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>18/04/2025</td>
                            <td>10,2 m3</td>
                            <td><span class="badge badge-success">Normal</span></td>
                            <td>15,30 €</td>
                        </tr>
                        <tr>
                            <td>17/04/2025</td>
                            <td>11,2 m3</td>
                            <td><span class="badge badge-success">Normal</span></td>
                            <td>16,80 €</td>
                        </tr>
                        <tr>
                            <td>16/04/2025</td>
                            <td>9,5 m3</td>
                            <td><span class="badge badge-success">Normal</span></td>
                            <td>14,25 €</td>
                        </tr>
                        <tr>
                            <td>16/04/2025</td>
                            <td>13 m3</td>
                            <td><span class="badge badge-warning">Élevé</span></td>
                            <td>19,50 €</td>
                        </tr>
                        <tr>
                            <td>16/04/2025</td>
                            <td>8,5 m3</td>
                            <td><span class="badge badge-success">Normal</span></td>
                            <td>12,75 €</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Meter Reading -->
            <div class="reading-card fade-in delay-4">
                <div class="reading-header">
                    <div class="reading-title">Dernière lecture</div>
                    <div class="reading-icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                </div>
                
                <div class="reading-value">123,8 m</div>
                <div class="reading-date">12 avril 2025</div>
                
                <div class="reading-comparison">
                    <i class="fas fa-arrow-up text-danger"></i>
                    <span>Consommation +1,5 m3 depuis la dernière lecture</span>
                </div>
                
                <button class="reading-btn">Nouveau relevé : valider</button>
            </div>
        </div>
    </div>
</body>
</html>