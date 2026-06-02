<?php
require_once __DIR__ . '/config.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_permissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        modulo VARCHAR(100) NOT NULL,
        ver TINYINT(1) DEFAULT 0,
        crear TINYINT(1) DEFAULT 0,
        editar TINYINT(1) DEFAULT 0,
        eliminar TINYINT(1) DEFAULT 0,
        bloqueo TINYINT(1) DEFAULT 0,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_modulo (user_id, modulo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $modulos = ['servidores', 'credenciales', 'anydesk', 'documentos', 'internet', 'payment_gateways', 'internal_systems', 'usuarios', 'monitoreo', 'bitacora', 'paginas_web'];

    $bloqueados = ['servidores', 'monitoreo', 'bitacora', 'payment_gateways', 'usuarios'];

    $stmtAdmins = $pdo->query("SELECT id FROM users WHERE role = 'Administrador' AND deleted_at IS NULL");
    $admins = $stmtAdmins->fetchAll();
    foreach ($admins as $admin) {
        foreach ($modulos as $modulo) {
            $pdo->prepare("INSERT IGNORE INTO user_permissions (user_id, modulo, ver, crear, editar, eliminar, bloqueo) VALUES (?, ?, 1, 1, 1, 1, 0)")
                ->execute([$admin['id'], $modulo]);
        }
    }

    $stmtUsers = $pdo->query("SELECT id FROM users WHERE role = 'Usuario' AND deleted_at IS NULL");
    $users = $stmtUsers->fetchAll();
    foreach ($users as $user) {
        foreach ($modulos as $modulo) {
            $bloqueo = in_array($modulo, $bloqueados) ? 1 : 0;
            $pdo->prepare("INSERT IGNORE INTO user_permissions (user_id, modulo, ver, crear, editar, eliminar, bloqueo) VALUES (?, ?, 1, 0, 0, 0, ?)")
                ->execute([$user['id'], $modulo, $bloqueo]);
        }
    }

    echo "Migration completed successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
