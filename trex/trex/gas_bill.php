<?php
$page_title = "Facture de gaz";
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_data = $_SESSION['user_data'] ?? [];
$user_name = $_SESSION['user_name'] ?? 'Utilisateur';

// Get gas bills for the user
try {
    $stmt = $pdo->prepare("
        SELECT * FROM bills 
        WHERE user_id = ? AND service_type = 'gas' 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $bills = [];
    error_log("Gas bills error: " . $e->getMessage());
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
            rgba(255, 107, 53, 0.1) 0%, 
            rgba(247, 147, 30, 0.1) 50%,
            rgba(255, 107, 53, 0.1) 100%);
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
        background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
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
        background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
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

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(5px);
        animation: fadeIn 0.3s ease;
    }

    .modal.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        margin: 20px;
        padding: 30px;
        border-radius: 20px;
        width: 90%;
        max-width: 600px;
        max-height: 80vh;
        overflow-y: auto;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.3s ease;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid rgba(255, 107, 53, 0.2);
    }

    .modal-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
    }

    .close {
        background: none;
        border: none;
        font-size: 2rem;
        cursor: pointer;
        color: #666;
        transition: color 0.3s ease;
    }

    .close:hover {
        color: #ff6b35;
    }

    .modal-section {
        margin-bottom: 25px;
    }

    .modal-section h3 {
        color: #ff6b35;
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 15px;
        border-bottom: 1px solid rgba(255, 107, 53, 0.2);
        padding-bottom: 8px;
    }

    .modal-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }

    .modal-item {
        background: rgba(255, 107, 53, 0.1);
        padding: 12px;
        border-radius: 8px;
        border: 1px solid rgba(255, 107, 53, 0.2);
    }

    .modal-item-label {
        font-size: 0.8rem;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .modal-item-value {
        font-size: 1rem;
        font-weight: 600;
        color: #1a1a1a;
    }

    .loading {
        opacity: 0.7;
        pointer-events: none;
    }

    .loading::after {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 20px;
        height: 20px;
        border: 2px solid transparent;
        border-top: 2px solid #ff6b35;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from { transform: translateY(50px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    @keyframes spin {
        from { transform: translate(-50%, -50%) rotate(0deg); }
        to { transform: translate(-50%, -50%) rotate(360deg); }
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

        .modal-content {
            margin: 10px;
            padding: 20px;
        }

        .modal-grid {
            grid-template-columns: 1fr;
        }
    }
';

$content = '
<div class="bills-container">
    <div class="bills-header">
        <div class="bills-icon">
            <i class="fas fa-fire"></i>
        </div>
        <h1>Factures de gaz</h1>
        <p>Consultez et gérez vos informations de facturation de gaz</p>
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
                    <div class="detail-value">TND' . number_format($bill['amount'], 2) . '</div>
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
                <a href="generate_bill_pdf.php?bill_id=' . $bill['id'] . '&print=1" class="btn btn-primary" target="_blank">
                    <i class="fas fa-print"></i>
                    Imprimer PDF
                </a>
                ' . ($bill['status'] === 'pending' ? '
                <a href="gas_payment.php?bill_id=' . $bill['id'] . '" class="btn btn-secondary">
                    <i class="fas fa-credit-card"></i>
                    Payer maintenant
                </a>' : '') . '
                <button class="btn btn-secondary" onclick="showBillDetails(' . $bill['id'] . ')">
                    <i class="fas fa-eye"></i>
                    Voir détails
                </button>
            </div>
        </div>';
    }
} else {
    $content .= '
    <div class="no-bills">
        <i class="fas fa-file-invoice"></i>
        <h3>Aucune facture trouvée</h3>
        <p>Vous n\'avez pas encore de factures de gaz. Les factures apparaîtront ici une fois qu\'elles seront générées.</p>
    </div>';
}

$content .= '
    </div>
</div>

<!-- Bill Details Modal -->
<div id="billModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Détails de la facture</h2>
            <button class="close" onclick="closeBillModal()">&times;</button>
        </div>
        <div id="modalBody">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>';

