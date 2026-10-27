<?php
// Puente SSO Fase 1: intranet → Credenciales (pestaña nueva, ?sso=token HMAC).
// Replica el login normal: regenerate_id + loadUserPermissions + csrf + audit.
// SEGURIDAD: solo usuarios EXISTENTES (bóveda de secretos, sin auto-alta).
// Si el email intranet no existe aquí → 403 + audit. Login directo intacto.

function credencialesTryIntranetSSO($pdo): bool
{
    if (!empty($_SESSION['user_id'])) return true; // ya logueado local
    $token = $_GET['sso'] ?? '';
    if (!is_string($token) || $token === '') return false;
    // SSO.php vive en la intranet (misma raíz htdocs en local)
    $ssoFile = dirname(__DIR__, 4) . '/interfaz_intranet/src/SSO.php';
    if (!is_file($ssoFile)) return false;
    require_once $ssoFile;
    $u = SSO::verify($token);
    if (!$u) return false;
    try {
        $stmt = $pdo->prepare('SELECT id, name, role FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([strtolower(trim($u['email']))]);
        $user = $stmt->fetch();
        if (!$user) {
            require_once __DIR__ . '/audit.php';
            logAction($pdo, 'sso_denied', 'sesion', 0, 'SSO sin cuenta: ' . $u['email']);
            http_response_code(403);
            echo 'Tu usuario de intranet no tiene cuenta en Credenciales. Pide tu alta a un administrador.';
            exit;
        }
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['last_activity'] = time();
        require_once __DIR__ . '/permissions.php';
        loadUserPermissions($pdo, $user['id']);
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['sso_intranet'] = $u['email'];
        require_once __DIR__ . '/audit.php';
        logAction($pdo, 'sso_login', 'sesion', $user['id'], "SSO intranet - {$user['name']} ({$user['role']})");
        return true;
    } catch (Throwable $e) {
        error_log('SSO credenciales error: ' . $e->getMessage());
        return false;
    }
}
