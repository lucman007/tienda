<template>
    <div class="autocomplete-component" id="autocomplete-credito-id">
        <b-modal id="credito-rapido" ref="credito-rapido" size="md" @show="init" @hidden="limpiar">
            <template slot="modal-title">
                Crédito rápido
            </template>
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <b-input-group>
                            <b-input-group-prepend>
                                <b-input-group-text>
                                    <i class="fas fa-user"></i>
                                </b-input-group-text>
                            </b-input-group-prepend>
                            <input autocomplete="off" type="text" id="buscador-cliente" onclick="this.select()" @click="autoComplete" :disabled="disabledBuscador"
                                   placeholder="Buscar cliente..." v-model="query" v-on:keyup="navigate"
                                   class="form-control"/>
                            <b-input-group-append v-if="register">
                                <b-button @click="registrarCliente" variant="primary"><i class="fas fa-plus"></i> Registrar</b-button>
                            </b-input-group-append>
                        </b-input-group>
                        <i class="fas fa-times-circle borrarCliente" v-show="disabledBuscador" v-on:click="borrarCliente" style="right: 30px;"></i>
                        <div class="panel-footer autocomplete-wrapper autocomplete-wrapper-credito" v-if="results.length">
                            <ul class="list-group">
                                <li v-on:click="agregarCliente(index)" style="cursor:pointer" class="list-group-item d-flex"
                                    v-for="(result,index) in results"
                                    v-bind:class='{"active_item": currentItem === index}'>
                                    <div class="col-lg-8">
                                        {{result.nombre }}
                                    </div>
                                    <div class="col-lg">
                                        {{result.num_documento }}
                                    </div>
                                </li>
                            </ul>

                        </div>
                    </div>
                    <div class="col-lg-6 mt-2">
                        <b-input-group>
                            <b-input-group-prepend>
                                <b-input-group-text>
                                    <i class="fas fa-dollar-sign"></i>
                                </b-input-group-text>
                            </b-input-group-prepend>
                            <input v-model="acuenta" type="number"
                                   class="form-control" placeholder="A cuenta">
                        </b-input-group>
                    </div>
                    <div class="col-lg-12 mt-2">
                        <textarea placeholder="Observación" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-lg-12 my-3">
                        <p>Un crédito rápido o fiado se guardará con fecha vencimiento a 30 días. Puedes gestionarlo en módulo créditos.</p>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-lg-12">
                        <div v-for="error in errorDatosVenta">
                            <p class="texto-error">{{ error }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <template #modal-footer="{ ok, cancel}">
                <b-button variant="secondary" @click="cancel()">Cancel</b-button>
                <b-button variant="primary" @click="procesar">Guardar</b-button>
            </template>
        </b-modal>
    </div>
</template>

<script>
    export default {
        name: 'credito-rapido',
        props: ['idpedido','items','total'],
        data() {
            return {
                currentItem: 0,
                results: [],
                clienteSeleccionado:{},
                query:'',
                acuenta:'',
                mostrarSpinner:false,
                disabledBuscador:false,
                register:false,
                errorVenta:0,
                errorDatosVenta:[],
                cuotas:[]
            }
        },
        created() {
            window.addEventListener('click', e => {
                if (!document.getElementById('autocomplete-credito-id').contains(e.target)){
                    this.results = [];
                }
            })
        },
        methods: {
            limpiar(){
                this.disabledBuscador=false;
                this.register=false;
                this.query = '';
                this.acuenta = '';
                this.errorVenta = 0;
                this.errorDatosVenta = [];
            },
            init(){
                setTimeout(() => {
                    document.getElementById('buscador-cliente').focus();
                }, 50);
            },
            registrarCliente(){
                let randomNumbers = Math.floor(100000 + Math.random() * 900000);
                let uniqueCode = `CL${randomNumbers}`;
                this.clienteSeleccionado.nombre_o_razon_social = this.query;
                this.clienteSeleccionado.ruc = uniqueCode;
                this.clienteSeleccionado.tipo_doc = 9;
                this.clienteSeleccionado.direccion = '-';
                this.clienteSeleccionado.esNuevo = true;
                this.disabledBuscador = true;
                this.register = false;
            },
            navigate(event){
                switch (event.code) {
                    case 'ArrowUp':
                        if (this.currentItem > 0) {
                            this.currentItem--;
                        }
                        break;
                    case 'ArrowDown':
                        if (this.currentItem < (this.results.length - 1)) {
                            this.currentItem++;
                        }
                        break;
                    case 'Escape':
                        this.results = [];
                        this.query = '';
                        break;
                    case 'Enter':
                    case 'NumpadEnter':
                        if (this.results.length > 0) {
                            this.$emit('agregar_cliente', this.results[this.currentItem]);
                            this.disabledBuscador = true;
                            let seleccionado = this.results[this.currentItem];
                            this.query = seleccionado['num_documento']+' - '+seleccionado['nombre'];
                            this.results = [];
                        }
                        break;
                    default:
                        this.currentItem = 0;
                        if (this.timer) {
                            clearTimeout(this.timer);
                            this.timer = null;
                        }
                        this.timer = setTimeout(() => {
                            this.autoComplete();
                        }, 400);
                        if(!this.query){
                            this.register = false;
                        }
                }

            },
            autoComplete(){
                this.results = [];
                let url = "/helper/obtener-clientes" + "/";
                if (this.es_proveedores) {
                    url = "/helper/obtener-proveedores" + "/";
                }
                if (this.query.length > 1) {
                    axios.get(url + this.query).then((response) => {
                        this.results = response.data;
                    });

                } else if(this.query.length == 0){
                    axios.get(url + '').then((response) => {
                        this.results = response.data;
                    });
                }
                if(this.query){
                    this.register = true;
                }
            },
            agregarCliente(index){
                this.$emit('agregar_cliente',  this.results[index]);
                this.currentItem = index;
                this.disabledBuscador = true;
                let seleccionado = this.results[index];
                this.query = seleccionado['num_documento']+' - '+seleccionado['nombre'];
                this.results = [];
                this.register = false;
                this.clienteSeleccionado.esNuevo = false;
                this.clienteSeleccionado.idcliente = seleccionado['idcliente'];
            },
            obtenerFechaActual() {
                const hoy = new Date();
                const anio = hoy.getFullYear();
                const mes = String(hoy.getMonth() + 1).padStart(2, '0');
                const dia = String(hoy.getDate()).padStart(2, '0');
                return `${anio}-${mes}-${dia}`;
            },
            obtenerFechaMas30Dias() {
                const hoy = new Date();
                const treintaDiasEnMilisegundos = 30 * 24 * 60 * 60 * 1000;
                const fechaMasTreintaDias = new Date(hoy.getTime() + treintaDiasEnMilisegundos);
                const anio = fechaMasTreintaDias.getFullYear();
                const mes = String(fechaMasTreintaDias.getMonth() + 1).padStart(2, '0');
                const dia = String(fechaMasTreintaDias.getDate()).padStart(2, '0');
                return `${anio}-${mes}-${dia}`;
            },
            validar(){
                this.errorVenta = 0;
                this.errorDatosVenta = [];
                if(!(this.query && this.disabledBuscador)){
                    this.errorDatosVenta.push('*Debes ingresar un cliente.');
                    this.errorDatosVenta.push('*Si el cliente es nuevo, primero presiona REGISTRAR y luego procede a guardar la venta.');
                }

                if(this.acuenta){
                    if(Number(this.acuenta) > Number(this.total)){
                        this.errorDatosVenta.push('*El monto dejado a cuenta es mayor que el total de la venta.');
                    }
                }

                if (this.errorDatosVenta.length) this.errorVenta = 1;
                return this.errorVenta;
            },
            procesar(){

                if (this.validar()) {
                    return;
                }

                if(this.acuenta){
                    let fechaPago = this.obtenerFechaMas30Dias();
                    let fechaHoy = this.obtenerFechaActual();
                    let detallePago = '[{"fecha":"'+fechaHoy+'","metodo_pago":1,"num_operacion":"","monto":'+this.acuenta+'}]';
                    let estadoPago = 1;
                    if(Number(this.acuenta) >= Number(this.total)){
                        estadoPago = 2;
                    }
                    this.cuotas = [
                        {monto:this.total,fecha:fechaPago,detalle:detallePago,estado:estadoPago}
                    ]
                }

                this.mostrarSpinner = true;

                axios.post('/ventas/facturacion-rapida-alt',{
                    'idventa': null,
                    'idpedido': this.idpedido,
                    'items':JSON.stringify(this.items),
                    'comprobante':30,
                    'tipo_pago_contado':2,
                    'cliente':JSON.stringify(this.clienteSeleccionado),
                    'num_operacion':'',
                    'pago_fraccionado': null,
                    'cuotas': JSON.stringify(this.cuotas)
                })
                    .then(response => {
                        let data = response.data;
                        this.mostrarSpinner = false;
                        this.$emit('obtener-mesas');
                        if (isNaN(data.idventa)) {
                            alert('Ha ocurrido un error al procesar la venta');
                        } else {
                            if(data.idventa == -1){
                                this.errorVenta = 1;
                                this.errorDatosVenta = [];
                                this.errorDatosVenta.push(data.respuesta);
                            } else{
                                this.$emit('limpiar');
                                this.$swal({
                                    position: 'top',
                                    icon: 'success',
                                    title: 'Crédito guardado correctamente',
                                    timer: 2000,
                                    showConfirmButton: false,
                                    toast:true
                                }).then(() => {
                                    this.$refs['credito-rapido'].hide();
                                });
                            }
                        }
                    })
                    .catch(error => {
                        this.mostrarSpinner = false;
                        alert('Ha ocurrido un error.');
                        console.log(error);
                    });
            },
            borrarCliente(){
                this.$emit('borrar_cliente');
                this.disabledBuscador = false;
                this.query = '';
                document.getElementById("buscador-cliente").focus();
            }
        }
    }
</script>
<style>
    .autocomplete-wrapper-credito{
        width: 95%;
    }
</style>