@extends('layouts.main')
@section('titulo', 'Requerimiento')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-sm-12">
                <h3 class="titulo-admin-1">Orden de compra N° {{$requerimiento->correlativo}}</h3>
                <b-button href="{{action('RequerimientoController@index')}}" class="mr-2"  variant="primary"><i class="fas fa-list"></i> Ver órdenes</b-button>
                <b-button href="{{action('RequerimientoController@nuevo_requerimiento')}}" class="mr-2"  variant="primary"><i class="fas fa-plus"></i> Nueva orden</b-button>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 mt-4 mb-3">
                <div class="card">
                    <div class="card-header">
                        Detalle
                    </div>
                    <div class="card-body">
                        <div class="row" v-show="editar">
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
                        <div class="row" v-show="editar">
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
                        <div class="row" v-show="!editar">
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
                                    <td>@{{ producto.nombre }}</td>
                                    <td>@{{ producto.descripcion}}</td>
                                    <td>@{{ producto.costo }}</td>
                                    <td>@{{ producto.cantidad }}</td>
                                    <td>@{{ producto.total }}</td>
                                    <td v-if="estado!='RECIBIDO'"><input @keyup="calcular(index)" class="form-control" type="text"
                                               v-model="producto.monto_recepcion"></td>
                                    <td v-if="estado!='RECIBIDO'"><input @keyup="calcular(index)" class="form-control" type="text"
                                               v-model="producto.cantidad_recepcion"></td>
                                    <td v-if="estado=='RECIBIDO'">@{{producto.monto_recepcion}}</td>
                                    <td v-if="estado=='RECIBIDO'">@{{ producto.cantidad_recepcion }}</td>
                                    <td>@{{producto.total_recepcion}}</td>
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
                                    <td><input class="form-control" type="text" v-model="producto.nombre" :disabled="producto.idproducto!=-1"></td>
                                    <td><input class="form-control" type="text" v-model="producto.descripcion" :disabled="producto.idproducto!=-1"></td>
                                    <td><input @keyup="calcular(index)" class="form-control" type="text"
                                               v-model="producto.costo"></td>
                                    <td><input @keyup="calcular(index)" class="form-control" type="text"
                                               v-model="producto.cantidad"></td>
                                    <td>@{{producto.total}}</td>
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
                productosSeleccionados: <?php echo $productos ?>,
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
                    if(typeof index === 'object'){
                        producto = index;
                    }
                    producto['total'] = (producto['costo'] * producto['cantidad']).toFixed(2);
                    producto['total_recepcion'] = (producto['monto_recepcion'] * producto['cantidad_recepcion']).toFixed(2);
                    console.log(this.productosSeleccionados)
                    this.calcularTotalCompra();
                },
                calcularTotalPorItem(){
                    for (let producto of this.productosSeleccionados) {
                        this.calcular(producto);
                    }
                },
                calcularTotalCompra(){
                    let suma = 0;
                    let suma_recepcion= 0;

                    //Calcular total requerimiento
                    for (let producto of this.productosSeleccionados) {
                        suma += Number(producto.total);
                    }
                    this.totalCompra = suma.toFixed(2);
                    this.subtotal = (this.totalCompra / 1.18).toFixed(2);
                    this.igv = (this.totalCompra - this.subtotal).toFixed(2);

                    //Calcular total recepcion
                    for (let producto of this.productosSeleccionados) {
                        suma_recepcion += Number(producto.total_recepcion);
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
                            alert('Ha ocurrido un error al procesar la orden de compra ');
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
                                alert('Ha ocurrido un error al procesar la orden de compra');
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
                    this.observaciones='';
                    this.num_comprobante_compra='';
                    this.calcularTotalCompra();
                }
            }

        });
    </script>
@endsection