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
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Internet - Gestor de Credenciales</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/custom/dark-mode.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
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

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-wifi mr-2"></i>Redes de Internet</h1>
                        <?php if ($isAdmin): ?>
                        <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" id="btnNuevaRed">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Nueva Red
                        </button>
                        <?php endif; ?>
                    </div>

                    <div class="row" id="internetGrid">
                    </div>

                    <?php if ($isAdmin): ?>
                    <div class="modal fade" id="editarRedModal" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                          <form id="formRed">
                              <div class="modal-header">
                                <h5 class="modal-title" id="tituloModalRed">Nueva Red de Internet</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                              <div class="modal-body">
                                <input type="hidden" id="edit_id" name="id">
                                <?= csrfTokenField() ?>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Nombre de la Red *</label>
                                        <input type="text" class="form-control" id="edit_name" name="name" required placeholder="Ej. Oficina Principal">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>SSID</label>
                                        <input type="text" class="form-control" id="edit_ssid" name="ssid" placeholder="Ej. INFINITUM_5G">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Contraseña *</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control password-input" id="edit_password" name="password" placeholder="Contraseña de la red WiFi">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary btn-generar-password" type="button" title="Generar contraseña"><i class="fas fa-random"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Proveedor</label>
                                        <input type="text" class="form-control" id="edit_provider" name="provider" placeholder="Ej. Telmex, Izzi, Totalplay">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>IP del Router</label>
                                        <input type="text" class="form-control" id="edit_ip" name="ip_address" placeholder="Ej. 192.168.1.1">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Notas</label>
                                    <textarea class="form-control" id="edit_notes" name="notes" rows="3" placeholder="Notas adicionales..."></textarea>
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

                    <div class="modal fade" id="verDetallesModal" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="tituloDetalles"><i class="fas fa-wifi text-primary mr-1"></i> Detalles de Red</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body" id="detallesBody">
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                          </div>
                        </div>
                      </div>
                    </div>

                </div>

            </div>

<?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        window.USER_ROLE = "<?= htmlspecialchars($_SESSION['user_role']) ?>";
        window.PERMISOS = <?= json_encode($_SESSION['user_permissions'] ?? []) ?>;
        window.CSRF_TOKEN = "<?= htmlspecialchars($_SESSION['csrf_token']) ?>";
    </script>

    <script src="js/password-generator.js"></script>
    <script src="js/internet.js"></script>

</body>

</html>
