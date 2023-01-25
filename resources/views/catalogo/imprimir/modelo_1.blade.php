@php /** Modelo 1 - habilitado**/@endphp
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
<div class="portada">
    <div class="imagen-portada">
        @if($catalogo->imagen_portada)
        <img src="{{$catalogo->imagen_portada}}">
        @endif
    </div>
    <div class="titulo-wrapper">
        <h1 class="titulo">{{$catalogo->titulo}}</h1>
    </div>
    <div class="subtitulo-wrapper">
        <h2 class="subtitulo">{{$catalogo->subtitulo}}</h2>
    </div>
    <div class="logo">
        @if($emisor->logo)
            <img src="{{'images/'.$emisor->logo}}">
        @endif
    </div>
    <div class="texto-info">
        {{$emisor->direccion}}, {{$emisor->urbanizacion}}, {{$emisor->provincia}},
        {{$emisor->departamento}}, {{$emisor->distrito}} <br> {{$emisor->telefono_1}} / {{$emisor->email}} <br>
        {{$emisor->texto_publicitario}} <br>
        <strong>Cta. detracciones:</strong> {{$emisor->cuenta_detracciones}} <br>
        <strong>Cta. Soles:</strong> {{$emisor->cuenta_1}} <br>
        <strong>Cta. Dólares:</strong> {{$emisor->cuenta_2}} <br>
    </div>
</div>
        @php
        $num_loop_row = 1;
        $num_loop_producto = 0;
        $productos_por_pagina = 15;
        $offset_top = 0;
        $close_row_flag = true;
        $close_page_flag = true;
        @endphp
        @foreach($catalogo->productos as $producto)
            @php
            if($num_loop_producto == 0){
            echo '<div class="page">';
            }
            @endphp
            @php
                switch($num_loop_row){
                    case 1:
                    echo '<div class="row" style="top: '.$offset_top.'">';
                    $class='p-izquierda';
                    break;
                    case 2:
                    $class='p-centro';
                    break;
                    case 3:
                    $class='p-derecha';
                    break;
                }
            @endphp
            <div class="thumb {{$class}}">
                @if($producto->imagen)
                    <img style="width:100%;" src="{{$producto->imagen}}" alt="">
                @else
                    <img src="{{'images/no-image.jpg'}}">
                @endif
                @if($catalogo->precios)
                <div class="precio">
                    {{$producto->moneda=='PEN'?'S/':'USD'}} {{$producto->precio}}
                </div>
                @endif
                <div class="caption">
                    <p>{{$producto->cod_producto==''?'':'CÓD. '.$producto->cod_producto.' -'}} {{$producto->nombre}}<br><span>{{\Illuminate\Support\Str::words($producto->presentacion,40,'...')}}</span></p>
                </div>
            </div>
            @php
            if($num_loop_row == 3){
                //CERRAR ROW
                echo '</div>';
                $offset_top = '50mm';
                $num_loop_row = 1;
                $close_row_flag = false;
            } else {
                $num_loop_row++;
                $close_row_flag = true;
            }
            $num_loop_producto++;
            @endphp
            @php
                if($num_loop_producto  == $productos_por_pagina){
                    //CERRAR PAGE
                    echo '</div>';
                    $num_loop_producto = 0;
                    $offset_top = '0';
                    $close_page_flag = false;
                } else {
                    $close_page_flag = true;
                }
            @endphp
        @endforeach
        @php
            //CERRAR ROW Y PAGE
            if($close_row_flag){
                echo '</div>';
            }
            if($close_page_flag){
                echo '</div>';
            }
        @endphp
</body>
</html>
<style>
    html, body{
        padding: 0;
        margin: 0;
    }
    .page{
        width: 200mm;
        height: 280mm;
    }
    .row{
        position: relative;
    }
    .thumb{
        border:2px solid #ffca11;
        margin: 5mm;
        float: left;
        width: 60mm;
        height: 45mm;
        overflow: hidden;
        position: absolute;
    }
    .p-izquierda{
        left: -2mm;
    }
    .p-centro{
        left: 64mm;
    }
    .p-derecha{
        right: -1mm;
    }
    .caption{
        position: absolute;
        bottom: 0;
        background: #ffca11;
        width: 105%;
        text-align: center;
        padding: 0 10px 10px;
    }
    .imagen-portada{
        width: 220mm;
        height: 150mm;
        background: #ffca11;
        margin: -5mm 0 0 -5mm;
    }
    .imagen-portada img {
        width: 100%;
        height: 100%;
    }
    .titulo-wrapper{
        position: relative;
        margin-top: -20mm;
        margin-left: 15mm;
    }
    .titulo{
        font-size: 80pt;
        font-family: impact;

    }
    .subtitulo-wrapper{
        margin-top: -10mm;
        position: relative;
        margin-left: 15mm;
    }
    .subtitulo{

    }
    .caption p{
        font-size: 9pt;
        vertical-align: middle;
    }
    .caption p span{
        font-size: 7pt;
    }
    .precio{
        position: absolute;
        top: 2mm;
        left: 2mm;
        background: #ffca11;
        padding: 5px;
        border-radius: 5px;
    }
    .portada{
        width: 220mm;
        height: 300mm;
        background: orangered;
        margin-left: -5mm;
    }
    .logo{
        margin: 70mm 0 0 140mm;
        position: relative;
        background: red;
        width: 60mm;
        text-align: center;
    }
    .texto-info{
        width: 120mm;
        position: absolute;
        bottom: 30mm;
        left:15mm;
        font-size: 9pt;
    }

</style>