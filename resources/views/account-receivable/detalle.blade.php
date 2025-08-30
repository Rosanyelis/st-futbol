<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Cuenta por Cobrar</title>
    <style type="text/css">
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            /* background: #f8fafd; */
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
        .costs-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .costs-table th {
            background: #f8f9fa;
            padding: 5px;
            text-align: center;
            border: 1px solid #dee2e6;
            font-weight: bold;
        }
        .costs-table td {
            padding: 5px;
            border: 1px solid #dee2e6;
            text-align: center;
        }
        .costs-table .text-end {
            text-align: right;
        }
        .costs-table .total-row {
            background: #f8f9fa;
            font-weight: bold;
        }
        .club-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
        }
        .footer {
            margin-top: 20px;
            font-size: 10px;
            color: #888;
            text-align: center;
            border-top: 2px solid #b3d7f2;
            padding-top: 10px;
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

        <!-- Información del Club -->
        <h2 class="section-title">Información del Club</h2>
        <table class="info-table">
            <tr>
                <th> Club:</th>
                <td>{{ $accountReceivable->club->name ?? 'Club no encontrado' }}</td>
                <th>Representante:</th>
                <td>{{ $accountReceivable->club->representant ?? 'No especificado' }}</td>
            </tr>
            <tr>
                <th>Teléfono:</th>
                <td>{{ $accountReceivable->club->phone ?? 'No especificado' }}</td>
                <th>Email:</th>
                <td>{{ $accountReceivable->club->email ?? 'No especificado' }}</td>
            </tr>
            <tr>
                <th>Evento:</th>
                <td>{{ $accountReceivable->event->name ?? 'Evento no encontrado' }}</td>
                <th>Moneda:</th>
                <td>{{ $accountReceivable->currency->name ?? 'Moneda no encontrada' }}</td>
            </tr>
               
        </table>

        <!-- Resumen de Montos -->
        <h2 class="section-title">Resumen de Montos</h2>
        <div class="summary-cards clearfix">
            <div class="summary-card card-total">
                <h4>Total</h4>
                <div class="amount">{{ $accountReceivable->currency->symbol ?? '$' }} {{ number_format($accountReceivable->total_amount, 2, ',', '.') }}</div>
            </div>
            <div class="summary-card card-paid">
                <h4>Pagado</h4>
                <div class="amount">{{ $accountReceivable->currency->symbol ?? '$' }} {{ number_format($accountReceivable->getPaidAmount(), 2, ',', '.') }}</div>
            </div>
            <div class="summary-card card-pending">
                <h4>Pendiente</h4>
                <div class="amount">{{ $accountReceivable->currency->symbol ?? '$' }} {{ number_format($accountReceivable->getPendingAmount(), 2, ',', '.') }}</div>
            </div>
            <div class="summary-card card-percentage">
                <h4>% Pagado</h4>
                <div class="amount">{{ $accountReceivable->getPaymentPercentage() }}%</div>
            </div>
        </div>

        <!-- Detalle de Jugadores y Costos -->
        <h2 class="section-title">Detalle de Jugadores y Costos</h2>
        <table class="costs-table">
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
                    <td>{{ number_format($accountReceivable->players_quantity, 0, ',', '.') }}</td>
                    <td class="text-end">{{ $accountReceivable->currency->symbol ?? '$' }} {{ number_format($accountReceivable->player_price, 2, ',', '.') }}</td>
                    <td class="text-end">{{ $accountReceivable->currency->symbol ?? '$' }} {{ number_format($accountReceivable->total_players, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Profesores</td>
                    <td>{{ number_format($accountReceivable->teachers_quantity, 0, ',', '.') }}</td>
                    <td class="text-end">{{ $accountReceivable->currency->symbol ?? '$' }} {{ number_format($accountReceivable->teacher_price, 2, ',', '.') }}</td>
                    <td class="text-end">{{ $accountReceivable->currency->symbol ?? '$' }} {{ number_format($accountReceivable->total_teachers, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Acompañantes</td>
                    <td>{{ number_format($accountReceivable->companions_quantity, 0, ',', '.') }}</td>
                    <td class="text-end">{{ $accountReceivable->currency->symbol ?? '$' }} {{ number_format($accountReceivable->companion_price, 2, ',', '.') }}</td>
                    <td class="text-end">{{ $accountReceivable->currency->symbol ?? '$' }} {{ number_format($accountReceivable->total_companions, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Choferes</td>
                    <td>{{ number_format($accountReceivable->drivers_quantity, 0, ',', '.') }}</td>
                    <td class="text-end">{{ $accountReceivable->currency->symbol ?? '$' }} {{ number_format($accountReceivable->driver_price, 2, ',', '.') }}</td>
                    <td class="text-end">{{ $accountReceivable->currency->symbol ?? '$' }} {{ number_format($accountReceivable->total_drivers, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Liberados</td>
                    <td>{{ number_format($accountReceivable->liberated_quantity, 0, ',', '.') }}</td>
                    <td class="text-end">{{ $accountReceivable->currency->symbol ?? '$' }} {{ number_format($accountReceivable->liberated_price ?? 0, 2, ',', '.') }}</td>
                    <td class="text-end">{{ $accountReceivable->currency->symbol ?? '$' }} {{ number_format($accountReceivable->total_liberated ?? 0, 2, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="2"><strong>Total de Personas</strong></td>
                    <td class="text-end"><strong>{{ number_format($accountReceivable->total_people, 0, ',', '.') }}</strong></td>
                    <td class="text-end"><strong>{{ $accountReceivable->currency->symbol ?? '$' }} {{ number_format($accountReceivable->total_amount, 2, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>

        <!-- Información de Hospedaje (si aplica) -->
        @if($accountReceivable->has_accommodation && $accountReceivable->supplier)
        <h2 class="section-title">Información de Hospedaje</h2>
        <table class="info-table">
            <tr>
                <th>Hotel:</th>
                <td>{{ $accountReceivable->supplier->name ?? 'Hotel no especificado' }}</td>
            </tr>
        </table>
        @endif

        <!-- Observaciones -->
        @if($accountReceivable->description)
        <h2 class="section-title">Observaciones</h2>
        <table class="info-table">
            <tr>
                <th>Descripción:</th>
                <td>{{ $accountReceivable->description }}</td>
            </tr>
        </table>
        @endif

        <!-- Historial de Abonos -->
        @if($accountReceivable->payments->count() > 0)
        <h2 class="section-title">Historial de Abonos</h2>
        <table class="costs-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($accountReceivable->payments as $payment)
                <tr>
                    <td>{{ $payment->date->format('d/m/Y') }}</td>
                    <td class="text-end">{{ $accountReceivable->currency->symbol ?? '$' }} {{ number_format($payment->amount, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <div class="footer">
            Detalle generado por el sistema - {{ \Carbon\Carbon::now()->isoFormat('LLLL') }}
        </div>
    </div>
</body>
</html>
