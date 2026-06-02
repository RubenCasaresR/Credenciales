$(document).ready(function() {
    function getFaviconUrl(domain) {
        if (!domain) return 'img/rocket.svg';
        var d = domain.replace(/^https?:\/\//, '').replace(/\/.*$/, '').split('/')[0];
        return 'https://www.google.com/s2/favicons?domain=' + encodeURIComponent(d) + '&sz=64';
    }

    function normalizeUrl(url, domain) {
        var u = url || domain || '';
        u = u.trim();
        if (!u) return '#';
        if (!/^https?:\/\//i.test(u)) {
            u = 'https://' + u;
        }
        return u;
    }

    function extractDomain(url) {
        if (!url) return '';
        var d = url.replace(/^https?:\/\//, '').replace(/\/.*$/, '').split('/')[0];
        return d;
    }

    var searchTimeout;
    var currentPages = [];

    function renderCards(pages) {
        currentPages = pages;
        var html = '';
        if (pages.length === 0) {
            html = '<div class="col-12"><div class="alert alert-info">No hay páginas web registradas.</div></div>';
            $('#paginasWebGrid').html(html);
            $('#totalCount').text('0');
            return;
        }

        pages.forEach(function(page) {
            var domain = extractDomain(page.domain || page.url);
            var favicon = getFaviconUrl(domain);
            var linkUrl = normalizeUrl(page.url, page.domain);
            var isCredencial = page.source === 'Credencial';
            var badgeColor = isCredencial ? 'primary' : 'success';
            var badgeIcon = isCredencial ? 'fa-key' : 'fa-cogs';
            var notes = page.notes || '';

            html += `
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-thumb-wrapper position-relative" style="height:130px;overflow:hidden;border-radius:0.35rem 0.35rem 0 0;background:#eaecf4;">
                        <img src="${favicon}" alt="" class="og-thumb" style="width:100%;height:100%;object-fit:cover;display:none;"
                             data-page-id="${page.id}"
                             onerror="this.style.display='none';this.parentNode.querySelector('.thumb-fallback').style.display='flex';">
                        <div class="thumb-fallback d-flex align-items-center justify-content-center" style="width:100%;height:100%;display:flex;">
                            <img src="${favicon}" alt="" width="48" height="48" onerror="this.style.display='none'">
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <div class="d-flex align-items-center mb-2">
                            <span class="h6 font-weight-bold text-gray-800 text-truncate" title="${page.name}" style="max-width:100%;">${page.name}</span>
                        </div>
                        <p class="card-text small text-muted mb-1 text-truncate">
                            <i class="fas fa-globe fa-fw text-${badgeColor}"></i>
                            <a href="${linkUrl}" target="_blank" rel="noopener">${domain || linkUrl}</a>
                        </p>
                        <div class="mb-1">
                            <span class="badge badge-${badgeColor}"><i class="fas ${badgeIcon}"></i> ${page.source}</span>
                        </div>
                        ${notes ? '<p class="card-text small text-muted mb-0 text-truncate"><i class="fas fa-sticky-note fa-fw"></i> ' + notes + '</p>' : ''}
                        <div class="text-right mt-2">
                            <a href="${linkUrl}" target="_blank" rel="noopener" class="btn btn-outline-${badgeColor} btn-sm" title="Abrir sitio">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            `;
        });

        $('#paginasWebGrid').html(html);
        $('#totalCount').text(pages.length);
    }

    function fetchOgImagesSequential(index) {
        if (index >= currentPages.length) return;

        var page = currentPages[index];
        var url = normalizeUrl(page.url, page.domain);

        if (!url || url === '#') {
            setTimeout(function() { fetchOgImagesSequential(index + 1); }, 200);
            return;
        }

        var thumb = $(`.og-thumb[data-page-id="${page.id}"]`);

        $.ajax({
            url: 'api/fetch_og_image.php',
            type: 'GET',
            data: { url: url },
            timeout: 12000,
            success: function(res) {
                if (res.success && res.og_image && thumb.length) {
                    var fallback = thumb.parent().find('.thumb-fallback');
                    thumb.attr('src', res.og_image);
                    thumb.on('load', function() {
                        thumb.show();
                        fallback.hide();
                    });
                    thumb.on('error', function() {
                        thumb.hide();
                        fallback.show();
                    });
                }
            },
            complete: function() {
                setTimeout(function() { fetchOgImagesSequential(index + 1); }, 600);
            }
        });
    }

    function loadPages(searchTerm) {
        var params = {};
        if (searchTerm) {
            params.search = searchTerm;
        }

        $.ajax({
            url: 'api/get_paginas_web.php',
            type: 'GET',
            data: params,
            success: function(res) {
                if (res.success) {
                    renderCards(res.data);
                    fetchOgImagesSequential(0);
                } else {
                    $('#paginasWebGrid').html('<div class="col-12"><div class="alert alert-danger">Error al cargar las páginas web.</div></div>');
                    $('#totalCount').text('0');
                }
            },
            error: function() {
                $('#paginasWebGrid').html('<div class="col-12"><div class="alert alert-danger">Error de conexión.</div></div>');
                $('#totalCount').text('0');
            }
        });
    }

    $('#searchPages').on('input', function() {
        clearTimeout(searchTimeout);
        var val = $(this).val();
        searchTimeout = setTimeout(function() {
            loadPages(val);
        }, 400);
    });

    $('#searchPages').on('keypress', function(e) {
        if (e.which === 13) {
            clearTimeout(searchTimeout);
            loadPages($(this).val());
        }
    });

    loadPages();
});
