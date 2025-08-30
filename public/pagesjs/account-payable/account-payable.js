'use strict';

document.addEventListener('DOMContentLoaded', () => {
    // Constantes y configuraciones iniciales
    const numberFormat = new Intl.NumberFormat('es-MX');
    const SELECTORS = {
        datatable: '.datatables',
        payOrderModal: '#PayOrderModal',
        supplierId: '#supplier_id',
        amountInput: '#amount',
        modalAmount: '#modalamount',
        formAction: '#formPayOrder',
        totalPendiente: '#totalPendiente'
    };

    // Funciones utilitarias
    const formatCurrency = data => `$ ${numberFormat.format(data)}`;
    const formatStatus = (data, type, row) => {
        const statusClasses = {
            'Pendiente': 'badge bg-label-warning',
            'En Proceso': 'badge bg-label-info',
            'Completado': 'badge bg-label-success',
            'Vencido': 'badge bg-label-danger'
        };
        return `<span class="${statusClasses[data] || 'badge bg-label-secondary'}">${data}</span>`;
    };
    // const formatDate = data => moment(data).format('DD/MM/YYYY');
    // const calculateTotal = data => data.reduce((sum, item) => sum + parseFloat(item.total_pendiente || 0), 0);

    // Configuración DataTable
    const initDataTable = () => {
        const dtElement = document.querySelector(SELECTORS.datatable);
        if (!dtElement) return null;

        return $(dtElement).DataTable({
            processing: true,
            ajax: {
                url: "/cuenta-por-pagar",
            },
            type: "POST",
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>><"table-responsive"t><"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            language: {
                url: "https://cdn.datatables.net/plug-ins/2.0.8/i18n/es-ES.json",
                paginate: {
                    next: '<i class="ri-arrow-right-s-line"></i>',
                    previous: '<i class="ri-arrow-left-s-line"></i>'
                }
            },
            lengthMenu: [10, 25, 50, 75, 100],
            columns: [
                { data: 'event_name' },
                { data: 'supplier_name' },
                { data: 'currency_name' },
                { data: 'amount', render: formatCurrency },
                { data: 'paid_amount', render: formatCurrency },
                { data: 'pending_amount', render: formatCurrency },
                { data: 'status', render: formatStatus },
                { data: 'actions', orderable: false, searchable: false }
            ],
        });
    };

    // Función para manejar pagos
    const payOrder = (id, amount, tipo) => {
        const formattedAmount = numberFormat.format(amount);
        // Limpiar el campo Select2 del método de pago antes de abrir el modal
        $('#method_payment_id').val(null).trigger('change');
        // Actualizar elementos del modal
        $('#account_payable_id').val(id);
        $('#amount').val(amount);
        $('#modalamount').text(formattedAmount);
        $(SELECTORS.payOrderModal).modal('show');
    };

    // Suma el total pendiente por moneda y actualiza los elementos por id
    function updateTotalPendientePorMoneda(data) {
        // data: array de objetos con al menos { currency_name, pending_amount }
        const totales = {};

        data.forEach(item => {
            const moneda = item.currency_name || 'Desconocida';
            if (!totales[moneda]) totales[moneda] = 0;
            totales[moneda] += parseFloat(item.pending_amount || 0);
        });

        // Actualiza los elementos por id: totalPendienteNOMBREMONEDA
        Object.entries(totales).forEach(([moneda, total]) => {
            // Elimina espacios y acentos para el id
            const id = 'totalPendiente' + moneda.normalize("NFD").replace(/[\u0300-\u036f\s]/g, '');
            const $el = document.getElementById(id);
            if ($el) {
                $el.textContent = `$ ${numberFormat.format(total)}`;
            }
        });
    }

    // Inicialización
    const dataTable = initDataTable();

    if (dataTable) {
        dataTable.on('xhr', function () {
            const json = dataTable.ajax.json();
            if (json && json.data) {
                updateTotalPendientePorMoneda(json.data);
            }
        });
    }

    // Hacer la función payOrder accesible globalmente si es necesario
    window.payOrder = payOrder;

    // Función para mostrar mensaje de no poder editar
    const showCannotEditMessage = () => {
        Swal.fire({
            title: 'No se puede editar',
            html: `<p>Esta cuenta por pagar no se puede editar porque tiene pagos registrados.</p><p><strong>Recomendación:</strong> Cree una nueva cuenta por pagar para registrar cambios adicionales.</p>`,
            icon: 'warning',
            confirmButtonText: 'Entendido',
            customClass: {
                confirmButton: 'btn btn-primary waves-effect waves-light'
                },
            buttonsStyling: false
        });
    };

    // Función para mostrar mensaje de no poder eliminar
    const showCannotDeleteMessage = () => {
        Swal.fire({
            title: 'No se puede eliminar',
            html: `<p>Esta cuenta por pagar no se puede eliminar porque tiene pagos registrados.</p><p>Para mantener la integridad de los datos, solo se pueden eliminar cuentas sin pagos.</p>`,
            icon: 'warning',
            confirmButtonText: 'Entendido',
            customClass: {
                confirmButton: 'btn btn-primary waves-effect waves-light'
                },
            buttonsStyling: false
        });
    };

    // Función para eliminar cuenta por pagar
    const deleteAccountPayable = (id) => {
        Swal.fire({
            title: '¿Está seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            customClass: {
                confirmButton: 'btn btn-primary waves-effect waves-light'
                },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loading
                Swal.fire({
                    title: 'Eliminando...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Realizar petición AJAX
                fetch(`/cuenta-por-pagar/${id}`, {
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
                            title: '¡Eliminado!',
                            text: data.message,
                            icon: 'success'
                        }).then(() => {
                            // Recargar la tabla
                            dataTable.ajax.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.message,
                            icon: 'error'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Ocurrió un error al eliminar la cuenta por pagar',
                        icon: 'error'
                    });
                });
            }
        });
    };

    // Hacer las funciones accesibles globalmente
    window.showCannotEditMessage = showCannotEditMessage;
    window.showCannotDeleteMessage = showCannotDeleteMessage;
    window.deleteAccountPayable = deleteAccountPayable;

    // Manejar el envío del formulario de pago
    $(document).on('submit', '#formPayOrder', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('#btnPayOrder');
        const originalText = submitBtn.text();
        
        // Validar campos requeridos
        const accountPayableId = $('#account_payable_id').val();
        const amount = $('#amount').val();
        const methodPaymentId = $('#method_payment_id').val();
        
        if (!accountPayableId || !amount || !methodPaymentId) {
            Swal.fire({
                title: 'Campos requeridos',
                text: 'Por favor complete todos los campos obligatorios',
                icon: 'warning',
                confirmButtonText: 'Entendido',
                customClass: {
                    confirmButton: 'btn btn-primary waves-effect waves-light'
                    },
                buttonsStyling: false
            });
            return;
        }
        
        // Mostrar loading
        submitBtn.prop('disabled', true).text('Procesando...');
        
        // Realizar petición AJAX
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: '¡Pago procesado!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'Entendido',
                        customClass: {
                            confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                        buttonsStyling: false
                    }).then(() => {
                        // Cerrar modal y recargar tabla
                        $('#PayOrderModal').modal('hide');
                        if (dataTable) {
                            dataTable.ajax.reload();
                        }
                        // Limpiar formulario
                        form[0].reset();
                        // Limpiar específicamente el campo Select2 del método de pago
                        $('#method_payment_id').val(null).trigger('change');
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.message || 'Error al procesar el pago',
                        icon: 'error',
                        confirmButtonText: 'Entendido',
                        customClass: {
                            confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                        buttonsStyling: false
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', xhr.responseText);
                
                let errorMessage = 'Error al procesar el pago';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    // Mostrar errores de validación
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage = errors.join('\n');
                } else if (xhr.status === 422) {
                    errorMessage = 'Error de validación en los datos enviados';
                } else if (xhr.status === 500) {
                    errorMessage = 'Error interno del servidor';
                }
                
                Swal.fire({
                    title: 'Error',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonText: 'Entendido',
                    customClass: {
                        confirmButton: 'btn btn-primary waves-effect waves-light'
                        },
                    buttonsStyling: false
                });
            },
            complete: function() {
                // Restaurar botón
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });

    // Inicializar Select2 para el método de pago
    $(document).ready(function() {
        $('#method_payment_id').select2({
            dropdownParent: $('#PayOrderModal'),
            placeholder: 'Seleccione método de pago',
            allowClear: true
        });
    });
});
