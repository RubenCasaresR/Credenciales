<?php
session_start();
require_once 'config.php';
require_once 'includes/intranet_sso.php';

// Puente SSO Fase 1: ?sso=token desde la intranet (pestaña nueva)
credencialesTryIntranetSSO($pdo);
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'includes/security.php';
sendSecurityHeaders();
checkSessionTimeout();
generateCsrfToken();

$isAdmin = ($_SESSION['user_role'] ?? '') === 'Administrador';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Credenciales - Dashboard</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/custom/dark-mode.css" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">
    <script src="vendor/jquery/jquery.min.js"></script>
</head>

<body id="page-top">

    <div id="wrapper">

<?php include 'includes/sidebar.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">

            <div id="content">

<?php include 'includes/topbar.php'; ?>

                <div class="container-fluid">

<?php if ($isAdmin): ?>

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                        <div>
                            <a href="api/export_csv.php?type=credentials" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm mr-2">
                                <i class="fas fa-download fa-sm text-white-50"></i> Exportar Credenciales
                            </a>
                            <a href="api/export_csv.php?type=servers" class="d-none d-sm-inline-block btn btn-sm btn-info shadow-sm mr-2">
                                <i class="fas fa-download fa-sm text-white-50"></i> Exportar Servidores
                            </a>
                            <a href="api/export_csv.php?type=anydesk" class="d-none d-sm-inline-block btn btn-sm btn-warning shadow-sm">
                                <i class="fas fa-download fa-sm text-white-50"></i> Exportar AnyDesk
                            </a>
                        </div>
                    </div>

                    <div class="row" id="statsRow">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Servidores</div><div class="h5 mb-0 font-weight-bold text-gray-800" id="statServers">-</div></div><div class="col-auto"><i class="fas fa-server fa-2x text-gray-300"></i></div></div></div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-success text-uppercase mb-1">Credenciales</div><div class="h5 mb-0 font-weight-bold text-gray-800" id="statCredentials">-</div></div><div class="col-auto"><i class="fas fa-key fa-2x text-gray-300"></i></div></div></div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-info text-uppercase mb-1">AnyDesk</div><div class="h5 mb-0 font-weight-bold text-gray-800" id="statAnydesk">-</div></div><div class="col-auto"><i class="fas fa-desktop fa-2x text-gray-300"></i></div></div></div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Documentos</div><div class="h5 mb-0 font-weight-bold text-gray-800" id="statDocuments">-</div></div><div class="col-auto"><i class="fas fa-file fa-2x text-gray-300"></i></div></div></div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-secondary shadow h-100 py-2">
                                <div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Usuarios</div><div class="h5 mb-0 font-weight-bold text-gray-800" id="statUsers">-</div></div><div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div></div></div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Pasarelas Pago</div><div class="h5 mb-0 font-weight-bold text-gray-800" id="statPaymentGateways">-</div></div><div class="col-auto"><i class="fas fa-credit-card fa-2x text-gray-300"></i></div></div></div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-dark shadow h-100 py-2">
                                <div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Sist. Internos</div><div class="h5 mb-0 font-weight-bold text-gray-800" id="statInternalSystems">-</div></div><div class="col-auto"><i class="fas fa-cogs fa-2x text-gray-300"></i></div></div></div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-info text-uppercase mb-1">Internet</div><div class="h5 mb-0 font-weight-bold text-gray-800" id="statInternet">-</div></div><div class="col-auto"><i class="fas fa-wifi fa-2x text-gray-300"></i></div></div></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-danger"><i class="fas fa-bell mr-1"></i> Próximos Vencimientos</h6>
                                    <a href="credenciales.php" class="btn btn-sm btn-danger">Ver todas</a>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm" id="expiringTable">
                                            <thead>
                                                <tr>
                                                    <th>Servicio</th>
                                                    <th>Tipo</th>
                                                    <th>Servidor</th>
                                                    <th>Fecha Pago</th>
                                                    <th>Estado</th>
                                                    <th>Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody id="expiringBody">
                                                <tr><td colspan="6" class="text-center text-muted">Cargando...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-clock mr-1"></i> Actividad Reciente</h6>
                                    <a href="bitacora.php" class="btn btn-sm btn-primary">Ver todas</a>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-borderless" id="recentActivityTable">
                                            <tbody id="recentActivityBody">
                                                <tr><td colspan="3" class="text-center text-muted">Cargando...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-server mr-1"></i> Top Servidores con más Credenciales</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm" id="topServersTable">
                                            <thead>
                                                <tr><th>#</th><th>Servidor</th><th class="text-center">Credenciales</th></tr>
                                            </thead>
                                            <tbody id="topServersBody">
                                                <tr><td colspan="3" class="text-center text-muted">Cargando...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-warning"><i class="fas fa-tag mr-1"></i> Distribución por Tipo de Credencial</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm" id="credTypesTable">
                                            <thead>
                                                <tr><th>Tipo</th><th class="text-center">Cantidad</th></tr>
                                            </thead>
                                            <tbody id="credTypesBody">
                                                <tr><td colspan="2" class="text-center text-muted">Cargando...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-secondary"><i class="fas fa-user-plus mr-1"></i> Últimos Usuarios Registrados</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm" id="recentUsersTable">
                                            <thead>
                                                <tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Fecha</th></tr>
                                            </thead>
                                            <tbody id="recentUsersBody">
                                                <tr><td colspan="4" class="text-center text-muted">Cargando...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

