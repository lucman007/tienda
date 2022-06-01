<template>
    <div>
        <b-modal id="modal-descuento" ref="modal-descuento" size="sm" @hide="limpiar" @show="getData">
            <template slot="modal-title">
                {{titulo}}
            </template>
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <b-form-checkbox @change="convertirPorcentaje" v-model="esDstoEnPorcentaje" switch class="mb-3">
                            Descuento en porcentaje
                        </b-form-checkbox>
                    </div>
                    <div class="col-12" v-show="!(global || esDstoEnPorcentaje)">
                        <b-form-checkbox @change="calcularPorUnidad" v-model="porUnidad" switch class="mb-3">
                            Descuento por cada unidad
                        </b-form-checkbox>
                    </div>
                    <div class="col-12">
                        <label>Monto descuento</label>
                        <b-input-group :append="getSufix">
                            <b-form-input @keyup="convertirPorcentaje(esDstoEnPorcentaje)" v-model="monto" type="number"
                                          onfocus="this.select()"></b-form-input>
                        </b-input-group>
                    </div>
                    <div class="col-12 mt-4">
                        <div class="alert alert-info text-center">
                            <label>Total descuento:</label>
                            <b>{{moneda}} {{(Number(montoConvertido).toFixed(2))}}</b> <br> ({{Number(montoPorcentaje).toFixed(2)}}%)
                        </div>
                    </div>
                </div>
            </div>
            <template #modal-footer="{ ok, cancel}">
                <b-button variant="secondary" @click="cancel()">Cancel</b-button>
                <b-button variant="primary" @click="guardarDatos">OK</b-button>
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
                porUnidad:false
            }
        },
        computed: {
            getSufix(){
                return this.esDstoEnPorcentaje ? '%' : this.moneda;
            },
        },
        methods: {
            getData(){
                let item = this.item;

                if(this.global){
                   this.titulo = 'Descuento global';
                   item = this.dataDescuento;
                } else {
                    this.titulo = 'Descuento de item';
                }

                this.esDstoEnPorcentaje = !!item['tipo_descuento'];
                this.montoConvertido = item['descuento'];
                this.montoPorcentaje = item['porcentaje_descuento'];
                this.porUnidad = !!item['descuento_por_und'];

                if (this.esDstoEnPorcentaje) {
                    this.monto = item['porcentaje_descuento'];
                    if(this.porUnidad){
                        this.monto = item['porcentaje_descuento'] / item['cantidad'];
                    }
                } else {
                    this.monto = item['descuento'];
                    if(this.porUnidad){
                        this.monto = item['descuento'] / item['cantidad'];
                    }
                }
                this.convertirPorcentaje(this.esDstoEnPorcentaje);
            },
            convertirPorcentaje(val){
                let subtotal = 0;
                let monto = Number(this.monto);
                if(val){
                    this.porUnidad = false;
                }
                if(this.global){
                    subtotal = this.dataDescuento['gravadas'];
                    this.montoConvertido = val ? (subtotal * monto / 100):monto;
                    this.montoPorcentaje = val ? monto:monto / subtotal * 100;
                } else {
                    if(this.porUnidad){
                        subtotal = this.igv ? this.item['precio'] / 1.18 : this.item['precio'];
                        this.montoConvertido = monto * this.item['cantidad'];
                        this.montoPorcentaje = this.montoConvertido / (subtotal * this.item['cantidad']) * 100;
                    } else{
                        subtotal = (this.igv ? this.item['precio'] / 1.18 : this.item['precio']) * this.item['cantidad'];
                        this.montoConvertido = val ? (subtotal * monto / 100):monto;
                        this.montoPorcentaje = val ? monto:monto / subtotal * 100;
                    }
                }
            },
            calcularPorUnidad(val){
                this.porUnidad = val;
                this.convertirPorcentaje(false)
            },
            guardarDatos(){
                let data = {
                    monto: this.montoConvertido,
                    porcentaje: this.montoPorcentaje,
                    tipo_descuento: this.esDstoEnPorcentaje,
                    porUnidad: this.porUnidad
                };
                this.$emit('actualizar', data);
                this.$refs['modal-descuento'].hide()
            },
            limpiar(){
                this.monto = '0';
                this.montoConvertido = '0.00';
                this.esDstoEnPorcentaje = false;
                this.porUnidad = false;
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
</style>