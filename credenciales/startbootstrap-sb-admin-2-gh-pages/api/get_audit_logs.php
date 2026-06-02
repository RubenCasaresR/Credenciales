<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require '../config.php';
require_once '../includes/permissions.php';

if (!userCan('bitacora', 'ver')) {
    echo json_encode(['success' => true, 'data' => [], 'total' => 0]);
    exit;
}

try {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 30;
    $offset = ($page - 1) * $limit;

    $stmt = $pdo->query("SELECT COUNT(*) FROM audit_log");
    $total = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT al.*, DATE_FORMAT(al.created_at, '%Y-%m-%d %H:%i:%s') as created_at_formatted FROM audit_log al ORDER BY al.created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$limit, $offset]);
    $logs = $stmt->fetchAll();

    // Parse user agent for better display
    foreach ($logs as &$log) {
        $ua = $log['user_agent'] ?? '';
        $log['device'] = parseUserAgent($ua);
    }

    echo json_encode(['success' => true, 'data' => $logs, 'total' => $total, 'page' => $page]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}

function parseUserAgent($ua) {
    if (empty($ua)) return 'Desconocido';

    $browser = 'Desconocido';
    $os = '';

    if (preg_match('/Edg\/([\d.]+)/', $ua)) $browser = 'Edge';
    elseif (preg_match('/OPR\/([\d.]+)/', $ua)) $browser = 'Opera';
    elseif (preg_match('/Firefox\/([\d.]+)/', $ua)) $browser = 'Firefox';
    elseif (preg_match('/Chrome\/([\d.]+)/', $ua)) $browser = 'Chrome';
    elseif (preg_match('/Safari\/([\d.]+)/', $ua)) $browser = 'Safari';

    if (strpos($ua, 'Windows NT 10.0') !== false) $os = 'Windows 10/11';
    elseif (strpos($ua, 'Windows NT 6.3') !== false) $os = 'Windows 8.1';
    elseif (strpos($ua, 'Windows NT 6.1') !== false) $os = 'Windows 7';
    elseif (strpos($ua, 'Mac OS X') !== false) $os = 'macOS';
    elseif (strpos($ua, 'Linux') !== false && strpos($ua, 'Android') !== false) $os = 'Android';
    elseif (strpos($ua, 'Linux') !== false) $os = 'Linux';
    elseif (strpos($ua, 'iPhone') !== false || strpos($ua, 'iPad') !== false) $os = 'iOS';
    elseif (strpos($ua, 'Android') !== false) $os = 'Android';

    $result = $browser;
    if ($os) $result .= " - {$os}";

    return $result;
}
?>