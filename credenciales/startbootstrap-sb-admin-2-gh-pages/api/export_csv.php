<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

require '../config.php';
require_once '../includes/crypto.php';

$type = $_GET['type'] ?? '';

$allowedTypes = ['credentials', 'servers', 'anydesk'];
if (!in_array($type, $allowedTypes)) {
    http_response_code(400);
    echo 'Invalid type';
    exit;
}

$filename = $type . '_' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$output = fopen('php://output', 'w');

fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM for UTF-8

try {
    switch ($type) {
        case 'servers':
            fputcsv($output, ['ID', 'Nombre', 'Proveedor', 'IP', 'Notas', 'Creado']);
            $stmt = $pdo->query("SELECT id, name, provider, ip_address, notes, created_at FROM servers WHERE deleted_at IS NULL ORDER BY name");
            while ($row = $stmt->fetch()) {
                fputcsv($output, [$row['id'], $row['name'], $row['provider'], $row['ip_address'], $row['notes'], $row['created_at']]);
            }
            break;

        case 'credentials':
            fputcsv($output, ['ID', 'Servicio', 'Tipo', 'URL', 'Usuario', 'Contraseña', 'Email Asociado', 'Pago', 'Notas', 'Servidor', 'Creado']);
            $stmt = $pdo->query("SELECT c.id, c.service_name, c.type, c.url, c.username, c.password, c.associated_email, c.payment_date, c.notes, s.name as server_name, c.created_at FROM credentials c JOIN servers s ON c.server_id = s.id WHERE c.deleted_at IS NULL ORDER BY c.service_name");
            while ($row = $stmt->fetch()) {
                $row['password'] = decryptData($row['password']);
                fputcsv($output, [$row['id'], $row['service_name'], $row['type'], $row['url'], $row['username'], $row['password'], $row['associated_email'], $row['payment_date'], $row['notes'], $row['server_name'], $row['created_at']]);
            }
            break;

        case 'anydesk':
            fputcsv($output, ['ID', 'Nombre PC', 'IP/ID', 'Password AnyDesk', 'Gmail Email', 'Password Gmail', 'NordVPN', 'Password NordVPN', 'Nord IP', 'AnyDesk Email', 'Password AnyDesk Email', 'Notas', 'Creado']);
            $stmt = $pdo->query("SELECT id, name, ip_address, password, gmail_email, gmail_password, nordvpn, nordvpn_password, nord_dedicada_ip, anydesk_email, anydesk_email_password, notes, created_at FROM anydesk_accounts WHERE deleted_at IS NULL ORDER BY name");
            while ($row = $stmt->fetch()) {
                $row['password'] = decryptData($row['password']);
                $row['gmail_password'] = decryptData($row['gmail_password']);
                $row['nordvpn_password'] = decryptData($row['nordvpn_password']);
                $row['anydesk_email_password'] = decryptData($row['anydesk_email_password']);
                fputcsv($output, [$row['id'], $row['name'], $row['ip_address'], $row['password'], $row['gmail_email'], $row['gmail_password'], $row['nordvpn'], $row['nordvpn_password'], $row['nord_dedicada_ip'], $row['anydesk_email'], $row['anydesk_email_password'], $row['notes'], $row['created_at']]);
            }
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
}

fclose($output);
?>