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
    <title>Sistemas Internos - Gestor de Credenciales</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/custom/dark-mode.css" rel="stylesheet">
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
                        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-cogs mr-2"></i>Sistemas Internos</h1>
                        <?php if ($isAdmin): ?>
                        <button class="d-none d-sm-inline-block btn btn-sm btn-info shadow-sm" id="btnNuevoSistema">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Nuevo Sistema
                        </button>
                        <?php endif; ?>
                    </div>

                    <div class="row" id="systemsGrid">
                    </div>

                    <?php if ($isAdmin): ?>
                    <div class="modal fade" id="editarSistemaModal" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                          <form id="formSistema">
                              <div class="modal-header">
                                <h5 class="modal-title" id="tituloModalSistema">Nuevo Sistema Interno</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                              <div class="modal-body">
                                <input type="hidden" id="edit_id" name="id">
                                <?= csrfTokenField() ?>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Nombre del Sistema *</label>
                                        <input type="text" class="form-control" id="edit_name" name="name" required placeholder="Ej. CRM, TicketSystem, ERP">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Dominio</label>
                                        <input type="text" class="form-control" id="edit_domain" name="domain" placeholder="Ej. crm.midominio.com">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Alojado en</label>
                                        <input type="text" class="form-control" id="edit_hosted_at" name="hosted_at" placeholder="Ej. HostGator, VPS propio, AWS">
                                    </div>
                                </div>

                                <hr>
                                <h6 class="text-info"><i class="fas fa-code-branch mr-1"></i> Credenciales</h6>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Cuenta GitHub asociada</label>
                                        <input type="text" class="form-control" id="edit_github_account" name="github_account" placeholder="Ej. usuario@github.com">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Usuario</label>
                                        <input type="text" class="form-control" id="edit_username" name="username" placeholder="Usuario de acceso al sistema">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Contraseña</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control password-input" id="edit_password" name="password" placeholder="Contraseña de acceso">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary btn-generar-password" type="button" title="Generar contraseña"><i class="fas fa-random"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Observaciones</label>
                                    <textarea class="form-control" id="edit_notes" name="notes" rows="3" placeholder="Notas adicionales..."></textarea>
                                </div>

                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-info">Guardar</button>
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
                            <h5 class="modal-title" id="tituloDetalles"><i class="fas fa-cogs text-info mr-1"></i> Detalles del Sistema</h5>
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
    <script src="js/internal_systems.js"></script>

</body>

</html>
