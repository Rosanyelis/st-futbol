@extends('layouts.app')
@section('title', 'Clubes - Ver')
@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/typeahead-js/typeahead.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detalle del Club</h5>
                    <a href="{{ route('club.index') }}" class="btn btn-sm btn-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Regresar
                    </a>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Logo del Club -->
                        <div class="col-md-4">
                            <div class="d-flex flex-column align-items-center gap-4">
                                @if($club->logo === null)
                                    <img src="{{ asset('assets/img/avatars/1.png') }}" alt="club-logo" class="d-block w-px-200 h-px-200 rounded-4" id="uploadedLogo">
                                @else
                                    <img src="{{ asset('storage/' . $club->logo) }}" alt="club-logo" class="d-block w-px-200 h-px-200 rounded-4" id="uploadedLogo">
                                @endif
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="row g-3">
                                <!-- Información Básica -->
                                <div class="col-md-4">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control" value="{{ $club->name }}" readonly>
                                        <label for="name">Nombre del Club</label>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control" value="{{ $club->cuit }}" readonly>
                                        <label for="cuit">CUIT</label>
                                    </div>
                                </div>

                                <!-- Información de Contacto -->
                                <div class="col-md-4 mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control" value="{{ $club->responsible }}" readonly>
                                        <label for="responsible">Responsable</label>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control" value="{{ $club->phone }}" readonly>
                                        <label for="phone">Cel. de contacto</label>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control" value="{{ $club->email }}" readonly>
                                        <label for="email">Email</label>
                                    </div>
                                </div>

                                <!-- Ubicación -->
                                <div class="col-md-4 mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control" value="{{ $club->country->name ?? '' }}" readonly>
                                        <label for="country">País</label>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control" value="{{ $club->province->name ?? '' }}" readonly>
                                        <label for="province">Provincia</label>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control" value="{{ $club->city->name ?? '' }}" readonly>
                                        <label for="city">Ciudad</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Eventos Asignados -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Eventos Asignados</h5>
                </div>
                <div class="card-datatable text-nowrap">
                    <table class="datatables-events table table-sm">
                        <thead>
                            <tr>
                                <th>Evento</th>
                                <th>Fecha de Inicio</th>
                                <th>Fecha de Fin</th>
                                <th>Año</th>
                                <th>Cuentas por Cobrar</th>
                                <th style="width: 10px"></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('pagesjs/clubs/show.js?v=2.0') }}"></script>
@endsection
