<?php
session_start();

require 'config.php';
require_once 'includes/audit.php';

$userId = $_SESSION['user_id'] ?? null;
$userName = $_SESSION['user_name'] ?? 'Desconocido';

if ($userId) {
    logAction($pdo, 'logout', 'sesion', $userId, "Cierre de sesión - {$userName}");
}

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

header("Location: login.php");
exit;
?>