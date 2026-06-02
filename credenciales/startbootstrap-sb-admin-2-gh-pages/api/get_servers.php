<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require '../config.php';
require_once '../includes/permissions.php';

if (!userCan('servidores', 'ver')) {
    echo json_encode(['success' => true, 'data' => []]);
    exit;
}

try {
    $stmt = $pdo->query("SELECT s.id, s.name, s.provider, s.ip_address, s.notes, (SELECT c.responsible FROM credentials c WHERE c.server_id = s.id AND c.type = 'Autenticador' AND c.deleted_at IS NULL LIMIT 1) AS autenticador_responsible FROM servers s WHERE s.deleted_at IS NULL ORDER BY s.created_at DESC");
    $servers = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $servers]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
