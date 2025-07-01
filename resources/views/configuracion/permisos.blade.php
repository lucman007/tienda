@extends('layouts.main')
@section('titulo', 'Permisos')
@section('contenido')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1 mb-2">
                    <a href="{{url('configuracion?tab=roles')}}"><i class="fas fa-arrow-circle-left"></i></a>
                    Rol {{$rol['name']}}
                </h3>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        Editar permisos de rol
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div v-for="permiso in permisos_parent" class="col-lg-4">
                                <b-form-checkbox v-model="permiso.isSelected"
                                                 @change="buscar_submodulos(permiso)"
                                                 :key="permiso.id" switch size="lg">
                                    <p style="font-size: 1rem;">@{{ permiso.name }}</p>
                                </b-form-checkbox>
                                <b-form-group class="permisos-submodulo">
                                    <b-form-checkbox
                                            v-for="option in seleccionados" v-if="option.parent == permiso.name" :key="option.id"
                                            v-model="option.isSelected"
                                            v-show="option.name != 'Configuración: crear permisos'"
                                            :value="option.name">@{{ option.child_name }}
                                    </b-form-checkbox>
                                </b-form-group>
                            </div>
                            <div class="col-lg-12 mt-3">
                                <div class="alert alert-secondary" style="display: flow-root;">
                                    <button @click="guardar_privilegios" class="btn btn-success float-right">Guardar</button>
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
                nombre_rol: '',
                nombre_permiso: '',
                privilegios: <?php echo $permisos?>,
                seleccionados: [],
                permisos_parent:<?php echo $permisos_parent?>,
            },
            created(){
                for (let permiso of this.permisos_parent) {
                    if(permiso.isSelected){
                        this.buscar_submodulos(permiso);
                    }
                }
            },
            methods: {
                buscar_submodulos(permiso){
                    if (permiso.isSelected) {
                        this.seleccionados.push(permiso);
                        for (let privilegio of this.privilegios) {
                            if (privilegio.parent == permiso.name) {
                                this.seleccionados.push(privilegio);
                            }
                        }
                    } else {
                        filtro=this.seleccionados.filter(element => {
                            return element.parent != permiso.name;
                        });
                        filtro=filtro.filter(element => {
                            return element.name != permiso.name;
                        });
                        this.seleccionados=filtro
                    }

                },
                guardar_privilegios(){

                    axios.post('{{action('ConfiguracionController@asignar_privilegios')}}', {
                        'idrol': {{$rol['id']}},
                        'privilegios': this.seleccionados
                    })
                        .then(response => {
                            location.reload();
                        })
                        .catch(error => {
                            alert('Ha ocurrido un error al guardar.');
                            console.log(error);
                        });
                }
            }

        });
    </script>
@endsection