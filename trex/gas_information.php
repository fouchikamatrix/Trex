<?php
$page_title = "Gas Information";
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_data = $_SESSION['user_data'] ?? [];
$user_name = $_SESSION['user_name'] ?? 'User';

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
        color: white;
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
        color: white;
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
        color: rgba(255, 255, 255, 0.8);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-content {
        color: rgba(255, 255, 255, 0.9);
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
        color: white;
    }

    .info-value {
        color: rgba(255, 255, 255, 0.8);
    }

    .no-info {
        text-align: center;
        padding: 60px 30px;
        color: rgba(255, 255, 255, 0.8);
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
        <h1>Gas Service Information</h1>
        <p>Detailed information about your gas service and account</p>
    </div>

    <div class="info-grid">
        <!-- Account Information -->
        <div class="info-card">
            <div class="card-header">
                <h3 class="card-title">Account Details</h3>
                <i class="fas fa-user card-icon"></i>
            </div>
            <ul class="info-list">
                <li>
                    <span class="info-label">Account Number</span>
                    <span class="info-value">' . htmlspecialchars($user_data['gas_counter_number'] ?? 'Not assigned') . '</span>
                </li>
                <li>
                    <span class="info-label">Customer ID</span>
                    <span class="info-value">' . htmlspecialchars($user_data['id_card_number'] ?? 'Not provided') . '</span>
                </li>
                <li>
                    <span class="info-label">Service Type</span>
                    <span class="info-value">Residential Gas</span>
                </li>
                <li>
                    <span class="info-label">Connection Date</span>
                    <span class="info-value">' . htmlspecialchars($user_data['installation_date'] ?? 'Not available') . '</span>
                </li>
                <li>
                    <span class="info-label">Tariff Plan</span>
                    <span class="info-value">Standard Residential</span>
                </li>
            </ul>
        </div>

        <!-- Meter Information -->
        <div class="info-card">
            <div class="card-header">
                <h3 class="card-title">Meter Information</h3>
                <i class="fas fa-tachometer-alt card-icon"></i>
            </div>
            <ul class="info-list">
                <li>
                    <span class="info-label">Meter Number</span>
                    <span class="info-value">' . htmlspecialchars($user_data['gas_counter_number'] ?? 'Not assigned') . '</span>
                </li>
                <li>
                    <span class="info-label">Meter Type</span>
                    <span class="info-value">Digital Smart Meter</span>
                </li>
                <li>
                    <span class="info-label">Last Reading</span>
                    <span class="info-value">1,247 m³</span>
                </li>
                <li>
                    <span class="info-label">Reading Date</span>
                    <span class="info-value">' . date('M d, Y', strtotime('-5 days')) . '</span>
                </li>
                <li>
                    <span class="info-label">Next Reading</span>
                    <span class="info-value">' . date('M d, Y', strtotime('+25 days')) . '</span>
                </li>
            </ul>
        </div>

        <!-- Service Statistics -->
        <div class="info-card">
            <div class="card-header">
                <h3 class="card-title">Service Statistics</h3>
                <i class="fas fa-chart-bar card-icon"></i>
            </div>
            <div class="info-stats">
                <div class="stat-item">
                    <div class="stat-value">98.5%</div>
                    <div class="stat-label">Uptime</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">24/7</div>
                    <div class="stat-label">Support</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">5</div>
                    <div class="stat-label">Years</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">0</div>
                    <div class="stat-label">Issues</div>
                </div>
            </div>
            <div class="info-content">
                <p>Your gas service has maintained excellent reliability with minimal interruptions. Our 24/7 support team is always available for any assistance you may need.</p>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="info-card">
            <div class="card-header">
                <h3 class="card-title">Emergency Contacts</h3>
                <i class="fas fa-phone card-icon"></i>
            </div>
            <ul class="info-list">
                <li>
                    <span class="info-label">Emergency Hotline</span>
                    <span class="info-value">+1 (555) 911-GAS</span>
                </li>
                <li>
                    <span class="info-label">Customer Service</span>
                    <span class="info-value">+1 (555) 123-4567</span>
                </li>
                <li>
                    <span class="info-label">Technical Support</span>
                    <span class="info-value">+1 (555) 789-0123</span>
                </li>
                <li>
                    <span class="info-label">Email Support</span>
                    <span class="info-value">gas@voltgaz.com</span>
                </li>
                <li>
                    <span class="info-label">Service Hours</span>
                    <span class="info-value">24/7 Emergency</span>
                </li>
            </ul>
        </div>

        <!-- Safety Information -->
        <div class="info-card">
            <div class="card-header">
                <h3 class="card-title">Safety Guidelines</h3>
                <i class="fas fa-shield-alt card-icon"></i>
            </div>
            <div class="info-content">
                <ul style="list-style: disc; padding-left: 20px; color: rgba(255, 255, 255, 0.9);">
                    <li style="margin-bottom: 10px;">Never ignore the smell of gas - evacuate immediately</li>
                    <li style="margin-bottom: 10px;">Keep gas appliances well-ventilated</li>
                    <li style="margin-bottom: 10px;">Schedule annual safety inspections</li>
                    <li style="margin-bottom: 10px;">Know the location of your gas shut-off valve</li>
                    <li style="margin-bottom: 10px;">Install gas detectors in your home</li>
                </ul>
            </div>
        </div>

        <!-- Billing Information -->
        <div class="info-card">
            <div class="card-header">
                <h3 class="card-title">Billing Information</h3>
                <i class="fas fa-file-invoice-dollar card-icon"></i>
            </div>
            <ul class="info-list">
                <li>
                    <span class="info-label">Billing Cycle</span>
                    <span class="info-value">Monthly</span>
                </li>
                <li>
                    <span class="info-label">Due Date</span>
                    <span class="info-value">' . date('jS', strtotime('+15 days')) . ' of each month</span>
                </li>
                <li>
                    <span class="info-label">Payment Method</span>
                    <span class="info-value">Auto-Pay Enabled</span>
                </li>
                <li>
                    <span class="info-label">Current Rate</span>
                    <span class="info-value">$0.85 per m³</span>
                </li>
                <li>
                    <span class="info-label">Average Monthly</span>
                    <span class="info-value">$127.50</span>
                </li>
            </ul>
        </div>
    </div>
</div>';

include 'layout.php';
?>

