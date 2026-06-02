<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'includes/security.php';
require_once 'includes/permissions.php';
sendSecurityHeaders();
checkSessionTimeout();

if (!userCan('bitacora', 'ver')) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>BitÃ¡cora - Gestor de Credenciales</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/custom/dark-mode.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="vendor/jquery/jquery.min.js"></script>
</head>

<body id="page-top">

    <div id="wrapper">

<?php include 'includes/sidebar.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">

            <div id="content">

<?php include 'includes/topbar.php'; ?>

                <div class="container-fluid">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-history text-primary mr-2"></i> BitÃ¡cora de Actividades</h1>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Registro de Actividades</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="bitacoraTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Usuario</th>
                                            <th>Acción</th>
                                            <th>Entidad</th>
                                            <th>Detalles</th>
                                            <th>IP</th>
                                            <th>Dispositivo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

<?php include 'includes/footer.php'; ?>

    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    $('#bitacoraTable').DataTable({
        "ajax": "api/get_audit_logs.php",
        "columns": [
            { "data": "created_at_formatted" },
            { "data": "user_name" },
            { "data": "action", "render": function(d) {
                var map = { 'crear': '<span class="badge badge-success">Crear</span>', 'editar': '<span class="badge badge-primary">Editar</span>', 'eliminar': '<span class="badge badge-danger">Eliminar</span>', 'subir': '<span class="badge badge-info">Subir</span>', 'login': '<span class="badge badge-success">Login</span>', 'logout': '<span class="badge badge-secondary">Logout</span>', 'ver': '<span class="badge badge-secondary">Ver</span>' };
                return map[d] || '<span class="badge badge-light">' + d + '</span>';
            }},
            { "data": "entity_type", "render": function(d) { return '<span class="text-capitalize">' + d + '</span>'; } },
            { "data": "details" },
            { "data": "ip_address" },
            { "data": "device" }
        ],
        "order": [[0, "desc"]],
        "pageLength": 25,
        "language": { "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json" }
    });
});
</script>

</body>
</html>
