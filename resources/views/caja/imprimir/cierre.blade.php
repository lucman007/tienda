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
            <td colspan="3">
                <hr style="border: 1px dotted black;">
                <span style="font-size: 8pt"><strong>//CIERRE DE CAJA</strong></span>
                <hr style="border: 1px dotted black;">
            </td>
        </tr>
        <tr>
            <td><strong>Caja:</strong></td>
            <td style="width: 28mm" class="float-r-alt">{{$caja->empleado->nombre}} {{$caja->empleado->apellidos}}</td>
        </tr>
        <tr>
            <td><strong>Fecha apertura:</strong></td>
            <td style="width: 28mm" class="float-r-alt">{{date('d/m/Y H:i',strtotime($caja->fecha_a))}}</td>
        </tr>
        <tr>
            <td><strong>Fecha de cierre:</strong></td>
            <td style="width: 28mm"
                class="float-r-alt">{{$caja->fecha_c?date('d/m/Y H:i',strtotime($caja->fecha_c)):''}}</td>
        </tr>
    </table>
</div>
<br>
@if($detallado == 'true')
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
        <tr>
            <td><strong>Total tarjeta:</strong></td>
            <td class="float-r">
                S/ {{number_format($caja->tarjeta + $caja->tarjeta_1 + $caja->rappi + $caja->deliverygo + $caja->pedidosya,2)}}</td>
        </tr>
        <tr>
            <td><strong>Total transferencia:</strong></td>
            <td class="float-r">S/ {{number_format($caja->yape + $caja->plin + $caja->transferencia,2)}}</td>
        </tr>
        <tr>
            <td><strong>Otros:</strong></td>
            <td class="float-r">S/ {{$caja->otros??'0.00'}}</td>
        </tr>
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
        @if($caja->credito > 0 || $caja->efectivo_usd > 0 || $caja->credito_usd > 0)
            <tr>
                <td colspan="2">
                    <hr style="border: 1px dashed black">
                </td>
            </tr>
            @if($caja->credito > 0)
                <tr>
                    <td><strong>Total crédito:</strong></td>
                    <td class="float-r">S/ {{$caja->credito}}</td>
                </tr>
            @endif
            @if($caja->efectivo_usd > 0)
            <tr>
                <td><strong>Total efectivo USD:</strong></td>
                <td class="float-r">USD {{$caja->efectivo_usd}}</td>
            </tr>
            @endif
            @if($caja->credito_usd > 0)
                <tr>
                    <td><strong>Total crédito USD:</strong></td>
                    <td class="float-r">USD {{$caja->credito_usd}}</td>
                </tr>
            @endif
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
    <table class="items">
        <thead>
        <tr>
            <td colspan="2">
                <hr style="border: 1px dotted black;">
                <span style="font-size: 8pt"><strong>//VENTAS POR TARJETA</strong></span>
                <hr style="border: 1px dotted black;">
            </td>
        </tr>
        </thead>
        <tbody>
        <tr class="items-tr">
            <td style="width: 42mm; text-align: left">Tarjeta (Visa)</td>
            <td style="width: 15mm">S/ {{$caja->tarjeta??'0.00'}}</td>
        </tr>
        <tr class="items-tr">
            <td style="width: 35mm; text-align: left">Tarjeta (Mastercard)</td>
            <td style="width: 15mm">S/ {{$caja->tarjeta_1??'0.00'}}</td>
        </tr>
        <tr>
            <td colspan="2">
                <hr style="border: 1px dashed black;">
            </td>
        </tr>
        </tbody>
    </table>
    <table class="items">
        <thead>
        <tr>
            <td colspan="2">
                <hr style="border: 1px dotted black;">
                <span style="font-size: 8pt"><strong>//VENTAS POR TRANSFERENCIA</strong></span>
                <hr style="border: 1px dotted black;">
            </td>
        </tr>
        </thead>
        <tbody>
        <tr class="items-tr">
            <td style="width: 42mm; text-align: left">Yape</td>
            <td style="width: 15mm">S/ {{$caja->yape??'0.00'}}</td>
        </tr>
        <tr class="items-tr">
            <td style="width: 35mm; text-align: left">Plin</td>
            <td style="width: 15mm">S/ {{$caja->plin??'0.00'}}</td>
        </tr>
        <tr class="items-tr">
            <td style="width: 35mm; text-align: left">Transferencia</td>
            <td style="width: 15mm">S/ {{$caja->transferencia??'0.00'}}</td>
        </tr>
        <tr>
            <td colspan="2">
                <hr style="border: 1px dashed black;">
            </td>
        </tr>
        </tbody>
    </table>
    <table class="items">
        <thead>
        <tr>
            <td colspan="3">
                <hr style="border: 1px dotted black;">
                <span style="font-size: 8pt"><strong>//RESUMEN DE PRODUCTOS</strong></span>
                <hr style="border: 1px dotted black;">
            </td>
        </tr>
        <tr class="table-header">
            <th scope="col">Producto</th>
            <th scope="col">Cant.</th>
            <th scope="col">Total</th>
        </tr>
        </thead>
        <tbody>
        @php
            $suma = 0;
            $suma_productos = 0;
            $suma_delivery = 0;
            $suma_cant_delivery = 0;
        @endphp
        @foreach($productos as $producto)
            @if($producto->tipo_producto != 4)
                @php
                    $suma += $producto->monto_total;
                    $suma_productos += $producto->vendidos;
                @endphp
                <tr class="items-tr">
                    <td style="width: 30mm; text-align: left">{{$producto->nombre}}</td>
                    <td style="width: 10mm">{{$producto->vendidos}}</td>
                    <td style="width: 15mm">S/{{number_format($producto->monto_total,3)}}</td>
                </tr>
            @else
                @php
                    $suma_delivery += $producto->monto_total;
                    $suma_cant_delivery += $producto->vendidos;
                @endphp
            @endif
        @endforeach
        <tr>
            <td colspan="3">
                <hr style="border: 1px dashed black;">
            </td>
        </tr>
        <tr>
            <td><strong>Total productos:</strong></td>
            <td>{{round($suma_productos,2)}}</td>
            <td>S/ {{number_format($suma,3)}}</td>
        </tr>
        </tbody>
    </table>
@else
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
        @if($caja->credito > 0 || $caja->efectivo_usd > 0 || $caja->credito_usd > 0)
            <tr>
                <td colspan="2">
                    <hr style="border: 1px dashed black">
                </td>
            </tr>
            @if($caja->credito > 0)
                <tr>
                    <td><strong>Total crédito:</strong></td>
                    <td class="float-r">S/ {{$caja->credito}}</td>
                </tr>
            @endif
            @if($caja->efectivo_usd > 0)
                <tr>
                    <td><strong>Total efectivo USD:</strong></td>
                    <td class="float-r">USD {{$caja->efectivo_usd}}</td>
                </tr>
            @endif
            @if($caja->credito_usd > 0)
                <tr>
                    <td><strong>Total crédito USD:</strong></td>
                    <td class="float-r">USD {{$caja->credito_usd}}</td>
                </tr>
            @endif
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
