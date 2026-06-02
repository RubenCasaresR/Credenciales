<?php
require_once '../includes/security.php';
require_once '../includes/permissions.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if (!userCan('contrasenas', 'editar')) {
    echo json_encode(['success' => false, 'error' => 'No tienes permisos para realizar esta acción.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

requireCsrfToken();

require '../config.php';
require '../includes/crypto.php';
require_once '../includes/audit.php';

$credential_id = $_POST['credential_id'] ?? null;
$new_password = $_POST['new_password'] ?? '';

if (empty($credential_id) || empty($new_password)) {
    echo json_encode(['success' => false, 'error' => 'Credential ID y nueva contraseña son obligatorios.']);
    exit;
}

if (strlen($new_password) < 6) {
    echo json_encode(['success' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, service_name, password FROM credentials WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$credential_id]);
    $credential = $stmt->fetch();
    
    if (!$credential) {
        echo json_encode(['success' => false, 'error' => 'La credencial no existe.']);
        exit;
    }
    
    $current_password = $credential['password'];
    $currentDecrypted = !empty($current_password) ? decryptData($current_password) : '';
    
    if ($currentDecrypted === $new_password) {
        echo json_encode(['success' => false, 'error' => 'La nueva contraseña es igual a la actual.']);
        exit;
    }
    
    if (!empty($current_password)) {
        $stmtHist = $pdo->prepare("INSERT INTO password_history (credential_id, password, changed_by, changed_by_name) VALUES (?, ?, ?, ?)");
        $stmtHist->execute([
            $credential_id,
            $current_password,
            $_SESSION['user_id'],
            $_SESSION['user_name']
        ]);
        
        $stmtPrune = $pdo->prepare("
            DELETE FROM password_history 
            WHERE credential_id = ? AND id NOT IN (
                SELECT id FROM (
                    SELECT id FROM password_history 
                    WHERE credential_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT 10
                ) AS keep
            )
        ");
        $stmtPrune->execute([$credential_id, $credential_id]);
    }
    
    $encrypted_password = encryptData($new_password);
    
    $stmtUpd = $pdo->prepare("UPDATE credentials SET password = ? WHERE id = ?");
    $stmtUpd->execute([$encrypted_password, $credential_id]);
    
    logAction($pdo, 'editar', 'credencial', $credential_id, "Contraseña actualizada para '{$credential['service_name']}' desde el módulo Contraseñas");
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
