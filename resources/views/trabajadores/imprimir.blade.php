<!doctype html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- CSS personalizado -->
    <link rel="stylesheet" href="{{asset('css/style_pdf.css')}}">
    <title>@yield('titulo') </title>
</head>
<body>
<div class="header">
    <h3>Pago de trabajador</h3>
    <div class="logo">
        <img src="images/logo.png">
    </div>
</div>
<div class="body">
    <div class="info-usuario">
        <table cellpadding="0">
            <tr>
                <td><strong>Nombre: </strong></td>
                <td>{{$trabajador->persona['nombre']}} {{$trabajador->persona['apellidos']}}</td>
            </tr>
        </table>
    </div>
    <div class="borde items">
        <table cellpadding="0">
            <tr class="table-header">
                <th scope="col"></th>
                <th style="width: 30mm" scope="col">Fecha</th>
                <th style="width: 30mm" scope="col">Caja</th>
                <th style="width: 50mm" scope="col">Tipo</th>
                <th style="width: 40mm" scope="col">N° comprobante</th>
                <th scope="col">Monto</th>
            </tr>
            @foreach($gastos as $gasto)
                <tr>
                    <td></td>
                    <td>{{date("d-m-Y",strtotime($gasto->fecha))}}</td>
                    <td>{{$gasto->caja}}</td>
                    <td>{{$gasto->tipo}}</td>
                    <td>{{$gasto->num_comprobante}}</td>
                    <td>{{$gasto->monto}}</td>
                </tr>
            @endforeach
        </table>
        <br>
        <table>
            <tr></tr>
            <tr>
                <td>Total sueldo:</td>
                <td>{{$total_pagado}}</td>
            </tr>
            <tr>
                <td>Total bonificaciones:</td>
                <td>{{$extras}}</td>
            </tr>
        </table>
    </div>

</div>
<div class="footer">

</div>

</body>
</html>

<style>

    .leyenda{
        width: 150mm;
        left: 25mm;
        position: absolute;
        bottom:-16mm;
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
        font-size: 8pt;
    }
    .borde{
        border: 1px solid black;
        border-radius: 5px;
        padding: 20px;
    }
    .info-emisor{
        width: 100mm;
        height: 20mm;
        padding: 20px;
    }
    .logo{
        width: 23mm;
        position: absolute;
        right: 0;
        top:6mm;

    }
    .logo img{
        width: 23mm;
        margin: 0;
    }
    .info-emisor .texto{
        width: 75mm;
        text-align: center;
        position: absolute;
        left: 25mm;
        top:12mm;
    }
    .info-ruc{
        position: absolute;
        top:5mm;
        right: 0;
        text-align: center;
        height: 20mm;
        padding: 25px 60px;
    }
    .info-usuario{
        width: 188mm;
        height: 14mm;
        margin-top: 5mm;
        position: relative;
    }
    .info-usuario p{
        line-height: 4mm;
    }
    .items{
        width: 188mm;
        margin-top: 5mm;
    }
    table{
        margin: 0;
        padding: 0;
    }
    .table-header td{
        border-bottom: 1px solid black;
        margin: 0;
    }
    .footer{
        position: absolute;
        bottom:5mm;
    }
    .footer-izquierda{
        width: 100mm;
        float: left;
        height: 15mm;
    }
    .footer-derecha{
        width: 100mm;
        float: left;
        height: 15mm;
    }
    .imagen-coche{
        position: absolute;
        right: 0;
        top:120mm;
    }
    ul{
        list-style-type: circle;
    }
    li {
        padding-left: 1.3em ;
    }

    li:before {
        content: "j";
        display: inline-block;
        margin-left: -1.3em;
        width: 1.3em;
    }
</style>