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
    <title>Documentos - Gestor de Credenciales</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
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
                        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-folder-open text-primary mr-2"></i> Documentos</h1>
                        <?php if ($isAdmin): ?>
                        <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" id="btnSubirDocumento">
                            <i class="fas fa-upload fa-sm text-white-50"></i> Subir Documento
                        </button>
                        <?php endif; ?>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Archivos</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="documentosTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Descripción</th>
                                            <th>Tipo</th>
                                            <th>Tamaño</th>
                                            <th>Subido</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="documentosBody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <?php if ($isAdmin): ?>
                    <div class="modal fade" id="subirDocumentoModal" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <form id="formDocumento" enctype="multipart/form-data">
                              <div class="modal-header">
                                <h5 class="modal-title" id="tituloModalDocumento">Subir Documento</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                              <div class="modal-body">
                                <input type="hidden" id="edit_doc_id" name="id">
                                <?= csrfTokenField() ?>
                                
                                <div class="form-group">
                                    <label>Nombre *</label>
                                    <input type="text" class="form-control" id="edit_doc_name" name="name" required placeholder="Nombre del documento">
                                </div>
                                <div class="form-group">
                                    <label>Descripción</label>
                                    <textarea class="form-control" id="edit_doc_description" name="description" rows="2" placeholder="Descripción opcional"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Archivo *</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="edit_doc_file" name="file" required>
                                        <label class="custom-file-label" for="edit_doc_file" id="fileLabel">Seleccionar archivo...</label>
                                    </div>
                                    <small class="form-text text-muted">Formatos permitidos: PDF, Word, Excel, imágenes, txt, etc.</small>
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Subir</button>
                              </div>
                          </form>
                        </div>
                      </div>
                    </div>
                    <?php endif; ?>

                </div>

            </div>

<?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.USER_ROLE = "<?= htmlspecialchars($_SESSION['user_role']) ?>";
        window.PERMISOS = <?= json_encode($_SESSION['user_permissions'] ?? []) ?>;
        window.CSRF_TOKEN = "<?= htmlspecialchars($_SESSION['csrf_token']) ?>";
    </script>
    <script src="js/documentos.js"></script>

</body>

</html>