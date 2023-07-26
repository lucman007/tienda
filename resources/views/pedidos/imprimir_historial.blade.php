<!doctype html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- CSS personalizado -->
    <link rel="stylesheet" href="{{asset('css/style_pdf.css')}}">
    <title>Imprimir pedido</title>
</head>
<body>
<div class="body">
    <p><strong>Fecha:</strong> {{date('d/m/Y')}}</p>
    <table class="items" cellpadding="0">
        <thead>
        <tr class="table-header">
            <td>N°</td>
            <td>Cliente</td>
            <td>Monto</td>
        </tr>
        </thead>
        <tbody>
        @php
        $sum_soles=0;
        $sum_usd=0;
        @endphp

        @foreach($pedidos as $item)
            <tr class="item-borde">
                <td style="width: 12mm">{{$item->facturacion->serie}}-{{+$item->facturacion->correlativo}}</td>
                <td style="width: 30mm">{{$item->cliente->persona->nombre}}</td>
                <td style="width: 18mm">{{$item->facturacion->codigo_moneda=='PEN'?'S/':'USD'}}{{$item->total_venta}}</td>
            </tr>
            @php
                if($item->facturacion->codigo_moneda=='PEN'){
                    $sum_soles+=$item->total_venta;
                } else {
                    $sum_usd+=$item->total_venta;
                }

            @endphp
        @endforeach
        <tr>
            <td></td>
            <td>Total pedidos atendidos:</td>
            <td>S/ {{$sum_soles}}</td>
        </tr>
        @if($sum_usd > 0)
        <tr>
            <td></td>
            <td>Total pedidos atendidos:</td>
            <td>USD {{$sum_usd}}</td>
        </tr>
        @endif
        </tbody>
    </table>
</div>
</body>
<style>
    body{
        font-family: Arial, Helvetica, sans-serif;
    }
    .item-borde td{
        border-bottom:1px solid #8C8C8C;
    }

    h3{
        font-size: 14pt;
        margin: 0;
        font-weight: lighter;
        margin-top: 3px;
    }
    h3 span{
        font-weight: bold;
    }
    p,td{
        font-size: 7.5pt;
    }
    .borde{
        border: 1px solid black;
        border-radius: 5px;
        padding: 20px;
    }

    table{
        margin: 0;
        padding: 0;
    }
    .table-header td{
        border-bottom: 1px solid black;
        margin: 0;
    }

    .body{
        position: relative;
        width: 50mm;
        height: 100mm;
        float: left;
        margin-top: 5mm;
    }
    .body .items {
        width: 50mm;
        position: relative;
    }
</style>
</html>
