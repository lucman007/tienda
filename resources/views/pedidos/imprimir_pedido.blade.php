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
    <div class="info-usuario">
        <p>
            #{{$orden->idorden}}  <br>
            Fecha: {{ date('d/m/Y H:i:s',strtotime($orden->fecha)) }} <br>
            @if($orden->vendedor->idpersona != -1)
            Atiende: {{$orden->vendedor->nombre}}  <br>
            @endif
            Cliente: {{json_decode($orden->datos_entrega,true)['contacto']}}
        </p>
    </div>
    <table class="items" cellpadding="0">
        <thead>
        <tr class="table-header">
            <td><strong>(Cant.) Descripción</strong></td>
        </tr>
        </thead>
        <tbody>
        @foreach($orden->productos as $item)
            <tr>
                <td style="width: 60mm">
                    ({{$item->detalle->cantidad}}) {{$item->nombre}} <br>
                    {!! $item->detalle->descripcion !!}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</body>

<style>
    .texto-anulado{
        font-size: 18pt;
        margin: 0;
        padding: 0;
    }
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
        margin: 0;
    }

    .body{
        position: relative;
        width: 200mm;
        height: 100mm;
        float: left;
        margin-top: 5mm;
    }

    .body .info-usuario{
        width: 188mm;
        margin-bottom: 5mm;
    }
    .body .info-usuario p{
        line-height: 4mm;
    }

    .body .items {
        width: 200mm;
        position: relative;
    }

    .leyenda{
        width: 200mm;
        margin-top: 3mm;
        text-align: center;
    }
</style>

</html>
