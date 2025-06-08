<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trex moudir  - Factures</title>
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
        
        /* Bills page styles */
        .bills-container {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .bills-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .bills-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            position: relative;
        }
        
        .bills-title::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(to right, var(--primary), var(--accent));
            border-radius: 3px;
        }
        
        .bills-actions {
            display: flex;
            gap: 1rem;
        }
        
        .filter-btn {
            background: white;
            border: 1px solid rgba(0, 0, 0, 0.1);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .filter-btn:hover {
            background: rgba(0, 0, 0, 0.02);
        }
        
        .filter-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .search-box {
            position: relative;
            width: 250px;
        }
        
        .search-box input {
            width: 100%;
            padding: 0.5rem 1rem 0.5rem 35px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        .search-box i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }
        
        /* Bills list */
        .bill-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            background: rgba(0, 0, 0, 0.01);
            border-left: 4px solid transparent;
        }
        
        .bill-item:hover {
            background: rgba(67, 97, 238, 0.03);
            border-left-color: var(--primary);
            transform: translateX(5px);
        }
        
        .bill-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .bill-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(67, 97, 238, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1.2rem;
        }
        
        .bill-details h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.3rem;
        }
        
        .bill-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.9rem;
            color: #666;
        }
        
        .bill-meta span {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .bill-amount {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--dark);
        }
        
        .bill-actions {
            display: flex;
            gap: 1rem;
        }
        
        .action-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(0, 0, 0, 0.03);
            color: var(--dark);
        }
        
        .action-btn:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }
        
        .download-btn {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .status-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-paid {
            background: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }
        
        .status-pending {
            background: rgba(248, 150, 30, 0.1);
            color: var(--warning);
        }
        
        .status-overdue {
            background: rgba(247, 37, 133, 0.1);
            color: var(--danger);
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
            gap: 0.5rem;
        }
        
        .page-btn {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            background: white;
            border: 1px solid rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .page-btn:hover {
            background: rgba(0, 0, 0, 0.02);
        }
        
        .page-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
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
            
            .bill-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .bill-actions {
                width: 100%;
                justify-content: flex-end;
            }
            
            .bills-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .search-box {
                width: 100%;
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
                        <i class="fas fa-home"></i>
                        Résidentiel
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-file-invoice-dollar"></i>
                        Factures
                    </a>
                </div>
                <div class="nav-item active">
                    <a href="#" class="nav-link">
                        <i class="fas fa-history"></i>
                        Historique
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
                    <h1>Factures et paiements</h1>
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
            
            <!-- Bills Container -->
            <div class="bills-container fade-in">
                <div class="bills-header">
                    <h2 class="bills-title">Vos factures</h2>
                    
                    <div class="bills-actions">
                        <div class="filter-btn active">
                            <i class="fas fa-list"></i> Toutes
                        </div>
                        <div class="filter-btn">
                            <i class="fas fa-check-circle"></i> Payées
                        </div>
                        <div class="filter-btn">
                            <i class="fas fa-clock"></i> En attente
                        </div>
                        
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" placeholder="Rechercher...">
                        </div>
                    </div>
                </div>
                
                <!-- Bills List -->
                <div class="bills-list">
                    <div class="bill-item fade-in">
                        <div class="bill-info">
                            <div class="bill-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <div class="bill-details">
                                <h3>Facture #STEG-2025-001</h3>
                                <div class="bill-meta">
                                    <span><i class="far fa-calendar-alt"></i> 09/01/2025</span>
                                    <span><i class="far fa-clock"></i> Période: Décembre 2024</span>
                                    <span class="status-badge status-paid">
                                        <i class="fas fa-check-circle"></i> Payée
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bill-amount">145,70 €</div>
                        
                        <div class="bill-actions">
                            <button class="download-btn">
                                <i class="fas fa-download"></i> PDF
                            </button>
                            <div class="action-btn">
                                <i class="fas fa-print"></i>
                            </div>
                            <div class="action-btn">
                                <i class="fas fa-share-alt"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bill-item fade-in delay-1">
                        <div class="bill-info">
                            <div class="bill-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <div class="bill-details">
                                <h3>Facture #STEG-2024-125</h3>
                                <div class="bill-meta">
                                    <span><i class="far fa-calendar-alt"></i> 11/01/2024</span>
                                    <span><i class="far fa-clock"></i> Période: Novembre 2024</span>
                                    <span class="status-badge status-paid">
                                        <i class="fas fa-check-circle"></i> Payée
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bill-amount">132,50 €</div>
                        
                        <div class="bill-actions">
                            <button class="download-btn">
                                <i class="fas fa-download"></i> PDF
                            </button>
                            <div class="action-btn">
                                <i class="fas fa-print"></i>
                            </div>
                            <div class="action-btn">
                                <i class="fas fa-share-alt"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bill-item fade-in delay-2">
                        <div class="bill-info">
                            <div class="bill-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <div class="bill-details">
                                <h3>Facture #STEG-2024-120</h3>
                                <div class="bill-meta">
                                    <span><i class="far fa-calendar-alt"></i> 01/01/2025</span>
                                    <span><i class="far fa-clock"></i> Période: Décembre 2024</span>
                                    <span class="status-badge status-pending">
                                        <i class="fas fa-clock"></i> En attente
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bill-amount">158,20 €</div>
                        
                        <div class="bill-actions">
                            <button class="download-btn">
                                <i class="fas fa-download"></i> PDF
                            </button>
                            <div class="action-btn">
                                <i class="fas fa-print"></i>
                            </div>
                            <div class="action-btn">
                                <i class="fas fa-share-alt"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bill-item fade-in delay-3">
                        <div class="bill-info">
                            <div class="bill-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <div class="bill-details">
                                <h3>Facture #STEG-2024-115</h3>
                                <div class="bill-meta">
                                    <span><i class="far fa-calendar-alt"></i> 03/01/2024</span>
                                    <span><i class="far fa-clock"></i> Période: Novembre 2024</span>
                                    <span class="status-badge status-overdue">
                                        <i class="fas fa-exclamation-circle"></i> En retard
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bill-amount">127,90 €</div>
                        
                        <div class="bill-actions">
                            <button class="download-btn">
                                <i class="fas fa-download"></i> PDF
                            </button>
                            <div class="action-btn">
                                <i class="fas fa-print"></i>
                            </div>
                            <div class="action-btn">
                                <i class="fas fa-credit-card"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Pagination -->
                <div class="pagination fade-in delay-4">
                    <div class="page-btn">
                        <i class="fas fa-chevron-left"></i>
                    </div>
                    <div class="page-btn active">1</div>
                    <div class="page-btn">2</div>
                    <div class="page-btn">3</div>
                    <div class="page-btn">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>