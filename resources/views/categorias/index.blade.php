@extends('layouts.main')
@section('titulo', 'Categorías')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1">Categorias</h3>
                <b-button v-b-modal.modal-1 variant="primary"><i class="fas fa-plus"></i> Nueva categoria</b-button>
            </div>
            <div class="col-lg-3">
                @include('categorias.buscador')
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 mt-4">
                <div class="card">
                    <div class="card-header">
                        Lista de categorías
                    </div>
                    <div class="card-body">
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col">Nombre</th>
                                    <th scope="col">Descripción</th>
                                    <th scope="col">Color</th>
                                    <th scope="col">Opciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($categorias as $categoria)
                                    <tr>
                                        <td></td>
                                        <td style="display:none">{{$categoria->idcategoria}}</td>
                                        <td>{{$categoria->nombre}}</td>
                                        <td>{{$categoria->descripcion}}</td>
                                        <td style="background: {{$categoria->color}}"></td>
                                        <td class="botones-accion">
                                            <a @@click="editarCategoria({{$categoria->idcategoria}})" href="javascript:void(0)">
                                                <button class="btn btn-success" title="Editar categoria"><i
                                                            class="fas fa-edit"></i></button>
                                            </a>
                                            <a @@click="borrarCategoria({{$categoria->idcategoria}})" href="javascript:void(0)">
                                                <button class="btn btn-danger" title="Eliminar"><i class="fas fa-trash-alt"></i>
                                                </button>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{$categorias->links('layouts.paginacion')}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--INICIO MODAL -->
    <b-modal id="modal-1" ref="modal-1"
             title="" @@ok="agregarCategoria" @@hidden="resetModal">
<template slot="modal-title">
    @{{tituloModal}}
</template>
<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" v-model="nombre" class="form-control">
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea v-model="descripcion" class="form-control" cols="30"
                          rows="4"></textarea>
            </div>
            <div class="form-group">
                <label for="descripcion">Color:</label>
                <select v-model="color" class="custom-select">
                    <option value="-1">Ninguno</option>
                    <option value="orange">Naranja</option>
                    <option value="red">Rojo</option>
                    <option value="#0680a5">Azul</option>
                    <option value="#72ab17">Verde</option>
                    <option value="#9528d9">Lila</option>
                    <option value="#d92882">Rosado</option>
                </select>
            </div>
            <div v-for="error in errorDatosCategoria">
                <p class="texto-error">@{{ error }}</p>
            </div>
        </div>
    </div>
</div>
</b-modal>
<!--FIN MODAL -->

@endsection
@section('script')
    <script>
        let app = new Vue({
            el: '.app',
            data: {
                errorDatosCategoria: [],
                errorCategoria: 0,
                tituloModal:'Agregar categoría',
                accion:'insertar',
                idcategoria: -1,
                nombre: '',
                descripcion: '',
                color:'-1',
            },
            methods: {
                agregarCategoria(e){
                    if (this.validarCategoria()) {
                        e.preventDefault();
                        return;
                    }
                    if(this.accion=='insertar'){
                        axios.post('{{action('CategoriaController@store')}}', {
                            'nombre': this.nombre,
                            'descripcion': this.descripcion,
                            'color': this.color
                        })
                            .then(function (response) {
                                window.location.reload(true)
                            })
                            .catch(function (error) {
                                alert('Ha ocurrido un error.');
                                console.log(error);
                            });
                    } else{
                        axios.put('{{action('CategoriaController@update')}}', {
                            'idcategoria': this.idcategoria,
                            'nombre': this.nombre,
                            'descripcion': this.descripcion,
                            'color': this.color
                        })
                            .then(function (response) {
                                window.location.reload(true)
                            })
                            .catch(function (error) {
                                alert('Ha ocurrido un error.');
                                console.log(error);
                            });
                    }

                },
                editarCategoria(id){
                    this.tituloModal='Editar categoria';
                    this.accion='editar';
                    this.idcategoria=id;
                    axios.get('{{url('/categorias/edit')}}' + '/' + id)
                        .then(response => {
                            let datos = response.data;
                            this.nombre=datos.nombre;
                            this.descripcion=datos.descripcion;
                            this.color=datos.color;
                            this.$refs['modal-1'].show();
                        })
                        .catch(function (error) {
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });

                },
                borrarCategoria(id){
                    if(confirm('Realmente desea eliminar la categoria')){

                        axios.delete('{{url('/categorias/destroy')}}' + '/' + id)
                            .then(function (response) {
                                window.location.reload(true)
                            })
                            .catch(function (error) {
                                alert('No puedes eliminar la categoría porque algunos productos pertenecen a ella.');
                                console.log(error);
                            });
                    }
                },
                validarCategoria(){
                    this.errorCategoria = 0;
                    this.errorDatosCategoria = [];
                    if (this.nombre.length==0) this.errorDatosCategoria.push('*Nombre de categoria no puede estar vacio');
                    if (this.errorDatosCategoria.length) this.errorCategoria = 1;
                    return this.errorCategoria;
                },
                resetModal(){
                        this.errorDatosCategoria=[];
                        this.errorCategoria= 0;
                        this.tituloModal='Agregar categoria';
                        this.accion='insertar';
                        this.idcategoria=-1;
                        this.nombre= '';
                        this.descripcion= '';
                        this.color='-1';

                }
            }

        });
    </script>
@endsection