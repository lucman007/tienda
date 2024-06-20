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
                @if($emisor->nombre_publicitario)
                    <h3><span>{{$emisor->nombre_publicitario}}</span></h3>
                    @else
                    <h3><span>{{$emisor->razon_social}}</span></h3>
                @endif
                <p>@if($emisor->nombre_publicitario)<strong>{{$emisor->razon_social}}</strong> <br> @endif{{$emisor->direccion}}, {{$emisor->provincia}},
                    {{$emisor->departamento}}, {{$emisor->distrito}} <br> {{$emisor->telefono_1}}/{{$emisor->email}} <br>
                    {{$emisor->texto_publicitario}}
                </p>
            </div>
        </div>
        <div class="borde info-ruc">
            <h3>R.U.C.: {{$emisor->ruc}}</h3>
            <h3><span>GUÍA DE REMISIÓN <br> REMITENTE<br>ELECTRÓNICA</span></h3>
            <h3>{{$documento->serie_correlativo}}</h3>
        </div>
    </div>
    <div class="body">
        <div class="borde info-usuario">
            <table cellpadding="0">
                <tr>
                    <td>DESTINATARIO:</td>
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
            </table>
        </div>
        <div class="borde info-usuario" style="margin-top: 5mm">
            <table cellpadding="0">
                <tr>
                    <td>ENVÍO:</td>
                </tr>
                <tr>
                    <td style="width: 100px"><strong>Fecha de emisión:</strong></td>
                    <td style="width: 100px">{{ date('d-m-Y',strtotime($documento->fecha_emision)) }}</td>
                    <td style="width: 150px"><strong>Fecha de inicio traslado:</strong></td>
                    <td style="width: 150px">{{ date('d-m-Y',strtotime($documento->fecha_traslado)) }}</td>

                </tr>
                <tr>
                    <td style="width: 150px"><strong>Modalidad de transporte:</strong></td>
                    <td style="width: 150px">{{$documento->codigo_transporte=='01'?'PÚBLICO':'PRIVADO'}}</td>
                    <td style="width: 100px"><strong>Peso bruto:</strong></td>
                    <td style="width: 100px">{{$documento->peso_bruto}} KG</td>
                    <td><strong>Número de bultos:</strong></td>
                    <td>{{$documento->cantidad_bultos}} UND</td>
                </tr>
                <tr>
                    <td><strong>Motivo de traslado:</strong></td>
                    <td style="width:150mm">{{$documento->motivo_traslado}}</td>
                </tr>
                <tr>
                    <td><strong>Punto de partida:</strong></td>
                    <td style="width:150mm">{{$documento->direccion_partida?$documento->direccion_partida:$documento->emisor->direccion_resumida}}</td>
                </tr>
                <tr>
                    <td><strong>Punto de llegada:</strong></td>
                    <td style="width:150mm">{{$documento->direccion_llegada}}</td>
                </tr>
                @if($documento->doc_relacionado != -1)
                    <tr>
                    <td><strong>Documento relacionado:</strong></td>
                    <td style="width:150mm">{{$documento->num_doc_relacionado}}</td>
                    </tr>
                @endif
                @if($documento->num_oc)
                    <tr>
                        <td><strong>Orden de compra:</strong></td>
                        <td style="width:150mm">{{$documento->num_oc}}</td>
                    </tr>
                @endif
            </table>
        </div>
        <div class="borde info-usuario" style="margin-top: 5mm">
            <table cellpadding="0">
                <tr>
                    <td>TRANSPORTE:</td>
                </tr>
                @if($documento->categoria_vehiculo != 'M1_L')
                    @if($documento->codigo_transporte == '01')
                    <tr>
                        <td style="width: 170px"><strong>Razón social:</strong></td>
                        <td style="width: 170px">{{$documento->razon_social_transportista}}</td>
                    </tr>
                    <tr>
                        <td style="width: 170px"><strong>Ruc:</strong></td>
                        <td style="width: 170px">{{ $documento->num_doc_transportista }}</td>
                    </tr>
                    @elseif($documento->codigo_transporte == '02')
                    <tr>
                        <td><strong>Placa de vehículo:</strong></td>
                        <td style="width:150mm">{{$documento->placa_vehiculo}}</td>
                    </tr>
                    <tr>
                        <td><strong>DNI Conductor:</strong></td>
                        <td style="width:150mm">{{$documento->dni_conductor}}</td>
                    </tr>
                    @endif
                @else
                    <tr>
                        <td><strong>Indicador de traslado en vehículos de categoría M1 o L:</strong></td>
                        <td style="width:150mm">SÍ</td>
                    </tr>
                    <tr>
                        <td><strong>Placa de vehículo:</strong> {{$documento->placa_vehiculo}}</td>
                        <td style="width:150mm"></td>
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
                    <td>Und</td>
                    <td>Cantidad</td>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td style="width: 5mm">{{$item->num_item}}</td>
                        <td style="width: 20mm">{{$item->codigo}}</td>
                        <td style="width: 110mm">{!! $item->descripcion !!}</td>
                        <td style="width: 20mm">{{$item->unidad_medida}}</td>
                        <td style="width: 18mm">{{$item->cantidad}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <table class="footer">
            <tr>
                <td class="footer-l">
                    @if($documento->qr)
                        <img class="qr" src="images/qr/{{$documento->qr}}">
                    @endif
                    <p>Representación Impresa de la {{$documento->titulo_doc}} Electrónica <br>
                        Código Hash: {{$documento->hash}} <br>
                        Para consultar el comprobante ingresar a : {{url('consulta')}}</p>
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
            padding: 8px 45px;
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
