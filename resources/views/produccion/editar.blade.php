@extends('layouts.main')
@section('titulo', 'Orden de producción')
@section('contenido')
    @php
        $agent = new \Jenssegers\Agent\Agent();
        $tipo_cambio_compra = cache('opciones')['tipo_cambio_compra'];
        $unidad_medida = \sysfact\Http\Controllers\Helpers\DataUnidadMedida::getUnidadMedida();
        $can_gestionar = false;
    @endphp
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-sm-12">
                <h3 class="titulo-admin-1">Orden de producción N° {{$produccion->correlativo}}</h3>
                <b-button href="{{url('produccion/pendientes')}}" class="mr-2"  variant="primary"><i class="fas fa-list"></i> Ver ordenes</b-button>
                <b-button href="{{action('ProduccionController@nueva_produccion')}}" class="mr-2"  variant="primary"><i class="fas fa-plus"></i> Nueva orden</b-button>
                @if($produccion->estado == 'PENDIENTE')
                <b-button href="{{url('produccion/marcar-completado/'.$produccion->idproduccion)}}" class="mr-2 mt-sm-2 mt-lg-0"  variant="success"><i class="fas fa-check"></i> Marcar como completado</b-button>
                @else
                    <b-button href="{{url('produccion/marcar-pendiente/'.$produccion->idproduccion)}}" class="mr-2 mt-sm-2 mt-lg-0"  variant="warning"><i class="fas fa-check"></i> Marcar como pendiente</b-button>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 mt-4 mb-3">
                <div class="card">
                    <div class="card-header">
                        Detalle
                    </div>
                    <div class="card-body">
                        <div class="row" v-if="editar">
                            <div class="col-lg-3 form-group">
                                <label>N° de orden</label>
                                <input disabled type="text" v-model="numeroCotizacion" class="form-control">
                            </div>
                            <div class="col-lg-3 form-group">
                                <label>Fecha</label>
                                <input disabled type="date" v-model="fecha" name="fecha" class="form-control">
                            </div>
                            <div class="col-lg-3 form-group">
                                <label>Fecha de entrega</label>
                                <input type="date" v-model="fecha_entrega" name="fecha_entrega" class="form-control">
                            </div>
                            <div class="col-lg-3 form-group">
                                <label>Prioridad</label>
                                <select v-model="prioridad" name="prioridad" class="custom-select">
                                    <option value="0">Normal</option>
                                    <option value="1">Alta</option>
                                </select>
                            </div>
                        </div>
                        <div class="row" v-if="editar" >
                            <div class="col-lg-3">
                                <label for="">Cliente</label>
                                <b-button v-b-modal.modal-cliente
                                          class="mb-4 mr-4 d-block" variant="primary"><i class="fas fa-search-plus"
                                                                                 v-show="!mostrarSpinnerCliente"></i>
                                    <b-spinner v-show="mostrarSpinnerCliente" small label="Loading..."></b-spinner>
                                    Seleccionar cliente
                                </b-button>
                            </div>
                            <div class="col-lg-3">
                                <label>Orden de compra</label>
                                <input type="text" v-model="num_oc" class="form-control mb-2"
                                       placeholder="N° orden de compra">
                            </div>
                        </div>
                        <div class="form-group" v-if="editar">
                            <div class="row">
                                <div class="col-lg-2">
                                    <label>Código</label>
                                    <input type="text" v-model="codigoCliente" class="form-control mb-2"
                                           placeholder="Código" disabled readonly>
                                </div>
                                <div class="col-lg-6">
                                    <label>Nombre/Razón social</label>
                                    <input type="text" v-model="nombreCliente" class="form-control mb-2"
                                           placeholder="Cliente" disabled readonly>
                                </div>
                                <div class="col-lg-3">
                                    <label>Dni/Ruc</label>
                                    <input maxlength="11" type="text" v-model="numDocCliente" class="form-control mb-2"
                                           placeholder="Dni" disabled readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row" v-if="!editar">
                            <div class="col-lg-6">
                                <strong>Fecha:</strong> {{date("d-m-Y",strtotime($produccion->fecha_emision))}} <hr>
                                <strong>Fecha de entrega:</strong> {{date("d-m-Y",strtotime($produccion->fecha_entrega))}}<hr>
                                <strong>Orden de compra:</strong> {{$produccion->num_oc}} <hr>
                            </div>
                            <div class="col-lg-6">
                                Prioridad:
                                @if($produccion->prioridad==0)
                                    <span class="badge badge-success">NORMAL</span><hr>
                                @else
                                    <span class="badge badge-danger">ALTA</span><hr>
                                @endif
                                Editado por: {{$produccion->editado_por}} <hr>
                                Fabricado por: {{$produccion->fabricado_por}} <hr>
                            </div>
                            <div class="col-lg-12">
                                <strong>Cliente:</strong> {{$produccion->cliente->persona['nombre']}} <hr>
                            </div>
                        </div>
                        <div class="row mt-5" v-if="editar">
                            <div class="col-lg-7 buscar_producto">
                                <autocomplete ref="suggest" v-on:agregar_producto="agregarProducto"></autocomplete>
                            </div>
                            <div class="col-lg-3">
                                <b-button v-b-modal.modal-nuevo-producto
                                          variant="primary"><i class="fas fa-plus" v-show="!mostrarSpinnerProducto"></i>
                                    <b-spinner v-show="mostrarSpinnerProducto" small label="Loading..."></b-spinner>
                                    Nuevo producto
                                </b-button>
                            </div>
                        </div>
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col" style="width: 10px"></th>
                                    <th scope="col" style="width: 250px">Código</th>
                                    <th scope="col" style="width: 250px">Producto</th>
                                    <th scope="col" style="width: 350px">Caracteristicas</th>
                                    <th scope="col" style="width: 100px">Cantidad</th>
                                    <th scope="col" style="width: 50px"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-if="editar" v-for="(producto,index) in productosSeleccionados" :key="producto.index">
                                    <td></td>
                                    <td><input class="form-control" type="text" v-model="producto.codigo_fabricacion" maxlength="50"></td>
                                    <td>@{{producto.nombre}}</td>
                                    <td><textarea class="form-control" rows="1" v-model="producto.presentacion"></textarea></td>
                                    <td><input class="form-control" type="text" v-model="producto.cantidad"></td>
                                    <td class="">
                                        <a @click="borrarItemVenta(index)" href="javascript:void(0)">
                                            <button class="btn btn-danger" title="Borrar item"><i class="fas fa-trash"></i>
                                            </button>
                                        </a>
                                    </td>
                                </tr>
                                <tr v-if="!editar" v-for="(producto,index) in productosSeleccionados" :key="producto.index">
                                    <td></td>
                                    <td>@{{producto.codigo_fabricacion}}</td>
                                    <td>@{{producto.nombre}}</td>
                                    <td style="white-space: break-spaces; width: 350px">@{{ producto.presentacion}}</td>
                                    <td>@{{ producto.cantidad }}</td>
                                    <td>
                                    </td>
                                </tr>
                                <tr class="text-center" v-show="productosSeleccionados.length == 0"><td colspan="11">No hay datos para mostrar</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="dropdown-divider"></div>
                        <div class="col-lg-6 mt-3">
                            <div class="form-group" v-if="editar">
                                <label for="observaciones">Observaciones:</label>
                                <textarea  v-model="observaciones" class="form-control" name="" id="" cols="15" rows="2"></textarea>
                            </div>
                            <div class="card" v-if="!editar" style="box-shadow: none">
                                <div class="card-body">
                                    Observaciones: <br>
                                    @{{ observaciones }}
                                </div>
                            </div>
                        </div>

                        <div class="row" v-if="!editar">
                            <div class="col-lg-12 mt-5 mb-3">
                                Archivos adjuntos:
                            </div>
                            <div class="col-lg-3" v-for="image of adjuntos" v-show="image['esEliminado']==0">
                                <figure>
                                    <img v-if="image['type_file'] == 'data:application/pdf'" class="previewImg" src="/images/pdf.png">
                                    <img v-else class="previewImg" :src="image['preview']">
                                    <a target="_blank" class="btn btn-success btn-borrar-fichero" target="_blank" :href="image['url']">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </figure>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 mb-3" v-if="editar">
                <div class="card">
                    <div class="card-header">
                        Información adicional
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12 form-group mb-4">
                                <label>Archivos adjuntos</label>
                                <p>
                                    - Se adminten imagenes JPG, PNG y archivos PDF <br>
                                    - Tamaño máximo de archivo: 2MB
                                </p>
                                <div class="card" style="box-shadow: none">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-12 mb-3">
                                                <b-button @click="modalImagen()"
                                                          v-b-modal.modal-producto
                                                          variant="primary"><i class="fas fa-plus" v-show="!mostrarSpinnerProducto"></i>
                                                    <b-spinner v-show="mostrarSpinnerProducto" small label="Loading..."></b-spinner>
                                                    Agregar
                                                </b-button>
                                            </div>
                                            <div class="col-lg-3" v-for="(image,index) in adjuntos" v-show="image['esEliminado']==0">
                                                <figure>
                                                    <img v-if="image['type_file'] == 'data:application/pdf'" class="previewImg" src="/images/pdf.png">
                                                    <img v-else class="previewImg" :src="image['preview']">
                                                    <button @click="borrarFichero(index)" class="btn btn-danger btn-borrar-fichero">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </figure>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 form-group mb-4">
                                <label>Editado por</label>
                                <input class="form-control" type="text" v-model="editado_por">
                            </div>
                            <div class="col-lg-4 form-group mb-4">
                                <label>Fabricado por</label>
                                <input class="form-control" type="text" v-model="fabricado_por">
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
                        <div v-show="mostrarProgresoArchivos" class="text-center mb-5">
                            <b-spinner label="Spinning"></b-spinner> <p>Cargando archivos adjuntos... no cierres la página por favor.</p>
                        </div>
                        <b-button v-show="editar" :disabled="mostrarProgresoGuardado || productosSeleccionados.length==0" class="mb-2" :disabled="productosSeleccionados.length==0" @click="procesarProduccion"
                                  variant="success">
                            <i v-show="!mostrarProgresoGuardado" class="fas fa-save"></i>
                            <b-spinner v-show="mostrarProgresoGuardado" small label="Loading..." ></b-spinner> Guardar cambios
                        </b-button>
                        <b-button v-if="!editar" @click="editar=!editar" class="mb-2"  variant="success">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z"/>
                            </svg>
                            Editar
                        </b-button>
                        <b-button v-if="editar"
                                  @click="cancelar_edicion" class="mb-2"
                                  variant="info">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-square" viewBox="0 0 16 16">
                                <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                                <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                            </svg>
                            Cancelar edición
                        </b-button>
                        <b-button :disabled="editar" class="mb-2" href="{{url('facturacion?produccion').'='.$produccion->idproduccion}}" variant="warning">
                            <i class="fas fa-file-invoice-dollar"></i> Crear factura
                        </b-button>
                        <b-button :disabled="editar" class="mb-2" target="_blank"
                                  href="{{url('produccion/imprimir').'/'.$produccion['idproduccion']}}"
                                  variant="warning">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16">
                                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
                            </svg>
                            Imprimir
                        </b-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--INICIO MODAL IMAGEN-->
    <b-modal id="modal-imagen" ref="modal-imagen" size="md" @@hidden="resetModalImagen">
    <template slot="modal-title">
        Agregar imagen
    </template>
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <form class="form-upload" method="POST" action="{{url('productos/agregar-imagen')}}"
                      enctype="multipart/form-data">
                    <div class="col-lg-12 mb-3">
                        <label for="precio">Imagen:</label>
                        <input @change="cargarFichero" type="file" id="input_file_data" accept="application/pdf,image/x-png,image/gif,image/jpeg">
                    </div>
                    <div class="col-lg-12">
                        <div class="image-preview" v-if="dataFichero.length > 0">
                            <img v-if="dataFicheroType == 'data:application/pdf'" class="preview" src="/images/pdf.png">
                            <img v-else class="preview" :src="dataFichero">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <template #modal-footer="{ ok, cancel}">
        <b-button variant="secondary" @click="cancel()">
            Cancel
        </b-button>
        <b-button :disabled="!input_file_data || mostrarProgresoGuardado"  variant="primary" @click="agregarImagen">
            <b-spinner v-show="mostrarProgresoGuardado" small label="Loading..." ></b-spinner>
            <span v-show="!mostrarProgresoGuardado">OK</span>
        </b-button>
    </template>
    </b-modal>
    <!--FIN MODAL IMAGEN -->
    <modal-cliente
            v-bind:url_obtener_clientes="'{{action('ProduccionController@obtenerClientes')}}'"
            v-on:agregar_cliente="agregarCliente">
    </modal-cliente>
    <agregar-producto
            :tipo_cambio="{{$tipo_cambio_compra}}"
            :unidad_medida="{{json_encode($unidad_medida)}}"
            :can_gestionar="{{json_encode($can_gestionar)}}"
            :tipo_de_producto="1"
            :origen="'produccion'"
            v-on:agregar="agregarProductoNuevo">
    </agregar-producto>
    <agregar-cliente
            v-bind:url_guardar="'{{action('ClienteController@store')}}'"
            v-on:agregar="agregarClienteReciente">
    </agregar-cliente>
