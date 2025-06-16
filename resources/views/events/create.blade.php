@extends('layouts.app')
@section('title', 'Eventos - Crear')
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
                        <h5 class="mb-0">Crear Evento</h5>

                        <a href="{{ route('event.index') }}" class="btn btn-sm btn-secondary"
                        ><i class="ri-arrow-left-line me-1"></i> Regresar</a>
                    </div>


                    <div class="card-body">
                        <form id="formTask" class="needs-validation" action="{{ route('event.store') }}" method="POST"
                        enctype="multipart/form-data"
                        >
                            @csrf
                            <div class="row gy-5">
                                <div class="d-flex align-items-start align-items-sm-center gap-6">
                                    <img src="{{ asset('assets/img/avatars/2.png') }}" alt="user-avatar" class="d-block w-px-200 h-px-200 rounded-4" id="uploadedAvatar">
                                    <div class="button-wrapper">
                                        <label for="upload" class="btn btn-primary me-3 mb-4 waves-effect waves-light" tabindex="0">
                                            <span class="d-none d-sm-block">Cargar Imagen de Evento</span>
                                            <i class="icon-base ri ri-upload-2-line d-block d-sm-none"></i>
                                            <input type="file" id="upload" class="account-file-input" name="url_images" hidden="" accept="image/png, image/jpeg, image/jpg">
                                        </label>
                                        <button type="button" class="btn btn-outline-danger account-image-reset mb-4 waves-effect">
                                            <i class="icon-base ri ri-refresh-line d-block d-sm-none"></i>
                                            <span class="d-none d-sm-block">Reset</span>
                                        </button>

                                        <div>formatos permitidos: JPG, JPEG, PNG. Peso máximo: 800K</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-floating form-floating-outline">
                                        <input
                                            type="text"
                                            class="form-control form-control-sm {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                            id="name"
                                            name="name"
                                            placeholder="Ingrese nombre de Evento"
                                            value="{{ old('name') }}"
                                            autofocus>
                                        <label for="name">Evento</label>

                                        @if($errors->has('name'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('name') }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-floating form-floating-outline">
                                        <input
                                            type="date"
                                            class="form-control form-control-sm {{ $errors->has('start_date') ? 'is-invalid' : '' }}"
                                            id="start_date"
                                            name="start_date"
                                            placeholder="Ingrese fecha de inicio"
                                            value="{{ old('start_date') }}">
                                        <label for="date">Fecha de Inicio</label>

                                        @if($errors->has('date'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('date') }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-floating form-floating-outline">
                                        <input
                                            type="date"
                                            class="form-control form-control-sm {{ $errors->has('end_date') ? 'is-invalid' : '' }}"
                                            id="end_date"
                                            name="end_date"
                                            placeholder="Ingrese fecha de fin"
                                            value="{{ old('end_date') }}">
                                        <label for="date">Fecha de Fin</label>

                                        @if($errors->has('date'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('date') }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-floating form-floating-outline">
                                        <input
                                            type="number"
                                            class="form-control form-control-sm {{ $errors->has('year') ? 'is-invalid' : '' }}"
                                            id="year"
                                            name="year"
                                            placeholder="Ingrese año"
                                            min="1900"
                                            max="2100"
                                            value="{{ old('year') }}">
                                        <label for="year">Año</label>

                                        @if($errors->has('year'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('year') }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="row justify-content-end mt-3">
                                <div class="mb-3 col-md-1">
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
    <script src="{{ asset('assets/js/pages-account-settings-account.js') }}"></script>
    <script src="{{ asset('assets/js/forms-selects.js') }}"></script>
@endsection
