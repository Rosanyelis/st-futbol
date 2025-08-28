@extends('layouts.app')
@section('title', 'Cuentas por Cobrar - Editar')
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
                        <h5 class="mb-0">Editar Cuenta por Cobrar</h5>
                        <a href="{{ route('account-receivable.index') }}" class="btn btn-sm btn-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Regresar
                        </a>
                    </div>
                    <div class="card-body">
                        <form id="formClub" class="needs-validation" action="{{ route('account-receivable.update', $accountReceivable->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="row g-3">
                                <div class="col-md-3 mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <select class="form-select select2 @error('event_id') is-invalid @enderror" 
                                        id="event_id" name="event_id" required>
                                            <option value="">Seleccione un evento</option>
                                            @foreach($events as $event)
                                                <option value="{{ $event->id }}" {{ old('event_id', $accountReceivable->event_id) == $event->id ? 'selected' : '' }}>
                                                    {{ $event->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label  for="event_id">Evento *</label>
                                    </div>
                                    @error('event_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <select class="form-select select2 @error('club_id') is-invalid @enderror" 
                                        id="club_id" name="club_id" required>
                                            <option value="">Seleccione un club</option>
                                            @if($accountReceivable->club)
                                                <option value="{{ $accountReceivable->club->id }}" selected>
                                                    {{ $accountReceivable->club->name }}
                                                </option>
                                            @endif
                                        </select>
                                        <label  for="club_id">Club *</label>
                                    </div>
                                    @error('club_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <select class="form-select select2 @error('currency_id') is-invalid @enderror" 
                                        id="currency_id" name="currency_id" required>
                                            <option value="">Seleccione una moneda</option>
                                            @foreach($currencies as $currency)
                                                <option value="{{ $currency->id }}" {{ old('currency_id', $accountReceivable->currency_id) == $currency->id ? 'selected' : '' }}>
                                                    {{ $currency->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label  for="currency_id">Moneda *</label>
                                    </div>
                                    @error('currency_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <!-- Hospedaje -->
                                <div class="col-md-3 mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <select class="form-select @error('has_accommodation') is-invalid @enderror" 
                                        id="has_accommodation" name="has_accommodation" required>
                                            <option value="1" {{ old('has_accommodation', $accountReceivable->has_accommodation) == '1' ? 'selected' : '' }}>Sí</option>
                                            <option value="0" {{ old('has_accommodation', $accountReceivable->has_accommodation) == '0' ? 'selected' : '' }}>No</option>
                                        </select>
                                        <label  for="has_accommodation">¿Paga hospedaje? *</label>
                                    </div>
                                    @error('has_accommodation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3" id="supplierField" style="display: {{ $accountReceivable->has_accommodation ? 'block' : 'none' }};">
                                    <div class="form-floating form-floating-outline">
                                        <select class="form-select select2 @error('supplier_id') is-invalid @enderror" 
                                        id="supplier_id" name="supplier_id">
                                            <option value="">Seleccione un hotel</option>
                                            @if($accountReceivable->supplier)
                                                <option value="{{ $accountReceivable->supplier->id }}" selected>
                                                    {{ $accountReceivable->supplier->name }}
                                                </option>
                                            @endif
                                        </select>
                                        <label  for="supplier_id">Nombre del Hotel</label>
                                    </div>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                        id="description" name="description" rows="3">{{ old('description', $accountReceivable->description) }}</textarea>
                                        <label for="description">Observaciones</label>
                                    </div>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <input type="hidden" id="total_club" name="total_amount" value="">
                            
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
                                            <td><input type="text" id="players_quantity" name="players_quantity" class="form-control" min="0" value="{{ old('players_quantity', number_format($accountReceivable->players_quantity, 0, ',', '.')) }}"></td>
                                            <td><input type="text" id="player_price" name="player_price" class="form-control" min="0" step="0.01" value="{{ old('player_price', number_format($accountReceivable->player_price, 2, ',', '.')) }}"></td>
                                            <td><input type="text" id="totalPlayers" name="total_players" class="form-control" min="0" step="0.01" value="{{ old('total_players', number_format($accountReceivable->total_players, 2, ',', '.')) }}" readonly></td>
                                        </tr>
                                        <tr>
                                            <td>Profesores</td>
                                            <td><input type="text" id="teachers_quantity" name="teachers_quantity" class="form-control" min="0" value="{{ old('teachers_quantity', number_format($accountReceivable->teachers_quantity, 0, ',', '.')) }}"></td>
                                            <td><input type="text" id="teacher_price" name="teacher_price" class="form-control" min="0" step="0.01" value="{{ old('teacher_price', number_format($accountReceivable->teacher_price, 2, ',', '.')) }}"></td>
                                            <td><input type="text" id="totalTeachers" name="total_teachers" class="form-control numeral-mask" min="0" step="0.01" value="{{ old('total_teachers', number_format($accountReceivable->total_teachers, 2, ',', '.')) }}" readonly></td>
                                        </tr>
                                        <tr>
                                            <td>Acompañantes</td>
                                            <td><input type="text" id="companions_quantity" name="companions_quantity" class="form-control" min="0" value="{{ old('companions_quantity', number_format($accountReceivable->companions_quantity, 0, ',', '.')) }}"></td>
                                            <td><input type="text" id="companion_price" name="companion_price" class="form-control" min="0" step="0.01" value="{{ old('companion_price', number_format($accountReceivable->companion_price, 2, ',', '.')) }}"></td>
                                            <td><input type="text" id="totalCompanions" name="total_companions" class="form-control" min="0" step="0.01" value="{{ old('total_companions', number_format($accountReceivable->total_companions, 2, ',', '.')) }}" readonly></td>
                                        </tr>
                                        <tr>
                                            <td>Choferes</td>
                                            <td><input type="text" id="drivers_quantity" name="drivers_quantity" class="form-control" min="0" value="{{ old('drivers_quantity', number_format($accountReceivable->drivers_quantity, 0, ',', '.')) }}"></td>
                                            <td><input type="text" id="driver_price" name="driver_price" class="form-control" min="0" step="0.01" value="{{ old('driver_price', number_format($accountReceivable->driver_price, 2, ',', '.')) }}"></td>
                                            <td><input type="text" id="totalDrivers" name="total_drivers" class="form-control" min="0" step="0.01" value="{{ old('total_drivers', number_format($accountReceivable->total_drivers, 2, ',', '.')) }}" readonly></td>
                                        </tr>
                                        <tr>
                                            <td>Liberados</td>
                                            <td><input type="text" id="liberated_quantity" name="liberated_quantity" class="form-control" min="0" value="{{ old('liberated_quantity', number_format($accountReceivable->liberated_quantity, 0, ',', '.')) }}"></td>
                                            <td><input type="text" id="liberated_price" name="liberated_price" class="form-control" min="0" step="0.01" value="{{ old('liberated_price', number_format($accountReceivable->liberated_price ?? 0, 2, ',', '.')) }}"></td>
                                            <td><input type="text" id="totalLiberated" name="total_liberated" class="form-control" min="0" step="0.01" value="{{ old('total_liberated', number_format($accountReceivable->total_liberated ?? 0, 2, ',', '.')) }}" readonly></td>
                                        </tr>

                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-end">Total Personas</th>
                                            <th><input type="text" id="totalPeople" name="total_people" class="form-control" min="0" step="0.01" value="{{ old('total_people', number_format($accountReceivable->total_people, 0, ',', '.')) }}" readonly></th>
                                        </tr>
                                        <tr>
                                            <th colspan="3" class="text-end">Total General</th>
                                            <th><input type="text" id="grandTotal" name="total_amount" class="form-control" min="0" step="0.01" value="{{ old('total_amount', number_format($accountReceivable->total_amount, 2, ',', '.')) }}" readonly></th>
                                        </tr>
                                    </tfoot>
                                </table>
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
    <script src="{{ asset('pagesjs/account-receivable/account-receivable-create.js?v=1.0') }}"></script>
    
@endsection
