@extends('layouts.main')
@section('titulo', 'Registrar')
@section('contenido')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <h3 class="titulo-admin-1">Facturación</h3>
                <b-button
                        :disabled="comprobante=='07.01' || comprobante=='07.02' || comprobante=='08.01' || comprobante=='08.02'"
                        @click="abrir_modal('copiar')" class="mr-2" variant="primary"><i class="fas fa-copy"></i> Copiar
                    de...
                </b-button>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-7 mt-4 mb-3">
                <div class="card">
                    <div class="card-header">
                        Lista de pedidos
                    </div>
                    <div class="card-body lista_pedidos" style="height: 350px;overflow-y: scroll;">
                        <pedidos ref="lista_pedidos" v-on:agregar_pedido="agregarDocumento"></pedidos>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 mt-4 mb-3">
                <div class="card">
                    <div class="card-header">
                        Datos facturación
                    </div>
                    <div class="card-body" style="height: 350px;">
                        <div class="row">
                            <div class="col-lg-6 form-group">
                                <label>Comprobante</label>
                                <select :disabled="inhabilitarComprobante" v-model="comprobante" name="comprobante"
                                        class="custom-select" id="selectComprobante">
                                    <option value="30">Ninguno</option>
                                    <option value="03">Boleta</option>
                                    <option value="01">Factura</option>
                                    <option value="07.01">Nota de crédito (Boleta)</option>
                                    <option value="07.02">Nota de crédito (Factura)</option>
                                    <option value="08.01">Nota de débito (Boleta)</option>
                                    <option value="08.02">Nota de débito (Factura)</option>
                                </select>
                            </div>
                            <div class="col-lg-6 form-group">
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
                            <div class="col-lg-4 form-group">
                                <label>Moneda</label>
                                <select :disabled="comprobante=='07.01' || comprobante=='07.02' || comprobante=='08.01' || comprobante=='08.02'"
                                        v-model="moneda" class="custom-select" id="selectComprobante">
                                    <option value="S/">Soles</option>
                                    <option value="USD">Dólares</option>
                                </select>
                            </div>
                            <div class="col-lg-4 form-group">
                                <label>Fecha de emisión</label>
                                <input type="date" v-model="fecha" min="{{date('Y-m-d', strtotime(date('Y-m-d').' - 2 days'))}}" max="{{date('Y-m-d')}}"
                                       class="form-control">
                            </div>
                            <div class="col-lg-4 form-group">
                                <label>Tipo de cambio</label>
                                <input type="text" v-model="tipoCambio"
                                       class="form-control">
                            </div>
                            <div v-show="comprobante == 01" class="col-lg-4 form-group">
                                <label>Tipo de operación</label>
                                <select v-model="codigo_tipo_factura" class="custom-select">
                                    <option value="0101">Venta interna</option>
                                    <option value="0200">Exportación de bienes</option>
                                    <option value="1001">Operación sujeta a detracción</option>
                                    <option value="1">Operación sujeta a retención</option>
                                </select>
                            </div>
                            <div v-show="codigo_tipo_factura=='1001'" class="col-lg-8 form-group">
                                <label>Bienes y servicios sujetos a detracción</label>
                                <select v-model="tipoDetraccion" class="custom-select">
                                    {{--<option value="001/10">Azúcar y melaza de caña - 10%</option>
                                    <option value="003/10">Alcohol etílico - 10%</option>
                                    <option value="005/4">Maíz amarillo duro - 4%</option>
                                    <option value="007/10">Caña de azúcar - 10%</option>
                                    <option value="008/4">Madera - 4%</option>
                                    <option value="009/10">Arena y piedra. - 10%</option>--}}
                                    <option value="019/10">Arrendamiento de bienes muebles - 10%</option>
                                    <option value="020/12">Mantenimiento y reparación de bienes muebles - 12%</option>
                                    <option value="022/12">Otros servicios empresariales - 12%</option>
                                    <option value="025/10">Fabricación de bienes por encargo - 10%</option>
                                    <option value="037/12">Demás servicios gravados con el IGV - 12%</option>
                                </select>
                            </div>
                            <div v-show="comprobante==01 || comprobante==03 || comprobante==30" class="col-lg-4 form-group">
                                <label>N° orden de compra</label>
                                <input type="text" v-model="numeroOc" name="numeroOc"
                                       placeholder="Número OC"
                                       class="form-control">
                            </div>
                            <div v-show="comprobante==01" class="col-lg-4 form-group">
                                <label class="d-block">Guías relacionadas</label>
                                <b-button @click="abrir_modal('guias')" variant="primary"><i
                                            class="fas fa-plus"></i> Agregar guías (@{{guiasRelacionadas.length}})
                                </b-button>
                            </div>
                            <div v-show="comprobante=='01'" class="col-lg-4 form-group">
                                <b-form-checkbox style="margin-top: 18px" v-model="esConGuia" switch size="sm">
                                    Crear guía
                                </b-form-checkbox>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-show="esConGuia" class="col-lg-12 mb-3">
                <div class="card">
                    <div class="card-header">
                        Datos guía electrónica
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-2 form-group">
                                <label>Fecha de emisión</label>
                                <input disabled type="date" v-model="fecha" name="fecha" class="form-control">
                            </div>
                            <div class="col-lg-3 form-group">
                                <label>Serie y correlativo</label>
                                <input disabled type="text" v-model="numeroGuia" class="form-control">
                            </div>
                            <div class="col-lg-3 form-group">
                                <label>Documento relacionado</label>
                                <select v-model="guia_datos_adicionales.doc_relacionado" name="cargo"
                                        class="custom-select">
                                    <option value="-1">Ninguno</option>
                                    <option value="01">Numeración DAN</option>
                                    <option value="02">N° de orden de entrega</option>
                                    <option value="03">N° SCOP</option>
                                    <option value="04">N° de maniefiesto de carga</option>
                                    <option value="05">N° de constancia de detracción</option>
                                    <option value="06">Otros</option>
                                </select>
                            </div>
                            <div v-show="guia_datos_adicionales.doc_relacionado!='-1'" class="col-lg-3 form-group">
                                <label>N° documento relacionado</label>
                                <input type="text" v-model="guia_datos_adicionales.num_doc_relacionado"
                                       placeholder="Número documento relacionado"
                                       class="form-control">
                            </div>
                            <div class="col-lg-6 form-group">
                                <label>Dirección de llegada</label>
                                <input maxlength="100" type="text" v-model="guia_datos_adicionales.direccion"
                                       name="direccion"
                                       class="form-control" placeholder="*Máximo 100 caracteres">
                            </div>
                            <div class="col-lg-2 form-group">
                                <label>Ubigeo</label>
                                <input disabled type="text" v-model="guia_datos_adicionales.ubigeo"
                                       class="form-control">
                                <b-button v-b-modal.modal-ubigeo variant="primary"
                                          class="buscar_documento boton_adjunto">
                                    <i class="fas fa-search"></i>
                                </b-button>
                            </div>
                            <div class="col-lg-2 form-group">
                                <label>Peso (KG)</label>
                                <input type="text" v-model="guia_datos_adicionales.peso" name="peso"
                                       class="form-control">
                            </div>
                            <div class="col-lg-2 form-group">
                                <label>N° de bultos</label>
                                <input type="text" v-model="guia_datos_adicionales.bultos" name="bultos"
                                       class="form-control">
                            </div>
                            <div class="col-lg-2 form-group">
                                <label>Tipo de transporte</label>
                                <select v-model="guia_datos_adicionales.tipo_transporte" name="cargo"
                                        class="custom-select" id="tipo_transporte">
                                    <option value="01">Público</option>
                                    <option value="02">Privado</option>
                                </select>
                            </div>
                            <div class="col-lg-10" v-show="guia_datos_adicionales.tipo_transporte == '01'">
                                <div class="row">
                                    <div class="col-lg-3 form-group">
                                        <label>Tipo documento transportista</label>
                                        <select v-model="guia_datos_adicionales.tipo_doc_transportista" name="cargo"
                                                class="custom-select" id="tipo_transporte">
                                            <option value="6">RUC</option>
                                            <option value="1">DNI</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label>Número doc. tranportista</label>
                                        <input :maxlength="guia_datos_adicionales.tipo_doc_transportista==1? 8 : 11"
                                               type="text" v-model="guia_datos_adicionales.num_doc_transportista"
                                               class="form-control">
                                    </div>
                                    <div class="col-lg-6 form-group">
                                        <label>Razón social tranportista</label>
                                        <input type="text" v-model="guia_datos_adicionales.razon_social_transportista"
                                               class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-10" v-show="guia_datos_adicionales.tipo_transporte == '02'">
                                <div class="row">
                                    <div class="col-lg-3 form-group">
                                        <label>Placa del vehículo</label>
                                        <input type="text" v-model="guia_datos_adicionales.placa_vehiculo"
                                               class="form-control">
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label>DNI del conductor</label>
                                        <input maxlength="8" type="text" v-model="guia_datos_adicionales.dni_conductor"
                                               class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 form-group">
                                <label>Motivo de traslado</label>
                                <select v-model="guia_datos_adicionales.codigo_traslado" name="cargo"
                                        class="custom-select">
                                    <option value="01">Venta</option>
                                    <option value="14">Venta sujeta a confirmacion del comprador</option>
                                    <option value="02">Compra</option>
                                    <option value="04">Traslado entre establecimientos de la misma empresa</option>
                                    <option value="18">Traslado emisor itinerante cp</option>
                                    <option value="08">Importación</option>
                                    <option value="09">Exportación</option>
                                    <option value="19">Traslado a zona primaria</option>
                                    <option value="13">Otros</option>
                                </select>
                            </div>
                            <div class="col-lg-2 form-group">
                                <label>Fecha traslado</label>
                                <input type="date" v-model="guia_datos_adicionales.fecha_traslado" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 mb-3"
                 v-show="comprobante=='07.01' || comprobante=='07.02' || comprobante=='08.01' || comprobante=='08.02'">
                <div class="card">
                    <div class="card-header">
                        Documento relacionado
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-3 form-group">
                                <label>Tipo</label>
                                <select v-show="comprobante=='07.01' || comprobante=='07.02'"
                                        v-model="tipo_nota_electronica" name="motivo" class="custom-select"
                                        id="selectMotivo">
                                    <option value="01">Anulación de la operación</option>
                                    <option value="02">Anulación por error en el RUC</option>
                                    <option value="13">Ajustes – montos y/o fechas de pago</option>
                                    {{--<option value="03">Corrección por error en la descripción</option>
                                    <option value="04">Descuento global</option>
                                    <option value="05">Descuento por ítem</option>
                                    <option value="06">Devolución total</option>
                                    <option value="07">Devolución por ítem</option>
                                    <option value="08">Bonificación</option>
                                    <option value="09">Disminuciónen el valor</option>
                                    <option value="10">Otros conceptos</option>--}}
                                </select>
                                <select v-show="comprobante=='08.01' || comprobante=='08.02'"
                                        v-model="tipo_nota_electronica" name="motivo" class="custom-select"
                                        id="selectMotivo">
                                    <option value="01">Intereses por mora</option>
                                    <option value="02">Aumento en el valor</option>
                                    <option value="03">Penalidades/Otros conceptos</option>
                                </select>
                            </div>
                            <div class="col-lg-4 form-group">
                                <label>Documento que modifica</label>
                                <input disabled type="text" v-model="comprobanteReferencia" name="comprobanteReferencia"
                                       placeholder="Serie y correlativo"
                                       class="form-control">
                                <b-button @click="abrir_modal('nota')" variant="primary"
                                          class="buscar_documento boton_adjunto">
                                    Seleccionar
                                </b-button>
                            </div>
                            <div class="col-lg-5 form-group">
                                <label>Motivo</label>
                                <input autocomplete="nope" type="text" v-model="motivo" placeholder="Descripcion breve"
                                       class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 mb-3" v-show="comprobante=='30' || comprobante == '01' || comprobante == '03' || ((comprobante == '08.01' || comprobante == '08.02') && comprobanteReferencia != '')">
                <div class="card">
                    <div class="card-header">
                        Detalle
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if(json_decode(cache('config')['interfaz'], true)['buscador_clientes'] == 1)
                                <div class="col-lg-9">
                                    <autocomplete-cliente v-on:agregar_cliente="agregarCliente"
                                                          v-on:borrar_cliente="borrarCliente"
                                                          ref="suggestCliente"></autocomplete-cliente>
                                </div>
                                <div class="col-lg-3">
                                    <b-button v-b-modal.modal-nuevo-cliente
                                              class="mb-4" variant="primary"><i class="fas fa-plus"
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
                        <div style="display:none" class="row mt-5">
                            <div class="col-lg-2 col-md-3">
                                <b-button disabled @click="abrir_modal('producto')"
                                          v-b-modal.modal-producto
                                          variant="primary"><i class="fas fa-plus" v-show="!mostrarSpinnerProducto"></i>
                                    <b-spinner v-show="mostrarSpinnerProducto" small label="Loading..."></b-spinner>
                                    Agregar producto
                                </b-button>
                            </div>
                            <div class="col-lg-3">
                                <b-form-checkbox disabled v-model="esConIgv" switch size="sm">
                                    Precios ya incluyen IGV
                                </b-form-checkbox>
                            </div>
                        </div>
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm tabla-facturar">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col" style="width: 10px"></th>
                                    <th scope="col" style="width: 200px">Producto</th>
                                    <th scope="col" style="width: 250px">Caracteristicas</th>
                                    <th scope="col" style="width: 90px">Precio</th>
                                    <th scope="col" style="width: 90px">Cantidad</th>
                                    <th scope="col" style="width: 90px; display: none">% Dscto</th>
                                    <th scope="col" style="width: 90px; display: none">Dscto</th>
                                    <th scope="col" style="width: 100px">Afectación</th>
                                    <th scope="col" style="width: 80px; text-align: center">Subtotal</th>
                                    <th scope="col" style="width: 80px; text-align: center">Igv</th>
                                    <th scope="col" style="width: 80px; text-align: center">Total</th>
                                    <th scope="col" style="width: 50px"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(producto,index) in productosSeleccionados" :key="producto.num_item">
                                    <td></td>
                                    <td style="display:none">@{{producto.idproducto}}</td>
                                    <td>@{{producto.cod_producto}} - @{{producto.nombre}}</td>
                                    <td><input class="form-control" type="text" v-model="producto.presentacion"></td>
                                    <td>@{{ producto.precio }}</td>
                                    <td>@{{ producto.cantidad }}</td>
                                    <td style="display:none;"><input @keyup="calcular(index)" class="form-control"
                                                                     type="text"
                                                                     v-model="producto.porcentaje_descuento"></td>
                                    <td style="display:none;"><input @keyup="calcular(index)" class="form-control"
                                                                     type="text" v-model="producto.descuento"></td>
                                    <td>
                                        <select @change="calcular(index)" v-model="producto.tipoAfectacion" name="cargo"
                                                class="custom-select">
                                            <option value="10">Gravado - Operación Onerosa</option>
                                            <option value="11">Gravado – Retiro por premio</option>
                                            <option value="12">Gravado – Retiro por donación</option>
                                            <option value="13">Gravado – Retiro</option>
                                            <option value="14">Gravado – Retiro por publicidad</option>
                                            <option value="15">Gravado – Bonificaciones</option>
                                            <option value="16">Gravado – Retiro por entrega a trabajadores</option>
                                            <option value="20">Exonerado - Operación Onerosa</option>
                                            <option value="21">Exonerado – Transferencia Gratuita</option>
                                            <option value="30">Inafecto - Operación Onerosa</option>
                                            <option value="31">Inafecto – Retiro por Bonificación</option>
                                            <option value="32">Inafecto – Retiro</option>
                                            <option value="33">Inafecto – Retiro por Muestras Médicas</option>
                                            <option value="34">Inafecto - Retiro por Convenio Colectivo</option>
                                            <option value="35">Inafecto – Retiro por premio</option>
                                            <option value="36">Inafecto - Retiro por publicidad</option>
                                        </select>
                                    </td>
                                    <td class="text-center">@{{producto.subtotal}}</td>
                                    <td class="text-center">@{{producto.igv}}</td>
                                    <td class="text-center">@{{producto.total}}</td>
                                    <td>
                                        <a style="display:none;" @click="borrarItemVenta(index)"
                                           href="javascript:void(0)">
                                            <button class="btn btn-danger" title="Borrar item"><i
                                                        class="fas fa-trash"></i>
                                            </button>
                                        </a>
                                    </td>
                                </tr>
                                <tr class="text-center" v-show="productosSeleccionados.length == 0">
                                    <td colspan="11">No hay datos para mostrar</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div>
                            <b-alert :variant="mensajeStock.style" show v-show="mensajeStock.string.length>0">
                                @{{ mensajeStock.string }}
                            </b-alert>
                        </div>
                        <div class="dropdown-divider"></div>
                        <div class="col-lg-2 mt-3">
                            <div class="form-group">
                                <label for="observaciones">Descuento global (%):</label>
                                <input @keyup="calcularTotalVenta()" class="form-control"
                                       v-model="porcentaje_descuento_global" type="text">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 mb-3" v-show="comprobanteReferencia != '' && (comprobante=='07.01' || comprobante == '07.02')">
                <div class="card">
                    <div class="card-header">
                        Detalle
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <strong>Cliente: </strong> @{{ this.clienteSeleccionado['num_documento'] }} - @{{this.clienteSeleccionado['nombre']}} <hr>
                            </div>
                        </div>
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm tabla-facturar">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col" style="width: 10px"></th>
                                    <th scope="col" style="width: 200px">Producto</th>
                                    <th scope="col" style="width: 250px">Caracteristicas</th>
                                    <th scope="col" style="width: 90px">Precio</th>
                                    <th scope="col" style="width: 90px">Cantidad</th>
                                    <th scope="col" style="width: 90px">Dscto</th>
                                    <th scope="col" style="width: 80px;">Subtotal</th>
                                    <th scope="col" style="width: 80px;">Igv</th>
                                    <th scope="col" style="width: 80px;">Total</th>
                                    <th scope="col" style="width: 50px"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(producto,index) in productosSeleccionados" :key="producto.index">
                                    <td></td>
                                    <td style="display:none">@{{producto.idproducto}}</td>
                                    <td>@{{ producto.nombre }}</td>
                                    <td style="white-space: break-spaces">@{{ producto.presentacion}}</td>
                                    <td>@{{ producto.precio }}</td>
                                    <td>@{{ producto.cantidad }}</td>
                                    <td>@{{ producto.descuento }}</td>
                                    <td>@{{ producto.subtotal }}</td>
                                    <td>@{{ producto.igv }}</td>
                                    <td>@{{ producto.total }}</td>
                                    <td>
                                    </td>
                                </tr>
                                <tr class="text-center" v-show="productosSeleccionados.length == 0">
                                    <td colspan="11">Agrega productos desde el buscador</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="dropdown-divider"></div>
                        <p>Descuento global: @{{porcentaje_descuento_global}} %</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 mb-5">
                <div class="card">
                    <div class="card-header">
                        Acciones
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6 tipoPagoDiv">
                                <label>Tipo de pago</label>
                                <div class="row">
                                    <div class="col-lg-6 form-group">
                                        <select :disabled="mostrarProgresoGuardado || productosSeleccionados.length==0 || comprobante=='07.01' || comprobante=='07.02' || comprobante=='08.01' || comprobante=='08.02'"
                                                v-model="tipoPago" class="custom-select">
                                            <option value="1">Contado</option>
                                            <option value="2">Crédito</option>
                                        </select>
                                    </div>
                                    <div v-show="tipoPago==1" class="col-lg-6 form-group">
                                        <select :disabled="mostrarProgresoGuardado || productosSeleccionados.length==0 || comprobante=='07.01' || comprobante=='07.02' || comprobante=='08.01' || comprobante=='08.02'"
                                                v-model="tipoPagoContado" class="custom-select">
                                            <option value="1">Efectivo</option>
                                            <option value="3">Depósito</option>
                                            <option value="4">Tarjeta</option>
                                        </select>
                                    </div>
                                    <div v-show="tipoPago==2" class="col-lg-6 form-group">
                                        <b-button
                                                :disabled="mostrarProgresoGuardado || productosSeleccionados.length==0 || comprobante=='07.01' || comprobante=='07.02' || comprobante=='08.01' || comprobante=='08.02'"
                                                @click="abrir_modal('pago')" variant="primary"><i
                                                    class="fas fa-plus"></i> Cuotas (@{{cuotas.length}})
                                        </b-button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-5">
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
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-5">
                <div class="card">
                    <div class="card-header">
                        Totales e impuestos
                    </div>
                    <div class="card-body">
                        <table style="width:100%;">
                            <tr v-show="gratuitas > 0">
                                <td style="width: 50%">OP. GRATUITAS:</td>
                                <td>@{{ moneda }} @{{ gratuitas }}</td>
                            </tr>
                            <tr v-show="inafectas > 0">
                                <td style="width: 50%">OP. INAFECTAS:</td>
                                <td>@{{ moneda }} @{{ inafectas }}</td>
                            </tr>
                            <tr v-show="exoneradas > 0">
                                <td style="width: 50%">OP. EXONERADAS:</td>
                                <td>@{{ moneda }} @{{ exoneradas }}</td>
                            </tr>
                            <tr v-show="descuentos > 0">
                                <td style="width: 50%">DESCUENTOS:</td>
                                <td>@{{ moneda }} @{{ descuentos }}</td>
                            </tr>
                            <tr>
                                <td style="width: 50%">OP. GRAVADAS:</td>
                                <td>@{{ moneda }} @{{ gravadas }}</td>
                            </tr>
                            <tr>
                                <td style="width: 50%">IGV:</td>
                                <td>@{{ moneda }} @{{ igv }}</td>
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
    <!--INICIO MODAL DOCUMENTO -->
    <b-modal size="lg" id="modal-documento" ref="modal-documento" ok-only @hidden="resetModal">
        <template slot="modal-title">
            Seleccionar documento
        </template>
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="buscar">Busca por correlativo o cliente:</label>
                        <input @keyup="delay()" v-model="buscar" type="text" name="buscar"
                               placeholder="Buscar..." class="form-control" autocomplete="off">
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="table-responsive tabla-gestionar">
                        <table class="table table-striped table-hover table-sm">
                            <thead class="bg-custom-green">
                            <tr>
                                <th scope="col">N° Doc.</th>
                                <th scope="col">Serie/correlativo</th>
                                <th scope="col">Cliente</th>
                                <th scope="col">Importe</th>
                                <th scope="col">Estado</th>
                                <th scope="col"></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr :class="{'td-anulado':doc.estado=='ANULADO'}" v-for="(doc,index) in listaDocumentos"
                                :key="doc.idventa">
                                <td>@{{doc.idventa}}</td>
                                <td style="width: 20%">@{{doc.serie}}-@{{doc.correlativo}}</td>
                                <td style="width: 40%">@{{doc.nombre}}</td>
                                <td>@{{doc.total_venta}}</td>
                                <td>
                                    <span class="badge"
                                          :class="{'badge-warning':doc.estado == 'PENDIENTE',
                                   'badge-success' : doc.estado == 'ACEPTADO',
                                   'badge-dark' : doc.estado == 'ANULADO','badge-danger' : doc.estado == 'RECHAZADO'}">
                                        @{{ doc.estado }}
                                    </span>
                                </td>
                                <td style="width: 5%" class="botones-accion">
                                    <button @click="agregarDocumento(doc.idventa,false)"
                                            :disabled="doc.estado!='ACEPTADO' && (comprobante=='07.01' || comprobante=='07.02' || comprobante=='08.01' || comprobante=='08.02')"
                                            class="btn btn-info" title="Seleccionar documento"><i
                                                class="fas fa-check"></i>
                                    </button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </b-modal>
    <!--FIN MODAL DOCUMENTO -->
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
                        <input v-model="cuota.monto" type="text" class="form-control">
                    </div>
                    <div class="col-lg-6">
                        <label>Fecha de pago:</label>
                        <input min="{{date('Y-m-d')}}" type="date" v-model="cuota.fecha" name="fechaCuota"
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
    <!--INICIO MODAL GUIAS RELACIONADAS -->
    <b-modal size="sm" id="modal-guias" ref="modal-guias" @ok="">
    <template slot="modal-title">
        Guias relacionadas
    </template>
    <div class="container">
        <div class="row">
            <div v-for="(guia,index) in guiasRelacionadasAux" class="col-lg-12 mb-3" :key="index">
                <div class="row">
                    <div class="col-lg-10">
                        <label>Guía @{{ index + 1 }}</label>
                        <input v-model="guia.correlativo" type="text" class="form-control">
                    </div>
                    <div class="col-lg-2">
                        <i @click="borrarGuia(index)" class="fas fa-times-circle btnBorrarCuota"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 mb-4">
                <button @click="agregarGuia(true)" class="btn btn-info"><i class="fas fa-plus"></i> Agregar
                </button>
            </div>
        </div>
    </div>
    <template #modal-footer="{ ok, cancel}">
        <b-button variant="secondary" @click="cancelarGuiasRel()">
            Cancel
        </b-button>
        <b-button variant="primary" @click="agregarGuiasRel">
            OK
        </b-button>
    </template>
    </b-modal>
    <!--FIN MODAL GUIAS RELACIONADAS -->
    <modal-ubigeo
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
            v-bind:ultimo_id="{{$ultimo_id}}"
            v-bind:tipo_cambio_compra="{{cache('opciones')['tipo_cambio_compra']}}"
            v-on:agregar="agregarProductoNuevo">
    </agregar-producto>
