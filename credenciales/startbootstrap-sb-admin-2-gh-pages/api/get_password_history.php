<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../includes/permissions.php';
require '../config.php';
require_once '../includes/crypto.php';
require_once '../includes/audit.php';

if (!userCan('contrasenas', 'ver')) {
    echo json_encode(['error' => 'No tienes permisos para ver esta información.']);
    exit;
}

try {
    $credential_id = $_GET['credential_id'] ?? null;
    
    if (!$credential_id) {
        echo json_encode(['error' => 'credential_id es requerido']);
        exit;
    }
    
    $stmt = $pdo->prepare("
        SELECT ph.id, ph.password, ph.changed_by_name, ph.created_at
        FROM password_history ph
        WHERE ph.credential_id = ?
        ORDER BY ph.created_at DESC
    ");
    $stmt->execute([$credential_id]);
    $history = $stmt->fetchAll();
    
    foreach ($history as &$entry) {
        $entry['password'] = decryptData($entry['password']);
    }
    
    $stmtCred = $pdo->prepare("SELECT password FROM credentials WHERE id = ? AND deleted_at IS NULL");
    $stmtCred->execute([$credential_id]);
    $credential = $stmtCred->fetch();
    $currentPassword = $credential ? decryptData($credential['password']) : '';
    
    logAction($pdo, 'ver', 'historial_contraseñas', $credential_id, "Consulta de historial de contraseñas para la credencial ID {$credential_id}");
    
    echo json_encode(['success' => true, 'data' => $history, 'current_password' => $currentPassword]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
