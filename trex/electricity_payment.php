<?php
$page_title = "Paiement électricité";
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_data = $_SESSION['user_data'] ?? [];
$user_name = $_SESSION['user_name'] ?? 'Utilisateur';
$success_message = '';
$error_message = '';

// Get pending electricity bills
try {
    $stmt = $pdo->prepare("
        SELECT * FROM bills 
        WHERE user_id = ? AND service_type = 'electricity' AND status = 'pending'
        ORDER BY due_date ASC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $pending_bills = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $pending_bills = [];
    error_log("Electricity payment bills error: " . $e->getMessage());
}

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bill_id = $_POST['bill_id'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    $amount = $_POST['amount'] ?? '';
    
    if (empty($bill_id) || empty($payment_method) || empty($amount)) {
        $error_message = 'Veuillez remplir tous les champs obligatoires.';
    } else {
        try {
            // Verify bill belongs to user
            $stmt = $pdo->prepare("SELECT * FROM bills WHERE id = ? AND user_id = ? AND status = 'pending'");
            $stmt->execute([$bill_id, $_SESSION['user_id']]);
            $bill = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$bill) {
                $error_message = 'Facture sélectionnée invalide.';
            } elseif (floatval($amount) !== floatval($bill['amount'])) {
                $error_message = 'Le montant du paiement ne correspond pas au montant de la facture.';
            } else {
                // Create payment record
                $transaction_id = 'TXN' . time() . rand(1000, 9999);
                $reference_number = 'REF' . date('Ymd') . rand(100, 999);
                
                $stmt = $pdo->prepare("
                    INSERT INTO payments (user_id, bill_id, payment_method, amount, transaction_id, reference_number, status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'completed')
                ");
                $stmt->execute([$_SESSION['user_id'], $bill_id, $payment_method, $amount, $transaction_id, $reference_number]);
                
                // Update bill status
                $stmt = $pdo->prepare("UPDATE bills SET status = 'paid', paid_at = NOW() WHERE id = ?");
                $stmt->execute([$bill_id]);
                
                $success_message = "Paiement réussi ! ID de transaction : $transaction_id";
                
                // Refresh pending bills
                $stmt = $pdo->prepare("
                    SELECT * FROM bills 
                    WHERE user_id = ? AND service_type = 'electricity' AND status = 'pending'
                    ORDER BY due_date ASC
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $pending_bills = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $error_message = 'Le traitement du paiement a échoué. Veuillez réessayer.';
            error_log("Payment error: " . $e->getMessage());
        }
    }
}

$additional_css = '
    .payment-container {
        max-width: 800px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    .payment-header {
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

    .payment-header::before {
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

    .payment-header h1 {
        color: #1a1a1a;
        font-size: 2.5rem;
        margin-bottom: 15px;
        font-weight: 800;
    }

    .payment-header p {
        color: #2d2d2d;
        font-size: 1.2rem;
    }

    .payment-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        background: linear-gradient(135deg, #4dabf7 0%, #2196f3 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .payment-form-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        margin-bottom: 30px;
    }

    .bill-selector {
        margin-bottom: 30px;
    }

    .bill-option {
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .bill-option:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-2px);
    }

    .bill-option.selected {
        border-color: rgba(77, 171, 247, 0.6);
        background: rgba(77, 171, 247, 0.1);
    }

    .bill-option input[type="radio"] {
        display: none;
    }

    .bill-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .bill-details h4 {
        color: #1a1a1a;
        font-size: 1.1rem;
        margin-bottom: 5px;
    }

    .bill-details p {
        color: #404040;
        font-size: 0.9rem;
    }

    .bill-amount {
        font-size: 1.5rem;
        font-weight: 700;
        color: #4dabf7;
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-group label {
        display: block;
        margin-bottom: 10px;
        font-weight: 700;
        color: #1a1a1a;
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-group select,
    .form-group input {
        width: 100%;
        padding: 18px 20px;
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: 15px;
        font-size: 1rem;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        color: #1a1a1a;
        font-weight: 500;
    }

    .form-group select:focus,
    .form-group input:focus {
        outline: none;
        border-color: rgba(77, 171, 247, 0.6);
        box-shadow: 0 0 0 4px rgba(77, 171, 247, 0.1);
    }

    .payment-methods {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }

    .payment-method {
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .payment-method:hover {
        background: rgba(255, 255, 255, 0.15);
    }

    .payment-method.selected {
        border-color: rgba(77, 171, 247, 0.6);
        background: rgba(77, 171, 247, 0.1);
    }

    .payment-method input[type="radio"] {
        display: none;
    }

    .payment-method i {
        font-size: 2rem;
        margin-bottom: 10px;
        color: #404040;
    }

    .payment-method span {
        display: block;
        color: #1a1a1a;
        font-weight: 600;
    }

    .submit-btn {
        width: 100%;
        padding: 20px;
        background: linear-gradient(135deg, #4dabf7 0%, #2196f3 100%);
        color: white;
        border: none;
        border-radius: 15px;
        font-size: 1.1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.4s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .submit-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(77, 171, 247, 0.4);
    }

    .submit-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    .alert {
        padding: 20px 25px;
        border-radius: 15px;
        margin-bottom: 25px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 12px;
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

    .no-bills {
        text-align: center;
        padding: 60px 30px;
        color: #404040;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 20px;
    }

    .no-bills h3 {
        color: #1a1a1a;
        font-size: 1.6rem;
        margin-bottom: 15px;
        font-weight: 700;
    }

    @media (max-width: 768px) {
        .payment-container {
            padding: 0 15px;
        }
        
        .bill-info {
            flex-direction: column;
            text-align: center;
        }
        
        .payment-methods {
            grid-template-columns: 1fr;
        }
    }
';

$content = '
<div class="payment-container">
    <div class="payment-header">
        <div class="payment-icon">
            <i class="fas fa-credit-card"></i>
        </div>
        <h1>Paiement électricité</h1>
        <p>Payez vos factures d\'électricité en ligne en toute sécurité</p>
    </div>

    ' . (!empty($success_message) ? '<div class="alert success"><i class="fas fa-check-circle"></i>' . htmlspecialchars($success_message) . '</div>' : '') . '
    ' . (!empty($error_message) ? '<div class="alert error"><i class="fas fa-exclamation-triangle"></i>' . htmlspecialchars($error_message) . '</div>' : '') . '

    <div class="payment-form-card">';

if (!empty($pending_bills)) {
    $content .= '
        <form method="POST" action="" id="paymentForm">
            <div class="form-group">
                <label>
                    <i class="fas fa-file-invoice"></i>
                    Sélectionner la facture à payer
                </label>
                <div class="bill-selector">';
    
    foreach ($pending_bills as $bill) {
        $content .= '
                    <div class="bill-option" onclick="selectBill(this, ' . $bill['id'] . ', ' . $bill['amount'] . ')">
                        <input type="radio" name="bill_id" value="' . $bill['id'] . '" required>
                        <div class="bill-info">
                            <div class="bill-details">
                                <h4>Facture #' . htmlspecialchars($bill['bill_number']) . '</h4>
                                <p>Échéance : ' . date('d M Y', strtotime($bill['due_date'])) . ' | ' . $bill['consumption'] . ' ' . $bill['unit'] . '</p>
                            </div>
                            <div class="bill-amount">' . number_format($bill['amount'], 2) . ' TND</div>
                        </div>
                    </div>';
    }
    
    $content .= '
                </div>
            </div>

            <div class="form-group">
                <label>
                    (TND)
                    Montant du paiement
                </label>
                <input type="number" name="amount" id="paymentAmount" step="0.01" readonly required>
            </div>

            <div class="form-group">
                <label>
                    <i class="fas fa-credit-card"></i>
                    Mode de paiement
                </label>
                <div class="payment-methods">
                    <div class="payment-method" onclick="selectPaymentMethod(this, \'credit_card\')">
                        <input type="radio" name="payment_method" value="credit_card" required>
                        <i class="fas fa-credit-card"></i>
                        <span>Carte de crédit</span>
                    </div>
                    <div class="payment-method" onclick="selectPaymentMethod(this, \'bank_transfer\')">
                        <input type="radio" name="payment_method" value="bank_transfer" required>
                        <i class="fas fa-university"></i>
                        <span>Virement bancaire</span>
                    </div>
                    <div class="payment-method" onclick="selectPaymentMethod(this, \'cash\')">
                        <input type="radio" name="payment_method" value="cash" required>
                        <i class="fas fa-money-bill"></i>
                        <span>Espèces</span>
                    </div>
                </div>
            </div>

            <button type="submit" class="submit-btn" id="submitBtn" disabled>
                <i class="fas fa-lock"></i>
                Traiter le paiement
            </button>
        </form>';
} else {
    $content .= '
        <div class="no-bills">
            <i class="fas fa-check-circle" style="font-size: 4rem; margin-bottom: 20px; opacity: 0.5;"></i>
            <h3>Aucune facture en attente</h3>
            <p>Vous n\'avez aucune facture d\'électricité en attente de paiement pour le moment.</p>
            <a href="electricity_bill.php" style="color: #4dabf7; text-decoration: none; font-weight: 600; margin-top: 15px; display: inline-block;">
                <i class="fas fa-arrow-left"></i> Voir toutes les factures
            </a>
        </div>';
}

$content .= '
    </div>
</div>';

$additional_js = '
function selectBill(element, billId, amount) {
    // Remove selected class from all bills
    document.querySelectorAll(".bill-option").forEach(el => el.classList.remove("selected"));
    
    // Add selected class to clicked bill
    element.classList.add("selected");
    
    // Check the radio button
    element.querySelector("input[type=radio]").checked = true;
    
    // Update payment amount
    document.getElementById("paymentAmount").value = amount.toFixed(2);
    
    // Enable submit button if payment method is also selected
    checkFormCompletion();
}

function selectPaymentMethod(element, method) {
    // Remove selected class from all payment methods
    document.querySelectorAll(".payment-method").forEach(el => el.classList.remove("selected"));
    
    // Add selected class to clicked method
    element.classList.add("selected");
    
    // Check the radio button
    element.querySelector("input[type=radio]").checked = true;
    
    // Enable submit button if bill is also selected
    checkFormCompletion();
}

function checkFormCompletion() {
    const billSelected = document.querySelector("input[name=bill_id]:checked");
    const paymentMethodSelected = document.querySelector("input[name=payment_method]:checked");
    const submitBtn = document.getElementById("submitBtn");
    
    if (billSelected && paymentMethodSelected) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = \'<i class="fas fa-credit-card"></i> Traiter le paiement\';
    } else {
        submitBtn.disabled = true;
        submitBtn.innerHTML = \'<i class="fas fa-lock"></i> Sélectionner facture et mode de paiement\';
    }
}

// Form submission
document.getElementById("paymentForm")?.addEventListener("submit", function() {
    const btn = document.getElementById("submitBtn");
    btn.disabled = true;
    btn.innerHTML = \'<i class="fas fa-spinner fa-spin"></i> Traitement en cours...\';
});
';

include 'layout.php';
?>