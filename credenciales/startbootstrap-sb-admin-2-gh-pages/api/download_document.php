<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require '../config.php';

$id = $_GET['id'] ?? null;
$forceDownload = isset($_GET['download']) && $_GET['download'] == 1;

if (empty($id)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID requerido']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT name, file_name, file_path, file_type, file_size FROM documents WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$id]);
    $doc = $stmt->fetch();

    if (!$doc) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Documento no encontrado']);
        exit;
    }

    if (!file_exists($doc['file_path'])) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Archivo no encontrado en el servidor']);
        exit;
    }

    header('Content-Type: ' . $doc['file_type']);
    header('Content-Length: ' . $doc['file_size']);

    if ($forceDownload) {
        header('Content-Disposition: attachment; filename="' . $doc['file_name'] . '"');
    } else {
        header('Content-Disposition: inline; filename="' . $doc['file_name'] . '"');
    }

    readfile($doc['file_path']);
    exit;
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error']);
}
?>