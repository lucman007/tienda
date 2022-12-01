@extends('layouts.main')
@section('titulo', 'Corregir guía')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-sm-12">
                <h3 class="titulo-admin-1">
                    <a href="{{url()->previous()}}"><i class="fas fa-arrow-circle-left"></i></a>
                    Corregir guía {{$guia->correlativo}}
                </h3>
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
                                <select v-model="guia_datos_adicionales.doc_relacionado" name="cargo" class="custom-select">
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
                                <input type="text" v-model="guia_datos_adicionales.num_doc_relacionado" placeholder="Número documento relacionado"
                                       class="form-control">
                            </div>
                            <div class="col-lg-6 form-group">
                                <label>Dirección de llegada</label>
                                <input maxlength="100" type="text" v-model="guia_datos_adicionales.direccion" name="direccion"
                                       class="form-control" placeholder="*Máximo 100 caracteres">
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
                                <select v-model="guia_datos_adicionales.tipo_transporte" name="cargo" class="custom-select" id="tipo_transporte">
                                    <option value="01">Público</option>
                                    <option value="02">Privado</option>
                                </select>
                            </div>
                            <div class="col-lg-10" v-show="guia_datos_adicionales.tipo_transporte == '01'">
                                <div class="row">
                                    <div class="col-lg-3 form-group">
                                        <label>Tipo documento transportista</label>
                                        <select v-model="guia_datos_adicionales.tipo_doc_transportista" class="custom-select" id="tipo_transporte">
                                            <option value="6">RUC</option>
                                            <option value="1">DNI</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label>Num. doc. tranportista</label>
                                        <b-input-group>
                                            <input @keyup.enter="consultaRucDni(guia_datos_adicionales.tipo_doc_transportista,guia_datos_adicionales.num_doc_transportista)" :maxlength="guia_datos_adicionales.tipo_doc_transportista==1? 8 : 11" type="text" v-model="guia_datos_adicionales.num_doc_transportista"
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
                                        <label>Razón social tranportista</label>
                                        <input type="text" v-model="guia_datos_adicionales.razon_social_transportista"
                                               class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-10" v-show="guia_datos_adicionales.tipo_transporte == '02'">
                                <div class="row">
                                    <div class="col-lg-2 form-group">
                                        <label>Placa del vehículo</label>
                                        <input type="text" v-model="guia_datos_adicionales.placa_vehiculo"
                                               class="form-control">
                                    </div>
                                    <div class="col-lg-2 form-group">
                                        <label>Licencia de conducir</label>
                                        <input type="text" v-model="guia_datos_adicionales.licencia_conductor"
                                               class="form-control">
                                    </div>
                                    <div class="col-lg-2 form-group">
                                        <label>DNI del conductor</label>
                                        <b-input-group>
                                            <input @keyup.enter="consultaRucDni(1,guia_datos_adicionales.dni_conductor)" maxlength="8" type="text" v-model="guia_datos_adicionales.dni_conductor"
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
                            <b-button class="mb-2" @click="procesarGuia"
                                      variant="success">
                                <i v-show="!mostrarProgresoGuardado" class="fas fa-paper-plane"></i>
                                <b-spinner v-show="mostrarProgresoGuardado" small label="Loading..." ></b-spinner>Corregir y enviar
                            </b-button>
                            <a href="{{url()->previous()}}"><b-button class="mb-2" variant="danger"><i class="fas fa-ban"></i> Cancelar
                                </b-button></a>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <modal-ubigeo
            v-on:agregar_ubigeo="agregarUbigeo">
    </modal-ubigeo>
    <modal-cliente
            v-on:agregar_cliente="agregarCliente">
    </modal-cliente>
    <agregar-cliente
            v-on:agregar="agregarClienteNuevo">
    </agregar-cliente>
