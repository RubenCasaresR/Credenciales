<?php
require 'config.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS credentials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        service_name VARCHAR(255) NOT NULL,
        type VARCHAR(100) NOT NULL,
        url VARCHAR(255) NULL,
        username VARCHAR(255) NULL,
        password VARCHAR(255) NOT NULL,
        associated_email VARCHAR(255) NULL,
        payment_date DATE NULL,
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        deleted_at TIMESTAMP NULL DEFAULT NULL
    )";
    
    $pdo->exec($sql);
    echo "Migration completed: 'credentials' table created successfully.\n";
} catch (PDOException $e) {
    echo "Error during migration: " . $e->getMessage() . "\n";
}
?>
