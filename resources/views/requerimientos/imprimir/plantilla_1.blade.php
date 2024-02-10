@php /** Modelo 1 - habilitado**/@endphp
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
        <div class="texto">
            <p>R.U.C.: {{$emisor->ruc}}<br>{{$emisor->direccion}}, {{$emisor->urbanizacion}}, {{$emisor->provincia}},
                {{$emisor->departamento}}, {{$emisor->distrito}} <br> {{$emisor->telefono_1}} / {{$emisor->email}}<br>
            </p>
        </div>
    </div>
    <div class="info-ruc">
        <h3 class="titulo_comprobante"><span>ORDEN DE COMPRA<br> N° {{$requerimiento['correlativo']}}</span></h3>
    </div>
</div>
<div class="body">
    <div class="line-bottom info-usuario">
        <table cellpadding="0">
            <tr>
                <td style="width: 20mm"><strong>Fecha:</strong></td>
                <td style="width: 50mm">{{ date("d/m/Y",strtotime($requerimiento->fecha_requerimiento)) }}</td>
            </tr>
            <tr>
                <td><strong>Proveedor:</strong></td>
                <td>{{$usuario->persona->nombre}}</td>
            </tr>
            <tr>
                <td><strong>Dirección:</strong></td>
                <td style="width: 120mm">{{ $usuario->persona->direccion }}</td>
            </tr>
            <tr>
                <td><strong>Ruc:</strong></td>
                <td>{{ $usuario->num_documento }}</td>
            </tr>
            <tr>
                <td><strong>Contacto:</strong></td>
                <td>{{ mb_strtoupper($requerimiento->atencion) }}</td>
            </tr>
        </table>
    </div>
    @php($i=1)
    <table class="items" cellpadding="0">
        <thead>
        <tr class="table-header">
            <td>#</td>
            <td>CÓD.</td>
            <td>DESCRIPCIÓN</td>
            <td>CANTIDAD</td>
            <td>UND</td>
            <td>P. UNITARIO</td>
            <td>TOTAL</td>
        </tr>
        </thead>
        <tbody>
        @foreach($requerimiento['productos'] as $item)
            <tr>
                <td style="width: 5mm">{{$i++}}</td>
                <td style="width: 15mm">{{$item->cod_producto}}</td>
                <td style="width: 75mm"><strong>{{$item->nombre}}</strong><br> {!!$item->detalle['descripcion']!!}</td>
                <td style="width: 20mm">{{floatval($item->detalle['cantidad'])}}</td>
                <td style="width: 10mm">{{explode('/',$item->unidad_medida)[1]}}</td>
                <td style="width: 20mm; text-align: right">{{number_format($item->monto, 2)}}</td>
                <td style="width: 20mm; text-align: right">{{number_format($item->total,2)}}</td>
            </tr>
        @endforeach
        @if(trim($requerimiento->observacion)!='')
            <tr>
                <td><br></td>
            </tr>
            <tr>
                <td colspan="7" style="margin-top: 5mm;width: 170mm">
                    Observación: {{$requerimiento->observacion}}
                </td>
            </tr>
        @endif
        </tbody>
    </table>
    <table class="footer">
        <tr>
            <td class="footer-l">
            </td>
            <td style="width: 1%"></td>
            <td class="footer-r">
                <table>
                    <tr>
                        <td><strong>Subtotal:</strong></td>
                        <td style="width: 28mm; text-align: right">{{$requerimiento->moneda}} {{number_format($requerimiento->total_compra/1.18,2)}}</td>
                    </tr>
                    <tr>
                        <td><strong>Total IGV 18%:</strong></td>
                        <td style="width: 28mm; text-align: right">{{$requerimiento->moneda}} {{number_format($requerimiento->total_compra-($requerimiento->total_compra/1.18),2)}}</td>
                    </tr>
                    <tr>
                        <td><strong>Importe total:</strong></td>
                        <td style="width: 28mm; text-align: right">{{$requerimiento->moneda}} {{$requerimiento->total_compra}}</td>
                    </tr>
                </table>
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

    .line-bottom{
        border-bottom: 1px solid #CCC;
        padding: 20px;
    }

    table{
        margin: 0;
        padding: 0;
    }
    .table-header td{
        border-bottom: 1px solid #CCC;
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
        height: 40mm;
        float: left;
        margin-bottom: 10mm;
    }

    .header .info-ruc{
        position: absolute;
        right: 15mm;
        top:15mm;
        text-align: center;
        padding: 20px 45px;
        border: 3px solid black;
        border-radius: 10px;
    }

    .header .info-emisor{
        position: absolute;
        width: 90mm;
        height: 30mm;
        padding: 25px 20px;
        top: 0
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
        width: 95mm;
        position: absolute;
        right: 0;
        top:35mm;
    }

    .body{
        position: relative;
        width: 200mm;
        height: 100mm;
        float: left;
    }

    .body .info-usuario{
        width: 188mm;
        height: 20mm;
    }
    .body .info-usuario p{
        line-height: 4mm;
    }

    .body .items {
        width: 200mm;
        margin-top: 5mm;
        position: relative;
        border-bottom: 1px solid #CCC;
        padding: 20px 32px;
    }
    .footer{
        width: 200mm;
        height: 20mm;
        margin-top: 5mm;
    }
    .footer .footer-l{
        width: 60%;
        border-right: 1px solid #CCC;
        padding: 20px 32px 25px 32px;
    }
    .footer .footer-r{
        width: 39%;
        padding: 20px 32px;
    }

    .leyenda{
        margin-top: 5mm;
        padding-bottom: 3mm;
        margin-left: 8mm;
    }
    .atentamente{
        position: absolute;
        bottom: 10mm;
    }

</style>