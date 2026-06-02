<?php
$file = 'index.php';
$content = file_get_contents($file);

// Replace CSS
$css_old = '    <link href="css/sb-admin-2.min.css" rel="stylesheet">

</head>';
$css_new = '    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <!-- Custom styles for this page -->
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
</head>';
$content = str_replace($css_old, $css_new, $content);

// Replace Scripts
$scripts_old = '    <!-- Page level plugins -->
    <script src="vendor/chart.js/Chart.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="js/demo/chart-area-demo.js"></script>
    <script src="js/demo/chart-pie-demo.js"></script>';

$scripts_new = '    <!-- Page level plugins -->
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom User scripts -->
    <script src="js/usuarios.js"></script>';
$content = str_replace($scripts_old, $scripts_new, $content);

// Replace Content
$start_marker = '<!-- Begin Page Content -->';
$end_marker = '<!-- /.container-fluid -->';
$start_pos = strpos($content, $start_marker);
$end_pos = strpos($content, $end_marker);

if ($start_pos !== false && $end_pos !== false) {
    $new_content = '
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
';
    
    // We construct it carefully
    $start_insert = strpos($content, '<div class="d-sm-flex');
    $end_insert = strpos($content, '</div>' . "\n" . '                <!-- /.container-fluid -->');
    if ($start_insert !== false && $end_insert !== false) {
        $content = substr($content, 0, $start_insert) . ltrim($new_content) . "\n                " . substr($content, $end_insert);
    }
}

file_put_contents($file, $content);
echo "Cleaned index.php\n";
?>
