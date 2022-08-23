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
<div class="header">
    <table>
        <tr>
            <td><strong>Caja:</strong></td>
            <td class="float-r-alt">{{$caja->empleado->nombre}} {{$caja->empleado->apellidos}}</td>
        </tr>
        <tr>
            <td><strong>Fecha apertura:</strong></td>
            <td class="float-r-alt">{{date('d/m/Y H:i',strtotime($caja->fecha_a))}}</td>
        </tr>
        <tr>
            <td><strong>Fecha de cierre:</strong></td>
            <td class="float-r-alt">{{$caja->fecha_c?date('d/m/Y H:i',strtotime($caja->fecha_c)):''}}</td>
        </tr>
    </table>
</div>
<br>
<div class="body">
    <table>
        <tr>
            <td colspan="2">
                <hr style="border: 1px dashed black">
            </td>
        </tr>
        <tr>
            <td><strong>Saldo inicial:</strong></td>
            <td class="float-r">S/ {{$caja->apertura}}</td>
        </tr>
        <tr>
            <td><strong>Total efectivo:</strong></td>
            <td class="float-r">S/ {{$caja->efectivo??'0.00'}}</td>
        </tr>
        @if($caja->tarjeta > 0)
        <tr>
            <td><strong>Tarjeta (Visa):</strong></td>
            <td class="float-r">S/ {{$caja->tarjeta??'0.00'}}</td>
        </tr>
        @endif
        @if($caja->tarjeta_1 > 0)
        <tr>
            <td><strong>Tarjeta (Mastercard):</strong></td>
            <td class="float-r">S/ {{$caja->tarjeta_1??'0.00'}}</td>
        </tr>
        @endif
        @if($caja->yape > 0)
        <tr>
            <td><strong>Yape:</strong></td>
            <td class="float-r">S/ {{$caja->yape??'0.00'}}</td>
        </tr>
        @endif
        @if($caja->plin > 0)
        <tr>
            <td><strong>Plin:</strong></td>
            <td class="float-r">S/ {{$caja->plin??'0.00'}}</td>
        </tr>
        @endif
        @if($caja->transferencia > 0)
        <tr>
            <td><strong>Transferencia:</strong></td>
            <td class="float-r">S/ {{$caja->transferencia??'0.00'}}</td>
        </tr>
        @endif
        @if($caja->otros > 0)
        <tr>
            <td><strong>Otros:</strong></td>
            <td class="float-r">S/ {{$caja->otros??'0.00'}}</td>
        </tr>
        @endif
        <tr>
            <td><strong>Gastos:</strong></td>
            <td class="float-r">S/ {{$caja->gastos??'0.00'}}</td>
        </tr>
        <tr>
            <td><strong>Devoluciones:</strong></td>
            <td class="float-r">S/ {{$caja->devoluciones??'0.00'}}</td>
        </tr>
        <tr>
            <td colspan="2">
                <hr style="border: 1px dashed black">
            </td>
        </tr>
        <tr>
            <td><strong>Total efectivo teórico:</strong></td>
            <td class="float-r"><strong>S/ {{$caja->efectivo_teorico??'0.00'}}</strong></td>
        </tr>
        <tr>
            <td><strong>Total efectivo real:</strong></td>
            <td class="float-r"><strong>S/ {{$caja->efectivo_real??'0.00'}}</strong></td>
        </tr>
        <tr>
            <td><strong>Descuadre:</strong></td>
            <td class="float-r">S/ {{$caja->descuadre??'0.00'}}</td>
        </tr>
        @if($caja->credito > 0)
            <tr>
                <td colspan="2">
                    <hr style="border: 1px dashed black">
                </td>
            </tr>
            <tr>
                <td><strong>Total crédito:</strong></td>
                <td class="float-r">S/ {{$caja->credito}}</td>
            </tr>
        @endif
        <tr>
            <td colspan="2">
                <hr style="border: 1px dashed black">
            </td>
        </tr>
        <tr>
            <td><strong>Observación:</strong></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="2" class="float-r" style="text-align: justify">{{$caja->observacion_c}}</td>
        </tr>
    </table>
</div>
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
