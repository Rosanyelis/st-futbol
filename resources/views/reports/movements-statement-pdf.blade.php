<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Estado General de Movimientos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            font-size: 10px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-success {
            color: #28a745;
        }
        .text-danger {
            color: #dc3545;
        }
        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .summary h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        .summary p {
            margin: 5px 0;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Estado General de Movimientos</h1>
        <p>Fecha de generación: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Total de registros: {{ $data->count() }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Evento</th>
                <th>Movimiento</th>
                <th>Ingreso</th>
                <th>Egreso</th>
                <th>Moneda</th>
                <th>Tipo Ingreso</th>
                <th>Tipo Egreso</th>
                <th>Club</th>
                <th>Proveedor</th>
                <th>Cuenta</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $item)
            <tr>
                <td class="text-center">{{ $item->date ? $item->date->format('d/m/Y') : '-' }}</td>
                <td>{{ $item->event ? $item->event->name : '-' }}</td>
                <td>{{ $item->description ?: '-' }}</td>
                <td class="text-right text-success">
                    @if($item->type === 'Ingreso')
                        {{ number_format($item->amount, 0, ',', '.') }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-right text-danger">
                    @if($item->type === 'Egreso')
                        {{ number_format($item->amount, 0, ',', '.') }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-center">{{ $item->currency ? $item->currency->name : '-' }}</td>
                <td>
                    @if($item->type === 'Ingreso' && $item->categoryIncome)
                        {{ $item->categoryIncome->name }}
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($item->type === 'Egreso' && $item->categoryEgress)
                        {{ $item->categoryEgress->name }}
                    @else
                        -
                    @endif
                </td>
                <td>{{ $item->club ? $item->club->name : '-' }}</td>
                <td>{{ $item->supplier ? $item->supplier->name : '-' }}</td>
                <td>{{ $item->methodPayment ? $item->methodPayment->account_holder : '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="11" class="text-center">No hay datos disponibles</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($data->count() > 0)
    <div class="summary">
        <h3>Resumen</h3>
        @php
            $totalIngresos = $data->where('type', 'Ingreso')->sum('amount');
            $totalEgresos = $data->where('type', 'Egreso')->sum('amount');
            $balance = $totalIngresos - $totalEgresos;
        @endphp
        <p><strong>Total Ingresos:</strong> {{ number_format($totalIngresos, 0, ',', '.') }}</p>
        <p><strong>Total Egresos:</strong> {{ number_format($totalEgresos, 0, ',', '.') }}</p>
        <p><strong>Balance:</strong> {{ number_format($balance, 0, ',', '.') }}</p>
    </div>
    @endif
</body>
</html>
