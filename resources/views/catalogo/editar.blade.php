@extends('layouts.main')
@section('titulo', 'Editar catálogo')
@section('contenido')
    @php $agent = new \Jenssegers\Agent\Agent() @endphp
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-sm-12">
                <h3 class="titulo-admin-1">
                    <a href="{{url('catalogos')}}"><i class="fas fa-arrow-circle-left"></i></a>
                    Catálogo
                </h3>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 mt-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="row" v-show="!editar">
                            <div class="col-lg-6">
                                <strong>Título:</strong> {{$catalogo->titulo}}<hr>
                                <strong>Subtítulo:</strong> {{$catalogo->subtitulo}} <hr>
                            </div>
                        </div>
                        <div class="row" v-show="editar">
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
                        <div class="row mt-4" v-show="editar">
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
                                <tr v-show="editar" v-for="(producto,index) in productosSeleccionados" :key="producto.index">
                                    <td></td>
                                    <td style="width: 5%"><img style="width: 100%" :src="producto.imagen?producto.imagen:'{{url('images/no-image.jpg')}}'" alt=""></td>
                                    <td><input class="form-control" type="text" v-model="producto.nombre" disabled></td>
                                    <td><textarea class="form-control" rows="1" v-model="producto.presentacion"></textarea></td>
                                    <td>
                                        <input onfocus="this.select()" @change="guardar_prev_precio(index)" @keyup="calcular(index)" class="form-control navigable nav-precio" :data-i="index" type="text" v-model="producto.precio">
                                    </td>
                                    <td class="botones-accion" style="width: 120px">
                                        <b-button  @click="borrarItemVenta(index)"  variant="danger" title="Borrar item"><i class="fas fa-trash"></i>
                                        </b-button>
                                    </td>
                                </tr>
                                <tr v-show="!editar" v-for="(producto,index) in productosSeleccionados" :key="producto.index">
                                    <td></td>
                                    <td style="width: 5%"><img style="width: 100%" :src="producto.imagen" alt=""></td>
                                    <td>@{{ producto.nombre }}</td>
                                    <td>@{{ producto.presentacion }}</td>
                                    <td>@{{ producto.precio }}</td>
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
                        <b-button title="Guardar" v-show="editar" :disabled="productosSeleccionados.length==0" class="mb-2" @click="actualizarCatalogo"
                                  variant="success">
                            <svg v-show="!mostrarProgresoGuardado" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-save-fill" viewBox="0 0 16 16">
                                <path d="M8.5 1.5A1.5 1.5 0 0 1 10 0h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h6c-.314.418-.5.937-.5 1.5v7.793L4.854 6.646a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0l3.5-3.5a.5.5 0 0 0-.708-.708L8.5 9.293V1.5z"/>
                            </svg>
                            <b-spinner v-show="mostrarProgresoGuardado" small label="Loading..." ></b-spinner>
                            Guardar cambios
                        </b-button>
                        <b-button title="Editar"  v-show="!editar" @click="editar = !editar" class="mb-2"  variant="success">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z"/>
                            </svg>
                        </b-button>
                        <b-button title="Cancelar"  v-show="editar" @click="cancelar_edicion" class="mb-2" variant="info">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-square" viewBox="0 0 16 16">
                                <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                                <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                            </svg>
                            Cancelar edición
                        </b-button>
                        @if($agent->isDesktop())
                            <b-button title="Imprimir"  :disabled="editar" class="mb-2"
                                      @if(json_decode(cache('config')['interfaz'], true)['tipo_impresion'] == 1)
                                      target="_blank" href="{{url('catalogos/imprimir').'/'.$catalogo['idcatalogo']}}"
                                      @else
                                      @click="imprimir({{$catalogo['idcatalogo']}})"
                                      @endif
                                      variant="warning">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16">
                                    <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                                    <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
                                </svg>
                            </b-button>
                        @endif
                        <b-button title="Descargar PDF"  :disabled="editar" class="mb-2" href="{{url('catalogos/descargar').'/'.$catalogo['idcatalogo']}}" variant="warning">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-arrow-down" viewBox="0 0 16 16">
                                <path d="M8.5 6.5a.5.5 0 0 0-1 0v3.793L6.354 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 10.293V6.5z"/>
                                <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                            </svg>
                            PDF
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
                editar:false,
                idcatalogo:<?php echo $catalogo->idcatalogo ?>,
                mostrarProgresoGuardado: false,
                buscar: '',
                productosSeleccionados: <?php echo $productos ?>,
                mostrarSpinnerProducto: false,
                titulo:'<?php echo $catalogo->titulo ?>',
                subtitulo:'<?php echo $catalogo->subtitulo ?>',
                link_portada:'<?php echo $catalogo->imagen_portada ?>',
                precios: <?php echo $catalogo->precios ?>,
                footer:'<?php echo $catalogo->footer ?>',
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
                imprimir(idcatalogo){
                    let iframe = document.createElement('iframe');
                    document.body.appendChild(iframe);
                    iframe.style.display = 'none';
                    iframe.onload = () => {
                        setTimeout(() => {
                            iframe.focus();
                            iframe.contentWindow.print();
                        }, 0);
                    };
                    iframe.src = "/presupuestos/imprimir/"+idcatalogo;
                },
                actualizarCatalogo(){
                    this.mostrarProgresoGuardado = true;
                    axios.post('{{action('CatalogoController@update')}}', {
                        'idcatalogo': this.idcatalogo,
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
                                location.reload();
                            }
                        })
                        .catch(error => {
                            this.alerta('Ha ocurrido un error al guardar el catálogo');
                            console.log(error);
                            this.mostrarProgresoGuardado = false;
                        });
                },
                cancelar_edicion(){
                    location.reload();
                },
                limpiar(){
                    this.mostrarProgresoGuardado = false;
                    this.buscar = '';
                    this.productosSeleccionados = [];
                    this.mostrarSpinnerProducto = false;
                    this.footer = '';
                    this.titulo = '';
                    this.subtitulo = '';
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