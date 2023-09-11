@extends('sunat.plantillas-pdf.main')
@section('titulo','Factura')
@section('contenido')
    <div class="header">
        <div class="info-emisor">
            @if($emisor->logo)
                <div class="logo">
                    <img src="{{'images/'.$emisor->logo}}">
                </div>
            @endif
            <div class="texto">
                <p><strong>{{$emisor->razon_social}}<br>R.U.C. {{$emisor->ruc}}</strong><br>{{$emisor->direccion}}, {{$emisor->provincia}},
                    {{$emisor->departamento}}, {{$emisor->distrito}} <br> {{$emisor->telefono_1}}/{{$emisor->email}} <br>
                    {{$emisor->texto_publicitario}}
                </p>
            </div>
        </div>
        <div class="info-ruc">
            <p>{{mb_strtoupper($documento->titulo_doc)}} ELECTRÓNICA N° {{$documento->serie_correlativo}}</p>
        </div>
    </div>
    <div class="body">
        <div class="info-usuario">
            <table cellpadding="0">
                <tr>
                    <td>DESTINATARIO:</td>
                </tr>
                <tr>
                    <td><strong>Razón social:</strong></td>
                    <td style="width:110mm">{{$usuario->razon_social}}</td>
                </tr>
                <tr>
                    <td><strong>Dirección:</strong></td>
                    <td style="width:110mm">{{ $usuario->persona->direccion }}</td>
                </tr>
                <tr>
                    <td><strong>Ruc:</strong></td>
                    <td>{{ $usuario->num_documento }}</td>
                </tr>
            </table>
        </div>
        <div class="info-usuario">
            <table cellpadding="0">
                <tr>
                    <td>ENVÍO:</td>
                </tr>
                <tr>
                    <td style="width: 100px"><strong>Fecha de emisión:</strong></td>
                    <td style="width: 100px">{{ date('d-m-Y',strtotime($documento->fecha_emision)) }}</td>
                    <td style="width: 100px"><strong>Fecha traslado:</strong></td>
                    <td style="width: 150px">{{ date('d-m-Y',strtotime($documento->fecha_traslado)) }}</td>

                </tr>
                <tr>
                    <td style="width: 100px"><strong>Transporte:</strong></td>
                    <td style="width: 100px">{{$documento->codigo_transporte=='01'?'PÚBLICO':'PRIVADO'}}</td>
                    <td style="width: 100px"><strong>Peso bruto:</strong></td>
                    <td style="width: 100px">{{$documento->peso_bruto}} KG</td>
                </tr>
                <tr>
                    <td><strong>Motivo de traslado:</strong></td>
                    <td style="width:100px">{{$documento->motivo_traslado}}</td>
                    <td style="width:100px"><strong>Número de bultos:</strong></td>
                    <td>{{$documento->cantidad_bultos}} UND</td>
                </tr>
                <tr>
                    <td><strong>Punto de partida:</strong></td>
                    <td style="width:110mm">{{$documento->direccion_partida?$documento->direccion_partida:$documento->emisor->direccion_resumida}}</td>
                </tr>
                <tr>
                    <td><strong>Punto de llegada:</strong></td>
                    <td style="width:110mm">{{$documento->direccion_llegada}}</td>
                </tr>
                @if($documento->doc_relacionado != -1)
                    <tr>
                    <td><strong>Documento relacionado:</strong></td>
                    <td style="width:110mm">{{$documento->num_doc_relacionado}}</td>
                    </tr>
                @endif
            </table>
        </div>
        <div class="info-usuario" style="margin-top: 5mm">
            <table cellpadding="0">
                <tr>
                    <td>TRANSPORTE:</td>
                </tr>
                @if($documento->codigo_transporte == '01')
                <tr>
                    <td><strong>Razón social:</strong></td>
                    <td style="width: 110mm">{{$documento->razon_social_transportista}}</td>
                </tr>
                <tr>
                    <td><strong>Ruc:</strong></td>
                    <td style="width: 110mm">{{ $documento->num_doc_transportista }}</td>
                </tr>
                @elseif($documento->codigo_transporte == '02')
                <tr>
                    <td><strong>Placa de vehículo:</strong></td>
                    <td style="width:110mm">{{$documento->placa_vehiculo}}</td>
                </tr>
                <tr>
                    <td><strong>DNI Conductor:</strong></td>
                    <td style="width:110mm">{{$documento->dni_conductor}}</td>
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
                        <td style="width: 12mm">{{$item->codigo}}</td>
                        <td style="width: 75mm">{!! $item->descripcion !!}</td>
                        <td style="width: 18mm">{{$item->unidad_medida}}</td>
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
            font-size: 7pt;
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
            height: 25mm;
            border-bottom: 1px solid #CCC;
            margin-bottom: 5mm;
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
            width: 80mm;
            height: 20mm;
        }
        .header .info-emisor .logo img{
            width: 15mm;
            text-align: center;
            margin-top: 3mm;
        }
        .header .info-emisor .texto{
            width: 65mm;
            position: absolute;
            right: -5mm;
        }

        .body{
            position: relative;
            width: 100mm;
            float: left;
        }

        .body .info-usuario{
            width: 100mm;
            height: 20mm;
        }
        .body .info-usuario p{
            width: 100mm;
            line-height: 4mm;
        }

        .body .items {
            width: 100mm;
            margin-top: -5mm;
            position: relative;
        }
        .footer{
            width: 100mm;
            margin-top: 5mm;
        }
        .footer .footer-l{
            width: 70%;
            float: left;
        }
        .footer .footer-r{
            width: 35%;
            float:left;
        }

        .leyenda{
            margin-top: 3mm;
            text-align: center;
        }
    </style>

@endsection
