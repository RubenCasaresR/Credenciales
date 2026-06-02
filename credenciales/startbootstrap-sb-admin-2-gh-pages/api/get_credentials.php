<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require '../config.php';
require_once '../includes/crypto.php';

$isAdmin = ($_SESSION['user_role'] === 'Administrador');

try {
    $server_id = $_GET['server_id'] ?? null;
    
    if ($server_id) {
        $stmt = $pdo->prepare("SELECT id, server_id, service_name, type, url, username, password, associated_email, db_name, port, domain_name, responsible, recovery_code, payment_date, notes FROM credentials WHERE server_id = ? AND deleted_at IS NULL ORDER BY created_at DESC");
        $stmt->execute([$server_id]);
    } else {
        $stmt = $pdo->prepare("SELECT id, server_id, service_name, type, url, username, password, associated_email, db_name, port, domain_name, responsible, recovery_code, payment_date, notes FROM credentials WHERE deleted_at IS NULL ORDER BY created_at DESC");
        $stmt->execute();
    }
    
    $credentials = $stmt->fetchAll();
    
    foreach ($credentials as &$cred) {
        $decrypted = decryptData($cred['password']);
        $cred['password'] = $isAdmin ? $decrypted : '********';
    }

    echo json_encode(['data' => $credentials]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
?>