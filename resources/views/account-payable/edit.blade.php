@extends('layouts.app')
@section('title', 'Cuentas por Pagar - Editar')
@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/typeahead-js/typeahead.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css') }}" />
@endsection
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Editar Cuenta por Pagar</h5>
                        <a href="{{ route('account-payable.index') }}" class="btn btn-sm btn-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Regresar
                        </a>
                    </div>
                    <div class="card-body">
                        <form id="formAccountPayable" class="needs-validation" action="{{ route('account-payable.update', $accountPayable->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="row g-3">
                                <div class="col-md-3 mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <select class="form-select select2 @error('event_id') is-invalid @enderror" 
                                        id="event_id" name="event_id" required>
                                            <option value="">Seleccione un evento</option>
                                            @foreach($events as $event)
                                                <option value="{{ $event->id }}" {{ old('event_id', $accountPayable->event_id) == $event->id ? 'selected' : '' }}>
                                                    {{ $event->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="event_id">Evento *</label>
                                    </div>
                                    @error('event_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <select class="form-select select2 @error('supplier_id') is-invalid @enderror" 
                                        id="supplier_id" name="supplier_id" required>
                                            <option value="">Seleccione un proveedor</option>
                                            @if($accountPayable->supplier)
                                                <option value="{{ $accountPayable->supplier->id }}" selected>
                                                    {{ $accountPayable->supplier->name }}
                                                </option>
                                            @endif
                                        </select>
                                        <label for="supplier_id">Proveedor *</label>
                                    </div>
                                    @error('supplier_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <select class="form-select select2 @error('currency_id') is-invalid @enderror" 
                                        id="currency_id" name="currency_id" required>
                                            <option value="">Seleccione una moneda</option>
                                            @foreach($currencies as $currency)
                                                <option value="{{ $currency->id }}" {{ old('currency_id', $accountPayable->currency_id) == $currency->id ? 'selected' : '' }}>
                                                    {{ $currency->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="currency_id">Moneda *</label>
                                    </div>
                                    @error('currency_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control @error('amount') is-invalid @enderror" 
                                        id="amount" name="amount" placeholder="0" value="{{ old('amount', number_format($accountPayable->amount, 0, ',', '.')) }}" required>
                                        <label for="amount">Monto Adeudado *</label>
                                    </div>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                        id="description" name="description" rows="3">{{ old('description', $accountPayable->description) }}</textarea>
                                        <label for="description">Descripción (Opcional)</label>
                                    </div>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row justify-content-end mt-3">
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ri-save-2-line me-1"></i>
                                        Actualizar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/typeahead-js/typeahead.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>
    <!-- Page JS -->
    <script src="{{ asset('assets/js/forms-selects.js') }}"></script>
    <script src="{{ asset('pagesjs/account-payable/account-payable-create.js?v=1.0') }}"></script>
@endsection
