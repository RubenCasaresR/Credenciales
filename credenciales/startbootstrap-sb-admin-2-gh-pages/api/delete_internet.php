<?php
require_once '../includes/security.php';
require_once '../includes/permissions.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if (!userCan('internet', 'eliminar')) {
    echo json_encode(['success' => false, 'error' => 'No tienes permisos para realizar esta acción.']);
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

if (empty($id)) {
    echo json_encode(['success' => false, 'error' => 'ID requerido']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT name FROM internet_networks WHERE id = ?");
    $stmt->execute([$id]);
    $net = $stmt->fetch();
    $name = $net ? $net['name'] : 'Desconocido';

    $stmt = $pdo->prepare("UPDATE internet_networks SET deleted_at = NOW() WHERE id = ?");
    $stmt->execute([$id]);
    logAction($pdo, 'eliminar', 'internet', $id, "Red '$name' eliminada");
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error de base de datos']);
}
?>
