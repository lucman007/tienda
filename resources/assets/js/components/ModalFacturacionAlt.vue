<template>
    <div>
        <b-modal id="modal-facturar-alt" ref="modal-facturar-alt" size="lg" @hidden="resetModal" @show="modalClienteShow">
            <template slot="modal-title">
                Facturar
            </template>
            <div class="container">
                <div class="row">
                    <div class="col-lg-3" v-show="comprobante != 30">
                        <div class="form-group">
                            <label>Comprobante</label>
                            <select v-model="comprobante" name="comprobante"
                                    class="custom-select" id="selectComprobante">
                                <option value="03">Boleta</option>
                                <option value="01">Factura</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-9">
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
                                <b-button @click="abrir_modal('fraccionado')" variant="primary"><i
                                        class="fas fa-plus"></i> Editar pago
                                </b-button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <autocomplete-cliente v-on:agregar_cliente="agregarCliente"
                                              v-on:borrar_cliente="borrarCliente"
                                              ref="suggestCliente"></autocomplete-cliente>
                    </div>
                    <div class="col-lg-4">
                        <b-button @click="abrir_modal('nuevoCliente')"
                                  class="mb-4" variant="primary"><i class="fas fa-plus"></i>
                            Nuevo cliente
                        </b-button>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div v-for="error in errorDatosVenta">
                        <p class="texto-error">@{{ error }}</p>
                    </div>
                </div>
            </div>
            <template #modal-footer="{ ok, cancel}">
                <b-button variant="secondary" @click="cancel()">
                    Cancel
                </b-button>
                <b-button :disabled="mostrarProgresoGuardado" variant="primary" @click="facturar_ticket">
                    <span v-show="mostrarProgresoGuardado"><b-spinner small label="Loading..."></b-spinner> Procesando...</span>
                    <span v-show="!mostrarProgresoGuardado">@{{ comprobante == 30 ? 'Generar' : 'Facturar' }}</span>
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
<agregar-cliente
        ref="agregarNuevoCliente"
        v-on:agregar="agregarClienteNuevo">
</agregar-cliente>
<script>
    import AgregarCliente from './AgregarCliente.vue'
    export default {
        name: 'modal-facturacion-alt',
        props: ['item'],
        components: {AgregarCliente},
        data() {
            return {
                clienteSeleccionado:{},
                errorVenta:0,
                errorDatosVenta:[],
                mostrarProgreso:false,
                mostrarSpinner:false,
                disabledCliente:true,
                disabledClienteRuc:true,
                comprobante : '03',
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
                totalVenta:0,
                mostrarProgresoGuardado: false,
            }
        },
        methods: {
            modalClienteShow(){
                axios.get('/helper/agregar-cliente'+'/'+this.item.idcliente)
                    .then(response => {
                        this.$refs['suggestCliente'].agregarCliente(response.data);
                        this.totalVenta = this.item.total_venta;
                        this.idventa = this.item.idventa;
                    })
                    .catch(function (error) {
                        alert('Ha ocurrido un error.');
                        console.log(error);
                    })
            },
            abrir_modal(nombre){
                switch (nombre) {
                    case 'nuevoCliente':
                        let num_doc = this.$refs['suggestCliente'].query;
                        this.$refs['agregarNuevoCliente'].buscar_num_doc(num_doc, this.comprobante);
                        break;
                }
            },
            agregarCliente(obj){
                this.clienteSeleccionado = obj;
            },
            agregarClienteNuevo(obj){
                this.$refs['suggestCliente'].agregarCliente(obj);
            },
            borrarCliente(){
                this.clienteSeleccionado = {};
            },
            resetModal(){
                this.tituloModal = 'Facturar';
                this.comprobante = '03';
                this.clienteSeleccionado={};
                this.totalVenta = 0;
                this.idventa = -1;
                this.errorDatosVenta = [];
                this.tipoPagoContado=1;
                this.pago_fraccionado=[
                    {
                        monto: '0.00',
                        tipo: '1'
                    },
                    {
                        monto: '0.00',
                        tipo: '3'
                    },
                ]
            },
            facturar_ticket(){

                if (this.validarVenta()) {
                    return;
                }

                this.mostrarProgresoGuardado = true;
                axios.post('/ventas/facturacion-desde-ticket',{
                    'idventa': this.idventa,
                    'comprobante':this.comprobante,
                    'tipo_pago_contado':this.tipoPagoContado,
                    'idcliente':this.clienteSeleccionado['idcliente'],
                    'pago_fraccionado': JSON.stringify(this.pago_fraccionado)
                })
                    .then(response => {
                        this.mostrarProgresoGuardado = false;
                        if (isNaN(response.data.idventa)) {
                            alert('Ha ocurrido un error al procesar la venta');
                            this.$refs['modal-facturar'].hide();
                        } else {
                            if(response.data.idventa == -1){
                                this.errorVenta = 1;
                                this.errorDatosVenta = [];
                                this.errorDatosVenta.push(response.data.respuesta);
                            } else{
                                this.$refs['modal-facturar'].hide();
                                if(isNaN(response.data.file)){
                                    //this.enviar_documentos(response.data.idventa,response.data.file,'0');
                                }
                                this.$swal({
                                    position: 'center',
                                    icon: 'success',
                                    title: 'Se ha generado el comprobante',
                                    text:response.data.respuesta,
                                    showConfirmButton: true,
                                    timer: 2500,
                                    timerProgressBar: true,
                                }).then((result) => {
                                    let iframe = document.createElement('iframe');
                                    document.body.appendChild(iframe);
                                    iframe.style.display = 'none';
                                    iframe.onload = function() {
                                        setTimeout(function() {
                                            iframe.focus();
                                            iframe.contentWindow.print();
                                        }, 0);
                                    };
                                    iframe.src = '/ventas/imprimir/'+response.data.file;

                                    let comp = (response.data.file).split('-');
                                    document.querySelector('#comp_'+response.data.idventa).innerHTML = comp[2]+'-'+comp[3];
                                    document.querySelector('#btn_imprimir_'+response.data.idventa).classList.remove('disabled');
                                    document.querySelector('#btn_anular_'+response.data.idventa).classList.remove('disabled');
                                    document.querySelector('#btn_facturar_'+response.data.idventa).classList.add('disabled');
                                });
                            }
                        }
                    })
                    .catch(error => {
                        this.mostrarProgresoGuardado = false;
                        alert('Ha ocurrido un error.');
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
                if(this.comprobante == '03' || this.comprobante == '01'){
                    if (Object.keys(this.clienteSeleccionado).length == 0) this.errorDatosVenta.push('*Debes ingresar un cliente');
                }
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

        }
    }
</script>