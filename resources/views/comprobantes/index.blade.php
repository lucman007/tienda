@extends('layouts.main')
@section('titulo', 'Comprobantes')
@section('contenido')
    @php $agent = new \Jenssegers\Agent\Agent() @endphp
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1">Comprobantes</h3>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-2 mb-2 mb-lg-0">
                <b-dropdown variant="primary">
                    <template #button-content>
                        <i class="far fa-file-alt"></i> Comprobantes
                    </template>
                    <b-dropdown-item href="{{action('ComprobanteController@comprobantes')}}"><i class="fas fa-file-alt"></i> Comprobantes</b-dropdown-item>
                    <b-dropdown-item href="{{action('ComprobanteController@anular')}}"><i class="fas fa-ban"></i> Anulaciones</b-dropdown-item>
                    <b-dropdown-item href="{{action('ComprobanteController@consulta')}}"><i class="fas fa-external-link-square-alt"></i> Consulta CDR</b-dropdown-item>
                    <b-dropdown-item href="{{action('ComprobanteController@resumenes_enviados')}}"><i class="fas fa-external-link-square-alt"></i> Consulta anulación</b-dropdown-item>
                    <b-dropdown-item href="{{action('ReporteController@reporte_ventas')}}"><i class="fas fa-chart-line"></i> Reporte de ventas</b-dropdown-item>
                    <b-dropdown-item href="{{action('GuiaController@index')}}"><i class="fas fa-dolly"></i> Guía de remisión</b-dropdown-item>
                </b-dropdown>
            </div>
            <div class="col-lg-10">
                <div class="row">
                    <div class="col-lg-3 mb-2 mb-lg-0">
                        <b-input-group>
                            <b-input-group-prepend>
                                <b-input-group-text>
                                    <i class="fas fa-filter"></i>
                                </b-input-group-text>
                            </b-input-group-prepend>
                            <select v-model="filtro" class="custom-select">
                                <option value="fecha">Fecha</option>
                                <option value="documento">Comprobante</option>
                                <option value="tipo-de-pago">Tipo de pago</option>
                                <option value="moneda">Moneda</option>
                                <option value="estado">Estado</option>
                                <option value="cliente">Cliente</option>
                            </select>
                        </b-input-group>
                    </div>
                    <div class="col-lg-2 mb-2 mb-lg-0" v-show="filtro=='documento'">
                        <b-input-group>
                            <b-input-group-prepend>
                                <b-input-group-text>
                                    <i class="fas fa-check"></i>
                                </b-input-group-text>
                            </b-input-group-prepend>
                            <select @change="filtrar" v-model="buscar" class="custom-select">
                                <option value="n">Seleccionar</option>
                                <option value="boleta">Boleta</option>
                                <option value="factura">Factura</option>
                                <option value="nota-de-credito">Nota de crédito</option>
                                <option value="nota-de-debito">Nota de débito</option>
                                <option value="recibo">Recibo</option>
                            </select>
                        </b-input-group>
                    </div>
                    <div class="col-lg-2 mb-2 mb-lg-0" v-show="filtro=='tipo-de-pago'">
                        <b-input-group>
                            <b-input-group-prepend>
                                <b-input-group-text>
                                    <i class="fas fa-check"></i>
                                </b-input-group-text>
                            </b-input-group-prepend>
                            <select @change="filtrar" v-model="buscar" class="custom-select">
                                <option value="n">Seleccionar</option>
                                @php
                                $tipo_pago = \sysfact\Http\Controllers\Helpers\DataTipoPago::getTipoPago();
                                @endphp
                                @foreach($tipo_pago as $pago)
                                    @if($pago['num_val'] != 4)
                                        <option value="{{$pago['text_val']}}">{{$pago['label']}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </b-input-group>
                    </div>
                    <div class="col-lg-2 mb-2 mb-lg-0" v-show="filtro=='moneda'">
                        <b-input-group>
                            <b-input-group-prepend>
                                <b-input-group-text>
                                    <i class="fas fa-check"></i>
                                </b-input-group-text>
                            </b-input-group-prepend>
                            <select @change="filtrar" v-model="buscar" class="custom-select">
                                <option value="n">Seleccionar</option>
                                <option value="pen">PEN</option>
                                <option value="usd">USD</option>
                            </select>
                        </b-input-group>
                    </div>
                    <div class="col-lg-2 mb-2 mb-lg-0" v-show="filtro=='estado'">
                        <b-input-group>
                            <b-input-group-prepend>
                                <b-input-group-text>
                                    <i class="fas fa-check"></i>
                                </b-input-group-text>
                            </b-input-group-prepend>
                            <select @change="filtrar" v-model="buscar" class="custom-select">
                                <option value="n">Seleccionar</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="aceptado">Aceptado</option>
                                <option value="anulado">Anulado</option>
                                <option value="rechazado">Rechazado</option>
                            </select>
                        </b-input-group>
                    </div>
                    <div class="col-lg-5 form-group mb-2 mb-lg-0" v-show="filtro=='cliente'">
                        <b-input-group>
                            <b-input-group-prepend>
                                <b-input-group-text>
                                    <i class="fas fa-user"></i>
                                </b-input-group-text>
                            </b-input-group-prepend>
                            <input v-model="buscar" type="text" class="form-control" placeholder="Buscar..." @keyup="buscar_cliente">
                            <b-input-group-append>
                                <b-button :disabled="buscar.length==0" @click="filtrar" variant="primary" ><i class="fas fa-search"></i></b-button>
                            </b-input-group-append>
                        </b-input-group>
                    </div>
                    <div class="col-lg-3 form-group">
                        <range-calendar :inicio="desde + ' 00:00:00'" :fin="hasta + ' 00:00:00'" v-on:setparams="setParams"></range-calendar>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 mt-4">
                <div class="card">
                    <div class="card-header">
                        Lista de comprobantes
                    </div>
                    <div class="card-body">
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col">Venta</th>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Cliente</th>
                                    @if(!$agent->isDesktop())
                                        <th scope="col">Importe</th>
                                    @else
                                        <th scope="col">Importe</th>
                                        <th scope="col">Moneda</th>
                                    @endif
                                    <th scope="col">Pago</th>
                                    <th scope="col">Ord. Compra</th>
                                    <th scope="col" style="width: 12%">Comprobante</th>
                                    <th scope="col">Estado</th>
                                    <th scope="col">Opciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($ventas))
                                    @foreach($ventas as $venta)
                                        <tr :class="{'td-anulado':'{{$venta->facturacion->estado}}'=='ANULADO' || '{{$venta->facturacion->estado}}'=='ANULADO (BAJA)' || '{{$venta->facturacion->estado}}'=='MODIFICADO'}" @if(!$agent->isDesktop()) onclick="location.href='{{url('facturacion/documento').'/'.$venta->idventa}}'" @endif>
                                            <td></td>
                                            <td style="width: 5%">{{$venta->idventa}}</td>
                                            @if(!$agent->isDesktop())
                                                <td style="width: 15%">{{date("d-m-Y",strtotime($venta->fecha))}}</td>
                                            @else
                                                <td style="width: 15%">{{date("d-m-Y H:i:s",strtotime($venta->fecha))}}</td>
                                            @endif
                                            <td>{{$venta->cliente->persona->nombre}} {{$venta->personaAlias?'('.$venta->personaAlias->nombre.')':''}}</td>
                                            @if(!$agent->isDesktop())
                                                <td>{{$venta->facturacion->codigo_moneda=='PEN'?'S/':'USD'}} {{$venta->total_venta}}</td>
                                            @else
                                                <td>{{$venta->total_venta}}</td>
                                                <td>{{$venta->facturacion->codigo_moneda}}</td>
                                            @endif
                                            <td>
                                                @php
                                                    $tipo_pago = \sysfact\Http\Controllers\Helpers\DataTipoPago::getTipoPago();
                                                    $pagos = $venta->pago;
                                                @endphp
                                                @foreach($pagos as $pago)
                                                    @php
                                                        $index = array_search($pago->tipo, array_column($tipo_pago,'num_val'));
                                                    @endphp
                                                    <span @if($filtros['filtro'] == 'tipo-de-pago' && $venta->tipo_pago == 4 && $filtros['buscar'] == $tipo_pago[$index]['text_val'])
                                                          style="font-weight: bold"
                                                          @elseif($filtros['filtro'] == 'tipo-de-pago' && $venta->tipo_pago == 4 && $filtros['buscar'] != $tipo_pago[$index]['text_val']) style="opacity: 0.6"
                                                            @endif>
                                                                {{mb_strtoupper($tipo_pago[$index]['label'])}} {{$pago->monto}}
                                                            </span>
                                                    <br>
                                                @endforeach
                                                @if($venta->estado_credito)
                                                    <a href="{{url('creditos/editar/'.$venta->idventa)}}"><span class="badge badge-secondary">{{$venta->estado_credito}}</span></a>
                                                @endif
                                            </td>
                                            <td>{{$venta->facturacion->oc_relacionada}}</td>
                                            <td><span class="badge {{$venta->badge_class_documento}} badge_doc">{{$venta->facturacion->serie}}-{{$venta->facturacion->correlativo}}</span><br>
                                                <span class="badge badge-info badge_doc">{{$venta->guia_relacionada['correlativo']}}</span>
                                            </td>
                                            <td>
                                                <span class="badge {{$venta->badge_class}}">{{$venta->facturacion->estado}}</span><br>
                                                @if($venta->guia_relacionada)
                                                    <span class="badge {{$venta->badge_class_guia}}">{{$venta->guia_relacionada['estado']}}</span>
                                                @endif
                                            </td>
                                            <td class="botones-accion" style="width: 10%" @click.stop>
                                                <a href="{{url('facturacion/documento').'/'.$venta->idventa}}">
                                                    <button class="btn btn-info" title="Ver detalle de venta">
                                                        <i class="fas fa-folder-open"></i>
                                                    </button>
                                                </a>
                                                <button class="btn btn-success" v-if="'{{$venta->facturacion->estado}}'=='PENDIENTE'"
                                                        @click="reenviar({{$venta->idventa}},'{{$venta->nombre_fichero}}','{{$venta->facturacion->num_doc_relacionado?$venta->facturacion->num_doc_relacionado:'0'}}')"
                                                        title="Reenviar a Sunat">
                                                    <i :id="'icon_{{$venta->idventa}}'" class="fas fa-paper-plane d-inline-block"></i>
                                                    <span :id="'spinner_{{$venta->idventa}}'" class="d-none"><b-spinner small label="Loading..." ></b-spinner></span>
                                                </button>
                                                @if($venta->facturacion->codigo_tipo_documento == '30')
                                                <button @click="eliminar({{$venta->idventa}})"
                                                        class="btn btn-danger" title="Eliminar"><i class="fas fa-trash-alt"></i>
                                                </button>
                                                @elseif(($venta->facturacion->codigo_tipo_documento == '01' || $venta->facturacion->codigo_tipo_documento == '03') && $venta->facturacion->estado=='ACEPTADO')
                                                    <b-button id="btn_anular_{{$venta->idventa}}" variant="danger" @click="abrir_modal('anulacion',{{$venta}})"><i class="fas fa-times"></i>
                                                    </b-button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="text-center">
                                        <td colspan="11">No hay datos que mostrar</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                        {{$ventas->links('layouts.paginacion')}}
                    </div>
                </div>
                <br>
                Notas: <br>
                -Sólo se pueden eliminar las ventas guardadas sin comprobante (serie REC-000). <br>
                -Sólo puede anular facturas y boletas con estado aceptado.
            </div>
        </div>
    </div>
    <!--INICIO MODAL ANULACION -->
    <b-modal size="md" id="modal-anulacion" ref="modal-anulacion" @ok="" @hidden="resetModalAnulacion">
    <template slot="modal-title">
        Anular comprobante
    </template>
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <label>Motivo de anulación</label>
                <input class="form-control" v-model="motivo_anulacion" type="text">
            </div>
            <div v-if="anulacionResponse" class="col-lg-12 mt-3 text-center">
                <i v-show="anulacion.success" class="far fa-check-circle text-success" style="font-size: 70px;"></i>
                <i v-show="!anulacion.success" class="fas fa-exclamation-circle text-danger" style="font-size: 70px;"></i>
                <p style="white-space: break-spaces;">@{{ anulacion.mensaje }}</p>
                <button @click="imprimir(anulacion.idventa)" v-show="anulacion.success" class="btn btn-success"><i class="fas fa-print"></i> Imprimir Nota de Crédito</button>
            </div>
        </div>
    </div>
    <template #modal-footer="{ ok, cancel}">
        <b-button variant="secondary" @click="cancel()">
            Cancelar
        </b-button>
        <b-button :disabled="anulacionDisabled"  variant="primary" @click="anular_comprobante">
            <span v-show="mostrarProgresoGuardado" ><b-spinner small label="Loading..." ></b-spinner> Procesando...</span>
            <span v-show="!mostrarProgresoGuardado">Procesar</span>
        </b-button>
    </template>
    </b-modal>
    <!--FIN MODAL ANULACION -->
