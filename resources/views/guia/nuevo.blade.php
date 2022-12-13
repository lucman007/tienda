@extends('layouts.main')
@section('titulo', 'Registrar guía')
@section('contenido')
    @php $agent = new \Jenssegers\Agent\Agent() @endphp
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-sm-12">
                <h3 class="titulo-admin-1">
                    <a href="{{url('guia')}}"><i class="fas fa-arrow-circle-left"></i></a>
                    Guía electrónica
                </h3>
                <b-button @click="abrir_modal('venta')"  class="mr-2"  variant="primary"><i class="fas fa-copy"></i> Copiar de venta</b-button>
                <b-button @click="abrir_modal('guia')"  class="mr-2"  variant="primary"><i class="fas fa-copy"></i> Copiar de guia</b-button>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 mt-4 mb-3">
                <div class="card">
                    <div class="card-header">
                        Datos guía
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-2 form-group">
                                <label>Fecha de emisión</label>
                                <input type="date" v-model="fecha" max="{{date('Y-m-d')}}" class="form-control">
                            </div>
                            <div class="col-lg-3 form-group">
                                <label>Serie y correlativo</label>
                                <input disabled type="text" v-model="numeroGuia" class="form-control">
                            </div>
                            <div class="col-lg-3 form-group">
                                <label>Documento relacionado</label>
                                <select v-model="guia_datos_adicionales.doc_relacionado" class="custom-select">
                                    @php
                                        $doc_relacionado = \sysfact\Http\Controllers\Helpers\DataGuia::getDocumentoRelacionado();
                                    @endphp
                                    <option value="-1">Ninguno</option>
                                    @foreach($doc_relacionado as $item)
                                        <option value="{{$item['num_val']}}">{{$item['label']}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div v-show="guia_datos_adicionales.doc_relacionado!='-1'" class="col-lg-3 form-group">
                                <label>N° documento relacionado</label>
                                <input type="text" v-model="guia_datos_adicionales.num_doc_relacionado" placeholder="Número documento relacionado"
                                       class="form-control">
                            </div>
                            <div class="col-lg-6 form-group">
                                <label>Dirección de llegada</label>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <b-form-checkbox @change="cambiarDireccionGuia" v-model="domicilioFiscalCliente" switch size="sm">
                                            Domicilio fiscal cliente
                                        </b-form-checkbox>
                                    </div>
                                    <div class="col-lg-8">
                                        <input :disabled="domicilioFiscalCliente" maxlength="100" type="text" v-model="guia_datos_adicionales.direccion"
                                               name="direccion"
                                               class="form-control" placeholder="*Máximo 100 caracteres">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 form-group">
                                <label>Ubigeo</label>
                                <b-input-group>
                                    <input disabled type="text" v-model="guia_datos_adicionales.ubigeo" class="form-control">
                                    <b-input-group-append>
                                        <b-button v-b-modal.modal-ubigeo variant="primary">
                                            <i class="fas fa-search"></i>
                                        </b-button>
                                    </b-input-group-append>
                                </b-input-group>
                            </div>
                            <div class="col-lg-2 form-group">
                                <label>Peso</label>
                                <b-input-group>
                                    <input type="number" v-model="guia_datos_adicionales.peso" name="peso"
                                           class="form-control">
                                    <b-input-group-append>
                                        <b-input-group-text>
                                            KG
                                        </b-input-group-text>
                                    </b-input-group-append>
                                </b-input-group>
                            </div>
                            <div class="col-lg-2 form-group">
                                <label>Bultos</label>
                                <b-input-group>
                                    <input type="number" v-model="guia_datos_adicionales.bultos" name="bultos"
                                           class="form-control">
                                    <b-input-group-append>
                                        <b-input-group-text>
                                            UND
                                        </b-input-group-text>
                                    </b-input-group-append>
                                </b-input-group>
                            </div>
                            <div class="col-lg-2 form-group">
                                <label>Tipo de transporte</label>
                                <select v-model="guia_datos_adicionales.tipo_transporte" class="custom-select">
                                    <option value="01">Público</option>
                                    <option value="02">Privado</option>
                                </select>
                            </div>
                            <div class="col-lg-2 form-group">
                                <label>Categoría</label>
                                <select v-model="guia_datos_adicionales.categoria_vehiculo" class="custom-select">
                                    <option value="M1_L">Vehículo M1 o L (De 2 ó 3 ruedas, o menor a 8 asientos)</option>
                                    <option value="otros">Otros</option>
                                </select>
                            </div>
                            <div v-show="guia_datos_adicionales.tipo_transporte == '02'" class="col-lg-1 form-group">
                                <label>Placa</label>
                                <input type="text" v-model="guia_datos_adicionales.placa_vehiculo"
                                       class="form-control">
                            </div>
                            <div class="col-lg-8" v-show="guia_datos_adicionales.tipo_transporte == '01' && guia_datos_adicionales.categoria_vehiculo != 'M1_L'">
                                <div class="row">
                                    <div class="col-lg-3 form-group">
                                        <label>Ruc de transportista</label>
                                        <b-input-group>
                                            <input @keyup.enter="consultaRucDni(guia_datos_adicionales.tipo_doc_transportista,guia_datos_adicionales.num_doc_transportista)" type="number" v-model="guia_datos_adicionales.num_doc_transportista"
                                                   class="form-control">
                                            <b-input-group-append>
                                                <b-button :disabled="guia_datos_adicionales.num_doc_transportista.length==0" @click="consultaRucDni(guia_datos_adicionales.tipo_doc_transportista,guia_datos_adicionales.num_doc_transportista)" variant="primary" >
                                                    <span v-show="!spinnerRuc"><i class="fas fa-search"></i></span>
                                                    <b-spinner v-show="spinnerRuc" small label="Loading..." ></b-spinner>
                                                </b-button>
                                            </b-input-group-append>
                                        </b-input-group>

                                    </div>
                                    <div class="col-lg-6 form-group">
                                        <label>Razón social transportista</label>
                                        <input type="text" v-model="guia_datos_adicionales.razon_social_transportista"
                                               class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-7" v-show="guia_datos_adicionales.tipo_transporte == '02' && guia_datos_adicionales.categoria_vehiculo != 'M1_L'">
                                <div class="row">
                                    <div class="col-lg-3 form-group">
                                        <label>Licencia de cond.</label>
                                        <input type="text" v-model="guia_datos_adicionales.licencia_conductor"
                                               class="form-control">
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label>DNI del conductor</label>
                                        <b-input-group>
                                            <input @keyup.enter="consultaRucDni(1,guia_datos_adicionales.dni_conductor)" type="number" v-model="guia_datos_adicionales.dni_conductor"
                                                   class="form-control">
                                            <b-input-group-append>
                                                <b-button :disabled="guia_datos_adicionales.dni_conductor.length==0" @click="consultaRucDni(1,guia_datos_adicionales.dni_conductor)" variant="primary" >
                                                    <span v-show="!spinnerRuc"><i class="fas fa-search"></i></span>
                                                    <b-spinner v-show="spinnerRuc" small label="Loading..." ></b-spinner>
                                                </b-button>
                                            </b-input-group-append>
                                        </b-input-group>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label>Nombres</label>
                                        <input type="text" v-model="guia_datos_adicionales.nombre_conductor"
                                               class="form-control">
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label>Apellidos</label>
                                        <input type="text" v-model="guia_datos_adicionales.apellido_conductor"
                                               class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 form-group">
                                <label>Motivo de traslado</label>
                                <select v-model="guia_datos_adicionales.codigo_traslado" class="custom-select">
                                    @php
                                        $motivo_traslado = \sysfact\Http\Controllers\Helpers\DataGuia::getMotivoTraslado();
                                    @endphp
                                    @foreach($motivo_traslado as $item)
                                        <option value="{{$item['num_val']}}">{{$item['label']}}</option>
                                    @endforeach
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
            <div class="col-lg-12 mb-3">
                <div class="card">
                    <div class="card-header">
                        Detalle
                    </div>
                    <div class="card-body">
                        <div class="row mt-4">
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
                                <div class="col-lg-8">
                                    <input type="text" v-model="nombreCliente" class="form-control mb-2"
                                           placeholder="Cliente" disabled readonly>
                                </div>
                            @endif
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
                        </div>
                        <div class="table-responsive tabla-gestionar">
                            @if($agent->isDesktop())
                            <table class="table table-striped table-hover table-sm tabla-facturar">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col" style="width: 10px"></th>
                                    <th scope="col" style="width: 200px">Producto</th>
                                    <th scope="col" style="width: 250px">Caracteristicas</th>
                                    <th scope="col" style="width: 90px">Cantidad</th>
                                    <th scope="col" style="width: 50px"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(producto,index) in productosSeleccionados" :key="producto.num_item">
                                    <td></td>
                                    <td style="display:none">@{{producto.idproducto}}</td>
                                    <td>@{{producto.cod_producto}} - @{{producto.nombre}}</td>
                                    <td><textarea rows="1" class="form-control" type="text" v-model="producto.presentacion"></textarea></td>
                                    <td><input class="form-control" type="text"
                                               v-model="producto.cantidad"></td>
                                    <td>
                                        <a @click="borrarItemVenta(index)" href="javascript:void(0)">
                                            <button class="btn btn-danger" title="Borrar item"><i class="fas fa-trash"></i>
                                            </button>
                                        </a>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            @else
                                <table class="table table-striped table-hover table-sm">
                                    <thead class="bg-custom-green">
                                    <tr>
                                        <th scope="col" style="width: 350px">Descripción</th>
                                        <th scope="col" style="width: 80px">Cantidad</th>
                                        <th scope="col" style="width: 50px"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(producto,index) in productosSeleccionados" :key="index" v-b-modal.modal-detalle @click="editarItem(producto, index)">
                                        <td>@{{producto.nombre}}</td>
                                        <td>@{{producto.cantidad}}</td>
                                        <td @click.stop >
                                            <button @click="borrarItemVenta(index)" class="btn btn-danger"
                                                    title="Borrar item"><i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="text-center" v-show="productosSeleccionados.length == 0"><td colspan="8">No has agregado productos</td></tr>
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 mb-5">
                <div class="card">
                    <div class="card-header">
                        Acciones
                    </div>
                    <div class="card-body pt-5">
                        <div class="form-group text-center">
                            <b-button :disabled="mostrarProgresoGuardado || productosSeleccionados.length==0" class="mb-2" @click="procesarGuia"
                                      variant="success">
                                <i v-show="!mostrarProgresoGuardado" class="fas fa-save"></i>
                                <b-spinner v-show="mostrarProgresoGuardado" small label="Loading..." ></b-spinner>Procesar
                            </b-button>
                            <b-button class="mb-2" @click="limpiar" variant="danger"><i class="fas fa-ban"></i> Cancelar
                            </b-button>
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
                                <th scope="col">N°</th>
                                <th scope="col">Serie/correlativo</th>
                                <th scope="col">Cliente</th>
                                <th v-show="comprobante_a_copiar=='-1'" scope="col">Importe</th>
                                <th scope="col">Estado</th>
                                <th scope="col"></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr :class="{'td-anulado':doc.estado=='ANULADO'}" v-for="(doc,index) in listaDocumentos" :key="doc.idventa">
                                <td v-show="comprobante_a_copiar=='-1'">@{{doc.idventa}}</td>
                                <td v-show="comprobante_a_copiar=='09'">@{{doc.idguia}}</td>
                                <td v-show="comprobante_a_copiar=='-1'" style="width: 20%">@{{doc.serie}}-@{{doc.correlativo}}</td>
                                <td v-show="comprobante_a_copiar=='09'" style="width: 20%">@{{doc.correlativo}}</td>
                                <td style="width: 40%">@{{doc.nombre}}</td>
                                <td v-show="comprobante_a_copiar=='-1'">@{{doc.total_venta}}</td>
                                <td>
                                    <span class="badge"
                                          :class="{'badge-warning':doc.estado == 'PENDIENTE',
                                   'badge-success' : doc.estado == 'ACEPTADO',
                                   'badge-dark' : doc.estado == 'ANULADO','badge-danger' : doc.estado == 'RECHAZADO'}">
                                        @{{ doc.estado }}
                                    </span>
                                </td>
                                <td style="width: 5%" class="botones-accion">
                                    <a href="javascript:void(0)">
                                        <button @click="agregarDocumento(doc.idventa,doc.idguia)" class="btn btn-info" title="Seleccionar documento"><i
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
    <!--FIN MODAL DOCUMENTO -->
    <modal-ubigeo
            v-on:agregar_ubigeo="agregarUbigeo">
    </modal-ubigeo>
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
    <modal-detalle
            :item="item"
            :show-precio="false"
            :can-edit-precio="true"
            v-on:actualizar="">
    </modal-detalle>
    @php
        $guia_data = json_decode(cache('config')['guia'], true);
    @endphp
