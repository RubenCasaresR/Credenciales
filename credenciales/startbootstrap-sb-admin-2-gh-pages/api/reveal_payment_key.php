<?php
require_once '../includes/security.php';
require_once '../includes/permissions.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!userCan('payment_gateways', 'ver')) {
    echo json_encode(['success' => false, 'error' => 'Solo administradores pueden revelar claves']);
    exit;
}
requireCsrfToken();

require '../config.php';
require_once '../includes/payment_crypto.php';
require_once '../includes/audit.php';

$id = $_POST['id'] ?? null;
$field = $_POST['field'] ?? ''; // 'secret_key' or 'webhook_secret'

if (empty($id) || !in_array($field, ['secret_key', 'webhook_secret'])) {
    echo json_encode(['success' => false, 'error' => 'Solicitud inválida']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, name, gateway_type, $field FROM payment_gateways WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$id]);
    $gw = $stmt->fetch();

    if (!$gw) {
        echo json_encode(['success' => false, 'error' => 'No encontrado']);
        exit;
    }

    $decrypted = decryptPaymentData($gw[$field]);

    logAction($pdo, 'revelar', 'payment_gateway', $id, "Clave '$field' revelada para '{$gw['name']}' ({$gw['gateway_type']})");

    echo json_encode(['success' => true, 'data' => $decrypted]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
