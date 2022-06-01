@extends('layouts.main')
@section('titulo', 'Configuración')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1 mb-4">
                    <a href="{{url()->previous()}}"><i class="fas fa-arrow-circle-left"></i></a>
                    Configuración
                </h3>
            </div>
        </div>
        @if(!(json_decode($configuracion->conexion, true)['esProduccion']))
        <div class="row">
            <div class="col-lg-12">
                <div class="alert alert-danger" style="background-color: #f72639; color: white">
                    MODO DEMO ACTIVADO
                </div>
            </div>
        </div>
        @endif
        <div>
            <b-card no-body>
                <b-tabs card>
                    @can('Configuración: logos')
                    <b-tab @click="tabChanged('logos')" title="Logos" :active="tabActive=='logos'">
                        <b-card-text>
                            <div class="container">
                                <div class="row">
                                    @include('configuracion.logo')
                                </div>
                            </div>
                        </b-card-text>
                    </b-tab>
                    @endcan
                    @can('Configuración: email')
                    <b-tab @click="tabChanged('email')" title="Email" :active="tabActive=='email'">
                        <b-card-text>
                            <div class="container">
                                <div class="row">
                                    @include('configuracion.email')
                                </div>
                            </div>
                        </b-card-text>
                    </b-tab>
                    @endcan
                    @can('Configuración: guía')
                    <b-tab @click="tabChanged('guia')" title="Guía" :active="tabActive=='guia'">
                        <b-card-text>
                            <div class="container">
                                <div class="row">
                                    @include('configuracion.guia')
                                </div>
                            </div>
                        </b-card-text>
                    </b-tab>
                    @endcan
                    @can('Configuración: cotización')
                    <b-tab @click="tabChanged('cotizacion')" title="Cotización" :active="tabActive=='cotizacion'">
                        <b-card-text>
                            <div class="container">
                                <div class="row">
                                    @include('configuracion.cotizacion')
                                </div>
                            </div>
                        </b-card-text>
                    </b-tab>
                    @endcan
                    @can('Configuración: comprobantes')
                    <b-tab @click="tabChanged('comprobantes')" title="Comprobantes" :active="tabActive=='comprobantes'">
                        <b-card-text>
                            <div class="container">
                                <div class="row">
                                    @include('configuracion.comprobantes')
                                </div>
                            </div>
                        </b-card-text>
                    </b-tab>
                    @endcan
                    @can('Configuración: interfaz')
                        <b-tab @click="tabChanged('interfaz')" title="Interfaz" :active="tabActive=='interfaz'">
                            <b-card-text>
                                <div class="container">
                                    <div class="row">
                                        @include('configuracion.interfaz')
                                    </div>
                                </div>
                            </b-card-text>
                        </b-tab>
                    @endcan
                    @can('Configuración: empresa')
                    <b-tab @click="tabChanged('empresa')" title="Empresa" :active="tabActive=='empresa'">
                        <b-card-text>
                            <div class="container">
                                <div class="row">
                                    @include('configuracion.empresa')
                                </div>
                            </div>
                        </b-card-text>
                    </b-tab>
                    @endcan
                    @can('Configuración: roles')
                        <b-tab @click="tabChanged('roles')" title="Roles" :active="tabActive=='roles'">
                            <div class="container">
                                <div class="row">
                                    @include('configuracion.roles')
                                </div>
                            </div>
                        </b-tab>
                    @endcan
                    @can('Configuración: sistema')
                    <b-tab @click="tabChanged('sistema')" title="Sistema" :active="tabActive=='sistema'">
                        <div class="container">
                            <div class="row">
                                @include('configuracion.sistema')
                            </div>
                        </div>
                    </b-tab>
                    @endcan
                    @can('Configuración: tenants')
                        <b-tab @click="tabChanged('tenants')" title="Tenants" :active="tabActive=='tenants'">
                            <div class="container">
                                <div class="row">
                                    @include('configuracion.tenants')
                                </div>
                            </div>
                        </b-tab>
                    @endcan
                </b-tabs>
            </b-card>
        </div>
    </div>
    <b-modal id="modal-2" ref="modal-2" size="md"
             title="" @@ok="guardar_edicion_permiso" @hidden="resetModal">
            <template slot="modal-title">
                Cambiar permiso
            </template>
    <div class="container modal-permisos">
        <div class="row">
            <div class="col-lg-12">
                <input class="form-control" type="text" v-model="nombre_permiso_editar">
            </div>
        </div>
    </div>
    </b-modal>
    <!--INICIO MODAL IMAGEN-->
    <b-modal id="modal-imagen" ref="modal-imagen" size="md" @@hidden="resetModal">
    <template slot="modal-title">
        Agregar imagen
    </template>
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <form class="form-upload" method="POST" action="{{url('productos/agregar-imagen')}}"
                      enctype="multipart/form-data">
                    <div class="col-lg-12 mb-3">
                        <label for="precio">Imagen:</label>
                        <input accept="image/x-png,image/jpeg" @change="cargarFichero" type="file" id="input_file_data">
                    </div>
                    <div class="col-lg-12">
                        <div class="image-preview" v-if="dataFichero.length > 0">
                            <img class="preview" :src="dataFichero">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <template #modal-footer="{ ok, cancel}">
        <b-button variant="secondary" @click="cancel()">
            Cancel
        </b-button>
        <b-button :disabled="!input_file_data || mostrarProgresoGuardado"  variant="primary" @click="agregarImagen">
            <b-spinner v-show="mostrarProgresoGuardado" small label="Loading..." ></b-spinner>
            <span v-show="!mostrarProgresoGuardado">OK</span>
        </b-button>
    </template>
    </b-modal>
    <!--FIN MODAL IMAGEN -->

