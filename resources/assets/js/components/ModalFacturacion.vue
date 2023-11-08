<template>
    <div>
        <b-modal id="modal-facturar" ref="modal-facturar" size="xl" @hidden="resetModalFacturar" @shown="init">
            <template slot="modal-title">
                {{tituloModal}}
            </template>
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 mb-2 mb-lg-0">
                        <b-input-group>
                            <b-input-group-prepend>
                                <b-input-group-text>
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </b-input-group-text>
                            </b-input-group-prepend>
                            <select :disabled="origen=='pedidos'" v-model="comprobante" name="comprobante"
                                    class="custom-select" id="selectComprobante">
                                <option value="03">Boleta</option>
                                <option value="01">Factura</option>
                                <option v-show="origen=='pedidos'" value="30">Nota de venta</option>
                            </select>
                        </b-input-group>
                    </div>
                    <div class="col-lg-9">
                        <div class="row">
                            <div class="col-lg-4">
                                <b-input-group>
                                    <b-input-group-prepend>
                                        <b-input-group-text>
                                            <i class="fas fa-dollar-sign"></i>
                                        </b-input-group-text>
                                    </b-input-group-prepend>
                                <select v-model="metodoPago" class="custom-select">
                                    <option v-for="pago in tipo_pago" v-bind:value="pago['num_val']">{{pago['label']}}</option>
                                </select>
                                </b-input-group>
                            </div>
                            <div class="col-lg-8 d-flex align-items-start mt-2 mt-lg-0 mb-lg-0">
                                <b-button class="w-100 w-lg-50 mb-2 mb-lg-0" v-show="metodoPago==4" v-b-modal.modal-pagofraccionado variant="primary"><i
                                        class="fas fa-edit"></i> Editar pago
                                </b-button>
                                <b-button class="w-100 w-lg-50 mb-2 mb-lg-0" v-show="metodoPago==2" @click="abrirCuotas" variant="primary"><i
                                        class="fas fa-plus"></i> Cuotas ({{cuotas.length}})
                                </b-button>
                                <b-input-group v-show="!(metodoPago==1 || metodoPago==4 || metodoPago==2)" class="mb-2 mb-lg-0 w-lg-50">
                                    <b-input-group-prepend>
                                        <b-input-group-text>
                                            <i class="fas fa-check"></i>
                                        </b-input-group-text>
                                    </b-input-group-prepend>
                                    <input v-model="num_operacion" type="text" class="form-control" placeholder="N° operación">
                                </b-input-group>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8 mt-2 mt-lg-2 order-2 order-lg-1">
                        <span v-show="clienteSeleccionado.esNuevo" class="badge badge-success n-cliente"> NUEVO CLIENTE</span>
                        <b-input-group>
                            <b-input-group-prepend>
                                <b-input-group-text>
                                    <i class="fas fa-user"></i>
                                </b-input-group-text>
                            </b-input-group-prepend>
                            <input :disabled="disabledCliente" v-model="clienteSeleccionado.nombre_o_razon_social"
                                   type="text" class="form-control">
                        </b-input-group>
                    </div>
                    <div class="col-lg-4 mt-0 mt-lg-2 order-1 order-lg-2">
                        <b-input-group>
                            <b-input-group-prepend>
                                <b-input-group-text>
                                    <i class="fas fa-id-card"></i>
                                </b-input-group-text>
                            </b-input-group-prepend>
                            <input autocomplete="nope" :disabled="disabledClienteRuc" @keyup="buscarCliente"
                                   v-model="query" id="buscar-cliente" maxlength="11" type="number"
                                   class="form-control">
                            <b-input-group-append>
                                <b-button :disabled="disabledClienteRuc" @click="buscarCliente(null)" variant="primary">
                                    <i v-show="!mostrarProgreso" class="fas fa-search"></i>
                                    <span v-show="mostrarProgreso"><b-spinner small label="Loading..."></b-spinner></span>
                                </b-button>
                            </b-input-group-append>
                        </b-input-group>
                        <i class="fas fa-times-circle borrarCliente" v-show="disabledClienteRuc"
                           v-on:click="borrarCliente"></i>
                    </div>
                    <div class="col-lg-10 mt-2 order-3">
                        <b-input-group>
                            <b-input-group-prepend>
                                <b-input-group-text>
                                    <i class="fas fa-home"></i>
                                </b-input-group-text>
                            </b-input-group-prepend>
                            <input :disabled="disabledClienteDireccion" v-model="clienteSeleccionado.direccion" type="text"
                                   class="form-control">
                            <b-input-group-append>
                                <b-button :disabled="!clienteSeleccionado.esNuevo" v-on:click="disabledClienteDireccion = false" variant="primary">
                                    <i class="fas fa-edit"></i>
                                </b-button>
                            </b-input-group-append>
                        </b-input-group>
                    </div>
                </div>
                <div class="row mb-2 mt-4">
                    <div class="col-lg-12">
                        <div class="card no-shadow">
                            <div class="card-body div-calculadora">
                                <span class="badge badge-secondary calc">CALCULADORA</span>
                                <div class="row">
                                    <div class="col-lg-9" style="display: inline-table">
                                        <p class="mb-1">Selecciona un billete o digita para calcular el vuelto:</p>
                                        <div @click="calcularVuelto(1)" class="d-inline-flex billete mt-1 mt-md-0">
                                            S/ 1
                                        </div>
                                        <div @click="calcularVuelto(5)" class="d-inline-flex billete mt-1 mt-md-0">
                                            S/ 5
                                        </div>
                                        <div @click="calcularVuelto(10)" class="d-inline-flex billete mt-1 mt-md-0">
                                            S/ 10
                                        </div>
                                        <div @click="calcularVuelto(20)" class="d-inline-flex billete mt-1 mt-md-0">
                                            S/ 20
                                        </div>
                                        <div @click="calcularVuelto(50)" class="d-inline-flex billete mt-1 mt-md-0">
                                            S/ 50
                                        </div>
                                        <div @click="calcularVuelto(100)" class="d-inline-flex billete mt-1 mt-md-0">
                                            S/ 100
                                        </div>
                                        <div @click="calcularVuelto(200)" class="d-inline-flex billete mt-1 mt-md-0">
                                            S/ 200
                                        </div>
                                        <div class="d-inline-flex billete mt-1 mt-md-0" @click="calcularVuelto(porPagar)">
                                            Exacto
                                        </div>
                                        <div class="d-inline-flex mt-1 mt-md-2 mt-lg-0">
                                            <input onfocus="this.select()" type="number" class="form-control" placeholder="Digita el monto" v-model="pagaCon" @keyup="calcularVuelto(null)">
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <table class="tabla_vuelto float-left float-md-right">
                                            <tr>
                                                <td style="width: 120px">Total:</td>
                                                <td>{{Number(porPagar).toFixed(2)}}</td>
                                            </tr>
                                            <tr>
                                                <td>Paga con:</td>
                                                <td>{{pagaCon==''?'0.00':Number(pagaCon).toFixed(2)}}</td>
                                            </tr>
                                            <tr>
                                                <td :style="vuelto<0?'color:#f14040':'color:#2b9505'"><strong>{{vuelto<0?'Faltan:':'Vuelto:'}}</strong></td>
                                                <td :style="vuelto<0?'color:#f14040':'color:#2b9505'"><strong>{{Number(vuelto).toFixed(2)}}</strong></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                <b-form-checkbox v-model="imprimir" switch size="lg" class="float-right">
                    <p style="font-size: 1rem;">Imprimir</p>
                </b-form-checkbox>
                <b-button variant="secondary" @click="cancel()">
                    Cancelar
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
                    <div v-for="pago,index in pago_fraccionado" class="col-lg-12 mb-3">
                        <div class="row">
                            <div class="col-lg-4">
                                <label>Monto</label>
                                <input v-model="pago.monto" type="number" class="form-control" onfocus="this.select()">
                            </div>
                            <div class="col-lg-6">
                                <label>Tipo de pago</label>
                                <select v-model="pago.tipo" class="custom-select">
                                    <option v-show="!(pago['num_val'] == 4 || pago['num_val'] == 2)" v-for="pago in tipo_pago" v-bind:value="pago['num_val']">{{pago['label']}}</option>
                                </select>
                            </div>
                            <div class="col-lg-2" v-show="index > 1">
                                <button @click="borrarFraccionado(index)" style="margin-top: 20px" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <b-button variant="primary" @click="agregarFraccionado"><i
                                class="fas fa-plus"></i>
                        </b-button>
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
        <!--INICIO MODAL CUOTAS -->
        <b-modal size="md" id="modal-tipopago" ref="modal-tipopago" @ok="">
            <template slot="modal-title">
                Pago a crédito
            </template>
            <div class="container">
                <div class="row">
                    <div v-for="(cuota,index) in cuotasAux" class="col-lg-12 mb-3" :key="index">
                        <div class="row">
                            <div class="col-lg-4">
                                <label>Cuota {{ index + 1 }} S/</label>
                                <input v-model="cuota.monto" type="text" class="form-control" onfocus="this.select()">
                            </div>
                            <div class="col-lg-6">
                                <label>Fecha de pago:</label>
                                <input :min="fecha" type="date" v-model="cuota.fecha" name="fechaCuota"
                                       class="form-control">
                            </div>
                            <div class="col-lg-2" v-show="index > 0">
                                <b-button variant="danger" style="margin-top: 20px" @click="borrarCuota(index)"><i class="fas fa-trash"></i></b-button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12 mb-4">
                        <button @click="agregarCuota(null)" class="btn btn-info"><i class="fas fa-plus"></i> Agregar cuota
                        </button>
                    </div>
                </div>
            </div>
            <template #modal-footer="{ ok, cancel}">
                <b-button variant="secondary" @click="cancelarCuotas()">
                    Cancel
                </b-button>
                <b-button variant="primary" @click="agregarCuotasVenta">
                    OK
                </b-button>
            </template>
        </b-modal>
        <!--FIN MODAL CUOTAS -->
    </div>
