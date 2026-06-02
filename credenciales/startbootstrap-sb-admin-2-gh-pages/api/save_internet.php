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
if (!userCan('internet', $accion)) {
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
$name = $_POST['name'] ?? '';
$ssid = $_POST['ssid'] ?? '';
$password = $_POST['password'] ?? '';
$provider = $_POST['provider'] ?? '';
$ip_address = $_POST['ip_address'] ?? '';
$notes = $_POST['notes'] ?? '';

if (empty($name)) {
    echo json_encode(['success' => false, 'error' => 'El nombre de la red es obligatorio.']);
    exit;
}

if (empty($id) && empty($password)) {
    echo json_encode(['success' => false, 'error' => 'La contraseña es obligatoria para nuevas redes.']);
    exit;
}

try {
    $encrypted_password = !empty($password) ? encryptData($password) : null;

    if (empty($id)) {
        $stmt = $pdo->prepare("INSERT INTO internet_networks (name, password, ssid, provider, ip_address, notes) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $encrypted_password, $ssid, $provider, $ip_address, $notes]);
        logAction($pdo, 'crear', 'internet', $pdo->lastInsertId(), "Red '$name' creada");
    } else {
        if (!empty($password)) {
            $stmt = $pdo->prepare("UPDATE internet_networks SET name = ?, password = ?, ssid = ?, provider = ?, ip_address = ?, notes = ? WHERE id = ?");
            $stmt->execute([$name, $encrypted_password, $ssid, $provider, $ip_address, $notes, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE internet_networks SET name = ?, ssid = ?, provider = ?, ip_address = ?, notes = ? WHERE id = ?");
            $stmt->execute([$name, $ssid, $provider, $ip_address, $notes, $id]);
        }
        logAction($pdo, 'editar', 'internet', $id, "Red '$name' editada");
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error de base de datos']);
}
?>
