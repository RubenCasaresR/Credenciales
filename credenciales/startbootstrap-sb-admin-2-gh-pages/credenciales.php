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
generateCsrfToken();

$isAdmin = ($_SESSION['user_role'] === 'Administrador');

if (!userCan('servidores', 'ver')) {
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
    <title>Servidores - Gestor de Credenciales</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/custom/dark-mode.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
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
                        <div class="d-flex align-items-center mb-2 mb-sm-0">
                            <h1 class="h3 mb-0 text-gray-800 mr-3">Servidores y Proyectos</h1>
                            <div class="input-group input-group-sm" style="max-width:200px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-sort"></i></span>
                                </div>
                                <select class="form-control" id="sortServers">
                                    <option value="date_desc" selected>Más recientes</option>
                                    <option value="date_asc">Más antiguos</option>
                                    <option value="name_asc">Nombre A-Z</option>
                                    <option value="name_desc">Nombre Z-A</option>
                                    <option value="provider_asc">Proveedor A-Z</option>
                                    <option value="provider_desc">Proveedor Z-A</option>
                                    <option value="ip">Dirección IP</option>
                                </select>
                            </div>
                        </div>
                        <?php if ($isAdmin): ?>
                        <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" id="btnNuevoServidor">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Nuevo Servidor
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Servers Grid -->
                    <div class="row" id="serversGrid">
                        <!-- Cargado por JS -->
                    </div>

                    <?php if ($isAdmin): ?>
                    <!-- Modal Crear/Editar Servidor -->
                    <div class="modal fade" id="editarServidorModal" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <form id="formServidor">
                              <div class="modal-header">
                                <h5 class="modal-title" id="tituloModalServidor">Servidor</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                              <div class="modal-body">
                                <input type="hidden" id="edit_server_id" name="id">
                                <?= csrfTokenField() ?>
                                
                                <div class="form-group">
                                    <label>Nombre del Proyecto / Servidor *</label>
                                    <input type="text" class="form-control" id="edit_server_name" name="name" required placeholder="Ej. Cliente XYZ">
                                </div>
                                <div class="form-group">
                                    <label>Proveedor</label>
                                    <input type="text" class="form-control" id="edit_server_provider" name="provider" placeholder="Ej. Neubox, AWS, HostGator">
                                </div>
                                <div class="form-group">
                                    <label>Dirección IP</label>
                                    <input type="text" class="form-control" id="edit_server_ip" name="ip_address" placeholder="Ej. 192.168.1.1">
                                </div>
                                <div class="form-group">
                                    <label>Notas</label>
                                    <textarea class="form-control" id="edit_server_notes" name="notes" rows="3"></textarea>
                                </div>
                                
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Guardar</button>
                              </div>
                          </form>
                        </div>
                      </div>
                    </div>
                    <?php endif; ?>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

<?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Pasa el rol de usuario al JS para ocultar/mostrar botones -->
    <script>
        window.USER_ROLE = "<?= htmlspecialchars($_SESSION['user_role']) ?>";
        window.PERMISOS = <?= json_encode($_SESSION['user_permissions'] ?? []) ?>;
        window.CSRF_TOKEN = "<?= htmlspecialchars($_SESSION['csrf_token']) ?>";
    </script>

    <!-- Page level custom scripts -->
    <script src="js/credenciales.js?v=1"></script>

</body>

</html>
