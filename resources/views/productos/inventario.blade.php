@extends('layouts.main')
@section('titulo', 'Kardex')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1">
                    <a href="{{url()->previous()}}"><i class="fas fa-arrow-circle-left"></i></a>
                    {{$producto['nombre']}}
                </h3>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 mt-4">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive tabla-gestionar">
                            @if($producto->tipo_producto==1)
                                <table class="table table-striped table-hover table-sm">
                                    <thead class="bg-custom-green">
                                    <tr>
                                        <th scope="col"></th>
                                        <th scope="col">Fecha</th>
                                        <th scope="col">Operación</th>
                                        <th scope="col">Entrada/salida</th>
                                        <th style="text-align: center" scope="col">Costo</th>
                                        <th style="text-align: center" scope="col">Saldo</th>
                                        <th scope="col">Empleado</th>
                                        <th scope="col">Observación</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($inventario as $item)
                                        <tr>
                                            <td></td>
                                            <td>{{date("d/m/Y H:i",strtotime($item->fecha))}}</td>
                                            <td>{{$item->operacion}}</td>
                                            <td v-show="'{{$item->cantidad}}'>0"
                                                style="background: #84d091; text-align: center">{{$item->cantidad}}</td>
                                            <td v-show="'{{$item->cantidad}}'<=0"
                                                style="background: #e8b16d; text-align: center">{{$item->cantidad}}</td>
                                            <td style="text-align: center">{{$item->moneda=='PEN'?'S/':'USD'}} {{$item->costo}}</td>
                                            <td style="text-align: center">{{$item->saldo}}</td>
                                            <td>{{$item->empleado->nombre}} {{$item->empleado->apellidos}}</td>
                                            <td>{{$item->descripcion}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @else
                                <table class="table table-striped table-hover table-sm">
                                    <thead class="bg-custom-green">
                                    <tr>
                                        <th scope="col"></th>
                                        <th scope="col">Fecha</th>
                                        <th scope="col">N° de Venta</th>
                                        <th scope="col">Servicios efectuados</th>
                                        <th scope="col">Empleado</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($inventario as $item)
                                        @if($item->cantidad != 0)
                                            <tr>
                                                <td></td>
                                                <td style="display:none">{{$item->idinventario}}</td>
                                                <td>{{date("d-m-Y H:i:s",strtotime($item->fecha))}}</td>
                                                <td>{{$item->operacion}}</td>
                                                <td v-show="'{{$item->cantidad}}'>0"
                                                    style="background: #84d091">{{$item->cantidad}}</td>
                                                <td v-show="'{{$item->cantidad}}'<0"
                                                    style="background: #e8b16d">{{$item->cantidad}}</td>
                                                <td>{{$item->empleado->nombre}} {{$item->empleado->apellidos}}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td>Total:</td>
                                        <td>{{$producto->total}}</td>
                                        <td></td>
                                    </tr>
                                    </tbody>
                                </table>
                            @endif
                        </div>
                        {{$inventario->links('layouts.paginacion')}}
                    </div>
            </div>
        </div>
    </div>
    </div>
@endsection
@section('script')
    <script>
        let app = new Vue({
            el: '.app'

        });
    </script>
@endsection