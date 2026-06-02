<?php
require_once '../includes/security.php';
require_once '../includes/permissions.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if (!userCan('usuarios', 'editar')) {
    echo json_encode(['success' => false, 'error' => 'No tienes permisos para realizar esta acción.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

requireCsrfToken();

require '../config.php';

$id = $_POST['id'] ?? null;
$name = $_POST['name'] ?? null;
$email = $_POST['email'] ?? null;
$role = $_POST['role'] ?? null;

if (!$id || !$name || !$email || !$role) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ? AND deleted_at IS NULL");
    $stmt->execute([$email, $id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Email already exists']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
    $stmt->execute([$name, $email, $role, $id]);

    $modulos = getAllModulos();
    $modulosBloqueables = ['servidores', 'monitoreo', 'bitacora', 'payment_gateways', 'usuarios'];
    $pdo->prepare("DELETE FROM user_permissions WHERE user_id = ?")->execute([$id]);
    $stmtPerm = $pdo->prepare("INSERT INTO user_permissions (user_id, modulo, ver, crear, editar, eliminar, bloqueo) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($modulos as $mod) {
        $ver = isset($_POST["permisos"][$mod]["ver"]) ? (int)$_POST["permisos"][$mod]["ver"] : 1;
        $crear = isset($_POST["permisos"][$mod]["crear"]) ? (int)$_POST["permisos"][$mod]["crear"] : 0;
        $editar = isset($_POST["permisos"][$mod]["editar"]) ? (int)$_POST["permisos"][$mod]["editar"] : 0;
        $eliminar = isset($_POST["permisos"][$mod]["eliminar"]) ? (int)$_POST["permisos"][$mod]["eliminar"] : 0;
        $defaultBloqueo = ($role === 'Usuario' && in_array($mod, $modulosBloqueables)) ? 1 : 0;
        $bloqueo = isset($_POST["permisos"][$mod]["bloqueo"]) ? (int)$_POST["permisos"][$mod]["bloqueo"] : $defaultBloqueo;
        $stmtPerm->execute([$id, $mod, $ver, $crear, $editar, $eliminar, $bloqueo]);
    }
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
