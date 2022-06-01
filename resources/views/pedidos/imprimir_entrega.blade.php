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
            <strong>Fecha:</strong> {{ date('d/m/Y H:i:s',strtotime($orden->fecha)) }} <br>
            <strong>Pedido:</strong> #{{$orden->idorden}}  <br>
            @if($orden->vendedor->idpersona != -1)
            <strong>Atiende: </strong>{{$orden->vendedor->nombre}}  <br>
            @endif
        </p>
        <div style="border-top: 1px dashed black"></div>
        <p>
            <strong>Cliente:</strong> {{$entrega['contacto']}} <br>
            <strong>Dirección:</strong> {{$entrega['direccion']}} <br>
            <strong>Referencia:</strong> {{$entrega['referencia']}} <br>
            <strong>Teléfono:</strong> {{$entrega['telefono']}} <br>
        </p>
        <div style="border-top: 1px dashed black"></div>
        <p>
            <strong>Costo delivery:</strong> S/ {{number_format($entrega['costo'],2)}}
        </p>
    </div>
</div>


<style>

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
        width: 200mm;
        height: 100mm;
        float: left;
        margin-top: 5mm;
    }

    .body .info-usuario{
        width: 64mm;
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
</body>
</html>
