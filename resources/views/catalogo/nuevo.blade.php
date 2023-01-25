@extends('layouts.main')
@section('titulo', 'Nuevo catálogo')
@section('contenido')
    @php $agent = new \Jenssegers\Agent\Agent() @endphp
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-sm-12">
                <h3 class="titulo-admin-1">
                    <a href="{{url('catalogos')}}"><i class="fas fa-arrow-circle-left"></i></a>
                    Nuevo catálogo
                </h3>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 mt-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4 form-group mb-4">
                                <label>Título</label>
                                <input class="form-control" type="text" v-model="titulo" placeholder="Título principal">
                            </div>
                            <div class="col-lg-4 form-group mb-4">
                                <label>Subtítulo</label>
                                <input class="form-control" type="text" v-model="subtitulo" placeholder="Redacta un subtítulo">
                            </div>
                            <div class="col-lg-4 form-group mb-4">
                                <label>Link imagen de portada</label>
                                <input class="form-control" type="text" v-model="link_portada" placeholder="Copia y pega el link aquí">
                            </div>
                            <div class="col-lg-2 mt-3">
                                <b-form-checkbox v-model="precios" switch size="sm">
                                    Mostrar precios
                                </b-form-checkbox>
                            </div>
                        </div>
                        <div class="row mt-4">
                            @if(json_decode(cache('config')['interfaz'], true)['buscador_productos'] == 1)
                                <div class="col-lg-7">
                                    <autocomplete ref="suggest" v-on:agregar_producto="agregarProducto"></autocomplete>
                                </div>
                            @else
                                <div class="col-lg-10">
                                    <b-button v-b-modal.modal-producto variant="primary" class="mr-2">
                                        <i class="fas fa-search-plus" v-show="!mostrarSpinnerProducto"></i>
                                        <b-spinner v-show="mostrarSpinnerProducto" small label="Loading..."></b-spinner>
                                        Seleccionar producto
                                    </b-button>
                                </div>
                            @endif
                        </div>
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col" style="width: 10px"></th>
                                    <th scope="col" style="width: 250px">Imagen</th>
                                    <th scope="col" style="width: 250px">Producto</th>
                                    <th scope="col" style="width: 350px">Caracteristicas</th>
                                    <th scope="col" style="width: 100px">Precio</th>
                                    <th scope="col" style="width: 50px"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(producto,index) in productosSeleccionados" :key="producto.index">
                                    <td></td>
                                    <td style="width: 5%"><img style="width: 100%" :src="producto.imagen?producto.imagen:'{{url('images/no-image.jpg')}}'" alt=""></td>
                                    <td><input class="form-control" type="text" v-model="producto.nombre" disabled></td>
                                    <td><textarea class="form-control" rows="1" v-model="producto.presentacion"></textarea></td>
                                    <td>
                                        <input onfocus="this.select()" @change="guardar_prev_precio(index)" @keyup="calcular(index)" class="form-control navigable nav-precio" :data-i="index" type="text" v-model="producto.precio">
                                    </td>
                                    <td></td>
                                </tr>
                                <tr class="text-center" v-show="productosSeleccionados.length == 0"><td colspan="10">Los productos que agregues se mostrarán de izquierda a derecha en el catálogo PDF</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="dropdown-divider"></div>
                        {{--<div class="row mt-3">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="">Pie de página</label>
                                    <textarea placeholder="Esto se mostrará en la parte inferior de las páginas, por ejemplo datos de contacto"  v-model="footer" class="form-control mt-4 mt-lg-0" cols="15" rows="1"></textarea>
                                </div>
                            </div>
                        </div>--}}
                    </div>
                </div>
            </div>
            <div class="col-lg-12 mb-5 order-1 order-md-0">
                <div class="card">
                    <div class="card-header">
                        Acciones
                    </div>
                    <div class="card-body text-center">
                        <b-button :disabled="mostrarProgresoGuardado || productosSeleccionados.length==0" class="mb-2" :disabled="productosSeleccionados.length==0" @click="procesarCatalogo"
                                  variant="success">
                            <i v-show="!mostrarProgresoGuardado" class="fas fa-save"></i>
                            <b-spinner v-show="mostrarProgresoGuardado" small label="Loading..." ></b-spinner> Guardar catálogo
                        </b-button>
                        <b-button class="mb-2" @click="limpiar" variant="danger"><i class="fas fa-ban"></i> Cancelar
                        </b-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <modal-producto
            v-bind:stock="false"
            v-on:agregar_producto="agregarProducto">
    </modal-producto>
    <modal-detalle
            :item="item"
            :show-precio="true"
            :can-edit-precio="true"
            v-on:actualizar="actualizarDetalle">
    </modal-detalle>
@endsection
@section('script')
    <script>

        let app = new Vue({
            el: '.app',
            data: {
                mostrarProgresoGuardado: false,
                buscar: '',
                productosSeleccionados: [],
                mostrarSpinnerProducto: false,
                titulo:'{{$titulo}}',
                subtitulo:'CATÁLOGO DE PRODUCTOS',
                link_portada:'',
                precios: false,
                footer:'',
                item:{},
                index:-1,
            },
            methods: {
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
                            descuento: this.descuento_global,
                            porcentaje_descuento: this.porcentaje_descuento_global,
                            tipo_descuento: this.tipo_descuento_global
                        };
                        this.esDstoGlobal = true;
                    }
                },
                actualizarDetalle(){
                    this.calcular(this.index);
                },
                agregarProducto(obj){
                    let productos = this.productosSeleccionados.push(Object.assign({}, obj));
                    let i = productos - 1;
                    let producto = this.productosSeleccionados[i];
                    this.$set(producto, 'num_item', i);
                    this.$set(producto, 'cantidad', 1);
                },
                borrarItemVenta(index){
                    this.productosSeleccionados.splice(index, 1);
                },
                resetModal(){
                    this.buscar = '';
                },
                procesarCatalogo(){
                    this.mostrarProgresoGuardado = true;
                    axios.post('{{action('CatalogoController@store')}}', {
                        'titulo': this.titulo,
                        'subtitulo': this.subtitulo,
                        'imagen_portada': this.link_portada,
                        'precios': this.precios,
                        'footer': this.footer,
                        'items': JSON.stringify(this.productosSeleccionados)
                    })
                        .then(response => {
                            if(isNaN(response.data)){
                                this.alerta(response.data);
                                this.mostrarProgresoGuardado = false;
                            } else{
                                window.location.href='/catalogos/editar/'+response.data;
                            }
                        })
                        .catch(error => {
                            this.alerta('Ha ocurrido un error al guardar el catálogo');
                            console.log(error);
                            this.mostrarProgresoGuardado = false;
                        });
                },
                limpiar(){
                    this.mostrarProgresoGuardado = false;
                    this.buscar = '';
                    this.productosSeleccionados = [];
                    this.mostrarSpinnerProducto = false;
                    this.footer = '';
                    this.titulo='{{$titulo}}';
                    this.subtitulo='CATÁLOGO DE PRODUCTOS';
                    this.link_portada = '';
                    this.precios = false;
                    this.item = {};
                    this.index = -1;
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
            },

        });
    </script>
@endsection