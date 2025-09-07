/**
 * Configuración para edición de cambio de monedas
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

    // Evento para cambiar moneda origen
    $('#currency_id').on('change', function() {
        const currencyId = $(this).val();
        if (currencyId) {
            loadMethodPaymentsByCurrency(currencyId, 'method_payment_id');
        } else {
            $('#method_payment_id').html('<option value="">Seleccione un método de pago</option>').trigger('change');
        }
    });

    // Evento para cambiar moneda destino
    $('#currency_receptor_id').on('change', function() {
        const currencyId = $(this).val();
        if (currencyId) {
            loadMethodPaymentsByCurrency(currencyId, 'method_payment_receptor_id');
        } else {
            $('#method_payment_receptor_id').html('<option value="">Seleccione un método de pago</option>').trigger('change');
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
        calculateConvertedAmount();
    });

    // Formatear campo de tasa de cambio al escribir
    $('#exchange_rate').on('input', function() {
        const value = $(this).val();
        const numericValue = value.replace(/[^\d,]/g, '');
        if (numericValue) {
            const number = parseFloat(numericValue.replace('.', ','));
            if (!isNaN(number)) {
                $(this).val(formatNumber(number));
            }
        }
        calculateConvertedAmount();
    });

    // Evento para cambiar tipo de operación
    $('#type_operation').on('change', function() {
        calculateConvertedAmount();
    });

    // Cargar métodos de pago iniciales para edición
    const currencyId = $('#currency_id').val();
    const currencyReceptorId = $('#currency_receptor_id').val();
    const methodPaymentId = $('#method_payment_id').val();
    const methodPaymentReceptorId = $('#method_payment_receptor_id').val();
    
    if (currencyId) {
        loadMethodPaymentsByCurrency(currencyId, 'method_payment_id', methodPaymentId);
    }
    
    if (currencyReceptorId) {
        loadMethodPaymentsByCurrency(currencyReceptorId, 'method_payment_receptor_id', methodPaymentReceptorId);
    }

    // Inicializar Select2 para el campo tipo_operacion
    $('#type_operation').select2({
        placeholder: 'Seleccione un tipo de operación',
        allowClear: false
    });

    // Calcular monto convertido inicial después de un pequeño delay para asegurar que Select2 esté listo
    setTimeout(() => {
        calculateConvertedAmount();
    }, 500);
};

/**
 * Carga los métodos de pago por moneda
 */
const loadMethodPaymentsByCurrency = (currencyId, targetSelectId, selectedValue = null) => {
    // Mostrar loading
    $(`#${targetSelectId}`).html('<option value="">Cargando métodos de pago...</option>').prop('disabled', true);

    fetch(`/cambio-de-monedas/currencies/${currencyId}/method-payments`)
        .then(response => response.json())
        .then(data => {
            console.log(data);
            let options = '<option value="">Seleccione un método de pago</option>';

            if (data.length > 0) {
                data.forEach(method => {
                    const isSelected = selectedValue && method.id == selectedValue ? 'selected' : '';
                    options += `<option value="${method.id}" ${isSelected}>${method.account_holder} - ${method.entity.name} - ${method.type_account} - ${method.current_balance}</option>`;
                });
            } else {
                options = '<option value="">No hay métodos de pago para esta moneda</option>';
            }

            $(`#${targetSelectId}`).html(options).prop('disabled', false).trigger('change');
        })
        .catch(error => {
            console.error('Error cargando métodos de pago:', error);
            $(`#${targetSelectId}`).html('<option value="">Error al cargar métodos de pago</option>').prop('disabled', false);
        });
};

/**
 * Formatea números con comas para mejor visualización
 */
const formatNumber = (number) => {
    return new Intl.NumberFormat('es-ES', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
        useGrouping: true
    }).format(number);
};

/**
 * Limpia el formato de números (quita comas) antes de enviar
 */
const cleanNumberFormat = (value) => {
    if (!value) return '0';
    // Remover puntos de miles y cambiar coma decimal por punto
    return value.toString().replace(/\./g, '').replace(',', '.');
};

/**
 * Calcula el monto convertido
 */
const calculateConvertedAmount = () => {
    const amount = cleanNumberFormat($('#amount').val());
    const exchangeRate = cleanNumberFormat($('#exchange_rate').val());
    const typeOperation = $('#type_operation').val();
    
    console.log('Calculando monto convertido:', {
        amount: amount,
        exchangeRate: exchangeRate,
        typeOperation: typeOperation
    });
    
    if (amount && exchangeRate && typeOperation) {
        let converted;
        
        if (typeOperation === 'Multiplicacion') {
            // Multiplicación: monto * tasa
            converted = parseFloat(amount) * parseFloat(exchangeRate);
        } else if (typeOperation === 'Division') {
            // División: monto / tasa
            converted = parseFloat(amount) / parseFloat(exchangeRate);
        } else {
            // Por defecto, usar multiplicación
            converted = parseFloat(amount) * parseFloat(exchangeRate);
        }
        
        console.log('Monto convertido calculado:', converted);
        $('#amount_converted').val(formatNumber(converted));
    } else {
        console.log('Faltan datos para calcular');
        $('#amount_converted').val('');
    }
};

/**
 * Validación del formulario antes de enviar
 */
document.getElementById('formChangeCurrency').addEventListener('submit', function(e) {
    e.preventDefault();

    // Limpiar formato de números antes de enviar
    const amount = cleanNumberFormat($('#amount').val());
    const exchangeRate = cleanNumberFormat($('#exchange_rate').val());
    
    $('#amount').val(amount);
    $('#exchange_rate').val(exchangeRate);

    // Validar campos requeridos
    const currencyId = $('#currency_id').val();
    const methodPaymentId = $('#method_payment_id').val();
    const currencyReceptorId = $('#currency_receptor_id').val();
    const methodPaymentReceptorId = $('#method_payment_receptor_id').val();
    const date = $('#date').val();

    if (!currencyId) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Por favor seleccione una moneda origen'
        });
        return;
    }

    if (!methodPaymentId) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Por favor seleccione un método de pago origen'
        });
        return;
    }

    if (!currencyReceptorId) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Por favor seleccione una moneda destino'
        });
        return;
    }

    if (!methodPaymentReceptorId) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Por favor seleccione un método de pago destino'
        });
        return;
    }

    if (!date) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Por favor seleccione una fecha'
        });
        return;
    }

    // Validar que el monto sea mayor a 0
    if (parseFloat(amount) <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Monto inválido',
            text: 'El monto debe ser mayor a 0'
        });
        return;
    }

    // Validar que la tasa de cambio sea mayor a 0
    if (parseFloat(exchangeRate) <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Tasa de cambio inválida',
            text: 'La tasa de cambio debe ser mayor a 0'
        });
        return;
    }

    // Validar tipo de operación
    const typeOperation = $('#type_operation').val();
    if (!typeOperation) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Por favor seleccione un tipo de operación'
        });
        return;
    }

    // Validar que origen y destino no sean iguales
    if (methodPaymentId === methodPaymentReceptorId) {
        Swal.fire({
            icon: 'warning',
            title: 'Métodos de pago iguales',
            text: 'El método de pago origen y destino no pueden ser iguales'
        });
        return;
    }

    // Si todo está bien, enviar el formulario
    this.submit();
});
