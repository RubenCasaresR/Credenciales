<?php
require_once __DIR__ . '/config.php';

try {
    $stmt = $pdo->query("SHOW COLUMNS FROM user_permissions LIKE 'bloqueo'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE user_permissions ADD COLUMN bloqueo TINYINT(1) DEFAULT 0 AFTER eliminar");
        echo "Column 'bloqueo' added to user_permissions.\n";
    } else {
        echo "Column 'bloqueo' already exists in user_permissions.\n";
    }

    $pdo->exec("ALTER TABLE users DROP COLUMN IF EXISTS bloqueo");
    echo "Column 'bloqueo' dropped from users (if existed).\n";

    $pdo->exec("UPDATE user_permissions up JOIN users u ON up.user_id = u.id SET up.bloqueo = 1 WHERE u.role = 'Usuario' AND u.deleted_at IS NULL AND up.modulo IN ('servidores', 'monitoreo', 'bitacora', 'payment_gateways', 'usuarios')");
    $pdo->exec("UPDATE user_permissions up JOIN users u ON up.user_id = u.id SET up.bloqueo = 0 WHERE u.role = 'Administrador'");
    $pdo->exec("UPDATE user_permissions up JOIN users u ON up.user_id = u.id SET up.bloqueo = 0 WHERE u.role = 'Usuario' AND u.deleted_at IS NULL AND up.modulo NOT IN ('servidores', 'monitoreo', 'bitacora', 'payment_gateways', 'usuarios')");

    echo "Migration completed successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
