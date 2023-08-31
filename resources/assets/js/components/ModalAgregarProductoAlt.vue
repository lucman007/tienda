<template>
    <div>
        <b-modal no-d id="modal-agregar-producto-alt" ref="modal-agregar-producto-alt" hide-header :hide-footer="!isdesktop" size="xl" @shown="init" @hidden="closeModal">
            <div class="container-fluid">
                <div class="row">
                    <div class="order-2 order-lg-1 lista-menu" :class="isdesktop?'col-lg-12':'col-lg-7'">
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
                                    <div v-if="isdesktop" class="container" style="background: #474747; color: white;">
                                        <div class="row py-2">
                                            <div class="col-lg-6"><strong>Producto</strong></div>
                                            <div class="col-lg-2"><strong>Precio</strong></div>
                                            <div class="col-lg-2"><strong>Otro precio</strong></div>
                                            <div class="col-lg-2"><strong>Stock</strong></div>
                                        </div>
                                    </div>
                                    <div class="card-body" id="section_productos" style="height: 500px" @scroll="getProductos">
                                        <div class="row mb-5">
                                            <div class="col-lg-12">
                                                <b-list-group v-show="!mostrarSpinner">
                                                    <b-list-group-item variant="primary"
                                                                       v-bind:class='{"active_item": currentItem === index}'
                                                                       @click="agregarProducto(producto)"
                                                                       v-for="(producto, index) in productos" button
                                                                       :key="index">
                                                        <div class="row">
                                                            <div class="col-lg-6"><span class="codigo_producto">{{producto.cod_producto?producto.cod_producto+' - ':''}}</span>{{producto.nombre}}<span v-show="producto.tipo_producto==3" class="badge badge-warning"><i class="far fa-star"></i> KIT</span> <br>
                                                                <span style="font-size: 11px; color: #0b870b;" v-for="item in producto.items_kit">+ ({{ item.cantidad }}) {{item['nombre']}} </span>
                                                                <span class="presentacion">{{extracto(producto.presentacion)}}</span>
                                                            </div>
                                                            <div class="col-lg-2">{{producto.moneda+producto.precio}}</div>
                                                            <div class="col-lg-2">
                                                                <span v-show="producto.precioPorMayor">{{producto.moneda+producto.precioPorMayor}}</span> <i class="badge badge-pill badge-secondary">{{producto.etiqueta}}</i><br v-if="isdesktop">
                                                                <span v-show="producto.precioPorMayor" class="presentacion"> Desde {{producto.cantidadPorMayor}}{{producto.unidad}}</span>
                                                            </div>
                                                            <div class="col-lg-2"><span :class="'badge '+producto.badge_stock">{{producto.stock+' '+producto.unidad}}</span></div>
                                                            <div :id="'spinner_'+producto.idproducto" class="alert alert-success mb-0 spinner_add">
                                                                <strong style="font-size: 16px;">
                                                                    +{{countClicks}}
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
                    <div v-if="!isdesktop" class="col-lg-5 order-1 order-lg-2 platos-seleccionar mb-2">
                        <div class="row">
                            <div class="order-2 col-lg-12 mb-2">
                                <div class="card">
                                    <div class="card-body" style="overflow: auto;">
                                        <input id="buscador-movil" @keyup="buscarProducto" type="text" v-model="query" class="form-control py-md-1" autocomplete="nope"
                                               placeholder="Buscar producto...">
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
            <template v-if="isdesktop" #modal-footer="{cancel}">
                <b-button style="width: 190px" variant="danger" @click="cancel()">Salir</b-button>
            </template>
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
                countClicks:1,
                idproducto:null,
                onOpenModalNewItem:false
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
            init(){
                this.focusBuscador();
                let width = window.innerWidth
                    || document.documentElement.clientWidth
                    || document.body.clientWidth;

                if(width > 992) {

                    let height = window.innerHeight
                        || document.documentElement.clientHeight
                        || document.body.clientHeight;

                    let h_box_productos = height - 90 - 180;

                    let box_productos = document.getElementById('section_productos');
                    Object.assign(box_productos.style, {height:h_box_productos+'px'});
                }
            },
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
                if ((offset + top) >= bottom) {
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
            focusBuscador(){
                if (this.isdesktop) {
                    document.getElementById('buscador').focus();
                } else {
                    setTimeout(() => {
                        document.getElementById('buscador-movil').focus();
                    }, 50);
                }
                this.getMenuCategoria(this.idcategoria||-1);
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
                this.$refs['modal-agregar-producto-alt'].hide();
                this.idcategoria = -1;
            },
            agregarProducto(producto){
                let spinner = document.getElementById("spinner_"+producto.idproducto);
                let spinnerAll = document.querySelectorAll(".spinner_add");
                spinnerAll.forEach((spinner) => {
                    spinner.style.opacity = 0;
                });

                if(producto.idproducto === this.idproducto){
                    this.countClicks++;
                } else {
                    this.countClicks = 1;
                    this.idproducto = producto.idproducto;
                }

                if(this.countClicks > 1){
                    this.onOpenModalNewItem = false;
                }

                this.$emit('agregar',{'producto':producto,'newItem':this.onOpenModalNewItem});
                if(spinner){
                    if (this.timer) {
                        clearTimeout(this.timer);
                        this.timer = null;
                    }
                    this.timer = setTimeout(() => {
                        spinner.style.opacity='0';
                    }, 800);
                    spinner.style.opacity='1';
                }
            },
            extracto(string){
                string = _.truncate(string, {
                    'length': 250,
                    'separator': ' '
                });
                return string;
            }
        }
    }
</script>
<style>
    .spinner_add {
        padding: 5px 20px;
        position: absolute;
        right: 5px;
        top: 4px;
        -webkit-transition: 0.3s;
        transition: 0.3s;
        -webkit-transition-timing-function: ease-out;
        transition-timing-function: ease-out;
        opacity: 0;
        background: #ff9901;
        color: white;
        border-radius: 20px;
    }

    #modal-agregar-producto-alt .modal-content{
        background: #eceff1;
    }

    #modal-agregar-producto-alt .menu-categorias{
        padding: 5px;
        display: inline-grid;
        align-items: center;
    }

    #modal-agregar-producto-alt .menu-categorias span{
        text-align: center;
        margin: 0;
        word-break: break-word;
        padding: 15px 10px;
    }

    #modal-agregar-producto-alt .card-body{
        overflow-y: scroll;
    }

    #modal-agregar-producto-alt .categorias-container{
        height: 500px;
    }
    .badge {
        font-size: 85%;
        font-weight: normal;
    }
    .presentacion{
        font-size: 12px;
        color: #45463e;
    }
    .active_item {
        background-color: #e1e1e1;
        color: black;
    }

    .list-group-item:hover {
        background-color: #e1e1e1 !important;
        color: black;
    }
    .codigo_producto{
        color:#8b8b8b;
    }
</style>