@endsection
@section('script')
    <script>

        let app = new Vue({
            el: '.app',
            data: {
                idpresupuesto:'<?php echo isset($_GET['presupuesto'])?$_GET['presupuesto']:null ?>',
                accion: "insertar",
                mostrarProgresoGuardado: false,
                fecha: "{{date("Y-m-d")}}",
                fecha_traslado: "{{date("Y-m-d")}}",
                numeroGuia:"",

                clienteSeleccionado: {},
                nombreCliente: "",
                buscar: "",
                mostrarSpinnerCliente: false,

                productosSeleccionados: [],
                mostrarSpinnerProducto: false,
                listaDocumentos:[],
                comprobante_a_copiar:-1,
                esConGuia:0,
                guia_datos_adicionales:{
                    direccion:"",
                    ubigeo:"",
                    peso:"",
                    bultos:"",
                    tipo_doc_transportista:"6",
                    num_doc_transportista:"",
                    razon_social_transportista:"",
                    placa_vehiculo:"<?php echo $guia_data['placa']??'' ?>",
                    dni_conductor:"<?php echo $guia_data['num_doc']??'' ?>",
                    licencia_conductor:"<?php echo $guia_data['licencia']??'' ?>",
                    nombre_conductor:"<?php echo $guia_data['nombre']??'' ?>",
                    apellido_conductor:"<?php echo $guia_data['apellido']??'' ?>",
                    categoria_vehiculo:"<?php echo $guia_data['categoria_vehiculo']??'M1_L' ?>",
                    codigo_traslado:"01",
                    fecha_traslado: "{{date("Y-m-d")}}",
                    doc_relacionado:"-1",
                    num_doc_relacionado:"",
                    tipo_transporte:<?php echo json_encode(json_decode(cache('config')['guia'], true)['tipo_transporte']) ?>,
                },
                tipo_busqueda:"",
                item:{},
                index:-1,
                spinnerRuc:false,
                domicilioFiscalCliente:true
            },
            created(){
                this.obtenerCorrelativo();
                if(this.idpresupuesto !== ''){
                    this.agregarDocumento(null, null, this.idpresupuesto);
                }
            },
            methods: {
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
                obtenerCorrelativo(){
                    axios.get('/guia/obtenerCorrelativo')
                        .then(response => {
                            this.numeroGuia = response.data;
                        })
                        .catch(error => {
                            this.alerta('No hay venta registrada. Ingresa el correlativo manualmente');
                            console.log(error);
                        });
                },
                editarItem(item, index = null){
                    this.item=item;
                    this.index = index;
                    this.num_item = item.num_item;
                },
                agregarCliente(obj){
                    this.clienteSeleccionado = obj;
                    this.nombreCliente = this.clienteSeleccionado['num_documento']+' - '+this.clienteSeleccionado['nombre'];
                    if(this.domicilioFiscalCliente){
                        this.guia_datos_adicionales.direccion = this.clienteSeleccionado['direccion']
                    }
                },
                borrarCliente(){
                    this.clienteSeleccionado = {};
                },
                agregarProductoNuevo(nombre){
                    this.buscar = nombre;
                },
                agregarClienteNuevo(obj){
                    if(this.$refs['suggestCliente']){
                        this.$refs['suggestCliente'].agregarCliente(obj);
                    } else {
                        this.agregarCliente(obj)
                    }
                },
                obtenerDocumentos(comprobante){
                    this.comprobante_a_copiar=-1;
                    if(comprobante=='guia'){
                        this.comprobante_a_copiar='09';
                    }

                    axios.post('{{action('GuiaController@obtenerDocumentos')}}', {
                        'textoBuscado': this.buscar,
                        'comprobante':this.comprobante_a_copiar
                    })
                        .then(response => {
                            this.listaDocumentos = response.data;
                        })
                        .catch(error => {
                            this.alerta('Ha ocurrido un error al obtener los documentos','error');
                            console.log(error);
                        });
                },
                agregarDocumento(idventa,idguia,idpresupuesto){
                    let post_action='{{action('GuiaController@copiarDocumento')}}';
                    axios.post(post_action, {
                        'idventa': idventa,
                        'idguia': idguia,
                        'idpresupuesto': idpresupuesto
                    })
                        .then(response => {
                            let datos = response.data;
                            if(this.$refs['suggestCliente']){
                                this.$refs['suggestCliente'].agregarCliente(datos.cliente);
                            } else {
                                this.clienteSeleccionado = datos.cliente;
                                this.nombreCliente = this.clienteSeleccionado['num_documento']+' - '+this.clienteSeleccionado['nombre'];
                            }
                            this.productosSeleccionados = datos.productos;

                            if(datos.guia_datos_adicionales){
                                this.guia_datos_adicionales = datos.guia_datos_adicionales;
                                this.guia_datos_adicionales.fecha_traslado='{{date('Y-m-d')}}'
                                this.guia_datos_adicionales.licencia_conductor='';
                                this.guia_datos_adicionales.nombre_conductor='';
                                this.guia_datos_adicionales.apellido_conductor='';
                                this.guia_datos_adicionales.categoria_vehiculo='M1_L';
                            } else {
                                this.guia_datos_adicionales={
                                    direccion:'',
                                    ubigeo:'',
                                    peso:'',
                                    bultos:'',
                                    tipo_doc_transportista:'6',
                                    num_doc_transportista:'',
                                    razon_social_transportista:'',
                                    placa_vehiculo:"<?php echo $guia_data['placa']??'' ?>",
                                    dni_conductor:"<?php echo $guia_data['num_doc']??'' ?>",
                                    licencia_conductor:"<?php echo $guia_data['licencia']??'' ?>",
                                    nombre_conductor:"<?php echo $guia_data['nombre']??'' ?>",
                                    apellido_conductor:"<?php echo $guia_data['apellido']??'' ?>",
                                    categoria_vehiculo:"<?php echo $guia_data['categoria_vehiculo']??'M1_L' ?>",
                                    codigo_traslado:'01',
                                    fecha_traslado: '{{date('Y-m-d')}}',
                                    doc_relacionado:'-1',
                                    num_doc_relacionado:'',
                                    tipo_transporte:'01'
                                }

                            }
                            this.$refs['modal-documento'].hide();
                        })
                        .catch(error => {
                            this.alerta('No se ha podido copiar la venta','error');
                            console.log(error);
                        });
                },
                abrir_modal(nombre){
                    switch (nombre){
                        case 'venta':
                        case 'guia':
                            this.$refs['modal-documento'].show();
                            this.obtenerDocumentos(nombre);
                            break;
                    }
                    this.tipo_busqueda=nombre;
                },
                delay(){
                    if (this.timer) {
                        clearTimeout(this.timer);
                        this.timer = null;
                    }
                    this.timer = setTimeout(() => {
                        switch (this.tipo_busqueda){
                            case 'venta':
                            case 'guia':
                                this.obtenerDocumentos(this.tipo_busqueda);
                                break;
                        }

                    }, 500);
                },
                agregarProducto(obj){
                    let productos = this.productosSeleccionados.push(Object.assign({}, obj));
                    let i = productos - 1;
                    this.$set(this.productosSeleccionados[i], 'num_item', i);
                    this.$set(this.productosSeleccionados[i], 'cantidad', 1);
                },
                borrarItemVenta(index){
                    this.productosSeleccionados.splice(index, 1);
                },
                resetModal(){
                    this.buscar = '';
                },
                procesarGuia(){

                    if (this.validarVenta()) {
                        return;
                    }
                    this.$swal({
                        heightAuto: false,
                        position: 'top',
                        icon: 'question',
                        text: 'Se registrará una guía. Confirma esta acción.',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        cancelButtonText: 'Cancelar',
                        confirmButtonText: 'Sí, registrar'
                    }).then((result) => {
                        if (result.isConfirmed){
                            this.mostrarProgresoGuardado = true;
                            axios.post('{{action('GuiaController@store')}}', {
                                'idcliente': this.clienteSeleccionado['idcliente'],
                                'num_guia': this.numeroGuia,
                                'fecha': this.fecha,
                                'guia_datos_adicionales': JSON.stringify(this.guia_datos_adicionales),
                                'items': JSON.stringify(this.productosSeleccionados)
                            })
                                .then(response => {
                                    if(isNaN(response.data.idguia) || response.data.idguia == -1){
                                        this.alerta('Ha ocurrido un error al procesar la : '+response.data.respuesta,'error');
                                        this.mostrarProgresoGuardado = false;
                                    } else{
                                        this.$swal({
                                            position: 'top',
                                            icon: 'success',
                                            title: 'Se ha guardado la guía',
                                            text:response.data.respuesta,
                                            timer: 6000,
                                            confirmButtonColor: '#007bff',
                                        }).then(()=>{
                                            location.href = '/guia/emision/' + response.data.idguia;
                                            this.mostrarProgresoGuardado = false;
                                        });
                                    }
                                })
                                .catch(error => {
                                    this.alerta('Ha ocurrido un error con la guía','error');
                                    console.log(error);
                                    this.mostrarProgresoGuardado = false;
                                });
                        }
                    })
                },
                validarVenta(){
                    let errorVenta = 0;
                    let errorDatosVenta = [];
                    let errorString = '';
                    if (this.fecha.length == 0) errorDatosVenta.push('*La fecha no puede estar vacia');
                    if (this.guia_datos_adicionales.direccion.length == 0) errorDatosVenta.push('*El campo direccion de la guia no puede estar vacío');
                    if (this.guia_datos_adicionales.peso.length == 0) errorDatosVenta.push('*El campo peso no puede estar vacío');
                    if (this.guia_datos_adicionales.bultos.length == 0) errorDatosVenta.push('*El campo N° de bultos no puede estar vacío');
                    if(this.guia_datos_adicionales.categoria_vehiculo != 'M1_L'){
                        if(this.guia_datos_adicionales.tipo_transporte=='01'){
                            if (this.guia_datos_adicionales.num_doc_transportista.length == 0) errorDatosVenta.push('*El campo número de documento de transportista no puede estar vacío');
                            if (!(this.guia_datos_adicionales.num_doc_transportista.length === 11) && this.guia_datos_adicionales.tipo_doc_transportista == '6') errorDatosVenta.push('*El campo número documento de transportista debe contener 11 dígitos');
                            if (this.guia_datos_adicionales.razon_social_transportista.length == 0) errorDatosVenta.push('*El campo razón social de transportista no puede estar vacío');
                        } else{
                            if (this.guia_datos_adicionales.placa_vehiculo.length == 0) errorDatosVenta.push('*El campo placa vehículo no puede estar vacío');
                            if (this.guia_datos_adicionales.dni_conductor.length == 0) errorDatosVenta.push('*El campo dni de conductor no puede estar vacío');
                            if (this.guia_datos_adicionales.licencia_conductor.length == 0) errorDatosVenta.push('*El campo licencia de conductor no puede estar vacío');
                            if (this.guia_datos_adicionales.nombre_conductor.length == 0) errorDatosVenta.push('*El campo nombres de conductor no puede estar vacío');
                            if (this.guia_datos_adicionales.apellido_conductor.length == 0) errorDatosVenta.push('*El campo apellidos de conductor no puede estar vacío');
                            if (this.guia_datos_adicionales.dni_conductor.length != 8) errorDatosVenta.push('*El campo dni de conductor debe contener 8 dígitos');
                        }
                    }
                    if(this.guia_datos_adicionales.doc_relacionado!='-1' && this.guia_datos_adicionales.num_doc_relacionado.length == 0)errorDatosVenta.push('*El campo número de documento relacionado no puede estar vacío');

                    if (/^\./.test(this.guia_datos_adicionales.bultos)) this.guia_datos_adicionales.bultos='0'+this.guia_datos_adicionales.bultos;
                    if (/^\./.test(this.guia_datos_adicionales.peso)) this.guia_datos_adicionales.peso='0'+this.guia_datos_adicionales.peso;

                    if (Object.keys(this.clienteSeleccionado).length == 0) errorDatosVenta.push('*Debes ingresar un cliente');

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


                    if (errorDatosVenta.length) {
                        errorVenta = 1;
                        for (let error of errorDatosVenta) {
                            errorString += error + '\n';
                        }
                        this.alerta(errorString);
                    }

                    return errorVenta;
                },
                agregarUbigeo(ubigeo){
                    this.guia_datos_adicionales.ubigeo=ubigeo;
                },
                limpiar(){
                    this.clienteSeleccionado = {};
                    this.nombreCliente = '';
                    this.codigoCliente='';
                    this.numDocCliente='';
                    this.productosSeleccionados = [];

                    this.fecha = '{{date('Y-m-d')}}';
                    this.fecha_vencimiento = '{{date('Y-m-d')}}';

                    this.guia_datos_adicionales={
                        direccion:'',
                        ubigeo:'',
                        peso:'',
                        bultos:'',
                        tipo_doc_transportista:'6',
                        num_doc_transportista:'',
                        razon_social_transportista:'',
                        placa_vehiculo:'',
                        dni_conductor:'',
                        licencia_conductor:'',
                        nombre_conductor:'',
                        apellido_conductor:'',
                        categoria_vehiculo:'M1_L',
                        codigo_traslado:'01',
                        fecha_traslado: '{{date('Y-m-d')}}',
                        doc_relacionado:'-1',
                        num_doc_relacionado:'',
                        tipo_transporte:'01'
                    };
                    this.domicilioFiscalCliente = true;
                    this.tipo_busqueda='';
                    this.obtenerCorrelativo();
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
                alerta(texto, icon){
                    this.$swal({
                        position: 'top',
                        icon: icon || 'warning',
                        title: texto,
                        timer: 6000,
                        toast:true,
                        confirmButtonColor: '#007bff',
                    });
                }
            }

        });
    </script>
@endsection