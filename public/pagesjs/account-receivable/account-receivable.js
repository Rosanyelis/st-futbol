'use strict';

document.addEventListener('DOMContentLoaded', () => {
    // Constantes y configuraciones iniciales
    const numberFormat = new Intl.NumberFormat('es-MX');
    const SELECTORS = {
        datatable: '.datatables',
        payOrderModal: '#PayOrderModal',
        preorderId: '#modalpreorden_id',
        orderId: '#order_id',
        clubId: '#club_id',
        amountInput: '#amount',
        modalAmount: '#modalamount',
        formAction: '#formPayOrder',
        totalPendiente: '#totalPendiente'
    };

    // Funciones utilitarias
    const formatCurrency = data => `$ ${numberFormat.format(data)}`;
    

    // Configuración DataTable
    const initDataTable = () => {
        const dtElement = document.querySelector(SELECTORS.datatable);
        if (!dtElement) return null;

        return $(dtElement).DataTable({
            processing: true,
            serverSide: true,
            url: "/cuenta-por-cobrar",
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
                { data: 'event_name', name: 'event_name' },
                { data: 'name', name: 'name' },
                { data: 'currency_name', name: 'currency_name' },
                { data: 'total_amount', render: formatCurrency, name: 'total_amount' },
                { data: 'saldo', render: formatCurrency, name: 'saldo' },
                { data: 'pendiente', render: formatCurrency, name: 'pendiente' },
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
        $(SELECTORS.clubId).val(id);
        $(SELECTORS.payOrderModal).modal('show');
    };

    // Inicialización
    const dataTable = initDataTable();

    // Hacer la función payOrder accesible globalmente si es necesario
    window.payOrder = payOrder;
});
