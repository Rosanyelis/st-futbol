$(document).ready(function() {
    $('#category_supplier_id').on('change', function() {
        var categoryId = $(this).val();
        var token = $('meta[name="csrf-token"]').attr('content');
        console.log(categoryId);
        console.log(token);
        $.ajax({
            url: '/proveedores/get-subcategory-suppliers',
            type: 'POST',
            data: {
                category_supplier_id: categoryId,
                _token: token
            },
            success: function(data) {
                // Limpiar subcategorías actuales
                $('#subcategory_supplier_id').empty();
                // Agregar nuevas subcategorías
                $.each(data, function(index, subcat) {
                    $('#subcategory_supplier_id').append(
                        $('<option>', {
                            value: subcat.id,
                            text: subcat.name
                        })
                    );
                });
            },
            error: function(xhr, status, error) {
                console.error('Error al obtener subcategorías:', error);
            }
        });
    });
});
