/**
 * Configuración para editar cuenta por cobrar
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
    initForm();
    initCalculations();
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
            loadClubsByEvent(eventId);
            loadHotelSuppliersByEvent(eventId);
        } else {
            // Limpiar selectores
            $('#club_id').html('<option value="">Seleccione un club</option>').trigger('change');
            $('#supplier_id').html('<option value="">Seleccione un hotel</option>').trigger('change');
        }
    });

    // Evento para cambiar hospedaje
    $('#has_accommodation').on('change', function() {
        const hasAccommodation = $(this).val();
        if (hasAccommodation === '1') {
            $('#supplierField').show();
            $('#supplier_id').prop('required', true);
            showAllTableRows();
            // Si ya hay un evento seleccionado, cargar los proveedores hoteleros
            const eventId = $('#event_id').val();
            if (eventId) {
                loadHotelSuppliersByEvent(eventId);
            }
        } else {
            $('#supplierField').hide();
            $('#supplier_id').prop('required', false);
            $('#supplier_id').val('').trigger('change');
            showOnlyBasicRows();
        }
        // Recalcular totales después de cambiar la visibilidad
        calculateGrandTotal();
    });

    // Verificar estado inicial del hospedaje
    const initialAccommodation = $('#has_accommodation').val();
    if (initialAccommodation === '1') {
        $('#supplierField').show();
        $('#supplier_id').prop('required', true);
        showAllTableRows();
        // Si ya hay un evento seleccionado al cargar la página, cargar los proveedores
        const eventId = $('#event_id').val();
        if (eventId) {
            loadHotelSuppliersByEvent(eventId);
        }
    } else {
        $('#supplierField').hide();
        $('#supplier_id').prop('required', false);
        showOnlyBasicRows();
    }
};

/**
 * Carga los clubs asignados al evento seleccionado
 */
const loadClubsByEvent = (eventId) => {
    // Guardar el valor actualmente seleccionado
    const currentClubId = $('#club_id').val();
    
    // Mostrar loading
    $('#club_id').html('<option value="">Cargando clubs...</option>').prop('disabled', true);

    fetch(`/cuenta-por-cobrar/events/${eventId}/clubs`)
        .then(response => response.json())
        .then(data => {
            let options = '<option value="">Seleccione un club</option>';
            
            if (data.length > 0) {
                data.forEach(club => {
                    const selected = club.id == currentClubId ? 'selected' : '';
                    options += `<option value="${club.id}" ${selected}>${club.name}</option>`;
                });
            } else {
                options = '<option value="">No hay clubs asignados a este evento</option>';
            }
            
            $('#club_id').html(options).prop('disabled', false).trigger('change');
        })
        .catch(error => {
            console.error('Error cargando clubs:', error);
            $('#club_id').html('<option value="">Error al cargar clubs</option>').prop('disabled', false);
        });
};

/**
 * Carga los proveedores hoteleros asignados al evento seleccionado
 */
const loadHotelSuppliersByEvent = (eventId) => {
    // Guardar el valor actualmente seleccionado
    const currentSupplierId = $('#supplier_id').val();
    
    // Mostrar loading
    $('#supplier_id').html('<option value="">Cargando hoteles...</option>').prop('disabled', true);

    fetch(`/cuenta-por-cobrar/events/${eventId}/hotel-suppliers`)
        .then(response => response.json())
        .then(data => {
            let options = '<option value="">Seleccione un hotel</option>';
            
            if (data.length > 0) {
                data.forEach(supplier => {
                    const selected = supplier.id == currentSupplierId ? 'selected' : '';
                    options += `<option value="${supplier.id}" ${selected}>${supplier.name}</option>`;
                });
            } else {
                options = '<option value="">No hay hoteles asignados a este evento</option>';
            }
            
            $('#supplier_id').html(options).prop('disabled', false).trigger('change');
        })
        .catch(error => {
            console.error('Error cargando hoteles:', error);
            $('#supplier_id').html('<option value="">Error al cargar hoteles</option>').prop('disabled', false);
        });
};

/**
 * Muestra solo las filas básicas (jugadores y liberados)
 */
const showOnlyBasicRows = () => {
    // Ocultar filas de profesores, acompañantes y choferes
    $('#tableAccommodation tbody tr').each(function(index) {
        const rowText = $(this).find('td:first').text().trim();
        if (rowText === 'Profesores' || rowText === 'Acompañantes' || rowText === 'Choferes') {
            $(this).hide();
            // Limpiar valores de campos ocultos
            $(this).find('input').val('0');
        }
    });
};

/**
 * Muestra todas las filas de la tabla
 */
const showAllTableRows = () => {
    // Mostrar todas las filas
    $('#tableAccommodation tbody tr').show();
};

/**
 * Función para calcular total de una fila
 */
const calculateRowTotal = (quantityId, priceId, totalId) => {
    const quantity = parseInt(cleanIntegerFormat($(`#${quantityId}`).val())) || 0;
    const price = parseFloat(cleanNumberFormat($(`#${priceId}`).val())) || 0;
    const total = quantity * price;
    $(`#${totalId}`).val(formatNumber(total));
    return total;
};

/**
 * Función para calcular total general
 */
