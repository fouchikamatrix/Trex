<?php
$page_title = "Informations gaz";
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_data = $_SESSION['user_data'] ?? [];
$user_name = $_SESSION['user_name'] ?? 'Utilisateur';

// Get gas information data
try {
    $stmt = $pdo->prepare("
        SELECT * FROM information 
        WHERE user_id = ? AND service_type = 'gas' 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $information = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $information = [];
    error_log("Gas information error: " . $e->getMessage());
}

$additional_css = '
    .info-container {
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    .info-header {
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

    .info-header::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(45deg, 
            rgba(255, 107, 53, 0.1) 0%, 
            rgba(247, 147, 30, 0.1) 50%,
            rgba(255, 107, 53, 0.1) 100%);
        background-size: 400% 400%;
        animation: gradientShift 10s ease infinite;
        z-index: -1;
    }

    .info-header h1 {
        color: #1a1a1a;
        font-size: 2.5rem;
        margin-bottom: 15px;
        font-weight: 800;
    }

    .info-header p {
        color: #2d2d2d;
        font-size: 1.2rem;
    }

    .info-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    .info-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    }

    .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .card-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1a1a1a;
        font-family: "Poppins", sans-serif;
    }

    .card-icon {
        font-size: 1.5rem;
        color: #ff6b35;
    }

    .info-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .stat-item {
        text-align: center;
        padding: 15px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #ff6b35;
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 0.85rem;
        color: #404040;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-content {
        color: #2d2d2d;
        line-height: 1.6;
    }

    .info-list {
        list-style: none;
        padding: 0;
    }

    .info-list li {
        padding: 10px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .info-list li:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: #1a1a1a;
    }

    .info-value {
        color: #2d2d2d;
    }

    .no-info {
        text-align: center;
        padding: 60px 30px;
        color: #404040;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .no-info i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    @media (max-width: 768px) {
        .info-container {
            padding: 0 15px;
        }
        
        .info-grid {
            grid-template-columns: 1fr;
        }
    }
';

