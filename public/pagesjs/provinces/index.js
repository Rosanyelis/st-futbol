/**
 * Configuración avanzada de DataTables para Provincias
 * - Inicialización segura con DOMContentLoaded
 * - Configuración modularizada
 * - Mejor manejo de eventos
 * - Sintaxis ES6+
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
    initProvincesTable();
});

/**
 * Inicializa y configura DataTable para subcategorías de gastos
 */
const initProvincesTable = () => {
    const dataTable = $('.datatables');
    
    if (!dataTable.length) return;

    dataTable.DataTable({
        processing: true,
        serverSide: true,
        url: "/provincias",
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
            { data: 'name', name: 'name' },
            { data: 'country.name', name: 'country.name' },
            { 
                data: 'actions', 
                name: 'actions', 
                orderable: false, 
                searchable: false 
            }
        ],
    });
};

/**
 * Muestra diálogo de confirmación para eliminar provincia
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
            window.location.href = `/provincias/${id}/destroy`;
        }
    });
};