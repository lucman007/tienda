@extends('layouts.main')
@section('titulo', 'Venta '.$venta->idventa)
@section('contenido')
    @php $agent = new \Jenssegers\Agent\Agent() @endphp
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-sm-12">
                <h3 class="titulo-admin-1">
                    <a href="{{url()->previous()}}"><i class="fas fa-arrow-circle-left"></i></a>
                    {{$venta->facturacion['comprobante'].' '.$venta->facturacion['serie'].'-'.$venta->facturacion['correlativo']}}
                </h3>
                <b-button href="{{action('VentaController@registrar')}}" class="mr-2"  variant="primary"><i class="fas fa-plus"></i> Nuevo comprobante</b-button>
                @if($venta->eliminado == 1)
                    <h3 class="float-right" style="background: #f94b4b;color: white;padding: 5px 20px; margin: 0">VENTA ANULADA</h3>
                @endif
                @if(auth()->user()->acceso == 1)
                    <b-button href="/ventas/regenerar/invoice/{{$venta->idventa}}" class="btn btn-primary"><i class="fas fa-sync-alt"></i> Regenerar</b-button>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 my-3">
                <div class="card">
                    <div class="card-header">
                        Detalle
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4">
                                @php
                                    $tipo_pago = \sysfact\Http\Controllers\Helpers\DataTipoPago::getTipoPago();
                                    $pagos = $venta->pago;
                                @endphp
                                    <strong>Fecha emisión:</strong> {{date("d/m/Y H:i:s",strtotime($venta->fecha))}}
                                <hr>
                                    <strong>Tipo de pago:</strong>
                                @foreach($pagos as $pago)
                                    @php
                                        $index = array_search($pago->tipo, array_column($tipo_pago,'num_val'));
                                    @endphp
                                    {{mb_strtoupper($tipo_pago[$index]['label'])}} @if(count($pagos)>1)({{$venta->codigo_moneda}}{{$pago->monto}})@endif
                                    <br>
                                @endforeach
                                <hr>
                                <strong>Moneda:</strong>
                                @if($venta->codigo_moneda=='S/')
                                    SOLES <hr>
                                @else
                                    DÓLARES <hr>
                                @endif
                            </div>
                            <div class="col-lg-4">
                                @if($venta->facturacion->oc_relacionada)
                                <strong>Orden de compra:</strong> {{$venta->facturacion->oc_relacionada}} <hr>
                                @endif
                                @if($venta->facturacion->guia_fisica)
                                    <strong>Guía:</strong> {{$venta->facturacion->guia_fisica}} <hr>
                                @endif
                                @if($venta->facturacion->retencion == 1)
                                <strong>Tipo de operación:</strong> Operación sujeta a retención <hr>
                                @endif
                                @if($venta->facturacion->codigo_tipo_factura == '1001')
                                    <strong>Tipo de operación:</strong> Operación sujeta a detracción <hr>
                                @endif
                                @if($venta->facturacion->codigo_tipo_documento == '07' || $venta->facturacion->codigo_tipo_documento == '08')
                                <strong>Documento que modifica:</strong> {{$venta->facturacion->num_doc_relacionado}} <hr>
                                <strong>Motivo:</strong> {{$venta->facturacion->descripcion_nota}}
                                    <hr>
                                @endif
                            </div>
                            <div class="col-lg-4">
                                @if($venta->facturacion->codigo_tipo_documento != '30')
                                    <strong>Estado de {{$venta->facturacion['comprobante']}}:</strong> <span class="badge" :class="{'badge-warning':estado=='PENDIENTE',
                                   'badge-success' : estado=='ACEPTADO',
                                   'badge-dark' : (estado=='ANULADO' || estado=='ANULADO (BAJA)'),
                                   'badge-danger' :estado=='RECHAZADO'}">@{{estado}}</span>
                                <hr>
                                @endif
                                @if($venta->facturacion->codigo_tipo_factura == '0200')
                                        <strong>Tipo de factura:</strong> Exportación
                                @endif
                                @if($venta->motivo_rechazo)
                                        <span v-show="estado=='RECHAZADO'"><strong>Motivo de rechazo:</strong> {{$venta->motivo_rechazo}}
                                        <hr>
                                         </span>
                                @endif
                                @if($venta->guia_relacionada)
                                        <strong>Guía:</strong>
                                        @if(isset($venta->guia_relacionada['idguia']))
                                            <a href="/guia/emision/{{$venta->guia_relacionada['idguia']}}">  {{$venta->guia_relacionada['correlativo']}}</a>
                                        @else
                                            {{$venta->guia_relacionada['correlativo']}}
                                        @endif
                                        <span class="badge" :class="{'badge-warning':estado_guia=='PENDIENTE',
                                   'badge-success' : estado_guia=='ACEPTADO',
                                   'badge-dark' : estado_guia=='ANULADO',
                                   'badge-danger' :estado_guia=='RECHAZADO'}">@{{estado_guia}}</span>
                                        @if($venta->guia_relacionada['estado']=='PENDIENTE')
                                            <a href="/guia/correccion/{{$venta->guia_relacionada['idguia']}}"><span class="badge badge-primary"><i class="fas fa-edit"></i> CORREGIR</span></a>
                                        @endif
                                        <hr>
                                        @if($venta->guia_relacionada->estado=='PENDIENTE')
                                            <strong>Mensaje:</strong> @{{mensaje}}  <hr>
                                        @endif
                                        @if($venta->guia_relacionada->estado=='ACEPTADO' && $venta->guia_relacionada->nota)
                                            <strong>Observación:</strong> {{$venta->guia_relacionada->nota}}  <hr>
                                        @endif
                                    @endif
                                    @if($venta->observacion)
                                        <strong>Observacion:</strong> {{$venta->observacion}}
                                        <hr>
                                        @endif
                            </div>
                            <div class="col-lg-8">
                                <strong>Cliente:</strong> {{$venta->cliente['num_documento']}} - {{$venta->persona['nombre']}}
                                <hr>
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
                                    <td>
                                        @{{ producto.nombre }} @{{ producto.detalle.num_serie?'/ SERIE PRODUCTO: '+producto.detalle.num_serie:'' }}<br>
                                        <span style="font-size: 11px; color: #0b870b;" v-for="item in producto.items_kit">+ (@{{ item.cantidad }}) @{{item['nombre']}}<br></span>
                                    </td>
                                    <td style="white-space: break-spaces; width: 250px">@{{ producto.detalle.descripcion}}</td>
                                    <td>@{{ producto.detalle.monto }}</td>
                                    <td>@{{ producto.detalle.cantidad }} @{{ producto.unidad_medida }}<span v-show="producto.detalle.devueltos > 0" class="badge badge-warning w-100">@{{producto.detalle.devueltos}} DEVUELTOS</span></td>
                                    <td>@{{ producto.detalle.tipo_descuento?parseFloat(producto.detalle.porcentaje_descuento)+'%':(Number(producto.detalle.descuento)).toFixed(2)}}</td>
                                    <td>@{{ producto.detalle.subtotal }}</td>
                                    <td>@{{ producto.detalle.igv }}</td>
                                    <td>@{{ producto.detalle.total }}</td>
                                    <td>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="dropdown-divider"></div>
                        @if($venta->tipo_pago==2 && ($venta->facturacion->codigo_tipo_documento == 01 || $venta->facturacion->codigo_tipo_documento == 07))
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover table-sm">
                                            <thead class="bg-custom-green">
                                            <tr>
                                                <th scope="col" style="width: 10px"></th>
                                                <th scope="col" style="width: 200px">Cuota</th>
                                                <th scope="col" style="width: 250px">Monto</th>
                                                <th scope="col" style="width: 250px">M. Neto</th>
                                                <th scope="col" style="width: 90px">F. Venc.</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @php
                                                $i=1
                                            @endphp
                                            @foreach($venta->pago as $item)
                                                @php
                                                    $monto_neto = $item->monto;
                                                    if($venta->facturacion->retencion == 1){
                                                        $r = round($item->monto * 0.03,2);
                                                        $monto_neto =  round($item->monto - $r,2);
                                                    }
                                                    if($venta->facturacion->codigo_tipo_factura == '1001'){
                                                        $detraccion = explode('/',$venta->facturacion->tipo_detraccion);
                                                        $porcentaje_detraccion = number_format($detraccion[1],2);
                                                        $r = round($item->monto * ($porcentaje_detraccion/100),2);
                                                        $monto_neto =  round($item->monto - $r,2);
                                                    }
                                                @endphp
                                                <tr>
                                                    <td></td>
                                                    <td>Cuota{{str_pad($i,3,"0",STR_PAD_LEFT)}}</td>
                                                    <td>{{ $venta->codigo_moneda }} {{$item->monto}}</td>
                                                    <td>{{ $venta->codigo_moneda }} {{number_format($monto_neto,2)}}</td>
                                                    <td>{{date('d/m/Y',strtotime($item->fecha))}}</td>
                                                </tr>
                                                @php
                                                    $i++
                                                @endphp
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                        @endif
                        <div v-show="descuento_global > 0" class="row">
                            <div class="col-lg-2 mt-3">
                                <strong>Descuento global:</strong> @{{ tipo_descuento_global==1?porcentaje_descuento_global+'%':moneda+' '+(Number(descuento_global)).toFixed(2)}}
                                <hr>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 mb-5">
                <div class="card">
                    <div class="card-header">
                        Acciones
                    </div>
                    <div class="card-body pt-5">
                        <div v-if="!(estado=='ANULADO' || estado=='RECHAZADO' || {{$venta->facturacion['codigo_tipo_documento']}}==30)" class="form-group d-flex justify-content-center">
                            <b-button v-if="estado=='PENDIENTE'" class="mb-2" @click="reenviar({{$venta->idventa}},'{{$venta->nombre_fichero}}','{{$venta->facturacion->num_doc_relacionado?$venta->facturacion->num_doc_relacionado:'0'}}')"
                                      variant="success">
                                <i v-show="!mostrarProgreso" class="fas fa-paper-plane"></i>
                                <b-spinner v-show="mostrarProgreso" small label="Loading..." ></b-spinner> Reenviar a sunat

                            </b-button>
                            <b-input-group style="width: 150px" class="mr-2">
                                <b-input-group-prepend>
                                    <b-input-group-text style="height: 35px;">
                                        <i class="fas fa-file"></i>
                                    </b-input-group-text>
                                </b-input-group-prepend>
                                <select v-model="formato" class="custom-select">
                                    <option value="A4">A4</option>
                                    <option value="80_1">80mm</option>
                                </select>
                            </b-input-group>
                            <b-button class="mb-2 mr-2"
                                      @if(json_decode(cache('config')['interfaz'], true)['tipo_impresion'] == 1 && $agent->isDesktop())
                                      target="_blank" :href="'{{url('ventas/imprimir').'/'.$venta->idventa.'?formato='}}'+formato"
                                      @else
                                      @click="imprimir('{{$venta->idventa}}')"
                                      @endif
                                      variant="secondary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16">
                                    <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                                    <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
                                </svg>
                            </b-button>
                            <b-button class="mb-2 mr-2"  :href="'{{url('ventas/descargar').'/'.$venta->idventa.'?formato='}}'+formato" title="Descargar PDF" variant="warning">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-pdf" viewBox="0 0 16 16">
                                    <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                                    <path d="M4.603 14.087a.81.81 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.68 7.68 0 0 1 1.482-.645 19.697 19.697 0 0 0 1.062-2.227 7.269 7.269 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .477.365c.088.164.12.356.127.538.007.188-.012.396-.047.614-.084.51-.27 1.134-.52 1.794a10.954 10.954 0 0 0 .98 1.686 5.753 5.753 0 0 1 1.334.05c.364.066.734.195.96.465.12.144.193.32.2.518.007.192-.047.382-.138.563a1.04 1.04 0 0 1-.354.416.856.856 0 0 1-.51.138c-.331-.014-.654-.196-.933-.417a5.712 5.712 0 0 1-.911-.95 11.651 11.651 0 0 0-1.997.406 11.307 11.307 0 0 1-1.02 1.51c-.292.35-.609.656-.927.787a.793.793 0 0 1-.58.029zm1.379-1.901c-.166.076-.32.156-.459.238-.328.194-.541.383-.647.547-.094.145-.096.25-.04.361.01.022.02.036.026.044a.266.266 0 0 0 .035-.012c.137-.056.355-.235.635-.572a8.18 8.18 0 0 0 .45-.606zm1.64-1.33a12.71 12.71 0 0 1 1.01-.193 11.744 11.744 0 0 1-.51-.858 20.801 20.801 0 0 1-.5 1.05zm2.446.45c.15.163.296.3.435.41.24.19.407.253.498.256a.107.107 0 0 0 .07-.015.307.307 0 0 0 .094-.125.436.436 0 0 0 .059-.2.095.095 0 0 0-.026-.063c-.052-.062-.2-.152-.518-.209a3.876 3.876 0 0 0-.612-.053zM8.078 7.8a6.7 6.7 0 0 0 .2-.828c.031-.188.043-.343.038-.465a.613.613 0 0 0-.032-.198.517.517 0 0 0-.145.04c-.087.035-.158.106-.196.283-.04.192-.03.469.046.822.024.111.054.227.09.346z"/>
                                </svg>
                                PDF
                            </b-button>
                            <b-button class="mb-2 mr-2" href="{{url('ventas/descargar').'/'.$venta->nombre_fichero.'.xml'}}" title="Descargar XML" variant="warning">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-code-slash" viewBox="0 0 16 16">
                                    <path d="M10.478 1.647a.5.5 0 1 0-.956-.294l-4 13a.5.5 0 0 0 .956.294l4-13zM4.854 4.146a.5.5 0 0 1 0 .708L1.707 8l3.147 3.146a.5.5 0 0 1-.708.708l-3.5-3.5a.5.5 0 0 1 0-.708l3.5-3.5a.5.5 0 0 1 .708 0zm6.292 0a.5.5 0 0 0 0 .708L14.293 8l-3.147 3.146a.5.5 0 0 0 .708.708l3.5-3.5a.5.5 0 0 0 0-.708l-3.5-3.5a.5.5 0 0 0-.708 0z"/>
                                </svg>
                                XML
                            </b-button>
                            <b-button  :disabled="estado=='PENDIENTE'" class="mb-2" href="{{url('ventas/descargar').'/'.'R-'.$venta->nombre_fichero.'.cdr'}}" title="Descargar CDR" variant="warning">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-code-slash" viewBox="0 0 16 16">
                                    <path d="M10.478 1.647a.5.5 0 1 0-.956-.294l-4 13a.5.5 0 0 0 .956.294l4-13zM4.854 4.146a.5.5 0 0 1 0 .708L1.707 8l3.147 3.146a.5.5 0 0 1-.708.708l-3.5-3.5a.5.5 0 0 1 0-.708l3.5-3.5a.5.5 0 0 1 .708 0zm6.292 0a.5.5 0 0 0 0 .708L14.293 8l-3.147 3.146a.5.5 0 0 0 .708.708l3.5-3.5a.5.5 0 0 0 0-.708l-3.5-3.5a.5.5 0 0 0-.708 0z"/>
                                </svg>
                                CDR
                            </b-button>
                        </div>
                        <div v-if="{{$venta->facturacion['codigo_tipo_documento']}}==30" class="form-group d-flex justify-content-center">
                            <b-input-group style="width: 150px" class="mr-2">
                                <b-input-group-prepend>
                                    <b-input-group-text style="height: 35px;">
                                        <i class="fas fa-file"></i>
                                    </b-input-group-text>
                                </b-input-group-prepend>
                                <select v-model="formato" class="custom-select">
                                    <option value="A4">A4</option>
                                    <option value="80_1">80mm</option>
                                </select>
                            </b-input-group>
                            <b-button class="mb-2 mr-2"
                                      @if(json_decode(cache('config')['interfaz'], true)['tipo_impresion'] == 1 && $agent->isDesktop())
                                      target="_blank" :href="'{{url('ventas/imprimir').'/'.$venta->idventa.'?formato='}}'+formato"
                                      @else
                                      @click="imprimir({{$venta->idventa}})"
                                      @endif
                                      variant="secondary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16">
                                    <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                                    <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
                                </svg>
                                Imprimir
                            </b-button>
                            <b-button class="mb-2"  :href="'{{url('ventas/descargar').'/'.$venta->idventa.'?formato='}}'+formato" title="Descargar PDF" variant="warning">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-pdf" viewBox="0 0 16 16">
                                    <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                                    <path d="M4.603 14.087a.81.81 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.68 7.68 0 0 1 1.482-.645 19.697 19.697 0 0 0 1.062-2.227 7.269 7.269 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .477.365c.088.164.12.356.127.538.007.188-.012.396-.047.614-.084.51-.27 1.134-.52 1.794a10.954 10.954 0 0 0 .98 1.686 5.753 5.753 0 0 1 1.334.05c.364.066.734.195.96.465.12.144.193.32.2.518.007.192-.047.382-.138.563a1.04 1.04 0 0 1-.354.416.856.856 0 0 1-.51.138c-.331-.014-.654-.196-.933-.417a5.712 5.712 0 0 1-.911-.95 11.651 11.651 0 0 0-1.997.406 11.307 11.307 0 0 1-1.02 1.51c-.292.35-.609.656-.927.787a.793.793 0 0 1-.58.029zm1.379-1.901c-.166.076-.32.156-.459.238-.328.194-.541.383-.647.547-.094.145-.096.25-.04.361.01.022.02.036.026.044a.266.266 0 0 0 .035-.012c.137-.056.355-.235.635-.572a8.18 8.18 0 0 0 .45-.606zm1.64-1.33a12.71 12.71 0 0 1 1.01-.193 11.744 11.744 0 0 1-.51-.858 20.801 20.801 0 0 1-.5 1.05zm2.446.45c.15.163.296.3.435.41.24.19.407.253.498.256a.107.107 0 0 0 .07-.015.307.307 0 0 0 .094-.125.436.436 0 0 0 .059-.2.095.095 0 0 0-.026-.063c-.052-.062-.2-.152-.518-.209a3.876 3.876 0 0 0-.612-.053zM8.078 7.8a6.7 6.7 0 0 0 .2-.828c.031-.188.043-.343.038-.465a.613.613 0 0 0-.032-.198.517.517 0 0 0-.145.04c-.087.035-.158.106-.196.283-.04.192-.03.469.046.822.024.111.054.227.09.346z"/>
                                </svg>
                                PDF
                            </b-button>
                        </div>
                        @if($venta->guia_relacionada)
                            <div  class="form-group text-center">
                                <p>Guía electrónica relacionada:</p>
                                @if(isset($venta->guia_relacionada['idguia']))
                                <b-button v-if="estado_guia=='PENDIENTE'" class="mb-2" href="/guia/correccion/{{$venta->guia_relacionada['idguia']}}"
                                          variant="primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                        <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                        <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z"/>
                                    </svg>
                                    Corregir guía
                                </b-button>
                                    <b-button v-show="estado=='PENDIENTE'" class="mb-2" @click="enviar_guia('{{$venta->guia_relacionada->ticket}}','{{$venta->guia_relacionada->idguia}}')"
                                              variant="success">
                                        <i v-show="!mostrarProgresoEnvio" class="fas fa-paper-plane"></i>
                                        <b-spinner v-show="mostrarProgresoEnvio" small label="Loading..." ></b-spinner>

                                    </b-button>
                                @endif
                                <b-button class="mb-2"
                                          @if(json_decode(cache('config')['interfaz'], true)['tipo_impresion'] == 1  && $agent->isDesktop())
                                          target="_blank" href="{{url('guia/imprimir').'/'.$venta->guia_relacionada['idguia']}}"
                                          @else
                                          @click="imprimir('{{$venta->guia_relacionada['idguia']}}',true)"
                                          @endif
                                          variant="secondary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16">
                                        <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                                        <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
                                    </svg>
                                </b-button>
                                <b-button class="mb-2" href="{{url('ventas/descargar').'/'.$venta->guia_relacionada['idguia'].'?guia=true'}}" title="Descargar PDF" variant="warning">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-pdf" viewBox="0 0 16 16">
                                        <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                                        <path d="M4.603 14.087a.81.81 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.68 7.68 0 0 1 1.482-.645 19.697 19.697 0 0 0 1.062-2.227 7.269 7.269 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .477.365c.088.164.12.356.127.538.007.188-.012.396-.047.614-.084.51-.27 1.134-.52 1.794a10.954 10.954 0 0 0 .98 1.686 5.753 5.753 0 0 1 1.334.05c.364.066.734.195.96.465.12.144.193.32.2.518.007.192-.047.382-.138.563a1.04 1.04 0 0 1-.354.416.856.856 0 0 1-.51.138c-.331-.014-.654-.196-.933-.417a5.712 5.712 0 0 1-.911-.95 11.651 11.651 0 0 0-1.997.406 11.307 11.307 0 0 1-1.02 1.51c-.292.35-.609.656-.927.787a.793.793 0 0 1-.58.029zm1.379-1.901c-.166.076-.32.156-.459.238-.328.194-.541.383-.647.547-.094.145-.096.25-.04.361.01.022.02.036.026.044a.266.266 0 0 0 .035-.012c.137-.056.355-.235.635-.572a8.18 8.18 0 0 0 .45-.606zm1.64-1.33a12.71 12.71 0 0 1 1.01-.193 11.744 11.744 0 0 1-.51-.858 20.801 20.801 0 0 1-.5 1.05zm2.446.45c.15.163.296.3.435.41.24.19.407.253.498.256a.107.107 0 0 0 .07-.015.307.307 0 0 0 .094-.125.436.436 0 0 0 .059-.2.095.095 0 0 0-.026-.063c-.052-.062-.2-.152-.518-.209a3.876 3.876 0 0 0-.612-.053zM8.078 7.8a6.7 6.7 0 0 0 .2-.828c.031-.188.043-.343.038-.465a.613.613 0 0 0-.032-.198.517.517 0 0 0-.145.04c-.087.035-.158.106-.196.283-.04.192-.03.469.046.822.024.111.054.227.09.346z"/>
                                    </svg>
                                    PDF
                                </b-button>
                                <b-button class="mb-2" href="{{url('ventas/descargar').'/'.$venta->nombre_guia.'.xml'}}" title="Descargar XML" variant="warning">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-code-slash" viewBox="0 0 16 16">
                                        <path d="M10.478 1.647a.5.5 0 1 0-.956-.294l-4 13a.5.5 0 0 0 .956.294l4-13zM4.854 4.146a.5.5 0 0 1 0 .708L1.707 8l3.147 3.146a.5.5 0 0 1-.708.708l-3.5-3.5a.5.5 0 0 1 0-.708l3.5-3.5a.5.5 0 0 1 .708 0zm6.292 0a.5.5 0 0 0 0 .708L14.293 8l-3.147 3.146a.5.5 0 0 0 .708.708l3.5-3.5a.5.5 0 0 0 0-.708l-3.5-3.5a.5.5 0 0 0-.708 0z"/>
                                    </svg>
                                    XML
                                </b-button>
                            </div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger" style="text-align: center">{{$errors->first()}}</div>
                        @endif
                        <div class="col-lg-12 mt-5">
                            <div class="row">
                                <div class="col-lg-6 mb-4 mb-lg-0">
                                    @php
                                        $codigos_pais = \sysfact\Http\Controllers\Helpers\DataGeneral::getCodigoPais();
                                    @endphp
                                    <input-whatsapp :text="'{{$venta->text_whatsapp}}'" :codigos="{{json_encode($codigos_pais)}}" :link="'{{$agent->isDesktop()?'https://web.whatsapp.com':'https://api.whatsapp.com'}}'"></input-whatsapp>
                                </div>
                                <div class="col-lg-6">
                                    <b-input-group>
                                        <b-input-group-prepend>
                                            <b-input-group-text>
                                                <i class="fas fa-envelope"></i>
                                            </b-input-group-text>
                                        </b-input-group-prepend>
                                        <input v-model="mail" type="text" class="form-control" placeholder="Enviar por email" >
                                        <b-input-group-append>
                                            <b-button :disabled="mail.length==0" @click="enviar_a_correo" variant="primary" >
                                                <i v-show="!mostrarProgresoMail" class="fas fa-paper-plane"></i>
                                                <b-spinner v-show="mostrarProgresoMail" small label="Loading..." ></b-spinner>
                                            </b-button>
                                        </b-input-group-append>
                                    </b-input-group>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <button @click="agregarCC" class="btn btn-primary float-right mt-2"><i class="fas fa-user-plus"></i></button>
                                        </div>
                                        <div class="col-lg-12 mt-2" v-for="item,index in cc" :key="index">
                                            <div class="form-group mb-2">
                                                <input v-model="item.email" type="email" class="form-control" placeholder="Con copia a...">
                                            </div>
                                        </div>
                                    </div>
                                    <b-form-checkbox v-model="conCopia" switch size="sm" class="my-2 text-center">
                                        Enviarme una copia
                                    </b-form-checkbox>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            @php
                                $mail = json_decode($venta->datos_adicionales, true)['mail'];
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
            <div class="col-lg-4 mb-5">
                <div class="card">
                    <div class="card-header">
                        Totales e impuestos
                    </div>
                    <div class="card-body">
                        <table style="width:100%;">
                            @if($venta->facturacion['total_gratuitas'] > 0)
                            <tr>
                                <td style="width: 50%">OP. GRATUITAS:</td>
                                <td>{{ $venta->codigo_moneda }} {{ $venta->facturacion['total_gratuitas'] }}</td>
                            </tr>
                            @endif
                            @if($venta->facturacion['total_inafectas'] > 0)
                            <tr>
                                <td style="width: 50%">OP. INAFECTAS:</td>
                                <td>{{ $venta->codigo_moneda }} {{ $venta->facturacion['total_inafectas'] }}</td>
                            </tr>
                            @endif
                            @if($venta->facturacion['total_exoneradas'] > 0)
                            <tr>
                                <td style="width: 50%">OP. EXONERADAS:</td>
                                <td>{{ $venta->codigo_moneda }} {{ $venta->facturacion['total_exoneradas'] }}</td>
                            </tr>
                            @endif
                            @if($venta->facturacion['total_descuentos'] > 0)
                                <tr>
                                    <td style="width: 50%">DESCUENTOS:</td>
                                    <td>{{ $venta->codigo_moneda }} {{ $venta->facturacion['total_descuentos'] }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td style="width: 50%">OP. GRAVADAS:</td>
                                <td>{{ $venta->codigo_moneda }} {{ $venta->facturacion['total_gravadas'] }}</td>
                            </tr>
                            <tr>
                                <td style="width: 50%">IGV:</td>
                                <td>{{ $venta->codigo_moneda }} {{ $venta->facturacion['igv'] }}</td>
                            </tr>
                        </table>
                        <p class="p-2 mt-2 total-venta" style="margin-top:140px;">{{ $venta->codigo_moneda }} {{ $venta->total_venta }}</p>
                        @if($venta->facturacion->retencion == 1)
                            <div class="container">
                                <div class="row">
                                    <span class="alert alert-info col-lg-12" role="alert">RETENCIÓN (3%): {{ $venta->codigo_moneda }} {{$venta->retencion}}</span>
                                    @if($venta->tipo_pago == 2)
                                    <span class="alert alert-info col-lg-12" role="alert">MONTO NETO PEND. DE PAGO: {{$venta->codigo_moneda}} {{$venta->monto_menos_retencion}}</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                        @if($venta->facturacion->codigo_tipo_factura == '1001')
                            @php
                                $detraccion = explode('/',$venta->facturacion->tipo_detraccion);
                            @endphp
                            <div class="container">
                                <div class="row">
                                    <span class="alert alert-info col-lg-12" role="alert">DETRACCIÓN ({{$detraccion[1]}}%): {{ $venta->codigo_moneda }} {{$venta->detraccion}}</span>
                                    @if($venta->tipo_pago == 2)
                                        <span class="alert alert-info col-lg-12" role="alert">MONTO NETO PEND. DE PAGO: {{$venta->codigo_moneda}} {{$venta->monto_menos_detraccion}}</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>

        let app = new Vue({
            el: '.app',
            data: {
                mostrarProgreso: false,
                mostrarProgresoMail: false,
                estado: '<?php echo $venta->facturacion['estado'] ?>',
                estado_guia: '<?php echo $venta->guia_relacionada['estado'] ?>',
                productosSeleccionados: <?php echo $venta['productos'] ?>,
                porcentaje_descuento_global:'<?php echo $venta->facturacion->porcentaje_descuento_global * 100?>',
                descuento_global:'<?php echo $venta->facturacion->descuento_global?>',
                tipo_descuento_global:'<?php echo $venta->facturacion->tipo_descuento?>',
                moneda: '<?php echo $venta['codigo_moneda']?>',
                mail:"<?php echo $venta->persona->correo ?>",
                conCopia:true,
                whatsapp: '',
                cc:[],
                mensaje:'<?php echo html_entity_decode($venta->guia_relacionada->response??'') ?>',
                mostrarProgresoEnvio:false,
                formato:'A4',
            },
            created(){
                if('<?php echo $venta->facturacion->estado ?>' == 'PENDIENTE' && ('<?php echo basename(url()->previous()) ?>').includes('facturacion')){
                    this.enviar_documentos(<?php echo $venta->idventa ?> ,"<?php echo $venta->nombre_fichero ?>","<?php echo $venta->facturacion->num_doc_relacionado?$venta->facturacion->num_doc_relacionado:'0'?>");
                    if('<?php echo $venta->guia_relacionada?>'){
                        this.enviar_guia("<?php echo $venta->guia_relacionada['ticket'] ?>",<?php echo $venta->guia_relacionada['idguia'] ?>);
                    }
                }
                if(('<?php echo url()->previous() ?>').includes('correccion')){
                    this.enviar_guia("<?php echo $venta->guia_relacionada['ticket'] ?>",<?php echo $venta->guia_relacionada['idguia'] ?>);
                }

                this.control_stock();
            },
            methods: {
                control_stock(){
                    if(('<?php echo basename(url()->previous()) ?>').includes('facturacion')){
                        axios.get('/helper/notificar-estado-stock' + '/' + '{{$venta->idventa}}')
                            .then(() => {
                                this.$eventBus.$emit('actualizar-notificaciones');
                            })
                            .catch(error => {
                                alert('Ha ocurrido un error al obtener el stock');
                                console.log(error);
                            });
                    }
                },
                agregarCC(){
                    this.cc.push({
                        email: '',
                    });
                },
                reenviar(idventa,nombre_comprobante, doc_relacionado){
                    this.mostrarProgreso = true;
                    axios.get('{{url('ventas/reenviar')}}' + '/' + idventa + '/' + nombre_comprobante + '/' + doc_relacionado)
                        .then(response => {
                            this.$swal({
                                position: 'top',
                                icon: 'info',
                                title: response.data,
                                timer: 6000,
                                toast:true,
                                confirmButtonColor: '#007bff',
                            }).then(()=>{
                                location.reload();
                                this.mostrarProgreso = false;
                            });

                        })
                        .catch(error => {
                            this.alerta('Error al reenviar los documentos','error');
                            console.log(error);
                            this.mostrarProgreso = false;
                        });
                },
                enviar_documentos(idventa,nombre_comprobante, doc_relacionado){

                    this.mostrarProgreso = true;
                    axios.get('{{url('ventas/reenviar')}}' + '/' + idventa + '/' + nombre_comprobante + '/' + doc_relacionado)
                        .then(response =>  {

                            let mensaje = response.data[0];
                            let titulo;
                            let color;
                            let tiempo;
                            if((mensaje.toLowerCase()).includes('aceptada') || (mensaje.toLowerCase()).includes('aceptado')){
                                titulo = 'Comprobante enviado con éxito';
                                color = 'primary';
                                tiempo = 5000;
                                this.$eventBus.$emit('count-comprobantes');
                            } else if((mensaje.toLowerCase()).includes('rechazado')) {
                                titulo = 'El comprobante ha sido rechazado y no es válido';
                                color = 'danger';
                                tiempo = 10000;
                                this.$eventBus.$emit('actualizar-notificaciones');
                            } else {
                                titulo = 'Comprobante pendiente de envío';
                                color = 'warning';
                                tiempo = 10000;
                                this.$eventBus.$emit('actualizar-notificaciones');
                            }

                            this.$bvToast.toast(mensaje, {
                                title: titulo,
                                variant: color,
                                solid: true,
                                autoHideDelay: tiempo,
                            });
                            this.estado = response.data[1];
                            this.mostrarProgreso = false;
                        })
                        .catch(error =>  {
                            alert('error');
                            console.log(error);
                            this.mostrarProgreso = false;
                        });
                },
                enviar_guia(ticket, idguia){
                    this.mostrarProgresoEnvio = true;
                    axios.post('{{url('guia/consultar-ticket')}}',{
                        'ticket':ticket,
                        'idguia':idguia,
                        'file':"<?php echo $venta->nombre_guia ?>",
                    })
                        .then(response =>  {
                            this.$bvToast.toast(response.data[0], {
                                title: 'Envío de guía',
                                variant: 'primary',
                                solid: true
                            });
                            this.estado_guia = response.data[1];
                            this.mensaje = response.data[0];
                            this.mostrarProgresoEnvio = false;
                        })
                        .catch(error =>  {
                            alert('error');
                            console.log(error);
                            this.mostrarProgresoEnvio = false;
                        });
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

                    if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(this.mail)){
                        let file_fact= '<?php echo $venta->nombre_fichero ?>';
                        let file_guia='<?php echo $venta->nombre_guia ?>';
                        if('<?php echo $venta->guia_relacionada['correlativo'] ?>'=='') file_guia=null;
                        this.mostrarProgresoMail = true;

                        let data = {
                            'mail':this.mail,
                            'conCopia':this.conCopia,
                            'destinatarios':JSON.stringify(mails),
                            'idventa':'{{$venta->idventa}}',
                            'idguia':'{{$venta->guia_relacionada['idguia']??-1}}'
                        };

                        @if($venta->facturacion->codigo_tipo_documento == 30)
                            data['recibo'] = '{{$venta->facturacion->serie}}-{{$venta->facturacion->correlativo}}';
                            this.enviar(data);
                        @else

                        data['factura']=file_fact;
                        data['guia']=file_guia;

                        axios.post('{{url('ventas/verificar-cdr-mail')}}',data)
                        .then(response => {
                            if(response.data===1){
                                this.enviar(data)
                            } else {
                                this.$swal({
                                    heightAuto: false,
                                    position: 'top',
                                    icon: 'question',
                                    html: response.data,
                                    showCancelButton: true,
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#d33',
                                    cancelButtonText: 'Cancelar',
                                    confirmButtonText: 'Enviar'
                                }).then((result) => {
                                    if(result.isConfirmed){
                                        this.enviar(data)
                                    } else{
                                        this.mostrarProgresoMail = false;
                                    }
                                }).catch(error=>{
                                    alert(error)
                                });
                            }
                        })
                        .catch(error => {
                            alert('ocurrio un error');
                        })

                        @endif

                    } else{
                        this.alerta("El correo electrónico ingresado no es válido");
                    }
                },
                enviar(data){
                    axios.post('{{url('ventas/mail')}}',data)
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
                },
                imprimir(idventa, esGuia){
                    let src = "/ventas/imprimir/"+idventa+"?formato="+this.formato;
                    if(esGuia){
                        src = "/guia/imprimir/"+idventa+"?formato="+this.formato;
                    }

                    @if(!$agent->isDesktop())
                        @if(isset(json_decode(cache('config')['interfaz'], true)['rawbt']) && json_decode(cache('config')['interfaz'], true)['rawbt'])
                            axios.get(src+'&rawbt=true')
                            .then(response => {
                                window.location.href = response.data;
                            })
                            .catch(error => {
                                alert('Ha ocurrido un error al imprimir con RawBT.');
                                console.log(error);
                            });
                        @else
                            window.open(src, '_blank');
                        @endif
                    @else
                        let iframe = document.createElement('iframe');
                        document.body.appendChild(iframe);
                        iframe.style.display = 'none';
                        iframe.onload = () => {
                            setTimeout(() => {
                                iframe.focus();
                                iframe.contentWindow.print();
                            }, 0);
                        };
                        iframe.src = src;
                    @endif
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