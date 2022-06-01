@extends('sunat.plantillas-pdf.main')
@section('titulo','Boleta')
@section('contenido')
    <div class="header">
        <div class="info-emisor">
            @if($emisor->logo)
            <div class="logo">
                <img src="{{'images/'.$emisor->logo}}">
            </div>
            @endif
            <div class="texto">
                <p>
                    <strong>{{$emisor->nombre_comercial?$emisor->nombre_comercial:$emisor->nombre_publicitario}}</strong><br>
                    {{json_decode(cache('config')['impresion'], true)['ocultar_razon_social']?'':$emisor->razon_social}}<br>R.U.C. {{$emisor->ruc}}<br><br>{{$emisor->direccion}}, {{$emisor->urbanizacion==''?'':$emisor->urbanizacion.','}} {{$emisor->provincia}},
                    {{$emisor->departamento}}, {{$emisor->distrito}} <br> {{$emisor->telefono_1}} / {{$emisor->telefono_2==''?'':$emisor->telefono_2.' / '}}{{$emisor->email}} <br>
                    {{$emisor->texto_publicitario}} <br><br>
                    <strong>{{mb_strtoupper($documento->titulo_doc)}} ELECTRÓNICA {{$documento->serie}}-{{$documento->correlativo}}</strong>
                </p>
            </div>
        </div>
    </div>
    <div class="body">
        <div class="info-usuario">
            <table cellpadding="0">
                <tr>
                    <td><strong>Cliente:</strong></td>
                    <td style="width:42mm">{{$usuario->razon_social}}</td>
                </tr>
                <tr>
                    <td><strong>Dirección:</strong></td>
                    <td style="width:42mm">{{ $usuario->persona->direccion }}</td>
                </tr>
                <tr>
                    <td><strong>Ruc:</strong></td>
                    <td>{{ $usuario->num_documento }}</td>
                </tr>
                <tr>
                    <td><strong>Fecha:</strong></td>
                    <td>{{ date('d/m/Y h:i:s A',strtotime($documento->fecha)) }}</td>
                </tr>
                <tr>
                    <td><strong>T. pago:</strong></td>
                    <td>{{ $documento->tipo_pago==2?'CRÉDITO':'CONTADO' }}</td>
                </tr>
                <tr>
                    <td><strong>Moneda:</strong></td>
                    @if($documento->codigo_moneda=='S/')
                        <td>SOLES</td>
                    @else
                        <td>DÓLARES</td>
                    @endif
                </tr>
                <tr>
                    <td><strong>Caja:</strong></td>
                    <td>{{ $documento->caja->nombre }} @if($documento->empleado->idpersona != -1)/ <strong>Mozo(a):</strong>  {{ $documento->empleado->nombre }}@endif</td>
                </tr>
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
                    <td style="width: 38mm">{{$item->detalle->cantidad}} {{$item->nombre}} {{$item->detalle->descripcion}}</td>
                    <td style="width: 13mm">{{$item->detalle->monto}}</td>
                    <td style="width: 13mm">{{$item->detalle->total}}</td>
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
            </tbody>
        </table>
        <table class="footer">
            @if($documento->facturacion->total_gratuitas > '0.00')
                <tr>
                    <td style="width: 20mm">Gratuita:</td>
                    <td style="text-align: right; width: 38mm">{{$documento->codigo_moneda}} {{$documento->facturacion->total_gratuitas}}</td>
                </tr>
            @endif
            @if($documento->facturacion->total_inafectas > '0.00')
                <tr>
                    <td style="width: 20mm">Total valor venta inafectas:</td>
                    <td style="text-align: right; width: 38mm">{{$documento->codigo_moneda}} {{$documento->facturacion->total_inafectas}}</td>
                </tr>
            @endif
            @if($documento->facturacion->total_exoneradas > '0.00')
                <tr>
                    <td style="width: 20mm">Exonerada:</td>
                    <td style="text-align: right; width: 38mm">{{$documento->codigo_moneda}} {{$documento->facturacion->total_exoneradas}}</td>
                </tr>
            @endif
            @if($documento->facturacion->total_descuentos > '0.00')
                <tr>
                    <td style="width: 20mm">Descuentos:</td>
                    <td style="text-align: right; width: 38mm">{{$documento->codigo_moneda}} {{$documento->facturacion->total_descuentos}}</td>
                </tr>
            @endif
            <tr>
                <td style="width: 20mm">Gravada:</td>
                <td style="text-align: right; width: 38mm">{{$documento->codigo_moneda}} {{$documento->facturacion->total_gravadas}}</td>
            </tr>
            <tr>
                <td style="width: 20mm">IGV 18%:</td>
                <td style="text-align: right; width: 38mm">{{$documento->codigo_moneda}} {{$documento->facturacion->igv}}</td>
            </tr>
            <tr>
                <td style="width: 20mm">Total:</td>
                <td style="text-align: right; width: 38mm">{{$documento->codigo_moneda}} {{$documento->total_venta}}</td>
            </tr>
        </table>
        <table style="text-align: center">
            <tr>
                <td style="width: 62mm"><img class="qr" src="images/qr/{{$documento->qr}}"></td>
            </tr>
            <tr>
                <td style="width: 62mm">SON: {{$documento->leyenda}}</td>
            </tr>
            <tr>
                <td style="width: 62mm">Representación Impresa de la {{$documento->titulo_doc}} Electrónica <br>
                    Código Hash: {{$documento->hash}} <br><br>
                    Para consultar el comprobante ingresar a : <br>{{url('consulta')}}</td>
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
            width: 62mm;
            float: left;
            text-align: center;
            margin-bottom: 10px;
        }

        .body{
            position: relative;
            width: 72mm;
            height: 100mm;
            float: left;
        }

        .body .info-usuario{
            width: 72mm;
            height: 20mm;
        }
        .body .items {
            width: 72mm;
            margin-top: 5mm;
            position: relative;
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
