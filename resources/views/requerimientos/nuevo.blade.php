@extends('layouts.main')
@section('titulo', 'Registrar')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-sm-12">
                <h3 class="titulo-admin-1">
                    <a href="{{url()->previous()}}"><i class="fas fa-arrow-circle-left"></i></a>
                    Nueva orden
                </h3>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 mt-4 mb-3">
                <div class="card">
                    <div class="card-header">
                        Detalle
                    </div>
                    <div class="card-body">
                        <b-button @click="abrir_modal('proveedor')"
                                  class="mb-4 mr-4" variant="primary"><i class="fas fa-search-plus"
                                                                         v-show="!mostrarSpinnerProveedor"></i>
                            <b-spinner v-show="mostrarSpinnerProveedor" small label="Loading..."></b-spinner>
                            Seleccionar proveedor
                        </b-button>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-lg-2">
                                    <input type="text" v-model="codigoProveedor" class="form-control mb-2"
                                           placeholder="Código" disabled readonly>
                                </div>
                                <div class="col-lg-6">
                                    <input type="text" v-model="nombreProveedor" class="form-control mb-2"
                                           placeholder="Proveedor" disabled readonly>
                                </div>
                            </div>
                        </div>
                        <b-button @click="abrir_modal('producto')"
                                  class="mt-4 mr-4" v-b-modal.modal-producto
                                  variant="primary"><i class="fas fa-plus" v-show="!mostrarSpinnerProducto"></i>
                            <b-spinner v-show="mostrarSpinnerProducto" small label="Loading..."></b-spinner>
                            Producto
                        </b-button>
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col" style="width: 10px"></th>
                                    <th scope="col" style="width: 250px">Producto</th>
                                    <th scope="col" style="width: 350px">Caracteristicas</th>
                                    <th scope="col" style="width: 100px">Costo</th>
                                    <th scope="col" style="width: 100px">Cantidad</th>
                                    <th style="display: none;" scope="col" style="width: 100px">Dscto</th>
                                    <th scope="col" style="width: 100px">Total</th>
                                    <th scope="col" style="width: 50px"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(producto,index) in productosSeleccionados" :key="producto.index">
                                    <td></td>
                                    <td style="display:none">@{{producto.idproducto}}</td>
                                    <td><input class="form-control" type="text" v-model="producto.nombre" :disabled="producto.idproducto!=-1"></td>
                                    <td><input class="form-control" type="text" v-model="producto.presentacion" :disabled="producto.idproducto!=-1"></td>
                                    <td><input @keyup="calcular(index)" class="form-control" type="text"
                                               v-model="producto.costo"></td>
                                    <td><input @keyup="calcular(index)" class="form-control" type="text"
                                               v-model="producto.cantidad"></td>
                                    <td style="display: none;"><input @keyup="calcular(index)" class="form-control" type="text" v-model="producto.descuento"></td>
                                    <td>@{{producto.total}}</td>

                                    <td class="">
                                        <a @click="borrarItemVenta(index)" href="javascript:void(0)">
                                            <button class="btn btn-danger" title="Borrar item"><i class="fas fa-trash"></i>
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
            <div class="col-lg-8 mb-5">
                <div class="card">
                    <div class="card-header">
                        Acciones
                    </div>
                    <div class="card-body text-center">
                        <b-button class="mb-2" :disabled="productosSeleccionados.length==0" @click="procesarRequerimiento"
                                  variant="success">
                            <i v-show="!mostrarProgresoGuardado" class="fas fa-save"></i>
                            <b-spinner v-show="mostrarProgresoGuardado" small label="Loading..." ></b-spinner> Guardar orden
                        </b-button>
                        <b-button class="mb-2" @click="limpiar" variant="danger"><i class="fas fa-ban"></i> Cancelar
                        </b-button>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-5">
                <div class="card">
                    <div class="card-header">
                        Totales
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <p>Subtotal: @{{ subtotal }}<br>
                                IGV: @{{ igv }}</p>
                            <p class="p-2 total-venta">@{{ moneda }} @{{ totalCompra }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--INICIO MODAL PROVEEDOR -->
    <b-modal size="lg" id="modal-proveedor" ref="modal-proveedor" ok-only @hidden="resetModal">
        <template slot="modal-title">
            Proveedor
            <b-button
                    class="mb-2" v-b-modal.modal-nuevo-proveedor
                    variant="primary"><i class="fas fa-plus"></i> Nuevo proveedor
            </b-button>
        </template>
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="buscar">Busca por nombre o razón social:</label>
                        <input @keyup="delay('proveedores')" v-model="buscar" type="text" name="buscar"
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
                            <tr v-for="(proveedor,index) in listaProveedores" :key="proveedor.idproveedor">
                                <td style="display:none">@{{proveedor.idproveedor}}</td>
                                <td style="width: 5%">@{{proveedor.codigo}}</td>
                                <td style="width: 30%">@{{proveedor.nombre}}</td>
                                <td style="width: 20%">@{{proveedor.num_documento}}</td>
                                <td style="width: 30%">@{{proveedor.direccion}}</td>
                                <td style="width: 5%" class="botones-accion">
                                    <a @click="agregarProveedor(index)" href="javascript:void(0)">
                                        <button class="btn btn-info" title="Seleccionar proveedor"><i
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
    <!--FIN MODAL PROVEEDOR -->
    <!--INICIO MODAL PRODUCTO -->
    <b-modal size="lg" id="modal-producto" ref="modal-producto" ok-only @hidden="resetModal">
        <template slot="modal-title">
            Seleccionar producto
        </template>
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="buscar">Busca por nombre o código:</label>
                        <input @keyup="delay('productos')" v-model="buscar" type="text" name="buscar"
                               placeholder="Buscar..." class="form-control" autocomplete="off">
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="table-responsive tabla-gestionar">
                        <table class="table table-striped table-hover table-sm">
                            <thead class="bg-custom-green">
                            <tr>
                                <th scope="col">Código</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Características</th>
                                <th scope="col">Stock</th>
                                <th scope="col">Precio</th>
                                <th scope="col"></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(producto,index) in listaProductos" :key="index">
                                <td style="display:none">@{{producto.idproducto}}</td>
                                <td>@{{producto.cod_producto}}</td>
                                <td>@{{producto.nombre}}</td>
                                <td>@{{producto.presentacion}}</td>
                                <td>@{{producto.stock}}</td>
                                <td>@{{producto.precio}}</td>
                                <td style="width: 5%" class="botones-accion">
                                    <a @click="agregarProducto(index)" href="javascript:void(0)">
                                        <button class="btn btn-info" title="Seleccionar producto"><i
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
    <!--FIN MODAL PRODUCTO -->
    <agregar-proveedor
            v-bind:url_guardar="'{{action('ProveedorController@store')}}'"
            v-on:agregar="agregarProveedorReciente">
    </agregar-proveedor>
@endsection
@section('script')
    <script>

        let app = new Vue({
            el: '.app',
            data: {
                accion: 'insertar',
                mostrarProgresoGuardado: false,


                listaProveedores: [],
                proveedorSeleccionado: {},
                nombreProveedor: '',
                codigoProveedor: '',
                buscar: '',
                mostrarSpinnerProveedor: false,

                listaProductos: [],
                productosSeleccionados: [],
                mostrarSpinnerProducto: false,

                listaDocumentos:[],
                comprobanteReferencia:'',

                totalCompra: 0.00,
                igv: 0.00,
                subtotal: 0.00,
                moneda: 'S/.'
            },
            created(){
                this.calcularTotalCompra();
            },
            methods: {
                obtenerProveedores(){
                    let _this = this;
                    axios.post('{{action('RequerimientoController@obtenerProveedores')}}', {
                        'textoBuscado': this.buscar
                    })
                        .then(function (response) {
                            let datos = response.data;
                            _this.listaProveedores = datos;
                        })
                        .catch(function (error) {
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });
                },
                agregarProveedor(index){
                    this.proveedorSeleccionado = this.listaProveedores[index];
                    this.codigoProveedor = this.proveedorSeleccionado['codigo'];
                    this.nombreProveedor = this.proveedorSeleccionado['nombre'];
                    this.$refs['modal-proveedor'].hide();
                },
                agregarProveedorReciente(nombre){
                    this.buscar = nombre;
                    this.obtenerProveedores();
                },
                agregarProductoReciente(nombre){
                    this.buscar = nombre;
                    this.obtenerProductos();
                },
                obtenerProductos(){
                    let _this = this;
                    axios.post('{{action('RequerimientoController@obtenerProductos')}}', {
                        'textoBuscado': this.buscar
                    })
                        .then(function (response) {
                            let datos = response.data;
                            _this.listaProductos = datos;
                        })
                        .catch(function (error) {
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });
                },
                abrir_modal(nombre){
                    if (nombre == 'producto') {
                        this.$refs['modal-producto'].show();
                        this.obtenerProductos();
                    } else if(nombre == 'proveedor') {
                        this.$refs['modal-proveedor'].show();
                        this.obtenerProveedores();
                    }
                },
                delay(func){
                    if (this.timer) {
                        clearTimeout(this.timer);
                        this.timer = null;
                    }
                    this.timer = setTimeout(() => {
                        if (func == 'productos') {
                            this.obtenerProductos();
                        } else if (func == 'proveedores') {
                            this.obtenerProveedores();
                        }

                    }, 500);
                },
                agregarProducto(index){
                    let productos = this.productosSeleccionados.push(this.listaProductos[index]);
                    //crear propiedades costo y cantidad en objeto productosSeleccionados:{} para usarlos
                    //más tarde al procesar la venta.
                    let i = productos - 1;
                    this.$set(this.productosSeleccionados[i], 'cantidad', 1);
                    this.$set(this.productosSeleccionados[i], 'descuento', '0.00');
                    this.$set(this.productosSeleccionados[i], 'total', this.productosSeleccionados[i]['costo']);

                    this.calcularTotalCompra();
                    this.listaProductos=[];
                    this.obtenerProductos();
                    //this.$refs['modal-producto'].hide();
                },
                calcular(index){
                    let producto = this.productosSeleccionados[index];
                    producto['total'] = (producto['costo'] * producto['cantidad']).toFixed(2);
                    this.calcularTotalCompra();
                },
                calcularTotalCompra(){
                    let suma = 0;

                    for (let producto of this.productosSeleccionados) {
                        suma += Number(producto.total);
                    }

                    this.totalCompra = suma.toFixed(2);
                    this.subtotal = (this.totalCompra / 1.18).toFixed(2);
                    this.igv = (this.totalCompra - this.subtotal).toFixed(2);

                },
                borrarItemVenta(index){
                    this.productosSeleccionados.splice(index, 1);
                    this.calcularTotalCompra();
                },
                resetModal(){
                    this.buscar = '';
                },
                procesarRequerimiento(){

                    this.mostrarProgresoGuardado = true;
                    axios.post('{{action('RequerimientoController@store')}}', {
                        'idproveedor': this.proveedorSeleccionado['idproveedor'],
                        'total_compra': this.totalCompra,
                        'items': JSON.stringify(this.productosSeleccionados)
                    })
                        .then(function (response) {
                            window.location.href='editar/'+response.data;
                        })
                        .catch(function (error) {
                            this.mostrarProgresoGuardado = false;
                            alert('Ha ocurrido un error al procesar el requerimiento');
                            console.log(error);
                        });
                },
                limpiar(){
                    this.proveedorSeleccionado = {};
                    this.nombreProveedor = '';
                    this.codigoProveedor='';
                    this.productosSeleccionados = [];
                    this.motivo='';
                    this.numeroGuia = '';
                    this.numeroOc = '';
                    this.moneda = 'S/.';
                    this.totalCompra = 0.00;
                    this.subtotal = 0.00;
                    this.igv = 0.00;
                    this.calcularTotalCompra();
                    this.obtenerCorrelativo();
                }
            }

        });
    </script>
@endsection