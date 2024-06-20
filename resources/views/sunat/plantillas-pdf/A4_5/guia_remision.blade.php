@extends('sunat.plantillas-pdf.main')
@section('titulo','Guía de remisión')
@section('contenido')
    <div class="header">
        <div class="header-ruc">
            <h3 style="color: yellow;text-transform: uppercase">{{$documento->titulo_doc}}<br><span>ELECTRÓNICA</span></h3>
            <h4 style="color: yellow">{{$documento->correlativo}}</h4>
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
                <td><span style="font-weight: bold">DESTINATARIO</span></td>
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
    <div class="usuario">
        <table cellpadding="0">
            <tr>
                <td><span style="font-weight: bold">ENVÍO</span></td>
            </tr>
            <tr>
                <td style="width: 100px"><strong>Fecha emisión:</strong></td>
                <td style="width: 100px">{{ date('d-m-Y',strtotime($documento->fecha_emision)) }}</td>
                <td style="width: 150px"><strong>Fecha de inicio traslado:</strong></td>
                <td style="width: 150px">{{ date('d-m-Y',strtotime($documento->fecha_traslado)) }}</td>

            </tr>
            <tr>
                <td style="width: 150px"><strong>Tipo transporte:</strong></td>
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
    <div class="usuario">
        <table cellpadding="0">
            <tr>
                <td><span style="font-weight: bold">TRANSPORTE:</span></td>
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
    <div class="div-table-header">
    </div>
    <table class="items" cellpadding="0">
        <thead>
        <tr class="table-header">
            <td><strong>#</strong></td>
            <td><strong>CÓD.</strong></td>
            <td><strong>DESCRIPCIÓN</strong></td>
            <td><strong>UND</strong></td>
            <td><strong>CANTIDAD</strong></td>
        </tr>
        </thead>
        <tbody>
        @foreach($items as $item)
            <tr class="items-tr">
                <td style="width: 5mm">{{$item->num_item}}</td>
                <td style="width: 20mm">{{$item->codigo}}</td>
                <td style="width: 115mm">{!! $item->descripcion !!}</td>
                <td style="width: 20mm">{{$item->unidad_medida}}</td>
                <td style="width: 18mm">{{$item->cantidad}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="body">
        <table class="footer">
            <tr>
                <td class="footer-l">
                    @if($documento->qr)
                        <img style="width: 90px; float: left" class="qr" src="images/qr/{{$documento->qr}}">
                    @endif
                    <p style="margin-top: 30px">Representación Impresa de la {{$documento->titulo_doc}} Electrónica <br>
                        Código Hash: {{$documento->hash}} <br>
                        Para consultar el comprobante ingresar a : {{url('consulta')}}</p>
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
            color: black;
        }
        .header{
            width: 180mm;
            margin-bottom: 5mm;
            padding: 20px;
            border-top: 15px solid #ffca11;
        }
        .header-ruc{
            width: 260px;
            text-align: center;
            background: black;
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
            width: 190mm; background: #ffca11; padding: 2mm 5mm; text-align: center; height: 5mm
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
            color: black;
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
