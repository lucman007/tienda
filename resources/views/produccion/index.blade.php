@extends('layouts.main')
@section('titulo', 'Producción')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-lg-8">
                <h3 class="titulo-admin-1">Orden de producción</h3>
                <b-button href="{{action('ProduccionController@nueva_produccion')}}" class="mr-2"  variant="primary"><i class="fas fa-plus"></i> Nuevo</b-button>
            </div>
            <div class="col-lg-4">
                @include('produccion.buscador')
            </div>
        </div>
        @if($textoBuscado!='')
            <div class="row">
                <div class="col-lg-12 mt-5">
                    <div class="alert alert-dark" role="alert"><h5 class="mb-0">Resultados de búsqueda para: {{$textoBuscado}}
                            <a href="{{url()->previous()}}"><i class="fa fa-times float-right"></i></a></h5></div>
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-sm-12 mt-4">
                <div class="card">
                    <div class="card-body">
                        <b-nav tabs>
                            <b-nav-item href="/produccion/pendientes" :active="'pendiente'=='{{$active}}'">Órdenes pendientes</b-nav-item>
                            <b-nav-item href="/produccion/completadas" :active="'completada'=='{{$active}}'">Órdenes completadas</b-nav-item>
                        </b-nav>
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col">N°</th>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Cliente</th>
                                    <th scope="col">Prioridad</th>
                                    <th scope="col">Estado</th>
                                    <th scope="col">Opciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($produccion)>0)
                                    @foreach($produccion as $item)
                                        <tr>
                                            <td></td>
                                            <td>{{$item->correlativo}}</td>
                                            <td style="width: 20%">{{date("d-m-Y H:i:s",strtotime($item->fecha_emision))}}</td>
                                            <td>{{$item->nombre}}</td>
                                            <td>@if($item->prioridad==0)
                                                    <span class="badge badge-success">NORMAL</span>
                                                @else
                                                    <span class="badge badge-danger">ALTA</span>
                                                @endif
                                            </td>
                                            <td>@if($item->estado=='PENDIENTE')
                                                    <span class="badge badge-warning">PENDIENTE</span>
                                                @else
                                                    <span class="badge badge-success">COMPLETADO</span>
                                                @endif
                                            </td>
                                            <td class="botones-accion">
                                                <a href="{{url('produccion/editar').'/'.$item->idproduccion}}">
                                                    <button class="btn btn-success" title="Abrir produccion">
                                                        <i class="fas fa-folder-open"></i>
                                                    </button>
                                                </a>
                                                <b-button target="_blank" href="{{url('produccion/imprimir').'/'.$item->idproduccion}}" variant="info"  title="Imprimir"><i class="fas fa-print"></i></b-button>
                                                <a @click="borrarProduccion({{$item->idproduccion}})" href="javascript:void(0)">
                                                    <button class="btn btn-danger" title="Eliminar"><i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="text-center">
                                        <td colspan="7">No hay datos que mostrar</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                        {{$produccion->links('layouts.paginacion')}}
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
                borrarProduccion(id){
                    if(confirm('¿Realmente desea eliminar la orden de produccion?')){
                        axios.delete('{{url('/produccion/destroy')}}' + '/' + id)
                            .then(() => {
                                window.location.reload(true);
                            })
                            .catch(error => {
                                console.log(error);
                            });
                    }
                },
            }

        });
    </script>
@endsection