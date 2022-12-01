<template>
    <div>
        <b-modal size="xl" id="modal-producto" ref="modal-producto" ok-only @hidden="resetModal">
            <template slot="modal-title">
                Seleccionar producto
            </template>
            <div class="container">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="buscar">Busca por nombre o código:</label>
                            <input @keyup="buscar_producto" v-model="buscar" type="text" name="buscar"
                                   placeholder="Buscar..." class="form-control" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col">Código</th>
                                    <th style="width: 250px" scope="col">Nombre</th>
                                    <th style="width: 350px" scope="col">Características</th>
                                    <th scope="col">Stock</th>
                                    <th scope="col">Precio</th>
                                    <th scope="col"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(producto,index) in listaProductos" :key="index">
                                    <td>{{producto.cod_producto}}</td>
                                    <td>{{producto.nombre}}</td>
                                    <td>{{producto.presentacion}}</td>
                                    <td v-show="producto.tipo_producto===1"><span :class="'badge '+producto.badge_stock">{{producto.stock+producto.unidad}}</span></td>
                                    <td v-show="producto.tipo_producto==2">-</td>
                                    <td>{{producto.moneda}}{{producto.precio}}</td>
                                    <td style="width: 5%" class="botones-accion">
                                        <button @click="agregar(index)"
                                                :disabled="stock && producto.stock <= 0 && producto.tipo_producto===1"
                                                class="btn btn-info" title="Seleccionar producto"><i
                                                class="fas fa-check"></i>
                                        </button>
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
        name:'modal-producto',
        props:[
            'stock'
        ],
        data() {
            return {
                listaProductos: [],
                buscar: ' '
            }
        },
        created(){
            this.obtenerProductos();
        },
        methods: {
            obtenerProductos(){
                axios.get('/helper/obtener-productos' + '/' + this.buscar)
                    .then(response => {
                        this.listaProductos = response.data;
                    })
                    .catch(error => {
                        alert('Ha ocurrido un error.');
                        console.log(error);
                    });
            },
            buscar_producto(){
                if (this.timer) {
                    clearTimeout(this.timer);
                    this.timer = null;
                }
                this.timer = setTimeout(() => {
                     this.obtenerProductos();

                }, 500);
            },
            agregar(index){
                this.$emit('agregar_producto',this.listaProductos[index]);
            },
            resetModal(){
                this.buscar = '';
            },
        }
    }
</script>