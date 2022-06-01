<template>
    <div>
        <b-modal no-d id="modal-agregar-producto" ref="modal-agregar-producto" hide-footer hide-header size="xl" @shown="focusBuscador" @hidden="closeModal">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-7 order-2 order-lg-1 lista-menu">
                        <div class="row">
                            <div v-if="isdesktop" class="col-lg-12 mb-2">
                                <div class="card">
                                    <div class="card-body" style="overflow: auto;">
                                        <input type="text" v-model="query" class="form-control"
                                               placeholder="Buscar producto..." id="buscador" autocomplete="off" v-on:keyup="navigate">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body" id="section_productos" style="height: 500px" @scroll="getProductos">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <b-list-group v-show="!mostrarSpinner">
                                                    <b-list-group-item variant="primary"
                                                                       v-bind:class='{"active_item": currentItem === index}'
                                                                       @click="agregarProducto(producto)"
                                                                       v-for="(producto, index) in productos" button
                                                                       :key="index">
                                                        <div class="row">
                                                            <div class="col-lg-6">{{producto.nombre}} <br>
                                                                <span class="presentacion">{{producto.presentacion}}</span>
                                                            </div>
                                                            <div class="col-lg-3">{{producto.moneda+producto.precio}}</div>
                                                            <div class="col-lg-3"><span :class="'badge '+producto.badge_stock">{{producto.stock+producto.unidad}}</span></div>
                                                            <div :id="'spinner_'+producto.idproducto" class="alert alert-success mb-0 d-none"
                                                                 style="padding: 5px 20px;position: absolute;right: 5px;bottom: 4px;">
                                                                <strong style="font-size: 16px;">
                                                                    <i class="far fa-check-circle"></i>
                                                                </strong>
                                                            </div>
                                                        </div>
                                                    </b-list-group-item>
                                                </b-list-group>
                                            </div>
                                            <div class="text-center col-lg-12"
                                                 v-show="productos.length <= 0 && !mostrarSpinner">
                                                <h5 class="mt-4">No hay productos para mostrar</h5>
                                            </div>
                                            <div class="loader" v-show="mostrarSpinner">
                                                <b-spinner label="Cargando..."></b-spinner>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5 order-1 order-lg-2 platos-seleccionar mb-2">
                        <div class="row">
                            <div v-if="!isdesktop" class="order-2 col-lg-12 mb-2">
                                <div class="card">
                                    <div class="card-body" style="overflow: auto;">
                                        <input @keyup="buscarProducto" type="text" v-model="query" class="form-control py-md-1" autocomplete="nope"
                                               placeholder="Buscar producto..." @focus="ocultarCategorias">
                                        <i class="fas fa-times-circle borrarProducto" v-show="!showCategorias" style="right: 30px;"
                                           v-on:click="mostrarCategorias"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 order-3 order-lg-1" v-show="showCategorias">
                                <div class="card">
                                    <div class="card-body categorias-container">
                                        <div class="row">
                                            <div v-for="categoria in categorias" class="col-6 col-sm-4 col-lg-4 menu-categorias" @click="getMenuCategoria(categoria.idcategoria)">
                                                <span :style="{'background':categoria.color,'font-size':categoria.nombre.length>33?'10px':'13px'}">
                                                        {{categoria.nombre}}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 offset-lg-6 col-lg-6 mt-3 mb-3 order-1 order-lg-2">
                                <b-button class="float-right" style="width: 100%;height: 60px;" @click="closeModal"
                                          variant="danger"><i class="fas fa-times-circle"></i>
                                    Salir
                                </b-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </b-modal>
    </div>
</template>
<script>

    export default {
        name: 'modal-agregar-producto',
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
                if(this.isdesktop){
                    let input = document.getElementById("buscador");
                    input.focus();
                    this.getMenuCategoria(this.idcategoria||-1);
                }
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
                            });
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
        background-color: #9fcdff;
        color: white;
    }

    .list-group-item:hover {
        background-color: #9fcdff;
        color: white
    }
</style>