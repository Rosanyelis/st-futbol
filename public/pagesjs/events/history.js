"use strict";

// Configuración y constantes
const CONFIG = {
    numberFormat: new Intl.NumberFormat("es-MX"),
    eventId: $("#idEvento").val(),
    endpoints: {
        historyJson: (id) => `/eventos/${id}/history-json`,
        paymentMethods: (currencyId) => `/eventos/metodo-pago/${currencyId}`,
        currencies: '/eventos/currencies',
        clubsPendingAmounts: (eventId) => `/eventos/${eventId}/clubs-pending-amounts`,
        clubsPendingAccountsFiltered: (eventId) => `/eventos/${eventId}/clubs-pending-accounts-filtered`,
        clubsByCategory: (categoryIncomeId) => `/eventos/clubs-by-category/${categoryIncomeId}`,
        clubsByCategoryForEdit: (categoryIncomeId) => `/eventos/clubs-by-category-for-edit/${categoryIncomeId}`,
        expensesByCategory: (categoryEgressId) => `/eventos/expenses-by-category/${categoryEgressId}`,
        suppliersByCategory: (categoryEgressId) => `/eventos/suppliers-by-category/${categoryEgressId}`,
        suppliersByCategoryForEdit: (categoryEgressId) => `/eventos/suppliers-by-category-for-edit/${categoryEgressId}`
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
            scrollY: '350px',
            scrollX: true,
            dom: this.getDatatableDOM(),
            language: this.getDatatableLanguage(),
            columns: this.getDatatableColumns(),
            columnDefs: this.getColumnDefinitions(),
            buttons: this.getDatatableButtons(),
            lengthMenu: [50, 100, 200, 500],
            order: [[0, 'desc']],
            stateSave: false, // No guardar estado para mantener ordenamiento consistente
            ordering: true, // Habilitar ordenamiento
            drawCallback: () => {
                this.updateTotals();
                this.initializeTooltips();
            },
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
            search: "Buscar:",
        };
    }

    // Definición de columnas
    getDatatableColumns() {
        return [
            {data: 'date', type: 'date'},
            {data: 'description'},
            {data: 'amount'},
            {data: 'amount'},
            {data: 'currency_name'},
            {data: 'club_name'},
            {data: 'supplier_name'},
            {data: 'method_payment_account_holder'},
            {data: 'actions', orderable: false, searchable: false},
        ];
    }

    // Definición de renderizado de columnas
    getColumnDefinitions() {
        return [
            {
                targets: 0,
                type: 'date',
                render: (data, type, full) => {
                    if (type === 'sort' || type === 'type') {
                        return moment(full.date).format('YYYY-MM-DD');
                    }
                    return `<span class='text-nowrap'>${moment(full.date).format("DD/MM/YYYY")}</span>`;
                }
            },
            {
                targets: 1,
                render: (data, type, full) => 
                    this.renderDescription(full.description)
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
                    `<span class='text-nowrap'>${full.currency_name} ${full.currency_symbol}</span>`
            },
            {
                targets: 5,
                render: (data, type, full) => 
                    this.renderOptionalField(full.club_name)
            },
            {
                targets: 6,
                render: (data, type, full) => 
                    this.renderOptionalField(full.supplier_name)
            },
            {
                targets: 7,
                render: (data, type, full) => 
                    this.renderMethodPaymentWithTooltip(full)
            },
            {
                targets: 8,
                render: (data, type, full) => 
                    this.renderActions(full)
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

    // Renderizado de descripción con tooltip si es muy larga
    renderDescription(description) {
        if (!description) return '<span class="text-nowrap"> - </span>';
        
        const maxLength = 50;
        if (description.length <= maxLength) {
            return `<span class='text-nowrap'>${description}</span>`;
        } else {
            const truncated = description.substring(0, maxLength) + '...';
            return `<span class='text-nowrap' data-bs-toggle="tooltip" data-bs-placement="top" title="${description.replace(/"/g, '&quot;')}">${truncated}</span>`;
        }
    }

    // Renderizado de método de pago
    renderMethodPayment(method) {
        return method?.account_holder
            ? `<span class='text-nowrap'>${method.account_holder} - ${method.entity?.name} - ${method.type_account}</span>`
            : `<span class='text-nowrap'> - </span>`;
    }

    // Renderizado de método de pago con tooltip para nombre completo
    renderMethodPaymentWithTooltip(full) {
        if (!full?.method_payment_account_holder) {
            return `<span class='text-nowrap'> - </span>`;
        }
        
        const fullText = `${full.method_payment_account_holder} - ${full.entity_name} - ${full.method_payment_type_account}`;
        const maxLength = 50;
        
        if (fullText.length <= maxLength) {
            return `<span class='text-nowrap'>${fullText}</span>`;
        } else {
            const truncated = fullText.substring(0, maxLength) + '...';
            return `<span class='text-nowrap' data-bs-toggle="tooltip" data-bs-placement="top" title="${fullText.replace(/"/g, '&quot;')}">${truncated}</span>`;
        }
    }

    // Renderizado de acciones
    renderActions(movement) {
        let actions = '';
        actions += `<div class="d-flex gap-2">`;

        // Botón de editar
        actions += `<a class="btn btn-sm btn-icon text-primary me-1" 
                           onclick="window.historyManager.openEditModal(${movement.id})" 
                           title="Editar movimiento">
                        <i class="ri-edit-line"></i>
                    </a>`;
        
        // Botón de cancelar (solo si es un movimiento de evento y está activo)
        if (movement.event_id && movement.status !== 'Cancelado') {
            actions += `<a class="btn btn-sm btn-icon text-danger btn-cancel-movement" 
                               data-movement-id="${movement.id}" 
                               title="Cancelar movimiento">
                            <i class="ri-close-line"></i>
                        </a>`;
        }
        actions += `</div>`;
        return actions || '<span class="text-muted">-</span>';
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

    // Inicializar tooltips
    initializeTooltips() {
        // Destruir tooltips existentes para evitar duplicados
        $('[data-bs-toggle="tooltip"]').tooltip('dispose');
        
        // Inicializar nuevos tooltips
        $('[data-bs-toggle="tooltip"]').tooltip({
            trigger: 'hover',
            placement: 'top',
            html: true
        });
    }

    // Inicialización de event listeners
    initializeEventListeners() {
        this.setupCurrencyChangeHandler();
        this.setupTypeChangeHandler();
        this.setupTypeIncomeChangeHandler();
        this.setupTypeExpenseChangeHandler();
        this.setupClubChangeHandler();
        this.setupSupplierChangeHandler();
        this.setupExpenseChangeHandler();
        this.setupAmountInputFormat();
        this.setupFormConfirmation();

        // Event listener para el cambio en el select de clubs
        $(document).on('change', CONFIG.selectors.forms.clubId, (e) => {
            const selectedOption = $(e.currentTarget).find('option:selected');
            const accountReceivableId = selectedOption.data('account-receivable-id');
            
            if (accountReceivableId) {
                $('#account_receivable_id').val(accountReceivableId);
                $('#account_receivable_id_div').show();
            } else {
                $('#account_receivable_id').val('');
                $('#account_receivable_id_div').hide();
            }
        });

        // Event listener para el cambio en el select de proveedores
        $(document).on('change', CONFIG.selectors.forms.supplierId, (e) => {
            const selectedOption = $(e.currentTarget).find('option:selected');
            const accountPayableId = selectedOption.data('account-payable-id');
            
            if (accountPayableId) {
                $('#account_payable_id').val(accountPayableId);
                $('#account_payable_id_div').show();
            } else {
                $('#account_payable_id').val('');
                $('#account_payable_id_div').hide();
            }
        });
    }

    // Manejador de cambio de moneda
    setupCurrencyChangeHandler() {
        $(CONFIG.selectors.forms.currencyId).change(() => {
            const currencyId = $(CONFIG.selectors.forms.currencyId).val();
            this.datatable.ajax.reload();
            
            // Limpiar la selección del método de pago cuando cambia la moneda
            $(CONFIG.selectors.forms.methodPaymentId).val('').trigger('change');
            
            if (currencyId) this.loadPaymentMethods(currencyId);
        });
    }

    // Manejador de cambio de tipo
    setupTypeChangeHandler() {
        $(CONFIG.selectors.forms.type).change(() => {
            const selectedType = $(CONFIG.selectors.forms.type).val();
            this.hideOptionalDivs();
            this.clearAllSelectors();
            this.updateDescription();

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
            
            this.updateDescription();
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
            
            this.updateDescription();
        });
    }

    // Manejador de cambio de club
    setupClubChangeHandler() {
        $(CONFIG.selectors.forms.clubId).change(() => {
            this.updateDescription();
        });
    }

    // Manejador de cambio de proveedor
    setupSupplierChangeHandler() {
        $(CONFIG.selectors.forms.supplierId).change(() => {
            this.updateDescription();
        });
    }

    // Manejador de cambio de gasto
    setupExpenseChangeHandler() {
        $(CONFIG.selectors.forms.expenseId).change(() => {
            this.updateDescription();
        });
    }

    // Actualizar descripción automáticamente
    updateDescription() {
        const type = $(CONFIG.selectors.forms.type).val();
        const typeIncome = $(CONFIG.selectors.forms.typeIncome).val();
        const typeExpense = $(CONFIG.selectors.forms.typeExpense).val();
        const clubId = $(CONFIG.selectors.forms.clubId).val();
        const supplierId = $(CONFIG.selectors.forms.supplierId).val();
        const expenseId = $(CONFIG.selectors.forms.expenseId).val();
        
        let description = '';
        
        if (type) {
            description = type;
            
            if (type === 'Ingreso' && typeIncome) {
                const typeIncomeText = $(CONFIG.selectors.forms.typeIncome).find('option:selected').text();
                description += ` - ${typeIncomeText}`;
                
                if (clubId) {
                    const clubText = $(CONFIG.selectors.forms.clubId).find('option:selected').text();
                    description += ` - ${clubText}`;
                }
            } else if (type === 'Egreso' && typeExpense) {
                const typeExpenseText = $(CONFIG.selectors.forms.typeExpense).find('option:selected').text();
                description += ` - ${typeExpenseText}`;
                
                if (typeExpense == 1 && expenseId) { // Gastos
                    const expenseText = $(CONFIG.selectors.forms.expenseId).find('option:selected').text();
                    description += ` - ${expenseText}`;
                } else if (typeExpense == 2 && supplierId) { // Proveedores
                    const supplierText = $(CONFIG.selectors.forms.supplierId).find('option:selected').text();
                    description += ` - ${supplierText}`;
                }
            }
        }
        
        $('#description').val(description);
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

    // Carga de clubs por categoría de ingreso con sus cuentas por cobrar
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
        console.log('loadExpensesByCategory called with categoryEgressId:', categoryEgressId);
        console.log('URL:', CONFIG.endpoints.expensesByCategory(categoryEgressId));
        
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
                        const optionText = expense.name || expense.category_expense?.name || 'Sin nombre';
                        $expenseSelect.append(
                            `<option value="${expense.id}">${optionText}</option>`
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
            data: { event_id: CONFIG.eventId },
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
            data: { event_id: CONFIG.eventId },
            success: (suppliers) => {
                if (suppliers?.length) {
                    suppliers.forEach(supplier => {
                        $supplierSelect.append(
                            `<option value="${supplier.id}" data-account-payable-id="${supplier.account_payable_id}">${supplier.name}</option>`
                        );
                    });
                    if (selectedSupplierId) {
                        $supplierSelect.val(selectedSupplierId).trigger('change');
                    }
                }
            }
        });
    }

    // Cargar proveedores para edición (incluye cuentas completadas) y seleccionar el proveedor correspondiente
    loadSuppliersByCategoryForEditAndSelect(categoryEgressId, selectedSupplierId) {
        const $supplierSelect = $(CONFIG.selectors.forms.supplierId);
        $supplierSelect.empty().append('<option value="">-- Seleccionar --</option>');
        $.ajax({
            url: CONFIG.endpoints.suppliersByCategoryForEdit(categoryEgressId),
            type: 'GET',
            data: { event_id: CONFIG.eventId },
            success: (suppliers) => {
                if (suppliers?.length) {
                    suppliers.forEach(supplier => {
                        $supplierSelect.append(
                            `<option value="${supplier.id}" data-account-payable-id="${supplier.account_payable_id}">${supplier.name}</option>`
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
                            `<option value="${club.id}" data-account-receivable-id="${club.account_receivable_id}">${club.name}</option>`
                        );
                    });
                    if (selectedClubId) {
                        $clubSelect.val(selectedClubId).trigger('change');
                    }
                }
            }
        });
    }

    // Cargar clubs para edición (incluye cuentas completadas) y seleccionar el club correspondiente
    loadClubsByCategoryForEditAndSelect(categoryIncomeId, selectedClubId) {
        const $clubSelect = $(CONFIG.selectors.forms.clubId);
        $clubSelect.empty().append('<option value="">-- Seleccionar --</option>');
        $.ajax({
            url: CONFIG.endpoints.clubsByCategoryForEdit(categoryIncomeId),
            type: 'GET',
            data: { event_id: CONFIG.eventId },
            success: (clubs) => {
                if (clubs?.length) {
                    clubs.forEach(club => {
                        $clubSelect.append(
                            `<option value="${club.id}" data-account-receivable-id="${club.account_receivable_id}">${club.name}</option>`
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
                    `<option value="${club.id}" data-account-receivable-id="${club.account_receivable_id}">${club.name}</option>`
                );
            });
        }
    }

    // Manejo de error en clubs
    handleClubsError(xhr, status, error) {
        console.error('Error al obtener clubs:', error);
        
        // Mostrar mensaje de error al usuario
        let errorMessage = 'Error al cargar los clubs';
        
        if (xhr.responseJSON && xhr.responseJSON.error) {
            errorMessage = xhr.responseJSON.error;
        } else if (xhr.status === 400) {
            errorMessage = 'Error: Event ID es requerido';
        } else if (xhr.status === 404) {
            errorMessage = 'Error: Evento no encontrado';
        }
        
        // Limpiar el select y mostrar mensaje de error
        $(CONFIG.selectors.forms.clubId)
            .empty()
            .append('<option value="">Error al cargar clubs</option>')
            .append(`<option value="" disabled>${errorMessage}</option>`);
    }

    // Manejo de respuesta de gastos
    handleExpensesResponse(expenses) {
        console.log('handleExpensesResponse called with:', expenses);
        
        if (expenses?.length) {
            console.log('Found', expenses.length, 'expenses');
            expenses.forEach(expense => {
                console.log('Processing expense:', expense);
                // Usar el nombre del gasto directamente
                const optionText = expense.name || expense.category_expense?.name || 'Sin nombre';
                console.log('Adding option:', optionText);
                $(CONFIG.selectors.forms.expenseId).append(
                    `<option value="${expense.id}">${optionText}</option>`
                );
            });
        } else {
            console.log('No expenses found or empty response');
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
                    `<option value="${supplier.id}" data-account-payable-id="${supplier.account_payable_id}">${supplier.name}</option>`
                );
            });
        }
    }

    // Manejo de error en proveedores
    handleSuppliersError(xhr, status, error) {
        console.error('Error al obtener proveedores:', error);
        
        // Mostrar mensaje de error al usuario
        let errorMessage = 'Error al cargar los proveedores';
        
        if (xhr.responseJSON && xhr.responseJSON.error) {
            errorMessage = xhr.responseJSON.error;
        } else if (xhr.status === 400) {
            errorMessage = 'Error: Event ID es requerido';
        } else if (xhr.status === 404) {
            errorMessage = 'Error: Evento no encontrado';
        }
        
        // Limpiar el select y mostrar mensaje de error
        $(CONFIG.selectors.forms.supplierId)
            .empty()
            .append('<option value="">Error al cargar proveedores</option>')
            .append(`<option value="" disabled>${errorMessage}</option>`);
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
    openEditModal(movementId) {
        // Mostrar loading
        Swal.fire({
            title: 'Cargando movimiento...',
            text: 'Por favor espera mientras se cargan los datos',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Cargar los datos del movimiento desde el servidor
        $.ajax({
            url: `/eventos/${movementId}/edit-history`,
            type: 'GET',
            success: (response) => {
                Swal.close();
                this.populateEditModal(response);
            },
            error: (xhr, status, error) => {
                Swal.close();
                Swal.fire({
                    title: 'Error',
                    text: 'Error al cargar los datos del movimiento',
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
                console.error('Error al cargar movimiento:', error);
            }
        });
    }

    // Poblar el modal de edición con los datos del movimiento
    populateEditModal(movement) {
        const modal = $(CONFIG.selectors.modals.movement);

        // Debug: mostrar los datos del movimiento en consola
        console.log('Datos del movimiento recibidos:', movement);

        modal.find('input[name="id"]').remove();
        modal.find('form').prepend(`<input type="hidden" name="id" value="${movement.id}">`);
        modal.find('textarea[name="description"]').val(movement.description ?? '');
        
        // Manejo mejorado de la fecha
        if (movement.date) {
            // Si la fecha viene en formato ISO (YYYY-MM-DD), usarla directamente
            if (movement.date.match(/^\d{4}-\d{2}-\d{2}$/)) {
                modal.find('input[name="date"]').val(movement.date);
            } else {
                // Si viene en otro formato, convertirla
                const dateObj = new Date(movement.date);
                if (!isNaN(dateObj.getTime())) {
                    const formattedDate = dateObj.toISOString().split('T')[0];
                    modal.find('input[name="date"]').val(formattedDate);
                } else {
                    console.warn('Formato de fecha no reconocido:', movement.date);
                    modal.find('input[name="date"]').val('');
                }
            }
        } else {
            modal.find('input[name="date"]').val('');
        }
        
        modal.find('select[name="type"]').val(movement.type ?? '').trigger('change');
        modal.find('select[name="type_income"]').val(movement.category_income_id ?? '').trigger('change');
        modal.find('select[name="type_expense"]').val(movement.category_egress_id ?? '').trigger('change');
        modal.find('select[name="expense_id"]').val(movement.expense_id ?? '').trigger('change');
        modal.find('select[name="currency_id"]').val(movement.currency_id ?? '').trigger('change');
        modal.find('input[name="amount"]').val(movement.amount ?? '');

        // Clubs dependiente de tipo de ingreso
        if (movement.type === 'Ingreso' && movement.category_income_id) {
            this.loadClubsByCategoryForEditAndSelect(movement.category_income_id, movement.club_id);
            
            // Hacer readonly el selector de club para evitar cambios
            $(CONFIG.selectors.forms.clubId).prop('disabled', true);
            
            // Si el movimiento tiene account_receivable_id, mostrarlo
            if (movement.account_receivable_id) {
                $('#account_receivable_id').val(movement.account_receivable_id);
                $('#account_receivable_id_div').show();
                console.log('ID de cuenta por cobrar incluido:', movement.account_receivable_id);
            }
            
            // Si el movimiento tiene account_receivable_payment_id, incluirlo en el formulario
            if (movement.account_receivable_payment_id) {
                $('#account_receivable_payment_id').val(movement.account_receivable_payment_id);
                console.log('ID de pago de cuenta por cobrar incluido:', movement.account_receivable_payment_id);
            }
            
            // Mostrar información del pago si existe
            if (movement.account_receivable_payment) {
                console.log('Información del pago de cuenta por cobrar:', movement.account_receivable_payment);
                // Mostrar notificación de que el pago será actualizado
                this.showPaymentUpdateNotification('cuenta por cobrar', movement.account_receivable_payment.amount, movement.amount);
            }
        }

        // Proveedores dependiente de tipo de egreso
        if (movement.type === 'Egreso' && movement.category_egress_id) {
            this.loadSuppliersByCategoryForEditAndSelect(movement.category_egress_id, movement.supplier_id);
            this.loadExpensesByCategoryAndSelect(movement.category_egress_id, movement.expense_id);
            
            // Hacer readonly el selector de proveedor para evitar cambios
            $(CONFIG.selectors.forms.supplierId).prop('disabled', true);
            
            // Si el movimiento tiene account_payable_id, mostrarlo
            if (movement.account_payable_id) {
                $('#account_payable_id').val(movement.account_payable_id);
                $('#account_payable_id_div').show();
                console.log('ID de cuenta por pagar incluido:', movement.account_payable_id);
            }
            
            // Si el movimiento tiene account_payable_payment_id, incluirlo en el formulario
            if (movement.account_payable_payment_id) {
                $('#account_payable_payment_id').val(movement.account_payable_payment_id);
                console.log('ID de pago de cuenta por pagar incluido:', movement.account_payable_payment_id);
            }
            
            // Mostrar información del pago si existe
            if (movement.account_payable_payment) {
                console.log('Información del pago de cuenta por pagar:', movement.account_payable_payment);
                // Mostrar notificación de que el pago será actualizado
                this.showPaymentUpdateNotification('cuenta por pagar', movement.account_payable_payment.amount, movement.amount);
            }
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
        
        // Actualizar descripción después de cargar todos los datos
        setTimeout(() => {
            this.updateDescription();
        }, 100);

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
        
        // Habilitar todos los selectores (en caso de que estuvieran deshabilitados por edición)
        modal.find('select').prop('disabled', false);
        
        // Limpiar el campo account_receivable_id
        $('#account_receivable_id').val('');
        $('#account_receivable_id_div').hide();
        
        // Limpiar el campo account_payable_id
        $('#account_payable_id').val('');
        $('#account_payable_id_div').hide();
        
        // Limpiar los campos de IDs de pagos
        $('#account_receivable_payment_id').val('');
        $('#account_payable_payment_id').val('');
        
        this.hideOptionalDivs();
        this.updateDescription(); // Limpiar descripción
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

            // Validar formulario antes de mostrar confirmación
            if (!window.historyManager.validateMovementForm()) {
                return false;
            }

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

    // Validar formulario de movimiento
    validateMovementForm() {
        const modal = $(CONFIG.selectors.modals.movement);
        let isValid = true;
        let errorMessages = [];

        // 1. Validar descripción
        const description = modal.find('textarea[name="description"]').val().trim();
        if (!description) {
            errorMessages.push('La descripción del movimiento es obligatoria.');
            isValid = false;
        }

        // 2. Validar fecha
        const date = modal.find('input[name="date"]').val();
        if (!date) {
            errorMessages.push('La fecha del movimiento es obligatoria.');
            isValid = false;
        }

        // 3. Validar tipo de movimiento
        const type = modal.find('select[name="type"]').val();
        if (!type) {
            errorMessages.push('Debe seleccionar un tipo de movimiento.');
            isValid = false;
        }

        // 4. Validar moneda
        const currencyId = modal.find('select[name="currency_id"]').val();
        if (!currencyId) {
            errorMessages.push('Debe seleccionar una moneda.');
            isValid = false;
        }

        // 5. Validar método de pago
        const methodPaymentId = modal.find('select[name="method_payment_id"]').val();
        if (!methodPaymentId) {
            errorMessages.push('Debe seleccionar un método de pago.');
            isValid = false;
        } else {
            // Validar que el método de pago tenga la misma moneda
            const selectedMethodOption = modal.find('select[name="method_payment_id"] option:selected');
            if (selectedMethodOption.length && selectedMethodOption.val()) {
                // Verificar que el método de pago esté disponible para la moneda seleccionada
                const methodText = selectedMethodOption.text();
                if (methodText.includes('-- Seleccionar --')) {
                    errorMessages.push('El método de pago seleccionado no es válido para la moneda elegida.');
                    isValid = false;
                }
            }
        }

        // 6. Validar monto
        const amount = modal.find('input[name="amount"]').val();
        if (!amount || parseFloat(amount.replace(/,/g, '')) <= 0) {
            errorMessages.push('El monto debe ser mayor a 0.');
            isValid = false;
        }

        // Validaciones específicas para Ingresos
        if (type === 'Ingreso') {
            const typeIncome = modal.find('select[name="type_income"]').val();
            if (!typeIncome) {
                errorMessages.push('Debe seleccionar una categoría de ingreso.');
                isValid = false;
            }

            // Si es categoría de club (ID 1), validar cuenta por cobrar
            if (typeIncome === '1') {
                const clubId = modal.find('select[name="club_id"]').val();
                if (!clubId) {
                    errorMessages.push('Debe seleccionar una cuenta por cobrar del club.');
                    isValid = false;
                }
            }
        }

        // Validaciones específicas para Egresos
        if (type === 'Egreso') {
            const typeExpense = modal.find('select[name="type_expense"]').val();
            if (!typeExpense) {
                errorMessages.push('Debe seleccionar una categoría de egreso.');
                isValid = false;
            }

            // Si es categoría de gastos (ID 1), validar gasto
            if (typeExpense === '1') {
                const expenseId = modal.find('select[name="expense_id"]').val();
                if (!expenseId) {
                    errorMessages.push('Debe seleccionar un gasto registrado.');
                    isValid = false;
                }
            }

            // Si es categoría de proveedor (ID 2), validar cuenta por pagar
            if (typeExpense === '2') {
                const supplierId = modal.find('select[name="supplier_id"]').val();
                if (!supplierId) {
                    errorMessages.push('Debe seleccionar una cuenta por pagar del proveedor.');
                    isValid = false;
                }
            }
        }

        // Mostrar errores si los hay
        if (!isValid) {
            Swal.fire({
                title: 'Error de validación',
                html: errorMessages.map(msg => `• ${msg}`).join('<br>'),
                icon: 'error',
                confirmButtonText: 'Entendido'
            });
        }

        return isValid;
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

    // Cancelar un movimiento de evento
    cancelEventMovement(movementId) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción cancelará el movimiento y restaurará el dinero en la cuenta correspondiente. ¿Deseas continuar?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'No, mantener',
            customClass: {
                confirmButton: 'btn btn-danger me-3 waves-effect waves-light',
                cancelButton: 'btn btn-outline-secondary waves-effect'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                this.processCancellation(movementId);
            }
        });
    }

    // Mostrar notificación de actualización de pago
    showPaymentUpdateNotification(paymentType, oldAmount, newAmount) {
        const oldAmountFormatted = CONFIG.numberFormat.format(oldAmount);
        const newAmountFormatted = CONFIG.numberFormat.format(newAmount);
        
        Swal.fire({
            title: 'Actualización de Pago',
            html: `
                <div class="text-start">
                    <p><strong>Tipo:</strong> ${paymentType}</p>
                    <p><strong>Monto anterior:</strong> ${oldAmountFormatted}</p>
                    <p><strong>Monto nuevo:</strong> ${newAmountFormatted}</p>
                    <p class="text-info small">El pago será actualizado automáticamente al guardar el movimiento.</p>
                </div>
            `,
            icon: 'info',
            confirmButtonText: 'Entendido',
            customClass: {
                confirmButton: 'btn btn-primary waves-effect waves-light'
            }
        });
    }

    // Procesar la cancelación
    processCancellation(movementId) {
        // Mostrar loading
        Swal.fire({
            title: 'Cancelando movimiento...',
            text: 'Por favor espera mientras se procesa la cancelación',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Realizar la petición AJAX
        $.ajax({
            url: `/event-movements/${movementId}/cancel`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (response) => {
                if (response.success) {
                    Swal.fire({
                        title: '¡Movimiento cancelado!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'Entendido'
                    }).then(() => {
                        // Recargar la tabla
                        this.datatable.ajax.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.message || 'Error al cancelar el movimiento',
                        icon: 'error',
                        confirmButtonText: 'Entendido'
                    });
                }
            },
            error: (xhr, status, error) => {
                let errorMessage = 'Error al cancelar el movimiento';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    title: 'Error',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
            }
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
                // Asegurar que todos los selectores estén habilitados para crear nuevo movimiento
                $(CONFIG.selectors.modals.movement).find('select').prop('disabled', false);
                $(CONFIG.selectors.modals.movement).modal('show');
            }
        });

        // Botón de cancelar movimiento
        $(document).on('click', '.btn-cancel-movement', function(e) {
            e.preventDefault();
            const movementId = $(this).data('movement-id');
            window.historyManager.cancelEventMovement(movementId);
        });
    });
