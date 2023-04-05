<!doctype html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- CSS personalizado -->
    <link rel="stylesheet" href="{{asset('css/style_pdf.css')}}">
    <title>@yield('titulo') </title>
</head>
<body>
<p style="margin-left: 20px;"><strong>Reporte del {{date('d/m/Y',strtotime($fecha['desde']))}} al {{date('d/m/Y',strtotime($fecha['hasta']))}}</strong></p>
    @if($tipo == 'tipo_pago')
    <table class="items">
        <thead>
        <tr>
            <td colspan="2">
                <hr style="border: 1px dotted black;">
                <span style="font-size: 8pt"><strong>//VENTAS POR TIPO DE PAGO</strong></span>
                <hr style="border: 1px dotted black;">
            </td>
        </tr>
        </thead>
        <tbody>
        <tr class="items-tr">
            <td style="width: 42mm; text-align: left">Efectivo</td>
            <td style="width: 15mm">S/ {{number_format($tipo_pago['efectivo']??0,2)}}</td>
        </tr>
        <tr class="items-tr">
            <td style="width: 42mm; text-align: left">Yape</td>
            <td style="width: 15mm">S/ {{number_format($tipo_pago['yape']??0,2)}}</td>
        </tr>
        <tr class="items-tr">
            <td style="width: 42mm; text-align: left">Plin</td>
            <td style="width: 15mm">S/ {{number_format($tipo_pago['plin']??0,2)}}</td>
        </tr>
        <tr class="items-tr">
            <td style="width: 42mm; text-align: left">Transferencia</td>
            <td style="width: 15mm">S/ {{number_format($tipo_pago['transferencia']??0,2)}}</td>
        </tr>
        <tr class="items-tr">
            <td style="width: 42mm; text-align: left">Crédito</td>
            <td style="width: 15mm">S/ {{number_format($tipo_pago['credito']??0,2)}}</td>
        </tr>
        <tr class="items-tr">
            <td style="width: 42mm; text-align: left">Otros</td>
            <td style="width: 15mm">S/ {{number_format($tipo_pago['otros']??0,2)}}</td>
        </tr>
        <tr class="items-tr">
            <td style="width: 42mm; text-align: left">Tarjeta visa</td>
            <td style="width: 15mm">S/ {{number_format($tipo_pago['visa']??0,2)}}</td>
        </tr>
        <tr class="items-tr">
            <td style="width: 42mm; text-align: left">Tarjeta Mastercard</td>
            <td style="width: 15mm">S/ {{number_format($tipo_pago['mastercard']??0,2)}}</td>
        </tr>
        <tr class="items-tr">
            <td style="width: 42mm; text-align: left">Rappi</td>
            <td style="width: 15mm">S/ {{number_format($tipo_pago['rappi']??0,2)}}</td>
        </tr>
        <tr class="items-tr">
            <td style="width: 42mm; text-align: left">DeliveryGo</td>
            <td style="width: 15mm">S/ {{number_format($tipo_pago['deliverygo']??0,2)}}</td>
        </tr>
        <tr class="items-tr">
            <td style="width: 42mm; text-align: left">PedidosYa</td>
            <td style="width: 15mm">S/ {{number_format($tipo_pago['pedidosya']??0,2)}}</td>
        </tr>
        <tr>
            <td colspan="2">
                <hr style="border: 1px dashed black;">
            </td>
        </tr>
        </tbody>
    </table>
     @else
        @php

        if($moneda == 'pen'){
            $totales = $totales[0];
            $signo_moneda = 'S/';
        } else {
            $totales = $totales[1];
            $signo_moneda = 'USD';
        }

        @endphp
        <table class="items">
            <thead>
            <tr>
                <td colspan="2">
                    <hr style="border: 1px dotted black;">
                    <span style="font-size: 8pt"><strong>//RESUMEN DE VENTAS</strong></span>
                    <hr style="border: 1px dotted black;">
                </td>
            </tr>
            </thead>
            <tbody>
            <tr class="items-tr">
                <td style="width: 42mm; text-align: left">Ventas brutas</td>
                <td style="width: 15mm">{{$signo_moneda}} {{number_format($totales['ventas_brutas']??0,2)}}</td>
            </tr>
            <tr class="items-tr">
                <td style="width: 42mm; text-align: left">Impuestos</td>
                <td style="width: 15mm">{{$signo_moneda}} {{number_format($totales['impuestos']??0,2)}}</td>
            </tr>
            <tr class="items-tr">
                <td style="width: 42mm; text-align: left">Ventas netas</td>
                <td style="width: 15mm">{{$signo_moneda}} {{number_format($totales['ventas_netas']??0,2)}}</td>
            </tr>
            <tr class="items-tr">
                <td style="width: 42mm; text-align: left">Costos</td>
                <td style="width: 15mm">{{$signo_moneda}} {{number_format($totales['costos']??0,2)}}</td>
            </tr>
            <tr class="items-tr">
                <td style="width: 42mm; text-align: left">Cobros por envío</td>
                <td style="width: 15mm">{{$signo_moneda}} {{number_format($totales['delivery']??0,2)}}</td>
            </tr>
            <tr class="items-tr">
                <td style="width: 42mm; text-align: left">Utilidad</td>
                <td style="width: 15mm">{{$signo_moneda}} {{number_format($totales['utilidad']??0,2)}}</td>
            </tr>
            <tr>
                <td colspan="2">
                    <hr style="border: 1px dashed black;">
                </td>
            </tr>
            </tbody>
        </table>
@endif
</body>
<style>
    p,td{
        font-size: 8pt;
    }
    table{
        width:50mm;
    }
    .float-r{
        text-align: right;
        width: 27mm;
    }
    .float-r-alt{
        text-align: right;
        width: 35mm;
    }
    .body, .header{
        width: 50mm;
    }
</style>
</html>
