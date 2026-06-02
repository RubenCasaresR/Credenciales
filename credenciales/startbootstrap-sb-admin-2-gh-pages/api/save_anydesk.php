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
if (!userCan('anydesk', $accion)) {
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
$ip_address = $_POST['ip_address'] ?? '';
$password = $_POST['password'] ?? '';
$gmail_email = $_POST['gmail_email'] ?? '';
$gmail_password = $_POST['gmail_password'] ?? '';
$nordvpn = $_POST['nordvpn'] ?? '';
$nordvpn_password = $_POST['nordvpn_password'] ?? '';
$nord_dedicada_ip = $_POST['nord_dedicada_ip'] ?? '';
$anydesk_email = $_POST['anydesk_email'] ?? '';
$anydesk_email_password = $_POST['anydesk_email_password'] ?? '';
$notes = $_POST['notes'] ?? '';

if (empty($name) || empty($ip_address)) {
    echo json_encode(['success' => false, 'error' => 'Nombre e IP son obligatorios.']);
    exit;
}

if (empty($id) && empty($password)) {
    echo json_encode(['success' => false, 'error' => 'La contraseña de AnyDesk es obligatoria para nuevas cuentas.']);
    exit;
}

try {
    $encrypted_password = !empty($password) ? encryptData($password) : null;
    $encrypted_gmail = !empty($gmail_password) ? encryptData($gmail_password) : null;
    $encrypted_nordvpn = !empty($nordvpn_password) ? encryptData($nordvpn_password) : null;
    $encrypted_anydesk_email = !empty($anydesk_email_password) ? encryptData($anydesk_email_password) : null;

    if (empty($id)) {
        $stmt = $pdo->prepare("INSERT INTO anydesk_accounts (name, ip_address, password, gmail_email, gmail_password, nordvpn, nordvpn_password, nord_dedicada_ip, anydesk_email, anydesk_email_password, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $ip_address, $encrypted_password, $gmail_email, $encrypted_gmail, $nordvpn, $encrypted_nordvpn, $nord_dedicada_ip, $anydesk_email, $encrypted_anydesk_email, $notes]);
        logAction($pdo, 'crear', 'anydesk', $pdo->lastInsertId(), "Cuenta AnyDesk '$name' creada");
    } else {
        $sql = "UPDATE anydesk_accounts SET name = ?, ip_address = ?, gmail_email = ?, nordvpn = ?, nord_dedicada_ip = ?, anydesk_email = ?, notes = ?";
        $params = [$name, $ip_address, $gmail_email, $nordvpn, $nord_dedicada_ip, $anydesk_email, $notes];

        if (!empty($anydesk_email_password)) {
            $sql .= ", anydesk_email_password = ?";
            $params[] = $encrypted_anydesk_email;
        }

        if (!empty($password)) {
            $sql .= ", password = ?";
            $params[] = $encrypted_password;
        }
        if (!empty($gmail_password)) {
            $sql .= ", gmail_password = ?";
            $params[] = $encrypted_gmail;
        }
        if (!empty($nordvpn_password)) {
            $sql .= ", nordvpn_password = ?";
            $params[] = $encrypted_nordvpn;
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        logAction($pdo, 'editar', 'anydesk', $id, "Cuenta AnyDesk '$name' editada");
    }
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error de base de datos']);
}
?>
