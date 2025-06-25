<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$admin_username = $_SESSION['admin_username'] ?? 'Admin';

// Get statistics with better error handling
$total_users = 0;
$total_news = 0;
$total_bills = 0;
$pending_bills = 0;
$recent_activities = [];

try {
    // Count total users
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_users = $result['total_users'] ?? 0;
    
    // Count total news
    $stmt = $pdo->query("SELECT COUNT(*) as total_news FROM news");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_news = $result['total_news'] ?? 0;
    
    // Count total bills
    $stmt = $pdo->query("SELECT COUNT(*) as total_bills FROM bills");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_bills = $result['total_bills'] ?? 0;
    
    // Count pending bills
    $stmt = $pdo->query("SELECT COUNT(*) as pending_bills FROM bills WHERE status = 'pending'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $pending_bills = $result['pending_bills'] ?? 0;

    // Count total contact messages
    $stmt = $pdo->query("SELECT COUNT(*) as total_messages FROM contact_messages");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_messages = $result['total_messages'] ?? 0;

    // Count pending contact messages
    $stmt = $pdo->query("SELECT COUNT(*) as pending_messages FROM contact_messages WHERE status = 'pending'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $pending_messages = $result['pending_messages'] ?? 0;
    
    // Recent activities
    $stmt = $pdo->query("
        SELECT 'news' as type, title as description, created_at as date FROM news 
        WHERE created_at IS NOT NULL
        UNION ALL 
        SELECT 'bill' as type, CONCAT('Facture #', bill_number, ' - ', amount, '€') as description, created_at as date FROM bills 
        WHERE created_at IS NOT NULL
        ORDER BY date DESC LIMIT 10
    ");
    $recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Database error in admin dashboard: " . $e->getMessage());
    // Keep default values
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gaztronik - Tableau de bord Admin</title>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --admin-gradient: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            --success-gradient: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
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
            padding: 20px;
        }

        .admin-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid var(--glass-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .admin-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .admin-title i {
            font-size: 2.5rem;
            background: var(--admin-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .admin-title h1 {
            font-size: 2rem;
            font-weight: 800;
            color: #1a1a1a;
            font-family: 'Poppins', sans-serif;
        }

        .admin-title p {
            color: #404040;
            font-size: 1rem;
        }

        .admin-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .logout-btn {
            padding: 12px 24px;
            background: rgba(239, 68, 68, 0.2);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.3);
            transform: translateY(-2px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid var(--glass-border);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--admin-gradient);
        }

        .stat-card.success::before {
            background: var(--success-gradient);
        }

        .stat-card.warning::before {
            background: var(--warning-gradient);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-icon {
            font-size: 2rem;
            padding: 15px;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.1);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #404040;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
        }

        .management-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .management-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid var(--glass-border);
            transition: all 0.3s ease;
        }

        .management-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .management-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .management-icon {
            font-size: 2rem;
            padding: 15px;
            border-radius: 15px;
            background: var(--admin-gradient);
            color: white;
        }

        .management-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1a1a1a;
        }

        .management-description {
            color: #404040;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .management-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: var(--admin-gradient);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .management-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 107, 53, 0.4);
        }

        .activity-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid var(--glass-border);
        }

        .activity-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .activity-icon {
            font-size: 1.5rem;
            color: #ff6b35;
        }

        .activity-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1a1a1a;
        }

        .activity-list {
            list-style: none;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-type {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .activity-type.news {
            background: rgba(77, 171, 247, 0.2);
            color: #4dabf7;
        }

        .activity-type.bill {
            background: rgba(255, 107, 53, 0.2);
            color: #ff6b35;
        }

        .activity-description {
            flex: 1;
            color: #2d2d2d;
            font-weight: 500;
        }

        .activity-date {
            color: #404040;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .management-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="admin-title">
            <i class="fas fa-shield-alt"></i>
            <div>
                <h1>Tableau de bord Admin</h1>
                <p>Bienvenue, <?php echo htmlspecialchars($admin_username); ?></p>
            </div>
        </div>
        <div class="admin-actions">
            <a href="admin_logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                Déconnexion
            </a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon" style="color: #4dabf7;">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="stat-value"><?php echo number_format($total_users); ?></div>
            <div class="stat-label">Utilisateurs totaux</div>
        </div>

        <div class="stat-card success">
            <div class="stat-header">
                <div class="stat-icon" style="color: #22c55e;">
                    <i class="fas fa-newspaper"></i>
                </div>
            </div>
            <div class="stat-value"><?php echo number_format($total_news); ?></div>
            <div class="stat-label">Actualités publiées</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon" style="color: #ff6b35;">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
            </div>
            <div class="stat-value"><?php echo number_format($total_bills); ?></div>
            <div class="stat-label">Factures totales</div>
        </div>

        <div class="stat-card warning">
            <div class="stat-header">
                <div class="stat-icon" style="color: #f59e0b;">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="stat-value"><?php echo number_format($pending_bills); ?></div>
            <div class="stat-label">Factures en attente</div>
        </div>

        <div class="stat-card warning">
            <div class="stat-header">
                <div class="stat-icon" style="color: #8b5cf6;">
                    <i class="fas fa-envelope"></i>
                </div>
            </div>
            <div class="stat-value"><?php echo number_format($pending_messages); ?></div>
            <div class="stat-label">Messages en attente</div>
        </div>
    </div>

    <div class="management-grid">
        <div class="management-card">
            <div class="management-header">
                <div class="management-icon">
                    <i class="fas fa-newspaper"></i>
                </div>
                <div class="management-title">Gestion des actualités</div>
            </div>
            <div class="management-description">
                Créez, modifiez et gérez les actualités pour les services gaz et électricité. Contrôlez la visibilité et les priorités des annonces.
            </div>
            <a href="admin_news.php" class="management-btn">
                <i class="fas fa-edit"></i>
                Gérer les actualités
            </a>
        </div>

        <div class="management-card">
            <div class="management-header">
                <div class="management-icon">
                    <i class="fas fa-fire"></i>
                </div>
                <div class="management-title">Factures gaz</div>
            </div>
            <div class="management-description">
                Créez et gérez les factures de gaz pour tous les utilisateurs. Suivez les paiements et les statuts des factures.
            </div>
            <a href="admin_gas_bills.php" class="management-btn">
                <i class="fas fa-plus-circle"></i>
                Gérer factures gaz
            </a>
        </div>

        <div class="management-card">
            <div class="management-header">
                <div class="management-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <div class="management-title">Factures électricité</div>
            </div>
            <div class="management-description">
                Créez et gérez les factures d'électricité pour tous les utilisateurs. Contrôlez les tarifs et les consommations.
            </div>
            <a href="admin_electricity_bills.php" class="management-btn">
                <i class="fas fa-plus-circle"></i>
                Gérer factures électricité
            </a>
        </div>

        <div class="management-card">
            <div class="management-header">
                <div class="management-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="management-title">Messages de contact</div>
            </div>
            <div class="management-description">
                Gérez les messages de contact des utilisateurs pour les services gaz et électricité. Répondez aux demandes et suivez leur statut.
            </div>
            <a href="admin_contact_messages.php" class="management-btn">
                <i class="fas fa-envelope-open"></i>
                Gérer les messages
            </a>
        </div>
    </div>

    <div class="activity-section">
        <div class="activity-header">
            <i class="fas fa-history activity-icon"></i>
            <div class="activity-title">Activités récentes</div>
        </div>
        
        <?php if (!empty($recent_activities)): ?>
            <ul class="activity-list">
                <?php foreach ($recent_activities as $activity): ?>
                    <li class="activity-item">
                        <span class="activity-type <?php echo $activity['type']; ?>">
                            <?php echo $activity['type'] === 'news' ? 'Actualité' : 'Facture'; ?>
                        </span>
                        <span class="activity-description">
                            <?php echo htmlspecialchars($activity['description']); ?>
                        </span>
                        <span class="activity-date">
                            <?php echo date('d/m/Y H:i', strtotime($activity['date'])); ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p style="color: #404040; text-align: center; padding: 20px;">Aucune activité récente</p>
        <?php endif; ?>
    </div>
</body>
</html>
