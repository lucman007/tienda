@php /** Modelo 6 - habilitado**/@endphp
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
    <div class="borde info-emisor">
        @if($emisor->logo)
            <div class="logo">
                <img src="{{'images/'.$emisor->logo}}">
            </div>
        @endif
        <div class="texto">
            <p><span><strong>{{$emisor->razon_social}}</strong><br><strong>R.U.C.: {{$emisor->ruc}}</strong></span><br>{{$emisor->direccion}}, {{$emisor->urbanizacion}}, {{$emisor->provincia}},
                {{$emisor->departamento}}, {{$emisor->distrito}} <br> {{$emisor->telefono_1}}/{{$emisor->email}}<br>{{json_decode(cache('config')['mail_contact'], true)['website']}}
                <br><strong>{{$emisor->texto_publicitario}}</strong></p>
        </div>
    </div>
    <div class="titulo_comprobante">
        <h3><span>COTIZACIÓN N° {{$presupuesto->correlativo}}</span></h3>
        <p>FECHA: {{ date('d/m/Y',strtotime($presupuesto->fecha)) }} HORA: {{ date('g:i A',strtotime($presupuesto->fecha)) }}</p>
    </div>
</div>
<div class="body">
    <div class="info-usuario">
        <table cellpadding="0">
            <tr>
                <td class="bg-#e30613"><strong>CLIENTE:</strong></td>
                <td colspan="3" style="width:163mm">{{$usuario->persona->nombre}}</td>
            </tr>
            <tr>
                <td class="bg-#e30613"><strong>DIRECCIÓN:</strong></td>
                <td colspan="3" style="width:163mm">{{ $usuario->persona->direccion }}</td>
            </tr>
            <tr>
                <td class="bg-#e30613"><strong>RUC:</strong></td>
                <td style="width: 45mm">{{ $usuario->num_documento }}</td>
                <td class="bg-#e30613"><strong>REFERENCIA:</strong></td>
                <td style="width: 45mm" >{{ $presupuesto->referencia }}</td>
            </tr>
            <tr>
                <td class="bg-#e30613"><strong>ATENCIÓN:</strong></td>
                <td style="width: 45mm">{{ $presupuesto->atencion }}</td>
                <td  class="bg-#e30613"><strong>VALIDEZ:</strong></td>
                <td style="width: 45mm">{{ $presupuesto->validez }} días</td>
            </tr>
        </table>
    </div>
    <p>ATENDIENDO A SU SOLICITUD, ENVIAMOS NUESTRA COTIZACIÓN POR LOS SIGUIENTES PRODUCTOS</p>
    @php
        $i=1;
    @endphp
        <table class="items" cellpadding="0">
            <thead>
                <tr class="table-header">
                    <td>ITEM</td>
                    <td>DESCRIPCIÓN</td>
                    <td style="text-align: center">CANT.</td>
                    <td style="text-align: center">UM</td>
                    <td style="text-align: center">P.UNIT.</td>
                    <td style="text-align: center">TOTAL</td>
                </tr>
            </thead>
            <tbody>
            @foreach($presupuesto['productos'] as $item)
                <tr>
                    <td style="width: 5mm; border-left: 0.5px solid black; border-right: 0.5px solid black; @if(count($presupuesto->productos)==$i) border-bottom: 1px solid black; border-right: 1px solid black; border-left: 1px solid black;@endif">{{$i}}</td>
                    <td style="width: 79mm; border-right: 0.5px solid black; @if(count($presupuesto->productos)==$i) border-bottom: 1px solid black; border-right: 1px solid black; @endif">
                        <strong>{{$item->nombre}}</strong><br> {!!$item->detalle['descripcion']!!} @if($item->detalle['descuento']>0) <br> <strong>Descuento: </strong>{{$presupuesto->moneda}} {{$item->monto_descuento}} @endif
                    </td>
                    <td style="width: 10mm; border-right: 0.5px solid black; text-align: center; @if(count($presupuesto->productos)==$i) border-bottom: 1px solid black; border-right: 1px solid black;@endif">{{$item->detalle['cantidad']}}</td>
                    <td style="width: 8mm; border-right: 0.5px solid black; text-align: center; @if(count($presupuesto->productos)==$i) border-bottom: 1px solid black; border-right: 1px solid black;@endif">{{explode('/',$item->unidad_medida)[1]}}</td>
                    <td style="width: 10mm; border-right: 0.5px solid black; text-align: right; @if(count($presupuesto->productos)==$i) border-bottom: 1px solid black; border-right: 1px solid black;@endif">{{number_format($item->monto, 3)}}</td>
                    <td style="width: 10mm; border-right: 0.5px solid black; text-align: right; @if(count($presupuesto->productos)==$i) border-bottom: 1px solid black; border-right: 1px solid black;@endif">{{number_format($item->total,2)}}</td>
                </tr>
                @php
                    $i++;
                @endphp
            @endforeach
            <tr>
                <td style="border: 1px solid black;" colspan="4"><strong>SON: {{$presupuesto->leyenda}}</strong></td>
                <td style="border: 1px solid black; border-left: 1px solid black">SUBTOTAL</td>
                <td style="text-align: right; border: 1px solid black">{{$presupuesto->moneda}} {{number_format($presupuesto->presupuesto/1.18,2)}}</td>
            </tr>
            <tr>
                <td style="border-right: 0.5px solid black" colspan="4"></td>
                <td style="border: 1px solid black">IGV 18%</td>
                <td style="text-align: right; border: 1px solid black">{{$presupuesto->moneda}} {{number_format($presupuesto->presupuesto-($presupuesto->presupuesto/1.18),2)}}</td>
            </tr>
            <tr>
                <td style="border-right: 0.5px solid black" colspan="4"></td>
                <td  style="border: 1px solid black">TOTAL</td>
                <td style="text-align: right; border: 1px solid black">{{$presupuesto->moneda}} {{$presupuesto->presupuesto}}</td>
            </tr>
            @if(trim($presupuesto->observaciones)!='')
                <tr>
                    <td colspan="6" style="margin-top: 5mm">
                        Observación: {{$presupuesto->observaciones}}
                    </td>
                </tr>
            @endif
            @if($presupuesto->descuento > 0)
                <tr>
                    <td colspan="4">
                        <br>Descuento global: {{$presupuesto->descuento_global}}
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    <p></p>
    <table class="footer">
        <tr>
            <td class="footer-l">
                <p><strong>CONDICIONES COMERCIALES</strong><br><br>
                    <strong>Forma de pago:</strong> {{$presupuesto->condicion_pago}} <br>
                    <strong>Tiempo de entrega:</strong> {{$presupuesto->tiempo_entrega}} <br>
                    <strong>Garantía: </strong>{{$presupuesto->garantia}} <br>
                    <strong>Moneda:</strong>
                    @if($presupuesto->moneda=='S/')
                        SOLES <br>
                    @else
                        DÓLARES <br>
                    @endif
                    <strong>Lugar de entrega:</strong> {{$presupuesto->lugar_entrega}} <br>
                    <strong>Contacto:</strong> {{$presupuesto->contacto}} <br>
                    <strong>Teléfonos:</strong> {{$presupuesto->telefonos}} <br><br>
                    <strong>Cta. BBVA CONTINENTAL:</strong> {{$emisor->cuenta_1}} <br>
                    <strong>Cta. BCP:</strong> {{$emisor->cuenta_2}}  <br>
                    <strong>Cta. detracciones:</strong> {{$emisor->cuenta_detracciones}}
                </p>
            </td>
            <td style="width: 1%"></td>
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
        padding: 20px;
    }


    table{
        margin: 0;
        padding: 0;
    }
    .info-usuario table{
        border-collapse: collapse;
    }
    .info-usuario table td{
        border: 1px solid black;
        padding: 1.5mm 3mm;
    }
    .table-header td{
        padding: 1.5mm 3mm;
        color: white;
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
        height: 30mm;
        float: left;
    }

    .header .info-ruc{
        position: absolute;
        right: 0;
        text-align: center;
        height: 20mm;
        padding: 25px 50px;
    }

    .header .info-emisor{
        width: 191mm;
        height: 20mm;
        padding: 20px 25px 20px 0;
    }
    .header .info-emisor p span{
        font-size: 11pt;
    }
    .header .info-emisor .logo{
        width: 80mm;
    }
    .header .info-emisor .logo img{
        width: 75mm;
        text-align: center;
        margin-left: 5mm;
    }
    .header .info-emisor .texto{
        width: 95mm;
        text-align: center;
        position: absolute;
        right: 10mm;
        top:3mm;
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
        height: 20mm;
    }
    .body .info-usuario p{
        line-height: 4mm;
    }

    .body .items {
        width: 250mm;
        margin-top: 2mm;
        border-collapse:collapse;
    }
    .body .items thead td{
        background: #e30613;
    }

    .items tbody td{
        padding: 1.5mm 3mm;
        border-bottom: 0;
    }

    /*.body .items td{
        border: none;
    }
    .body .items thead tr{
        background: #e30613;
    }
    .body .items thead td{
    }
    .items tbody td{
        border-left: 0.5px solid black;
        padding: 1.5mm 3mm;
    }*/
    .footer{
        width: 200mm;
        height: 20mm;
    }
    .footer .footer-l{
        width: 80%;
    }

    .leyenda{
        width: 200mm;
        margin-top: 3mm;
        text-align: center;
    }
    .titulo_comprobante{
        text-align: center;
        margin-top: 5mm;
    }
    .titulo_comprobante h3{
        font-size: 17pt;
    }
    .titulo_comprobante p{
        margin-top: 1mm;
        padding: 0;
    }
    td.bg-#e30613{
        background: #e30613;
        color: white;
    }

</style>