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
<div class="body">
    <table style="width: 70mm">
        <tr>
            <td colspan="2">
                <hr style="border: 1px dotted black;">
                <span style="font-size: 8pt"><strong>RECIBO DE PAGO N° {{str_pad($pago->correlativo,5,'0', STR_PAD_LEFT)}}</strong></span>
                <hr style="border: 1px dotted black;">
            </td>
        </tr>
        <tr>
            <td style="width: 30mm"><strong>Caja:</strong></td>
            <td style="width: 30mm" class="float-r-alt">{{$pago->cajero->nombre}} {{$pago->cajero->apellidos}}</td>
        </tr>
        <tr>
            <td><strong>Fecha:</strong></td>
            <td style="width: 25mm" class="float-r-alt">{{date('d/m/Y',strtotime($pago->fecha))}}</td>
        </tr>
        <tr>
            <td colspan="2"><hr style="border: 1px dotted black;"></td>
        </tr>
        <tr>
            <td><strong>Empleado:</strong></td>
            <td style="width: 25mm" class="float-r-alt">{{$pago->empleado->nombre}} {{$pago->empleado->apellidos}}</td>
        </tr>
        <tr>
            <td><strong>Monto:</strong></td>
            <td style="width: 25mm" class="float-r-alt">{{$pago->monto}}</td>
        </tr>
        <tr>
            <td><strong>Corresponde a:</strong></td>
            <td style="width: 25mm" class="float-r-alt">{{$pago->mes}}</td>
        </tr>
        <tr>
            <td><strong>Concepto:</strong></td>
            <td style="width: 25mm" class="float-r-alt">{{$pago->tipo}}</td>
        </tr>
    </table>
</div>
<br>
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
