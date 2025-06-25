"use strict";

// Configuración y constantes
const CONFIG = {
    numberFormat: new Intl.NumberFormat("es-MX"),
    eventId: $("#idEvento").val(),
    endpoints: {
        historyJson: (id) => `/eventos/${id}/history-json`,
        paymentMethods: (currencyId) => `/eventos/metodo-pago/${currencyId}`,
        currencies: '/eventos/currencies'
    },
    selectors: {
        datatable: ".datatables-history",
        forms: {
            currencyId: '#currency_id',
            type: '#type',
            typeIncome: '#type_income',
            typeExpense: '#type_expense',
            methodPaymentId: '#method_payment_id',
            clubId: '#club_id',
            supplierId: '#supplier_id',
            expenseId: '#expense_id'
        },
        divs: {
            typeIncome: '#type_income_div',
            typeExpense: '#type_expense_div',
            club: '#club_id_div',
            supplier: '#supplier_id_div',
            expense: '#expense_id_div'
        },
        totals: {
            balance: '#totalBalance',
            income: '#totalIngresos',
            expense: '#totalEgresos'
        },
        modals: {
            movement: '#MovimientoModal'
        }
    }
};

// Clase principal para manejar la página de historial
class HistoryManager {
    constructor() {
        this.datatable = null;
        this.initializeDatatable();
        this.initializeEventListeners();
        this.hideOptionalDivs();
    }

