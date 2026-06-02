<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require '../includes/crypto.php';

try {
    $stmt = $pdo->query("SELECT id, name, domain, hosted_at, github_account, username, password, notes FROM internal_systems WHERE deleted_at IS NULL ORDER BY name ASC");
    $systems = $stmt->fetchAll();

    foreach ($systems as &$s) {
        $s['password_decrypted'] = decryptData($s['password']);
        unset($s['password']);
    }

    echo json_encode(['success' => true, 'data' => $systems]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
