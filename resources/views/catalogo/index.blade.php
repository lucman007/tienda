@extends('layouts.main')
@section('titulo', 'Catálogos')
@section('contenido')
    @php $agent = new \Jenssegers\Agent\Agent() @endphp
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <h3 class="titulo-admin-1">Catálogos</h3>
                <b-button href="{{url('catalogos/nuevo')}}" class="mr-2 mb-2 mb-lg-0"  variant="primary"><i class="fas fa-plus"></i> Nuevo catálogo</b-button>
            </div>
            <div class="col-lg-4">
                @include('catalogo.buscador')
            </div>
        </div>
        @if($textoBuscado!='')
            <div class="row">
                <div class="col-lg-12 mt-5">
                    <div class="alert alert-dark" role="alert"><h5 class="mb-0">Resultados de búsqueda para: {{$textoBuscado}}
                            <a href="{{url('/catalogos')}}"><i class="fa fa-times float-right"></i></a></h5></div>
                </div>
            </div>
        @endif
        <div class="card mt-4">
            <div class="row">
                <div class="col-sm-12">
                        <div class="card-body">
                            <div class="table-responsive tabla-gestionar">
                                <table class="table table-striped table-hover table-sm">
                                    <thead class="bg-custom-green">
                                    <tr>
                                        <th scope="col"></th>
                                        <th scope="col">Fecha</th>
                                        <th scope="col">Título</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if(count($catalogos))
                                        @foreach($catalogos as $item)
                                            <tr>
                                                <td></td>
                                                <td>{{date("d-m-Y",strtotime($item->fecha))}}</td>
                                                <td>{{$item->titulo}}</td>
                                                <td class="botones-accion" @click.stop>
                                                    <b-button href="{{url('catalogos/editar').'/'.$item->idcatalogo}}" class="btn btn-success" title="Abrir catálogo">
                                                        <i class="fas fa-folder-open"></i>
                                                    </b-button>
                                                    <b-button variant="info" @click="duplicar({{$item->idcatalogo}})" title="Duplicar"><i class="fas fa-copy"></i></b-button>
                                                    <button @click="borrarCatalogo({{$item->idcatalogo}})" class="btn btn-danger" title="Eliminar"><i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr class="text-center">
                                            <td colspan="9">No hay datos para mostrar</td>
                                        </tr>
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                            {{$catalogos->links('layouts.paginacion')}}
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
                borrarCatalogo(id){
                    if(confirm('¿Realmente desea eliminar el catalogo?')){
                        axios.delete('{{url('/catalogos/destroy')}}' + '/' + id)
                            .then(() => {
                                window.location.reload(true)
                            })
                            .catch(error => {
                                console.log(error);
                            });
                    }
                },
                duplicar(id){
                    if(confirm('Se duplicará el catálogo, confirma la acción.')){
                        axios.get('{{url('/catalogos/duplicar')}}' + '/' + id)
                            .then(() => {
                                window.location.reload(true)
                            })
                            .catch(error => {
                                console.log(error);
                            });
                    }
                }
            }

        });
    </script>
@endsection