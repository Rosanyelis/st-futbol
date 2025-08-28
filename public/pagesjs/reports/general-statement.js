"use strict";

// Configuración y constantes
const CONFIG = {
    numberFormat: new Intl.NumberFormat("es-MX"),
    endpoints: {
        generalStatementJson: '/reportes/estado-general',
        paymentMethods: (currencyId) => `/eventos/metodo-pago/${currencyId}`,
        currencies: '/eventos/currencies',
        events: '/eventos/list',
        categoryIncomes: '/categorias-ingresos/list',
        categoryEgress: '/categorias-egresos/list'
    },
    selectors: {
        datatable: ".datatables-general-statement",
        filters: {
            eventId: '#event_filter',
            currencyId: '#currency_filter',
            categoryIncomeId: '#category_income_filter',
            categoryEgressId: '#category_egress_filter',
            startDate: '#start_date_filter',
            endDate: '#end_date_filter'
        },
        totals: {
            balance: '#totalBalance',
            income: '#totalIngresos',
            expense: '#totalEgresos'
        }
    }
};

// Clase principal para manejar la página de estado general
class GeneralStatementManager {
    constructor() {
        this.datatable = null;
        this.initializeDatatable();
        this.initializeEventListeners();
    }

    // Inicialización del DataTable
    initializeDatatable() {
        console.log('Inicializando DataTable...');
        const table = $(CONFIG.selectors.datatable);
        console.log('Tabla encontrada:', table.length);
        if (!table.length) {
            console.log('No se encontró la tabla, saliendo...');
            return;
        }

        this.datatable = table.DataTable({
            processing: true,
            serverSide: true, // Usar procesamiento del lado del servidor
            ajax: {
                url: CONFIG.endpoints.generalStatementJson,
                data: (d) => {
                    d.event_id = $(CONFIG.selectors.filters.eventId).val();
                    d.currency_id = $(CONFIG.selectors.filters.currencyId).val();
                    d.category_income_id = $(CONFIG.selectors.filters.categoryIncomeId).val();
                    d.category_egress_id = $(CONFIG.selectors.filters.categoryEgressId).val();
                    d.start_date = $(CONFIG.selectors.filters.startDate).val();
                    d.end_date = $(CONFIG.selectors.filters.endDate).val();
                }
            },
            dom: this.getDatatableDOM(),
            language: this.getDatatableLanguage(),
            columns: this.getDatatableColumns(),
            columnDefs: this.getColumnDefinitions(),
            buttons: this.getDatatableButtons(),
            pageLength: 50, // Mostrar más registros por página
            drawCallback: () => {
                this.updateTotals();
                this.initializeTooltips();
                this.debugData(); // Activado para verificar funcionamiento
            },
            initComplete: () => {
                console.log('DataTable initComplete: Configurando filtros...');
                this.setupFilters();
                console.log('DataTable initComplete: Filtros configurados');
            }
        });

        console.log('DataTable creado correctamente');
        this.setupDatatableStyles();
    }

