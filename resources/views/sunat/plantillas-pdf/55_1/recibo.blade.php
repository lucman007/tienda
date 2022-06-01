@extends('sunat.plantillas-pdf.main')
@section('titulo','Recibo')
@section('contenido')
    <div class="header">
        <div class="info-emisor">
            <div class="texto">
                <p>
                    <strong>{{$emisor->razon_social}}</strong><br>
                    R.U.C. {{$emisor->ruc}}<br>{{$emisor->direccion}}, {{$emisor->urbanizacion==''?'':$emisor->urbanizacion.','}} {{$emisor->provincia}},
                    {{$emisor->departamento}}, {{$emisor->distrito}} <br> {{$emisor->telefono_1}}<br>
                    <strong>TICKET {{$documento->ticket}}</strong>
                </p>
            </div>
        </div>
    </div>
    <div class="body">
        <div class="info-usuario">
            <table cellpadding="0" style="width: 20mm">
                <tr>
                    <td colspan="2">{{ date('d/m/Y h:i:s A',strtotime($documento->fecha)) }}</td>
                </tr>
                @if($usuario->persona->idpersona != -1)
                    <tr>
                        <td><strong>Cliente:</strong></td>
                        <td>{{$usuario->persona->nombre}}</td>
                    </tr>
                @endif
                <tr>
                    <td colspan="2"><strong>Caja:</strong> {{ $documento->caja->nombre }}
                        @if(isset(json_decode(cache('config')['impresion'], true)['mostrar_mozo']) && json_decode(cache('config')['impresion'], true)['mostrar_mozo'] && $documento->empleado->idpersona != -1)
                            / <strong>Mozo(a):</strong>  {{ $documento->empleado->nombre }}
                        @endif
                    </td>
                </tr>
                @if(isset(json_decode(cache('config')['impresion'], true)['mostrar_mesa']) && json_decode(cache('config')['impresion'], true)['mostrar_mesa'] && $documento->mesa)
                    <tr>
                        @if($documento->mesa == 'DELIVERY')
                            <td colspan="2" style="width:42mm"><strong>Para llevar</strong></td>
                        @else
                            <td><strong>Mesa:</strong></td>
                            <td style="width:42mm">{{$documento->mesa}}</td>
                        @endif
                    </tr>
                @endif
            </table>
        </div>
        <table class="items" cellpadding="0">
            <thead>
            <tr style="font-weight: bold">
                <td>Cant/Descripción</td>
                <td>P/U</td>
                <td>Total</td>
            </tr>
            </thead>
            <tbody>
            @foreach($items as $item)
                <tr>
                    <td style="width: 15mm">{{$item->detalle->cantidad}} {{$item->nombre}} {{$item->detalle->descripcion}}</td>
                    <td style="width: 10mm">{{$item->detalle->monto}}</td>
                    <td style="width: 10mm">{{$item->detalle->total}}</td>
                </tr>
            @endforeach
            <tr>
                <td></td>
                <td>TOTAL:</td>
                <td>{{$documento->codigo_moneda}} {{$documento->total_venta}}</td>
            </tr>
            </tbody>
        </table>
        <table style="text-align: center">
            <tr>
                <td style="width: 32mm">¡GRACIAS POR SU PREFERENCIA!</td>
            </tr>
        </table>
    </div>
    <style>
        .logo{
            width: 30mm;
            text-align: center;
            margin-left: 3mm;
        }

        .logo img{
            width: 100%;
        }

        h3{
            font-size: 12pt;
            margin: 0;
            font-weight: lighter;
            margin-top: 3px;
        }
        h3 span{
            font-weight: bold;
        }
        p,td{
            font-size: 5pt;
        }

        table{
            margin: 0;
            padding: 0;
        }

        .qr{
            width: 25mm;
        }

        .header{
            position: relative;
            height: 25mm;
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
            width: 72mm;
            height: 20mm;
        }
        .header .info-emisor .texto{
            width: 35mm;
            float: left;
            text-align: center;
            margin-bottom: 10px;
        }

        .body{
            position: relative;
            width: 35mm;
            height: 100mm;
            float: left;
        }

        .body .info-usuario{
            width: 25mm;
            height: 20mm;
            text-align: center;
        }
        .body .items {
            margin-top: 0;
            width: 35mm;
            position: relative;
            border-top:2px dashed #1f1f1f;
        }
        .footer{
            width: 35mm;
            height: 20mm;
            margin-top: 3mm;
        }

        .leyenda{
            width: 35mm;
            margin-top: 3mm;
            text-align: center;
            float: left;
        }
    </style>

@endsection
