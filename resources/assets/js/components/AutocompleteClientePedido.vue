<template>
    <div class="autocomplete-component" id="autocomplete-id">
        <b-input-group>
            <input @blur="setCliente()" placeholder="Nombre o alias" autocomplete="off" type="text" id="buscador-cliente" onclick="this.select()" @click="autoComplete" :disabled="disabledBuscador"
                   v-model="query" v-on:keyup="navigate"
                   class="form-control"/>
        </b-input-group>
        <i class="fas fa-times-circle borrarClientePedido" v-show="disabledBuscador" v-on:click="borrarClientePedido"></i>
        <div class="panel-footer autocomplete-wrapper" v-if="results.length">
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
            if((e.code=='ArrowUp' || e.code=='ArrowDown') && document.getElementById("buscador-cliente") === document.activeElement){
                e.view.event.preventDefault();
            }
        };
        window.addEventListener('keydown', this.handler);

        this.clicBlur = e => {
          if (!document.getElementById('autocomplete-id').contains(e.target)){
            this.results = [];
          }
        }

        window.addEventListener('click', this.clicBlur)
    },
    beforeDestroy() {
        window.removeEventListener('keydown', this.handler);
        window.removeEventListener('click', this.clicBlur);
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
                if(seleccionado['num_documento']){
                  this.query = seleccionado['num_documento']+' - '+seleccionado['persona']['nombre'];
                } else {
                  this.query = seleccionado['persona']['nombre'];
                }
            } else{
                this.$emit('agregar_cliente',  this.results[index_or_object]);
                this.currentItem = index_or_object;
                this.disabledBuscador = true;
                let seleccionado = this.results[index_or_object];
                this.query = seleccionado['num_documento']+' - '+seleccionado['nombre'];
                this.results = [];
            }
        },
        setCliente(data){
          setTimeout(() => {
            document.getElementById("buscador-cliente").focus();
          }, 0);
          if(data){
            if(data.nombre != '' && data.nombre != '-'){
              let obj = {idcliente:data.idcliente,nombre: data.nombre, num_documento:null, persona: {nombre:data.nombre}};
              this.agregarCliente(obj);
            }

          } else {
            if(this.query){
              let cliente = this.query.toUpperCase();
              let obj = {idcliente:null,nombre: cliente, num_documento:null, persona: {nombre:cliente}};
              this.agregarCliente(obj);
              //this.results = [];
            }
          }
        },
        autoComplete(){
            this.results = [];
            let url = "/helper/obtener-clientes" + "/";
            if (this.query.length > 1) {
                axios.get(url + this.query).then((response) => {
                    this.results = response.data;
                });
            }
        },
        borrarClientePedido(){
            this.$emit('borrar_cliente');
            this.disabledBuscador = false;
            this.query = '';
          setTimeout(() => {
            document.getElementById("buscador-cliente").focus();
          }, 0);
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
    .borrarClientePedido {
        position: absolute;
        top: 9px;
        right: 10px;
        font-size: 18px;
        cursor: pointer;
    }
</style>
