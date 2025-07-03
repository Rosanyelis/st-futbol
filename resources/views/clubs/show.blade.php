@extends('layouts.app')
@section('title', 'Clubes - Ver')
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
                    <h5 class="mb-0">Detalle del Club</h5>
                    <a href="{{ route('club.index') }}" class="btn btn-sm btn-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Regresar
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Logo del Club -->
                        <div class="col-12 mb-4">
                            <div class="d-flex align-items-start align-items-sm-center gap-4">
                                @if($club->logo === null)
                                    <img src="{{ asset('assets/img/avatars/1.png') }}" alt="club-logo" class="d-block w-px-200 h-px-200 rounded-4" id="uploadedLogo">
                                @else
                                    <img src="{{ asset('storage/' . $club->logo) }}" alt="club-logo" class="d-block w-px-200 h-px-200 rounded-4" id="uploadedLogo">
                                @endif
                            </div>
                        </div>

                        <!-- Información Básica -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nombre del Club</label>
                            <input type="text" class="form-control" value="{{ $club->name }}" readonly>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Evento</label>
                            <input type="text" class="form-control" value="{{ $club->event->name ?? '' }}" readonly>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Moneda</label>
                            <input type="text" class="form-control" value="{{ $club->currency->name ?? '' }}" readonly>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">CUIT</label>
                            <input type="text" class="form-control" value="{{ $club->cuit }}" readonly>
                        </div>

                        <!-- Información de Contacto -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Responsable</label>
                            <input type="text" class="form-control" value="{{ $club->responsible }}" readonly>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Cel. de contacto</label>
                            <input type="text" class="form-control" value="{{ $club->phone }}" readonly>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Email</label>
                            <input type="text" class="form-control" value="{{ $club->email }}" readonly>
                        </div>

                        <!-- Ubicación -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label">País</label>
                            <input type="text" class="form-control" value="{{ $club->country->name ?? '' }}" readonly>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Provincia</label>
                            <input type="text" class="form-control" value="{{ $club->province->name ?? '' }}" readonly>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Ciudad</label>
                            <input type="text" class="form-control" value="{{ $club->city->name ?? '' }}" readonly>
                        </div>

                        <!-- Hospedaje -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label">¿Paga hospedaje?</label>
                            <input type="text" class="form-control" value="{{ $club->has_accommodation ? 'Sí' : 'No' }}" readonly>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nombre del Hotel</label>
                            <input type="text" class="form-control" value="{{ $club->supplier->name ?? '' }}" readonly>
                        </div>
                    </div>

                    <!-- Tabla de Totales -->
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
                                <td><input type="text" class="form-control" value="{{ number_format($club->players_quantity, 0, ',') }}" readonly></td>
                                <td><input type="text" class="form-control" value="{{ number_format($club->player_price, 0, ',') }}" readonly></td>
                                <td><input type="text" class="form-control" value="{{  number_format($club->total_players, 0, ',')  }}" readonly></td>
                            </tr>
                            <tr>
                                <td>Profesores</td>
                                <td><input type="text" class="form-control" value="{{  number_format($club->teachers_quantity, 0, ',')  }}" readonly></td>
                                <td><input type="text" class="form-control" value="{{  number_format($club->teacher_price, 0, ',')  }}" readonly></td>
                                <td><input type="text" class="form-control" value="{{  number_format($club->total_teachers, 0, ',')  }}" readonly></td>
                            </tr>
                            <tr>
                                <td>Acompañantes</td>
                                <td><input type="text" class="form-control" value="{{  number_format($club->companions_quantity, 0, ',')  }}" readonly></td>
                                <td><input type="text" class="form-control" value="{{  number_format($club->companion_price, 0, ',')  }}" readonly></td>
                                <td><input type="text" class="form-control" value="{{  number_format($club->total_companions, 0, ',')  }}" readonly></td>
                            </tr>
                            <tr>
                                <td>Choferes</td>
                                <td><input type="text" class="form-control" value="{{  number_format($club->drivers_quantity, 0, ',')  }}" readonly></td>
                                <td><input type="text" class="form-control" value="{{  number_format($club->driver_price, 0, ',')  }}" readonly></td>
                                <td><input type="text" class="form-control" value="{{  number_format($club->total_drivers, 0, ',')  }}" readonly></td>
                            </tr>
                            <tr>
                                <td>Liberados</td>
                                <td><input type="text" class="form-control" value="{{  number_format($club->liberated_quantity, 0, ',')  }}" readonly></td>
                                <td><input type="text" class="form-control" value="{{  number_format($club->liberated_price, 0, ',')  }}" readonly></td>
                                <td><input type="text" class="form-control" value="{{  number_format($club->total_liberated, 0, ',')  }}" readonly></td>
                            </tr>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th class="text-end fs-5 fw-bold">Total Personas</th>
                                <td><input type="text" class="form-control" value="{{  number_format($club->total_people, 0, ',')  }}" readonly></td>
                                <th class="text-end fs-5 fw-bold">Total General</th>
                                <td><input type="text" class="form-control" value="{{  number_format($club->total_amount, 0, ',')  }}" readonly></td>
                            </tr>
                        </tfoot>
                    </table>

                    <!-- Pagos realizados: -->
                    <div class="mt-4">
                        <h5>Pagos Realizados</h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Monto</th>
                                    <th>Método de pago</th>
                                    <th>Titular</th>
                                    <th>Entidad</th>
                                    <th>Tipo de cuenta</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($club->payments as $payment)
                                    <tr>
                                        <td>{{ $payment->date }}</td>
                                        <td>{{ number_format($payment->amount, 0, '.', ',') }} {{ $payment->methodPayment->currency->name }}</td>
                                        <td>{{ $payment->methodPayment->categoryMethodPayment->name }}</td>
                                        <td>{{ $payment->methodPayment->account_holder }}</td>
                                        <td>{{ $payment->methodPayment->entity->name }} </td>
                                        <td>{{ $payment->methodPayment->type_account }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
