<template>
    <div>
        <b-modal size="lg" id="modal-cliente" ref="modal-cliente" ok-only @hidden="resetModal">
        <template slot="modal-title">
            Seleccionar cliente
        </template>
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="buscar">Busca por cliente o N° de documento:</label>
                        <input @keyup="buscar_cliente" v-model="buscar" type="text" name="buscar"
                         placeholder="Buscar..." class="form-control" autocomplete="off">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">

                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="table-responsive tabla-gestionar">
                        <table class="table table-striped table-hover table-sm">
                            <thead class="bg-custom-green">
                            <tr>
                                <th scope="col">Código</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Ruc</th>
                                <th scope="col">Dirección</th>
                                <th scope="col"></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(cliente,index) in listaClientes" :key="cliente.index">
                            <td style="display:none">{{cliente.idcliente}}</td>
                            <td style="width: 5%">{{cliente.cod_cliente}}</td>
                            <td style="width: 30%">{{cliente.nombre}}</td>
                            <td style="width: 20%">{{cliente.num_documento}}</td>
                            <td style="width: 30%">{{cliente.direccion}}</td>
                            <td style="width: 5%" class="botones-accion">
                                <a @click="agregar(index)" href="javascript:void(0)">
                                <button class="btn btn-info" title="Seleccionar cliente"><i
                                    class="fas fa-check"></i>
                                </button>
                            </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</b-modal>
    </div>
</template>
<script>
    export default {
        name:'modal-cliente',
        props:[
            'url_obtener_clientes'
        ],
        data() {
            return {
                listaClientes: [],
                buscar: ' '
            }
        },
        created(){
            this.obtenerClientes();
        },
        methods: {
            obtenerClientes(){
                let _this = this;
                axios.get('/helper/obtener-clientes' + '/' + this.buscar)
                    .then(function (response) {
                        let datos = response.data;
                        _this.listaClientes = datos;
                    })
                    .catch(function (error) {
                        alert('Ha ocurrido un error.');
                        console.log(error);
                    });
            },
            buscar_cliente(){
                if (this.timer) {
                    clearTimeout(this.timer);
                    this.timer = null;
                }
                this.timer = setTimeout(() => {
                     this.obtenerClientes();

                }, 500);
            },
            agregar(index){
                this.$emit('agregar_cliente',this.listaClientes[index]);
                this.$refs['modal-cliente'].hide();
                this.buscar = '';
                this.obtenerClientes();
            },
            resetModal(){
                this.buscar = '';
            },
        }
    }
</script>