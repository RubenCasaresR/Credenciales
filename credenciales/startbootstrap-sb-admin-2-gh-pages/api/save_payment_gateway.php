<?php
require_once '../includes/security.php';
require_once '../includes/permissions.php';
session_start();
header('Content-Type: application/json');

$accion = !empty($_POST['id']) ? 'editar' : 'crear';
if (!userCan('payment_gateways', $accion)) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}
requireCsrfToken();

require '../config.php';
require_once '../includes/payment_crypto.php';
require_once '../includes/audit.php';

$id = $_POST['id'] ?? null;
$name = $_POST['name'] ?? '';
$gateway_type = $_POST['gateway_type'] ?? '';
$merchant_id = $_POST['merchant_id'] ?? '';
$public_key = $_POST['public_key'] ?? '';
$secret_key = $_POST['secret_key'] ?? '';
$webhook_secret = $_POST['webhook_secret'] ?? '';
$sandbox = !empty($_POST['sandbox']) ? 1 : 0;
$email = $_POST['email'] ?? '';
$notes = $_POST['notes'] ?? '';

if (empty($name) || empty($gateway_type)) {
    echo json_encode(['success' => false, 'error' => 'Nombre y tipo son obligatorios']);
    exit;
}

try {
    if (empty($id)) {
        if (empty($secret_key)) {
            echo json_encode(['success' => false, 'error' => 'La clave secreta es obligatoria']);
            exit;
        }
        $encryptedSecret = encryptPaymentData($secret_key);
        $encryptedWebhook = encryptPaymentData($webhook_secret);

        $stmt = $pdo->prepare("INSERT INTO payment_gateways (name, gateway_type, merchant_id, public_key, secret_key, webhook_secret, sandbox, email, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $gateway_type, $merchant_id, $public_key, $encryptedSecret, $encryptedWebhook, $sandbox, $email, $notes]);
        logAction($pdo, 'crear', 'payment_gateway', $pdo->lastInsertId(), "Cuenta '$name' ($gateway_type) creada");
    } else {
        if (!empty($secret_key)) {
            $encryptedSecret = encryptPaymentData($secret_key);
            $encryptedWebhook = encryptPaymentData($webhook_secret);
            $stmt = $pdo->prepare("UPDATE payment_gateways SET name=?, gateway_type=?, merchant_id=?, public_key=?, secret_key=?, webhook_secret=?, sandbox=?, email=?, notes=? WHERE id=? AND deleted_at IS NULL");
            $stmt->execute([$name, $gateway_type, $merchant_id, $public_key, $encryptedSecret, $encryptedWebhook, $sandbox, $email, $notes, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE payment_gateways SET name=?, gateway_type=?, merchant_id=?, public_key=?, sandbox=?, email=?, notes=? WHERE id=? AND deleted_at IS NULL");
            $stmt->execute([$name, $gateway_type, $merchant_id, $public_key, $sandbox, $email, $notes, $id]);
        }
        logAction($pdo, 'editar', 'payment_gateway', $id, "Cuenta '$name' ($gateway_type) editada");
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
