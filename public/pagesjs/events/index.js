/**
 * Configuración avanzada de DataTables para Tipos de Gastos
 * - Inicialización segura con DOMContentLoaded
 * - Configuración modularizada
 * - Mejor manejo de eventos
 * - Sintaxis ES6+
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
    initEventsTable();
});

/**
 * Inicializa y configura DataTable para tipos de gastos
 */
const initEventsTable = () => {
    const dataTable = $('.datatables');
    
    if (!dataTable.length) return;

    dataTable.DataTable({
        processing: true,
            ajax: {
                url: "/eventos",
            },
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
            { data: 'start_date', name: 'start_date' },
            { data: 'end_date', name: 'end_date' },
            { data: 'year', name: 'year' },
            { 
                data: 'actions', 
                name: 'actions', 
                orderable: false, 
                searchable: false 
            }
        ],
        columnDefs: [
            {
                targets: [0],
                searchable: true,
                render: function(data, type, row) {
                    // agregar junto con el nombre la imagen del evento, la imagen en redondo
                    return `
                    <div class="d-flex justify-content-start align-items-center user-name">
                        <div class="avatar-wrapper">
                            <div class="avatar avatar-sm me-4">
                                <img src="storage/${row.url_images}" alt="${row.name}" class="rounded-circle">
                            </div>
                        </div>  
                        <div class="d-flex flex-column">
                            <a href="/eventos/${row.id}/history" class="text-heading text-truncate">
                                <span class="fw-medium">${row.name}</span>
                            </a>
                        </div>
                    </div>
                    `;
                }
            },
            {
                targets: [1],
                searchable: true,
                render: function(data, type, row) {
                    // me esta restando un dia a la fecha de inicio
                    return moment(row.start_date).add(1, 'day').format('DD/MM/YYYY');
                }
            },
            {
                targets: [2],
                searchable: true,
                render: function(data, type, row) {
                    return moment(row.end_date).add(1, 'day').format('DD/MM/YYYY');
                }
            },
            {
                targets: [3],
                searchable: true,
                render: function(data, type, row) {
                    return row.year;
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
            window.location.href = `/eventos/${id}/destroy`;
        }
    });
};