@extends('layouts.app')
@section('title', 'Detalles de Cuenta por Cobrar')
 @section('css')
     <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
 @endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detalles de Cuenta por Cobrar</h5>
                    <div>
                        <button class="btn btn-primary btn-sm me-2" onclick="generateDetail()">
                            <i class="ri-file-pdf-line"></i> Generar Detalle PDF
                        </button>
                        <a href="{{ route('account-receivable.index') }}" class="btn btn-secondary btn-sm">
                            <i class="ri-arrow-left-line"></i> Volver
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Información del Club -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h6 class="text-primary mb-3">Información del Club</h6>
                            <div class="row">
                                <div class="col-md-2">
                                    @if($accountReceivable->club)
                                        <img src="{{ asset('storage/' . $accountReceivable->club->logo) }}" 
                                             alt="Logo del club" 
                                             class="img-fluid rounded" 
                                             style="max-width: 100px; max-height: 100px;">
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                             style="width: 100px; height: 100px;">
                                            <i class="ri-building-line text-muted" style="font-size: 2rem;"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-10">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Nombre:</strong> {{ $accountReceivable->club->name ?? 'Club no encontrado' }}</p>
                                            <p><strong>Representante:</strong> {{ $accountReceivable->club->responsible ?? 'No especificado' }}</p>
                                            <p><strong>Teléfono:</strong> {{ $accountReceivable->club->phone ?? 'No especificado' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Email:</strong> {{ $accountReceivable->club->email ?? 'No especificado' }}</p>
                                            <p><strong>Evento:</strong> {{ $accountReceivable->event->name ?? 'Evento no encontrado' }}</p>
                                            <p><strong>Moneda:</strong> {{ $accountReceivable->currency->name ?? 'Moneda no encontrada' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen de Montos -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h6 class="text-primary mb-3">Resumen de Montos</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h6>Total</h6>
                                            <h4>{{ number_format($accountReceivable->total_amount, 2, ',', '.') }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h6>Pagado</h6>
                                            <h4>{{ number_format($accountReceivable->getPaidAmount(), 2, ',', '.') }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body text-center">
                                            <h6>Pendiente</h6>
                                            <h4>{{ number_format($accountReceivable->getPendingAmount(), 2, ',', '.') }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body text-center">
                                            <h6>% Pagado</h6>
                                            <h4>{{ $accountReceivable->getPaymentPercentage() }}%</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detalle de Jugadores y Costos -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h6 class="text-primary mb-3">Detalle de Jugadores y Costos</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Concepto</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-end">Precio Unitario</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Jugadores</td>
                                            <td class="text-center">{{ number_format($accountReceivable->players_quantity, 0, ',', '.') }}</td>
                                            <td class="text-end">{{ number_format($accountReceivable->player_price, 2, ',', '.') }}</td>
                                            <td class="text-end">{{ number_format($accountReceivable->total_players, 2, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Profesores</td>
                                            <td class="text-center">{{ number_format($accountReceivable->teachers_quantity, 0, ',', '.') }}</td>
                                            <td class="text-end">{{ number_format($accountReceivable->teacher_price, 2, ',', '.') }}</td>
                                            <td class="text-end">{{ number_format($accountReceivable->total_teachers, 2, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Acompañantes</td>
                                            <td class="text-center">{{ number_format($accountReceivable->companions_quantity, 0, ',', '.') }}</td>
                                            <td class="text-end">{{ number_format($accountReceivable->companion_price, 2, ',', '.') }}</td>
                                            <td class="text-end">{{ number_format($accountReceivable->total_companions, 2, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Choferes</td>
                                            <td class="text-center">{{ number_format($accountReceivable->drivers_quantity, 0, ',', '.') }}</td>
                                            <td class="text-end">{{ number_format($accountReceivable->driver_price, 2, ',', '.') }}</td>
                                            <td class="text-end">{{ number_format($accountReceivable->total_drivers, 2, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Liberados</td>
                                            <td class="text-center">{{ number_format($accountReceivable->liberated_quantity, 0, ',', '.') }}</td>
                                            <td class="text-end">{{ number_format($accountReceivable->liberated_price ?? 0, 2, ',', '.') }}</td>
                                            <td class="text-end">{{ number_format($accountReceivable->total_liberated ?? 0, 2, ',', '.') }}</td>
                                        </tr>
                                        <tr class="table-light">
                                            <td colspan="2"><strong>Total de Personas</strong></td>
                                            <td class="text-end"><strong>{{ number_format($accountReceivable->total_people, 0, ',', '.') }}</strong></td>
                                            <td class="text-end"><strong>{{ number_format($accountReceivable->total_amount, 2, ',', '.') }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Información Adicional -->
                                         @if($accountReceivable->has_accommodation && $accountReceivable->supplier)
                     <div class="row mb-4">
                         <div class="col-md-12">
                             <h6 class="text-primary mb-3">Información de Hospedaje</h6>
                             <p><strong>Hotel:</strong> {{ $accountReceivable->supplier->name ?? 'Hotel no especificado' }}</p>
                         </div>
                     </div>
                     @endif

                    @if($accountReceivable->description)
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h6 class="text-primary mb-3">Observaciones</h6>
                            <p>{{ $accountReceivable->description }}</p>
                        </div>
                    </div>
                    @endif

                                         <!-- Listado de Abonos -->
                     <div class="row">
                         <div class="col-md-12">
                             <h6 class="text-primary mb-3">Historial de Abonos</h6>
                             <div class="table-responsive">
                                 <table class="table table-bordered">
                                     <thead class="table-light">
                                         <tr>
                                             <th>Fecha</th>
                                             <th class="text-end">Monto</th>
                                             <th>Descripción</th>
                                             <th class="text-center">Acciones</th>
                                         </tr>
                                     </thead>
                                     <tbody>
                                         @forelse($accountReceivable->payments as $payment)
                                         <tr>
                                             <td>{{ $payment->date->format('d/m/Y') }}</td>
                                             <td class="text-end">{{ number_format($payment->amount, 2, ',', '.') }}</td>
                                             <td>{{ $payment->description ?? 'Sin descripción' }}</td>
                                             <td class="text-center">
                                                 <button class="btn btn-sm btn-primary" onclick="generateReceipt({{ $payment->id }})">
                                                     <i class="ri-printer-line"></i> Recibo
                                                 </button>
                                             </td>
                                         </tr>
                                         @empty
                                         <tr>
                                             <td colspan="3" class="text-center">No hay abonos registrados</td>
                                         </tr>
                                         @endforelse
                                     </tbody>
                                 </table>
                             </div>
                         </div>
                     </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

 @section('scripts')
     <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
     <script>
         'use strict';
 
         // Función para generar recibo
         function generateReceipt(paymentId) {
             Swal.fire({
                 title: 'Generando Recibo',
                 text: '¿Desea generar el recibo de este pago?',
                 icon: 'question',
                 showCancelButton: true,
                 confirmButtonText: 'Sí, Generar',
                 cancelButtonText: 'Cancelar',
                 customClass: {
                    confirmButton: 'btn btn-primary waves-effect waves-light',
                    cancelButton: 'btn btn-secondary waves-effect waves-light'
                    },
                buttonsStyling: false
             }).then((result) => {
                 if (result.isConfirmed) {
                     // Mostrar loading
                     Swal.fire({
                         title: 'Generando PDF...',
                         text: 'Por favor espere mientras se genera el recibo',
                         allowOutsideClick: false,
                         customClass: {
                            confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                        buttonsStyling: false,
                         didOpen: () => {
                             Swal.showLoading();
                         }
                     });
                     
                     // Generar el recibo PDF
                     const url = `/cuenta-por-cobrar/recibo/${paymentId}`;
                     window.open(url, '_blank');
                     
                     // Cerrar el loading y mostrar éxito
                     Swal.fire({
                         title: 'Recibo Generado',
                         text: 'El recibo se ha generado correctamente y se abrirá en una nueva pestaña',
                         icon: 'success',
                         customClass: {
                            confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                        buttonsStyling: false
                     });
                 }
             });
         }

         // Función para generar detalle completo
         function generateDetail() {
             Swal.fire({
                 title: 'Generando Detalle',
                 text: '¿Desea generar el detalle completo de esta cuenta por cobrar?',
                 icon: 'question',
                 showCancelButton: true,
                 confirmButtonText: 'Sí, Generar',
                 cancelButtonText: 'Cancelar',
                 customClass: {
                    confirmButton: 'btn btn-primary waves-effect waves-light',
                    cancelButton: 'btn btn-secondary waves-effect waves-light'
                    },
                buttonsStyling: false
             }).then((result) => {
                 if (result.isConfirmed) {
                     // Mostrar loading
                     Swal.fire({
                         title: 'Generando PDF...',
                         text: 'Por favor espere mientras se genera el detalle completo',
                         allowOutsideClick: false,
                         customClass: {
                            confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                        buttonsStyling: false,
                         didOpen: () => {
                             Swal.showLoading();
                         }
                     });
                     
                     // Generar el detalle PDF
                     const url = `/cuenta-por-cobrar/detalle/{{ $accountReceivable->id }}`;
                     window.open(url, '_blank');
                     
                     // Cerrar el loading y mostrar éxito
                     Swal.fire({
                         title: 'Detalle Generado',
                         text: 'El detalle completo se ha generado correctamente y se abrirá en una nueva pestaña',
                         icon: 'success',
                         customClass: {
                            confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                        buttonsStyling: false
                     });
                 }
             });
         }
     </script>
 @endsection
