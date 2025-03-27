@php /** Modelo 14 - habilitado**/@endphp
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
@php
    $ancho_logo = json_decode(cache('config')['interfaz'], true)['ancho_logo']??'40';
@endphp
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
        <h3 class="titulo_comprobante">R.U.C.: {{$emisor->ruc}} <br><span>COTIZACIÓN </span><br> N° {{$presupuesto['correlativo']}}</h3>
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
    <div class="info-usuario" style="margin-top: 3mm;">
        <table cellpadding="0">
            <tr>
                <td class="bg-#e30613"><strong>TIEMPO DE ENTREGA:</strong></td>
                <td class="bg-#e30613" style="width: 35mm"><strong>LUGAR DE ENTREGA:</strong></td>
                <td class="bg-#e30613"><strong>VALIDEZ:</strong></td>
                <td class="bg-#e30613" style="width: 40mm"><strong>CONDICIÓN DE PAGO:</strong></td>
                <td class="bg-#e30613" style="width: 15mm"><strong>MONEDA:</strong></td>
            </tr>
            <tr>
                <td>{{strtoupper($presupuesto->tiempo_entrega)}}</td>
                <td>{{mb_strtoupper($presupuesto->lugar_entrega)}}</td>
                <td>{{ $presupuesto->validez }}</td>
                <td>{{strtoupper($presupuesto->condicion_pago)}}</td>
                <td>@if($presupuesto->moneda=='S/')
                            SOLES <br>
                        @else
                            DÓLARES <br>
                        @endif</td>
            </tr>
        </table>
    </div>
    @php
        $i=1
    @endphp
        <table class="items" cellpadding="0" style="margin-top: -4mm;">
            <thead>
                <tr class="table-header" border: 1px solid black>
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
                    <td>{{$i++}}</td>
                    <td style="width: 15mm">{{$item->cod_producto}}</td>
                    <td style="width: 60mm"><strong>{{$item->nombre}}</strong><br> {!!$item->detalle['descripcion']!!}</td>
                    <td>{{floatval($item->detalle['cantidad'])}}</td>
                    <td>{{explode('/',$item->unidad_medida)[1]}}</td>
                    <td style="text-align: right">@if(!$presupuesto->ocultar_precios){{number_format($item->monto, 3)}}@endif</td>
                    <td style="text-align: right">@if(!$presupuesto->ocultar_precios){{$item->monto_descuento}}@endif</td>
                    <td style="text-align: right">@if(!$presupuesto->ocultar_precios){{number_format($item->total,2)}}@endif</td>
                </tr>
            @endforeach
            @php
            $rows = 20;
            $num_items = count($presupuesto['productos']);
            $dif = ($rows - $num_items) >= 0 ? $rows - $num_items : 0;
            @endphp
            @for($i = 1; $i <= $dif; $i++)
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            @endfor
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
                    <strong>Garantía:</strong> {{mb_strtoupper($presupuesto->garantia)}} <br>
                    <strong>Impuesto:</strong> {{mb_strtoupper($presupuesto->impuesto)}} <br>
                </p>
                <div>
                    <p><strong>Cuentas bancarias:</strong></p>
                    @if($presupuesto->exportacion)
                        @foreach($emisor->cuentas as $cuenta)
                            @if($cuenta['banco'] == 8)
                                <strong>Beneficiario : </strong> {{$emisor->razon_social}}<br>
                                <strong>Código SWIFT:</strong> {{$cuenta['cci']}} <br>
                                <strong>Cuenta N°:</strong> {{$cuenta['cuenta']}}<br>
                                <strong>Banco:</strong> {{$cuenta['descripcion']}} <br>
                                @if(str_contains(app()->domain(),'linetech'))
                                    <strong>Tipo gasto:</strong> OUR<br><br>
                                @endif
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
                </div>
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
    @php
        $logos_ = json_decode(cache('config')['interfaz'], true)['buscador_productos_alt']??false;
    @endphp
    <table style="width: 200mm">
        <tr>
            <td>
                <span class="logo-footer-1"><img style="width: 100%" src="{{public_path('images/dya/logos-dya.png')}}" alt=""></span>
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
        right: 0mm;
        top:0mm;
        text-align: center;
        padding: 20px 20px;
        border: 2px solid black;
    }

    .header .info-emisor{
        width: 180mm;
        padding: 0 20px 10px 20px;
    }
    .header .info-emisor .logo{
        width: 60mm;
    }
    .header .info-emisor .logo img{
        width: 60mm;
        text-align: center;
        margin-left: -5mm;
    }
    .header .info-emisor .texto{
        width: 75mm;
        margin-left: 60mm;
        margin-top:-30mm;
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
        width: 200mm;
        margin-top: 2mm;
        border-collapse:collapse;
    }
    .body .items thead td{
        background: #e30613;
    }

    .items tbody td{
        padding: 1.5mm 3mm;
        border-bottom: 0;
        border: 1px solid black
    }

    .footer{
        width: 200mm;
        height: 20mm;
    }
    .footer .footer-l{
        width: 73%;
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
    .border{
        margin-top:5mm;
        border: 2px solid #CCC;
        border-radius:5px;
        padding: 0 10px 10px;
    }

    .info-usuario table{
        border-collapse: collapse;
    }
    .info-usuario table td{
        border: 1px solid black;
        padding: 1.5mm 3mm;
    }
    td.bg-#e30613{
        background: #e30613;
        color: white;
    }


</style>