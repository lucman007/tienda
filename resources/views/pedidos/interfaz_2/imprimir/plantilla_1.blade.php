@extends('sunat.plantillas-pdf.main')
@section('titulo','Orden')
@section('contenido')
    <div class="body">
        <div class="info-usuario">
            <p>
                Fecha: {{ date('d/m/Y',strtotime($orden->fecha)) }} <br>
                Pedido N° {{$orden->idorden}} /
                Cliente: {{$orden->cliente->persona->nombre}}
            </p>
        </div>
        <table class="items" cellpadding="0">
            <thead>
            <tr class="table-header">
                <td>Item</td>
                <td>Código</td>
                <td>Descripción</td>
                <td>Cantidad</td>
                <td>Precio unitario</td>
                <td>Importe</td>
            </tr>
            </thead>
            <tbody>
            @foreach($orden->productos as $item)
                <tr class="item-borde">
                    <td style="width: 5mm">{{$item->detalle->num_item}}</td>
                    <td style="width: 20mm">{{$item->cod_producto}}</td>
                    <td style="width: 105mm">{{$item->nombre}} {{$item->detalle->descripcion}}</td>
                    <td style="width: 20mm">{{$item->detalle->cantidad}} {{explode('/',$item->unidad_medida)[1]}}</td>
                    <td style="width: 20mm; text-align: right">{{$item->detalle->monto}}</td>
                    <td style="width: 20mm; text-align: right">{{number_format(round($item->detalle->monto * $item->detalle->cantidad,2),2)}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <p>Total: {{$orden->moneda}} {{$orden->total}}</p>
    </div>


    <style>

        .item-borde td{
            border-bottom:1px solid #8C8C8C;
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
            font-size: 7.5pt;
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

        .body{
            position: relative;
            width: 200mm;
            height: 100mm;
            float: left;
            margin-top: 5mm;
        }

        .body .info-usuario{
            width: 188mm;
            margin-bottom: 5mm;
        }
        .body .info-usuario p{
            line-height: 4mm;
        }

        .body .items {
            width: 200mm;
            position: relative;
        }

        .leyenda{
            width: 200mm;
            margin-top: 3mm;
            text-align: center;
        }
    </style>

@endsection
