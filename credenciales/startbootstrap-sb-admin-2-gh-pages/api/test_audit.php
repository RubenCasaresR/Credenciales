<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require '../config.php';
require_once '../includes/audit.php';

try {
    logAction($pdo, 'ver', 'test', null, 'Prueba de auditoría - ' . date('Y-m-d H:i:s'));
    echo json_encode(['success' => true, 'message' => 'Audit log insertado correctamente']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>