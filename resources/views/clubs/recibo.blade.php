<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo</title>
    <style type="text/css">
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            background: #f8fafd;
            margin: 0;
            padding: 0;
        }
        .recibo-container {
            background: #fff;
            border: 1px solid #b3d7f2;
            border-radius: 15px;
            padding: 18px 18px 8px 18px;
            position: relative;
        }
        .recibo-header {
            width: 100%;
            overflow: hidden;
            border-bottom: 2px solid #b3d7f2;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .recibo-header-left {
            float: left;
            width: 60%;
        }
        .recibo-header-right {
            float: right;
            width: 38%;
            text-align: right;
        }
        .recibo-logo {
            width: 80px;
            margin-bottom: 2px;
        }
        .recibo-title {
            font-size: 1.7em;
            font-weight: bold;
            letter-spacing: 0.2em;
            color: #1b6ca8;
            margin-bottom: 2px;
            margin-top: 0;
        }
        .recibo-numero-box {
            border: 1px solid #1b6ca8;
            border-radius: 5px;
            padding: 2px 12px;
            font-size: 1.1em;
            font-weight: bold;
            color: #1b6ca8;
            background: #f8fafd;
            margin-top: 6px;
            text-align: left;
            width: 100%;
            
        }
        .recibo-monto-box {
            border: 1px solid #1b6ca8;
            border-radius: 5px;
            padding: 2px 12px;
            font-size: 1.1em;
            font-weight: bold;
            color: #1b6ca8;
            background: #f8fafd;
            margin-top: 6px;
            text-align: right;
            width: 93%;
            
        }
       
        .recibo-footer {
            margin-top: 18px;
            font-size: 10px;
            color: #888;
            text-align: center;
            border-top: 2px solid #b3d7f2;
            padding-top: 6px;
        }
        .recibo-pie {
            background: #e3f2fd;
            height: 30px;
            width: 100%;
            position: absolute;
            left: 0;
            bottom: 0;
            z-index: 0;
        }
    </style>
</head>
<body>
    <div class="recibo-container">
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
            <tr>
                <th style="width: 50%; text-align: center; padding-bottom: 10px; margin: auto;">
                    <img src="{{ public_path('assets/img/logo-isotipo.png') }}" alt="logo" class="recibo-logo"><br>
                </th>
                <th style="width: 50%; text-align: center; padding-bottom: 10px; margin: auto;">
                    <div style="font-size:16px; font-weight:bold;">TORNEOS DE FUTBOL INFANTIL</div>
                    <div style="font-size:16px; font-weight:bold;">STC TORNEOS</div>
                    <div style="font-size:14px;">C.P. 1424 AOA - Ciudad de Buenos Aires</div>
                    <div style="font-size:14px;">Cel. : 54 911 4066-2544</div>
                    <div style="font-size:14px;">eventosdeportivos1977@hotmail.com</div>
                </th>
            </tr>
            <tr>
                <th colspan="2"><span class="recibo-title">RECIBO</span></th>
            </tr>
        </table>
        <div class="recibo-header"></div>
        <div style="clear: both;"></div>
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <tr>
                <td style="width: 50%; text-align: left;">
                    <div class="recibo-numero-box">
                        N° Recibo: 0000000{{ $payment->id }} 
                    </div>
                </td>
                <td style="width: 50%; text-align: right;">
                    <div class="recibo-monto-box">
                        {{ $payment->currency->symbol }} {{ number_format($payment->amount, 0, ',', '.') }}
                    </div>
                </td>
            </tr>
        </table>
        <table style="
                    width: 100%; 
                    border-collapse: collapse; 
                    margin-top: 10px; 
                    padding: 10px;
                    line-height: 1.5;
                    font-size: 14px;">
            <tr>
                <th style="width: 50%; text-align: left;">Evento:</th>
                <td style="width: 50%; text-align: right;">{{ $club->event->name }}</td>
            </tr>
            <tr>
                <th style="width: 50%; text-align: left;">Club:</th>
                <td style="width: 50%; text-align: right;">{{ $club->name }}</td>
            </tr>
            <tr>
                <th style="width: 50%; text-align: left;">Encargado:</th>
                <td style="width: 50%; text-align: right;">{{ $club->responsible }}</td>
            </tr>
            <tr>
                <th style="width: 50%; text-align: left;">Monto:</th>
                <td style="width: 50%; text-align: right;">{{ $payment->currency->symbol }} {{ number_format($payment->amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th style="width: 50%; text-align: left;">Moneda:</th>
                <td style="width: 50%; text-align: right;">{{ $payment->currency->name }}</td>
            </tr>
            <tr>
                <th style="width: 50%; text-align: left;">Fecha de Pago:</th>
                <td style="width: 50%; text-align: right;">{{ \Carbon\Carbon::parse($payment->created_at)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <th style="width: 50%; text-align: left;">Observación:</th>
                <td style="width: 50%; text-align: right;">{{ $payment->description }}</td>
            </tr>
        </table>
            
            <div class="recibo-footer">
                Recibo generado por el sistema - {{ \Carbon\Carbon::now()->isoFormat('LLLL') }}
            </div>
            <div class="recibo-pie"></div>
        </div>

        
        
    </div>
</body>
</html>