<?php
$original = file_get_contents('index_original.html');

// Extract head part
$head_part = substr($original, 0, strpos($original, '<ul class="navbar-nav'));
// Replace title
$head_part = preg_replace('/<title>.*?<\/title>/', '<title>Credenciales - Dashboard</title>', $head_part);
// Add jQuery early so inline scripts in topbar.php work
$head_part = str_replace('</head>', '    <script src="vendor/jquery/jquery.min.js"></script>' . "\n" . '</head>', $head_part);

// Extract Dashboard Content
$start_content = strpos($original, '<!-- Begin Page Content -->');
$end_content = strpos($original, '<!-- /.container-fluid -->') + 26;
$dashboard_content = substr($original, $start_content, $end_content - $start_content);

// Build index.php
$index_php = "<?php
session_start();
if (!isset(\$_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
$head_part
<?php include 'includes/sidebar.php'; ?>

        <!-- Content Wrapper -->
        <div id=\"content-wrapper\" class=\"d-flex flex-column\">

            <!-- Main Content -->
            <div id=\"content\">

<?php include 'includes/topbar.php'; ?>

$dashboard_content

            </div>
            <!-- End of Main Content -->

<?php include 'includes/footer.php'; ?>

    <!-- Page level plugins -->
    <script src=\"vendor/chart.js/Chart.min.js\"></script>

    <!-- Page level custom scripts -->
    <script src=\"js/demo/chart-area-demo.js\"></script>
    <script src=\"js/demo/chart-pie-demo.js\"></script>

</body>
</html>";

file_put_contents('index.php', $index_php);

// Build usuarios.php
$usuarios_head = preg_replace('/<title>.*?<\/title>/', '<title>Credenciales - Usuarios</title>', $head_part);
// Add Datatables and SweetAlert CSS
$css_insert = '
    <!-- Custom styles for this page -->
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="vendor/jquery/jquery.min.js"></script>
</head>';
$usuarios_head = str_replace('</head>', $css_insert, $usuarios_head);

$usuarios_content = '
                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Gestión de Usuarios</h1>
                    </div>

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Lista de Usuarios</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="usuariosTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Email</th>
                                            <th>Rol</th>
                                            <th>Fecha Creación</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Ver Usuario -->
                    <div class="modal fade" id="verUsuarioModal" tabindex="-1" role="dialog" aria-labelledby="verUsuarioModalLabel" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="verUsuarioModalLabel">Detalles del Usuario</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body">
                            <p><strong>ID:</strong> <span id="ver_id"></span></p>
                            <p><strong>Nombre:</strong> <span id="ver_nombre"></span></p>
                            <p><strong>Email:</strong> <span id="ver_email"></span></p>
                            <p><strong>Rol:</strong> <span id="ver_rol"></span></p>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Modal Editar Usuario -->
                    <div class="modal fade" id="editarUsuarioModal" tabindex="-1" role="dialog" aria-labelledby="editarUsuarioModalLabel" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <form id="formEditarUsuario">
                              <div class="modal-header">
                                <h5 class="modal-title" id="editarUsuarioModalLabel">Editar Usuario</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                              <div class="modal-body">
                                <input type="hidden" id="edit_id" name="id">
                                <div class="form-group">
                                    <label>Nombre</label>
                                    <input type="text" class="form-control" id="edit_nombre" name="name" required>
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" class="form-control" id="edit_email" name="email" required>
                                </div>
                                <div class="form-group">
                                    <label>Rol</label>
                                    <select class="form-control" id="edit_rol" name="role" required>
                                        <option value="Administrador">Administrador</option>
                                        <option value="Usuario">Usuario</option>
                                    </select>
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                              </div>
                          </form>
                        </div>
                      </div>
                    </div>

                </div>
                <!-- /.container-fluid -->
';

$usuarios_php = "<?php
session_start();
if (!isset(\$_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
$usuarios_head
<?php include 'includes/sidebar.php'; ?>

        <!-- Content Wrapper -->
        <div id=\"content-wrapper\" class=\"d-flex flex-column\">

            <!-- Main Content -->
            <div id=\"content\">

<?php include 'includes/topbar.php'; ?>

$usuarios_content

            </div>
            <!-- End of Main Content -->

<?php include 'includes/footer.php'; ?>

    <!-- Page level plugins -->
    <script src=\"vendor/datatables/jquery.dataTables.min.js\"></script>
    <script src=\"vendor/datatables/dataTables.bootstrap4.min.js\"></script>
    <script src=\"https://cdn.jsdelivr.net/npm/sweetalert2@11\"></script>

    <!-- Page level custom scripts -->
    <script src=\"js/usuarios.js\"></script>

</body>
</html>";

file_put_contents('usuarios.php', $usuarios_php);

echo "Built index.php and usuarios.php successfully.\n";
?>
