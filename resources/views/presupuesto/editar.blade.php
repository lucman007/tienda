@extends('layouts.main')
@section('titulo', 'Cotización')
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
                <h3 class="titulo-admin-1">Cotización N° {{$presupuesto['correlativo']}}</h3>
                <b-button href="{{action('PresupuestoController@index')}}" class="mr-2"  variant="primary"><i class="fas fa-list"></i> Ver cotizaciones</b-button>
                <b-button href="{{action('PresupuestoController@nuevo_presupuesto')}}" class="mr-2"  variant="primary"><i class="fas fa-plus"></i> Nueva cotización</b-button>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 mt-4 mb-3">
                <div class="card">
                    <div class="card-header">
                        Detalle
                    </div>
                    <div class="card-body">
                        <div class="row" v-show="!editar">
                            <div class="col-lg-4">
                                <strong>Fecha:</strong> {{date("d/m/Y H:i:s",strtotime($presupuesto->fecha))}} <hr>
                                <strong>Moneda:</strong>
                                @if($presupuesto->moneda=='S/')
                                    SOLES <hr>
                                @else
                                    DÓLARES <hr>
                                @endif
                                <strong>Atención:</strong> {{$presupuesto->atencion}} <hr>

                            </div>
                            <div class="col-lg-4">
                                <strong>Validez:</strong> {{$presupuesto->validez}} días <hr>
                                <strong>Condiciones de pago:</strong> {{$presupuesto->condicion_pago}} <hr>
                                <strong>Tiempo de entrega:</strong> {{$presupuesto->tiempo_entrega}} <hr>
                            </div>
                            <div class="col-lg-4">
                                <strong>Garantía:</strong> {{$presupuesto->garantia}} <hr>
                                <strong>Impuesto:</strong> {{$presupuesto->impuesto}} <hr>
                                <strong>Lugar de entrega:</strong> {{$presupuesto->lugar_entrega}} <hr>
                            </div>
                            <div class="col-lg-8">
                                <strong>Cliente:</strong> @if($presupuesto->cliente->idcliente != -1) {{$presupuesto->cliente['num_documento']}} - {{$presupuesto->persona['nombre']}}@endif <hr>
                            </div>
                            <div class="col-lg-4" v-show="exportacion">
                                <strong>Incoterm:</strong> {{$presupuesto->incoterm}} / <strong>Flete:</strong> {{$presupuesto->flete}}/ <strong>Seguro:</strong> {{$presupuesto->seguro}}
                            </div>
                        </div>
                        <div class="row" v-show="editar">
                            <div class="col-lg-2 form-group">
                                <label>Fecha</label>
                                <input type="date" v-model="fecha" class="form-control">
                            </div>
                            <div class="col-lg-2 form-group mb-4">
                                <label>Moneda</label>
                                <select v-model="moneda" class="custom-select">
                                    <option value="S/">Soles</option>
                                    <option value="USD">Dólares</option>
                                </select>
                            </div>
                            <div class="col-lg-3 form-group mb-4">
                                <label>Atención</label>
                                <input class="form-control" type="text" v-model="atencion" placeholder="Persona a quien se dirige">
                            </div>
                            <div class="col-lg-3">
                                <div class="row">
                                    <div class="col-lg-6 form-group mb-4">
                                        <label>Validez (días)</label>
                                        <input class="form-control" type="text" v-model="validez" placeholder="Número de días">
                                    </div>
                                    <div class="col-lg-6 form-group">
                                        <label>Tipo cambio</label>
                                        <input type="text" v-model="tipoCambio"
                                               class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 mt-3">
                                <b-form-checkbox v-model="exportacion" switch size="sm">
                                    Es exportación
                                </b-form-checkbox>
                            </div>
                        </div>
                        <div class="row">
                            <div v-show="editar" class="col-lg-7">
                                <div class="row">
                                    @if(json_decode(cache('config')['interfaz'], true)['buscador_clientes'] == 1)
                                        <div class="col-lg-9 order-2 order-lg-1">
                                            <autocomplete-cliente v-on:agregar_cliente="agregarCliente" v-on:borrar_cliente="borrarCliente" ref="suggestCliente"></autocomplete-cliente>
                                        </div>
                                        <div class="col-lg-3 order-1 order-lg-2">
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
                                        <div class="col-lg-12">
                                            <input type="text" v-model="nombreCliente" class="form-control mb-2"
                                                   placeholder="Cliente" disabled readonly>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div v-show="editar && exportacion" class="col-lg-5" style="margin-top: -20px">
                                <div class="row">
                                    <div class="col-lg-4 mb-2">
                                        <label>Incoterm</label>
                                        <input v-model="incoterm" type="text" class="form-control">
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label>Flete</label>
                                        <input @keyup="calcularTotalVenta()" v-model="flete" type="number" class="form-control">
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label>Seguro</label>
                                        <input @keyup="calcularTotalVenta()" v-model="seguro" type="number" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-show="editar" class="row mt-4">
                            @if(json_decode(cache('config')['interfaz'], true)['buscador_productos'] == 1)
                                <div class="col-lg-7 order-2 order-lg-1">
                                    <autocomplete ref="suggest" v-on:agregar_producto="agregarProducto"></autocomplete>
                                </div>
                                <div class="col-lg-3 order-1 order-lg-2">
                                    <b-button class="mb-4 mt-2 mt-lg-0 float-right float-lg-left"  v-b-modal.modal-nuevo-producto
                                              variant="primary"><i class="fas fa-plus" v-show="!mostrarSpinnerProducto"></i>
                                        <b-spinner v-show="mostrarSpinnerProducto" small label="Loading..."></b-spinner>
                                        Nuevo producto
                                    </b-button>
                                    <b-button class="mb-4 mt-2 ml-1 mt-lg-0 float-left" :disabled="disabledNr" @click="agregar_nr('00NR')"
                                              variant="success"><i class="fas fa-plus"></i>
                                        <b-spinner v-show="mostrarSpinnerProducto" small label="Loading..."></b-spinner>
                                        NR
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
                            <div class="col-lg-2 order-3 my-2 my-md-0" v-show="!exportacion">
                                <b-form-checkbox v-model="esConIgv" switch size="sm">
                                    Incluir IGV
                                </b-form-checkbox>
                            </div>
                        </div>
                        <div class="table-responsive tabla-gestionar">
                            @if($agent->isDesktop())
                            <table-draggable v-show="ordenar" :productos="productosSeleccionados"></table-draggable>
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr v-show="!ordenar">
                                    <th scope="col" style="width: 10px"></th>
                                    <th scope="col" style="width: 250px">Producto</th>
                                    <th scope="col" style="width: 350px">Caracteristicas</th>
                                    <th scope="col" style="width: 100px">Precio</th>
                                    <th scope="col" style="width: 100px">Cantidad</th>
                                    <th scope="col" style="width: 80px; text-align: center">Dscto</th>
                                    <th scope="col" style="width: 80px; text-align: center">Subtotal</th>
                                    <th scope="col" style="width: 80px; text-align: center">Igv</th>
                                    <th scope="col" style="width: 100px">Total</th>
                                    <th scope="col" style="width: 50px"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-show="editar" v-for="(producto,index) in productosSeleccionados" :key="producto.index">
                                    <td></td>
                                    <td><input class="form-control" type="text" v-model="producto.nombre" disabled></td>
                                    <td><textarea class="form-control texto-desc" ref="textareas" @input="expandirTextarea" rows="1" v-model="producto.presentacion"></textarea></td>
                                    <td>
                                        <input onfocus="this.select()" @change="guardar_prev_precio(index)" @keyup="calcular(index)" class="form-control navigable nav-precio" :data-i="index" type="text" v-model="producto.precio">
                                    </td>
                                    <td>
                                        <b-input-group>
                                            <input onfocus="this.select()" @keyup="calcular(index)" class="form-control navigable nav-cantidad" :data-i="index" type="text" v-model="producto.cantidad">
                                            <b-input-group-append>
                                                <b-input-group-text style="font-size: 10px !important; font-weight: 700;">
                                                    @{{ (producto.unidad_medida).split('/')[1] || producto.unidad_medida }}
                                                </b-input-group-text>
                                            </b-input-group-append>
                                        </b-input-group>
                                    </td>
                                    <td class="text-center">@{{(Number(producto.descuento)).toFixed(2)}} <br><span v-show="Number(producto.descuento) > 0" style="color:green">(@{{redondearSinCeros(Number(producto.porcentaje_descuento))+'%'}})</span></td>
                                    <td class="text-center">@{{(Number(producto.subtotal)).toFixed(2)}}</td>
                                    <td class="text-center">@{{(Number(producto.igv)).toFixed(2)}}</td>
                                    <td>@{{(Number(producto.total)).toFixed(2)}}</td>
                                    <td class="botones-accion" style="width: 120px">
                                        <b-button :disabled="producto['precio']<=0 || producto['cantidad']<=0" v-b-modal.modal-descuento @click="editarItem(producto,index)" variant="success" title="Agregar descuento"><i class="fas fa-percentage"></i></b-button>
                                        <b-button  @click="borrarItemVenta(index)"  variant="danger" title="Borrar item"><i class="fas fa-trash"></i>
                                        </b-button>
                                    </td>
                                </tr>
                                <tr v-show="!(editar || ordenar)" v-for="(producto,index) in productosSeleccionados" :key="producto.index">
                                    <td></td>
                                    <td> @{{ producto.cod_producto == '00NR'?'-':producto.nombre }}</td>
                                    <td style="white-space: break-spaces" class="text_desc">@{{ producto.presentacion}}</td>
                                    <td>@{{ producto.precio }}</td>
                                    <td>@{{ producto.cantidad }} @{{ producto.unidad_medida }}</td>
                                    <td class="text-center">@{{(Number(producto.descuento)).toFixed(2)}} <br><span v-show="Number(producto.descuento) > 0" style="color:green">(@{{redondearSinCeros(Number(producto.porcentaje_descuento))+'%'}})</span></td>
                                    <td style="text-align: center">@{{(Number(producto.subtotal)).toFixed(2)}}</td>
                                    <td style="text-align: center">@{{(Number(producto.igv)).toFixed(2)}}</td>
                                    <td>@{{(Number(producto.total)).toFixed(2)}}</td>
                                    <td>
                                    </td>
                                </tr>
                                <tr class="text-center" v-show="productosSeleccionados.length == 0"><td colspan="9">Agrega productos desde el buscador</td></tr>
                                </tbody>
                            </table>
                            <div class="alert alert-primary text-center" v-show="ordenar">
                                Selecciona un item y arrastra para reordenarlo
                            </div>
                            <button v-show="!(ordenar||editar)" class="btn btn-primary float-right mb-2 mr-2" @click="ordenar = !ordenar"><i class="fas fa-arrows-alt"></i> Ordenar items</button>
                            <button v-show="ordenar" class="btn btn-success float-right mb-2 mr-2" @click="actualizarPresupuesto"><i class="fas fa-save"></i> Guardar</button>
                            <button v-show="ordenar" class="btn btn-danger float-right mb-2 mr-2" @click="cancelar_edicion"><i class="fas fa-times"></i> Cancelar</button>
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
                                    <tr v-show="editar" v-for="(producto,index) in productosSeleccionados" :key="index" v-b-modal.modal-detalle @click="editarItem(producto, index)">
                                        <td>@{{producto.cod_producto == '00NR'?producto.presentacion:producto.nombre}} x @{{producto.cantidad}}</td>
                                        <td>@{{(Number(producto.total)).toFixed(2)}}</td>
                                        <td @click.stop>
                                            <b-button :disabled="producto['precio']<=0 || producto['cantidad']<=0" v-b-modal.modal-descuento @click="editarItem(producto,index)" variant="success" title="Agregar descuento">
                                                <i class="fas fa-percentage"></i>
                                            </b-button>
                                            <button @click="borrarItemVenta(index)" class="btn btn-danger"
                                                    title="Borrar item"><i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-show="!editar" v-for="(producto,index) in productosSeleccionados">
                                        <td>@{{producto.cod_producto == '00NR'?producto.presentacion:producto.nombre}} x @{{producto.cantidad}}</td>
                                        <td>@{{(Number(producto.total)).toFixed(2)}}</td>
                                        <td></td>
                                    </tr>
                                    <tr class="text-center" v-show="productosSeleccionados.length == 0"><td colspan="8">No has agregado productos</td></tr>
                                    </tbody>
                                </table>
                            @endif
                        </div>
                        <div class="dropdown-divider"></div>
                        <div class="row mt-3"  v-show="editar">
                            <div class="col-lg-3">
                                <b-button :disabled="productosSeleccionados.length==0 || subtotal <= 0" class="w-100" v-b-modal.modal-descuento @click="editarItem()" variant="success">
                                    <i class="fas fa-percentage"></i> Descuento global: @{{tipo_descuento_global?porcentaje_descuento_global+'%':moneda+' '+(Number(descuento_global)).toFixed(2)}}
                                </b-button>
                            </div>
                            <div class="col-lg-7">
                                <div class="form-group">
                                    <textarea placeholder="Observaciones..."  v-model="observaciones" class="form-control mt-4 mt-lg-0" cols="15" rows="1"></textarea>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <input placeholder="Referencia" type="text" v-model="referencia"
                                           class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="card" v-show="!editar" style="box-shadow: none">
                            <div class="card-body">
                                <strong>Descuento global:</strong> @{{tipo_descuento_global?porcentaje_descuento_global+'%':moneda+' '+(Number(descuento_global)).toFixed(2)}}<br>
                                <strong>Observaciones:</strong> @{{ observaciones }} <br>
                                <strong>Referencia:</strong> @{{ referencia }} <br>
                                <strong>Ocultar impuestos en impresión:</strong> @{{ ocultar_impuestos?'Sí':'No' }} <br>
                                <strong>Ocultar precios en impresión:</strong> @{{ ocultar_precios?'Sí':'No' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-show="editar" class="col-lg-12 mb-3">
                <div class="card">
                    <div class="card-header">
                        Información y configuración adicional
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-3 form-group mb-4">
                                <label>Condiciones de pago</label>
                                <input class="form-control" type="text" v-model="condicion_pago">
                            </div>
                            <div class="col-lg-4 form-group mb-4">
                                <label>Tiempo de entrega</label>
                                <input class="form-control" type="text" v-model="tiempo_entrega">
                            </div>
                            <div class="col-lg-2 form-group mb-4">
                                <label>Garantía</label>
                                <input class="form-control" type="text" v-model="garantia">
                            </div>
                            <div class="col-lg-3 form-group mb-4">
                                <label>Impuesto</label>
                                <input class="form-control" type="text" v-model="impuesto">
                            </div>
                            <div class="col-lg-4 form-group mb-4">
                                <label>Lugar de entrega</label>
                                <input class="form-control" type="text" v-model="lugar_entrega">
                            </div>
                            <div class="col-lg-4 form-group mb-4">
                                <label>Atentamente</label>
                                <input class="form-control" type="text" v-model="contacto">
                            </div>
                            <div class="col-lg-4 form-group mb-4">
                                <label>Teléfonos</label>
                                <input class="form-control" type="text" v-model="telefonos">
                            </div>
                            <div class="col-lg-12">
                                <p>Impresión PDF:</p>
                            </div>
                            <div class="col-lg-2">
                                <b-form-checkbox v-model="ocultar_impuestos" switch size="sm">
                                    Ocultar impuestos
                                </b-form-checkbox>
                            </div>
                            <div class="col-lg-2">
                                <b-form-checkbox v-model="ocultar_precios" switch size="sm">
                                    Ocultar precios
                                </b-form-checkbox>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 mb-5 order-1 order-md-0">
                <div class="card" v-show="!ordenar">
                    <div class="card-header">
                        Acciones
                    </div>
                    <div class="card-body text-center">
                        <b-button title="Guardar" v-show="editar" :disabled="productosSeleccionados.length==0" class="mb-2" @click="actualizarPresupuesto"
                                  variant="success">
                            <svg v-show="!mostrarProgresoGuardado" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-save-fill" viewBox="0 0 16 16">
                                <path d="M8.5 1.5A1.5 1.5 0 0 1 10 0h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h6c-.314.418-.5.937-.5 1.5v7.793L4.854 6.646a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0l3.5-3.5a.5.5 0 0 0-.708-.708L8.5 9.293V1.5z"/>
                            </svg>
                            <b-spinner v-show="mostrarProgresoGuardado" small label="Loading..." ></b-spinner>
                            Guardar cambios
                        </b-button>
                        <b-button title="Editar"  v-show="!editar" @click="editarPresupuesto" class="mb-2"  variant="success">
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
                                          target="_blank" href="{{url('presupuestos/imprimir').'/'.$presupuesto['idpresupuesto']}}"
                                          @else
                                          @click="imprimir({{$presupuesto['idpresupuesto']}})"
                                          @endif
                                          variant="warning">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16">
                                        <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                                        <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
                                    </svg>
                                </b-button>
                        @endif
                        <b-button title="Descargar PDF"  :disabled="editar" class="mb-2" href="{{url('presupuestos/descargar').'/'.$presupuesto['idpresupuesto']}}" variant="warning">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-arrow-down" viewBox="0 0 16 16">
                                <path d="M8.5 6.5a.5.5 0 0 0-1 0v3.793L6.354 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 10.293V6.5z"/>
                                <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                            </svg>
                            PDF
                        </b-button>
                        @can('Facturación')
                            <b-dropdown :disabled="editar" class="mb-2" id="dropdown-1" text="Opciones" variant="warning">
                                <b-dropdown-item href="{{url('facturacion?presupuesto').'='.$presupuesto['idpresupuesto']}}"><i class="fas fa-file-alt"></i> Crear factura</b-dropdown-item>
                                <b-dropdown-item href="{{url('guia/nuevo?presupuesto').'='.$presupuesto['idpresupuesto']}}"><i class="fas fa-shipping-fast"></i> Generar guia electrónica</b-dropdown-item>
                                @can('Producción')
                                <b-dropdown-item href="{{url('produccion/nuevo?presupuesto').'='.$presupuesto['idpresupuesto']}}"><i class="fas fa-tools"></i> Orden de producción</b-dropdown-item>
                                @endcan
                            </b-dropdown>
                        @endcan
                        <div v-show="!editar" class="col-lg-12 mt-5">
                            <label class="mb-2">Enviar a correo electrónico:</label>
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group mb-2">
                                        <input v-model="mensaje" class="form-control mb-2" placeholder="Nombre del destinatario" type="text">
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <div class="form-group mb-2">
                                        <input v-model="mail" type="email" class="form-control" placeholder="Correo electrónico">
                                    </div>
                                </div>
                                <div class="col-lg-1">
                                    <button @click="agregarCC" class="btn btn-primary"><i class="fas fa-user-plus"></i></button>
                                </div>
                                <div class="offset-lg-4 col-lg-7" v-for="item in cc" :key="index">
                                    <div class="form-group mb-2">
                                        <input v-model="item.email" type="email" class="form-control" placeholder="Con copia a...">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 mb-3">
                                <b-form-checkbox v-model="conCopia" switch size="sm" class="my-2">
                                    Enviarme una copia
                                </b-form-checkbox>
                                <b-button :disabled="mostrarProgresoMail" @click="enviar_a_correo" variant="primary">
                                    <i v-show="!mostrarProgresoMail" class="fas fa-envelope"></i>
                                    <b-spinner v-show="mostrarProgresoMail" small label="Loading..." ></b-spinner> Enviar
                                </b-button>
                            </div>
                            <div class="col-lg-12">
                                @php
                                    $mail = json_decode($presupuesto->datos_adicionales, true)['mail'];
                                @endphp
                                @if($mail)
                                    @foreach($mail as $item)
                                        <div class="alert alert-primary text-left py-1 mb-1">
                                            <i class="fas fa-envelope"></i> Enviado a <strong>{{$item['direccion']}}</strong> el día {{date('d/m/Y', strtotime($item['fecha']))}} a las {{date('H:i', strtotime($item['fecha']))}} horas
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-5 order-0 order-md-1">
                <div class="card" v-show="!ordenar">
                    <div class="card-header">
                        Totales
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <p>@{{descuentos>0?'Descuentos: '+ moneda + ' ' + descuentos:''}}<br>
                                <span v-show="!ocultar_impuestos">Subtotal: @{{ moneda }} @{{ (Number(subtotal)).toFixed(2) }}</span><br>
                                <span v-show="!ocultar_impuestos">IGV: @{{ moneda }} @{{ (Number(igv)).toFixed(2) }}</span></p>
                            <p class="p-2 total-venta" style="margin-top:20px;">@{{ moneda }} @{{ (Number(totalVenta)).toFixed(2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
            :ultimo_id="{{$ultimo_id}}"
            :tipo_cambio="{{$tipo_cambio_compra}}"
            :unidad_medida="{{json_encode($unidad_medida)}}"
            :can_gestionar="{{json_encode($can_gestionar)}}"
            :tipo_de_producto="1"
            :origen="'cotizaciones'"
            v-on:agregar="agregarProductoNuevo">
    </agregar-producto>
    <modal-descuento ref="descuentos"
            :item="item"
            :moneda="moneda"
            :igv="esConIgv"
            :global="esDstoGlobal"
            :data-descuento="dataDescuento"
            v-on:actualizar="actualizarDescuento">
    ></modal-descuento>
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
                ordenar:false,
                editar:false,
                accion: 'insertar',
                idpresupuesto:<?php echo $presupuesto['idpresupuesto'] ?>,
                mostrarProgresoGuardado: false,
                mostrarProgresoFacturacion: false,
                clienteSeleccionado: <?php echo $presupuesto['cliente'] ?>,
                buscar: '',
                mostrarSpinnerCliente: false,
                productosSeleccionados: <?php echo $productos ?>,
                mostrarSpinnerProducto: false,
                totalVenta: 0.00,
                igv: 0.00,
                subtotal: 0.00,
                moneda: '<?php echo $presupuesto['moneda'] ?>',
                observaciones: "<?php echo $presupuesto['observaciones'] ?>",
                atencion:"<?php echo $presupuesto['atencion'] ?>",
                validez:"<?php echo $presupuesto['validez'] ?>",
                condicion_pago:"<?php echo $presupuesto['condicion_pago'] ?>",
                tiempo_entrega:"<?php echo $presupuesto['tiempo_entrega'] ?>",
                garantia:"<?php echo $presupuesto['garantia'] ?>",
                impuesto:"<?php echo $presupuesto['impuesto'] ?>",
                lugar_entrega:"<?php echo $presupuesto['lugar_entrega'] ?>",
                contacto:"<?php echo $presupuesto['contacto'] ?>",
                telefonos:"<?php echo $presupuesto['telefonos'] ?>",
                esConIgv:!!<?php echo $presupuesto['igv_incluido'] ?>,
                mail:"<?php echo $presupuesto->persona->correo ?>",
                conCopia:true,
                mostrarProgresoMail:false,
                mensaje:'',
                tipoCambio: <?php echo cache('opciones')['tipo_cambio_compra'] ?>,
                descuentos: 0.00,
                descuento_global: <?php echo $presupuesto['descuento'] ?>,
                porcentaje_descuento_global:<?php echo $presupuesto['porcentaje_descuento'] ?>,
                exportacion: !!<?php echo $presupuesto->exportacion ?>,
                flete: '<?php echo $presupuesto->flete ?>',
                seguro: '<?php echo $presupuesto->seguro ?>',
                incoterm:'<?php echo $presupuesto->incoterm ?>',
                altura:'35px',
                nombreCliente: '',
                tipo_descuento_global: !!<?php echo $presupuesto['tipo_descuento'] ?>,
                item:{},
                index:-1,
                esDstoGlobal: false,
                dataDescuento:{},
                referencia: '<?php echo $presupuesto->referencia ?>',
                disabledNr:false,
                cc:[],
                fecha:'{{date('Y-m-d', strtotime($presupuesto->fecha))}}',
                ocultar_impuestos:!!<?php echo $presupuesto->ocultar_impuestos ?>,
                ocultar_precios:!!<?php echo $presupuesto->ocultar_precios ?>
            },
            created(){
                this.calcularTotalPorItem();
            },
            methods: {
                redondearSinCeros(numero) {
                    let numeroRedondeado = parseFloat(numero.toFixed(3));
                    return numeroRedondeado.toString().replace(/(\.0*|(?<=(\..*))0*)$/, '');
                },
                agregarCC(){
                    this.cc.push({
                        email: '',
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
                actualizarDescuento(obj){
                    if(this.esDstoGlobal){
                        this.descuento_global=obj['monto'];
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
                editarPresupuesto(){
                    this.editar=!this.editar;
                    let obj = <?php echo json_encode($presupuesto->cliente)?>;
                    if(this.$refs['suggestCliente']){
                        this.$refs['suggestCliente'].agregarCliente(obj);
                    } else {
                        this.agregarCliente(obj)
                    }
                    this.$nextTick(() => {
                        const textareas = this.$refs.textareas;
                        textareas.forEach(textarea => {
                            this.expandirTextarea({ target: textarea });
                        });
                    });
                },
                agregarCliente(obj){
                    this.clienteSeleccionado = obj;
                    this.nombreCliente = this.clienteSeleccionado['num_documento']+' - '+this.clienteSeleccionado['nombre'];
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
                agregarClienteNuevo(obj){
                    if(this.$refs['suggestCliente']){
                        this.$refs['suggestCliente'].agregarCliente(obj);
                    } else {
                        this.agregarCliente(obj)
                    }
                },
                agregarProducto(obj){
                    let productos = this.productosSeleccionados.push(Object.assign({}, obj));
                    let i = productos - 1;
                    let producto = this.productosSeleccionados[i];

                    this.$set(producto, 'prev_precio', (producto['precio']));

                    if(producto['moneda']=='S/' && this.moneda=='USD'){
                        producto['precio']=(producto['precio'] / this.tipoCambio).toFixed(2);
                    } else if(producto['moneda']=='USD' && this.moneda=='S/'){
                            producto['precio']=(producto['precio'] * this.tipoCambio).toFixed(2)
                    }

                    let precio = this.esConIgv?producto['precio']/1.18:Number(producto['precio']);

                    this.$set(producto, 'num_item', i);
                    this.$set(producto, 'cantidad', 1);
                    this.$set(producto, 'tipo_descuento', 0);
                    this.$set(producto, 'porcentaje_descuento', '0.00');
                    this.$set(producto, 'descuento_por_und', 0);
                    this.$set(producto, 'descuento', '0.00');
                    this.$set(producto, 'subtotal', precio);
                    this.$set(producto, 'igv', precio * 0.18);
                    this.$set(producto, 'total', precio * 1.18);

                    if(this.exportacion) {
                        this.$set(producto, 'igv', 0);
                        this.$set(producto, 'total', precio);
                    }

                    this.calcularTotalVenta();
                },
                calcular(index){
                    let producto = this.productosSeleccionados[index];
                    if(typeof index === 'object'){
                        producto = index;
                    }

                    if(producto['descuento'] > 0 && (producto['precio']<=0 || producto['cantidad']<=0)){
                        producto['tipo_descuento']=0;
                        producto['porcentaje_descuento']=0;
                        producto['descuento']=0;
                        producto['descuento_por_und']=0;
                    }

                    let precio = this.esConIgv?producto['precio']/1.18:producto['precio'];

                    let descuento=producto['tipo_descuento']?producto['porcentaje_descuento']/100:producto['descuento'];
                    let monto_descuento=producto['tipo_descuento']?precio*producto['cantidad']*descuento:descuento;
                    producto['descuento'] = monto_descuento;
                    producto['subtotal'] = precio * producto['cantidad'] - monto_descuento;
                    producto['igv'] = Number(producto['subtotal']) * 0.18;
                    producto['total'] = Number(producto['subtotal']) + Number(producto['igv']);

                    producto['porcentaje_descuento'] = producto['descuento'] / (precio * producto['cantidad']) * 100;

                    if(this.exportacion) {
                        producto['igv'] = 0;
                        producto['subtotal'] = producto['precio']*producto['cantidad'] - monto_descuento;
                        producto['total'] = producto['subtotal'];
                    }

                    this.calcularTotalVenta();

                },
                calcularTotalPorItem(){
                    for (let producto of this.productosSeleccionados) {
                        this.calcular(producto);
                    }
                },
                calcularTotalVenta(){

                    let suma_descuentos = 0;
                    let suma_gravadas = 0;

                    for (let producto of this.productosSeleccionados) {
                        suma_gravadas += Number(producto['subtotal']);
                        suma_descuentos += Number(producto['descuento']);
                    }

                    let desc_global = this.tipo_descuento_global ? this.porcentaje_descuento_global/100: this.descuento_global;
                    let monto_descuento = this.tipo_descuento_global ? suma_gravadas * desc_global : desc_global;
                    let gravadas = suma_gravadas - monto_descuento;
                    this.descuentos = (suma_descuentos + Number(monto_descuento)).toFixed(2);
                    this.igv = gravadas * 0.18;

                    if(this.exportacion){
                        this.totalVenta = (gravadas + Number(this.flete) + Number(this.seguro)).toFixed(2);
                        this.subtotal = this.totalVenta;
                        this.igv=0;
                    } else {
                        this.totalVenta = (gravadas + Number(this.igv)).toFixed(2);
                        this.subtotal = gravadas;
                    }

                },
                borrarItemVenta(index){
                    this.productosSeleccionados.splice(index, 1);
                    this.calcularTotalVenta();
                },
                resetModal(){
                    this.buscar = '';
                },
                actualizarPresupuesto(){
                    if (this.validar()) {
                        return;
                    }
                    this.mostrarProgresoGuardado = true;
                    axios.post('{{action('PresupuestoController@update')}}', {
                        'idpresupuesto': <?php echo $presupuesto['idpresupuesto'] ?>,
                        'idcliente': this.clienteSeleccionado['idcliente'],
                        'presupuesto': this.totalVenta,
                        'descuento': this.descuento_global,
                        'porcentaje_descuento':this.porcentaje_descuento_global,
                        'tipo_descuento':this.tipo_descuento_global,
                        'moneda': this.moneda,
                        'fecha': this.fecha,
                        'observaciones':this.observaciones,
                        'atencion':this.atencion,
                        'validez':this.validez,
                        'condicion_pago':this.condicion_pago,
                        'tiempo_entrega':this.tiempo_entrega,
                        'garantia':this.garantia,
                        'impuesto':this.impuesto,
                        'lugar_entrega':this.lugar_entrega,
                        'contacto':this.contacto,
                        'telefonos':this.telefonos,
                        'igv_incluido': this.esConIgv,
                        'exportacion': this.exportacion,
                        'flete':this.flete,
                        'seguro':this.seguro,
                        'incoterm':this.incoterm,
                        'referencia':this.referencia,
                        'ocultar_impuestos':this.ocultar_impuestos,
                        'ocultar_precios':this.ocultar_precios,
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
                            this.alerta('Ha ocurrido un error al procesar la cotización');
                            console.log(error);
                            this.mostrarProgresoGuardado = false;
                        });
                },
                validar(){
                    let errorVenta = 0;
                    let errorDatosVenta = [];
                    let errorString = '';

                    //if (Object.keys(this.clienteSeleccionado).length == 0) errorDatosVenta.push('*Debes ingresar un cliente');

                    if (errorDatosVenta.length) {
                        errorVenta = 1;
                        for (let error of errorDatosVenta) {
                            errorString += error + '\n';
                        }
                        this.alerta(errorString);
                    }

                    return errorVenta;
                },
                cancelar_edicion(){
                    location.reload();
                },
                crear_factura(){
                    this.mostrarProgresoFacturacion = true
                },
                limpiar(){
                    this.clienteSeleccionado = {};
                    this.productosSeleccionados = [];
                    this.numeroGuia = '';
                    this.numeroOc = '';
                    this.moneda = 'S/';
                    this.totalVenta = 0.00;
                    this.subtotal = 0.00;
                    this.igv = 0.00;
                    this.observaciones='';
                    this.calcularTotalVenta();
                    if(this.$refs['suggestCliente']){
                        this.$refs['suggestCliente'].borrarCliente();
                    }
                    if(this.$refs['suggest']){
                        this.$refs['suggest'].limpiar();
                    }
                    this.nombreCliente = "";
                    this.tipoCambio = <?php echo cache('opciones')['tipo_cambio_compra'] ?>;
                    this.descuentos = 0;
                    this.descuento_global= 0;
                    this.exportacion = false;
                    this.flete='0.00';
                    this.seguro='0.00';
                    this.incoterm='';
                    this.referencia='';
                    this.ocultar_impuestos=false;
                    this.ocultar_precios=false;
                },
                enviar_a_correo(){

                    let mails = [];
                    let i = 0;
                    let error = 0;
                    this.cc.map((item) => {
                        if(item['email'].length > 0){
                            if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(item['email'])){
                                mails[i]= item['email'];
                                i++;
                            } else{
                                this.alerta("Hay casillas con dirección de email no válidos");
                                error = 1;
                            }
                        }
                    });

                    if(error){
                        return;
                    }

                    if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(this.mail))
                    {
                        this.mostrarProgresoMail = true;
                        axios.post('{{url('presupuestos/mail')}}',{
                            'mail':this.mail,
                            'destinatarios':JSON.stringify(mails),
                            'mensaje':this.mensaje,
                            'idpresupuesto':this.idpresupuesto,
                            'conCopia':this.conCopia
                        })
                            .then(response => {
                                this.$swal({
                                    position: 'top',
                                    icon: 'success',
                                    title: response.data,
                                    timer: 6000,
                                    toast:true,
                                    confirmButtonColor: '#007bff',
                                }).then(function () {
                                    location.reload(true)
                                });
                            })
                            .catch(error => {
                                this.alerta(error.response.data.mensaje,'error');
                                console.log(error);
                                this.mostrarProgresoMail = false;
                            });
                    } else{
                        this.alerta("El correo electrónico ingresado no es válido");
                    }
                },
                guardar_prev_precio(index){
                    let producto = this.productosSeleccionados[index];
                    producto['prev_precio']=producto.precio
                    producto['moneda']=this.moneda=='S/'?'PEN':'USD';
                },
                imprimir(idpresupuesto){
                    let iframe = document.createElement('iframe');
                    document.body.appendChild(iframe);
                    iframe.style.display = 'none';
                    iframe.onload = () => {
                        setTimeout(() => {
                            iframe.focus();
                            iframe.contentWindow.print();
                        }, 0);
                    };
                    iframe.src = "/presupuestos/imprimir/"+idpresupuesto;
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
                },
                agregar_nr(codigo){
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
                expandirTextarea(event){
                    let textarea = event.target;
                    textarea.style.height = 'auto';
                    textarea.style.height = textarea.scrollHeight + 'px';
                },
            },
            watch:{
                esConIgv(){
                    this.productosSeleccionados.forEach(
                        (valor, indice, array) => {
                            this.calcular(indice);
                        }
                    );
                },
                exportacion(){
                    this.productosSeleccionados.forEach(
                        (valor, indice, array) => {
                            this.calcular(indice);
                        }
                    );
                },
                moneda(moneda){
                    this.productosSeleccionados.forEach(
                        (valor, indice, array) => {
                            if(moneda=='USD'){
                                if(valor['moneda']=='USD'){
                                    valor['precio']=(valor['prev_precio']);
                                }
                                if(valor['moneda']=='PEN' || valor['moneda']=='S/'){
                                    valor['precio']=(valor['prev_precio'] / this.tipoCambio).toFixed(2);
                                }
                            }
                            if(moneda=='S/'){
                                if(valor['moneda']=='USD'){
                                    valor['precio']=((valor['prev_precio']) * this.tipoCambio).toFixed(2);
                                }
                                if(valor['moneda']=='PEN' || valor['moneda']=='S/'){
                                    valor['precio']=(valor['prev_precio']);
                                }

                            }
                            this.calcular(indice);
                        }
                    );
                }
            }

        });
    </script>
@endsection