<?php
require_once '../includes/security.php';
require_once '../includes/permissions.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if (!userCan('usuarios', 'crear')) {
    echo json_encode(['success' => false, 'error' => 'No tienes permisos para realizar esta acción.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

requireCsrfToken();

require '../config.php';

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? 'Usuario';

if (empty($name) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Nombre, email y contraseña son obligatorios.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Email inválido.']);
    exit;
}

$pwErrors = validatePassword($password);
if (!empty($pwErrors)) {
    echo json_encode(['success' => false, 'error' => implode(' ', $pwErrors)]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND deleted_at IS NULL");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'El email ya está registrado.']);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $hashedPassword, $role]);
    $userId = $pdo->lastInsertId();

    $modulos = getAllModulos();
    $modulosBloqueables = ['servidores', 'monitoreo', 'bitacora', 'payment_gateways', 'usuarios'];
    $stmtPerm = $pdo->prepare("INSERT INTO user_permissions (user_id, modulo, ver, crear, editar, eliminar, bloqueo) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($modulos as $mod) {
        $ver = isset($_POST["permisos"][$mod]["ver"]) ? (int)$_POST["permisos"][$mod]["ver"] : 1;
        $crear = isset($_POST["permisos"][$mod]["crear"]) ? (int)$_POST["permisos"][$mod]["crear"] : 0;
        $editar = isset($_POST["permisos"][$mod]["editar"]) ? (int)$_POST["permisos"][$mod]["editar"] : 0;
        $eliminar = isset($_POST["permisos"][$mod]["eliminar"]) ? (int)$_POST["permisos"][$mod]["eliminar"] : 0;
        $defaultBloqueo = ($role === 'Usuario' && in_array($mod, $modulosBloqueables)) ? 1 : 0;
        $bloqueo = isset($_POST["permisos"][$mod]["bloqueo"]) ? (int)$_POST["permisos"][$mod]["bloqueo"] : $defaultBloqueo;
        $stmtPerm->execute([$userId, $mod, $ver, $crear, $editar, $eliminar, $bloqueo]);
    }

    require_once '../includes/audit.php';
    logAction($pdo, 'crear', 'usuario', $userId, "Usuario '$name' creado por " . ($_SESSION['user_name'] ?? 'Admin'));

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>