<?php
require_once '../includes/security.php';
require_once '../includes/permissions.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$accionDoc = !empty($_POST['id']) ? 'editar' : 'crear';
if (!userCan('documentos', $accionDoc)) {
    echo json_encode(['success' => false, 'error' => 'No tienes permisos para realizar esta acción.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

requireCsrfToken();

require '../config.php';
require_once '../includes/audit.php';

$id = $_POST['id'] ?? null;
$name = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';

if (empty($name)) {
    echo json_encode(['success' => false, 'error' => 'El nombre es obligatorio.']);
    exit;
}

$uploadDir = '../uploads/documentos/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

try {
    if (empty($id)) {
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => 'Debes seleccionar un archivo.']);
            exit;
        }

        $file = $_FILES['file'];

        // Validate file
        $uploadErrors = validateUploadedFile($file);
        if (!empty($uploadErrors)) {
            echo json_encode(['success' => false, 'error' => implode(' ', $uploadErrors)]);
            exit;
        }

        $originalName = basename($file['name']);
        $fileType = $file['type'];
        $fileSize = $file['size'];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $uniqueName = uniqid('doc_', true) . '.' . $ext;
        $filePath = $uploadDir . $uniqueName;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            echo json_encode(['success' => false, 'error' => 'Error al guardar el archivo.']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO documents (name, description, file_name, file_path, file_type, file_size, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $originalName, $filePath, $fileType, $fileSize, $_SESSION['user_id']]);
        logAction($pdo, 'subir', 'documento', $pdo->lastInsertId(), "Documento '$name' subido");
        echo json_encode(['success' => true]);
    } else {
        $stmt = $pdo->prepare("SELECT file_path FROM documents WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $doc = $stmt->fetch();

        if (!$doc) {
            echo json_encode(['success' => false, 'error' => 'Documento no encontrado.']);
            exit;
        }

        $filePath = $doc['file_path'];
        $originalName = $doc['file_name'];
        $fileType = $doc['file_type'];
        $fileSize = $doc['file_size'];

        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['file'];

            // Validate file
            $uploadErrors = validateUploadedFile($file);
            if (!empty($uploadErrors)) {
                echo json_encode(['success' => false, 'error' => implode(' ', $uploadErrors)]);
                exit;
            }

            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $originalName = basename($file['name']);
            $fileType = $file['type'];
            $fileSize = $file['size'];
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $uniqueName = uniqid('doc_', true) . '.' . $ext;
            $filePath = $uploadDir . $uniqueName;
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                echo json_encode(['success' => false, 'error' => 'Error al guardar el archivo.']);
                exit;
            }
        }

        $stmt = $pdo->prepare("UPDATE documents SET name = ?, description = ?, file_name = ?, file_path = ?, file_type = ?, file_size = ? WHERE id = ?");
        $stmt->execute([$name, $description, $originalName, $filePath, $fileType, $fileSize, $id]);
        logAction($pdo, 'editar', 'documento', $id, "Documento '$name' editado");
        echo json_encode(['success' => true]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error de base de datos']);
}
?>