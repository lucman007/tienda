<!doctype html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <base href=""/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Font awesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css"
          integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">
    <!-- app.css -->
    <link rel="stylesheet" href="{{asset('css/app.css')}}">
    <!-- CSS personalizado -->
    <link rel="stylesheet" href="{{asset('css/admin.css')}}">
    <title>Facfa | @yield('titulo') </title>
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
    </script>
</head>
<body>
<main class="wrapper app" v-cloak>
    @yield('contenido')
</main>
<footer class="my-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 mt-4 text-center">
                <p class="copy">Sistema desarrollado por <a href="https://coditec.pe" target="_blank"> coditec.pe</a> | © {{date('Y')}}</p>
            </div>
        </div>
    </div>
</footer>
<script src="{{asset('js/app.js')}}"></script>
@yield('script')
</body>
</html>