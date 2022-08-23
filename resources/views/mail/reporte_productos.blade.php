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
    <h3 style="border-bottom: 2px solid #131313; padding: 5mm; margin-left: 5mm">RESUMEN PRODUCTOS VENDIDOS</h3>
    <table class="items">
        <thead>
        <tr class="table-header">
            <th style="text-align: left; margin-left: 5px" scope="col">PRODUCTO</th>
            <th scope="col">CANTIDAD</th>
            <th scope="col">TOTAL</th>
        </tr>
        </thead>
        <tbody>
        @php
            $suma = 0;
        @endphp
        @foreach($productos as $producto)
            @if($producto->tipo_producto != 4)
                @php
                $suma += $producto->monto_total;
                @endphp
                <tr class="items-tr">
                    <td style="width: 90mm; text-align: left">{{$producto->nombre}}</td>
                    <td style="width: 30mm">{{$producto->vendidos}}</td>
                    <td style="width: 30mm">S/{{number_format($producto->monto_total,3)}}</td>
                </tr>
            @endif
        @endforeach
        <tr class="table-footer">
            <td colspan="2">Total</td>
            <td>S/ {{number_format($suma,3)}}</td>
        </tr>
        </tbody>
    </table>
    {{--<h3 style="border-bottom: 2px solid #131313; padding: 5mm; margin-left: 5mm">RESUMEN PAGO DELIVERY</h3>
    <table class="items">
        <thead>
        <tr class="table-header">
            <th style="text-align: left; margin-left: 5px" scope="col">DELIVERY</th>
            <th scope="col">CANTIDAD</th>
            <th scope="col">TOTAL</th>
        </tr>
        </thead>
        <tbody>
        @php
            $suma = 0;
        @endphp
        @foreach($productos as $producto)
            @if($producto->tipo_producto == 4)
                @php
                    $suma += $producto->monto_total;
                @endphp
                <tr class="items-tr">
                    <td style="width: 90mm; text-align: left">{{$producto->nombre}}</td>
                    <td style="width: 30mm">{{$producto->vendidos}}</td>
                    <td style="width: 30mm">{{number_format($producto->monto_total,3)}}</td>
                </tr>
            @endif
        @endforeach
        <tr class="table-footer">
            <td colspan="2">Total</td>
            <td>S/ {{number_format($suma,3)}}</td>
        </tr>
        </tbody>
    </table>--}}
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
