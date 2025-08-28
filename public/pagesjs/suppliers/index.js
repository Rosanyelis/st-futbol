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
        ajax: {
            url: "/proveedores",
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
            { data: 'category_supplier_name', name: 'category_supplier_name' },
            { data: 'subcategory_supplier_name', name: 'subcategory_supplier_name' },
            { data: 'name', name: 'name' },
            { data: 'representant', name: 'representant' },
            { data: 'phone', name: 'phone' },
            { data: 'description', name: 'description' },
            { data: 'events_count', name: 'events_count' },
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
 * Abre la modal para asignar eventos a un proveedor
 * @param {number} supplierId - ID del proveedor
 */
const openAssignEventsModal = (supplierId) => {
    // Limpiar formulario
    document.getElementById('assignEventsForm').reset();
    document.getElementById('supplierId').value = supplierId;
    
    // Cargar eventos disponibles
    loadAvailableEvents(supplierId);

    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('assignEventsModal'));
    modal.show();
};

/**
 * Carga los eventos disponibles para asignar al proveedor
 * @param {number} supplierId - ID del proveedor
 */
const loadAvailableEvents = (supplierId) => {
    fetch(`/proveedores/${supplierId}/available-events-modal`)
        .then(response => response.json())
        .then(data => {
            const eventSelect = document.getElementById('eventSelect');
            eventSelect.innerHTML = '<option value="">Seleccione un evento...</option>';
            
            data.forEach(event => {
                const option = document.createElement('option');
                option.value = event.id;
                option.textContent = event.name;
                eventSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error cargando eventos:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al cargar los eventos disponibles',
                customClass: {
                    confirmButton: 'btn btn-primary waves-effect waves-light'
                    },
                buttonsStyling: false
            });
        });
};

/**
 * Asigna un evento al proveedor
 */
const assignEventToSupplier = () => {
    const form = document.getElementById('assignEventsForm');
    const formData = new FormData(form);
    
    // Validar formulario
    if (!formData.get('event_id')) {
        Swal.fire({
            icon: 'warning',
            title: 'Campos requeridos',
            text: 'Por favor seleccione un evento'
        });
        return;
    }
    
    const supplierId = formData.get('supplier_id');
    
    // Mostrar loading
    Swal.fire({
        title: 'Asignando evento...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
        customClass: {
            confirmButton: 'btn btn-primary waves-effect waves-light'
            },
        buttonsStyling: false
    });
    
    fetch(`/proveedores/${supplierId}/assign-event-modal`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            event_id: formData.get('event_id')
        })
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
            
            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('assignEventsModal'));
            modal.hide();
            
            // Recargar DataTable para actualizar el contador
            $('.datatables').DataTable().ajax.reload();
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
            text: 'Error al asignar el evento al proveedor',
            customClass: {
                confirmButton: 'btn btn-primary waves-effect waves-light'
                },
            buttonsStyling: false
        });
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