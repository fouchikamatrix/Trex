<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Access denied');
}

// Get bill ID from URL
$bill_id = $_GET['bill_id'] ?? '';
if (empty($bill_id)) {
    http_response_code(400);
    exit('Bill ID required');
}

// Get bill data
try {
    $stmt = $pdo->prepare("SELECT * FROM bills WHERE id = ? AND user_id = ?");
    $stmt->execute([$bill_id, $_SESSION['user_id']]);
    $bill = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$bill) {
        http_response_code(404);
        exit('Bill not found');
    }
    
    // Get user info from session or try users table
    $user_name = $_SESSION['user_name'] ?? 'Client';
    $user_email = $_SESSION['user_email'] ?? '';
    $user_address = $_SESSION['user_address'] ?? 'Non spécifiée';
    
    try {
        $user_stmt = $pdo->prepare("SELECT name, email, address FROM users WHERE id = ?");
        $user_stmt->execute([$_SESSION['user_id']]);
        $user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user_data) {
            $user_name = $user_data['name'] ?? $user_name;
            $user_email = $user_data['email'] ?? $user_email;
            $user_address = $user_data['address'] ?? $user_address;
        }
    } catch (PDOException $e) {
        error_log("User table access error: " . $e->getMessage());
    }
    
    $bill['user_name'] = $user_name;
    $bill['user_email'] = $user_email;
    $bill['user_address'] = $user_address;
    
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Bill PDF generation error: " . $e->getMessage());
    exit('Database error');
}

// Check if we want to use browser printing or try to generate actual PDF
$use_browser_print = $_GET['print'] ?? false;

if ($use_browser_print) {
    // Return HTML page optimized for printing
    echo generatePrintableHTML($bill);
} else {
    // Try to generate actual PDF using a simple library-free approach
    generateSimplePDF($bill);
}

function generateSimplePDF($bill) {
    // For a proper PDF, we need a library. For now, let's create a text-based approach
    // that actually works, or redirect to print version
    
    $service_type_fr = $bill['service_type'] === 'gas' ? 'Gaz' : 'Électricité';
    $status_fr = [
        'pending' => 'En attente',
        'paid' => 'Payée',
        'overdue' => 'En retard',
        'cancelled' => 'Annulée'
    ][$bill['status']] ?? $bill['status'];
    
    // Create a simple text-based PDF content
    $content = "Gaztronik - FACTURE DE " . strtoupper($service_type_fr) . "\n";
    $content .= str_repeat("=", 50) . "\n\n";
    $content .= "Facture #: " . $bill['bill_number'] . "\n";
    $content .= "Date d'émission: " . date('d/m/Y', strtotime($bill['created_at'])) . "\n";
    $content .= "Date d'échéance: " . date('d/m/Y', strtotime($bill['due_date'])) . "\n";
    $content .= "Statut: " . $status_fr . "\n\n";
    
    $content .= "CLIENT:\n";
    $content .= "Nom: " . $bill['user_name'] . "\n";
    $content .= "Email: " . $bill['user_email'] . "\n";
    $content .= "Adresse: " . $bill['user_address'] . "\n\n";
    
    $content .= "DÉTAILS DE FACTURATION:\n";
    $content .= "Période: " . date('d/m/Y', strtotime($bill['billing_period_start'])) . " - " . date('d/m/Y', strtotime($bill['billing_period_end'])) . "\n";
    $content .= "Consommation: " . $bill['consumption'] . " " . $bill['unit'] . "\n";
    $content .= "Tarif: " . number_format($bill['rate'], 3) . " $/unité\n";
    $content .= "Sous-total: " . number_format($bill['amount'] * 0.85, 2) . " TND\n";
    $content .= "Taxes (15%): " . number_format($bill['amount'] * 0.15, 2) . " TND\n";
    $content .= "TOTAL: " . number_format($bill['amount'], 2) . " $\n\n";
    
    $content .= str_repeat("=", 50) . "\n";
    $content .= "Gaztronik - Services de gaz et d'électricité\n";
    $content .= "Pour toute question: support@Gaztronik.com\n";
    $content .= "Merci de votre confiance!\n";
    
    // Set headers for text file download (since we can't generate proper PDF without library)
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="facture_' . $bill['bill_number'] . '.txt"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    echo $content;
}

