<?php
$page_title = "Tableau de bord";
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user data
$user_data = $_SESSION['user_data'] ?? [];
$user_name = $_SESSION['user_name'] ?? 'Utilisateur';

// Set additional CSS for dashboard-specific styling
$additional_css = '
    .dashboard {
        padding: 0;
        max-width: none;
        margin: 0;
        position: relative;
        z-index: 1;
        width: 100%;
    }

    .dashboard-header {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
        backdrop-filter: blur(20px);
        color: #1a1a1a;
        padding: 40px;
        border-radius: 20px;
        margin-bottom: 35px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        position: relative;
        overflow: hidden;
        z-index: 1;
    }

    .dashboard-header::before {
        content: "";
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
        animation: gradientShift 10s ease infinite;
        z-index: -1;
    }

    .welcome-text {
        position: relative;
        z-index: 2;
    }

    .welcome-text h2 {
        font-size: 2.5rem;
        margin-bottom: 12px;
        font-weight: 800;
        color: #1a1a1a;
        font-family: "Poppins", sans-serif;
    }

    .welcome-text p {
        color: #2d2d2d;
        font-size: 1.2rem;
        font-weight: 500;
    }

    .logout-btn {
        padding: 15px 30px;
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid rgba(255, 255, 255, 0.3);
        color: #1a1a1a;
        border-radius: 12px;
        cursor: pointer;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        backdrop-filter: blur(10px);
        position: relative;
        z-index: 2;
    }

    .logout-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }

    .dashboard-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 30px;
        margin-bottom: 35px;
        position: relative;
        z-index: 1;
        width: 100%;
    }

    .card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        position: relative;
        overflow: hidden;
        z-index: 1;
        width: 100%;
    }

    .card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, 
            rgba(255, 255, 255, 0.1) 0%, 
            rgba(255, 255, 255, 0.05) 50%,
            rgba(255, 255, 255, 0.1) 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: -1;
    }

    .card:hover::before {
        opacity: 1;
    }

    .card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
    }

    .card-header {
        display: flex;
        align-items: center;
        margin-bottom: 25px;
        position: relative;
        z-index: 2;
    }

    .card-icon {
        font-size: 2.5rem;
        margin-right: 20px;
        padding: 20px;
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        width: 70px;
        height: 70px;
        position: relative;
        overflow: hidden;
    }

    .card-icon::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        opacity: 0.2;
        animation: pulse 3s ease-in-out infinite;
    }

    .card-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: #1a1a1a;
        font-family: "Poppins", sans-serif;
    }

    .card-content {
        color: #2d2d2d;
        line-height: 1.6;
        position: relative;
        z-index: 2;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .info-item {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        padding: 20px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .info-item::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 0 2px 2px 0;
    }

    .info-item:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-2px);
    }

    .info-label {
        font-weight: 600;
        color: #1a1a1a;
        font-size: 0.9rem;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-value {
        color: #2d2d2d;
        font-size: 1.1rem;
        font-weight: 500;
        word-break: break-word;
    }

    .coming-soon {
        text-align: center;
        padding: 80px 30px;
        color: #404040;
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        position: relative;
        overflow: hidden;
    }

    .coming-soon::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        opacity: 0.1;
        animation: pulse 4s ease-in-out infinite;
    }

    .coming-soon h3 {
        font-size: 1.6rem;
        margin-bottom: 15px;
        color: #1a1a1a;
        font-weight: 700;
        position: relative;
        z-index: 2;
    }

    .coming-soon p {
        position: relative;
        z-index: 2;
        font-size: 1.1rem;
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
        .dashboard-header {
            text-align: center;
            flex-direction: column;
            padding: 30px 25px;
        }

        .welcome-text h2 {
            font-size: 2rem;
        }

        .welcome-text p {
            font-size: 1.1rem;
        }

        .card {
            padding: 25px;
        }

        .card-icon {
            font-size: 2rem;
            padding: 15px;
            width: 60px;
            height: 60px;
        }

        .card-title {
            font-size: 1.2rem;
        }

        .dashboard-content {
            grid-template-columns: 1fr;
            gap: 25px;
        }
    }

    @media (max-width: 480px) {
        .info-grid {
            grid-template-columns: 1fr;
        }

        .card-header {
            flex-direction: column;
            text-align: center;
            gap: 15px;
        }

        .card-icon {
            margin-right: 0;
        }

        .coming-soon {
            padding: 60px 20px;
        }
    }
';

