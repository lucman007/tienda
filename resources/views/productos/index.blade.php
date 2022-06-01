@extends('layouts.main')
@section('titulo', 'Productos')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1">Productos</h3>
                <b-button class="mr-2" v-b-modal.modal-1 variant="primary"><i class="fas fa-plus"></i> Nuevo producto / servicio</b-button>
                <b-button class="mr-2" v-b-modal.modal-2 variant="primary"><i class="fas fa-file-import"></i> Importar</b-button>
                <b-button href="{{action('ProductoController@exportar')}}" variant="primary"><i class="fas fa-file-export"></i> Exportar...</b-button>
            </div>
            <div class="col-lg-3">
                @include('productos.buscador')
            </div>
        </div>
        @if($textoBuscado!='')
            <div class="row">
                <div class="col-lg-12 mt-5">
                    <div class="alert alert-dark" role="alert"><h5 class="mb-0">Resultados de búsqueda para: {{$textoBuscado}}
                            <a href="{{url('/productos')}}"><i class="fa fa-times float-right"></i></a></h5></div>
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-sm-12 mt-4">
                <div class="card">
                    <div class="card-header">
                        Lista de de productos y servicios
                    </div>
                    <div class="card-body">
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col"><a href="?orderby=cod_producto&order={{$order}}">Código <span class="icon-hover @if($orderby=='cod_producto') icon-hover-active @endif">{!!$order_icon!!}</span></a></th>
                                    <th scope="col">Tipo</th>
                                    <th style="width: 15%" scope="col"><a href="?orderby=nombre&order={{$order}}">Producto <span class="icon-hover @if($orderby=='nombre') icon-hover-active @endif">{!!$order_icon!!}</span></a></th>
                                    <th scope="col" style="width: 25%">Características</th>
                                    <th scope="col"><a href="?orderby=categoria&order={{$order}}">Categoría <span class="icon-hover @if($orderby=='categoria') icon-hover-active @endif">{!!$order_icon!!}</span></a></th>
                                    <th scope="col">Stock</th>
                                    <th scope="col">Costo</th>
                                    <th scope="col"><a href="?orderby=precio&order={{$order}}">Precio <span class="icon-hover @if($orderby=='precio') icon-hover-active @endif">{!!$order_icon!!}</span></a></th>
                                    <th scope="col">Opciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($productos as $producto)
                                    <tr>
                                        <td></td>
                                        <td>{{$producto->cod_producto}}</td>
                                        @if($producto->tipo_producto==1)
                                            <td >PRODUCTO</td>
                                        @else
                                            <td>SERVICIO</td>
                                        @endif
                                        <td>{{$producto->nombre}}</td>
                                        <td>{{$producto->presentacion}}</td>
                                        <td>{{$producto->categoria}}</td>
                                        @if($producto->tipo_producto==1)
                                            <td>{{$producto->cantidad}}</td>
                                        @else
                                            <td>-</td>
                                        @endif
                                        <td>{{$producto->moneda_compra=='PEN'?'S/':'USD'}}{{$producto->costo}}</td>
                                        <td>{{$producto->moneda=='PEN'?'S/':'USD'}}{{$producto->precio}}</td>
                                        <td class="botones-accion">
                                            <a @@click="editarProducto({{$producto->idproducto}})" href="javascript:void(0)">
                                                <button class="btn btn-success" title="Editar producto"><i
                                                            class="fas fa-edit"></i></button>
                                            </a>
                                            <a @can('Inventario: gestionar producto') href="{{url('productos/inventario').'/'.$producto->idproducto}}" @endcan  >
                                                <button @cannot('Inventario: kardex') disabled @endcannot  class="btn btn-info" title="Ver inventario"><i class="fas fa-list"></i>
                                                </button>
                                            </a>
                                            <button @cannot('Inventario: gestionar producto') disabled @endcannot @click="borrarProducto({{$producto->idproducto}})" class="btn btn-danger" title="Eliminar"><i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{$productos->links('layouts.paginacion')}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--INICIO MODAL PRODUCTOS-->
    <b-modal id="modal-1" ref="modal-1" size="lg"
             title="" @@ok="agregarProducto" @@hidden="resetModal">
    <template slot="modal-title">
        @{{tituloModal}}
    </template>
    <div class="container">
        <div class="row">
            <div class="col-lg-3">
                <div class="form-group">
                    <label>Tipo:</label>
                    <select v-model="tipo_producto" class="custom-select">
                        <option value="1">Producto</option>
                        <option value="2">Servicio</option>
                    </select>
                </div>
            </div>
            <div v-if="accion=='editar'" class="col-lg-3">
                <div class="form-group">
                    <label for="cod_producto">Código Producto:</label>
                    <input autocomplete="off" type="text" v-model="cod_producto" name="cod_producto" class="form-control">
                </div>
            </div>
            <div :class="[accion=='insertar'?'col-lg-9':'col-lg-6']">
                <div class="form-group">
                    <label for="nombre">Nombre producto / servicio:</label>
                    <input type="text" v-model="nombre" name="nombre"  class="form-control" autocomplete="off">
                </div>
            </div>
            <div class="col-lg-12">
                <div class="form-group">
                    <label for="presentacion">Características:</label>
                    <textarea v-model="presentacion" class="form-control" name="presentacion" id="" cols="30"
                              rows="2"></textarea>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <label for="idcategoria">Categoría:</label>
                    <select v-model="idcategoria" name="idcategoria" class="custom-select" id="selectPlantilla">
                        <option v-for="categoria in categorias" v-bind:value="categoria.idcategoria">@{{categoria.nombre}}</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <label for="undMedida">Unidad de medida:</label>
                    <select v-model="medida" name="undMedida" class="custom-select" id="selectUnd">
                        <option value="NIU/UND">Unidad</option>
                        <option value="MTR/M">Metro</option>
                        <option value="RO/ROL">Rollo</option>
                        <option value="KGM/KG">Kilogramo</option>
                        <option value="GRM/G">Gramo</option>
                        <option value="LTR/L">Litro</option>
                        <option value="MTK/M2">Metro cuadrado</option>
                        <option value="MTQ/M3">Metro cúbico</option>
                        <option value="PK/PQ">Paquete</option>
                        <option value="BX/CJ">Caja</option>
                        <option value="NIU/JG">Juego</option>
                        <option value="NIU/PR">Par</option>
                        <option value="BE/BE">Fardo</option>
                        <option value="BG/BG">Bolsa</option>
                        <option value="BJ/BJ">Balde</option>
                    </select>
                </div>
            </div>
            @can('Inventario: gestionar producto')
            <div v-show="tipo_producto==1" class="col-lg-3">
                <div class="form-group">
                    <label for="cantidad">Cantidad:</label>
                    <input autocomplete="off" type="number" v-model="cantidad" name="cantidad" class="form-control">
                </div>
            </div>
            <div v-show="tipo_producto==1" class="col-lg-3">
                <div class="form-group">
                    <label for="stock_bajo">Stock mínimo:</label>
                    <input autocomplete="off" type="number" v-model="stock_bajo" name="stock_bajo" class="form-control">
                </div>
            </div>
            <div class="col-lg-4">
                <label>Precio de venta:</label>
                <b-input-group>
                    <b-form-input type="number" v-model="precio"></b-form-input>
                    <template #append>
                        <b-dropdown :text="moneda" variant="secondary">
                            <b-dropdown-item @click="moneda = 'PEN'">PEN</b-dropdown-item>
                            <b-dropdown-item @click="moneda = 'USD'">USD</b-dropdown-item>
                        </b-dropdown>
                    </template>
                </b-input-group>
            </div>
            <div class="col-lg-5" v-show="accion=='editar' && tipo_producto=='1'">
                <label>Código de barras:</label>
                <div class="form-group">
                    {{--{!! DNS1D::getBarcodeSVG('4445645656', 'C39',3,33) !!}--}}
                    <a :href="'data:image/png;base64,'+barcode" download>
                        <img :src="'data:image/png;base64,'+barcode" alt="barcode">
                    </a>
                    <b-button variant="success" :href="'data:image/png;base64,'+barcode" download><i class="fas fa-download"></i></b-button>
                </div>
            </div>
            <div v-show="tipo_producto==1" class="col-lg-4 mb-3">
                <label for="precio">Descuentos:</label>
                <button @click="agregarDescuento" class="btn btn-primary"><i class="fas fa-plus"></i> Agregar descuento
                </button>
            </div>
            <div v-show="tipo_producto==1"  class="col-lg-12">
                <div class="row">
                    <div class="col-lg-6" v-for="(descuento,index) in descuentos" :key="index">
                        <div class="row">
                            <div class="col-lg-5 form-group">
                                <label for="precio">Cantidad > ó =:</label>
                                <input class="form-control" v-model="descuento.cantidad" type="number" placeholder="cantidad">
                            </div>
                            <div class="col-lg-5 form-group">
                                <label for="precio">Precio:</label>
                                <input class="form-control" v-model="descuento.precio" type="number" placeholder="precio">
                            </div>
                            <div class="col-lg-2">
                                <i style="right: 50px" @click="borrarDescuento(index)" class="fas fa-times-circle borrarCliente"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 mt-3">
                <p><strong>Data para reportes</strong></p>
            </div>
            <div class="col-lg-4">
                <label>Costo por ofrecer el @{{tipo_producto==1?'producto':'servicio'}}:</label>
                <b-input-group>
                    <b-form-input type="number" v-model="costo"></b-form-input>
                    <template #append>
                        <b-dropdown :text="moneda_compra" variant="secondary">
                            <b-dropdown-item @click="moneda_compra = 'PEN'">PEN</b-dropdown-item>
                            <b-dropdown-item @click="moneda_compra = 'USD'">USD</b-dropdown-item>
                        </b-dropdown>
                    </template>
                </b-input-group>
            </div>
            <div class="col-lg-3" v-show="moneda_compra == 'USD'">
                <label>Tipo de cambio</label>
                <b-input-group prepend="S/">
                    <b-form-input type="number" v-model="tipo_cambio_compra"></b-form-input>
                </b-input-group>
            </div>
            <div v-show="accion == 'editar' && cantidad != cantidad_aux && tipo_producto==1" class="col-lg-12 mt-2">
                <div class="form-group">
                    <label for="presentacion">Indica la razón de haber cambiado manualmente la cantidad:</label>
                    <input v-model="observacion" class="form-control" type="text">
                </div>
            </div>
            @endcan
            <div class="col-lg-12 mt-3">
                <div v-for="error in errorDatosProducto">
                    <p class="texto-error">@{{ error }}</p>
                </div>
            </div>
        </div>
    </div>
    </b-modal>
    <!--FIN MODAL PRODUCTOS -->
    <!--INICIO MODAL IMPORTACIÓN-->
    <b-modal id="modal-2" ref="modal-2" size="md" @@hidden="resetModal">
        <template slot="modal-title">
            Importar archivo excel
        </template>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <form class="form-upload" method="POST" action="{{url('productos/importar-productos')}}"
                          enctype="multipart/form-data">
                        <div class="form-group">
                            <input @change="cargarFichero" type="file" id="excel_file" name="excel_file" class="form-control-file">
                        </div>
                    </form>
                </div>
                <div class="col-lg-12 mt-4">
                    <a href="{{url('/productos/descargar-formato-importacion')}}"><i class="fas fa-download"></i> Descargar
                        formato de importación</a>
                </div>
            </div>
        </div>
        <template #modal-footer="{ ok, cancel}">
            <b-button variant="secondary" @click="cancel()">
                Cancel
            </b-button>
            <b-button :disabled="!input_file_data"  variant="primary" @click="importar_productos">
                <b-spinner v-show="mostrarProgresoGuardado" small label="Loading..." ></b-spinner>
                <span v-show="!mostrarProgresoGuardado">OK</span>
            </b-button>
        </template>
    </b-modal>
    <!--FIN MODAL IMPORTACIÓN -->
    <!--INICIO MODAL IMAGEN-->
    <b-modal id="modal-imagen" ref="modal-imagen" size="md" @@hidden="resetModal">
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
                            <input accept="image/*" @change="cargarFichero" type="file" id="input_file_data">
                        </div>
                        <div class="col-lg-12">
                            <div class="image-preview" v-if="dataFichero.length > 0">
                                <img class="preview" :src="dataFichero">
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


