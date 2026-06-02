<?php
require_once '../includes/security.php';
require_once '../includes/permissions.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$accion = !empty($_POST['id']) ? 'editar' : 'crear';
if (!userCan('credenciales', $accion)) {
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

$id = $_POST['id'] ?? null;
$server_id = $_POST['server_id'] ?? null;

// Verify server exists
if (!empty($server_id)) {
    $check = $pdo->prepare("SELECT id FROM servers WHERE id = ? AND deleted_at IS NULL");
    $check->execute([$server_id]);
    if (!$check->fetch()) {
        echo json_encode(['success' => false, 'error' => 'El servidor seleccionado no existe.']);
        exit;
    }
}
$service_name = $_POST['service_name'] ?? '';
$type = $_POST['type'] ?? '';
$url = $_POST['url'] ?? '';
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$associated_email = $_POST['associated_email'] ?? '';
$db_name = $_POST['db_name'] ?? '';
$port = $_POST['port'] ?? '';
$domain_name = $_POST['domain_name'] ?? '';
$responsible = $_POST['responsible'] ?? '';
$recovery_code = $_POST['recovery_code'] ?? '';
$payment_date = !empty($_POST['payment_date']) ? $_POST['payment_date'] : null;
$notes = $_POST['notes'] ?? '';

if (empty($server_id) || empty($service_name) || empty($type)) {
    echo json_encode(['success' => false, 'error' => 'Servidor, Nombre y Tipo son obligatorios.']);
    exit;
}

$encrypted_password = !empty($password) ? encryptData($password) : null;

try {
    if (empty($id)) {
        $stmt = $pdo->prepare("INSERT INTO credentials (server_id, service_name, type, url, username, password, associated_email, db_name, port, domain_name, responsible, recovery_code, payment_date, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$server_id, $service_name, $type, $url, $username, $encrypted_password, $associated_email, $db_name, $port, $domain_name, $responsible, $recovery_code, $payment_date, $notes]);
        $newId = $pdo->lastInsertId();
        logAction($pdo, 'crear', 'credencial', $newId, "Credencial '$service_name' creada");
    } else {
        $sql = "UPDATE credentials SET server_id = ?, service_name = ?, type = ?, url = ?, username = ?, associated_email = ?, db_name = ?, port = ?, domain_name = ?, responsible = ?, recovery_code = ?, payment_date = ?, notes = ?";
        $params = [$server_id, $service_name, $type, $url, $username, $associated_email, $db_name, $port, $domain_name, $responsible, $recovery_code, $payment_date, $notes];

        if (!empty($password)) {
            $sql .= ", password = ?";
            $params[] = $encrypted_password;
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        logAction($pdo, 'editar', 'credencial', $id, "Credencial '$service_name' editada");
    }
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>