@extends('layouts.main')
@section('titulo', 'Editar pedido')
@section('contenido')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <h3 class="titulo-admin-1">Pedido N° {{$orden['idorden']}}</h3>
                <b-button href="{{action('PedidoController@nueva_orden')}}" class="mr-2"  variant="primary"><i class="fas fa-plus"></i> Nuevo pedido</b-button>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 mt-4 mb-3">
                <div class="card">
                    <div class="card-header">
                        Registrar
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-8 col-md-7 col-md-3 buscar_producto">
                                <autocomplete ref="suggest" v-on:agregar_producto="agregarProducto"></autocomplete>
                            </div>
                            <div class="col-lg-4 col-md-5 d-lg-block d-md-flex">
                                <div class="float-left">
                                    <b-form-checkbox v-model="esConIgv" switch size="sm">
                                        Precios ya incluyen IGV
                                    </b-form-checkbox>
                                </div>
                                <div class="float-right">
                                    <select v-model="idvendedor" style="width: 150px" class="custom-select">
                                        <option value="-1" style="font-weight: bold">Vendedor:</option>
                                        <option v-for="empleado in empleados" :value="empleado.idempleado">@{{ empleado.persona.nombre }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col" style="width: 10px"></th>
                                    <th scope="col" style="width: 250px">Producto</th>
                                    <th scope="col" style="width: 350px">Caracteristicas</th>
                                    <th scope="col" style="width: 100px">Precio</th>
                                    <th scope="col" style="width: 100px">Cantidad</th>
                                    <th style="display: none;" scope="col" style="width: 100px">Dscto</th>
                                    <th scope="col" style="width: 80px; text-align: center">Subtotal</th>
                                    <th scope="col" style="width: 80px; text-align: center">Igv</th>
                                    <th scope="col" style="width: 100px">Total</th>
                                    <th scope="col" style="width: 50px"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(producto,index) in productosSeleccionados" :key="producto.index">
                                    <td></td>
                                    <td style="display:none">@{{producto.idproducto}}</td>
                                    <td>@{{producto.cod_producto}} - @{{producto.nombre}}</td>
                                    <td><textarea class="form-control" rows="1" v-model="producto.presentacion"></textarea></td>
                                    <td><input @keyup="calcular(index)" class="form-control" type="text"
                                               v-model="producto.precio"></td>
                                    <td><input @keyup="calcular(index)" class="form-control" type="text"
                                               v-model="producto.cantidad"></td>
                                    <td style="display: none;"><input @keyup="calcular(index)" class="form-control" type="text" v-model="producto.descuento"></td>
                                    <td class="text-center">@{{producto.subtotal}}</td>
                                    <td class="text-center">@{{producto.igv}}</td>
                                    <td>@{{producto.total}}</td>

                                    <td class="">
                                        <a @click="borrarItemVenta(index)" href="javascript:void(0)">
                                            <button class="btn btn-danger" title="Borrar item"><i class="fas fa-trash"></i>
                                            </button>
                                        </a>
                                    </td>
                                </tr>
                                <tr class="text-center" v-show="productosSeleccionados.length == 0"><td colspan="9">Agrega productos desde el buscador</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div>
                            <b-alert :variant="mensajeStock.style" show v-show="mensajeStock.string.length>0">
                                @{{ mensajeStock.string }}
                            </b-alert>
                        </div>
                        <div class="dropdown-divider"></div>
                        <div class="row">
                            <div class="col-lg-6 mt-3">
                                <div class="form-group">
                                    <label for="observaciones">Observaciones:</label>
                                    <textarea v-model="observaciones" class="form-control" cols="15" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="offset-lg-2 col-lg-4">
                                <div class="text-center">
                                    <p>Subtotal: @{{ subtotal }}<br>
                                        IGV: @{{ igv }}</p>
                                    <p class="p-2 total-venta" style="margin-top:20px;">@{{ moneda }} @{{ totalVenta }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 mt-1 mb-3">
                <div class="card">
                    <div class="card-header">
                        Datos de facturación
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-8">
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
                            <div class="col-lg-2 form-group mb-4">
                                <b-input-group>
                                    <b-input-group-prepend>
                                        <b-input-group-text>
                                            <i class="fas fa-receipt"></i>
                                        </b-input-group-text>
                                    </b-input-group-prepend>
                                    <select v-model="comprobante" class="custom-select" id="selectComprobante">
                                        <option value="30">Recibo</option>
                                        <option value="03">Boleta</option>
                                        <option value="01">Factura</option>
                                    </select>
                                </b-input-group>
                            </div>
                            <div class="col-lg-2 form-group mb-4">
                                <b-input-group>
                                    <b-input-group-prepend>
                                        <b-input-group-text>
                                            <i class="fas fa-money-bill-alt"></i>
                                        </b-input-group-text>
                                    </b-input-group-prepend>
                                    <select v-model="moneda" class="custom-select" id="selectMoneda">
                                        <option value="S/">Soles</option>
                                        <option value="USD">Dólares</option>
                                    </select>
                                </b-input-group>
                            </div>
                            <div class="col-lg-8">
                                <b-input-group>
                                    <b-input-group-prepend>
                                        <b-input-group-text>
                                            <i class="fas fa-user-alt"></i>
                                        </b-input-group-text>
                                    </b-input-group-prepend>
                                    <input type="text" v-model="nombreCliente" class="form-control"
                                           placeholder="Cliente" disabled readonly>
                                </b-input-group>
                            </div>
                            <div class="col-lg-4">
                                <b-button class="mb-2 float-right" href="/pedidos" variant="danger"><i class="fas fa-ban"></i> Cancelar
                                </b-button>
                                <b-button :disabled="mostrarProgresoGuardado || productosSeleccionados.length==0" class="mb-2 float-right mr-3" :disabled="productosSeleccionados.length==0" @click="actualizarOrden"
                                          variant="success">
                                    <i v-show="!mostrarProgresoGuardado" class="fas fa-save"></i>
                                    <b-spinner v-show="mostrarProgresoGuardado" small label="Loading..." ></b-spinner> Guardar cambios
                                </b-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <modal-cliente
            v-on:agregar_cliente="agregarCliente">
    </modal-cliente>
    <agregar-cliente
            v-on:agregar="agregarClienteNuevo">
    </agregar-cliente>
    <agregar-producto
            v-bind:ultimo_id="{{$ultimo_id}}"
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
                clienteSeleccionado: {
                    'idcliente':'<?php echo $orden['idcliente'] ?>',
                },
                nombreCliente: "<?php echo $orden['cliente']['persona']['nombre'] ?>",
                codigoCliente: '<?php echo $orden['cliente']['cod_cliente'] ?>',
                numDocCliente: '<?php echo $orden['cliente']['num_documento'] ?>',
                buscar: '',
                mostrarSpinnerCliente: false,

                productosSeleccionados: <?php echo $productos ?>,
                mostrarSpinnerProducto: false,

                totalVenta: 0.00,
                igv: 0.00,
                subtotal: 0.00,
                moneda: '<?php echo $orden['moneda'] ?>',
                comprobante: '<?php echo $orden['comprobante'] ?>',
                observaciones: '<?php echo $orden['observaciones'] ?>',
                esConIgv:!!<?php echo $orden['igv_incluido'] ?>,
                idvendedor:"{{$idvendedor}}",
                empleados:[],
                mensajeStock:{
                    string:'',
                    style:''
                }
            },
            created(){
                this.calcularTotalPorItem();
                this.calcularTotalVenta();
            },
            methods: {
                agregarCliente(obj){
                    this.clienteSeleccionado = obj;
                    this.codigoCliente = this.clienteSeleccionado['cod_cliente'];
                    this.nombreCliente = this.clienteSeleccionado['nombre'];
                    this.numDocCliente = this.clienteSeleccionado['num_documento'];
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
                abrir_modal(nombre){
                    if (nombre == 'producto') {
                        this.$refs['modal-producto'].show();
                        this.obtenerProductos();
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
                        } else {
                            this.obtenerDocumentos();
                        }

                    }, 500);
                },
                agregarProducto(obj){
                    let productos = this.productosSeleccionados.push(Object.assign({}, obj));
                    //crear propiedades precio y cantidad en objeto productosSeleccionados:{} para usarlos
                    //más tarde al procesar la venta.
                    let i = productos - 1;
                    let subtotal = (this.productosSeleccionados[i]['precio']*1).toFixed(2);
                    this.$set(this.productosSeleccionados[i], 'num_item', i);
                    this.$set(this.productosSeleccionados[i], 'cantidad', 1);
                    this.$set(this.productosSeleccionados[i], 'porcentaje_descuento', '0');
                    this.$set(this.productosSeleccionados[i], 'descuento', '0.00');
                    this.$set(this.productosSeleccionados[i], 'subtotal', subtotal);
                    this.$set(this.productosSeleccionados[i], 'igv', (this.productosSeleccionados[i]['precio']*0.18).toFixed(2));
                    this.$set(this.productosSeleccionados[i], 'total', (this.productosSeleccionados[i]['precio']*1.18).toFixed(2));

                    if(this.esConIgv){
                        subtotal = (this.productosSeleccionados[i]['precio']/1.18).toFixed(2);
                        this.$set(this.productosSeleccionados[i], 'subtotal', subtotal);
                        this.$set(this.productosSeleccionados[i], 'igv', (this.productosSeleccionados[i]['precio']-subtotal).toFixed(2));
                        this.$set(this.productosSeleccionados[i], 'total', this.productosSeleccionados[i]['precio']);
                    }

                    this.listaProductos=[];
                    this.calcularTotalVenta();
                    this.validar_stock(this.productosSeleccionados[i]);
                },
                calcular(index){
                    let producto = this.productosSeleccionados[index];
                    let _porcentaje_descuento=producto['porcentaje_descuento']/100;
                    let precio_bruto = producto['precio']/1.18;

                    monto_descuento_subtotal=precio_bruto*producto['cantidad']*_porcentaje_descuento;
                    monto_descuento_total=producto['precio']*producto['cantidad']*_porcentaje_descuento;
                    producto['descuento'] = monto_descuento_total.toFixed(2);
                    producto['subtotal'] = (producto['precio'] * producto['cantidad'] - monto_descuento_total).toFixed(2);
                    producto['igv'] = (producto['subtotal']*0.18).toFixed(2);
                    producto['total'] = (Number(producto['subtotal']) + Number(producto['igv'])).toFixed(2);
                    if(this.esConIgv){
                        producto['descuento'] = monto_descuento_subtotal.toFixed(2);
                        producto['subtotal'] = (precio_bruto * producto['cantidad'] - monto_descuento_subtotal).toFixed(2);
                        producto['total'] = (producto['precio'] * producto['cantidad'] - monto_descuento_total).toFixed(2);
                        producto['igv'] = (producto['total'] - producto['subtotal']).toFixed(2);
                    }
                    this.calcularTotalVenta();
                    this.validar_stock(producto);
                },
                calcularTotalPorItem(){
                    for (let producto of this.productosSeleccionados) {

                        let _porcentaje_descuento=producto['porcentaje_descuento']/100;
                        let precio_bruto = producto['precio']/1.18;

                        monto_descuento_subtotal=precio_bruto*producto['cantidad']*_porcentaje_descuento;
                        monto_descuento_total=producto['precio']*producto['cantidad']*_porcentaje_descuento;
                        producto['descuento'] = monto_descuento_total.toFixed(2);
                        producto['subtotal'] = (producto['precio'] * producto['cantidad'] - monto_descuento_total).toFixed(2);
                        producto['igv'] = (producto['subtotal']*0.18).toFixed(2);
                        producto['total'] = (Number(producto['subtotal']) + Number(producto['igv'])).toFixed(2);
                        if(this.esConIgv){
                            producto['descuento'] = monto_descuento_subtotal.toFixed(2);
                            producto['subtotal'] = (precio_bruto * producto['cantidad'] - monto_descuento_subtotal).toFixed(2);
                            producto['total'] = (producto['precio'] * producto['cantidad'] - monto_descuento_total).toFixed(2);
                            producto['igv'] = (producto['total'] - producto['subtotal']).toFixed(2);
                        }
                    }
                },
                calcularTotalVenta(){
                    let suma = 0;

                    for (let producto of this.productosSeleccionados) {
                        suma += Number(producto.total);
                    }

                    if(this.esConIgv){
                        this.totalVenta = suma.toFixed(2);
                        this.subtotal = (this.totalVenta / 1.18).toFixed(2);
                        this.igv = (this.totalVenta - this.subtotal).toFixed(2);
                    } else{
                        this.totalVenta = suma.toFixed(2);
                        this.subtotal = (this.totalVenta / 1.18).toFixed(2);
                        this.igv = (this.totalVenta - this.subtotal).toFixed(2);
                    }

                },
                borrarItemVenta(index){
                    this.productosSeleccionados.splice(index, 1);
                    this.calcularTotalVenta();
                },
                resetModal(){
                    this.buscar = '';
                },
                actualizarOrden(){
                    if (this.validar()) {
                        return;
                    }
                    this.mostrarProgresoGuardado = true;
                    axios.post('{{action('PedidoController@update')}}', {
                        'idorden': <?php echo $orden['idorden'] ?>,
                        'idcliente': this.clienteSeleccionado['idcliente'],
                        'idvendedor':this.idvendedor,
                        'total': this.totalVenta,
                        'moneda': this.moneda,
                        'comprobante': this.comprobante,
                        'observaciones':this.observaciones,
                        'igv_incluido': this.esConIgv,
                        'items': JSON.stringify(this.productosSeleccionados)
                    })
                        .then(response => {
                            this.$swal({
                                position: 'center',
                                icon: 'success',
                                title: 'Se ha actualizado el pedido',
                                html:response.data.respuesta,
                                timer: 60000,
                            }).then(() => {
                                this.limpiar();
                                this.mostrarProgresoGuardado = false;
                            });

                        })
                        .catch(error => {
                            alert('Ha ocurrido un error al procesar el pedido');
                            console.log(error);
                            this.mostrarProgresoGuardado = false;
                        });
                },
                validar(){
                    let errorVenta = 0;
                    let errorDatosVenta = [];
                    let errorString = '';

                    if (Object.keys(this.clienteSeleccionado).length == 0) {
                        if((this.comprobante == '03' && this.totalVenta>=700) ||  this.comprobante == '01'){
                            errorDatosVenta.push('*Debes ingresar un cliente');
                        }
                    }
                    if(this.comprobante=='01'){
                        if (this.numDocCliente.length != 11) errorDatosVenta.push('*Ingrese un RUC válido');
                    } else if(this.comprobante=='03' && this.totalVenta>=700){
                        if (this.numDocCliente.length < 8 || this.numDocCliente.length > 11) errorDatosVenta.push('*Ingrese un DNI o RUC válido');
                    }
                    if (errorDatosVenta.length) {
                        errorVenta = 1;
                        for (let error of errorDatosVenta) {
                            errorString += error + '\n';
                        }
                        alert(errorString);
                    }

                    return errorVenta;
                },
                validar_stock(producto){
                    if(producto['tipo_producto']===1){
                        this.mensajeStock.string='';
                        if(producto['cantidad'] > producto['stock']){
                            this.mensajeStock.string='La cantidad ingresada supera el stock del producto '+ producto['nombre']+'('+ producto['stock']+' '+producto['unidad_medida']+')'+'. Revise sus existencias.';
                            this.mensajeStock.style='danger';
                        }else if(producto['cantidad'] >= (producto['stock']-producto['stock_bajo'])){
                            this.mensajeStock.string='El stock del producto '+ producto['nombre']+' ('+ producto['stock']+' '+producto['unidad_medida']+')'+' es bajo, es necesario adquirir más unidades.';
                            this.mensajeStock.style='warning';
                        }
                    }
                },
                limpiar(){
                    this.clienteSeleccionado = {};
                    this.nombreCliente = '';
                    this.codigoCliente='';
                    this.numDocCliente= '';
                    this.productosSeleccionados = [];
                    this.motivo='';
                    this.numeroGuia = '';
                    this.numeroOc = '';
                    this.moneda = 'S/';
                    this.comprobante = '30';
                    this.totalVenta = 0.00;
                    this.subtotal = 0.00;
                    this.igv = 0.00;
                    this.observaciones = '';
                    this.esConIgv = true;
                    this.calcularTotalVenta();
                    this.mensajeStock={string:'',style:''};
                    this.$refs['suggest'].limpiar();
                },
            },
            watch:{
                esConIgv(){
                    _this=this;
                    this.productosSeleccionados.forEach(
                        function (valor, indice, array) {
                            _this.calcular(indice);
                        }
                    );
                }
            }

        });
    </script>
@endsection