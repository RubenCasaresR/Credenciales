<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require '../config.php';
require_once '../includes/permissions.php';

if (!userCan('contrasenas', 'ver')) {
    echo json_encode(['success' => true, 'data' => []]);
    exit;
}

try {
    $stmt = $pdo->query("SELECT DISTINCT s.id, s.name, s.provider, s.ip_address, s.notes FROM servers s INNER JOIN credentials c ON c.server_id = s.id WHERE c.type = 'Hosting' AND c.deleted_at IS NULL AND s.deleted_at IS NULL ORDER BY s.created_at DESC");
    $servers = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $servers]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
