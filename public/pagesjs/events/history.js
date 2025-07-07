"use strict";

// Configuración y constantes
const CONFIG = {
    numberFormat: new Intl.NumberFormat("es-MX"),
    eventId: $("#idEvento").val(),
    endpoints: {
        historyJson: (id) => `/eventos/${id}/history-json`,
        paymentMethods: (currencyId) => `/eventos/metodo-pago/${currencyId}`,
        currencies: '/eventos/currencies',
        clubsByCategory: (categoryIncomeId) => `/eventos/clubs-by-category/${categoryIncomeId}`,
        expensesByCategory: (categoryEgressId) => `/eventos/expenses-by-category/${categoryEgressId}`,
        suppliersByCategory: (categoryEgressId) => `/eventos/suppliers-by-category/${categoryEgressId}`
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
            {data: 'methodPayment.account_holder'},
            {data: 'actions', orderable: false, searchable: false},
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
            className: 'btn btn-primary btn-new-movement',
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
        this.setupAmountInputFormat();
        this.setupFormConfirmation(); // <-- Agrega esto
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

    loadExpensesByCategoryAndSelect(categoryEgressId, selectedExpenseId) {
        const $expenseSelect = $(CONFIG.selectors.forms.expenseId);
        $expenseSelect.empty().append('<option value="">-- Seleccionar --</option>');
        $.ajax({
            url: CONFIG.endpoints.expensesByCategory(categoryEgressId),
            type: 'GET',
            success: (expenses) => {
                if (expenses?.length) {
                    expenses.forEach(expense => {
                        $expenseSelect.append(
                            `<option value="${expense.id}">${expense.category_expense.name} - ${expense.subcategory_expense.name}</option>`
                        );
                    });
                    if (selectedExpenseId) {
                        $expenseSelect.val(selectedExpenseId).trigger('change');
                    }
                }
            }
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

    // Cargar proveedores por categoría y seleccionar el proveedor correspondiente
    loadSuppliersByCategoryAndSelect(categoryEgressId, selectedSupplierId) {
        const $supplierSelect = $(CONFIG.selectors.forms.supplierId);
        $supplierSelect.empty().append('<option value="">-- Seleccionar --</option>');
        $.ajax({
            url: CONFIG.endpoints.suppliersByCategory(categoryEgressId),
            type: 'GET',
            success: (suppliers) => {
                if (suppliers?.length) {
                    suppliers.forEach(supplier => {
                        $supplierSelect.append(
                            `<option value="${supplier.id}">${supplier.name} - ${supplier.representant}</option>`
                        );
                    });
                    if (selectedSupplierId) {
                        $supplierSelect.val(selectedSupplierId).trigger('change');
                    }
                }
            }
        });
    }

    // Cargar clubs y seleccionar el club correspondiente
    loadClubsByCategoryAndSelect(categoryIncomeId, selectedClubId) {
        const $clubSelect = $(CONFIG.selectors.forms.clubId);
        $clubSelect.empty().append('<option value="">-- Seleccionar --</option>');
        $.ajax({
            url: CONFIG.endpoints.clubsByCategory(categoryIncomeId),
            type: 'GET',
            data: { event_id: CONFIG.eventId },
            success: (clubs) => {
                if (clubs?.length) {
                    clubs.forEach(club => {
                        $clubSelect.append(
                            `<option value="${club.id}">${club.name}</option>`
                        );
                    });
                    if (selectedClubId) {
                        $clubSelect.val(selectedClubId).trigger('change');
                    }
                }
            }
        });
    }

    // Cargar métodos de pago y seleccionar el método correspondiente
    loadPaymentMethodsAndSelect(currencyId, selectedMethodId) {
        const $methodSelect = $(CONFIG.selectors.forms.methodPaymentId);
        $methodSelect.empty().append('<option value="">-- Seleccionar --</option>');
        $.ajax({
            url: CONFIG.endpoints.paymentMethods(currencyId),
            type: 'GET',
            success: (methods) => {
                if (methods?.length) {
                    methods.forEach(method => {
                        $methodSelect.append(
                            `<option value="${method.id}">
                                ${method.account_holder} - ${method.entity.name} - ${method.type_account}
                            </option>`
                        );
                    });
                    if (selectedMethodId) {
                        $methodSelect.val(selectedMethodId).trigger('change');
                    }
                }
            }
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

    // Abre el modal para editar un movimiento
    openEditModal(movement) {
        const modal = $(CONFIG.selectors.modals.movement);

        modal.find('input[name="id"]').remove();
        modal.find('form').prepend(`<input type="hidden" name="id" value="${movement.id}">`);
        modal.find('textarea[name="description"]').val(movement.description ?? '');
        modal.find('input[name="date"]').val(movement.date ?? '');
        modal.find('select[name="type"]').val(movement.type ?? '').trigger('change');
        modal.find('select[name="type_income"]').val(movement.category_income_id ?? '').trigger('change');
        modal.find('select[name="type_expense"]').val(movement.category_egress_id ?? '').trigger('change');
        modal.find('select[name="expense_id"]').val(movement.expense_id ?? '').trigger('change');
        modal.find('select[name="currency_id"]').val(movement.currency_id ?? '').trigger('change');
        modal.find('input[name="amount"]').val(movement.amount ?? '');

        // Clubs dependiente de tipo de ingreso
        if (movement.type === 'Ingreso' && movement.category_income_id) {
            this.loadClubsByCategoryAndSelect(movement.category_income_id, movement.club_id);
        }

        // Proveedores dependiente de tipo de egreso
        if (movement.type === 'Egreso' && movement.category_egress_id) {
            this.loadSuppliersByCategoryAndSelect(movement.category_egress_id, movement.supplier_id);
            this.loadExpensesByCategoryAndSelect(movement.category_egress_id, movement.expense_id); // <-- agrega esto
        }

        // Método de pago dependiente de moneda
        if (movement.currency_id) {
            this.loadPaymentMethodsAndSelect(movement.currency_id, movement.method_payment_id);
        }

        // Muestra/oculta los divs según el tipo de movimiento
        this.hideOptionalDivs();
        if (movement.type === 'Ingreso') {
            $(CONFIG.selectors.divs.typeIncome).show();
            if (movement.club_id) $(CONFIG.selectors.divs.club).show();
        } else if (movement.type === 'Egreso') {
            $(CONFIG.selectors.divs.typeExpense).show();
            if (movement.supplier_id) $(CONFIG.selectors.divs.supplier).show();
            if (movement.expense_id) $(CONFIG.selectors.divs.expense).show();
        }

        modal.find('.modal-title').text('Editar Movimiento de Ingreso/Egreso');
        modal.find('button[type="submit"]').text('Actualizar'); 
        modal.find('form').attr('action', `/eventos/${movement.id}/update-history`);
        modal.modal('show');
    }

    // Limpiar el formulario del modal de movimiento
    clearMovementModal() {
        const modal = $(CONFIG.selectors.modals.movement);
        modal.find('form')[0].reset();
        modal.find('input[name="id"]').remove();
        modal.find('select').val('').trigger('change');
        this.hideOptionalDivs();
        modal.find('.modal-title').text('Crear Movimiento de Ingreso/Egreso');
        modal.find('button[type="submit"]').text('Crear');
        modal.find('form').attr('action', $('#formMovimiento').data('action-create') || $('#formMovimiento').attr('action'));
    }

    // Eliminar movimiento
    deleteMovement(movementId) {
        
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción eliminará el movimiento de forma permanente.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            customClass: {
                confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
                cancelButton: 'btn btn-outline-danger waves-effect'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `/eventos/${movementId}/destroy-history`;
            }
        });
    }

    // Validar confirmación antes de crear o editar movimiento
    setupFormConfirmation() {
        const modal = $(CONFIG.selectors.modals.movement);
        const $form = modal.find('form');

        $form.off('submit').on('submit', function(e) {
            e.preventDefault();

            // Detectar si es crear o editar según el texto del botón
            const isEdit = modal.find('button[type="submit"]').text().trim().toLowerCase() === 'actualizar';
            const actionText = isEdit ? 'Actualizar' : 'Crear';

            Swal.fire({
                title: `¿Está seguro de ${actionText} el movimiento?`,
                text: `Esta acción ${isEdit ? 'modificará' : 'creará'} el movimiento en el historial.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: `Sí, ${actionText}`,
                cancelButtonText: 'No, revisar',
                customClass: {
                    confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
                    cancelButton: 'btn btn-outline-secondary waves-effect'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $form.off('submit'); // Evita loop infinito
                    $form.submit();
                }
                // Si cancela, no hace nada y el modal sigue abierto para edición
            });
        });
    }

    // Formatear el campo de monto en tiempo real
    setupAmountInputFormat() {
        const $amountInput = $(CONFIG.selectors.modals.movement).find('input[name="amount"]');
        $amountInput.on('input', function () {
            let value = $(this).val();

            // Eliminar todo excepto números y punto
            value = value.replace(/[^0-9.]/g, '');

            // Separar parte entera y decimal
            let [integer, decimal] = value.split('.');
            integer = integer ? integer.replace(/^0+/, '') : '';

            // Formatear miles
            if (integer.length) {
                integer = integer.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            // Limitar a dos decimales
            if (decimal !== undefined) {
                decimal = decimal.substring(0, 2);
                value = integer + '.' + decimal;
            } else {
                value = integer;
            }

            $(this).val(value);
        });
    }
}

// Inicialización cuando el documento está listo
$(function() {
    window.historyManager = new HistoryManager();

    // Botón "Nuevo Movimiento"
    $(document).on('click', '.btn-new-movement', function() {
        // Solo si es el botón de "Nuevo Movimiento"
        if ($(this).text().trim() === 'Nuevo Movimiento') {
            window.historyManager.clearMovementModal();
            $(CONFIG.selectors.modals.movement).modal('show');
        }
    });
});
