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
                <p><strong>{{$emisor->razon_social}}<br>R.U.C. {{$emisor->ruc}}</strong><br>{{$emisor->direccion}}, {{$emisor->provincia}},
                    {{$emisor->departamento}}, {{$emisor->distrito}} <br> {{$emisor->telefono_1}}/{{$emisor->email}} <br>
                    {{$emisor->texto_publicitario}}
                </p>
            </div>
        </div>
        <div class="info-ruc">
            <p>{{mb_strtoupper($documento->titulo_doc)}} ELECTRÓNICA N° {{$documento->facturacion->serie}}-{{$documento->facturacion->correlativo}}</p>
        </div>
    </div>
    <div class="body">
        <div class="info-usuario">
            <table cellpadding="0">
                <tr>
                    <td><strong>Razón social:</strong></td>
                    <td style="width:110mm">{{$usuario->razon_social}}</td>
                </tr>
                <tr>
                    <td><strong>Dirección:</strong></td>
                    <td style="width:110mm">{{ $usuario->persona->direccion }}</td>
                </tr>
                <tr>
                    <td><strong>Ruc:</strong></td>
                    <td>{{ $usuario->num_documento }}</td>
                </tr>
                <tr>
                    <td><strong>Fecha de emisión:</strong></td>
                    <td>{{ date('d/m/Y',strtotime($documento->fecha)) }}</td>
                </tr>
                @if($documento->tipo_pago==2)
                    <tr>
                        <td><strong>Fecha de vencimiento:</strong></td>
                        <td>{{ date('d-m-Y',strtotime($documento->fecha_vencimiento)) }}</td>
                    </tr>
                @endif
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
        <table class="items" cellpadding="0">
            <thead>
                <tr class="table-header">
                    <td>Item</td>
                    <td>Código</td>
                    <td>Descripción</td>
                    <td>Cant.</td>
                    <td>P. unit.</td>
                    <td>Importe</td>
                </tr>
            </thead>
            <tbody>
            @foreach($items as $item)
                <tr>
                    <td style="width: 5mm">{{$item->num_item}}</td>
                    <td style="width: 12mm">{{$item->codigo}}</td>
                    <td style="width: 55mm">{!! $item->descripcion !!}</td>
                    <td style="width: 18mm">{{$item->cantidad}} {{$item->unidad_medida}}</td>
                    <td style="width: 18mm; text-align: right">{{$item->precio}}</td>
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
                    <td colspan="6">Descuento global ({{$documento->facturacion->porcentaje_descuento_global*100}}):
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
                </td>
            </tr>
        </table>
    </div>
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
            font-size: 7pt;
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
        }

        .header{
            position: relative;
            height: 25mm;
            border-bottom: 1px solid #CCC;
            margin-bottom: 5mm;
        }

        .header .info-ruc{
            position: absolute;
            right: 0;
            text-align: center;
            width: 30mm;
            padding: 10px 25px 18px 25px;
            border: 1px solid black;
            border-radius: 3px;
            margin-top: 2mm;
        }

        .header .info-emisor{
            width: 80mm;
            height: 20mm;
        }
        .header .info-emisor .logo img{
            width: 15mm;
            text-align: center;
            margin-top: 3mm;
        }
        .header .info-emisor .texto{
            width: 65mm;
            position: absolute;
            right: -5mm;
        }

        .body{
            position: relative;
            width: 100mm;
            float: left;
        }

        .body .info-usuario{
            width: 100mm;
            height: 20mm;
        }
        .body .info-usuario p{
            width: 100mm;
            line-height: 4mm;
        }

        .body .items {
            width: 100mm;
            margin-top: 5mm;
            position: relative;
        }
        .footer{
            width: 100mm;
            margin-top: 5mm;
        }
        .footer .footer-l{
            width: 70%;
            float: left;
        }
        .footer .footer-r{
            width: 35%;
            float:left;
        }

        .leyenda{
            margin-top: 3mm;
            text-align: center;
        }
    </style>

@endsection
