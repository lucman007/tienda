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
    <div class="info-emisor">
        @if($emisor->logo)
            <div class="logo">
                <img src="{{'images/'.$emisor->logo}}">
            </div>
        @endif
    </div>
    <div class="info-ruc">
        <h3 class="titulo_comprobante"><span>ORDEN DE PRODUCCIÓN</span></h3>
        <h3>N° {{$produccion['correlativo']}}</h3>
    </div>
</div>
<div class="body">
    <div class="borde info-usuario">
        <table cellpadding="0">
            <tr>
                <td><strong>Fecha:</strong></td>
                <td>{{date("d/m/Y",strtotime($produccion->fecha_emision))}}</td>
                <td><strong>Editado por:</strong> {{$produccion->editado_por}}</td>
            </tr>
            <tr>
                <td><strong>Fecha de entrega:</strong></td>
                <td>{{date("d/m/Y",strtotime($produccion->fecha_entrega))}}</td>
                <td><strong>Fabricado por:</strong> {{$produccion->fabricado_por}}</td>
            </tr>
            <tr>
                <td><strong>Cliente:</strong></td>
                <td colspan="2" style="width:150mm">{{$usuario->persona->nombre}}</td>
            </tr>
            <tr>
                <td><strong>Orden de compra:</strong></td>
                <td colspan="2" style="width:150mm">{{$produccion->num_oc}}</td>
            </tr>
            <tr>
                <td><strong>Prioridad:</strong></td>
                <td colspan="2">@if($produccion->prioridad==0)
                        NORMAL
                    @else
                        ALTA
                    @endif</td>
            </tr>
        </table>
    </div>
    @php($i=1)
        <table class="items" cellpadding="0">
            <thead>
                <tr class="table-header">
                    <td>#</td>
                    <td>Código</td>
                    <td>Producto</td>
                    <td>Características</td>
                    <td>Cantidad</td>
                </tr>
            </thead>
            <tbody>
            @foreach($produccion['productos'] as $item)
                <tr>
                    <td style="width: 5mm">{{$i++}}</td>
                    <td style="width: 25mm">{{$item->detalle->codigo_fabricacion}}</td>
                    <td style="width: 53mm">{{$item->nombre}}</td>
                    <td style="width: 75mm">{!!$item->detalle['descripcion']!!}</td>
                    <td style="width: 15mm">{{$item->detalle['cantidad']}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    <table class="footer">
        <tr>
            <td style="padding: 5px">
                <p><strong>Observaciones:</strong>
                    {{ $produccion['observacion']}}
                </p>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
<style>

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
        font-size: 8pt;
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

    .hash{
        width: 115mm;
        height: 20mm;
        padding-left: 20px;
    }
    .qr{
        width: 25mm;
    }
    .impuesto{
        width: 60mm;
        position: absolute;
        right: 0;
        top: 0;
    }

    .header{
        position: relative;
        width: 200mm;
        height: 20mm;
        float: left;
    }

    .header .info-ruc{
        position: absolute;
        right: 50mm;
        text-align: center;
        height: 10mm;
        padding: 25px 50px;
    }

    .header .info-emisor{
        width: 80mm;
        height: 10mm;
        padding: 25px 20px;
    }
    .header .info-emisor .logo{
        width: 30mm;
    }
    .header .info-emisor .logo img{
        width: 45mm;
        text-align: center;
        margin-left: -5mm;
    }
    .header .info-emisor .texto{
        width: 75mm;
        text-align: center;
        position: absolute;
        right: 5mm;
        top:5mm;
    }

    .body{
        position: relative;
        width: 200mm;
        height: 100mm;
        float: left;
    }

    .body .info-usuario{
        width: 188mm;
        height: 15mm;
    }
    .body .info-usuario p{
        line-height: 4mm;
    }

    .body .items {
        width: 200mm;
        margin-top: 5mm;
        position: relative;
        border: 1px solid black;
        border-radius: 5px;
        padding: 20px 32px;
    }
    .footer{
        width: 200mm;
        height: 20mm;
        margin-top: 5mm;
    }
    .footer .footer-l{
        width: 100%;
        padding: 20px 32px 25px 32px;
    }
    .leyenda{
        width: 200mm;
        margin-top: 3mm;
        text-align: center;
    }


</style>