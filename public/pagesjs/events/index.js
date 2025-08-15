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
            { data: 'clubs_count', name: 'clubs_count' },
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
            },
            {
                targets: [4],
                searchable: true,
                render: function(data, type, row) {
                    return row.clubs_count;
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

/**
 * Abre la modal para asignar clubs a un evento
 * @param {number} eventId - ID del evento
 */
const openAssignClubsModal = (eventId) => {
    // Limpiar formulario
    document.getElementById('assignClubsForm').reset();
    document.getElementById('eventId').value = eventId;
    
    // Cargar clubs disponibles
    loadAvailableClubs(eventId);
    
    // Cargar años disponibles
    loadAvailableYears();
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('assignClubsModal'));
    modal.show();
};

/**
 * Carga los clubs disponibles para asignar al evento
 * @param {number} eventId - ID del evento
 */
const loadAvailableClubs = (eventId) => {
    fetch(`/eventos/${eventId}/available-clubs-modal`)
        .then(response => response.json())
        .then(data => {
            const clubSelect = document.getElementById('clubSelect');
            clubSelect.innerHTML = '<option value="">Seleccione un club...</option>';
            
            data.forEach(club => {
                const option = document.createElement('option');
                option.value = club.id;
                option.textContent = club.name;
                clubSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error cargando clubs:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al cargar los clubs disponibles'
            });
        });
};

/**
 * Carga los años disponibles para el evento
 */
const loadAvailableYears = () => {
    const yearSelect = document.getElementById('yearSelect');
    yearSelect.innerHTML = '<option value="">Seleccione un año...</option>';
    
    // Obtener el año actual y los próximos 5 años
    const currentYear = new Date().getFullYear();
    for (let i = 0; i < 6; i++) {
        const year = currentYear + i;
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        yearSelect.appendChild(option);
    }
};

/**
 * Asigna un club al evento
 */
const assignClubToEvent = () => {
    const form = document.getElementById('assignClubsForm');
    const formData = new FormData(form);
    
    // Validar formulario
    if (!formData.get('club_id') || !formData.get('year')) {
        Swal.fire({
            icon: 'warning',
            title: 'Campos requeridos',
            text: 'Por favor seleccione un club y un año'
        });
        return;
    }
    
    const eventId = formData.get('event_id');
    
    // Mostrar loading
    Swal.fire({
        title: 'Asignando club...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch(`/eventos/${eventId}/assign-club-modal`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            club_id: formData.get('club_id'),
            year: formData.get('year')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: data.message
            });
            
            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('assignClubsModal'));
            modal.hide();
            
            // Recargar DataTable para actualizar el contador
            $('.datatables').DataTable().ajax.reload();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al asignar el club al evento'
        });
    });
};