@endsection
@section('script')
    <script>
        let app = new Vue({
            el: '.app',
            data: {
                filtro:'{{$filtros['filtro']}}',
                desde:'{{$filtros['desde']}}',
                hasta:'{{$filtros['hasta']}}',
                buscar:'{{$filtros['buscar']}}',
                motivo_anulacion:'',
                anulacionResponse: false,
                anulacionSuccess:true,
                anulacion:{},
                anulacionDisabled:false,
                mostrarProgresoGuardado: false,
            },
            methods: {
                setParams(obj){
                    let d1 = new Date(obj.startDate).toISOString().split('T')[0];
                    let d2 = new Date(obj.endDate).toISOString().split('T')[0];
                    this.desde=d1;
                    this.hasta=d2;
                    this.filtrar();
                },
                filtrar(){
                    if(this.buscar!=='n'){
                        window.location.href='/comprobantes/'+this.desde+'/'+this.hasta+'?filtro='+this.filtro+'&buscar='+this.buscar;
                    }
                },
                buscar_cliente(event){
                    switch (event.code) {
                        case 'Enter':
                        case 'NumpadEnter':
                            this.filtrar();
                            break;
                    }
                },
                imprimir(file_or_id){
                    @if(!$agent->isDesktop())
                        let src = "{{url('/ventas/imprimir').'/'}}"+file_or_id;
                        @if(isset(json_decode(cache('config')['interfaz'], true)['rawbt']) && json_decode(cache('config')['interfaz'], true)['rawbt'])
                            axios.get(src+'?rawbt=true')
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
                        iframe.src = "/ventas/imprimir/"+file_or_id;
                        iframe.onload = function() {
                            setTimeout(function() {
                                iframe.focus();
                                iframe.contentWindow.print();
                            }, 0);
                        };
                    @endif
                },
                reenviar(idventa,nombre_comprobante, doc_relacionado){
                    if(confirm('¿Está seguro de enviar el comprobante a SUNAT?')){
                        let icon = document.getElementById("icon_"+idventa);
                        icon.classList.remove('d-inline-block');
                        icon.classList.add('d-none');

                        let spinner = document.getElementById("spinner_"+idventa);
                        spinner.classList.remove('d-none');
                        spinner.classList.add('d-inline-block');

                        axios.get('{{url('ventas/reenviar')}}' + '/' + idventa + '/' + nombre_comprobante + '/' + doc_relacionado)
                            .then(response => {
                                alert(response.data);
                                window.location.reload();
                            })
                            .catch(error => {
                                alert('Ha ocurrido un error al enviar el documento.');
                                console.log(error);
                            });
                    }
                },
                eliminar(idventa){
                    if (confirm('¿Está seguro de eliminar la venta?')) {
                        axios.get('{{url('ventas/eliminar-venta')}}'+'/'+idventa)
                            .then(() => {
                                window.location.reload();
                            })
                            .catch(error => {
                                alert('Ha ocurrido un error al eliminar la venta.');
                                console.log(error);
                            });
                    }
                },
                abrir_modal(nombre, obj){
                    switch (nombre) {
                        case 'anulacion':
                            this.idventa = obj.idventa;
                            this.comprobante = obj.facturacion.codigo_tipo_documento;
                            this.$refs['modal-anulacion'].show();
                            break;
                    }
                },
                resetModalAnulacion(){
                    if(this.anulacionResponse){
                        window.location.reload(true);
                    }
                },
                anular_comprobante(){
                    if ((this.motivo_anulacion).trim() == '') {
                        alert('Debes ingresar un motivo de anulación');
                    } else {
                        this.mostrarProgresoGuardado = true;
                        axios.post('/ventas/anulacion-rapida',{
                            'idventa':this.idventa,
                            'motivo_anulacion':this.motivo_anulacion,
                            'comprobante':this.comprobante
                        })
                            .then(response => {
                                let data = response.data;
                                this.anulacionResponse=true;
                                if(data.success === true || data.success === false){
                                    this.anulacion = data;
                                } else {
                                    this.anulacion.mensaje = 'Ha ocurrido un error al anular. Inténtalo más tarde o comunícate con el administrador del sistema.';
                                }
                                this.mostrarProgresoGuardado = false;
                                this.anulacionDisabled = true;
                            })
                            .catch(function (error) {
                                this.mostrarProgresoGuardado = false;
                                this.anulacionDisabled = true;
                                alert('Ha ocurrido un error.');
                                console.log(error);
                            });
                    }
                },

            },
            watch:{
                filtro(){
                    this.buscar='n';
                    switch (this.filtro){
                        case 'cliente':
                            this.buscar='';
                            break;
                        case 'fecha':
                            window.location.href='/comprobantes';
                            break;
                    }
                    this.desde = '{{date('Y-m-d')}}';
                    this.hasta = '{{date('Y-m-d')}}';
                },
            },

        })
    </script>
@endsection