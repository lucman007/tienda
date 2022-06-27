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
                    <strong>NOTA DE VENTA {{$documento->facturacion->serie}}-{{$documento->facturacion->correlativo}}</strong>
                </p>
            </div>
        </div>
    </div>
    <div class="body">
        <div class="info-usuario">
            <table cellpadding="0">
                <tr>
                    <td><strong>Fecha:</strong></td>
                    <td>{{ date('d/m/Y h:m:s A',strtotime($documento->fecha)) }}</td>
                </tr>
                <tr>
                    <td colspan="2"><strong>Caja:</strong> {{ $documento->caja->nombre }} @if($documento->empleado->idpersona != -1) / <strong>Vend:</strong>  {{ $documento->empleado->nombre }} @endif</td>
                </tr>
                @if($usuario->persona->idpersona != -1)
                <tr>
                    <td><strong>Cliente:</strong></td>
                    <td style="width:42mm">{{$usuario->persona->nombre}}</td>
                </tr>
                @endif
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
                    <td style="width: 38mm">{{$item->detalle->cantidad}} {{$item->nombre}} {{$item->detalle->descripcion}}</td>
                    <td style="width: 13mm">{{$item->detalle->monto}}</td>
                    <td style="width: 13mm">{{$item->detalle->total}}</td>
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
                <td style="width: 60mm">¡GRACIAS POR SU PREFERENCIA!</td>
            </tr>
        </table>
    </div>
    <style>
        .logo{
            width: 62mm;
            text-align: center;
        }

        .logo img{
            width: 40mm;
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

        .qr{
            width: 25mm;
        }

        .header{
            position: relative;
            height: 15mm;
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
            border-top:2px dashed #1f1f1f;
        }
        .footer{
            width: 72mm;
            height: 20mm;
            margin-top: 5mm;
        }

        .leyenda{
            width: 72mm;
            margin-top: 3mm;
            text-align: center;
            float: left;
        }
    </style>

@endsection
