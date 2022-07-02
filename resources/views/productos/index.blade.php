@extends('layouts.main')
@section('titulo', 'Productos')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1">Productos</h3>
                <b-button class="mr-2" v-b-modal.modal-1 variant="primary"><i class="fas fa-plus"></i> Nuevo</b-button>
                <b-button class="mr-2" v-b-modal.modal-2 variant="primary"><i class="fas fa-file-import"></i> Importar</b-button>
                <b-button class="mr-2"href="{{action('ProductoController@exportar')}}" variant="primary"><i class="fas fa-file-export"></i> Exportar...</b-button>
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
                        <b-button variant="secondary" v-b-modal.modal-col class="float-right mb-3">Mostrar / ocultar columnas</b-button>
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col"></th>
                                    <th @if(!$columnas['ubicacion']) style="display: none;" @endif scope="col">Ubicación</th>
                                    <th @if(!$columnas['codigo']) style="display: none;" @endif scope="col"><a href="?orderby=cod_producto&order={{$order}}">Código <span class="icon-hover @if($orderby=='cod_producto') icon-hover-active @endif">{!!$order_icon!!}</span></a></th>
                                    <th @if(!$columnas['tipo_producto']) style="display: none;" @endif scope="col">Clasif.</th>
                                    <th style="width: 15%" scope="col"><a href="?orderby=nombre&order={{$order}}">Producto <span class="icon-hover @if($orderby=='nombre') icon-hover-active @endif">{!!$order_icon!!}</span></a></th>
                                    <th scope="col" style="width: 20%">Descripción</th>
                                    <th @if(!$columnas['montaje']) style="display: none;" @endif>Montaje</th>
                                    <th @if(!$columnas['capsula']) style="display: none;" @endif>Cápsula</th>
                                    <th @if(!$columnas['tipo']) style="display: none;" @endif>Tipo</th>
                                    <th @if(!$columnas['marca']) style="display: none;" @endif scope="col">Marca</th>
                                    <th @if(!$columnas['modelo']) style="display: none;" @endif scope="col">Modelo</th>
                                    <th @if(!$columnas['categoria']) style="display: none;" @endif scope="col"><a href="?orderby=categoria&order={{$order}}">Categoría <span class="icon-hover @if($orderby=='categoria') icon-hover-active @endif">{!!$order_icon!!}</span></a></th>
                                    <th @if(!$columnas['stock']) style="display: none;" @endif scope="col">Stock</th>
                                    <th @if(!$columnas['costo']) style="display: none;" @endif scope="col">Compra</th>
                                    <th @if(!$columnas['precio']) style="display: none;" @endif scope="col"><a href="?orderby=precio&order={{$order}}">Precio <span class="icon-hover @if($orderby=='precio') icon-hover-active @endif">{!!$order_icon!!}</span></a></th>
                                    <th @if(!$columnas['precio_min']) style="display: none;" @endif scope="col">Precio min.</th>
                                    <th @if(!$columnas['imagen']) style="display: none;" @endif scope="col">Imagen</th>
                                    <th scope="col">Opciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($productos as $producto)
                                    <tr>
                                        <td></td>
                                        <td @if(!$columnas['ubicacion']) style="display: none;" @endif>{{$producto->ubicacion}}</td>
                                        <td @if(!$columnas['codigo']) style="display: none;" @endif>{{$producto->cod_producto}}</td>
                                        @if($producto->tipo_producto==1)
                                            <td @if(!$columnas['tipo_producto']) style="display: none;" @endif scope="col">PRODUCTO</td>
                                        @else
                                            <td @if(!$columnas['tipo_producto']) style="display: none;" @endif scope="col">SERVICIO</td>
                                        @endif
                                        <td>{{$producto->nombre}}</td>
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
                                        <td class="botones-accion" style="width: 10%">
                                            <a @click="editarProducto({{$producto->idproducto}})" href="javascript:void(0)">
                                                <button class="btn btn-success" title="Editar producto"><i
                                                            class="fas fa-edit"></i></button>
                                            </a>
                                            <b-dropdown id="dropdown-1" text="Más" class="m-md-2 " variant="warning">
                                                <b-dropdown-item @can('Inventario: kardex') href="{{url('productos/inventario').'/'.$producto->idproducto}}" @endcan><i class="fas fa-list"></i> Ver kardex</b-dropdown-item>
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
    <div>
        <b-card no-body class="no-shadow">
            <b-tabs card>
                <b-tab title="General" active>
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
                                <div class="col-lg-12 mt-3">
                                    <p><strong>Data para reportes</strong></p>
                                </div>
                                <div class="col-lg-4">
                                    <label>@{{tipo_producto==1?'Precio de compra':'Costo de producción'}}:</label>
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
                        </div>
                    </div>
                </b-tab>
                <b-tab :disabled="tipo_producto!=1"  title="Descuentos">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-4 mb-3">
                                <button @click="agregarDescuento" class="btn btn-primary"><i class="fas fa-plus"></i> Agregar descuento
                                </button>
                            </div>
                            <div v-show="tipo_producto==1"  class="col-lg-12">
                                <div class="row">
                                    <div class="col-lg-12" v-for="(descuento,index) in descuentos" :key="index">
                                        <div class="row">
                                            <div class="col-lg-5 form-group">
                                                <label for="precio">Cantidad mayor o igual a:</label>
                                                <input class="form-control" v-model="descuento.cantidad" type="number" placeholder="cantidad">
                                            </div>
                                            <div class="col-lg-5 form-group">
                                                <label for="precio">Precio:</label>
                                                <input class="form-control" v-model="descuento.precio" type="number" placeholder="precio">
                                            </div>
                                            <div class="col-lg-2">
                                                <button @click="borrarDescuento(index)" style="margin-top: 20px" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </b-tab>
                <b-tab title="Ubicación">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Almacén</label>
                                    <select :disabled="accion == 'editar'" v-model="idalmacen" class="custom-select" @change="obtener_ubicacion()">
                                        <option v-for="item in almacen" v-bind:value="item.idalmacen">@{{item.nombre}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Ubicación</label>
                                    <select v-model="idubicacion" class="custom-select">
                                        <option v-for="item in ubicacion" v-bind:value="item.idubicacion">@{{item.nombre}}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </b-tab>
                <b-tab title="Otros atributos">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-3 form-group">
                                <label>Marca:</label>
                                <input v-model="marca" class="form-control" maxlength="60" type="text">
                            </div>
                            <div class="col-lg-3 form-group">
                                <label>Modelo:</label>
                                <input v-model="modelo" class="form-control" maxlength="60" type="text">
                            </div>
                            <div class="col-lg-3 form-group">
                                <label>Montaje</label>
                                <input v-model="param_1" class="form-control" maxlength="60" type="text">
                            </div>
                            <div class="col-lg-3 form-group">
                                <label>Cápsula</label>
                                <input v-model="param_2" class="form-control" maxlength="60" type="text">
                            </div>
                            <div class="col-lg-3 form-group">
                                <label>Tipo</label>
                                <input v-model="param_3" class="form-control" maxlength="60" type="text">
                            </div>
                            <div class="col-lg-3 form-group">
                                <label>Precio mínimo</label>
                                <b-input-group>
                                    <b-form-input type="number" v-model="param_4"></b-form-input>
                                    <template #append>
                                        <b-dropdown :text="param_5==null ? 'PEN':param_5" variant="secondary">
                                            <b-dropdown-item @click="param_5 = 'PEN'">PEN</b-dropdown-item>
                                            <b-dropdown-item @click="param_5 = 'USD'">USD</b-dropdown-item>
                                        </b-dropdown>
                                    </template>
                                </b-input-group>
                            </div>
                        </div>
                    </div>
                </b-tab>
            </b-tabs>
        </b-card>
    </div>
    <div class="col-lg-12 mt-3">
        <div v-for="error in errorDatosProducto">
            <p class="texto-error">@{{ error }}</p>
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
                urlImagen: '',
                almacen:[],
                idalmacen:-1,
                ubicacion:[],
                idubicacion:null,
                marca:'',
                modelo:'',
                param_1:'',
                param_2:'',
                param_3:'',
                param_4:'0.00',
                param_5:'PEN',
                columnas: <?php echo json_encode($columnas) ?>
            },
            mounted(){
                this.obtener_categorias();
                this.obtener_almacen();
            },
            methods: {
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
                obtener_categorias(){
                    axios.get('{{action('ProductoController@mostrar_categorias')}}')
                        .then(response => {
                            let datos = response.data;
                            this.categorias = datos.categorias;
                            if (datos.categorias.length > 0) {
                                this.idcategoria = datos.categorias[0]['idcategoria'];
                            }
                        })
                        .catch(error => {
                            this.alerta('Ha ocurrido un error al obtener las categorías');
                            console.log(error);
                        });
                },
                obtener_almacen(){
                    axios.get('{{action('ProductoController@mostrar_almacen')}}')
                        .then(response => {
                            let datos = response.data;
                            this.almacen = datos.almacen;
                            if (datos.almacen.length > 0) {
                                this.idalmacen = datos.almacen[0]['idalmacen'];
                            }
                            this.obtener_ubicacion(this.idalmacen)
                        })
                        .catch(error => {
                            this.alerta('Ha ocurrido un error al obtener los datos');
                            console.log(error);
                        });
                },
                obtener_ubicacion(){
                    axios.get('{{url('/productos/mostrar-ubicacion')}}'+'/'+this.idalmacen)
                        .then(response => {
                            let datos = response.data;
                            this.ubicacion = datos.ubicacion;
                            if (datos.ubicacion.length > 0) {
                                this.idubicacion = datos.ubicacion[0]['idubicacion'];
                            } else {
                                this.idubicacion = null;
                            }
                        })
                        .catch(error => {
                            this.alerta('Ha ocurrido un error al obtener los datos');
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
                        'idalmacen': this.idalmacen,
                        'idubicacion': this.idubicacion,
                        'marca': this.marca,
                        'modelo': this.modelo,
                        'param_1': this.param_1,
                        'param_2': this.param_2,
                        'param_3': this.param_3,
                        'param_4': this.param_4,
                        'param_5': this.param_5,
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
                            this.marca=datos.marca;
                            this.modelo=datos.modelo;
                            this.param_1=datos.param_1;
                            this.param_2=datos.param_2;
                            this.param_3=datos.param_3;
                            this.param_4=datos.param_4;
                            this.param_5=datos.param_5;
                            this.moneda_compra = datos.moneda_compra;
                            this.tipo_cambio_compra = datos.tipo_cambio;
                            this.idalmacen = datos.almacen.idalmacen || null;
                            this.idubicacion = datos.almacen.idubicacion || null;
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
                    if (!this.idubicacion) this.errorDatosProducto.push('*Ubicación de producto no puede quedar en blanco');
                    if (this.accion == 'editar' && this.cantidad != this.cantidad_aux && this.tipo_producto==1 && !this.observacion) this.errorDatosProducto.push('*El motivo de edición no puede estar vacío');

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
                    this.urlImagen = '';
                    this.idubicacion = this.ubicacion[0].idubicacion;
                    this.idalmacen = this.almacen[0].idalmacen;
                    this.marca = '';
                    this.modelo = '';
                    this.param_1 = '';
                    this.param_2 = '';
                    this.param_3 = '';
                    this.param_4 = '0.00';
                    this.param_5 = 'PEN';
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
            },
            watch:{
                idalmacen(){
                    this.obtener_ubicacion();
                }
            }

        });
    </script>
@endsection