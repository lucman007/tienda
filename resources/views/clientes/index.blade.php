@extends('layouts.main')
@section('titulo', 'Clientes')
@section('contenido')
    @php $agent = new \Jenssegers\Agent\Agent() @endphp
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1">Clientes</h3>
                <b-button class="mr-2 mb-2 mb-lg-0" v-b-modal.modal-nuevo-cliente variant="primary"><i class="fas fa-plus"></i> Nuevo cliente</b-button>
                <b-button class="mr-2 mb-2 mb-lg-0" v-b-modal.modal-2 variant="primary"><i class="fas fa-file-import"></i> Importar</b-button>
                <b-button class="mb-2 mb-lg-0" href="{{action('ClienteController@exportar')}}" variant="primary"><i class="fas fa-file-export"></i> Exportar...</b-button>
            </div>
            <div class="col-lg-3">
                @include('clientes.buscador')
            </div>
        </div>
        @if($textoBuscado!='')
            <div class="row">
                <div class="col-lg-12 mt-5">
                    <div class="alert alert-dark" role="alert"><h5 class="mb-0">Resultados de búsqueda para: {{$textoBuscado}}
                            <a href="{{url('/clientes')}}"><i class="fa fa-times float-right"></i></a></h5></div>
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-sm-12 mt-4">
                <div class="card">
                    <div class="card-header">
                        Lista de clientes
                    </div>
                    <div class="card-body">
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col"><a href="?orderby=cod_cliente&order={{$order}}">Código <span class="icon-hover @if($orderby=='cod_cliente') icon-hover-active @endif">{!!$order_icon!!}</span></a></th>
                                    <th scope="col"><a href="?orderby=nombre&order={{$order}}">Nombre <span class="icon-hover @if($orderby=='nombre') icon-hover-active @endif">{!!$order_icon!!}</span></a></th>
                                    <th scope="col"><a href="?orderby=num_documento&order={{$order}}">N° doc. <span class="icon-hover @if($orderby=='num_documento') icon-hover-active @endif">{!!$order_icon!!}</span></a>.</th>
                                    <th scope="col">Dirección</th>
                                    <th scope="col">Telefono</th>
                                    <th scope="col">E-mail</th>
                                    <th scope="col">Cuentas</th>
                                    <th scope="col"></th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($clientes) > 0)
                                    @foreach($clientes as $cliente)
                                        @php
                                            $cuentas = $cliente->cuentas?json_decode($cliente->cuentas, true):false;
                                        @endphp
                                        <tr @if(!$agent->isDesktop()) @click="editarCliente({{$cliente->idcliente}})" @endif>
                                            <td></td>
                                            <td>{{$cliente->cod_cliente}}</td>
                                            <td style="width: 20%">{{$cliente->nombre}}</td>
                                            <td>{{$cliente->num_documento}}</td>
                                            <td style="width: 30%">{{$cliente->direccion}}</td>
                                            <td>{{$cliente->telefono}}</td>
                                            <td style="width: 20%">{{$cliente->correo}}</td>
                                            <td style="width: 10%">
                                                @if($cuentas)
                                                    @foreach ($cuentas as $cuenta)
                                                        {{$cuenta['banco'].' '.$cuenta['moneda'].': '.$cuenta['cuenta'].' '.$cuenta['cci']}} <br>
                                                    @endforeach
                                                @endif
                                            </td>
                                            <td @click.stop class="botones-accion" style="text-align: right">
                                                <button @click="editarCliente({{$cliente->idcliente}})" class="btn btn-success"
                                                        title="Editar cliente"><i class="fas fa-edit"></i></button>
                                                <button @click="borrarCliente({{$cliente->idcliente}})" class="btn btn-danger"
                                                        title="Eliminar"><i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="text-center">
                                        <td colspan="8">No hay datos que mostrar</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                        {{$clientes->links('layouts.paginacion')}}
                    </div>
                </div>
            </div>
        </div>
    </div>
<!--INICIO MODAL IMPORTACIÓN-->
<b-modal id="modal-2" ref="modal-2" size="md" @ok="importar_clientes">
<template slot="modal-title">
    Importar archivo excel
</template>
<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <form class="form-upload" method="POST" action="{{url('clientes/importar-clientes')}}" enctype="multipart/form-data">
                <div class="form-group">
                    <input type="file" id="excel_file" name="excel_file" class="form-control-file">
                </div>

            </form>
        </div>
        <div class="col-lg-12 mt-4">
            <a href="{{url('/clientes/descargar-formato-importacion')}}"><i class="fas fa-download"></i>  Descargar formato de importación</a>
        </div>
    </div>
</div>
</b-modal>
<!--FIN MODAL IMPORTACIÓN -->
    <agregar-cliente v-on:agregar="reload" ref="agregarCliente"></agregar-cliente>

@endsection
@section('script')
    <script>
        let app = new Vue({
            el: '.app',
            data: {
            },
            methods: {
                reload(){
                  location.reload(true);
                },
                editarCliente(id){
                    this.$refs['agregarCliente'].editarCliente(id);
                },
                borrarCliente(id){
                    if(confirm('Realmente desea eliminar el cliente')){
                        axios.delete('{{url('/clientes/destroy')}}' + '/' + id)
                            .then(() => {
                                window.location.reload(true)
                            })
                            .catch(error => {
                                console.log(error);
                            });
                    }
                },
                importar_clientes(){
                    let excel_file = document.getElementById("excel_file").files[0];
                    let data = new FormData();
                    data.append('excel_file', excel_file);
                    let settings = { headers: { 'content-type': 'multipart/form-data' } };

                    axios.post('{{url('/clientes/importar-clientes')}}', data, settings)
                        .then(response => {
                            if(response.data===1){
                                alert('Importación realizada con éxito');
                                window.location.reload(true)
                            } else if(response.data===0){
                                alert("No se ha encontrado archivo para importar")
                            } else{
                                alert("El archivo no cumple las condiciones de importación. Verifique los datos ingresados.")
                            }
                        })
                        .catch(error => {
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });
                }
            }
        });
    </script>
@endsection