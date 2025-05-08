@extends('layouts.main')
@section('titulo', 'Trabajadores')
@section('contenido')
    @php $agent = new \Jenssegers\Agent\Agent() @endphp
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1">Trabajadores</h3>
                <b-button v-b-modal.modal-1 variant="primary"><i class="fas fa-plus"></i> Nuevo</b-button>
            </div>
            <div class="col-lg-3">
                @include('trabajadores.buscador')
            </div>
        </div>
        @if($textoBuscado!='')
            <div class="row">
                <div class="col-lg-12 mt-5">
                    <div class="alert alert-dark" role="alert"><h5 class="mb-0">Resultados de búsqueda para: {{$textoBuscado}}
                            <a href="{{url('/trabajadores')}}"><i class="fa fa-times float-right"></i></a></h5></div>
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-sm-12 mt-4">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col">Nombre y apellidos</th>
                                    <th scope="col">Dni</th>
                                    <th scope="col">Direccion</th>
                                    <th scope="col">Telefono</th>
                                    <th scope="col">E-mail</th>
                                    <th scope="col">Pago</th>
                                    <th scope="col">Opciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($trabajadores as $trabajador)
                                    @if($trabajador->acceso != 1)
                                        <tr @if(!$agent->isDesktop()) @click="editarTrabajador({{$trabajador->idempleado}})" @endif>
                                            <td></td>
                                            <td style="display:none">{{$trabajador->idempleado}}</td>
                                            <td> <i v-show="{{$trabajador->es_usuario}}==1"
                                                        class="fas fa-user-circle"></i> {{$trabajador->nombre}} {{$trabajador->apellidos}}</td>
                                            <td>{{$trabajador->dni}}</td>
                                            <td>{{$trabajador->direccion}} - {{$trabajador->ciudad}}</td>
                                            <td>{{$trabajador->telefono}}</td>
                                            <td>{{$trabajador->correo}}</td>
                                            <td>{{$trabajador->dia_pago}}</td>
                                            <td @click.stop class="botones-accion" style="width: 20%">
                                                <b-button @click="editarTrabajador({{$trabajador->idempleado}})" class="btn btn-success" title="Editar trabajador"><i
                                                            class="fas fa-edit"></i></b-button>
                                                <a @if($trabajador->es_usuario || !$bloquear_registro)
                                                       href="{{ url('trabajadores/usuario') . '/' . $trabajador->idempleado }}"
                                                   @else
                                                       onclick="alert('Ya no se pueden crear más usuarios. Migra a un plan superior.');"
                                                        @endif>
                                                    <button class="btn btn-info" title="Gestionar accesos"><i
                                                                class="fas fa-key"></i></button>
                                                </a>
                                                <a href="{{url('trabajadores/pagos').'/'.$trabajador->idempleado}}">
                                                    <button class="btn btn-warning" title="Historial de pagos"><i
                                                                class="fas fa-coins"></i></button>
                                                </a>
                                                <b-button @click="borrarTrabajador({{$trabajador->idempleado}})" class="btn btn-danger" title="Eliminar"><i
                                                            class="fas fa-trash-alt"></i>
                                                </b-button>
                                            </td>
                                        </tr>
                                    @endif
                                    @if($trabajador->acceso == 1 && $acceso == 1)
                                        <tr @if(!$agent->isDesktop()) @click="editarTrabajador({{$trabajador->idempleado}})" @endif>
                                            <td></td>
                                            <td style="display:none">{{$trabajador->idempleado}}</td>
                                            <td><i v-show="{{$trabajador->es_usuario}}==1" class="fas fa-user-circle"></i> {{$trabajador->nombre}} {{$trabajador->apellidos}}</td>
                                            <td>{{$trabajador->dni}}</td>
                                            <td>{{$trabajador->direccion}} - {{$trabajador->ciudad}}</td>
                                            <td>{{$trabajador->telefono}}</td>
                                            <td>{{$trabajador->correo}}</td>
                                            <td>{{$trabajador->dia_pago}}</td>
                                            <td @click.stop class="botones-accion" style="width: 20%">
                                                <a @click="editarTrabajador({{$trabajador->idempleado}})" href="javascript:void(0)">
                                                    <button class="btn btn-success" title="Editar trabajador"><i
                                                                class="fas fa-edit"></i></button>
                                                </a>
                                                <a href="{{url('trabajadores/usuario').'/'.$trabajador->idempleado}}">
                                                    <button class="btn btn-info" title="Gestionar accesos"><i class="fas fa-key"></i></button>
                                                </a>
                                                <a href="{{url('trabajadores/pagos').'/'.$trabajador->idempleado}}">
                                                    <button class="btn btn-warning" title="Historial de pagos"><i class="fas fa-coins"></i></button>
                                                </a>
                                                <a @click="borrarTrabajador({{$trabajador->idempleado}})" href="javascript:void(0)">
                                                    <button class="btn btn-danger" title="Eliminar"><i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </a>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{$trabajadores->links('layouts.paginacion')}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--INICIO MODAL -->
    <b-modal id="modal-1" ref="modal-1" size="lg"
             title="" @@ok="agregarTrabajador" @@hidden="resetModal">