function generatePrintableHTML($bill) {
    $service_type_fr = $bill['service_type'] === 'gas' ? 'Gaz' : 'Électricité';
    $status_fr = [
        'pending' => 'En attente',
        'paid' => 'Payée',
        'overdue' => 'En retard',
        'cancelled' => 'Annulée'
    ][$bill['status']] ?? $bill['status'];
    
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Facture ' . htmlspecialchars($bill['bill_number']) . '</title>
        <style>
            @media print {
                body { margin: 0; }
                .no-print { display: none; }
            }
            
            body { 
                font-family: Arial, sans-serif; 
                margin: 20px; 
                color: #333; 
                line-height: 1.4;
            }
            
            .header { 
                text-align: center; 
                margin-bottom: 30px; 
                border-bottom: 3px solid #4dabf7; 
                padding-bottom: 20px; 
            }
            
            .company-name { 
                font-size: 32px; 
                font-weight: bold; 
                color: #4dabf7; 
                margin-bottom: 10px; 
            }
            
            .bill-title { 
                font-size: 24px; 
                margin-bottom: 20px; 
                color: #333;
            }
            
            .bill-info { 
                display: flex; 
                justify-content: space-between; 
                margin-bottom: 30px; 
                gap: 30px;
            }
            
            .customer-info, .bill-details { 
                flex: 1;
                background: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
            }
            
            .info-title { 
                font-weight: bold; 
                font-size: 18px; 
                margin-bottom: 15px; 
                color: #4dabf7; 
                border-bottom: 1px solid #ddd;
                padding-bottom: 5px;
            }
            
            .info-item { 
                margin-bottom: 10px; 
                font-size: 14px;
            }
            
            .consumption-table { 
                width: 100%; 
                border-collapse: collapse; 
                margin: 30px 0; 
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .consumption-table th, .consumption-table td { 
                border: 1px solid #ddd; 
                padding: 15px; 
                text-align: left; 
            }
            
            .consumption-table th { 
                background-color: #4dabf7; 
                color: white;
                font-weight: bold; 
            }
            
            .consumption-table td {
                background-color: #f8f9fa;
            }
            
            .total-section { 
                text-align: right; 
                margin-top: 30px; 
                font-size: 16px;
                background: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
            }
            
            .total-amount { 
                font-weight: bold; 
                color: #4dabf7; 
                font-size: 24px; 
                margin-top: 10px;
                border-top: 2px solid #4dabf7;
                padding-top: 10px;
            }
            
            .footer { 
                margin-top: 40px; 
                text-align: center; 
                font-size: 12px; 
                color: #666; 
                border-top: 1px solid #ddd;
                padding-top: 20px;
            }
            
            .status { 
                padding: 8px 16px; 
                border-radius: 20px; 
                display: inline-block; 
                font-weight: bold; 
                font-size: 12px;
            }
            
            .status-pending { background-color: #fff3cd; color: #856404; }
            .status-paid { background-color: #d4edda; color: #155724; }
            .status-overdue { background-color: #f8d7da; color: #721c24; }
            .status-cancelled { background-color: #f8d7da; color: #721c24; }
            
            .print-button {
                background: #4dabf7;
                color: white;
                border: none;
                padding: 15px 30px;
                border-radius: 8px;
                font-size: 16px;
                cursor: pointer;
                margin: 20px 0;
            }
            
            .print-button:hover {
                background: #2196f3;
            }
            
            @media (max-width: 768px) {
                .bill-info { flex-direction: column; }
                body { margin: 10px; }
            }
        </style>
    </head>
    <body>
        <div class="no-print">
            <button class="print-button" onclick="window.print()">🖨️ Imprimer cette facture</button>
            <button class="print-button" onclick="window.close()" style="background: #6c757d;">❌ Fermer</button>
        </div>
        
        <div class="header">
            <div class="company-name">Gaztronik</div>
            <div class="bill-title">Facture de ' . $service_type_fr . '</div>
        </div>
        
        <div class="bill-info">
            <div class="customer-info">
                <div class="info-title">📋 Informations client</div>
                <div class="info-item"><strong>Nom:</strong> ' . htmlspecialchars($bill['user_name']) . '</div>
                <div class="info-item"><strong>Email:</strong> ' . htmlspecialchars($bill['user_email']) . '</div>
                <div class="info-item"><strong>Adresse:</strong> ' . htmlspecialchars($bill['user_address']) . '</div>
            </div>
            
            <div class="bill-details">
                <div class="info-title">🧾 Détails de la facture</div>
                <div class="info-item"><strong>Numéro:</strong> ' . htmlspecialchars($bill['bill_number']) . '</div>
                <div class="info-item"><strong>Date d\'émission:</strong> ' . date('d/m/Y', strtotime($bill['created_at'])) . '</div>
                <div class="info-item"><strong>Date d\'échéance:</strong> ' . date('d/m/Y', strtotime($bill['due_date'])) . '</div>
                <div class="info-item"><strong>Statut:</strong> <span class="status status-' . $bill['status'] . '">' . $status_fr . '</span></div>
            </div>
        </div>
        
        <table class="consumption-table">
            <thead>
                <tr>
                    <th>📊 Description</th>
                    <th>📅 Période</th>
                    <th>⚡ Consommation</th>
                    <th>💰 Tarif</th>
                    <th>💵 Montant</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Consommation de ' . $service_type_fr . '</td>
                    <td>' . date('d/m/Y', strtotime($bill['billing_period_start'])) . ' - ' . date('d/m/Y', strtotime($bill['billing_period_end'])) . '</td>
                    <td>' . number_format($bill['consumption'], 2) . ' ' . $bill['unit'] . '</td>
                    <td>' . number_format($bill['rate'], 3) . ' TND/unité</td>
                    <td>' . number_format($bill['amount'], 2) . ' TND</td>
                </tr>
            </tbody>
        </table>
        
        <div class="total-section">
            <div><strong>Sous-total:</strong> ' . number_format($bill['amount'] * 0.85, 2) . ' TND</div>
            <div><strong>Taxes (15%):</strong> ' . number_format($bill['amount'] * 0.15, 2) . ' TND</div>
            <div class="total-amount"><strong>TOTAL À PAYER: ' . number_format($bill['amount'], 2) . ' TND</strong></div>
        </div>
        
        <div class="footer">
            <p><strong>Gaztronik</strong> - Services de gaz et d\'électricité</p>
            <p>📧 Pour toute question, contactez-nous au <strong>support@Gaztronik.com</strong></p>
            <p>🙏 Merci de votre confiance!</p>
            <p><em>Facture générée le ' . date('d/m/Y à H:i') . '</em></p>
        </div>
        
        <script>
            // Auto-print when page loads if requested
            if (window.location.search.includes("autoprint=1")) {
                window.onload = function() {
                    setTimeout(function() {
                        window.print();
                    }, 500);
                };
            }
        </script>
    </body>
    </html>';
}
?>