@endsection
@section('script')
    <script>

        let app = new Vue({
            el: '.app',
            data: {
                idguia: {{$guia->idguia}},
                mostrarProgresoGuardado: false,
                fecha: "{{date("Y-m-d", strtotime($guia->fecha_emision))}}",
                numeroGuia:"{{$guia->correlativo}}",
                guia_datos_adicionales:{
                    direccion:"{{$guia->direccion_llegada}}",
                    ubigeo:"{{$guia->ubigeo_direccion_llegada}}",
                    peso:"{{$guia->peso_bruto}}",
                    bultos:"{{$guia->cantidad_bultos}}",
                    tipo_doc_transportista:"{{$guia->tipo_doc_transportista}}",
                    num_doc_transportista:"{{$guia->num_doc_transportista}}",
                    razon_social_transportista:"{{$guia->razon_social_transportista}}",
                    placa_vehiculo:"{{$guia->placa_vehiculo}}",
                    dni_conductor:"{{$guia->dni_conductor}}",
                    licencia_conductor:"{{$guia->licencia_conductor}}",
                    nombre_conductor:"{{$guia->nombre_conductor}}",
                    apellido_conductor:"{{$guia->apellido_conductor}}",
                    codigo_traslado:"{{$guia->motivo_traslado}}",
                    fecha_traslado: "{{date("Y-m-d",strtotime($guia->fecha_traslado))}}",
                    doc_relacionado:"{{$guia->doc_relacionado}}",
                    num_doc_relacionado:"{{$guia->num_doc_relacionado}}",
                    tipo_transporte:"{{$guia->tipo_transporte}}"
                },
                clienteSeleccionado: <?php echo $guia['cliente'] ?>,
                nombreCliente: "{{$guia->cliente->num_documento.' - '.$guia->cliente->persona->nombre}}",
                mostrarSpinnerCliente: false,
                spinnerRuc:false
            },
            mounted(){
                let obj = <?php echo json_encode($guia->cliente)?>;
                if(this.$refs['suggestCliente']){
                    this.$refs['suggestCliente'].agregarCliente(obj);
                } else {
                    this.agregarCliente(obj)
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
                agregarCliente(obj){
                    this.clienteSeleccionado = obj;
                    this.nombreCliente = this.clienteSeleccionado['num_documento']+' - '+this.clienteSeleccionado['nombre'];
                },
                borrarCliente(){
                    this.clienteSeleccionado = {};
                },
                agregarClienteNuevo(obj){
                    if(this.$refs['suggestCliente']){
                        this.$refs['suggestCliente'].agregarCliente(obj);
                    } else {
                        this.agregarCliente(obj)
                    }
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
                procesarGuia(){
                    if (this.validarVenta()) {
                        return;
                    }
                    this.mostrarProgresoGuardado = true;
                    axios.post('{{action('GuiaController@update')}}', {
                        'idcliente': this.clienteSeleccionado['idcliente'],
                        'fecha': this.fecha,
                        'idguia':this.idguia,
                        'guia_datos_adicionales': JSON.stringify(this.guia_datos_adicionales)
                    })
                        .then(response => {
                            if(isNaN(response.data.idguia)){
                                this.alerta('Ha ocurrido un error al procesar la guía','error');
                                this.mostrarProgresoGuardado = false;
                            } else{
                                location.href = '{{url()->previous()}}';
                                this.mostrarProgreso = false;
                            }
                        })
                        .catch(error => {
                            this.alerta('Ha ocurrido un error con la guía','error');
                            console.log(error);
                            this.mostrarProgresoGuardado = false;
                        });
                },
                validarVenta(){
                    let errorVenta = 0;
                    let errorDatosVenta = [];
                    let errorString = '';
                    if (this.fecha.length == 0) errorDatosVenta.push('*La fecha no puede estar vacia');
                    if (this.guia_datos_adicionales.direccion.length == 0) errorDatosVenta.push('*El campo direccion de la guia no puede estar vacío');
                    if (this.guia_datos_adicionales.ubigeo.length!=6) errorDatosVenta.push('*El campo ubigeo debe contener un código de 6 dígitos');
                    if (isNaN(this.guia_datos_adicionales.ubigeo)) errorDatosVenta.push('*El campo ubigeo debe ser un número');
                    if (this.guia_datos_adicionales.peso.length == 0) errorDatosVenta.push('*El campo peso no puede estar vacío');
                    if (isNaN(this.guia_datos_adicionales.peso)) errorDatosVenta.push('*El campo peso debe ser un número');
                    if (this.guia_datos_adicionales.bultos.length == 0) errorDatosVenta.push('*El campo N° de bultos no puede estar vacío');
                    if (isNaN(this.guia_datos_adicionales.bultos)) errorDatosVenta.push('*El campo N° de bultos debe ser un número');
                    if(this.guia_datos_adicionales.tipo_transporte=='01'){
                        if (this.guia_datos_adicionales.num_doc_transportista.length == 0) errorDatosVenta.push('*El campo número de documento de transportista no puede estar vacío');
                        if (isNaN(this.guia_datos_adicionales.num_doc_transportista)) errorDatosVenta.push('*El campo número de documento de transportista debe ser un número sin letras ni espacios');
                        if (!(this.guia_datos_adicionales.num_doc_transportista.length === 11) && this.guia_datos_adicionales.tipo_doc_transportista == '6') errorDatosVenta.push('*El campo número documento de transportista debe contener 11 dígitos');
                        if (!(this.guia_datos_adicionales.num_doc_transportista.length == 8) && this.guia_datos_adicionales.tipo_doc_transportista == '1') errorDatosVenta.push('*El campo número documento de transportista debe contener 8 dígitos');
                        if (this.guia_datos_adicionales.razon_social_transportista.length == 0) errorDatosVenta.push('*El campo razón social de transportista no puede estar vacío');
                    } else{
                        if (this.guia_datos_adicionales.placa_vehiculo.length == 0) errorDatosVenta.push('*El campo placa vehículo no puede estar vacío');
                        if (this.guia_datos_adicionales.dni_conductor.length == 0) errorDatosVenta.push('*El campo dni de conductor no puede estar vacío');
                        if (this.guia_datos_adicionales.licencia_conductor.length == 0) errorDatosVenta.push('*El campo licencia de conductor no puede estar vacío');
                        if (this.guia_datos_adicionales.nombre_conductor.length == 0) errorDatosVenta.push('*El campo nombres de conductor no puede estar vacío');
                        if (this.guia_datos_adicionales.apellido_conductor.length == 0) errorDatosVenta.push('*El campo apellidos de conductor no puede estar vacío');
                        if (isNaN(this.guia_datos_adicionales.dni_conductor)) errorDatosVenta.push('*El campo dni de conductor debe ser un número sin letras ni espacios');
                        if (this.guia_datos_adicionales.dni_conductor.length != 8) errorDatosVenta.push('*El campo dni de conductor debe contener 8 dígitos');
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