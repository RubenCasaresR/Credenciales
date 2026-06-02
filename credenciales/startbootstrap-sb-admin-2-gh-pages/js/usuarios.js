$(document).ready(function() {
    var tablaUsuarios = $('#usuariosTable').DataTable({
        "ajax": "api/get_users.php",
        "columns": [
            { "data": "id" },
            { "data": "name" },
            { "data": "email" },
            { "data": "role" },
            { "data": "created_at" },
            {
                "data": null,
                "defaultContent": `
                    <button class="btn btn-info btn-sm btn-ver"><i class="fas fa-eye"></i></button>
                    <button class="btn btn-primary btn-sm btn-editar"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-danger btn-sm btn-eliminar"><i class="fas fa-trash"></i></button>
                `,
                "orderable": false
            }
        ],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
        }
    });

    var modulosBloqueables = ['servidores', 'monitoreo', 'bitacora', 'payment_gateways', 'usuarios'];

    function configurarTablaPermisos($table, rol, permisosIniciales) {
        var isAdmin = (rol === 'Administrador');
        $table.find('tbody tr').each(function() {
            var $row = $(this);
            var modulo = $row.find('.acceso-total').data('modulo');
            var permModulo = permisosIniciales && permisosIniciales[modulo] ? permisosIniciales[modulo] : null;

            $row.find('.permiso').each(function() {
                var $cb = $(this);
                var accion = $cb.data('accion');
                var checked = false;
                if (permModulo !== null) {
                    checked = permModulo[accion] === true;
                } else {
                    checked = isAdmin || accion === 'ver';
                }
                $cb.prop('checked', checked);
                $cb.prop('disabled', isAdmin);
            });

            var $bloqueoCb = $row.find('.permiso-bloqueo');
            if (permModulo !== null) {
                $bloqueoCb.prop('checked', permModulo.bloqueo === true);
            } else {
                $bloqueoCb.prop('checked', !isAdmin && modulosBloqueables.indexOf(modulo) !== -1);
            }
            $bloqueoCb.prop('disabled', isAdmin);

            var totalChecked = $row.find('.permiso').length === $row.find('.permiso:checked').length;
            $row.find('.acceso-total').prop('checked', totalChecked);
            $row.find('.acceso-total').prop('disabled', isAdmin);
        });
    }

    $('.tabla-permisos').on('change', '.acceso-total', function() {
        var $row = $(this).closest('tr');
        var checked = $(this).is(':checked');
        $row.find('.permiso').prop('checked', checked);
    });

    $('.tabla-permisos').on('change', '.permiso', function() {
        var $row = $(this).closest('tr');
        var allChecked = $row.find('.permiso').length === $row.find('.permiso:checked').length;
        $row.find('.acceso-total').prop('checked', allChecked);
    });

    function setearPermisosPorRol($table, rol) {
        var isAdmin = (rol === 'Administrador');
        $table.find('tbody tr').each(function() {
            var $row = $(this);
            var modulo = $row.find('.acceso-total').data('modulo');

            if (isAdmin) {
                $row.find('.permiso, .acceso-total').prop('checked', true).prop('disabled', true);
            } else {
                $row.find('.permiso, .acceso-total').prop('disabled', false);
                $row.find('.permiso').each(function() {
                    if ($(this).hasClass('ver')) {
                        $(this).prop('checked', true);
                    } else {
                        $(this).prop('checked', false);
                    }
                });
                var allChecked = $row.find('.permiso').length === $row.find('.permiso:checked').length;
                $row.find('.acceso-total').prop('checked', allChecked);
            }

            var $bloqueoCb = $row.find('.permiso-bloqueo');
            if (isAdmin) {
                $bloqueoCb.prop('checked', false).prop('disabled', true);
            } else {
                $bloqueoCb.prop('disabled', false);
                $bloqueoCb.prop('checked', modulosBloqueables.indexOf(modulo) !== -1);
            }
        });
    }

    function collectPermisos($table) {
        var data = {};
        $table.find('tbody tr').each(function() {
            var $row = $(this);
            $row.find('.permiso, .permiso-bloqueo').each(function() {
                var $cb = $(this);
                var name = $cb.attr('name');
                if (name) {
                    if (!$cb.is(':checked')) {
                        if (!data[name]) {
                            data[name] = '0';
                        }
                    } else {
                        data[name] = '1';
                    }
                }
            });
        });
        return data;
    }

    // Nuevo Usuario
    $('#btnNuevoUsuario').on('click', function() {
        $('#formNuevoUsuario')[0].reset();
        var rol = $('#new_rol').val();
        setearPermisosPorRol($('#nuevoUsuarioModal .tabla-permisos'), rol);
        $('#nuevoUsuarioModal').modal('show');
    });

    $('#new_rol').on('change', function() {
        setearPermisosPorRol($('#nuevoUsuarioModal .tabla-permisos'), $(this).val());
    });

    $('#formNuevoUsuario').on('submit', function(e) {
        e.preventDefault();
        var $table = $('#nuevoUsuarioModal .tabla-permisos');
        var $form = $(this);
        var extraPermisos = collectPermisos($table);
        var formData = $form.serializeArray();
        $.each(extraPermisos, function(key, value) {
            var found = false;
            $.each(formData, function(i, field) {
                if (field.name === key) {
                    field.value = value;
                    found = true;
                }
            });
            if (!found) {
                formData.push({name: key, value: value});
            }
        });
        $.ajax({
            url: 'api/create_user.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                if(response.success) {
                    $('#nuevoUsuarioModal').modal('hide');
                    Swal.fire('Creado!', 'El usuario ha sido creado correctamente.', 'success');
                    tablaUsuarios.ajax.reload();
                } else {
                    Swal.fire('Error', response.error, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error de comunicación con el servidor', 'error');
            }
        });
    });

    $('#usuariosTable tbody').on('click', '.btn-ver', function () {
        var data = tablaUsuarios.row($(this).parents('tr')).data();
        $('#ver_id').text(data.id);
        $('#ver_nombre').text(data.name);
        $('#ver_email').text(data.email);
        $('#ver_rol').text(data.role);
        $('#verUsuarioModal').modal('show');
    });

    $('#usuariosTable tbody').on('click', '.btn-editar', function () {
        var data = tablaUsuarios.row($(this).parents('tr')).data();
        $('#edit_id').val(data.id);
        $('#edit_nombre').val(data.name);
        $('#edit_email').val(data.email);
        $('#edit_rol').val(data.role);
        var $table = $('#editarUsuarioModal .tabla-permisos');
        $table.find('tbody tr').each(function() {
            var $row = $(this);
            $row.find('.acceso-total, .permiso, .permiso-bloqueo').prop('checked', false).prop('disabled', false);
        });
        $.ajax({
            url: 'api/get_user.php',
            type: 'GET',
            data: { id: data.id },
            success: function(response) {
                if (response.success) {
                    var permisos = response.permisos || {};
                    configurarTablaPermisos($table, data.role, permisos);
                }
            }
        });
        $('#editarUsuarioModal').modal('show');
    });

    $('#edit_rol').on('change', function() {
        setearPermisosPorRol($('#editarUsuarioModal .tabla-permisos'), $(this).val());
    });

    $('#formEditarUsuario').on('submit', function(e) {
        e.preventDefault();
        var $table = $('#editarUsuarioModal .tabla-permisos');
        var $form = $(this);
        var extraPermisos = collectPermisos($table);
        var formData = $form.serializeArray();
        $.each(extraPermisos, function(key, value) {
            var found = false;
            $.each(formData, function(i, field) {
                if (field.name === key) {
                    field.value = value;
                    found = true;
                }
            });
            if (!found) {
                formData.push({name: key, value: value});
            }
        });
        $.ajax({
            url: 'api/update_user.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                if(response.success) {
                    $('#editarUsuarioModal').modal('hide');
                    Swal.fire('Actualizado!', 'El usuario ha sido actualizado.', 'success');
                    tablaUsuarios.ajax.reload();
                } else {
                    Swal.fire('Error', response.error, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error de comunicación con el servidor', 'error');
            }
        });
    });

    $('#usuariosTable tbody').on('click', '.btn-eliminar', function () {
        var data = tablaUsuarios.row($(this).parents('tr')).data();
        
        Swal.fire({
            title: '¿Estás seguro?',
            text: "El usuario será eliminado (soft delete).",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            cancelButtonColor: '#858796',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'api/delete_user.php',
                    type: 'POST',
                    data: { id: data.id, csrf_token: window.CSRF_TOKEN },
                    success: function(response) {
                        if(response.success) {
                            Swal.fire('Eliminado!', 'El usuario ha sido eliminado.', 'success');
                            tablaUsuarios.ajax.reload();
                        } else {
                            Swal.fire('Error', response.error, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Error al eliminar', 'error');
                    }
                });
            }
        });
    });
});
