$(document).ready(function() {
    var isAdmin = (window.USER_ROLE === 'Administrador');
    var puedeCrear = isAdmin || (window.PERMISOS && window.PERMISOS.internal_systems && window.PERMISOS.internal_systems.crear);
    var puedeEditar = isAdmin || (window.PERMISOS && window.PERMISOS.internal_systems && window.PERMISOS.internal_systems.editar);
    var puedeEliminar = isAdmin || (window.PERMISOS && window.PERMISOS.internal_systems && window.PERMISOS.internal_systems.eliminar);

    function copyText(text, btn) {
        navigator.clipboard.writeText(text).then(function() {
            var original = btn.html();
            btn.html('<i class="fas fa-check"></i> Copiado');
            btn.removeClass('btn-outline-secondary').addClass('btn-success');
            setTimeout(function() {
                btn.html(original);
                btn.removeClass('btn-success').addClass('btn-outline-secondary');
            }, 1500);
        });
    }

    function loadSystems() {
        $.ajax({
            url: 'api/get_internal_systems.php',
            type: 'GET',
            success: function(res) {
                if (res.success) {
                    var html = '';
                    if (res.data.length === 0) {
                        html = '<div class="col-12"><div class="alert alert-info">No hay sistemas internos registrados.</div></div>';
                    } else {
                        res.data.forEach(function(sys) {
                            var safeSys = encodeURIComponent(JSON.stringify(sys));

                            html += `
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-left-info shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="h5 mb-2 font-weight-bold text-gray-800 text-truncate" title="${sys.name}">
                                                    <i class="fas fa-cogs fa-sm text-info mr-1"></i> ${sys.name}
                                                </div>
                                                <p class="card-text small text-muted mb-1"><i class="fas fa-globe fa-fw text-info"></i> ${sys.domain || 'Sin dominio'}</p>
                                                ${sys.hosted_at ? '<p class="card-text small text-muted mb-1"><i class="fas fa-server fa-fw text-info"></i> ' + sys.hosted_at + '</p>' : ''}
                                                <div class="mt-2">
                                                    <button class="btn btn-sm btn-info w-100 mb-2 btn-ver-detalles" data-sys='${safeSys}'>
                                                        <i class="fas fa-eye"></i> Ver detalles
                                                    </button>
                                                    ${(puedeEditar || puedeEliminar) ? `
                                                    <div class="d-flex justify-content-between">
                                                        ${puedeEditar ? `<button class="btn btn-sm btn-outline-secondary btn-editar-sistema flex-fill mr-1" data-sys='${safeSys}'>
                                                            <i class="fas fa-edit"></i>
                                                        </button>` : ''}
                                                        ${puedeEliminar ? `<button class="btn btn-sm btn-outline-danger btn-eliminar-sistema flex-fill ml-1" data-id="${sys.id}" data-name="${sys.name}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>` : ''}
                                                    </div>
                                                    ` : ''}
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-cogs fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            `;
                        });
                    }
                    $('#systemsGrid').html(html);
                } else {
                    $('#systemsGrid').html('<div class="col-12"><div class="alert alert-danger">Error al cargar los sistemas.</div></div>');
                }
            },
            error: function() {
                $('#systemsGrid').html('<div class="col-12"><div class="alert alert-danger">Error de conexión.</div></div>');
            }
        });
    }

    function showDetails(sys) {
        function fieldRow(label, value, icon, colorClass) {
            if (!value) return '';
            return `
                <div class="form-group row align-items-center mb-2">
                    <label class="col-sm-4 col-form-label text-sm-right font-weight-bold ${colorClass}">
                        <i class="fas fa-${icon} fa-fw"></i> ${label}
                    </label>
                    <div class="col-sm-5">
                        <input type="text" class="form-control form-control-sm" value="${value}" readonly onclick="this.select()">
                    </div>
                    <div class="col-sm-3">
                        <button class="btn btn-sm btn-outline-secondary btn-copy" onclick="$(this).parent().prev().find('input').select(); navigator.clipboard.writeText('${value.replace(/'/g, "\\'")}'); var b=$(this); b.html('<i class=\\'fas fa-check\\'></i> Copiado').removeClass('btn-outline-secondary').addClass('btn-success'); setTimeout(function(){b.html('<i class=\\'fas fa-copy\\'></i> Copiar').removeClass('btn-success').addClass('btn-outline-secondary');},1500);">
                            <i class="fas fa-copy"></i> Copiar
                        </button>
                    </div>
                </div>`;
        }

        var html = '';
        html += '<div class="container-fluid">';
        html += '<h5 class="text-gray-800 mb-3"><i class="fas fa-cogs text-info mr-1"></i> ' + sys.name + '</h5>';
        html += fieldRow('Nombre', sys.name, 'tag', 'text-info');
        html += fieldRow('Dominio', sys.domain, 'globe', 'text-info');
        html += fieldRow('Alojado en', sys.hosted_at, 'server', 'text-info');
        html += '<hr class="my-2"><h6 class="text-info mb-2"><i class="fas fa-code-branch"></i> Credenciales</h6>';
        html += fieldRow('Cuenta GitHub', sys.github_account, 'code-branch', 'text-info');
        html += fieldRow('Usuario', sys.username, 'user', 'text-info');
        html += fieldRow('Contraseña', sys.password_decrypted, 'lock', 'text-info');
        if (sys.notes) {
            html += '<hr class="my-2"><div class="form-group row mb-0"><label class="col-sm-4 col-form-label text-sm-right text-muted"><i class="fas fa-sticky-note fa-fw"></i> Observaciones</label><div class="col-sm-8"><p class="form-control-plaintext">' + sys.notes + '</p></div></div>';
        }
        html += '</div>';

        $('#tituloDetalles').html('<i class="fas fa-cogs text-info mr-1"></i> ' + sys.name);
        $('#detallesBody').html(html);
        $('#verDetallesModal').modal('show');
    }

    loadSystems();

    $('#systemsGrid').on('click', '.btn-ver-detalles', function() {
        var sys = JSON.parse(decodeURIComponent($(this).data('sys')));
        showDetails(sys);
    });

    if (puedeCrear) {
        $('#btnNuevoSistema').on('click', function() {
            $('#formSistema')[0].reset();
            $('#edit_id').val('');
            $('#edit_password').attr('required', true);
            $('#tituloModalSistema').text('Nuevo Sistema Interno');
            $('#editarSistemaModal').modal('show');
        });
    } else {
        $('#btnNuevoSistema').hide();
    }

    if (puedeEditar) {
        $('#systemsGrid').on('click', '.btn-editar-sistema', function() {
            var sys = JSON.parse(decodeURIComponent($(this).data('sys')));
            $('#edit_id').val(sys.id);
            $('#edit_name').val(sys.name);
            $('#edit_domain').val(sys.domain || '');
            $('#edit_hosted_at').val(sys.hosted_at || '');
            $('#edit_github_account').val(sys.github_account || '');
            $('#edit_username').val(sys.username || '');
            $('#edit_password').val(sys.password_decrypted || '');
            $('#edit_password').attr('required', false);
            $('#edit_notes').val(sys.notes || '');
            $('#tituloModalSistema').text('Editar Sistema Interno');
            $('#editarSistemaModal').modal('show');
        });
    }

    if (puedeCrear || puedeEditar) {
        $('#formSistema').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'api/save_internal_system.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        $('#editarSistemaModal').modal('hide');
                        Swal.fire('Guardado!', 'Sistema interno guardado correctamente.', 'success');
                        loadSystems();
                    } else {
                        Swal.fire('Error', response.error, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error de comunicación', 'error');
                }
            });
        });
    }

    if (puedeEliminar) {
        $('#systemsGrid').on('click', '.btn-eliminar-sistema', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');

            Swal.fire({
                title: '¿Eliminar sistema?',
                text: "Se eliminará el sistema '" + name + "'",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'api/delete_internal_system.php',
                        type: 'POST',
                        data: { id: id, csrf_token: window.CSRF_TOKEN },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Eliminado!', 'Sistema interno eliminado.', 'success');
                                loadSystems();
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
    }
});
