<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Cuenta por Pagar</title>
    <style type="text/css">
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        .detalle-container {
            background: #fff;
            border: 1px solid #b3d7f2;
            border-radius: 15px;
            padding: 20px;
            position: relative;
        }
        .detalle-header {
            width: 100%;
            overflow: hidden;
            border-bottom: 2px solid #b3d7f2;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .detalle-logo {
            width: 80px;
            margin-bottom: 5px;
        }
        .detalle-title {
            font-size: 1.8em;
            font-weight: bold;
            letter-spacing: 0.1em;
            color: #1b6ca8;
            margin-bottom: 5px;
            margin-top: 0;
            text-align: center;
        }
        .section-title {
            font-size: 1.2em;
            font-weight: bold;
            color: #1b6ca8;
            margin: 15px 0 10px 0;
            padding-bottom: 5px;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        .info-table th {
            background: #f8f9fa;
            padding: 8px;
            text-align: left;
            border: 1px solid #dee2e6;
            font-weight: bold;
            width: 15%;
        }
        .info-table td {
            padding: 8px;
            border: 1px solid #dee2e6;
        }
        .summary-cards {
            width: 100%;
            text-align: center;
            overflow: hidden;
        }
        .summary-card {
            float: left;
            width: 19%;
            margin: 0 1%;
            padding: 10px 10px;
            text-align: center;
            color: white;
            font-weight: bold;
            border: 1px solid #ccc;
        }
        .card-total {  
            color: #212529;
            border-color: #0056b3;
            border-radius: 10px;
        }
        .card-paid { 
            color: #212529;
            border-color: #1e7e34;
            border-radius: 10px;
        }
        .card-pending { 
            color: #212529; 
            border-color: #d39e00;
            border-radius: 10px;
        }
        .card-percentage { 
            color: #212529;
            border-color: #117a8b;
            border-radius: 10px;
        }
        .summary-card h4 {
            margin: 0 0 5px 0;
            font-size: 0.9em;
        }
        .summary-card .amount {
            font-size: 1.3em;
        }
        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }
        .payments-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .payments-table th {
            background: #f8f9fa;
            padding: 5px;
            text-align: center;
            border: 1px solid #dee2e6;
            font-weight: bold;
        }
        .payments-table td {
            padding: 5px;
            border: 1px solid #dee2e6;
            text-align: center;
        }
        .payments-table .text-end {
            text-align: right;
        }
        .payments-table .text-left {
            text-align: left;
        }
        .footer {
            margin-top: 20px;
            font-size: 10px;
            color: #888;
            text-align: center;
            border-top: 2px solid #b3d7f2;
            padding-top: 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="detalle-container">
        <!-- Encabezado -->
        <div class="detalle-header">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <th style="width: 50%; text-align: center;">
                        <img src="{{ public_path('assets/img/logo-isotipo.png') }}" alt="logo" class="detalle-logo"><br>
                    </th>
                    <th style="width: 50%; text-align: center;">
                        <div style="font-size:16px; font-weight:bold;">TORNEOS DE FUTBOL INFANTIL</div>
                        <div style="font-size:16px; font-weight:bold;">STC TORNEOS</div>
                        <div style="font-size:14px;">C.P. 1424 AOA - Ciudad de Buenos Aires</div>
                        <div style="font-size:14px;">Cel. : 54 911 4066-2544</div>
                        <div style="font-size:14px;">eventosdeportivos1977@hotmail.com</div>
                    </th>
                </tr>
            </table>
        </div>

        <!-- Información del Proveedor -->
        <h2 class="section-title">Información del Proveedor</h2>
        <table class="info-table">
            <tr>
                <th>Proveedor:</th>
                <td>{{ $accountPayable->supplier->name ?? 'Proveedor no encontrado' }}</td>
                <th>Representante:</th>
                <td>{{ $accountPayable->supplier->representant ?? 'No especificado' }}</td>
            </tr>
            <tr>
                <th>Teléfono:</th>
                <td>{{ $accountPayable->supplier->phone ?? 'No especificado' }}</td>
                <th>Email:</th>
                <td>{{ $accountPayable->supplier->email ?? 'No especificado' }}</td>
            </tr>
            <tr>
                <th>Evento:</th>
                <td>{{ $accountPayable->event->name ?? 'Evento no encontrado' }}</td>
                <th>Moneda:</th>
                <td>{{ $accountPayable->currency->name ?? 'Moneda no encontrada' }}</td>
            </tr>
            <tr>
                <th>Fecha de Creación:</th>
                <td>{{ $accountPayable->date->format('d/m/Y') }}</td>
                <th>Estado:</th>
                <td>
                    <span class="status-badge {{ $accountPayable->status === 'Completado' ? 'status-completed' : 'status-pending' }}">
                        {{ $accountPayable->status ?? 'Pendiente' }}
                    </span>
                </td>
            </tr>
        </table>

        <!-- Resumen de Montos -->
        <h2 class="section-title">Resumen de Montos</h2>
        <div class="summary-cards clearfix">
            <div class="summary-card card-total">
                <h4>Total</h4>
                <div class="amount">{{ $accountPayable->currency->symbol ?? '$' }} {{ number_format($accountPayable->amount, 2, ',', '.') }}</div>
            </div>
            <div class="summary-card card-paid">
                <h4>Pagado</h4>
                <div class="amount">{{ $accountPayable->currency->symbol ?? '$' }} {{ number_format($accountPayable->getPaidAmount(), 2, ',', '.') }}</div>
            </div>
            <div class="summary-card card-pending">
                <h4>Pendiente</h4>
                <div class="amount">{{ $accountPayable->currency->symbol ?? '$' }} {{ number_format($accountPayable->getPendingAmount(), 2, ',', '.') }}</div>
            </div>
            <div class="summary-card card-percentage">
                <h4>% Pagado</h4>
                <div class="amount">{{ $accountPayable->getPaymentPercentage() }}%</div>
            </div>
        </div>

        <!-- Descripción -->
        @if($accountPayable->description)
        <h2 class="section-title">Descripción</h2>
        <table class="info-table">
            <tr>
                <th>Descripción:</th>
                <td colspan="3">{{ $accountPayable->description }}</td>
            </tr>
        </table>
        @endif

        <!-- Historial de Abonos -->
        @if($accountPayable->payments->count() > 0)
        <h2 class="section-title">Historial de Abonos</h2>
        <table class="payments-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Monto</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($accountPayable->payments as $payment)
                <tr>
                    <td>{{ $payment->date->format('d/m/Y') }}</td>
                    <td class="text-end">{{ $accountPayable->currency->symbol ?? '$' }} {{ number_format($payment->amount, 2, ',', '.') }}</td>
                    <td class="text-left">{{ $payment->description ?? 'Sin descripción' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <h2 class="section-title">Historial de Abonos</h2>
        <table class="payments-table">
            <tbody>
                <tr>
                    <td colspan="3" style="text-align: center;">No hay abonos registrados</td>
                </tr>
            </tbody>
        </table>
        @endif

        <div class="footer">
            Detalle generado por el sistema - {{ \Carbon\Carbon::now()->isoFormat('LLLL') }}
        </div>
    </div>
</body>
</html>