@endsection
@section('script')
    <script>

        let app = new Vue({
            el: '.app',
            data: {
                idpresupuesto: '<?php echo isset($_GET['presupuesto']) ? $_GET['presupuesto'] : null ?>',
                idorden: '<?php echo isset($_GET['orden']) ? $_GET['orden'] : null ?>',
                idguia: '<?php echo isset($_GET['guia']) ? $_GET['guia'] : null ?>',
                accion: 'insertar',
                mostrarProgresoGuardado: false,
                numeroGuia: '',
                numeroOc: '',
                fecha: '{{date('Y-m-d')}}',
                serie: 'B001',
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
                    placa_vehiculo:<?php echo json_encode(json_decode(cache('config')['guia'], true)['placa']) ?>,
                    dni_conductor:<?php echo json_encode(json_decode(cache('config')['guia'], true)['num_doc']) ?>,
                    codigo_traslado: '01',
                    fecha_traslado: '{{date('Y-m-d')}}',
                    doc_relacionado: '-1',
                    num_doc_relacionado: '',
                    tipo_transporte:<?php echo json_encode(json_decode(cache('config')['guia'], true)['tipo_transporte']) ?>,
                },
                numero_guia_fisica: '',
                mensajeStock: {
                    string: '',
                    style: ''
                },
                guiasRelacionadas: [],
                guiasRelacionadasAux: [],
                tipoCambio: <?php echo cache('opciones')['tipo_cambio_compra'] ?>,
                nombreCliente: "",
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

            },
            created(){
                this.calcularTotalVenta();
                this.obtenerCorrelativo();
                if (this.idpresupuesto !== '') {
                    this.agregarDocumento(this.idpresupuesto, true, false);
                }
                if (this.idguia !== '') {
                    this.agregarDocumento(this.idguia, false, true);
                }
                if (this.idorden !== '') {
                    this.agregarDocumento(this.idorden, true);
                }
            },
            methods: {
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
                        fecha: '{{date('Y-m-d')}}',
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
                        let regex = new RegExp(/^([0-9\u002D\u0054\u0074-]+)$/);
                        if (!regex.test(guia.correlativo)) {
                            this.alerta('Una de las casillas contiene un número de guía no válido. Usa solo números para guías físicas e inicia con T para guías electrónicas.');
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
                    this.buscar = nombre;
                },
                obtenerDocumentos(copiar){
                    let filtro_comprobante = -1;
                    if (!copiar) {
                        switch (this.comprobante) {
                            case '07.01':
                                filtro_comprobante = '03';
                                break;
                            case '07.02':
                                filtro_comprobante = '01';
                                break;
                            case '08.01':
                                filtro_comprobante = '03';
                                break;
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
                agregarDocumento(idventa, esDesdeOrden){

                    let post_action = '{{action('VentaController@copiarVenta')}}';

                    if (esDesdeOrden) {
                        post_action = '{{action('VentaController@copiarOrden')}}'
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
                            this.monto_descuento_global = 0.00;
                            this.comprobanteReferencia = datos['comprobante_referencia'];
                            this.esConIgv = datos.igv_incluido === 1;
                            this.productosSeleccionados = datos.productos;
                            this.porcentaje_descuento_global = datos.facturacion.porcentaje_descuento_global * 100;
                            this.gravadas = datos.facturacion.total_gravadas;
                            this.exoneradas = datos.facturacion.total_exoneradas;
                            this.inafectas = datos.facturacion.total_inafectas;
                            this.gratuitas = datos.facturacion.total_gratuitas;
                            this.base_descuento_global = datos.facturacion.base_descuento_global;
                            this.monto_descuento_global = datos.facturacion.descuento_global;
                            this.descuentos = datos.facturacion.total_descuentos;
                            this.igv = datos.facturacion.igv;
                            this.totalVenta = datos.total_venta;
                            this.subtotalVenta = datos.facturacion.valor_venta_bruto;
                            this.numero_guia_fisica = datos.facturacion.guia_relacionada;
                            this.numeroOc = datos.facturacion.oc_relacionada;
                            this.moneda = datos.facturacion.codigo_moneda;
                            this.cuotas = [];
                            this.codigo_tipo_factura = datos.facturacion.codigo_tipo_factura || '0101';
                            if (datos.facturacion.codigo_moneda == 'PEN') {
                                this.moneda = 'S/';
                            }
                            if (this.comprobante == '30' || this.comprobante == '03' || this.comprobante == '01') {
                                this.comprobante = datos.facturacion.codigo_tipo_documento;
                                this.comprobanteReferencia = null;
                                this.inhabilitarComprobante = false;
                            } else {
                                if(this.tipo_nota_electronica == 13){
                                    for (let producto of this.productosSeleccionados) {
                                        producto['precio'] = 0;
                                        producto['descuento'] = 0;
                                        producto['subtotal'] = 0;
                                        producto['igv'] = 0;
                                        producto['total'] = 0;
                                    }
                                    this.calcularTotalVenta();
                                }
                                this.inhabilitarComprobante = true;
                            }
                            this.fecha = '{{date('Y-m-d')}}';
                            this.fecha_vencimiento = '{{date('Y-m-d')}}';
                            this.idorden = null;
                            if (esDesdeOrden) {
                                this.idorden = datos.idorden;
                                this.calcularTotalPorItem();
                                this.calcularTotalVenta();
                                this.inhabilitarComprobante = false;
                                this.comprobante = datos.facturacion.codigo_tipo_documento;
                            }

                            this.$refs['modal-documento'].hide();
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
                        }

                    }, 500);
                },
                agregarProducto(obj){
                    let productos = this.productosSeleccionados.push(Object.assign({}, obj));
                    //crear propiedades precio y cantidad en objeto productosSeleccionados:{} para usarlos
                    //más tarde al procesar la venta.
                    let i = productos - 1;

                    let subtotal = (this.productosSeleccionados[i]['precio'] * 1).toFixed(2);
                    this.$set(this.productosSeleccionados[i], 'num_item', i);
                    this.$set(this.productosSeleccionados[i], 'cantidad', 1);
                    this.$set(this.productosSeleccionados[i], 'porcentaje_descuento', '0');
                    this.$set(this.productosSeleccionados[i], 'descuento', '0.00');
                    this.$set(this.productosSeleccionados[i], 'subtotal', subtotal);


                    if(this.codigo_tipo_factura == '0101'){
                        this.$set(this.productosSeleccionados[i], 'igv', (this.productosSeleccionados[i]['precio'] * 0.18).toFixed(2));
                        this.$set(this.productosSeleccionados[i], 'tipoAfectacion', '10');
                        this.$set(this.productosSeleccionados[i], 'total', (this.productosSeleccionados[i]['precio'] * 1.18).toFixed(2));

                        if (this.esConIgv) {
                            subtotal = (this.productosSeleccionados[i]['precio'] / 1.18).toFixed(2);
                            this.$set(this.productosSeleccionados[i], 'subtotal', subtotal);
                            this.$set(this.productosSeleccionados[i], 'igv', (this.productosSeleccionados[i]['precio'] - subtotal).toFixed(2));
                            this.$set(this.productosSeleccionados[i], 'total', this.productosSeleccionados[i]['precio']);
                        }

                    } else{
                        this.$set(this.productosSeleccionados[i], 'igv', 0);
                        this.$set(this.productosSeleccionados[i], 'tipoAfectacion', '10');
                        this.$set(this.productosSeleccionados[i], 'total', subtotal);
                    }



                    this.calcularTotalVenta();
                    this.validar_stock(this.productosSeleccionados[i]);
                    this.agregarProductosSession();
                },
                calcular(index){
                    let producto = this.productosSeleccionados[index];
                    let _porcentaje_descuento = producto['porcentaje_descuento'] / 100;
                    let monto_descuento = 0;
                    //obtener precio bruto de productos afectados con el igv
                    let precio_bruto = producto['precio'] / 1.18;
                    if(this.codigo_tipo_factura == '0200'){
                        monto_descuento_subtotal = precio_bruto * producto['cantidad'] * _porcentaje_descuento;
                        monto_descuento_total = producto['precio'] * producto['cantidad'] * _porcentaje_descuento;
                        producto['descuento'] = monto_descuento_total.toFixed(2);
                        producto['subtotal'] = (producto['precio'] * producto['cantidad'] - monto_descuento_total).toFixed(2);
                        producto['igv'] = 0;
                        producto['total'] = producto['subtotal'];
                    } else {
                        switch (producto['tipoAfectacion']) {
                            case '10':
                                monto_descuento_subtotal = precio_bruto * producto['cantidad'] * _porcentaje_descuento;
                                monto_descuento_total = producto['precio'] * producto['cantidad'] * _porcentaje_descuento;
                                producto['descuento'] = monto_descuento_total.toFixed(2);
                                producto['subtotal'] = (producto['precio'] * producto['cantidad'] - monto_descuento_total).toFixed(2);
                                producto['igv'] = (producto['subtotal'] * 0.18).toFixed(2);
                                producto['total'] = (Number(producto['subtotal']) + Number(producto['igv'])).toFixed(2);
                                if (this.esConIgv) {
                                    producto['descuento'] = monto_descuento_subtotal.toFixed(2);
                                    producto['subtotal'] = (precio_bruto * producto['cantidad'] - monto_descuento_subtotal).toFixed(2);
                                    producto['total'] = (producto['precio'] * producto['cantidad'] - monto_descuento_total).toFixed(2);
                                    producto['igv'] = (producto['total'] - producto['subtotal']).toFixed(2);
                                }
                                break;
                            case '20':
                            case '30':
                                monto_descuento_subtotal = producto['precio'] * producto['cantidad'] * _porcentaje_descuento;
                                producto['descuento'] = monto_descuento_subtotal.toFixed(2);
                                producto['subtotal'] = (producto['precio'] * producto['cantidad'] - monto_descuento_subtotal).toFixed(2);
                                producto['total'] = (producto['precio'] * producto['cantidad'] - monto_descuento_subtotal).toFixed(2);
                                producto['igv'] = (producto['total'] - producto['subtotal']).toFixed(2);
                                break;
                            default:
                                monto_descuento_subtotal = producto['precio'] * producto['cantidad'] * _porcentaje_descuento;
                                producto['descuento'] = monto_descuento_subtotal.toFixed(2);
                                producto['subtotal'] = (producto['precio'] * producto['cantidad'] - monto_descuento_subtotal).toFixed(2);
                                producto['total'] = '0.00';
                                producto['igv'] = '0.00';
                                break;
                        }
                    }

                    this.calcularTotalVenta();
                    this.validar_stock(producto);
                    this.agregarProductosSession();
                },
                calcularTotalPorItem(){

                    for (let producto of this.productosSeleccionados) {

                        let _porcentaje_descuento = producto['porcentaje_descuento'] / 100;
                        let precio_bruto = producto['precio'] / 1.18;

                        monto_descuento_subtotal = precio_bruto * producto['cantidad'] * _porcentaje_descuento;
                        monto_descuento_total = producto['precio'] * producto['cantidad'] * _porcentaje_descuento;
                        producto['descuento'] = monto_descuento_total.toFixed(2);
                        producto['subtotal'] = (producto['precio'] * producto['cantidad'] - monto_descuento_total).toFixed(2);
                        producto['igv'] = (producto['subtotal'] * 0.18).toFixed(2);
                        producto['total'] = (Number(producto['subtotal']) + Number(producto['igv'])).toFixed(2);
                        if (this.esConIgv) {
                            producto['descuento'] = monto_descuento_subtotal.toFixed(2);
                            producto['subtotal'] = (precio_bruto * producto['cantidad'] - monto_descuento_subtotal).toFixed(2);
                            producto['total'] = (producto['precio'] * producto['cantidad'] - monto_descuento_total).toFixed(2);
                            producto['igv'] = (producto['total'] - producto['subtotal']).toFixed(2);
                        }
                    }
                },
                calcularTotalVenta(){

                    //Calcular operaciones gravadas

                    let suma_gravadas = 0;
                    let suma_exoneradas = 0;
                    let suma_inafectas = 0;
                    let suma_gratuitas = 0;
                    let suma_descuentos = 0;
                    let suma_igv = 0;
                    let desc_global = this.porcentaje_descuento_global / 100;
                    let total_venta_bruto = 0; //Total de ventas sin igv ni descuentos

                    for (let producto of this.productosSeleccionados) {

                        switch (producto.tipoAfectacion) {
                            case '10':
                                suma_gravadas += Number(producto['subtotal']);
                                if (this.esConIgv) {
                                    total_venta_bruto += producto['cantidad'] * producto['precio'] / 1.18;
                                } else {
                                    total_venta_bruto += producto['cantidad'] * producto['precio'];
                                }
                                break;
                            case '20':
                                suma_exoneradas += Number(producto.subtotal);
                                total_venta_bruto += producto['cantidad'] * producto['precio'];
                                break;
                            case '30':
                                suma_inafectas += Number(producto.subtotal);
                                total_venta_bruto += producto['cantidad'] * producto['precio'];
                                break;
                            default:
                                suma_gratuitas += Number(producto.subtotal);
                        }

                        suma_descuentos += Number(producto['descuento']);
                        suma_igv += Number(producto['igv']);

                    }

                    this.gravadas = (suma_gravadas - (suma_gravadas * desc_global)).toFixed(2);
                    this.exoneradas = (suma_exoneradas - (suma_exoneradas * desc_global)).toFixed(2);
                    this.inafectas = (suma_inafectas - (suma_inafectas * desc_global)).toFixed(2);
                    this.gratuitas = (suma_gratuitas).toFixed(2);
                    this.base_descuento_global = suma_gravadas + suma_inafectas + suma_exoneradas;
                    this.monto_descuento_global = ((suma_gravadas + suma_inafectas + suma_exoneradas) * desc_global).toFixed(2);
                    this.descuentos = (suma_descuentos + Number(this.monto_descuento_global)).toFixed(2);
                    this.igv = (suma_igv - (suma_igv * desc_global)).toFixed(2);
                    this.totalVenta = (Number(this.gravadas) + Number(this.exoneradas) + Number(this.inafectas) + Number(this.igv)).toFixed(2);
                    this.subtotalVenta = total_venta_bruto.toFixed(2);
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
                                'idorden': this.idorden,
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
                            })
                                .then(response => {
                                    localStorage.removeItem('productos');
                                    localStorage.removeItem('cliente');
                                    localStorage.removeItem('esConIgv');
                                    if (isNaN(response.data.idventa)) {
                                        this.$swal({
                                            position: 'top',
                                            icon: 'info',
                                            title: 'Se ha guardado la venta',
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
                                            title: 'Se ha guardado la venta',
                                            text: response.data.respuesta,
                                            timer: 60000,
                                        }).then(() => {
                                            location.href = '/facturacion/documento/' + response.data.idventa;
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
                    if (this.tipoPago == 2 && (this.comprobante == '01' || this.comprobante == '03')) {
                        if (this.cuotas.length == 0) errorDatosVenta.push('*Las ventas a crédito deben contener detalle de las cuotas');

                        let suma_cuotas = 0;
                        for (let cuota of this.cuotas) {
                            suma_cuotas += Number(cuota.monto);
                        }

                        if (suma_cuotas > this.totalVenta) errorDatosVenta.push('*La suma de las cuotas supera el monto total de la venta');
                        if (suma_cuotas < this.totalVenta) errorDatosVenta.push('*La suma de las cuotas es inferior al monto total de la venta');
                    }


                    if (Object.keys(this.clienteSeleccionado).length == 0) errorDatosVenta.push('*Debes ingresar un cliente');
                    if (this.comprobante == '01' || this.comprobante == '07.02' || this.comprobante == '08.02') {
                        if (this.clienteSeleccionado['num_documento'] && this.clienteSeleccionado['num_documento'].length != 11 && this.codigo_tipo_factura == '0101') errorDatosVenta.push('*Ingrese un RUC válido');
                    } else if (this.comprobante == '03' || this.comprobante == '07.01' || this.comprobante == '08.01') {
                        let totalCheck = this.totalVenta;
                        if(this.moneda == 'USD'){
                            totalCheck = this.totalVenta * Number(this.tipoCambio);
                        }
                        if (this.clienteSeleccionado['num_documento'] && totalCheck >= 700){
                            str = this.clienteSeleccionado['num_documento'];
                            let regex = new RegExp(/(.)\1{7}/);
                            if(regex.test(str)){
                                errorDatosVenta.push('*Para boletas mayores a S/.700.00 debe ingresar un DNI válido');
                            }

                        }
                        if (this.clienteSeleccionado['num_documento'] && (this.clienteSeleccionado['num_documento'].length < 8 || this.clienteSeleccionado['num_documento'].length > 11)) errorDatosVenta.push('*Ingrese un DNI o RUC válido');
                    }
                    if (this.comprobante == '07.02' || this.comprobante == '08.02' || this.comprobante == '07.01' || this.comprobante == '08.01') {
                        if (this.motivo.length == 0) errorDatosVenta.push('*El campo motivo no puede quedar en blanco');
                        if (this.comprobanteReferencia.length == 0) errorDatosVenta.push('*El campo documento que modifica no puede quedar en blanco');
                    }

                    if (this.comprobante == '07.01' || this.comprobante == '07.02') {
                        if(this.tipo_nota_electronica == 13){
                            if (this.cuotas.length == 0) errorDatosVenta.push('*El tipo de nota de crédito debe contener detalle de las cuotas');
                        }
                    }

                    if (this.esConGuia) {
                        if (this.guia_datos_adicionales.direccion.length == 0) errorDatosVenta.push('*El campo direccion de la guia no puede estar vacío');
                        if (this.guia_datos_adicionales.ubigeo.length != 6) errorDatosVenta.push('*El campo ubigeo debe contener un código de 6 dígitos');
                        if (isNaN(this.guia_datos_adicionales.ubigeo)) errorDatosVenta.push('*El campo ubigeo debe ser un número');
                        if (this.guia_datos_adicionales.peso.length == 0) errorDatosVenta.push('*El campo peso no puede estar vacío');
                        if (isNaN(this.guia_datos_adicionales.peso)) errorDatosVenta.push('*El campo peso debe ser un número');
                        if (this.guia_datos_adicionales.bultos.length == 0) errorDatosVenta.push('*El campo N° de bultos no puede estar vacío');
                        if (isNaN(this.guia_datos_adicionales.bultos)) errorDatosVenta.push('*El campo N° de bultos debe ser un número');
                        if (this.guia_datos_adicionales.tipo_transporte == '01') {
                            if (this.guia_datos_adicionales.num_doc_transportista.length == 0) errorDatosVenta.push('*El campo número de documento de transportista no puede estar vacío');
                            if (isNaN(this.guia_datos_adicionales.num_doc_transportista)) errorDatosVenta.push('*El campo número de documento de transportista debe ser un número sin letras ni espacios');
                            if (!(this.guia_datos_adicionales.num_doc_transportista.length === 11) && this.guia_datos_adicionales.tipo_doc_transportista == '6') errorDatosVenta.push('*El campo número documento de transportista debe contener 11 dígitos');
                            if (!(this.guia_datos_adicionales.num_doc_transportista.length == 8) && this.guia_datos_adicionales.tipo_doc_transportista == '1') errorDatosVenta.push('*El campo número documento de transportista debe contener 8 dígitos');
                            if (this.guia_datos_adicionales.razon_social_transportista.length == 0) errorDatosVenta.push('*El campo razón social de transportista no puede estar vacío');
                        } else {
                            if (this.guia_datos_adicionales.placa_vehiculo.length == 0) errorDatosVenta.push('*El campo placa vehículo no puede estar vacío');
                            if (this.guia_datos_adicionales.dni_conductor.length == 0) errorDatosVenta.push('*El campo dni de conductor no puede estar vacío');
                            if (isNaN(this.guia_datos_adicionales.dni_conductor)) errorDatosVenta.push('*El campo dni de conductor debe ser un número sin letras ni espacios');
                            if (this.guia_datos_adicionales.dni_conductor.length != 8) errorDatosVenta.push('*El campo dni de conductor debe contener 8 dígitos');
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
                validar_stock(producto){
                    if (producto['tipo_producto'] === 1) {
                        this.mensajeStock.string = '';
                        if (producto['cantidad'] > producto['stock']) {
                            this.mensajeStock.string = 'La cantidad ingresada supera el stock del producto ' + producto['nombre'] + '(' + producto['stock'] + ' ' + producto['unidad_medida'] + ')' + '. Revise sus existencias.';
                            this.mensajeStock.style = 'danger';
                        } else if (producto['cantidad'] >= (producto['stock'] - producto['stock_bajo'])) {
                            this.mensajeStock.string = 'El stock del producto ' + producto['nombre'] + ' (' + producto['stock'] + ' ' + producto['unidad_medida'] + ')' + ' es bajo, es necesario adquirir más unidades.';
                            this.mensajeStock.style = 'warning';
                        }
                    }
                },
                cambiarSerie(){

                    switch (this.comprobante) {
                        case '01':
                            this.serie = 'F001';
                            break;
                        case '03':
                            this.serie = 'B001';
                            break;
                        case '07.01':
                            this.serie = 'BC01';
                            break;
                        case '07.02':
                            this.serie = 'FC01';
                            break;
                        case '08.01':
                            this.serie = 'BD01';
                            break;
                        case '08.02':
                            this.serie = 'FD01';
                            break;
                        case '20':
                            this.serie = 'PROF';
                            break;
                        default:
                            this.serie = 'REC';
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
                    this.serie = 'B001';
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
                        codigo_traslado: '01',
                        fecha_traslado: '{{date('Y-m-d')}}',
                        doc_relacionado: '-1',
                        num_doc_relacionado: '',
                        tipo_transporte: '01'
                    };
                    this.mensajeStock = {
                        string: '',
                        style: ''
                    };
                    this.numero_guia_fisica = '';
                    this.tipo_busqueda = '';
                    this.guiasRelacionadas = [];
                    this.guiasRelacionadasAux = [];
                    this.calcularTotalVenta();
                    this.obtenerCorrelativo();
                    localStorage.removeItem('productos');
                    localStorage.removeItem('cliente');
                    localStorage.removeItem('esConIgv');
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
                }
            },
            watch: {
                comprobante(comp){
                    this.obtenerCorrelativo();
                    this.comprobanteReferencia = '';
                    this.guiasRelacionadas = [];
                    this.esConGuia = 0;
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