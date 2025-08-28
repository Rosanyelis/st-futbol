"use strict";

// Configuración y constantes
const CONFIG = {
    endpoints: {
        cancelMovement: (id) => `/event-movements/${id}/cancel`,
        getMovementsByEvent: (eventId) => `/event-movements/event/${eventId}`,
        getMovementsByClub: (clubId) => `/event-movements/club/${clubId}`
    }
};

// Clase principal para manejar movimientos de eventos
class EventMovementManager {
    constructor() {
        this.initializeEventListeners();
    }

    // Inicialización de event listeners
    initializeEventListeners() {
        // Botón para cancelar movimientos
        $(document).on('click', '.btn-cancel-movement', (e) => {
            e.preventDefault();
            const movementId = $(e.currentTarget).data('movement-id');
            this.cancelMovement(movementId);
        });
    }

    // Cancelar un movimiento
    cancelMovement(movementId) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción cancelará el movimiento y restaurará el dinero en la cuenta correspondiente. ¿Deseas continuar?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'No, mantener',
            customClass: {
                confirmButton: 'btn btn-danger me-3 waves-effect waves-light',
                cancelButton: 'btn btn-outline-secondary waves-effect'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                this.processCancellation(movementId);
            }
        });
    }

    // Procesar la cancelación
    processCancellation(movementId) {
        // Mostrar loading
        Swal.fire({
            title: 'Cancelando movimiento...',
            text: 'Por favor espera mientras se procesa la cancelación',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Realizar la petición AJAX
        $.ajax({
            url: CONFIG.endpoints.cancelMovement(movementId),
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (response) => {
                if (response.success) {
                    Swal.fire({
                        title: '¡Movimiento cancelado!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'Entendido'
                    }).then(() => {
                        // Recargar la página o actualizar la tabla
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.message || 'Error al cancelar el movimiento',
                        icon: 'error',
                        confirmButtonText: 'Entendido'
                    });
                }
            },
            error: (xhr, status, error) => {
                let errorMessage = 'Error al cancelar el movimiento';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    title: 'Error',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
            }
        });
    }

    // Obtener movimientos por evento
    getMovementsByEvent(eventId, callback) {
        $.ajax({
            url: CONFIG.endpoints.getMovementsByEvent(eventId),
            type: 'GET',
            success: callback,
            error: (xhr, status, error) => {
                console.error('Error al obtener movimientos del evento:', error);
                if (callback) callback([]);
            }
        });
    }

    // Obtener movimientos por club
    getMovementsByClub(clubId, callback) {
        $.ajax({
            url: CONFIG.endpoints.getMovementsByClub(clubId),
            type: 'GET',
            success: callback,
            error: (xhr, status, error) => {
                console.error('Error al obtener movimientos del club:', error);
                if (callback) callback([]);
            }
        });
    }
}

// Inicialización cuando el documento está listo
$(function() {
    window.eventMovementManager = new EventMovementManager();
});
