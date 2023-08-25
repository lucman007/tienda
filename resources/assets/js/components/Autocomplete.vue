<template>
    <div class="autocomplete-component" id="autocomplete-component-id">
        <b-input-group>
            <input autocomplete="off" type="text" id="buscador" onclick="this.select()" @click="autoComplete"
                   placeholder="Buscar producto..." v-model="query" v-on:keyup="navigate"
                   class="form-control"/>
            <b-input-group-append>
                <b-dropdown :variant="filtro==-1?'secondary':'success'">
                    <template #button-content>
                        <i class="fas fa-sliders-h"></i>
                    </template>
                    <b-dropdown-item @click="activarFiltro('categoria')">Categoría</b-dropdown-item>
                    <b-dropdown-item @click="activarFiltro('marca')">Marca</b-dropdown-item>
                    <b-dropdown-item @click="activarFiltro('param_1')">Montaje</b-dropdown-item>
                    <b-dropdown-item @click="activarFiltro('param_2')">Cápsula</b-dropdown-item>
                    <b-dropdown-item @click="activarFiltro('param_3')">Tipo</b-dropdown-item>
                    <b-dropdown-item v-show="filtro != -1" @click="activarFiltro(-1)"><span style="color:red">Quitar filtro</span></b-dropdown-item>
                </b-dropdown>
            </b-input-group-append>
        </b-input-group>
        <div class="panel-footer autocomplete-wrapper">
            <ul class="list-group">
                <li v-on:click="agregarProducto(index)" style="cursor:pointer" class="list-group-item d-flex"
                    v-for="(result,index) in results"
                    v-bind:class='{"active_item": currentItem === index}'>
                    <div class="col-lg-8">
                        {{result.cod_producto}} {{(result.cod_producto).length==0?"":"-"}} <strong>{{result.nombre }}</strong> {{result.presentacion }}
                    </div>
                    <div v-if="origen=='compras'" class="col-lg">
                        {{result.moneda_compra+result.costo }}
                    </div>
                    <div v-else class="col-lg">
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
    props: ['origen'],
    data(){
        return {
            query: '',
            results: [],
            currentItem: 0,
            cursor_position: 0,
            filtro: -1,
        }
    },
    mounted(){
        let input = document.getElementById("buscador");
        input.focus();
    },
    created() {
        this.handler = e => {
            if((e.code=='ArrowUp' || e.code=='ArrowDown') && document.getElementById("buscador") === document.activeElement){
                e.view.event.preventDefault();
                let input = document.getElementById("buscador");
                input.focus();
            }
        };
        window.addEventListener('keydown', this.handler);

        window.addEventListener('click', e => {
            if (!document.getElementById('autocomplete-component-id').contains(e.target)){
                this.results = [];
            }
        })
    },
    beforeDestroy() {
        window.removeEventListener('keydown', this.handler);
    },
    methods: {
        activarFiltro(filtro){
          this.filtro = filtro;
        },
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
                        axios.get('/helper/agregar-producto' + '/' + this.query)
                            .then(response => {
                                this.results = response.data;
                                if((Object.keys(this.results).length === 0)){
                                    alert('No se ha encontrado el producto con el código marcado');
                                } else{
                                    this.$emit('agregar_producto', this.results);
                                }
                            });
                    }
                    //this.limpiar();
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
                axios.get('/helper/obtener-productos' + '/' + this.query+'?filtro='+this.filtro)
                    .then(response => {
                        this.results = response.data;
                    });
            } else if(this.query.length == 0){
                axios.get('/helper/obtener-productos' + '/' + '')
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
    .list-group-item{
        border: none;
        font-size: 12px;
        padding: 0.4rem 1.25rem;
    }
</style>
