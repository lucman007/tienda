<template>
    <div>
        <!--INICIO MODAL DOCUMENTO -->
        <b-modal size="lg" id="modal-devolucion" ref="modal-devolucion" ok-only @hidden="resetModal">
            <template slot="modal-title">
                Seleccionar documento
            </template>
            <div class="container">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="buscar">Busca por correlativo o cliente:</label>
                            <input @keyup="delay()" v-model="buscar" type="text" name="buscar"
                                   placeholder="Buscar..." class="form-control" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col">N° Doc.</th>
                                    <th scope="col">Serie/correlativo</th>
                                    <th scope="col">Cliente</th>
                                    <th scope="col">Importe</th>
                                    <th scope="col"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr :class="{'td-anulado':doc.estado=='ANULADO'}" v-for="(doc,index) in listaDocumentos"
                                    :key="doc.idventa">
                                    <td>{{doc.idventa}}</td>
                                    <td style="width: 20%">{{doc.serie}}-{{doc.correlativo}}</td>
                                    <td style="width: 40%">{{doc.nombre}}</td>
                                    <td>{{doc.total_venta}}</td>
                                    <td style="width: 5%" class="botones-accion">
                                        <button :disabled="doc.estado == 'ANULADO' || doc.estado == 'RECHAZADO'" @click="verDetalle(doc)" class="btn btn-info"
                                                title="Ver detalle"><i
                                                class="fas fa-check"></i>
                                        </button>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </b-modal>
        <!--FIN MODAL DOCUMENTO -->
        <!--INICIO MODAL DOCUMENTO DETALLE -->
        <b-modal size="lg" id="modal-devolucion-detalle" ref="modal-devolucion-detalle" @hidden="resetModal">
            <template slot="modal-title">
                {{titulo_modal_detalle}}
            </template>
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <span class="float-right"><strong>Total devolución: {{ moneda_devolucion+' '+ total_devolucion}}</strong></span>
                    </div>
                    <div class="col-lg-12">
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Producto</th>
                                    <th scope="col">Precio</th>
                                    <th scope="col">Cantidad</th>
                                    <th scope="col">Total</th>
                                    <th scope="col" style="width: 120px">Devolución</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr :style="{opacity:Number(item.detalle.devueltos) >= Number(item.detalle.cantidad)?0.5:1}" v-for="(item,index) in detalle"
                                    :key="index">
                                    <td>{{ item.detalle.num_item }}</td>
                                    <td>{{ item.nombre }} <span v-show="Number(item.detalle.devueltos) > 0" class="badge badge-warning">{{item.detalle.devueltos}} DEVUELTOS</span></td>
                                    <td>{{ item.detalle.monto }}</td>
                                    <td>{{ item.detalle.cantidad }}</td>
                                    <td>{{ item.detalle.total }}</td>
                                    <td>
                                        <b-form-checkbox @change="calcularMontoDevolucion" :disabled="Number(item.detalle.devueltos) >= Number(item.detalle.cantidad)" class="float-left" v-model="item.devolver" switch size="sm">
                                        </b-form-checkbox>
                                        <input @keyup="calcularMontoDevolucion" v-model="item.cantidad_devolucion" v-show="item.devolver" type="number" class="form-control w-50" max="item.cantidad_devolucion">
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <template #modal-footer="{ ok, cancel}">
                <b-button variant="secondary" @click="cancel()">
                    Cancel
                </b-button>
                <b-button variant="primary" @click="devolverProductos">
                    <b-spinner v-show="mostrarProgresoGuardado" small label="Loading..." ></b-spinner>
                    <span v-show="!mostrarProgresoGuardado">Devolver productos</span>
                </b-button>
            </template>
        </b-modal>
        <!--FIN MODAL DOCUMENTO DETALLE -->
    </div>
</template>
<script>
    export default {
        name: 'modal-devolucion',
        data() {
            return {
                buscar: '',
                mostrarProgresoGuardado:false,
                listaDocumentos: [],
                detalle:[],
                titulo_modal_detalle:'',
                total_devolucion:'0.00',
                moneda_devolucion: 'S/',
                idventa: -1
            }
        },
        methods: {
            delay(){
                if (this.timer) {
                    clearTimeout(this.timer);
                    this.timer = null;
                }
                this.timer = setTimeout(() => {
                    this.obtenerDocumentos(true);
                }, 500);
            },
            verDetalle(venta){
                this.$refs['modal-devolucion'].hide();
                this.$refs['modal-devolucion-detalle'].show();
                this.titulo_modal_detalle = 'Venta N° '+venta.idventa;
                this.moneda_devolucion = 'S/';
                this.idventa = venta.idventa;

                axios.get('/caja/obtener-detalle-venta'+'/'+venta.idventa, {
                    'idventa':venta.idventa
                })
                    .then(response => {
                        this.detalle = response.data;
                    })
                    .catch(function (error) {
                        alert('Ha ocurrido un error al obtener los datos.');
                        console.log(error);
                    });
            },
            obtenerDocumentos(){
                axios.post('/ventas/obtenerDocumentos', {
                    'textoBuscado': this.buscar,
                    'comprobante': -1
                })
            .then(response => {
                    this.listaDocumentos = response.data;
                })
                    .catch(error => {
                        this.alerta('Ha ocurrido un error al obtener los documentos');
                        console.log(error);
                    });
            },
            resetModal(){

            },
            calcularMontoDevolucion(){

                this.$nextTick(() => {
                    let suma = 0;
                    for (let item of this.detalle) {
                        if (item.devolver) {
                            suma += item.detalle.monto * item.cantidad_devolucion;
                        }
                    }
                    this.total_devolucion = suma.toFixed(2);

                });

            },
            devolverProductos(){
                let items = [];
                let i = 0;

                for (let item of this.detalle) {
                    if (item.devolver) {
                        if(Number(item.cantidad_devolucion) > item.detalle.cantidad){
                            alert('No se puede devolver más cantidad de la vendida.');
                            return;
                        }
                        if(Number(item.cantidad_devolucion) <= 0){
                            alert('La cantidad de devolución debe ser mayor a 0');
                            return;
                        }
                        items[i] = item;
                        i++;
                    }
                }

                if (items.length > 0) {
                    if (confirm('Los productos seleccionados retornarán al inventario. ¿Confirma esta acción?')) {
                        this.mostrarProgresoGuardado=true;
                        axios.post('/caja/devolver-productos', {
                            'total_devolucion':this.total_devolucion,
                            'moneda_devolucion':this.moneda_devolucion,
                            'idventa':this.idventa,
                            'items': JSON.stringify(items)
                        })
                    .then(response => {
                            alert(response.data);
                            this.mostrarProgresoGuardado=false;
                            window.location.reload();
                        })
                            .catch(error => {
                                alert('Ha ocurrido un error al procesar la operación.');
                                this.mostrarProgresoGuardado=false;
                                console.log(error);
                            });
                    }

                } else {
                    alert('No hay productos seleccionados')
                }

            },
        }
    }
</script>