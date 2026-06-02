$(document).ready(function() {
    var isAdmin = (window.USER_ROLE === 'Administrador');
    var puedeCrear = isAdmin || (window.PERMISOS && window.PERMISOS.internet && window.PERMISOS.internet.crear);
    var puedeEditar = isAdmin || (window.PERMISOS && window.PERMISOS.internet && window.PERMISOS.internet.editar);
    var puedeEliminar = isAdmin || (window.PERMISOS && window.PERMISOS.internet && window.PERMISOS.internet.eliminar);

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

    function loadNetworks() {
        $.ajax({
            url: 'api/get_internet.php',
            type: 'GET',
            success: function(res) {
                if (res.success) {
                    var html = '';
                    if (res.data.length === 0) {
                        html = '<div class="col-12"><div class="alert alert-info">No hay redes de internet registradas.</div></div>';
                    } else {
                        res.data.forEach(function(net) {
                            var safeNet = encodeURIComponent(JSON.stringify(net));

                            html += `
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-left-primary shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="h5 mb-2 font-weight-bold text-gray-800 text-truncate" title="${net.name}">
                                                    <i class="fas fa-wifi fa-sm text-primary mr-1"></i> ${net.name}
                                                </div>
                                                <p class="card-text small text-muted mb-1"><i class="fas fa-signal fa-fw text-primary"></i> ${net.ssid || 'SSID no especificado'}</p>
                                                ${net.provider ? '<p class="card-text small text-muted mb-1"><i class="fas fa-building fa-fw text-primary"></i> ' + net.provider + '</p>' : ''}
                                                <div class="mt-2">
                                                    <button class="btn btn-sm btn-primary w-100 mb-2 btn-ver-detalles" data-net='${safeNet}'>
                                                        <i class="fas fa-eye"></i> Ver detalles
                                                    </button>
                                                    ${(puedeEditar || puedeEliminar) ? `
                                                    <div class="d-flex justify-content-between">
                                                        ${puedeEditar ? `<button class="btn btn-sm btn-outline-secondary btn-editar-red flex-fill mr-1" data-net='${safeNet}'>
                                                            <i class="fas fa-edit"></i>
                                                        </button>` : ''}
                                                        ${puedeEliminar ? `<button class="btn btn-sm btn-outline-danger btn-eliminar-red flex-fill ml-1" data-id="${net.id}" data-name="${net.name}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>` : ''}
                                                    </div>
                                                    ` : ''}
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-wifi fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            `;
                        });
                    }
                    $('#internetGrid').html(html);
                } else {
                    $('#internetGrid').html('<div class="col-12"><div class="alert alert-danger">Error al cargar las redes.</div></div>');
                }
            },
            error: function() {
                $('#internetGrid').html('<div class="col-12"><div class="alert alert-danger">Error de conexión.</div></div>');
            }
        });
    }

    function showDetails(net) {
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
        html += '<h5 class="text-gray-800 mb-3"><i class="fas fa-wifi text-primary mr-1"></i> ' + net.name + '</h5>';
        html += fieldRow('Nombre', net.name, 'tag', 'text-primary');
        html += fieldRow('SSID', net.ssid, 'signal', 'text-primary');
        html += fieldRow('Contraseña', net.password_decrypted, 'lock', 'text-primary');
        html += fieldRow('Proveedor', net.provider, 'building', 'text-primary');
        html += fieldRow('IP del Router', net.ip_address, 'network-wired', 'text-primary');
        if (net.notes) {
            html += '<hr class="my-2"><div class="form-group row mb-0"><label class="col-sm-4 col-form-label text-sm-right text-muted"><i class="fas fa-sticky-note fa-fw"></i> Notas</label><div class="col-sm-8"><p class="form-control-plaintext">' + net.notes + '</p></div></div>';
        }
        html += '</div>';

        $('#tituloDetalles').html('<i class="fas fa-wifi text-primary mr-1"></i> ' + net.name);
        $('#detallesBody').html(html);
        $('#verDetallesModal').modal('show');
    }

    loadNetworks();

    $('#internetGrid').on('click', '.btn-ver-detalles', function() {
        var net = JSON.parse(decodeURIComponent($(this).data('net')));
        showDetails(net);
    });

    if (puedeCrear) {
        $('#btnNuevaRed').on('click', function() {
            $('#formRed')[0].reset();
            $('#edit_id').val('');
            $('#edit_password').attr('required', true);
            $('#tituloModalRed').text('Nueva Red de Internet');
            $('#editarRedModal').modal('show');
        });
    } else {
        $('#btnNuevaRed').hide();
    }

    if (puedeEditar) {
        $('#internetGrid').on('click', '.btn-editar-red', function() {
            var net = JSON.parse(decodeURIComponent($(this).data('net')));
            $('#edit_id').val(net.id);
            $('#edit_name').val(net.name);
            $('#edit_ssid').val(net.ssid || '');
            $('#edit_password').val(net.password_decrypted || '');
            $('#edit_password').attr('required', false);
            $('#edit_provider').val(net.provider || '');
            $('#edit_ip').val(net.ip_address || '');
            $('#edit_notes').val(net.notes || '');
            $('#tituloModalRed').text('Editar Red de Internet');
            $('#editarRedModal').modal('show');
        });
    }

    if (puedeCrear || puedeEditar) {
        $('#formRed').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'api/save_internet.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        $('#editarRedModal').modal('hide');
                        Swal.fire('Guardado!', 'Red de internet guardada correctamente.', 'success');
                        loadNetworks();
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
        $('#internetGrid').on('click', '.btn-eliminar-red', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');

            Swal.fire({
                title: '¿Eliminar red?',
                text: "Se eliminará la red '" + name + "'",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'api/delete_internet.php',
                        type: 'POST',
                        data: { id: id, csrf_token: window.CSRF_TOKEN },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Eliminado!', 'Red de internet eliminada.', 'success');
                                loadNetworks();
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
