<template>
    <div>
        <b-modal no-d id="modal-agregar-producto-alt" ref="modal-agregar-producto-alt" ok-only hide-header size="xl" @shown="focusBuscador" @hidden="closeModal">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-6 mt-4">
                        <input type="text" v-model="query" class="form-control"
                               placeholder="Busca por nombre o código..." id="buscador" autocomplete="off" v-on:keyup="navigate">
                    </div>
                    <div class="col-lg-12" style="height: 400px; overflow-y: scroll">
                        <div class="table-responsive mt-4">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col">Código</th>
                                    <th style="width: 40%" scope="col">Nombre y caracteristicas</th>
                                    <th scope="col">Stock</th>
                                    <th scope="col">Precio</th>
                                    <th scope="col">Por mayor</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-bind:class='{"active_item": currentItem === index}' v-show="!mostrarSpinner" v-for="(producto,index) in productos" :key="index" @click="agregarProducto(producto)" style="cursor: pointer">
                                    <td>{{producto.cod_producto}}</td>
                                    <td><strong>{{producto.nombre}}</strong> <br> {{producto.presentacion}}</td>
                                    <td v-show="producto.tipo_producto===1"><span :class="'badge '+producto.badge_stock">{{producto.stock+producto.unidad}}</span></td>
                                    <td v-show="producto.tipo_producto==2">-</td>
                                    <td>{{producto.moneda+producto.precio}}</td>
                                    <td v-if="producto.precioPorMayor">{{producto.moneda+producto.precioPorMayor}}</td>
                                    <td v-else>-</td>
                                </tr>
                                <tr v-show="mostrarSpinner">
                                    <td colspan="6" class="text-center"><b-spinner label="Cargando..."></b-spinner></td>
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
        name: 'modal-agregar-producto-alt',
        props: ['categorias','isdesktop'],
        data() {
            return {
                productos:[],
                productoSeleccionado:{},
                mostrarSpinner:false,
                mostrarProgreso:false,
                query:'',
                showCategorias:true,
                idcategoria: -1,
                currentItem: 0,
            }
        },
        created() {
            this.handler = function(e){
                if((e.code=='ArrowUp' || e.code=='ArrowDown' || e.code=='Enter' || e.code=='NumpadEnter')){
                    e.view.event.preventDefault();
                    let input = document.getElementById("buscador");
                    input.focus();
                }
            };
            window.addEventListener('keydown', this.handler);

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
                        if (this.currentItem < (this.productos.length - 1)) {
                            this.currentItem++;
                        }
                        break;
                    case 'Enter':
                    case 'NumpadEnter':
                        if (this.productos.length > 0) {
                            this.agregarProducto(this.productos[this.currentItem]);
                        } else{
                            axios.get('/helper/agregar-producto' + '/' + this.query)
                                .then(response => {
                                    this.productos = response.data;
                                    if((Object.keys(this.productos).length === 0)){
                                        alert('No se ha encontrado el producto con el código marcado');
                                    } else{
                                        this.agregarProducto(this.productos[this.currentItem]);
                                    }
                                });
                        }
                        break;
                    default:
                        this.currentItem = 0;
                        this.buscarProducto();

                }

            },
            getProductos(){
                let div = document.getElementById("section_productos");
                let bottom = div.scrollHeight;
                let offset = div.offsetHeight;
                let top = div.scrollTop;
                if ((offset + top) === bottom) {
                    axios.post('/pedidos/productos_por_categoria', {
                        'idcategoria': this.idcategoria,
                        'skip':this.productos.length || 0
                    })
                        .then(response => {
                            this.productos = this.productos.concat(response.data);
                        })
                        .catch(error => {
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });
                }
            },
            ocultarCategorias(){
                this.showCategorias=false;
            },
            mostrarCategorias(){
                this.showCategorias=true;
                this.query = '';
            },
            focusBuscador(){
                let input = document.getElementById("buscador");
                input.focus();
            },
            getMenuCategoria(val){
                this.mostrarSpinner = true;
                this.idcategoria = val;
                axios.post('/pedidos/productos_por_categoria', {
                    'idcategoria': val,
                })
                    .then(response => {
                        this.productos = response.data;
                        this.mostrarSpinner = false;
                    })
                    .catch(error => {
                        alert('Ha ocurrido un error.');
                        console.log(error);
                        this.mostrarSpinner = false;
                    });
            },
            buscarProducto(){
                this.mostrarSpinner = true;
                if (this.timer) {
                    clearTimeout(this.timer);
                    this.timer = null;
                }
                this.timer = setTimeout(() => {
                    this.productos = [];
                    if (this.query.length > 1) {
                        axios.get('/helper/obtener-productos' + '/' + this.query)
                            .then(response => {
                                this.productos = response.data;
                                this.mostrarSpinner = false;
                            });
                    } else {
                        this.mostrarSpinner = false;
                    }
                }, 400);
            },
            closeModal(){
                this.$emit('guardar');
                this.productoSeleccionado = {};
                this.query='';
                this.showCategorias = true;
                this.$refs['modal-agregar-producto'].hide();
            },
            agregarProducto(producto){
                let spinner = document.getElementById("spinner_"+producto.idproducto);
                this.$emit('agregar',producto);
                setTimeout(() => {
                    spinner.classList.remove('d-inline-block');
                    spinner.classList.add('d-none');
                },400);
                spinner.classList.remove('d-none');
                spinner.classList.add('d-inline-block');
            },
        }
    }
</script>
<style>
    #modal-agregar-producto .modal-content{
        background: #eceff1;
    }

    #modal-agregar-producto .menu-categorias{
        padding: 5px;
        display: inline-grid;
        align-items: center;
    }

    #modal-agregar-producto .menu-categorias span{
        text-align: center;
        margin: 0;
        word-break: break-word;
        padding: 15px 10px;
    }

    #modal-agregar-producto .card-body{
        overflow-y: scroll;
    }

    #modal-agregar-producto .categorias-container{
        height: 500px;
    }
    .badge {
        font-size: 85%;
        font-weight: normal;
    }
    .presentacion{
        font-size: 12px;
        color: #7e7d7d;
    }
    .active_item {
        background-color: #2a90ff !important;
        color: white;
    }

    .list-group-item:hover {
        background-color: #9fcdff;
        color: white
    }
</style>