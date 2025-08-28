<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Resultados por Evento y por Moneda</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2196F3;
            padding-bottom: 10px;
        }
        
        .header h1 {
            color: #2196F3;
            margin: 0;
            font-size: 18px;
        }
        
        .info-section {
            margin-bottom: 20px;
        }
        
        .info-row {
            margin-bottom: 5px;
        }
        
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 100px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: right;
        }
        
        th {
            background-color: #2196F3;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        
        .section-header {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: left;
        }
        
        .total-row {
            background-color: #E3F2FD;
            font-weight: bold;
        }
        
        .resultado-row {
            background-color: #C8E6C9;
            font-weight: bold;
            font-size: 14px;
        }
        
        .concepto {
            text-align: left;
            font-weight: bold;
        }
        
        .amount {
            text-align: right;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ESTADO DE RESULTADOS POR EVENTO Y POR MONEDA</h1>
    </div>
    
    <div class="info-section">
        @if(request('event_id'))
            @php
                $event = $events->find(request('event_id'));
            @endphp
            <div class="info-row">
                <span class="info-label">Evento:</span>
                <span>{{ $event ? $event->name . ' - ' . $event->year : 'N/A' }}</span>
            </div>
        @endif
        
        @if(request('start_date') || request('end_date'))
            @php
                $fechaInicio = request('start_date') ? date('d/m/Y', strtotime(request('start_date'))) : 'N/A';
                $fechaFin = request('end_date') ? date('d/m/Y', strtotime(request('end_date'))) : 'N/A';
            @endphp
            <div class="info-row">
                <span class="info-label">Período:</span>
                <span>{{ $fechaInicio }} - {{ $fechaFin }}</span>
            </div>
        @endif
        
        <div class="info-row">
            <span class="info-label">Fecha Reporte:</span>
            <span>{{ date('d/m/Y H:i:s') }}</span>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Concepto</th>
                @foreach($monedas as $moneda)
                    <th>{{ strtoupper($moneda->name) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <!-- SECCIÓN INGRESOS -->
            <tr class="section-header">
                <td colspan="{{ count($monedas) + 1 }}">INGRESOS</td>
            </tr>
            
            @foreach($categorias as $categoria)
                <tr>
                    <td class="concepto">{{ $categoria->name }}</td>
                    @foreach($monedas as $moneda)
                        <td class="amount">
                            $ {{ number_format(
                                optional($totales->first(function($t) use ($categoria, $moneda) {
                                    return $t->categoria === $categoria->name && $t->moneda === $moneda->name;
                                }))->total ?? 0,
                                0,
                                '.',
                                ','
                            ) }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
            
            <tr class="total-row">
                <td class="concepto">TOTAL INGRESOS</td>
                @foreach($monedas as $moneda)
                    <td class="amount">
                        $ {{ number_format(
                            $totales->where('moneda', $moneda->name)->sum('total') ?? 0,
                            0,
                            '.',
                            ','
                        ) }}
                    </td>
                @endforeach
            </tr>
            
            <!-- SECCIÓN EGRESOS -->
            <tr class="section-header">
                <td colspan="{{ count($monedas) + 1 }}">EGRESOS</td>
            </tr>
            
            @foreach($categoriasEgreso as $categoria)
                <tr>
                    <td class="concepto">{{ $categoria->name }}</td>
                    @foreach($monedas as $moneda)
                        <td class="amount">
                            $ {{ number_format(
                                optional($totalesEgreso->first(function($t) use ($categoria, $moneda) {
                                    return $t->categoria === $categoria->name && $t->moneda === $moneda->name;
                                }))->total ?? 0,
                                0,
                                '.',
                                ','
                            ) }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
            
            <tr class="total-row">
                <td class="concepto">TOTAL EGRESOS</td>
                @foreach($monedas as $moneda)
                    <td class="amount">
                        $ {{ number_format(
                            $totalesEgreso->where('moneda', $moneda->name)->sum('total') ?? 0,
                            0,
                            '.',
                            ','
                        ) }}
                    </td>
                @endforeach
            </tr>
            
            <!-- RESULTADO -->
            <tr class="resultado-row">
                <td class="concepto">RESULTADO</td>
                @foreach($monedas as $moneda)
                    @php
                        $ingresos = $totales->where('moneda', $moneda->name)->sum('total') ?? 0;
                        $egresos = $totalesEgreso->where('moneda', $moneda->name)->sum('total') ?? 0;
                        $resultado = $ingresos - $egresos;
                    @endphp
                    <td class="amount">
                        $ {{ number_format($resultado, 0, '.', ',') }}
                    </td>
                @endforeach
            </tr>
        </tbody>
    </table>
    
    <div class="footer">
        <p>Reporte generado el {{ date('d/m/Y') }} a las {{ date('H:i:s') }}</p>
        <p>STC FUTBOL - Sistema de Gestión de Eventos</p>
    </div>
</body>
</html>
