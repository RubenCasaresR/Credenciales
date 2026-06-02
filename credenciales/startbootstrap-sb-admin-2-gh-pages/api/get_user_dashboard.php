<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require '../config.php';
require_once '../includes/permissions.php';

$userId = $_SESSION['user_id'];
$isAdmin = ($_SESSION['user_role'] === 'Administrador');

if ($isAdmin) {
    echo json_encode(['success' => false, 'error' => 'Not available for administrators']);
    exit;
}

try {
    $internet = $pdo->prepare("SELECT id, name, provider, ip_address, created_at FROM internet_networks WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT 5");
    $internet->execute();
    $internetNetworks = $internet->fetchAll();

    $anydesk = $pdo->prepare("SELECT id, name, ip_address, created_at FROM anydesk_accounts WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT 5");
    $anydesk->execute();
    $anydeskAccounts = $anydesk->fetchAll();

    $docs = $pdo->prepare("SELECT id, name, file_type, file_size, created_at FROM documents WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT 5");
    $docs->execute();
    $documents = $docs->fetchAll();

    $systems = $pdo->prepare("SELECT id, name, domain, created_at FROM internal_systems WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT 5");
    $systems->execute();
    $internalSystems = $systems->fetchAll();

    $countInternet = $pdo->prepare("SELECT COUNT(*) FROM internet_networks WHERE deleted_at IS NULL");
    $countInternet->execute();
    $countAnyDesk = $pdo->prepare("SELECT COUNT(*) FROM anydesk_accounts WHERE deleted_at IS NULL");
    $countAnyDesk->execute();
    $countDocuments = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE deleted_at IS NULL");
    $countDocuments->execute();
    $countInternalSystems = $pdo->prepare("SELECT COUNT(*) FROM internal_systems WHERE deleted_at IS NULL");
    $countInternalSystems->execute();

    echo json_encode([
        'success' => true,
        'data' => [
            'internet' => $internetNetworks,
            'anydesk' => $anydeskAccounts,
            'documents' => $documents,
            'internalSystems' => $internalSystems,
            'counts' => [
                'internet' => (int)$countInternet->fetchColumn(),
                'anydesk' => (int)$countAnyDesk->fetchColumn(),
                'documents' => (int)$countDocuments->fetchColumn(),
                'internalSystems' => (int)$countInternalSystems->fetchColumn(),
            ]
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>