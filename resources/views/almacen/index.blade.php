@extends('layouts.main')
@section('titulo', 'Almacén')
@section('contenido')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1">Almacén</h3>
                {{--<b-button v-b-modal.modal-1 variant="primary"><i class="fas fa-plus"></i> Nuevo almacén</b-button>--}}
            </div>
           {{-- <div class="col-lg-3">
                @include('almacen.buscador')
            </div>--}}
        </div>
        <div class="row">
            <div class="col-sm-12 mt-4">
                <div class="card">
                    <div class="card-header">
                        Lista de almacenes
                    </div>
                    <div class="card-body">
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col">Código</th>
                                    <th scope="col">Nombre</th>
                                    {{--<th scope="col">Mostrar
                                        <i class="fas fa-question-circle" id="popover-target-1"></i>
                                        <b-popover target="popover-target-1" triggers="hover" placement="top" variant="danger">
                                            Cuando realices una venta muestra u oculta los productos pertenecientes a determinado almacén
                                        </b-popover>
                                    </th>--}}
                                    <th scope="col">Opciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($almacen) > 0)
                                    @foreach($almacen as $item)
                                        <tr>
                                            <td></td>
                                            <td>{{$item->codigo}}</td>
                                            <td>{{$item->nombre}}</td>
                                            {{--<td>
                                                <b-form-checkbox switch size="sm">
                                                </b-form-checkbox>
                                            </td>--}}
                                            <td class="botones-accion">
                                                <button @click="editarAlmacen({{$item->idalmacen}})" class="btn btn-success" title="Editar almacen"><i
                                                            class="fas fa-edit"></i></button>
                                                <button @click="editarUbicacion({{$item->idalmacen}})" class="btn btn-warning" title="Ubicación"><i class="fas fa-flag-checkered"></i></button>
                                                @if($item->idalmacen != 1)
                                                <button @click="borrarAlmacen({{$item->idalmacen}})" class="btn btn-danger" title="Eliminar"><i class="fas fa-trash-alt"></i>
                                                </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="text-center">
                                        <td colspan="4">No hay datos que mostrar</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                        {{$almacen->links('layouts.paginacion')}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--INICIO MODAL -->
    <b-modal id="modal-1" ref="modal-1"
             title="" @@ok="agregarAlmacen" @@hidden="resetModal">
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
                <div v-for="error in errorDatosAlmacen">
                    <p class="texto-error">@{{ error }}</p>
                </div>
            </div>
        </div>
    </div>
    </b-modal>
    <!--FIN MODAL -->
    <!--INICIO MODAL UBICACION -->
    <b-modal id="modal-2" ref="modal-2" size="lg"
             title="" @@ok="agregarAlmacen" @@hidden="resetModal">
    <template slot="modal-title">
        Ubicaciones
    </template>
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-4 mb-3">
                        <button @click="agregarUbicacion" class="btn btn-primary"><i class="fas fa-plus"></i> Agregar ubicación
                        </button>
                    </div>
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-12" v-for="(item,index) in ubicacion" :key="index" v-show="item.eliminado == 0">
                                <div class="row">
                                    <div class="col-lg-4 form-group">
                                        <label for="precio">Rack / estante:</label>
                                        <input class="form-control" v-model="item.nombre" type="text" maxlength="30">
                                    </div>
                                    <div class="col-lg-6 form-group">
                                        <label for="precio">Descripción:</label>
                                        <input class="form-control" v-model="item.descripcion" type="text" maxlength="30">
                                    </div>
                                    <div class="col-lg-2">
                                        <button v-show="item.idubicacion != 1" @click="borrarUbicacion(index)" style="margin-top: 20px" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-for="error in errorDatosAlmacen">
                        <p class="texto-error">@{{ error }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <template #modal-footer="{ ok, cancel}">
        <b-button variant="secondary" @click="cancel()">
            Cancel
        </b-button>
        <b-button :disabled="mostrarProgresoGuardado"  variant="primary" @click="guardarUbicacion">
            <b-spinner v-show="mostrarProgresoGuardado" small label="Loading..." ></b-spinner>
            <span v-show="!mostrarProgresoGuardado">OK</span>
        </b-button>
    </template>
    </b-modal>
    <!--FIN MODAL UBICACION -->

@endsection
@section('script')
    <script>
        let app = new Vue({
            el: '.app',
            data: {
                mostrarProgresoGuardado:false,
                errorDatosAlmacen: [],
                errorAlmacen: 0,
                tituloModal:'Agregar almacén',
                accion:'insertar',
                idalmacen: -1,
                nombre: '',
                ubicacion: [],
            },
            methods: {
                agregarAlmacen(e){
                    if (this.validarAlmacen()) {
                        e.preventDefault();
                        return;
                    }
                    let url = this.accion=='insertar'?'{{action('AlmacenController@store')}}':'{{action('AlmacenController@update')}}';

                    axios.post(url, {
                        'idalmacen':this.idalmacen,
                        'nombre': this.nombre
                    })
                        .then(response => {
                            window.location.reload(true)
                        })
                        .catch(function (error) {
                            alert(error);
                            console.log(error);
                        });

                },
                editarAlmacen(id){
                    this.tituloModal='Editar almacén';
                    this.accion='editar';
                    this.idalmacen=id;
                    axios.get('{{url('/almacenes/editar')}}' + '/' + id)
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
                borrarAlmacen(id){
                    if(confirm('Realmente desea eliminar el almacén')){

                        axios.delete('{{url('/almacenes/destroy')}}' + '/' + id)
                            .then(function (response) {
                                window.location.reload(true)
                            })
                            .catch(function (error) {
                                alert('No puedes eliminar el almacén porque contiene productos.');
                                console.log(error);
                            });
                    }
                },
                validarAlmacen(){
                    this.errorAlmacen = 0;
                    this.errorDatosAlmacen = [];
                    if (this.nombre.length==0) this.errorDatosAlmacen.push('*Nombre de almacén no puede estar vacio');
                    if (this.errorDatosAlmacen.length) this.errorAlmacen = 1;
                    return this.errorAlmacen;
                },
                editarUbicacion(idalmacen){
                    this.idalmacen = idalmacen;
                    axios.get('{{url('/almacenes/editar-ubicacion')}}' + '/' + idalmacen)
                        .then(response => {
                            this.ubicacion = response.data;
                            this.$refs['modal-2'].show();
                        })
                        .catch(function (error) {
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });
                },
                validarUbicacion(){
                    this.errorAlmacen = 0;
                    this.errorDatosAlmacen = [];
                    for (let item of this.ubicacion) {
                        if (item.nombre.length == 0 && item.eliminado == 0){
                            this.errorDatosAlmacen.push('*Nombre de rack o estante no puede estar vacio');
                            break;
                        }

                    }
                    if (this.errorDatosAlmacen.length) this.errorAlmacen = 1;
                    return this.errorAlmacen;
                },
                agregarUbicacion(){
                    this.ubicacion.push({
                        idubicacion:null,
                        idalmacen:this.idalmacen,
                        nombre: '',
                        descripcion: '',
                        eliminado: 0
                    });
                },
                borrarUbicacion(index){
                    this.ubicacion[index]['eliminado'] = 1;
                },
                guardarUbicacion(e){
                    if (this.validarUbicacion()) {
                        e.preventDefault();
                        return;
                    }

                    axios.post('{{action('AlmacenController@storeUbicacion')}}', {
                        'idalmacen':this.idalmacen,
                        'ubicacion': JSON.stringify(this.ubicacion),
                    })
                        .then(response => {
                            window.location.reload(true)
                        })
                        .catch(function (error) {
                            alert(error);
                            console.log(error);
                        });
                },
                resetModal(){
                    this.errorDatosAlmacen=[];
                    this.errorAlmacen= 0;
                    this.tituloModal='Agregar almacén';
                    this.accion='insertar';
                    this.idalmacen=-1;
                    this.nombre= '';
                    this.descripcion= '';
                    this.color='-1';

                }
            }

        });
    </script>
@endsection