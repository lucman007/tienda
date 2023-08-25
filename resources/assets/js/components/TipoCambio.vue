<template>
    <p style="margin-left:20px" :style="color"><i class="fas fa-dollar-sign color-dollar"></i> Compra {{compra}} / Venta: {{venta}}</p>
</template>
<script>
    export default {
        name: 'tipo-cambio',
        props:['cambio','color'],
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