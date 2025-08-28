/**
 * Configuración para crear cuenta por pagar
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
    initForm();
});

/**
 * Inicializa el formulario
 */
const initForm = () => {
    // Inicializar Select2
    $('.select2').select2({
        placeholder: 'Seleccione una opción',
        allowClear: true
    });

    // Evento para cambiar evento
    $('#event_id').on('change', function() {
        const eventId = $(this).val();
        if (eventId) {
            loadSuppliersByEvent(eventId);
        } else {
            // Limpiar selector de proveedores
            $('#supplier_id').html('<option value="">Seleccione un proveedor</option>').trigger('change');
        }
    });

    // Formatear campo de monto al escribir
    $('#amount').on('input', function() {
        const value = $(this).val();
        const numericValue = value.replace(/[^\d,]/g, '');
        if (numericValue) {
            const number = parseFloat(numericValue.replace('.', ','));
            if (!isNaN(number)) {
                $(this).val(formatNumber(number));
            }
        }
    });
};

/**
 * Carga los proveedores asignados al evento seleccionado
 */
const loadSuppliersByEvent = (eventId) => {
    // Mostrar loading
    $('#supplier_id').html('<option value="">Cargando proveedores...</option>').prop('disabled', true);

    fetch(`/cuenta-por-pagar/events/${eventId}/suppliers`)
        .then(response => response.json())
        .then(data => {
            let options = '<option value="">Seleccione un proveedor</option>';
            
            if (data.length > 0) {
                data.forEach(supplier => {
                    options += `<option value="${supplier.id}">${supplier.name}</option>`;
                });
            } else {
                options = '<option value="">No hay proveedores asignados a este evento</option>';
            }
            
            $('#supplier_id').html(options).prop('disabled', false).trigger('change');
        })
        .catch(error => {
            console.error('Error cargando proveedores:', error);
            $('#supplier_id').html('<option value="">Error al cargar proveedores</option>').prop('disabled', false);
        });
};

/**
 * Formatea números con comas para mejor visualización
 */
const formatNumber = (number) => {
    return new Intl.NumberFormat('es-ES', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
        useGrouping: true
    }).format(number);
};

/**
 * Limpia el formato de números (quita comas) antes de enviar
 */
const cleanNumberFormat = (value) => {
    if (!value) return '0';
    return value.toString().replace(/\./g, '').replace(',', '.');
};

/**
 * Validación del formulario antes de enviar
 */
document.getElementById('formAccountPayable').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Limpiar formato de números antes de enviar
    const amount = cleanNumberFormat($('#amount').val());
    $('#amount').val(amount);
    
    // Validar que se haya seleccionado un evento y proveedor
    const eventId = $('#event_id').val();
    const supplierId = $('#supplier_id').val();
    const currencyId = $('#currency_id').val();
    
    if (!eventId) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Por favor seleccione un evento',
            customClass: {
                confirmButton: 'btn btn-primary waves-effect waves-light'
                },
            buttonsStyling: false
        });
        return;
    }
    
    if (!supplierId) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Por favor seleccione un proveedor',
            customClass: {
                confirmButton: 'btn btn-primary waves-effect waves-light'
                },
            buttonsStyling: false
        });
        return;
    }

    if (!currencyId) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Por favor seleccione una moneda',
            customClass: {
                confirmButton: 'btn btn-primary waves-effect waves-light'
                },
            buttonsStyling: false
        });
        return;
    }
    
    // Validar que el monto sea mayor a 0
    if (parseFloat(amount) <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Monto inválido',
            text: 'El monto debe ser mayor a 0',
            customClass: {
                confirmButton: 'btn btn-primary waves-effect waves-light'
                },
            buttonsStyling: false
        });
        return;
    }
    
    // Si todo está bien, enviar el formulario
    this.submit();
});
