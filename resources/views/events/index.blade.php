@extends('layouts.app')
@section('title', 'Eventos')
@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/typeahead-js/typeahead.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/animate-css/animate.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Ajax Sourced Server-side -->
    <div class="card">
        <div class="card-header header-elements border-bottom">
            <h5 class="mb-0 me-2">Eventos</h5>

            <div class="card-header-elements ms-auto">
                <a href="{{ route('event.create') }}" class="btn btn-sm btn-primary"
                >Crear Evento</a>
            </div>
        </div>
        <div class="card-datatable text-nowrap">
            <table class="datatables table table-sm">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Fecha de inicio</th>
                        <th>Fecha de fin</th>
                        <th>Año</th>
                        <th>Clubes</th>
                        <th>Proveedores</th>
                        <th style="width: 10px"></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!--/ Ajax Sourced Server-side -->
</div>

<!-- Modal para asignar clubs -->
<div class="modal fade" id="assignClubsModal" tabindex="-1" aria-labelledby="assignClubsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignClubsModalLabel">Asignar Clubs al Evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="assignClubsForm">
                    <input type="hidden" id="eventId" name="event_id">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select select2" id="clubSelect" name="club_id" required>
                                    <option value="">Seleccione un club...</option>
                                </select>
                                <label for="clubSelect">Seleccionar Club</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="assignClubToEvent()">Asignar Club</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para asignar proveedores -->
<div class="modal fade" id="assignSuppliersModal" tabindex="-1" aria-labelledby="assignSuppliersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignSuppliersModalLabel">Asignar Proveedores al Evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="assignSuppliersForm">
                    <input type="hidden" id="supplierEventId" name="event_id">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select select2" id="supplierSelect" name="supplier_id" required>
                                    <option value="">Seleccione un proveedor...</option>
                                </select>
                                <label for="supplierSelect">Seleccionar Proveedor</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="assignSupplierToEvent()">Asignar Proveedor</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/typeahead-js/typeahead.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <!-- Page JS -->
    <script src="{{ asset('assets/js/forms-selects.js') }}"></script>
    <script src="{{ asset('pagesjs/events/index.js?v=2.0') }}"></script>
@endsection