$additional_js = '
function showBillDetails(billId) {
    const modal = document.getElementById("billModal");
    const modalBody = document.getElementById("modalBody");
    
    // Show modal with loading state
    modal.classList.add("show");
    modalBody.innerHTML = `
        <div style="text-align: center; padding: 40px;">
            <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #ff6b35;"></i>
            <p style="margin-top: 15px; color: #666;">Chargement des détails...</p>
        </div>
    `;
    
    // Fetch bill details
    fetch(`get_bill_details.php?bill_id=${billId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayBillDetails(data.bill);
            } else {
                modalBody.innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #dc2626;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 2rem;"></i>
                        <p style="margin-top: 15px;">Erreur: ${data.error}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            modalBody.innerHTML = `
                <div style="text-align: center; padding: 40px; color: #dc2626;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 2rem;"></i>
                    <p style="margin-top: 15px;">Erreur de chargement des détails</p>
                </div>
            `;
        });
}

function displayBillDetails(bill) {
    const modalBody = document.getElementById("modalBody");
    modalBody.innerHTML = `
        <div class="modal-section">
            <h3>Informations générales</h3>
            <div class="modal-grid">
                <div class="modal-item">
                    <div class="modal-item-label">Numéro de facture</div>
                    <div class="modal-item-value">${bill.bill_number}</div>
                </div>
                <div class="modal-item">
                    <div class="modal-item-label">Service</div>
                    <div class="modal-item-value">${bill.service_type_fr}</div>
                </div>
                <div class="modal-item">
                    <div class="modal-item-label">Statut</div>
                    <div class="modal-item-value">${bill.status_fr}</div>
                </div>
                <div class="modal-item">
                    <div class="modal-item-label">Date d\'émission</div>
                    <div class="modal-item-value">${bill.created_at}</div>
                </div>
            </div>
        </div>
        
        <div class="modal-section">
            <h3>Consommation et facturation</h3>
            <div class="modal-grid">
                <div class="modal-item">
                    <div class="modal-item-label">Période de facturation</div>
                    <div class="modal-item-value">${bill.billing_period_start} - ${bill.billing_period_end}</div>
                </div>
                <div class="modal-item">
                    <div class="modal-item-label">Consommation</div>
                    <div class="modal-item-value">${bill.consumption} ${bill.unit}</div>
                </div>
                <div class="modal-item">
                    <div class="modal-item-label">Tarif unitaire</div>
                    <div class="modal-item-value">${bill.rate} $/unité</div>
                </div>
                <div class="modal-item">
                    <div class="modal-item-label">Date d\'échéance</div>
                    <div class="modal-item-value">${bill.due_date}</div>
                </div>
            </div>
        </div>
        
        <div class="modal-section">
            <h3>Détail des coûts</h3>
            <div class="modal-grid">
                <div class="modal-item">
                    <div class="modal-item-label">Sous-total</div>
                    <div class="modal-item-value">${bill.subtotal} $</div>
                </div>
                <div class="modal-item">
                    <div class="modal-item-label">Taxes (15%)</div>
                    <div class="modal-item-value">${bill.taxes} $</div>
                </div>
                <div class="modal-item" style="background: rgba(255, 107, 53, 0.2); border-color: rgba(255, 107, 53, 0.4);">
                    <div class="modal-item-label">Total à payer</div>
                    <div class="modal-item-value" style="font-size: 1.2rem; color: #ff6b35;">${bill.amount} $</div>
                </div>
            </div>
        </div>
        
        <div class="modal-section">
            <h3>Informations client</h3>
            <div class="modal-grid">
                <div class="modal-item">
                    <div class="modal-item-label">Nom</div>
                    <div class="modal-item-value">${bill.user_name}</div>
                </div>
                <div class="modal-item">
                    <div class="modal-item-label">Email</div>
                    <div class="modal-item-value">${bill.user_email}</div>
                </div>
                <div class="modal-item">
                    <div class="modal-item-label">Adresse</div>
                    <div class="modal-item-value">${bill.user_address}</div>
                </div>
            </div>
        </div>
    `;
}

function closeBillModal() {
    const modal = document.getElementById("billModal");
    modal.classList.remove("show");
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById("billModal");
    if (event.target === modal) {
        closeBillModal();
    }
}

// Close modal with Escape key
document.addEventListener("keydown", function(event) {
    if (event.key === "Escape") {
        closeBillModal();
    }
});
';

include 'layout.php';
?>
