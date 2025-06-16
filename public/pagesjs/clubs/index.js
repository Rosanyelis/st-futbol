/**
 * Configuración avanzada de DataTables para Clubs
 * - Inicialización segura con DOMContentLoaded
 * - Configuración modularizada
 * - Mejor manejo de eventos
 * - Sintaxis ES6+
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
    initClubsTable();
});

const numberFormat = new Intl.NumberFormat("es-MX");
/**
 * Inicializa y configura DataTable para países
 */
const initClubsTable = () => {
    const dataTable = $('.datatables');
    
    if (!dataTable.length) return;

    dataTable.DataTable({
        processing: true,
        serverSide: true,
        url: "/clubs",
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
            { data: 'event', name: 'event' },
            { data: 'responsible', name: 'responsible' },
            { data: 'phone', name: 'phone' },
            { data: 'email', name: 'email' },
            { data: 'country', name: 'country' },
            { data: 'supplier', name: 'supplier' },
            { data: 'category_supplier', name: 'category_supplier' },
            { data: 'currency', name: 'currency' },
            { data: 'players_quantity', name: 'players_quantity' },
            { data: 'player_price', name: 'player_price' },
            { data: 'total_players', name: 'total_players' },
            { data: 'teachers_quantity', name: 'teachers_quantity' },
            { data: 'teacher_price', name: 'teacher_price' },
            { data: 'total_teachers', name: 'total_teachers' },
            { data: 'companions_quantity', name: 'companions_quantity' },
            { data: 'companion_price', name: 'companion_price' },
            { data: 'total_companions', name: 'total_companions' },
            { data: 'drivers_quantity', name: 'drivers_quantity' },
            { data: 'driver_price', name: 'driver_price' },
            { data: 'total_drivers', name: 'total_drivers' },
            { data: 'liberated_quantity', name: 'liberated_quantity' },
            { data: 'total_people', name: 'total_people' },
            { data: 'total_amount', name: 'total_amount' },
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
                                <img src="${row.logo}" alt="${row.name}" class="rounded-circle">
                            </div>
                        </div>  
                        <div class="d-flex flex-column">
                            <a href="app-user-view-account.html" class="text-heading text-truncate">
                                <span class="fw-medium">${row.name}</span>
                            </a>
                        </div>
                    </div>
                    `;
                }
            },
            {
                targets: [10, 11, 13, 14, 16, 17, 19, 20, 23],
                searchable: true,
                render: function(data, type, row) {
                    return `
                    <span>${numberFormat.format(data)}</span>
                    `;
                }
            }
        ]
    });
};

/**
 * Muestra diálogo de confirmación para eliminar país
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
            window.location.href = `/clubs/${id}/destroy`;
        }
    });
};