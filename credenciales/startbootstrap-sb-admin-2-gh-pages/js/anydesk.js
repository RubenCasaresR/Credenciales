$(document).ready(function() {
    var isAdmin = (window.USER_ROLE === 'Administrador');
    var puedeCrear = isAdmin || (window.PERMISOS && window.PERMISOS.anydesk && window.PERMISOS.anydesk.crear);
    var puedeEditar = isAdmin || (window.PERMISOS && window.PERMISOS.anydesk && window.PERMISOS.anydesk.editar);
    var puedeEliminar = isAdmin || (window.PERMISOS && window.PERMISOS.anydesk && window.PERMISOS.anydesk.eliminar);

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

    function loadAccounts() {
        $.ajax({
            url: 'api/get_anydesk.php',
            type: 'GET',
            success: function(res) {
                if (res.success) {
                    var html = '';
                    if (res.data.length === 0) {
                        html = '<div class="col-12"><div class="alert alert-info">No hay cuentas AnyDesk registradas.</div></div>';
                    } else {
                        res.data.forEach(function(account) {
                            var safeAccount = encodeURIComponent(JSON.stringify(account));
                            
                            html += `
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-left-success shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="h5 mb-2 font-weight-bold text-gray-800 text-truncate" title="${account.name}">
                                                    <i class="fas fa-desktop fa-sm text-success mr-1"></i> ${account.name}
                                                </div>
                                                <p class="card-text small text-muted mb-1"><i class="fas fa-network-wired fa-fw text-success"></i> ${account.ip_address}</p>
                                                <div class="mt-2">
                                                    <button class="btn btn-sm btn-success w-100 mb-2 btn-ver-detalles" data-account='${safeAccount}'>
                                                        <i class="fas fa-eye"></i> Ver detalles
                                                    </button>
                                                    ${(puedeEditar || puedeEliminar) ? `
                                                    <div class="d-flex justify-content-between">
                                                        ${puedeEditar ? `<button class="btn btn-sm btn-outline-secondary btn-editar-cuenta flex-fill mr-1" data-account='${safeAccount}'>
                                                            <i class="fas fa-edit"></i>
                                                        </button>` : ''}
                                                        ${puedeEliminar ? `<button class="btn btn-sm btn-outline-danger btn-eliminar-cuenta flex-fill ml-1" data-id="${account.id}" data-name="${account.name}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>` : ''}
                                                    </div>
                                                    ` : ''}
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-desktop fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            `;
                        });
                    }
                    $('#anydeskGrid').html(html);
                } else {
                    $('#anydeskGrid').html('<div class="col-12"><div class="alert alert-danger">Error al cargar las cuentas.</div></div>');
                }
            },
            error: function() {
                $('#anydeskGrid').html('<div class="col-12"><div class="alert alert-danger">Error de conexión.</div></div>');
            }
        });
    }

    function showDetails(account) {
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
        html += '<h5 class="text-gray-800 mb-3"><i class="fas fa-desktop text-success mr-1"></i> ' + account.name + '</h5>';
        html += fieldRow('IP / ID', account.ip_address, 'network-wired', 'text-success');
        html += fieldRow('Contraseña AnyDesk', account.password_decrypted, 'lock', 'text-success');
        if (account.anydesk_email || account.anydesk_email_password_decrypted) {
            html += '<hr class="my-2"><h6 class="text-success mb-2"><i class="fas fa-envelope"></i> AnyDesk Email</h6>';
            html += fieldRow('Correo', account.anydesk_email, 'envelope', 'text-success');
            html += fieldRow('Contraseña', account.anydesk_email_password_decrypted, 'key', 'text-success');
        }
        if (account.gmail_email || account.gmail_password_decrypted) {
            html += '<hr class="my-2"><h6 class="text-danger mb-2"><i class="fas fa-envelope"></i> Gmail</h6>';
            html += fieldRow('Correo', account.gmail_email, 'envelope', 'text-danger');
            html += fieldRow('Contraseña', account.gmail_password_decrypted, 'key', 'text-danger');
        }
        if (account.nordvpn || account.nordvpn_password_decrypted || account.nord_dedicada_ip) {
            html += '<hr class="my-2"><h6 class="text-info mb-2"><i class="fas fa-shield-alt"></i> NordVPN</h6>';
            html += fieldRow('Usuario', account.nordvpn, 'user', 'text-info');
            html += fieldRow('Contraseña', account.nordvpn_password_decrypted, 'key', 'text-info');
            html += fieldRow('IP Dedicada', account.nord_dedicada_ip, 'globe', 'text-info');
        }
        if (account.notes) {
            html += '<hr class="my-2"><div class="form-group row mb-0"><label class="col-sm-4 col-form-label text-sm-right text-muted"><i class="fas fa-sticky-note fa-fw"></i> Notas</label><div class="col-sm-8"><p class="form-control-plaintext">' + account.notes + '</p></div></div>';
        }
        html += '</div>';

        $('#tituloDetalles').html('<i class="fas fa-desktop text-success mr-1"></i> ' + account.name);
        $('#detallesBody').html(html);
        $('#verDetallesModal').modal('show');
    }

    // Load initially
    loadAccounts();

    // View details
    $('#anydeskGrid').on('click', '.btn-ver-detalles', function() {
        var account = JSON.parse(decodeURIComponent($(this).data('account')));
        showDetails(account);
    });

    if (puedeCrear) {
        $('#btnNuevaCuenta').on('click', function() {
            $('#formCuenta')[0].reset();
            $('#edit_account_id').val('');
            $('#edit_account_password').attr('required', true);
            $('#tituloModalCuenta').text('Nueva Cuenta AnyDesk');
            $('#editarCuentaModal').modal('show');
        });
    } else {
        $('#btnNuevaCuenta').hide();
    }

    if (puedeEditar) {
        $('#anydeskGrid').on('click', '.btn-editar-cuenta', function() {
            var account = JSON.parse(decodeURIComponent($(this).data('account')));
            $('#edit_account_id').val(account.id);
            $('#edit_account_name').val(account.name);
            $('#edit_account_ip').val(account.ip_address);
            $('#edit_account_password').val(account.password_decrypted || '');
            $('#edit_account_password').attr('required', false);
            $('#edit_gmail_email').val(account.gmail_email || '');
            $('#edit_gmail_password').val(account.gmail_password_decrypted || '');
            $('#edit_nordvpn').val(account.nordvpn || '');
            $('#edit_nordvpn_password').val(account.nordvpn_password_decrypted || '');
            $('#edit_nord_dedicada_ip').val(account.nord_dedicada_ip || '');
            $('#edit_anydesk_email').val(account.anydesk_email || '');
            $('#edit_anydesk_email_password').val(account.anydesk_email_password_decrypted || '');
            $('#edit_account_notes').val(account.notes || '');
            $('#tituloModalCuenta').text('Editar Cuenta AnyDesk');
            $('#editarCuentaModal').modal('show');
        });
    }

    if (puedeCrear || puedeEditar) {
        $('#formCuenta').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'api/save_anydesk.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        $('#editarCuentaModal').modal('hide');
                        Swal.fire('Guardado!', 'Cuenta AnyDesk guardada correctamente.', 'success');
                        loadAccounts();
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
        $('#anydeskGrid').on('click', '.btn-eliminar-cuenta', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            
            Swal.fire({
                title: '¿Eliminar cuenta?',
                text: "Se eliminará la cuenta '" + name + "'",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'api/delete_anydesk.php',
                        type: 'POST',
                        data: { id: id, csrf_token: window.CSRF_TOKEN },
                        success: function(response) {
                            if(response.success) {
                                Swal.fire('Eliminado!', 'Cuenta AnyDesk eliminada.', 'success');
                                loadAccounts();
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