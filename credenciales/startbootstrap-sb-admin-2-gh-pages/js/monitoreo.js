var monitoreoTable = null;

function formatPort(port, result, label) {
    if (!result) return '';
    var cls = result.open ? 'port-open' : 'port-closed';
    var icon = result.open ? 'fa-check' : 'fa-times';
    return '<span class="port-badge ' + cls + '"><i class="fas ' + icon + ' mr-1"></i>' + label + '</span>';
}

function formatTime(ms) {
    if (ms === null || ms === undefined) return '-';
    if (ms < 1000) return ms + ' ms';
    return (ms / 1000).toFixed(2) + ' s';
}

function cargarMonitoreo() {
    $('#monitoreoBody').html('<tr><td colspan="5" class="text-center text-muted"><i class="fas fa-spinner fa-spin mr-1"></i> Verificando servidores...</td></tr>');

    $.getJSON('api/monitoreo_check.php', function(r) {
        if (!r.success) {
            $('#monitoreoBody').html('<tr><td colspan="5" class="text-center text-danger">Error al verificar servidores.</td></tr>');
            return;
        }

        var s = r.summary;
        $('#sumTotal').text(s.total);
        $('#sumOnline').text(s.online);
        $('#sumOffline').text(s.offline);
        $('#sumUnknown').text(s.unknown);

        $('#lastCheckedLabel').text('Última verificación: ' + new Date().toLocaleTimeString());

        var html = '';
        $.each(r.data, function(i, sv) {
            var statusClass = 'status-' + sv.status;
            var statusText = sv.status === 'online' ? 'Online' : (sv.status === 'offline' ? 'Offline' : 'Sin IP');
            var statusIcon = sv.status === 'online' ? 'fa-check-circle text-success' : (sv.status === 'offline' ? 'fa-times-circle text-danger' : 'fa-question-circle text-secondary');

            var portsHtml = formatPort(80, sv.http, 'HTTP') + formatPort(443, sv.https, 'HTTPS');
            if (!portsHtml) portsHtml = '<span class="text-muted">-</span>';

            var bestTime = null;
            if (sv.http && sv.http.time_ms) bestTime = sv.http.time_ms;
            else if (sv.https && sv.https.time_ms) bestTime = sv.https.time_ms;

            html += '<tr>' +
                '<td><strong>' + $('<span>').text(sv.name).html() + '</strong></td>' +
                '<td><code>' + $('<span>').text(sv.ip_address).html() + '</code></td>' +
                '<td><span class="status-indicator ' + statusClass + '"></span><i class="fas ' + statusIcon + ' mr-1"></i> ' + statusText + '</td>' +
                '<td>' + portsHtml + '</td>' +
                '<td>' + (bestTime ? formatTime(bestTime) : '-') + '</td>' +
                '</tr>';
        });

        if (!html) {
            html = '<tr><td colspan="5" class="text-center text-muted">No hay servidores registrados.</td></tr>';
        }

        $('#monitoreoBody').html(html);

        if (monitoreoTable) {
            monitoreoTable.destroy();
        }
        monitoreoTable = $('#monitoreoTable').DataTable({
            order: [[2, 'asc']],
            pageLength: 25,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            }
        });
    });
}

$(document).ready(function() {
    cargarMonitoreo();

    $('#btnRefrescar').on('click', function() {
        location.reload();
    });
});
