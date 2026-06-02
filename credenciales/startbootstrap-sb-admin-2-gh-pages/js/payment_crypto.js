$(document).ready(function() {
    var isAdmin = (window.USER_ROLE === 'Administrador');
    var puedeCrear = isAdmin || (window.PERMISOS && window.PERMISOS.payment_gateways && window.PERMISOS.payment_gateways.crear);
    var puedeEditar = isAdmin || (window.PERMISOS && window.PERMISOS.payment_gateways && window.PERMISOS.payment_gateways.editar);
    var puedeEliminar = isAdmin || (window.PERMISOS && window.PERMISOS.payment_gateways && window.PERMISOS.payment_gateways.eliminar);
    var puedeVer = isAdmin || (window.PERMISOS && window.PERMISOS.payment_gateways && window.PERMISOS.payment_gateways.ver);
    var revealTimers = {};

    var gatewayIcons = {
        'paypal': 'fa-credit-card',
        'stripe': 'fa-credit-card',
        'mercadopago': 'fa-credit-card',
        'otro': 'fa-credit-card'
    };

    var gatewayColors = {
        'paypal': 'info',
        'stripe': 'primary',
        'mercadopago': 'success',
        'otro': 'secondary'
    };

    function escHtml(str) {
        return $('<span>').text(str || '').html();
    }

    function loadGateways() {
        $.ajax({
            url: 'api/get_payment_gateways.php',
            type: 'GET',
            success: function(res) {
                if (res.success) {
                    var html = '';
                    if (res.data.length === 0) {
                        html = '<div class="col-12"><div class="alert alert-info">No hay cuentas de pago registradas.</div></div>';
                    } else {
                        res.data.forEach(function(gw) {
                            var icon = gatewayIcons[gw.gateway_type] || 'fa-credit-card';
                            var color = gatewayColors[gw.gateway_type] || 'secondary';
                            var badgeClass = 'badge-' + color;
                            var sandboxBadge = gw.sandbox ? '<span class="badge badge-warning ml-1">Sandbox</span>' : '<span class="badge badge-success ml-1">Producción</span>';

                            var typeLabel = gw.gateway_type.charAt(0).toUpperCase() + gw.gateway_type.slice(1);

                            var safeGw = encodeURIComponent(JSON.stringify(gw));

                            html += `
                            <div class="col-xl-4 col-md-6 mb-4">
                                <div class="card border-left-${color} shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="h5 mb-2 font-weight-bold text-gray-800 text-truncate" title="${escHtml(gw.name)}">
                                                    ${escHtml(gw.name)} ${sandboxBadge}
                                                </div>
                                                <p class="card-text small text-muted mb-1">
                                                    <i class="fas ${icon} fa-fw text-${color}"></i> ${typeLabel}
                                                </p>
                                                ${gw.merchant_id ? '<p class="card-text small text-muted mb-1"><i class="fas fa-hashtag fa-fw"></i> ' + escHtml(gw.merchant_id) + '</p>' : ''}
                                                ${gw.email ? '<p class="card-text small text-muted mb-1"><i class="fas fa-envelope fa-fw"></i> ' + escHtml(gw.email) + '</p>' : ''}
                                                <div class="mt-2">
                                                    <button class="btn btn-sm btn-${color} w-100 mb-2 btn-ver-detalles" data-gw='${safeGw}'>
                                                        <i class="fas fa-eye"></i> Ver detalles
                                                    </button>
                                                    ${(puedeEditar || puedeEliminar) ? `
                                                    <div class="d-flex justify-content-between">
                                                        ${puedeEditar ? `<button class="btn btn-sm btn-outline-secondary btn-editar-cuenta flex-fill mr-1" data-gw='${safeGw}'>
                                                            <i class="fas fa-edit"></i>
                                                        </button>` : ''}
                                                        ${puedeEliminar ? `<button class="btn btn-sm btn-outline-danger btn-eliminar-cuenta flex-fill ml-1" data-id="${gw.id}" data-name="${gw.name}">
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
                    $('#paymentGatewayGrid').html(html);
                } else {
                    $('#paymentGatewayGrid').html('<div class="col-12"><div class="alert alert-danger">Error al cargar cuentas.</div></div>');
                }
            },
            error: function() {
                $('#paymentGatewayGrid').html('<div class="col-12"><div class="alert alert-danger">Error de conexión.</div></div>');
            }
        });
    }

    function revealField(id, field, btnElement) {
        var $btn = $(btnElement);
        var $container = $btn.closest('.reveal-container');
        var $display = $container.find('.reveal-display');

        if ($display.data('revealed') === true) {
            return;
        }

        if (!$display.data('mask-original')) {
            $display.data('mask-original', $display.val());
        }

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: 'api/reveal_payment_key.php',
            type: 'POST',
            data: {
                id: id,
                field: field,
                csrf_token: window.CSRF_TOKEN
            },
            success: function(res) {
                if (res.success) {
                    $display.val(res.data).data('revealed', true);
                    $btn.html('<i class="fas fa-eye-slash"></i> Ocultar');

                    if (revealTimers[id + '_' + field]) {
                        clearTimeout(revealTimers[id + '_' + field]);
                    }

                    revealTimers[id + '_' + field] = setTimeout(function() {
                        var originalMask = $display.data('mask-original');
                        if (originalMask) {
                            $display.val(originalMask);
                        } else {
                            var displayLen = res.data.length;
                            if (displayLen > 4) {
                                $display.val('********' + res.data.slice(-4));
                            } else {
                                $display.val(res.data);
                            }
                        }
                        $display.data('revealed', false);
                        $btn.html('<i class="fas fa-eye"></i> Revelar');
                    }, 30000);
                } else {
                    Swal.fire('Error', res.error, 'error');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Reveal error:', textStatus, errorThrown, jqXHR.responseText);
                Swal.fire('Error', 'Error de comunicación', 'error');
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    }

    function hideField(id, field, btnElement) {
        var $btn = $(btnElement);
        var $container = $btn.closest('.reveal-container');
        var $display = $container.find('.reveal-display');

        if (revealTimers[id + '_' + field]) {
            clearTimeout(revealTimers[id + '_' + field]);
        }

        var masked = $display.data('mask-original') || $display.val();
        $display.val(masked).data('revealed', false);
        $btn.html('<i class="fas fa-eye"></i> Revelar');
    }

    function showDetails(gw) {
        function fieldRow(label, value, icon, revealable, fieldName, id) {
            if (!value && value !== '') return '';
            var displayValue = value;

            if (revealable) {
                var displayId = (id || '0') + '_' + (fieldName || '');
                return `
                    <div class="form-group row align-items-center mb-2">
                        <label class="col-sm-4 col-form-label text-sm-right font-weight-bold text-primary">
                            <i class="fas fa-${icon} fa-fw"></i> ${label}
                        </label>
                        <div class="col-sm-8 reveal-container">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control form-control-sm reveal-display bg-white" value="${escHtml(displayValue)}" readonly data-revealed="false">
                                <div class="input-group-append">
                                    <button class="btn btn-sm btn-outline-secondary btn-reveal" type="button" data-id="${id}" data-field="${fieldName}">
                                        <i class="fas fa-eye"></i> Revelar
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted">Se ocultará automáticamente en 30s. Esta acción queda registrada.</small>
                        </div>
                    </div>`;
            } else {
                return `
                    <div class="form-group row align-items-center mb-2">
                        <label class="col-sm-4 col-form-label text-sm-right font-weight-bold text-primary">
                            <i class="fas fa-${icon} fa-fw"></i> ${label}
                        </label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control form-control-sm" value="${escHtml(value)}" readonly onclick="this.select()">
                        </div>
                        <div class="col-sm-3">
                            <button class="btn btn-sm btn-outline-secondary btn-copy" onclick="var inp=$(this).parent().prev().find('input'); inp.select(); navigator.clipboard.writeText(inp.val()).then(function(){var b=arguments[0]; b.html('<i class=\\'fas fa-check\\'></i> Copiado').removeClass('btn-outline-secondary').addClass('btn-success'); setTimeout(function(){b.html('<i class=\\'fas fa-copy\\'></i> Copiar').removeClass('btn-success').addClass('btn-outline-secondary');},1500);}.bind(null, $(this)));">
                                <i class="fas fa-copy"></i> Copiar
                            </button>
                        </div>
                    </div>`;
            }
        }

        var html = '';
        html += '<div class="container-fluid">';
        html += '<h5 class="text-gray-800 mb-3"><i class="fas fa-credit-card text-primary mr-1"></i> ' + escHtml(gw.name) + '</h5>';

        var typeLabel = gw.gateway_type.charAt(0).toUpperCase() + gw.gateway_type.slice(1);
        html += fieldRow('Tipo', typeLabel, 'tag');
        html += fieldRow('Merchant ID', gw.merchant_id, 'hashtag');
        html += fieldRow('Correo', gw.email, 'envelope');
        html += fieldRow('Public Key', gw.public_key, 'key');
        html += fieldRow('Modo', gw.sandbox ? 'Sandbox / Pruebas' : 'Producción', gw.sandbox ? 'flask' : 'check-circle');

        html += '<hr class="my-2">';
        html += '<h6 class="text-danger mb-2"><i class="fas fa-lock mr-1"></i> Claves Sensibles</h6>';
        html += '<div class="alert alert-warning py-2 small"><i class="fas fa-exclamation-triangle mr-1"></i> Al revelar una clave queda registrado en la bitácora quién, cuándo y desde qué IP la consultó.</div>';

        html += fieldRow('Secret Key', gw.secret_key_masked, 'lock', true, 'secret_key', gw.id);
        html += fieldRow('Webhook Secret', gw.webhook_secret_masked, 'bell', true, 'webhook_secret', gw.id);

        if (gw.notes) {
            html += '<hr class="my-2"><div class="form-group row mb-0"><label class="col-sm-4 col-form-label text-sm-right text-muted"><i class="fas fa-sticky-note fa-fw"></i> Notas</label><div class="col-sm-8"><p class="form-control-plaintext">' + escHtml(gw.notes) + '</p></div></div>';
        }
        html += '</div>';

        $('#tituloDetalles').html('<i class="fas fa-credit-card text-primary mr-1"></i> ' + escHtml(gw.name));
        $('#detallesBody').html(html);

        if (puedeVer) {
            $('#verDetallesModal').on('shown.bs.modal', function() {
                $('#detallesBody .btn-reveal').each(function() {
                    var $btn = $(this);
                    var id = $btn.data('id');
                    var field = $btn.data('field');
                    revealField(id, field, this);
                });
                $(this).off('shown.bs.modal');
            });
        }

        $('#verDetallesModal').modal('show');
    }

    loadGateways();

    $('#paymentGatewayGrid').on('click', '.btn-ver-detalles', function() {
        var gw = JSON.parse(decodeURIComponent($(this).data('gw')));
        showDetails(gw);
    });

    $(document).on('click', '.btn-reveal', function() {
        var $btn = $(this);
        var $container = $btn.closest('.reveal-container');
        var $display = $container.find('.reveal-display');
        var id = $btn.data('id');
        var field = $btn.data('field');

        if ($display.data('revealed') === true) {
            hideField(id, field, this);
        } else {
            revealField(id, field, this);
        }
    });


    if (puedeCrear) {
        $('#btnNuevaCuenta').on('click', function() {
            $('#formCuenta')[0].reset();
            $('#edit_id').val('');
            $('#edit_sandbox').prop('checked', true);
            $('#edit_secret_key').attr('required', true);
            $('#tituloModalCuenta').text('Nueva Cuenta');
            $('#editarCuentaModal').modal('show');
        });
    } else {
        $('#btnNuevaCuenta').hide();
    }

    if (puedeEditar) {
        $('#paymentGatewayGrid').on('click', '.btn-editar-cuenta', function() {
            var gw = JSON.parse(decodeURIComponent($(this).data('gw')));
            $('#edit_id').val(gw.id);
            $('#edit_name').val(gw.name);
            $('#edit_gateway_type').val(gw.gateway_type);
            $('#edit_merchant_id').val(gw.merchant_id || '');
            $('#edit_public_key').val(gw.public_key || '');
            $('#edit_secret_key').val('').attr('required', false);
            $('#edit_webhook_secret').val('');
            $('#edit_sandbox').prop('checked', gw.sandbox == 1);
            $('#edit_email').val(gw.email || '');
            $('#edit_notes').val(gw.notes || '');
            $('#tituloModalCuenta').text('Editar Cuenta');
            $('#editarCuentaModal').modal('show');
        });
    }

    if (puedeCrear || puedeEditar) {
        $('#formCuenta').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'api/save_payment_gateway.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        $('#editarCuentaModal').modal('hide');
                        Swal.fire('Guardado!', 'Cuenta guardada correctamente.', 'success');
                        loadGateways();
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
        $('#paymentGatewayGrid').on('click', '.btn-eliminar-cuenta', function() {
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
                        url: 'api/delete_payment_gateway.php',
                        type: 'POST',
                        data: { id: id, csrf_token: window.CSRF_TOKEN },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Eliminado!', 'Cuenta eliminada.', 'success');
                                loadGateways();
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
