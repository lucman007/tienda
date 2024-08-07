@extends('sunat.plantillas-pdf.main')
@section('titulo','Factura')
@section('contenido')
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
            <h3><span>{{mb_strtoupper($documento->titulo_doc)}} ELECTRÓNICA</span></h3>
            <h3>{{$documento->serie}}-{{$documento->correlativo}}</h3>
        </div>
    </div>
    <div class="body">
        <div class="borde info-usuario">
            <table cellpadding="0">
                <tr>
                    <td><strong>Razón social:</strong></td>
                    <td style="width:150mm">{{$usuario->razon_social}}</td>
                </tr>
                <tr>
                    <td><strong>Dirección:</strong></td>
                    <td style="width:150mm">{{ $usuario->persona->direccion }}</td>
                </tr>
                <tr>
                    <td><strong>Ruc:</strong></td>
                    <td>{{ $usuario->num_documento }}</td>
                </tr>
                <tr>
                    <td><strong>Fecha de emisión:</strong></td>
                    <td>{{ date('d/m/Y',strtotime($documento->fecha)) }}</td>
                </tr>
                <tr>
                    <td><strong>Tipo de pago:</strong></td>
                    <td>{{ $documento->tipo_pago==2?'CRÉDITO':'CONTADO' }}</td>
                </tr>
                <tr>
                    <td><strong>Tipo de moneda:</strong></td>
                    @if($documento->codigo_moneda=='S/')
                        <td>SOLES</td>
                    @else
                        <td>DÓLARES</td>
                    @endif
                </tr>
                @if($documento->facturacion->oc_relacionada)
                    <tr>
                        <td><strong>N° orden de compra:</strong></td>
                        <td>{{$documento->facturacion->oc_relacionada}}</td>
                    </tr>
                @endif
                @if($documento->facturacion->guia_relacionada)
                    <tr>
                        <td><strong>N° de guía:</strong></td>
                        <td>{{$documento->facturacion->guia_relacionada}}</td>
                    </tr>
                @endif
                @if($documento->facturacion->guia_fisica)
                    <tr>
                        <td><strong>N° de guía:</strong></td>
                        <td>{{$documento->facturacion->guia_fisica}}</td>
                    </tr>
                @endif
                @if($documento->facturacion->codigo_tipo_factura == '0200')
                    <tr>
                        <td><strong>Tipo de factura:</strong></td>
                        <td>Exportación</td>
                    </tr>
                @endif
            </table>
        </div>
        <div class="tabla-alto-fijo">
            <table class="items" cellpadding="0">
                <thead>
                <tr class="table-header">
                    <td>Item</td>
                    <td>Código</td>
                    <td>Descripción</td>
                    <td>Cantidad</td>
                    <td>Und</td>
                    <td>Precio unitario</td>
                    <td>Descuento</td>
                    <td>Importe</td>
                </tr>
                </thead>
                <tbody>
                @foreach($items as $item)
                    <tr>
                        <td style="width: 5mm">{{$item->num_item}}</td>
                        <td style="width: 15mm">{{$item->codigo}}</td>
                        <td style="width: 70mm">{!! $item->descripcion !!}</td>
                        <td style="width: 12mm">{{$item->cantidad}}</td>
                        <td style="width: 10mm">{{$item->unidad_medida}}</td>
                        <td style="width: 20mm; text-align: right">{{$item->precio}}</td>
                        <td style="width: 15mm; text-align: right">{{$item->detalle->tipo_descuento?floatval($item->detalle->porcentaje_descuento).'%':$item->detalle->descuento}}</td>
                        @if($documento->igv_incluido == 1)
                            <td style="width: 20mm; text-align: right">{{$item->detalle->total}}</td>
                        @else
                            <td style="width: 20mm; text-align: right">{{$item->detalle->subtotal}}</td>
                        @endif

                    </tr>
                @endforeach
                @if($documento->facturacion->descuento_global > '0.00')
                    <tr>
                        <td><br></td>
                    </tr>
                    <tr>
                        <td colspan="8">Descuento global ({{$documento->facturacion->porcentaje_descuento_global*100}}%):
                            {{$documento->codigo_moneda}} {{$documento->facturacion->descuento_global}}</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
        <table class="footer">
            <tr>
                <td class="footer-l">
                    <img class="qr" src="images/qr/{{$documento->qr}}">
                    <p>SON: {{$documento->leyenda}}<br>
                        Representación Impresa de la {{$documento->titulo_doc}} Electrónica <br>
                        Código Hash: {{$documento->hash}} <br>
                        Para consultar el comprobante ingresar a : {{url('consulta')}}</p>
                </td>
                <td style="width: 1%"></td>
                <td class="footer-r">
                    <table>
                        @if($documento->facturacion->total_gratuitas > '0.00')
                            <tr>
                                <td>Total valor venta gratuitas:</td>
                                <td style="width: 28mm; text-align: right">{{$documento->codigo_moneda}} {{$documento->facturacion->total_gratuitas}}</td>
                            </tr>
                        @endif
                        @if($documento->facturacion->total_inafectas > '0.00')
                            <tr>
                                <td>Total valor venta inafectas:</td>
                                <td style="width: 28mm; text-align: right">{{$documento->codigo_moneda}} {{$documento->facturacion->total_inafectas}}</td>
                            </tr>
                        @endif
                        @if($documento->facturacion->total_exoneradas > '0.00')
                            <tr>
                                <td>Total valor venta exoneradas:</td>
                                <td style="width: 28mm; text-align: right">{{$documento->codigo_moneda}} {{$documento->facturacion->total_exoneradas}}</td>
                            </tr>
                        @endif
                        @if($documento->facturacion->total_descuentos > '0.00')
                            <tr>
                                <td>Total descuentos:</td>
                                <td style="width: 28mm; text-align: right">{{$documento->codigo_moneda}} {{$documento->facturacion->total_descuentos}}</td>
                            </tr>
                        @endif
                        <tr>
                            <td>Total valor venta gravado:</td>
                            <td style="width: 28mm; text-align: right">{{$documento->codigo_moneda}} {{$documento->facturacion->total_gravadas}}</td>
                        </tr>
                        <tr>
                            <td>Total IGV 18%:</td>
                            <td style="width: 28mm; text-align: right">{{$documento->codigo_moneda}} {{$documento->facturacion->igv}}</td>
                        </tr>
                        <tr>
                            <td>Importe total:</td>
                            <td style="width: 28mm; text-align: right">{{$documento->codigo_moneda}} {{$documento->total_venta}}</td>
                        </tr>
                    </table>
                    @if($documento->facturacion->retencion == 1)
                        <br>
                        <table>
                            <tr>
                                <td><strong>Retención:</strong> <br> {{$documento->codigo_moneda}} {{$documento->retencion}}</td>
                            </tr>
                            @if($documento->tipo_pago == 2)
                                <tr>
                                    <td><strong>Monto neto pendiente de pago:</strong><br> {{$documento->codigo_moneda}} {{$documento->monto_menos_retencion}}</td>
                                </tr>
                            @endif
                        </table>
                        <br>
                    @endif
                    @if($documento->facturacion->codigo_tipo_factura == '1001')
                        <p><strong>OPERACIÓN SUJETA A DETRACCIÓN {{$documento->codigo_moneda}} {{$documento->detraccion}} ({{$documento->porcentaje_detraccion}}%)</strong>
                            <br>
                        N° de cuenta Banco de la Nación detracción: {{$emisor->cuentas[0]['cuenta']}}</p>                    @endif
                </td>
            </tr>
        </table>
        <table class="footer">
            <tr>
                <td style="width: 60%;">
                    <p>
                        @php
                            $bancos = \sysfact\Http\Controllers\Helpers\DataGeneral::getBancos();
                        @endphp
                        @foreach($emisor->cuentas as $key=>$cuenta)
                            @php
                                $index = array_search($cuenta['banco'], array_column($bancos,'num_val'));
                            @endphp
                            @if($key !== 0)
                                <strong>N° de Cta. {{$bancos[$index]['label']}} ({{$cuenta['moneda']=='USD'?'Dólares':'Soles'}}):</strong> {{$cuenta['cuenta']}} {{$cuenta['cci']?'- CCI: '.$cuenta['cci']:''}} {{isset($cuenta['descripcion'])&&$cuenta['descripcion']!=''?'('.$cuenta['descripcion'].')':''}} <br>
                            @endif
                        @endforeach
                    </p>
                </td>
                <td style="width: 25%;">
                    @if($documento->tipo_pago == 2)
                        @php
                            $i=1
                        @endphp
                        <table style="border: 1px solid black; border-radius: 5px;">
                            <thead>
                            <tr><td colspan="3" style="text-align:center; background: black; color:white"><strong>Detalle de cuotas</strong></td></tr>
                            <tr>
                                <td style="width:20mm">Cuota</td>
                                <td style="width:30mm">Monto</td>
                                <td>F. Vencimiento</td>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($documento->pago as $pago)
                                <tr>
                                    <td>{{str_pad($i,3,"0",STR_PAD_LEFT)}}</td>
                                    <td>{{$documento->codigo_moneda}} {{$pago->monto}}</td>
                                    <td>{{date('d/m/Y',strtotime($pago->fecha))}}</td>
                                </tr>
                                @php
                                    $i++
                                @endphp
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </td>
            </tr>
        </table>
        <p style="width: 150mm; text-align: center">{{$emisor->direccion}}, {{$emisor->provincia}},
            {{$emisor->departamento}}, {{$emisor->distrito}} <br> {{$emisor->telefono_1}}/{{$emisor->email}}
        </p>
        @if(json_decode(cache('config')['impresion'], true)['tipo_cambio_comprobantes'])
            <p style="margin-left: 5mm"> Tipo de cambio: Compra: {{cache('opciones')['tipo_cambio_compra']}} | Venta: {{cache('opciones')['tipo_cambio_venta']}}</p>
        @endif
    </div>


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

        .qr{
            width: 25mm;
            float: left;
            margin-right: 5mm;
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
            padding: 25px 25px;
        }

        .header .info-emisor{
            width: 112mm;
            height: 25mm;
            padding: 25px 20px;
        }
        .header .info-emisor .logo{
            width: 30mm;
            position: absolute;
            top: 0;
            left: 25mm;
        }
        .header .info-emisor .logo img{
            width: 70mm;
            text-align: center;
            margin-left: 5mm;
        }
        .header .info-emisor .texto{
            width: 115mm;
            text-align: center;
            position: absolute;
            left: 3mm;
            top:25mm;
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
            position: relative;
            padding: 20px 32px;
        }
        .footer{
            width: 200mm;
            height: 20mm;
            margin-top: 5mm;
        }
        .footer .footer-l{
            width: 57.5%;
            border: 1px solid black;
            border-radius: 5px;
            padding: 3mm;
        }
        .footer .footer-r{
            width: 39%;
            padding: 20px 25px 0;
        }

        .leyenda{
            width: 200mm;
            margin-top: 3mm;
            text-align: center;
        }
        .tabla-alto-fijo{
            margin-top: 5mm;
            border: 1px solid black;
            height: 100mm;
            border-radius: 5px;
        }
        .cuotas_table{
            width:100mm;
            border-collapse: collapse;
        }
        .cuotas_table tr td{
            border: 1px solid black;
        }
    </style>

@endsection
