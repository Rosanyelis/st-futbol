"use strict";

// Configuración y constantes
const CONFIG = {
    numberFormat: new Intl.NumberFormat("es-MX"),
    eventId: $("#idEvento").val(),
    endpoints: {
        historyJson: (id) => `/negocio/history-json`,
        paymentMethods: (currencyId) => `/negocio/metodo-pago/${currencyId}`,
        currencies: '/negocio/currencies',
        clubsByCategory: (categoryIncomeId) => `/negocio/clubs-by-category/${categoryIncomeId}`,
        expensesByCategory: (categoryEgressId) => `/negocio/expenses-by-category/${categoryEgressId}`,
        suppliersByCategory: (categoryEgressId) => `/negocio/suppliers-by-category/${categoryEgressId}`
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
                    d.start_date = $('#start_date_filter').val();
                    d.end_date = $('#end_date_filter').val();
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
                this.setupDateFilter();
            }
        });

        this.setupDatatableStyles();
    }

    // Configuración del DOM del DataTable
    getDatatableDOM() {
        return '<"card-header d-flex border-top rounded-0 flex-wrap pb-md-0 pt-0"' +
            '<"d-flex align-items-center me-5"' +
                '<"me-3"f>' +
                '<"currency-filter me-3">' +
                '<"date-filter">' +
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
        let currencyTotals = {};

        data.each(item => {
            // Sumar totales generales
            if (item.type === 'Ingreso') totals.income += parseFloat(item.amount);
            if (item.type === 'Egreso') totals.expense += parseFloat(item.amount);

            // Sumar por moneda
            const currencyName = item.currency?.name;
            if (currencyName) {
                if (!currencyTotals[currencyName]) {
                    currencyTotals[currencyName] = {income: 0, expense: 0};
                }
                if (item.type === 'Ingreso') {
                    currencyTotals[currencyName].income += parseFloat(item.amount);
                }
                if (item.type === 'Egreso') {
                    currencyTotals[currencyName].expense += parseFloat(item.amount);
                }
            }
        });

        // Actualizar totales generales
        $(CONFIG.selectors.totals.balance).text(CONFIG.numberFormat.format(totals.income - totals.expense));
        $(CONFIG.selectors.totals.income).text(CONFIG.numberFormat.format(totals.income));
        $(CONFIG.selectors.totals.expense).text(CONFIG.numberFormat.format(totals.expense));

        // Actualizar totales por moneda
        for (const currency in currencyTotals) {
            $(`#totalIngreso${currency}`).text(CONFIG.numberFormat.format(currencyTotals[currency].income));
            $(`#totalEgreso${currency}`).text(CONFIG.numberFormat.format(currencyTotals[currency].expense));
        }
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
            // Solo ocultar los divs relacionados con egresos y limpiar el club
            this.hideExpenseRelatedDivs();
            $(CONFIG.selectors.forms.clubId).val('');

            const selectedTypeIncome = $(CONFIG.selectors.forms.typeIncome).val();

            // Siempre muestra el select de tipo de ingreso
            $(CONFIG.selectors.divs.typeIncome).show();

            if (selectedTypeIncome == 1) { // ID 1 = "Clubs"
                this.loadClubsByCategory(selectedTypeIncome);
                $(CONFIG.selectors.divs.club).show();
            } else {
                $(CONFIG.selectors.divs.club).hide();
            }
            // Si hay más tipos de ingreso, puedes agregar lógica aquí
        });
    }

    // Manejador de cambio de tipo de egreso
    setupTypeExpenseChangeHandler() {
        $(CONFIG.selectors.forms.typeExpense).change(() => {
            const selectedTypeExpense = $(CONFIG.selectors.forms.typeExpense).val();
            this.hideExpenseRelatedDivs();
            
            if (selectedTypeExpense == 1) { // ID 1 = "Gastos"
                this.loadExpensesByCategory(selectedTypeExpense);
                $(CONFIG.selectors.divs.expense).show();
            } else if (selectedTypeExpense == 2) { // ID 2 = "Proveedores"
                this.loadSuppliersByCategory(selectedTypeExpense);
                $(CONFIG.selectors.divs.supplier).show();
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

    // Carga de clubs por categoría de ingreso
    loadClubsByCategory(categoryIncomeId) {
        $(CONFIG.selectors.forms.clubId)
            .empty()
            .append('<option value="">-- Seleccionar --</option>');

        $.ajax({
            url: CONFIG.endpoints.clubsByCategory(categoryIncomeId),
            type: 'GET',
            data: { event_id: CONFIG.eventId },
            success: this.handleClubsResponse.bind(this),
            error: this.handleClubsError.bind(this)
        });
    }

    // Carga de gastos por categoría de egreso
    loadExpensesByCategory(categoryEgressId) {
        $(CONFIG.selectors.forms.expenseId)
            .empty()
            .append('<option value="">-- Seleccionar --</option>');

        $.ajax({
            url: CONFIG.endpoints.expensesByCategory(categoryEgressId),
            type: 'GET',
            success: this.handleExpensesResponse.bind(this),
            error: this.handleExpensesError.bind(this)
        });
    }

    // Carga de proveedores por categoría de egreso
    loadSuppliersByCategory(categoryEgressId) {
        $(CONFIG.selectors.forms.supplierId)
            .empty()
            .append('<option value="">-- Seleccionar --</option>');

        $.ajax({
            url: CONFIG.endpoints.suppliersByCategory(categoryEgressId),
            type: 'GET',
            success: this.handleSuppliersResponse.bind(this),
            error: this.handleSuppliersError.bind(this)
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

    // Manejo de respuesta de clubs
    handleClubsResponse(clubs) {
        if (clubs?.length) {
            clubs.forEach(club => {
                $(CONFIG.selectors.forms.clubId).append(
                    `<option value="${club.id}">${club.name}</option>`
                );
            });
        }
    }

    // Manejo de error en clubs
    handleClubsError(xhr, status, error) {
        console.error('Error al obtener clubs:', error);
    }

    // Manejo de respuesta de gastos
    handleExpensesResponse(expenses) {
        if (expenses?.length) {
            expenses.forEach(expense => {
                $(CONFIG.selectors.forms.expenseId).append(
                    `<option value="${expense.id}">${expense.category_expense.name} - ${expense.subcategory_expense.name}</option>`
                );
            });
        }
    }

    // Manejo de error en gastos
    handleExpensesError(xhr, status, error) {
        console.error('Error al obtener gastos:', error);
    }

    // Manejo de respuesta de proveedores
    handleSuppliersResponse(suppliers) {
        if (suppliers?.length) {
            suppliers.forEach(supplier => {
                $(CONFIG.selectors.forms.supplierId).append(
                    `<option value="${supplier.id}">${supplier.name} - ${supplier.representant}</option>`
                );
            });
        }
    }

    // Manejo de error en proveedores
    handleSuppliersError(xhr, status, error) {
        console.error('Error al obtener proveedores:', error);
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

    // Configurar el filtro de fechas
    setupDateFilter() {
        const dateFilterHTML = `
            <div class="d-flex align-items-center" style="min-width: 300px;">
                <div class="me-2">
                    <div class="form-floating form-floating-outline">
                        <input type="date" id="start_date_filter" class="form-control form-control-sm" style="width: 130px;">
                        <label class="small">Desde:</label>
                    </div>
                </div>
                <div class="me-2">
                    <div class="form-floating form-floating-outline">
                        <input type="date" id="end_date_filter" class="form-control form-control-sm" style="width: 130px;">
                        <label class="small">Hasta:</label>
                    </div>
                </div>
                <div class="d-flex align-items-end">
                    <button id="clear_date_filter" class="btn btn-outline-secondary btn-sm" style="height: 32px;">
                        <i class="ri-refresh-line"></i>
                    </button>
                </div>
            </div>
        `;
        
        $('.date-filter').html(dateFilterHTML);

        // Configurar event listeners para el filtro de fechas
        $('#start_date_filter, #end_date_filter').on('change', () => {
            this.validateDateRange();
            this.datatable.ajax.reload();
        });

        // Botón para limpiar filtros de fecha
        $('#clear_date_filter').on('click', () => {
            $('#start_date_filter').val('');
            $('#end_date_filter').val('');
            this.datatable.ajax.reload();
        });
    }

    // Validar rango de fechas
    validateDateRange() {
        const startDate = $('#start_date_filter').val();
        const endDate = $('#end_date_filter').val();
        
        if (startDate && endDate && startDate > endDate) {
            Swal.fire({
                title: 'Error en fechas',
                text: 'La fecha de inicio no puede ser mayor que la fecha de fin',
                icon: 'error',
                confirmButtonText: 'Entendido'
            });
            $('#end_date_filter').val('');
        }
    }
}

// Inicialización cuando el documento está listo
$(function() {
    new HistoryManager();
});
