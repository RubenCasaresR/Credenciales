<?php
require 'config.php';

try {
    // 1. Create servers table
    $sqlServers = "CREATE TABLE IF NOT EXISTS servers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        provider VARCHAR(100) NULL,
        ip_address VARCHAR(50) NULL,
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        deleted_at TIMESTAMP NULL DEFAULT NULL
    )";
    $pdo->exec($sqlServers);
    echo "Table 'servers' created successfully.\n";

    // 2. Add server_id to credentials
    // We check if the column exists to avoid errors on multiple runs
    $stmt = $pdo->query("SHOW COLUMNS FROM credentials LIKE 'server_id'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE credentials ADD COLUMN server_id INT NULL AFTER id");
        // Optional: add foreign key
        // $pdo->exec("ALTER TABLE credentials ADD CONSTRAINT fk_server FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE SET NULL");
        echo "Column 'server_id' added to credentials.\n";
    } else {
        echo "Column 'server_id' already exists in credentials.\n";
    }

    echo "Migration completed.\n";
} catch (PDOException $e) {
    echo "Error during migration: " . $e->getMessage() . "\n";
}
?>
