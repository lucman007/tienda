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
<p style="margin-left: 20px;"><strong>Reporte del {{date('d/m/Y',strtotime($fecha['desde']))}} al {{date('d/m/Y',strtotime($fecha['hasta']))}}</strong>
    @if($buscar != '')
        <br>
        <br>
        FILTRO: VENTAS {{strtoupper($buscar)}}
    @endif
</p>
<table>
    <thead>
    <tr>
        <td colspan="2"><strong>VENTAS EN SOLES</strong></td>
    </tr>
    <tr>
        <th scope="col"><strong>Venta</strong></th>
        <th scope="col"><strong>Monto</strong></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>Ventas brutas</td>
        <td>{{$totales['ventas_brutas']}}</td>
    </tr>
    <tr>
        <td>Impuestos</td>
        <td>{{$totales['impuestos']}}</td>
    </tr>
    <tr>
        <td>Ventas netas</td>
        <td>{{$totales['ventas_netas']}}</td>
    </tr>
    <tr>
        <td>Costos</td>
        <td>{{$totales['costos']}}</td>
    </tr>
    <tr>
        <td>Utilidad</td>
        <td>{{$totales['utilidad']}}</td>
    </tr>
    </tbody>
</table>
<br>
<table>
    <thead>
    <tr>
        <td colspan="2"><strong>VENTAS POR TIPO DE PAGO</strong></td>
    </tr>
    <tr>
        <th scope="col"><strong>Tipo de pago</strong></th>
        <th scope="col"><strong>Monto</strong></th>
    </tr>
    </thead>
    <tbody>
    @foreach($tipo_pago as $key=>$pago)
        <tr>
            <td>{{$key}}</td>
            <td>{{$pago}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
<br>
<table class="items">
    <thead>
    <tr class="table-header">
        <td colspan="7"><strong>LISTA DE VENTAS</strong></td>
    </tr>
    <tr>
        <th scope="col"><strong>Venta</strong></th>
        <th scope="col"><strong>Fecha</strong></th>
        <th scope="col"><strong>Cliente</strong></th>
        <th scope="col"><strong>Importe</strong></th>
        <th scope="col"><strong>Moneda</strong></th>
        <th scope="col"><strong>Pago</strong></th>
        <th scope="col"><strong>Comprobante</strong></th>
    </tr>
    </thead>
    <tbody>
    @php
        $tipo_de_pago = \sysfact\Http\Controllers\Helpers\DataTipoPago::getTipoPago();
    @endphp
    @foreach($ventas as $venta)
        <tr class="items-tr">
            <td>{{$venta->idventa}}</td>
            <td>{{$venta->fecha}}</td>
            <td style="width: 50mm">{{$venta->cliente->persona->nombre}}</td>
            <td>{{$venta->total_venta}}</td>
            <td>{{$venta->facturacion->codigo_moneda}}</td>
            <td style="width: 25mm">
                @php
                    $i = 0;
                @endphp
                @foreach($venta->pago as $pago)
                    @if($i != 0)
                        <br>
                    @endif
                    @php
                        $index = array_search($pago->tipo, array_column($tipo_de_pago,'num_val'));
                        $i++;
                    @endphp
                    {{strtoupper($tipo_de_pago[$index]['label'])}} {{$pago->monto}}
                @endforeach
            </td>
            <td>{{$venta->facturacion->serie.'-'.$venta->facturacion->correlativo}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
<style>
    p,td,th{
        font-size: 8pt;
    }
    table{
        border-collapse: collapse;
        margin-left: 20px;
    }
    h3{
        font-size: 12pt;
        margin: 0;
        padding: 0;
    }
    .items {
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
    table td, table th{
        border: 1px solid #575757;
        padding: 5px 2px 5px 5px;
        text-align: center;
    }
</style>
</html>
