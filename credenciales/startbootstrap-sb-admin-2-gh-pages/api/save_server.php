<?php
require_once '../includes/security.php';
require_once '../includes/permissions.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$accion = !empty($_POST['id']) ? 'editar' : 'crear';
if (!userCan('servidores', $accion)) {
    echo json_encode(['success' => false, 'error' => 'Permisos insuficientes']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

requireCsrfToken();

require '../config.php';
require_once '../includes/audit.php';

$id = $_POST['id'] ?? null;
$name = $_POST['name'] ?? '';
$provider = $_POST['provider'] ?? '';
$ip_address = $_POST['ip_address'] ?? '';
$notes = $_POST['notes'] ?? '';

if (empty($name)) {
    echo json_encode(['success' => false, 'error' => 'El nombre del servidor es obligatorio']);
    exit;
}

try {
    if (empty($id)) {
        $stmt = $pdo->prepare("INSERT INTO servers (name, provider, ip_address, notes) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $provider, $ip_address, $notes]);
        logAction($pdo, 'crear', 'servidor', $pdo->lastInsertId(), "Servidor '$name' creado");
    } else {
        $stmt = $pdo->prepare("UPDATE servers SET name = ?, provider = ?, ip_address = ?, notes = ? WHERE id = ?");
        $stmt->execute([$name, $provider, $ip_address, $notes, $id]);
        logAction($pdo, 'editar', 'servidor', $id, "Servidor '$name' editado");
    }
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error de base de datos']);
}
?>
