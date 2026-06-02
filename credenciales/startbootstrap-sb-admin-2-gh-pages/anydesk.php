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
    <title>AnyDesk - Gestor de Credenciales</title>

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
                        <h1 class="h3 mb-0 text-gray-800">Cuentas AnyDesk</h1>
                        <?php if ($isAdmin): ?>
                        <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" id="btnNuevaCuenta">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Nueva Cuenta
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- AnyDesk Cards Grid -->
                    <div class="row" id="anydeskGrid">
                        <!-- Cargado por JS -->
                    </div>

                    <?php if ($isAdmin): ?>
                    <!-- Modal Crear/Editar Cuenta -->
                    <div class="modal fade" id="editarCuentaModal" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                          <form id="formCuenta">
                              <div class="modal-header">
                                <h5 class="modal-title" id="tituloModalCuenta">Nueva Cuenta AnyDesk</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                              <div class="modal-body">
                                <input type="hidden" id="edit_account_id" name="id">
                                <?= csrfTokenField() ?>
                                
                                <div class="form-group">
                                    <label>Nombre de la Cuenta *</label>
                                    <input type="text" class="form-control" id="edit_account_name" name="name" required placeholder="Ej. PC Oficina - Juan">
                                </div>
                                <div class="row">
                                    <div class="col-md-4 border-right">
                                        <h6 class="text-primary"><i class="fas fa-desktop"></i> AnyDesk</h6>
                                        <div class="form-group">
                                            <label>IP / ID *</label>
                                            <input type="text" class="form-control" id="edit_account_ip" name="ip_address" required placeholder="Ej. 123 456 789">
                                        </div>
                                        <div class="form-group">
                                            <label>Contraseña *</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control password-input" id="edit_account_password" name="password" placeholder="Contraseña de AnyDesk">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary btn-generar-password" type="button" data-target="edit_account_password" title="Generar contraseña"><i class="fas fa-random"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 border-right">
                                        <h6 class="text-danger"><i class="fas fa-envelope"></i> Gmail</h6>
                                        <div class="form-group">
                                            <label>Correo</label>
                                            <input type="email" class="form-control" id="edit_gmail_email" name="gmail_email" placeholder="ejemplo@gmail.com">
                                        </div>
                                        <div class="form-group">
                                            <label>Contraseña</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control password-input" id="edit_gmail_password" name="gmail_password" placeholder="Contraseña de Gmail">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary btn-generar-password" type="button" data-target="edit_gmail_password" title="Generar contraseña"><i class="fas fa-random"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="text-info"><i class="fas fa-shield-alt"></i> NordVPN</h6>
                                        <div class="form-group">
                                            <label>Usuario / Correo</label>
                                            <input type="text" class="form-control" id="edit_nordvpn" name="nordvpn" placeholder="Usuario o correo">
                                        </div>
                                        <div class="form-group">
                                            <label>Contraseña</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control password-input" id="edit_nordvpn_password" name="nordvpn_password" placeholder="Contraseña de NordVPN">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary btn-generar-password" type="button" data-target="edit_nordvpn_password" title="Generar contraseña"><i class="fas fa-random"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>IP Dedicada</label>
                                            <input type="text" class="form-control" id="edit_nord_dedicada_ip" name="nord_dedicada_ip" placeholder="Ej. 123.45.67.89">
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-success"><i class="fas fa-envelope"></i> AnyDesk Email</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Correo</label>
                                                    <input type="email" class="form-control" id="edit_anydesk_email" name="anydesk_email" placeholder="correo@anydesk.com">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Contraseña</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control password-input" id="edit_anydesk_email_password" name="anydesk_email_password" placeholder="Contraseña del correo">
                                                        <div class="input-group-append">
                                                            <button class="btn btn-outline-secondary btn-generar-password" type="button" data-target="edit_anydesk_email_password" title="Generar contraseña"><i class="fas fa-random"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Notas</label>
                                            <textarea class="form-control" id="edit_account_notes" name="notes" rows="3" placeholder="Notas adicionales..."></textarea>
                                        </div>
                                    </div>
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

                    <!-- Modal Ver Detalles -->
                    <div class="modal fade" id="verDetallesModal" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="tituloDetalles"><i class="fas fa-desktop text-success mr-1"></i> Detalles de Cuenta</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body" id="detallesBody">
                            <!-- Cargado por JS -->
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                          </div>
                        </div>
                      </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

<?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Pasa el rol de usuario al JS -->
    <script>
        window.USER_ROLE = "<?= htmlspecialchars($_SESSION['user_role']) ?>";
        window.PERMISOS = <?= json_encode($_SESSION['user_permissions'] ?? []) ?>;
        window.CSRF_TOKEN = "<?= htmlspecialchars($_SESSION['csrf_token']) ?>";
    </script>

    <!-- Page level custom scripts -->
    <script src="js/anydesk.js"></script>

</body>

</html>
