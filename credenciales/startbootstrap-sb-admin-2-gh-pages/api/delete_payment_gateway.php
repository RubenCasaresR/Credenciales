<?php
require_once '../includes/security.php';
require_once '../includes/permissions.php';
session_start();
header('Content-Type: application/json');

if (!userCan('payment_gateways', 'eliminar')) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}
requireCsrfToken();

require '../config.php';
require_once '../includes/audit.php';

$id = $_POST['id'] ?? null;
if (empty($id)) {
    echo json_encode(['success' => false, 'error' => 'ID requerido']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT name, gateway_type FROM payment_gateways WHERE id = ?");
    $stmt->execute([$id]);
    $gw = $stmt->fetch();

    if (!$gw) {
        echo json_encode(['success' => false, 'error' => 'No encontrado']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE payment_gateways SET deleted_at = NOW() WHERE id = ?");
    $stmt->execute([$id]);
    logAction($pdo, 'eliminar', 'payment_gateway', $id, "Cuenta '{$gw['name']}' ({$gw['gateway_type']}) eliminada");

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
