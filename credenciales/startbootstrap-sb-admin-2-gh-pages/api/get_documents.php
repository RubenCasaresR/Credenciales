<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require '../config.php';

try {
    $stmt = $pdo->query("SELECT id, name, description, file_name, file_type, file_size, created_at FROM documents WHERE deleted_at IS NULL ORDER BY created_at DESC");
    $documents = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $documents]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>