<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_status':
                $message_id = $_POST['message_id'] ?? '';
                $new_status = $_POST['new_status'] ?? '';
                $admin_response = trim($_POST['admin_response'] ?? '');
                
                if (!empty($message_id) && !empty($new_status)) {
                    try {
                        $stmt = $pdo->prepare("
                            UPDATE contact_messages 
                            SET status = ?, admin_response = ?, admin_id = ?, updated_at = NOW() 
                            WHERE id = ?
                        ");
                        $stmt->execute([$new_status, $admin_response, $_SESSION['admin_id'], $message_id]);
                        $success_message = 'Message mis à jour avec succès !';
                    } catch (PDOException $e) {
                        $error_message = 'Erreur lors de la mise à jour: ' . $e->getMessage();
                        error_log("Contact message update error: " . $e->getMessage());
                    }
                }
                break;
                
            case 'delete':
                $message_id = $_POST['message_id'] ?? '';
                if (!empty($message_id)) {
                    try {
                        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
                        $stmt->execute([$message_id]);
                        $success_message = 'Message supprimé avec succès !';
                    } catch (PDOException $e) {
                        $error_message = 'Erreur lors de la suppression: ' . $e->getMessage();
                        error_log("Contact message deletion error: " . $e->getMessage());
                    }
                }
                break;
        }
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$priority_filter = $_GET['priority'] ?? '';
$category_filter = $_GET['category'] ?? '';

// Build query with filters
$where_conditions = [];
$params = [];

if (!empty($status_filter)) {
    $where_conditions[] = "cm.status = ?";
    $params[] = $status_filter;
}

if (!empty($priority_filter)) {
    $where_conditions[] = "cm.priority = ?";
    $params[] = $priority_filter;
}

