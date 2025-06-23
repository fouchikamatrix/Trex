<?php
require_once 'config.php';

// Admin credentials
$username = 'admin';
$password = 'admin123';
$role = 'super_admin';

try {
    // Hash the password properly
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->fetch()) {
        // Update existing admin
        $stmt = $pdo->prepare("UPDATE admin_users SET password = ?, role = ? WHERE username = ?");
        $stmt->execute([$hashed_password, $role, $username]);
        echo "Admin user updated successfully!<br>";
    } else {
        // Create new admin
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$username, $hashed_password, $role]);
        echo "Admin user created successfully!<br>";
    }
    
    echo "Username: " . $username . "<br>";
    echo "Password: " . $password . "<br>";
    echo "You can now delete this file for security.";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>