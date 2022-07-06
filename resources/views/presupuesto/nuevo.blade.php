@extends('layouts.main')
@section('titulo', 'Nueva cotización')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-sm-12">
                <h3 class="titulo-admin-1">
                    <a href="{{url('presupuestos')}}"><i class="fas fa-arrow-circle-left"></i></a>
                    Nueva cotización
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
                            <div class="col-lg-2 form-group">
                                <label>N° de cotización</label>
                                <input disabled type="text" v-model="numeroCotizacion" class="form-control">
                            </div>
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
                            <div class="col-lg-3">
                                <div class="row">
                                    <div class="col-lg-6 form-group mb-4">
                                        <label>Validez (días)</label>
                                        <input class="form-control" type="text" v-model="validez" placeholder="Número de días">
                                    </div>
                                    <div class="col-lg-6 form-group">
                                        <label>Tipo cambio</label>
                                        <input type="text" v-model="tipoCambio"
                                               class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 mt-3">
                                <b-form-checkbox v-model="exportacion" switch size="sm">
                                    Es exportación
                                </b-form-checkbox>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-lg-7">
                                <div class="row">
                                    @if(json_decode(cache('config')['interfaz'], true)['buscador_clientes'] == 1)
                                        <div class="col-lg-9">
                                            <autocomplete-cliente v-on:agregar_cliente="agregarCliente" v-on:borrar_cliente="borrarCliente" ref="suggestCliente"></autocomplete-cliente>
                                        </div>
                                        <div class="col-lg-3 no-gutters">
                                            <b-button v-b-modal.modal-nuevo-cliente
                                                      class="mb-4 mt-2 mt-lg-0 float-right float-lg-left" variant="primary"><i class="fas fa-plus"
                                                                                                                               v-show="!mostrarSpinnerCliente"></i>
                                                <b-spinner v-show="mostrarSpinnerCliente" small label="Loading..."></b-spinner>
                                                Nuevo cliente
                                            </b-button>
                                        </div>
                                    @else
                                        <div class="col-lg-9">
                                            <b-button v-b-modal.modal-cliente
                                                      class="mb-4 mr-2" variant="primary"><i class="fas fa-search-plus"
                                                                                             v-show="!mostrarSpinnerCliente"></i>
                                                <b-spinner v-show="mostrarSpinnerCliente" small label="Loading..."></b-spinner>
                                                Seleccionar cliente
                                            </b-button>
                                            <b-button v-b-modal.modal-nuevo-cliente
                                                      class="mb-4" variant="primary"><i class="fas fa-user-plus"
                                                                                        v-show="!mostrarSpinnerCliente"></i>
                                                <b-spinner v-show="mostrarSpinnerCliente" small label="Loading..."></b-spinner>
                                                Nuevo cliente
                                            </b-button>
                                        </div>
                                        <div class="col-lg-12">
                                            <input type="text" v-model="nombreCliente" class="form-control mb-2"
                                                   placeholder="Cliente" disabled readonly>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div v-show="exportacion" class="col-lg-5" style="margin-top: -20px">
                                <div class="row">
                                    <div class="col-lg-4 mb-2">
                                        <label>Incoterm</label>
                                        <input type="text" v-model="incoterm"
                                               class="form-control">
                                    </div>
                                    <div class="col-lg-4 mb-2">
                                        <label>Flete</label>
                                        <input @keyup="calcularTotalVenta()" type="number" v-model="flete"
                                               class="form-control">
                                    </div>
                                    <div class="col-lg-4 mb-2">
                                        <label>Seguro</label>
                                        <input @keyup="calcularTotalVenta()" type="number" v-model="seguro"
                                               class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            @if(json_decode(cache('config')['interfaz'], true)['buscador_productos'] == 1)
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
                            @else
                                <div class="col-lg-10">
                                    <b-button v-b-modal.modal-producto variant="primary" class="mr-2">
                                        <i class="fas fa-search-plus" v-show="!mostrarSpinnerProducto"></i>
                                        <b-spinner v-show="mostrarSpinnerProducto" small label="Loading..."></b-spinner>
                                        Seleccionar producto
                                    </b-button>
                                    <b-button class=""  v-b-modal.modal-nuevo-producto
                                              variant="primary"><i class="fas fa-plus" v-show="!mostrarSpinnerProducto"></i>
                                        <b-spinner v-show="mostrarSpinnerProducto" small label="Loading..."></b-spinner>
                                        Nuevo producto
                                    </b-button>
                                </div>
                            @endif
                            <div class="col-lg-2" v-show="!exportacion">
                                <b-form-checkbox v-model="esConIgv" switch size="sm">
                                    Incluir IGV
                                </b-form-checkbox>
                            </div>
                        </div>
                        <div class="table-responsive tabla-gestionar">
                            @if(!str_contains(strtolower($_SERVER['HTTP_USER_AGENT']),'android'))
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col" style="width: 10px"></th>
                                    <th scope="col" style="width: 250px">Producto</th>
                                    <th scope="col" style="width: 350px">Caracteristicas</th>
                                    <th scope="col" style="width: 100px">Precio</th>
                                    <th scope="col" style="width: 100px">Cantidad</th>
                                    <th scope="col" style="width: 80px; text-align: center">Dscto</th>
                                    <th scope="col" style="width: 80px; text-align: center">Subtotal</th>
                                    <th scope="col" style="width: 80px; text-align: center">Igv</th>
                                    <th scope="col" style="width: 100px">Total</th>
                                    <th scope="col" style="width: 50px"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(producto,index) in productosSeleccionados" :key="producto.index">
                                    <td></td>
                                    <td><input class="form-control" type="text" v-model="producto.nombre" disabled></td>
                                    <td><textarea class="form-control" rows="1" v-model="producto.presentacion"></textarea></td>
                                    <td>
                                        <input onfocus="this.select()" @change="guardar_prev_precio(index)" @keyup="calcular(index)" class="form-control navigable nav-precio" :data-i="index" type="text" v-model="producto.precio">
                                    </td>
                                    <td>
                                        <input onfocus="this.select()" @keyup="calcular(index)" class="form-control navigable nav-cantidad" :data-i="index" type="text" v-model="producto.cantidad">
                                    </td>
                                    <td class="text-center">@{{producto.tipo_descuento?producto.porcentaje_descuento+'%':(Number(producto.descuento)).toFixed(2)}}</td>
                                    <td class="text-center">@{{(Number(producto.subtotal)).toFixed(2)}}</td>
                                    <td class="text-center">@{{(Number(producto.igv)).toFixed(2)}}</td>
                                    <td>@{{(Number(producto.total)).toFixed(2)}}</td>
                                    <td class="botones-accion" style="width: 120px">
                                        <b-button :disabled="producto['precio']<=0 || producto['cantidad']<=0" v-b-modal.modal-descuento @click="editarItem(producto,index)" variant="success" title="Agregar descuento"><i class="fas fa-percentage"></i></b-button>
                                        <b-button  @click="borrarItemVenta(index)"  variant="danger" title="Borrar item"><i class="fas fa-trash"></i>
                                        </b-button>
                                    </td>
                                </tr>
                                <tr class="text-center" v-show="productosSeleccionados.length == 0"><td colspan="10">Agrega productos desde el buscador</td></tr>
                                </tbody>
                            </table>
                            @else
                                <table class="table table-striped table-hover table-sm">
                                    <thead class="bg-custom-green">
                                    <tr>
                                        <th scope="col" style="width: 350px">Descripción</th>
                                        <th scope="col" style="width: 80px">Total</th>
                                        <th scope="col" style="width: 50px"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(producto,index) in productosSeleccionados" :key="index" v-b-modal.modal-detalle @click="editarItem(producto, index)">
                                        <td>@{{producto.nombre}} x @{{producto.cantidad}}</td>
                                        <td>@{{(Number(producto.total)).toFixed(2)}}</td>
                                        <td @click.stop >
                                            <b-button :disabled="producto['precio']<=0 || producto['cantidad']<=0" v-b-modal.modal-descuento @click="editarItem(producto,index)" variant="success" title="Agregar descuento">
                                                <i class="fas fa-percentage"></i>
                                            </b-button>
                                            <button @click="borrarItemVenta(index)" class="btn btn-danger"
                                                    title="Borrar item"><i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="text-center" v-show="productosSeleccionados.length == 0"><td colspan="8">Agrega productos desde el buscador</td></tr>
                                    </tbody>
                                </table>
                            @endif
                        </div>
                        <div class="dropdown-divider"></div>
                        <div class="row mt-3">
                            <div class="col-lg-3">
                                <b-button :disabled="productosSeleccionados.length==0 || subtotal <= 0" class="w-100" v-b-modal.modal-descuento @click="editarItem()" variant="success">
                                    <i class="fas fa-percentage"></i> Descuento global: @{{tipo_descuento_global?porcentaje_descuento_global+'%':moneda+' '+(Number(descuento_global)).toFixed(2)}}
                                </b-button>
                            </div>
                            <div class="col-lg-7">
                                <div class="form-group">
                                    <textarea placeholder="Observaciones..."  v-model="observaciones" class="form-control mt-4 mt-lg-0" cols="15" rows="1"></textarea>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <input placeholder="Referencia" type="text" v-model="referencia"
                                           class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 mb-3">
                <div class="card">
                    <div class="card-header">
                        Información adicional
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-3 form-group mb-4">
                                <label>Condiciones de pago</label>
                                <input class="form-control" type="text" v-model="condicion_pago">
                            </div>
                            <div class="col-lg-4 form-group mb-4">
                                <label>Tiempo de entrega</label>
                                <input class="form-control" type="text" v-model="tiempo_entrega">
                            </div>
                            <div class="col-lg-2 form-group mb-4">
                                <label>Garantía</label>
                                <input class="form-control" type="text" v-model="garantia">
                            </div>
                            <div class="col-lg-3 form-group mb-4">
                                <label>Impuesto</label>
                                <input class="form-control" type="text" v-model="impuesto">
                            </div>
                            <div class="col-lg-4 form-group mb-4">
                                <label>Lugar de entrega</label>
                                <input class="form-control" type="text" v-model="lugar_entrega">
                            </div>
                            <div class="col-lg-4 form-group mb-4">
                                <label>Atentamente</label>
                                <input class="form-control" type="text" v-model="contacto">
                            </div>
                            <div class="col-lg-4 form-group mb-4">
                                <label>Teléfonos</label>
                                <input class="form-control" type="text" v-model="telefonos">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 mb-5 order-1 order-md-0">
                <div class="card">
                    <div class="card-header">
                        Acciones
                    </div>
                    <div class="card-body text-center">
                        <b-button :disabled="mostrarProgresoGuardado || productosSeleccionados.length==0" class="mb-2" :disabled="productosSeleccionados.length==0" @click="procesarPresupuesto"
                                  variant="success">
                            <i v-show="!mostrarProgresoGuardado" class="fas fa-save"></i>
                            <b-spinner v-show="mostrarProgresoGuardado" small label="Loading..." ></b-spinner> Guardar cotización
                        </b-button>
                        <b-button class="mb-2" @click="limpiar" variant="danger"><i class="fas fa-ban"></i> Cancelar
                        </b-button>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-5 order-0 order-md-1">
                <div class="card">
                    <div class="card-header">
                        Totales
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <p>@{{descuentos>0?'Descuentos: '+ moneda + ' ' + descuentos:''}}<br>
                                Subtotal: @{{ moneda }} @{{ (Number(subtotal)).toFixed(2) }}<br>
                                IGV: @{{ moneda }} @{{ (Number(igv)).toFixed(2) }}</p>
                            <p class="p-2 total-venta" style="margin-top:20px;">@{{ moneda }} @{{ (Number(totalVenta)).toFixed(2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <modal-producto
            v-bind:stock="false"
            v-on:agregar_producto="agregarProducto">
    </modal-producto>
    <modal-cliente
            v-on:agregar_cliente="agregarCliente">
    </modal-cliente>
    <agregar-cliente
            v-on:agregar="agregarClienteNuevo">
    </agregar-cliente>
    <agregar-producto
            v-bind:ultimo_id="{{$ultimo_id}}"
            v-bind:tipo_cambio_compra="{{cache('opciones')['tipo_cambio_compra']}}"
            v-on:agregar="agregarProductoNuevo">
    </agregar-producto>
    <modal-descuento
            :item="item"
            :moneda="moneda"
            :igv="esConIgv"
            :global="esDstoGlobal"
            :data-descuento="dataDescuento"
            v-on:actualizar="actualizarDescuento">
    ></modal-descuento>
    <modal-detalle
            :item="item"
            v-on:actualizar="actualizarDetalle">
    </modal-detalle>
@endsection
@section('script')
    <script>

        let app = new Vue({
            el: '.app',
            data: {
                accion: 'insertar',
                mostrarProgresoGuardado: false,
                clienteSeleccionado: {},
                buscar: '',
                mostrarSpinnerCliente: false,
                productosSeleccionados: [],
                mostrarSpinnerProducto: false,
                totalVenta: 0.00,
                igv: 0.00,
                subtotal: 0.00,
                moneda: 'S/',
                observaciones:'',
                atencion:'',
                validez:"<?php echo $config['validez']?>",
                condicion_pago:"<?php echo $config['condicion_pago']?>",
                tiempo_entrega:"<?php echo $config['tiempo_entrega']?>",
                garantia:"<?php echo $config['garantia']?>",
                impuesto:"<?php echo $config['impuesto']?>",
                lugar_entrega:"<?php echo $config['lugar_entrega']?>",
                contacto:"<?php echo $config['remitente']==''?$usuario->nombre.' '.$usuario->apellidos:$config['remitente']?>",
                telefonos:"<?php echo $config['remitente_telefonos']?>",
                esConIgv:<?php echo json_encode(json_decode(cache('config')['interfaz'], true)['igv_incluido']) ?>,
                numeroCotizacion:"",
                tipoCambio: <?php echo cache('opciones')['tipo_cambio_compra'] ?>,
                descuentos: 0.00,
                descuento_global: 0,
                porcentaje_descuento_global:0,
                exportacion: false,
                flete: '0.00',
                seguro: '0.00',
                incoterm:'',
                nombreCliente: '',
                tipo_descuento_global: false,
                item:{},
                index:-1,
                esDstoGlobal: false,
                dataDescuento:{},
                referencia:''
            },
            created(){
                this.obtenerCorrelativo();
                this.calcularTotalVenta();
            },
            methods: {
                obtenerCorrelativo(){
                    axios.get('/presupuestos/obtenerCorrelativo')
                        .then(response => {
                            this.numeroCotizacion = response.data;
                        })
                        .catch(error => {
                            this.alerta('No hay venta registrada. Ingresa el correlativo manualmente');
                            console.log(error);
                        });
                },
                editarItem(item, index){
                    if(item){
                        this.item=item;
                        this.index = index;
                        this.esDstoGlobal = false;
                    } else {
                        let suma_gravadas=0;
                        for (let producto of this.productosSeleccionados) {
                            suma_gravadas += Number(producto['subtotal']);
                        }
                        this.dataDescuento = {
                            gravadas: suma_gravadas,
                            descuento: this.descuento_global,
                            porcentaje_descuento: this.porcentaje_descuento_global,
                            tipo_descuento: this.tipo_descuento_global
                        };
                        this.esDstoGlobal = true;
                    }
                },
                actualizarDetalle(obj){
                    let producto = this.productosSeleccionados[this.index];
                    producto['cantidad']=obj['cantidad'];
                    producto['precio']=obj['precio'];
                    producto['presentacion']=obj['presentacion'];
                    this.calcular(this.index);
                },
                actualizarDescuento(obj){
                    if(this.esDstoGlobal){
                        this.descuento_global=obj['monto'];
                        this.porcentaje_descuento_global=obj['porcentaje'];
                        this.tipo_descuento_global=obj['tipo_descuento'];
                        this.calcularTotalVenta();
                    } else {
                        let producto = this.productosSeleccionados[this.index];
                        producto['tipo_descuento']=obj['tipo_descuento'];
                        producto['porcentaje_descuento']=obj['porcentaje'];
                        producto['descuento']=obj['monto'];
                        producto['descuento_por_und']=obj['porUnidad'];
                        this.calcular(this.index);
                    }
                },
                agregarCliente(obj){
                    this.clienteSeleccionado = obj;
                    this.nombreCliente = this.clienteSeleccionado['num_documento']+' - '+this.clienteSeleccionado['nombre'];
                },
                borrarCliente(){
                    this.clienteSeleccionado = {};
                },
                agregarProductoNuevo(nombre){
                    this.buscar = nombre;
                    this.obtenerProductos();
                },
                agregarClienteNuevo(obj){
                    if(this.$refs['suggestCliente']){
                        this.$refs['suggestCliente'].agregarCliente(obj);
                    } else {
                        this.agregarCliente(obj)
                    }
                },
                agregarProducto(obj){
                    let productos = this.productosSeleccionados.push(Object.assign({}, obj));
                    let i = productos - 1;
                    let producto = this.productosSeleccionados[i];

                    this.$set(producto, 'prev_precio', (producto['precio']));

                    if(producto['moneda']=='PEN' && this.moneda=='USD'){
                        producto['precio']=(producto['precio'] / this.tipoCambio).toFixed(2);
                    } else if(producto['moneda']=='USD' && this.moneda=='S/'){
                            producto['precio']=(producto['precio'] * this.tipoCambio).toFixed(2)
                    }

                    let precio = this.esConIgv?producto['precio']/1.18:Number(producto['precio']);

                    this.$set(producto, 'num_item', i);
                    this.$set(producto, 'cantidad', 1);
                    this.$set(producto, 'tipo_descuento', 0);
                    this.$set(producto, 'porcentaje_descuento', '0.00');
                    this.$set(producto, 'descuento_por_und', 0);
                    this.$set(producto, 'descuento', '0.00');
                    this.$set(producto, 'subtotal', precio);
                    this.$set(producto, 'igv', precio * 0.18);
                    this.$set(producto, 'total', precio * 1.18);

                    if(this.exportacion) {
                        this.$set(producto, 'igv', 0);
                        this.$set(producto, 'total', precio);
                    }

                    this.calcularTotalVenta();
                },
                calcular(index){
                    let producto = this.productosSeleccionados[index];
                    if(typeof index === 'object'){
                        producto = index;
                    }

                    if(producto['descuento'] > 0 && (producto['precio']<=0 || producto['cantidad']<=0)){
                        producto['tipo_descuento']=0;
                        producto['porcentaje_descuento']=0;
                        producto['descuento']=0;
                        producto['descuento_por_und']=0;
                    }

                    let precio = this.esConIgv?producto['precio']/1.18:producto['precio'];

                    let descuento=producto['tipo_descuento']?producto['porcentaje_descuento']/100:producto['descuento'];
                    let monto_descuento=producto['tipo_descuento']?precio*producto['cantidad']*descuento:descuento;
                    producto['descuento'] = monto_descuento;
                    producto['subtotal'] = precio * producto['cantidad'] - monto_descuento;
                    producto['igv'] = Number(producto['subtotal']) * 0.18;
                    producto['total'] = Number(producto['subtotal']) + Number(producto['igv']);

                    if(this.exportacion) {
                        producto['igv'] = 0;
                        producto['subtotal'] = producto['precio']*producto['cantidad'] - monto_descuento;
                        producto['total'] = producto['subtotal'];
                    }
                    this.calcularTotalVenta();

                },
                calcularTotalPorItem(){
                    for (let producto of this.productosSeleccionados) {
                        this.calcular(producto);
                    }
                },
                calcularTotalVenta(){

                    let suma_descuentos = 0;
                    let suma_gravadas = 0;

                    for (let producto of this.productosSeleccionados) {
                        suma_gravadas += Number(producto['subtotal']);
                        suma_descuentos += Number(producto['descuento']);
                    }

                    let desc_global = this.tipo_descuento_global ? this.porcentaje_descuento_global/100: this.descuento_global;
                    let monto_descuento = this.tipo_descuento_global ? suma_gravadas * desc_global : desc_global;
                    let gravadas = suma_gravadas - monto_descuento;
                    this.descuentos = (suma_descuentos + Number(monto_descuento)).toFixed(2);
                    this.igv = gravadas * 0.18;

                    if(this.exportacion){
                        this.totalVenta = (gravadas + Number(this.flete) + Number(this.seguro)).toFixed(2);
                        this.subtotal = this.totalVenta;
                        this.igv=0;
                    } else {
                        this.totalVenta = (gravadas + Number(this.igv)).toFixed(2);
                        this.subtotal = gravadas;
                    }

                },
                borrarItemVenta(index){
                    this.productosSeleccionados.splice(index, 1);
                    this.calcularTotalVenta();
                },
                resetModal(){
                    this.buscar = '';
                },
                procesarPresupuesto(){

                    if (this.validar()) {
                        return;
                    }
                    this.mostrarProgresoGuardado = true;
                    axios.post('{{action('PresupuestoController@store')}}', {
                        'idcliente': this.clienteSeleccionado['idcliente'],
                        'presupuesto': this.totalVenta,
                        'descuento': this.descuento_global,
                        'porcentaje_descuento':this.porcentaje_descuento_global,
                        'tipo_descuento':this.tipo_descuento_global,
                        'moneda': this.moneda,
                        'correlativo': this.numeroCotizacion,
                        'observaciones':this.observaciones,
                        'atencion':this.atencion,
                        'validez':this.validez,
                        'condicion_pago':this.condicion_pago,
                        'tiempo_entrega':this.tiempo_entrega,
                        'garantia':this.garantia,
                        'impuesto':this.impuesto,
                        'lugar_entrega':this.lugar_entrega,
                        'contacto':this.contacto,
                        'telefonos':this.telefonos,
                        'igv_incluido': this.esConIgv,
                        'exportacion': this.exportacion,
                        'flete':this.flete,
                        'seguro':this.seguro,
                        'incoterm':this.incoterm,
                        'referencia':this.referencia,
                        'items': JSON.stringify(this.productosSeleccionados)
                    })
                        .then(response => {
                            if(isNaN(response.data)){
                                this.alerta(response.data);
                                this.mostrarProgresoGuardado = false;
                            } else{
                                window.location.href='editar/'+response.data;
                            }
                        })
                        .catch(error => {
                            this.alerta('Ha ocurrido un error al procesar la cotización');
                            console.log(error);
                            this.mostrarProgresoGuardado = false;
                        });
                },
                validar(){
                    let errorVenta = 0;
                    let errorDatosVenta = [];
                    let errorString = '';

                    //if (Object.keys(this.clienteSeleccionado).length == 0) errorDatosVenta.push('*Debes ingresar un cliente');

                    if (errorDatosVenta.length) {
                        errorVenta = 1;
                        for (let error of errorDatosVenta) {
                            errorString += error + '\n';
                        }
                        this.alerta(errorString);
                    }

                    return errorVenta;
                },
                limpiar(){
                    this.clienteSeleccionado = {};
                    this.productosSeleccionados = [];
                    this.numeroGuia = '';
                    this.numeroOc = '';
                    this.moneda = 'S/';
                    this.totalVenta = 0.00;
                    this.subtotal = 0.00;
                    this.igv = 0.00;
                    this.observaciones = '';
                    this.atencion = '';
                    this.validez = '';
                    this.condicion_pago = 'Factura 30 días';
                    this.tiempo_entrega = '01 día de recibir la orden de compra';
                    this.garantia = '1 año';
                    this.impuesto = 'Más IGV 18%';
                    this.lugar_entrega = '';
                    this.contacto = '';
                    this.telefonos = '';
                    this.referencia = '';
                    this.esConIgv = <?php echo json_encode(json_decode(cache('config')['interfaz'], true)['igv_incluido']) ?>;
                    this.calcularTotalVenta();
                    if(this.$refs['suggestCliente']){
                        this.$refs['suggestCliente'].borrarCliente();
                    }
                    if(this.$refs['suggest']){
                        this.$refs['suggest'].limpiar();
                    }
                    this.nombreCliente = "";
                    this.tipoCambio = <?php echo cache('opciones')['tipo_cambio_compra'] ?>;
                    this.descuentos = 0;
                    this.descuento_global= 0;
                    this.exportacion = false;
                    this.flete='0.00';
                    this.seguro='0.00';
                    this.incoterm='';
                    this.tipo_descuento_global= false;
                    this.descuento_global=0;
                    this.porcentaje_descuento_global=0;
                    this.item={};
                    this.index=-1;
                    this.esDstoGlobal= false;
                    this.dataDescuento={};
                },
                guardar_prev_precio(index){
                    let producto = this.productosSeleccionados[index];
                    producto['prev_precio']=producto.precio
                    producto['moneda']=this.moneda=='S/'?'PEN':'USD';
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
                }
            },
            watch:{
                esConIgv(){
                    this.productosSeleccionados.forEach(
                        (valor, indice, array) => {
                            this.calcular(indice);
                        }
                    );
                },
                exportacion(){
                    this.productosSeleccionados.forEach(
                        (valor, indice, array) => {
                            this.calcular(indice);
                        }
                    );
                },
                moneda(moneda){
                    this.productosSeleccionados.forEach(
                        (valor, indice, array) => {
                            if(moneda=='USD'){
                                if(valor['moneda']=='USD'){
                                    valor['precio']=(valor['prev_precio']);
                                }
                                if(valor['moneda']=='PEN'){
                                    valor['precio']=(valor['prev_precio'] / this.tipoCambio).toFixed(2);
                                }
                            }
                            if(moneda=='S/'){
                                if(valor['moneda']=='USD'){
                                    valor['precio']=((valor['prev_precio']) * this.tipoCambio).toFixed(2);
                                }
                                if(valor['moneda']=='PEN'){
                                    valor['precio']=(valor['prev_precio']);
                                }

                            }
                            this.calcular(indice);
                        }
                    );
                }
            }

        });
    </script>
@endsection