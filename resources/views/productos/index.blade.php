@extends('layouts.main')
@section('titulo', 'Productos')
@section('contenido')
    @php
        $agent = new \Jenssegers\Agent\Agent();
        $filtro = $_GET['filtro']??'Filtro';
        $tipo_cambio_compra = cache('opciones')['tipo_cambio_compra'];
        $unidad_medida = \sysfact\Http\Controllers\Helpers\DataUnidadMedida::getUnidadMedida();
        $can_gestionar = false;
    @endphp
    @can('Inventario: gestionar producto')
        @php
            $can_gestionar = true
        @endphp
    @endcan
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1">Productos</h3>
                <b-button class="mr-2 mb-2 mb-lg-0" v-b-modal.modal-nuevo-producto @click="tipo_producto = 1" variant="primary"><i class="fas fa-plus"></i> Nuevo</b-button>
                <b-button class="mr-2 mb-2 mb-lg-0" v-b-modal.modal-2 variant="primary"><i class="fas fa-file-import"></i> Importar</b-button>
                <b-button class="mr-2 mb-2 mb-lg-0" href="{{action('ProductoController@exportar')}}" variant="primary"><i class="fas fa-file-export"></i> Exportar...</b-button>
                <b-button class="mr-2 mb-2 mb-lg-0" v-b-modal.modal-nuevo-producto @click="tipo_producto = 3" variant="primary"><i class="fas fa-cube"></i> Kit de productos
                </b-button>
{{--
                <b-button class="mr-2" href="{{action('ProductoController@exportar')}}" variant="success"><i class="fas fa-edit"></i> Edición múltiple</b-button>
--}}
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
                    <div class="card-body">
                        <b-dropdown id="dropdown-opc" left text="Opciones" variant="success" class="float-left mr-3">
                            <b-dropdown-item href="/productos/descargar-barcodes"><i style="width: 2em" class="fa-solid fa-barcode"></i> Descargar códigos de barra
                            </b-dropdown-item>
                            <b-dropdown-item href="#" @click="selectMultiple(1)"><i class="fas fa-edit"></i> Edición múltiple
                            </b-dropdown-item>
                            <b-dropdown-item href="#" @click="selectMultiple(2)"><span class="text-danger"><i class="fas fa-trash"></i> Borrado múltiple</span>
                            </b-dropdown-item>
                        </b-dropdown>
                        <b-button variant="primary" v-b-modal.modal-edicion-multiple :disabled="!count_seleccionados" v-show="showSelectColumn && tipoEdicion==1"><i class="fas fa-edit"></i> Editar</b-button>
                        <b-button variant="danger" @click="borrarMultiple" :disabled="!count_seleccionados" v-show="showSelectColumn && tipoEdicion==2"><i class="fas fa-trash"></i> Borrar</b-button>
                        <b-button variant="outline-secondary" v-show="showSelectColumn" @click="cancelarMultiple"><i class="fas fa-times"></i> Cancelar</b-button>
                        <b-button variant="success" v-b-modal.modal-col class="float-right mb-3"><i class="fas fa-columns"></i> Columnas</b-button>
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col"></th>
                                    <th v-show="showSelectColumn"><b-form-checkbox v-model="selAll"  @change="seleccionarTodo($event)"></b-form-checkbox></th>
                                    <th @if(!$columnas['ubicacion']) style="display: none;" @endif scope="col">Ubicación</th>
                                    <th @if(!$columnas['codigo']) style="display: none;" @endif scope="col"><a href="{{$filtro == 'Filtro'?'?':url()->full()}}&orderby=cod_producto&order={{$order}}">Código <span class="icon-hover @if($orderby=='cod_producto') icon-hover-active @endif">{!!$order_icon!!}</span></a></th>
                                    <th @if(!$columnas['tipo_producto']) style="display: none;" @endif scope="col">Clasif.</th>
                                    <th style="width: 15%" scope="col"><a href="{{$filtro == 'Filtro'?'?':url()->full()}}&orderby=nombre&order={{$order}}">Producto <span class="icon-hover @if($orderby=='nombre') icon-hover-active @endif">{!!$order_icon!!}</span></a></th>
                                    <th scope="col" style="width: 20%">Descripción</th>
                                    <th @if(!$columnas['montaje']) style="display: none;" @endif>Montaje</th>
                                    <th @if(!$columnas['capsula']) style="display: none;" @endif>Cápsula</th>
                                    <th @if(!$columnas['tipo']) style="display: none;" @endif>Tipo</th>
                                    <th @if(!$columnas['marca']) style="display: none;" @endif scope="col">Marca</th>
                                    <th @if(!$columnas['modelo']) style="display: none;" @endif scope="col">Modelo</th>
                                    <th @if(!$columnas['categoria']) style="display: none;" @endif scope="col"><a href="{{$filtro == 'Filtro'?'?':url()->full()}}&orderby=categoria&order={{$order}}">Categoría <span class="icon-hover @if($orderby=='categoria') icon-hover-active @endif">{!!$order_icon!!}</span></a></th>
                                    <th @if(!$columnas['stock']) style="display: none;" @endif scope="col">Stock</th>
                                    <th @if(!$columnas['costo']) style="display: none;" @endif scope="col">Compra</th>
                                    <th @if(!$columnas['precio']) style="display: none;" @endif scope="col"><a href="{{$filtro == 'Filtro'?'?':url()->full()}}&orderby=precio&order={{$order}}">Precio <span class="icon-hover @if($orderby=='precio') icon-hover-active @endif">{!!$order_icon!!}</span></a></th>
                                    <th @if(!$columnas['precio_min']) style="display: none;" @endif scope="col">Precio min.</th>
                                    <th @if(!$columnas['imagen']) style="display: none;" @endif scope="col">Imagen</th>
                                    <th scope="col">Opciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($productos) > 0)
                                    @foreach($productos as $key=>$producto)
                                        <tr @if(!$agent->isDesktop()) @click="editarProducto({{$producto->idproducto}})" @endif>
                                            <td></td>
                                            <td @click.stop v-show="showSelectColumn"><b-form-checkbox v-model="productosSeleccionados[{{$key}}]['seleccionado']" @change="eventSeleccionado($event,{{$key}})"></b-form-checkbox></td>
                                            <td @if(!$columnas['ubicacion']) style="display: none;" @endif>{{$producto->ubicacion}}</td>
                                            <td @if(!$columnas['codigo']) style="display: none;" @endif>{{str_pad($producto->cod_producto,5,'0',STR_PAD_LEFT) }}</td>
                                            <td @if(!$columnas['tipo_producto']) style="display: none;" @endif scope="col">{{$producto->tipo_producto_nombre}}</td>
                                            <td>
                                                {{$producto->nombre}} @if($producto->tipo_producto == 3) <span class="badge badge-warning"><i class="far fa-star"></i> KIT</span> <br>@endif
                                                @if($producto->items_kit)
                                                    @php
                                                        $kit = json_decode($producto->items_kit, true);
                                                    @endphp
                                                    @foreach($kit as $item)
                                                    <span style="font-size: 11px; color: #0b870b;">+ ({{ $item['cantidad'] }}) {{$item['nombre']}}
                                                        <br></span>
                                                    @endforeach
                                                @endif
                                            </td>
                                            <td>{{$producto->presentacion}}</td>
                                            <td @if(!$columnas['montaje']) style="display: none;" @endif>{{$producto->param_1}}</td>
                                            <td @if(!$columnas['capsula']) style="display: none;" @endif>{{$producto->param_2}}</td>
                                            <td @if(!$columnas['tipo']) style="display: none;" @endif>{{$producto->param_3}}</td>
                                            <td @if(!$columnas['marca']) style="display: none;" @endif>{{$producto->marca}}</td>
                                            <td @if(!$columnas['modelo']) style="display: none;" @endif>{{$producto->modelo}}</td>
                                            <td @if(!$columnas['categoria']) style="display: none;" @endif>{{$producto->categoria}}</td>
                                            @if($producto->tipo_producto==1)
                                                <td @if(!$columnas['stock']) style="display: none;" @endif>{{$producto->cantidad}}</td>
                                            @else
                                                <td @if(!$columnas['stock']) style="display: none;" @endif>-</td>
                                            @endif
                                            <td @if(!$columnas['costo']) style="display: none;" @endif>{{$producto->moneda_compra=='PEN'?'S/':'USD'}}{{$producto->costo}}</td>
                                            <td @if(!$columnas['precio']) style="display: none;" @endif>{{$producto->moneda=='PEN'?'S/':'USD'}}{{$producto->precio}}</td>
                                            <td @if(!$columnas['precio_min']) style="display: none;" @endif>@if($producto->param_5) {{$producto->param_5=='PEN'?'S/':'USD'}} @endif{{$producto->param_4}}</td>
                                            <td @if(!$columnas['imagen']) style="display: none;" @endif class="image-product"><a><img @click="modalImagen({{$producto->idproducto}},'{{$producto->imagen}}')" src="{{$producto->imagen?$producto->imagen:url('images/no-image.jpg')}}" class="card-img-top"></a></td>
                                            <td class="botones-accion" style="width: 10%" @click.stop>
                                                <a @click="editarProducto({{$producto->idproducto}})" href="javascript:void(0)">
                                                    <button class="btn btn-success" title="Editar producto"><i
                                                                class="fas fa-edit"></i></button>
                                                </a>
                                                <b-dropdown id="dropdown-1" text="Más" class="m-md-2 " variant="warning">
                                                    <b-dropdown-item @can('Inventario: kardex') href="{{url('productos/inventario').'/'.$producto->idproducto}}" @else disabled @endcan><i class="fas fa-list"></i> Ver kardex</b-dropdown-item>
    {{--
                                                    <b-dropdown-item @cannot('Inventario: gestionar producto') disabled @endcannot @click="borrarProducto({{$producto->idproducto}})"><i class="fas fa-exchange-alt"></i> Trasladar</b-dropdown-item>
    --}}
    {{--                                                <b-dropdown-item @cannot('Inventario: gestionar producto') disabled @endcannot @click="borrarProducto({{$producto->idproducto}})"><i class="fas fa-copy"></i> Duplicar</b-dropdown-item>
                                                    <b-dropdown-item @cannot('Inventario: gestionar producto') disabled @endcannot @click="borrarProducto({{$producto->idproducto}})"><i class="fas fa-ban"></i> Inhabilitar</b-dropdown-item>--}}
                                                    <b-dropdown-item @cannot('Inventario: gestionar producto') disabled @endcannot @click="borrarProducto({{$producto->idproducto}})"><i class="fas fa-trash"></i> Eliminar</b-dropdown-item>
                                                </b-dropdown>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="text-center">
                                        <td colspan="18">No hay datos que mostrar</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                        {{$productos->links('layouts.paginacion')}}
                    </div>
                </div>
            </div>
        </div>
    </div>
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
                            <input @change="cargarFichero" type="file" id="excel_file" name="excel_file" class="form-control-file" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
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
                <div class="col-lg-12 mb-3">
                    <label>Link de imagen:</label>
                    <i class="fas fa-question-circle" id="popover-target-1"></i>
                    <b-popover target="popover-target-1" triggers="hover" placement="top" variant="danger">
                        Agrega una imagen al producto copiando y pegando el link de su ubicación. Por ejemplo: https://google.com/imagen.jpg
                    </b-popover>
                    <input type="url" v-model="urlImagen" class="form-control" placeholder="Pega el link aquí">
                </div>
                <div class="col-lg-12">
                    <div class="image-preview" v-if="urlImagen.length > 5">
                        <img class="preview" :src="urlImagen">
                    </div>
                </div>
                {{--<div class="col-lg-12">
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
                </div>--}}
            </div>
        </div>
        <template #modal-footer="{ ok, cancel}">
            <b-button variant="secondary" @click="cancel()">
                Cancel
            </b-button>
            <b-button :disabled="mostrarProgresoGuardado"  variant="primary" @click="agregarImagen">
                <b-spinner v-show="mostrarProgresoGuardado" small label="Loading..." ></b-spinner>
                <span v-show="!mostrarProgresoGuardado">OK</span>
            </b-button>
        </template>
    </b-modal>
    <!--FIN MODAL IMAGEN -->
    <!--INICIO MODAL COLUMNAS-->
    <b-modal id="modal-col" ref="modal-col" size="sm" @hidden="resetModal" @ok="ocultarColumnas">
    <template slot="modal-title">
       <p class="mb-0">Mostrar / ocultar columnas</p>
    </template>
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <b-form-checkbox v-model="columnas.ubicacion" switch>Ubicación</b-form-checkbox>
                <b-form-checkbox v-model="columnas.codigo" switch>Código</b-form-checkbox>
                <b-form-checkbox v-model="columnas.tipo_producto" switch>Clasificación</b-form-checkbox>
                <b-form-checkbox v-model="columnas.marca" switch>Marca</b-form-checkbox>
                <b-form-checkbox v-model="columnas.modelo" switch>Modelo</b-form-checkbox>
                <b-form-checkbox v-model="columnas.categoria" switch>Categoría</b-form-checkbox>
                <b-form-checkbox v-model="columnas.stock" switch>Stock</b-form-checkbox>
                <b-form-checkbox v-model="columnas.costo" switch>Costo</b-form-checkbox>
                <b-form-checkbox v-model="columnas.precio" switch>Precio</b-form-checkbox>
                <b-form-checkbox v-model="columnas.precio_min" switch>Precio mínimo</b-form-checkbox>
                <b-form-checkbox v-model="columnas.imagen" switch>Imagen</b-form-checkbox>
                <b-form-checkbox v-model="columnas.montaje" switch>Montaje</b-form-checkbox>
                <b-form-checkbox v-model="columnas.capsula" switch>Cápsula</b-form-checkbox>
                <b-form-checkbox v-model="columnas.tipo" switch>Tipo</b-form-checkbox>
            </div>
        </div>
    </div>
    </b-modal>
    <!--FIN MODAL COLUMNAS -->
    <b-modal size="xl" id="modal-edicion-multiple" ref="modal-edicion-multiple" ok-only @hidden="resetModal">
        <template slot="modal-title">
            Edición múltiple
        </template>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="table-responsive tabla-gestionar">
                        <table class="table table-striped table-hover table-sm">
                            <thead class="bg-custom-green">
                            <tr>
                                <th scope="col" style="width: 15%">Código</th>
                                <th style="width: 30%" scope="col">Nombre</th>
                                <th style="width: 30%" scope="col">Características</th>
                                <th scope="col" style="width: 10%">Moneda</th>
                                <th scope="col" style="width: 15%">Precio</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(producto,index) in productosSeleccionados" v-if="producto.seleccionado" :key="index">
                                <td><input v-on:keydown="navigate($event)" name="cod_producto" type="text" class="form-control" onfocus="this.select()" v-model="producto.cod_producto"></td>
                                <td><input v-on:keydown="navigate($event)" name="nombre" type="text" class="form-control" onfocus="this.select()" v-model="producto.nombre"></td>
                                <td><input v-on:keydown="navigate($event)" name="presentacion" type="text" class="form-control" onfocus="this.select()" v-model="producto.presentacion"></td>
                                <td>
                                    <select v-on:keydown="navigate($event, 'select')" name="moneda" v-model="producto.moneda" class="custom-select" :disabled="tipo_producto == 3">
                                        <option value="PEN">PEN</option>
                                        <option value="USD">USD</option>
                                    </select>
                                </td>
                                <td><input v-on:keydown="navigate($event, 'number')" name="precio" type="number" class="form-control" onfocus="this.select()" v-model="producto.precio"></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <template #modal-footer="{ ok, cancel}">
            <b-button variant="secondary" @click="cancel()">
                Cancel
            </b-button>
            <b-button :disabled="mostrarProgresoGuardado" variant="primary" @click="guardarEdicionMultiple">
                <b-spinner v-show="mostrarProgresoGuardado" small label="Loading..." ></b-spinner>
                <span v-show="!mostrarProgresoGuardado">Guardar cambios</span>
            </b-button>
        </template>
    </b-modal>
    <agregar-producto
            ref="modal-producto"
            :tipo_cambio="{{$tipo_cambio_compra}}"
            :unidad_medida="{{json_encode($unidad_medida)}}"
            :can_gestionar="{{json_encode($can_gestionar)}}"
            :tipo_de_producto="tipo_producto"
            :origen="'productos'">
    </agregar-producto>