@endsection
@section('script')
    <script>
        let app = new Vue({
            el: '.app',
            data: {
                errorDatosProducto: [],
                errorProducto: 0,
                tituloModal:'Agregar producto / servicio',
                accion:'insertar',
                idproducto: -1,
                cod_producto: '',
                nombre: '',
                presentacion: '',
                precio: '0.0',
                costo: '0.0',
                eliminado: 0,
                imagen: '',
                cantidad: 0,
                cantidad_aux: 0,
                stock_bajo: '0',
                idcategoria: -1,
                idcategoria_aux: -1,
                medida: 'NIU/UND',
                categorias: [],
                tipo_producto: 1,
                barcode:'',
                input_file_data:'',
                mostrarProgresoGuardado:false,
                observacion: '',
                moneda: 'PEN',
                dataFichero: "",
                moneda_compra:'PEN',
                tipo_cambio_compra:"{{cache('opciones')['tipo_cambio_compra']}}",
                descuentos: [],
            },
            mounted(){
                this.obtener_categorias();
            },
            methods: {
                obtener_categorias(){
                    axios.get('{{action('ProductoController@mostrar_categorias')}}')
                        .then(response => {
                            let datos = response.data;
                            this.categorias = datos.categorias;
                            if (datos.categorias.length > 0) {
                                this.idcategoria = datos.categorias[0]['idcategoria'];
                                this.idcategoria_aux = datos.categorias[0]['idcategoria'];
                            }
                        })
                        .catch(error => {
                            this.alerta('Ha ocurrido un error al obtener las categorías');
                            console.log(error);
                        });
                },
                cargarFichero(event){
                    let input = event.target;
                    if (input.files && input.files[0]) {
                        let reader = new FileReader();
                        reader.onload = (e) => {
                            this.dataFichero = e.target.result;
                        };
                        reader.readAsDataURL(input.files[0]);
                        this.input_file_data = input.files[0];
                    }
                },
                modalImagen(id,imagen){
                    if(imagen){
                        this.dataFichero='/images/image-products/'+imagen;
                    }
                    this.$refs['modal-imagen'].show();
                    this.idproducto = id;
                },
                agregarImagen(){
                    this.mostrarProgresoGuardado=true;
                    let _this = this;
                    let data = new FormData();
                    data.append('imagen', this.input_file_data);
                    data.append('idproducto',this.idproducto);
                    let settings = {headers: {'content-type': 'multipart/form-data'}};

                    axios.post('{{url('/productos/agregar-imagen')}}', data, settings)
                        .then(function (response) {
                            _this.mostrarProgresoGuardado=false;
                            if (response.data === 1) {
                                window.location.reload(true)
                            } else {
                                alert(response.data)
                            }

                        })
                        .catch(function (error) {
                            _this.mostrarProgresoGuardado=false;
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });
                },
                agregarProducto(e){
                    if (this.validarProducto()) {
                        e.preventDefault();
                        return;
                    }

                    if(this.accion=='insertar'){
                        if(this.cod_producto.length==0){
                            this.generarCodigo();
                        }
                    }

                    let dataset={
                        'idproducto': this.idproducto,
                        'cod_producto': this.cod_producto,
                        'nombre': this.nombre,
                        'presentacion': this.presentacion,
                        'precio': this.precio,
                        'costo': this.costo,
                        'cantidad': this.cantidad,
                        'cantidad_aux': this.cantidad_aux,
                        'stock_bajo': this.stock_bajo,
                        'idcategoria': this.idcategoria,
                        'medida': this.medida,
                        'tipo_producto':this.tipo_producto,
                        'moneda': this.moneda,
                        'descuentos': JSON.stringify(this.descuentos),
                        'observacion': this.observacion,
                        'moneda_compra' : this.moneda_compra,
                        'tipo_cambio_compra' : this.tipo_cambio_compra,
                    };

                    let tipo_accion = this.accion == 'insertar' ? '{{action('ProductoController@store')}}' : '{{action('ProductoController@update')}}';

                    axios.post(tipo_accion, dataset)
                        .then(() => {
                            location.reload(true)
                        })
                        .catch(error => {
                            this.alerta('Ha ocurrido un error al guardar el producto');
                            console.log(error);
                        });

                },
                agregarDescuento(){
                    this.descuentos.push({
                        cantidad: 0,
                        precio: '0.00'
                    });
                },
                borrarDescuento(index){
                    this.descuentos.splice(index,1);
                },
                editarProducto(id){
                    this.tituloModal = 'Editar producto';
                    this.accion = 'editar';
                    this.idproducto = id;

                    axios.get('{{url('/productos/edit')}}' + '/' + id)
                        .then(response => {
                            let datos = response.data;
                            this.cod_producto = datos.cod_producto;
                            this.nombre = datos.nombre;
                            this.costo = datos.costo;
                            this.precio = datos.precio;
                            this.cantidad = datos.cantidad;
                            this.cantidad_aux = datos.cantidad;
                            this.stock_bajo = datos.stock_bajo;
                            this.presentacion = datos.presentacion;
                            this.medida = datos.unidad_medida;
                            this.idcategoria = datos.idcategoria;
                            this.tipo_producto = datos.tipo_producto;
                            this.descuentos = datos.descuentos;
                            this.barcode=datos.barcode;
                            this.moneda=datos.moneda;
                            this.moneda_compra = datos.moneda_compra;
                            this.tipo_cambio_compra = datos.tipo_cambio;
                            this.$refs['modal-1'].show();
                        })
                        .catch(error => {
                            this.alerta('Ha ocurrido un error.');
                            console.log(error);
                        });

                },
                borrarProducto(id){
                    if(confirm('Realmente desea eliminar el producto')){
                        axios.delete('{{url('/productos/destroy')}}' + '/' + id)
                            .then(response => {
                                location.reload(true)
                            })
                            .catch(error => {
                                console.log(error);
                            });
                    }
                },
                validarProducto(){
                    this.errorProducto = 0;
                    this.errorDatosProducto = [];
                    if (!this.nombre) this.errorDatosProducto.push('*Nombre de producto no puede estar vacio');
                    if (this.idcategoria == -1) this.errorDatosProducto.push('*Crea una categoría antes de guardar el producto');
                    if (isNaN(this.precio) || this.precio.length == 0) this.errorDatosProducto.push('*El campo precio debe contener un número');
                    if (isNaN(this.costo) || this.costo.length == 0) this.errorDatosProducto.push('*El campo costo debe contener un número');
                    if (isNaN(this.cantidad) || this.cantidad.length == 0) this.errorDatosProducto.push('*El campo cantidad debe contener un número');
                    if (isNaN(this.stock_bajo) || this.stock_bajo.length == 0) this.errorDatosProducto.push('*El campo stock bajo debe contener un número');

                    if(this.tipo_producto == '1' && this.descuentos.length > 0){

                        for(descuento of this.descuentos){
                            if (isNaN(descuento.precio) || descuento.precio.length == 0 || isNaN(descuento.cantidad) || descuento.cantidad.length == 0) this.errorDatosProducto.push('*Las casillas de descuento deben contener un número');
                            if (descuento.cantidad <= 0 || descuento.precio <= 0) this.errorDatosProducto.push('*Las casillas de descuento deben ser mayor a 0');
                            break;
                        }

                    }

                    if (this.errorDatosProducto.length) this.errorProducto = 1;
                    return this.errorProducto;
                },
                generarCodigo(){
                    let codigoCaracter = this.nombre.slice(0, 3);
                    let obj = <?php echo $ultimo_id ?>;
                    let codigoNumero = obj['idproducto'];
                    if (this.nombre <= 3) {
                        codigoCaracter = this.nombre.trim();
                    }
                    if (codigoNumero < 100) {
                        codigoNumero = '0' + codigoNumero;
                    }
                    this.cod_producto = codigoCaracter + codigoNumero;
                },
                importar_productos(){
                    this.mostrarProgresoGuardado=true;
                    let data = new FormData();
                    data.append('excel_file', this.input_file_data);
                    let settings = {headers: {'content-type': 'multipart/form-data'}};

                    axios.post('{{url('/productos/importar-productos')}}', data, settings)
                        .then(response => {
                            this.mostrarProgresoGuardado=false;
                            if (response.data === 1) {
                                this.$swal({
                                    position: 'top',
                                    icon: 'success',
                                    title: 'Importación realizada con éxito',
                                    timer: 6000,
                                    toast:true,
                                    confirmButtonColor: '#007bff',
                                }).then(()=>{
                                    window.location.reload()
                                });

                            } else if (response.data === 0) {
                                this.alerta("No se ha encontrado archivo para importar")
                            } else {
                                this.alerta("El archivo no cumple las condiciones de importación. Verifique los datos ingresados.")
                                document.getElementById("excel_file").value="";
                            }

                        })
                        .catch(error => {
                            this.mostrarProgresoGuardado=false;
                            this.alerta('Ha ocurrido un error al importar el archivo', 'error');
                            console.log(error);
                        });
                },
                resetModal(){
                    this.errorDatosProducto = [];
                    this.errorProducto = 0;
                    this.tituloModal = 'Agregar producto';
                    this.accion = 'insertar';
                    this.idproducto = -1;
                    this.cod_producto = '';
                    this.nombre = '';
                    this.presentacion = '';
                    this.precio = '0.0';
                    this.costo = '0.0';
                    this.eliminado = 0;
                    this.imagen = '';
                    this.cantidad = '0';
                    this.stock_bajo = '0';
                    this.idcategoria = this.idcategoria_aux;
                    this.medida = 'NIU/UND';
                    this.tipo_producto = 1;
                    this.moneda='PEN';
                    this.descuentos = [];
                    this.observacion = '';
                    this.input_file_data='';
                    this.dataFichero= "";
                    this.mostrarProgresoGuardado=false;
                    this.moneda_compra='PEN';
                    this.tipo_cambio_compra="{{cache('opciones')['tipo_cambio_compra']}}";
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