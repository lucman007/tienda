<template>
    <div>
        <b-modal id="modal-descuento" ref="modal-descuento" size="md" @hide="limpiar" @show="getData(item)">
            <template slot="modal-title">
                {{titulo}}
            </template>
            <div class="container">
                <div class="row">
                    <div class="col-md-5">
                        <b-input-group>
                            <b-form-input @keyup="convertirPorcentaje(esDstoEnPorcentaje)" v-model="monto" type="number"
                                          onfocus="this.select()"></b-form-input>
                            <template #append>
                                <b-dropdown :text="tipo" variant="secondary" class="tipo_conversion">
                                    <b-dropdown-item @click="convertir(false)">{{moneda}}</b-dropdown-item>
                                    <b-dropdown-item @click="convertir(true)">%</b-dropdown-item>
                                </b-dropdown>
                            </template>
                        </b-input-group>
                    </div>
                    <div class="col-md-7 mt-4 mt-sm-1" v-show="!(global || esDstoEnPorcentaje)">
                        <b-form-checkbox @change="calcularPorUnidad" v-model="convertirPorUnidad" switch class="mb-3">
                            Descuento por cada unidad
                        </b-form-checkbox>
                    </div>
                    <div class="col-12 mt-4">
                        <div class="alert alert-info text-center">
                            <label>Total descuento:</label>
                            <b>{{moneda}} {{(Number(montoConvertido).toFixed(2))}}</b>
                            <span v-show="tipo!='%'"> ({{Number(montoPorcentaje).toFixed(3)}}%)</span>
                        </div>
                    </div>
                    <div class="col-12 mt-4" v-if="descuentos.length && !global">
                        <h5>Lista de precios:</h5>
                        <ul class="list-group w-100">
                            <li v-on:click="agregarDescuento(item.monto_desc, item.cantidad_min)" style="cursor:pointer" class="list-group-item d-flex"
                                v-for="(item,index) in descuentos">
                                <div class="col-lg-6 p-0">
                                    MAYOR O IGUAL A <strong>{{item.cantidad_min}}</strong> UND
                                </div>
                                <div class="col-lg-4 p-0">
                                    S/{{(item.monto_desc)}} C/U
                                </div>
                                <div class="col-lg-2 p-0">
                                    <span class="badge badge-warning">{{item.etiqueta}}</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <template #modal-footer="{ ok, cancel}">
                <b-button variant="secondary" @click="cancel()">Cancel</b-button>
                <b-button variant="primary" @click="guardarDatos(true)">OK</b-button>
            </template>
        </b-modal>
    </div>
</template>

<script>
    export default {
        name: 'modal-descuento',
        props: ['item', 'moneda', 'igv', 'global','data-descuento'],
        data() {
            return {
                esDstoEnPorcentaje: false,
                monto: '0',
                montoConvertido: '0.00',
                montoPorcentaje: 0,
                titulo: 'Descuento de item',
                convertirPorUnidad:false,
                esConIgv:false,
                item_:{},
                tipo:'%',
                descuentos: [],
            }
        },
        methods: {
            getDescuentosProducto(){
                axios.get('/helper/obtener-descuentos' + '/' + this.item.idproducto)
                    .then(response => {
                        this.descuentos = response.data;
                    })
                    .catch(function (error) {
                        alert('Ha ocurrido un error.');
                        console.log(error);
                    });
            },
            agregarDescuento(precio,cantidad){
                this.item.precio = precio;
                this.item.cantidad = cantidad
                this.$emit('actualizar-detalle');
                this.$refs['modal-descuento'].hide()
            },
            getData(item){

                this.getDescuentosProducto();

                this.item_ = item;
                this.esConIgv = this.igv;

                if(this.global){
                   this.titulo = 'Descuento global';
                   this.item_ = this.dataDescuento;
                } else {
                    this.titulo = 'Descuento de item';
                }

                this.esDstoEnPorcentaje = !!this.item_['tipo_descuento'];
                this.montoConvertido = this.item_['descuento'];
                this.montoPorcentaje = this.item_['porcentaje_descuento'];
                this.convertirPorUnidad = !!this.item_['descuento_por_und'];

                if (this.esDstoEnPorcentaje) {
                    this.tipo = '%';
                    this.monto = this.item_['porcentaje_descuento'];
                    if(this.convertirPorUnidad){
                        this.monto = this.item_['porcentaje_descuento'] / this.item_['cantidad'];
                    }
                } else {
                    this.tipo = this.moneda;
                    this.monto = this.item_['descuento'];
                    if(this.convertirPorUnidad){
                        this.monto = this.item_['descuento'] / this.item_['cantidad'];
                    }
                }
                this.convertirPorcentaje();
            },
            convertir(esDstoEnPorcentaje){
                this.esDstoEnPorcentaje = esDstoEnPorcentaje;
                this.tipo = esDstoEnPorcentaje?'%':this.moneda;
                this.convertirPorcentaje();
            },
            convertirPorcentaje(){
                let subtotal = 0;
                let monto = Number(this.monto);
                if(this.esDstoEnPorcentaje){
                    this.convertirPorUnidad = false;
                }
                if(this.global){
                    subtotal = this.dataDescuento['gravadas'];
                    this.montoConvertido = this.esDstoEnPorcentaje ? (subtotal * monto / 100):monto;
                    this.montoPorcentaje = this.esDstoEnPorcentaje ? monto:monto / subtotal * 100;
                } else {

                    if(this.convertirPorUnidad){
                        subtotal = this.esConIgv ? this.item_['precio'] / 1.18 : this.item_['precio'];
                        this.montoConvertido = monto * this.item_['cantidad'];
                        this.montoPorcentaje = this.montoConvertido / (subtotal * this.item_['cantidad']) * 100;
                    } else{
                        subtotal = (this.esConIgv ? this.item_['precio'] / 1.18 : this.item_['precio']) * this.item_['cantidad'];
                        this.montoConvertido = this.esDstoEnPorcentaje ? (subtotal * monto / 100):monto;
                        this.montoPorcentaje = this.esDstoEnPorcentaje ? monto:monto / subtotal * 100;

                    }
                }
            },
            calcularPorUnidad(val){
                this.convertirPorUnidad = val;
                this.convertirPorcentaje(false)
            },
            guardarDatos(calcular){
                let data = {
                    monto: this.montoConvertido,
                    porcentaje: (this.montoPorcentaje).toFixed(3),
                    tipo_descuento: this.esDstoEnPorcentaje,
                    porUnidad: this.convertirPorUnidad,
                    recalcular: calcular
                };
                this.$emit('actualizar', data);
                this.$refs['modal-descuento'].hide()
            },
            limpiar(){
                this.monto = '0';
                this.montoConvertido = '0.00';
                this.esDstoEnPorcentaje = false;
                this.convertirPorUnidad = false;
                this.descuento = []
            }
        }
    }
</script>
<style>
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type=number] {
        -moz-appearance: textfield;
    }
    .tipo_conversion ul{
        min-width: 30px !important;
    }
    .tipo_conversion ul li a{
        padding: 8px 30px 8px 30px !important;
    }
</style>