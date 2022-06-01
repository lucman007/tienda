<template>
    <p style="margin-left:20px"><i class="fas fa-dollar-sign"></i> Compra {{compra}} / Venta: {{venta}}</p>
</template>
<script>
    export default {
        name: 'tipo-cambio',
        props:['cambio'],
        data() {
            return {
                compra:0,
                venta:0,
            }
        },
        created(){
            this.obtenerTipoCambio();
        },
        methods:{
            obtenerTipoCambio(){
                this.compra = this.cambio['tipo_cambio_compra'];
                this.venta = this.cambio['tipo_cambio_venta'];
            },
        }
    }
</script>
<style>
    .fa-dollar-sign{
        background: #187c0c;
        padding: 3px;
        border-radius: 5px;
    }
</style>