    // Configuración del DOM del DataTable
    getDatatableDOM() {
        return '<"card-header d-flex border-top rounded-0 flex-wrap pb-md-0 pt-0"' +
            '<"d-flex align-items-center me-5"' +
                '<"me-3"f>' +
                '<"event-filter me-2">' +
                '<"category-income-filter me-2">' +
                '<"category-egress-filter me-2">' +
                '<"date-filter">' +
            '>' +
            '<"ms-auto d-flex justify-content-end align-items-center gap-4"' +
                '<"d-flex align-items-center"l>' +
                '<"dt-action-buttons d-flex align-items-center"B>' +
            '>' +
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
            {data: 'event.name'},
            {data: 'description'},
            {data: 'amount'},
            {data: 'amount'},
            {data: 'currency.name'},
            {data: 'category_income.name'},
            {data: 'category_egress.name'},
            {data: 'club.name'},
            {data: 'supplier.name'},
            {data: 'method_payment'},
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
                    this.renderOptionalField(full.event?.name)
            },
            {
                targets: 2,
                render: (data, type, full) => 
                    this.renderDescription(full.description)
            },
            {
                targets: 3,
                render: (data, type, full) => 
                    this.renderAmount(full, 'Ingreso')
            },
            {
                targets: 4,
                render: (data, type, full) => 
                    this.renderAmount(full, 'Egreso')
            },
            {
                targets: 5,
                render: (data, type, full) => 
                    `<span class='text-nowrap'>${full.currency?.name} ${full.currency?.symbol}</span>`
            },
            {
                targets: 6,
                render: (data, type, full) => {
                    // Solo mostrar categoría de ingreso si el tipo es 'Ingreso'
                    if (full.type === 'Ingreso') {
                        const categoryName = full.category_income?.name || full.categoryIncome?.name;
                        if (categoryName) {
                            return `<span class="text-nowrap text-primary">${categoryName}</span>`;
                        } else {
                            return '<span class="text-nowrap text-muted">Sin categoría</span>';
                        }
                    }
                    return '<span class="text-nowrap text-muted"> - </span>';
                }
            },
            {
                targets: 7,
                render: (data, type, full) => {
                    // Solo mostrar categoría de egreso si el tipo es 'Egreso'
                    if (full.type === 'Egreso') {
                        const categoryName = full.category_egress?.name || full.categoryEgress?.name;
                        if (categoryName) {
                            return `<span class="text-nowrap text-danger">${categoryName}</span>`;
                        } else {
                            return '<span class="text-nowrap text-muted">Sin categoría</span>';
                        }
                    }
                    return '<span class="text-nowrap text-muted"> - </span>';
                }
            },
            {
                targets: 8,
                render: (data, type, full) => 
                    this.renderOptionalField(full.club?.name)
            },
            {
                targets: 9,
                render: (data, type, full) => 
                    this.renderOptionalField(full.supplier?.name)
            },
            {
                targets: 10,
                render: (data, type, full) => {
                    // Verificar si tenemos datos de método de pago
                    const methodPayment = full.method_payment || full.methodPayment;
                    return this.renderMethodPaymentWithTooltip(methodPayment);
                }
            }
        ];
    }

    // Botones del DataTable
    getDatatableButtons() {
        return [{
            text: 'Exportar Excel',
            className: 'btn btn-success btn-export-excel',
            action: () => this.exportToExcel()
        }];
    }

    // Renderizado de montos
    renderAmount(item, type) {
        return item.type === type
            ? `<span class='text-nowrap'><strong>${CONFIG.numberFormat.format(item.amount ?? 0)}</strong></span>`
            : `<span class='text-nowrap'> - </span>`;
    }

    // Renderizado de descripción
    renderDescription(description) {
        return description ? `<span class='text-wrap'>${description}</span>` : '-';
    }

    // Renderizado de campos opcionales
    renderOptionalField(value) {
        return value ? `<span class='text-nowrap'>${value}</span>` : '-';
    }

    // Renderizado de método de pago con tooltip
    renderMethodPaymentWithTooltip(method) {
        if (!method?.account_holder) {
            return `<span class='text-nowrap'> - </span>`;
        }
        
        // Texto para mostrar en la celda (con <br> para indentación visual)
        const displayText = `${method.account_holder} <br> ${method.entity?.name} <br> ${method.type_account}`;
        
        // Texto para el tooltip (con saltos de línea reales, sin HTML)
        const tooltipText = `${method.account_holder}\n${method.entity?.name}\n${method.type_account}`;
        
        const maxLength = 50;
        
        if (displayText.length <= maxLength) {
            return `<span class='text-nowrap'>${displayText}</span>`;
        } else {
            const truncated = displayText.substring(0, maxLength) + '...';
            return `<span class='text-nowrap' data-bs-toggle="tooltip" data-bs-placement="top" title="${tooltipText.replace(/"/g, '&quot;')}">${truncated}</span>`;
        }
    }

    // Configuración de filtros
    setupFilters() {
        console.log('Configurando filtros...');
        this.setupEventFilter();
        this.setupCategoryIncomeFilter();
        this.setupCategoryEgressFilter();
        this.setupDateFilter();
        console.log('Filtros configurados correctamente');
    }

    // Filtro de eventos
    setupEventFilter() {
        const filterContainer = $(CONFIG.selectors.datatable).closest('.card').find('.event-filter');
        filterContainer.html(`
            <select id="event_filter" class="form-select form-select-sm" style="width: 150px;">
                <option value="">Todos los eventos</option>
            </select>
        `);

        // Cargar eventos
        $.get(CONFIG.endpoints.events, (events) => {
            const select = $('#event_filter');
            events.forEach(event => {
                select.append(`<option value="${event.id}">${event.name} - ${event.year}</option>`);
            });
        });

        $('#event_filter').on('change', () => this.datatable.ajax.reload());
    }

    // Filtro de categorías de ingreso
    setupCategoryIncomeFilter() {
        const filterContainer = $(CONFIG.selectors.datatable).closest('.card').find('.category-income-filter');
        filterContainer.html(`
            <select id="category_income_filter" class="form-select form-select-sm" style="width: 150px;">
                <option value="">Todos los tipos de ingreso</option>
            </select>
        `);

        // Cargar categorías de ingreso
        $.get(CONFIG.endpoints.categoryIncomes, (categories) => {
            const select = $('#category_income_filter');
            categories.forEach(category => {
                select.append(`<option value="${category.id}">${category.name}</option>`);
            });
        });

        $('#category_income_filter').on('change', () => this.datatable.ajax.reload());
    }

    // Filtro de categorías de egreso
    setupCategoryEgressFilter() {
        const filterContainer = $(CONFIG.selectors.datatable).closest('.card').find('.category-egress-filter');
        filterContainer.html(`
            <select id="category_egress_filter" class="form-select form-select-sm" style="width: 150px;">
                <option value="">Todos los tipos de egreso</option>
            </select>
        `);

        // Cargar categorías de egreso
        $.get(CONFIG.endpoints.categoryEgress, (categories) => {
            const select = $('#category_egress_filter');
            categories.forEach(category => {
                select.append(`<option value="${category.id}">${category.name}</option>`);
            });
        });

        $('#category_egress_filter').on('change', () => this.datatable.ajax.reload());
    }

    // Filtro de fechas
    setupDateFilter() {
        const filterContainer = $(CONFIG.selectors.datatable).closest('.card').find('.date-filter');
        filterContainer.html(`
            <input type="date" id="start_date_filter" class="form-control form-control-sm" style="width: 130px;" placeholder="Desde">
            <input type="date" id="end_date_filter" class="form-control form-control-sm ms-1" style="width: 130px;" placeholder="Hasta">
        `);

        $('#start_date_filter, #end_date_filter').on('change', () => this.datatable.ajax.reload());
    }

    // Actualizar totales
    updateTotals() {
        const data = this.datatable.data().toArray();
        const totals = {};

        data.forEach(item => {
            const currencyName = item.currency?.name || 'Unknown';
            if (!totals[currencyName]) {
                totals[currencyName] = { ingresos: 0, egresos: 0 };
            }

            if (item.type === 'Ingreso') {
                totals[currencyName].ingresos += parseFloat(item.amount || 0);
            } else if (item.type === 'Egreso') {
                totals[currencyName].egresos += parseFloat(item.amount || 0);
            }
        });

        // Actualizar widgets
        Object.keys(totals).forEach(currencyName => {
            const ingresosElement = $(`#totalIngreso${currencyName}`);
            const egresosElement = $(`#totalEgreso${currencyName}`);

            if (ingresosElement.length) {
                ingresosElement.text(CONFIG.numberFormat.format(totals[currencyName].ingresos));
            }
            if (egresosElement.length) {
                egresosElement.text(CONFIG.numberFormat.format(totals[currencyName].egresos));
            }
        });
    }

    // Inicializar tooltips
    initializeTooltips() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    }

    // Configurar estilos del DataTable
    setupDatatableStyles() {
        // Agregar clases CSS personalizadas si es necesario
    }

    // Inicializar event listeners
    initializeEventListeners() {
        // Event listeners adicionales si son necesarios
    }

    // Función de depuración para verificar datos
    debugData() {
        const data = this.datatable.data().toArray();
        console.log('Total de registros cargados:', data.length);
        
        if (data.length > 0) {
            console.log('Primer registro:', data[0]);
            console.log('Categoría de ingreso:', data[0].categoryIncome);
            console.log('Categoría de egreso:', data[0].categoryEgress);
            console.log('Tipo de movimiento:', data[0].type);
            console.log('Método de pago:', data[0].method_payment || data[0].methodPayment);
            
            // Verificar específicamente las columnas de categorías y métodos de pago
            data.forEach((item, index) => {
                if (index < 5) { // Mostrar los primeros 5 registros
                    const methodPayment = item.method_payment || item.methodPayment;
                    console.log(`Registro ${index + 1}:`, {
                        id: item.id,
                        type: item.type,
                        description: item.description,
                        categoryIncome: item.categoryIncome?.name || item.category_income?.name || 'NULL',
                        categoryEgress: item.categoryEgress?.name || item.category_egress?.name || 'NULL',
                        methodPayment: methodPayment ? {
                            account_holder: methodPayment.account_holder,
                            entity: methodPayment.entity?.name,
                            type_account: methodPayment.type_account
                        } : 'NULL'
                    });
                }
            });
        } else {
            console.log('No hay registros cargados en el DataTable');
        }
    }

    // Exportar a Excel
    exportToExcel() {
        const params = new URLSearchParams({
            event_id: $(CONFIG.selectors.filters.eventId).val() || '',
            currency_id: $(CONFIG.selectors.filters.currencyId).val() || '',
            category_income_id: $(CONFIG.selectors.filters.categoryIncomeId).val() || '',
            category_egress_id: $(CONFIG.selectors.filters.categoryEgressId).val() || '',
            start_date: $(CONFIG.selectors.filters.startDate).val() || '',
            end_date: $(CONFIG.selectors.filters.endDate).val() || ''
        });

        window.open(`${CONFIG.endpoints.generalStatementJson}/export?${params.toString()}`, '_blank');
    }
}

// Inicialización cuando el DOM esté listo
$(document).ready(function() {
    console.log('General Statement Manager: Inicializando...');
    new GeneralStatementManager();
    console.log('General Statement Manager: Inicializado correctamente');
});
