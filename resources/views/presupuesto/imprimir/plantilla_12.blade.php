@php /** Modelo 12 - habilitado**/@endphp
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
    <div class="header-ruc">
        <h3 style=""><strong>COTIZACIÓN</strong></h3>
        <h4 style="">{{$presupuesto['correlativo']}}</h4>
        <p>FECHA: {{ date('d/m/Y',strtotime($presupuesto->fecha)) }} HORA: {{ date('g:i A',strtotime($presupuesto->fecha)) }}</p>
    </div>
    @if($emisor->logo)
        <div class="logo" style="">
            <img src="{{'images/'.$emisor->logo}}">
        </div>
    @endif
    <div class="header-info">
        <p><strong style="font-size: 9pt;">{{$emisor->razon_social}} - R.U.C.: {{$emisor->ruc}}</strong>
            <br>
            {{$emisor->direccion}}, {{$emisor->urbanizacion}}, {{$emisor->provincia}},
            {{$emisor->departamento}}, {{$emisor->distrito}} <br> {{$emisor->telefono_1}} / {{$emisor->email}} <br>
            {{$emisor->texto_publicitario}}
        </p>
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
<div style="width: 200mm;">
    <img style="width: 100%" src="{{public_path('images/fade/logo-marcas-fade.jpg')}}">
</div>
<div class="div-table-header">
</div>
@php($i=1)
<table class="items" cellpadding="0" style="margin-left: 10mm">
    <thead>
    <tr class="table-header" style="color: black">
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
            <br>
            <p class="leyenda">
                Atentamente <br>
                {{mb_strtoupper($presupuesto->contacto)==''?'Área de ventas':mb_strtoupper($presupuesto->contacto)}} <br>
                Telf.: {{strtoupper($presupuesto->telefonos)}} <br>
                {{$config['website']}}
            </p>
        </td>
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
                    <td><strong>Importe total:</strong></td>
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
        width: 190mm;
        background: #00a34d;
        padding: 2mm 5mm;
        text-align: center;
        margin-top: 104mm;
        height: 5mm;
        position: absolute;
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

    .header-ruc{
        width: 260px;
        text-align: left;
        padding: 10px 0;
        float: left;
        display: inline;
        border-bottom: 2px solid #131313;
    }

    .logo{
        width: 200px;
        display: block;
        margin-left: 210px;
    }
    .logo img{
        width: 100%;
    }

    .header-ruc h3{
        font-size: 25px;
        margin-bottom:10px;
        padding: 0;
    }
    .header-ruc h3 span{
        font-size: 15px;
    }
    .header-ruc h4{
        font-size: 20px;
        margin: -10px;
        padding: 0;
        font-weight: 400;
    }
    .header .info-emisor .texto{
        width: 95mm;
        right: 0;
        top:25mm;
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