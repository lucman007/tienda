@extends('layouts.main')
@section('titulo', 'Reporte de stock')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1">Reporte de productos</h3>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <b-nav tabs>
                    <b-nav-item href="{{url('/reportes/productos/stock_bajo')}}" active>Stock bajo</b-nav-item>
                    <b-nav-item href="{{url('/reportes/productos/mas-vendidos')}}">Más vendidos</b-nav-item>
                </b-nav>
                <div class="row mt-4">
                    <div class="col-lg-9">
                        <div class="row">
                            <div class="col-lg-3 form-group">
                                <b-button @if(count($productos) == 0) disabled @endif href="?export=true" variant="primary"><i class="fas fa-file-export"></i> Exportar excel</b-button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card no-shadow">
                                    <div class="card-body">
                                        <div class="table-responsive tabla-gestionar">
                                            <table class="table table-striped table-hover table-sm">
                                                <thead class="bg-custom-green">
                                                <tr>
                                                    <th scope="col"></th>
                                                    <th scope="col">Código</th>
                                                    <th scope="col">Producto</th>
                                                    <th scope="col">Características</th>
                                                    <th scope="col">Stock actual</th>

                                                </tr>
                                                </thead>
                                                <tbody>
                                                @if(count($productos) != 0)
                                                    @foreach($productos as $producto)
                                                        <tr>
                                                            <td></td>
                                                            <td>{{ $producto->cod_producto }}</td>
                                                            <td>{{ $producto->nombre }}</td>
                                                            <td>{{ $producto->presentacion }}</td>
                                                            <td style="color:{{$producto->saldo <= 0?'red':'inherit'}}">{{ $producto->saldo}}</td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="6" class="text-center">No hay datos para mostrar</td>
                                                    </tr>
                                                @endif
                                                </tbody>
                                            </table>
                                        </div>
                                        {{$productos->links('layouts.paginacion')}}
                                    </div>
                                </div>
                            </div>
                        </div>
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
            methods:{
            }
        });
    </script>
@endsection