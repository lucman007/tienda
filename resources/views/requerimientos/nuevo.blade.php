@extends('layouts.main')
@section('titulo', 'Requerimiento')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-sm-12">
                <h3 class="titulo-admin-1">
                    <a href="{{url('/requerimientos')}}"><i class="fas fa-arrow-circle-left"></i></a>
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
                        <div class="row">
                            <div class="col-lg-6">
                                <autocomplete-cliente v-on:agregar_cliente="agregarProveedor"
                                                      v-on:borrar_cliente="borrarProveedor" v-bind:es_proveedores="true"
                                                      ref="suggestCliente"></autocomplete-cliente>
                            </div>
                            <div class="col-lg-3 no-gutters">
                                <b-button v-b-modal.modal-nuevo-proveedor
                                          class="mb-4 mt-2 mt-lg-0 float-right float-lg-left" variant="primary"><i
                                            class="fas fa-plus"
                                            v-show="!mostrarSpinnerProveedor"></i>
                                    <b-spinner v-show="mostrarSpinnerProveedor" small label="Loading..."></b-spinner>
                                    Nuevo proveedor
                                </b-button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-7">
                                <autocomplete ref="suggest" v-on:agregar_producto="agregarProducto"></autocomplete>
                            </div>
                            <div class="col-lg-3">
                                <b-button class="mb-4 mt-2 mt-lg-0 float-right float-lg-left"  v-b-modal.modal-nuevo-producto
                                          variant="primary"><i class="fas fa-plus" v-show="!mostrarSpinnerProducto"></i>
                                    <b-spinner v-show="mostrarSpinnerProducto" small label="Loading..."></b-spinner>
                                    Nuevo producto
                                </b-button>
                            </div>
                        </div>
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
    <agregar-proveedor
            v-on:agregar="agregarProveedorNuevo">
    </agregar-proveedor>
    <agregar-producto
            v-bind:ultimo_id="1"
            v-bind:tipo_cambio_compra="{{cache('opciones')['tipo_cambio_compra']}}"
            v-on:agregar="agregarProductoNuevo">
    </agregar-producto>
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

                listaProductos: [],
                productosSeleccionados: [],
                mostrarSpinnerProducto: false,

                listaDocumentos:[],
                comprobanteReferencia:'',

                totalCompra: 0.00,
                igv: 0.00,
                subtotal: 0.00,
                moneda: 'S/',
                mostrarSpinnerProveedor: false,
            },
            created(){
                this.calcularTotalCompra();
            },
            methods: {
                agregarProveedor(obj){
                    this.proveedorSeleccionado = obj;
                    this.nombreCliente = this.proveedorSeleccionado['num_documento']+' - '+this.proveedorSeleccionado['nombre'];
                },
                borrarProveedor(){
                    this.proveedorSeleccionado = {};
                },
                agregarProductoNuevo(nombre){
                    this.buscar = nombre;
                },
                agregarProveedorNuevo(obj){
                    if(this.$refs['suggestCliente']){
                        this.$refs['suggestCliente'].agregarCliente(obj);
                    } else {
                        this.agregarProveedor(obj)
                    }
                },
                agregarProducto(obj){
                    let productos = this.productosSeleccionados.push(Object.assign({}, obj));
                    let i = productos - 1;
                    let producto = this.productosSeleccionados[i];

                    this.$set(producto, 'cantidad', 1);
                    this.$set(producto, 'descuento', '0.00');
                    this.$set(producto, 'total', producto['costo']);

                    this.calcularTotalCompra();
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
                    if (this.validar()) {
                        return;
                    }
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
                validar(){
                    let errorVenta = 0;
                    let errorDatosVenta = [];
                    let errorString = '';

                    if (Object.keys(this.proveedorSeleccionado).length == 0) errorDatosVenta.push('*Debes ingresar un proveedor');

                    if (errorDatosVenta.length) {
                        errorVenta = 1;
                        for (let error of errorDatosVenta) {
                            errorString += error + '\n';
                        }
                        this.alerta(errorString);
                    }

                    return errorVenta;
                },
                alerta(texto){
                    this.$swal({
                        position: 'top',
                        icon: 'warning',
                        title: texto,
                        timer: 6000,
                        toast:true,
                        confirmButtonColor: '#007bff',
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
                }
            }

        });
    </script>
@endsection