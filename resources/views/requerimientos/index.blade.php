@extends('layouts.main')
@section('titulo', 'Clientes')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1">Requerimientos</h3>
                <b-button href="{{action('RequerimientoController@nuevo_requerimiento')}}" class="mr-2" variant="primary"><i class="fas fa-plus"></i> Nueva orden de compra</b-button>
            </div>
            <div class="col-lg-3">
                @include('requerimientos.buscador')
            </div>
        </div>
        @if($textoBuscado!='')
            <div class="row">
                <div class="col-lg-12 mt-5">
                    <div class="alert alert-dark" role="alert"><h5 class="mb-0">Resultados de búsqueda para: {{$textoBuscado}}
                            <a href="{{url('/requerimientos')}}"><i class="fa fa-times float-right"></i></a></h5></div>
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-sm-12 mt-4">
                <div class="card">
                    <div class="card-header">
                        Lista de requerimientos
                    </div>
                    <div class="card-body">
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col">Num.</th>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Proveedor</th>
                                    <th scope="col">Total</th>
                                    <th scope="col">Estado</th>
                                    <th scope="col">Opciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($requerimientos))
                                    @foreach($requerimientos as $requerimiento)
                                        <tr>
                                            <td></td>
                                            <td>{{$requerimiento->correlativo}}</td>
                                            <td>{{date("d-m-Y H:i:s",strtotime($requerimiento->fecha_requerimiento))}}</td>
                                            <td>{{$requerimiento->proveedor}}</td>
                                            <td>{{$requerimiento->total_compra}}</td>
                                            <td><p class="badge"
                                                   :class="['<?php echo $requerimiento->estado ?>'=='PENDIENTE' ? 'badge-warning' : 'badge-success']">{{$requerimiento->estado}}</p>
                                            </td>
                                            <td class="botones-accion">
                                                <a href="{{url('requerimientos/editar').'/'.$requerimiento->idrequerimiento}}">
                                                    <button class="btn btn-success" title="Abrir requerimiento">
                                                        <i class="fas fa-folder-open"></i>
                                                    </button>
                                                </a>
                                                <a @click="borrarRequerimiento({{$requerimiento->idrequerimiento}})"
                                                   href="javascript:void(0)">
                                                    <button class="btn btn-danger" title="Eliminar"><i
                                                                class="fas fa-trash-alt"></i>
                                                    </button>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="text-center">
                                        <td colspan="7">No hay datos para mostrar</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                        {{$requerimientos->links('layouts.paginacion')}}
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
                borrarRequerimiento(id){
                    if(confirm('Realmente desea eliminar este requerimiento')){
                        axios.delete('{{url('/requerimientos/destroy')}}' + '/' + id)
                            .then(function () {
                                window.location.href = "/requerimientos"
                            })
                            .catch(function (error) {
                                console.log(error);
                            });
                    }
                }
            }

        });
    </script>
@endsection