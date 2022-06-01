@extends('layouts.main')
@section('titulo', 'Requerimiento')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-sm-12">
                <h3 class="titulo-admin-1">Orden de compra N° {{$requerimiento['idrequerimiento']}}</h3>
                <b-button href="{{action('RequerimientoController@index')}}" class="mr-2"  variant="primary"><i class="fas fa-list"></i> Ver requerimientos</b-button>
                <b-button href="{{action('RequerimientoController@nuevo_requerimiento')}}" class="mr-2"  variant="primary"><i class="fas fa-plus"></i> Nuevo requerimiento</b-button>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 mt-4 mb-3">
                <div class="card">
                    <div class="card-header">
                        Detalle
                    </div>
                    <div class="card-body">
                        <b-button v-if="editar" @click="abrir_modal('proveedor')"
                                  class="mb-4 mr-4" variant="primary"><i class="fas fa-search-plus"
                                                                         v-show="!mostrarSpinnerProveedor"></i>
                            <b-spinner v-show="mostrarSpinnerProveedor" small label="Loading..."></b-spinner>
                            Seleccionar proveedor
                        </b-button>
                        <div class="form-group" v-if="editar">
                            <div class="row">
                                <div class="col-lg-2">
                                    <input type="text" v-model="codigoProveedor" class="form-control mb-2"
                                           placeholder="Código" disabled readonly>
                                </div>
                                <div class="col-lg-5">
                                    <input type="text" v-model="nombreProveedor" class="form-control mb-2"
                                           placeholder="Proveedor" disabled readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row" v-if="!editar">
                            <div class="col-lg-6">
                                <p>Código: @{{codigoProveedor}} <br>
                                    Proveedor: @{{nombreProveedor}} <br>
                                    Ruc: @{{rucProveedor}} </p>
                            </div>
                            <div class="col-lg-6">
                                <p>Fecha: {{date("d-m-Y H:i:s",strtotime($requerimiento->fecha_requerimiento))}} <br>
                                    Estado: <span class="badge" :class="[estado=='PENDIENTE' ? 'badge-warning' : 'badge-success']">@{{estado}}</span><br>
                                    Comprobante: {{$requerimiento['num_comprobante']}} </p>
                            </div>
                        </div>
                        <b-button v-if="editar" @click="abrir_modal('producto')"
                                  class="mt-4 mr-4" v-b-modal.modal-producto
                                  variant="primary"><i class="fas fa-plus" v-show="!mostrarSpinnerProducto"></i>
                            <b-spinner v-show="mostrarSpinnerProducto" small label="Loading..."></b-spinner>
                            Producto
                        </b-button>
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm">
                                <thead v-if="!editar" class="bg-custom-green">
                                <tr>
                                    <th scope="col" style="width: 10px"></th>
                                    <th colspan="5">Requerimiento</th>
                                    <th colspan="4" style="background:rgb(252, 255, 36)">Recepción</th>
                                </tr>
                                <tr>
                                    <th scope="col" style="width: 10px"></th>
                                    <th scope="col" style="width: 250px">Producto</th>
                                    <th scope="col" style="width: 350px">Caracteristicas</th>
                                    <th scope="col" style="width: 100px">Costo</th>
                                    <th scope="col" style="width: 100px">Cantidad</th>
                                    <th scope="col" style="width: 100px">Total</th>
                                    <th scope="col" style="width: 100px; background:rgb(252, 255, 36)">Costo</th>
                                    <th scope="col" style="width: 100px; background:rgb(252, 255, 36)">Cantidad</th>
                                    <th scope="col" style="width: 100px; background:rgb(252, 255, 36)">Total</th>
                                    <th v-if="editar" scope="col" style="width: 50px; background:rgb(252, 255, 36)"></th>
                                </tr>
                                </thead>
                                <tbody v-if="!editar">
                                <tr v-for="(producto,index) in productosSeleccionados" :key="producto.index">
                                    <td></td>
                                    <td style="display:none">@{{producto.idproducto}}</td>
                                    <td v-if="producto.idproducto!=-1">@{{ producto.nombre }}</td>
                                    <td v-if="producto.idproducto==-1">@{{ producto.detalle.producto_nombre }}</td>
                                    <td>@{{ producto.detalle.descripcion}}</td>
                                    <td>@{{ producto.detalle.monto }}</td>
                                    <td>@{{ producto.detalle.cantidad }}</td>
                                    <td>@{{ producto.detalle.total }}</td>
                                    <td v-if="estado!='RECIBIDO'"><input @keyup="calcular(index)" class="form-control" type="text"
                                               v-model="producto.detalle.monto_recepcion"></td>
                                    <td v-if="estado!='RECIBIDO'"><input @keyup="calcular(index)" class="form-control" type="text"
                                               v-model="producto.detalle.cantidad_recepcion"></td>
                                    <td v-if="estado=='RECIBIDO'">@{{producto.detalle.monto_recepcion}}</td>
                                    <td v-if="estado=='RECIBIDO'">@{{ producto.detalle.cantidad_recepcion }}</td>
                                    <td>@{{producto.detalle.total_recepcion}}</td>
                                </tr>
                                <tr>
                                    <th style="background: #cef5d6;" colspan="4"></th>
                                    <th style="background: #cef5d6;">Subtotal:</th>
                                    <th style="background: #cef5d6;">@{{ subtotal }}</th>
                                    <th style="background:rgb(245, 251, 214)"></th>
                                    <th style="background:rgb(245, 251, 214)">Subtotal:</th>
                                    <th style="background:rgb(245, 251, 214)">@{{ subtotal_recepcion }}</th>
                                    <th v-if="editar" style="background:rgb(245, 251, 214)"></th>
                                </tr>
                                <tr>
                                    <th style="background: #cef5d6;" colspan="4"></th>
                                    <th style="background: #cef5d6;">Igv:</th>
                                    <th style="background: #cef5d6;">@{{ igv }}</th>
                                    <th style="background:rgb(245, 251, 214)"></th>
                                    <th style="background:rgb(245, 251, 214)">Igv:</th>
                                    <th style="background:rgb(245, 251, 214)">@{{ igv_recepcion }}</th>
                                    <th v-if="editar" style="background:rgb(245, 251, 214)"></th>
                                </tr>
                                <tr>
                                    <th style="background: #cef5d6;" colspan="4"></th>
                                    <th style="background: #cef5d6;">Total:</th>
                                    <th style="background: #cef5d6;">@{{ moneda }} @{{ totalCompra }}</th>
                                    <th style="background:rgb(245, 251, 214)"></th>
                                    <th style="background:rgb(245, 251, 214)">Total:</th>
                                    <th style="background:rgb(245, 251, 214)">@{{ moneda }} @{{ totalCompra_recepcion }}</th>
                                    <th v-if="editar" style="background:rgb(245, 251, 214)"></th>
                                </tr>
                                </tbody>
                                <thead v-if="editar" class="bg-custom-green">
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
                                <tbody v-if="editar">
                                <tr v-for="(producto,index) in productosSeleccionados" :key="producto.index">
                                    <td></td>
                                    <td style="display:none">@{{producto.idproducto}}</td>
                                    <td v-if="producto.idproducto!=-1"><input  class="form-control" type="text" v-model="producto.nombre" :disabled="producto.idproducto!=-1"></td>
                                    <td v-if="producto.idproducto==-1"><input class="form-control" type="text" v-model="producto.detalle.producto_nombre"></td>
                                    <td><input class="form-control" type="text" v-model="producto.detalle.descripcion" :disabled="producto.idproducto!=-1"></td>
                                    <td><input @keyup="calcular(index)" class="form-control" type="text"
                                               v-model="producto.detalle.monto"></td>
                                    <td><input @keyup="calcular(index)" class="form-control" type="text"
                                               v-model="producto.detalle.cantidad"></td>
                                    <td>@{{producto.detalle.total}}</td>
                                    <td class="">
                                        <a @click="borrarItemVenta(index)" href="javascript:void(0)">
                                            <button class="btn btn-danger" title="Borrar item"><i class="fas fa-trash"></i>
                                            </button>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th style="background: #cef5d6;" colspan="4"></th>
                                    <th style="background: #cef5d6;">Subtotal:</th>
                                    <th style="background: #cef5d6;">@{{ subtotal }}</th>
                                    <th v-if="editar" style="background: #cef5d6;"></th>
                                </tr>
                                <tr>
                                    <th style="background: #cef5d6;" colspan="4"></th>
                                    <th style="background: #cef5d6;">Igv:</th>
                                    <th style="background: #cef5d6;">@{{ igv }}</th>
                                    <th v-if="editar" style="background: #cef5d6;"></th>
                                </tr>
                                <tr>
                                    <th style="background: #cef5d6;" colspan="4"></th>
                                    <th style="background: #cef5d6;">Total:</th>
                                    <th style="background: #cef5d6;">@{{ moneda }} @{{ totalCompra }}</th>
                                    <th v-if="editar" style="background: #cef5d6;"></th>
                                </tr>
                                <tbody>
                            </table>
                        </div>
                        <div class="dropdown-divider"></div>
                        <div v-if="estado=='PENDIENTE'" v-show="!editar" class="col-lg-4 offset-lg-8 mt-3">
                            <div class="form-group">
                                <label for="num_compronbante">N° comprobante de compra:</label>
                                <input name="num_comprobante" class="form-control" type="text" v-model="num_comprobante_compra" placeholder="">
                                <button @click="recibir_requerimiento" class="boton_adjunto btn btn-warning"><i v-show="!mostrarProgresoAprobacion" class="fas fa-check"></i>
                                    <b-spinner v-show="mostrarProgresoAprobacion" small label="Loading..." ></b-spinner> Recibir</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 mb-5">
                <div class="card">
                    <div class="card-header">
                        Acciones
                    </div>
                    <div class="card-body text-center">
                        <b-button v-if="estado!='RECIBIDO'" :disabled="!editar || productosSeleccionados.length==0" class="mb-2" @click="actualizarRequerimiento"
                                  variant="success"><i v-show="!mostrarProgresoGuardado" class="fas fa-save"></i><b-spinner v-show="mostrarProgresoGuardado" small label="Loading..." ></b-spinner> Guardar cambios
                        </b-button>
                        <b-button v-show="!editar" v-if="estado!='RECIBIDO'" @click="editar=!editar" class="mb-2"  variant="info">
                            <i class="fas fa-edit"></i> Editar
                        </b-button>
                        <b-button v-if="editar" @click="cancelar_edicion" class="mb-2" variant="info"><i class="fas fa-edit"></i> Cancelar edición
                        </b-button>
                        <b-button :disabled="editar" class="mb-2" target="_blank" href="{{url('requerimientos/imprimir').'/'.$requerimiento['idrequerimiento']}}" variant="secondary"><i class="fas fa-print"></i> Imprimir
                        </b-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--INICIO MODAL CLIENTE -->
    <b-modal size="lg" id="modal-proveedor" ref="modal-proveedor" ok-only @hidden="resetModal">
        <template slot="modal-title">
            Seleccionar proveedor
        </template>
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="buscar">Busca por nombre o raón social:</label>
                        <input @keyup="delay('proveedors')" v-model="buscar" type="text" name="buscar"
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
                                <th scope="col">Placa</th>
                                <th scope="col">Vehículo</th>
                                <th scope="col">Proveedor</th>
                                <th scope="col">Dirección</th>
                                <th scope="col"></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(datos,index) in listaProveedor" :key="datos.index">
                                <td style="display:none"></td>
                                <td style="width: 5%">@{{datos.matricula}}</td>
                                <td style="width: 30%">@{{datos.marca}} @{{datos.modelo}}</td>
                                <td style="width: 20%">@{{datos.nombre}}</td>
                                <td style="width: 30%">@{{datos.direccion}}</td>
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
                                <th scope="col">Costo</th>
                                <th scope="col"></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(producto,index) in listaProductos" :key="producto.idproducto">
                                <td style="display:none">@{{producto.idproducto}}</td>
                                <td>@{{producto.cod_producto}}</td>
                                <td>@{{producto.nombre}}</td>
                                <td>@{{producto.presentacion}}</td>
                                <td>@{{producto.costo}}</td>
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
    <!--FIN MODAL CLIENTE -->
    <agregar-producto
            v-bind:url_guardar_producto="'{{action('ProductoController@store')}}'"
            v-bind:url_categorias="'{{action('CategoriaController@show')}}'"
            v-on:agregar="agregarProductoReciente">
    </agregar-producto>