if (!empty($category_filter)) {
    $where_conditions[] = "cm.category = ?";
    $params[] = $category_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get contact messages with user information
try {
    $stmt = $pdo->prepare("
        SELECT cm.*, u.name as user_name, u.email as user_email, au.username as admin_username
        FROM contact_messages cm
        LEFT JOIN users u ON cm.user_id = u.id
        LEFT JOIN admin_users au ON cm.admin_id = au.id
        $where_clause
        ORDER BY 
            CASE cm.priority 
                WHEN 'high' THEN 1 
                WHEN 'normal' THEN 2 
                WHEN 'low' THEN 3 
            END,
            cm.created_at DESC
    ");
    $stmt->execute($params);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $messages = [];
    error_log("Error fetching contact messages: " . $e->getMessage());
}

// Get statistics
try {
    $stats_stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
            SUM(CASE WHEN priority = 'high' THEN 1 ELSE 0 END) as high_priority
        FROM contact_messages
    ");
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stats = ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'resolved' => 0, 'high_priority' => 0];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gaztronik - Gestion des messages de contact</title>
    
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

        .back-btn {
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.1);
            color: #1a1a1a;
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 15px;
            padding: 25px;
            border: 1px solid var(--glass-border);
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #404040;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filters-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid var(--glass-border);
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filter-group label {
            font-weight: 600;
            color: #1a1a1a;
            font-size: 0.9rem;
        }

        .filter-group select {
            padding: 10px 15px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            color: #1a1a1a;
            font-weight: 500;
        }

        .filter-btn {
            padding: 10px 20px;
            background: var(--admin-gradient);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 107, 53, 0.4);
        }

        .messages-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid var(--glass-border);
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .message-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .message-card:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            gap: 15px;
            flex-wrap: wrap;
        }

        .message-info h3 {
            color: #1a1a1a;
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .message-meta {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-pending {
            background: rgba(251, 191, 36, 0.2);
            color: #f59e0b;
        }

        .badge-in-progress {
            background: rgba(77, 171, 247, 0.2);
            color: #4dabf7;
        }

        .badge-resolved {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
        }

        .badge-closed {
            background: rgba(156, 163, 175, 0.2);
            color: #6b7280;
        }

        .badge-high {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        .badge-normal {
            background: rgba(77, 171, 247, 0.2);
            color: #4dabf7;
        }

        .badge-low {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
        }

        .message-content {
            color: #2d2d2d;
            line-height: 1.6;
            margin-bottom: 15px;
            background: rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 10px;
        }

        .message-actions {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .action-form {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .action-select {
            padding: 8px 12px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: #1a1a1a;
            font-weight: 500;
        }

        .action-textarea {
            width: 100%;
            min-width: 300px;
            padding: 10px 15px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: #1a1a1a;
            font-weight: 500;
            resize: vertical;
            min-height: 80px;
        }

        .action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
        }

        .btn-update {
            background: var(--admin-gradient);
            color: white;
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.2);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .action-btn:hover {
            transform: translateY(-1px);
        }

        .admin-response {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
        }

        .admin-response h4 {
            color: #16a34a;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .admin-response p {
            color: #2d2d2d;
            line-height: 1.5;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert.success {
            background: rgba(34, 197, 94, 0.1);
            color: #16a34a;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .alert.error {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .no-messages {
            text-align: center;
            padding: 40px;
            color: #404040;
        }

        .no-messages i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                text-align: center;
            }

            .message-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .action-form {
                flex-direction: column;
                align-items: stretch;
            }

            .action-textarea {
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="admin-title">
            <i class="fas fa-comments"></i>
            <h1>Gestion des messages de contact</h1>
        </div>
        <a href="admin_dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Retour au tableau de bord
        </a>
    </div>

    <?php if (!empty($success_message)): ?>
        <div class="alert success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="alert error">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?php echo $stats['total']; ?></div>
            <div class="stat-label">Total messages</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #f59e0b;"><?php echo $stats['pending']; ?></div>
            <div class="stat-label">En attente</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #4dabf7;"><?php echo $stats['in_progress']; ?></div>
            <div class="stat-label">En cours</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #22c55e;"><?php echo $stats['resolved']; ?></div>
            <div class="stat-label">Résolus</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #ef4444;"><?php echo $stats['high_priority']; ?></div>
            <div class="stat-label">Priorité élevée</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-grid">
            <div class="filter-group">
                <label for="status">Statut</label>
                <select id="status" name="status">
                    <option value="">Tous les statuts</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>En attente</option>
                    <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>>En cours</option>
                    <option value="resolved" <?php echo $status_filter === 'resolved' ? 'selected' : ''; ?>>Résolu</option>
                    <option value="closed" <?php echo $status_filter === 'closed' ? 'selected' : ''; ?>>Fermé</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="priority">Priorité</label>
                <select id="priority" name="priority">
                    <option value="">Toutes les priorités</option>
                    <option value="high" <?php echo $priority_filter === 'high' ? 'selected' : ''; ?>>Élevée</option>
                    <option value="normal" <?php echo $priority_filter === 'normal' ? 'selected' : ''; ?>>Normale</option>
                    <option value="low" <?php echo $priority_filter === 'low' ? 'selected' : ''; ?>>Faible</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="category">Catégorie</label>
                <select id="category" name="category">
                    <option value="">Toutes les catégories</option>
                    <option value="billing" <?php echo $category_filter === 'billing' ? 'selected' : ''; ?>>Facturation</option>
                    <option value="outage" <?php echo $category_filter === 'outage' ? 'selected' : ''; ?>>Panne</option>
                    <option value="leak" <?php echo $category_filter === 'leak' ? 'selected' : ''; ?>>Fuite</option>
                    <option value="meter" <?php echo $category_filter === 'meter' ? 'selected' : ''; ?>>Compteur</option>
                    <option value="connection" <?php echo $category_filter === 'connection' ? 'selected' : ''; ?>>Connexion</option>
                    <option value="safety" <?php echo $category_filter === 'safety' ? 'selected' : ''; ?>>Sécurité</option>
                    <option value="maintenance" <?php echo $category_filter === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                    <option value="complaint" <?php echo $category_filter === 'complaint' ? 'selected' : ''; ?>>Réclamation</option>
                    <option value="general" <?php echo $category_filter === 'general' ? 'selected' : ''; ?>>Général</option>
                </select>
            </div>
            
            <div class="filter-group">
                <button type="submit" class="filter-btn">
                    <i class="fas fa-filter"></i>
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Messages -->
    <div class="messages-section">
        <h2 class="section-title">
            <i class="fas fa-envelope"></i>
            Messages de contact (<?php echo count($messages); ?>)
        </h2>

        <?php if (!empty($messages)): ?>
            <?php foreach ($messages as $message): ?>
                <div class="message-card">
                    <div class="message-header">
                        <div class="message-info">
                            <h3><?php echo htmlspecialchars($message['subject']); ?></h3>
                            <div class="message-meta">
                                <span class="badge badge-<?php echo $message['status']; ?>">
                                    <?php 
                                    $status_labels = [
                                        'pending' => 'En attente',
                                        'in_progress' => 'En cours',
                                        'resolved' => 'Résolu',
                                        'closed' => 'Fermé'
                                    ];
                                    echo $status_labels[$message['status']] ?? $message['status'];
                                    ?>
                                </span>
                                <span class="badge badge-<?php echo $message['priority']; ?>">
                                    <?php 
                                    $priority_labels = [
                                        'high' => 'Élevée',
                                        'normal' => 'Normale',
                                        'low' => 'Faible'
                                    ];
                                    echo $priority_labels[$message['priority']] ?? $message['priority'];
                                    ?>
                                </span>
                                <span class="badge" style="background: rgba(147, 51, 234, 0.2); color: #9333ea;">
                                    <?php echo ucfirst($message['category']); ?>
                                </span>
                            </div>
                            <p style="color: #666; font-size: 0.9rem;">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($message['user_name']); ?> 
                                (<?php echo htmlspecialchars($message['user_email']); ?>)
                                <br>
                                <i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($message['created_at'])); ?>
                                <?php if ($message['updated_at'] !== $message['created_at']): ?>
                                    - Mis à jour: <?php echo date('d/m/Y H:i', strtotime($message['updated_at'])); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    <div class="message-content">
                        <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                    </div>

                    <?php if (!empty($message['admin_response'])): ?>
                        <div class="admin-response">
                            <h4><i class="fas fa-reply"></i> Réponse admin (<?php echo htmlspecialchars($message['admin_username'] ?? 'Admin'); ?>)</h4>
                            <p><?php echo nl2br(htmlspecialchars($message['admin_response'])); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="message-actions">
                        <form method="POST" class="action-form">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                            
                            <select name="new_status" class="action-select" required>
                                <option value="">Changer le statut</option>
                                <option value="pending" <?php echo $message['status'] === 'pending' ? 'selected' : ''; ?>>En attente</option>
                                <option value="in_progress" <?php echo $message['status'] === 'in_progress' ? 'selected' : ''; ?>>En cours</option>
                                <option value="resolved" <?php echo $message['status'] === 'resolved' ? 'selected' : ''; ?>>Résolu</option>
                                <option value="closed" <?php echo $message['status'] === 'closed' ? 'selected' : ''; ?>>Fermé</option>
                            </select>
                            
                            <textarea name="admin_response" class="action-textarea" 
                                      placeholder="Réponse admin (optionnel)..."><?php echo htmlspecialchars($message['admin_response'] ?? ''); ?></textarea>
                            
                            <button type="submit" class="action-btn btn-update">
                                <i class="fas fa-save"></i>
                                Mettre à jour
                            </button>
                        </form>

                        <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                            <button type="submit" class="action-btn btn-delete">
                                <i class="fas fa-trash"></i>
                                Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-messages">
                <i class="fas fa-inbox"></i>
                <h3>Aucun message trouvé</h3>
                <p>Il n'y a aucun message correspondant aux filtres sélectionnés.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Auto-hide success/error messages
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.transition = 'all 0.5s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            });
        }, 5000);
    </script>
</body>
</html>
