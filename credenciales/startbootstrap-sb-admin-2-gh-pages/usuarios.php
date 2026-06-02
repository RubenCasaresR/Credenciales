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

$modulos = getAllModulos();

if (!userCan('usuarios', 'ver')) {
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
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Credenciales - Usuarios</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/custom/dark-mode.css" rel="stylesheet">


    <!-- Custom styles for this page -->
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="vendor/jquery/jquery.min.js"></script>
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        
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
                        <h1 class="h3 mb-0 text-gray-800">Gestión de Usuarios</h1>
                        <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" id="btnNuevoUsuario">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Nuevo Usuario
                        </button>
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

                    <!-- Modal Nuevo Usuario -->
                    <div class="modal fade" id="nuevoUsuarioModal" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                          <form id="formNuevoUsuario">
                              <div class="modal-header">
                                <h5 class="modal-title">Nuevo Usuario</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                              </div>
                              <div class="modal-body">
                                <div class="row">
                                  <div class="col-md-5">
                                    <?= csrfTokenField() ?>
                                    <div class="form-group">
                                        <label>Nombre *</label>
                                        <input type="text" class="form-control" id="new_nombre" name="name" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Email *</label>
                                        <input type="email" class="form-control" id="new_email" name="email" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Contraseña *</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control password-input" id="new_password" name="password" required>
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary btn-generar-password" type="button" data-target="new_password" title="Generar contraseña"><i class="fas fa-random"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Rol *</label>
                                        <select class="form-control" id="new_rol" name="role" required>
                                            <option value="Usuario">Usuario</option>
                                            <option value="Administrador">Administrador</option>
                                        </select>
                                    </div>
                                  </div>
                                    <div class="col-md-7">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Permisos por Módulo</label>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm tabla-permisos" data-context="new">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th style="width:140px">Módulo</th>
                                                        <th class="text-center">Acceso Total</th>
                                                        <th class="text-center">Ver</th>
                                                        <th class="text-center">Crear</th>
                                                        <th class="text-center">Editar</th>
                                                        <th class="text-center">Eliminar</th>
                                                        <th class="text-center">Bloqueo</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($modulos as $mod): ?>
                                                    <tr>
                                                        <td><?= getModuloLabel($mod) ?></td>
                                                        <td class="text-center"><input type="checkbox" class="acceso-total" data-modulo="<?= $mod ?>"></td>
                                                        <td class="text-center"><input type="checkbox" class="permiso ver" name="permisos[<?= $mod ?>][ver]" value="1" data-modulo="<?= $mod ?>" data-accion="ver" checked></td>
                                                        <td class="text-center"><input type="checkbox" class="permiso crear" name="permisos[<?= $mod ?>][crear]" value="1" data-modulo="<?= $mod ?>" data-accion="crear"></td>
                                                        <td class="text-center"><input type="checkbox" class="permiso editar" name="permisos[<?= $mod ?>][editar]" value="1" data-modulo="<?= $mod ?>" data-accion="editar"></td>
                                                        <td class="text-center"><input type="checkbox" class="permiso eliminar" name="permisos[<?= $mod ?>][eliminar]" value="1" data-modulo="<?= $mod ?>" data-accion="eliminar"></td>
                                                        <td class="text-center"><input type="checkbox" class="permiso-bloqueo" name="permisos[<?= $mod ?>][bloqueo]" value="1" data-modulo="<?= $mod ?>" data-accion="bloqueo"></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                  </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Crear Usuario</button>
                              </div>
                          </form>
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
                                <?= csrfTokenField() ?>
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
                                <div class="form-group">
                                    <label class="font-weight-bold">Permisos por Módulo</label>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm tabla-permisos" data-context="edit">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th style="width:160px">Módulo</th>
                                                    <th class="text-center">Acceso Total</th>
                                                    <th class="text-center">Ver</th>
                                                    <th class="text-center">Crear</th>
                                                    <th class="text-center">Editar</th>
                                                    <th class="text-center">Eliminar</th>
                                                    <th class="text-center">Bloqueo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($modulos as $mod): ?>
                                                <tr>
                                                    <td><?= getModuloLabel($mod) ?></td>
                                                    <td class="text-center"><input type="checkbox" class="acceso-total" data-modulo="<?= $mod ?>"></td>
                                                    <td class="text-center"><input type="checkbox" class="permiso ver" name="permisos[<?= $mod ?>][ver]" value="1" data-modulo="<?= $mod ?>" data-accion="ver"></td>
                                                    <td class="text-center"><input type="checkbox" class="permiso crear" name="permisos[<?= $mod ?>][crear]" value="1" data-modulo="<?= $mod ?>" data-accion="crear"></td>
                                                    <td class="text-center"><input type="checkbox" class="permiso editar" name="permisos[<?= $mod ?>][editar]" value="1" data-modulo="<?= $mod ?>" data-accion="editar"></td>
                                                    <td class="text-center"><input type="checkbox" class="permiso eliminar" name="permisos[<?= $mod ?>][eliminar]" value="1" data-modulo="<?= $mod ?>" data-accion="eliminar"></td>
                                                    <td class="text-center"><input type="checkbox" class="permiso-bloqueo" name="permisos[<?= $mod ?>][bloqueo]" value="1" data-modulo="<?= $mod ?>" data-accion="bloqueo"></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
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


            </div>
            <!-- End of Main Content -->

<?php include 'includes/footer.php'; ?>

    <!-- Page level plugins -->
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        window.CSRF_TOKEN = "<?= htmlspecialchars($_SESSION['csrf_token']) ?>";
    </script>

    <!-- Page level custom scripts -->
    <script src="js/usuarios.js"></script>

</body>
</html>