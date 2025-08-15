@extends('layouts.app')
@section('title', 'Clubes - Editar')
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
                        <h5 class="mb-0">Editar Club</h5>
                        <a href="{{ route('club.index') }}" class="btn btn-sm btn-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Regresar
                        </a>
                    </div>

                    <div class="card-body">
                        <form id="formClub" class="needs-validation" action="{{ route('club.update', $club->id) }}" method="POST" enctype="multipart/form-data">
                            @method('PUT')
                            @csrf
                            <div class="row g-3">
                                <!-- Logo del Club -->
                                <div class="col-md-3">
                                    <div class="d-flex flex-column align-items-center gap-4">
                                        <img src="{{ asset('storage/'.$club->logo) }}" alt="club-logo" class="d-block w-px-200 h-px-200 rounded-4" id="uploadedLogo">
                                        <div class="button-wrapper text-center">
                                            <label for="upload" class="btn btn-primary me-3 mb-4 waves-effect waves-light" tabindex="0">
                                                <span class="d-none d-sm-block">Cargar logo</span>
                                                <i class="icon-base ri ri-upload-2-line d-block d-sm-none"></i>
                                                <input type="file" id="upload" class="account-file-input" name="logo" hidden accept="image/png, image/jpeg, image/jpg">
                                            </label>
                                            <button type="button" class="btn btn-outline-danger account-image-reset mb-4 waves-effect">
                                                <i class="icon-base ri ri-refresh-line d-block d-sm-none"></i>
                                                <span class="d-none d-sm-block">Reset</span>
                                            </button>
                                            <div>Formatos permitidos: JPG, JPEG, PNG. Peso máximo: 2MB</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Información Básica -->
                                <div class="col-md-9">
                                    <div class="row g-3">
                                        <div class="col-md-4 mb-3">
                                            <div class="form-floating form-floating-outline">
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                id="name" name="name" value="{{ old('name', $club->name) }}" required>
                                                <label for="name">Nombre del Club *</label>
                                                @if($errors->has('name'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('name') }}
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                
                                        <div class="col-md-4 mb-3">
                                            <div class="form-floating form-floating-outline">
                                                <input type="text" class="form-control @error('cuit') is-invalid @enderror" id="cuit" name="cuit" value="{{ old('cuit', $club->cuit) }}">
                                                <label for="cuit">CUIT</label>
                                                @if($errors->has('cuit'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('cuit') }}
                                                </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Información de Contacto -->
                                        <div class="col-md-4 mb-3">
                                            <div class="form-floating form-floating-outline">
                                                <input type="text" class="form-control @error('responsible') is-invalid @enderror" id="responsible" name="responsible" value="{{ old('responsible', $club->responsible) }}" required>
                                                <label for="responsible">Responsable *</label>
                                                @if($errors->has('responsible'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('responsible') }}
                                                </div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <div class="form-floating form-floating-outline">
                                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $club->phone) }}" required>
                                                <label for="phone">Cel. de contacto *</label>
                                                @if($errors->has('phone'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('phone') }}
                                                </div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <div class="form-floating form-floating-outline">
                                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $club->email) }}" required>
                                                <label for="email">Email *</label>
                                                @if($errors->has('email'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('email') }}
                                                </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Ubicación -->
                                        <div class="col-md-4 mb-3">
                                            <div class="form-floating form-floating-outline">
                                                <select class="form-select select2 @error('country') is-invalid @enderror" id="country"
                                                    name="country_id" required>
                                                    <option value="">Seleccione un país</option>
                                                    @foreach($countries as $country)
                                                        <option value="{{ $country->id }}" {{ old('country_id', $club->country_id) == $country->id ? 'selected' : '' }}>
                                                            {{ $country->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <label for="country">País</label>
                                                @if($errors->has('country'))
                                                <div class="invalid-feedback">
                                                        {{ $errors->first('country') }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <div class="form-floating form-floating-outline">
                                                <select id="province" class="form-select select2 @error('province_id') is-invalid @enderror" name="province_id" data-selected="{{ old('province_id', $club->province_id) }}">
                                                    <!-- Opciones se llenan por JS -->
                                                </select>
                                                <label for="province">Provincia</label>
                                                @if($errors->has('province'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('province') }}
                                                </div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <div class="form-floating form-floating-outline">
                                                <select id="city" class="form-select select2 @error('city_id') is-invalid @enderror" name="city_id" data-selected="{{ old('city_id', $club->city_id) }}">
                                                <!-- Opciones se llenan por JS -->
                                                </select>
                                                <label for="city">Ciudad</label>
                                                @if($errors->has('city'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('city') }}
                                                </div>  
                                                @endif
                                            </div>
                                        </div>  
                                    </div>
                                </div>
                            </div>
                            <div class="row justify-content-end mt-3">
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
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
    <script src="{{ asset('pagesjs/clubs/edit.js?v=1.0') }}"></script>
    
@endsection
