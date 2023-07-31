@extends('sunat.plantillas-pdf.main')
@section('titulo','Guía de remisión')
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
                    {{$emisor->razon_social}}<br>R.U.C. {{$emisor->ruc}}<br>{{$emisor->direccion}}, {{$emisor->urbanizacion==''?'':$emisor->urbanizacion.','}} {{$emisor->provincia}},
                    {{$emisor->departamento}}, {{$emisor->distrito}} <br> {{$emisor->telefono_1}} / {{$emisor->telefono_2==''?'':$emisor->telefono_2.' / '}}{{$emisor->email}} <br>
                    {{$emisor->texto_publicitario}} <br>
                    <strong><span style="text-transform: uppercase;">{{$documento->titulo_doc}} ELECTRÓNICA {{$documento->serie_correlativo}}</span></strong>
                </p>
            </div>
        </div>
    </div>
    <div class="body">
        <div class="info-usuario">
            <table cellpadding="0" style="width: 20mm">
                <tr>
                    <td>DESTINATARIO:</td>
                </tr>
                <tr>
                    <td><strong>Cliente:</strong></td>
                    <td>{{$usuario->razon_social}}</td>
                </tr>
                <tr>
                    <td colspan="2">{{ $usuario->persona->direccion }}</td>
                </tr>
                <tr>
                    <td colspan="2">RUC {{ $usuario->num_documento }}</td>
                </tr>
            </table>
        </div>
        <div class="info-usuario">
            <table cellpadding="0">
                <tr>
                    <td>ENVÍO:</td>
                </tr>
                <tr>
                    <td style="width: 80px"><strong>Fecha emisión:</strong></td>
                    <td style="width: 42mm">{{ date('dm/Y',strtotime($documento->fecha_emision)) }}</td>
                </tr>
                <tr>
                    <td style="width: 80px"><strong>Fecha traslado:</strong></td>
                    <td style="width: 42mm">{{ date('dm/Y',strtotime($documento->fecha_traslado)) }}</td>
                </tr>
                <tr>
                    <td style="width: 80px"><strong>Transporte:</strong></td>
                    <td style="width: 42mm">{{$documento->codigo_transporte=='01'?'PÚBLICO':'PRIVADO'}}</td>
                </tr>
                <tr>
                    <td style="width: 80px"><strong>Peso bruto:</strong></td>
                    <td style="width: 42mm">{{$documento->peso_bruto}} KG</td>
                </tr>
                <tr>
                    <td><strong>Motivo traslado:</strong></td>
                    <td style="width:80px">{{$documento->motivo_traslado}}</td>
                </tr>
                <tr>
                    <td style="width:80px"><strong>Núm. de bultos:</strong></td>
                    <td>{{$documento->cantidad_bultos}} UND</td>
                </tr>
                <tr>
                    <td><strong>Punto de partida:</strong></td>
                    <td style="width:38mm">{{$documento->emisor->direccion_resumida}}</td>
                </tr>
                <tr>
                    <td><strong>Punto de llegada:</strong></td>
                    <td style="width:38mm">{{$documento->direccion_llegada}}</td>
                </tr>
                @if($documento->doc_relacionado != -1)
                    <tr>
                    <td><strong>Documento relacionado:</strong></td>
                    <td style="width:42mm">{{$documento->num_doc_relacionado}}</td>
                    </tr>
                @endif
            </table>
        </div>
        <div class="info-usuario" style="margin-top: 5mm">
            <table cellpadding="0">
                <tr>
                    <td>TRANSPORTE:</td>
                </tr>
                @if($documento->categoria_vehiculo != 'M1_L')
                    @if($documento->codigo_transporte == '01')
                    <tr>
                        <td style="width: 80px"><strong>Razón social:</strong></td>
                        <td style="width: 42mm">{{$documento->razon_social_transportista}}</td>
                    </tr>
                    <tr>
                        <td style="width: 80px"><strong>Ruc:</strong></td>
                        <td style="width: 42mm">{{ $documento->num_doc_transportista }}</td>
                    </tr>
                    @elseif($documento->codigo_transporte == '02')
                    <tr>
                        <td style="width: 80px"><strong>Placa de vehículo:</strong></td>
                        <td style="width:42mm">{{$documento->placa_vehiculo}}</td>
                    </tr>
                    <tr>
                        <td style="width: 80px"><strong>DNI Conductor:</strong></td>
                        <td style="width:42mm">{{$documento->dni_conductor}}</td>
                    </tr>
                     @endif
                @else
                    <tr>
                        <td style="width: 80px"><strong>Vehículo M1 o L:</strong></td>
                        <td style="width:42mm">SÍ</td>
                    </tr>
                @endif
            </table>
        </div>
        <table class="items" cellpadding="0">
            <thead>
                <tr class="table-header">
                    <td>Descripción</td>
                    <td>Cantidad/Und</td>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td style="width: 38mm">{{$item->descripcion}}</td>
                        <td>{{$item->cantidad}} {{explode('/',$item->unidad_medida)[1]}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <table style="text-align: center; margin-top: 5mm">
            <tr>
                <td style="width: 60mm">Representación Impresa de la {{$documento->titulo_doc}} Electrónica <br>
                    Código Hash: {{$documento->hash}} <br><br>
                    Para consultar el comprobante ingresar a : <br>{{url('consulta')}}</td>
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
            width: 35mm;
            height: 20mm;
        }
        .header .info-emisor .texto{
            width: 35mm;
            position: absolute;
            text-align: center;
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
            width: 35mm;
            position: relative;
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
