<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require '../config.php';

try {
    $stmtExpired = $pdo->prepare("
        SELECT c.id, c.service_name, c.type, c.payment_date,
               s.name AS server_name, s.id AS server_id
        FROM credentials c
        LEFT JOIN servers s ON c.server_id = s.id
        WHERE c.deleted_at IS NULL
          AND c.payment_date IS NOT NULL
          AND c.payment_date < CURDATE()
        ORDER BY c.payment_date ASC
    ");
    $stmtExpired->execute();
    $expired = $stmtExpired->fetchAll();

    $stmtSoon = $pdo->prepare("
        SELECT c.id, c.service_name, c.type, c.payment_date,
               s.name AS server_name, s.id AS server_id
        FROM credentials c
        LEFT JOIN servers s ON c.server_id = s.id
        WHERE c.deleted_at IS NULL
          AND c.payment_date IS NOT NULL
          AND c.payment_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY)
        ORDER BY c.payment_date ASC
    ");
    $stmtSoon->execute();
    $soon = $stmtSoon->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => [
            'total'   => count($expired) + count($soon),
            'expired' => $expired,
            'soon'    => $soon,
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