</template>
<script>
    export default {
        name: 'modal-facturacion',
        props: ['idventa','idpedido','origen','tipo_doc','items','total','tipo_de_pago','fecha'],
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
                metodoPago: 1,
                imprimir:true,
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
                tipo_pago:[],
                num_operacion:'',
                cuotas:[],
                cuotasAux:[],
                pagaCon:'',
                vuelto:'0.00',
                productos:[],
                total_venta:0,
                porPagar:0,
                idorden: -1,
                idorden_origen: -1,
                dividir:0,
                propina:'',
            }
        },
        methods: {
            mostrarModal(){
              this.$refs['modal-facturar'].show()
            },
            agregarFraccionado(){
                this.pago_fraccionado.push({monto: '0.00', tipo: '1'});
            },
            borrarFraccionado(index){
                this.pago_fraccionado.splice(index,1);
            },
            init(){
                if(this.origen == 'pedidos'){
                    if(this.tipo_doc == '01'){
                        this.tituloModal = 'Generar factura';
                        this.disabledClienteRuc=false;
                    } else if(this.tipo_doc == '03') {
                        this.tituloModal = 'Generar boleta';
                        this.query = '00000000';
                        this.buscarCliente(null);
                    } else if(this.tipo_doc == '30') {
                        this.tituloModal = 'Generar nota de venta';
                        this.query = '00000000';
                        this.buscarCliente(null);
                    }
                    this.comprobante = this.tipo_doc;
                    setTimeout(() => {
                        document.getElementById('buscar-cliente').focus();
                    }, 50);
                } else if(this.origen == 'ventas'){
                    this.comprobante = '03';
                    this.tituloModal ='Generar comprobante desde ticket';
                    this.query ='00000000';
                    this.buscarCliente();
                }
                this.tipo_pago = this.tipo_de_pago;
                this.total_venta = this.total;
                this.porPagar = this.total;
                this.pagaCon = this.total;
            },
            abrirCuotas(){
                this.$refs['modal-tipopago'].show();
                this.cuotasAux = Object.assign([], this.cuotas);
                this.agregarCuota(this.total);
            },
            agregarCuota(total){
                let monto = '0.00';
                if (this.cuotasAux.length > 0 && total) {
                    return;
                } else {
                    if (total) {
                        monto = Number(total).toFixed(2);
                    }
                }
                this.cuotasAux.push({
                    monto: monto,
                    fecha: this.fecha
                });
            },
            borrarCuota(index){
                this.cuotasAux.splice(index, 1);
            },
            agregarCuotasVenta(){

                for (let cuota of this.cuotasAux) {
                    if ((Number(cuota.monto)) <= 0) {
                        alert('Solo se admiten casillas con cuotas mayor a 0.00');
                        return;
                    }
                    if (!cuota.fecha) {
                        alert('Una de las fechas de pago no tiene el formato correcto');
                        return;
                    }
                    if(cuota.fecha < this.fecha){
                        alert('Las fechas de las cuotas deben ser mayor a la fecha actual');
                        return;
                    }
                }

                this.cuotas = Object.assign([], this.cuotasAux);
                this.$refs['modal-tipopago'].hide();

            },
            cancelarCuotas(){
                this.cuotasAux = [];
                this.$refs['modal-tipopago'].hide();
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
                                    this.alerta('No se ha encontrado el cliente, ingrese los datos manualmente','info');
                                    this.disabledCliente = false;
                                    this.disabledClienteDireccion = false;
                                    this.disabledClienteRuc = false;
                                }
                                this.mostrarProgreso = false;
                            });
                    } else {
                        this.alerta('Asegúrate de colocar la cantidad de dígitos correcta, para DNI 8 dígitos, para RUC 11 dígitos','info');
                        this.mostrarProgreso = false;
                    }
                }
            },
            borrarCliente(){
                this.clienteSeleccionado = {};
                this.disabledClienteRuc = false;
                this.query = '';
                setTimeout(() => {
                    document.getElementById('buscar-cliente').focus();
                }, 50);

            },
            calcularVuelto(monto){
                let vuelto = Number(this.pagaCon) - this.porPagar;
                if(monto){
                    vuelto = monto - this.porPagar;
                    this.pagaCon = monto;
                }
                this.vuelto = vuelto;
            },
            procesar(){
                if (this.validarVenta()) {
                    return;
                }

                if (this.metodoPago == 2 && this.cuotas.length == 0) {
                    alert('Debes ingresar al menos una cuota con su fecha de vencimiento');
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
                    'tipo_pago_contado':this.metodoPago,
                    'cliente':JSON.stringify(this.clienteSeleccionado),
                    'num_operacion':this.num_operacion,
                    'pago_fraccionado': JSON.stringify(this.pago_fraccionado),
                    'cuotas': JSON.stringify(this.cuotas),
                })
                    .then(response => {
                        let data = response.data;
                        this.mostrarSpinner = false;
                        if(this.origen == 'pedidos'){
                            this.$emit('obtener-mesas');
                        }
                        if (isNaN(data.idventa)) {
                            this.alerta('Ha ocurrido un error al procesar la venta','error');
                        } else {
                            if(data.idventa == -1){
                                this.errorVenta = 1;
                                this.errorDatosVenta = [];
                                this.errorDatosVenta.push(data.respuesta);
                            } else{
                                if(isNaN(data.file)){
                                    this.enviar_documentos(data.idventa,data.file,'0');
                                }
                                if(this.origen == 'pedidos'){
                                    this.$emit('limpiar');
                                } else{
                                    this.$emit('after-save',data);
                                }
                                if(this.imprimir){
                                    this.$emit('imprimir',data.idventa);
                                }
                                this.$refs['modal-facturar'].hide();
                                this.control_stock(data.idventa);
                            }
                            this.cuotas = [];
                            this.cuotasAux = [];
                        }
                    })
                    .catch(error => {
                        this.mostrarSpinner = false;
                        this.alerta('Ha ocurrido un error.','error');
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
                        if((mensaje.toLowerCase()).includes('aceptada') || (mensaje.toLowerCase()).includes('aceptado')){
                            titulo = 'Comprobante enviado con éxito';
                            color = 'primary';
                            tiempo = 5000;
                            this.$eventBus.$emit('count-comprobantes');
                        } else if((mensaje.toLowerCase()).includes('rechazado')) {
                            titulo = 'El comprobante ha sido rechazado y no es válido';
                            color = 'danger';
                            tiempo = 10000;
                            this.$eventBus.$emit('count-notificaciones');
                        } else {
                            titulo = 'Comprobante pendiente de envío';
                            color = 'warning';
                            tiempo = 10000;
                            this.$eventBus.$emit('count-notificaciones');
                        }

                        this.$bvToast.toast(mensaje, {
                            title: titulo,
                            variant: color,
                            solid: true,
                            autoHideDelay: tiempo,
                        });

                    })
                    .catch(error => {
                        this.alerta('Error','error');
                        console.log(error);
                    });
            },
            control_stock(idventa){
                axios.get('/helper/notificar-estado-stock' + '/' + idventa)
                    .then(() => {
                        this.$eventBus.$emit('count-notificaciones');
                    })
                    .catch(error => {
                        alert('Ha ocurrido un error al obtener el stock');
                        console.log(error);
                    });
            },
            validarVenta(){

                this.errorVenta = 0;
                this.errorDatosVenta = [];
                //Validar pagos fraccionados
                if (this.metodoPago == 4) {
                    let suma_pago_fra = 0;
                    for (let pago of this.pago_fraccionado) {
                        suma_pago_fra += Number(pago.monto);
                    }

                    if (suma_pago_fra > this.porPagar) this.errorDatosVenta.push('*La suma de los pagos fraccionados supera el monto total de la venta');
                    if (suma_pago_fra < this.porPagar) this.errorDatosVenta.push('*La suma de los pagos fraccionados es inferior al monto total de la venta');
                }
                if (Object.keys(this.clienteSeleccionado).length == 0) this.errorDatosVenta.push('*Debes ingresar un cliente');
                if ('nombre_o_razon_social' in this.clienteSeleccionado && this.clienteSeleccionado['nombre_o_razon_social'].length == 0) this.errorDatosVenta.push('*Debes ingresar un cliente');

                if(this.comprobante == '01'){
                    if (this.clienteSeleccionado['num_documento'] && this.clienteSeleccionado['num_documento'].length != 11) this.errorDatosVenta.push('*Ingrese un RUC válido');
                }
                if (this.comprobante == '03') {
                    if (this.total_venta >= 700){
                        let str = this.clienteSeleccionado['num_documento'];
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
                this.metodoPago = 1;
                this.idorden = -1;
                this.idorden_origen = -1;
                this.dividir = 0;
                this.propina = '';
                this.vuelto = 0;
                this.imprimir = true;
                this.num_operacion='';
                this.pago_fraccionado = [
                    {
                        monto: '0.00',
                        tipo: '1'
                    },
                    {
                        monto: '0.00',
                        tipo: '3'
                    },
                ];
            },
            alerta(texto, icon){
                this.$swal({
                    position: 'top',
                    icon: icon || 'warning',
                    title: texto,
                    timer: 5000,
                    toast:true,
                    confirmButtonColor: '#007bff',
                });
            }
        }
    }
</script>
<style>
    .borrarCliente{
        right: 65px;
        top:9px;
    }
    .editarCliente{
        position: absolute;
        top: 28px;
        right: 20px;
        font-size: 18px;
        cursor: pointer;
    }
    .n-cliente{
        position: absolute;
        z-index: 99;
        right: 19px;
        top: 10px;
    }
    .div-calculadora{
        padding:10px;
    }
    .billete{
        background: #5dba3c;
        padding: 5px;
        margin-right: 7px;
        border-radius: 2px;
        color: black;
        font-weight: 700;
        cursor:pointer;
    }
    .billete:hover {
        background:#0bd311;
    }
    .calc{
        position:absolute;
        top: -10px;
        left: 28px;
    }
    .tabla_vuelto td{
        font-size: 17px;
        padding: 0;
    }
    @media (min-width: 992px) {
        .w-lg-50 { width: 50%!important; }
    }
</style>