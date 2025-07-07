/**
 * Configuración avanzada de DataTables para Proveedores
 * - Inicialización segura con DOMContentLoaded
 * - Configuración modularizada
 * - Mejor manejo de eventos
 * - Sintaxis ES6+
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
    initSuppliersTable();
});
const numberFormat = new Intl.NumberFormat("es-MX");
/**
 * Inicializa y configura DataTable para Proveedores
 */
const initSuppliersTable = () => {
    const dataTable = $('.datatables');
    
    if (!dataTable.length) return;

    dataTable.DataTable({
        processing: true,
        
        url: "/proveedores",
        type: "POST",
        dataType: 'json',
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>><"table-responsive"t><"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        language: {
            url: "https://cdn.datatables.net/plug-ins/2.0.8/i18n/es-ES.json",
            paginate: {
                next: '<i class="ri-arrow-right-s-line"></i>',
                previous: '<i class="ri-arrow-left-s-line"></i>'
            }
        },
        columns: [
            { data: 'category_supplier_name', name: 'category_supplier_name' },
            { data: 'subcategory_supplier_name', name: 'subcategory_supplier_name' },
            { data: 'name', name: 'name' },
            { data: 'representant', name: 'representant' },
            { data: 'phone', name: 'phone' },
            { data: 'amount', name: 'amount' },
            { data: 'currency_name', name: 'currency_name' },
            { data: 'description', name: 'description' },
            { 
                data: 'actions', 
                name: 'actions', 
                orderable: false, 
                searchable: false 
            }
        ],
        columnDefs: [
            {
                targets:[5],
                render: function(data, type, row) {
                    return numberFormat.format(data);
                }
            }
        ]
    });
};

/**
 * Muestra diálogo de confirmación para eliminar tipo de gasto
 * @param {string|number} id - ID del registro a eliminar
 */
const deleteRecord = (id) => {
    Swal.fire({
        title: '¿Está seguro de eliminar este registro?',
        text: "¡No podrá recuperar esta información!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
            cancelButton: 'btn btn-outline-danger waves-effect'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `/proveedores/${id}/destroy`;
        }
    });
};