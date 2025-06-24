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
            case 'create':
                $user_id = $_POST['user_id'] ?? '';
                $bill_number = trim($_POST['bill_number'] ?? '');
                $consumption = floatval($_POST['consumption'] ?? 0);
                $unit = $_POST['unit'] ?? 'm³';
                $rate = floatval($_POST['rate'] ?? 0);
                $amount = floatval($_POST['amount'] ?? 0);
                $due_date = $_POST['due_date'] ?? '';
                $billing_period_start = $_POST['billing_period_start'] ?? '';
                $billing_period_end = $_POST['billing_period_end'] ?? '';
                
                if (empty($user_id) || empty($bill_number) || empty($due_date) || $amount <= 0) {
                    $error_message = 'Veuillez remplir tous les champs obligatoires.';
                } else {
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO bills (user_id, service_type, bill_number, consumption, unit, rate, amount, due_date, billing_period_start, billing_period_end, status, created_at) 
                            VALUES (?, 'gas', ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
                        ");
                        $stmt->execute([$user_id, $bill_number, $consumption, $unit, $rate, $amount, $due_date, $billing_period_start, $billing_period_end]);
                        $success_message = 'Facture de gaz créée avec succès !';
                    } catch (PDOException $e) {
                        $error_message = 'Erreur lors de la création de la facture.';
                    }
                }
                break;
                
            case 'delete':
                $bill_id = $_POST['bill_id'] ?? '';
                if (!empty($bill_id)) {
                    try {
                        $stmt = $pdo->prepare("DELETE FROM bills WHERE id = ? AND service_type = 'gas'");
                        $stmt->execute([$bill_id]);
                        $success_message = 'Facture supprimée avec succès !';
                    } catch (PDOException $e) {
                        $error_message = 'Erreur lors de la suppression.';
                    }
                }
                break;
                
            case 'update_status':
                $bill_id = $_POST['bill_id'] ?? '';
                $new_status = $_POST['new_status'] ?? '';
                if (!empty($bill_id) && !empty($new_status)) {
                    try {
                        $update_data = [$new_status, $bill_id];
                        $sql = "UPDATE bills SET status = ?";
                        if ($new_status === 'paid') {
                            $sql .= ", paid_at = NOW()";
                        }
                        $sql .= " WHERE id = ?";
                        
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($update_data);
                        $success_message = 'Statut mis à jour avec succès !';
                    } catch (PDOException $e) {
                        $error_message = 'Erreur lors de la mise à jour du statut.';
                    }
                }
                break;
        }
    }
}

// Get all users for dropdown
try {
    $stmt = $pdo->query("SELECT id, name, last_name, gas_counter_number FROM users ORDER BY name, last_name");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
}

