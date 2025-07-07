"use strict";
// Funciones utilitarias
const numberFormat = new Intl.NumberFormat('es-MX');
const formatCurrency = data => `$ ${numberFormat.format(data)}`;
class AccountReceivableReportsManager {
    constructor() {
        this.numberFormat = new Intl.NumberFormat("es-MX", {
            minimumFractionDigits: 0,
        });
        this.tableSelector = ".datatables-history";
        this.init();

        
    }

    init() {
        this.setupCustomFilters();
        this.initDataTable();
    }

    initDataTable() {
        const $table = $(this.tableSelector);
        if (!$table.length) return;

        this.datatable = $table.DataTable({
            processing: true,
            ajax: {
                url: "/reportes/cuentas-por-cobrar",
                data: (d) => {
                    d.event = $("#filter_event").val();
                },
            },
            dom:
                '<"card-header d-flex border-top rounded-0 flex-wrap pb-md-0 pt-0"' +
                '<"d-flex align-items-center me-5"' +
                '<"me-3"f>' +
                '<"event-filter me-3">' +
                ">" +
                '<"ms-auto d-flex justify-content-end align-items-center gap-4"' +
                '<"d-flex align-items-center"l>' +
                '<"dt-action-buttons d-flex align-items-center"B>' +
                ">" +
                ">t" +
                '<"row mx-1"' +
                '<"col-sm-12 col-md-6"i>' +
                '<"col-sm-12 col-md-6"p>' +
                ">",
            responsive: true,
            lengthMenu: [10, 20, 50, 70, 100], //for length of menu
            language: {
                sLengthMenu: "_MENU_",
                search: "",
                searchPlaceholder: "Buscar",
                info: "Mostrando _START_ a _END_ de _TOTAL_ entradas",
                paginate: {
                    next: '<i class="ri-arrow-right-s-line"></i>',
                    previous: '<i class="ri-arrow-left-s-line"></i>',
                },
            },
            columns: [
                { data: 'event_name', name: 'event_name' },
                { data: 'name', name: 'name' },
                { data: 'currency_name', name: 'currency_name' },
                { data: 'total_amount', render: formatCurrency, name: 'total_amount' },
                { data: 'pendiente', render: formatCurrency, name: 'pendiente' },
            ],
            order: [[0, "desc"]],
            buttons: [
                {
                    extend: "collection",
                    className:
                        "btn btn-outline-secondary dropdown-toggle me-4 waves-effect waves-light",
                    text: '<i class="ri-upload-2-line ri-16px me-2"></i><span class="d-none d-sm-inline-block">Exportar </span>',
                    buttons: [
                        {
                            extend: "csv",
                            text: '<i class="ri-file-text-line me-1" ></i>Csv',
                            className: "dropdown-item",
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4],
                                modifier: {
                                    search: "applied",
                                    order: "applied",
                                    page: "all", // <-- Esto asegura que exporte todos los registros
                                },
                                format: {
                                    body: function (inner, coldex, rowdex) {
                                        if (inner.length <= 0) return inner;
                                        var el = $.parseHTML(inner);
                                        var result = "";
                                        $.each(el, function (index, item) {
                                            if (
                                                item.classList !== undefined &&
                                                item.classList.contains(
                                                    "product-name"
                                                )
                                            ) {
                                                result =
                                                    result +
                                                    item.lastChild.firstChild
                                                        .textContent;
                                            } else if (
                                                item.innerText === undefined
                                            ) {
                                                result =
                                                    result + item.textContent;
                                            } else
                                                result =
                                                    result + item.innerText;
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
                                columns: [0, 1, 2, 3, 4],
                                // prevent avatar to be display
                                format: {
                                    body: function (inner, coldex, rowdex) {
                                        if (inner.length <= 0) return inner;
                                        var el = $.parseHTML(inner);
                                        var result = "";
                                        $.each(el, function (index, item) {
                                            if (
                                                item.classList !== undefined &&
                                                item.classList.contains(
                                                    "product-name"
                                                )
                                            ) {
                                                result =
                                                    result +
                                                    item.lastChild.firstChild
                                                        .textContent;
                                            } else if (
                                                item.innerText === undefined
                                            ) {
                                                result =
                                                    result + item.textContent;
                                            } else
                                                result =
                                                    result + item.innerText;
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
                                columns: [0, 1, 2, 3, 4],
                                // prevent avatar to be display
                                format: {
                                    body: function (inner, coldex, rowdex) {
                                        if (inner.length <= 0) return inner;
                                        var el = $.parseHTML(inner);
                                        var result = "";
                                        $.each(el, function (index, item) {
                                            if (
                                                item.classList !== undefined &&
                                                item.classList.contains(
                                                    "product-name"
                                                )
                                            ) {
                                                result =
                                                    result +
                                                    item.lastChild.firstChild
                                                        .textContent;
                                            } else if (
                                                item.innerText === undefined
                                            ) {
                                                result =
                                                    result + item.textContent;
                                            } else
                                                result =
                                                    result + item.innerText;
                                        });
                                        return result;
                                    },
                                },
                            },
                        },
                    ],
                },
            ],
            drawCallback: this.updateTotals.bind(this),
            initComplete: () => {
                // Ya no es necesario mover filtros
            },
            footerCallback: function (row, data, start, end, display) {
                // ... tu lógica de totales si aplica ...
            },
        });

        // Redibujar tabla al cambiar filtros
        $(document).on("change", "#filter_start_date, #filter_end_date", () => {
            this.datatable.ajax.reload();
        });
    }

    updateTotals() {
        if (!this.datatable) return;

        const data = this.datatable.rows({ search: "applied" }).data();
        let pendienteTotals = {};

        data.each((item) => {
            // Usa el campo de moneda según tu estructura de datos
            const currencyName = item.currency_name || (item.currency && item.currency.name) || "Desconocida";
            const pendiente = parseFloat(item.pendiente) || 0;
            if (!pendienteTotals[currencyName]) {
                pendienteTotals[currencyName] = 0;
            }
            pendienteTotals[currencyName] += pendiente;
        });

        // Actualiza los elementos del footer por moneda
        for (const currency in pendienteTotals) {
            const el = document.getElementById(`totalPendiente${currency.replace(/\s/g, "")}`);
            if (el) {
                el.textContent = this.numberFormat.format(pendienteTotals[currency]);
            }
        }
    }

    setupCustomFilters() {
        const self = this; // Guarda referencia a la instancia
        $.ajax({
            url: "/reportes/eventos", // Cambia esta URL por la de tu API de eventos
            method: "GET",
            success: (eventos) => {
                let options = '<option value="">Todos los eventos</option>';
                eventos.forEach(ev => {
                    options += `<option value="${ev.id}">${ev.name} - ${ev.year}</option>`;
                });

                const filtersHtml = `
                    <div class="custom-filters d-flex align-items-end gap-2 ms-2 ">
                        <div class="form-floating form-floating-outline">
                            <select id="filter_event" class="form-select form-select-sm" style="width: 200px;">
                                ${options}
                            </select>
                            <label for="filter_event" class="small" style="font-size:12px;">Evento</label>
                        </div>
                        <div>
                            <button id="clear_filters" class="btn btn-outline-secondary btn-sm" type="button" title="Limpiar filtros">
                                <i class="ri-refresh-line"></i>
                            </button>
                        </div>
                    </div>
                `;

                setTimeout(() => {
                    $(".dataTables_filter")
                        .addClass("d-flex align-items-end gap-2 mb-2")
                        .append(filtersHtml);

                    // Filtro por evento
                    $("#filter_event").on("change", function () {
                        $(self.tableSelector).DataTable().ajax.reload();
                    });

                    // Botón limpiar filtros
                    $("#clear_filters").on("click", function () {
                        $("#filter_event").val("");
                        $(self.tableSelector).DataTable().ajax.reload();
                    });
                }, 300);
            }
        });
    }
}

// Inicialización cuando el documento está listo
$(function () {
    window.AccountReceivableReportsManager = new AccountReceivableReportsManager();
});
