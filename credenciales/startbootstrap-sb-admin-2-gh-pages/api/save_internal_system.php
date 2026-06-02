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
if (!userCan('internal_systems', $accion)) {
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
$domain = $_POST['domain'] ?? '';
$hosted_at = $_POST['hosted_at'] ?? '';
$github_account = $_POST['github_account'] ?? '';
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$notes = $_POST['notes'] ?? '';

if (empty($name)) {
    echo json_encode(['success' => false, 'error' => 'El nombre del sistema es obligatorio.']);
    exit;
}

try {
    $encrypted_password = !empty($password) ? encryptData($password) : null;

    if (empty($id)) {
        $stmt = $pdo->prepare("INSERT INTO internal_systems (name, domain, hosted_at, github_account, username, password, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $domain, $hosted_at, $github_account, $username, $encrypted_password, $notes]);
        logAction($pdo, 'crear', 'internal_system', $pdo->lastInsertId(), "Sistema interno '$name' creado");
    } else {
        if (!empty($password)) {
            $stmt = $pdo->prepare("UPDATE internal_systems SET name = ?, domain = ?, hosted_at = ?, github_account = ?, username = ?, password = ?, notes = ? WHERE id = ?");
            $stmt->execute([$name, $domain, $hosted_at, $github_account, $username, $encrypted_password, $notes, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE internal_systems SET name = ?, domain = ?, hosted_at = ?, github_account = ?, username = ?, notes = ? WHERE id = ?");
            $stmt->execute([$name, $domain, $hosted_at, $github_account, $username, $notes, $id]);
        }
        logAction($pdo, 'editar', 'internal_system', $id, "Sistema interno '$name' editado");
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error de base de datos']);
}
?>
