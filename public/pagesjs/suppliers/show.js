/**
 * Configuración de DataTables para eventos asignados a un proveedor
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
    initAssignedEventsTable();
});

/**
 * Inicializa y configura DataTable para eventos asignados
 */
const initAssignedEventsTable = () => {
    const dataTable = $('.datatables-events');
    const urlBase = '/storage/';
    if (!dataTable.length) return;

    // Obtener el ID del proveedor de la URL
    const supplierId = window.location.pathname.split('/')[2];

    dataTable.DataTable({
        processing: true,
        ajax: {
            url: `/proveedores/${supplierId}/assigned-events`,
        },
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
            { data: 'payables_count', name: 'payables_count' },
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
                    return `
                    <div class="d-flex justify-content-start align-items-center user-name">
                        <div class="avatar-wrapper">
                            <div class="avatar avatar-sm me-4">
                                <img src="${urlBase}${row.url_images}" alt="${row.name}" class="rounded-circle">
                            </div>
                        </div>  
                        <div class="d-flex flex-column">
                            <span class="fw-medium">${row.name}</span>
                        </div>
                    </div>
                    `;
                }
            },
            {
                targets: [1],
                searchable: true,
                render: function(data, type, row) {
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
            },
            {
                targets: [4],
                searchable: true,
                render: function(data, type, row) {
                    if (row.payables_count > 0) {
                        return `<span class="badge bg-warning">${row.payables_count} cuenta(s)</span>`;
                    } else {
                        return '<span class="badge bg-success">Sin cuentas</span>';
                    }
                }
            }
        ]
    });
};

/**
 * Eliminar asignación de evento a proveedor
 * @param {number} supplierId - ID del proveedor
 * @param {number} eventId - ID del evento
 */
const deleteEventAssignment = (supplierId, eventId) => {
    Swal.fire({
        title: '¿Está seguro de eliminar esta asignación?',
        text: "Esta acción no se puede deshacer",
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
            // Mostrar loading
            Swal.fire({
                title: 'Eliminando asignación...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
                customClass: {
                    confirmButton: 'btn btn-primary waves-effect waves-light'
                    },
                buttonsStyling: false
            });
            
            fetch(`/proveedores/${supplierId}/events/${eventId}/detach`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: data.message,
                        customClass: {
                            confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                        buttonsStyling: false
                    });
                    
                    // Recargar DataTable
                    $('.datatables-events').DataTable().ajax.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        customClass: {
                            confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                        buttonsStyling: false
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al eliminar la asignación',
                    customClass: {
                        confirmButton: 'btn btn-primary waves-effect waves-light'
                        },
                    buttonsStyling: false
                });
            });
        }
    });
};
