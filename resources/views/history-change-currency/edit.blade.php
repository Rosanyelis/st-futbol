@extends('layouts.app')
@section('title', 'Cambio de Monedas - Editar')
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
                        <h5 class="mb-0">Editar Cambio de Moneda</h5>
                        <a href="{{ route('history-change-currency.index') }}" class="btn btn-sm btn-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Regresar
                        </a>
                    </div>
                    <div class="card-body">
                        <form id="formChangeCurrency" class="needs-validation" action="{{ route('history-change-currency.update', $historyChangeCurrency->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="row g-3">
                                <!-- Sección Origen -->
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">Información de Origen</h6>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <select class="form-select select2 @error('currency_id') is-invalid @enderror" 
                                        id="currency_id" name="currency_id" required>
                                            <option value="">Seleccione una moneda</option>
                                            @foreach($currencies as $currency)
                                                <option value="{{ $currency->id }}" {{ old('currency_id', $historyChangeCurrency->currency_id) == $currency->id ? 'selected' : '' }}>
                                                    {{ $currency->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="currency_id">Moneda Origen *</label>
                                    </div>
                                    @error('currency_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <select class="form-select select2 @error('method_payment_id') is-invalid @enderror" 
                                        id="method_payment_id" name="method_payment_id" required>
                                            <option value="">Seleccione un método de pago</option>
                                            @if($historyChangeCurrency->methodPayment)
                                                <option value="{{ $historyChangeCurrency->methodPayment->id }}" selected>
                                                    {{ $historyChangeCurrency->methodPayment->account_holder }}
                                                </option>
                                            @endif
                                        </select>
                                        <label for="method_payment_id">Método de Pago Origen *</label>
                                    </div>
                                    @error('method_payment_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control @error('amount') is-invalid @enderror" 
                                        id="amount" name="amount" placeholder="0" value="{{ old('amount', number_format($historyChangeCurrency->amount, 2, ',', '.')) }}" required>
                                        <label for="amount">Monto a Cambiar *</label>
                                    </div>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Sección Destino -->
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">Información de Destino</h6>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <select class="form-select select2 @error('currency_receptor_id') is-invalid @enderror" 
                                        id="currency_receptor_id" name="currency_receptor_id" required>
                                            <option value="">Seleccione una moneda</option>
                                            @foreach($currencies as $currency)
                                                <option value="{{ $currency->id }}" {{ old('currency_receptor_id', $historyChangeCurrency->currency_receptor_id) == $currency->id ? 'selected' : '' }}>
                                                    {{ $currency->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="currency_receptor_id">Moneda Destino *</label>
                                    </div>
                                    @error('currency_receptor_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <select class="form-select select2 @error('method_payment_receptor_id') is-invalid @enderror" 
                                        id="method_payment_receptor_id" name="method_payment_receptor_id" required>
                                            <option value="">Seleccione un método de pago</option>
                                            @if($historyChangeCurrency->methodPaymentReceptor)
                                                <option value="{{ $historyChangeCurrency->methodPaymentReceptor->id }}" selected>
                                                    {{ $historyChangeCurrency->methodPaymentReceptor->account_holder }}
                                                </option>
                                            @endif
                                        </select>
                                        <label for="method_payment_receptor_id">Método de Pago Destino *</label>
                                    </div>
                                    @error('method_payment_receptor_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <select class="form-select select2 @error('type_operation') is-invalid @enderror" 
                                        id="type_operation" name="type_operation" required>
                                            <option value="">Seleccione un tipo de operación</option>
                                            <option value="Multiplicacion" {{ old('type_operation', $historyChangeCurrency->type_operation) == 'Multiplicacion' ? 'selected' : '' }}>Multiplicación</option>
                                            <option value="Division" {{ old('type_operation', $historyChangeCurrency->type_operation) == 'Division' ? 'selected' : '' }}>División</option>
                                        </select>
                                        <label for="type_operation">Tipo de Operación *</label>
                                    </div>
                                    @error('type_operation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control @error('exchange_rate') is-invalid @enderror" 
                                        id="exchange_rate" name="exchange_rate" placeholder="0" value="{{ old('exchange_rate', number_format($historyChangeCurrency->exchange_rate, 2, ',', '.')) }}" required>
                                        <label for="exchange_rate">Tasa de Cambio *</label>
                                    </div>
                                    @error('exchange_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Información Adicional -->
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <input type="date" class="form-control @error('date') is-invalid @enderror" 
                                        id="date" name="date" value="{{ old('date', $historyChangeCurrency->date->format('Y-m-d')) }}" required>
                                        <label for="date">Fecha *</label>
                                    </div>
                                    @error('date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control" id="amount_converted" value="{{ number_format($historyChangeCurrency->amount_converted, 2, ',', '.') }}" readonly>
                                        <label for="amount_converted">Monto Convertido</label>
                                    </div>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                        id="description" name="description" rows="3">{{ old('description', $historyChangeCurrency->description) }}</textarea>
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
    <script src="{{ asset('pagesjs/history-change-currency/history-change-currency-edit.js?v=1.0') }}"></script>
@endsection
