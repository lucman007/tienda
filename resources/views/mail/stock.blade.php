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
    <table>
        @foreach($mensajes as $mensaje)
        <tr class="items-tr" style="background: {{$mensaje['bg_color']}}">
            <td>{{$mensaje['mensaje']}}</td>
        </tr>
        @endforeach
    </table>
</div>
<br>
</body>
<style>
    p,td{
        font-size: 8pt;
    }
    table{
        width:50mm;
    }
    .items-tr td{
        border: 1px solid #575757;
        padding: 5px 2px 5px 5px;
        text-align: center;
    }
</style>
</html>
