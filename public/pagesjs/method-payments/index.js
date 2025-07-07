/**
 * Configuración avanzada de DataTables para Metodos de Pago
 * - Inicialización segura con DOMContentLoaded
 * - Configuración modularizada
 * - Mejor manejo de eventos
 * - Sintaxis ES6+
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
    initMethodPaymentsTable();
});
const numberFormat = new Intl.NumberFormat("es-MX");
/**
 * Inicializa y configura DataTable para Metodos de Pago
 */
const initMethodPaymentsTable = () => {
    const dataTable = $('.datatables');
    
    if (!dataTable.length) return;

    dataTable.DataTable({
        processing: true,
        
        url: "/metodos-pago",
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
            { data: 'category_method_payment_name', name: 'category_method_payment_name' },
            { data: 'entity_name', name: 'entity_name' },
            { data: 'account_holder', name: 'account_holder' },
            { data: 'alias', name: 'alias' },
            { data: 'type_account', name: 'type_account' },
            { data: 'account_number', name: 'account_number' },
            { data: 'cbu_cvu', name: 'cbu_cvu' },
            { data: 'initial_balance', name: 'initial_balance' },
            { data: 'current_balance', name: 'current_balance' },
            { 
                data: 'actions', 
                name: 'actions', 
                orderable: false, 
                searchable: false 
            }
        ],
        columnDefs: [
            
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
            window.location.href = `/metodos-pago/${id}/destroy`;
        }
    });
};