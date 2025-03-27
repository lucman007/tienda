<template>
    <div>
        <b-modal id="modal-nuevo-cliente" ref="modal-nuevo-cliente" size="lg"
                 title="" @ok="agregarCliente" @hidden="resetModal">
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
                                    <b-input-group>
                                        <input v-on:keyup="shortcut_buscar" autocomplete="off" type="number" v-model="ruc_buscar" class="form-control" :maxlength="tipo_documento_buscar==6?11:8">
                                        <b-input-group-append>
                                            <b-button @click="buscar_en_sunat" variant="primary">
                                                <span v-show="!spinnerRuc"><i class="fas fa-search"></i></span>
                                                <b-spinner v-show="spinnerRuc" small label="Loading..." ></b-spinner>
                                            </b-button>
                                        </b-input-group-append>
                                    </b-input-group>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="nombre">*Nombre / Razón social:</label>
                                    <input id="nombre" autocomplete="off" type="text" v-model="nombre" name="nombre"  class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>Tipo de documento:</label>
                                    <select v-model="tipo_documento" class="custom-select">
                                        <option value="9">Ninguno</option>
                                        <option value="6">RUC</option>
                                        <option value="1">DNI</option>
                                        <option value="0">NIF</option>
                                        <option value="4">Carnet de extrajería</option>
                                        <option value="7">Pasaporte</option>
                                        <option value="D">Identification Number</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3" v-show="tipo_documento != 9">
                                <div class="form-group">
                                    <label>*N° documento:</label>
                                    <input autocomplete="off" type="text" v-model="num_documento" class="form-control" :maxlength="max_num">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>*Dirección:</label>
                                    <input autocomplete="off" type="text"  v-model="direccion" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>Teléfono:</label>
                                    <input autocomplete="off" type="text"  v-model="telefono" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-9">
                                <div class="form-group">
                                    <label>E-mail:</label>
                                    <input autocomplete="off" type="text" v-model="email" class="form-control">
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
            <div class="col-lg-12 mt-3">
                <div v-for="error in errorDatosCliente">
                    <p class="texto-error">{{ error }}</p>
                </div>
            </div>
        </b-modal>
    </div>
</template>

<script>
    export default {
        name:'agregar-cliente',
        data() {
            return {
                errorDatosCliente: [],
                errorCliente: 0,
                tituloModal:'Agregar cliente',
                accion:'insertar',
                idcliente: -1,
                cod_cliente: "",
                nombre: "",
                num_documento: "",
                tipo_documento:'9',
                direccion: "",
                telefono:"",
                email: "",
                eliminado: 0,
                ruc_buscar:"",
                tipo_doc:'ruc',
                spinnerRuc:false,
                tipo_documento_buscar:6,
                max_num:8,
                cuentas:[],
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
            editarCliente(id){
                this.accion = 'editar';
                this.idcliente = id;
                this.tituloModal='Editar cliente';
                axios.get('/clientes/edit' + '/' + id)
                    .then(response => {
                        let dataCliente = response.data;
                        this.cod_cliente=dataCliente.cod_cliente;
                        this.nombre=dataCliente.nombre;
                        this.idcliente=dataCliente.idcliente;
                        this.num_documento=dataCliente.num_documento;
                        this.tipo_documento=dataCliente.tipo_documento;
                        this.direccion=dataCliente.direccion;
                        this.telefono=dataCliente.telefono;
                        this.email=dataCliente.correo;
                        this.cuentas=dataCliente.cuentas==null ? [] : JSON.parse(dataCliente.cuentas);
                        this.$refs['modal-nuevo-cliente'].show();
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
            agregarCliente(e){
                e.preventDefault();
                if (this.validarCliente()) {
                    return;
                }

                let url = this.accion=='insertar'?'/helper/nuevo-cliente':'/clientes/update';

                axios.post(url, {
                    'idcliente': this.idcliente,
                    'cod_cliente':this.cod_cliente,
                    'nombre': this.nombre,
                    'num_documento': this.num_documento,
                    'tipo_documento':this.tipo_documento,
                    'direccion': this.direccion,
                    'telefono': this.telefono,
                    'email': this.email,
                    'cuentas':JSON.stringify(this.cuentas)
                })
                    .then(response => {
                        if(response.data == '1'){
                            this.$swal({
                                position: 'top',
                                icon: 'warning',
                                title: 'El cliente ya existe en la base de datos',
                                timer: 2000,
                                showConfirmButton: false,
                                toast:true
                            });
                        } else{
                            let obj= response.data;
                            this.$emit('agregar',obj);
                            this.$refs['modal-nuevo-cliente'].hide();
                        }

                    })
                    .catch(error => {
                        this.$swal({
                            position: 'top',
                            icon: 'error',
                            title: error.response.data.mensaje,
                            timer: 2000,
                            showConfirmButton: false,
                            toast:true
                        });
                    });

            },
            validarCliente(){
                this.errorCliente = 0;
                this.errorDatosCliente = [];
                if (this.nombre.length==0) this.errorDatosCliente.push('*Nombre de cliente no puede estar vacio');
                if(this.num_documento.length==0 && this.tipo_documento != 9) this.errorDatosCliente.push('*El campo N° documento no puede estar vacío');
                if(this.direccion.length==0 && this.tipo_documento != 9) this.errorDatosCliente.push('*El campo Dirección no puede estar vacío');
                if(isNaN(this.telefono)) this.errorDatosCliente.push('*El campo telefono debe contener un número');
                if(isNaN(this.num_documento) && !(this.tipo_documento == 0 || this.tipo_documento == 9)) this.errorDatosCliente.push('*El campo N° documento debe contener un número');
                switch(this.tipo_documento){
                    case '1':
                        if(this.num_documento.length!=8) this.errorDatosCliente.push('*El número de documento de identidad no contiene la cantidad de dígitos correctos (8 dígitos)');
                        break;
                    case '6':
                        if(this.num_documento.length!=11) this.errorDatosCliente.push('*El número de RUC no contiene la cantidad de dígitos correctos (RUC 11 dígitos)');
                        break;
                }
                for(let item of this.cuentas){
                    if (item.cuenta.length == 0) this.errorDatosCliente.push('*Las casilla de número de cuenta no puede quedar vacía');
                }
                if (this.errorDatosCliente.length) this.errorCliente = 1;
                return this.errorCliente;
            },
            buscar_num_doc(num_doc, comprobante){
                if(!isNaN(num_doc)) {
                    this.ruc_buscar = num_doc;
                }
                this.tipo_documento_buscar = comprobante =='01'?'6':'1';
                this.$refs['modal-nuevo-cliente'].show();
            },
            resetModal(){
                this.errorDatosCliente=[];
                this.errorCliente= 0;
                this.tituloModal='Agregar cliente';
                this.accion='insertar';
                this.idcliente=-1;
                this.cod_cliente= '';
                this.nombre= '';
                this.direccion= '';
                this.num_documento= '';
                this.tipo_documento='9';
                this.telefono='';
                this.eliminado= 0;
                this.email= '';
                this.ruc_buscar='';
                this.tipo_documento_buscar=6;
                this.cuentas=[];
            }
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