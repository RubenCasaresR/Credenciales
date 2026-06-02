<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require '../includes/crypto.php';

try {
    $stmt = $pdo->query("SELECT id, name, password, ssid, provider, ip_address, notes FROM internet_networks WHERE deleted_at IS NULL ORDER BY name ASC");
    $networks = $stmt->fetchAll();

    foreach ($networks as &$net) {
        $net['password_decrypted'] = decryptData($net['password']);
        unset($net['password']);
    }

    echo json_encode(['success' => true, 'data' => $networks]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
