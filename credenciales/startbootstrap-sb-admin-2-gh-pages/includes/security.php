<?php
/**
 * Central security helpers: CSRF, session timeout, password policy, rate limiting
 */

// ─── CSRF Protection ───────────────────────────────────────────────
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfTokenField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCsrfToken() . '">';
}

function validateCsrfToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) return false;
    return hash_equals($_SESSION['csrf_token'], $token);
}

function requireCsrfToken() {
    $token = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($token)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'CSRF token inválido. Recarga la página e intenta de nuevo.']);
        exit;
    }
}

// ─── Session Timeout ──────────────────────────────────────────────
function checkSessionTimeout($timeoutMinutes = 30) {
    if (!isset($_SESSION['user_id'])) return;
    
    $timeout = $timeoutMinutes * 60;
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        $_SESSION = [];
        session_destroy();
        if (!headers_sent()) {
            header('Location: login.php?expired=1');
            exit;
        }
    }
    $_SESSION['last_activity'] = time();
}

// ─── Password Policy ──────────────────────────────────────────────
function validatePassword($password) {
    $errors = [];
    if (strlen($password) < 8) {
        $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Debe contener al menos una mayúscula.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Debe contener al menos una minúscula.';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Debe contener al menos un número.';
    }
    return $errors;
}

// ─── Rate Limiting ────────────────────────────────────────────────
function checkRateLimit($pdo, $identifier, $maxAttempts = 5, $windowMinutes = 15) {
    $window = date('Y-m-d H:i:s', strtotime("-{$windowMinutes} minutes"));
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM audit_log WHERE details = ? AND created_at >= ?");
    $type = 'login_fail_' . $identifier;
    $stmt->execute([$type, $window]);
    $attempts = $stmt->fetchColumn();
    
    if ($attempts >= $maxAttempts) {
        return false;
    }
    return true;
}

function logLoginAttempt($pdo, $identifier) {
    $stmt = $pdo->prepare("INSERT INTO audit_log (user_id, user_name, action, entity_type, entity_id, details, ip_address, user_agent, created_at) VALUES (NULL, 'Sistema', 'login_fail', 'sesion', NULL, ?, ?, '', NOW())");
    $stmt->execute(['login_fail_' . $identifier, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
}

// ─── Security Headers ──────────────────────────────────────────────
function sendSecurityHeaders() {
    if (headers_sent()) return;
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// ─── File Upload Validation ───────────────────────────────────────
function validateUploadedFile($file) {
    $errors = [];
    $maxSize = 50 * 1024 * 1024;
    $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'zip', 'rar', 'csv'];

    if ($file['size'] > $maxSize) {
        $errors[] = 'El archivo excede el tamaño máximo de 50MB.';
        return $errors;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExtensions)) {
        $errors[] = "Extensión '.$ext' no permitida. Extensiones permitidas: " . implode(', ', $allowedExtensions);
        return $errors;
    }

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = [
            'application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg', 'image/png', 'image/gif',
            'text/plain', 'application/zip', 'application/x-rar-compressed',
            'text/csv', 'application/csv'
        ];

        if (!in_array($mime, $allowedMimes)) {
            $errors[] = "Tipo de archivo '$mime' no permitido.";
        }
    }

    return $errors;
}
?>