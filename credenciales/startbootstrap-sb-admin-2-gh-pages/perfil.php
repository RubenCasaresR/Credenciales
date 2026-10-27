<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'includes/security.php';
sendSecurityHeaders();
checkSessionTimeout();

require 'config.php';

// Fetch current user data
try {
    $stmt = $pdo->prepare('SELECT name, email, role FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    $user = ['name' => 'Unknown', 'email' => 'Unknown', 'role' => 'Unknown'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Credenciales - Perfil</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/custom/dark-mode.css" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">
    <script src="vendor/jquery/jquery.min.js"></script>
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

<?php include 'includes/sidebar.php'; ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

<?php include 'includes/topbar.php'; ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Mi Perfil</h1>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <!-- Basic Card Example -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Información Personal</h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>Nombre:</strong> <?= htmlspecialchars($user['name']) ?></p>
                                    <p><strong>Correo Electrónico:</strong> <?= htmlspecialchars($user['email']) ?></p>
                                    <p><strong>Rol:</strong> <span class="badge badge-info"><?= htmlspecialchars($user['role']) ?></span></p>
                                    <hr>
                                    <p class="mb-0 text-muted small">Más opciones de perfil estarán disponibles pronto.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

<?php include 'includes/footer.php'; ?>

</body>
</html>
