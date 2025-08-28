@extends('layouts.app')

@section('title', 'Movimientos de Eventos')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Movimientos de Eventos</h5>
                    <div class="d-flex gap-2">
                        <select id="event_filter" class="form-select form-select-sm" style="width: 200px;">
                            <option value="">Todos los eventos</option>
                        </select>
                        <select id="status_filter" class="form-select form-select-sm" style="width: 150px;">
                            <option value="">Todos los estados</option>
                            <option value="Activo">Activo</option>
                            <option value="Cancelado">Cancelado</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered datatables-event-movements">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Evento</th>
                                    <th>Club/Proveedor</th>
                                    <th>Tipo</th>
                                    <th>Monto</th>
                                    <th>Moneda</th>
                                    <th>Método de Pago</th>
                                    <th>Estado</th>
                                    <th>Usuario</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los datos se cargan dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Cancelación -->
<div class="modal fade" id="cancelMovementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Cancelación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas cancelar este movimiento?</p>
                <p class="text-warning">
                    <i class="ri-information-line me-2"></i>
                    Esta acción cancelará el movimiento y restaurará el dinero en la cuenta correspondiente.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmCancelMovement">Sí, cancelar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('pagesjs/event-movements/event-movements.js') }}"></script>
<script>
$(document).ready(function() {
    // Inicializar DataTable
    const table = $('.datatables-event-movements').DataTable({
        processing: true,
        serverSide: false, // Por ahora cargamos todos los datos
        ajax: {
            url: '{{ route("event-movements.by-event", "") }}',
            data: function(d) {
                d.event_id = $('#event_filter').val();
                d.status = $('#status_filter').val();
            }
        },
        columns: [
            {data: 'date', render: function(data) {
                return moment(data).format('DD/MM/YYYY');
            }},
            {data: 'event.name', defaultContent: '-'},
            {data: 'club.name', defaultContent: '-'},
            {data: 'type', render: function(data) {
                const badgeClass = data === 'Ingreso' ? 'badge bg-success' : 'badge bg-danger';
                return `<span class="${badgeClass}">${data}</span>`;
            }},
            {data: 'amount', render: function(data) {
                return new Intl.NumberFormat('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(data);
            }},
            {data: 'currency.name', defaultContent: '-'},
            {data: 'methodPayment.account_holder', defaultContent: '-'},
            {data: 'status', render: function(data) {
                const badgeClass = data === 'Activo' ? 'badge bg-success' : 'badge bg-secondary';
                return `<span class="${badgeClass}">${data}</span>`;
            }},
            {data: 'user.name', defaultContent: '-'},
            {data: null, orderable: false, render: function(data, type, row) {
                if (row.status === 'Activo') {
                    return `<button class="btn btn-sm btn-outline-danger btn-cancel-movement" 
                                   data-movement-id="${row.id}" 
                                   title="Cancelar movimiento">
                                <i class="ri-close-line"></i>
                            </button>`;
                }
                return '<span class="text-muted">-</span>';
            }}
        ],
        language: {
            url: "https://cdn.datatables.net/plug-ins/2.0.8/i18n/es-ES.json"
        },
        order: [[0, 'desc']]
    });

    // Filtros
    $('#event_filter, #status_filter').on('change', function() {
        table.ajax.reload();
    });

    // Cargar eventos para el filtro
    $.get('/eventos', function(events) {
        const eventFilter = $('#event_filter');
        events.forEach(event => {
            eventFilter.append(`<option value="${event.id}">${event.name}</option>`);
        });
    });
});
</script>
@endpush
