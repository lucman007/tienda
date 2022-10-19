@extends('sunat.plantillas-pdf.main')
@section('titulo','Factura')
@section('contenido')
    <div class="header">
        <div class="header-ruc">
            <h3 style="">{{mb_strtoupper($documento->titulo_doc)}}<br><span>ELECTRÓNICA</span></h3>
            <h4 style="">{{$documento->serie}}-{{$documento->correlativo}}</h4>
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
    <div class="usuario">
        <table cellpadding="0">
            <tr>
                <td><strong>F. emisión:</strong></td>
                <td>{{ date('d/m/Y',strtotime($documento->fecha)) }}</td>
            </tr>
            <tr>
                <td><strong>Tipo nota de crédito:</strong></td>
                <td>{{$documento->facturacion->tipo_nota_electronica}} - {{$documento->leyenda_nota}}</td>
            </tr>
            <tr>
                <td><strong>Documento que modifica:</strong></td>
                <td>
                    @if($documento->facturacion->tipo_doc_relacionado=='01')
                        Factura
                    @else
                        Boleta
                    @endif
                    {{$documento->facturacion->num_doc_relacionado}}</td>
            </tr>
            <tr>
                <td><strong>Motivo:</strong></td>
                <td>{{$documento->facturacion->descripcion_nota}}</td>
            </tr>
            <tr>
                <td><strong>Cliente:</strong></td>
                <td style="width:150mm;">{{$usuario->razon_social}}</td>
            </tr>
            <tr>
                <td><strong>Dirección:</strong></td>
                <td style="width:150mm">{{ $usuario->persona->direccion }}</td>
            </tr>
            <tr>
                <td><strong>RUC:</strong></td>
                <td>{{ $usuario->num_documento }}</td>
            </tr>
        </table>
    </div>
    <div class="body" style="height: 130mm">
        <table class="items" cellpadding="0">
            <thead>
                <tr class="table-header">
                    <td><strong>#</strong></td>
                    <td><strong>CONCEPTO</strong></td>
                    <td><strong>CANT.</strong></td>
                    <td><strong>UND</strong></td>
                    <td><strong>V. UNIT.</strong></td>
                    <td><strong>DSCTO</strong></td>
                    <td><strong>IMPORTE</strong></td>
                </tr>
            </thead>
            <tbody>
            @foreach($items as $item)
                <tr class="items-tr">
                    <td style="width: 5mm">{{$item->num_item}}</td>
                    <td style="width: 75mm; text-align: left">{!! $item->descripcion !!}</td>
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
                    <td colspan="7">Descuento global ({{$documento->facturacion->porcentaje_descuento_global*100}}%):
                        {{$documento->codigo_moneda}} {{$documento->facturacion->descuento_global}}</td>
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
                        @if($documento->facturacion->total_gratuitas > '0.00')
                            <tr>
                                <td><strong>Total gratuitas:</strong></td>
                                <td style="width: 28mm; text-align: right">{{$documento->codigo_moneda}} {{$documento->facturacion->total_gratuitas}}</td>
                            </tr>
                        @endif
                        @if($documento->facturacion->total_inafectas > '0.00')
                            <tr>
                                <td><strong>Total inafectas:</strong></td>
                                <td style="width: 28mm; text-align: right">{{$documento->codigo_moneda}} {{$documento->facturacion->total_inafectas}}</td>
                            </tr>
                        @endif
                        @if($documento->facturacion->total_exoneradas > '0.00')
                            <tr>
                                <td><strong>Total exoneradas:</strong></td>
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
                            <td><strong>Total gravado:</strong></td>
                            <td style="width: 28mm; text-align: right">{{$documento->codigo_moneda}} {{$documento->facturacion->total_gravadas}}</td>
                        </tr>
                        <tr>
                            <td><strong>IGV 18%:</strong></td>
                            <td style="width: 28mm; text-align: right">{{$documento->codigo_moneda}} {{$documento->facturacion->igv}}</td>
                        </tr>
                        <tr>
                            <td><strong>Total venta:</strong></td>
                            <td style="width: 28mm; text-align: right">{{$documento->codigo_moneda}} {{$documento->total_venta}}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <table style="margin-left:20px;">
        <tr>
            <td>
                <img class="qr" src="images/qr/{{$documento->qr}}">
            </td>
            <td>
                <p>Representación Impresa de la <span style="font-weight: bold">{{$documento->titulo_doc}} Electrónica </span><br>
                    Código Hash: {{$documento->hash}} <br>
                    Consulta el comprobante aquí: {{url('consulta')}}</p>
            </td>
        </tr>
    </table>
    <style>
        p,td{
            font-size: 7pt;
        }
        table{
            margin: 0;
            padding: 0;
        }
        strong{
            color: black;
        }
        .header{
            width: 180mm;
            margin-bottom: 5mm;
            padding: 20px;
        }
        .header-ruc{
            width: 260px;
            text-align: left;
            padding: 10px 0;
            float: left;
            display: inline;
            border-bottom: 2px solid #131313;
        }
        .header-ruc h3{
            font-size: 25px;
            margin: 0;
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
        .logo{
            width: 200px;
            display: block;
            margin-left: 210px;
        }
        .logo img{
            width: 100%;
        }
        .header-info{
            width: 90%;
        }
        .usuario{
            width: 193mm;
            padding: 10px 20px;
            margin-bottom: 3mm;
        }
        .items {
            width: 170mm;
            margin-top: 2mm;
            margin-bottom: 5mm;
            margin-left: 20px;
            border-collapse: collapse;
        }
        .items-tr td{
            border: 1px solid #575757;
            padding: 5px 2px 5px 5px;
            text-align: center;
        }
        .table-header td{
            margin: 0;
            background: #dfdfdf;
            border: 1px solid #767676;
            padding: 10px 0;
        }
        .table-header strong{
            font-style: normal;
        }
        .cuotas_table{
            width:100mm;
            border-collapse: collapse;
        }
        .cuotas_table tr td{
            border: 1px solid #767676;
            text-align: center;
        }
        .footer{
            margin-left:20px;
        }
        .footer-l{
            width: 130mm;
        }

    </style>

@endsection
