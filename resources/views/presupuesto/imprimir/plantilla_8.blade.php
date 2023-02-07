@php /** Modelo 08 - habilitado**/@endphp
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
    <div class="box-header" style="width: 200mm;  height: 18mm; border-bottom:1px solid black; margin-top: 10mm; position: relative">
        @if($emisor->logo)
            <div class="logo" style="position: absolute; width: 250px; top:-13mm">
                <img src="{{'images/'.$emisor->logo}}" style="width: 100%">
            </div>
        @endif
        <div class="fecha" style="position: absolute; right: 3mm">
            <p style="text-align: right">FECHA: {{ date('d/m/Y',strtotime($presupuesto->fecha)) }} HORA: {{ date('g:i A',strtotime($presupuesto->fecha)) }}</p>
            <p style="font-size: 14px; text-align: right"><strong>Cotización N° {{$presupuesto['correlativo']}}</strong></p>
        </div>
    </div>
</div>
<div style="width: 190mm; margin-bottom: 5mm">
    <table cellpadding="0">
        <tr>
            <td>
                <table>
                    <tr>
                        <td colspan="3"><strong style="text-decoration: underline">PROVEEDOR:</strong></td>
                    </tr>
                    <tr>
                        <td colspan="3"><strong>{{$emisor->razon_social}}</strong></td>
                    </tr>
                    <tr>
                        <td>{{$emisor->ruc}}</td>
                    </tr>
                    <tr>
                        <td>{{$emisor->email}} / {{$emisor->telefono_1}}</td>
                    </tr>
                    <tr>
                        <td style="width: 65mm" colspan="3">{{$emisor->direccion}}, {{$emisor->urbanizacion}}, {{$emisor->provincia}},
                            {{$emisor->departamento}}, {{$emisor->distrito}}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 10mm">

            </td>
            <td>
                <table>
                    <tr>
                        <td colspan="3"><strong style="text-decoration: underline">CLIENTE:</strong></td>
                    </tr>
                    <tr>
                        <td colspan="3"><strong>{{$usuario->persona->nombre}}</strong></td>
                    </tr>
                    <tr>
                        <td>{{ $usuario->num_documento }}</td>
                    </tr>
                    <tr>
                        <td>{{$usuario->correo}} / {{$usuario->telefono}}</td>
                    </tr>
                    <tr>
                        <td style="width: 85mm" colspan="3">{{ $usuario->persona->direccion }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <table>
                    <tr>
                        <td colspan="3"><strong style="text-decoration: underline">CONDICIONES COMERCIALES:</strong></td>
                    </tr>
                    <tr>
                        <td colspan="3">Moneda:
                            <strong>
                                @if($presupuesto->moneda=='S/')
                                    SOLES
                                @else
                                    DÓLARES
                                @endif
                            </strong>
                        </td>
                    </tr>
                    <tr>
                        <td>Atención: <strong>{{ mb_strtoupper($presupuesto->atencion) }} </strong></td>
                    </tr>
                    <tr>
                        <td>Forma de pago: <strong>{{strtoupper($presupuesto->condicion_pago)}}</strong></td>
                    </tr>
                    <tr>
                        <td>Tiempo de entrega: <strong>{{strtoupper($presupuesto->tiempo_entrega)}}</strong></td>
                    </tr>
                    <tr>
                        <td>Punto de entrega: <strong>{{mb_strtoupper($presupuesto->lugar_entrega)}}</strong></td>
                    </tr>
                </table>
            </td>
            <td style="width: 10mm">

            </td>
            <td>
            </td>
        </tr>
    </table>
</div>
<div class="div-table-header">
</div>
<div class="body">
    @php($i=1)
        <table class="items" cellpadding="0">
            <thead>
                <tr class="table-header" style="color: white">
                    <td><strong>#</strong></td>
                    <td><strong>CONCEPTO</strong></td>
                    <td><strong>CANT.</strong></td>
                    <td><strong>UND</strong></td>
                    <td><strong>P. UNITARIO</strong></td>
                    <td><strong>DSCTO</strong></td>
                    <td style="text-align: right"><strong>IMPORTE</strong></td>
                </tr>
            </thead>
            <tbody>
            @foreach($presupuesto['productos'] as $item)
                <tr class="items-tr">
                    <td style="width: 5mm">{{$i++}}</td>
                    <td style="width: 85mm"><strong>{{$item->nombre}}</strong><br> {!!$item->detalle['descripcion']!!}</td>
                    <td style="width: 20mm">{{floatval($item->detalle['cantidad'])}}</td>
                    <td style="width: 10mm">{{explode('/',$item->unidad_medida)[1]}}</td>
                    <td style="width: 20mm; text-align: right">@if(!$presupuesto->ocultar_precios){{number_format($item->monto, 3)}}@endif</td>
                    <td style="width: 15mm; text-align: right">@if(!$presupuesto->ocultar_precios){{$item->monto_descuento}}@endif</td>
                    <td style="width: 20mm; text-align: right">@if(!$presupuesto->ocultar_precios){{number_format($item->total,2)}}@endif</td>
                </tr>
            @endforeach
            @if(trim($presupuesto->observaciones)!='')
                <tr>
                    <td><br></td>
                </tr>
                <tr>
                    <td colspan="7" style="margin-top: 5mm">
                        Observación: {{$presupuesto->observaciones}}
                    </td>
                </tr>
            @endif
            @if($presupuesto->descuento > 0)
                <tr>
                    <td colspan="7" style="margin-top: 5mm">
                        <br>Descuento global: {{$presupuesto->descuento_global}}
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
</div>
<table class="footer">
    <tr>
        <td class="footer-l">
            <p><strong>VALIDEZ DE COTIZACIÓN: {{$presupuesto->validez}} DÍAS</strong>
                <br>
                @if($presupuesto->exportacion)
                    <strong>Tipo venta:</strong> Exportación <br>
                    <strong>Incoterm:</strong> {{$presupuesto->incoterm}}
                @endif
                <br>
                @if($presupuesto->exportacion)
                    <strong>Beneficiario : </strong> <br>
                    <strong>Código SWIFT:</strong><br>
                    <strong>Cuenta N°:</strong>  <br>
                    <strong>Banco:</strong> <br>
                @else

                    <strong>Cta. detracciones:</strong> {{$emisor->cuenta_detracciones}} <br>
                    <strong>Cta. Soles:</strong> {{$emisor->cuenta_1}} <br>
                    <strong>Cta. Dólares:</strong> {{$emisor->cuenta_2}} <br>
                @endif
            </p>
        </td>
        <td class="footer-r">
            <table style="background: #dfdfdf;">
                @if($presupuesto->exportacion)
                    <tr>
                        <td><strong>Flete:</strong></td>
                        <td style="width: 28mm; text-align: right">{{$presupuesto->moneda}} {{number_format($presupuesto->flete,2)}}</td>
                    </tr>
                    <tr>
                        <td><strong>Seguro:</strong></td>
                        <td style="width: 28mm; text-align: right">{{$presupuesto->moneda}} {{number_format($presupuesto->seguro,2)}}</td>
                    </tr>
                @else
                    @if(!$presupuesto->ocultar_impuestos)
                    <tr>
                        <td><strong>Subtotal:</strong></td>
                        <td style="width: 28mm; text-align: right">{{$presupuesto->moneda}} {{number_format($presupuesto->presupuesto/1.18,2)}}</td>
                    </tr>
                    <tr>
                        <td><strong>Total IGV 18%:</strong></td>
                        <td style="width: 28mm; text-align: right">{{$presupuesto->moneda}} {{number_format($presupuesto->presupuesto-($presupuesto->presupuesto/1.18),2)}}</td>
                    </tr>
                    @endif
                @endif
                <tr>
                    <td><strong>Total:</strong></td>
                    <td style="width: 28mm; text-align: right">{{$presupuesto->moneda}} {{number_format($presupuesto->presupuesto,2)}}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
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
        font-size: 7pt;
    }

    .div-table-header{
        width: 190mm; background: #ff4809; padding: 2mm 5mm; text-align: center; margin-top: 5mm; height: 5mm
    }

    .line-bottom{
        border-bottom: 1px solid #CCC;
        padding: 20px;
    }

    table{
        margin: 0;
        padding: 0;
        border-collapse: collapse;
    }
    .items-tr td{
        border-bottom: 1px solid #575757;
        padding: 10px 0;
    }
    .table-header td{
        margin: 0;
        height: 10mm;
        background: none;
        border-bottom:none;
        padding: 0;
    }

    .impuesto{
        width: 60mm;
        position: absolute;
        right: 0;
        top: 0;
    }

    .header{
        position: relative;
        width: 190mm;
        height: 25mm;
        margin-bottom: 4mm;
    }


    .header .info-emisor{
        width: 90mm;
        padding: 25px 20px;
    }
    .header .info-emisor .logo{
        width: 40mm;
    }
    .header .info-emisor .logo img{
        width: 70mm;
        text-align: center;
        margin-left: -5mm;
    }
    .header .info-emisor .texto{
        width: 95mm;
        right: 0;
        top:25mm;
    }

    .body{
        position: relative;
        width: 200mm;
    }

    .body .items {
        width: 200mm;
        position: relative;
        padding: 0 32px 20px 32px;
        margin-top: -25px;
    }
    .footer{
        width: 220mm;
        height: 20mm;
        margin-top: 5mm;
        margin-bottom: 10mm;
    }
    .footer .footer-l{
        width: 60%;
        padding: 0 5mm 0 5mm;
    }
    .footer .footer-r{
        width: 30%;
    }
    .footer-r td{
        padding: 2mm 3mm;
    }


</style>