@endsection
@section('script')
    <script>
        let app = new Vue({
            el: '.app',
            data: {
                tab:'',
                nombre_rol:'',
                nombre_permiso:'',
                nombre_permiso_editar:'',
                id_permiso_editar:'',
                rsocial:'',
                ruc:'',
                dir:'',
                privilegios: <?php echo $permisos?>,
                privilegios_seleccionados:[],
                rol_seleccionado:'',
                permissions:<?php echo $permissions?>,
                emisor:<?php echo $configuracion->emisor ?>,
                mail_send_from: <?php echo $configuracion->mail_send_from ?>,
                mail_contact: <?php echo $configuracion->mail_contact ?>,
                conexion: <?php echo $configuracion->conexion ?>,
                interfaz: <?php echo $configuracion->interfaz ?>,
                interfaz_pedidos: <?php echo $configuracion->interfaz_pedidos ?>,
                cotizacion: <?php echo $configuracion->cotizacion ?>,
                input_file_data:'',
                dataFichero: "",
                mostrarProgresoGuardado:false,
                tipo_logo:'',
                impresion:<?php echo $configuracion->impresion ?>,
                series: <?php echo $configuracion->series ?>,
                guia:<?php echo $configuracion->guia ?>,
            },
            computed:{
                tabActive(){
                    return "<?php echo !isset($_GET['tab'])?'logos':$_GET['tab'] ?>";
                },
            },
            methods: {
                guardar_rol(){
                    if(this.nombre_rol==''){
                        alert('Ingresa un nombre de rol')
                    } else{
                        axios.post('{{action('ConfiguracionController@crear_rol')}}', {
                            'rol': this.nombre_rol
                        })
                            .then(response => {
                                location.href="/configuracion?tab=roles";
                            })
                            .catch(error => {
                                alert('Ha ocurrido un error al guardar.');
                                console.log(error);
                            });
                    }

                },
                guardar_permiso(){
                    if(this.nombre_permiso==''){
                        alert('Ingresa un permiso')
                    } else {
                        axios.post('{{action('ConfiguracionController@crear_permiso')}}', {
                            'permiso': this.nombre_permiso
                        })
                            .then(response => {
                                location.href="/configuracion?tab=roles";
                            })
                            .catch(error => {
                                alert('Ha ocurrido un error al guardar.');
                                console.log(error);
                            });
                    }
                },
                editar_privilegios(id){
                    this.rol_seleccionado=id;
                    axios.post('{{action('ConfiguracionController@editar_privilegios')}}', {
                        'idrol':id,
                    })
                        .then(response => {
                            this.privilegios_seleccionados=response.data;
                        })
                        .catch(error => {
                            alert('Ha ocurrido un error al guardar.');
                            console.log(error);
                        });
                },
                guardar_privilegios(){
                    axios.post('{{action('ConfiguracionController@asignar_privilegios')}}', {
                        'idrol':this.rol_seleccionado,
                        'privilegios': this.privilegios_seleccionados
                    })
                        .then(() => {
                            location.href="/configuracion?tab=roles";
                        })
                        .catch(error => {
                            alert('Ha ocurrido un error al guardar.');
                            console.log(error);
                        });
                },
                borrarPermiso(id){
                    if(confirm('¿Está seguro de eliminar el permiso?')) {
                        axios.post('{{action('ConfiguracionController@borrar_permiso')}}', {
                            'idpermiso': id,
                        })
                            .then(response => {
                                location.href="/configuracion?tab=roles";
                            })
                            .catch(error => {
                                alert('Ha ocurrido un error al guardar.');
                                console.log(error);
                            });
                    }
                },
                editarPermiso(id, nombre){
                    this.nombre_permiso_editar = nombre;
                    this.id_permiso_editar = id;
                    this.$refs['modal-2'].show();
                },
                guardar_edicion_permiso(){

                    axios.post('{{action('ConfiguracionController@actualizar_permiso')}}', {
                        'idpermiso': this.id_permiso_editar,
                        'nombre': this.nombre_permiso_editar
                    })
                        .then(response => {
                            location.href="/configuracion?tab=roles";
                        })
                        .catch(error => {
                            alert('Ha ocurrido un error al guardar.');
                            console.log(error);
                        });
                },
                resetModal(){
                    this.nombre_permiso_editar = '';
                    this.id_permiso_editar = '';
                },
                guardarConfiguracion(clave){
                    axios.post('{{action('ConfiguracionController@guardarConfiguracion')}}', {
                        'clave': clave,
                        'valor': JSON.stringify(this[clave]),
                    })
                        .then(response => {
                            this.$swal({
                                position: 'center',
                                icon: 'success',
                                title: response.data,
                                showConfirmButton: true,
                                timer: 1500
                            }).then(() => {
                                if(clave=='conexion' || clave =='interfaz'){
                                    location.reload(true);
                                }
                            });
                        })
                        .catch(error => {
                            this.$swal({
                                position: 'center',
                                icon: 'error',
                                title: error.response.data.mensaje,
                                showConfirmButton: true,
                                timer: 1500
                            }).then(() => {
                                if(clave=='conexion' || clave =='interfaz'){
                                    location.reload(true);
                                }
                            });
                            console.log(error);
                        });
                },
                cargarFichero(event){
                    let input = event.target;
                    if (input.files && input.files[0]) {
                        // create a new FileReader to read this image and convert to base64 format
                        let reader = new FileReader();
                        // Define a callback function to run, when FileReader finishes its job
                        reader.onload = (e) => {
                            // Note: arrow function used here, so that "this.dataFichero" refers to the dataFichero of Vue component
                            // Read image as base64 and set to dataFichero
                            this.dataFichero = e.target.result;
                            this.dataFicheroType = (this.dataFichero).split(";")[0];
                            let sizeFile = ((input.files[0].size/1024)/1024).toFixed(4);
                            if(sizeFile > 2.5){
                                alert('Archivo demasiado grande. Tamaño máximo permitido 2MB');
                                this.input_file_data = false;
                                this.dataFichero = "";
                            }
                            if(!(this.dataFicheroType == 'data:image/jpg' || this.dataFicheroType == 'data:image/png' || this.dataFicheroType == 'data:image/jpeg')){
                                alert('Tipo de archivo no admitido: Solo es válido ficheros JPG y PNG');
                                this.input_file_data = false;
                                this.dataFichero = "";
                            }
                        };
                        // Start the reader job - read file as a data url (base64 format)
                        reader.readAsDataURL(input.files[0]);
                        this.input_file_data = input.files[0];
                    }

                },
                modalImagen(tipo_logo,imagen){
                    if(imagen){
                        this.dataFichero='/images/'+imagen;
                    }
                    this.$refs['modal-imagen'].show();
                    this.tipo_logo = tipo_logo;
                },
                agregarImagen(){
                    this.mostrarProgresoGuardado=true;
                    let data = new FormData();
                    data.append('imagen', this.input_file_data);
                    data.append('tipo_logo',this.tipo_logo);
                    let settings = {headers: {'content-type': 'multipart/form-data'}};

                    axios.post('{{url('/configuracion/agregar-imagen')}}', data, settings)
                        .then(response => {
                            this.mostrarProgresoGuardado=false;
                            if (response.data === 1) {
                                window.location.reload(true)
                            } else {
                                alert(response.data)
                            }

                        })
                        .catch(error => {
                            this.mostrarProgresoGuardado=false;
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });
                },
                borrarFichero(tipo_logo){

                    let mensaje = tipo_logo == 'logo_comprobantes'?"¿Está seguro de borrar el logo? Ya no se mostrará en los comprobantes":"¿Confirma borrado del logo?";

                    if(confirm(mensaje)){
                        axios.post('{{action('ConfiguracionController@borrarImagen')}}', {
                            'tipo_logo': tipo_logo,
                        })
                            .then(response => {
                                location.reload(true);
                            })
                            .catch(error => {
                                alert('Ha ocurrido un error al guardar.');
                                console.log(error);
                            });
                    }
                },
                tabChanged(tab){
                    history.pushState("", "", '/configuracion?tab='+tab);
                },
                imprimir(){

                    let img = document.createElement('img');
                    let src = "{{url('/images/logo.png')}}";
                    img.style.width = '78mm';
                    img.id="aaab"
                    document.body.appendChild(img);

                    let popup = window.open('','', 'height=400,width=600');
                    popup.document.write('<html><head><title>' + document.title  + '</title>');
                    popup.document.write('</head><body style="width: 78mm;">');
                    popup.document.write('<img src="{{url('/images/logo.png')}}" style="width:78mm">');
                    popup.document.write('</body></html>');
                    popup.document.close();
                    popup.onload = function() {
                        setTimeout(function () {
                            popup.focus();
                            popup.print();
                        }, 0);
                    };
                },
            },
        });
    </script>
@endsection