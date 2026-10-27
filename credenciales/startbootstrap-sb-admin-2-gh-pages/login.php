<?php
require_once 'includes/security.php';
session_start();
require 'config.php';
sendSecurityHeaders();
require_once 'includes/intranet_sso.php';

// Puente SSO Fase 1: si viene de la intranet (?sso=token), entra sin password
if (credencialesTryIntranetSSO($pdo) && !empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF check
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Sesión expirada. Recarga la página.';
    } else {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!empty($email) && !empty($password)) {
            try {
                // Rate limiting: max 5 attempts per 15 min per IP
                $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                if (!checkRateLimit($pdo, $ip)) {
                    $error = 'Demasiados intentos. Intenta de nuevo en 15 minutos.';
                } else {
                    $stmt = $pdo->prepare('SELECT id, name, role, password FROM users WHERE email = ?');
                    $stmt->execute([$email]);
                    $user = $stmt->fetch();

                    if ($user && password_verify($password, $user['password'])) {
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_role'] = $user['role'];
                        $_SESSION['last_activity'] = time();
                        require_once 'includes/permissions.php';
                        loadUserPermissions($pdo, $user['id']);
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                        require_once 'includes/audit.php';
                        logAction($pdo, 'login', 'sesion', $user['id'], "Inicio de sesión - {$user['name']} ({$user['role']})");

                        header("Location: index.php");
                        exit;
                    } else {
                        logLoginAttempt($pdo, $ip);
                        $error = 'Email o contraseña incorrectos.';
                    }
                }
            } catch (PDOException $e) {
                $error = 'Ocurrió un error. Intenta de nuevo.';
            }
        } else {
            $error = 'Ingresa email y contraseña.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>SB Admin 2 - Login</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">

</head>

<body class="bg-gradient-primary">

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                                        <?php if (!empty($error)): ?>
                                            <div class="alert alert-danger" role="alert">
                                                <?= htmlspecialchars($error) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <form class="user" method="POST" action="login.php">
                                        <?= csrfTokenField() ?>
                                        <div class="form-group">
                                            <input type="email" class="form-control form-control-user"
                                                id="exampleInputEmail" name="email" aria-describedby="emailHelp"
                                                placeholder="Enter Email Address..." required>
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user"
                                                id="exampleInputPassword" name="password" placeholder="Password" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            Iniciar Sesión
                                        </button>
                                    </form>
                                    <hr>
                                    <div class="text-center">
                                        <a class="small" href="forgot-password.html">Forgot Password?</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

</body>

</html>