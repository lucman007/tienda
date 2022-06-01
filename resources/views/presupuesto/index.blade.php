@extends('layouts.main')
@section('titulo', 'Cotizaciones')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-lg-8">
                <h3 class="titulo-admin-1">Cotizaciones</h3>
                <b-button href="{{action('PresupuestoController@nuevo_presupuesto')}}" class="mr-2"  variant="primary"><i class="fas fa-plus"></i> Nueva cotización</b-button>
            </div>
            <div class="col-lg-4">
                @include('presupuesto.buscador')
            </div>
        </div>
        @if($textoBuscado!='')
            <div class="row">
                <div class="col-lg-12 mt-5">
                    <div class="alert alert-dark" role="alert"><h5 class="mb-0">Resultados de búsqueda para: {{$textoBuscado}}
                            <a href="{{url('/presupuestos')}}"><i class="fa fa-times float-right"></i></a></h5></div>
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-sm-12 mt-4">
                <div class="card">
                    <div class="card-header">
                       Lista de cotizaciones
                        <span class="float-right">
                            <a href="/configuracion?tab=cotizacion"><i class="fas fa-cogs"></i> Configurar</a>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col"><a href="?orderby=correlativo&order={{$order}}">
                                            {{str_contains(strtolower($_SERVER['HTTP_USER_AGENT']),'android')?'N°':'Número'}} <span class="icon-hover @if($orderby=='correlativo') icon-hover-active @endif">{!!$order_icon!!}</span></a>
                                    </th>
                                    @if(!str_contains(strtolower($_SERVER['HTTP_USER_AGENT']),'android'))
                                    <th>Vend.</th>
                                    @endif
                                    <th scope="col"><a href="?orderby=fecha&order={{$order}}">Fecha <span class="icon-hover @if($orderby=='fecha') icon-hover-active @endif">{!!$order_icon!!}</span></a></th>
                                    <th scope="col" style="width: 40%"><a href="?orderby=cliente&order={{$order}}">Cliente <span class="icon-hover @if($orderby=='cliente') icon-hover-active @endif">{!!$order_icon!!}</span></a></th>
                                    <th scope="col">Total</th>
                                    @if(!str_contains(strtolower($_SERVER['HTTP_USER_AGENT']),'android'))
                                        <th scope="col"><a href="?orderby=moneda&order={{$order}}">Moneda <span class="icon-hover @if($orderby=='moneda') icon-hover-active @endif">{!!$order_icon!!}</span></a></th>
                                        <th scope="col">Opciones</th>
                                    @else
                                        <th scope="col"></th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($presupuesto))
                                    @foreach($presupuesto as $item)
                                        <tr>
                                            <td></td>
                                            <td>{{$item->correlativo}}</td>
                                            @if(!str_contains(strtolower($_SERVER['HTTP_USER_AGENT']),'android'))
                                            <td>{{mb_strtoupper($item->empleado->nombre)}}</td>
                                            @endif
                                            @if(str_contains(strtolower($_SERVER['HTTP_USER_AGENT']),'android'))
                                                <td style="width: 20%">{{date("d-m-Y",strtotime($item->fecha))}}</td>
                                            @else
                                                <td style="width: 20%">{{date("d-m-Y H:i:s",strtotime($item->fecha))}}</td>
                                            @endif
                                            <td>{{$item->cliente->persona->nombre}}</td>
                                            @if(str_contains(strtolower($_SERVER['HTTP_USER_AGENT']),'android'))
                                                <td>{{$item->moneda=='PEN'?'S/':'USD'}} {{$item->presupuesto}}</td>
                                            @else
                                                <td>{{$item->presupuesto}}</td>
                                                <td>{{$item->moneda}}</td>
                                            @endif
                                            <td class="botones-accion">
                                                <a href="{{url('presupuestos/editar').'/'.$item->idpresupuesto}}">
                                                    <button class="btn btn-success" title="Abrir presupuesto">
                                                        <i class="fas fa-folder-open"></i>
                                                    </button>
                                                </a>
                                                <b-button variant="info" @click="duplicar({{$item->idpresupuesto}})" title="Duplicar"><i class="fas fa-copy"></i></b-button>
                                                <button @click="borrarPresupuesto({{$item->idpresupuesto}})" class="btn btn-danger" title="Eliminar"><i class="fas fa-trash-alt"></i>
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
                        {{$presupuesto->links('layouts.paginacion')}}
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
                borrarPresupuesto(id){
                    if(confirm('¿Realmente desea eliminar el presupuesto?')){
                        axios.delete('{{url('/presupuestos/destroy')}}' + '/' + id)
                            .then(() => {
                                window.location.reload(true)
                            })
                            .catch(error => {
                                console.log(error);
                            });
                    }
                },
                duplicar(id){
                    if(confirm('Se duplicará un item, confirma la acción.')){
                        axios.get('{{url('/presupuestos/duplicar')}}' + '/' + id)
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