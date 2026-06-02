<?php
require_once '../includes/security.php';
require_once '../includes/permissions.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

if (!userCan('servidores', 'eliminar')) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método inválido']);
    exit;
}

requireCsrfToken();

require '../config.php';
require_once '../includes/audit.php';

$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'Falta el ID del servidor']);
    exit;
}

try {
    // We should ideally soft-delete all associated credentials too, but for simplicity, 
    // we'll just soft-delete the server. The query fetching credentials uses INNER JOIN or filters if needed.
    $stmt = $pdo->prepare("SELECT name FROM servers WHERE id = ?");
    $stmt->execute([$id]);
    $srv = $stmt->fetch();
    $name = $srv ? $srv['name'] : 'Desconocido';

    $stmt = $pdo->prepare("UPDATE servers SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$id]);
    
    $stmtCreds = $pdo->prepare("UPDATE credentials SET deleted_at = CURRENT_TIMESTAMP WHERE server_id = ? AND deleted_at IS NULL");
    $stmtCreds->execute([$id]);

    logAction($pdo, 'eliminar', 'servidor', $id, "Servidor '$name' y sus credenciales eliminados");
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error de base de datos']);
}
?>
