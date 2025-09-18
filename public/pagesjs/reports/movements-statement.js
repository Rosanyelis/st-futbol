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
            ajax: {
                url: CONFIG.endpoints.movementsStatementJson,
                data: (d) => {
                    d.event_id = $(CONFIG.selectors.filters.eventId).val();
                    d.currency_id = $(CONFIG.selectors.filters.currencyId).val();
                    d.method_payment_id = $(CONFIG.selectors.filters.methodPaymentId).val();
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
            dom: this.getDatatableDOM(),
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


    // Configuración del DOM del DataTable
    getDatatableDOM() {
        return '<"card-header d-flex border-top rounded-0 flex-wrap pb-md-0 pt-0"' +
            '<"d-flex align-items-center me-5"' +
                '<"event-filter me-3">' +
                '<"currency-filter me-3">' +
                '<"method-payment-filter me-3">' +
                '<"date-filter me-3">' +
            '>' +
            '<"ms-auto d-flex justify-content-end align-items-center gap-4"' +
                '<"d-flex align-items-center"l>' +
                '<"dt-action-buttons d-flex align-items-center"B>' +
            '>' +
            '>' +
            '<"card-header d-flex border-top rounded-0 flex-wrap pb-md-0 pt-0"' +
            '<"d-flex align-items-center me-5"' +
                '<"me-3"f>' +
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
            search: "",
            searchPlaceholder: "Buscar..."
        };
    }

    // Definición de columnas
    getDatatableColumns() {
        return [
            {data: 'formatted_date'},
            {data: 'movement_type'},
            {data: 'movement_source'},
            {data: 'account_info'},
            {data: 'currency.name'},
            {data: 'event_name'},
            {data: 'club_name'},
            {data: 'supplier_name'},
            {data: 'formatted_amount'},
            {data: 'formatted_amount'},
            {data: 'categoryIncome.name'},
            {data: 'categoryEgress.name'},
            {data: 'description'}
        ];
    }

    // Definición de renderizado de columnas
    getColumnDefinitions() {
        return [
            {
                targets: 0,
                render: (data, type, full) => 
                    `<span class='text-nowrap'>${full.formatted_date || '-'}</span>`
            },
            {
                targets: 1,
                render: (data, type, full) => {
                    const badgeClass = full.movement_type === 'Evento' ? 'badge bg-info' : 'badge bg-warning';
                    return `<span class="${badgeClass}">${full.movement_type}</span>`;
                }
            },
            {
                targets: 2,
                render: (data, type, full) => 
                    `<span class='text-nowrap'>${full.movement_source || '-'}</span>`
            },
            {
                targets: 3,
                render: (data, type, full) => {
                    const methodPayment = full.methodPayment;
                    return this.renderMethodPaymentWithTooltip(methodPayment);
                }
            },
            {
                targets: 4,
                render: (data, type, full) => 
                    `<span class='text-nowrap'>${full.currency?.name || '-'} ${full.currency?.symbol || ''}</span>`
            },
            {
                targets: 5,
                render: (data, type, full) => 
                    this.renderOptionalField(full.event_name)
            },
            {
                targets: 6,
                render: (data, type, full) => 
                    this.renderOptionalField(full.club_name)
            },
            {
                targets: 7,
                render: (data, type, full) => 
                    this.renderOptionalField(full.supplier_name)
            },
            {
                targets: 8,
                render: (data, type, full) => 
                    this.renderAmount(full, 'Ingreso')
            },
            {
                targets: 9,
                render: (data, type, full) => 
                    this.renderAmount(full, 'Egreso')
            },
            {
                targets: 10,
                render: (data, type, full) => {
                    if (full.type === 'Ingreso') {
                        const categoryName = full.categoryIncome?.name;
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
                targets: 11,
                render: (data, type, full) => {
                    if (full.type === 'Egreso') {
                        const categoryName = full.categoryEgress?.name;
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
                targets: 12,
                render: (data, type, full) => 
                    this.renderDescription(full.description)
            }
        ];
    }

    // Botones del DataTable
    getDatatableButtons() {
        return [{
            extend: "collection",
            className: "btn btn-outline-secondary dropdown-toggle me-4 waves-effect waves-light",
            text: '<i class="ri-upload-2-line ri-16px me-2"></i><span class="d-none d-sm-inline-block">Exportar</span>',
            buttons: [
                {
                    extend: "csv",
                    text: '<i class="ri-file-text-line me-1" ></i>Csv',
                    className: "dropdown-item",
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                        modifier: {
                            search: 'applied',
                            order: 'applied',
                            page: 'all'
                        },
                        format: {
                            body: function (inner, coldex, rowdex) {
                                if (inner.length <= 0) return inner;
                                var el = $.parseHTML(inner);
                                var result = "";
                                $.each(el, function (index, item) {
                                    if (item.innerText === undefined) {
                                        result = result + item.textContent;
                                    } else result = result + item.innerText;
                                });
                                return result;
                            },
                        },
                    },
                },
                {
                    extend: "excel",
                    text: '<i class="ri-file-excel-line me-1"></i>Excel',
                    className: "dropdown-item",
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                        modifier: {
                            search: 'applied',
                            order: 'applied',
                            page: 'all'
                        },
                        format: {
                            body: function (inner, coldex, rowdex) {
                                if (inner.length <= 0) return inner;
                                var el = $.parseHTML(inner);
                                var result = "";
                                $.each(el, function (index, item) {
                                    if (item.innerText === undefined) {
                                        result = result + item.textContent;
                                    } else result = result + item.innerText;
                                });
                                return result;
                            },
                        },
                    },
                },
                {
                    extend: "pdf",
                    text: '<i class="ri-file-pdf-line me-1"></i>Pdf',
                    className: "dropdown-item",
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                        modifier: {
                            search: 'applied',
                            order: 'applied',
                            page: 'all'
                        },
                        format: {
                            body: function (inner, coldex, rowdex) {
                                if (inner.length <= 0) return inner;
                                var el = $.parseHTML(inner);
                                var result = "";
                                $.each(el, function (index, item) {
                                    if (item.innerText === undefined) {
                                        result = result + item.textContent;
                                    } else result = result + item.innerText;
                                });
                                return result;
                            },
                        },
                    },
                    customize: function (doc) {
                        // Personalizar el PDF
                        doc.content[1].table.widths = Array(doc.content[1].table.body[0].length + 1).join('*').split('');
                        doc.styles.tableHeader = {
                            fillColor: '#f8f9fa',
                            color: '#000',
                            fontSize: 10,
                            bold: true
                        };
                        doc.defaultStyle = {
                            fontSize: 9
                        };
                    }
                },
                {
                    extend: "print",
                    text: '<i class="ri-printer-line me-1" ></i>Imprimir',
                    className: "dropdown-item",
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                        modifier: {
                            search: 'applied',
                            order: 'applied',
                            page: 'all'
                        },
                        format: {
                            body: function (inner, coldex, rowdex) {
                                if (inner.length <= 0) return inner;
                                var el = $.parseHTML(inner);
                                var result = "";
                                $.each(el, function (index, item) {
                                    if (item.innerText === undefined) {
                                        result = result + item.textContent;
                                    } else result = result + item.innerText;
                                });
                                return result;
                            },
                        },
                    },
                    customize: function (win) {
                        $(win.document.body)
                            .css('font-size', '10pt')
                            .prepend('<h3>Movimientos por Cuentas/Métodos de Pago</h3>');
                        $(win.document.body).find('table')
                            .addClass('compact')
                            .css('font-size', 'inherit');
                    }
                }
            ]
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
            return `<span class='text-nowrap '> - </span>`;
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
        this.setupEventFilter();
        this.setupCurrencyFilter();
        this.setupMethodPaymentFilter();
        this.setupDateFilter();
        console.log('Filtros de movimientos configurados correctamente');
    }

    // Filtro de eventos
    setupEventFilter() {
        const filterContainer = $(CONFIG.selectors.datatable).closest('.card').find('.event-filter');
        filterContainer.html(`
            <select id="event_filter" class="form-select form-select-sm" style="width: 180px;">
                <option value="">Todos los eventos</option>
            </select>
        `);

        $.get(CONFIG.endpoints.events, (events) => {
            const select = $('#event_filter');
            events.forEach(event => {
                select.append(`<option value="${event.id}">${event.name} - ${event.year}</option>`);
            });
        });

        $('#event_filter').on('change', () => this.datatable.ajax.reload());
    }

    // Filtro de monedas
    setupCurrencyFilter() {
        const filterContainer = $(CONFIG.selectors.datatable).closest('.card').find('.currency-filter');
        filterContainer.html(`
            <select id="currency_filter" class="form-select form-select-sm" style="width: 140px;">
                <option value="">Todas las monedas</option>
            </select>
        `);

        $.get(CONFIG.endpoints.currencies, (currencies) => {
            const select = $('#currency_filter');
            currencies.forEach(currency => {
                select.append(`<option value="${currency.id}">${currency.name}</option>`);
            });
        });

        $('#currency_filter').on('change', () => this.datatable.ajax.reload());
    }

    // Filtro de métodos de pago
    setupMethodPaymentFilter() {
        const filterContainer = $(CONFIG.selectors.datatable).closest('.card').find('.method-payment-filter');
        filterContainer.html(`
            <select id="method_payment_filter" class="form-select form-select-sm" style="width: 220px;">
                <option value="">Todos los métodos de pago</option>
            </select>
        `);

        $.get(CONFIG.endpoints.methodPayments, (methodPayments) => {
            const select = $('#method_payment_filter');
            methodPayments.forEach(method => {
                const displayText = `${method.account_holder} - ${method.entity?.name || 'Sin entidad'}`;
                select.append(`<option value="${method.id}">${displayText}</option>`);
            });
        });

        $('#method_payment_filter').on('change', () => this.datatable.ajax.reload());
    }



    // Filtro de fechas
    setupDateFilter() {
        const filterContainer = $(CONFIG.selectors.datatable).closest('.card').find('.date-filter');
        filterContainer.html(`<div class="d-flex align-items-center">
            <div class="me-2">
                <input type="date" id="start_date_filter" class="form-control form-control-sm" style="width: 130px;" placeholder="dd/mm/aaaa">
            </div>
            <div class="me-2">
                <input type="date" id="end_date_filter" class="form-control form-control-sm" style="width: 130px;" placeholder="dd/mm/aaaa">
            </div>
            <div class="d-flex align-items-end">
                <button id="clear_date_filter" class="btn btn-outline-secondary btn-sm" style="height: 32px;" title="Limpiar">
                    <i class="ri-refresh-line"></i>
                </button>
            </div>
        </div>`);

        $('#start_date_filter, #end_date_filter').on('change', () => this.datatable.ajax.reload());
        $('#clear_date_filter').on('click', () => {
            $('#start_date_filter, #end_date_filter').val('');
            this.datatable.ajax.reload();
        });
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
            
            data.forEach((item, index) => {
                if (index < 3) { // Mostrar los primeros 3 registros
                    const methodPayment = item.method_payment || item.methodPayment;
                    console.log(`Registro de movimiento ${index + 1}:`, {
                        id: item.id,
                        date: item.date,
                        type: item.type,
                        description: item.description,
                        event: item.event?.name || 'NULL',
                        currency: item.currency?.name || 'NULL',
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


}

// Inicialización cuando el DOM esté listo
$(document).ready(function() {
    console.log('Movements Statement Manager: Inicializando...');
    new MovementsStatementManager();
    console.log('Movements Statement Manager: Inicializado correctamente');
});