<?php else: ?>

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-home mr-2"></i> Bienvenido, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuario') ?>
                        </h1>
                        <span class="badge badge-info py-2 px-3">
                            <i class="fas fa-user mr-1"></i> Usuario
                        </span>
                    </div>

                    <div class="row" id="userStatsRow">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-info text-uppercase mb-1">Internet</div><div class="h5 mb-0 font-weight-bold text-gray-800" id="statUserInternet">-</div></div><div class="col-auto"><i class="fas fa-wifi fa-2x text-gray-300"></i></div></div></div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-info text-uppercase mb-1">AnyDesk</div><div class="h5 mb-0 font-weight-bold text-gray-800" id="statUserAnydesk">-</div></div><div class="col-auto"><i class="fas fa-desktop fa-2x text-gray-300"></i></div></div></div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-info text-uppercase mb-1">Documentos</div><div class="h5 mb-0 font-weight-bold text-gray-800" id="statUserDocuments">-</div></div><div class="col-auto"><i class="fas fa-file fa-2x text-gray-300"></i></div></div></div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-info text-uppercase mb-1">Sist. Internos</div><div class="h5 mb-0 font-weight-bold text-gray-800" id="statUserInternalSystems">-</div></div><div class="col-auto"><i class="fas fa-cogs fa-2x text-gray-300"></i></div></div></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-info"><i class="fas fa-wifi mr-1"></i> Conexiones de Internet</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm" id="userInternetTable">
                                            <thead>
                                                <tr><th>Nombre</th><th>Proveedor</th><th>IP</th><th>Creado</th></tr>
                                            </thead>
                                            <tbody id="userInternetBody">
                                                <tr><td colspan="4" class="text-center text-muted">Cargando...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-info"><i class="fas fa-desktop mr-1"></i> AnyDesk</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm" id="userAnydeskTable">
                                            <thead>
                                                <tr><th>Nombre</th><th>IP</th><th>Creado</th></tr>
                                            </thead>
                                            <tbody id="userAnydeskBody">
                                                <tr><td colspan="3" class="text-center text-muted">Cargando...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-info"><i class="fas fa-file mr-1"></i> Documentos Recientes</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm" id="userDocumentsTable">
                                            <thead>
                                                <tr><th>Nombre</th><th>Tipo</th><th>Tamaño</th><th>Creado</th></tr>
                                            </thead>
                                            <tbody id="userDocumentsBody">
                                                <tr><td colspan="4" class="text-center text-muted">Cargando...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-info"><i class="fas fa-cogs mr-1"></i> Sistemas Internos</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm" id="userInternalSystemsTable">
                                            <thead>
                                                <tr><th>Nombre</th><th>Dominio</th><th>Creado</th></tr>
                                            </thead>
                                            <tbody id="userInternalSystemsBody">
                                                <tr><td colspan="3" class="text-center text-muted">Cargando...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

