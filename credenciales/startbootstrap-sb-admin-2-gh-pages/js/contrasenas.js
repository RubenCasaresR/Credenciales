$(document).ready(function() {
    var isAdmin = (window.USER_ROLE === 'Administrador');
    var puedeEditar = isAdmin || (window.PERMISOS && window.PERMISOS.contrasenas && window.PERMISOS.contrasenas.editar);
    var allServers = [];

    function escHtml(str) {
        return $('<span>').text(str || '').html();
    }

    function renderServers(servers) {
        var html = '';
        if (servers.length === 0) {
            html = '<div class="col-12"><div class="alert alert-info">No hay servidores registrados.</div></div>';
        } else {
            servers.forEach(function(server) {
                var providerText = server.provider ? `<p class="card-text small text-muted mb-1"><i class="fas fa-building fa-fw"></i> ${escHtml(server.provider)}</p>` : '';
                var ipText = server.ip_address ? `<p class="card-text small text-muted mb-1"><i class="fas fa-network-wired fa-fw"></i> ${escHtml(server.ip_address)}</p>` : '';

                html += `
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="h5 mb-2 font-weight-bold text-gray-800 text-truncate" title="${escHtml(server.name)}">
                                        ${escHtml(server.name)}
                                    </div>
                                    ${providerText}
                                    ${ipText}
                                    <div class="mt-3">
                                        <button class="btn btn-sm btn-primary shadow-sm w-100 btn-ver-credenciales" data-server-id="${server.id}" data-server-name="${escHtml(server.name)}">
                                            <i class="fas fa-key fa-sm text-white-50"></i> Ver Contraseñas
                                        </button>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-lock fa-2x text-gray-300"></i>
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

    function loadServers() {
        $.ajax({
            url: 'api/get_contrasenas.php',
            type: 'GET',
            success: function(res) {
                if (res.success) {
                    allServers = res.data;
                    renderServers(allServers);
                } else {
                    $('#serversGrid').html('<div class="col-12"><div class="alert alert-danger">Error al cargar servidores.</div></div>');
                }
            },
            error: function() {
                $('#serversGrid').html('<div class="col-12"><div class="alert alert-danger">Error de conexión.</div></div>');
            }
        });
    }

    function renderCredentialsInModal(credentials, serverName) {
        var html = '';

        if (!credentials.length) {
            html = '<div class="alert alert-info">No hay credenciales registradas para este servidor.</div>';
        } else {
            credentials.forEach(function(cred) {
                var badgeColor = 'badge-primary';
                if (cred.type === 'Hosting') badgeColor = 'badge-primary';
                else if (cred.type === 'Dominio') badgeColor = 'badge-success';
                else if (cred.type === 'Correo') badgeColor = 'badge-warning';
                else if (cred.type === 'Google Workspace') badgeColor = 'badge-info';
                else if (cred.type === 'Base de Datos') badgeColor = 'badge-secondary';
                else if (cred.type === 'FTP') badgeColor = 'badge-dark';
                else if (cred.type === 'Autenticador') badgeColor = 'badge-info';

                html += `
                <div class="card shadow mb-3 credencial-item" data-cred-id="${cred.id}">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <span class="badge ${badgeColor} mr-2">${escHtml(cred.type || 'Otro')}</span>
                            ${escHtml(cred.service_name)}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <small class="text-muted d-block">Usuario</small>
                                <div class="input-group input-group-sm" style="flex-wrap:nowrap;">
                                    <input type="text" class="form-control form-control-sm bg-white" value="${escHtml(cred.username || '')}" readonly style="font-family:monospace;font-size:0.8rem;">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary btn-sm btn-copy-field" type="button" title="Copiar"><i class="fas fa-copy"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <small class="text-muted d-block">Contraseña</small>
                                <div class="input-group input-group-sm" style="flex-wrap:nowrap;">
                                    <input type="password" class="form-control form-control-sm bg-white password-display" value="${escHtml(cred.password || '')}" readonly style="font-family:monospace;font-size:0.8rem;">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary btn-sm toggle-password-display" type="button" title="Mostrar/Ocultar"><i class="fas fa-eye"></i></button>
                                        <button class="btn btn-outline-secondary btn-sm btn-copy-field" type="button" title="Copiar"><i class="fas fa-copy"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        ${puedeEditar ? `
                        <hr>
                        <div class="row align-items-end">
                            <div class="col-md-6 mb-2 mb-md-0">
                                <small class="text-muted d-block">Actualizar Contraseña</small>
                                <div class="input-group input-group-sm" style="flex-wrap:nowrap;">
                                    <input type="text" class="form-control form-control-sm nueva-password" placeholder="Nueva contraseña">
                                    <div class="input-group-append">
                                        <button class="btn btn-success btn-sm btn-guardar-password" type="button"><i class="fas fa-save"></i> Guardar</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-info btn-sm btn-historial" type="button" data-cred-id="${cred.id}" data-cred-name="${escHtml(cred.service_name)}">
                                    <i class="fas fa-history"></i> Historial
                                </button>
                            </div>
                        </div>
                        ` : `
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <button class="btn btn-info btn-sm btn-historial" type="button" data-cred-id="${cred.id}" data-cred-name="${escHtml(cred.service_name)}">
                                    <i class="fas fa-history"></i> Historial
                                </button>
                            </div>
                        </div>
                        `}
                    </div>
                </div>
                `;
            });
        }

        $('#tituloModalCredenciales').text('Credenciales - ' + serverName);
        $('#modalCredencialesBody').html(html);
    }

    $(document).on('click', '.btn-ver-credenciales', function() {
        var serverId = $(this).data('server-id');
        var serverName = $(this).data('server-name');

        $('#modalCredencialesBody').html('<div class="text-center py-5"><i class="fas fa-spinner fa-pulse fa-3x mb-3"></i><p>Cargando credenciales...</p></div>');
        $('#verCredencialesModal').modal('show');

        $.ajax({
            url: 'api/get_credentials.php',
            type: 'GET',
            data: { server_id: serverId },
            success: function(res) {
                var credentials = (res.data || []).filter(function(c) { return c.type === 'Hosting'; });
                renderCredentialsInModal(credentials, serverName);
            },
            error: function() {
                $('#modalCredencialesBody').html('<div class="alert alert-danger">Error al cargar credenciales.</div>');
            }
        });
    });

    $(document).on('click', '.toggle-password-display', function() {
        var input = $(this).closest('.input-group').find('.password-display');
        var icon = $(this).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    $(document).on('click', '.btn-copy-field', function() {
        var input = $(this).closest('.input-group').find('input');
        input.select();
        document.execCommand('copy');
        var icon = $(this).find('i');
        icon.removeClass('fa-copy').addClass('fa-check');
        setTimeout(function() { icon.removeClass('fa-check').addClass('fa-copy'); }, 1500);
    });

    $(document).on('click', '.btn-guardar-password', function() {
        var $card = $(this).closest('.credencial-item');
        var credId = $card.data('cred-id');
        var $input = $card.find('.nueva-password');
        var newPassword = $input.val().trim();

        if (!newPassword) {
            Swal.fire('Error', 'Debes escribir una nueva contraseña.', 'error');
            return;
        }

        if (newPassword.length < 6) {
            Swal.fire('Error', 'La contraseña debe tener al menos 6 caracteres.', 'error');
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-pulse"></i>');

        $.ajax({
            url: 'api/update_credential_password.php',
            type: 'POST',
            data: {
                credential_id: credId,
                new_password: newPassword,
                csrf_token: window.CSRF_TOKEN
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Actualizado!', 'Contraseña actualizada correctamente.', 'success');
                    $input.val('');
                    var $passDisplay = $card.find('.password-display');
                    $passDisplay.val(newPassword);
                } else {
                    Swal.fire('Error', response.error, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error de comunicación con el servidor', 'error');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
            }
        });
    });

    $(document).on('click', '.btn-historial', function() {
        var credId = $(this).data('cred-id');
        var credName = $(this).data('cred-name');

        $('#tituloHistorial').text('Historial de Contraseñas - ' + credName);
        $('#historialContent').html('<div class="text-center py-5"><i class="fas fa-spinner fa-pulse fa-3x mb-3"></i><p>Cargando historial...</p></div>');
        $('#historialPasswordModal').modal('show');

        $.ajax({
            url: 'api/get_password_history.php',
            type: 'GET',
            data: { credential_id: credId },
            success: function(res) {
                if (res.success) {
                    var data = res.data || [];
                    var html = '';

                    var currentPass = res.current_password || '';
                    html += `
                    <div class="card border-left-success shadow mb-4">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-lock text-success mr-2"></i>
                                <strong class="text-success">Contraseña Actual</strong>
                            </div>
                            <div class="input-group input-group-sm" style="flex-wrap:nowrap;">
                                <input type="password" class="form-control form-control-sm bg-white password-display" value="${escHtml(currentPass)}" readonly style="font-family:monospace;font-size:0.9rem;">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-success btn-sm toggle-password-display" type="button" title="Mostrar/Ocultar"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-outline-success btn-sm btn-copy-field" type="button" title="Copiar"><i class="fas fa-copy"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>`;

                    if (!data.length) {
                        html += '<div class="alert alert-info">No hay historial de cambios anteriores para esta credencial.</div>';
                    } else {
                        html += '<div class="table-responsive"><table class="table table-bordered table-hover"><thead class="thead-light"><tr><th>#</th><th>Fecha y Hora</th><th>Contraseña</th><th>Cambiado por</th></tr></thead><tbody>';
                        data.forEach(function(entry, index) {
                            html += `<tr>
                                <td>${index + 1}</td>
                                <td>${escHtml(entry.created_at)}</td>
                                <td><input type="text" class="form-control form-control-sm bg-white" value="${escHtml(entry.password)}" readonly style="font-family:monospace;font-size:0.8rem;max-width:220px;"></td>
                                <td>${escHtml(entry.changed_by_name || 'N/A')}</td>
                            </tr>`;
                        });
                        html += '</tbody></table></div>';
                    }

                    $('#historialContent').html(html);
                } else {
                    $('#historialContent').html('<div class="alert alert-danger">' + (res.error || 'Error al cargar historial.') + '</div>');
                }
            },
            error: function() {
                $('#historialContent').html('<div class="alert alert-danger">Error de conexión.</div>');
            }
        });
    });

    loadServers();
});