$content = '
<div class="info-container">
    <div class="info-header">
        <div class="info-icon">
            <i class="fas fa-info-circle"></i>
        </div>
        <h1>Informations du service gaz</h1>
        <p>Informations détaillées sur votre service gaz et votre compte</p>
    </div>

    <div class="info-grid">
        <!-- Account Information -->
        <div class="info-card">
            <div class="card-header">
                <h3 class="card-title">Détails du compte</h3>
                <i class="fas fa-user card-icon"></i>
            </div>
            <ul class="info-list">
                <li>
                    <span class="info-label">Numéro de compte</span>
                    <span class="info-value">' . htmlspecialchars($user_data['gas_counter_number'] ?? 'Non assigné') . '</span>
                </li>
                <li>
                    <span class="info-label">ID client</span>
                    <span class="info-value">' . htmlspecialchars($user_data['id_card_number'] ?? 'Non fourni') . '</span>
                </li>
                <li>
                    <span class="info-label">Type de service</span>
                    <span class="info-value">Gaz résidentiel</span>
                </li>
                <li>
                    <span class="info-label">Date de connexion</span>
                    <span class="info-value">' . htmlspecialchars($user_data['installation_date'] ?? 'Non disponible') . '</span>
                </li>
                <li>
                    <span class="info-label">Plan tarifaire</span>
                    <span class="info-value">Résidentiel standard</span>
                </li>
            </ul>
        </div>

        <!-- Meter Information -->
        <div class="info-card">
            <div class="card-header">
                <h3 class="card-title">Informations du compteur</h3>
                <i class="fas fa-tachometer-alt card-icon"></i>
            </div>
            <ul class="info-list">
                <li>
                    <span class="info-label">Numéro de compteur</span>
                    <span class="info-value">' . htmlspecialchars($user_data['gas_counter_number'] ?? 'Non assigné') . '</span>
                </li>
                <li>
                    <span class="info-label">Type de compteur</span>
                    <span class="info-value">Compteur intelligent numérique</span>
                </li>
                <li>
                    <span class="info-label">Dernière lecture</span>
                    <span class="info-value">1,247 m³</span>
                </li>
                <li>
                    <span class="info-label">Date de lecture</span>
                    <span class="info-value">' . date('d M Y', strtotime('-5 days')) . '</span>
                </li>
                <li>
                    <span class="info-label">Prochaine lecture</span>
                    <span class="info-value">' . date('d M Y', strtotime('+25 days')) . '</span>
                </li>
            </ul>
        </div>

        <!-- Service Statistics -->
        <div class="info-card">
            <div class="card-header">
                <h3 class="card-title">Statistiques du service</h3>
                <i class="fas fa-chart-bar card-icon"></i>
            </div>
            <div class="info-stats">
                <div class="stat-item">
                    <div class="stat-value">98.5%</div>
                    <div class="stat-label">Disponibilité</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">24/7</div>
                    <div class="stat-label">Support</div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-value">0</div>
                    <div class="stat-label">Problèmes</div>
                </div>
            </div>
            <div class="info-content">
                <p>Votre service gaz a maintenu une excellente fiabilité avec des interruptions minimales. Notre équipe de support 24/7 est toujours disponible pour toute assistance dont vous pourriez avoir besoin.</p>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="info-card">
            <div class="card-header">
                <h3 class="card-title">Contacts d\'urgence</h3>
                <i class="fas fa-phone card-icon"></i>
            </div>
            <ul class="info-list">
                <li>
                    <span class="info-label">Ligne d\'urgence</span>
                    <span class="info-value">+1 (555) 197</span>
                </li>
                <li>
                    <span class="info-label">Service client</span>
                    <span class="info-value">+1 (555) 92-654-654</span>
                </li>
                <li>
                    <span class="info-label">Support technique</span>
                    <span class="info-value">+1 (555) 97-651-325</span>
                </li>
                <li>
                    <span class="info-label">Support par email</span>
                    <span class="info-value">gas@Gaztronik.com</span>
                </li>
                <li>
                    <span class="info-label">Heures de service</span>
                    <span class="info-value">24/7 Urgence</span>
                </li>
            </ul>
        </div>

        <!-- Safety Information -->
        <div class="info-card">
            <div class="card-header">
                <h3 class="card-title">Consignes de sécurité</h3>
                <i class="fas fa-shield-alt card-icon"></i>
            </div>
            <div class="info-content">
                <ul style="list-style: disc; padding-left: 20px; color: #2d2d2d;">
                    <li style="margin-bottom: 10px;">N\'ignorez jamais l\'odeur de gaz - évacuez immédiatement</li>
                    <li style="margin-bottom: 10px;">Gardez les appareils à gaz bien ventilés</li>
                    <li style="margin-bottom: 10px;">Planifiez des inspections de sécurité annuelles</li>
                    <li style="margin-bottom: 10px;">Connaissez l\'emplacement de votre vanne d\'arrêt de gaz</li>
                    <li style="margin-bottom: 10px;">Installez des détecteurs de gaz dans votre maison</li>
                </ul>
            </div>
        </div>

        <!-- Billing Information -->
        <div class="info-card">
            <div class="card-header">
                <h3 class="card-title">Informations de facturation</h3>
                <i class="fas fa-file-invoice-dollar card-icon"></i>
            </div>
            <ul class="info-list">
                <li>
                    <span class="info-label">Cycle de facturation</span>
                    <span class="info-value">Mensuel</span>
                </li>
                <li>
                    <span class="info-label">Date d\'échéance</span>
                    <span class="info-value">' . date('jS', strtotime('+15 days')) . ' de chaque mois</span>
                </li>
                <li>
                    <span class="info-label">Mode de paiement</span>
                    <span class="info-value">Paiement automatique activé</span>
                </li>
                <li>
                    <span class="info-label">Tarif actuel</span>
                    <span class="info-value">0,85 TND par m³</span>
                </li>
                <li>
                    <span class="info-label">Moyenne mensuelle</span>
                    <span class="info-value">127,50 TND</span>
                </li>
            </ul>
        </div>
    </div>
</div>';

include 'layout.php';
?>