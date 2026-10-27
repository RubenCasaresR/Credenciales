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

if (!userCan('payment_gateways', 'ver')) {
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
    <title>Pasarelas de Pago - Gestor de Credenciales</title>

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
                        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-credit-card mr-2"></i>Pasarelas de Pago</h1>
                        <?php if ($isAdmin): ?>
                        <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" id="btnNuevaCuenta">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Nueva Cuenta
                        </button>
                        <?php endif; ?>
                    </div>

                    <div class="row" id="paymentGatewayGrid">
                    </div>

                    <?php if ($isAdmin): ?>
                    <div class="modal fade" id="editarCuentaModal" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                          <form id="formCuenta">
                              <div class="modal-header">
                                <h5 class="modal-title" id="tituloModalCuenta">Nueva Cuenta</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                              <div class="modal-body">
                                <input type="hidden" id="edit_id" name="id">
                                <?= csrfTokenField() ?>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Nombre de la Cuenta *</label>
                                        <input type="text" class="form-control" id="edit_name" name="name" required placeholder="Ej. PayPal Producción">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Tipo *</label>
                                        <select class="form-control" id="edit_gateway_type" name="gateway_type" required>
                                            <option value="">Seleccione...</option>
                                            <option value="paypal">PayPal</option>
                                            <option value="stripe">Stripe</option>
                                            <option value="mercadopago">Mercado Pago</option>
                                            <option value="otro">Otra</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Merchant ID / Account ID</label>
                                        <input type="text" class="form-control" id="edit_merchant_id" name="merchant_id" placeholder="Ej. XXXXXXXXXXXXX">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Correo asociado</label>
                                        <input type="email" class="form-control" id="edit_email" name="email" placeholder="correo@ejemplo.com">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Public Key / Client ID</label>
                                    <input type="text" class="form-control" id="edit_public_key" name="public_key" placeholder="Ej. pk_live_***">
                                </div>

                                <div class="form-group">
                                    <label>Secret Key * <small class="text-muted">(se cifrará con libsodium)</small></label>
                                    <input type="text" class="form-control" id="edit_secret_key" name="secret_key" placeholder="Ej. sk_live_***">
                                </div>

                                <div class="form-group">
                                    <label>Webhook Secret <small class="text-muted">(se cifrará con libsodium)</small></label>
                                    <input type="text" class="form-control" id="edit_webhook_secret" name="webhook_secret" placeholder="Ej. whsec_***">
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <div class="form-check mt-2">
                                            <input type="checkbox" class="form-check-input" id="edit_sandbox" name="sandbox" value="1" checked>
                                            <label class="form-check-label" for="edit_sandbox">Modo Sandbox / Pruebas</label>
                                        </div>
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
                            <h5 class="modal-title" id="tituloDetalles"><i class="fas fa-credit-card text-primary mr-1"></i> Detalles</h5>
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

    <script src="js/payment_crypto.js"></script>

</body>

</html>
