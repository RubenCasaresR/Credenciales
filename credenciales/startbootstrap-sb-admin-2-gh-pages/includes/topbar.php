                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search" id="globalSearchForm">
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" placeholder="Buscar servidores, credenciales, anydesk..." id="globalSearchInput" autocomplete="off">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit"><i class="fas fa-search fa-sm"></i></button>
                            </div>
                        </div>
                        <div id="globalSearchResults" class="dropdown-menu w-100" style="display:none; max-height:400px; overflow-y:auto;"></div>
                    </form>

                    <ul class="navbar-nav ml-auto">

                        <li class="nav-item dropdown no-arrow d-sm-none">
                            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-search fa-fw"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in" aria-labelledby="searchDropdown">
                                <form class="form-inline mr-auto w-100 navbar-search">
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
                                        <div class="input-group-append"><button class="btn btn-primary" type="button"><i class="fas fa-search fa-sm"></i></button></div>
                                    </div>
                                </form>
                            </div>
                        </li>

                        <li class="nav-item no-arrow mx-1">
                            <a class="nav-link" href="#" id="darkModeToggle" title="Modo oscuro">
                                <i class="fas fa-moon"></i>
                            </a>
                        </li>

                        <li class="nav-item dropdown no-arrow mx-1" id="notifBellContainer">
                            <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell fa-fw"></i>
                                <span class="badge badge-danger badge-counter" id="notifBadge" style="display:none;">0</span>
                            </a>
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="notifDropdown">
                                <h6 class="dropdown-header">Notificaciones</h6>
                                <div id="notifList" style="max-height:320px; overflow-y:auto;">
                                    <span class="dropdown-item text-muted small">Cargando...</span>
                                </div>
                                <a class="dropdown-item text-center small text-gray-500" href="credenciales.php">Ver todas las credenciales</a>
                            </div>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuario') ?></span>
                                <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="perfil.php"><i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i> Perfil</a>
                                <a class="dropdown-item" href="bitacora.php"><i class="fas fa-history fa-sm fa-fw mr-2 text-gray-400"></i> Bitácora</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal"><i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i> Cerrar Sesión</a>
                            </div>
                        </li>

                    </ul>

                </nav>

<script>
$(document).ready(function() {
    var searchTimeout;
    $('#globalSearchInput').on('input', function() {
        clearTimeout(searchTimeout);
        var q = $(this).val();
        if (q.length < 2) { $('#globalSearchResults').hide(); return; }
        searchTimeout = setTimeout(function() {
            $.getJSON('api/search.php?q=' + encodeURIComponent(q), function(r) {
                if (r.success) {
                    var html = '';
                    var d = r.data;
                    if (d.servers.length) {
                        html += '<h6 class="dropdown-header">Servidores</h6>';
                        d.servers.forEach(function(s) { html += '<a class="dropdown-item" href="servidor_detalle.php?id=' + s.id + '"><i class="fas fa-server fa-fw text-primary mr-2"></i>' + s.name + ' <small class="text-muted">' + (s.ip_address || '') + '</small></a>'; });
                    }
                    if (d.credentials.length) {
                        html += '<h6 class="dropdown-header">Credenciales</h6>';
                        d.credentials.forEach(function(c) { html += '<a class="dropdown-item" href="' + (c.link || '#') + '"><i class="fas fa-key fa-fw text-success mr-2"></i>' + c.service_name + ' <small class="text-muted">(' + c.server_name + ')</small></a>'; });
                    }
                    if (d.anydesk.length) {
                        html += '<h6 class="dropdown-header">AnyDesk</h6>';
                        d.anydesk.forEach(function(a) { html += '<a class="dropdown-item" href="anydesk.php"><i class="fas fa-desktop fa-fw text-info mr-2"></i>' + a.name + ' <small class="text-muted">' + a.ip_address + '</small></a>'; });
                    }
                    if (d.documents.length) {
                        html += '<h6 class="dropdown-header">Documentos</h6>';
                        d.documents.forEach(function(doc) { html += '<a class="dropdown-item" href="documentos.php"><i class="fas fa-file fa-fw text-warning mr-2"></i>' + doc.name + '</a>'; });
                    }
                    if (d.payment_gateways && d.payment_gateways.length) {
                        html += '<h6 class="dropdown-header">Pasarelas de Pago</h6>';
                        d.payment_gateways.forEach(function(pg) { html += '<a class="dropdown-item" href="payment_gateways.php"><i class="fas fa-credit-card fa-fw text-info mr-2"></i>' + pg.name + ' <small class="text-muted">(' + pg.gateway_type + ')</small></a>'; });
                    }
                    if (!html) html = '<a class="dropdown-item text-muted">Sin resultados</a>';
                    $('#globalSearchResults').html(html).show();
                }
            });
        }, 300);
    });
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#globalSearchForm').length) $('#globalSearchResults').hide();
    });
    $('#globalSearchForm').on('submit', function(e) { e.preventDefault(); });

    function escHtml(str) {
        return $('<span>').text(str || '').html();
    }

    function cargarNotificaciones() {
        $.getJSON('api/get_notifications.php', function(r) {
            if (!r.success) return;
            var expired = r.data.expired || [];
            var soon = r.data.soon || [];
            var total = expired.length + soon.length;

            var badge = $('#notifBadge');
            if (total > 0) {
                badge.text(total).show();
            } else {
                badge.hide();
            }

            var html = '';
            if (total === 0) {
                html = '<span class="dropdown-item text-muted small">Sin notificaciones</span>';
            } else {
                if (expired.length) {
                    html += '<div class="dropdown-item text-danger small font-weight-bold border-bottom"><i class="fas fa-exclamation-triangle mr-1"></i>Vencidas (' + expired.length + ')</div>';
                    expired.forEach(function(c) {
                        var dias = Math.floor((new Date() - new Date(c.payment_date)) / (1000*60*60*24));
                        html += '<a class="dropdown-item py-2" href="servidor_detalle.php?id=' + c.server_id + '&credential=' + c.id + '">' +
                            '<div class="d-flex align-items-center">' +
                                '<div class="mr-2"><div class="icon-circle bg-danger"><i class="fas fa-exclamation text-white"></i></div></div>' +
                                '<div class="small"><strong>' + escHtml(c.service_name) + '</strong><br>' +
                                '<span class="text-muted">Vencido hace ' + dias + ' día(s) - ' + escHtml(c.server_name) + '</span></div>' +
                            '</div></a>';
                    });
                }
                if (soon.length) {
                    html += '<div class="dropdown-divider"></div>';
                    html += '<div class="dropdown-item text-warning small font-weight-bold border-bottom"><i class="fas fa-clock mr-1"></i>Próximas (' + soon.length + ')</div>';
                    soon.forEach(function(c) {
                        var hoy = new Date();
                        var venc = new Date(c.payment_date);
                        var diff = Math.ceil((venc - hoy) / (1000*60*60*24));
                        html += '<a class="dropdown-item py-2" href="servidor_detalle.php?id=' + c.server_id + '&credential=' + c.id + '">' +
                            '<div class="d-flex align-items-center">' +
                                '<div class="mr-2"><div class="icon-circle bg-warning"><i class="fas fa-clock text-white"></i></div></div>' +
                                '<div class="small"><strong>' + escHtml(c.service_name) + '</strong><br>' +
                                '<span class="text-muted">Vence en ' + diff + ' día(s) - ' + escHtml(c.server_name) + '</span></div>' +
                            '</div></a>';
                    });
                }
            }
            $('#notifList').html(html);
        });
    }

    cargarNotificaciones();
    setInterval(cargarNotificaciones, 60000);

});
</script>