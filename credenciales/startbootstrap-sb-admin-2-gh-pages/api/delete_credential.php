<?php
require_once '../includes/security.php';
require_once '../includes/permissions.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if (!userCan('credenciales', 'eliminar')) {
    echo json_encode(['success' => false, 'error' => 'No tienes permisos para realizar esta acción.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

requireCsrfToken();

require '../config.php';
require_once '../includes/audit.php';

$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'Missing ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT service_name FROM credentials WHERE id = ?");
    $stmt->execute([$id]);
    $cred = $stmt->fetch();
    $name = $cred ? $cred['service_name'] : 'Desconocido';

    $stmt = $pdo->prepare("UPDATE credentials SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$id]);
    logAction($pdo, 'eliminar', 'credencial', $id, "Credencial '$name' eliminada");
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
