<?php
session_start();
require_once 'config.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit();
}

// Get bill ID from request
$bill_id = $_GET['bill_id'] ?? '';
if (empty($bill_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Bill ID required']);
    exit();
}

try {
    // Get bill information - Updated to match actual table structure
    $stmt = $pdo->prepare("SELECT * FROM bills WHERE id = ? AND user_id = ?");
    $stmt->execute([$bill_id, $_SESSION['user_id']]);
    $bill = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$bill) {
        http_response_code(404);
        echo json_encode(['error' => 'Bill not found']);
        exit();
    }
    
    // Try to get user data if users table exists, otherwise use session data
    $user_name = $_SESSION['user_name'] ?? 'Client';
    $user_email = $_SESSION['user_email'] ?? '';
    $user_address = $_SESSION['user_address'] ?? 'Non spécifiée';
    
    // Try to get user info from users table if it exists
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
        // Users table might not exist or have different structure, use session data
        error_log("User table access error: " . $e->getMessage());
    }
    
    // Format the response
    $response = [
        'success' => true,
        'bill' => [
            'id' => $bill['id'],
            'bill_number' => $bill['bill_number'],
            'service_type' => $bill['service_type'],
            'amount' => number_format($bill['amount'], 2),
            'consumption' => $bill['consumption'],
            'unit' => $bill['unit'],
            'rate' => number_format($bill['rate'], 3),
            'status' => $bill['status'],
            'due_date' => date('d/m/Y', strtotime($bill['due_date'])),
            'billing_period_start' => date('d/m/Y', strtotime($bill['billing_period_start'])),
            'billing_period_end' => date('d/m/Y', strtotime($bill['billing_period_end'])),
            'created_at' => date('d/m/Y H:i', strtotime($bill['created_at'])),
            'paid_at' => $bill['paid_at'] ? date('d/m/Y H:i', strtotime($bill['paid_at'])) : null,
            'user_name' => $user_name,
            'user_email' => $user_email,
            'user_address' => $user_address,
            'subtotal' => number_format($bill['amount'] * 0.85, 2),
            'taxes' => number_format($bill['amount'] * 0.15, 2),
            'service_type_fr' => $bill['service_type'] === 'gas' ? 'Gaz' : 'Électricité',
            'status_fr' => [
                'pending' => 'En attente',
                'paid' => 'Payée', 
                'overdue' => 'En retard',
                'cancelled' => 'Annulée'
            ][$bill['status']] ?? $bill['status']
        ]
    ];
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
    error_log("Bill details error: " . $e->getMessage());
}
?>
