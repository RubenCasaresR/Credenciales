<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require '../config.php';

$search = $_GET['search'] ?? '';

try {
    $params = [];
    $conditions = [];

    if (!empty($search)) {
        $conditions[] = '(c.service_name LIKE ? OR c.domain_name LIKE ? OR c.url LIKE ?)';
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $where = '';
    if (!empty($conditions)) {
        $where = 'AND ' . implode(' AND ', $conditions);
    }

    $sql = "
        SELECT
            CONCAT('cred_', c.id) AS id,
            c.service_name AS name,
            COALESCE(NULLIF(c.domain_name, ''), c.url) AS domain,
            c.url,
            c.notes,
            'Credencial' AS source,
            c.type AS source_type,
            c.created_at
        FROM credentials c
        WHERE (c.type = 'Dominio' OR (c.domain_name IS NOT NULL AND c.domain_name != ''))
          AND c.deleted_at IS NULL
          $where

        UNION ALL

        SELECT
            CONCAT('sys_', s.id) AS id,
            s.name,
            s.domain,
            s.domain AS url,
            s.notes,
            'Sistema Interno' AS source,
            NULL AS source_type,
            s.created_at
        FROM internal_systems s
        WHERE s.domain IS NOT NULL AND s.domain != ''
          AND s.deleted_at IS NULL
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $pages = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $pages]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
