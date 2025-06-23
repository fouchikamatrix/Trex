<?php
$page_title = "Facture d'électricité";
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_data = $_SESSION['user_data'] ?? [];
$user_name = $_SESSION['user_name'] ?? 'Utilisateur';

// Get electricity bills for the user
try {
    $stmt = $pdo->prepare("
        SELECT * FROM bills 
        WHERE user_id = ? AND service_type = 'electricity' 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $bills = [];
    error_log("Electricity bills error: " . $e->getMessage());
}

$additional_css = '
    .bills-container {
        max-width: 1000px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    .bills-header {
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

    .bills-header::before {
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

    .bills-header h1 {
        color: #1a1a1a;
        font-size: 2.5rem;
        margin-bottom: 15px;
        font-weight: 800;
    }

    .bills-header p {
        color: #2d2d2d;
        font-size: 1.2rem;
    }

    .bills-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        background: linear-gradient(135deg, #4dabf7 0%, #2196f3 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .bills-grid {
        display: grid;
        gap: 25px;
        margin-bottom: 30px;
    }

    .bill-card {
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

    .bill-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    }

    .bill-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .bill-number {
        font-size: 1.2rem;
        font-weight: 700;
        color: #1a1a1a;
        font-family: "Poppins", sans-serif;
    }

    .bill-status {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-pending {
        background: rgba(251, 191, 36, 0.2);
        color: #fbbf24;
        border: 1px solid rgba(251, 191, 36, 0.3);
    }

    .status-paid {
        background: rgba(34, 197, 94, 0.2);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.3);
    }

    .status-overdue {
        background: rgba(239, 68, 68, 0.2);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }

    .bill-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }

    .detail-item {
        background: rgba(255, 255, 255, 0.1);
        padding: 15px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .detail-label {
        font-size: 0.85rem;
        color: #404040;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }

    .detail-value {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1a1a1a;
    }

    .bill-actions {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #4dabf7 0%, #2196f3 100%);
        color: white;
    }

    .btn-secondary {
        background: rgba(255, 255, 255, 0.1);
        color: #1a1a1a;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }

    .no-bills {
        text-align: center;
        padding: 60px 30px;
        color: #404040;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .no-bills h3 {
        color: #1a1a1a;
        font-size: 1.6rem;
        margin-bottom: 15px;
        font-weight: 700;
    }

    .no-bills i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    @media (max-width: 768px) {
        .bills-container {
            padding: 0 15px;
        }
        
        .bill-header {
            flex-direction: column;
            text-align: center;
        }
        
        .bill-actions {
            justify-content: center;
        }
    }
';

$content = '
<div class="bills-container">
    <div class="bills-header">
        <div class="bills-icon">
            <i class="fas fa-bolt"></i>
        </div>
        <h1>Factures d\'électricité</h1>
        <p>Consultez et gérez vos informations de facturation d\'électricité</p>
    </div>

    <div class="bills-grid">';

if (!empty($bills)) {
    foreach ($bills as $bill) {
        $statusClass = 'status-' . $bill['status'];
        $statusText = '';
        switch($bill['status']) {
            case 'pending':
                $statusText = 'En attente';
                break;
            case 'paid':
                $statusText = 'Payée';
                break;
            case 'overdue':
                $statusText = 'En retard';
                break;
            default:
                $statusText = ucfirst($bill['status']);
        }
        
        $content .= '
        <div class="bill-card">
            <div class="bill-header">
                <div class="bill-number">Facture #' . htmlspecialchars($bill['bill_number']) . '</div>
                <div class="bill-status ' . $statusClass . '">' . $statusText . '</div>
            </div>
            
            <div class="bill-details">
                <div class="detail-item">
                    <div class="detail-label">Montant</div>
                    <div class="detail-value">$' . number_format($bill['amount'], 2) . '</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Date d\'échéance</div>
                    <div class="detail-value">' . date('d M Y', strtotime($bill['due_date'])) . '</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Consommation</div>
                    <div class="detail-value">' . $bill['consumption'] . ' ' . $bill['unit'] . '</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Période</div>
                    <div class="detail-value">' . date('d M', strtotime($bill['billing_period_start'])) . ' - ' . date('d M Y', strtotime($bill['billing_period_end'])) . '</div>
                </div>
            </div>
            
            <div class="bill-actions">
                <a href="#" class="btn btn-primary">
                    <i class="fas fa-download"></i>
                    Télécharger PDF
                </a>
                ' . ($bill['status'] === 'pending' ? '
                <a href="electricity_payment.php?bill_id=' . $bill['id'] . '" class="btn btn-secondary">
                    <i class="fas fa-credit-card"></i>
                    Payer maintenant
                </a>' : '') . '
                <a href="#" class="btn btn-secondary">
                    <i class="fas fa-eye"></i>
                    Voir détails
                </a>
            </div>
        </div>';
    }
} else {
    $content .= '
    <div class="no-bills">
        <i class="fas fa-file-invoice"></i>
        <h3>Aucune facture trouvée</h3>
        <p>Vous n\'avez pas encore de factures d\'électricité. Les factures apparaîtront ici une fois qu\'elles seront générées.</p>
    </div>';
}

$content .= '
    </div>
</div>';

include 'layout.php';
?>