@extends('layouts.app')
@section('title', 'Movimientos por Cuentas')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('css')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/typeahead-js/typeahead.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css') }}" />

<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />

@endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Product List Widget -->
    <div class="card mb-6 ">
        <div class="card-widget-separator-wrapper">
            <div class="card-body card-widget-separator">
                <div class="row gy-4 gy-sm-1" id="monedas">
                    @foreach ($currencies as $currency)
                    <div class="col-sm-6 col-lg-3">
                        <div class="d-flex flex-column align-items-start card-widget-1 border-end pb-4 pb-sm-0">
                            <div>
                                <p class="mb-1">{{ $currency->name }}</p>
                            </div>
                            <div>
                                <h4 id="totalIngreso{{ $currency->name }}" class="mb-1 text-success">0</h4>
                                <h4 id="totalEgreso{{ $currency->name }}" class="mb-1 text-danger">0</h4>
                            </div>
                        </div>
                        <hr class="d-none d-sm-block d-lg-none me-6" />
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Product List Table -->
    <div class="card">
        <div class="card-header header-elements border-bottom">
            <h5 class="card-title">Reporte de Movimientos de Metodos de Pago (Eventos y Negocio)</h5>
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatables-movements-statement table">
                <thead>
                     <tr>
                         <th>Fecha</th>
                         <th>Tipo de Movimiento</th>
                         <th>Fuente</th>
                         <th>Cuenta/Método de Pago</th>
                         <th>Moneda</th>
                         <th>Evento</th>
                         <th>Club</th>
                         <th>Proveedor</th>
                         <th>Ingreso</th>
                         <th>Egreso</th>
                         <th>Tipo de Ingreso</th>
                         <th>Tipo de Egreso</th>
                         <th>Descripción</th>
                     </tr>
                 </thead>
            </table>
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
<script src="{{ asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
<script src="{{ asset('pagesjs/reports/movements-statement.js?v=2.0.6') }}"></script>
@endsection
