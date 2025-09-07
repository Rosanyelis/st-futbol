@extends('layouts.app')
@section('title', 'Proveedores - Ver')
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
                    <h5 class="mb-0">Detalle del Proveedor</h5>
                    <a href="{{ route('supplier.index') }}" class="btn btn-sm btn-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Regresar
                    </a>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Información Básica -->
                        <div class="col-md-4">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" value="{{ $supplier->name }}" readonly>
                                <label for="name">Nombre del Proveedor</label>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" value="{{ $supplier->representant }}" readonly>
                                <label for="representant">Encargado</label>
                            </div>
                        </div>

                        <!-- Información de Contacto -->
                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" value="{{ $supplier->phone }}" readonly>
                                <label for="phone">Teléfono</label>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" value="{{ $supplier->email ?? 'No especificado' }}" readonly>
                                <label for="email">Email</label>
                            </div>
                        </div>

                        <!-- Categorías -->
                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" value="{{ $supplier->categorySupplier->name ?? '' }}" readonly>
                                <label for="category">Categoría</label>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" value="{{ $supplier->subcategorySupplier->name ?? '' }}" readonly>
                                <label for="subcategory">Subcategoría</label>
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div class="col-md-12 mb-3">
                            <div class="form-floating form-floating-outline">
                                <textarea class="form-control" rows="3" readonly>{{ $supplier->description ?? 'Sin observaciones' }}</textarea>
                                <label for="description">Observaciones</label>
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
                                <th>Cuentas por Pagar</th>
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
    <script src="{{ asset('pagesjs/suppliers/show.js?v=2.0') }}"></script>
@endsection
