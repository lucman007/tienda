<template>
    <div class="autocomplete-component" id="autocomplete-component-id">
        <input autocomplete="off" type="text" id="buscador" onfocus="this.value = this.value;"
               placeholder="Buscar producto..." v-model="query" v-on:keyup="navigate"
               class="form-control"/>
        <div class="panel-footer autocomplete-wrapper" v-if="results.length">
            <ul class="list-group">
                <li v-on:click="agregarProducto(index)" style="cursor:pointer" class="list-group-item d-flex"
                    v-for="(result,index) in results"
                    v-bind:class='{"active_item": currentItem === index}'>
                    <div class="col-lg-8">
                        {{result.cod_producto}} {{(result.cod_producto).length==0?"":"-"}} {{result.nombre }}
                    </div>
                    <div class="col-lg">
                        {{result.moneda+result.precio }}
                    </div>
                    <div v-show="result.tipo_producto===1" class="col-lg">
                        <span :class="'badge '+result.badge_stock">{{result.stock+result.unidad}}</span>
                    </div>
                    <div v-show="result.tipo_producto===2" class="col-lg">
                        -
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
            query: '',
            results: [],
            currentItem: 0,
            cursor_position: 0
        }
    },
    mounted(){
        let input = document.getElementById("buscador");
        input.focus();
    },
    created() {
        this.handler = function(e){
            if((e.code=='ArrowUp' || e.code=='ArrowDown') && document.getElementById("buscador") === document.activeElement){
                e.view.event.preventDefault();
                let input = document.getElementById("buscador");
                input.focus();
            }
        };
        window.addEventListener('keydown', this.handler);

        let _this = this;

        window.addEventListener('click', function(e){
            if (!document.getElementById('autocomplete-component-id').contains(e.target)){
                _this.results = [];
            }
        })
    },
    beforeDestroy() {
        window.removeEventListener('keydown', this.handler);
    },
    methods: {
        navigate(event){
            switch (event.code) {
                case 'ArrowUp':
                    if (this.currentItem > 0) {
                        this.currentItem--;
                    }
                    break;
                case 'ArrowDown':
                    if (this.currentItem < (this.results.length - 1)) {
                        this.currentItem++;
                    }
                    break;
                case 'Escape':
                    this.results = [];
                    this.query = '';
                    break;
                case 'Enter':
                case 'NumpadEnter':
                    if (this.results.length > 0) {
                        this.$emit('agregar_producto', this.results[this.currentItem]);
                    } else{
                        let _this = this;
                        axios.get('/helper/agregar-producto' + '/' + this.query)
                            .then(response => {
                                _this.results = response.data;
                                if((Object.keys(_this.results).length === 0)){
                                    alert('No se ha encontrado el producto con el código marcado');
                                } else{
                                    _this.$emit('agregar_producto', _this.results);
                                }
                            });
                    }
                    this.limpiar();
                    break;
                default:
                    this.currentItem = 0;
                    if (this.timer) {
                        clearTimeout(this.timer);
                        this.timer = null;
                    }
                    this.timer = setTimeout(() => {
                        this.autoComplete();
                    }, 400);

            }

        },
        agregarProducto(index){
            this.$emit('agregar_producto',  this.results[index]);
            this.currentItem = index;
            document.getElementById("buscador").focus();
        },
        autoComplete(){
            this.results = [];
            if (this.query.length > 1) {
                axios.get('/helper/obtener-productos' + '/' + this.query)
                    .then(response => {
                        this.results = response.data;
                    });
            }
        },
        limpiar(){
            this.results = [];
            this.query = '';
            this.currentItem = 0;
            document.getElementById("buscador").focus();
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
        background-color: #2b2b2b;
        color: white
    }
</style>
