<template>
    <div>
        <b-modal id="modal-producto-descuento" ref="modal-producto-descuento" centered size="md" @hide="limpiar" @show="getData">
            <template slot="modal-title">
                {{titulo}}
            </template>
            <div class="container">
                <div class="row">
                    <ul class="list-group w-100">
                        <li v-on:click="agregarDescuento(item.monto_desc, item.cantidad_min)" style="cursor:pointer" class="list-group-item d-flex"
                            v-for="(item,index) in descuentos">
                            <div class="col-lg-6">
                                MAYOR O IGUAL A <strong>{{item.cantidad_min}}</strong> UND
                            </div>
                            <div class="col-lg-4">
                                S/{{(item.monto_desc)}} C/U
                            </div>
                            <div class="col-lg-2">
                                <span class="badge badge-warning">{{item.etiqueta}}</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <template #modal-footer="{ ok, cancel}">
                <b-button variant="secondary" @click="cancel()">Listo</b-button>
            </template>
        </b-modal>
    </div>
</template>

<script>
    export default {
        name: 'modal-producto-descuento',
        props: ['item'],
        data() {
            return {
                descuentos: [],
                titulo: 'Descuentos del producto',
            }
        },
        methods: {
            getData(){
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
                let data = {
                    precio: precio,
                    cantidad: cantidad
                };
                this.$emit('agregar', data);
                this.$refs['modal-producto-descuento'].hide()
            },
            limpiar(){
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
</style>