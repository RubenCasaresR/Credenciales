$(document).ready(function() {
    var isAdmin = (window.USER_ROLE === 'Administrador');
    var puedeCrear = isAdmin || (window.PERMISOS && window.PERMISOS.documentos && window.PERMISOS.documentos.crear);
    var puedeEditar = isAdmin || (window.PERMISOS && window.PERMISOS.documentos && window.PERMISOS.documentos.editar);
    var puedeEliminar = isAdmin || (window.PERMISOS && window.PERMISOS.documentos && window.PERMISOS.documentos.eliminar);

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 B';
        var k = 1024;
        var sizes = ['B', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function getFileIcon(type) {
        if (type.includes('pdf')) return 'fa-file-pdf text-danger';
        if (type.includes('word') || type.includes('doc')) return 'fa-file-word text-primary';
        if (type.includes('sheet') || type.includes('excel') || type.includes('xls')) return 'fa-file-excel text-success';
        if (type.includes('image')) return 'fa-file-image text-warning';
        if (type.includes('zip') || type.includes('rar') || type.includes('tar')) return 'fa-file-archive text-secondary';
        if (type.includes('text') || type.includes('txt')) return 'fa-file-alt text-info';
        return 'fa-file text-muted';
    }

    function loadDocuments() {
        $.ajax({
            url: 'api/get_documents.php',
            type: 'GET',
            success: function(res) {
                if (res.success) {
                    var html = '';
                    if (res.data.length === 0) {
                        html = '<tr><td colspan="6" class="text-center text-muted">No hay documentos subidos.</td></tr>';
                    } else {
                        res.data.forEach(function(doc) {
                            var icon = getFileIcon(doc.file_type);
                            html += '<tr>';
                            html += '<td><i class="fas ' + icon + ' fa-fw mr-2"></i>' + doc.name + '</td>';
                            html += '<td>' + (doc.description || '<span class="text-muted">-</span>') + '</td>';
                            html += '<td>' + doc.file_type + '</td>';
                            html += '<td>' + formatFileSize(doc.file_size) + '</td>';
                            html += '<td>' + doc.created_at + '</td>';
                            html += '<td class="text-center">';
                            html += '<a href="api/download_document.php?id=' + doc.id + '" class="btn btn-sm btn-outline-success mr-1" target="_blank" title="Ver"><i class="fas fa-eye"></i></a>';
                            html += '<a href="api/download_document.php?id=' + doc.id + '&download=1" class="btn btn-sm btn-outline-primary mr-1" title="Descargar"><i class="fas fa-download"></i></a>';
                            if (puedeEliminar) {
                                html += '<button class="btn btn-sm btn-outline-danger btn-eliminar-documento" data-id="' + doc.id + '" data-name="' + doc.name + '" title="Eliminar"><i class="fas fa-trash"></i></button>';
                            }
                            html += '</td>';
                            html += '</tr>';
                        });
                    }
                    $('#documentosBody').html(html);
                } else {
                    $('#documentosBody').html('<tr><td colspan="6" class="text-center text-danger">Error al cargar documentos.</td></tr>');
                }
            },
            error: function() {
                $('#documentosBody').html('<tr><td colspan="6" class="text-center text-danger">Error de conexión.</td></tr>');
            }
        });
    }

    loadDocuments();

    // Update file input label
    $('#edit_doc_file').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $('#fileLabel').text(fileName || 'Seleccionar archivo...');
    });

    if (puedeCrear || puedeEditar) {
        $('#btnSubirDocumento').on('click', function() {
            $('#formDocumento')[0].reset();
            $('#edit_doc_id').val('');
            $('#fileLabel').text('Seleccionar archivo...');
            $('#edit_doc_file').attr('required', true);
            $('#tituloModalDocumento').text('Subir Documento');
            $('.modal-footer button[type="submit"]').text('Subir');
            $('#subirDocumentoModal').modal('show');
        });

        $('#formDocumento').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            formData.append('csrf_token', window.CSRF_TOKEN);
            $.ajax({
                url: 'api/upload_document.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        $('#subirDocumentoModal').modal('hide');
                        Swal.fire('Subido!', 'Documento subido correctamente.', 'success');
                        loadDocuments();
                    } else {
                        Swal.fire('Error', response.error || 'Error al subir el documento.', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error de comunicación', 'error');
                }
            });
        });
    } else {
        $('#btnSubirDocumento').hide();
    }

    if (puedeEliminar) {
        $('#documentosBody').on('click', '.btn-eliminar-documento', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            
            Swal.fire({
                title: '¿Eliminar documento?',
                text: "Se eliminará '" + name + "'",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'api/delete_document.php',
                        type: 'POST',
                        data: { id: id, csrf_token: window.CSRF_TOKEN },
                        success: function(response) {
                            if(response.success) {
                                Swal.fire('Eliminado!', 'Documento eliminado.', 'success');
                                loadDocuments();
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