<?php endif; ?>

                </div>

            </div>

<?php include 'includes/footer.php'; ?>

<?php if ($isAdmin): ?>

    <script>
        $(document).ready(function() {
            function escHtml(str) {
                return $('<span>').text(str || '').html();
            }

            $.getJSON('api/get_dashboard_stats.php', function(r) {
                if (r.success) {
                    $('#statServers').text(r.data.servers);
                    $('#statCredentials').text(r.data.credentials);
                    $('#statAnydesk').text(r.data.anydesk);
                    $('#statDocuments').text(r.data.documents);
                    $('#statUsers').text(r.data.users);
                    $('#statPaymentGateways').text(r.data.paymentGateways);
                    $('#statInternalSystems').text(r.data.internalSystems);
                    $('#statInternet').text(r.data.internetNetworks);

                    if (r.data.topServers && r.data.topServers.length) {
                        var html = '';
                        r.data.topServers.forEach(function(s, i) {
                            html += '<tr><td>' + (i+1) + '</td><td><a href="servidor_detalle.php?id=' + s.id + '">' + escHtml(s.name) + '</a></td><td class="text-center"><span class="badge badge-primary">' + s.total + '</span></td></tr>';
                        });
                        $('#topServersBody').html(html);
                    }

                    if (r.data.credTypes && r.data.credTypes.length) {
                        var html = '';
                        r.data.credTypes.forEach(function(t) {
                            html += '<tr><td><span class="badge badge-secondary">' + escHtml(t.type) + '</span></td><td class="text-center">' + t.total + '</td></tr>';
                        });
                        $('#credTypesBody').html(html);
                    }

                    if (r.data.recentUsers && r.data.recentUsers.length) {
                        var html = '';
                        r.data.recentUsers.forEach(function(u) {
                            var roleBadge = u.role === 'Administrador' ? 'badge-danger' : 'badge-info';
                            html += '<tr><td>' + escHtml(u.name) + '</td><td>' + escHtml(u.email) + '</td><td><span class="badge ' + roleBadge + '">' + u.role + '</span></td><td>' + u.created_at + '</td></tr>';
                        });
                        $('#recentUsersBody').html(html);
                    }
                }
            });

            function cargarVencimientos() {
                $.getJSON('api/get_notifications.php', function(r) {
                    if (!r.success) return;
                    var expired = r.data.expired || [];
                    var soon = r.data.soon || [];
                    var all = expired.concat(soon);
                    var tbody = $('#expiringBody');

                    if (!all.length) {
                        tbody.html('<tr><td colspan="6" class="text-center text-muted">No hay servicios próximos a vencer.</td></tr>');
                        return;
                    }

                    var html = '';
                    all.forEach(function(c) {
                        var hoy = new Date();
                        var venc = new Date(c.payment_date);
                        var diff = Math.ceil((venc - hoy) / (1000*60*60*24));
                        var estado, badgeClass;
                        if (diff < 0) {
                            estado = 'Vencido hace ' + Math.abs(diff) + ' día(s)';
                            badgeClass = 'badge-danger';
                        } else if (diff <= 5) {
                            estado = 'Vence en ' + diff + ' día(s)';
                            badgeClass = 'badge-warning text-white';
                        } else {
                            estado = 'Vence en ' + diff + ' día(s)';
                            badgeClass = 'badge-info';
                        }

                        html += '<tr>' +
                            '<td>' + escHtml(c.service_name) + '</td>' +
                            '<td><span class="badge badge-secondary">' + escHtml(c.type) + '</span></td>' +
                            '<td>' + escHtml(c.server_name || '-') + '</td>' +
                            '<td>' + escHtml(c.payment_date) + '</td>' +
                            '<td><span class="badge ' + badgeClass + '">' + estado + '</span></td>' +
                            '<td><a href="servidor_detalle.php?id=' + c.server_id + '&credential=' + c.id + '" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye mr-1"></i>Ver</a></td>' +
                        '</tr>';
                    });
                    tbody.html(html);
                });
            }

            $.getJSON('api/get_recent_activity.php', function(r) {
                if (r.success && r.data.length) {
                    var html = '';
                    r.data.forEach(function(log) {
                        var icon = 'fa-circle';
                        var color = 'text-primary';
                        if (log.action === 'crear' || log.action === 'subir') { icon = 'fa-plus-circle'; color = 'text-success'; }
                        else if (log.action === 'editar') { icon = 'fa-edit'; color = 'text-primary'; }
                        else if (log.action === 'eliminar') { icon = 'fa-trash'; color = 'text-danger'; }
                        html += '<tr><td class="pl-0"><i class="fas ' + icon + ' ' + color + ' fa-fw mr-1"></i> <strong>' + escHtml(log.user_name) + '</strong></td><td>' + escHtml(log.details) + '</td><td class="text-right text-muted small">' + escHtml(log.created_at_formatted) + '</td></tr>';
                    });
                    $('#recentActivityBody').html(html);
                } else {
                    $('#recentActivityBody').html('<tr><td colspan="3" class="text-center text-muted">Sin actividad reciente.</td></tr>');
                }
            });

            cargarVencimientos();
        });
    </script>

