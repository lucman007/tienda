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
<body onload="window.print()">
<div class="body">
    <div class="info-usuario" style="margin-left: -5mm">
        <p>
            #{{$orden->idorden}}  <br>
            Fecha: {{ date('d/m/Y H:i:s',strtotime($orden->fecha)) }} <br>
            @if($orden->vendedor->idpersona != -1)
            Atiende: {{$orden->vendedor->nombre}}  <br>
            @endif
            Cliente: {{json_decode($orden->datos_entrega,true)['contacto']}}
        </p>
    </div>
    <table class="items" cellpadding="0" style="margin-left: -5mm">
        <thead>
        <tr class="table-header">
            <td><strong>(Cant.) Descripción</strong></td>
            <td>Total</td>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td colspan="2">
                <hr style="border: 1px dashed black">
            </td>
        </tr>
        @foreach($orden->productos as $item)
            <tr>
                <td style="width: 60mm">
                    ({{$item->detalle->cantidad}}) <strong>{{$item->nombre}} </strong><br>
                    {!! $item->detalle->descripcion !!}
                </td>
                <td>{{number_format($item->detalle->monto * $item->detalle->cantidad, 2)}}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="2">
                <hr style="border: 1px dashed black">
            </td>
        </tr>
        <tr>
            <td><strong>Total:</strong></td>
            <td><strong>{{$orden->total}}</strong></td>
        </tr>
        </tbody>
    </table>
</div>
</body>

<style>
    h3{
        font-size: 13pt;
        margin: 0;
        font-weight: lighter;
        margin-top: 3px;
    }
    h3 span{
        font-weight: bold;
    }
    p,td{
        font-size: 9pt;
    }
    table{
        margin: 0;
        padding: 0;
    }
    .table-header td{
        margin: 0;
    }

    .body .info-usuario{
        margin-left: -5mm;
        margin-bottom: 5mm;
        width: 60mm;
    }
    .body .info-usuario p{
        line-height: 4mm;
    }
</style>

</html>