const calculateGrandTotal = () => {
    const hasAccommodation = $('#has_accommodation').val();
    
    const totalPlayers = calculateRowTotal('players_quantity', 'player_price', 'totalPlayers');
    const totalLiberated = calculateRowTotal('liberated_quantity', 'liberated_price', 'totalLiberated');
    
    let totalTeachers = 0;
    let totalCompanions = 0;
    let totalDrivers = 0;
    
    // Solo calcular otros totales si hay hospedaje
    if (hasAccommodation === '1') {
        totalTeachers = calculateRowTotal('teachers_quantity', 'teacher_price', 'totalTeachers');
        totalCompanions = calculateRowTotal('companions_quantity', 'companion_price', 'totalCompanions');
        totalDrivers = calculateRowTotal('drivers_quantity', 'driver_price', 'totalDrivers');
    }

    // Calcular total de personas
    let totalPeople = (parseInt(cleanIntegerFormat($('#players_quantity').val())) || 0) +
                     (parseInt(cleanIntegerFormat($('#liberated_quantity').val())) || 0);
    
    // Solo sumar otras personas si hay hospedaje
    if (hasAccommodation === '1') {
        totalPeople += (parseInt(cleanIntegerFormat($('#teachers_quantity').val())) || 0) +
                      (parseInt(cleanIntegerFormat($('#companions_quantity').val())) || 0) +
                      (parseInt(cleanIntegerFormat($('#drivers_quantity').val())) || 0);
    }

    // Calcular total general
    const grandTotal = totalPlayers + totalTeachers + totalCompanions + totalDrivers + totalLiberated;

    $('#totalPeople').val(formatInteger(totalPeople));
    $('#grandTotal').val(formatNumber(grandTotal));
    
    // Actualizar campo oculto
    $('#total_club').val(grandTotal.toFixed(0));
};

/**
 * Inicializa los cálculos automáticos
 */
const initCalculations = () => {

    // Eventos para calcular automáticamente
    $('#players_quantity, #player_price').on('input', () => calculateGrandTotal());
    $('#teachers_quantity, #teacher_price').on('input', () => calculateGrandTotal());
    $('#companions_quantity, #companion_price').on('input', () => calculateGrandTotal());
    $('#drivers_quantity, #driver_price').on('input', () => calculateGrandTotal());
    $('#liberated_quantity, #liberated_price').on('input', () => calculateGrandTotal());

    // Formatear campos de precio al escribir
    $('#player_price, #teacher_price, #companion_price, #driver_price, #liberated_price').on('input', function() {
        const value = $(this).val();
        const numericValue = value.replace(/[^\d,]/g, '');
        if (numericValue) {
            const number = parseFloat(numericValue.replace('.', ','));
            if (!isNaN(number)) {
                $(this).val(formatNumber(number));
            }
        }
    });

    // Formatear campos de cantidad al escribir (números enteros)
    $('#players_quantity, #teachers_quantity, #companions_quantity, #drivers_quantity, #liberated_quantity').on('input', function() {
        const value = $(this).val();
        const numericValue = value.replace(/[^\d]/g, '');
        if (numericValue) {
            const number = parseInt(numericValue);
            if (!isNaN(number)) {
                $(this).val(formatInteger(number));
            }
        }
    });

    // Calcular inicialmente
    calculateGrandTotal();
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
 * Formatea números enteros con puntos de miles
 */
const formatInteger = (number) => {
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
 * Limpia el formato de números enteros (quita puntos de miles)
 */
const cleanIntegerFormat = (value) => {
    if (!value) return '0';
    return value.toString().replace(/\./g, '');
};

/**
 * Validación del formulario antes de enviar
 */
document.getElementById('formClub').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Limpiar formato de números antes de enviar
    const totalAmount = cleanNumberFormat($('#grandTotal').val());
    $('#grandTotal').val(totalAmount);
    
    // Limpiar formato de todos los campos numéricos antes de enviar
    $('#players_quantity').val(cleanIntegerFormat($('#players_quantity').val()));
    $('#liberated_quantity').val(cleanIntegerFormat($('#liberated_quantity').val()));
    
    $('#player_price').val(cleanNumberFormat($('#player_price').val()));
    $('#liberated_price').val(cleanNumberFormat($('#liberated_price').val()));
    
    // Solo limpiar campos adicionales si hay hospedaje
    const hasAccommodation = $('#has_accommodation').val();
    if (hasAccommodation === '1') {
        $('#teachers_quantity').val(cleanIntegerFormat($('#teachers_quantity').val()));
        $('#companions_quantity').val(cleanIntegerFormat($('#companions_quantity').val()));
        $('#drivers_quantity').val(cleanIntegerFormat($('#drivers_quantity').val()));
        
        $('#teacher_price').val(cleanNumberFormat($('#teacher_price').val()));
        $('#companion_price').val(cleanNumberFormat($('#companion_price').val()));
        $('#driver_price').val(cleanNumberFormat($('#driver_price').val()));
    } else {
        // Si no hay hospedaje, establecer valores en 0 para campos ocultos
        $('#teachers_quantity').val('0');
        $('#companions_quantity').val('0');
        $('#drivers_quantity').val('0');
        $('#teacher_price').val('0');
        $('#companion_price').val('0');
        $('#driver_price').val('0');
    }
    
    // Validar que se haya seleccionado un evento y club
    const eventId = $('#event_id').val();
    const clubId = $('#club_id').val();
    
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
    
    if (!clubId) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Por favor seleccione un club',
            customClass: {
                confirmButton: 'btn btn-primary waves-effect waves-light'
                },
            buttonsStyling: false
        });
        return;
    }
    
    // Validar que el total sea mayor a 0
    if (parseFloat(totalAmount) <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Monto inválido',
            text: 'El total debe ser mayor a 0',
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