    // Inicialización del DataTable
    initializeDatatable() {
        const table = $(CONFIG.selectors.datatable);
        if (!table.length) return;

        this.datatable = table.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: CONFIG.endpoints.historyJson(CONFIG.eventId),
                data: (d) => {
                    d.currency_id = $('#currency_filter').val();
                }
            },
            dom: this.getDatatableDOM(),
            language: this.getDatatableLanguage(),
            columns: this.getDatatableColumns(),
            columnDefs: this.getColumnDefinitions(),
            buttons: this.getDatatableButtons(),
            drawCallback: this.updateTotals.bind(this),
            initComplete: () => {
                this.setupCurrencyFilter();
            }
        });

        this.setupDatatableStyles();
    }

    // Configuración del DOM del DataTable
    getDatatableDOM() {
        return '<"card-header d-flex border-top rounded-0 flex-wrap pb-md-0 pt-0"' +
            '<"d-flex align-items-center me-5"' +
                '<"me-3"f>' +
                '<"currency-filter">' +
            '>' +
            '<"ms-auto d-flex justify-content-end align-items-center gap-4"' +
                '<"d-flex align-items-center"l>' +
                '<"dt-action-buttons d-flex align-items-center"B>' +
            '>' +
            ">t" +
            '<"row mx-1"' +
            '<"col-sm-12 col-md-6"i>' +
            '<"col-sm-12 col-md-6"p>' +
            ">";
    }

    // Configuración del lenguaje
    getDatatableLanguage() {
        return {
            url: "https://cdn.datatables.net/plug-ins/2.0.8/i18n/es-ES.json",
            paginate: {
                next: '<i class="ri-arrow-right-s-line"></i>',
                previous: '<i class="ri-arrow-left-s-line"></i>'
            },
            lengthMenu: "_MENU_",
            search: "Buscar:"
        };
    }

    // Definición de columnas
    getDatatableColumns() {
        return [
            {data: 'date'},
            {data: 'description'},
            {data: 'amount'},
            {data: 'amount'},
            {data: 'currency.name'},
            {data: 'club.name'},
            {data: 'supplier.name'},
            {data: 'methodPayment.account_holder'}
        ];
    }

    // Definición de renderizado de columnas
    getColumnDefinitions() {
        return [
            {
                targets: 0,
                render: (data, type, full) => 
                    `<span class='text-nowrap'>${moment(full.date).format("DD/MM/YYYY")}</span>`
            },
            {
                targets: 1,
                render: (data, type, full) => 
                    `<span class='text-nowrap'>${full.description}</span>`
            },
            {
                targets: 2,
                render: (data, type, full) => 
                    this.renderAmount(full, 'Ingreso')
            },
            {
                targets: 3,
                render: (data, type, full) => 
                    this.renderAmount(full, 'Egreso')
            },
            {
                targets: 4,
                render: (data, type, full) => 
                    `<span class='text-nowrap'>${full.currency?.name} ${full.currency?.symbol}</span>`
            },
            {
                targets: 5,
                render: (data, type, full) => 
                    this.renderOptionalField(full.club?.name)
            },
            {
                targets: 6,
                render: (data, type, full) => 
                    this.renderOptionalField(full.supplier?.name)
            },
            {
                targets: 7,
                render: (data, type, full) => 
                    this.renderMethodPayment(full.method_payment)
            }
        ];
    }

    // Botones del DataTable
    getDatatableButtons() {
        return [{
            text: 'Nuevo Movimiento',
            className: 'btn btn-primary',
            action: () => $(CONFIG.selectors.modals.movement).modal('show')
        }];
    }

    // Renderizado de montos
    renderAmount(item, type) {
        return item.type === type
            ? `<span class='text-nowrap'><strong>${CONFIG.numberFormat.format(item.amount ?? 0)}</strong></span>`
            : `<span class='text-nowrap'> - </span>`;
    }

    // Renderizado de campos opcionales
    renderOptionalField(value) {
        return value
            ? `<span class='text-nowrap'>${value}</span>`
            : `<span class='text-nowrap'> - </span>`;
    }

    // Renderizado de método de pago
    renderMethodPayment(method) {
        return method?.account_holder
            ? `<span class='text-nowrap'>${method.account_holder} - ${method.entity?.name} - ${method.type_account}</span>`
            : `<span class='text-nowrap'> - </span>`;
    }

    // Actualización de totales
    updateTotals() {
        const data = this.datatable.data();
        let totals = {income: 0, expense: 0};

        data.each(item => {
            if (item.type === 'Ingreso') totals.income += parseFloat(item.amount);
            if (item.type === 'Egreso') totals.expense += parseFloat(item.amount);
        });

        $(CONFIG.selectors.totals.balance).text(CONFIG.numberFormat.format(totals.income - totals.expense));
        $(CONFIG.selectors.totals.income).text(CONFIG.numberFormat.format(totals.income));
        $(CONFIG.selectors.totals.expense).text(CONFIG.numberFormat.format(totals.expense));
    }

    // Estilos del DataTable
    setupDatatableStyles() {
        $(".dt-action-buttons").addClass("pt-0");
        $(".dt-buttons").addClass("d-flex flex-wrap");
    }

    // Inicialización de event listeners
    initializeEventListeners() {
        this.setupCurrencyChangeHandler();
        this.setupTypeChangeHandler();
        this.setupTypeIncomeChangeHandler();
        this.setupTypeExpenseChangeHandler();
    }

    // Manejador de cambio de moneda
    setupCurrencyChangeHandler() {
        $(CONFIG.selectors.forms.currencyId).change(() => {
            const currencyId = $(CONFIG.selectors.forms.currencyId).val();
            this.datatable.ajax.reload();
            if (currencyId) this.loadPaymentMethods(currencyId);
        });
    }

    // Manejador de cambio de tipo
    setupTypeChangeHandler() {
        $(CONFIG.selectors.forms.type).change(() => {
            const selectedType = $(CONFIG.selectors.forms.type).val();
            this.hideOptionalDivs();
            this.clearAllSelectors();
            
            if (selectedType === 'Ingreso') {
                $(CONFIG.selectors.divs.typeIncome).show();
            } else if (selectedType === 'Egreso') {
                $(CONFIG.selectors.divs.typeExpense).show();
            }
        });
    }

    // Manejador de cambio de tipo de ingreso
    setupTypeIncomeChangeHandler() {
        $(CONFIG.selectors.forms.typeIncome).change(() => {
            const selectedTypeIncome = $(CONFIG.selectors.forms.typeIncome).val();
            $(CONFIG.selectors.divs.club).hide();
            $(CONFIG.selectors.forms.clubId).val('');
            
            if (selectedTypeIncome === 'Club') {
                $(CONFIG.selectors.divs.club).show();
            }
        });
    }

    // Manejador de cambio de tipo de egreso
    setupTypeExpenseChangeHandler() {
        $(CONFIG.selectors.forms.typeExpense).change(() => {
            const selectedTypeExpense = $(CONFIG.selectors.forms.typeExpense).val();
            this.hideExpenseRelatedDivs();
            
            if (selectedTypeExpense === 'Proveedor') {
                $(CONFIG.selectors.divs.supplier).show();
            } else if (selectedTypeExpense === 'Gasto') {
                $(CONFIG.selectors.divs.expense).show();
            }
        });
    }

    // Carga de métodos de pago
    loadPaymentMethods(currencyId) {
        $(CONFIG.selectors.forms.methodPaymentId)
            .empty()
            .append('<option value="">-- Seleccionar --</option>');

        $.ajax({
            url: CONFIG.endpoints.paymentMethods(currencyId),
            type: 'GET',
            success: this.handlePaymentMethodsResponse.bind(this),
            error: this.handlePaymentMethodsError
        });
    }

    // Manejo de respuesta de métodos de pago
    handlePaymentMethodsResponse(response) {
        if (response?.length) {
            response.forEach(method => {
                $(CONFIG.selectors.forms.methodPaymentId).append(
                    `<option value="${method.id}">
                        ${method.account_holder} - ${method.entity.name} - ${method.type_account}
                    </option>`
                );
            });
        }
    }

    // Manejo de error en métodos de pago
    handlePaymentMethodsError(xhr, status, error) {
        console.error('Error al obtener métodos de pago:', error);
    }

    // Ocultar todos los divs opcionales
    hideOptionalDivs() {
        Object.values(CONFIG.selectors.divs).forEach(selector => $(selector).hide());
    }

    // Ocultar divs relacionados con gastos
    hideExpenseRelatedDivs() {
        $(CONFIG.selectors.divs.supplier).hide();
        $(CONFIG.selectors.forms.supplierId).val('');
        $(CONFIG.selectors.divs.expense).hide();
        $(CONFIG.selectors.forms.expenseId).val('');
    }

    // Limpiar todos los selectores
    clearAllSelectors() {
        [
            CONFIG.selectors.forms.typeIncome,
            CONFIG.selectors.forms.typeExpense,
            CONFIG.selectors.forms.clubId,
            CONFIG.selectors.forms.supplierId,
            CONFIG.selectors.forms.expenseId
        ].forEach(selector => $(selector).val(''));
    }

    // Configurar el filtro de monedas
    setupCurrencyFilter() {
        $.ajax({
            url: CONFIG.endpoints.currencies,
            type: 'GET',
            success: this.handleCurrenciesResponse.bind(this),
            error: this.handleCurrenciesError.bind(this)
        });
    }

    // Manejar la respuesta exitosa de la API de monedas
    handleCurrenciesResponse(currencies) {
        if (!currencies || !currencies.length) {
            console.warn('La respuesta de monedas está vacía o no es un array.');
            $('.currency-filter').html('<span class="text-danger small">No se encontraron monedas.</span>');
            return;
        }
        
        const currencyOptions = currencies.map(currency => 
            `<option value="${currency.id}">${currency.name} (${currency.symbol})</option>`
        ).join('');

        const currencyFilterHTML = `
            <div style="min-width: 200px;">
                <select id="currency_filter" class="form-select form-select-sm">
                    <option value="">Todas las monedas</option>
                    ${currencyOptions}
                </select>
            </div>
        `;
        
        $('.currency-filter').html(currencyFilterHTML);

        // Cuando el filtro cambia, recarga la tabla
        $('#currency_filter').on('change', () => {
            this.datatable.ajax.reload();
        });
    }

    // Manejar errores de la API de monedas
    handleCurrenciesError(xhr, status, error) {
        console.error('Error al obtener las monedas:', error);
        $('.currency-filter').html('<span class="text-danger small">Error al cargar monedas</span>');
    }
}

// Inicialización cuando el documento está listo
$(function() {
    new HistoryManager();
});
