@extends('layouts.main')
@section('titulo', 'Proveedores')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1">Proveedores</h3>
                <b-button v-b-modal.modal-nuevo-proveedor variant="primary"><i class="fas fa-plus"></i> Nuevo proveedor</b-button>
            </div>
            <div class="col-lg-3">
                @include('proveedores.buscador')
            </div>
        </div>
        @if($textoBuscado!='')
        <div class="row">
            <div class="col-lg-12 mt-5">
                <div class="alert alert-dark" role="alert"><h5 class="mb-0">Resultados de búsqueda para: {{$textoBuscado}}
                        <a href="{{url('/proveedores')}}"><i class="fa fa-times float-right"></i></a></h5></div>
            </div>
        </div>
        @endif
        <div class="row">
            <div class="col-sm-12 mt-4">
                <div class="card">
                    <div class="card-header">
                        Lista de proveedores
                    </div>
                    <div class="card-body">
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col">Codigo</th>
                                    <th scope="col">Nombre</th>
                                    <th scope="col">Contacto</th>
                                    <th scope="col">Telefono 1</th>
                                    <th scope="col">Telefono 2</th>
                                    <th scope="col">Correo</th>
                                    <th scope="col">Web</th>
                                    <th scope="col">Opciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($proveedores as $proveedor)
                                    <tr>
                                        <td></td>
                                        <td style="display:none">{{$proveedor->idproveedor}}</td>
                                        <td>{{$proveedor->codigo}}</td>
                                        <td>{{$proveedor->nombre}}</td>
                                        <td>{{$proveedor->contacto}}</td>
                                        <td>{{$proveedor->telefono}}</td>
                                        <td>{{$proveedor->telefono_2}}</td>
                                        <td>{{$proveedor->correo}}</td>
                                        <td>{{$proveedor->web}}</td>
                                        <td class="botones-accion">
                                            <button @click="editarProveedor({{$proveedor->idproveedor}})" class="btn btn-success" title="Editar proveedor"><i
                                                        class="fas fa-edit"></i></button>
                                            <button @click="borrarProveedor({{$proveedor->idproveedor}})" class="btn btn-danger" title="Eliminar"><i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{$proveedores->links('layouts.paginacion')}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <agregar-proveedor v-on:agregar="reload" ref="agregarProveedor"></agregar-proveedor>
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
                editarProveedor(id){
                    this.$refs['agregarProveedor'].editarProveedor(id);
                },
                borrarProveedor(id){
                    if(confirm('Realmente desea eliminar el proveedor')){
                        axios.delete('{{url('/proveedores/destroy')}}' + '/' + id)
                            .then(function () {
                                window.location.href = "/proveedores"
                            })
                            .catch(function (error) {
                                console.log(error);
                            });
                    }
                },
            },


        });
    </script>
@endsection