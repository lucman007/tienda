@extends('sunat.plantillas-pdf.main')
@section('titulo','Factura')
@section('contenido')
    <div class="header">
        <div class="borde info-emisor">
            @if($emisor->logo)
                <div class="logo">
                    <img src="{{'images/'.$emisor->logo}}">
                </div>
            @endif
            <div class="texto">
                <h3><span>{{$emisor->nombre_publicitario}}</span></h3>
                <p><strong>{{$emisor->razon_social}}</strong> <br> {{$emisor->direccion}}, {{$emisor->provincia}},
                    {{$emisor->departamento}}, {{$emisor->distrito}} <br> {{$emisor->telefono_1}}/{{$emisor->email}} <br>
                    {{$emisor->texto_publicitario}}
                </p>
            </div>
        </div>
        <div class="borde info-ruc">
            <h3>R.U.C.: {{$emisor->ruc}}</h3>
            <h3 class="titulo_comprobante"><span>RECIBO DE VENTA N°</span></h3>
            <h3>{{$documento->facturacion->serie}}-{{$documento->facturacion->correlativo}}</h3>
        </div>
    </div>
    <div class="body">
        <div class="borde info-usuario">
            <table cellpadding="0">
                <tr>
                    <td><strong>Razón social:</strong></td>
                    <td style="width:150mm">{{$usuario->persona->nombre}}</td>
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
                    @if($documento->facturacion->codigo_moneda=='S/')
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
            </table>
        </div>
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
                        <td style="width: 5mm">{{$item->detalle->num_item}}</td>
                        <td style="width: 20mm">{{$item->cod_producto}}</td>
                        <td style="width: 60mm">{{$item->nombre}} {!! $item->detalle->descripcion !!}</td>
                        <td style="width: 18mm">{{$item->detalle->cantidad}}</td>
                        <td style="width: 10mm">{{explode('/',$item->unidad_medida)[1]}}</td>
                        <td style="width: 20mm; text-align: right">{{$item->detalle->monto}}</td>
                        <td style="width: 15mm; text-align: right">{{$item->detalle->tipo_descuento?floatval($item->detalle->porcentaje_descuento).'%':$item->detalle->descuento}}</td>
                        <td style="width: 20mm; text-align: right">{{$item->detalle->total}}</td>
                    </tr>
                @endforeach
                @if($documento->facturacion->descuento_global > '0.00')
                    <tr>
                        <td><br></td>
                    </tr>
                    <tr>
                        <td colspan="8">Descuento global ({{$documento->facturacion->porcentaje_descuento_global*100}}):
                            {{$documento->facturacion->codigo_moneda}} {{$documento->facturacion->descuento_global}}</td>
                    </tr>
                @endif
            </tbody>
        </table>
        <table class="footer">
            <tr>
                <td class="footer-l">
                    <p>SON: {{$documento->leyenda}}</p>
                </td>
                <td style="width: 1%"></td>
                <td class="footer-r">
                    <table>
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
            padding: 24px 36px;
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
            text-align: center;
            position: absolute;
            right: 2mm;
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
            width: 57.5%;
            border: 1px solid black;
            border-radius: 5px;
            padding: 20px 32px 25px 32px;
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
    </style>

@endsection
