<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require '../config.php';
require_once '../includes/payment_crypto.php';
require_once '../includes/permissions.php';

if (!userCan('payment_gateways', 'ver')) {
    echo json_encode(['success' => true, 'data' => []]);
    exit;
}

try {
    $stmt = $pdo->query("SELECT id, name, gateway_type, merchant_id, public_key, secret_key, webhook_secret, sandbox, email, notes, created_at FROM payment_gateways WHERE deleted_at IS NULL ORDER BY created_at DESC");
    $gateways = $stmt->fetchAll();

    foreach ($gateways as &$gw) {
        $decryptedSecret = decryptPaymentData($gw['secret_key']);
        $decryptedWebhook = decryptPaymentData($gw['webhook_secret']);

        if (!empty($decryptedSecret) && strlen($decryptedSecret) > 4) {
            $gw['secret_key_masked'] = str_repeat('*', strlen($decryptedSecret) - 4) . substr($decryptedSecret, -4);
        } else {
            $gw['secret_key_masked'] = $decryptedSecret;
        }

        if (!empty($decryptedWebhook) && strlen($decryptedWebhook) > 4) {
            $gw['webhook_secret_masked'] = str_repeat('*', strlen($decryptedWebhook) - 4) . substr($decryptedWebhook, -4);
        } else {
            $gw['webhook_secret_masked'] = $decryptedWebhook;
        }

        unset($gw['secret_key']);
        unset($gw['webhook_secret']);
    }

    echo json_encode(['success' => true, 'data' => $gateways]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
