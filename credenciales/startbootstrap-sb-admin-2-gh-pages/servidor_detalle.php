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

if (!isset($_GET['id'])) {
    header('Location: credenciales.php');
    exit;
}

require 'config.php';
$server_id = $_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM servers WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$server_id]);
    $server = $stmt->fetch();
    
    if (!$server) {
        header('Location: credenciales.php');
        exit;
    }
} catch (PDOException $e) {
    die("Database error");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Detalles Servidor - <?= htmlspecialchars($server['name']) ?></title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/custom/dark-mode.css" rel="stylesheet">
    <!-- Custom styles for this page -->
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
                        <h1 class="h3 mb-0 text-gray-800">
                            <a href="credenciales.php" class="btn btn-sm btn-light shadow-sm mr-2"><i class="fas fa-arrow-left"></i></a>
                            Servidor: <?= htmlspecialchars($server['name']) ?>
                        </h1>
                        <?php if ($isAdmin): ?>
                        <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" id="btnNuevaCredencial">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Nueva Credencial
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Server Info Card -->
                    <div class="card shadow mb-4 border-left-primary">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3"><strong>Proveedor:</strong> <?= htmlspecialchars($server['provider'] ?: 'N/A') ?></div>
                                <div class="col-md-3"><strong>IP:</strong> <?= htmlspecialchars($server['ip_address'] ?: 'N/A') ?></div>
                                <div class="col-md-6"><strong>Notas:</strong> <?= htmlspecialchars($server['notes'] ?: 'Ninguna') ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Type filter cards -->
                    <div class="row mb-4" id="tipoCards"></div>

                    <!-- Credential cards -->
                    <div id="credencialCards" class="row"></div>

                    <!-- Modal Ver Credencial -->
                    <div class="modal fade" id="verCredencialModal" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Detalles de la Credencial</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body">
                            <p><strong>Identificador:</strong> <span id="ver_servicio"></span></p>
                            <p><strong>Tipo:</strong> <span class="badge badge-primary" id="ver_tipo"></span></p>
                            <div id="ver_field_url">
                                <p><strong>URL:</strong> <a href="#" target="_blank" id="ver_url_link"><span id="ver_url"></span></a></p>
                                <hr>
                            </div>
                            <div id="ver_field_usuario_pass">
                                <p><strong>Usuario:</strong> <span id="ver_usuario"></span></p>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Password</span>
                                    </div>
                                    <input type="text" class="form-control bg-white" id="ver_password" readonly>
                                </div>
                                <hr>
                            </div>
                            <div id="ver_field_pago">
                                <p><strong>Fecha de Pago:</strong> <span id="ver_pago"></span></p>
                            </div>
                            <div id="ver_field_autenticador">
                                <p><strong>Responsable:</strong> <span id="ver_responsable"></span></p>
                                <p><strong>Código de Recuperación:</strong> <span id="ver_recovery_code"></span></p>
                            </div>
                            <p><strong>Notas:</strong> <span id="ver_notas"></span></p>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                          </div>
                        </div>
                      </div>
                    </div>

                    <?php if ($isAdmin): ?>
                    <!-- Modal Editar/Crear Credencial -->
                    <div class="modal fade" id="editarCredencialModal" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                          <form id="formCredencial">
                              <div class="modal-header">
                                <h5 class="modal-title" id="tituloModalCredencial">Credencial</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                              <div class="modal-body">
                                <input type="hidden" id="edit_id" name="id">
                                <input type="hidden" id="edit_server_id" name="server_id" value="<?= htmlspecialchars($server_id) ?>">
                                <?= csrfTokenField() ?>
                                
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Identificador (Ej. cPanel, Webmail principal) *</label>
                                        <input type="text" class="form-control" id="edit_servicio" name="service_name" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Tipo *</label>
                                        <select class="form-control" id="edit_tipo" name="type" required>
                                            <option value="">Seleccione...</option>
                                            <option value="Hosting">Hosting / Panel</option>
                                            <option value="Dominio">Dominio</option>
                                            <option value="Correo">Correo Electrónico</option>
                                            <option value="Google Workspace">Google Workspace</option>
                                            <option value="Base de Datos">Base de Datos</option>
                                            <option value="FTP">FTP / SSH</option>
                                            <option value="Autenticador">Autenticador</option>
                                            <option value="Otro">Otro</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group" id="field_url">
                                    <label><span id="url_label">URL de Acceso</span></label>
                                    <input type="url" class="form-control" id="edit_url" name="url">
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group" id="field_usuario">
                                        <label>Usuario</label>
                                        <input type="text" class="form-control" id="edit_usuario" name="username">
                                    </div>
                                    <div class="col-md-6 form-group" id="field_password">
                                        <label>Contraseña *</label>
                                        <input type="text" class="form-control" id="edit_password" name="password">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group" id="field_pago">
                                        <label>Fecha de Pago</label>
                                        <input type="date" class="form-control" id="edit_pago" name="payment_date">
                                    </div>
                                    <div class="col-md-6 form-group" id="field_db_name" style="display:none;">
                                        <label>Nombre de Base de Datos</label>
                                        <input type="text" class="form-control" id="edit_db_name" name="db_name" placeholder="Ej. bd_cliente">
                                    </div>
                                    <div class="col-md-6 form-group" id="field_port" style="display:none;">
                                        <label>Puerto</label>
                                        <input type="text" class="form-control" id="edit_port" name="port" placeholder="Ej. 21, 990">
                                    </div>
                                    <div class="col-md-6 form-group" id="field_domain_name" style="display:none;">
                                        <label>Nombre de Dominio</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="edit_domain_name" name="domain_name" placeholder="ejemplo.com">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button" id="btnCopyDomain" title="Copiar"><i class="fas fa-copy"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 form-group" id="field_responsable" style="display:none;">
                                        <label>Responsable</label>
                                        <input type="text" class="form-control" id="edit_responsable" name="responsible" placeholder="Persona responsable">
                                    </div>
                                    <div class="col-md-6 form-group" id="field_recovery_code" style="display:none;">
                                        <label>Código de Recuperación</label>
                                        <textarea class="form-control" id="edit_recovery_code" name="recovery_code" rows="2" placeholder="Código de recuperación del autenticador"></textarea>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Notas</label>
                                    <textarea class="form-control" id="edit_notas" name="notes" rows="3"></textarea>
                                </div>
                                
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Guardar Credencial</button>
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

    <script>
        window.USER_ROLE = "<?= htmlspecialchars($_SESSION['user_role']) ?>";
        window.PERMISOS = <?= json_encode($_SESSION['user_permissions'] ?? []) ?>;
        window.SERVER_ID = "<?= htmlspecialchars($server_id) ?>";
        window.CSRF_TOKEN = "<?= htmlspecialchars($_SESSION['csrf_token']) ?>";
        window.CREDENTIAL_ID = "<?= htmlspecialchars($_GET['credential'] ?? '') ?>";
    </script>

    <!-- Page level custom scripts -->
    <script src="js/servidor_detalle.js"></script>

</body>

</html>
