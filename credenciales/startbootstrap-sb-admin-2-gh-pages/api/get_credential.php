<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require '../includes/crypto.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'ID requerido']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, server_id, service_name, type, url, username, password, associated_email, db_name, port, domain_name, responsible, recovery_code, payment_date, notes FROM credentials WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$id]);
    $credential = $stmt->fetch();

    if ($credential) {
        $credential['password'] = decryptData($credential['password']);
        echo json_encode(['success' => true, 'data' => $credential]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>