<template slot="modal-title">
    @{{tituloModal}}
</template>
<div class="container">
    <div class="card" style="box-shadow:none; margin-bottom:20px" v-show="accion=='insertar'">
        <div class="card-header">
            Buscar por DNI
        </div>
        <div class="card-body row">
            <div class="col-lg-6 form-group">
                <label>Número de DNI:</label>
                <b-input-group>
                    <input v-model="dni_buscar" @keyup.enter="buscarDNI" class="form-control" maxlength="8" type="number" autocomplete="off">
                    <b-input-group-append>
                        <b-button @click="buscarDNI" variant="primary">
                            <span v-show="!spinnerDni"><i class="fas fa-search"></i></span>
                            <b-spinner v-show="spinnerDni" small></b-spinner>
                        </b-button>
                    </b-input-group-append>
                </b-input-group>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" v-model="nombre" name="nombre" class="form-control">
            </div>
        </div>
        <div class="col-lg-6">
            <div class="form-group">
                <label for="apellidos">Apellidos:</label>
                <input type="text" v-model="apellidos" name="apellidos"  class="form-control">
            </div>
        </div>
        <div class="col-lg-3">
            <div class="form-group">
                <label for="dni">DNI:</label>
                <input type="text" v-model="dni" name="dni"  class="form-control">
            </div>
        </div>
        <div class="col-lg-6">
            <div class="form-group">
                <label for="direccion">Dirección:</label>
                <input type="text"  v-model="direccion" name="direccion" class="form-control">
            </div>
        </div>
        <div class="col-lg-3">
            <div class="form-group">
                <label for="ciudad">Ciudad:</label>
                <input type="text" v-model="ciudad" name="ciudad" class="form-control">
            </div>
        </div>
        <div class="col-lg-3">
            <div class="form-group">
                <label for="telefono">Teléfono:</label>
                <input type="text"  v-model="telefono" name="telefono" class="form-control">
            </div>
        </div>
        <div class="col-lg-6">
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="text" v-model="email" name="email" class="form-control">
            </div>
        </div>
        <div class="col-lg-3">
            <div class="form-group">
                <label for="fecha_in">Inicio de labores:</label>
                <input type="date" v-model="fecha_in" name="fecha_in" class="form-control">
            </div>
        </div>
        <div class="col-lg-3">
            <div class="form-group">
                <label for="ciclo_pago">Ciclo de pago:</label>
                <select v-model="ciclo_pago" name="ciclo_pago" class="custom-select" id="ciclo_pago">
                    <option value="1">Mensual</option>
                    <option value="2">Quincenal</option>
                    <option value="3">Semanal</option>
                </select>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="form-group">
                <label for="dia_pago">Día de pago:</label>
                <select v-model="dia_pago" name="dia_pago" class="custom-select" id="dia_pago">
                    <option value="1">1 de cada mes</option>
                    <option value="2">2 de cada mes</option>
                    <option value="3">3 de cada mes</option>
                    <option value="4">4 de cada mes</option>
                    <option value="5">5 de cada mes</option>
                    <option value="6">6 de cada mes</option>
                    <option value="7">7 de cada mes</option>
                    <option value="8">8 de cada mes</option>
                    <option value="9">9 de cada mes</option>
                    <option value="10">10 de cada mes</option>
                    <option value="11">11 de cada mes</option>
                    <option value="12">12 de cada mes</option>
                    <option value="13">13 de cada mes</option>
                    <option value="14">14 de cada mes</option>
                    <option value="15">15 de cada mes</option>
                    <option value="16">16 de cada mes</option>
                    <option value="17">17 de cada mes</option>
                    <option value="18">18 de cada mes</option>
                    <option value="19">19 de cada mes</option>
                    <option value="20">20 de cada mes</option>
                    <option value="21">21 de cada mes</option>
                    <option value="22">22 de cada mes</option>
                    <option value="23">23 de cada mes</option>
                    <option value="24">24 de cada mes</option>
                    <option value="25">25 de cada mes</option>
                    <option value="26">26 de cada mes</option>
                    <option value="27">27 de cada mes</option>
                    <option value="28">28 de cada mes</option>
                    <option value="29">29 de cada mes</option>
                    <option value="30">30 de cada mes</option>
                    <option value="31">31 de cada mes</option>
                </select>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="form-group">
                <label for="sueldo">Salario:</label>
                <input type="text" v-model="sueldo" name="sueldo" class="form-control">
            </div>
        </div>
        <div class="col-lg-3">
            <div class="form-group">
                <label>Cargo:</label>
                <select v-model="cargo" class="custom-select">
                    <option value="-1">Ninguno</option>
                    <option value="1">Vendedor</option>
                    <option value="2">Motorizado (a)</option>
                </select>
            </div>
        </div>
        <div class="col-lg-12">
            <div v-for="error in errorDatosTrabajador">
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
        window.userId = {{ auth()->user()->idempleado }};
        let app = new Vue({
            el: '.app',
            data: {
                errorDatosTrabajador: [],
                errorTrabajador: 0,
                tituloModal:'Agregar empleado',
                accion:'insertar',
                idtrabajador: -1,
                nombre: '',
                apellidos: '',
                dni: '',
                direccion: '',
                ciudad: '',
                telefono:'',
                email: '',
                fecha_in: '{{date('Y-m-d')}}',
                ciclo_pago: 1,
                sueldo:'',
                dia_pago:'31',
                cargo:-1,
                dni_buscar: '',
                spinnerDni: false,
            },
            methods: {
                buscarDNI() {
                    if (this.dni_buscar.length !== 8 || isNaN(this.dni_buscar)) {
                        alert('Ingrese un DNI válido');
                        return;
                    }

                    this.spinnerDni = true;

                    axios.post('/helper/buscar-ruc', {
                        num_doc: this.dni_buscar,
                        tipo_doc: 1
                    })
                        .then(response => {
                            let data = response.data;
                            if (!data || !data.nombre_o_razon_social) {
                                alert('No se encontró información, ingréselo manualmente');
                            } else {
                                const nombresCompletos = data.nombre_o_razon_social.trim().split(' ');
                                this.nombre = nombresCompletos.slice(2).join(' ');
                                this.apellidos = nombresCompletos.slice(0, 2).join(' ');
                                this.dni = data.ruc;
                                this.direccion = data.direccion || '';
                            }
                            this.spinnerDni = false;
                        })
                        .catch(error => {
                            this.spinnerDni = false;
                            alert('Error al consultar DNI');
                            console.error(error);
                        });
                },
                agregarTrabajador(e){
                    if (this.validarTrabajador()) {
                        e.preventDefault();
                        return;
                    }

                    let dataset={
                        'idtrabajador': this.idtrabajador,
                        'nombre': this.nombre,
                        'apellidos': this.apellidos,
                        'dni': this.dni,
                        'direccion': this.direccion,
                        'ciudad': this.ciudad,
                        'telefono': this.telefono,
                        'email': this.email,
                        'fecha_ingreso': this.fecha_in,
                        'ciclo_pago': this.ciclo_pago,
                        'sueldo': this.sueldo,
                        'dia_pago': this.dia_pago,
                        'cargo': this.cargo
                    };

                    let tipo_accion=this.accion=='insertar'?'{{action('TrabajadorController@store')}}':'{{action('TrabajadorController@update')}}';

                    axios.post(tipo_accion, dataset)
                        .then(() => {
                            window.location.reload(true)
                        })
                        .catch(error => {
                            alert('Ha ocurrido un error al guardar los datos.');
                            console.log(error);
                        });

                },
                editarTrabajador(id){
                    this.tituloModal='Editar trabajador';
                    this.accion='editar';
                    this.idtrabajador=id;
                    axios.get('{{url('/trabajadores/edit')}}' + '/' + id)
                        .then(response => {
                            let datos = response.data;
                            this.nombre=datos.persona.nombre;
                            this.apellidos=datos.persona.apellidos;
                            this.dni=datos.dni;
                            this.direccion=datos.persona.direccion;
                            this.ciudad=datos.persona.ciudad;
                            this.telefono=datos.persona.telefono;
                            this.email=datos.persona.correo;
                            this.usuario=datos.usuario;
                            this.fecha_in=datos.fecha_ingreso;
                            this.ciclo_pago=datos.ciclo_pago;
                            this.sueldo=datos.sueldo;
                            this.dia_pago=datos.dia_pago;
                            this.cargo=datos.cargo;
                            this.$refs['modal-1'].show();
                        })
                        .catch(error => {
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });

                },
                borrarTrabajador(id) {
                    if (confirm('Realmente desea eliminar el trabajador')) {
                        axios.delete('{{url('/trabajadores/destroy')}}' + '/' + id)
                            .then(() => {
                                if (parseInt(id) === window.userId) {
                                    window.location.href = '{{ url('/logout') }}';
                                } else {
                                    window.location.reload(true);
                                }
                            })
                            .catch(error => {
                                console.log(error);
                            });
                    }
                },
                validarTrabajador(){
                    this.errorTrabajador = 0;
                    this.errorDatosTrabajador = [];
                    if (this.nombre.length==0) this.errorDatosTrabajador.push('*Nombre de trabajador no puede estar vacio');
                    if (this.apellidos.length==0) this.errorDatosTrabajador.push('*Apellidos de trabajador no puede estar vacio');
                    if(isNaN(this.telefono)) this.errorDatosTrabajador.push('*El campo telefono debe contener un número');
                    if(isNaN(this.dni)) this.errorDatosTrabajador.push('*El campo DNI debe contener un número');

                    if (this.errorDatosTrabajador.length) this.errorTrabajador = 1;
                    return this.errorTrabajador;

                },
                resetModal(){
                    this.errorDatosTrabajador=[];
                    this.errorTrabajador= 0;
                    this.tituloModal='Agregar trabajador';
                    this.accion='insertar';
                    this.idtrabajador= -1;
                    this.nombre= '';
                    this.apellidos= '';
                    this.dni= '';
                    this.direccion= '';
                    this.ciudad= '';
                    this.telefono='';
                    this.email= '';
                    this.fecha_in='{{date('Y-m-d')}}';
                    this.ciclo_pago=1;
                    this.sueldo='';
                    this.dia_pago='31';
                    this.cargo=-1;
                }
            }

        });
    </script>
@endsection