<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require '../config.php';
require_once '../includes/permissions.php';

if (!userCan('monitoreo', 'ver')) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

function checkPort($ip, $port, $timeout = 3) {
    $start = microtime(true);
    $socket = @fsockopen($ip, $port, $errno, $errstr, $timeout);
    $elapsed = round((microtime(true) - $start) * 1000);
    if ($socket) {
        fclose($socket);
        return ['open' => true, 'time_ms' => $elapsed];
    }
    return ['open' => false, 'time_ms' => null];
}

try {
    $servers = $pdo->query("SELECT id, name, ip_address FROM servers WHERE deleted_at IS NULL ORDER BY name ASC")->fetchAll();

    $results = [];

    foreach ($servers as $server) {
        $ip = $server['ip_address'];
        if (empty($ip)) {
            $results[] = [
                'id' => $server['id'],
                'name' => $server['name'],
                'ip_address' => 'N/A',
                'http' => null,
                'https' => null,
                'status' => 'unknown',
                'error' => 'Sin IP asignada'
            ];
            continue;
        }

        $http = checkPort($ip, 80);
        $https = checkPort($ip, 443);

        $isOnline = ($http['open'] || $https['open']);

        $results[] = [
            'id' => $server['id'],
            'name' => $server['name'],
            'ip_address' => $ip,
            'http' => $http,
            'https' => $https,
            'status' => $isOnline ? 'online' : 'offline',
            'error' => null
        ];
    }

    $online = count(array_filter($results, fn($r) => $r['status'] === 'online'));
    $offline = count(array_filter($results, fn($r) => $r['status'] === 'offline'));
    $unknown = count(array_filter($results, fn($r) => $r['status'] === 'unknown'));

    echo json_encode([
        'success' => true,
        'data' => $results,
        'summary' => [
            'total' => count($results),
            'online' => $online,
            'offline' => $offline,
            'unknown' => $unknown
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
