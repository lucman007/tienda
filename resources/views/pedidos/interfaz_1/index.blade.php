@extends('layouts.main')
@section('titulo', 'Pedidos')
@section('contenido')
    @php
        $buscador_alternativo = json_decode(cache('config')['interfaz'], true)['buscador_productos_alt']??false;
        $colapsar = json_decode(cache('config')['interfaz'], true)['colapsar_categorias']??false;
        $emitir_solo_ticket = json_decode(cache('config')['interfaz'], true)['emitir_solo_ticket']??false;
        $aumentar_cantidad_producto = json_decode(cache('config')['interfaz'], true)['aumentar_cantidad_producto']??false;
    @endphp
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}} interfaz_3">
        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <b-button :disabled="disabledNuevo" alt="Nuevo pedido"  @click="nuevoDelivery" variant="primary"><i class="fas fa-plus"></i> Nueva venta</b-button>
                        <div style="display: flex">
                            <b-button alt="Resumen del día"  variant="primary" class="mx-2" @cannot('Facturación: facturar') class="disabled" disabled @endcannot href="{{action('PedidoController@ventas')}}">
                                <i class="fas fa-list-ul"></i> Resumen del día
                            </b-button>
                            <b-button @click="reloadPage" variant="warning" title="Actualizar"><i class="fas fa-sync"></i></b-button>
                        </div>
                    </div>
                    <div class="card-body scroll-mesas" id="box-pedidos">
                        <div class="row">
                            <div class="col-lg-12">
                                <table class="table table-striped table-hover table-sm">
                                    <thead class="bg-custom-green">
                                    <tr>
                                        @if($agent->isDesktop())
                                        <th scope="col"></th>
                                        @endif
                                        <th scope="col">N°</th>
                                        <th scope="col">Vend.</th>
                                        <th scope="col">Cliente</th>
                                        <th scope="col">Total</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr class="delivery-item" :class="{'active-order':item.idorden == idpedido}" @click="obtener_data_pedido(item.idorden)" v-for="item, index in ordenes" :key="index">
                                        @if($agent->isDesktop())
                                        <td></td>
                                        @endif
                                        <td>@{{item.idorden}}</td>
                                        <td>@{{item.empleado}}</td>
                                        <td>@{{item.datos_entrega['contacto']}}</td>
                                        <td>S/ @{{item.total}}</td>
                                    </tr>
                                    <tr v-show="ordenes.length == 0" class="text-center">
                                        <td colspan="9">No hay datos que mostrar</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header" id="top-btns">
                                <div class="mb-3 mb-sm-0">
                                    @if($agent->isDesktop())
                                        <div v-show="idpedido != -1">
                                            <div class="info_selected_mesa">
                                                <h4>
                                                    <span class="numero_mesa" :class="{'transicion':mostrarSpinner}">#@{{ idpedido }}</span>
                                                </h4>
                                            </div>
                                            <div class="info_selected_mesa"><h4>S/@{{ Number(totalVenta).toFixed(2) }}</h4></div>
                                        </div>
                                    @endif
                                </div>
                                <div style="display: flex; flex-direction: row; align-items: center;flex-wrap: wrap;justify-content: center;">
                                    <b-button v-b-modal.modal-devolucion variant="success" title="Datos de entrega">
                                        <i class="fas fa-undo"></i> {{!($agent->isTablet()||$agent->isDesktop())?'':'Devolución'}}
                                    </b-button>
                                    <select @if($idvendedor != -1) disabled @endif @change="cambiarEmpleado" v-model="idvendedor" style="width: 110px" class="custom-select mx-1">
                                        <option value="-1" style="font-weight: bold">Vendedor:</option>
                                        <option v-for="empleado in empleados" :value="empleado.idempleado">@{{ empleado.persona.nombre }}</option>
                                    </select>
                                    <b-button :disabled="idpedido == -1" v-b-modal.modal-entrega variant="primary" title="Datos de entrega">
                                        <i class="fas fa-map-marker-alt"></i> {{!($agent->isTablet()||$agent->isDesktop())?'':'Entrega'}}
                                    </b-button>
                                </div>
                            </div>
                            <div class="card-body" style="height: 360px; overflow-y: auto; padding-top:10px;padding-bottom:10px;" id="box-items">
                                @if(!$agent->isDesktop())
                                <div class="total-movil">
                                    <p>S/@{{ Number(totalVenta).toFixed(2) }}</p>
                                </div>
                                @endif
                                <div class="loader" v-show="mostrarSpinner">
                                    <b-spinner label="Cargando..." ></b-spinner>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="table-responsive">
                                            @if($agent->isDesktop())
                                                <table class="table table-striped table-hover table-sm">
                                                <thead class="bg-custom-green">
                                                <tr>
                                                    <th scope="col" style="width: 10px"></th>
                                                    <th scope="col" style="width: 290px">Producto</th>
                                                    <th scope="col" style="width: 300px">Descripción</th>
                                                    <th scope="col" style="width: 100px">Precio</th>
                                                    <th scope="col" style="width: 150px">Cantidad</th>
                                                    <th scope="col" style="width: 50px">Total</th>
                                                    <th scope="col" style="width: 50px"></th>
                                                    <th></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr v-for="(producto,index) in productosSeleccionados" :key="index">
                                                    <td></td>
                                                    <td>
                                                        @{{producto.nombre}}
                                                        <span v-show="producto.serie" style="font-size: 11px; color: #0f53ff;"><br>@{{'SERIE: '+producto.serie}}</span>
                                                        <br>
                                                        <span v-show="producto.alert_stock">
                                                            <span class="badge" :class="producto.alert_color">@{{ producto.alert_stock }} </span>
                                                        </span>
                                                        <span style="font-size: 11px; color: #0b870b;" v-for="item in producto.items_kit">+ (@{{ item.cantidad }}) @{{item['nombre']}}<br></span>
                                                    </td>
                                                    <td @click="habilitar(producto.num_item,'d')"><input
                                                                onblur="app.noFocus(this)"
                                                                @keyup="actualizarDetalle"
                                                                :id="producto.num_item+'-d'"
                                                                class="form-control td-dis" type="text"
                                                                readonly
                                                                v-model="producto.presentacion"></td>
                                                    <td @click="habilitar(producto.num_item,'p')"><input
                                                                onblur="app.noFocus(this)"
                                                                onfocus="this.select()"
                                                                @keyup="actualizarDetalle"
                                                                :id="producto.num_item+'-p'"
                                                                class="form-control td-dis" type="number"
                                                                readonly
                                                                v-model="producto.precio" @cannot('Pedido: editar precio') readonly @endcannot></td>
                                                    <td @click="habilitar(producto.num_item,'c')">
                                                        <b-input-group>
                                                            <b-input-group-prepend>
                                                                <b-button @click="cambiarCantidad(index,'-')"
                                                                          variant="primary">-
                                                                </b-button>
                                                            </b-input-group-prepend>
                                                            <input
                                                                    onblur="app.noFocus(this)"
                                                                    onfocus="this.select()"
                                                                    @keyup="actualizarDetalle"
                                                                    :id="producto.num_item+'-c'"
                                                                    class="form-control td-dis" type="number"
                                                                    readonly
                                                                    v-model="producto.cantidad">
                                                            <b-input-group-append>
                                                                <b-button @click="cambiarCantidad(index,'+')"
                                                                          variant="primary">+
                                                                </b-button>
                                                            </b-input-group-append>
                                                        </b-input-group>
                                                    </td>
                                                    <td>@{{producto.total}}</td>
                                                    <td>
                                                        <span v-show="producto.loading">
                                                            <b-spinner small label="Loading..." ></b-spinner>
                                                        </span>
                                                        <span v-show="producto.warning">
                                                            <i style="color: orange;" :id="'warning-'+index" class="fas fa-exclamation-triangle"></i>
                                                            <b-tooltip :target="'warning-'+index" triggers="hover">
                                                                No se ha podido actualizar el item, intenta recargar la página.
                                                            </b-tooltip>
                                                        </span>
                                                    </td>
                                                    <td class="" style="width: 120px;">
                                                        <button @click="borrarItemVenta(index)"
                                                                :disabled="mostrarSpinner" class="btn btn-danger"
                                                                title="Borrar item"><i class="fas fa-trash"></i>
                                                        </button>
                                                        <button v-show="!!producto.discounts" v-b-modal.modal-producto-descuento @click="editarItem(producto,index)"
                                                                :disabled="mostrarSpinner" class="btn btn-success"
                                                                title="Descuento"><i class="fas fa-percentage"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr class="text-center" v-show="productosSeleccionados.length == 0"><td colspan="8">Ningún producto seleccionado</td></tr>
                                                </tbody>
                                            </table>
                                            @else
                                                <table class="table table-striped table-hover table-sm">
                                                    <thead class="bg-custom-green">
                                                    <tr>
                                                        <th scope="col" style="width: 350px">Descripción</th>
                                                        <th scope="col" style="width: 80px">Total</th>
                                                        <th scope="col" style="width: 50px"></th>
                                                        <th></th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr v-for="(producto,index) in productosSeleccionados" :key="index" v-b-modal.modal-detalle @click="editarItem(producto)">
                                                        <td>
                                                            @{{producto.nombre}} x @{{producto.cantidad}}
                                                            <br>
                                                            <span v-show="producto.alert_stock">
                                                                <span class="badge" :class="producto.alert_color">@{{ producto.alert_stock }} </span>
                                                            </span>
                                                            <span style="font-size: 11px; color: #0b870b;" v-for="item in producto.items_kit">+@{{item['nombre']}} (x@{{ item.cantidad }})<br></span>
                                                        </td>
                                                        <td>@{{producto.total}}</td>
                                                        <td>
                                                        <span v-show="producto.loading">
                                                            <b-spinner small label="Loading..." ></b-spinner>
                                                        </span>
                                                            <span v-show="producto.warning">
                                                            <i style="color: orange;" :id="'warning-'+index" class="fas fa-exclamation-triangle"></i>
                                                            <b-tooltip :target="'warning-'+index" triggers="hover">
                                                                No se ha podido actualizar el item, intenta recargar la página.
                                                            </b-tooltip>
                                                        </span>
                                                        </td>
                                                        <td @click.stop style="width: 20%">
                                                            <button @click="borrarItemVenta(index)"
                                                                    :disabled="mostrarSpinner" class="btn btn-danger"
                                                                    title="Borrar item"><i class="fas fa-trash"></i>
                                                            </button>
                                                            <button v-show="!!producto.discounts" v-b-modal.modal-producto-descuento @click="editarItem(producto,index)"
                                                                    :disabled="mostrarSpinner" class="btn btn-success"
                                                                    title="Descuento"><i class="fas fa-percentage"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr class="text-center" v-show="productosSeleccionados.length == 0"><td colspan="8">Ningún producto seleccionado</td></tr>
                                                    </tbody>
                                                </table>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-lg-12">
                        <div class="card" id="bottom-btns">
                            <div class="card-body" @if($agent->isDesktop()) style="padding: 10px" @endif>
                                <div class="bottom-btns-container">
                                    <div style="display: flex; justify-content: center;flex-wrap: nowrap;">
                                        <b-button :disabled="idpedido == -1"
                                                  @if($buscador_alternativo)
                                                  v-b-modal.modal-agregar-producto-alt
                                                  @else
                                                  v-b-modal.modal-agregar-producto
                                                  @endif
                                                  @click="abrirModalProducto"
                                                  variant="primary"><i class="fas fa-plus"></i>
                                            Agregar producto
                                        </b-button>
                                        <b-button :disabled="idpedido == -1" @click="limpiarPedido"
                                                  variant="danger"><i class="fas fa-times-circle"></i>
                                            Anular venta
                                        </b-button>
                                        <b-button :disabled="productosSeleccionados == 0" @click="imprimir('pedido')"
                                                  variant="secondary"><i class="fas fa-box-open"></i>
                                            Imprimir pedido
                                        </b-button>
                                        <b-button :disabled="productosSeleccionados == 0"
                                                  v-b-modal.credito-rapido
                                                  variant="secondary"><i class="fas fa-money-bill-wave"></i>
                                            Crédito rápido
                                        </b-button>
                                    </div>
                                    <div style="display: flex; justify-content: center;flex-wrap: nowrap;">
                                        @can('Pedido: procesar')
                                            <b-button :disabled="disabledTicket"
                                                      v-b-modal.modal-facturar
                                                      @click="comprobante='30'"
                                                      variant="info"><i class="fas fa-receipt"></i>
                                                Nota de venta
                                            </b-button>
                                            @if(!$emitir_solo_ticket)
                                                <b-button :disabled="productosSeleccionados == 0 || disabledVentas"
                                                          v-b-modal.modal-facturar
                                                          @click="comprobante='03'"
                                                          variant="warning"><i class="fas fa-file-invoice-dollar"></i>
                                                    Generar boleta
                                                </b-button>
                                                <b-button :disabled="productosSeleccionados == 0 || disabledVentas"
                                                          v-b-modal.modal-facturar
                                                          @click="comprobante='01'"
                                                          variant="warning"><i class="fas fa-file-invoice-dollar"></i>
                                                    Generar factura
                                                </b-button>
                                            @endif
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <modal-facturacion
            :tipo_doc="comprobante"
            :idpedido="idpedido"
            :total="totalVenta"
            :origen="'pedidos'"
            :fecha="'{{date('Y-m-d', strtotime(date('Y-m-d').' + 1 days'))}}'"
            :tipo_de_pago="{{json_encode(\sysfact\Http\Controllers\Helpers\DataTipoPago::getTipoPago())}}"
            :items="productosSeleccionados"
            v-on:imprimir="imprimir"
            v-on:obtener-mesas="obtener_pedidos"
            v-on:limpiar="limpiar(true)">
    </modal-facturacion>
    @if($buscador_alternativo)
    <modal-agregar-producto-alt
            ref="agregarPlato"
            :categorias="{{$categorias}}"
            :isdesktop="{{json_encode($agent->isDesktop())}}"
            v-on:agregar="agregarProducto"
            v-on:guardar="guardarPedido">
    </modal-agregar-producto-alt>
    @else
    <modal-agregar-producto
            ref="agregarPlato"
            :categorias="{{$categorias}}"
            :isdesktop="{{json_encode($agent->isDesktop())}}"
            :colapsar="{{json_encode($colapsar)}}"
            v-on:agregar="agregarProducto"
            v-on:guardar="guardarPedido">
    </modal-agregar-producto>
    @endif
    <modal-entrega
            :idpedido="idpedido"
            v-on:send="sendWS"
            v-on:delivery="obtener_pedidos">
    </modal-entrega>
    <modal-detalle
            :item="item"
            :show-precio="true"
            :can-edit-precio="@can('Pedido: editar precio') true @else false @endcan"
            v-on:actualizar="actualizarDetalle(null)">
    </modal-detalle>
    <modal-producto-descuento :item="item" v-on:agregar="agregarDescuento"></modal-producto-descuento>
    <modal-devolucion></modal-devolucion>
    <credito-rapido
            :idpedido="idpedido"
            :total="totalVenta"
            :items="productosSeleccionados"
            v-on:obtener-mesas="obtener_pedidos"
            v-on:limpiar="limpiar(true)">
    </credito-rapido>
