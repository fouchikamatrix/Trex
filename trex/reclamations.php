<?php
$page_title = "Mes réclamations";
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_data = $_SESSION['user_data'] ?? [];
$user_name = $_SESSION['user_name'] ?? 'Utilisateur';

// Get user's contact messages
try {
    $stmt = $pdo->prepare("
        SELECT cm.*, au.username as admin_username
        FROM contact_messages cm
        LEFT JOIN admin_users au ON cm.admin_id = au.id
        WHERE cm.user_id = ?
        ORDER BY cm.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $messages = [];
    error_log("Error fetching user messages: " . $e->getMessage());
}

// Get statistics for user
$stats = [
    'total' => count($messages),
    'pending' => count(array_filter($messages, fn($m) => $m['status'] === 'pending')),
    'in_progress' => count(array_filter($messages, fn($m) => $m['status'] === 'in_progress')),
    'resolved' => count(array_filter($messages, fn($m) => $m['status'] === 'resolved')),
];

$additional_css = '
    .reclamations-container {
        max-width: 1000px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    .reclamations-header {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
        backdrop-filter: blur(20px);
        color: #1a1a1a;
        padding: 40px;
        border-radius: 20px;
        margin-bottom: 35px;
        text-align: center;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        position: relative;
        overflow: hidden;
    }

    .reclamations-header::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(45deg, 
            rgba(147, 51, 234, 0.1) 0%, 
            rgba(79, 70, 229, 0.1) 50%,
            rgba(147, 51, 234, 0.1) 100%);
        background-size: 400% 400%;
        animation: gradientShift 10s ease infinite;
        z-index: -1;
    }

    .reclamations-header h1 {
        color: #1a1a1a;
        font-size: 2.5rem;
        margin-bottom: 15px;
        font-weight: 800;
    }

    .reclamations-header p {
        color: #2d2d2d;
        font-size: 1.2rem;
    }

    .reclamations-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        background: linear-gradient(135deg, #9333ea 0%, #4f46e5 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
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
        border: 1px solid rgba(255, 255, 255, 0.2);
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

    .messages-section {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        padding: 30px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        margin-bottom: 30px;
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
        position: relative;
        overflow: hidden;
    }

    .message-card:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: translateY(-2px);
    }

    .message-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--status-color, #9333ea);
        border-radius: 0 2px 2px 0;
    }

    .message-card.pending::before {
        --status-color: #f59e0b;
    }

    .message-card.in-progress::before {
        --status-color: #4dabf7;
    }

    .message-card.resolved::before {
        --status-color: #22c55e;
    }

    .message-card.closed::before {
        --status-color: #6b7280;
    }

    .message-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
        gap: 15px;
        flex-wrap: wrap;
    }

    .message-title {
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

    .message-date {
        color: #666;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .message-content {
        color: #2d2d2d;
        line-height: 1.6;
        margin-bottom: 15px;
        background: rgba(255, 255, 255, 0.05);
        padding: 15px;
        border-radius: 10px;
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
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .admin-response p {
        color: #2d2d2d;
        line-height: 1.5;
    }

    .no-messages {
        text-align: center;
        padding: 60px 30px;
        color: #404040;
    }

    .no-messages i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .no-messages h3 {
        color: #1a1a1a;
        font-size: 1.6rem;
        margin-bottom: 15px;
        font-weight: 700;
    }

    .contact-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 30px;
    }

    .contact-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px);
        padding: 25px;
        border-radius: 15px;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
        text-decoration: none;
        color: inherit;
    }

    .contact-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        text-decoration: none;
        color: inherit;
    }

    .contact-card-icon {
        font-size: 2.5rem;
        margin-bottom: 15px;
        background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .contact-card.electricity .contact-card-icon {
        background: linear-gradient(135deg, #4dabf7 0%, #2196f3 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .contact-card h3 {
        color: #1a1a1a;
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .contact-card p {
        color: #2d2d2d;
        font-size: 0.9rem;
    }

    @media (max-width: 768px) {
        .reclamations-container {
            padding: 0 15px;
        }
        
        .message-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .contact-actions {
            grid-template-columns: 1fr;
        }
    }
';

$content = '
<div class="reclamations-container">
    <div class="reclamations-header">
        <div class="reclamations-icon">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <h1>Mes réclamations</h1>
        <p>Suivez le statut de vos demandes et réclamations</p>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">' . $stats['total'] . '</div>
            <div class="stat-label">Total messages</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #f59e0b;">' . $stats['pending'] . '</div>
            <div class="stat-label">En attente</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #4dabf7;">' . $stats['in_progress'] . '</div>
            <div class="stat-label">En cours</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #22c55e;">' . $stats['resolved'] . '</div>
            <div class="stat-label">Résolus</div>
        </div>
    </div>

    <!-- Messages -->
    <div class="messages-section">
        <h2 class="section-title">
            <i class="fas fa-envelope"></i>
            Historique de mes messages (' . count($messages) . ')
        </h2>';

if (!empty($messages)) {
    foreach ($messages as $message) {
        // Status and priority labels
        $status_labels = [
            'pending' => 'En attente',
            'in_progress' => 'En cours',
            'resolved' => 'Résolu',
            'closed' => 'Fermé'
        ];
        
        $priority_labels = [
            'high' => 'Élevée',
            'normal' => 'Normale',
            'low' => 'Faible'
        ];
        
        $category_labels = [
            'billing' => 'Facturation',
            'outage' => 'Panne',
            'leak' => 'Fuite',
            'meter' => 'Compteur',
            'connection' => 'Connexion',
            'pressure' => 'Pression',
            'voltage' => 'Tension',
            'safety' => 'Sécurité',
            'maintenance' => 'Maintenance',
            'complaint' => 'Réclamation',
            'general' => 'Général'
        ];
        
        $content .= '
        <div class="message-card ' . $message['status'] . '">
            <div class="message-header">
                <div>
                    <div class="message-title">' . htmlspecialchars($message['subject']) . '</div>
                    <div class="message-meta">
                        <span class="badge badge-' . $message['status'] . '">
                            ' . ($status_labels[$message['status']] ?? $message['status']) . '
                        </span>
                        <span class="badge badge-' . $message['priority'] . '">
                            Priorité ' . ($priority_labels[$message['priority']] ?? $message['priority']) . '
                        </span>
                        <span class="badge" style="background: rgba(147, 51, 234, 0.2); color: #9333ea;">
                            ' . ($category_labels[$message['category']] ?? ucfirst($message['category'])) . '
                        </span>
                    </div>
                </div>
                <div class="message-date">
                    <i class="fas fa-clock"></i>
                    ' . date('d/m/Y H:i', strtotime($message['created_at'])) . '
                </div>
            </div>

            <div class="message-content">
                ' . nl2br(htmlspecialchars($message['message'])) . '
            </div>';
            
        if (!empty($message['admin_response'])) {
            $content .= '
            <div class="admin-response">
                <h4>
                    <i class="fas fa-reply"></i>
                    Réponse de l\'équipe support' . (!empty($message['admin_username']) ? ' (' . htmlspecialchars($message['admin_username']) . ')' : '') . '
                </h4>
                <p>' . nl2br(htmlspecialchars($message['admin_response'])) . '</p>
            </div>';
        }
        
        if ($message['updated_at'] !== $message['created_at']) {
            $content .= '
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255, 255, 255, 0.1); color: #666; font-size: 0.85rem;">
                <i class="fas fa-edit"></i> Dernière mise à jour: ' . date('d/m/Y H:i', strtotime($message['updated_at'])) . '
            </div>';
        }
        
        $content .= '</div>';
    }
} else {
    $content .= '
        <div class="no-messages">
            <i class="fas fa-inbox"></i>
            <h3>Aucun message envoyé</h3>
            <p>Vous n\'avez encore envoyé aucun message de contact. Utilisez les liens ci-dessous pour contacter notre équipe.</p>
        </div>';
}

$content .= '
    </div>

    <!-- Contact Actions -->
    <div class="contact-actions">
        <a href="gas_contact.php" class="contact-card">
            <div class="contact-card-icon">
                <i class="fas fa-fire"></i>
            </div>
            <h3>Contacter le service gaz</h3>
            <p>Problèmes de gaz, fuites, facturation, maintenance</p>
        </a>

        <a href="electricity_contact.php" class="contact-card electricity">
            <div class="contact-card-icon">
                <i class="fas fa-bolt"></i>
            </div>
            <h3>Contacter le service électricité</h3>
            <p>Pannes électriques, compteurs, facturation, maintenance</p>
        </a>
    </div>
</div>';

include 'layout.php';
?>
