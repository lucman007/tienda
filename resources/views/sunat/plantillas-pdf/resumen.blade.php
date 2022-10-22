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
            <h3>{{$emisor->ruc}}</h3>
            <h3><span>FACTURA ELECTRÓNICA</span></h3>
            <h3>{{$documento->serie}}-{{$documento->correlativo}}</h3>
        </div>
    </div>
    <div class="body">
        <div class="borde items">
            <table cellpadding="0">
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
                @foreach($items as $item)
                    <tr>
                        <td style="width: 5mm">{{$item->num_item}}</td>
                        <td style="width: 20mm">{{$item->codigo}}</td>
                        <td style="width: 60mm">{{$item->descripcion}}</td>
                        <td style="width: 20mm">{{$item->cantidad}}</td>
                        <td style="width: 10mm">{{$item->unidad_medida}}</td>
                        <td style="width: 20mm; text-align: right">{{$item->precio}}</td>
                        <td style="width: 20mm; text-align: right">{{$item->descuento}}</td>
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
                        <td colspan="8">Descuento global ({{$documento->facturacion->porcentaje_descuento_global*100}}):
                            {{$documento->codigo_moneda}} {{$documento->facturacion->descuento_global}}</td>
                    </tr>
                @endif
            </table>
        </div>
    </div>
    <div class="footer">
        <div class="hash">
            <img class="qr" src="images/qr/{{$documento->qr}}">
            <p>SON: {{$documento->leyenda}}</p>
            <p>Representación Impresa de la Factura Electrónica <br>
                Código Hash: {{$documento->hash}} <br>
                Autorizado para ser Emisor electrónico mediante la Resolución de Intendencia N° 000000/SUNAT <br>
                Para consultar el comprobante ingresar a : </p>
        </div>
        <div class="borde impuesto">
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
            font-size: 7pt;
        }
        .borde{
            border: 1px solid black;
            border-radius: 5px;
            padding: 20px;
        }
        .info-emisor{
            width: 100mm;
            height: 20mm;
            padding: 20px;
        }
        .info-emisor .logo{
            width: 23mm;
        }
        .info-emisor .logo img{
            width: 23mm;
            text-align: center;
            margin: 0;
        }
        .info-emisor .texto{
            width: 75mm;
            text-align: center;
            position: absolute;
            right: 10mm;
            top:10mm;
        }
        .info-ruc{
            position: absolute;
            top:5mm;
            right: 0;
            text-align: center;
            height: 20mm;
        }
        .info-usuario{
            width: 188mm;
            height: 14mm;
            margin-top: 5mm;
            position: relative;
            word-wrap: break-word;
        }
        .info-usuario p{
            line-height: 4mm;
        }
        .items{
            width: 188mm;
            margin-top: 5mm;
        }
        table{
            margin: 0;
            padding: 0;
        }
        .table-header td{
            border-bottom: 1px solid black;
            margin: 0;
        }
        .footer{
            position: relative;
            height: 20mm;
            margin-top: 5mm;
        }
        .hash{
            width: 100mm;
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
    </style>
@endsection
