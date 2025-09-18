@extends('layouts.app')
@section('title', 'Detalles de Cuenta por Pagar')
 @section('css')
     <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
 @endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detalles de Cuenta por Pagar</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('account-payable.pdf', $accountPayable->id) }}" 
                           class="btn btn-danger btn-sm" target="_blank">
                            <i class="ri-file-pdf-line"></i> Generar PDF
                        </a>
                        <a href="{{ route('account-payable.index') }}" class="btn btn-secondary btn-sm">
                            <i class="ri-arrow-left-line"></i> Volver
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Información del Proveedor -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h6 class="text-primary mb-3">Información del Proveedor</h6>
                            <div class="row">
                                <div class="col-md-2">
                                    @if($accountPayable->supplier && $accountPayable->supplier->logo)
                                        <img src="{{ asset('storage/' . $accountPayable->supplier->logo) }}" 
                                             alt="Logo del proveedor" 
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
                                            <p><strong>Nombre:</strong> {{ $accountPayable->supplier->name ?? 'Proveedor no encontrado' }}</p>
                                            <p><strong>Representante:</strong> {{ $accountPayable->supplier->representant ?? 'No especificado' }}</p>
                                            <p><strong>Teléfono:</strong> {{ $accountPayable->supplier->phone ?? 'No especificado' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Email:</strong> {{ $accountPayable->supplier->email ?? 'No especificado' }}</p>
                                            <p><strong>Evento:</strong> {{ $accountPayable->event->name ?? 'Evento no encontrado' }}</p>
                                            <p><strong>Moneda:</strong> {{ $accountPayable->currency->name ?? 'Moneda no encontrada' }}</p>
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
                                            <h4>{{ number_format($accountPayable->amount, 2, ',', '.') }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h6>Pagado</h6>
                                            <h4>{{ number_format($accountPayable->getPaidAmount(), 2, ',', '.') }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body text-center">
                                            <h6>Pendiente</h6>
                                            <h4>{{ number_format($accountPayable->getPendingAmount(), 2, ',', '.') }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body text-center">
                                            <h6>% Pagado</h6>
                                            <h4>{{ $accountPayable->getPaymentPercentage() }}%</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


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
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($accountPayable->payments as $payment)
                                        <tr>
                                            <td>{{ $payment->date->format('d/m/Y') }}</td>
                                            <td class="text-end">{{ number_format($payment->amount, 2, ',', '.') }} {{ $accountPayable->currency->symbol ?? '' }}</td>
                                            <td>{{ $payment->description ?? 'Sin descripción' }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center">No hay abonos registrados</td>
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
 
         // Función para generar comprobante
         function generateReceipt(paymentId) {
             Swal.fire({
                 title: 'Generando Comprobante',
                 text: '¿Desea generar el comprobante de este pago?',
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
                         text: 'Por favor espere mientras se genera el comprobante',
                         allowOutsideClick: false,
                         customClass: {
                            confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                        buttonsStyling: false,
                         didOpen: () => {
                             Swal.showLoading();
                         }
                     });
                     
                     // Generar el comprobante PDF
                     const url = `/cuenta-por-pagar/comprobante/${paymentId}`;
                     window.open(url, '_blank');
                     
                     // Cerrar el loading y mostrar éxito
                     Swal.fire({
                         title: 'Comprobante Generado',
                         text: 'El comprobante se ha generado correctamente y se abrirá en una nueva pestaña',
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
                 text: '¿Desea generar el detalle completo de esta cuenta por pagar?',
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
                     const url = `/cuenta-por-pagar/detalle/{{ $accountPayable->id }}`;
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
