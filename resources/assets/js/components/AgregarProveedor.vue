<template>
    <div>
        <b-modal id="modal-nuevo-proveedor" ref="modal-nuevo-proveedor" size="lg"
                 title="" @ok="agregarProveedor" @hidden="resetModal">
            <template slot="modal-title">
                {{tituloModal}}
            </template>
            <div class="container">
                <div class="card" style="box-shadow:none; margin-bottom:20px" v-show="accion=='insertar'">
                    <div class="card-header">
                        Consulta RUC / DNI
                    </div>
                    <div class="card-body row">
                        <div class="col-lg-3">
                            <label>Documento:</label>
                            <select v-model="tipo_documento_buscar" class="custom-select">
                                <option value="6">RUC</option>
                                <option value="1">DNI</option>
                            </select>
                        </div>
                        <div class="col-lg-5 form-group">
                            <label>{{tipo_documento_buscar==6?'Número de RUC:':'Número de DNI:'}}</label>
                            <input v-on:keyup="shortcut_buscar" autocomplete="off" type="number" v-model="ruc_buscar" class="form-control" :maxlength="tipo_documento_buscar==6?11:8">
                            <b-button @click="buscar_en_sunat" variant="primary" class="boton_adjunto">
                                <span v-show="!spinnerRuc"><i class="fas fa-search"></i></span>
                                <b-spinner v-show="spinnerRuc" small label="Loading..." ></b-spinner>
                            </b-button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="nombre">*Nombre / Razón social:</label>
                            <input id="nombre" autocomplete="off" type="text" v-model="nombre" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Tipo de documento:</label>
                            <select v-model="tipo_documento" class="custom-select">
                                <option value="6">RUC</option>
                                <option value="1">DNI</option>
                                <option value="0">NIF</option>
                                <option value="4">Carnet de extrajería</option>
                                <option value="7">Pasaporte</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>*N° documento:</label>
                            <input autocomplete="off" type="text" v-model="num_documento" class="form-control" :maxlength="max_num">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="direccion">*Dirección:</label>
                            <input autocomplete="off" type="text"  v-model="direccion" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label for="telefono">Teléfono 1:</label>
                            <input autocomplete="off" type="text" v-model="telefono" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label for="telefono_2">Teléfono 2:</label>
                            <input autocomplete="off" type="text" v-model="telefono_2" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="correo">Correo:</label>
                            <input autocomplete="off" type="text"  v-model="correo" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="web">Sitio web:</label>
                            <input autocomplete="off" type="text" v-model="web" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="contacto">Persona de contacto:</label>
                            <input autocomplete="off" type="text" v-model="contacto" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div v-for="error in errorDatosProveedor">
                            <p class="texto-error">{{ error }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </b-modal>
    </div>
</template>

<script>
    export default {
        name:'agregar-proveedor',
        data() {
            return {
                errorDatosProveedor: [],
                errorProveedor: 0,
                tituloModal:'Agregar proveedor',
                accion:'insertar',
                idproveedor: -1,
                codigo: '',
                nombre: '',
                num_documento:'',
                direccion: '',
                telefono:'',
                telefono_2:'',
                correo: '',
                web:'',
                contacto:'',
                observaciones:'',
                ruc_buscar:"",
                tipo_doc:'ruc',
                spinnerRuc:false,
                tipo_documento_buscar:6,
                max_num:8,
                tipo_documento:'1',
            }
        },
        methods: {
            editarProveedor(id){
                this.tituloModal='Editar proveedor';
                this.accion='editar';
                this.idproveedor=id;
                axios.get('/proveedores/edit' + '/' + id)
                .then(response => {
                    let datos = response.data;
                    this.codigo= datos.codigo;
                    this.nombre= datos.nombre;
                    this.num_documento=datos.num_documento;
                    this.direccion= datos.direccion;
                    this.telefono=datos.telefono;
                    this.telefono_2=datos.telefono_2;
                    this.correo= datos.correo;
                    this.web=datos.web;
                    this.contacto=datos.contacto;
                    this.observaciones=datos.observaciones;
                    this.$refs['modal-nuevo-proveedor'].show();
                })
                    .catch(function (error) {
                        alert('Ha ocurrido un error.');
                        console.log(error);
                    });

            },
            shortcut_buscar(event){
                switch (event.code){
                    case 'Enter':
                    case 'NumpadEnter':
                        this.buscar_en_sunat();
                        break;
                }
            },
            buscar_en_sunat(){
                if((this.tipo_documento_buscar==6 && this.ruc_buscar.length != 11) || (this.tipo_documento_buscar==6 && isNaN(this.ruc_buscar))){
                    alert('Ingresa un RUC válido');
                    return;
                }
                if((this.tipo_documento_buscar==1 && this.ruc_buscar.length != 8) || (this.tipo_documento_buscar==1 && isNaN(this.ruc_buscar))){
                    alert('Ingresa un DNI válido');
                    return;
                }
                this.spinnerRuc=true;
                axios.post('/helper/buscar-ruc', {
                    'num_doc': this.ruc_buscar,
                    'tipo_doc': this.tipo_documento_buscar,
                })
                    .then(response => {
                        let data=response.data;
                        if(!data || data.length == 0){
                            alert('No se obtuvieron resultados, ingresar manualmente.');
                            let input = document.getElementById("nombre");
                            input.focus();
                        } else {
                            this.num_documento=data.ruc;
                            this.nombre=data.nombre_o_razon_social;
                            this.tipo_documento=this.tipo_documento_buscar;
                            this.direccion=data.direccion;

                        }
                        this.spinnerRuc=false;
                    })
                    .catch(error => {
                        this.spinnerRuc=false;
                        alert('Ha ocurrido un error.');
                        console.log(error);
                    });
            },
            agregarProveedor(e){
                if (this.validarProveedor()) {
                    e.preventDefault();
                    return;
                }

                let dataset={
                    'idproveedor': this.idproveedor,
                    'codigo': this.codigo,
                    'nombre': this.nombre,
                    'num_documento': this.num_documento,
                    'direccion': this.direccion,
                    'telefono': this.telefono,
                    'telefono_2': this.telefono_2,
                    'correo': this.correo,
                    'web': this.web,
                    'contacto': this.contacto,
                    'observaciones': this.observaciones
                };

                let tipo_accion=this.accion=='insertar'?'/proveedores/store':'/proveedores/update';

                axios.post(tipo_accion, dataset)
                    .then(response => {
                        if(response.data == '1'){
                            alert('El cliente ya existe en la base de datos');
                        } else{
                            let obj= response.data;
                            this.$emit('agregar',obj);
                            this.$refs['modal-nuevo-proveedor'].hide();
                        }
                    })
                    .catch(function (error) {
                        alert('Ha ocurrido un error.');
                        console.log(error);
                    });

            },
            validarProveedor(){
                this.errorProveedor = 0;
                this.errorDatosProveedor = [];
                if (this.nombre.length==0) this.errorDatosProveedor.push('*Nombre de proveedor no puede estar vacio');
                if(isNaN(this.telefono)) this.errorDatosProveedor.push('*El campo telefono debe contener un número');
                if(isNaN(this.telefono_2)) this.errorDatosProveedor.push('*El campo telefono 2 debe contener un número');
                if(isNaN(this.num_documento)) this.errorDatosProveedor.push('*El campo RUC debe contener un número');
                if(this.num_documento!=null){
                    if(this.num_documento.length>11) this.errorDatosProveedor.push('*El campo RUC ha excedido la cantidad de dígitos');
                }
                if (this.errorDatosProveedor.length) this.errorProveedor = 1;
                return this.errorProveedor;
            },
            resetModal(){
                this.errorDatosProveedor=[];
                this.errorProveedor= 0;
                this.tituloModal='Agregar proveedor';
                this.accion='insertar';
                this.idproveedor= -1;
                this.codigo= '';
                this.nombre= '';
                this.num_documento='';
                this.direccion= '';
                this.telefono='';
                this.telefono_2='';
                this.correo= '';
                this.web='';
                this.contacto='';
                this.observaciones='';
                this.ruc_buscar="";
                this.tipo_doc='ruc';
                this.spinnerRuc=false;
                this.tipo_documento_buscar=6;
                this.max_num=8;
                this.tipo_documento='1';
            },
        },
        watch:{
            tipo_documento(val){
                switch(val){
                    case '1':
                        this.max_num = 8;
                        break;
                    case '4':
                        this.max_num = 12;
                        break;
                    case '6':
                        this.max_num = 11;
                        break;
                    case '7':
                        this.max_num = 12;
                        break;
                    default:
                        this.max_num = 30;
                }
            }
        }
    }
</script>