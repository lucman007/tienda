<template>
    <div>
        <b-modal id="modal-nuevo-proveedor" ref="modal-nuevo-proveedor" size="lg"
                 title="" @ok="agregarProveedor" @hidden="resetModal">
            <template slot="modal-title">
                {{tituloModal}}
            </template>
            <b-card no-body class="no-shadow">
                <b-tabs card>
                    <b-tab title="General" active>
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
                            <div class="col-lg-7">
                                <div class="form-group">
                                    <label for="nombre">*Nombre / Razón social:</label>
                                    <input id="nombre" autocomplete="off" type="text" v-model="nombre" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label>*Doc:</label>
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
                                    <label>Dirección:</label>
                                    <input autocomplete="off" type="text"  v-model="direccion" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>Teléfono 1:</label>
                                    <input autocomplete="off" type="text" v-model="telefono" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>Teléfono 2:</label>
                                    <input autocomplete="off" type="text" v-model="telefono_2" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="form-group">
                                    <label>Correo:</label>
                                    <input autocomplete="off" type="text"  v-model="correo" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Sitio web:</label>
                                    <input autocomplete="off" type="text" v-model="web" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>Contacto:</label>
                                    <input autocomplete="off" type="text" v-model="contacto" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Descripción:</label>
                                    <textarea v-model="observacion" rows="1" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                    </b-tab>
                    <b-tab title="Bancos">
                        <div class="row">
                            <div class="col-lg-4 mb-3">
                                <button @click="agregarCuenta" class="btn btn-primary"><i class="fas fa-plus"></i> Agregar cuenta
                                </button>
                            </div>
                            <div class="col-lg-12">
                                <div class="row">
                                    <div class="col-lg-12" v-for="(item,index) in cuentas" :key="index">
                                        <div class="row">
                                            <div class="col-lg-3 form-group">
                                                <label>Banco:</label>
                                                <select v-model="item.banco" class="custom-select">
                                                    <option value="BCP">BCP</option>
                                                    <option value="BBVA">BBVA</option>
                                                    <option value="INTERBANK">INTERBANK</option>
                                                    <option value="SCOTIABANK">SCOTIABANK</option>
                                                    <option value="PICHINCHA">PICHINCHA</option>
                                                    <option value="BANBIF">BANBIF</option>
                                                    <option value="BANCO DE LA NACIÓN">BANCO DE LA NACIÓN</option>
                                                    <option value="YAPE">YAPE</option>
                                                    <option value="PLIN">PLIN</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-2 form-group">
                                                <label>Moneda:</label>
                                                <select v-model="item.moneda" class="custom-select">
                                                    <option value="S/">S/</option>
                                                    <option value="USD">USD</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label>N° de cuenta:</label>
                                                <input class="form-control" v-model="item.cuenta" type="text"
                                                       placeholder="Cuenta">
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label>CCI:</label>
                                                <input class="form-control" v-model="item.cci" type="text"
                                                       placeholder="CCI">
                                            </div>
                                            <div class="col-lg-1">
                                                <button @click="borrarCuenta(index)" style="margin-top: 20px"
                                                        class="btn btn-danger"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </b-tab>
                </b-tabs>
            </b-card>
            <div class="col-lg-12">
                <div v-for="error in errorDatosProveedor">
                    <p class="texto-error">{{ error }}</p>
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
                ruc_buscar:"",
                tipo_doc:'ruc',
                spinnerRuc:false,
                tipo_documento_buscar:6,
                max_num:8,
                tipo_documento:'1',
                cuentas:[],
                observacion:''
            }
        },
        methods: {
            agregarCuenta(){
                this.cuentas.push({
                    banco: 'BCP',
                    moneda: 'S/',
                    cuenta: '',
                    cci: '',
                });
            },
            borrarCuenta(index){
                this.cuentas.splice(index,1);
            },
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
                    this.tipo_documento = datos.tipo_documento;
                    this.contacto=datos.contacto;
                    this.cuentas=datos.cuentas==null ? [] : JSON.parse(datos.cuentas);
                    this.observacion = datos.observaciones;
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
                    'tipo_documento': this.tipo_documento,
                    'direccion': this.direccion,
                    'telefono': this.telefono,
                    'telefono_2': this.telefono_2,
                    'correo': this.correo,
                    'web': this.web,
                    'contacto': this.contacto,
                    'cuentas':JSON.stringify(this.cuentas),
                    'observacion': this.observacion
                };

                let tipo_accion=this.accion=='insertar'?'/proveedores/store':'/proveedores/update';

                axios.post(tipo_accion, dataset)
                    .then(response => {
                        if(response.data == '1'){
                            alert('El cliente ya existe en la base de datos');
                        } else{
                            this.$emit('agregar',response.data);
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
                if(!this.num_documento) this.errorDatosProveedor.push('*El campo número documento no puede quedar vacío');
                if(isNaN(this.num_documento)) this.errorDatosProveedor.push('*El campo RUC debe contener un número');
                if(this.num_documento!=null){
                    if(this.num_documento.length>11) this.errorDatosProveedor.push('*El campo RUC ha excedido la cantidad de dígitos');
                }

                for(let item of this.cuentas){
                    if (item.cuenta.length == 0) this.errorDatosProveedor.push('*Las casilla de número de cuenta no puede quedar vacía');
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
                this.tipo_documento='1';
                this.direccion= '';
                this.telefono='';
                this.telefono_2='';
                this.correo= '';
                this.web='';
                this.contacto='';
                this.ruc_buscar="";
                this.tipo_doc='ruc';
                this.spinnerRuc=false;
                this.tipo_documento_buscar=6;
                this.max_num=8;
                this.tipo_documento='1';
                this.cuentas=[];
                this.observacion = '';
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