@endsection
@section('script')
    <script>

        let app = new Vue({
            el: '.app',
            data: {
                editar:false,
                accion: 'insertar',
                mostrarProgresoGuardado: false,
                mostrarProgresoAprobacion: false,
                mostrarProgresoRecepcion: false,
                listaProveedor: [],
                proveedorSeleccionado: {
                    'idproveedor':'<?php echo $requerimiento['idproveedor'] ?>',
                },
                codigoProveedor:'<?php echo $requerimiento['proveedor']['codigo'] ?>',
                rucProveedor:'<?php echo $requerimiento['proveedor']['num_documento'] ?>',
                nombreProveedor: '<?php echo $requerimiento['proveedor']['persona']['nombre'] ?>',
                buscar: '',
                mostrarSpinnerProveedor: false,
                listaProductos: [],
                productosSeleccionados: <?php echo $requerimiento['productos'] ?>,
                mostrarSpinnerProducto: false,
                totalCompra: 0.00,
                igv: 0.00,
                subtotal: 0.00,
                totalCompra_recepcion: 0.00,
                igv_recepcion: 0.00,
                subtotal_recepcion: 0.00,
                moneda: 'S/.',
                observaciones: '<?php echo $requerimiento['observaciones'] ?>',
                estado:'<?php echo $requerimiento['estado'] ?>',
                num_comprobante_compra:''
            },
            created(){
                this.calcularTotalPorItem();
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
                    } else {
                        this.$refs['modal-documento'].show();
                        this.obtenerDocumentos();
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
                        } else {
                            this.obtenerDocumentos();
                        }

                    }, 500);
                },
                agregarProducto(index){
                    let productos = this.productosSeleccionados.push({'detalle':this.listaProductos[index]});
                    let i = productos - 1;
                    this.$set(this.productosSeleccionados[i], 'idproducto', this.listaProductos[index]['idproducto']);
                    this.$set(this.productosSeleccionados[i], 'nombre', this.listaProductos[index]['nombre']);
                    this.$set(this.productosSeleccionados[i]['detalle'], 'descripcion', this.listaProductos[index]['presentacion']);
                    this.$set(this.productosSeleccionados[i]['detalle'], 'monto', this.listaProductos[index]['costo']);
                    this.$set(this.productosSeleccionados[i]['detalle'], 'cantidad', 1);
                    this.$set(this.productosSeleccionados[i]['detalle'], 'descuento', '0.00');
                    this.$set(this.productosSeleccionados[i]['detalle'], 'total', this.listaProductos[index]['costo']);
                    this.$set(this.productosSeleccionados[i]['detalle'], 'cantidad_recepcion', 1);
                    this.$set(this.productosSeleccionados[i]['detalle'], 'monto_recepcion', this.listaProductos[index]['costo']);
                    this.$set(this.productosSeleccionados[i]['detalle'], 'total_recepcion', this.listaProductos[index]['costo']);

                    this.calcularTotalCompra();
                    this.listaProductos=[];
                    this.obtenerProductos();
                },
                calcular(index){
                    let producto = this.productosSeleccionados[index];
                    producto['detalle']['total'] = (producto['detalle']['monto'] * producto['detalle']['cantidad']-producto['detalle']['descuento']).toFixed(2);
                    producto['detalle']['total_recepcion'] = (producto['detalle']['monto_recepcion'] * producto['detalle']['cantidad_recepcion']-producto['detalle']['descuento']).toFixed(2);
                    this.calcularTotalCompra();
                },
                calcularTotalPorItem(){
                    for (let producto of this.productosSeleccionados) {
                        producto['detalle']['total']=(producto['detalle']['cantidad']*producto['detalle']['monto']-producto['detalle']['descuento']).toFixed(2);
                        producto['detalle']['total_recepcion']=(producto['detalle']['cantidad_recepcion']*producto['detalle']['monto_recepcion']-producto['detalle']['descuento']).toFixed(2);
                    }
                },
                calcularTotalCompra(){
                    let suma = 0;
                    let suma_recepcion= 0;

                    //Calcular total requerimiento
                    for (let producto of this.productosSeleccionados) {
                        suma += Number(producto.detalle.total);
                    }
                    this.totalCompra = suma.toFixed(2);
                    this.subtotal = (this.totalCompra / 1.18).toFixed(2);
                    this.igv = (this.totalCompra - this.subtotal).toFixed(2);

                    //Calcular total recepcion
                    for (let producto of this.productosSeleccionados) {
                        suma_recepcion += Number(producto.detalle.total_recepcion);
                    }
                    this.totalCompra_recepcion = suma_recepcion.toFixed(2);
                    this.subtotal_recepcion = (this.totalCompra_recepcion / 1.18).toFixed(2);
                    this.igv_recepcion = (this.totalCompra_recepcion - this.subtotal_recepcion).toFixed(2);

                },
                borrarItemVenta(index){
                    this.productosSeleccionados.splice(index, 1);
                    this.calcularTotalCompra();
                },
                resetModal(){
                    this.buscar = '';
                },
                actualizarRequerimiento(){
                    let _this = this;
                    this.mostrarProgresoGuardado = true;
                    console.log(this.productosSeleccionados)
                    axios.post('{{action('RequerimientoController@update')}}', {
                        'idrequerimiento': <?php echo $requerimiento['idrequerimiento'] ?>,
                        'idproveedor': this.proveedorSeleccionado['idproveedor'],
                        'total_compra': this.totalCompra,
                        'observaciones':this.observaciones,
                        'items': JSON.stringify(this.productosSeleccionados)
                    })
                        .then(function (response) {
                            location.reload();
                        })
                        .catch(function (error) {
                            alert('Ha ocurrido un error al procesar la venta');
                            console.log(error);
                        });
                },
                recibir_requerimiento(){

                    if(confirm('Este procedimiento no se puede revertir, asegúrese que los datos ingresados sean correctos. ¿Desea continuar?')){
                        this.mostrarProgresoAprobacion=true;
                        axios.post('{{action('RequerimientoController@recibir')}}', {
                            'idrequerimiento': <?php echo $requerimiento['idrequerimiento'] ?>,
                            'idproveedor': this.proveedorSeleccionado['idproveedor'],
                            'num_comprobante':this.num_comprobante_compra,
                            'total_compra': this.totalCompra,
                            'observaciones':this.observaciones,
                            'items': JSON.stringify(this.productosSeleccionados)
                        })
                            .then(function (response) {
                                location.reload();
                            })
                            .catch(function (error) {
                                alert('Ha ocurrido un error al procesar la venta');
                                console.log(error);
                            });
                    }

                },
                cancelar_edicion(){
                    location.reload();
                },
                limpiar(){
                    this.proveedorSeleccionado = {};
                    this.nombreProveedor = '';
                    this.codigoProveedor='';
                    this.productosSeleccionados = [];
                    this.numeroGuia = '';
                    this.numeroOc = '';
                    this.moneda = 'S/.';
                    this.totalCompra = 0.00;
                    this.subtotal = 0.00;
                    this.igv = 0.00;
                    this.totalCompra_recepcion = 0.00;
                    this.subtotal_recepcion = 0.00;
                    this.igv_recepcion = 0.00;
                    this.matriculaVehiculo='';
                    this.marcaVehiculo='';
                    this.observaciones='';
                    this.num_comprobante_compra='';
                    this.calcularTotalCompra();
                }
            }

        });
    </script>
@endsection