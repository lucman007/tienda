@extends('sunat.plantillas-pdf.main')
@section('titulo','Recibo')
@section('contenido')
    <div class="header">
        <div class="header-ruc">
            <h3 style="color: white">RECIBO<br><span>DE VENTA</span></h3>
            <h4 style="color: white">{{$documento->facturacion->serie}}-{{$documento->facturacion->correlativo}}</h4>
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
                <td><strong>Tipo de pago:</strong> {{ $documento->tipo_pago==2?'CRÉDITO':'CONTADO' }}</td>
                <td>
                    <strong>Moneda:</strong>
                @if($documento->facturacion->codigo_moneda=='S/')
                    SOLES
                @else
                    DÓLARES
                @endif
                </td>
            </tr>
            <tr>
                @if($documento->facturacion->guia_relacionada)
                    <td><strong>Guía:</strong></td>
                    <td>{{$documento->facturacion->guia_relacionada}}</td>
                @endif
                @if($documento->facturacion->oc_relacionada)
                    <td><strong>Ord. compra:</strong> {{$documento->facturacion->oc_relacionada}}</td>
                @endif
                @if($documento->facturacion->guia_fisica)
                    <td><strong>Guía:</strong> {{$documento->facturacion->guia_fisica}}</td>
                @endif
            </tr>
            <tr>
                <td><strong>Cliente:</strong></td>
                <td colspan="3" style="width:150mm;">{{$usuario->persona->nombre}}</td>
            </tr>
            <tr>
                <td><strong>Dirección:</strong></td>
                <td colspan="3" style="width:150mm">{{ $usuario->persona->direccion }}</td>
            </tr>
            <tr>
                <td><strong>RUC:</strong></td>
                <td>{{ $usuario->num_documento }}</td>
            </tr>
        </table>
    </div>
    <div class="div-table-header">
    </div>
    <div class="body" style="height: 130mm">
        <table class="items" cellpadding="0">
            <thead>
                <tr class="table-header">
                    <td><strong>#</strong></td>
                    <td><strong>CONCEPTO</strong></td>
                    <td><strong>CANT.</strong></td>
                    <td><strong>UND</strong></td>
                    <td><strong>P. UNITARIO</strong></td>
                    <td><strong>DSCTO</strong></td>
                    <td><strong>IMPORTE</strong></td>
                </tr>
            </thead>
            <tbody>
            @foreach($items as $item)
                <tr class="items-tr">
                    <td style="width: 5mm">{{$item->detalle->num_item}}</td>
                    <td style="width: 88mm">{{$item->nombre}} {!! $item->detalle->descripcion !!}</td>
                    <td style="width: 12mm">{{$item->detalle->cantidad}}</td>
                    <td style="width: 10mm">{{explode('/',$item->unidad_medida)[1]}}</td>
                    <td style="width: 20mm; text-align: right">{{$item->detalle->monto}}</td>
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
                    <table>
                        <tr>
                            <td>
                                <br><br>
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
                            </td>
                        </tr>
                    </table>
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


    <style>
        p,td{
            font-size: 8pt;
        }
        table{
            margin: 0;
            padding: 0;
        }
        strong{
            color: #ff4809;
        }
        .header{
            width: 180mm;
            margin-bottom: 5mm;
            background: #e9e9e9;
            padding: 20px;
            border-radius: 5px;
            border:3px solid #7c8083;
        }
        .header-ruc{
            width: 260px;
            text-align: center;
            background: #ff4809;
            padding: 10px;
            float: left;
            display: inline;
            border-radius:40px;
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
            width: 250px;
            display: block;
            margin-left: 180px;
        }
        .logo img{
            width: 100%;
        }
        .header-info{
            width: 90%;
        }
        .usuario{
            width: 193mm;
            padding: 10px;
            margin-bottom: 3mm;
        }
        .div-table-header{
            width: 190mm; background: #ff4809; padding: 2mm 5mm; text-align: center; border-radius: 5px 5px 0 0; height: 5mm
        }
        .items {
            width: 200mm;
            margin-top: -25px;
            margin-bottom: 5mm;
        }
        .items-tr td{
            border-bottom: 1px solid #575757;
            background: #e9e9e9;
            padding: 5px 2px 5px 5px;
        }
        .table-header td{
            margin: 0;
            height: 6mm;
            background: none;
            border-bottom:none;
            padding: 0;
        }
        .table-header strong{
            font-style: normal;
            color: white;
        }
        .cuotas_table{
            width:100mm;
            border-collapse: collapse;
        }
        .cuotas_table tr td{
            border: 1px solid black;
        }
        .footer-l{
            width: 130mm;
        }

    </style>

@endsection
