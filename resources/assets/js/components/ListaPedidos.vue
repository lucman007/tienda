<template>
    <div>
        <div class="panel-footer" v-if="results.length">
            <ul class="list-group">
                <li class="list-group-item d-flex">
                    <div>N°</div>
                    <div class="col-lg">Vendedor</div>
                    <div class="col-lg-4">Cliente</div>
                    <div class="col-lg">Total</div>
                    <div class="col-lg">Doc.</div>
                </li>
                <li v-on:click="agregarPedido(result.idorden,index)" style="cursor:pointer" class="list-group-item d-flex"
                    v-for="(result,index) in results"
                    v-bind:class='{"active_item": currentItem === index}'>
                    <div>
                        {{result.idorden}}
                    </div>
                    <div class="col-lg">
                        {{(result.trabajador.persona.nombre).toUpperCase() }}
                    </div>
                    <div class="col-lg-4">
                        {{(result.cliente.persona.nombre)}}
                    </div>
                    <div class="col-lg">
                        {{result.moneda}} {{result.total}}
                    </div>
                    <div class="col-lg">
                        <span v-bind:class="'badge ' + result.badge_class">{{result.comprobante}}</span>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
export default{
    data(){
        return {
            results: [],
            currentItem: 0
        }
    },
    mounted(){
        this.obtenerPedidos();
    },
    created() {
        let _this = this;
        this.handler = function(e){
            if(e.code=='ArrowUp' || e.code=='ArrowDown'){
                e.view.event.preventDefault();
            }
            switch (e.code) {
                case 'ArrowUp':
                    if (_this.currentItem > 0) {
                        _this.currentItem--;
                    }
                    break;
                case 'ArrowDown':
                    if (_this.currentItem < (_this.results.length - 1)) {
                        _this.currentItem++;
                    }
                    break;
                case 'Enter':
                    if (_this.results.length > 0) {
                        _this.$emit('agregar_pedido', _this.results[_this.currentItem]['idorden'], true);
                    }
                    break;

            }
        };
        window.addEventListener('keydown', this.handler);
    },
    beforeDestroy() {
        window.removeEventListener('keydown', this.handler);
    },
    methods: {
        agregarPedido(idorden,index){
            this.$emit('agregar_pedido', idorden, true);
            this.currentItem = index;
        },
        obtenerPedidos(){
            this.results = [];
            axios.get('ventas/obtenerPedidos')
                .then(response => {
                    this.results = response.data;
                })
                .catch(function (error) {
                    alert('Ha ocurrido un error al obtener lista de pedidos');
                });
        },
        limpiar(){
            this.results = [];
            this.currentItem = 0;
        }
    }
}
</script>

<style>
    .active_item {
        background-color: #007bff;
        color: white;
    }

    .list-group-item:hover {
        background-color: #CCC;
    }
</style>
