@extends('layouts.app')
@section('title', 'Gastos - Crear')
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
                        <h5 class="mb-0">Crear Gasto</h5>

                        <a href="{{ route('expense.index') }}" class="btn btn-sm btn-secondary"
                        ><i class="ri-arrow-left-line me-1"></i> Regresar</a>
                    </div>


                    <div class="card-body">
                        <form id="formTask" class="needs-validation" action="{{ route('expense.store') }}" method="POST">
                            @csrf
                            <div class="row gy-5">
                                
                                <div class="col-md-4 ">
                                    <div class="form-floating form-floating-outline">
                                        <select class="form-select @error('category_expense_id') is-invalid @enderror select2" 
                                        id="category_expense_id" name="category_expense_id" required>
                                            <option value="">Seleccione una categoria</option>
                                            @foreach($categoryExpenses as $categoryExpense)
                                                <option value="{{ $categoryExpense->id }}" {{ old('category_expense_id') == $categoryExpense->id ? 'selected' : '' }}>
                                                    {{ $categoryExpense->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="category_expense_id">Categoria *</label>
                                        @error('category_expense_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4 ">
                                    <div class="form-floating form-floating-outline">
                                        <select class="form-select @error('subcategory_expense_id') is-invalid @enderror select2" 
                                        id="subcategory_expense_id" name="subcategory_expense_id" required>
                                            <option value="">Seleccione una Subcategoria</option>
                                        </select>
                                        <label for="subcategory_expense_id">Subcategoria *</label>
                                        @error('subcategory_expense_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
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
    <script src="{{ asset('pagesjs/expense/create.js') }}"></script>
@endsection
