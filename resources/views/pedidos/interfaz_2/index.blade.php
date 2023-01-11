@extends('layouts.main')
@section('titulo', 'Lista de pedidos')
@section('contenido')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <h3 class="titulo-admin-1">Historial de pedidos</h3>
                <b-button href="{{action('PedidoController@nueva_orden')}}" class="mr-2"  variant="primary"><i class="fas fa-plus"></i> Nuevo pedido</b-button>
                <b-button target="_blank" href="{{action('PedidoController@imprimir_lista')}}" class="mr-2"  variant="primary"><i class="fas fa-print"></i> Imprimir</b-button>
            </div>
            <div class="col-lg-4">
                @include('pedidos.interfaz_2.buscador')
            </div>
        </div>
        @if($textoBuscado!='')
            <div class="row">
                <div class="col-lg-12 mt-5">
                    <div class="alert alert-dark" role="alert"><h5 class="mb-0">Resultados de búsqueda para: {{$textoBuscado}}
                            <a href="{{url('/pedidos')}}"><i class="fa fa-times float-right"></i></a></h5></div>
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-sm-12 mt-4">
                <div class="card">
                    <div class="card-header">
                        Lista de pedidos
                    </div>
                    <div class="card-body">
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col">N° pedido</th>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Vendedor</th>
                                    <th scope="col">Cliente</th>
                                    <th scope="col">Total</th>
                                    <th scope="col">Estado</th>
                                    <th scope="col">Opciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($ordenes as $item)
                                    <tr>
                                        <td></td>
                                        <td>{{$item->idorden}}</td>
                                        <td>{{date("d-m-Y H:i:s",strtotime($item->fecha))}}</td>
                                        <td>{{strtoupper($item->empleado)}}</td>
                                        <td>{{$item->cliente}}</td>
                                        <td>{{$item->total}}</td>
                                        <td><span class="badge {{$item->badge_class}}">{{$item->estado}}</span></td>
                                        <td class="botones-accion">
                                            <b-button target="_blank" href="{{url('pedidos/imprimir').'/'.$item->idorden}}" class="btn btn-info" title="Imprimir">
                                                <i class="fas fa-print"></i>
                                            </b-button>
                                            <b-button @if($item->estado!='EN COLA') disabled style="opacity: 0.2" @endif href="{{url('pedidos/editar').'/'.$item->idorden}}" class="btn btn-success" title="Editar orden">
                                                <i class="fas fa-edit"></i>
                                            </b-button>
                                            <button @if($item->estado!='EN COLA') disabled style="opacity: 0.2" @endif @click="borrarOrden({{$item->idorden}})" class="btn btn-danger" title="Eliminar"><i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{$ordenes->links('layouts.paginacion')}}
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
                borrarOrden(id){
                    if(confirm('¿Realmente desea eliminar la orden?')){
                        axios.delete('{{url('/pedidos/destroy')}}' + '/' + id)
                            .then(function () {
                                window.location.href = "/pedidos"
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