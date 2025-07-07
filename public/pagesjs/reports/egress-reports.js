"use strict";

class EgressReportsManager {
    constructor() {
        this.numberFormat = new Intl.NumberFormat("es-MX", {
            minimumFractionDigits: 0,
        });
        this.tableSelector = ".datatables-history";
        this.init();
    }

    init() {
        this.initDataTable();
        this.setupCustomFilters();
    }

    initDataTable() {
        const $table = $(this.tableSelector);
        if (!$table.length) return;

        this.datatable = $table.DataTable({
            processing: true,
            ajax: {
                url: "/reportes/estado-egresos",
                data: (d) => {
                    d.start_date = $("#filter_start_date").val();
                    d.end_date = $("#filter_end_date").val();
                },
            },
            dom:
                '<"card-header d-flex border-top rounded-0 flex-wrap pb-md-0 pt-0"' +
                '<"d-flex align-items-center me-5"' +
                '<"me-3"f>' +
                '<"currency-filter me-3">' +
                '<"date-filter">' +
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
            lengthMenu: [7, 10, 20, 50, 70, 100], //for length of menu
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
                { data: "date", name: "date" },
                { data: "category_egress.name", name: "category_egress.name" },
                // Cambiado de "expense.name" a "supplier.name" para reflejar el cambio en la estructura de datos
                { data: "supplier.name", name: "supplier.name" },
                // Eliminamos la definición duplicada de data: null que estaba aquí
                { data: "currency.name", name: "currency.name" },
                { data: "amount", name: "amount" },
                {
                    data: "method_payment.category_method_payment.name",
                    name: "method_payment.category_method_payment.name",
                },
                {
                    data: "method_payment.entity.name",
                    name: "method_payment.entity.name",
                },
                {
                    data: "method_payment.account_holder",
                    name: "method_payment.account_holder",
                },
            ],
            columnDefs: [
                // Cambiado de columnsDefs a columnDefs
                {
                    targets: 2, // Columna índice 2 (la tercera columna)
                    render: function (data, type, row) {
                        // Primero verifica si existe expense.name
                        if (row.expense && row.expense.category_expense && row.expense.subcategory_expense.name) {
                            return row.expense.category_expense.name + " - " + row.expense.subcategory_expense.name;
                        }
                        // Si no, verifica supplier.name
                        if (row.supplier && row.supplier.name) {
                            return row.supplier.name;
                        }
                        // Si no hay ninguno, devuelve un valor por defecto
                        return "N/A";
                    },
                },
                {
                    targets: 4,
                    render: (data) => '$ ' + this.numberFormat.format(data),
                },
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
                                columns: [0, 1, 2, 3, 4, 5, 6],
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
                                columns: [0, 1, 2, 3, 4, 5, 6],
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
                                columns: [0, 1, 2, 3, 4, 5, 6],
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
                this.moveFilters();
            },
            footerCallback: function (row, data, start, end, display) {
                const api = this.api
                    ? this.api()
                    : new $.fn.dataTable.Api(this);

                // Usa el formateador global si existe, si no, crea uno local
                const numberFormat =
                    window.incomeReportsManager &&
                    window.incomeReportsManager.numberFormat
                        ? window.incomeReportsManager.numberFormat
                        : new Intl.NumberFormat("es-MX", {
                              minimumFractionDigits: 0,
                          });

                // 1. Sumar montos por moneda de TODOS los datos (total general)
                let totalesGenerales = {};
                let allData =
                    api.ajax.json() && api.ajax.json().data
                        ? api.ajax.json().data
                        : [];
                allData.forEach((row) => {
                    const moneda =
                        row.currency && row.currency.name
                            ? row.currency.name
                            : "Desconocida";
                    const monto = Number(row.amount) || 0;
                    if (!totalesGenerales[moneda]) totalesGenerales[moneda] = 0;
                    totalesGenerales[moneda] += monto;
                });

                // 2. Sumar montos por moneda SOLO de los datos filtrados/visibles (total filtrado)
                let totalesFiltrados = {};
                api.rows({ search: "applied" }).every(function () {
                    const rowData = this.data();
                    const moneda =
                        rowData.currency && rowData.currency.name
                            ? rowData.currency.name
                            : "Desconocida";
                    const monto = Number(rowData.amount) || 0;
                    if (!totalesFiltrados[moneda]) totalesFiltrados[moneda] = 0;
                    totalesFiltrados[moneda] += monto;
                });

                // 3. Actualiza los elementos de totales por moneda
                Object.keys(totalesGenerales).forEach((moneda) => {
                    const id = "totalIngresos" + moneda.replace(/\s/g, "");
                    const el = document.getElementById(id);
                    if (el) {
                        el.textContent = numberFormat.format(
                            totalesFiltrados[moneda] || 0
                        );
                    }
                });
            },
        });

        // Redibujar tabla al cambiar filtros
        $(document).on("change", "#filter_start_date, #filter_end_date", () => {
            this.datatable.ajax.reload();
        });
    }

    updateTotals() {
        if (!this.datatable) return; // Evita el error si la tabla aún no está lista

        const data = this.datatable.rows({ search: "applied" }).data();
        let currencyTotals = {};

        data.each((item) => {
            const currencyName = item.currency?.name;
            if (currencyName) {
                if (!currencyTotals[currencyName]) {
                    currencyTotals[currencyName] = 0;
                }
                currencyTotals[currencyName] += parseFloat(item.amount);
            }
        });

        for (const currency in currencyTotals) {
            const el = document.getElementById(
                `totalIngresos${currency.replace(/\s/g, "")}`
            );
            if (el) {
                el.textContent = this.numberFormat.format(
                    currencyTotals[currency]
                );
            }
        }
    }

    setupCustomFilters() {
        const filtersHtml = `
            <div class="custom-filters d-flex align-items-end gap-2 ms-2 ">
                <div class="form-floating form-floating-outline">
                    
                    <input type="date" id="filter_start_date" class="form-control form-control-sm" style="width: 130px;">
                    <label for="filter_start_date" class="small" style="font-size:12px;">Desde:</label>
                </div>
                <div class="form-floating form-floating-outline">
                    <input type="date" id="filter_end_date" class="form-control form-control-sm" style="width: 130px;">
                    <label for="filter_end_date" class="small" style="font-size:12px;">Hasta:</label>
                </div>
                <div>
                    <button id="clear_filters" class="btn btn-outline-secondary btn-sm" type="button" title="Limpiar filtros">
                        <i class="ri-refresh-line"></i>
                    </button>
                </div>
            </div>
        `;
        setTimeout(() => {
            // Inserta los filtros DENTRO del contenedor del buscador
            $(".dataTables_filter")
                .addClass("d-flex align-items-end gap-2 mb-2")
                .append(filtersHtml);

            // Botón limpiar filtros
            $("#clear_filters").on("click", () => {
                $("#filter_start_date").val("");
                $("#filter_end_date").val("");
                $(this.tableSelector).DataTable().ajax.reload();
            });
        }, 300);
    }

    moveFilters() {
        // Ya no es necesario mover los filtros, pues ya están alineados con el buscador
    }
}

// Inicialización cuando el documento está listo
$(function () {
    window.EgressReportsManager = new EgressReportsManager();
});
