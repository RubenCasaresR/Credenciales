<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'No ID provided']);
    exit;
}

require '../config.php';
require_once '../includes/permissions.php';

try {
    $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$_GET['id']]);
    $user = $stmt->fetch();
    
    if ($user) {
        $permisos = getUserPermissionsFromDb($pdo, $user['id']);
        echo json_encode(['success' => true, 'data' => $user, 'permisos' => $permisos]);
    } else {
        echo json_encode(['success' => false, 'error' => 'User not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
