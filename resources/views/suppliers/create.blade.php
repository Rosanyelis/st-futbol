@extends('layouts.app')
@section('title', 'Proveedores - Crear')
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
                        <h5 class="mb-0">Crear Proveedor</h5>

                        <a href="{{ route('supplier.index') }}" class="btn btn-sm btn-secondary"
                        ><i class="ri-arrow-left-line me-1"></i> Regresar</a>
                    </div>


                    <div class="card-body">
                        <form id="formTask" class="needs-validation" action="{{ route('supplier.store') }}" method="POST">
                            @csrf
                            <div class="row gy-5">
                                <div class="col-md-4">
                                    <div class="form-floating form-floating-outline">
                                        <select class="form-select @error('event_id') is-invalid @enderror select2" 
                                        id="event_id" name="event_id" required>
                                            <option value="">Seleccione un evento</option>
                                            @foreach($events as $event)
                                                <option value="{{ $event->id }}" {{ old('event_id') == $event->id ? 'selected' : '' }}>
                                                    {{ $event->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="event_id">Evento *</label>
                                        @error('event_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4 ">
                                    <div class="form-floating form-floating-outline">
                                        <select class="form-select @error('category_supplier_id') is-invalid @enderror select2" 
                                        id="category_supplier_id" name="category_supplier_id" required>
                                            <option value="">Seleccione una categoria</option>
                                            @foreach($categorySuppliers as $categorySupplier)
                                                <option value="{{ $categorySupplier->id }}" {{ old('category_supplier_id') == $categorySupplier->id ? 'selected' : '' }}>
                                                    {{ $categorySupplier->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="category_supplier_id">Categoria *</label>
                                        @error('category_supplier_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4 ">
                                    <div class="form-floating form-floating-outline">
                                        <select class="form-select @error('subcategory_supplier_id') is-invalid @enderror select2" 
                                        id="subcategory_supplier_id" name="subcategory_supplier_id" required>
                                            <option value="">Seleccione una Subcategoria</option>
                                        </select>
                                        <label for="subcategory_supplier_id">Subcategoria *</label>
                                        @error('subcategory_supplier_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating form-floating-outline">
                                        <select class="form-select @error('currency_id') is-invalid @enderror select2" 
                                        id="currency_id" name="currency_id" required>
                                            <option value="">Seleccione una moneda</option>
                                            @foreach($currencies as $currency)
                                                <option value="{{ $currency->id }}" {{ old('currency_id') == $currency->id ? 'selected' : '' }}>
                                                    {{ $currency->name }} ({{ $currency->symbol }})
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
                                            class="form-control form-control-sm {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                            id="name"
                                            name="name"
                                            placeholder="Ingrese nombre de Proveedor"
                                            value="{{ old('name') }}"
                                            autofocus>
                                        <label for="name">Proveedor</label>

                                        @if($errors->has('name'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('name') }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating form-floating-outline">
                                        <input
                                            type="text"
                                            class="form-control form-control-sm {{ $errors->has('representant') ? 'is-invalid' : '' }}"
                                            id="representant"
                                            name="representant"
                                            placeholder="Ingrese nombre de Encargado"
                                            value="{{ old('representant') }}"
                                            autofocus>
                                        <label for="representant">Encargado</label>

                                        @if($errors->has('representant'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('representant') }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating form-floating-outline">
                                        <input
                                            type="text"
                                            class="form-control form-control-sm {{ $errors->has('phone') ? 'is-invalid' : '' }}"
                                            id="phone" 
                                            name="phone"
                                            placeholder="Ingrese telefono"
                                            value="{{ old('phone') }}"
                                            autofocus>
                                        <label for="phone">Telefono</label>

                                        @if($errors->has('phone'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('phone') }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating form-floating-outline">
                                        <input
                                            type="text"
                                            class="form-control form-control-sm {{ $errors->has('amount') ? 'is-invalid' : '' }}"
                                            id="amount"
                                            name="amount"
                                            placeholder="Ingrese monto"
                                            value="{{ old('amount') }}"
                                            autofocus>
                                        <label for="amount">Monto</label>   
                                        @if($errors->has('amount'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('amount') }}
                                        </div>
                                        @endif
                                    </div>
                                </div>      
                                <div class="col-md-12">
                                    <div class="form-floating form-floating-outline">
                                        <textarea
                                            class="form-control h-px-100 {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                            id="description"
                                            name="description"
                                            placeholder="Ingrese descripcion"
                                            value="{{ old('description') }}"
                                            cols="30" rows="10"></textarea>
                                        <label for="description">Descripcion</label>
                                        @if($errors->has('description'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('description') }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="row justify-content-end mt-4">
                                <div class=" col-md-1">
                                    <button type="submit" class="btn btn-primary float-end">
                                        <i class="ri-save-2-line me-1"></i>
                                        Guardar
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
    <script src="{{ asset('pagesjs/suppliers/create.js') }}"></script>
@endsection
