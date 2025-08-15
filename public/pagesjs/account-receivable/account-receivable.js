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
            'Parcial': 'badge bg-label-info',
            'Pagado': 'badge bg-label-success',
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
                { data: 'total_amount', render: formatCurrency, name: 'total_amount' },
                { data: 'paid_amount', render: formatCurrency, name: 'paid_amount' },
                { data: 'pending_amount', render: formatCurrency, name: 'pending_amount' },
                { data: 'status', render: formatStatus, name: 'status' },
                { data: 'due_date', render: formatDate, name: 'due_date' },
                { data: 'payment_percentage', name: 'payment_percentage' },
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
            icon: 'info'
        });
    };

    // Suma el total pendiente por moneda y actualiza los elementos por id
    function updateTotalPendientePorMoneda(data) {
        const totales = {};

        data.forEach(item => {
            const moneda = item.currency_name || 'Desconocida';
            if (!totales[moneda]) totales[moneda] = 0;
            totales[moneda] += parseFloat(item.pending_amount || 0);
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

    // Hacer las funciones accesibles globalmente
    window.payOrder = payOrder;
    window.viewPayments = viewPayments;
});
