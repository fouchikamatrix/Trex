<?php
$page_title = "Electricity Information";
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_data = $_SESSION['user_data'] ?? [];
$user_name = $_SESSION['user_name'] ?? 'User';

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
            rgba(77, 171, 247, 0.1) 0%, 
            rgba(33, 150, 243, 0.1) 50%,
            rgba(77, 171, 247, 0.1) 100%);
        background-size: 400% 400%;
        animation: gradientShift 10s ease infinite;
        z-index: -1;
    }

    .info-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        background: linear-gradient(135deg, #4dabf7 0%, #2196f3 100%);
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
        color: #4dabf7;
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
        color: #4dabf7;
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
        <h1>Electricity Service Information</h1>
        <p>Detailed information about your electricity service and account</p>
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
                    <span class="info-value">' . htmlspecialchars($user_data['electric_counter_number'] ?? 'Not assigned') . '</span>
                </li>
                <li>
                    <span class="info-label">Customer ID</span>
                    <span class="info-value">' . htmlspecialchars($user_data['id_card_number'] ?? 'Not provided') . '</span>
                </li>
                <li>
                    <span class="info-label">Service Type</span>
                    <span class="info-value">Residential Electricity</span>
                </li>
                <li>
                    <span class="info-label">Connection Date</span>
                    <span class="info-value">' . htmlspecialchars($user_data['installation_date'] ?? 'Not available') . '</span>
                </li>
                <li>
                    <span class="info-label">Tariff Plan</span>
                    <span class="info-value">Time-of-Use Residential</span>
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
                    <span class="info-value">' . htmlspecialchars($user_data['electric_counter_number'] ?? 'Not assigned') . '</span>
                </li>
                <li>
                    <span class="info-label">Meter Type</span>
                    <span class="info-value">' . ucfirst($user_data['electric_counter_type'] ?? 'Standard') . ' Smart Meter</span>
                </li>
                <li>
                    <span class="info-label">Last Reading</span>
                    <span class="info-value">8,547 kWh</span>
                </li>
                <li>
                    <span class="info-label">Reading Date</span>
                    <span class="info-value">' . date('M d, Y', strtotime('-3 days')) . '</span>
                </li>
                <li>
                    <span class="info-label">Next Reading</span>
                    <span class="info-value">' . date('M d, Y', strtotime('+27 days')) . '</span>
                </li>
            </ul>
        </div>

        <!-- Service Statistics -->
        <div class="info-card">
            <div class="card-header">
                <h3 class="card-title">Service Statistics</h3>
                <i class="fas fa-chart-line card-icon"></i>
            </div>
            <div class="info-stats">
                <div class="stat-item">
                    <div class="stat-value">99.2%</div>
                    <div class="stat-label">Uptime</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">24/7</div>
                    <div class="stat-label">Support</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">3</div>
                    <div class="stat-label">Years</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">1</div>
                    <div class="stat-label">Outage</div>
                </div>
            </div>
            <div class="info-content">
                <p>Your electricity service maintains high reliability with smart grid technology. Our automated systems quickly detect and resolve most issues before they affect your service.</p>
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
                    <span class="info-label">Power Outage Hotline</span>
                    <span class="info-value">+1 (555) 911-POWER</span>
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
                    <span class="info-value">electric@voltgaz.com</span>
                </li>
                <li>
                    <span class="info-label">Service Hours</span>
                    <span class="info-value">24/7 Emergency</span>
                </li>
            </ul>
        </div>

        <!-- Rate Information -->
        <div class="info-card">
            <div class="card-header">
                <h3 class="card-title">Rate Structure</h3>
                <i class="fas fa-dollar-sign card-icon"></i>
            </div>
            <ul class="info-list">
                <li>
                    <span class="info-label">Peak Hours (2-8 PM)</span>
                    <span class="info-value">$0.28 per kWh</span>
                </li>
                <li>
                    <span class="info-label">Off-Peak Hours</span>
                    <span class="info-value">$0.12 per kWh</span>
                </li>
                <li>
                    <span class="info-label">Weekend Rate</span>
                    <span class="info-value">$0.10 per kWh</span>
                </li>
                <li>
                    <span class="info-label">Connection Fee</span>
                    <span class="info-value">$15.00/month</span>
                </li>
                <li>
                    <span class="info-label">Average Monthly</span>
                    <span class="info-value">$185.75</span>
                </li>
            </ul>
        </div>

        <!-- Energy Efficiency -->
        <div class="info-card">
            <div class="card-header">
                <h3 class="card-title">Energy Efficiency Tips</h3>
                <i class="fas fa-leaf card-icon"></i>
            </div>
            <div class="info-content">
                <ul style="list-style: disc; padding-left: 20px; color: rgba(255, 255, 255, 0.9);">
                    <li style="margin-bottom: 10px;">Use LED bulbs to reduce lighting costs by 75%</li>
                    <li style="margin-bottom: 10px;">Set thermostat 2-3 degrees higher in summer</li>
                    <li style="margin-bottom: 10px;">Unplug electronics when not in use</li>
                    <li style="margin-bottom: 10px;">Use programmable thermostats</li>
                    <li style="margin-bottom: 10px;">Schedule high-energy tasks during off-peak hours</li>
                </ul>
            </div>
        </div>
    </div>
</div>';

include 'layout.php';
?>
