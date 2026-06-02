<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require '../config.php';

$isAdmin = ($_SESSION['user_role'] === 'Administrador');

try {
    $servers = $pdo->query("SELECT COUNT(*) FROM servers WHERE deleted_at IS NULL")->fetchColumn();
    $credentials = $pdo->query("SELECT COUNT(*) FROM credentials WHERE deleted_at IS NULL")->fetchColumn();
    $anydesk = $pdo->query("SELECT COUNT(*) FROM anydesk_accounts WHERE deleted_at IS NULL")->fetchColumn();
    $documents = $pdo->query("SELECT COUNT(*) FROM documents WHERE deleted_at IS NULL")->fetchColumn();
    $users = $pdo->query("SELECT COUNT(*) FROM users WHERE deleted_at IS NULL")->fetchColumn();
    $paymentGateways = $pdo->query("SELECT COUNT(*) FROM payment_gateways WHERE deleted_at IS NULL")->fetchColumn();
    $internalSystems = $pdo->query("SELECT COUNT(*) FROM internal_systems WHERE deleted_at IS NULL")->fetchColumn();
    $internetNetworks = $pdo->query("SELECT COUNT(*) FROM internet_networks WHERE deleted_at IS NULL")->fetchColumn();
    $bitacora = $pdo->query("SELECT COUNT(*) FROM audit_log")->fetchColumn();

    $result = [
        'success' => true,
        'data' => [
            'servers' => (int)$servers,
            'credentials' => (int)$credentials,
            'anydesk' => (int)$anydesk,
            'documents' => (int)$documents,
            'users' => (int)$users,
            'paymentGateways' => (int)$paymentGateways,
            'internalSystems' => (int)$internalSystems,
            'internetNetworks' => (int)$internetNetworks,
            'bitacora' => (int)$bitacora,
        ]
    ];

    if ($isAdmin) {
        $topServers = $pdo->query("
            SELECT s.id, s.name, COUNT(c.id) as total
            FROM servers s
            LEFT JOIN credentials c ON c.server_id = s.id AND c.deleted_at IS NULL
            WHERE s.deleted_at IS NULL
            GROUP BY s.id, s.name
            ORDER BY total DESC
            LIMIT 5
        ")->fetchAll();

        $credTypes = $pdo->query("
            SELECT type, COUNT(*) as total
            FROM credentials
            WHERE deleted_at IS NULL
            GROUP BY type
            ORDER BY total DESC
        ")->fetchAll();

        $recentUsers = $pdo->query("
            SELECT id, name, email, role, created_at
            FROM users
            WHERE deleted_at IS NULL
            ORDER BY created_at DESC
            LIMIT 5
        ")->fetchAll();

        $result['data']['topServers'] = $topServers;
        $result['data']['credTypes'] = $credTypes;
        $result['data']['recentUsers'] = $recentUsers;
    }

    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>