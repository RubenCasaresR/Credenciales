$(document).ready(function() {
    var isAdmin = (window.USER_ROLE === 'Administrador');
    var puedeCrear = isAdmin || (window.PERMISOS && window.PERMISOS.servidores && window.PERMISOS.servidores.crear);
    var puedeEditar = isAdmin || (window.PERMISOS && window.PERMISOS.servidores && window.PERMISOS.servidores.editar);
    var puedeEliminar = isAdmin || (window.PERMISOS && window.PERMISOS.servidores && window.PERMISOS.servidores.eliminar);
    var allServers = [];

    function renderServers(servers) {
        var html = '';
        if (servers.length === 0) {
            html = '<div class="col-12"><div class="alert alert-info">No hay servidores registrados.</div></div>';
        } else {
            servers.forEach(function(server) {
                var icon = 'fa-server';
                var providerText = server.provider ? `<p class="card-text small text-muted mb-1"><i class="fas fa-building fa-fw"></i> ${server.provider}</p>` : '';
                var ipText = server.ip_address ? `<p class="card-text small text-muted mb-1"><i class="fas fa-network-wired fa-fw"></i> ${server.ip_address}</p>` : '';
                var authText = server.autenticador_responsible ? '<p class="card-text small text-muted mb-1"><i class="fas fa-shield-alt fa-fw"></i> Autenticador Google: ' + $('<span>').text(server.autenticador_responsible).html() + '</p>' : '';
                
                html += `
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="h5 mb-2 font-weight-bold text-gray-800 text-truncate" title="${server.name}">
                                        ${server.name}
                                    </div>
                                    ${providerText}
                                    ${ipText}
                                    ${authText}
                                    <div class="mt-3">
                                        <a href="servidor_detalle.php?id=${server.id}" class="btn btn-sm btn-primary shadow-sm w-100 mb-2">
                                            <i class="fas fa-eye fa-sm text-white-50"></i> Ver Credenciales
                                        </a>
                                        ${(puedeEditar || puedeEliminar) ? `
                                        <div class="d-flex justify-content-between">
                                            ${puedeEditar ? `<button class="btn btn-sm btn-outline-secondary btn-editar-servidor flex-fill mr-1" data-server='${JSON.stringify(server)}'>
                                                <i class="fas fa-edit"></i>
                                            </button>` : ''}
                                            ${puedeEliminar ? `<button class="btn btn-sm btn-outline-danger btn-eliminar-servidor flex-fill ml-1" data-id="${server.id}" data-name="${server.name}">
                                                <i class="fas fa-trash"></i>
                                            </button>` : ''}
                                        </div>
                                        ` : ''}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas ${icon} fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                `;
            });
        }
        $('#serversGrid').html(html);
    }

    function sortServers() {
        var criteria = $('#sortServers').val();
        console.log('Sorting by:', criteria, 'Total:', allServers.length);
        var sorted = allServers.slice();

        switch (criteria) {
            case 'name_asc':
                sorted.sort(function(a, b) { return (a.name || '').localeCompare(b.name || ''); });
                break;
            case 'name_desc':
                sorted.sort(function(a, b) { return (b.name || '').localeCompare(a.name || ''); });
                break;
            case 'provider_asc':
                sorted.sort(function(a, b) { return (a.provider || '').localeCompare(b.provider || ''); });
                break;
            case 'provider_desc':
                sorted.sort(function(a, b) { return (b.provider || '').localeCompare(a.provider || ''); });
                break;
            case 'ip':
                sorted.sort(function(a, b) { return (a.ip_address || '').localeCompare(b.ip_address || ''); });
                break;
            case 'date_asc':
                sorted.reverse();
                break;
            case 'date_desc':
            default:
                break;
        }

        renderServers(sorted);
    }

    function loadServers() {
        $.ajax({
            url: 'api/get_servers.php',
            type: 'GET',
            success: function(res) {
                if (res.success) {
                    allServers = res.data;
                    console.log('Servers loaded:', allServers.length, allServers);
                    sortServers();
                } else {
                    $('#serversGrid').html('<div class="col-12"><div class="alert alert-danger">Error al cargar servidores.</div></div>');
                }
            },
            error: function() {
                $('#serversGrid').html('<div class="col-12"><div class="alert alert-danger">Error de conexión.</div></div>');
            }
        });
    }

    loadServers();

    $('#sortServers').on('change', function() {
        sortServers();
    });

    if (puedeCrear) {
        $('#btnNuevoServidor').on('click', function() {
            $('#formServidor')[0].reset();
            $('#edit_server_id').val('');
            $('#tituloModalServidor').text('Nuevo Servidor');
            $('#editarServidorModal').modal('show');
        });
    } else {
        $('#btnNuevoServidor').hide();
    }

    if (puedeEditar) {
        $('#serversGrid').on('click', '.btn-editar-servidor', function() {
            var server = $(this).data('server');
            $('#edit_server_id').val(server.id);
            $('#edit_server_name').val(server.name);
            $('#edit_server_provider').val(server.provider);
            $('#edit_server_ip').val(server.ip_address);
            $('#edit_server_notes').val(server.notes);
            $('#tituloModalServidor').text('Editar Servidor');
            $('#editarServidorModal').modal('show');
        });
    }

    if (puedeCrear || puedeEditar) {
        $('#formServidor').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'api/save_server.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        $('#editarServidorModal').modal('hide');
                        Swal.fire('Guardado!', 'Servidor guardado correctamente.', 'success');
                        loadServers();
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
        $('#serversGrid').on('click', '.btn-eliminar-servidor', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            
            Swal.fire({
                title: '¿Eliminar servidor?',
                text: "Se eliminará el servidor '" + name + "' y todas las credenciales asociadas.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'api/delete_server.php',
                        type: 'POST',
                        data: { id: id, csrf_token: window.CSRF_TOKEN },
                        success: function(response) {
                            if(response.success) {
                                Swal.fire('Eliminado!', 'Servidor eliminado.', 'success');
                                loadServers();
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
