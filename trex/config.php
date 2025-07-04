<?php
// config.php - Database configuration
$host = 'localhost';
$dbname = 'gas_electricity_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Create table if not exists
$createTable = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    id_card_number VARCHAR(20) UNIQUE NOT NULL,
    reference VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    gas_counter_number VARCHAR(50) NOT NULL,
    electric_counter_number VARCHAR(50) NOT NULL,
    electric_counter_type ENUM('classic', 'electronic', 'linky') NOT NULL,
    client_type ENUM('residentiel', 'industriel') NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$pdo->exec($createTable);
?>