<!doctype html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <base href=""/>
    <link rel="shortcut icon" href="{{url('images/favicon.ico')}}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
    <!-- app.css -->
    <link rel="stylesheet" href="{{asset('css/app.css')}}">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css">
    <!-- CSS personalizado -->
    @if(json_decode(cache('config')['interfaz_pedidos'], true)['tipo'] == 'modo_2')
        <link rel="stylesheet" href="{{asset('css/facilito.css?v='.filemtime('css/facilito.css'))}}">
    @else
        <link rel="stylesheet" href="{{asset('css/admin.css?v='.filemtime('css/admin.css'))}}">
    @endif
    <link rel="stylesheet" href="{{asset('css/OverlayScrollbars.min.css')}}">
    <title>Facsy | @yield('titulo') </title>
    @laravelPWA
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
    </script>
</head>
<body @if(isset($idbody)) id="{{$idbody}}" @endif>
@php
    $color = json_decode(cache('config')['interfaz'], true)['text_top_header_style']??'';
    $interfaz = json_decode(cache('config')['interfaz_pedidos'], true)['tipo'];
    $agent = new \Jenssegers\Agent\Agent();
    if($interfaz == 'modo_2'){
        $ruta = 'facilito/';
    } else {
        $ruta = '';
    }
@endphp
<div class="app_menu" v-cloak>
    @if($interfaz == 'modo_2')
        <header class="fixed-top" style="display: flex; background: #742284;background: linear-gradient(180deg, rgba(116,34,132,1) 0%, rgba(82,34,108,1) 100%);">
            <div style="width: 150px; padding: 10px" class="d-none d-lg-block">
                <a href="/">
                    <img style="width:100%;margin-top: 8px;" src="{{url('images/logo-facilito.png')}}" alt="">
                </a>
            </div>
            <div style="width: 100%">
                <div class="top-header d-none d-lg-block" style="background: linear-gradient(180deg, rgba(116,34,132,1) 0%, rgba(82,34,108,1) 100%);">
                    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
                        <div class="row no-gutters">
                            <div class="col-sm-12">
                                <div class="float-left">
                                    <p id="liveclock" style="{{$color}}"></p>
                                    @if(!$agent->isMobile())
                                        <tipo-cambio :cambio="{{json_encode(cache('opciones'))}}" :color="'{{$color}}'"></tipo-cambio>
                                    @endif
                                </div>
                                @if(!(json_decode(cache('config')['conexion'], true)['esProduccion']))
                                    <div class="ml-2 alert alert-danger float-right isDemo" style="background-color: #f72639; color: white">
                                        MODO DEMO
                                    </div>
                                @endif
                                <div class="float-left float-md-right d-flex">
                                    <b-dropdown id="dropdown-1" text="Dropdown Button" variant="outline-secondary" class="m-md-2 float-right btn-admin">
                                        <template v-slot:button-content>
                                            <span style="{{$color}}">¡Hola! {{$usuario->nombre}} <i class="fas fa-user-circle"></i></span>
                                        </template>
                                        <b-dropdown-item href="{{url('logout')}}"><i class="fas fa-power-off"></i> Cerrar sesión</b-dropdown-item>
                                    </b-dropdown>
                                </div>
                                <div class="float-right">
                                    <panel-notificacion v-on:disabledventas="disabled_ventas" ref="panelNotificacion"></panel-notificacion>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="middle-header" style="background: linear-gradient(180deg, rgba(116,34,132,1) 0%, rgba(82,34,108,1) 100%);">
                    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
                        <div>
                            <b-navbar toggleable="lg" type="dark">
                                <b-navbar-brand class="d-lg-none" href="/"><img src="{{url('images/logo-facilito.png')}}" alt=""></b-navbar-brand>
                                <b-navbar-toggle target="nav-collapse"></b-navbar-toggle>
                                <b-collapse id="nav-collapse" is-nav>
                                    <div class="profile mt-2 mb-4 d-flex d-lg-none">
                                        <div class="profile-avatar">
                                            <img src="{{url('images/user-icon.png')}}" alt="">
                                        </div>
                                        <div class="profile-text ml-2">
                                            ¡Hola! {{$usuario->nombre}}
                                        </div>
                                        <div>
                                            <a href="{{url('/notificaciones')}}">
                                                <panel-notificacion ref="panelNotificacion"></panel-notificacion>
                                            </a>
                                        </div>
                                        @if(!(json_decode(cache('config')['conexion'], true)['esProduccion']))
                                            <div class="ml-2 alert alert-danger float-right isDemo" style="background-color: #f72639; color: white">
                                                DEMO
                                            </div>
                                        @endif
                                    </div>
                                    <b-navbar-nav>
                                        <b-nav-item @cannot('Pedido: crear pedido') class="disabled" disabled @endcannot href="{{action('PedidoController@index')}}">
                                            <img src="{{url('images/menubar/icons/'.$ruta.'caja.png')}}" alt="">
                                            Ventas
                                        </b-nav-item>
                                        <b-nav-item-dropdown @cannot('Facturación') class="disabled" disabled @endcannot left>
                                            <template v-slot:button-content>
                                                <img src="{{url('images/menubar/icons/'.$ruta.'facturar.png')}}" alt="">
                                                Comprobantes
                                            </template>
                                            <b-dropdown-item @cannot('Facturación: facturar') class="disabled" disabled @endcannot href="{{action('VentaController@registrar')}}"><i class="fas fa-file-invoice-dollar"></i> Facturación avanzada</b-dropdown-item>
                                            <b-dropdown-item @cannot('Facturación: facturar') class="disabled" disabled @endcannot href="{{action('ComprobanteController@comprobantes')}}"><i class="fas fa-list-ul"></i> Comprobantes emitidos</b-dropdown-item>
                                            <b-dropdown-item @cannot('Facturación: guía') class="disabled" disabled @endcannot href="{{action('GuiaController@index')}}"><i class="fas fa-shipping-fast"></i> Guía de remisión electrónica</b-dropdown-item>
                                        </b-nav-item-dropdown>
                                        <b-nav-item-dropdown @cannot('Caja') class="disabled" disabled @endcannot left>
                                            <template v-slot:button-content>
                                                <img src="{{url('images/menubar/icons/'.$ruta.'registradora.png')}}" alt="">
                                                Caja
                                            </template>
                                            <b-dropdown-item @cannot('Caja: gestionar') class="disabled" disabled @endcannot href="{{action('CajaController@index')}}"><i class="fas fa-cash-register"></i> Turnos</b-dropdown-item>
                                            <b-dropdown-item @cannot('Caja: egresos') class="disabled" disabled @endcannot href="{{action('GastoController@index')}}"><i class="fas fa-coins"></i> Movimientos</b-dropdown-item>
                                            <b-dropdown-item @cannot('Caja') class="disabled" disabled @endcannot href="{{action('CreditoController@index')}}"><i class="fas fa-money-bill-wave"></i> Créditos</b-dropdown-item>
                                        </b-nav-item-dropdown>
                                        <b-nav-item-dropdown @cannot('Inventario') class="disabled" disabled @endcannot left>
                                            <template v-slot:button-content>
                                                <img src="{{url('images/menubar/icons/'.$ruta.'receivings.png')}}" alt="">
                                                Inventario
                                            </template>
                                            <b-dropdown-item @cannot('Inventario: productos') class="disabled" disabled @endcannot href="{{action('ProductoController@index')}}"><i class="fas fa-gem"></i> Productos</b-dropdown-item>
                                            <b-dropdown-item @cannot('Mantenimiento: categorías') class="disabled" disabled @endcannot href="{{action('CategoriaController@index')}}"><i class="fas fa-th-list"></i> Categorías</b-dropdown-item>
                                            <b-dropdown-item @cannot('Inventario: almacenes') class="disabled" disabled @endcannot href="{{action('AlmacenController@index')}}"><i class="fas fa-dolly-flatbed"></i> Almacenes</b-dropdown-item>
                                            <b-dropdown-item @cannot('Inventario: requerimientos') class="disabled" disabled @endcannot href="{{action('RequerimientoController@index')}}"><i class="fas fa-box-open"></i> Requerimientos</b-dropdown-item>
                                            <b-dropdown-item @cannot('Inventario: productos') class="disabled" disabled @endcannot href="{{url('caja/movimientos?tipo=devoluciones')}}"><i class="fas fa-undo"></i> Devoluciones</b-dropdown-item>
                                            <b-dropdown-item @cannot('Catalogos') class="disabled" disabled @endcannot href="{{url('catalogos')}}"><i class="fas fa-th-large"></i> Catálogos</b-dropdown-item>
                                        </b-nav-item-dropdown>
                                        <b-nav-item @cannot('Cotizaciones') class="disabled" disabled @endcannot href="{{action('PresupuestoController@index')}}">
                                            <img src="{{url('images/menubar/icons/'.$ruta.'cotizar.png')}}" alt="">
                                            Cotizaciones
                                        </b-nav-item>
                                        <b-nav-item-dropdown @cannot('Reportes') class="disabled" disabled @endcannot left>
                                            <template v-slot:button-content>
                                                <img src="{{url('images/menubar/icons/'.$ruta.'reports.png')}}" alt="">
                                                Reportes
                                            </template>
                                            <b-dropdown-item @cannot('Reportes: ventas') class="disabled" disabled @endcannot href="{{action('ReporteController@reporte_ventas')}}"><i class="fas fa-chart-line"></i> Reporte de ventas</b-dropdown-item>
                                            <b-dropdown-item @cannot('Reportes: gastos') class="disabled" disabled @endcannot href="{{url('/reportes/gastos/diario').'/'.date('Y-m')}}"><i class="fas fa-coins"></i> Reporte de gastos</b-dropdown-item>
                                            <b-dropdown-item @cannot('Reportes: ventas') class="disabled" disabled @endcannot href="{{action('ReporteController@reporte_caja')}}"><i class="fas fa-cash-register"></i> Reporte de caja</b-dropdown-item>
                                            <b-dropdown-item @cannot('Reportes: comprobantes') class="disabled" disabled @endcannot href="{{action('ReporteController@reporte_comprobantes')}}"><i class="fas fa-file-invoice-dollar"></i> Reporte de comprobantes</b-dropdown-item>
                                            <b-dropdown-item @cannot('Reportes: productos') class="disabled" disabled @endcannot href="{{url('/reportes/productos/resumen-diario')}}"><i class="fas fa-dolly"></i> Reporte de productos</b-dropdown-item>
                                        </b-nav-item-dropdown>
                                        <b-nav-item-dropdown @cannot('Mantenimiento') class="disabled" disabled @endcannot left>
                                            <template v-slot:button-content>
                                                <img src="{{url('images/menubar/icons/'.$ruta.'config.png')}}" alt="">
                                                Mantenimiento
                                            </template>
                                            <b-dropdown-item @cannot('Clientes') class="disabled" disabled @endcannot href="{{action('ClienteController@index')}}"><i class="fas fa-user-tag"></i> Clientes</b-dropdown-item>
                                            <b-dropdown-item @cannot('Mantenimiento: empleados') class="disabled" disabled @endcannot href="{{action('TrabajadorController@index')}}"><i class="fas fa-users"></i> Empleados</b-dropdown-item>
                                            <b-dropdown-item @cannot('Mantenimiento: proveedores') class="disabled" disabled @endcannot href="{{action('ProveedorController@index')}}"><i class="fas fa-user-tie"></i> Proveedores</b-dropdown-item>
                                            <b-dropdown-item @cannot('Configuración') class="disabled" disabled style="display: none;" @endcannot href="{{action('ConfiguracionController@index')}}"><i class="fas fa-cogs"></i> Configuración</b-dropdown-item>
                                        </b-nav-item-dropdown>
                                        <b-nav-item-dropdown left class="d-block d-lg-none">
                                            <template v-slot:button-content>
                                                <img src="{{url('images/menubar/icons/facilito/off.png')}}" alt="">
                                                Cerrar sesion
                                            </template>
                                            <b-dropdown-item href="{{url('logout')}}"><i class="fas fa-power-off"></i> Cerrar sesion</b-dropdown-item>
                                        </b-nav-item-dropdown>
                                    </b-navbar-nav>
                                </b-collapse>
                            </b-navbar>
                        </div>
                    </div>
                </div>
            </div>
        </header>
    @else
    <header class="fixed-top">
        <div class="top-header d-none d-lg-block" style="{{json_decode(cache('config')['interfaz'], true)['top_header_style']}}">
            <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
                <div class="row no-gutters">
                    <div class="col-sm-12">
                        <div class="float-left">
                            <p id="liveclock" style="{{$color}}"></p>
                            @if(!$agent->isMobile())
                            <tipo-cambio :cambio="{{json_encode(cache('opciones'))}}" :color="'{{$color}}'"></tipo-cambio>
                            @endif
                        </div>
                        @if(!(json_decode(cache('config')['conexion'], true)['esProduccion']))
                            <div class="ml-2 alert alert-danger float-right isDemo" style="background-color: #f72639; color: white">
                                MODO DEMO
                            </div>
                        @endif
                        <div class="float-left float-md-right d-flex">
                            <b-dropdown id="dropdown-1" text="Dropdown Button" variant="outline-secondary" class="m-md-2 float-right btn-admin">
                                <template v-slot:button-content>
                                    <span style="{{$color}}">¡Hola! {{$usuario->nombre}} <i class="fas fa-user-circle"></i></span>
                                </template>
                                <b-dropdown-item href="{{url('logout')}}"><i class="fas fa-power-off"></i> Cerrar sesión</b-dropdown-item>
                            </b-dropdown>
                        </div>
                        <div class="float-right">
                            <panel-notificacion v-on:disabledventas="disabled_ventas" ref="panelNotificacion"></panel-notificacion>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="middle-header" style="{{json_decode(cache('config')['interfaz'], true)['bottom_header_style']}}">
            <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
                <div>
                    <b-navbar toggleable="lg" type="dark">
                        <b-navbar-brand href="/"><img src="{{url('images/'.cache('config')['logo_sistema'])}}" alt=""></b-navbar-brand>
                        <b-navbar-toggle target="nav-collapse"></b-navbar-toggle>
                        <b-collapse id="nav-collapse" is-nav>
                            <div class="profile mt-2 mb-4 d-flex d-lg-none">
                                <div class="profile-avatar">
                                    <img src="{{url('images/user-icon.png')}}" alt="">
                                </div>
                                <div class="profile-text ml-2">
                                    ¡Hola! {{$usuario->nombre}}
                                </div>
                                <div>
                                    <a href="{{url('/notificaciones')}}">
                                        <panel-notificacion ref="panelNotificacion"></panel-notificacion>
                                    </a>
                                </div>
                                @if(!(json_decode(cache('config')['conexion'], true)['esProduccion']))
                                    <div class="ml-2 alert alert-danger float-right isDemo" style="background-color: #f72639; color: white">
                                        DEMO
                                    </div>
                                @endif
                            </div>
                            @if(json_decode(cache('config')['interfaz_pedidos'], true)['tipo'] == 'modo_4')
                                <b-navbar-nav>
                                    <b-nav-item @cannot('Facturación: facturar') class="disabled" disabled @endcannot href="{{action('VentaController@registrar')}}">
                                        <img src="{{url('images/menubar/icons/caja.png')}}" alt="">
                                        Facturar
                                    </b-nav-item>
                                    <b-nav-item-dropdown @cannot('Facturación') class="disabled" disabled @endcannot left>
                                        <template v-slot:button-content>
                                            <img src="{{url('images/menubar/icons/facturar.png')}}" alt="">
                                            Comprobantes
                                        </template>
                                        <b-dropdown-item @cannot('Facturación: comprobantes') class="disabled" disabled @endcannot href="{{action('ComprobanteController@comprobantes')}}"><i class="fas fa-file-invoice-dollar"></i> Comprobantes emitidos</b-dropdown-item>
                                        <b-dropdown-item @cannot('Facturación: guía') class="disabled" disabled @endcannot href="{{action('GuiaController@index')}}"><i class="fas fa-shipping-fast"></i> Guía de remisión electrónica</b-dropdown-item>
                                    </b-nav-item-dropdown>
                                    <b-nav-item @cannot('Cotizaciones') class="disabled" disabled @endcannot href="{{action('PresupuestoController@index')}}">
                                        <img src="{{url('images/menubar/icons/cotizar.png')}}" alt="">
                                        Cotizaciones
                                    </b-nav-item>
                                    @can('Producción')
                                        <b-nav-item href="{{url('produccion/pendientes')}}">
                                            <img src="{{url('images/menubar/icons/produccion.png')}}" alt="">
                                            Produccion
                                        </b-nav-item>
                                    @endcan
                                    <b-nav-item-dropdown @cannot('Inventario') class="disabled" disabled @endcannot left>
                                        <template v-slot:button-content>
                                            <img src="{{url('images/menubar/icons/receivings.png')}}" alt="">
                                            Inventario
                                        </template>
                                        <b-dropdown-item @cannot('Inventario: productos') class="disabled" disabled @endcannot href="{{action('ProductoController@index')}}"><i class="fas fa-gem"></i> Productos</b-dropdown-item>
                                        <b-dropdown-item @cannot('Mantenimiento: categorías') class="disabled" disabled @endcannot href="{{action('CategoriaController@index')}}"><i class="fas fa-th-list"></i> Categorías</b-dropdown-item>
                                        <b-dropdown-item @cannot('Inventario: requerimientos') class="disabled" disabled @endcannot href="{{action('RequerimientoController@index')}}"><i class="fas fa-box-open"></i> Requerimientos</b-dropdown-item>
                                        <b-dropdown-item @cannot('Catalogos') class="disabled" disabled @endcannot href="{{url('catalogos')}}"><i class="fas fa-th-large"></i> Catálogos</b-dropdown-item>
                                    </b-nav-item-dropdown>
                                    <b-nav-item-dropdown @cannot('Reportes') class="disabled" disabled @endcannot left>
                                        <template v-slot:button-content>
                                            <img src="{{url('images/menubar/icons/reports.png')}}" alt="">
                                            Reportes
                                        </template>
                                        <b-dropdown-item @cannot('Reportes: ventas') class="disabled" disabled @endcannot href="{{action('ReporteController@reporte_ventas')}}"><i class="fas fa-chart-line"></i> Reporte de ventas</b-dropdown-item>
                                        <b-dropdown-item @cannot('Reportes: gastos') class="disabled" disabled @endcannot href="{{url('/reportes/gastos')}}"><i class="fas fa-coins"></i> Reporte de gastos</b-dropdown-item>
                                        <b-dropdown-item @cannot('Reportes: comprobantes') class="disabled" disabled @endcannot href="{{action('ReporteController@reporte_comprobantes')}}"><i class="fas fa-file-invoice-dollar"></i> Reporte de comprobantes</b-dropdown-item>
                                        <b-dropdown-item @cannot('Reportes: productos') class="disabled" disabled @endcannot href="{{url('/reportes/productos/resumen-diario')}}"><i class="fas fa-dolly"></i> Reporte de productos</b-dropdown-item>
                                    </b-nav-item-dropdown>
                                    @can('Créditos')
                                        <b-nav-item href="{{action('CreditoController@index')}}">
                                            <img src="{{url('images/menubar/icons/egresos.png')}}" alt="">
                                            Créditos
                                        </b-nav-item>
                                    @endcan
                                    <b-nav-item-dropdown @cannot('Mantenimiento') class="disabled" disabled @endcannot left>
                                        <template v-slot:button-content>
                                            <img src="{{url('images/menubar/icons/config.png')}}" alt="">
                                            @cannot('Producción')
                                                Mantenimiento
                                            @endcannot
                                        </template>
                                        <b-dropdown-item @cannot('Clientes') class="disabled" disabled @endcannot href="{{action('ClienteController@index')}}"><i class="fas fa-user-tag"></i> Clientes</b-dropdown-item>
                                        <b-dropdown-item @cannot('Mantenimiento: empleados') class="disabled" disabled @endcannot href="{{action('TrabajadorController@index')}}"><i class="fas fa-users"></i> Empleados</b-dropdown-item>
                                        <b-dropdown-item @cannot('Gastos') class="disabled" disabled @endcannot href="{{action('GastoController@index')}}"><i class="fas fa-coins"></i> Pagos y gastos</b-dropdown-item>
                                        <b-dropdown-item @cannot('Mantenimiento: proveedores') class="disabled" disabled @endcannot href="{{action('ProveedorController@index')}}"><i class="fas fa-user-tie"></i> Proveedores</b-dropdown-item>
                                        <b-dropdown-item @cannot('Configuración') class="disabled" disabled style="display: none;" @endcannot href="{{action('ConfiguracionController@index')}}"><i class="fas fa-cogs"></i> Configuración</b-dropdown-item>
                                    </b-nav-item-dropdown>
                                    <b-nav-item-dropdown left class="d-block d-lg-none">
                                        <template v-slot:button-content>
                                            <img src="{{url('images/menubar/icons/facilito/off.png')}}" alt="">
                                            Cerrar sesion
                                        </template>
                                        <b-dropdown-item href="{{url('logout')}}"><i class="fas fa-power-off"></i> Cerrar sesion</b-dropdown-item>
                                    </b-nav-item-dropdown>
                                </b-navbar-nav>
                            @else
                                <b-navbar-nav>
                                    <b-nav-item @cannot('Pedido: crear pedido') class="disabled" disabled @endcannot href="{{action('PedidoController@index')}}">
                                        <img src="{{url('images/menubar/icons/'.$ruta.'caja.png')}}" alt="">
                                        Ventas
                                    </b-nav-item>
                                    <b-nav-item-dropdown @cannot('Facturación') class="disabled" disabled @endcannot left>
                                        <template v-slot:button-content>
                                            <img src="{{url('images/menubar/icons/'.$ruta.'facturar.png')}}" alt="">
                                            Comprobantes
                                        </template>
                                        <b-dropdown-item @cannot('Facturación: facturar') class="disabled" disabled @endcannot href="{{action('VentaController@registrar')}}"><i class="fas fa-file-invoice-dollar"></i> Facturación avanzada</b-dropdown-item>
                                        <b-dropdown-item @cannot('Facturación: facturar') class="disabled" disabled @endcannot href="{{action('ComprobanteController@comprobantes')}}"><i class="fas fa-list-ul"></i> Comprobantes emitidos</b-dropdown-item>
                                        <b-dropdown-item @cannot('Facturación: guía') class="disabled" disabled @endcannot href="{{action('GuiaController@index')}}"><i class="fas fa-shipping-fast"></i> Guía de remisión electrónica</b-dropdown-item>
                                    </b-nav-item-dropdown>
                                    <b-nav-item-dropdown @cannot('Caja') class="disabled" disabled @endcannot left>
                                        <template v-slot:button-content>
                                            <img src="{{url('images/menubar/icons/'.$ruta.'registradora.png')}}" alt="">
                                            Caja
                                        </template>
                                        <b-dropdown-item @cannot('Caja: gestionar') class="disabled" disabled @endcannot href="{{action('CajaController@index')}}"><i class="fas fa-cash-register"></i> Turnos</b-dropdown-item>
                                        <b-dropdown-item @cannot('Caja: egresos') class="disabled" disabled @endcannot href="{{action('GastoController@index')}}"><i class="fas fa-coins"></i> Movimientos</b-dropdown-item>
                                        <b-dropdown-item @cannot('Caja') class="disabled" disabled @endcannot href="{{action('CreditoController@index')}}"><i class="fas fa-money-bill-wave"></i> Créditos</b-dropdown-item>
                                    </b-nav-item-dropdown>
                                    <b-nav-item-dropdown @cannot('Inventario') class="disabled" disabled @endcannot left>
                                        <template v-slot:button-content>
                                            <img src="{{url('images/menubar/icons/'.$ruta.'receivings.png')}}" alt="">
                                            Inventario
                                        </template>
                                        <b-dropdown-item @cannot('Inventario: productos') class="disabled" disabled @endcannot href="{{action('ProductoController@index')}}"><i class="fas fa-gem"></i> Productos</b-dropdown-item>
                                        <b-dropdown-item @cannot('Mantenimiento: categorías') class="disabled" disabled @endcannot href="{{action('CategoriaController@index')}}"><i class="fas fa-th-list"></i> Categorías</b-dropdown-item>
                                        <b-dropdown-item @cannot('Inventario: almacenes') class="disabled" disabled @endcannot href="{{action('AlmacenController@index')}}"><i class="fas fa-dolly-flatbed"></i> Almacenes</b-dropdown-item>
                                        <b-dropdown-item @cannot('Inventario: requerimientos') class="disabled" disabled @endcannot href="{{action('RequerimientoController@index')}}"><i class="fas fa-box-open"></i> Requerimientos</b-dropdown-item>
                                        <b-dropdown-item @cannot('Inventario: productos') class="disabled" disabled @endcannot href="{{url('caja/movimientos?tipo=devoluciones')}}"><i class="fas fa-undo"></i> Devoluciones</b-dropdown-item>
                                        <b-dropdown-item @cannot('Catalogos') class="disabled" disabled @endcannot href="{{url('catalogos')}}"><i class="fas fa-th-large"></i> Catálogos</b-dropdown-item>
                                    </b-nav-item-dropdown>
                                    <b-nav-item @cannot('Cotizaciones') class="disabled" disabled @endcannot href="{{action('PresupuestoController@index')}}">
                                        <img src="{{url('images/menubar/icons/'.$ruta.'cotizar.png')}}" alt="">
                                        Cotizaciones
                                    </b-nav-item>
                                    <b-nav-item-dropdown @cannot('Reportes') class="disabled" disabled @endcannot left>
                                        <template v-slot:button-content>
                                            <img src="{{url('images/menubar/icons/'.$ruta.'reports.png')}}" alt="">
                                            Reportes
                                        </template>
                                        <b-dropdown-item @cannot('Reportes: ventas') class="disabled" disabled @endcannot href="{{action('ReporteController@reporte_ventas')}}"><i class="fas fa-chart-line"></i> Reporte de ventas</b-dropdown-item>
                                        <b-dropdown-item @cannot('Reportes: gastos') class="disabled" disabled @endcannot href="{{url('/reportes/gastos/diario').'/'.date('Y-m')}}"><i class="fas fa-coins"></i> Reporte de gastos</b-dropdown-item>
                                        <b-dropdown-item @cannot('Reportes: ventas') class="disabled" disabled @endcannot href="{{action('ReporteController@reporte_caja')}}"><i class="fas fa-cash-register"></i> Reporte de caja</b-dropdown-item>
                                        <b-dropdown-item @cannot('Reportes: comprobantes') class="disabled" disabled @endcannot href="{{action('ReporteController@reporte_comprobantes')}}"><i class="fas fa-file-invoice-dollar"></i> Reporte de comprobantes</b-dropdown-item>
                                        <b-dropdown-item @cannot('Reportes: productos') class="disabled" disabled @endcannot href="{{url('/reportes/productos/resumen-diario')}}"><i class="fas fa-dolly"></i> Reporte de productos</b-dropdown-item>
                                    </b-nav-item-dropdown>
                                    <b-nav-item-dropdown @cannot('Mantenimiento') class="disabled" disabled @endcannot left>
                                        <template v-slot:button-content>
                                            <img src="{{url('images/menubar/icons/'.$ruta.'config.png')}}" alt="">
                                            Mantenimiento
                                        </template>
                                        <b-dropdown-item @cannot('Clientes') class="disabled" disabled @endcannot href="{{action('ClienteController@index')}}"><i class="fas fa-user-tag"></i> Clientes</b-dropdown-item>
                                        <b-dropdown-item @cannot('Mantenimiento: empleados') class="disabled" disabled @endcannot href="{{action('TrabajadorController@index')}}"><i class="fas fa-users"></i> Empleados</b-dropdown-item>
                                        <b-dropdown-item @cannot('Mantenimiento: proveedores') class="disabled" disabled @endcannot href="{{action('ProveedorController@index')}}"><i class="fas fa-user-tie"></i> Proveedores</b-dropdown-item>
                                        <b-dropdown-item @cannot('Configuración') class="disabled" disabled style="display: none;" @endcannot href="{{action('ConfiguracionController@index')}}"><i class="fas fa-cogs"></i> Configuración</b-dropdown-item>
                                    </b-nav-item-dropdown>
                                    <b-nav-item-dropdown left class="d-block d-lg-none">
                                        <template v-slot:button-content>
                                            <img src="{{url('images/menubar/icons/facilito/off.png')}}" alt="">
                                            Cerrar sesion
                                        </template>
                                        <b-dropdown-item href="{{url('logout')}}"><i class="fas fa-power-off"></i> Cerrar sesion</b-dropdown-item>
                                    </b-nav-item-dropdown>
                                </b-navbar-nav>
                            @endif
                        </b-collapse>
                    </b-navbar>
                </div>
            </div>
        </div>
    </header>

    @endif