// Set the content to be displayed in the layout
$content = '
<div class="dashboard">
    <div class="dashboard-header">
        <div class="welcome-text">
            <h2>Bon retour, ' . htmlspecialchars($user_name) . '! ✨</h2>
            <p>Gérez vos services de gaz et d\'électricité avec style</p>
        </div>
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            Déconnexion
        </a>
    </div>
    
    <div class="dashboard-content">
        <!-- Profile Information Card -->
        <div class="card">
            <div class="card-header">
                <div class="card-icon">👤</div>
                <div class="card-title">Informations du profil</div>
            </div>
            <div class="card-content">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Nom complet</div>
                        <div class="info-value">' . htmlspecialchars(($user_data['name'] ?? '') . ' ' . ($user_data['last_name'] ?? '')) . '</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Numéro de carte d\'identité</div>
                        <div class="info-value">' . htmlspecialchars($user_data['id_card_number'] ?? 'Non fourni') . '</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Référence</div>
                        <div class="info-value">' . htmlspecialchars($user_data['reference'] ?? 'Non fournie') . '</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Type de client</div>
                        <div class="info-value">' . htmlspecialchars(ucfirst($user_data['client_type'] ?? 'Standard')) . '</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information Card -->
        <div class="card">
            <div class="card-header">
                <div class="card-icon">📞</div>
                <div class="card-title">Informations de contact</div>
            </div>
            <div class="card-content">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value">' . htmlspecialchars($user_data['email'] ?? 'Non fourni') . '</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Téléphone</div>
                        <div class="info-value">' . htmlspecialchars($user_data['phone'] ?? 'Non fourni') . '</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Adresse</div>
                        <div class="info-value">' . htmlspecialchars($user_data['address'] ?? 'Non fournie') . '</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Ville</div>
                        <div class="info-value">' . htmlspecialchars($user_data['city'] ?? 'Non fournie') . '</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Counter Information Card -->
        <div class="card">
            <div class="card-header">
                <div class="card-icon">⚡</div>
                <div class="card-title">Informations des compteurs</div>
            </div>
            <div class="card-content">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Compteur de gaz</div>
                        <div class="info-value">' . htmlspecialchars($user_data['gas_counter_number'] ?? 'Non assigné') . '</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Compteur électrique</div>
                        <div class="info-value">' . htmlspecialchars($user_data['electric_counter_number'] ?? 'Non assigné') . '</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Type de compteur électrique</div>
                        <div class="info-value">' . htmlspecialchars(ucfirst($user_data['electric_counter_type'] ?? 'Standard')) . '</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date d\'installation</div>
                        <div class="info-value">' . htmlspecialchars($user_data['installation_date'] ?? 'Non disponible') . '</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="card">
            <div class="card-header">
                <div class="card-icon">⚡</div>
                <div class="card-title">Actions rapides</div>
            </div>
            <div class="card-content">
                <div class="info-grid">
                    <div class="info-item" style="cursor: pointer;" onclick="window.location.href=\'gas_bill.php\'">
                        <div class="info-label">Voir la facture de gaz</div>
                        <div class="info-value">Consultez votre dernière facture de gaz</div>
                    </div>
                    <div class="info-item" style="cursor: pointer;" onclick="window.location.href=\'electricity_bill.php\'">
                        <div class="info-label">Voir la facture d\'électricité</div>
                        <div class="info-value">Consultez votre dernière facture d\'électricité</div>
                    </div>
                    <div class="info-item" style="cursor: pointer;" onclick="window.location.href=\'gas_payment.php\'">
                        <div class="info-label">Effectuer un paiement</div>
                        <div class="info-value">Payez vos factures en ligne</div>
                    </div>
                    <div class="info-item" style="cursor: pointer;" onclick="window.location.href=\'gas_contact.php\'">
                        <div class="info-label">Contacter le support</div>
                        <div class="info-value">Obtenez de l\'aide et du support</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Status Card -->
        <div class="card">
            <div class="card-header">
                <div class="card-icon">📊</div>
                <div class="card-title">Statut du compte</div>
            </div>
            <div class="card-content">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Statut du compte</div>
                        <div class="info-value" style="color: #4ade80;">Actif</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Dernier paiement</div>
                        <div class="info-value">' . htmlspecialchars($user_data['last_payment'] ?? 'Aucun paiement encore') . '</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date de la prochaine facture</div>
                        <div class="info-value">' . htmlspecialchars($user_data['next_bill_date'] ?? 'Non programmée') . '</div>
                    </div>
                    
                </div>
            </div>
        </div>

        <!-- Recent Activity Card -->
        
    </div>
</div>';

// Include layout with the content
include 'layout.php';
?>