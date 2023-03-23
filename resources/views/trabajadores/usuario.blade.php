@extends('layouts.main')
@section('titulo', 'Trabajadores')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1">
                    <a href="{{url('trabajadores')}}"><i class="fas fa-arrow-circle-left"></i></a>
                    Gestionar accesos: {{$trabajador->persona['nombre'].' '.$trabajador->persona['apellidos']}}
                </h3>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 mt-4 mb-3">
                <div class="card">
                    <div class="card-header">
                        <b-form-checkbox @change="eliminar_credenciales" v-model="es_usuario" switch size="lg"><p style="font-size: 1rem;">Establecer como usuario</p></b-form-checkbox>
                    </div>
                    <div v-show="es_usuario" class="card-body">
                        <div class="row">
                            <div class="col-lg-4 form-group">
                                <label>Nombre de usuario</label>
                                <input type="text" v-model="usuario" name="usuario" placeholder="Usuario"
                                       class="form-control">
                            </div>
                            <div v-show="!existe_password" class="col-lg-4 form-group">
                                <label>Contraseña</label>
                                <input type="password" v-model="first_password" name="password" placeholder="Contraseña"
                                       class="form-control">
                            </div>
                            <div v-show="existe_password" class="col-lg-4 form-group">
                                <label>Contraseña</label>
                                <div class="row no-gutters">
                                    <div class="col-lg-9">
                                        <input :disabled="password=='password-text'" type="password" v-model="password" name="password" placeholder="Contraseña"
                                               class="form-control">
                                    </div>
                                    <div class="col-lg-3">
                                        <b-button variant="primary" @click="editar_password"><i class="fas fa-edit"></i></b-button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 form-group">
                                <label for="tipo_acceso">Nivel de acceso:</label>
                                <select v-model="tipo_acceso" name="tipo_acceso" class="custom-select" id="tipo_acceso">
                                    @foreach($roles as $rol)
                                        @if($rol['name']!='Superusuario')
                                            <option value="{{$rol['name']}}">{{$rol['name']}}</option>
                                        @endif
                                    @endforeach
                                    {{--@if(auth()->user()->acceso==='1')
                                    <option value="1">Supervisor</option>
                                    @endif
                                    <option value="2">Administración</option>
                                    <option value="3">Caja</option>
                                    <option value="4">Almacén</option>--}}
                                </select>
                            </div>
                            <div class="col-lg-12">
                                <div v-for="error in errorDatosCredenciales">
                                    <p class="texto-error">@{{ error }}</p>
                                </div>
                            </div>
                            <div class="col-lg-12 mt-4">
                                <b-button :disabled="usuario=='' || password==''" variant="primary" @click="guardar_credenciales"><i class="fas fa-save"></i> Guardar</b-button>
                                <b-button variant="danger" href="{{url('trabajadores/')}}"><i class="fas fa-ban"></i> Cancelar</b-button>
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
                idtrabajador:'<?php echo $trabajador['idempleado'] ?>',
                es_usuario:<?php echo $trabajador['es_usuario']===1?'true':'false' ?>,
                tipo_acceso:'<?php echo $acceso ?>',
                usuario:'<?php echo $trabajador['usuario'] ?>',
                usuario_aux:'<?php echo $trabajador['usuario'] ?>',
                password:'password-text',
                first_password:'',
                existe_password:<?php echo $trabajador['password']==null?0:1 ?>,
                existe_usuario:0,
                edicion_password:0,
                errorDatosCredenciales: [],
                errorCredenciales: 0,
            },
            methods:{
                guardar_credenciales(e){
                    if (this.validarCredenciales()) {
                        e.preventDefault();
                        return;
                    }

                    let password='';

                    if(this.existe_password){
                        password=this.password;
                    } else{
                        password=this.first_password;
                    }

                    let dataset={
                        'idtrabajador': this.idtrabajador,
                        'usuario': this.usuario,
                        'password': password,
                        'es_usuario':this.es_usuario,
                        'edicion_password': this.edicion_password,
                        'existe_password': this.existe_password,
                        'acceso': this.tipo_acceso
                    };

                    //verificar si existe usuario en la base de datos:
                    if(this.usuario!=this.usuario_aux){
                        axios.post('{{action('TrabajadorController@verificarUsuario')}}', {
                            'usuario': this.usuario
                        })
                            .then(response => {
                                if (response.data){
                                    alert('*El usuario ya existe en la base de datos');
                                }
                                else{
                                    axios.post('{{action('TrabajadorController@guardar_credenciales')}}', dataset)
                                        .then(() => {
                                            window.location.href = "/trabajadores"
                                        })
                                        .catch(error => {
                                            alert('Ha ocurrido un error al guardar los datos.');
                                            console.log(error);
                                        });
                                }
                            })
                            .catch(error => {
                                alert('Ha ocurrido un error al verificar el usuario.');
                                console.log(error);
                            });
                    } else{
                        axios.post('{{action('TrabajadorController@guardar_credenciales')}}', dataset)
                            .then(() => {
                                window.location.href = "/trabajadores"
                            })
                            .catch(error => {
                                alert('Ha ocurrido un error al guardar los datos.');
                                console.log(error);
                            });
                    }


                },
                validarCredenciales(){
                    this.errorCredenciales = 0;
                    this.errorDatosCredenciales = [];
                    if(this.existe_password){
                        password=this.password;
                    } else{
                        password=this.first_password;
                    }
                    if (this.usuario.length<4) this.errorDatosCredenciales.push('*El usuario debe tener como mínimo 4 caracteres');
                    if (password.length<4) this.errorDatosCredenciales.push('*La contraseña debe tener como mínimo 4 caracteres');

                    if (this.errorDatosCredenciales.length) this.errorCredenciales = 1;
                    return this.errorCredenciales;

                },
                editar_password(){
                    this.password='';
                    this.edicion_password=1;
                },
                eliminar_credenciales(){
                    if(this.es_usuario){
                        if(confirm('Se eliminarán las credenciales de este empleado y dejará de ser usuario del sistema, ¿Desea continuar?')){

                            let dataset={
                                'idtrabajador': this.idtrabajador
                            };

                            axios.post('{{action('TrabajadorController@eliminar_credenciales')}}', dataset)
                                .then(() => {
                                    window.location.href = "/trabajadores"
                                })
                                .catch(error => {
                                    alert('Ha ocurrido un error al guardar los datos.');
                                    console.log(error);
                                });
                        } else{
                            this.es_usuario=1;
                            window.location.href = "/trabajadores/usuario"+'/'+this.idtrabajador;
                        }
                    }
                }
            }
        });
    </script>
@endsection