<?php else: ?>

    <script>
        $(document).ready(function() {
            function escHtml(str) {
                return $('<span>').text(str || '').html();
            }

            $.getJSON('api/get_user_dashboard.php', function(r) {
                if (!r.success) return;

                var d = r.data;
                var counts = d.counts || {};

                $('#statUserInternet').text(counts.internet || 0);
                $('#statUserAnydesk').text(counts.anydesk || 0);
                $('#statUserDocuments').text(counts.documents || 0);
                $('#statUserInternalSystems').text(counts.internalSystems || 0);

                if (d.internet && d.internet.length) {
                    var html = '';
                    d.internet.forEach(function(item) {
                        html += '<tr><td>' + escHtml(item.name) + '</td><td>' + escHtml(item.provider || '-') + '</td><td>' + escHtml(item.ip_address || '-') + '</td><td class="small">' + escHtml(item.created_at) + '</td></tr>';
                    });
                    $('#userInternetBody').html(html);
                }

                if (d.anydesk && d.anydesk.length) {
                    var html = '';
                    d.anydesk.forEach(function(item) {
                        html += '<tr><td>' + escHtml(item.name) + '</td><td>' + escHtml(item.ip_address || '-') + '</td><td class="small">' + escHtml(item.created_at) + '</td></tr>';
                    });
                    $('#userAnydeskBody').html(html);
                }

                if (d.documents && d.documents.length) {
                    var html = '';
                    d.documents.forEach(function(item) {
                        var size = item.file_size ? (item.file_size / 1024).toFixed(1) + ' KB' : '-';
                        html += '<tr><td>' + escHtml(item.name) + '</td><td>' + escHtml(item.file_type || '-') + '</td><td>' + size + '</td><td class="small">' + escHtml(item.created_at) + '</td></tr>';
                    });
                    $('#userDocumentsBody').html(html);
                }

                if (d.internalSystems && d.internalSystems.length) {
                    var html = '';
                    d.internalSystems.forEach(function(item) {
                        html += '<tr><td>' + escHtml(item.name) + '</td><td>' + escHtml(item.domain || '-') + '</td><td class="small">' + escHtml(item.created_at) + '</td></tr>';
                    });
                    $('#userInternalSystemsBody').html(html);
                }
            });
        });
    </script>

<?php endif; ?>

</body>
</html>