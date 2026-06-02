<?php
require 'config.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS payment_gateways (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        gateway_type VARCHAR(50) NOT NULL,
        merchant_id VARCHAR(255) DEFAULT NULL,
        public_key VARCHAR(255) DEFAULT NULL,
        secret_key TEXT DEFAULT NULL,
        webhook_secret TEXT DEFAULT NULL,
        sandbox TINYINT(1) DEFAULT 1,
        email VARCHAR(255) DEFAULT NULL,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        deleted_at TIMESTAMP NULL DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    echo "Migration completed: payment_gateways table created.\n";
} catch (PDOException $e) {
    echo "Error during migration: " . $e->getMessage() . "\n";
}
