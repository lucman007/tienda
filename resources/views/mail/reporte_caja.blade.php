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
    <h3 style="border-bottom: 2px solid #131313; padding: 5mm; margin-left: 5mm">RESUMEN DE VENTAS DEL TURNO</h3>
    <table class="items">
        <thead>
        <tr class="table-header">
            <th scope="col">#</th>
            <th scope="col">FECHA</th>
            <th style="text-align: left; margin-left: 5px" scope="col">CLIENTE</th>
            <th scope="col">IMPORTE</th>
            <th scope="col">PAGO</th>
            <th scope="col">DOC.</th>
        </tr>
        </thead>
        <tbody>
        @php
        $i=1;
        @endphp
        @foreach($ventas as $venta)
            <tr class="items-tr">
                <td>{{str_pad($i, 2, "0", STR_PAD_LEFT)}}</td>
                <td>{{$venta->fecha}}</td>
                <td style="width: 90mm; text-align: left">{{$venta->cliente->persona->nombre}}</td>
                <td style="width: 20mm">S/ {{$venta->total_venta}}</td>
                <td>{{$venta->tipo_pago}}</td>
                <td>{{$venta->facturacion->codigo_tipo_documento==30?$venta->ticket:$venta->facturacion->serie.'-'.$venta->facturacion->correlativo}}</td>
            </tr>
            @php
                $i++;
            @endphp
        @endforeach
        <tr class="table-footer">
            <td colspan="3">Total</td>
            <td colspan="3">S/ {{number_format($total,2)}}</td>
        </tr>
        </tbody>
    </table>
</body>
<style>
    p,td,th{
        font-size: 8pt;
    }
    table{
        width:200mm;
    }
    h3{
        font-size: 12pt;
        margin: 0;
        padding: 0;
    }
    .items {
         width: 200mm;
         margin-top: 2mm;
         margin-bottom: 5mm;
         margin-left: 20px;
        border-collapse: collapse;
     }
    .table-header th, .table-footer td{
        margin: 0;
        background: #dfdfdf;
        border: 1px solid #767676;
        padding: 10px 0;
        text-align: center;
    }
    .items-tr td{
        border: 1px solid #575757;
        padding: 5px 2px 5px 5px;
        text-align: center;
    }
</style>
</html>
