<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require '../config.php';

try {
    $stmt = $pdo->query("SELECT al.*, DATE_FORMAT(al.created_at, '%Y-%m-%d %H:%i:%s') as created_at_formatted FROM audit_log al ORDER BY al.created_at DESC LIMIT 10");
    $logs = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $logs]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>