<?php
require 'config.php';

try {
    // Add 'name' column if not exists
    $pdo->exec("ALTER TABLE users ADD COLUMN name VARCHAR(255) NOT NULL DEFAULT 'Admin' AFTER id");
    // Add 'role' column if not exists
    $pdo->exec("ALTER TABLE users ADD COLUMN role VARCHAR(50) DEFAULT 'Usuario' AFTER password");
    // Add 'deleted_at' column if not exists
    $pdo->exec("ALTER TABLE users ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL AFTER created_at");

    // Update the default user's name
    $pdo->exec("UPDATE users SET name = 'Administrador', role = 'Administrador' WHERE email = 'admin@example.com'");

    echo "Migration completed successfully.\n";
} catch (PDOException $e) {
    echo "Error during migration: " . $e->getMessage() . "\n";
}
?>