</div>
<!-- Side menu -->

<main class="wrapper app" v-cloak>
    @yield('contenido')
</main>
<footer class="my-5 main-footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 mt-4">
                <p class="copy text-center">Sistema <a target="_blank" href="https://facebook.com/facsyperu">Facsy.</a> Un producto de <a href="https://coditec.pe" target="_blank"> coditec.pe</a> | © {{date('Y')}}</p>
            </div>
        </div>
    </div>
</footer>
<script src="{{asset('js/app.js?v='.filemtime('js/app.js'))}}"></script>
@yield('script')
<script>
    let app_menu = new Vue({
        el: '.app_menu',
        methods:{
            disabled_ventas(){
                if (typeof app.disabled_ventas === 'function'){
                    app.disabled_ventas();
                }
            }
        }
    })
</script>
<script src="{{asset('js/phpjsdate.js')}}"></script>
<script src="{{asset('js/helpers.js')}}"></script>
<script type="text/javascript">
    // live clock
    function clockTick() {
        setInterval('updateClock();', 1000);
    }
    // start the clock immediatly
    clockTick();

    let now = new Date(<?php echo time() * 1000 ?>);

    function updateClock() {
        now.setTime(now.getTime() + 1000);
        document.getElementById('liveclock').innerHTML = phpjsDate('d/m/Y h:i:s A',);
    }

    navigation();

</script>
@yield('css')
</body>
</html>