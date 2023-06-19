@php /** Modelo 13 - habilitado**/@endphp
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
            <p><strong>{{mb_strtoupper($emisor->texto_publicitario)}}</strong> <br>  R.U.C.: {{$emisor->ruc}}<br>{{$emisor->direccion}}, {{$emisor->urbanizacion}}, {{$emisor->provincia}},
                {{$emisor->departamento}}, {{$emisor->distrito}} <br> {{$emisor->telefono_1}} / {{$emisor->email}}</p>
        </div>
    </div>
    <div class="info-ruc">
        <h3 class="titulo_comprobante"><span>COTIZACIÓN <br> N° {{$presupuesto['correlativo']}}</span></h3>
    </div>
</div>
<div class="body">
    <div class="line-bottom info-usuario">
        <table cellpadding="0">
            <tr>
                <td style="width: 20mm"><strong>Fecha:</strong></td>
                <td style="width: 50mm">{{ date("d/m/Y",strtotime($presupuesto->fecha)) }}</td>
                <td style="width: 20mm"><strong>Validez:</strong></td>
                <td style="width: 90mm">{{$presupuesto->validez}} DÍAS</td>
            </tr>
            <tr>
                <td><strong>Razón social:</strong></td>
                <td colspan="3">{{$usuario->persona->nombre}}</td>
            </tr>
            <tr>
                <td><strong>Dirección:</strong></td>
                <td style="width: 120mm" colspan="3">{{ $usuario->persona->direccion }}</td>
            </tr>
            <tr>
                <td><strong>Ruc:</strong></td>
                <td>{{ $usuario->num_documento }}</td>
            </tr>
            @if($presupuesto->exportacion)
                <tr>
                    <td><strong>Tipo venta:</strong></td>
                    <td>Exportación</td>
                    <td><strong>Incoterm:</strong> {{$presupuesto->incoterm}}</td>
                </tr>
            @endif
            <tr>
                <td><strong>Atención:</strong></td>
                <td>{{ mb_strtoupper($presupuesto->atencion) }}</td>
            </tr>
        </table>
    </div>
    @php
        $i=1
    @endphp
        <table class="items" cellpadding="0">
            <thead>
                <tr class="table-header">
                    <td>#</td>
                    <td>CÓD.</td>
                    <td>DESCRIPCIÓN</td>
                    <td>CANTIDAD</td>
                    <td>UND</td>
                    <td>P. UNITARIO</td>
                    <td>DSCTO</td>
                    <td>IMPORTE</td>
                </tr>
            </thead>
            <tbody>
            @foreach($presupuesto['productos'] as $item)
                <tr>
                    <td style="width: 5mm">{{$i++}}</td>
                    <td style="width: 15mm">{{$item->cod_producto}}</td>
                    <td style="width: 65mm"><strong>{{$item->nombre}}</strong><br> {!!$item->detalle['descripcion']!!}</td>
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
                    <td colspan="8" style="margin-top: 5mm">
                        Observación: {{$presupuesto->observaciones}}
                    </td>
                </tr>
            @endif
            @if($presupuesto->descuento > 0)
                <tr>
                    <td colspan="8" style="margin-top: 5mm">
                        Descuento global: {{$presupuesto->descuento_global}}
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    <table class="footer">
        <tr>
            <td class="footer-l">
                <p>
                    <strong>Pago:</strong> {{strtoupper($presupuesto->condicion_pago)}} <br>
                    <strong>Tiempo de entrega:</strong> {{strtoupper($presupuesto->tiempo_entrega)}} <br>
                    <strong>Garantía:</strong> {{mb_strtoupper($presupuesto->garantia)}} <br>
                    <strong>Moneda: </strong>
                    @if($presupuesto->moneda=='S/')
                        SOLES <br>
                    @else
                        DÓLARES <br>
                    @endif
                    <strong>Impuesto:</strong> {{mb_strtoupper($presupuesto->impuesto)}} <br>
                    <strong>Punto de entrega:</strong> {{mb_strtoupper($presupuesto->lugar_entrega)}} <br>
                    @if($presupuesto->exportacion)
                        <strong>Beneficiario : </strong> LINE TECH EIRL<br>
                        <strong>Código SWIFT:</strong> BCPLPEPL <br>
                        <strong>Cuenta N°:</strong> 192-2669185-1-73 <br>
                        <strong>Banco:</strong> BANCO DE CREDITO DEL PERU <br>
                    @else
                        <strong>Cta. detracciones:</strong> {{$emisor->cuenta_detracciones}} <br>
                        <strong>Cta. Soles:</strong> {{$emisor->cuenta_1}} <br>
                        <strong>Cta. Dólares:</strong> {{$emisor->cuenta_2}} <br>
                    @endif
                </p>
            </td>
            <td style="width: 1%"></td>
            <td class="footer-r">
                <table>
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
    <table class="atentamente">
        <tr>
            <td>
                <p class="leyenda">
                    Sin otro particular, nos despedimos de Uds. <br><br>
                    Atentamente <br>
                    {{mb_strtoupper($presupuesto->contacto)==''?'Área de ventas':mb_strtoupper($presupuesto->contacto)}} <br>
                    Telf.: {{strtoupper($presupuesto->telefonos)}} <br>
                    {{$config['website']}}
                </p>
            </td>
        </tr>
    </table>
    @php
        $logos_ = json_decode(cache('config')['interfaz'], true)['buscador_productos_alt']??false;
    @endphp
    <table style="width: 200mm">
        <tr>
            <td>
                <span class="logo-footer-1"><img style="width: 100%" src="{{public_path('images/linetech/logos-linetech.png')}}" alt=""></span>
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
        height: 100mm;
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
        margin-bottom: 10mm;
    }
    .footer .footer-l{
        width: 60%;
        border-right: 1px solid #CCC;
        padding: 20px 32px 0px 32px;
    }
    .footer .footer-r{
        width: 39%;
        padding: 20px 32px;
    }

    .leyenda{
        margin-top: 5mm;
        padding-bottom: 3mm;
        margin-left: 8mm;
        text-align: center;
    }
    .atentamente{
        text-align: center;
        position: absolute;
        bottom: 10mm;
    }
    .atentamente td{
        width: 200mm;
    }

</style>