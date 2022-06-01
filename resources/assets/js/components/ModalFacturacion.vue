<template>
    <div>
        <b-modal id="modal-facturar" ref="modal-facturar" size="xl" @hidden="resetModalFacturar" @shown="init">
            <template slot="modal-title">
                {{tituloModal}}
            </template>
            <div class="container">
                <div class="row">
                    <div class="col-lg-3" v-show="comprobante != 30">
                        <div class="form-group">
                            <label>Comprobante</label>
                            <select :disabled="origen=='pedidos'" v-model="comprobante" name="comprobante"
                                    class="custom-select" id="selectComprobante">
                                <option value="03">Boleta</option>
                                <option value="01">Factura</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <label>Tipo de pago</label>
                        <div class="row">
                            <div class="col-lg-4 form-group">
                                <select v-model="tipoPagoContado" class="custom-select">
                                    <option value="1">Efectivo</option>
                                    <option value="3">Tarjeta / depósito</option>
                                    <option value="4">Fraccionado</option>
                                </select>
                            </div>
                            <div v-show="tipoPagoContado==4" class="col-lg-4 form-group">
                                <b-button v-b-modal.modal-pagofraccionado variant="primary"><i
                                        class="fas fa-plus"></i> Editar pago
                                </b-button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-9 mt-2 order-2 order-md-1">
                        <label>Razón social</label> <span v-show="clienteSeleccionado.esNuevo" class="badge badge-success"> NUEVO CLIENTE</span>
                        <input :disabled="disabledCliente" v-model="clienteSeleccionado.nombre_o_razon_social"
                               type="text" class="form-control">
                    </div>
                    <div class="col-lg-3 mt-2 order-1 order-md-2">
                        <label>
                            DNI / RUC
                        </label>
                        <input autocomplete="nope" :disabled="disabledClienteRuc" @keyup="buscarCliente"
                               v-model="query" ref="focusThis" id="buscar-cliente" maxlength="11" type="number"
                               class="form-control">
                        <b-button :disabled="disabledClienteRuc" @click="buscarCliente(null)" variant="primary" class="boton_adjunto">
                            <i v-show="!mostrarProgreso" class="fas fa-search"></i>
                            <span v-show="mostrarProgreso"><b-spinner small label="Loading..."></b-spinner></span>
                        </b-button>
                        <i class="fas fa-times-circle borrarCliente" v-show="disabledClienteRuc"
                           v-on:click="borrarCliente"></i>
                    </div>
                    <div class="col-lg-10 mt-2 order-3">
                        <label>Dirección</label>
                        <input :disabled="disabledClienteDireccion" v-model="clienteSeleccionado.direccion" type="text"
                               class="form-control">
                        <i v-show="clienteSeleccionado.esNuevo" class="fas fa-edit editarCliente"
                           v-on:click="disabledClienteDireccion = false"></i>
                    </div>
                    <div class="col-lg-12">
                        <div v-for="error in errorDatosVenta">
                            <p class="texto-error">{{ error }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <template #modal-footer="{ ok, cancel}">
                <b-button variant="secondary" @click="cancel()">
                    Cancel
                </b-button>
                <b-button :disabled="mostrarSpinner" variant="primary" @click="procesar">
                    <span v-show="mostrarSpinner"><b-spinner small label="Loading..."></b-spinner> Procesando...</span>
                    <span v-show="!mostrarSpinner">Procesar</span>
                </b-button>
            </template>
        </b-modal>
        <!--INICIO MODAL PAGO FRACCIONADO -->
        <b-modal size="md" id="modal-pagofraccionado" ref="modal-pagofraccionado" @ok="">
            <template slot="modal-title">
                Pago fraccionado
            </template>
            <div class="container">
                <div class="row">
                    <div v-for="pago in pago_fraccionado" class="col-lg-12 mb-3">
                        <div class="row">
                            <div class="col-lg-5">
                                <label>Monto</label>
                                <input v-model="pago.monto" type="text" class="form-control">
                            </div>
                            <div class="col-lg-6">
                                <label>Tipo de pago</label>
                                <select disabled v-model="pago.tipo" class="custom-select">
                                    <option value="1">Efectivo</option>
                                    <option value="3">Tarjeta / depósito</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <template #modal-footer="{ ok, cancel}">
                <b-button variant="secondary" @click="cancel()">
                    Listo
                </b-button>
            </template>
        </b-modal>
        <!--FIN MODAL PAGO FRACCIONADO -->
    </div>
</template>
<script>
    export default {
        name: 'modal-facturacion',
        props: ['idventa','idpedido','origen','tipo_doc','items'],
        data() {
            return {
                clienteSeleccionado:{},
                errorVenta:0,
                errorDatosVenta:[],
                mostrarProgreso:false,
                mostrarSpinner:false,
                disabledCliente:true,
                disabledClienteRuc:true,
                disabledClienteDireccion: true,
                comprobante : '',
                tituloModal:'',
                query:'',
                tipoPagoContado: 1,
                pago_fraccionado:[
                    {
                        monto: '0.00',
                        tipo: '1'
                    },
                    {
                        monto: '0.00',
                        tipo: '3'
                    },
                ],
            }
        },
        methods: {
            init(){
                if(this.origen == 'pedidos'){
                    if(this.tipo_doc == '01'){
                        this.tituloModal = 'Generar factura';
                        this.disabledClienteRuc=false;
                    } else if(this.tipo_doc == '03') {
                        this.tituloModal = 'Generar boleta';
                        this.query = '00000000';
                        this.buscarCliente(null);
                    }
                    this.comprobante = this.tipo_doc;
                    this.$nextTick(function () {
                        this.$refs.focusThis.focus()
                    })
                } else if(this.origen == 'ventas'){
                    this.comprobante = '03';
                    this.tituloModal ='Generar comprobante desde ticket';
                    this.query ='00000000';
                    this.buscarCliente();
                }
            },
            buscarCliente(event){
                if(event == null || event.code == 'Enter' || event.code == 'NumpadEnter'){
                    if (this.query.length == 8 || this.query.length == 11) {
                        this.mostrarProgreso = true;
                        this.disabledCliente = true;
                        this.disabledClienteDireccion = true;
                        this.disabledClienteRuc = true;
                        axios.get('/helper/buscar-clientes' + '/' + this.query)
                            .then(response => {
                                this.clienteSeleccionado = response.data;
                                if(response.data.success === false){
                                    alert('No se ha encontrado el cliente, ingrese los datos manualmente');
                                    this.disabledCliente = false;
                                    this.disabledClienteDireccion = false;
                                    this.disabledClienteRuc = false;
                                }
                                this.mostrarProgreso = false;
                            });
                    } else {
                        alert('Asegúrate de colocar la cantidad de dígitos correcta, para DNI 8 dígitos, para RUC 11 dígitos.');
                        this.mostrarProgreso = false;
                    }
                }
            },
            borrarCliente(){
                this.clienteSeleccionado = {};
                this.disabledClienteRuc = false;
                this.query = '';
                this.$nextTick(function () {
                    this.$refs.focusThis.focus()
                })
            },
            procesar(){
                if (this.validarVenta()) {
                    return;
                }
                this.mostrarSpinner = true;
                if(this.clienteSeleccionado.success === false){
                    this.clienteSeleccionado['ruc'] = this.query;
                }
                let url = this.origen == 'pedidos'?'/ventas/facturacion-rapida-alt':'/ventas/facturacion-desde-ticket-alt';

                axios.post(url,{
                    'idventa': this.idventa,
                    'idpedido': this.idpedido,
                    'items':JSON.stringify(this.items),
                    'comprobante':this.comprobante,
                    'tipo_pago_contado':this.tipoPagoContado,
                    'cliente':JSON.stringify(this.clienteSeleccionado),
                    'pago_fraccionado': JSON.stringify(this.pago_fraccionado)
                })
                    .then(response => {
                        let data = response.data;
                        this.mostrarSpinner = false;
                        if(this.origen == 'pedidos'){
                            this.$emit('obtener-mesas');
                        }
                        if (isNaN(data.idventa)) {
                            alert('Ha ocurrido un error al procesar la venta');
                        } else {
                            if(data.idventa == -1){
                                this.errorVenta = 1;
                                this.errorDatosVenta = [];
                                this.errorDatosVenta.push(data.respuesta);
                            } else{
                                if(isNaN(data.file)){
                                    this.enviar_documentos(data.idventa,data.file,'0');
                                    if(this.origen == 'pedidos'){
                                        this.$emit('imprimir',data.file);
                                        this.$emit('limpiar');
                                    } else{
                                        this.$emit('after-save',data);
                                    }
                                    this.$refs['modal-facturar'].hide();
                                }
                            }
                        }
                    })
                    .catch(error => {
                        this.mostrarSpinner = false;
                        alert('Ha ocurrido un error.');
                        console.log(error);
                    });
            },
            enviar_documentos(idventa, nombre_comprobante, doc_relacionado){
                axios.get('/ventas/reenviar' + '/' + idventa + '/' + nombre_comprobante + '/' + doc_relacionado)
                    .then(response => {
                        let mensaje = response.data[0];
                        let titulo;
                        let color;
                        let tiempo;
                        if((mensaje.toLowerCase()).includes('error')){
                            titulo = 'El comprobante contiene errores';
                            color = 'warning';
                            tiempo = 10000;
                            this.$emit('notificaciones');
                        } else if((mensaje.toLowerCase()).includes('rechazado')) {
                            titulo = 'El comprobante ha sido rechazado y no es válido';
                            color = 'danger';
                            tiempo = 10000;
                            this.$emit('notificaciones');
                        } else {
                            titulo = 'Comprobante enviado con éxito';
                            color = 'primary';
                            tiempo = 5000;
                        }

                        this.$bvToast.toast(mensaje, {
                            title: titulo,
                            variant: color,
                            solid: true,
                            autoHideDelay: tiempo,
                        });

                    })
                    .catch(error => {
                        alert('error');
                        console.log(error);
                    });
            },
            validarVenta(){

                this.errorVenta = 0;
                this.errorDatosVenta = [];
                //Validar pagos fraccionados
                if (this.tipoPagoContado == 4) {
                    let suma_pago_fra = 0;
                    for (let pago of this.pago_fraccionado) {
                        suma_pago_fra += Number(pago.monto);
                    }

                    if (suma_pago_fra > this.totalVenta) this.errorDatosVenta.push('*La suma de los pagos fraccionados supera el monto total de la venta');
                    if (suma_pago_fra < this.totalVenta) this.errorDatosVenta.push('*La suma de los pagos fraccionados es inferior al monto total de la venta');
                }

                if (Object.keys(this.clienteSeleccionado).length == 0) this.errorDatosVenta.push('*Debes ingresar un cliente');
                if ('nombre_o_razon_social' in this.clienteSeleccionado && this.clienteSeleccionado['nombre_o_razon_social'].length == 0) this.errorDatosVenta.push('*Debes ingresar un cliente');

                if(this.comprobante == '01'){
                    if (this.clienteSeleccionado['num_documento'] && this.clienteSeleccionado['num_documento'].length != 11) this.errorDatosVenta.push('*Ingrese un RUC válido');
                }
                if (this.comprobante == '03') {
                    if (this.totalVenta >= 700){
                        str = this.clienteSeleccionado['num_documento'];
                        let regex = new RegExp(/(.)\1{7}/);
                        if(regex.test(str)){
                            this.errorDatosVenta.push('*Para boletas mayores a S/.700.00 debe ingresar un DNI válido');
                        }
                    }
                    if (this.clienteSeleccionado['num_documento'] && (this.clienteSeleccionado['num_documento'].length < 8 || this.clienteSeleccionado['num_documento'].length > 11)) this.errorDatosVenta.push('*Ingrese un DNI o RUC válido');
                }

                if (this.errorDatosVenta.length) this.errorVenta = 1;
                return this.errorVenta;
            },
            resetModalFacturar(){
                this.tituloModal = '';
                this.clienteSeleccionado={};
                this.query = '';
                this.disabledCliente = true;
                this.disabledClienteDireccion = true;
                this.disabledClienteRuc = true;
                this.errorVenta=0;
                this.errorDatosVenta=[];
                this.mostrarProgreso=false;
                this.mostrarSpinner=false;
            }
        }
    }
</script>
<style>
    .borrarCliente{
        right: 65px;
    }
    .editarCliente{
        position: absolute;
        top: 28px;
        right: 20px;
        font-size: 18px;
        cursor: pointer;
    }
</style>