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
            <div class="texto" style="color:#2a5ac5">
                <h3><span>{{$emisor->nombre_publicitario}}</span></h3>
                <p><strong>{{$emisor->razon_social}}</strong> <br> {{$emisor->direccion}}, {{$emisor->urbanizacion}}, {{$emisor->provincia}},
                    {{$emisor->departamento}}, {{$emisor->distrito}} <br> {{$emisor->telefono_1}} / {{$emisor->email}} <br>
                    {{$emisor->texto_publicitario}}
                </p>
            </div>
        </div>
        <div class="info-ruc">
            <h3>R.U.C.: {{$emisor->ruc}}</h3>
            <h3><span>{{mb_strtoupper($documento->titulo_doc)}} ELECTRÓNICA</span></h3>
            <h3>{{$documento->serie}}-{{$documento->correlativo}}</h3>
        </div>
    </div>
    <div class="body">
        <div class="info-usuario">
            <table cellpadding="0">
                <tr>
                    <td><strong>Fecha de emisión:</strong></td>
                    <td>{{ date('d/m/Y',strtotime($documento->fecha)) }}</td>
                </tr>
                <tr>
                    <td><strong>Tipo de pago:</strong></td>
                    <td>{{ $documento->tipo_pago==2?'CRÉDITO':'CONTADO' }}</td>
                </tr>
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
            </table>
        </div>
        <table class="items" cellpadding="0">
            <thead>
                <tr class="table-header">
                    <td><strong>#</strong></td>
                    <td><strong>CÓD.</strong></td>
                    <td><strong>DESCRIPCIÓN</strong></td>
                    <td><strong>CANT.</strong></td>
                    <td><strong>UND</strong></td>
                    <td><strong>P. UNITARIO</strong></td>
                    <td><strong>DSCTO</strong></td>
                    <td><strong>IMPORTE</strong></td>
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
                    <td style="width: 15mm; text-align: right">{{floatval($item->detalle->porcentaje_descuento)}}%</td>
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
        <table class="footer">
            <tr>
                <td class="footer-l">
                    <img class="qr" src="images/qr/{{$documento->qr}}">
                    <p>SON: {{$documento->leyenda}}</p>
                    <p>Representación Impresa de la {{$documento->titulo_doc}} Electrónica <br>
                        Código Hash: {{$documento->hash}} <br>
                        Para consultar el comprobante ingresar a : {{url('consulta')}}</p>
                </td>
                <td style="width: 1%"></td>
                <td class="footer-r">
                    <table>
                        @if($documento->facturacion->total_gratuitas > '0.00')
                            <tr>
                                <td><strong>Total valor venta gratuitas:</strong></td>
                                <td style="width: 28mm; text-align: right">{{$documento->codigo_moneda}} {{$documento->facturacion->total_gratuitas}}</td>
                            </tr>
                        @endif
                        @if($documento->facturacion->total_inafectas > '0.00')
                            <tr>
                                <td><strong>Total valor venta inafectas:</strong></td>
                                <td style="width: 28mm; text-align: right">{{$documento->codigo_moneda}} {{$documento->facturacion->total_inafectas}}</td>
                            </tr>
                        @endif
                        @if($documento->facturacion->total_exoneradas > '0.00')
                            <tr>
                                <td><strong>Total valor venta exoneradas:</strong></td>
                                <td style="width: 28mm; text-align: right">{{$documento->codigo_moneda}} {{$documento->facturacion->total_exoneradas}}</td>
                            </tr>
                        @endif
                        @if($documento->facturacion->total_descuentos > '0.00')
                            <tr>
                                <td><strong>Total descuentos:</strong></td>
                                <td style="width: 28mm; text-align: right">{{$documento->codigo_moneda}} {{$documento->facturacion->total_descuentos}}</td>
                            </tr>
                        @endif
                        <tr>
                            <td><strong>Total valor venta gravado:</strong></td>
                            <td style="width: 28mm; text-align: right">{{$documento->codigo_moneda}} {{$documento->facturacion->total_gravadas}}</td>
                        </tr>
                        <tr>
                            <td><strong>Total IGV 18%:</strong></td>
                            <td style="width: 28mm; text-align: right">{{$documento->codigo_moneda}} {{$documento->facturacion->igv}}</td>
                        </tr>
                        <tr>
                            <td><strong>Importe total:</strong></td>
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
                </td>
            </tr>
        </table>
        @if($documento->tipo_pago == 2)
            @php
                $i=1
            @endphp
            <table class=cuotas_table>
                <thead>
                <tr><td colspan="3" style="text-align:center"><strong>Detalle de cuotas</strong></td></tr>
                <tr>
                    <td style="width:20mm">Cuota</td>
                    <td style="width:30mm">Monto</td>
                    <td style="width:40mm">F. Vencimiento</td>
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
        @if(json_decode(cache('config')['impresion'], true)['tipo_cambio_comprobantes'])
            <p style="margin-left: 5mm"> Tipo de cambio: Compra: {{cache('opciones')['tipo_cambio_compra']}} | Venta: {{cache('opciones')['tipo_cambio_venta']}}</p>
        @endif
        <div class="cuentas">
            <p>
                <strong>Cta. detracciones:</strong> {{$emisor->cuenta_detracciones}} <br>
                <strong>Cta. Soles:</strong> {{$emisor->cuenta_1}} <br>
                <strong>Cta. Dólares:</strong> {{$emisor->cuenta_2}} <br>
            </p>
        </div>
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
        table{
            margin: 0;
            padding: 0;
        }
        .table-header td{
            border-bottom: 1px solid #CCC;
            margin: 0;
        }

        .qr{
            width: 25mm;
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
            border: 3px solid black;
            top: 5mm;
        }

        .header .info-emisor{
            width: 112mm;
            height: 20mm;
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
            position: absolute;
            right: 0mm;
            top:5mm;
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
            margin-left: 5mm;
        }
        .body .info-usuario p{
            line-height: 4mm;
        }

        .body .items {
            width: 200mm;
            margin-top: 5mm;
            position: relative;
            border-bottom: 1px solid #CCC;
            border-top: 1px solid #CCC;
            padding: 20px 32px;
        }
        .footer{
            width: 200mm;
            height: 20mm;
            margin-top: 5mm;
        }
        .footer .footer-l{
            width: 57.5%;
            border-right: 1px solid #CCC;
            padding: 20px 32px 25px 32px;
            text-align: center;
        }
        .footer .footer-r{
            width: 39%;
            padding: 20px 32px;
        }

        .leyenda{
            width: 200mm;
            margin-top: 3mm;
            text-align: center;
        }
        .cuotas_table{
            width:100mm;
            border-collapse: collapse;
            margin-left: 5mm;
        }
        .cuotas_table tr td{
            border: 1px solid black;
        }
        .cuentas{
            margin: 4mm 0 0 5mm;
        }
    </style>

@endsection
