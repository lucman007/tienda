<template>
    <div>
        <b-modal id="modal-nuevo-proveedor" ref="modal-nuevo-proveedor" size="lg"
                 title="" @ok="agregarCliente" @hidden="resetModal">
            <template slot="modal-title">
                {{tituloModal}}
            </template>
            <div class="container">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label for="codigo">Código:</label>
                            <input autocomplete="off" type="text" v-model="codigo" name="codigo" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-9">
                        <div class="form-group">
                            <label for="nombre">*Razón social:</label>
                            <input autocomplete="off" type="text" v-model="nombre" name="nombre"  class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="ruc">Ruc:</label>
                            <input autocomplete="off" type="text" v-model="ruc" name="ruc"  class="form-control" maxlength="11">
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="form-group">
                            <label for="direccion">Dirección:</label>
                            <input autocomplete="off" type="text"  v-model="direccion" name="direccion" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label for="telefono">Teléfono 1:</label>
                            <input autocomplete="off" type="text" v-model="telefono" name="telefono" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label for="telefono_2">Teléfono 2:</label>
                            <input autocomplete="off" type="text" v-model="telefono_2" name="telefono_2" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="correo">Correo:</label>
                            <input autocomplete="off" type="text"  v-model="correo" name="correo" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="web">Sitio web:</label>
                            <input autocomplete="off" type="text" v-model="web" name="web" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="contacto">Persona de contacto:</label>
                            <input autocomplete="off" type="text" v-model="contacto" name="contacto" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="observaciones">Observaciones:</label>
                            <textarea v-model="observaciones" class="form-control" name="observaciones" id="" cols="15" rows="3"></textarea>
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
        props:[
            'url_guardar'
        ],
        data() {
            return {
                errorDatosProveedor: [],
                errorProveedor: 0,
                tituloModal:'Agregar proveedor',
                accion:'insertar',
                idproveedor: -1,
                codigo: '',
                nombre: '',
                ruc:'',
                direccion: '',
                telefono:'',
                telefono_2:'',
                correo: '',
                web:'',
                contacto:'',
                observaciones:'',
            }
        },
        methods: {
            agregarCliente(e){
                if (this.validarProveedor()) {
                    e.preventDefault();
                    return;
                }

                let _this=this;
                let nombre=this.nombre;
                let dataset={
                    'idproveedor': this.idproveedor,
                    'codigo': this.codigo,
                    'nombre': this.nombre,
                    'num_documento': this.ruc,
                    'direccion': this.direccion,
                    'telefono': this.telefono,
                    'telefono_2': this.telefono_2,
                    'correo': this.correo,
                    'web': this.web,
                    'contacto': this.contacto,
                    'observaciones': this.observaciones
                };

                axios.post(this.url_guardar,dataset)
                    .then(function () {
                        _this.$emit('agregar',nombre);
                        _this.$refs['modal-nuevo-proveedor'].hide();
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
                if(isNaN(this.ruc)) this.errorDatosProveedor.push('*El campo RUC debe contener un número');
                if(this.ruc!=null){
                    if(this.ruc.length>11) this.errorDatosProveedor.push('*El campo RUC ha excedido la cantidad de dígitos');
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
                this.ruc='';
                this.direccion= '';
                this.telefono='';
                this.telefono_2='';
                this.correo= '';
                this.web='';
                this.contacto='';
                this.observaciones='';
            }
        }
    }
</script>