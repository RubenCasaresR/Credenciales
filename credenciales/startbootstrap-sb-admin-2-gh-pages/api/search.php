<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require '../config.php';

$q = $_GET['q'] ?? '';
if (empty($q) || strlen($q) < 2) {
    echo json_encode(['success' => true, 'servers' => [], 'credentials' => [], 'anydesk' => [], 'documents' => [], 'payment_gateways' => []]);
    exit;
}

$like = '%' . $q . '%';

try {
    require_once '../includes/crypto.php';

    $results = [];

    // Search servers
    $stmt = $pdo->prepare("SELECT id, name, provider, ip_address FROM servers WHERE deleted_at IS NULL AND (name LIKE ? OR provider LIKE ? OR ip_address LIKE ?) LIMIT 5");
    $stmt->execute([$like, $like, $like]);
    $results['servers'] = $stmt->fetchAll();

    // Search credentials
    $stmt = $pdo->prepare("SELECT c.id, c.server_id, c.service_name, c.type, c.username, s.name as server_name FROM credentials c JOIN servers s ON c.server_id = s.id WHERE c.deleted_at IS NULL AND (c.service_name LIKE ? OR c.username LIKE ? OR c.url LIKE ?) LIMIT 5");
    $stmt->execute([$like, $like, $like]);
    $creds = $stmt->fetchAll();
    foreach ($creds as &$c) {
        $c['link'] = 'servidor_detalle.php?id=' . $c['server_id'];
    }
    $results['credentials'] = $creds;

    // Search anydesk
    $stmt = $pdo->prepare("SELECT id, name, ip_address FROM anydesk_accounts WHERE deleted_at IS NULL AND (name LIKE ? OR ip_address LIKE ?) LIMIT 5");
    $stmt->execute([$like, $like]);
    $results['anydesk'] = $stmt->fetchAll();

    // Search documents
    $stmt = $pdo->prepare("SELECT id, name, file_name, file_type FROM documents WHERE deleted_at IS NULL AND (name LIKE ? OR file_name LIKE ?) LIMIT 5");
    $stmt->execute([$like, $like]);
    $results['documents'] = $stmt->fetchAll();

    // Search payment gateways
    $stmt = $pdo->prepare("SELECT id, name, gateway_type, merchant_id, email FROM payment_gateways WHERE deleted_at IS NULL AND (name LIKE ? OR gateway_type LIKE ? OR merchant_id LIKE ? OR email LIKE ?) LIMIT 5");
    $stmt->execute([$like, $like, $like, $like]);
    $results['payment_gateways'] = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $results]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>