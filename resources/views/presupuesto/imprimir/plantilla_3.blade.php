@php /** Modelo 03 - habilitado**/@endphp
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
            <p><strong>{{$emisor->razon_social}}</strong><br>
                {{mb_strtoupper($emisor->texto_publicitario)}}</p>
        </div>
    </div>
    <div class="borde info-ruc">
        <h3>R.U.C.: {{$emisor->ruc}}</h3>
        <h3 class="titulo_comprobante"><span>COTIZACIÓN</span></h3>
        <h3>N° {{$presupuesto['correlativo']}}</h3>
    </div>
</div>
<div class="body">
    <div class="borde info-usuario">
        <table cellpadding="0">
            <tr>
                <td><strong>Fecha:</strong></td>
                <td>{{ date("d/m/Y",strtotime($presupuesto->fecha)) }}</td>
            </tr>
            <tr>
                <td><strong>Razón social:</strong></td>
                <td style="width:120mm">{{$usuario->persona->nombre}}</td>
            </tr>
            <tr>
                <td><strong>Dirección:</strong></td>
                <td style="width:120mm">{{ $usuario->persona->direccion }}</td>
            </tr>
            <tr>
                <td><strong>Ruc:</strong></td>
                <td>{{ $usuario->num_documento }}</td>
            </tr>
            <tr>
                <td><strong>Atención:</strong></td>
                <td>{{ mb_strtoupper($presupuesto->atencion) }}</td>
            </tr>
            <tr>
                <td><strong>Validez:</strong></td>
                <td>{{ $presupuesto->validez }} días</td>
            </tr>
            @if($presupuesto->exportacion)
                <tr>
                    <td><strong>Tipo venta:</strong></td>
                    <td>Exportación</td>
                    <td><strong>Incoterm:</strong> {{$presupuesto->incoterm}}</td>
                </tr>
            @endif
        </table>
    </div>
    @php $i=1 @endphp
        <table class="items" cellpadding="0">
            <thead>
                <tr class="table-header">
                    <td>Item</td>
                    <td>Código</td>
                    <td>Descripción</td>
                    <td>Cantidad</td>
                    <td>Und</td>
                    <td>P. unitario</td>
                    <td>Dscto.</td>
                    <td>Importe</td>
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
                    <strong>Condiciones de pago:</strong> {{$presupuesto->condicion_pago}} <br>
                    <strong>Tiempo de entrega:</strong> {{$presupuesto->tiempo_entrega}} <br>
                    <strong>Garantía:</strong> {{$presupuesto->garantia}} <br>
                    <strong>Moneda:</strong>
                    @if($presupuesto->moneda=='S/')
                        SOLES <br>
                    @else
                        DÓLARES <br>
                    @endif
                    <strong>Impuesto:</strong> {{$presupuesto->impuesto}} <br>
                    <strong>Lugar de entrega:</strong> {{$presupuesto->lugar_entrega}} <br>
                    <strong>Contacto:</strong> {{$presupuesto->contacto}} <br>
                    <strong>Teléfonos:</strong> {{$presupuesto->telefonos}} <br>
                    @if($presupuesto->exportacion)
                        @foreach($emisor->cuentas as $cuenta)
                            @if($cuenta['banco'] == 8)
                                <strong>Beneficiario : </strong> {{$emisor->razon_social}}<br>
                                <strong>Código SWIFT:</strong> {{$cuenta['cci']}} <br>
                                <strong>Cuenta N°:</strong> {{$cuenta['cuenta']}}<br>
                                <strong>Banco:</strong> {{$cuenta['descripcion']}} <br><br>
                            @endif
                        @endforeach
                    @else
                        <?php $bancos = \sysfact\Http\Controllers\Helpers\DataGeneral::getBancos(); ?>
                        @foreach($emisor->cuentas as $key=>$cuenta)
                            <?php $index = array_search($cuenta['banco'], array_column($bancos,'num_val')); ?>
                            @if($key === 0)
                                @if($cuenta['cuenta'] != '')
                                    <strong>N° de Cta. detracciones:</strong> {{$cuenta['cuenta']}} {{$cuenta['cci']?'- CCI: '.$cuenta['cci']:''}} <br>
                                @endif
                            @else
                                @if($cuenta['banco'] != 8)
                                    <strong>N° de Cta. {{$bancos[$index]['label']}} ({{$cuenta['moneda']=='USD'?'Dólares':'Soles'}}):</strong> {{$cuenta['cuenta']}} {{$cuenta['cci']?'- CCI: '.$cuenta['cci']:''}} {{isset($cuenta['descripcion'])&&$cuenta['descripcion']!=''?'('.$cuenta['descripcion'].')':''}} <br>
                                @endif
                            @endif
                        @endforeach
                    @endif
                </p>
            </td>
            <td style="width: 1%"></td>
            <td class="footer-r">
                <table>
                    @if($presupuesto->exportacion)
                        <tr>
                            <td>Flete:</td>
                            <td style="width: 28mm; text-align: right">{{$presupuesto->moneda}} {{number_format($presupuesto->flete,2)}}</td>
                        </tr>
                        <tr>
                            <td>Seguro:</td>
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
                        <td style="width: 28mm; text-align: right">{{$presupuesto->moneda}} {{number_format($presupuesto->presupuesto, 2)}}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td style="width: 25mm"></td>
            <td class="leyenda">
                {{$emisor->direccion}}, {{$emisor->urbanizacion}}, {{$emisor->provincia}},
                {{$emisor->departamento}}, {{$emisor->distrito}} <br> {{$emisor->telefono_1}}/{{$emisor->email}}
                {{$config['website']}}
            </td>
            <td style="width: 25mm"></td>
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
        width: 112mm;
        height: 25mm;
        padding: 0 20px 25px 20px;
    }
    .header .info-emisor .logo{
        width: 112mm;
        text-align: center;
    }
    .header .info-emisor .logo img{
        width: 85mm;
        text-align: center;
        align-items: center;
    }
    .header .info-emisor .texto{
        width: 112mm;
        text-align: center;
    }

    .header .info-emisor .texto p{
        margin-top: 0;
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
        width: 60%;
        border: 1px solid black;
        border-radius: 5px;
        padding: 20px 32px 25px 32px;
    }
    .footer .footer-r{
        width: 39%;
        border: 1px solid black;
        border-radius: 5px;
        padding: 20px 32px;
    }

    .leyenda{
        width: 150mm;
        margin-top: 3mm;
        text-align: center;
    }


</style>