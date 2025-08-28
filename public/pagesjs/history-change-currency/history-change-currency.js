'use strict';

document.addEventListener('DOMContentLoaded', () => {
    // Constantes y configuraciones iniciales
    const numberFormat = new Intl.NumberFormat('es-MX');
    const SELECTORS = {
        datatable: '.datatables'
    };

    // Funciones utilitarias
    const formatCurrency = data => `$ ${numberFormat.format(data)}`;
    const formatDate = data => moment(data).format('DD/MM/YYYY');

    // Configuración DataTable
    const initDataTable = () => {
        const dtElement = document.querySelector(SELECTORS.datatable);
        if (!dtElement) return null;

        return $(dtElement).DataTable({
            processing: true,
            ajax: {
                url: "/cambio-de-monedas",
            },
            type: "POST",
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>><"table-responsive"t><"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            language: {
                url: "https://cdn.datatables.net/plug-ins/2.0.8/i18n/es-ES.json",
                paginate: {
                    next: '<i class="ri-arrow-right-s-line"></i>',
                    previous: '<i class="ri-arrow-left-s-line"></i>'
                }
            },
            lengthMenu: [10, 25, 50, 75, 100],
            columns: [
                { data: 'date', render: formatDate },
                { data: 'origin_currency' },
                { data: 'origin_method' },
                { data: 'formatted_amount' },
                { data: 'formatted_exchange_rate' },
                { data: 'destination_currency' },
                { data: 'destination_method' },
                { data: 'formatted_amount_converted' },
                { data: 'description' },
                { data: 'actions', orderable: false, searchable: false }
            ],
        });
    };

    // Función para cancelar cambio de moneda
    const cancelChangeCurrency = (id) => {
        Swal.fire({
            title: '¿Está seguro?',
            text: "Esta acción cancelará el cambio de moneda y revertirá los saldos. Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loading
                Swal.fire({
                    title: 'Cancelando...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Realizar petición AJAX
                fetch(`/cambio-de-monedas/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: '¡Cancelado!',
                            text: data.message,
                            icon: 'success'
                        }).then(() => {
                            // Recargar la tabla
                            dataTable.ajax.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.message,
                            icon: 'error'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Ocurrió un error al cancelar el cambio de moneda',
                        icon: 'error'
                    });
                });
            }
        });
    };

    // Inicialización
    const dataTable = initDataTable();

    // Hacer las funciones accesibles globalmente
    window.cancelChangeCurrency = cancelChangeCurrency;
});
