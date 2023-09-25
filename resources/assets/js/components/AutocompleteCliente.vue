<template>
    <div class="autocomplete-component" id="autocomplete-component-id">
        <b-input-group>
            <b-input-group-prepend>
                <b-input-group-text>
                    <i class="fas fa-user"></i>
                </b-input-group-text>
            </b-input-group-prepend>
            <input autocomplete="off" type="text" id="buscador-cliente" onclick="this.select()" @click="autoComplete" :disabled="disabledBuscador"
                   :placeholder="es_proveedores ? 'Buscar proveedor...' : 'Buscar cliente...'" v-model="query" v-on:keyup="navigate"
                   class="form-control"/>
        </b-input-group>
        <i class="fas fa-times-circle borrarCliente" v-show="disabledBuscador" v-on:click="borrarCliente"></i>
        <div class="panel-footer autocomplete-wrapper autocomplete-wrapper-cliente" v-if="results.length">
            <ul class="list-group">
                <li v-on:click="agregarCliente(index)" style="cursor:pointer" class="list-group-item d-flex"
                    v-for="(result,index) in results"
                    v-bind:class='{"active_item": currentItem === index}'>
                    <div class="col-lg-8">
                        {{result.nombre }}
                    </div>
                    <div class="col-lg">
                        {{result.num_documento }}
                    </div>
                </li>
            </ul>

        </div>
    </div>
</template>

<script>
export default{
    props: ["es_proveedores"],
    data(){
        return {
            query: '',
            results: [],
            currentItem: 0,
            cursor_position: 0,
            disabledBuscador:false
        }
    },
    created() {
        this.handler = e => {
            if((e.code=='ArrowUp' || e.code=='ArrowDown') && document.getElementById("buscador-cliente") === document.activeElement){
                e.view.event.preventDefault();
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
                        this.$emit('agregar_cliente', this.results[this.currentItem]);
                        this.disabledBuscador = true;
                        let seleccionado = this.results[this.currentItem];
                        this.query = seleccionado['num_documento']+' - '+seleccionado['nombre'];
                        this.results = [];
                    }
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
        agregarCliente(index_or_object){
            if(typeof index_or_object === 'object'){
                this.$emit('agregar_cliente',  index_or_object);
                this.disabledBuscador = true;
                let seleccionado = index_or_object;
                this.query = seleccionado['num_documento']+' - '+seleccionado['persona']['nombre'];
            } else{
                this.$emit('agregar_cliente',  this.results[index_or_object]);
                this.currentItem = index_or_object;
                this.disabledBuscador = true;
                let seleccionado = this.results[index_or_object];
                this.query = seleccionado['num_documento']+' - '+seleccionado['nombre'];
                this.results = [];
            }

        },
        autoComplete(){
            this.results = [];
            let url = "/helper/obtener-clientes" + "/";
            if (this.es_proveedores) {
                url = "/helper/obtener-proveedores" + "/";
            }
            if (this.query.length > 1) {
                axios.get(url + this.query).then((response) => {
                    this.results = response.data;
                });

            } else if(this.query.length == 0){
                axios.get(url + '').then((response) => {
                    this.results = response.data;
                });
            }
        },
        limpiar(){
            this.results = [];
            this.query = '';
            this.currentItem = 0;
            document.getElementById("buscador-cliente").focus();
        },
        borrarCliente(){
            this.$emit('borrar_cliente');
            this.disabledBuscador = false;
            this.query = '';
            document.getElementById("buscador-cliente").focus();
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
    .borrarCliente {
        position: absolute;
        top: 9px;
        right: 10px;
        font-size: 18px;
        cursor: pointer;
    }
</style>
