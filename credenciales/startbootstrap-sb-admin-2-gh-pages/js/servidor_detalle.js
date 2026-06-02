$(document).ready(function() {
    var isAdmin = (window.USER_ROLE === 'Administrador');
    var puedeCrear = isAdmin || (window.PERMISOS && window.PERMISOS.credenciales && window.PERMISOS.credenciales.crear);
    var puedeEditar = isAdmin || (window.PERMISOS && window.PERMISOS.credenciales && window.PERMISOS.credenciales.editar);
    var puedeEliminar = isAdmin || (window.PERMISOS && window.PERMISOS.credenciales && window.PERMISOS.credenciales.eliminar);
    var serverId = window.SERVER_ID;
    var allCredentials = [];
    var selectedTipo = null;

    var tipoConfig = {
        'Hosting':          { color: 'primary',   icon: 'fa-server' },
        'Dominio':          { color: 'success',   icon: 'fa-globe' },
        'Correo':           { color: 'warning',   icon: 'fa-envelope' },
        'Google Workspace': { color: 'info',      icon: 'fa-google' },
        'Base de Datos':    { color: 'secondary', icon: 'fa-database' },
        'FTP':              { color: 'dark',      icon: 'fa-folder' },
        'Autenticador':     { color: 'info',      icon: 'fa-shield-alt' },
        'Otro':             { color: 'light',     icon: 'fa-tag' }
    };

    var tiposConPago = ['Hosting', 'Dominio', 'Otro'];

    function getCredentialById(id) {
        for (var i = 0; i < allCredentials.length; i++) {
            if (allCredentials[i].id == id) return allCredentials[i];
        }
        return null;
    }

    function cargarCredenciales() {
        $.getJSON('api/get_credentials.php?server_id=' + serverId, function(r) {
            allCredentials = r.data || [];
            renderTipoCards();
            renderCredencialCards(selectedTipo);
        });
    }

    function getTipoCounts() {
        var counts = {};
        allCredentials.forEach(function(c) {
            var tipo = c.type || 'Otro';
            counts[tipo] = (counts[tipo] || 0) + 1;
        });
        return counts;
    }

    function renderTipoCards() {
        var counts = getTipoCounts();
        var total = allCredentials.length;
        var html = '';

        html += tipoCardHtml('Todos', total, 'primary', 'fa-th-list', selectedTipo === null);

        var tiposOrdenados = ['Hosting', 'Dominio', 'Correo', 'Google Workspace', 'Base de Datos', 'FTP', 'Autenticador', 'Otro'];
        tiposOrdenados.forEach(function(tipo) {
            if (counts[tipo]) {
                var cfg = tipoConfig[tipo] || { color: 'light', icon: 'fa-tag' };
                html += tipoCardHtml(tipo, counts[tipo], cfg.color, cfg.icon, selectedTipo === tipo);
            }
        });

        $('#tipoCards').html(html);
    }

    function tipoCardHtml(tipo, count, color, icon, active) {
        var cls = active ? 'shadow-sm border-left-' + color : 'border-left-' + color;
        return '<div class="col-xl-2 col-md-4 mb-3 tipo-card" data-tipo="' + tipo + '" style="cursor:pointer;">' +
            '<div class="card ' + cls + ' h-100 py-2">' +
                '<div class="card-body">' +
                    '<div class="row no-gutters align-items-center">' +
                        '<div class="col mr-2">' +
                            '<div class="text-xs font-weight-bold text-' + color + ' text-uppercase mb-1">' + tipo + '</div>' +
                            '<div class="h5 mb-0 font-weight-bold text-gray-800">' + count + '</div>' +
                        '</div>' +
                        '<div class="col-auto">' +
                            '<i class="fas ' + icon + ' fa-2x text-gray-300"></i>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    function escHtml(str) {
        return $('<span>').text(str || '').html();
    }

    function renderCredencialCards(tipo) {
        var filtered = tipo ? allCredentials.filter(function(c) { return c.type === tipo; }) : allCredentials;
        var container = $('#credencialCards');

        if (!filtered.length) {
            container.html('<div class="col-12"><div class="card shadow mb-4"><div class="card-body text-center text-muted py-5"><i class="fas fa-inbox fa-3x mb-3"></i><p class="mb-0">' + (tipo ? 'No hay credenciales de tipo "' + tipo + '".' : 'No hay credenciales registradas para este servidor.') + '</p></div></div></div>');
            return;
        }

        var html = '';
        filtered.forEach(function(c) {
            var cfg = tipoConfig[c.type] || { color: 'primary', icon: 'fa-key' };
            var badgeColor = 'badge-' + cfg.color;

            html += '<div class="col-lg-6 mb-4">' +
                '<div class="card shadow h-100 credencial-card" data-id="' + c.id + '">' +
                    '<div class="card-header py-3 d-flex justify-content-between align-items-center">' +
                        '<h6 class="m-0 font-weight-bold text-' + cfg.color + '">' +
                            '<span class="badge ' + badgeColor + ' mr-2">' + (c.type || 'Otro') + '</span>' +
                            escHtml(c.service_name) +
                        '</h6>' +
                    '</div>' +
                    '<div class="card-body">' +
                        (c.type !== 'Dominio' && c.type !== 'Autenticador' ? '<div class="row">' +
                            '<div class="col-md-6 mb-2">' +
                                '<small class="text-muted d-block">Usuario</small>' +
                                (c.username ? '<div class="input-group input-group-sm" style="flex-wrap:nowrap;">' +
                                    '<input type="text" class="form-control form-control-sm bg-white" value="' + escHtml(c.username) + '" readonly style="max-width:160px;font-family:monospace;font-size:0.8rem;">' +
                                    '<div class="input-group-append">' +
                                        '<button class="btn btn-outline-secondary btn-sm btn-copy-field" type="button" title="Copiar"><i class="fas fa-copy"></i></button>' +
                                    '</div></div>'
                                : '<span>-</span>') +
                            '</div>' +
                            '<div class="col-md-6 mb-2">' +
                                '<small class="text-muted d-block">Contraseña</small>' +
                                (puedeEditar || isAdmin ?
                                    '<div class="input-group input-group-sm" style="flex-wrap:nowrap;">' +
                                        '<input type="password" class="form-control form-control-sm bg-white password-cell" value="' + escHtml(c.password || '') + '" readonly style="max-width:160px;font-family:monospace;font-size:0.8rem;">' +
                                        '<div class="input-group-append">' +
                                            '<button class="btn btn-outline-secondary btn-sm toggle-password-cell" type="button" title="Mostrar/Ocultar"><i class="fas fa-eye"></i></button>' +
                                            '<button class="btn btn-outline-secondary btn-sm btn-copy-field" type="button" title="Copiar"><i class="fas fa-copy"></i></button>' +
                                        '</div></div>'
                                    : '<span>********</span>') +
                            '</div>' +
                        '</div>' : '') +
                        (c.type !== 'Autenticador' ? '<div class="row">' +
                            '<div class="col-md-6 mb-2">' +
                                '<small class="text-muted d-block">' + (c.type === 'Base de Datos' || c.type === 'FTP' ? 'Host' : 'URL') + '</small>' +
                                (c.url ? '<a href="' + escHtml(c.url) + '" target="_blank">' + escHtml(c.url) + '</a>' : '<span class="text-muted">-</span>') +
                            '</div>' +
                            '<div class="col-md-6 mb-2">' +
                                '<small class="text-muted d-block">Fecha Pago</small>' +
                                '<span>' + (c.payment_date || '-') + '</span>' +
                            '</div>' +
                        '</div>' : '') +
                        (c.db_name ? '<div class="row"><div class="col-12 mb-2"><small class="text-muted d-block">Nombre de Base de Datos</small><span>' + escHtml(c.db_name) + '</span></div></div>' : '') +
                        (c.port ? '<div class="row"><div class="col-12 mb-2"><small class="text-muted d-block">Puerto</small><span>' + escHtml(c.port) + '</span></div></div>' : '') +
                        (c.domain_name ? '<div class="row"><div class="col-12 mb-2"><small class="text-muted d-block">Nombre de Dominio</small><div class="input-group input-group-sm" style="flex-wrap:nowrap;max-width:300px;"><input type="text" class="form-control form-control-sm bg-white" value="' + escHtml(c.domain_name) + '" readonly><div class="input-group-append"><button class="btn btn-outline-secondary btn-sm btn-copy-domain" type="button" title="Copiar"><i class="fas fa-copy"></i></button></div></div></div></div>' : '') +
                        (c.type === 'Autenticador' ? '<div class="row"><div class="col-12 mb-2"><small class="text-muted d-block">Responsable</small><span>' + escHtml(c.responsible || '-') + '</span></div></div>' : '') +
                        (c.type === 'Autenticador' ? '<div class="row"><div class="col-12 mb-2"><small class="text-muted d-block">Código de Recuperación</small><span>' + escHtml(c.recovery_code || '-') + '</span></div></div>' : '') +
                        '<div class="row">' +
                            '<div class="col-12">' +
                                '<small class="text-muted d-block">Notas</small>' +
                                '<span>' + escHtml(c.notes || 'Ninguna') + '</span>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="card-footer bg-transparent d-flex justify-content-end">' +
                        '<button class="btn btn-info btn-sm btn-ver mr-1"><i class="fas fa-eye"></i> Ver</button>' +
                        (puedeEditar ? '<button class="btn btn-primary btn-sm btn-editar mr-1"><i class="fas fa-edit"></i> Editar</button>' : '') +
                        (puedeEliminar ? '<button class="btn btn-danger btn-sm btn-eliminar"><i class="fas fa-trash"></i> Eliminar</button>' : '') +
                    '</div>' +
                '</div>' +
            '</div>';
        });

        container.html(html);
    }

    $(document).on('click', '.tipo-card', function() {
        var tipo = $(this).data('tipo');
        selectedTipo = tipo === 'Todos' ? null : tipo;
        renderTipoCards();
        renderCredencialCards(selectedTipo);
    });

    if (puedeCrear) {
        $('#btnNuevaCredencial').on('click', function() {
            $('#formCredencial')[0].reset();
            $('#edit_id').val('');
            $('#tituloModalCredencial').text('Nueva Credencial');
            actualizarCamposPorTipo('');
            $('#editarCredencialModal').modal('show');
        });
    } else {
        $('#btnNuevaCredencial').hide();
    }

    function actualizarCamposPorTipo(tipo) {
        var isAutenticador = tipo === 'Autenticador';
        var isDB = tipo === 'Base de Datos';
        var isFTP = tipo === 'FTP';
        var isDominio = tipo === 'Dominio';
        var showPago = !tipo || tiposConPago.indexOf(tipo) !== -1;
        var showHost = isDB || isFTP;
        var showUsuarioPass = !isDominio && !isAutenticador;

        $('#field_usuario').toggle(showUsuarioPass);
        $('#field_password').toggle(showUsuarioPass);
        $('#field_pago').toggle(!isAutenticador && showPago);
        $('#field_db_name').toggle(!isAutenticador && isDB);
        $('#field_port').toggle(!isAutenticador && isFTP);
        $('#field_domain_name').toggle(!isAutenticador && isDominio);
        $('#field_url').toggle(!isAutenticador);
        $('#field_responsable').toggle(isAutenticador);
        $('#field_recovery_code').toggle(isAutenticador);

        if (showHost && !isAutenticador) {
            $('#url_label').text('Host');
            $('#edit_url').attr('placeholder', 'Ej. mysql.dominio.com');
        } else {
            $('#url_label').text('URL de Acceso');
            $('#edit_url').attr('placeholder', '');
        }
    }

    function actualizarCamposVerPorTipo(tipo) {
        var isAutenticador = tipo === 'Autenticador';
        var isDominio = tipo === 'Dominio';
        var showUsuarioPass = !isAutenticador && !isDominio;
        var showPago = tiposConPago.indexOf(tipo) !== -1;

        $('#ver_field_url').toggle(!isAutenticador);
        $('#ver_field_usuario_pass').toggle(showUsuarioPass);
        $('#ver_field_pago').toggle(showPago);
        $('#ver_field_autenticador').toggle(isAutenticador);
    }

    $('#edit_tipo').on('change', function() {
        actualizarCamposPorTipo($(this).val());
    });

    $(document).on('click', '.btn-ver', function() {
        var id = $(this).closest('.credencial-card').data('id');
        $.ajax({
            url: 'api/get_credential.php',
            type: 'GET',
            data: { id: id },
            success: function(res) {
                if(res.success) {
                    var data = res.data;
                    $('#ver_servicio').text(data.service_name);
                    $('#ver_tipo').text(data.type);
                    $('#ver_url').text(data.url || 'No URL');
                    $('#ver_url_link').attr('href', data.url || '#');
                    $('#ver_usuario').text(data.username || 'N/A');
                    $('#ver_password').val(data.password);
                    $('#ver_pago').text(data.payment_date || 'N/A');
                    $('#ver_responsable').text(data.responsible || 'N/A');
                    $('#ver_recovery_code').text(data.recovery_code || 'N/A');
                    $('#ver_notas').text(data.notes || 'Ninguna');
                    actualizarCamposVerPorTipo(data.type);
                    $('#verCredencialModal').modal('show');
                } else {
                    Swal.fire('Error', res.error, 'error');
                }
            }
        });
    });

    if (puedeEditar) {
        $(document).on('click', '.btn-editar', function() {
            var id = $(this).closest('.credencial-card').data('id');
            $.ajax({
                url: 'api/get_credential.php',
                type: 'GET',
                data: { id: id },
                success: function(res) {
                    if(res.success) {
                        var data = res.data;
                        $('#tituloModalCredencial').text('Editar Credencial');
                        $('#edit_id').val(data.id);
                        $('#edit_servicio').val(data.service_name);
                        $('#edit_tipo').val(data.type);
                        actualizarCamposPorTipo(data.type);
                        $('#edit_url').val(data.url);
                        $('#edit_usuario').val(data.username);
                        $('#edit_password').val(data.password);
                        $('#edit_db_name').val(data.db_name);
                        $('#edit_port').val(data.port);
                        $('#edit_domain_name').val(data.domain_name);
                        $('#edit_responsable').val(data.responsible);
                        $('#edit_recovery_code').val(data.recovery_code);
                        $('#edit_pago').val(data.payment_date);
                        $('#edit_notas').val(data.notes);
                        $('#editarCredencialModal').modal('show');
                    } else {
                        Swal.fire('Error', res.error, 'error');
                    }
                }
            });
        });
    }

    if (puedeCrear || puedeEditar) {
        $('#formCredencial').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'api/save_credential.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if(response.success) {
                        $('#editarCredencialModal').modal('hide');
                        Swal.fire('Guardado!', 'La credencial se ha guardado correctamente.', 'success');
                        cargarCredenciales();
                    } else {
                        Swal.fire('Error', response.error, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error de comunicación con el servidor', 'error');
                }
            });
        });
    }

    if (puedeEliminar) {
        $(document).on('click', '.btn-eliminar', function() {
            var id = $(this).closest('.credencial-card').data('id');
            var cred = getCredentialById(id);
            Swal.fire({
                title: '¿Eliminar credencial?',
                text: "El servicio '" + (cred ? cred.service_name : '') + "' será eliminado.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'api/delete_credential.php',
                        type: 'POST',
                        data: { id: id, csrf_token: window.CSRF_TOKEN },
                        success: function(response) {
                            if(response.success) {
                                Swal.fire('Eliminado!', 'Credencial eliminada.', 'success');
                                cargarCredenciales();
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

    $(document).on('click', '#btnCopyDomain', function() {
        var input = $('#edit_domain_name');
        input.select();
        document.execCommand('copy');
        Swal.fire({ title: 'Copiado!', text: 'Dominio copiado al portapapeles.', icon: 'success', timer: 1500, showConfirmButton: false });
    });

    $(document).on('click', '.btn-copy-domain', function() {
        var input = $(this).closest('.input-group').find('input');
        input.select();
        document.execCommand('copy');
        var icon = $(this).find('i');
        icon.removeClass('fa-copy').addClass('fa-check');
        setTimeout(function() { icon.removeClass('fa-check').addClass('fa-copy'); }, 1500);
    });

    $(document).on('click', '.btn-copy-field', function() {
        var input = $(this).closest('.input-group').find('input');
        input.select();
        document.execCommand('copy');
        var icon = $(this).find('i');
        icon.removeClass('fa-copy').addClass('fa-check');
        setTimeout(function() { icon.removeClass('fa-check').addClass('fa-copy'); }, 1500);
    });

    $(document).on('click', '.toggle-password-cell', function() {
        var input = $(this).closest('.input-group').find('.password-cell');
        var icon = $(this).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    cargarCredenciales();

    if (window.CREDENTIAL_ID) {
        setTimeout(function() {
            var btn = $('.credencial-card[data-id="' + window.CREDENTIAL_ID + '"] .btn-ver');
            if (btn.length) {
                btn.click();
                $('html, body').animate({
                    scrollTop: btn.closest('.credencial-card').offset().top - 100
                }, 500);
            } else {
                $.ajax({
                    url: 'api/get_credential.php',
                    type: 'GET',
                    data: { id: window.CREDENTIAL_ID },
                    success: function(res) {
                        if (res.success) {
                            var data = res.data;
                            $('#ver_servicio').text(data.service_name);
                            $('#ver_tipo').text(data.type);
                            $('#ver_url').text(data.url || 'No URL');
                            $('#ver_url_link').attr('href', data.url || '#');
                            $('#ver_usuario').text(data.username || 'N/A');
                            $('#ver_password').val(data.password);
                            $('#ver_pago').text(data.payment_date || 'N/A');
                            $('#ver_responsable').text(data.responsible || 'N/A');
                            $('#ver_recovery_code').text(data.recovery_code || 'N/A');
                            $('#ver_notas').text(data.notes || 'Ninguna');
                            actualizarCamposVerPorTipo(data.type);
                            $('#verCredencialModal').modal('show');
                        }
                    }
                });
            }
        }, 500);
    }
});