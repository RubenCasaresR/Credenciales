<?php
require 'config.php';

try {
    $pdo->exec("ALTER TABLE credentials ADD COLUMN responsible VARCHAR(255) DEFAULT NULL AFTER domain_name");
    $pdo->exec("ALTER TABLE credentials ADD COLUMN recovery_code TEXT DEFAULT NULL AFTER responsible");
    echo "Migración completada: columnas 'responsible' y 'recovery_code' agregadas a 'credentials'.\n";
} catch (PDOException $e) {
    echo "Error durante la migración: " . $e->getMessage() . "\n";
}
?>