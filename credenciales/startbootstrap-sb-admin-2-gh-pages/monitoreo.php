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

$isAdmin = ($_SESSION['user_role'] === 'Administrador');

if (!userCan('monitoreo', 'ver')) {
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
    <title>Monitoreo - Gestor de Credenciales</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/custom/dark-mode.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="vendor/jquery/jquery.min.js"></script>
    <style>
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 6px;
        }
        .status-online { background-color: #1cc88a; }
        .status-offline { background-color: #e74a3b; }
        .status-unknown { background-color: #858796; }
        .status-pulse {
            animation: pulse 1.5s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
        .port-badge {
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 12px;
            margin-right: 4px;
            display: inline-block;
        }
        .port-open { background-color: #d4edda; color: #155724; }
        .port-closed { background-color: #f8d7da; color: #721c24; }
        .last-checked {
            font-size: 0.8rem;
            color: #858796;
        }
    </style>
</head>

<body id="page-top">

    <div id="wrapper">

<?php include 'includes/sidebar.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">

            <div id="content">

<?php include 'includes/topbar.php'; ?>

                <div class="container-fluid">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-heartbeat text-primary mr-2"></i>Monitoreo de Servidores
                        </h1>
                        <div>
                            <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" id="btnRefrescar">
                                <i class="fas fa-sync fa-sm text-white-50"></i> Verificar Todo
                            </button>
                        </div>
                    </div>

                    <div class="row" id="summaryRow">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Servidores</div><div class="h5 mb-0 font-weight-bold text-gray-800" id="sumTotal">-</div></div><div class="col-auto"><i class="fas fa-server fa-2x text-gray-300"></i></div></div></div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-success text-uppercase mb-1">Online</div><div class="h5 mb-0 font-weight-bold text-gray-800" id="sumOnline">-</div></div><div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div></div></div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Offline</div><div class="h5 mb-0 font-weight-bold text-gray-800" id="sumOffline">-</div></div><div class="col-auto"><i class="fas fa-times-circle fa-2x text-gray-300"></i></div></div></div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-secondary shadow h-100 py-2">
                                <div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Sin IP</div><div class="h5 mb-0 font-weight-bold text-gray-800" id="sumUnknown">-</div></div><div class="col-auto"><i class="fas fa-question-circle fa-2x text-gray-300"></i></div></div></div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-list mr-1"></i> Estado de Servidores</h6>
                            <span class="last-checked" id="lastCheckedLabel">Última verificación: --</span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="monitoreoTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Servidor</th>
                                            <th>IP</th>
                                            <th>Estado</th>
                                            <th>Puertos</th>
                                            <th>Tiempo Resp.</th>
                                        </tr>
                                    </thead>
                                    <tbody id="monitoreoBody">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">
                                                <i class="fas fa-spinner fa-spin mr-1"></i> Verificando servidores...
                                            </td>
                                        </tr>
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

    <script src="js/monitoreo.js"></script>

</body>

</html>
