<template>
    <div class="autocomplete-component" id="autocomplete-component-id">
        <b-input-group>
            <b-input-group-prepend>
                <b-input-group-text>
                    <i class="fas fa-check"></i>
                </b-input-group-text>
            </b-input-group-prepend>
            <input autocomplete="off" type="text" id="buscador-kardex" onclick="this.select()" @click="autoComplete" :disabled="disabledBuscador"
                   placeholder="Buscar..." v-model="query" v-on:keyup="navigate"
                   class="form-control"/>
        </b-input-group>
        <i class="fas fa-times-circle borrarCliente" v-show="disabledBuscador" v-on:click="borrarCliente" :style="add_btn ? { right: '43px' } : {}"></i>
        <div class="panel-footer autocomplete-wrapper autocomplete-wrapper-cliente" v-if="results.length">
            <ul class="list-group">
                <li v-on:click="agregarBuscado(index)" style="cursor:pointer" class="list-group-item d-flex"
                    v-for="(result,index) in results"
                    v-bind:class='{"active_item": currentItem === index}'>
                    <div class="col-lg-8">
                        {{result.nombre || result.num_comprobante || result.num_proceso}}
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
    props: ["es_proveedores","filtro","add_btn","limpiar_buscador"],
    data(){
        return {
            query: '',
            results: [],
            currentItem: 0,
            cursor_position: 0,
            disabledBuscador:false,
        }
    },
    created() {
        this.handler = e => {
            if((e.code=='ArrowUp' || e.code=='ArrowDown') && document.getElementById("buscador-kardex") === document.activeElement){
                e.view.event.preventDefault();
            }
        };
        window.addEventListener('keydown', this.handler);

        window.addEventListener('click', e => {
            if(document.getElementById('autocomplete-component-id')){
                if (!document.getElementById('autocomplete-component-id').contains(e.target)){
                    this.results = [];
                }
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
                        this.agregarBuscado(this.currentItem);
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
        setQuery(query){
          this.query = query;
          if(query){
              this.disabledBuscador = true;
          } else {
              this.disabledBuscador = false;
          }
        },
        agregarBuscado(index_or_object){

            let seleccionado = this.results[index_or_object];
            let emit = null;
            let indexLocalStorage = null;

            switch (this.filtro) {
                case 'proveedor':
                    this.query = seleccionado['num_documento']+' - '+seleccionado['nombre'];
                    emit = seleccionado['idproveedor'];
                    indexLocalStorage = emit;
                    break;
                case 'producto':
                    this.query = seleccionado['nombre'];
                    emit = seleccionado['idproducto'];
                    indexLocalStorage = emit;
                    break;
                case 'comprobante':
                    this.query = seleccionado['num_comprobante'];
                    emit = seleccionado['idrequerimiento'];
                    indexLocalStorage = emit;
                    break;
                case 'cliente':
                    this.query = seleccionado['num_documento']+' - '+seleccionado['nombre'];
                    emit = seleccionado['idcliente'];
                    indexLocalStorage = emit;
                    break;
                case 'trabajador':
                    this.query = seleccionado['num_documento']+' - '+seleccionado['nombre'];
                    emit = seleccionado;
                    indexLocalStorage = seleccionado['idtrabajador'];
                    break;
            }

            if(this.limpiar_buscador){
                this.disabledBuscador = false;
                this.query = '';
            } else {
                this.disabledBuscador = true;
                localStorage.setItem(indexLocalStorage, this.query);
            }
            this.$emit('buscar',  emit);
            this.currentItem = index_or_object;
            this.results = [];
        },
        autoComplete(){
            this.results = [];
            let url = "/helper/filtrar" + "/";
            if (this.query.length > 1) {
                axios.get(url + this.query + '?filtro='+this.filtro).then((response) => {
                    this.results = response.data;
                });
            } else if(this.query.length == 0){
                axios.get(url + '' + '?filtro='+this.filtro).then((response) => {
                    this.results = response.data;
                });
            }
        },
        limpiar(){
            this.results = [];
            this.query = '';
            this.currentItem = 0;
            document.getElementById("buscador-kardex").focus();
        },
        llenarInput(obj){
            this.query = obj['num_documento']+' - '+obj['nombre'];
            this.disabledBuscador = true;
        },
        borrarCliente(){
            this.$emit('borrar_cliente');
            this.disabledBuscador = false;
            this.query = '';
            document.getElementById("buscador-kardex").focus();
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
