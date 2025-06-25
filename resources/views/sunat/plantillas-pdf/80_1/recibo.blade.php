@extends('sunat.plantillas-pdf.main')
@section('titulo','Recibo')
@section('contenido')
    @php
        $logo_ticket = json_decode(cache('config')['impresion'], true)['mostrar_logo_ticket']??false;
        $ancho_logo = json_decode(cache('config')['interfaz'], true)['ancho_logo']??'40';
    @endphp
    <div class="header">
        <div class="info-emisor">
            @if($emisor->logo && $logo_ticket)
                <div class="logo">
                    <img src="{{public_path('images/'.$emisor->logo)}}" style="width: {{$ancho_logo}}mm">
                </div>
            @endif
            <div class="texto">
                <p>
                    <strong>{{$emisor->nombre_comercial?$emisor->nombre_comercial:$emisor->nombre_publicitario}}</strong><br>
                    <strong>{{json_decode(cache('config')['impresion'], true)['ocultar_razon_social']?'':$emisor->razon_social}}</strong><br>
                    R.U.C. {{$emisor->ruc}}<br>{{$emisor->direccion}}, {{$emisor->urbanizacion==''?'':$emisor->urbanizacion.','}} {{$emisor->provincia}},
                    {{$emisor->departamento}}, {{$emisor->distrito}} <br> {{$emisor->telefono_1}}<br>
                    <strong>NOTA DE VENTA {{$documento->facturacion->serie}}-{{$documento->facturacion->correlativo}}</strong>
                </p>
            </div>
        </div>
    </div>
    <div class="body">
        <div class="info-usuario">
            <table cellpadding="0">
                <tr>
                    <td style="width: 20mm"><strong>Fecha:</strong></td>
                    <td style="width: 35mm">{{ date('d/m/Y h:m:s A',strtotime($documento->fecha)) }}</td>
                </tr>
                @php
                    $ver_vendedor=json_decode(cache('config')['impresion'], true)['mostrar_vendedor']??false;
                    $ver_cajero=json_decode(cache('config')['impresion'], true)['mostrar_cajero']??false;
                @endphp
                @if($ver_cajero)
                <tr>
                    <td><strong>Caja:</strong></td>
                    <td>{{ $documento->caja->nombre }}</td>
                </tr>
                @endif
                @if($ver_vendedor && $documento->empleado->idpersona != -1)
                <tr>
                    <td><strong>Vend:</strong></td>
                    <td>{{ $documento->empleado->nombre }}</td>
                </tr>
                @endif
                @if($usuario->persona->idpersona != -1)
                <tr>
                    <td><strong>Cliente:</strong></td>
                    <td style="width:42mm">{{$usuario->persona->nombre}}</td>
                </tr>
                @endif
                <tr>
                    <td colspan="2">
                        <hr style="border: 1px dashed black">
                    </td>
                </tr>
            </table>
        </div>
        <table class="items" cellpadding="0">
            <thead>
            <tr style="font-weight: bold">
                <td>Cant/Descripción</td>
                <td>P/U</td>
                <td>Importe</td>
            </tr>
            </thead>
            <tbody>
            @foreach($items as $item)
                <tr>
                    <td style="width: 35mm">{{$item->detalle->cantidad}} {{$item->nombre}} {{$item->detalle->num_serie?'- SERIE PRODUCTO: '.$item->detalle->num_serie:''}} {!!$item->detalle->descripcion!!}</td>
                    <td style="width: 10mm">{{$item->detalle->monto}}</td>
                    <td style="width: 10mm">{{$item->detalle->total}}</td>
                </tr>
            @endforeach
            <tr>
                <td></td>
                <td>TOTAL:</td>
                <td>{{$documento->codigo_moneda}} {{$documento->total_venta}}</td>
            </tr>
            @if($documento->observacion)
                <tr>
                    <td colspan="2">Observación: {{$documento->observacion}}</td>
                </tr>
            @endif
            <tr>
                <td colspan="3">
                    <hr style="border: 1px dashed black">
                </td>
            </tr>
            <tr>
                @if($documento->tipo_pago == 2)
                    <td colspan="3">FORMA DE PAGO:
                        <br>
                        CRÉDITO {{$documento->codigo_moneda}} {{$documento->total_venta}}
                    </td>
                @else
                    @if($documento->facturacion->emitir_como_contado === 1)
                        <td colspan="3">FORMA DE PAGO:
                            <br>
                            CONTADO {{$documento->codigo_moneda}} {{$documento->total_venta}}
                        </td>
                    @else
                        <td colspan="3">PAGOS:
                            @php
                                $tipo_pago = \sysfact\Http\Controllers\Helpers\DataTipoPago::getTipoPago();
                            @endphp
                            @foreach($documento->pago as $pago)
                                @php
                                    $index = array_search($pago->tipo, array_column($tipo_pago,'num_val'));
                                @endphp
                                <br>
                                {{mb_strtoupper($tipo_pago[$index]['label'])}} {{$documento->codigo_moneda}} {{$pago->monto}}
                            @endforeach
                        </td>
                    @endif
                @endif
            </tr>
            <tr>
                <td colspan="3">
                    <hr style="border: 1px dashed black">
                </td>
            </tr>
            <tr>
                <td colspan="3">SON: {{$documento->leyenda}}</td>
            </tr>
            </tbody>
        </table>
        <table style="text-align: center">
            <tr>
                <td style="width: 60mm">¡GRACIAS POR SU PREFERENCIA!</td>
            </tr>
        </table>
    </div>
    <style>
        .logo{
            width: 62mm;
            text-align: center;
        }

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
        .header{
            position: relative;
            height: 15mm;
        }

        .header .info-emisor{
            width: 72mm;
            height: 20mm;
        }
        .header .info-emisor .texto{
            width: 62mm;
            float: left;
            text-align: center;
        }

        .body{
            position: relative;
            width: 72mm;
            height: 100mm;
            float: left;
        }

        .body .info-usuario{
            width: 72mm;
            height: 5mm;
            margin-bottom: 0;
        }
        .body .items {
            margin-top: 0;
            width: 72mm;
            position: relative;
        }
        .leyenda{
            width: 72mm;
            margin-top: 3mm;
            text-align: center;
            float: left;
        }
    </style>

@endsection
