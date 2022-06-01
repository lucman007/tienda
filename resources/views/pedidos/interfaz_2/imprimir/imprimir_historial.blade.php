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
    <p><strong>HISTORIAL DE PEDIDOS</strong></p>
</div>
<div class="body">
    <div class="items">
        @php($suma =0)
        <table cellpadding="0">
            <thead>
                <tr class="table-header">
                    <td>N°</td>
                    <td>Fecha</td>
                    <td>Vendedor</td>
                    <td>Cliente</td>
                    <td>Total</td>
                    <td>Estado</td>
                </tr>
            </thead>
            @foreach($ordenes as $item)
                @php($suma += $item->total)
                <tr>
                    <td>{{$item->idorden}}</td>
                    <td style="width: 120px">{{date("d/m/Y H:i:s",strtotime($item->fecha))}}</td>
                    <td style="width: 100px">{{strtoupper($item->trabajador->persona->nombre)}}</td>
                    <td style="width: 300px">{{$item->cliente->persona->nombre}}</td>
                    <td style="width: 80px">{{$item->moneda}} {{$item->total}}</td>
                    <td>{{$item->estado}}</td>
                </tr>
            @endforeach
        </table>
    </div>
    <p>Total: S/ {{$suma}}</p>
</div>

</body>
</html>

<style>

    .leyenda{
        width: 150mm;
        left: 25mm;
        position: absolute;
        bottom:-16mm;
        text-align: center;
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
        font-size: 7pt;
    }

    .table-header td{
        border-bottom: 1px solid black;
        margin: 0;
    }

    .logo img{
        width: 23mm;
        margin: 0;
    }
    .info-emisor .texto{
        width: 75mm;
        text-align: center;
        position: absolute;
        left: 25mm;
        top:12mm;
    }

    .info-usuario p{
        line-height: 4mm;
    }
    .items{
        width: 188mm;
        margin-top: 5mm;
    }
    table{
        margin: 0;
        padding: 0;
    }

    ul{
        list-style-type: circle;
    }
    li {
        padding-left: 1.3em ;
    }

    li:before {
        content: "j";
        display: inline-block;
        margin-left: -1.3em;
        width: 1.3em;
    }
</style>