@endsection
@section('script')
    <script>

        let app = new Vue({
            el: '.app',
            data: {
                accion: 'insertar',
                editar:false,
                idproduccion:<?php echo $produccion['idproduccion'] ?>,
                mostrarProgresoGuardado: false,

                fecha: '{{date("Y-m-d",strtotime($produccion->fecha_emision))}}',
                fecha_entrega: '{{date("Y-m-d",strtotime($produccion->fecha_entrega))}}',
                observaciones:"<?php echo $produccion['observacion'] ?>",
                editado_por:"<?php echo $produccion['editado_por'] ?>",
                fabricado_por:"<?php echo $produccion['fabricado_por'] ?>",
                prioridad:"<?php echo $produccion['prioridad'] ?>",

                listaClientes: [],
                clienteSeleccionado: <?php echo $produccion['cliente'] ?>,
                nombreCliente: "<?php echo $produccion['cliente']['persona']['nombre'] ?>",
                codigoCliente: '<?php echo $produccion['cliente']['cod_cliente'] ?>',
                numDocCliente: '<?php echo $produccion['cliente']['num_documento'] ?>',
                buscar: '',
                mostrarSpinnerCliente: false,

                listaProductos: [],
                productosSeleccionados: <?php echo $productos ?>,
                mostrarSpinnerProducto: false,
                numeroCotizacion:"<?php echo $produccion['correlativo'] ?>",
                input_file_data:'',
                dataFichero: "",
                dataFicheroType:"",
                adjuntos:<?php echo $produccion['adjuntos'] ?>,
                mostrarProgresoArchivos: false,
                num_oc:"<?php echo $produccion->num_oc ?>"
            },
            created(){
                let today = new Date().toISOString().split('T')[0];
                document.getElementsByName("fecha_entrega")[0].setAttribute('min', today);
            },
            methods: {
                modalImagen(){
                    this.$refs['modal-imagen'].show();
                },
                borrarFichero(index){
                    this.adjuntos[index]['esEliminado']=1;
                },
                cargarFichero(event){
                    let input = event.target;
                    if (input.files && input.files[0]) {
                        // create a new FileReader to read this image and convert to base64 format
                        let reader = new FileReader();
                        // Define a callback function to run, when FileReader finishes its job
                        reader.onload = (e) => {
                            // Note: arrow function used here, so that "this.dataFichero" refers to the dataFichero of Vue component
                            // Read image as base64 and set to dataFichero
                            this.dataFichero = e.target.result;
                            this.dataFicheroType = (this.dataFichero).split(";")[0];
                            let sizeFile = ((input.files[0].size/1024)/1024).toFixed(4);
                            if(sizeFile > 2.5){
                                this.alerta('Archivo demasiado grande. Tamaño máximo permitido 2MB');
                                this.input_file_data = false;
                                this.dataFichero = "";
                            }
                            if(!(this.dataFicheroType == 'data:application/pdf' || this.dataFicheroType == 'data:image/jpg' || this.dataFicheroType == 'data:image/png' || this.dataFicheroType == 'data:image/jpeg')){
                                this.alerta('Tipo de archivo no admitido: Solo es válido ficheros JPG, PNG y PDF');
                                this.input_file_data = false;
                                this.dataFichero = "";
                            }
                        };
                        // Start the reader job - read file as a data url (base64 format)
                        reader.readAsDataURL(input.files[0]);
                        this.input_file_data = input.files[0];
                    }

                },
                agregarImagen(){
                    this.adjuntos.push({data_file:this.input_file_data, preview:this.dataFichero,esSubido:1,esEliminado:0, type_file:this.dataFicheroType});
                    this.$refs['modal-imagen'].hide();
                },
                cancelar_edicion(){
                    location.reload();
                },
                agregarCliente(obj){
                    this.clienteSeleccionado = obj;
                    this.codigoCliente = this.clienteSeleccionado['cod_cliente'];
                    this.nombreCliente = this.clienteSeleccionado['nombre'];
                    this.numDocCliente = this.clienteSeleccionado['num_documento'];
                },
                agregarProductoNuevo(nombre){
                    if(this.$refs['suggest']){
                        this.$refs['suggest'].query = nombre;
                        this.$refs['suggest'].autoComplete();
                    }
                },
                agregarClienteReciente(nombre){
                    this.buscar = nombre;
                    this.obtenerClientes();
                },
                agregarProducto(obj){
                    let productos = this.productosSeleccionados.push(Object.assign({}, obj));
                    //crear propiedades precio y cantidad en objeto productosSeleccionados:{} para usarlos
                    //más tarde al procesar la venta.
                    let i = productos - 1;
                    this.$set(this.productosSeleccionados[i], 'num_item', i);
                    this.$set(this.productosSeleccionados[i], 'cantidad', 1);
                    this.$set(this.productosSeleccionados[i], 'codigo_fabricacion', '');
                },
                borrarItemVenta(index){
                    this.productosSeleccionados.splice(index, 1);
                },
                resetModalImagen(){
                    this.dataFichero = "";
                    this.dataFicheroType = "";
                },
                procesarProduccion(){

                    if (this.validar()) {
                        return;
                    }
                    this.mostrarProgresoGuardado = true;

                    axios.post('{{action('ProduccionController@update')}}', {
                        'idproduccion': this.idproduccion,
                        'idcliente': this.clienteSeleccionado['idcliente'],
                        'fecha_entrega': this.fecha_entrega,
                        'observaciones':this.observaciones,
                        'editado_por':this.editado_por,
                        'fabricado_por':this.fabricado_por,
                        'prioridad':this.prioridad,
                        'num_oc':this.num_oc,
                        'items': JSON.stringify(this.productosSeleccionados)
                    })
                        .then(response => {
                            if(isNaN(response.data)){
                                this.alerta(response.data);
                                this.mostrarProgresoGuardado = false;
                            } else{
                                this.mostrarProgresoGuardado = false;
                                this.mostrarProgresoArchivos = true;
                                let data = new FormData();
                                let i=0;
                                for(let adjunto of this.adjuntos){
                                    if(adjunto['esEliminado']==0){
                                        if(adjunto['esSubido']==1){
                                            data.append('image_' + i, adjunto['data_file']);
                                        } else{
                                            data.append('image_' + i, adjunto['url']);
                                        }
                                    } else{
                                        i--;
                                    }
                                    i++;
                                }
                                data.append('idproduccion',this.idproduccion);
                                let settings = {headers: {'content-type': 'multipart/form-data'}};
                                axios.post('{{url('/produccion/agregar-imagen')}}', data, settings)
                                    .then(r => {
                                        if(r.data != 1){
                                            this.$swal({
                                                position: 'top',
                                                icon: 'warning',
                                                title: r.data,
                                                timer: 6000,
                                                toast:true,
                                                confirmButtonColor: '#007bff',
                                            }).then(()=>{
                                                window.location.reload();
                                            })
                                        } else {
                                            window.location.reload();
                                        }

                                    })
                                    .catch(e => {
                                        this.mostrarProgresoGuardado=false;
                                        this.mostrarProgresoArchivos = false;
                                        if (e.message && e.message.includes('413')) {
                                            this.alerta('Uno de los archivos es demasiado grande. Tamaño máximo permitido 2MB');
                                        }
                                        this.alerta(e.response.data.mensaje,'error');
                                        console.log(e);
                                    });

                            }
                        })
                        .catch(error => {
                            this.alerta('Ha ocurrido un error al procesar la orden', 'error');
                            console.log(error);
                            this.mostrarProgresoGuardado = false;
                        });
                },
                validar(){
                    let errorVenta = 0;
                    let errorDatosVenta = [];
                    let errorString = '';

                    if (Object.keys(this.clienteSeleccionado).length == 0) errorDatosVenta.push('*Debes ingresar un cliente');

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
                    this.nombreCliente = '';
                    this.codigoCliente='';
                    this.numDocCliente= '';
                    this.productosSeleccionados = [];
                    this.prioridad='0';
                    this.fecha= '{{date('Y-m-d')}}';
                    this.fecha_entrega= '{{date('Y-m-d')}}';
                    this.observaciones="";
                    this.editado_por="";
                    this.fabricado_por="";
                    this.num_oc="";
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