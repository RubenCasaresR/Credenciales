<?php
function logAction($pdo, $action, $entityType, $entityId = null, $details = null) {
    if (!isset($_SESSION['user_id'])) return;
    
    $userId = $_SESSION['user_id'];
    $userName = $_SESSION['user_name'] ?? 'Desconocido';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $stmt = $pdo->prepare("INSERT INTO audit_log (user_id, user_name, action, entity_type, entity_id, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $userName, $action, $entityType, $entityId, $details, $ip, $userAgent]);
}
?>