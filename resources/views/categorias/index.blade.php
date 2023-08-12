@extends('layouts.main')
@section('titulo', 'Categorías')
@section('contenido')
    @php $agent = new \Jenssegers\Agent\Agent() @endphp
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
                                    <th scope="col"></th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($categorias) > 0)
                                @foreach($categorias as $categoria)
                                    <tr @if(!$agent->isDesktop()) @click="editarCategoria({{$categoria->idcategoria}})" @endif>
                                        <td></td>
                                        <td style="display:none">{{$categoria->idcategoria}}</td>
                                        <td>{{$categoria->nombre}}</td>
                                        <td>{{$categoria->descripcion}}</td>
                                        <td><span style="background: {{$categoria->color}};" class="cat-circle-color"></span></td>
                                        <td class="botones-accion" style="text-align: right">
                                            <b-button @click="editarCategoria({{$categoria->idcategoria}})" class="btn btn-success" title="Editar categoria"><i
                                                        class="fas fa-edit"></i></b-button>
                                            <button @click="borrarCategoria({{$categoria->idcategoria}})" class="btn btn-danger" title="Eliminar"><i class="fas fa-trash-alt"></i>
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
                    <option style="background: orange; color: white" value="orange">Naranja</option>
                    <option style="background: red; color: white" value="red">Rojo</option>
                    <option style="background: #0680a5; color: white" value="#0680a5">Azul</option>
                    <option style="background: #72ab17; color: white" value="#72ab17">Verde</option>
                    <option style="background: #9528d9; color: white" value="#9528d9">Lila</option>
                    <option style="background: #d92882; color: white" value="#d92882">Rosado</option>
                    <option style="background: #4100c1; color: white" value="#4100c1">Violeta</option>
                    <option style="background: #fff700; color: black" value="#fff700">Amarillo</option>
                    <option style="background: #891a0f; color: white" value="#891a0f">Ocre</option>
                    <option style="background: #933a06; color: white" value="#933a06">Marrón</option>
                    <option style="background: #af8900; color: white" value="#af8900">Dorado</option>
                    <option style="background: #ffa876; color: white" value="#ffa876">Melón</option>
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
                nombre: "",
                descripcion: "",
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
                            .then(response => {
                                window.location.reload(true)
                            })
                            .catch(error => {
                                this.alerta('Ha ocurrido un error.');
                                console.log(error);
                            });
                    } else{
                        axios.put('{{action('CategoriaController@update')}}', {
                            'idcategoria': this.idcategoria,
                            'nombre': this.nombre,
                            'descripcion': this.descripcion,
                            'color': this.color
                        })
                            .then(response => {
                                window.location.reload(true)
                            })
                            .catch(error => {
                                this.alerta('Ha ocurrido un error.');
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
                        .catch(error => {
                            this.alerta('Ha ocurrido un error.');
                            console.log(error);
                        });

                },
                borrarCategoria(id){
                    if(confirm('Realmente desea eliminar la categoria')){

                        axios.delete('{{url('/categorias/destroy')}}' + '/' + id)
                            .then(response => {
                                window.location.reload(true)
                            })
                            .catch(error => {
                                this.alerta('No puedes eliminar la categoría porque algunos productos pertenecen a ella.');
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
                },
                alerta(texto){
                    this.$swal({
                        position: 'top',
                        icon: 'warning',
                        title: texto,
                        timer: 6000,
                        toast:true,
                        confirmButtonColor: '#007bff',
                    });
                }
            }

        });
    </script>
@endsection