// Get all gas bills
try {
    $stmt = $pdo->query("
        SELECT b.*, u.name, u.last_name, u.gas_counter_number 
        FROM bills b 
        JOIN users u ON b.user_id = u.id 
        WHERE b.service_type = 'gas' 
        ORDER BY b.created_at DESC
    ");
    $gas_bills = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $gas_bills = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GazTronik - Gestion factures gaz</title>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gas-gradient: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            --success-gradient: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
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
            background: var(--gas-gradient);
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

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .form-section, .bills-list-section {
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

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1a1a1a;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            font-size: 0.95rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            color: #1a1a1a;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: rgba(255, 107, 53, 0.6);
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.1);
        }

        .form-group select option {
            background: #333;
            color: white;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: var(--gas-gradient);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 107, 53, 0.4);
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

        .bill-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .bill-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .bill-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            gap: 15px;
        }

        .bill-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 5px;
        }

        .bill-user {
            color: #404040;
            font-size: 0.9rem;
        }

        .bill-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .detail-item {
            text-align: center;
            padding: 10px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
        }

        .detail-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: #ff6b35;
            margin-bottom: 3px;
        }

        .detail-label {
            font-size: 0.8rem;
            color: #404040;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .bill-status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: rgba(251, 191, 36, 0.2);
            color: #f59e0b;
        }

        .status-paid {
            background: rgba(34, 197, 94, 0.2);
            color: #16a34a;
        }

        .status-overdue {
            background: rgba(239, 68, 68, 0.2);
            color: #dc2626;
        }

        .bill-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-paid {
            background: rgba(34, 197, 94, 0.2);
            color: #16a34a;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .btn-pending {
            background: rgba(251, 191, 36, 0.2);
            color: #f59e0b;
            border: 1px solid rgba(251, 191, 36, 0.3);
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.2);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .action-btn:hover {
            transform: translateY(-1px);
        }

        .no-bills {
            text-align: center;
            padding: 40px;
            color: #404040;
        }

        .calculate-btn {
            background: rgba(77, 171, 247, 0.2);
            color: #4dabf7;
            border: 1px solid rgba(77, 171, 247, 0.3);
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .calculate-btn:hover {
            background: rgba(77, 171, 247, 0.3);
        }

        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                text-align: center;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .bill-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .bill-details {
                grid-template-columns: 1fr 1fr;
            }

            .bill-actions {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="admin-title">
            <i class="fas fa-fire"></i>
            <h1>Gestion factures gaz</h1>
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

    <div class="content-grid">
        <div class="form-section">
            <h2 class="section-title">
                <i class="fas fa-plus-circle"></i>
                Créer une facture gaz
            </h2>
            
            <form method="POST" id="billForm">
                <input type="hidden" name="action" value="create">
                
                <div class="form-group">
                    <label for="user_id">Utilisateur *</label>
                    <select id="user_id" name="user_id" required>
                        <option value="">Sélectionner un utilisateur</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>">
                                <?php echo htmlspecialchars($user['name'] . ' ' . $user['last_name'] . ' - ' . $user['gas_counter_number']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="bill_number">Numéro de facture *</label>
                    <input type="text" id="bill_number" name="bill_number" required 
                           placeholder="Ex: GAZ-2024-001" 
                           value="GAZ-<?php echo date('Y'); ?>-<?php echo str_pad(count($gas_bills) + 1, 3, '0', STR_PAD_LEFT); ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="consumption">Consommation *</label>
                        <input type="number" id="consumption" name="consumption" step="0.01" required 
                               placeholder="Ex: 150.50">
                    </div>
                    <div class="form-group">
                        <label for="unit">Unité</label>
                        <select id="unit" name="unit">
                            <option value="m³">m³</option>
                            <option value="kWh">kWh</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="rate">Tarif (TND/unité)</label>
                        <input type="number" id="rate" name="rate" step="0.001" 
                               placeholder="Ex: 0.85" value="0.85">
                    </div>
                    <div class="form-group">
                        <label for="amount">Montant total (TND) *</label>
                        <input type="number" id="amount" name="amount" step="0.01" required 
                               placeholder="Ex: 127.50">
                        <button type="button" class="calculate-btn" onclick="calculateAmount()">
                            <i class="fas fa-calculator"></i>
                            Calculer automatiquement
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="due_date">Date d'échéance *</label>
                    <input type="date" id="due_date" name="due_date" required 
                           value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="billing_period_start">Début période</label>
                        <input type="date" id="billing_period_start" name="billing_period_start" 
                               value="<?php echo date('Y-m-01'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="billing_period_end">Fin période</label>
                        <input type="date" id="billing_period_end" name="billing_period_end" 
                               value="<?php echo date('Y-m-t'); ?>">
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-plus"></i>
                    Créer la facture
                </button>
            </form>
        </div>

        <div class="bills-list-section">
            <h2 class="section-title">
                <i class="fas fa-list"></i>
                Factures gaz existantes
            </h2>

            <?php if (!empty($gas_bills)): ?>
                <?php foreach ($gas_bills as $bill): ?>
                    <div class="bill-item">
                        <div class="bill-header">
                            <div>
                                <div class="bill-title">Facture #<?php echo htmlspecialchars($bill['bill_number']); ?></div>
                                <div class="bill-user">
                                    <?php echo htmlspecialchars($bill['name'] . ' ' . $bill['last_name']); ?>
                                    - Compteur: <?php echo htmlspecialchars($bill['gas_counter_number']); ?>
                                </div>
                            </div>
                            <span class="bill-status status-<?php echo $bill['status']; ?>">
                                <?php 
                                echo $bill['status'] === 'pending' ? 'En attente' : 
                                     ($bill['status'] === 'paid' ? 'Payée' : 'En retard'); 
                                ?>
                            </span>
                        </div>
                        
                        <div class="bill-details">
                            <div class="detail-item">
                                <div class="detail-value"><?php echo number_format($bill['consumption'], 2); ?></div>
                                <div class="detail-label"><?php echo $bill['unit']; ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-value"><?php echo number_format($bill['rate'], 3); ?>TND</div>
                                <div class="detail-label">Tarif</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-value"><?php echo number_format($bill['amount'], 2); ?>TND</div>
                                <div class="detail-label">Montant</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-value"><?php echo date('d/m/Y', strtotime($bill['due_date'])); ?></div>
                                <div class="detail-label">Échéance</div>
                            </div>
                        </div>
                        
                        <div class="bill-actions">
                            <?php if ($bill['status'] === 'pending'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="bill_id" value="<?php echo $bill['id']; ?>">
                                    <input type="hidden" name="new_status" value="paid">
                                    <button type="submit" class="action-btn btn-paid">
                                        <i class="fas fa-check"></i>
                                        Marquer comme payée
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="bill_id" value="<?php echo $bill['id']; ?>">
                                    <input type="hidden" name="new_status" value="pending">
                                    <button type="submit" class="action-btn btn-pending">
                                        <i class="fas fa-undo"></i>
                                        Marquer en attente
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette facture ?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="bill_id" value="<?php echo $bill['id']; ?>">
                                <button type="submit" class="action-btn btn-delete">
                                    <i class="fas fa-trash"></i>
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-bills">
                    <i class="fas fa-file-invoice-dollar" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                    <p>Aucune facture de gaz trouvée</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function calculateAmount() {
            const consumption = parseFloat(document.getElementById('consumption').value) || 0;
            const rate = parseFloat(document.getElementById('rate').value) || 0;
            const amount = consumption * rate;
            
            if (consumption > 0 && rate > 0) {
                document.getElementById('amount').value = amount.toFixed(2);
            } else {
                alert('Veuillez entrer une consommation et un tarif valides.');
            }
        }

        // Auto-calculate when consumption or rate changes
        document.getElementById('consumption').addEventListener('input', function() {
            const rate = parseFloat(document.getElementById('rate').value);
            if (rate > 0) {
                calculateAmount();
            }
        });

        document.getElementById('rate').addEventListener('input', function() {
            const consumption = parseFloat(document.getElementById('consumption').value);
            if (consumption > 0) {
                calculateAmount();
            }
        });
    </script>
</body>
</html>