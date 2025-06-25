@extends('layouts.app')
@section('title', 'Clubes - Crear')
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
                        <h5 class="mb-0">Crear Club</h5>
                        <a href="{{ route('club.index') }}" class="btn btn-sm btn-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Regresar
                        </a>
                    </div>
                    <div class="card-body">
                        <form id="formClub" class="needs-validation" action="{{ route('club.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <!-- Logo del Club -->
                                <div class="col-12 mb-4">
                                    <div class="d-flex align-items-start align-items-sm-center gap-4">
                                        <img src="{{ asset('assets/img/avatars/2.png') }}" alt="club-logo" class="d-block w-px-200 h-px-200 rounded-4" id="uploadedLogo">
                                        <div class="button-wrapper">
                                            <label for="upload" class="btn btn-primary me-3 mb-4 waves-effect waves-light" tabindex="0">
                                                <span class="d-none d-sm-block">Cargar Logo del Club</span>
                                                <i class="icon-base ri ri-upload-2-line d-block d-sm-none"></i>
                                                <input type="file" id="upload" class="account-file-input" name="logo" hidden accept="image/png, image/jpeg, image/jpg">
                                            </label>
                                            <button type="button" class="btn btn-outline-danger account-image-reset mb-4 waves-effect">
                                                <i class="icon-base ri ri-refresh-line d-block d-sm-none"></i>
                                                <span class="d-none d-sm-block">Reset</span>
                                            </button>
                                            <div>Formatos permitidos: JPG, JPEG, PNG. Peso máximo: 2MB</div>
                                            @error('logo')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Información Básica -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="name">Nombre del Club *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                    id="name" name="name" value="{{ old('name') }}" required maxlength="100">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="event_id">Evento *</label>
                                    <select class="form-select select2 @error('event_id') is-invalid @enderror" 
                                    id="event_id" name="event_id" required>
                                        <option value="">Seleccione un evento</option>
                                        @foreach($events as $event)
                                            <option value="{{ $event->id }}" {{ old('event_id') == $event->id ? 'selected' : '' }}>
                                                {{ $event->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('event_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="currency_id">Moneda *</label>
                                    <select class="form-select select2 @error('currency_id') is-invalid @enderror" 
                                    id="currency_id" name="currency_id" required>
                                        <option value="">Seleccione una moneda</option>
                                        @foreach($currencies as $currency)
                                            <option value="{{ $currency->id }}" {{ old('currency_id') == $currency->id ? 'selected' : '' }}>
                                                {{ $currency->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('currency_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="cuit">CUIT</label>
                                    <input type="text" class="form-control @error('cuit') is-invalid @enderror" 
                                    id="cuit" name="cuit" value="{{ old('cuit') }}">
                                    @error('cuit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Información de Contacto -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="responsible">Responsable *</label>
                                    <input type="text" class="form-control @error('responsible') is-invalid @enderror" 
                                    id="responsible" name="responsible" value="{{ old('responsible') }}" required>
                                    @error('responsible')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="phone">Cel. de contacto *</label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                    id="phone" name="phone" value="{{ old('phone') }}" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="email">Email *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                    id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Ubicación -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="country">País *</label>
                                    <select class="form-select select2 @error('country') is-invalid @enderror" id="country"
                                     name="country_id" required>
                                        <option value="">Seleccione un país</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->id }}" {{ old('country') == $country->id ? 'selected' : '' }}>
                                                {{ $country->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="province">Provincia</label>
                                    <select class="form-select select2 @error('province') is-invalid @enderror" 
                                    id="province" name="province_id">
                                        <option value="">Seleccione una provincia</option>
                                    </select>
                                    @error('province')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="city">Ciudad</label>
                                    <select class="form-select select2 @error('city') is-invalid @enderror" 
                                    id="city" name="city_id">
                                        <option value="">Seleccione una ciudad</option>
                                    </select>
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Hospedaje -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="has_accommodation">¿Paga hospedaje? *</label>
                                    <select class="form-select @error('has_accommodation') is-invalid @enderror" 
                                    id="has_accommodation" name="has_accommodation" required>
                                        <option value="1" {{ old('has_accommodation') == '1' ? 'selected' : '' }}>Sí</option>
                                        <option value="0" {{ old('has_accommodation') == '0' ? 'selected' : '' }}>No</option>
                                    </select>
                                    @error('has_accommodation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3" id="supplierField" style="display: none;">
                                    <label class="form-label" for="supplier_id">Nombre del Hotel</label>
                                    <select class="form-select" id="supplier_id" name="supplier_id">
                                        <option value="">Seleccione un hotel</option>
                                        @foreach($suppliers as $hotel)
                                            <option value="{{ $hotel->id }}">{{ $hotel->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <input type="hidden" id="details_club" name="details_club" value="">
                                <input type="hidden" id="total_club" name="total_amount" value="">
                            </div>
                            <table class="table table-bordered mt-4" id="tableAccommodation">
                                <thead>
                                    <tr>
                                        <th>Concepto</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unitario</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Jugadores</td>
                                        <td><input type="number" id="players_quantity" name="players_quantity" class="form-control" min="0" value="{{ old('players_quantity', 0) }}"></td>
                                        <td><input type="number" id="player_price" name="player_price" class="form-control" min="0" step="0.01" value="{{ old('player_price', 0) }}"></td>
                                        <td><input type="number" id="totalPlayers" name="total_players" class="form-control" min="0" step="0.01" value="{{ old('total_players', 0) }}" readonly></td>
                                    </tr>
                                    <tr>
                                        <td>Profesores</td>
                                        <td><input type="number" id="teachers_quantity" name="teachers_quantity" class="form-control" min="0" value="{{ old('teachers_quantity', 0) }}"></td>
                                        <td><input type="number" id="teacher_price" name="teacher_price" class="form-control" min="0" step="0.01" value="{{ old('teacher_price', 0) }}"></td>
                                        <td><input type="number" id="totalTeachers" name="total_teachers" class="form-control" min="0" step="0.01" value="{{ old('total_teachers', 0) }}" readonly></td>
                                    </tr>
                                    <tr>
                                        <td>Acompañantes</td>
                                        <td><input type="number" id="companions_quantity" name="companions_quantity" class="form-control" min="0" value="{{ old('companions_quantity', 0) }}"></td>
                                        <td><input type="number" id="companion_price" name="companion_price" class="form-control" min="0" step="0.01" value="{{ old('companion_price', 0) }}"></td>
                                        <td><input type="number" id="totalCompanions" name="total_companions" class="form-control" min="0" step="0.01" value="{{ old('total_companions', 0) }}" readonly></td>
                                    </tr>
                                    <tr>
                                        <td>Choferes</td>
                                        <td><input type="number" id="drivers_quantity" name="drivers_quantity" class="form-control" min="0" value="{{ old('drivers_quantity', 0) }}"></td>
                                        <td><input type="number" id="driver_price" name="driver_price" class="form-control" min="0" step="0.01" value="{{ old('driver_price', 0) }}"></td>
                                        <td><input type="number" id="totalDrivers" name="total_drivers" class="form-control" min="0" step="0.01" value="{{ old('total_drivers', 0) }}" readonly></td>
                                    </tr>
                                    <tr>
                                        <td>Liberados</td>
                                        <td><input type="number" id="liberated_quantity" name="liberated_quantity" class="form-control" min="0" value="{{ old('liberated_quantity', 0) }}"></td>
                                        <td><input type="number" id="liberated_price" name="liberated_price" class="form-control" min="0" step="0.01" value="{{ old('liberated_price', 0) }}" readonly></td>
                                        <td><input type="number" id="totalLiberated" name="total_liberated" class="form-control" min="0" step="0.01" value="{{ old('total_liberated', 0) }}" readonly></td>
                                    </tr>

                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Total Personas</th>
                                        <th><input type="number" id="totalPeople" name="total_people" class="form-control" min="0" step="0.01" value="{{ old('total_people', 0) }}" readonly></th>
                                    </tr>
                                    <tr>
                                        <th colspan="3" class="text-end">Total General</th>
                                        <th><input type="number" id="grandTotal" name="total_amount" class="form-control" min="0" step="0.01" value="{{ old('total_amount', 0) }}" readonly></th>
                                    </tr>
                                </tfoot>
                            </table>
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
    <script src="{{ asset('pagesjs/clubs/create.js') }}"></script>
    
@endsection
