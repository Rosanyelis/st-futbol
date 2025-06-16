@extends('layouts.app')
@section('title', 'Metodos de Pago - Editar')
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
                        <h5 class="mb-0">Editar Metodo de Pago</h5>

                        <a href="{{ route('method-payment.index') }}" class="btn btn-sm btn-secondary"
                        ><i class="ri-arrow-left-line me-1"></i> Regresar</a>
                    </div>


                    <div class="card-body">
                        <form id="formTask" class="needs-validation" action="{{ route('method-payment.update', $methodPayment->id) }}" method="POST">
                            @method('PUT')
                            @csrf
                            <div class="row gy-5">
                                <div class="col-md-4 ">
                                    <div class="form-floating form-floating-outline">
                                        <select class="form-select @error('category_method_payment_id') is-invalid @enderror select2" 
                                        id="category_method_payment_id" name="category_method_payment_id" required>
                                            <option value="">Seleccione una categoria</option>
                                            @foreach($categoryMethodPayments as $categoryMethodPayment)
                                                <option value="{{ $categoryMethodPayment->id }}" {{ $methodPayment->category_method_payment_id == $categoryMethodPayment->id ? 'selected' : '' }}>
                                                    {{ $categoryMethodPayment->name }}
                                                </option>
                                            @endforeach 
                                        </select>
                                        <label for="category_method_payment_id">Categoria *</label>
                                        @error('category_method_payment_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4 ">
                                    <div class="form-floating form-floating-outline">
                                        <select class="form-select @error('entity_id') is-invalid @enderror select2" 
                                        id="entity_id" name="entity_id" required>
                                            <option value="">Seleccione una Entidad</option>
                                            @foreach($entities as $entity)
                                                <option value="{{ $entity->id }}" {{ $methodPayment->entity_id == $entity->id ? 'selected' : '' }}>
                                                    {{ $entity->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="entity_id">Entidad *</label>
                                        @error('entity_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating form-floating-outline">
                                        <select class="form-select @error('currency_id') is-invalid @enderror select2" 
                                        id="currency_id" name="currency_id" required>
                                            <option value="">Seleccione una Moneda</option>
                                            @foreach($currencies as $currency)
                                                <option value="{{ $currency->id }}" {{ $methodPayment->currency_id == $currency->id ? 'selected' : '' }}>
                                                    {{ $currency->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="currency_id">Moneda *</label>
                                        @error('currency_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating form-floating-outline">
                                        <input
                                            type="text"
                                            class="form-control form-control-sm {{ $errors->has('account_holder') ? 'is-invalid' : '' }}"
                                            id="account_holder"
                                            name="account_holder"
                                            placeholder="Ingrese titular"
                                            value="{{ $methodPayment->account_holder }}"
                                            autofocus>
                                        <label for="account_holder">Titular</label>

                                        @if($errors->has('account_holder'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('account_holder') }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating form-floating-outline">
                                        <input
                                            type="text"
                                            class="form-control form-control-sm {{ $errors->has('alias') ? 'is-invalid' : '' }}"
                                            id="alias"
                                            name="alias"
                                            placeholder="Ingrese alias"
                                            value="{{ $methodPayment->alias }}"
                                            autofocus>
                                        <label for="alias">Alias</label>

                                        @if($errors->has('alias'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('alias') }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                    
                                <div class="col-md-4">
                                    <div class="form-floating form-floating-outline">
                                        <select class="form-select @error('type_account') is-invalid @enderror select2"     
                                        id="type_account" name="type_account" required>
                                            <option value="">Seleccione un tipo de cuenta</option>
                                            <option value="Propia" {{ $methodPayment->type_account == 'Propia' ? 'selected' : '' }}>Propia</option>
                                            <option value="Terceros" {{ $methodPayment->type_account == 'Terceros' ? 'selected' : '' }}>Terceros</option>
                                        </select>
                                        <label for="type_account">Tipo de cuenta</label>

                                        @if($errors->has('type_account'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('type_account') }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating form-floating-outline">
                                        <input
                                            type="text"
                                            class="form-control form-control-sm {{ $errors->has('account_number') ? 'is-invalid' : '' }}"
                                            id="account_number"
                                            name="account_number"
                                            placeholder="Ingrese numero de cuenta"
                                            value="{{ $methodPayment->account_number }}"
                                            autofocus>
                                        <label for="account_number">Numero de cuenta</label>   
                                        @if($errors->has('account_number'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('account_number') }}
                                        </div>
                                        @endif
                                    </div>
                                </div>      
                                <div class="col-md-4">
                                    <div class="form-floating form-floating-outline">
                                        <input
                                            type="text"
                                            class="form-control {{ $errors->has('cbu_cvu') ? 'is-invalid' : '' }}"
                                            id="cbu_cvu"
                                            name="cbu_cvu"
                                            placeholder="Ingrese CBU/CVU"
                                            value="{{ $methodPayment->cbu_cvu }}">
                                        <label for="cbu_cvu">CBU/CVU</label>
                                        @if($errors->has('cbu_cvu'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('cbu_cvu') }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating form-floating-outline">
                                        <input
                                            type="text"
                                            class="form-control form-control-sm {{ $errors->has('initial_balance') ? 'is-invalid' : '' }}"
                                            id="initial_balance"
                                            name="initial_balance"
                                            placeholder="Ingrese saldo inicial"
                                            value="{{ $methodPayment->initial_balance }}"
                                            autofocus>
                                        <label for="initial_balance">Saldo inicial</label>
                                        @if($errors->has('initial_balance'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('initial_balance') }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="row justify-content-end mt-4">
                                <div class=" col-md-1">
                                    <button type="submit" class="btn btn-primary float-end">
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
@endsection
