<?php
require_once '../includes/security.php';
require_once '../includes/permissions.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if (!userCan('usuarios', 'eliminar')) {
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

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'Missing user ID']);
    exit;
}

// Optional: Prevent deleting oneself
if ($id == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'error' => 'Cannot delete your own account']);
    exit;
}

try {
    // Soft delete by setting deleted_at
    $stmt = $pdo->prepare("UPDATE users SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
