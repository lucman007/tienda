@extends('layouts.main')
@section('titulo', 'Requerimiento')
@section('contenido')
    @php
        $tipo_cambio_compra = cache('opciones')['tipo_cambio_compra'];
        $unidad_medida = \sysfact\Http\Controllers\Helpers\DataUnidadMedida::getUnidadMedida();
        $can_gestionar = false;
    @endphp
    @can('Inventario: gestionar producto')
        @php
            $can_gestionar = true
        @endphp
    @endcan    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-sm-12">
                <h3 class="titulo-admin-1">
                    <a href="{{url()->previous()}}"><i class="fas fa-arrow-circle-left"></i></a>
                    OC N° {{$requerimiento->correlativo}}
                </h3>
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
                            <div class="col-lg-2 form-group mb-4">
                                <label>Moneda</label>
                                <select v-model="moneda" class="custom-select">
                                    <option value="S/">Soles</option>
                                    <option value="USD">Dólares</option>
                                </select>
                            </div>
                            <div class="col-lg-3 form-group mb-4">
                                <label>Atención</label>
                                <input class="form-control" type="text" v-model="atencion" placeholder="Persona a quien se dirige">
                            </div>
                            <div class="col-lg-2">
                                <label>Tipo cambio</label>
                                <input type="text" v-model="tipoCambio"
                                       class="form-control">
                            </div>
                        </div>
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
                                <autocomplete ref="suggest" v-on:agregar_producto="agregarProducto" :origen="'compras'"></autocomplete>
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
                                <strong>Código:</strong> @{{codigoProveedor}} <hr>
                                    <strong>Proveedor:</strong> @{{nombreProveedor}} <hr>
                                    <strong>Ruc:</strong> @{{rucProveedor}}
                            </div>
                            <div class="col-lg-6">
                                <strong>Fecha:</strong>{{date("d-m-Y H:i:s",strtotime($requerimiento->fecha_requerimiento))}} <hr>
                                    <strong>Estado:</strong> <span class="badge" :class="[estado=='PENDIENTE' ? 'badge-warning' : 'badge-success']">@{{estado}}</span><hr>
                                    <strong>Comprobante:</strong> {{$requerimiento['num_comprobante']}}
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
                                    <td v-if="estado!='RECIBIDO'"><input @keyup="calcular(index)" class="form-control" type="number" onfocus="this.select()"
                                               v-model="producto.monto_recepcion"></td>
                                    <td v-if="estado!='RECIBIDO'"><input @keyup="calcular(index)" class="form-control" type="number" onfocus="this.select()"
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
                                    <td><input class="form-control" type="text" v-model="producto.nombre" disabled></td>
                                    <td><input class="form-control" type="text" v-model="producto.descripcion" disabled></td>
                                    <td><input @keyup="calcular(index)" class="form-control" type="number" onfocus="this.select()"
                                               v-model="producto.costo"></td>
                                    <td><input @keyup="calcular(index)" class="form-control" type="number" onfocus="this.select()"
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
                        <div class="row" v-show="editar">
                            <div class="col-lg-8">
                                <div class="form-group">
                                    <textarea placeholder="Observaciones..."  v-model="observaciones" class="form-control mt-4 mt-lg-0" cols="15" rows="1"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="card" v-show="!editar" style="box-shadow: none">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-8">
                                        <strong>Observaciones:</strong> @{{ observaciones }} <br>
                                    </div>
                                    <div v-if="estado=='PENDIENTE'" v-show="!editar" class="col-lg-4">
                                        <b-input-group>
                                            <input class="form-control" type="text" v-model="num_comprobante_compra" placeholder="Comprobante de compra">
                                            <b-input-group-append>
                                                <b-button variant="warning" @click="recibir_requerimiento">
                                                    <i class="fas fa-check"></i> Recibir
                                                </b-button>
                                            </b-input-group-append>
                                        </b-input-group>
                                    </div>
                                </div>
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
                        <b-button v-show="!editar" v-if="estado!='RECIBIDO'" @click="editarRequerimiento" class="mb-2"  variant="info">
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
            :tipo_cambio="{{$tipo_cambio_compra}}"
            :unidad_medida="{{json_encode($unidad_medida)}}"
            :can_gestionar="{{json_encode($can_gestionar)}}"
            :tipo_de_producto="1"
            :origen="'requerimientos'"
            v-on:agregar="agregarProductoNuevo">>
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
                    'idproveedor':'{{ $requerimiento['idproveedor'] }}',
                },
                codigoProveedor:"{{ $requerimiento['proveedor']['codigo'] }}",
                rucProveedor:"{{ $requerimiento['proveedor']['num_documento'] }}",
                nombreProveedor: "{{ $requerimiento['proveedor']['persona']['nombre'] }}",
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
                moneda: "{{ $requerimiento->moneda }}",
                tipoCambio: "{{ $requerimiento->tipo_cambio }}",
                observaciones: '{{ $requerimiento['observacion'] }}',
                estado:'{{ $requerimiento['estado'] }}',
                num_comprobante_compra:'',
                atencion:"{{ $requerimiento->atencion }}",
            },
            created(){
                this.calcularTotalPorItem();
            },
            methods: {
                editarRequerimiento(){
                    this.editar=!this.editar;
                    let obj = <?php echo json_encode($requerimiento->proveedor)?>;
                    if(this.$refs['suggestCliente']){
                        this.$refs['suggestCliente'].agregarCliente(obj);
                    } else {
                        this.agregarCliente(obj)
                    }
                },
                agregarProveedor(obj){
                    this.proveedorSeleccionado = obj;
                },
                borrarProveedor(){
                    this.proveedorSeleccionado = {};
                },
                agregarProveedorNuevo(obj){
                    this.$refs['suggestCliente'].agregarCliente(obj);
                },
                agregarProductoNuevo(nombre){
                    if(this.$refs['suggest']){
                        this.$refs['suggest'].query = nombre;
                        this.$refs['suggest'].autoComplete();
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
                    this.mostrarProgresoGuardado = true;
                    axios.post('{{action('RequerimientoController@update')}}', {
                        'idrequerimiento': <?php echo $requerimiento['idrequerimiento'] ?>,
                        'idproveedor': this.proveedorSeleccionado['idproveedor'],
                        'total_compra': this.totalCompra,
                        'observaciones':this.observaciones,
                        'moneda': this.moneda,
                        'tipo_cambio': this.tipoCambio,
                        'atencion': this.atencion,
                        'items': JSON.stringify(this.productosSeleccionados)
                    })
                        .then(function (response) {
                            location.reload();
                            this.mostrarProgresoGuardado = false;
                        })
                        .catch(function (error) {
                            this.mostrarProgresoGuardado = false;
                            alert('Ha ocurrido un error al procesar la orden de compra ');
                            console.log(error);
                        });
                },
                recibir_requerimiento(){

                    if(confirm('Los productos se registrarán en el inventario, ¿deseas continuar?')){
                        this.mostrarProgresoAprobacion=true;
                        axios.post('{{action('RequerimientoController@recibir')}}', {
                            'idrequerimiento': <?php echo $requerimiento['idrequerimiento'] ?>,
                            'correlativo': '<?php echo $requerimiento['correlativo'] ?>',
                            'idproveedor': this.proveedorSeleccionado['idproveedor'],
                            'num_comprobante':this.num_comprobante_compra,
                            'total_compra': this.totalCompra,
                            'moneda': this.moneda,
                            'tipo_cambio': this.tipoCambio,
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