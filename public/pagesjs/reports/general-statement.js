"use strict";

// Configuración y constantes
const CONFIG = {
    numberFormat: new Intl.NumberFormat("es-MX"),
    endpoints: {
        movementsStatementJson: '/reportes/estado-movimientos',
        events: '/eventos/list',
        currencies: '/eventos/currencies',
        methodPayments: '/metodos-pago/list',
        categoryIncomes: '/categorias-ingresos/list',
        categoryEgress: '/categorias-egresos/list'
    },
    selectors: {
        datatable: ".datatables-movements-statement",
        filters: {
            eventId: '#event_filter',
            currencyId: '#currency_filter',
            methodPaymentId: '#method_payment_filter',
            categoryIncomeId: '#category_income_filter',
            categoryEgressId: '#category_egress_filter',
            startDate: '#start_date_filter',
            endDate: '#end_date_filter'
        }
    }
};

// Clase principal para manejar la página de movimientos por cuentas
class MovementsStatementManager {
    constructor() {
        this.datatable = null;
        this.initializeDatatable();
        this.initializeEventListeners();
    }

    // Inicialización del DataTable
    initializeDatatable() {
        console.log('Inicializando DataTable de Movimientos...');
        const table = $(CONFIG.selectors.datatable);
        console.log('Tabla encontrada:', table.length);
        if (!table.length) {
            console.log('No se encontró la tabla, saliendo...');
            return;
        }

        this.datatable = table.DataTable({
            processing: true,
            // serverSide: true, // Usar procesamiento del lado del servidor
            ajax: {
                url: CONFIG.endpoints.movementsStatementJson,
                data: (d) => {
                    d.event_id = $(CONFIG.selectors.filters.eventId).val();
                    d.category_income_id = $(CONFIG.selectors.filters.categoryIncomeId).val();
                    d.category_egress_id = $(CONFIG.selectors.filters.categoryEgressId).val();
                    d.start_date = $(CONFIG.selectors.filters.startDate).val();
                    d.end_date = $(CONFIG.selectors.filters.endDate).val();
                },
                error: (xhr, error, thrown) => {
                    console.error('Error en AJAX:', error);
                    console.error('XHR:', xhr);
                    console.error('Thrown:', thrown);
                }
            },
            scrollY: '350px',
            scrollX: true,
            language: this.getDatatableLanguage(),
            columns: this.getDatatableColumns(),
            columnDefs: this.getColumnDefinitions(),
            buttons: this.getDatatableButtons(),
            pageLength: 50,
            order: [[0, 'desc']], // Ordenar por la primera columna (fecha) de forma descendente
            drawCallback: () => {
                this.updateTotals();
                this.initializeTooltips();
                this.debugData();
            },
            initComplete: () => {
                console.log('DataTable initComplete: Configurando filtros...');
                this.setupFilters();
                console.log('DataTable initComplete: Filtros configurados');
            }
        });

        console.log('DataTable de Movimientos creado correctamente');
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
            search: "",
            searchPlaceholder: "Buscar..."
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

    // Renderizado de método de pago con tooltip
    renderMethodPaymentWithTooltip(method) {
        if (!method?.account_holder) {
            return `<span class='text-nowrap'> - </span>`;
        }
        
        // Texto para mostrar en la celda (con <br> para indentación visual)
        const displayText = `${method.account_holder} - ${method.entity?.name} - ${method.type_account}`;
        
        // Texto para el tooltip (con saltos de línea reales, sin HTML)
        const tooltipText = `${method.account_holder} - ${method.entity?.name} - ${method.type_account}`;
        
        return `<span class='text-nowrap text-center'>${displayText}</span>`;
    }

    // Renderizado de descripción
    renderDescription(description) {
        return description ? `<span class='text-nowrap'>${description}</span>` : '-';
    }

    // Renderizado de campos opcionales
    renderOptionalField(value) {
        return value ? `<span class='text-nowrap'>${value}</span>` : '-';
    }



    // Configuración de filtros
    setupFilters() {
        console.log('Configurando filtros de movimientos...');
        
        this.loadEventFilter();
        this.loadCategoryIncomeFilter();
        this.loadCategoryEgressFilter();
        this.setupFilterEvents();
        console.log('Filtros de movimientos configurados correctamente');
    }

    // Cargar filtro de eventos
    loadEventFilter() {
        console.log('Cargando filtro de eventos...');
        
        $.get(CONFIG.endpoints.events, (events) => {
            console.log('Eventos cargados:', events.length);
            const select = $('#event_filter');
            events.forEach(event => {
                select.append(`<option value="${event.id}">${event.name} - ${event.year}</option>`);
            });
        }).fail((error) => {
            console.error('Error cargando eventos:', error);
        });
    }

    // Cargar filtro de categorías de ingreso
    loadCategoryIncomeFilter() {
        console.log('Cargando filtro de categorías de ingreso...');
        
        $.get(CONFIG.endpoints.categoryIncomes, (categories) => {
            console.log('Categorías de ingreso cargadas:', categories.length);
            const select = $('#category_income_filter');
            categories.forEach(category => {
                select.append(`<option value="${category.id}">${category.name}</option>`);
            });
        }).fail((error) => {
            console.error('Error cargando categorías de ingreso:', error);
        });
    }

    // Cargar filtro de categorías de egreso
    loadCategoryEgressFilter() {
        console.log('Cargando filtro de categorías de egreso...');
        
        $.get(CONFIG.endpoints.categoryEgress, (categories) => {
            console.log('Categorías de egreso cargadas:', categories.length);
            const select = $('#category_egress_filter');
            categories.forEach(category => {
                select.append(`<option value="${category.id}">${category.name}</option>`);
            });
        }).fail((error) => {
            console.error('Error cargando categorías de egreso:', error);
        });
    }

    // Configurar eventos de filtros
    setupFilterEvents() {
        console.log('Configurando eventos de filtros...');
        
        // Eventos para recargar la tabla cuando cambien los filtros
        $('#event_filter, #category_income_filter, #category_egress_filter, #start_date_filter, #end_date_filter').on('change', () => {
            console.log('Filtro cambiado, recargando tabla...');
            this.datatable.ajax.reload();
        });
        
        // Botón para limpiar filtros
        $('#clear_filters').on('click', () => {
            console.log('Limpiando filtros...');
            $('#event_filter').val('');
            $('#category_income_filter').val('');
            $('#category_egress_filter').val('');
            $('#start_date_filter').val('');
            $('#end_date_filter').val('');
            this.datatable.ajax.reload();
        });

        // Botones de exportación
        $('a[href*="movementsStatementPdf"]').on('click', (e) => {
            e.preventDefault();
            this.exportToPdf();
        });

        $('a[href*="movementsStatementExcel"]').on('click', (e) => {
            e.preventDefault();
            this.exportToExcel();
        });
        
        console.log('Eventos de filtros configurados');
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

    // Inicializar event listeners
    initializeEventListeners() {
        // Event listeners adicionales si son necesarios
    }

    // Función de depuración para verificar datos
    debugData() {
        const data = this.datatable.data().toArray();
        console.log('Total de registros de movimientos cargados:', data.length);
        
        if (data.length > 0) {
            console.log('Primer registro de movimientos:', data[0]);
            console.log('Categoría de ingreso:', data[0].categoryIncome);
            console.log('Categoría de egreso:', data[0].categoryEgress);
            console.log('Tipo de movimiento:', data[0].type);
            console.log('Método de pago:', data[0].method_payment || data[0].methodPayment);
            
            data.forEach((item, index) => {
                if (index < 5) { // Mostrar los primeros 5 registros
                    const methodPayment = item.method_payment || item.methodPayment;
                    console.log(`Registro de movimiento ${index + 1}:`, {
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
            console.log('No hay registros de movimientos cargados en el DataTable');
        }
    }

    // Exportar a Excel
    exportToExcel() {
        const params = new URLSearchParams({
            event_id: $('#event_filter').val() || '',
            category_income_id: $('#category_income_filter').val() || '',
            category_egress_id: $('#category_egress_filter').val() || '',
            start_date: $('#start_date_filter').val() || '',
            end_date: $('#end_date_filter').val() || ''
        });

        window.open(`/reportes/estado-movimientos/excel?${params.toString()}`, '_blank');
    }

    // Exportar a PDF
    exportToPdf() {
        const params = new URLSearchParams({
            event_id: $('#event_filter').val() || '',
            category_income_id: $('#category_income_filter').val() || '',
            category_egress_id: $('#category_egress_filter').val() || '',
            start_date: $('#start_date_filter').val() || '',
            end_date: $('#end_date_filter').val() || ''
        });

        window.open(`/reportes/estado-movimientos/pdf?${params.toString()}`, '_blank');
    }
}

// Inicialización cuando el DOM esté listo
$(document).ready(function() {
    console.log('Movements Statement Manager: Inicializando...');
    new MovementsStatementManager();
    console.log('Movements Statement Manager: Inicializado correctamente');
});
