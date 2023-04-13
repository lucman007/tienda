@extends('layouts.main')
@section('titulo', 'Gestionar créditos')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1">Créditos</h3>
            </div>
            <div class="col-lg-3">
                @include('creditos.buscador')
            </div>
        </div>
        @if($textoBuscado!='')
            <div class="row">
                <div class="col-lg-12 mt-5">
                    <div class="alert alert-dark" role="alert"><h5 class="mb-0">Resultados de búsqueda
                            para: {{$textoBuscado}}
                            <a href="{{url('/creditos')}}"><i class="fa fa-times float-right"></i></a></h5></div>
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-sm-12 mt-4">
                <div class="card">
                    <div class="card-header">
                        Lista de ventas a crédito
                    </div>
                    <div class="card-body">
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col"><a href="?orderby=idventa&order={{$order}}">Venta <span class="icon-hover @if($orderby=='idventa') icon-hover-active @endif">{!!$order_icon!!}</span></a></th>
                                    <th scope="col"><a href="?orderby=fecha&order={{$order}}">Fecha <span class="icon-hover @if($orderby=='fecha') icon-hover-active @endif">{!!$order_icon!!}</span></a></th>
                                    <th scope="col">Vend.</th>
                                    <th scope="col" style="width:25%"><a href="?orderby=cliente&order={{$order}}">Cliente <span class="icon-hover @if($orderby=='cliente') icon-hover-active @endif">{!!$order_icon!!}</span></a></th>
                                    <th scope="col">Importe</th>
                                    <th scope="col">Moneda</th>
                                    <th scope="col" style="width: 12%">Comprobante</th>
                                    <th scope="col">Estado</th>
                                    <th scope="col">Opciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($creditos) > 0)
                                    @foreach($creditos as $venta)
                                        <tr>
                                            <td></td>
                                            <td>{{$venta->idventa}}</td>
                                            <td style="width: 15%">{{date("d-m-Y",strtotime($venta->fecha))}}</td>
                                            <td>{{mb_strtoupper($venta->empleado->nombre)}}</td>
                                            <td>{{$venta->cliente}} {{$venta->alias?'('.$venta->alias.')':''}}</td>
                                            <td>{{$venta->total_venta}}</td>
                                            <td>{{$venta->facturacion->codigo_moneda}}</td>
                                            <td>{{$venta->facturacion->serie}}-{{$venta->facturacion->correlativo}}<br>
                                                {{$venta->guia_relacionada['correlativo']}}
                                            </td>
                                            <td><span class="badge {{$venta->estado_badge_class}}">{{$venta->estado}}</span></td>
                                            <td class="botones-accion" style="width: 10%">
                                                <a href="{{url('creditos/editar').'/'.$venta->idventa}}">
                                                    <button class="btn btn-success" title="Abrir">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="text-center">
                                        <td colspan="11">No hay datos que mostrar</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                        {{$creditos->links('layouts.paginacion')}}
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection
@section('script')
    <script>
        let app = new Vue({
            el: '.app',
            data: {
            },
            methods: {

            }

        });
    </script>
@endsection