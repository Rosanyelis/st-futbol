'use strict';

document.addEventListener('DOMContentLoaded', () => {
    // Constantes y configuraciones iniciales
    const numberFormat = new Intl.NumberFormat('es-MX');
    const SELECTORS = {
        datatable: '.datatables',
        payOrderModal: '#PayOrderModal',
        preorderId: '#modalpreorden_id',
        orderId: '#order_id',
        amountInput: '#amount',
        modalAmount: '#modalamount',
        formAction: '#formPayOrder',
        totalPendiente: '#totalPendiente'
    };

    // Funciones utilitarias
    const formatCurrency = data => `$ ${numberFormat.format(data)}`;
    // const formatDate = data => moment(data).format('DD/MM/YYYY');
    // const calculateTotal = data => data.reduce((sum, item) => sum + parseFloat(item.total_pendiente || 0), 0);

    // Configuración DataTable
    const initDataTable = () => {
        const dtElement = document.querySelector(SELECTORS.datatable);
        if (!dtElement) return null;

        return $(dtElement).DataTable({
            processing: true,
            serverSide: true,
            url: "/cuenta-por-pagar",
            type: "POST",
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>><"table-responsive"t><"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            language: {
                url: "https://cdn.datatables.net/plug-ins/2.0.8/i18n/es-ES.json",
                paginate: {
                    next: '<i class="ri-arrow-right-s-line"></i>',
                    previous: '<i class="ri-arrow-left-s-line"></i>'
                }
            },
            lengthMenu: [7, 10, 25, 50, 75, 100],
            columns: [
                { data: 'event.name' },
                { data: 'name' },
                { data: 'currency.name' },
                { data: 'amount', render: formatCurrency },
                { data: 'saldo', render: formatCurrency },
                { data: 'pendiente', render: formatCurrency },
                { data: 'action', orderable: false, searchable: false }
            ],
            // footerCallback: function(_, data) {
            //     document.querySelector(SELECTORS.totalPendiente).textContent = formatCurrency(calculateTotal(data));
            // },
            // initComplete: function() {
            //     this.api().columns.adjust().responsive.recalc();
            // }
        });
    };

    // Función para manejar pagos
    const payOrder = (id, amount, tipo) => {
        const URLS = {
            1: '/ordenes/pagar-orden',
            2: '/afinacion-de-partidas/pagar-orden'
        };

        const actionUrl = URLS[tipo] || URLS[1];
        const formattedAmount = numberFormat.format(amount);

        // Actualizar elementos del modal
        $(SELECTORS.formAction).attr('action', actionUrl);
        $(SELECTORS.preorderId).text(id);
        $(SELECTORS.amountInput).val(amount);
        $(SELECTORS.modalAmount).text(formattedAmount);
        $(SELECTORS.orderId).val(id);
        $(SELECTORS.payOrderModal).modal('show');
    };

    // Inicialización
    const dataTable = initDataTable();

    // Hacer la función payOrder accesible globalmente si es necesario
    window.payOrder = payOrder;
});
