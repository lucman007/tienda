@extends('layouts.main')
@section('titulo', 'Registrar')
@section('contenido')
    @php
        $agent = new \Jenssegers\Agent\Agent();
        $emitir_solo_ticket = json_decode(cache('config')['interfaz'], true)['emitir_solo_ticket']??false;
        $tipo_cambio_compra = cache('opciones')['tipo_cambio_compra'];
        $unidad_medida = \sysfact\Http\Controllers\Helpers\DataUnidadMedida::getUnidadMedida();
        $can_gestionar = false;
    @endphp
    @can('Inventario: gestionar producto')
        @php
            $can_gestionar = true
        @endphp
    @endcan
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <h3 class="titulo-admin-1">Nota de venta</h3>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 mt-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-3 form-group">
                                <label>Serie y correlativo</label>
                                <div class="row no-gutters">
                                    <div class="col-lg-4">
                                        <input type="text" v-model="serie" name="serie" placeholder="Serie"
                                               class="form-control" maxlength="4" disabled>
                                    </div>
                                    <div class="col-lg-1 text-center">
                                        _
                                    </div>
                                    <div class="col-lg-7">
                                        <input type="text" v-model="correlativo" name="correlativo"
                                               placeholder="Correlativo" class="form-control" maxlength="8" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 form-group">
                                <label>Moneda</label>
                                <select :disabled="comprobante=='07.01' || comprobante=='07.02' || comprobante=='08.01' || comprobante=='08.02'"
                                        v-model="moneda" class="custom-select" id="selectComprobante">
                                    <option value="S/">Soles</option>
                                    <option value="USD">Dólares</option>
                                </select>
                            </div>
                            <div class="col-lg-2 form-group">
                                <label>Fecha</label>
                                <input type="date" v-model="fecha" max="{{date('Y-m-d')}}"
                                       class="form-control">
                            </div>
                            <div class="col-lg-2 form-group">
                                <label>Tipo de cambio</label>
                                <input type="text" v-model="tipoCambio"
                                       class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 mb-3"
                 v-show="comprobante=='30' ||
                  comprobante == '01' ||
                   comprobante == '03' ||
                   ((comprobante == '08.01' || comprobante == '08.02') && comprobanteReferencia != '')">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            @if(json_decode(cache('config')['interfaz'], true)['buscador_clientes'] == 1)
                                <div class="col-lg-6 order-2 order-lg-1">
                                    <autocomplete-cliente v-on:agregar_cliente="agregarCliente"
                                                          v-on:borrar_cliente="borrarCliente"
                                                          ref="suggestCliente"></autocomplete-cliente>
                                </div>
                                <div class="col-lg-6 order-1 order-lg-2">
                                    <b-button v-b-modal.modal-nuevo-cliente
                                              class="mb-4" variant="primary"><i class="fas fa-plus"
                                                                                v-show="!mostrarSpinnerCliente"></i>
                                        <b-spinner v-show="mostrarSpinnerCliente" small label="Loading..."></b-spinner>
                                        Nuevo cliente
                                    </b-button>
                                </div>
                            @else
                                <div class="col-lg-12">
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
                                <div class="col-lg-6">
                                    <input type="text" v-model="nombreCliente" class="form-control mb-2"
                                           placeholder="Cliente" disabled readonly>
                                </div>
                            @endif
                        </div>
                        <div class="row mt-4">
                            @if(json_decode(cache('config')['interfaz'], true)['buscador_productos'] == 1)
                                <div v-show="!adelantoAgregado" class="col-lg-6 buscar_producto order-2 order-lg-1">
                                    <autocomplete ref="suggest" v-on:agregar_producto="agregarProducto"></autocomplete>
                                </div>
                                <div v-show="!adelantoAgregado" class="col-lg-3 order-1 order-lg-2">
                                    <b-button v-b-modal.modal-nuevo-producto class="float-right float-lg-left"
                                              variant="primary"><i class="fas fa-plus" v-show="!mostrarSpinnerProducto"></i>
                                        <b-spinner v-show="mostrarSpinnerProducto" small label="Loading..."></b-spinner>
                                        Nuevo producto
                                    </b-button>
                                    <b-button title="Agregar item" class="mb-4 ml-1 mt-lg-0 float-left" :disabled="disabledNr" @click="agregar_nr('00NR')"
                                              variant="success"><i class="fas fa-plus"></i>
                                        <b-spinner v-show="mostrarSpinnerProducto" small label="Loading..."></b-spinner>
                                        Item
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
                        </div>
                        @if($agent->isDesktop())
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm tabla-facturar">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col" style="width: 10px"></th>
                                    <th scope="col" style="width: 200px">Producto</th>
                                    <th scope="col" style="width: 250px">Caracteristicas</th>
                                    <th scope="col" style="width: 90px">Precio</th>
                                    <th scope="col" style="width: 110px">Cantidad</th>
                                    <th scope="col" style="width: 70px; text-align: center">Dscto</th>
                                    <th scope="col" style="width: 80px; text-align: center">Subtotal</th>
                                    <th scope="col" style="width: 80px; text-align: center">Total</th>
                                    <th scope="col" style="width: 100px;"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(producto,index) in productosSeleccionados" :key="index">
                                    <td></td>
                                    <td>@{{producto.cod_producto}} - @{{producto.nombre}} <br>
                                        <span style="font-size: 11px; color: #0b870b;" v-for="item in producto.items_kit">+ (@{{ item.cantidad }}) @{{item['nombre']}}<br></span>
                                    </td>
                                    <td><textarea rows="1" @keyup="agregarCaracteristicasSession()" class="form-control texto-desc" ref="textareas" @input="expandirYContarTextarea(index,$event)"
                                                  v-model="producto.presentacion"></textarea><p v-show="charCounts[index] > 100"  :class="{ 'text-danger': charCounts[index] > 250 }" class="textCountChar">@{{ charCounts[index] }} caracteres</p></td>
                                    <td><input onfocus="this.select()" @keyup="calcular(index)" class="form-control" type="text"
                                               v-model="producto.precio"></td>
                                    <td>
                                        <b-input-group>
                                            <input onfocus="this.select()" @keyup="calcular(index)" class="form-control" type="text"
                                                   v-model="producto.cantidad">
                                            <b-input-group-append>
                                                <b-input-group-text style="font-size: 10px !important; font-weight: 700;">
                                                    @{{ (producto.unidad_medida).split('/')[1] }}
                                                </b-input-group-text>
                                            </b-input-group-append>
                                        </b-input-group>
                                    </td>
                                    <td class="text-center">@{{(Number(producto.descuento)).toFixed(2)}} <br><span v-show="Number(producto.descuento) > 0" style="color:green">(@{{redondearSinCeros(Number(producto.porcentaje_descuento))+'%'}})</span></td>
                                    <td style="display:none;"><input @keyup="calcular(index)" class="form-control"
                                                                     type="text" v-model="producto.descuento"></td>
                                    <td class="text-center">@{{Number(producto.subtotal).toFixed(2)}}</td>
                                    <td class="text-center">@{{Number(producto.total).toFixed(2)}}</td>
                                    <td style="text-align: right">
                                        <b-button :disabled="producto['precio']<=0 || producto['cantidad']<=0" v-b-modal.modal-descuento @click="editarItem(producto,index)" variant="success" title="Agregar descuento">
                                            <i class="fas fa-percentage"></i>
                                        </b-button>
                                        <button @click="borrarItemVenta(index)" class="btn btn-danger" title="Borrar item"><i
                                                    class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr class="text-center" v-show="productosSeleccionados.length == 0">
                                    <td colspan="12">Ningún producto seleccionado</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
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
                                    <td>@{{producto.cod_producto == '00NR'?producto.presentacion:producto.nombre}} x @{{producto.cantidad}}</td>
                                    <td>@{{(Number(producto.total)).toFixed(2)}}</td>
                                    <td @click.stop >
                                        <b-button :disabled="producto['precio']<=0 || producto['cantidad']<=0" v-b-modal.modal-descuento @click="editarItem(producto,index)" variant="success" title="Agregar descuento">
                                            <i class="fas fa-percentage"></i>
                                        </b-button>
                                        <button @click="editarItem(producto,index)" class="btn btn-warning" v-b-modal.modal-afectacion
                                                title="Afectación"><i class="fas fa-stream"></i>
                                        </button>
                                        <button @click="borrarItemVenta(index)" class="btn btn-danger"
                                                title="Borrar item"><i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr class="text-center" v-show="productosSeleccionados.length == 0"><td colspan="8">No has agregado productos</td></tr>
                                </tbody>
                            </table>
                        @endif
                        <div class="dropdown-divider"></div>
                        <div class="row mt-3">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <input class="form-control"
                                           v-model="doc_observacion" type="text" placeholder="Observación">
                                </div>
                            </div>
                        </div>
                        <div>
                            <b-alert v-for="mensaje in mensajesStock" show fade dismissible :key="mensaje.id" :variant="mensaje.estilo" v-on:dismissed="cerrarMensajeStock(mensaje.id)">
                                @{{ mensaje.mensaje }}
                            </b-alert>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        Acciones
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4 tipoPagoDiv">
                                <label>Tipo de pago</label>
                                <div class="row">
                                    <div class="col-lg-6 form-group">
                                        <select :disabled="mostrarProgresoGuardado || productosSeleccionados.length==0 || comprobante=='07.01' || comprobante=='07.02' || comprobante=='08.01' || comprobante=='08.02' || adelantoAgregado"
                                                v-model="tipoPago" class="custom-select">
                                            <option value="1">Contado</option>
                                            <option value="2">Crédito</option>
                                        </select>
                                    </div>
                                    <div v-show="tipoPago==1" class="col-lg-6 form-group">
                                        <select :disabled="mostrarProgresoGuardado || productosSeleccionados.length==0 || comprobante=='07.01' || comprobante=='07.02' || comprobante=='08.01' || comprobante=='08.02'"
                                                v-model="tipoPagoContado" class="custom-select">
                                            @php
                                                $tipo_pago = \sysfact\Http\Controllers\Helpers\DataTipoPago::getTipoPago();
                                            @endphp
                                            @foreach($tipo_pago as $pago)
                                                @if($pago['num_val'] != 2)
                                                <option :disabled="'{{$pago['num_val']}}' == 4 && adelantoAgregado" value="{{$pago['num_val']}}">{{$pago['label']}}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div v-show="tipoPago==1 && tipoPagoContado == 4" class="col-lg-6 form-group">
                                        <b-button v-b-modal.modal-pagofraccionado variant="primary"><i
                                                    class="fas fa-edit"></i> Editar pago
                                        </b-button>
                                    </div>
                                    <div v-show="tipoPago==2" class="col-lg-6 form-group">
                                        <b-button
                                                :disabled="mostrarProgresoGuardado || productosSeleccionados.length==0 || ((comprobante=='07.01' || comprobante=='07.02') && tipo_nota_electronica != 13) || comprobante=='08.01' || comprobante=='08.02'"
                                                @click="abrir_modal('pago')" variant="primary"><i
                                                    class="fas fa-plus"></i> Cuotas (@{{cuotas.length}})
                                        </b-button>
                                    </div>
                                </div>
                                <div class="alert alert-primary mt-3" v-if="Object.keys(objAdelanto).length > 0">
                                    Adelanto: @{{ objAdelanto.total_venta }} - @{{objAdelanto.serie}}-@{{objAdelanto.correlativo}}
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <label></label>
                                <div class="form-group text-center">
                                    <b-button :disabled="mostrarProgresoGuardado || productosSeleccionados.length==0"
                                              class="mb-2" @click="procesarVenta"
                                              variant="success">
                                        <i v-show="!mostrarProgresoGuardado" class="fas fa-save"></i>
                                        <b-spinner v-show="mostrarProgresoGuardado" small
                                                   label="Loading..."></b-spinner>
                                        Procesar
                                    </b-button>
                                    <b-button class="mb-2" @click="limpiar" variant="danger"><i class="fas fa-ban"></i>
                                        Cancelar
                                    </b-button>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <table style="width:100%;">
                                    <tr v-show="descuentos > 0">
                                        <td style="width: 50%">DESCUENTOS:</td>
                                        <td>@{{ moneda }} @{{ descuentos }}</td>
                                    </tr>
                                    <tr>
                                        <td style="width: 50%">OP. GRAVADAS:</td>
                                        <td>@{{ moneda }} @{{ gravadas.toFixed(2) }}</td>
                                    </tr>
                                </table>
                                <p class="p-2 mt-2 total-venta">@{{ moneda }} @{{ totalVenta }}</p>
                                <div class="container" v-show="codigo_tipo_factura == '1001' || codigo_tipo_factura == '1'">
                                    <div class="row">
                                        <span class="alert alert-info col-lg-12" role="alert">@{{ codigo_tipo_factura == '1001' ? 'DETRACCIÓN':'RETENCIÓN' }} (@{{ montoDeduccionPorcentaje }}%): @{{ moneda }} @{{ montoDeduccion }}</span>
                                        <span v-show="tipoPago == 2" class="alert alert-info col-lg-12" role="alert">MONTO NETO PEND. DE PAGO: @{{ moneda }} @{{montoNePenPago}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--INICIO MODAL PAGO FRACCIONADO -->
    <b-modal size="md" id="modal-pagofraccionado" ref="modal-pagofraccionado" @ok="">
    <template slot="modal-title">
        Pago fraccionado
    </template>
    <div class="container">
        <div class="row">
            <div v-for="pago,index in pago_fraccionado" class="col-lg-12 mb-3">
                <div class="row">
                    <div class="col-lg-4">
                        <label>Monto</label>
                        <input v-model="pago.monto" type="number" class="form-control" onfocus="this.select()">
                    </div>
                    <div class="col-lg-6">
                        <label>Tipo de pago</label>
                        <select v-model="pago.tipo" class="custom-select">
                            @foreach($tipo_pago as $pago)
                                @if($pago['num_val'] != 2)
                                    <option v-show="!({{$pago['num_val']}} == 4 || {{$pago['num_val']}} == 2)" value="{{$pago['num_val']}}">{{$pago['label']}}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2" v-show="index > 1">
                        <button @click="borrarFraccionado(index)" style="margin-top: 20px" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <b-button variant="primary" @click="agregarFraccionado"><i
                            class="fas fa-plus"></i>
                </b-button>
            </div>
        </div>
    </div>
    <template #modal-footer="{ ok, cancel}">
        <b-button variant="secondary" @click="cancel()">
            Listo
        </b-button>
    </template>
    </b-modal>
    <!--FIN MODAL PAGO FRACCIONADO -->
    <!--INICIO MODAL TIPO DE PAGO -->
    <b-modal size="md" id="modal-tipopago" ref="modal-tipopago" @ok="">
    <template slot="modal-title">
        Pago a crédito
    </template>
    <div class="container">
        <div class="row">
            <div v-for="(cuota,index) in cuotasAux" class="col-lg-12 mb-3" :key="index">
                <div class="row">
                    <div class="col-lg-5">
                        <label>Cuota @{{ index + 1 }} (@{{ moneda }})</label>
                        <input onfocus="this.select()" v-model="cuota.monto" type="text" class="form-control">
                    </div>
                    <div class="col-lg-6">
                        <label>Fecha de pago:</label>
                        <input min="{{date('Y-m-d', strtotime(date('Y-m-d').' + 1 days'))}}" type="date" v-model="cuota.fecha" name="fechaCuota"
                               class="form-control">
                    </div>
                    <div class="col-lg-1">
                        <i @click="borrarCuota(index)" class="fas fa-times-circle btnBorrarCuota"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 mb-4">
                <button @click="agregarCuota(null)" class="btn btn-info"><i class="fas fa-plus"></i> Agregar cuota
                </button>
            </div>
        </div>
    </div>
    <template #modal-footer="{ ok, cancel}">
        <b-button variant="secondary" @click="cancelarCuotas()">
            Cancel
        </b-button>
        <b-button variant="primary" @click="agregarCuotasVenta">
            OK
        </b-button>
    </template>
    </b-modal>
    <!--FIN MODAL TIPO DE PAGO -->
    <modal-ubigeo
            :es_ubigeo="true"
            v-on:agregar_ubigeo="agregarUbigeo">
    </modal-ubigeo>
    <modal-producto
            v-bind:stock="true"
            v-on:agregar_producto="agregarProducto">
    </modal-producto>
    <modal-cliente
            v-on:agregar_cliente="agregarCliente">
    </modal-cliente>
    <agregar-cliente
            v-on:agregar="agregarClienteNuevo">
    </agregar-cliente>
    <agregar-producto
            :tipo_cambio="{{$tipo_cambio_compra}}"
            :unidad_medida="{{json_encode($unidad_medida)}}"
            :can_gestionar="{{json_encode($can_gestionar)}}"
            :tipo_de_producto="1"
            :origen="'ventas'"
            v-on:agregar="agregarProductoNuevo">
    </agregar-producto>
    <modal-detalle
            :item="item"
            :show-precio="true"
            :can-edit-precio="@can('Facturación: facturar') true @else false @endcan"
            v-on:actualizar="actualizarDetalle">
    </modal-detalle>
    <modal-descuento ref="descuentos"
                     :item="item"
                     :moneda="moneda"
                     :igv="esConIgv"
                     :global="esDstoGlobal"
                     :data-descuento="dataDescuento"
                     v-on:actualizar="actualizarDescuento"
                     v-on:actualizar-detalle="actualizarDetalle">
        ></modal-descuento>
    @php
        $guia_data = json_decode(cache('config')['guia'], true);
    @endphp
@endsection
@section('script')
    <script>

        let app = new Vue({
            el: '.app',
            data: {
                idpresupuesto: '<?php echo isset($_GET['presupuesto']) ? $_GET['presupuesto'] : null ?>',
                idguia: '<?php echo isset($_GET['guia']) ? $_GET['guia'] : null ?>',
                idproduccion: '<?php echo isset($_GET['produccion']) ? $_GET['produccion'] : null ?>',
                accion: 'insertar',
                mostrarProgresoGuardado: false,
                numeroGuia: '',
                numeroOc: '',
                fecha: '{{date('Y-m-d')}}',
                serie: '{{$serie_comprobantes['boleta']}}',
                correlativo: '',
                motivo: '',
                tipo_nota_electronica: '01',
                codigo_tipo_factura: '0101',
                tipoDetraccion: '037/12',
                montoDeduccion:0,
                montoNePenPago:0,
                montoDeduccionPorcentaje: 0,
                esConIgv:<?php echo json_encode(json_decode(cache('config')['interfaz'], true)['igv_incluido']) ?>,

                clienteSeleccionado: {},
                buscar: '',
                mostrarSpinnerCliente: false,

                productosSeleccionados: [],
                mostrarSpinnerProducto: false,

                listaDocumentos: [],
                comprobanteReferencia: '',

                totalVenta: 0.00,
                subtotalVenta: 0.00,
                igv: 0.00,
                gravadas: 0.00,
                gratuitas: 0.00,
                exoneradas: 0.00,
                inafectas: 0.00,
                descuentos: 0.00,
                porcentaje_descuento_global: 0,
                monto_descuento_global: 0.00,
                base_descuento_global: 0.00,
                moneda: 'S/',
                tipoPago: 1,
                tipoPagoContado: 1,
                cuotas: [],
                cuotasAux: [],
                comprobante: '30',
                inhabilitarComprobante: false,
                tipo_busqueda: '',
                esConGuia: 0,
                guia_datos_adicionales: {
                    direccion: '',
                    ubigeo: '',
                    peso: '',
                    bultos: '',
                    tipo_doc_transportista: '6',
                    num_doc_transportista: '',
                    razon_social_transportista: '',
                    placa_vehiculo:"<?php echo $guia_data['placa']??'' ?>",
                    dni_conductor:"<?php echo $guia_data['num_doc']??'' ?>",
                    licencia_conductor:"<?php echo $guia_data['licencia']??'' ?>",
                    nombre_conductor:"<?php echo $guia_data['nombre']??'' ?>",
                    apellido_conductor:"<?php echo $guia_data['apellido']??'' ?>",
                    categoria_vehiculo:"<?php echo $guia_data['categoria_vehiculo']??'M1_L' ?>",
                    codigo_traslado: '01',
                    fecha_traslado: '{{date('Y-m-d')}}',
                    doc_relacionado: '-1',
                    num_doc_relacionado: '',
                    tipo_transporte:<?php echo json_encode(json_decode(cache('config')['guia'], true)['tipo_transporte']) ?>,
                },
                numero_guia_fisica: '',
                doc_relacionado_nc:'',
                guiasRelacionadas: [],
                guiasRelacionadasAux: [],
                tipoCambio: <?php echo cache('opciones')['tipo_cambio_compra'] ?>,
                nombreCliente: "",
                idventa_modifica:-1,
                disabledNr:false,
                tipo_descuento_global: false,
                item:{},
                index:-1,
                esDstoGlobal: false,
                dataDescuento:{},
                spinnerRuc:false,
                domicilioFiscalCliente:true,
                doc_observacion:"MÁS IGV",
                mensajesStock: [],
                pago_fraccionado:[
                    {
                        monto: '0.00',
                        tipo: '1'
                    },
                    {
                        monto: '0.00',
                        tipo: '3'
                    },
                ],
                anulacion_factura_exportacion:'',
                charCounts: [],
                esAdelanto:false,
                adelantoAgregado: false,
                buscarAdelanto: false,
                objAdelanto: {}
            },
            mounted() {
                if (localStorage.getItem('productos')) {
                    try {
                        this.productosSeleccionados = JSON.parse(localStorage.getItem('productos'));
                        this.calcularTotalVenta();
                    } catch (e) {
                        localStorage.removeItem('productos');
                    }
                }
                if (localStorage.getItem('cliente')) {
                    this.clienteSeleccionado = JSON.parse(localStorage.getItem('cliente'));
                    this.nombreCliente = this.clienteSeleccionado['num_documento']+' - '+this.clienteSeleccionado['nombre'];
                }
                if (localStorage.getItem('esConIgv')) {
                    this.esConIgv = localStorage.getItem('esConIgv') == 'true' ? true : false;
                }
                this.countTextareas();

            },
            created(){
                this.calcularTotalVenta();
                this.obtenerCorrelativo();
                if (this.idpresupuesto !== '') {
                    this.agregarDocumento(this.idpresupuesto, true, false, false);
                }
                if (this.idguia !== '') {
                    this.agregarDocumento(this.idguia, false, true, false);
                }
                if (this.idproduccion !== '') {
                    this.agregarDocumento(this.idproduccion, false, false, true);
                }
            },
            methods: {
                agregarAdelanto(item){
                    this.objAdelanto = item;
                    this.$refs['modal-documento'].hide();
                },
                agregarFraccionado(){
                    this.pago_fraccionado.push({monto: '0.00', tipo: '1'});
                },
                borrarFraccionado(index){
                    this.pago_fraccionado.splice(index,1);
                },
                redondearSinCeros(numero) {
                    let numeroRedondeado = parseFloat(numero.toFixed(3));
                    return numeroRedondeado.toString().replace(/(\.0*|(?<=(\..*))0*)$/, '');
                },
                setAfectacion(afectacion){
                    let producto = this.productosSeleccionados[this.index];
                    producto['tipoAfectacion']=afectacion;
                    this.calcular(this.index);
                    this.$refs['modal-afectacion'].hide();
                },
                actualizarDescuento(obj){
                    if(this.esDstoGlobal){
                        this.monto_descuento_global=obj['monto'];
                        this.porcentaje_descuento_global=obj['porcentaje'];
                        this.tipo_descuento_global=obj['tipo_descuento'];
                        this.calcularTotalVenta();
                    } else {
                        let producto = this.productosSeleccionados[this.index];
                        producto['tipo_descuento']=obj['tipo_descuento'];
                        producto['porcentaje_descuento']=obj['porcentaje'];
                        producto['descuento']=obj['monto'];
                        producto['descuento_por_und']=obj['porUnidad'];
                        if(obj['recalcular']){
                            this.calcular(this.index);
                        }
                    }
                },
                consultaRucDni(tipo, numero){
                    if(tipo == 6 && numero.length != 11){
                        this.alerta('Ingresa un ruc válido de 11 dígitos');
                        return;
                    }
                    if(tipo == 1 && numero.length != 8){
                        this.alerta('Ingresa un dni válido de 8 dígitos');
                        return;
                    }
                    this.spinnerRuc=true;
                    axios.post('/helper/buscar-ruc', {
                        'num_doc': numero,
                        'tipo_doc': tipo,
                    })
                        .then(response => {
                            let data=response.data;
                            if(!data || data.length == 0 || !data['success']){
                                this.alerta('No se obtuvieron resultados, ingresa el nombre o razón social manualmente.');
                            } else {
                                if(this.guia_datos_adicionales.tipo_transporte === '01'){
                                    this.guia_datos_adicionales.razon_social_transportista = data.nombre_o_razon_social;
                                } else {
                                    let ex = data.nombre_o_razon_social.split(' ');
                                    this.guia_datos_adicionales.nombre_conductor = ex[ex.length - 1];
                                    this.guia_datos_adicionales.apellido_conductor = ex[0];
                                }

                            }
                            this.spinnerRuc=false;
                        })
                        .catch(error => {
                            this.spinnerRuc=false;
                            this.alerta('Ocurrió un error al obtener el dni');
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
                            descuento: this.monto_descuento_global,
                            porcentaje_descuento: this.porcentaje_descuento_global,
                            tipo_descuento: this.tipo_descuento_global
                        };
                        this.esDstoGlobal = true;
                    }
                },
                actualizarDetalle(){
                    this.calcular(this.index);
                },
                agregarCuota(total){
                    let monto = '0.00';
                    if (this.cuotasAux.length > 0 && total) {
                        return;
                    } else {
                        if (total) {
                            monto = Number(total).toFixed(2);
                        }
                    }
                    this.cuotasAux.push({
                        monto: monto,
                        fecha: '{{date('Y-m-d', strtotime(date('Y-m-d').' + 1 days'))}}',
                    });
                },
                agregarDescuentoNC(){
                    //TIPO DE NOTA ELECTRONICA DESCUENTO
                    axios.post('{{action('VentaController@obtenerDecuentoNc')}}', {
                        'idproducto':-2
                    })
                        .then(response => {
                            this.agregarProducto(response.data);
                        })
                        .catch(function (error) {
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });

                },
                borrarCuota(index){
                    this.cuotasAux.splice(index, 1);
                },
                agregarCuotasVenta(){

                    for (let cuota of this.cuotasAux) {
                        if ((Number(cuota.monto)) <= 0) {
                            this.alerta('Solo se admiten casillas con cuotas mayor a 0.00');
                            return;
                        }
                        if (!cuota.fecha) {
                            this.alerta('Una de las fechas de pago no tiene el formato correcto');
                            return;
                        }
                    }

                    this.cuotas = Object.assign([], this.cuotasAux);
                    this.$refs['modal-tipopago'].hide();

                },
                agregarGuia(btnAdd){
                    if (this.guiasRelacionadasAux.length == 0 || btnAdd) {
                        this.guiasRelacionadasAux.push({
                            correlativo:''
                        });
                    }
                },
                borrarGuia(index){
                    this.guiasRelacionadasAux.splice(index, 1);
                },
                agregarGuiasRel(){

                    for (let guia of this.guiasRelacionadasAux) {
                        if (guia.correlativo.length == 0) {
                            this.alerta('No dejes casillas en blanco');
                            return;
                        }
                        let regex = new RegExp(/^(?:\d+|T\d{3}|EG\d{2})-(?!.*-).*$/i);
                        if (!regex.test(guia.correlativo)) {
                            this.alerta('Una de las casillas contiene un número de guía no válido. Usa solo números para guías físicas e inicia con T o EG para guías electrónicas.');
                            return;
                        }
                    }

                    this.guiasRelacionadas = Object.assign([], this.guiasRelacionadasAux);
                    this.$refs['modal-guias'].hide();

                },
                cancelarGuiasRel(){
                    this.guiasRelacionadasAux = [];
                    this.$refs['modal-guias'].hide();
                },
                cancelarCuotas(){
                    this.cuotasAux = [];
                    this.$refs['modal-tipopago'].hide();
                },
                obtenerCorrelativo(){
                    this.cambiarSerie();
                    axios.get('/ventas/obtenerCorrelativo' + '/' + this.comprobante)
                        .then(response => {
                            this.correlativo = response.data;
                        })
                        .catch(error => {
                            this.alerta('No hay venta registrada. Ingresa el correlativo manualmente');
                            console.log(error);
                        });
                },
                agregarCliente(obj){
                    this.clienteSeleccionado = obj;
                    this.nombreCliente = this.clienteSeleccionado['num_documento']+' - '+this.clienteSeleccionado['nombre'];
                    if(this.domicilioFiscalCliente){
                        this.guia_datos_adicionales.direccion = this.clienteSeleccionado['direccion']
                    }
                    this.agregarClienteSession();
                },
                agregarClienteNuevo(obj){
                    if(this.$refs['suggestCliente']){
                        this.$refs['suggestCliente'].agregarCliente(obj);
                    } else {
                        this.agregarCliente(obj)
                    }
                },
                borrarCliente(){
                    this.clienteSeleccionado = {};
                },
                agregarProductoNuevo(nombre){
                    if(this.$refs['suggest']){
                        this.$refs['suggest'].query = nombre;
                        this.$refs['suggest'].autoComplete();
                    }
                },
                obtenerDocumentos(copiar){
                    let filtro_comprobante = -1;

                    if (typeof copiar === 'number' && copiar < 0) {
                        filtro_comprobante = copiar;
                    } else if (!copiar) {
                        switch (this.comprobante) {
                            case '07.01':
                            case '08.01':
                                filtro_comprobante = '03';
                                break;
                            case '07.02':
                            case '08.02':
                                filtro_comprobante = '01';
                                break;
                        }
                    }

                    axios.post('{{action('VentaController@obtenerDocumentos')}}', {
                        'textoBuscado': this.buscar,
                        'comprobante': filtro_comprobante
                    })
                        .then(response => {
                            this.listaDocumentos = response.data;
                        })
                        .catch(error => {
                            this.alerta('Ha ocurrido un error al obtener los documentos');
                            console.log(error);
                        });
                },
                agregarDocumento(idventa, esDesdePresupuesto, esDesdeGuia, esDesdeProduccion){

                    let post_action = '{{action('VentaController@copiarVenta')}}';

                    if (esDesdePresupuesto) {
                        post_action = '{{action('VentaController@copiarPresupuesto')}}'
                    }

                    if (esDesdeGuia) {
                        post_action = '{{action('VentaController@copiarGuia')}}'
                    }

                    if (esDesdeProduccion) {
                        post_action = '{{action('VentaController@copiarProduccion')}}'
                    }

                    axios.post(post_action, {
                        'idventa': idventa
                    })
                        .then(response => {
                            let datos = response.data;
                            if(this.$refs['suggestCliente']){
                                this.$refs['suggestCliente'].agregarCliente(datos.cliente);
                            } else {
                                this.clienteSeleccionado = datos.cliente;
                                this.nombreCliente = this.clienteSeleccionado['num_documento']+' - '+this.clienteSeleccionado['nombre'];
                            }
                            this.tipoPago = datos.tipo_pago;
                            this.tipoPagoContado = datos.tipo_pago_contado;
                            this.comprobanteReferencia = datos['comprobante_referencia'];
                            this.esConIgv = datos.igv_incluido === 1;
                            this.productosSeleccionados = datos.productos;
                            this.porcentaje_descuento_global = datos.facturacion.porcentaje_descuento_global * 100;
                            this.tipo_descuento_global = datos.facturacion.tipo_descuento_global;
                            this.base_descuento_global = datos.facturacion.base_descuento_global;
                            this.monto_descuento_global = datos.facturacion.descuento_global;
                            this.gravadas = datos.facturacion.total_gravadas;
                            this.exoneradas = datos.facturacion.total_exoneradas;
                            this.inafectas = datos.facturacion.total_inafectas;
                            this.gratuitas = datos.facturacion.total_gratuitas;
                            this.descuentos = datos.facturacion.total_descuentos;
                            this.igv = datos.facturacion.igv;
                            this.totalVenta = datos.total_venta;
                            this.subtotalVenta = datos.facturacion.valor_venta_bruto;
                            this.numero_guia_fisica = datos.facturacion.guia_relacionada;
                            this.numeroOc = datos.facturacion.oc_relacionada;
                            this.moneda = datos.facturacion.codigo_moneda;
                            this.anulacion_factura_exportacion = datos.anulacion_factura_exportacion;
                            this.cuotas = [];
                            this.idventa_modifica = idventa;
                            this.codigo_tipo_factura = datos.facturacion.codigo_tipo_factura || '0101';
                            if (datos.facturacion.codigo_moneda == 'PEN') {
                                this.moneda = 'S/';
                            }
                            if (this.comprobante == '30' || this.comprobante == '03' || this.comprobante == '01') {
                                @if($emitir_solo_ticket || $disabledVentas)
                                    this.comprobante = 30;
                                @else
                                    this.comprobante = datos.facturacion.codigo_tipo_documento;
                                @endif
                                this.comprobanteReferencia = null;
                                this.inhabilitarComprobante = false;
                            } else {
                                if(this.tipo_nota_electronica == 13 || this.tipo_nota_electronica == '03'){
                                    for (let producto of this.productosSeleccionados) {
                                        producto['precio'] = 0;
                                        producto['descuento'] = 0;
                                        producto['subtotal'] = 0;
                                        producto['igv'] = 0;
                                        producto['total'] = 0;
                                    }
                                    this.calcularTotalVenta();
                                }
                                if(this.tipo_nota_electronica=='04'){
                                    this.monto_descuento_global = 0;
                                    this.base_descuento_global = 0;
                                    this.porcentaje_descuento_global = 0;
                                    this.productosSeleccionados = [];
                                    this.agregarDescuentoNC();
                                }
                                this.inhabilitarComprobante = true;
                            }
                            if (esDesdePresupuesto || this.codigo_tipo_factura != '0200') {
                                this.calcularTotalPorItem();
                                this.calcularTotalVenta();
                            }

                            this.$refs['modal-documento'].hide();
                            this.countTextareas();
                        })
                        .catch(error => {
                            this.alerta('No se ha podido copiar la venta');
                            console.log(error);
                        });
                },
                abrir_modal(nombre){
                    switch (nombre) {
                        case 'nota':
                            this.$refs['modal-documento'].show();
                            this.obtenerDocumentos(false);
                            break;
                        case 'copiar':
                            this.$refs['modal-documento'].show();
                            this.obtenerDocumentos(true);
                            break;
                        case 'adelantos':
                            this.$refs['modal-documento'].show();
                            this.buscarAdelanto = true;
                            this.obtenerDocumentos(-3);
                            break;
                        case 'pago':
                            this.$refs['modal-tipopago'].show();
                            this.cuotasAux = Object.assign([], this.cuotas);
                            this.agregarCuota(this.totalVenta);
                            break;
                        case 'guias':
                            this.$refs['modal-guias'].show();
                            this.guiasRelacionadasAux = Object.assign([], this.guiasRelacionadas);
                            this.agregarGuia(false);
                            break;
                    }
                    this.tipo_busqueda = nombre;

                },
                delay(){
                    if (this.timer) {
                        clearTimeout(this.timer);
                        this.timer = null;
                    }
                    this.timer = setTimeout(() => {
                        switch (this.tipo_busqueda) {
                            case 'nota':
                                this.obtenerDocumentos(false);
                                break;
                            case 'copiar':
                                this.obtenerDocumentos(true);
                                break;
                            case 'adelantos':
                                this.obtenerDocumentos(-3);
                                break;
                        }

                    }, 500);
                },
                agregarProducto(obj){
                    let productos = this.productosSeleccionados.push(Object.assign({}, obj));
                    let i = productos - 1;

                    let subtotal = (this.productosSeleccionados[i]['precio'] * 1).toFixed(2);
                    this.$set(this.productosSeleccionados[i], 'num_item', i);
                    this.$set(this.productosSeleccionados[i], 'cantidad', 1);
                    this.$set(this.productosSeleccionados[i], 'tipo_descuento', 0);
                    this.$set(this.productosSeleccionados[i], 'porcentaje_descuento', '0');
                    this.$set(this.productosSeleccionados[i], 'descuento_por_und', 0);
                    this.$set(this.productosSeleccionados[i], 'descuento', '0.00');
                    this.$set(this.productosSeleccionados[i], 'subtotal', subtotal);

                    this.$set(this.productosSeleccionados[i], 'igv', 0);
                    this.$set(this.productosSeleccionados[i], 'tipoAfectacion', '10');
                    this.$set(this.productosSeleccionados[i], 'total', subtotal);

                    this.calcularTotalVenta();
                    this.validar_stock(this.productosSeleccionados[i]);
                    this.agregarProductosSession();
                    this.countTextareas();
                },
                calcular(index){
                    let producto = this.productosSeleccionados[index];

                    if(typeof index === 'object'){
                        producto = index;
                    }

                    let _porcentaje_descuento = producto['porcentaje_descuento'] / 100;
                    let precio_bruto = producto['precio'] / 1.18;
                    let monto_descuento = producto['descuento'];
                    if(producto['tipo_descuento' == 1]){
                        monto_descuento = (this.esConIgv?precio_bruto:producto['precio']) * producto['cantidad'] * _porcentaje_descuento;
                    }

                    producto['subtotal'] = (producto['precio'] * producto['cantidad'] - monto_descuento);
                    producto['igv'] = 0;
                    producto['total'] = (Number(producto['subtotal']) + Number(producto['igv']));


                    if(producto['descuento'] > 0 && (producto['precio']<=0 || producto['cantidad']<=0)){
                        producto['tipo_descuento']=0;
                        producto['porcentaje_descuento']=0;
                        producto['descuento']=0;
                        producto['descuento_por_und']=0;
                    }

                    let precio = this.esConIgv?producto['precio']/1.18:producto['precio'];
                    producto['porcentaje_descuento'] = producto['descuento'] / (precio * producto['cantidad']) * 100;

                    this.calcularTotalVenta();
                    this.validar_stock(producto);
                    this.agregarProductosSession();
                },
                calcularTotalPorItem(){
                    for (let producto of this.productosSeleccionados) {
                        this.calcular(producto);
                    }
                },
                calcularTotalVenta(){

                    let sumas = {
                        gravadas: 0,
                        exoneradas: 0,
                        inafectas: 0,
                        gratuitas: 0,
                        descuentos: 0,
                        igv: 0
                    };

                    let desc_global = this.tipo_descuento_global ? this.porcentaje_descuento_global / 100 : this.monto_descuento_global;
                    let total_venta_bruto = 0;

                    for (let producto of this.productosSeleccionados) {

                        sumas.gravadas += Number(producto['subtotal']);
                        total_venta_bruto += producto['cantidad'] * producto['precio'];

                        sumas.descuentos += Number(producto['descuento']);
                        sumas.igv += 0;
                    }

                    this.gravadas = (sumas.gravadas - desc_global);
                    this.exoneradas = (sumas.exoneradas);
                    this.inafectas = (sumas.inafectas);
                    this.igv = 0;

                    this.gratuitas = (sumas.gratuitas);
                    this.totalVenta = (Number(this.gravadas) + Number(this.exoneradas) + Number(this.inafectas) + Number(this.igv)).toFixed(2);
                    this.base_descuento_global = sumas.gravadas + sumas.inafectas + sumas.exoneradas;
                    this.descuentos = (sumas.descuentos + Number(this.monto_descuento_global)).toFixed(2);
                    this.subtotalVenta = total_venta_bruto.toFixed(3);
                    this.calcularDeducciones();

                },
                calcularDeducciones(){
                    if(this.codigo_tipo_factura == '1' || this.codigo_tipo_factura == '1001'){

                        let porcentaje = 0.03;
                        if(this.codigo_tipo_factura == '1001'){
                            let string= this.tipoDetraccion.split('/');
                            porcentaje = Number(string[1]) / 100;
                        }

                        this.montoDeduccion = (this.totalVenta * porcentaje).toFixed(2);
                        this.montoDeduccionPorcentaje = porcentaje * 100;
                        this.montoNePenPago = (this.totalVenta - this.montoDeduccion).toFixed(2);
                    }
                },
                borrarItemVenta(index){
                    this.productosSeleccionados.splice(index, 1);
                    this.calcularTotalVenta();
                    this.agregarProductosSession();
                },
                resetModal(){
                    this.buscar = '';
                    this.buscarAdelanto = false;
                },
                procesarVenta(){

                    if (this.validarVenta()) {
                        return;
                    }
                    this.$swal({
                            heightAuto: false,
                            position: 'top',
                            icon: 'question',
                            text: 'Se registrará una venta. Confirma esta acción.',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            cancelButtonText: 'Cancelar',
                            confirmButtonText: 'Sí, registrar'
                        }).then((result) => {
                        if (result.isConfirmed) {
                            this.mostrarProgresoGuardado = true;
                            let comprobante;

                            switch (this.comprobante) {
                                case '07.01':
                                    comprobante = '07';
                                    break;
                                case '07.02':
                                    comprobante = '07';
                                    break;
                                case '08.01':
                                    comprobante = '08';
                                    break;
                                case '08.02':
                                    comprobante = '08';
                                    break;
                                default:
                                    comprobante = this.comprobante;
                            }

                            axios.post('{{action('VentaController@store')}}', {
                                'idcliente': this.clienteSeleccionado['idcliente'],
                                'serie': this.serie,
                                'correlativo': this.correlativo,
                                'num_guia': this.numeroGuia,
                                'num_oc': this.numeroOc,
                                'fecha': this.fecha,
                                'moneda': this.moneda,
                                'tipo_pago': this.tipoPago,
                                'comprobante': comprobante,
                                'total_venta': this.totalVenta,
                                'num_doc_relacionado': this.comprobanteReferencia,
                                'tipo_nota_electronica': this.tipo_nota_electronica,
                                'observacion': this.motivo,
                                'gravadas': this.gravadas,
                                'gratuitas': this.gratuitas,
                                'exoneradas': this.exoneradas,
                                'inafectas': this.inafectas,
                                'descuentos': this.descuentos,
                                'igv': this.igv,
                                'tipo_descuento':this.tipo_descuento_global,
                                'porcentaje_descuento_global': this.porcentaje_descuento_global,
                                'monto_descuento_global': this.monto_descuento_global,
                                'base_descuento_global': this.base_descuento_global,
                                'subtotal': this.subtotalVenta,
                                'esConGuia': this.esConGuia,
                                'esConIgv': this.esConIgv,
                                'tipo_detraccion':this.tipoDetraccion,
                                'guia_fisica': this.numero_guia_fisica,
                                'guias_relacionadas': JSON.stringify(this.guiasRelacionadas),
                                'guia_datos_adicionales': JSON.stringify(this.guia_datos_adicionales),
                                'items': JSON.stringify(this.productosSeleccionados),
                                'cuotas': JSON.stringify(this.cuotas),
                                'tipo_pago_contado': this.tipoPagoContado,
                                'codigo_tipo_factura':this.codigo_tipo_factura,
                                'doc_relacionado_nc':this.doc_relacionado_nc,
                                'idventa_modifica':this.idventa_modifica,
                                'doc_observacion':this.doc_observacion,
                                'tipo_cambio':this.tipoCambio,
                                'adelanto':this.esAdelanto,
                                'objAdelanto':JSON.stringify(this.objAdelanto),
                                'pago_fraccionado': JSON.stringify(this.pago_fraccionado),
                                'anulacion_factura_exportacion':this.anulacion_factura_exportacion
                            })
                                .then(response => {
                                    localStorage.removeItem('productos');
                                    localStorage.removeItem('cliente');
                                    localStorage.removeItem('esConIgv');
                                    let texto = 'la venta';
                                    if(comprobante == '07' || comprobante == '08'){
                                        texto = 'la nota';
                                    }if (isNaN(response.data.idventa)) {
                                        this.$swal({
                                            position: 'top',
                                            icon: 'info',
                                            title: 'Se ha guardado '+texto,
                                            text: 'Existen algunos problemas con el envío de comprobantes',
                                            timer: 3000,
                                        }).then(() => {
                                            this.mostrarProgresoGuardado = false;
                                            window.location.reload();
                                        });
                                    } else {
                                        this.mostrarProgresoGuardado = false;
                                        this.$swal({
                                            position: 'top',
                                            icon: 'success',
                                            title: 'Se ha guardado '+texto,
                                            text: response.data.respuesta,
                                            timer: 60000,
                                        }).then(() => {
                                            location.href = '/facturacion/documento/' + response.data.idventa + '?notaDeVenta=true';
                                        });
                                    }
                                })
                                .catch(error => {
                                    this.$swal({
                                        position: 'top',
                                        icon: 'error',
                                        title: error.response.data.mensaje,
                                        timer: 3000,
                                    }).then(() => {
                                        this.mostrarProgresoGuardado = false;
                                        localStorage.removeItem('productos');
                                        localStorage.removeItem('cliente');
                                        localStorage.removeItem('esConIgv');
                                        window.location.reload();
                                        console.log(error);
                                    });
                                });
                        }
                    });
                },
                validarVenta(){
                    let errorVenta = 0;
                    let errorDatosVenta = [];
                    let errorString = '';
                    if (this.fecha.length == 0) errorDatosVenta.push('*La fecha no puede estar vacia');
                    if (this.serie.length == 0 || this.correlativo.length == 0) errorDatosVenta.push('*La serie y correlativo no puede estar vacio');
                    if (this.tipoPago == 2 && (this.comprobante == '01' || this.comprobante == '03' || this.comprobante == '30')) {
                        if (this.cuotas.length == 0) errorDatosVenta.push('*Las ventas a crédito deben contener detalle de las cuotas');

                        let suma_cuotas = 0;
                        for (let cuota of this.cuotas) {
                            suma_cuotas += Number(cuota.monto);
                        }

                        if (suma_cuotas > this.totalVenta) errorDatosVenta.push('*La suma de las cuotas supera el monto total de la venta');
                        if (suma_cuotas < this.totalVenta) errorDatosVenta.push('*La suma de las cuotas es inferior al monto total de la venta');
                    }

                    if (this.tipoPago == 1 && this.tipoPagoContado == 4) {
                        let suma_pago_fra = 0;
                        for (let pago of this.pago_fraccionado) {
                            suma_pago_fra += Number(pago.monto);
                        }

                        if (suma_pago_fra > this.totalVenta) errorDatosVenta.push('*La suma de los pagos fraccionados supera el monto total de la venta');
                        if (suma_pago_fra < this.totalVenta) errorDatosVenta.push('*La suma de los pagos fraccionados es inferior al monto total de la venta');
                    }

                    if (this.comprobante == '01' || this.comprobante == '07.02' || this.comprobante == '08.02') {
                        if (Object.keys(this.clienteSeleccionado).length == 0) errorDatosVenta.push('*Debes ingresar un cliente');
                    }
                    if (this.comprobante == '01' || this.comprobante == '07.02' || this.comprobante == '08.02') {
                        if (this.clienteSeleccionado['num_documento'] && this.clienteSeleccionado['num_documento'].length != 11 && this.codigo_tipo_factura == '0101') errorDatosVenta.push('*Ingrese un RUC válido');
                    } else if (this.comprobante == '03' || this.comprobante == '07.01' || this.comprobante == '08.01') {
                        let totalCheck = this.totalVenta;
                        if(this.moneda == 'USD'){
                            totalCheck = this.totalVenta * Number(this.tipoCambio);
                        }
                        if (totalCheck >= 700){
                            if(this.clienteSeleccionado['num_documento']){
                                str = this.clienteSeleccionado['num_documento'];
                                let regex = new RegExp(/(.)\1{7}/);
                                if(regex.test(str)){
                                    errorDatosVenta.push('*Para boletas mayores a S/.700.00 debe ingresar un DNI válido');
                                }
                            } else{
                                errorDatosVenta.push('*Para boletas mayores a S/.700.00 debe ingresar un DNI válido');
                            }
                        }
                        //if (this.clienteSeleccionado['num_documento'] && (this.clienteSeleccionado['num_documento'].length < 8 || this.clienteSeleccionado['num_documento'].length > 11)) errorDatosVenta.push('*Ingrese un DNI o RUC válido');
                    }
                    if (this.comprobante == '07.02' || this.comprobante == '08.02' || this.comprobante == '07.01' || this.comprobante == '08.01') {
                        if (this.motivo.length == 0) errorDatosVenta.push('*El campo motivo no puede quedar en blanco');
                        if (this.comprobanteReferencia.length == 0) errorDatosVenta.push('*El campo documento que modifica no puede quedar en blanco');
                        if(this.tipo_nota_electronica == '02'){
                            if (this.doc_relacionado_nc.length == 0) errorDatosVenta.push('*Debes ingresar el correlativo de la nueva factura emitida');
                        }
                    }

                    if (this.comprobante == '07.01' || this.comprobante == '07.02') {
                        if(this.tipo_nota_electronica == 13){
                            if (this.cuotas.length == 0) errorDatosVenta.push('*El tipo de nota de crédito debe contener detalle de las cuotas');
                        }
                    }

                    if (this.esConGuia) {
                        if (this.guia_datos_adicionales.direccion.length == 0) errorDatosVenta.push('*El campo direccion de la guia no puede estar vacío');
                        if (this.guia_datos_adicionales.ubigeo.length!=6) errorDatosVenta.push('*El campo ubigeo debe contener un código de 6 dígitos');
                        if (this.guia_datos_adicionales.peso.length == 0) errorDatosVenta.push('*El campo peso no puede estar vacío');
                        if (this.guia_datos_adicionales.bultos.length == 0) errorDatosVenta.push('*El campo N° de bultos no puede estar vacío');
                        if(this.guia_datos_adicionales.categoria_vehiculo != 'M1_L') {
                            if (this.guia_datos_adicionales.tipo_transporte == '01') {
                                if (this.guia_datos_adicionales.num_doc_transportista.length == 0) errorDatosVenta.push('*El campo número de documento de transportista no puede estar vacío');
                                if (!(this.guia_datos_adicionales.num_doc_transportista.length === 11) && this.guia_datos_adicionales.tipo_doc_transportista == '6') errorDatosVenta.push('*El campo número documento de transportista debe contener 11 dígitos');
                                if (this.guia_datos_adicionales.razon_social_transportista.length == 0) errorDatosVenta.push('*El campo razón social de transportista no puede estar vacío');
                            } else {
                                if (this.guia_datos_adicionales.placa_vehiculo.length == 0) errorDatosVenta.push('*El campo placa vehículo no puede estar vacío');
                                if (this.guia_datos_adicionales.dni_conductor.length == 0) errorDatosVenta.push('*El campo dni de conductor no puede estar vacío');
                                if (this.guia_datos_adicionales.licencia_conductor.length == 0) errorDatosVenta.push('*El campo licencia de conductor no puede estar vacío');
                                if (this.guia_datos_adicionales.nombre_conductor.length == 0) errorDatosVenta.push('*El campo nombres de conductor no puede estar vacío');
                                if (this.guia_datos_adicionales.apellido_conductor.length == 0) errorDatosVenta.push('*El campo apellidos de conductor no puede estar vacío');
                                if (this.guia_datos_adicionales.dni_conductor.length != 8) errorDatosVenta.push('*El campo dni de conductor debe contener 8 dígitos');
                            }
                        }

                        if (this.guia_datos_adicionales.doc_relacionado != '-1' && this.guia_datos_adicionales.num_doc_relacionado.length == 0) errorDatosVenta.push('*El campo número de documento relacionado no puede estar vacío');

                        if (/^\./.test(this.guia_datos_adicionales.bultos)) this.guia_datos_adicionales.bultos = '0' + this.guia_datos_adicionales.bultos;
                        if (/^\./.test(this.guia_datos_adicionales.peso)) this.guia_datos_adicionales.peso = '0' + this.guia_datos_adicionales.peso;

                        //Validar motivo de traslado
                        switch (this.guia_datos_adicionales.codigo_traslado) {
                            case '01':
                                if (this.clienteSeleccionado['num_documento'] == <?php echo $ruc_emisor ?>) errorDatosVenta.push('*El destinatario no debe ser igual al remitente');
                                break;
                            case '02':
                            case '04':
                            case '18':
                                if (this.clienteSeleccionado['num_documento'] != <?php echo $ruc_emisor ?>) errorDatosVenta.push('*Para el motivo de traslado ingresado el destinatario debe ser igual al remitente');
                                break;
                            case '08':
                            case '09':
                                if (this.guia_datos_adicionales.doc_relacionado != '01') errorDatosVenta.push('*Para importación / exportación debes ingresar el número DAN');

                        }
                    }

                    if (errorDatosVenta.length) {
                        errorVenta = 1;
                        for (let error of errorDatosVenta) {
                            errorString += error + '\n';
                        }
                        this.alerta(errorString);
                    }

                    return errorVenta;
                },
                validar_stock(producto) {

                    if (producto['tipo_producto'] === 1) {
                        const mensajeExistente = this.mensajesStock.find(mensaje => mensaje.idproducto === producto.idproducto);
                        let mensaje = '';
                        let estilo = '';
                        let unidad = (producto.unidad_medida).split('/')[1];
                        if (producto['cantidad'] > producto['stock']) {
                            mensaje = 'El stock del producto ' + producto['nombre'] + ' es de ' + producto['stock'] + ' ' + unidad + '. ¡Revisa tu stock antes de vender!';
                            estilo = 'danger';
                        } else if (producto['cantidad'] >= (producto['stock'] - producto['stock_bajo'])) {
                            mensaje = 'El stock del producto ' + producto['nombre'] + ' es de ' + producto['stock'] + ' ' + unidad + '. ¡Está por agotarse!';
                            estilo = 'warning';
                        }

                        if (mensajeExistente) {
                            mensajeExistente.mensaje = mensaje;
                            mensajeExistente.estilo = estilo;
                        } else {
                            const mensajeStock = {
                                idproducto: producto.idproducto,
                                id: Date.now(),
                                mensaje: mensaje,
                                estilo: estilo
                            };
                            this.mensajesStock.push(mensajeStock);
                        }
                    }
                },
                cerrarMensajeStock(id) {
                    this.mensajesStock = this.mensajesStock.filter(mensaje => mensaje.id !== id);
                },
                cambiarSerie(){

                    switch (this.comprobante) {
                        case '01':
                            this.serie = '{{$serie_comprobantes['factura']}}';
                            break;
                        case '03':
                            this.serie = '{{$serie_comprobantes['boleta']}}';
                            break;
                        case '07.01':
                            this.serie = '{{$serie_comprobantes['nota_credito_boleta']}}';
                            break;
                        case '07.02':
                            this.serie = '{{$serie_comprobantes['nota_credito_factura']}}';
                            break;
                        case '08.01':
                            this.serie = '{{$serie_comprobantes['nota_debito_boleta']}}';
                            break;
                        case '08.02':
                            this.serie = '{{$serie_comprobantes['nota_debito_factura']}}';
                            break;
                        default:
                            this.serie = '{{$serie_comprobantes['recibo']}}';
                    }


                },
                limpiar(){
                    this.clienteSeleccionado = {};
                    if(this.$refs['suggestCliente']){
                        this.$refs['suggestCliente'].borrarCliente();
                    }
                    if(this.$refs['suggest']){
                        this.$refs['suggest'].limpiar();
                    }
                    this.nombreCliente = "";
                    this.productosSeleccionados = [];
                    this.comprobante = '30';
                    this.serie = '{{$serie_comprobantes['boleta']}}';
                    this.comprobanteReferencia = '';
                    this.motivo = '';
                    this.tipo_nota_electronica = '01';
                    this.tipoPago = 1;
                    this.tipoPagoContado = 1;
                    this.cuotas = [];
                    this.cuotasAux = [];
                    this.numeroGuia = '';
                    this.numeroOc = '';
                    this.moneda = 'S/';
                    this.esConIgv = <?php echo json_encode(json_decode(cache('config')['interfaz'], true)['igv_incluido']) ?>;
                    this.totalVenta = 0.00;
                    this.gravadas = 0.00;
                    this.igv = 0.00;
                    this.fecha = '{{date('Y-m-d')}}';
                    this.porcentaje_descuento_global = 0;
                    this.monto_descuento_global = 0.00;
                    this.base_descuento_global = 0.00;
                    this.inhabilitarComprobante = false;
                    this.esConGuia = 0;
                    this.codigo_tipo_factura = '0101';
                    this.guia_datos_adicionales = {
                        direccion: '',
                        ubigeo: '',
                        peso: '',
                        bultos: '',
                        tipo_doc_transportista: '6',
                        num_doc_transportista: '',
                        razon_social_transportista: '',
                        placa_vehiculo: '',
                        dni_conductor: '',
                        licencia_conductor:'',
                        nombre_conductor:'',
                        apellido_conductor:'',
                        codigo_traslado: '01',
                        fecha_traslado: '{{date('Y-m-d')}}',
                        doc_relacionado: '-1',
                        num_doc_relacionado: '',
                        tipo_transporte: '01'
                    };
                    this.numero_guia_fisica = '';
                    this.tipo_busqueda = '';
                    this.guiasRelacionadas = [];
                    this.guiasRelacionadasAux = [];
                    this.calcularTotalVenta();
                    this.obtenerCorrelativo();
                    this.idventa_modifica=-1;
                    this.domicilioFiscalCliente = true;
                    localStorage.removeItem('productos');
                    localStorage.removeItem('cliente');
                    localStorage.removeItem('esConIgv');
                    this.doc_relacionado_nc='';
                    this.doc_observacion="";
                    this.esAdelanto = false;
                    this.adelantoAgregado = false;
                    this.buscarAdelanto = false;
                    this.objAdelanto = {};
                    this.mensajesStock = [];
                },
                agregarUbigeo(ubigeo){
                    this.guia_datos_adicionales.ubigeo=ubigeo;
                },
                agregarProductosSession(){
                    //Agregar a localStorage
                    const parsed = JSON.stringify(this.productosSeleccionados);
                    localStorage.setItem('productos', parsed);
                },
                agregarClienteSession(){
                    //Agregar a localStorage
                    const parsed = JSON.stringify(this.clienteSeleccionado);
                    localStorage.setItem('cliente', parsed);
                },
                agregarCaracteristicasSession(){
                    setTimeout(()=>{
                        this.agregarProductosSession()
                    }, 2000)
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
                agregar_nr(codigo){
                    if(codigo == '00ADT'){
                        this.adelantoAgregado = true;
                        this.inhabilitarComprobante = true;
                        this.tipoPago = 1;
                    }
                    this.disabledNr = true;
                    axios.get('/helper/agregar-producto'+'/'+codigo)
                        .then(response => {
                            this.results = response.data;
                            if((Object.keys(this.results).length === 0)){
                                alert('No se ha encontrado el producto con el código marcado');
                            } else{
                                this.agregarProducto(this.results);
                            }
                            this.disabledNr = false;
                        })
                        .catch(error => {
                            this.disabledNr = false;
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });
                },
                cambiarDireccionGuia(){
                    this.$nextTick(() => {
                        if(this.domicilioFiscalCliente){
                            this.guia_datos_adicionales.direccion = this.clienteSeleccionado['direccion']
                        } else {
                            this.guia_datos_adicionales.direccion = '';
                        }
                    });
                },
                expandirYContarTextarea(index, event) {
                    const textarea = event.target;
                    textarea.style.height = 'auto';
                    textarea.style.height = `${textarea.scrollHeight}px`;
                    if(this.productosSeleccionados[index]){
                        this.$set(this.charCounts, index, 0);
                        this.charCounts[index] = this.productosSeleccionados[index].presentacion.length;
                    }
                },
                countTextareas(){
                    this.$nextTick(() => {
                        const textareas = this.$refs.textareas;
                        if (textareas) {
                            textareas.forEach((textarea, index) => {
                                this.expandirYContarTextarea(index, {target: textarea});
                            });
                        }
                    });
                }
            },
            watch: {
                comprobante(){
                    this.obtenerCorrelativo();
                    this.comprobanteReferencia = '';
                    this.guiasRelacionadas = [];
                    this.doc_relacionado_nc='';
                    this.esConGuia = 0;
                    this.adelantoAgregado = false;
                    this.esAdelanto = false;
                },
                esConGuia(){
                    if (this.esConGuia == 1) {
                        this.numero_guia_fisica = '';
                        axios.get('/ventas/obtenerCorrelativoGuia')
                            .then(response => {
                                this.numeroGuia = response.data;
                            })
                            .catch(error => {
                                this.alerta('No hay guias registradas. Ingresa el correlativo manualmente');
                                console.log(error);
                            });
                    } else {
                        this.numeroGuia = '';
                    }

                },
                esConIgv(val){
                    localStorage.setItem('esConIgv', val);
                    this.productosSeleccionados.forEach(
                        (valor, indice, array) =>  {
                            this.calcular(indice);
                        }
                    );
                },
                codigo_tipo_factura(){
                    this.productosSeleccionados.forEach(
                        (valor, indice, array) =>  {
                            this.calcular(indice);
                        }
                    );
                },
                tipoDetraccion(){
                    this.calcularDeducciones();
                }
            }

        });
    </script>
@endsection