@endsection
@section('script')
    <script>
        let app = new Vue({
            el: '.app',
            data: {
                imagen: '',
                tipo_producto:1,
                input_file_data:'',
                mostrarProgresoGuardado:false,
                dataFichero: "",
                urlImagen: '',
                columnas: <?php echo json_encode($columnas) ?>,
                search: '{{$textoBuscado}}',
                showSelectColumn:false,
                tipoEdicion:false,
                productosSeleccionados:<?php echo json_encode($productos->toArray()['data']) ?>,
                count_seleccionados:false,
                selAll:false
            },
            methods: {
                navigate(event) {
                    if(event.key === 'ArrowUp' ||
                        event.key === 'ArrowDown' ||
                        event.key === 'ArrowRight' ||
                        event.key === 'ArrowLeft' ||
                        event.key === 'Enter' ||
                        event.key === 'NumpadEnter'
                    )
                    {
                        event.preventDefault();
                        event.stopPropagation();

                        let nextField = event.key === 'ArrowRight' ? 'next' : event.key === 'ArrowLeft' ? 'previous' : null;
                        let currentInput = event.currentTarget;

                        if (nextField) {
                            let nextInput = currentInput.parentElement[nextField + 'ElementSibling'];
                            if (nextInput) {
                                nextInput = nextInput.querySelector('input') || nextInput.querySelector('select');
                                nextInput.focus();
                                if(nextInput.tagName === 'INPUT'){
                                    nextInput.select();
                                }
                            }
                        } else {
                            let nextField = 'previous';
                            if(event.key === 'Enter' || event.key === 'NumpadEnter' || event.key === 'ArrowDown'){
                                nextField = 'next'
                            }
                            let nextInputContainer = currentInput.parentElement.parentElement[nextField + 'ElementSibling'];
                            if (nextInputContainer) {
                                let nextInput = nextInputContainer.querySelector(`input[name="${currentInput.name}"]`) || nextInputContainer.querySelector(`select[name="${currentInput.name}"]`);

                                nextInput.focus();
                                if(nextInput.tagName === 'INPUT'){
                                    nextInput.select();
                                }
                            }
                        }
                    }

                },
                guardarEdicionMultiple(){
                    this.mostrarProgresoGuardado =true;
                    axios.post('{{url('/productos/edicion-multiple')}}', {
                        'productos':JSON.stringify(this.productosSeleccionados),
                    })
                        .then((response) => {
                            this.$swal({
                                position: 'top',
                                icon: 'success',
                                title: response.data.mensaje,
                                timer: 2000,
                                showConfirmButton: false,
                                toast:true
                            }).then(() => {
                                window.location.reload(true)
                            });
                        })
                        .catch(error => {
                            this.mostrarProgresoGuardado=false;
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });
                },
                seleccionarTodo(event){
                    for(let i=0; i < this.productosSeleccionados.length; i++){
                        this.eventSeleccionado(event,i)
                    }
                },
                eventSeleccionado(event, index){
                    this.productosSeleccionados[index]['seleccionado']=event;
                    this.countSeleccionados();
                },
                countSeleccionados(){
                    for(let i=0; i < this.productosSeleccionados.length; i++){
                        if(this.productosSeleccionados[i]['seleccionado']){
                            this.count_seleccionados = true;
                            break;
                        } else {
                            this.count_seleccionados = false;
                        }
                    }
                },
                selectMultiple(tipo){
                    this.showSelectColumn = true;
                    this.tipoEdicion = tipo;
                },
                cancelarMultiple(){
                    this.selAll = false;
                    this.showSelectColumn = false;
                    for(let i=0; i < this.productosSeleccionados.length; i++){
                        this.eventSeleccionado(false,i)
                    }
                },
                buscar(event){
                    if(event.code == 'Enter' || event.code == 'NumpadEnter'){
                        const textoCodificado = encodeURIComponent(this.search);
                        event.preventDefault();
                        if('Filtro' == '<?php echo $filtro ?>'){
                            window.location.href = '/productos?textoBuscado='+textoCodificado;
                        } else {
                            window.location.href = '/productos?textoBuscado='+textoCodificado+'&filtro=<?php echo $filtro ?>'
                        }
                    }
                },
                ocultarColumnas(){
                    axios.post('{{url('/productos/ocultar-columnas')}}', {
                        'columnas':JSON.stringify(this.columnas),
                    })
                        .then(response => {
                            window.location.reload(true)
                        })
                        .catch(error => {
                            this.mostrarProgresoGuardado=false;
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });
                },
                editarProducto(id){
                    this.$refs['modal-producto'].editarProducto(id);
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
                        //this.dataFichero='/images/'+'{{$domain}}'+'/image-products/'+imagen;
                        this.urlImagen = imagen;
                    }
                    this.$refs['modal-imagen'].show();
                    this.idproducto = id;
                },
                agregarImagen(){
                    this.mostrarProgresoGuardado=true;
                    /*let data = new FormData();
                    data.append('imagen', this.input_file_data);
                    data.append('idproducto',this.idproducto);
                    let settings = {headers: {'content-type': 'multipart/form-data'}};*/

                    axios.post('{{url('/productos/agregar-imagen')}}', {
                        'imagen':this.urlImagen,
                        'idproducto':this.idproducto
                    })
                        .then(response => {
                            this.mostrarProgresoGuardado=false;
                            if (response.data === 1) {
                                window.location.reload(true)
                            } else {
                                alert(response.data)
                            }

                        })
                        .catch(error => {
                            this.mostrarProgresoGuardado=false;
                            alert('Ha ocurrido un error.');
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
                borrarMultiple(){
                    if(confirm('¿Está seguro de eliminar los productos seleccionados?')){
                        this.mostrarProgresoGuardado =true;
                        axios.post('{{url('/productos/borrado-multiple')}}', {
                            'productos':JSON.stringify(this.productosSeleccionados),
                        })
                            .then((response) => {
                                this.$swal({
                                    position: 'top',
                                    icon: 'success',
                                    title: response.data.mensaje,
                                    timer: 2000,
                                    showConfirmButton: false,
                                    toast:true
                                }).then(() => {
                                    window.location.reload(true)
                                });
                            })
                            .catch(error => {
                                this.mostrarProgresoGuardado=false;
                                alert('Ha ocurrido un error.');
                                console.log(error);
                            });
                    }
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
                                this.alerta(response.data);
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
                    this.tituloModal = 'Agregar producto';
                    this.accion = 'insertar';
                    this.idproducto = -1;
                    this.eliminado = 0;
                    this.imagen = '';
                    this.input_file_data='';
                    this.dataFichero= "";
                    this.mostrarProgresoGuardado=false;
                    this.urlImagen = '';
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