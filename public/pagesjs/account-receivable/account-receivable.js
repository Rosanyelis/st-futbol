'use strict';

document.addEventListener('DOMContentLoaded', () => {
    // Constantes y configuraciones iniciales
    const numberFormat = new Intl.NumberFormat('es-MX');
    const SELECTORS = {
        datatable: '.datatables',
        payOrderModal: '#PayOrderModal',
        preorderId: '#modalpreorden_id',
        receivableId: '#receivable_id',
        amountInput: '#amount',
        modalAmount: '#modalamount',
        formAction: '#formPayOrder',
        totalPendiente: '#totalPendiente',
        eventFilter: '#event_filter',
        statusFilter: '#status_filter'
    };

    // Funciones utilitarias
    const formatCurrency = data => `$ ${numberFormat.format(data)}`;
    const formatDate = data => moment(data).format('DD/MM/YYYY');
    const formatStatus = (data, type, row) => {
        const statusClasses = {
            'Pendiente': 'badge bg-label-warning',
            'En Proceso': 'badge bg-label-info',
            'Completado': 'badge bg-label-success',
            'Vencido': 'badge bg-label-danger'
        };
        return `<span class="${statusClasses[data] || 'badge bg-label-secondary'}">${data}</span>`;
    };

    // Configuración DataTable
    const initDataTable = () => {
        const dtElement = document.querySelector(SELECTORS.datatable);
        if (!dtElement) return null;

        return $(dtElement).DataTable({
            processing: true,
            ajax: {
                url: "/cuenta-por-cobrar",
                data: function(d) {
                    d.event_id = $(SELECTORS.eventFilter).val();
                    d.status = $(SELECTORS.statusFilter).val();
                }
            },
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
                { data: 'club_name', name: 'club_name' },
                { data: 'event_name', name: 'event_name' },
                { data: 'currency_name', name: 'currency_name' },
                { data: 'total_amount', name: 'total_amount' },
                { data: 'paid_amount', name: 'paid_amount' },
                { data: 'pending_amount', name: 'pending_amount' },
                { data: 'status', render: formatStatus, name: 'status' },
                { data: 'actions', orderable: false, searchable: false, name: 'actions' }
            ],
        });
    };

    // Función para manejar pagos
    const payOrder = (id, amount) => {
        const formattedAmount = numberFormat.format(amount);

        // Actualizar elementos del modal
        $(SELECTORS.preorderId).text(id);
        $(SELECTORS.amountInput).val(amount);
        $(SELECTORS.modalAmount).text(formattedAmount);
        $(SELECTORS.receivableId).val(id);
        $(SELECTORS.payOrderModal).modal('show');
    };

    // Función para ver pagos
    const viewPayments = (id) => {
        // Implementar vista de pagos
        Swal.fire({
            title: 'Historial de Pagos',
            text: 'Funcionalidad en desarrollo',
            icon: 'info',
            customClass: {
                confirmButton: 'btn btn-primary waves-effect waves-light'
                },
            buttonsStyling: false
        });
    };

    // Suma el total pendiente por moneda y actualiza los elementos por id
    function updateTotalPendientePorMoneda(data) {
        const totales = {};

        data.forEach(item => {
            const moneda = item.currency_name || 'Desconocida';
            if (!totales[moneda]) totales[moneda] = 0;
            
            // Usar el valor raw para cálculos (sin formato)
            const pendingAmount = item.pending_amount_raw || 0;
            
            totales[moneda] += pendingAmount;
        });

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

        // Filtros
        $(SELECTORS.eventFilter).on('change', function() {
            dataTable.ajax.reload();
        });

        $(SELECTORS.statusFilter).on('change', function() {
            dataTable.ajax.reload();
        });
    }

    // Manejar envío del formulario de pago
    $(SELECTORS.formAction).on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire('Éxito', response.message, 'success');
                    $(SELECTORS.payOrderModal).modal('hide');
                    dataTable.ajax.reload();
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                Swal.fire('Error', response.message || 'Error al procesar el pago', 'error');
            }
        });
    });

    // Función para mostrar mensaje de no poder editar
    const showCannotEditMessage = () => {
        Swal.fire({
            title: 'No se puede editar',
            html: `
                <p>Esta cuenta por cobrar no se puede editar porque tiene pagos registrados.</p>
                <p><strong>Recomendación:</strong> Cree una nueva cuenta por cobrar para registrar cambios adicionales.</p>
            `,
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
            html: `
                <p>Esta cuenta por cobrar no se puede eliminar porque tiene pagos registrados.</p>
                <p>Para mantener la integridad de los datos, solo se pueden eliminar cuentas sin pagos.</p>
            `,
            icon: 'warning',
            confirmButtonText: 'Entendido',
            customClass: {
                confirmButton: 'btn btn-primary waves-effect waves-light'
                },
            buttonsStyling: false
        });
    };

    // Función para eliminar cuenta por cobrar
    const deleteAccountReceivable = (id) => {
        Swal.fire({
            title: '¿Está seguro?',
            text: 'Esta acción eliminará permanentemente la cuenta por cobrar. Esta acción no se puede deshacer.',
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
                    },
                    customClass: {
                        confirmButton: 'btn btn-primary waves-effect waves-light'
                        },
                    buttonsStyling: false
                });

                // Realizar petición AJAX
                fetch(`/cuenta-por-cobrar/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Eliminado',
                            text: data.message,
                            icon: 'success',
                            customClass: {
                                confirmButton: 'btn btn-primary waves-effect waves-light'
                                },
                            buttonsStyling: false
                        }).then(() => {
                            // Recargar DataTable
                            if (dataTable) {
                                dataTable.ajax.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.message,
                            icon: 'error',
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
                        title: 'Error',
                        text: 'Ocurrió un error al eliminar la cuenta por cobrar',
                        icon: 'error',
                        customClass: {
                            confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                        buttonsStyling: false
                    });
                });
            }
        });
    };

    // Hacer las funciones accesibles globalmente
    window.payOrder = payOrder;
    window.viewPayments = viewPayments;
    window.showCannotEditMessage = showCannotEditMessage;
    window.showCannotDeleteMessage = showCannotDeleteMessage;
    window.deleteAccountReceivable = deleteAccountReceivable;
});