@endsection
@section('script')
    <script>
        let app = new Vue({
                el: '.app',
                data: {
                    errorDatosVenta: [],
                    errorVenta: 0,
                    ordenes : [],
                    comprobante: '30',
                    piso:'1',
                    idpedido:-1,
                    idcliente: -1,
                    mostrarSpinner: false,
                    totalVenta:0,
                    productosSeleccionados:[],
                    productosSeleccionadosAux:[],
                    ticket:'',
                    num_item : -1,
                    element:'',
                    timer:null,
                    disabledTicket:true,
                    current_val:'',
                    empleados:[],
                    idvendedor:"{{$idvendedor}}",
                    item:{},
                    disabledNuevo: false,
                    disabledVentas: false,
                },
                created(){
                    this.obtener_pedidos();
                    this.obtenerEmpleados();
                },
                mounted(){
                    this.getHeights();
                    this.$options.sockets.onmessage = (message) => {
                        let obj = JSON.parse(message.data);
                        let baseUrl = window.location.protocol + '//' + window.location.host + '/';
                        if(obj.dominio === baseUrl){
                            this.obtener_pedidos();
                            if(obj.clave=='limpiar'){
                                this.limpiar(false);
                            } else {
                                if(this.idpedido && this.idpedido != -1){
                                    this.obtener_data_pedido(this.idpedido, true);
                                }
                            }
                        }
                    };
                },
                methods:{
                    checkStock(producto){
                        if(producto.tipo_producto == 1){
                            axios.post('/pedidos/check-stock',{
                                'idproducto':producto.idproducto,
                                'cantidad':producto.cantidad
                            })
                                .then(response => {
                                    producto.alert_stock = response.data.mensaje;
                                    producto.alert_color = response.data.color;
                                })
                                .catch(error => {
                                    console.log(error);
                                });
                        }
                    },
                    getHeights(){
                        let width = window.innerWidth
                            || document.documentElement.clientWidth
                            || document.body.clientWidth;

                        if(width > 992) {
                            let height = window.innerHeight
                                || document.documentElement.clientHeight
                                || document.body.clientHeight;

                            let bottom_btns = document.getElementById('bottom-btns').offsetHeight;
                            let top_btns = document.getElementById('top-btns').offsetHeight;
                            let h_box_pedidos = height - 90 - 141;
                            let h_box_items = height - 90 - top_btns - bottom_btns - 100;

                            let box_pedidos = document.getElementById('box-pedidos');
                            let box_items = document.getElementById('box-items');
                            Object.assign(box_pedidos.style, {height:h_box_pedidos+'px'});
                            Object.assign(box_items.style, {height:h_box_items+'px'});
                        }
                    },
                    abrirModalProducto(){
                        this.productosSeleccionadosAux = [ ...this.productosSeleccionados ]
                    },
                    disabled_ventas(){
                      this.disabledVentas = true;
                    },
                    reloadPage() {
                        window.location.reload();
                    },
                    agregarDescuento(obj){
                        let producto = this.productosSeleccionados[this.index];
                        producto['precio'] = obj.precio;
                        producto['cantidad'] = obj.cantidad;
                        this.actualizarDetalle(null);
                    },
                    editarItem(item, index = null){
                        this.item=item;
                        this.index = index;
                        this.num_item = item.num_item;
                    },
                    noFocus(input){
                        if(input.value != this.current_val){
                            this.actualizarDetalle(null);
                        }
                        input.setAttribute('readonly',"");
                    },
                    habilitar(num_item, el){
                        this.num_item = num_item;
                        this.element = el;
                        let input = document.getElementById(this.num_item+'-'+this.element)
                        input.removeAttribute('readonly');
                        input.focus();
                        this.current_val=input.value;
                    },
                    obtener_pedidos(){
                        axios.get('/pedidos/obtener-pedidos/')
                            .then(response => {
                                this.ordenes = response.data;
                            })
                            .catch(function (error) {
                                alert('Ha ocurrido un error.');
                                console.log(error);
                            });
                    },
                    obtener_data_pedido(id,socket=false){
                        this.mostrarSpinner = true;
                        let inputs = document.getElementsByClassName('td-dis');
                        for(input of inputs){
                            input.setAttribute('readonly',"");
                        }
                        axios.get('/pedidos/obtener-data-pedido/'+id)
                            .then(response => {
                                let data = response.data;
                                this.idpedido = data.pedido.idorden;
                                this.productosSeleccionados = data.productos_seleccionados;
                                this.idvendedor = data.pedido.idvendedor;
                                this.calcularTotales();
                                this.mostrarSpinner = false;
                                if(this.productosSeleccionados == 0){
                                    this.disabledTicket=true;
                                } else {
                                    this.disabledTicket=false;
                                }
                                if(!socket){
                                    window.scrollTo({
                                        top: document.body.scrollHeight + 500,
                                        behavior: 'smooth',
                                    });
                                }
                            })
                            .catch(error => {
                                alert('Ha ocurrido un error.');
                                console.log(error);
                                this.mostrarSpinner = false;
                            });
                    },
                    agregarProducto(obj){
                        let newItem = obj.newItem;
                        let item= obj.producto;
                        let existeProducto = null;
                        if(!newItem){
                            let ultimoProductoAgregado = this.productosSeleccionados[this.productosSeleccionados.length - 1]||false;
                            if(ultimoProductoAgregado){
                                existeProducto = ultimoProductoAgregado.idproducto === item.idproducto?ultimoProductoAgregado:null;
                            }
                        }

                        if (existeProducto && '{{$aumentar_cantidad_producto}}' === '1') {
                            existeProducto.cantidad++;
                            existeProducto.total = existeProducto.cantidad * existeProducto.precio;
                            this.num_item = this.productosSeleccionados.length;
                            if (this.timer) {
                                clearTimeout(this.timer);
                                this.timer = null;
                            }
                            this.timer = setTimeout(() => {
                                this.actualizarDetalle(null)
                            }, 600);
                        } else {
                            let productos = this.productosSeleccionados.push({...item});
                            let i = productos - 1;
                            this.$set(this.productosSeleccionados[i], 'num_item', i + 1);
                            this.$set(this.productosSeleccionados[i], 'loading', false);
                            this.$set(this.productosSeleccionados[i], 'warning', false);
                            this.$set(this.productosSeleccionados[i], 'cantidad', 1);
                            this.$set(this.productosSeleccionados[i], 'descuento', '0.00');
                            this.$set(this.productosSeleccionados[i], 'total', this.productosSeleccionados[i]['precio']);
                            this.disabledTicket = false;
                            this.checkStock(this.productosSeleccionados[i]);
                            this.calcularTotales();
                        }

                    },
                    guardarPedido(){
                        if(this.productosSeleccionados.length != this.productosSeleccionadosAux.length) {
                            this.procesar("{{action('PedidoController@update')}}",'editar');
                        }
                    },
                    nuevoDelivery(){
                        this.totalVenta = '0.00';
                        this.productosSeleccionados=[];
                        this.idpedido = -1;
                        this.ticket = '';
                        this.disabledTicket = true;
                        this.procesar("{{action('PedidoController@store')}}", 'nuevo')
                    },
                    procesar(accion, tipo){
                        let data = {
                            'idvendedor':this.idvendedor,
                            'idorden': this.idpedido,
                            'idcliente': -1,
                            'total': this.totalVenta,
                            'moneda': 'S/',
                            'comprobante': '30',
                            'observaciones': '',
                            'igv_incluido': 1,
                            'items': JSON.stringify(this.productosSeleccionados)
                        };

                        if(tipo == 'nuevo'){
                            this.disabledNuevo = true;
                            data['datos_entrega'] = JSON.stringify({direccion:'',referencia:'',contacto:'-',telefono:'', costo:'0'});
                        }

                        axios.post(accion, data)
                            .then(response => {
                                if (!response.data.idorden) {
                                    alert('Ha ocurrido un error. Intenta nuevamente.');
                                } else {
                                    this.totalVenta = response.data.total;
                                    this.idpedido = response.data.idorden;
                                    this.obtener_pedidos()
                                }
                                this.disabledNuevo = false;
                                window.scrollTo({
                                    top: document.body.scrollHeight + 500,
                                    behavior: 'smooth',
                                });
                                this.sendWS();
                            })
                            .catch(error => {
                                alert('Ha ocurrido un error.');
                                this.disabledNuevo = false;
                                console.log(error);
                            });
                    },
                    calcularTotales(){
                        let suma = 0;
                        for (let producto of this.productosSeleccionados) {
                            producto['total'] = (producto['precio'] * producto['cantidad']).toFixed(2);
                            suma += Number(producto.total);
                        }
                        this.totalVenta = suma.toFixed(2);
                    },
                    borrarItemVenta(index){
                        if(this.productosSeleccionados.length > 1){
                            this.mostrarSpinner = true;
                            this.productosSeleccionados.splice(index, 1);
                            this.calcularTotales();
                            axios.post('{{action('PedidoController@borrarItemPedido')}}',{
                                'idorden':this.idpedido,
                                'total': this.totalVenta,
                                'items': JSON.stringify(this.productosSeleccionados),
                            })
                                .then(response => {
                                    this.productosSeleccionados = response.data;
                                    this.obtener_pedidos();
                                    this.mostrarSpinner = false;
                                    this.sendWS();
                                })
                                .catch(error => {
                                    alert('Ha ocurrido un error.');
                                    console.log(error);
                                    this.mostrarSpinner = false;
                                });
                        } else {
                            this.limpiarPedido();
                        }

                    },
                    limpiarPedido(){
                        if(confirm('Se anulará la venta. Confirme la acción.')){
                            axios.delete('{{url('/pedidos/destroy')}}' + '/' + this.idpedido)
                                .then(() =>{
                                    this.limpiar(true);
                                })
                                .catch(error => {
                                    console.log(error);
                                });
                        }
                    },
                    actualizarDetalle(event){
                        if(event ==null || event.code == 'Enter' || event.code == 'NumpadEnter'){
                            let index = this.num_item - 1;
                            let producto = this.productosSeleccionados[index];
                            producto['loading'] = true;
                            producto['warning'] = false;
                            this.calcularTotales();
                            axios.post('{{action('PedidoController@actualizarDetalle')}}',{
                                'idorden':this.idpedido,
                                'total':this.totalVenta,
                                'item':JSON.stringify(producto)
                            })
                                .then(response => {
                                    this.checkStock(producto);
                                    this.current_val = producto['cantidad'];
                                    producto['loading'] = false;
                                    producto['precio'] = (Number(producto['precio'])).toFixed(2);
                                    if(response.data == 1){
                                        this.obtener_pedidos();
                                        let input = document.getElementById(+this.num_item+"-"+this.element);
                                        if(input){
                                            input.setAttribute('readonly',"");
                                        }
                                    } else {
                                        producto['warning'] = true;
                                    }
                                    this.sendWS();
                                })
                                .catch(error => {
                                    alert('Ha ocurrido un error.');
                                    producto['loading'] = false;
                                    producto['warning'] = true;
                                    console.log(error);
                                });
                        }
                    },
                    imprimir(file_or_id){

                        let src = '';
                        switch (file_or_id) {
                            case 'pedido':
                                src = "{{url('/pedidos/imprimir').'/'}}" + this.idpedido;
                                break;
                            case 'entrega':
                                src = "{{url('/pedidos/imprimir_entrega/').'/'}}" + this.idpedido;
                                break;
                            default:
                                src = "{{url('/ventas/imprimir/').'/'}}" + file_or_id;
                        }

                         @if(!$agent->isDesktop())

                            @if(isset(json_decode(cache('config')['interfaz'], true)['rawbt']) && json_decode(cache('config')['interfaz'], true)['rawbt'])

                                axios.get(src+'?rawbt=true')
                                    .then(response => {
                                        window.location.href = response.data;
                                    })
                                    .catch(error => {
                                        alert('Ha ocurrido un error al imprimir con RawBT.');
                                        console.log(error);
                                    });
                                /*let  beforeUrl = 'intent:';
                                afterUrl = '#Intent;package=ru.a402d.rawbtprinter;scheme=rawbt;component=ru.a402d.rawbtprinter.activity.PrintDownloadActivity;end;';
                                document.location=beforeUrl+encodeURI(src)+afterUrl;*/
                            @else
                                window.open(src, '_blank');
                            @endif
                        @else
                            let iframe = document.createElement('iframe');
                            document.body.appendChild(iframe);
                            iframe.style.display = 'none';
                            iframe.onload = function() {
                                setTimeout(function() {
                                    iframe.focus();
                                    iframe.contentWindow.print();
                                }, 0);
                            };
                            iframe.src = src;
                        @endif
                    },
                    limpiar(sendToSocket){
                        if(sendToSocket){
                            this.totalVenta = '0.00';
                            this.ticket = '';
                            this.idvendedor="{{$idvendedor}}";
                            this.obtener_pedidos();
                            this.disabledTicket = true;
                            this.productosSeleccionados=[];
                            this.idpedido = -1;
                            this.sendWS('limpiar')
                        }
                    },
                    obtenerEmpleados(){
                        axios.get('/pedidos/obtener-empleados')
                            .then(response => {
                                this.empleados = response.data.empleados;
                                this.idvendedor = response.data.idvendedor;
                            })
                            .catch(error => {
                                alert('Ha ocurrido un error.');
                                console.log(error);
                            });
                    },
                    cambiarEmpleado(){
                        if(this.idpedido != -1){
                            this.mostrarSpinner = true;
                            axios.post('/pedidos/cambiar-vendedor',{
                                'idpedido':this.idpedido,
                                'idvendedor':this.idvendedor
                            })
                                .then(response => {
                                    if(response.data!=1){
                                        alert('Ha ocurrido un error al actualizar el vendedor');
                                    } else {
                                        this.obtener_pedidos();
                                        this.sendWS();
                                    }
                                    this.mostrarSpinner = false;
                                })
                                .catch(error => {
                                    this.mostrarSpinner = false;
                                    alert('Ha ocurrido un error.');
                                    console.log(error);
                                });
                        }
                    },
                    cambiarCantidad(index, tipo){
                        let producto = this.productosSeleccionados[index];
                        if(tipo == '+'){
                            producto.cantidad++;
                        } else {
                            producto.cantidad--;
                        }
                        if (this.timer) {
                            clearTimeout(this.timer);
                            this.timer = null;
                        }
                        this.timer = setTimeout(() => {
                            this.actualizarDetalle(null)
                        }, 500);
                    },
                    sendWS(accion='') {
                        this.$socket.addEventListener('open', (event) => {
                        });
                        let baseUrl = window.location.protocol + '//' + window.location.host + '/';
                        let data = {dominio:baseUrl,clave:accion,valor:''};
                        if (this.$socket.readyState === WebSocket.OPEN) {
                            this.$socket.send(JSON.stringify(data));
                        } else {
                            console.error('El WebSocket no está en estado abierto (OPEN)');
                        }
                    },
                }
            }
        );
    </script>
@endsection
@section('css')
    <style>
        #top-btns{
            display: flex;
            justify-content: space-between;
        }
        .bottom-btns-container{
            display: flex;
            flex-wrap: nowrap;
            justify-content: space-between;
        }
        .bottom-btns-container button{
            display: flex;
            flex-wrap: wrap;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 3px;
            max-width: 110px;
            padding: 10px 15px;
        }
        @media (max-width: 700px) {
            .bottom-btns-container{
                flex-wrap: wrap;
                justify-content: center;
            }
        }
        @media (max-width: 532px) {
            #top-btns {
                flex-direction: column;
                flex-wrap: wrap;
                align-items: center;
            }
            .bottom-btns-container button{
                padding: 8px 10px;
            }
        }

    </style>
@endsection