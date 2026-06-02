<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require '../config.php';

try {
    // Fetch users that are not soft-deleted
    $stmt = $pdo->query("SELECT id, name, email, role, created_at FROM users WHERE deleted_at IS NULL");
    $users = $stmt->fetchAll();
    
    // DataTables expects data wrapped in a 'data' array
    echo json_encode(['data' => $users]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
?>
