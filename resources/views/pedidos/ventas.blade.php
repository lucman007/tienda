@extends('layouts.main')
@section('titulo', 'Resumen de ventas')
@section('contenido')
    @php
        $codigos_pais = \sysfact\Http\Controllers\Helpers\DataGeneral::getCodigoPais();
    @endphp
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <h3 class="titulo-admin-1">
                    <a href="{{url()->previous()}}"><i class="fas fa-arrow-circle-left"></i></a>
                    Resumen de ventas
                </h3>
                <b-button @click="imprimir(null)" class="mr-2" variant="primary"><i class="fas fa-print"></i>
                    Imprimir
                </b-button>
            </div>
            <div class="col-lg-4">
                @include('pedidos.buscador')
            </div>
        </div>
        @if($textoBuscado!='')
            <div class="row">
                <div class="col-lg-12 mt-5">
                    <div class="alert alert-dark" role="alert"><h5 class="mb-0">Resultados de búsqueda para: {{$textoBuscado}}
                            <a href="{{url('pedidos/ventas')}}"><i class="fa fa-times float-right"></i></a></h5></div>
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-sm-12 mt-4">
                <div class="card">
                    <div class="card-body">
                        @if($textoBuscado=='')
                            <h3 class="float-right d-inline-flex">
                                <span style="font-size: 12px; margin: 8px;">Total del día:</span> S/ {{number_format($total_soles,2)}}
                                @if($total_dolares > 0)
                                <span style="font-size: 12px; margin: 8px 8px 0 20px;">Total dólares:</span> USD {{number_format($total_dolares,2)}}
                                @endif
                            </h3>

                        @endif
                        <div class="table-responsive tabla-gestionar" style="min-height: 300px">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Caja</th>
                                    <th scope="col">Vend.</th>
                                    <th scope="col">Cliente</th>
                                    <th scope="col">Importe</th>
                                    <th scope="col">Pago</th>
                                    <th scope="col">N° comprobante</th>
                                    <th scope="col">Opciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($ventas))
                                    @foreach($ventas as $item)
                                        <tr>
                                            <td></td>
                                            <td style="width:10%;">{{date("d-m-Y H:i:s",strtotime($item->fecha))}}</td>
                                            <td>{{$item->caja->idpersona == -1?'-':strtoupper($item->caja->nombre)}}</td>
                                            <td>{{$item->empleado->idpersona == -1?'-':strtoupper($item->empleado->nombre)}}</td>
                                            <td>{{$item->persona->nombre}}</td>
                                            <td>{{$item->moneda}}{{$item->total_venta}}</td>
                                            <td>
                                                @if($item->adelanto < 0)
                                                    <span style="opacity: 0.6">
                                                                ADELANTO {{number_format(abs($item->adelanto),2)}}
                                                            </span>
                                                    <br>
                                                @endif
                                                @php
                                                    $tipo_pago = \sysfact\Http\Controllers\Helpers\DataTipoPago::getTipoPago();
                                                    $pagos = $item->pago;
                                                @endphp
                                                @foreach($pagos as $pago)
                                                    @php
                                                        $index = array_search($pago->tipo, array_column($tipo_pago,'num_val'));
                                                    @endphp
                                                    {{mb_strtoupper($tipo_pago[$index]['label'])}} {{$item->moneda}}{{$pago->monto}} @if($pago->referencia) (N° OP.: {{$pago->referencia}}) @endif<br>
                                                @endforeach
                                            </td>
                                            <td id="comp_{{$item->idventa}}">
                                                @if($item->facturacion->codigo_tipo_documento=='30')
                                                    {{$item->comprobante}}
                                                @else
                                                    <span class="badge {{$item->badge_comp}}">{{$item->comprobante}}</span>
                                                @endif
                                            </td>
                                            <td class="botones-accion">
                                                <b-button @click="abrir_modal('editar-pago',{{$item}})" class="btn btn-success" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </b-button>
                                                <b-dropdown id="dropdown-1" text="Más" class="m-md-2 " variant="warning">
                                                    <b-dropdown-item href="{{url('facturacion/documento').'/'.$item->idventa}}"><i class="fas fa-stream"></i> Ver detalle</b-dropdown-item>
                                                    @can('Facturación: facturar')
                                                        <b-dropdown-item id="btn_facturar_{{$item->idventa}}" :disabled="{{$item->facturacion->codigo_tipo_documento}}!= '30' || disabledVentas" @click="facturar({{$item}})">
                                                            <i class="fas fa-file-import"></i> Crear Boleta / Factura
                                                        </b-dropdown-item>
                                                        <b-dropdown-item id="btn_facturar_alt_{{$item->idventa}}" class="d-none" disabled><i class="fas fa-file-import"></i> Crear Boleta / Factura</b-dropdown-item>
                                                    @endcan
                                                    @if($item->facturacion->codigo_tipo_documento == '30')
                                                    <b-dropdown-item id="btn_imprimir_{{$item->idventa}}" @click="imprimir('{{$item->idventa}}')"><i class="fas fa-receipt"></i> Imprimir nota de venta</b-dropdown-item>
                                                    @else
                                                    <b-dropdown-item @click="imprimir('{{$item->idventa}}')"><i class="fas fa-file-invoice-dollar"></i> Imprimir comprobante</b-dropdown-item>
                                                    @endif
                                                    <b-dropdown-item id="btn_anular_{{$item->idventa}}" @click="abrir_modal('anulacion',{{$item}})" :disabled="{{$item->facturacion->codigo_tipo_documento}} == 30">
                                                        <i class="fas fa-times"></i> Anular comprobante
                                                    </b-dropdown-item>
                                                    <b-dropdown-item @click="text_whatsapp = '{{$item->text_whatsapp}}'" v-b-modal.modal-whatsapp><i style="width: 2em;" class="fab fa-whatsapp"></i> Enviar por whatsapp</b-dropdown-item>
                                                </b-dropdown>
                                                <button id="btn_borrar_{{$item->idventa}}" @if($item->facturacion->codigo_tipo_documento!='30') disabled style="opacity: 0.2" @endif @click="eliminar({{$item->idventa}})"
                                                        class="btn btn-danger" title="Eliminar"><i class="fas fa-trash-alt"></i>
                                                </button>
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
            </div>
        </div>
    </div>
<!--INICIO MODAL PAGO FRACCIONADO -->
<b-modal size="md" id="mod-fraccionado" ref="mod-fraccionado" @ok="">
<template slot="modal-title">
    Pago fraccionado
</template>
<div class="container">
    <div class="row">
        <div v-for="pago in pago_fraccionado" class="col-lg-12 mb-3">
            <div class="row">
                <div class="col-lg-5">
                    <label>Monto</label>
                    <input v-model="pago.monto" type="text" class="form-control" onfocus="this.select()">
                </div>
                <div class="col-lg-6">
                    <label>Tipo de pago</label>
                    <select v-model="pago.tipo" class="custom-select">
                        @php
                            $tipo_pago = \sysfact\Http\Controllers\Helpers\DataTipoPago::getTipoPago();
                        @endphp
                        @foreach($tipo_pago as $pago)
                            @if($pago['num_val'] != 4 && $pago['num_val'] != 2)
                                <option value="{{$pago['num_val']}}">{{$pago['label']}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
<template #modal-footer="{ ok, cancel}">
    <b-button variant="secondary" @click="cancel()">
        Listo
    </b-button>
</template>
</b-modal>
<!--FIN MODAL PAGO FRACCIONADO -->
<!--INICIO MODAL EDITAR TIPO PAGO -->
<b-modal size="md" id="modal-editarPago" ref="modal-editarPago" @ok="" @hidden="resetModalAfterEditPago">
<template slot="modal-title">
    Editar
</template>
<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <label>Tipo de pago</label>
            <div class="row">
                <div class="col-lg-5 form-group">
                    <select v-model="tipoPagoContado" class="custom-select">
                        @php
                            $tipo_pago = \sysfact\Http\Controllers\Helpers\DataTipoPago::getTipoPago();
                        @endphp
                        @foreach($tipo_pago as $pago)
                            <option value="{{$pago['num_val']}}">{{$pago['label']}}</option>
                        @endforeach
                    </select>
                </div>
                <div v-show="tipoPagoContado==4" class="col-lg-5 form-group">
                    <b-button @click="abrir_modal('fraccionado')" variant="primary"><i
                                class="fas fa-plus"></i> Editar pago
                    </b-button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-12">
        <div v-for="error in errorDatosVenta">
            <p class="texto-error">@{{ error }}</p>
        </div>
    </div>
</div>
<template #modal-footer="{ ok, cancel}">
    <b-button variant="secondary" @click="cancel()">
        Cancelar
    </b-button>
    <b-button :disabled="mostrarProgresoGuardado"  variant="primary" @click="editar_tipo_pago">
        <span v-show="mostrarProgresoGuardado" ><b-spinner small label="Loading..." ></b-spinner> Guardando...</span>
        <span v-show="!mostrarProgresoGuardado">Guardar</span>
    </b-button>
</template>
</b-modal>
<!--FIN MODAL EDITAR TIPO PAGO -->
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
    <modal-facturacion
            ref="modalFacturacion"
            :idventa="idventa"
            :total="totalVenta"
            :origen="'ventas'"
            :fecha="'{{date('Y-m-d', strtotime(date('Y-m-d').' + 1 days'))}}'"
            :tipo_de_pago="{{json_encode(\sysfact\Http\Controllers\Helpers\DataTipoPago::getTipoPago())}}"
            v-on:imprimir="imprimir"
            v-on:countcomprobantes="obtener_num_comprobantes"
            v-on:after-save="after_save">
    </modal-facturacion>
    <modal-whatsapp :text="text_whatsapp" :link="'{{$agent->isDesktop()?'https://web.whatsapp.com':'https://api.whatsapp.com'}}'" :codigos="{{json_encode($codigos_pais)}}"></modal-whatsapp>
@endsection
@section('script')
    <script>
        let app = new Vue({
            el: '.app',
            data: {
                errorDatosVenta: [],
                errorVenta: 0,
                comprobante: '03',
                errorDatosFacturacion:[],
                tipoPagoContado: 1,
                pago_fraccionado:[
                    {
                        monto: '0.00',
                        tipo: '1'
                    },
                    {
                        monto: '0.00',
                        tipo: '3'
                    },
                ],
                idventa:-1,
                venta:null,
                idcliente: -1,
                tituloModal:'Facturar',
                mostrarProgresoGuardado: false,
                mostrarProgreso: false,
                clienteSeleccionado:{},
                totalVenta:0,
                motivo_anulacion:'',
                anulacionResponse: false,
                anulacionSuccess:true,
                anulacion:{},
                anulacionDisabled:false,
                text_whatsapp: '',
                disabledVentas: !!'<?php echo $disabledVentas ?>'
            },
            methods:{
                obtener_num_comprobantes(){
                    app_menu.$refs['panelNotificacion'].countComprobantes();
                },
                disabled_ventas(){
                    this.disabledVentas = true;
                },
                facturar(item){
                    this.idventa = item.idventa;
                    this.venta = item;
                    this.totalVenta = item.total_venta;
                    this.$refs.modalFacturacion.mostrarModal();
                },
                eliminar(idventa){
                    if (confirm('¿Está seguro de eliminar la venta?')) {
                        axios.get('{{url('ventas/eliminar-venta')}}'+'/'+idventa)
                            .then(function () {
                                window.location.reload(true);
                            })
                            .catch(function (error) {
                                alert('Ha ocurrido un error al eliminar la venta.');
                                console.log(error);
                            });
                    }
                },
                imprimir(file_or_id){
                     @if(!$agent->isDesktop())
                        let src = "{{url('/ventas/imprimir').'/'}}"+file_or_id;
                        if(file_or_id==null) {
                            src = "{{url('/pedidos/imprimir-historial').($idcaja?'?idcaja='.$idcaja:'')}}";
                        }
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
                        if(file_or_id==null) {
                            iframe.src = "{{url('/pedidos/imprimir-historial').($idcaja?'?idcaja='.$idcaja:'')}}";
                        } else{
                            iframe.src = "/ventas/imprimir/"+file_or_id;
                        }
                        iframe.onload = function() {
                            setTimeout(function() {
                                iframe.focus();
                                iframe.contentWindow.print();
                            }, 0);
                        };
                    @endif
                },
                resetModalAnulacion(){
                  if(this.anulacionResponse){
                      window.location.reload(true);
                  }
                },
                resetModalAfterEditPago(){
                    this.idventa = -1;
                    this.errorDatosVenta = [];
                    this.tipoPagoContado=1;
                    this.pago_fraccionado=[
                        {
                            monto: '0.00',
                            tipo: '1'
                        },
                        {
                            monto: '0.00',
                            tipo: '3'
                        },
                    ]
                },
                after_save(data){
                    let comp = (data.file).split('-');
                    let badge_class = 'badge-success';
                    if(comp[2].includes('F00')){
                        badge_class = 'badge-warning';
                    }
                    document.querySelector('#comp_'+data.idventa).innerHTML = '<span class="badge '+ badge_class +'">'+comp[2]+'-'+comp[3]+'</span>';
                    document.querySelector('#btn_imprimir_'+data.idventa).innerHTML = '<i class="fas fa-file-invoice-dollar"></i> Imprimir comprobante';
                    document.querySelector('#btn_anular_'+data.idventa).classList.remove('disabled');
                    document.querySelector('#btn_facturar_'+data.idventa).remove();
                    document.querySelector('#btn_borrar_'+data.idventa).classList.add('disabled');
                    document.querySelector('#btn_borrar_'+data.idventa).disabled = true;
                    document.querySelector('#btn_borrar_'+data.idventa).style.opacity = '0.2';

                    const elementoHijo = document.querySelector('#btn_facturar_alt_'+data.idventa);
                    const elementoPadre = elementoHijo.parentNode;
                    elementoPadre.classList.remove('d-none');
                },
                abrir_modal(nombre, obj){
                    console.log('aaa'+nombre)
                    switch (nombre) {
                        case 'fraccionado':
                            this.$refs['mod-fraccionado'].show();
                            break;
                        case 'editar-pago':
                            this.idventa = obj.idventa;
                            this.totalVenta = obj.total_venta;
                            this.$refs['modal-editarPago'].show();
                            break;
                        case 'anulacion':
                            this.idventa = obj.idventa;
                            this.comprobante = obj.facturacion.codigo_tipo_documento;
                            this.$refs['modal-anulacion'].show();
                            break;
                    }
                },
                editar_tipo_pago(){
                    this.mostrarProgresoGuardado = true;
                    this.errorDatosVenta = [];
                    if (this.tipoPagoContado == 4) {
                        let suma_pago_fra = 0;
                        for (let pago of this.pago_fraccionado) {
                            suma_pago_fra += Number(pago.monto);
                        }
                        if (suma_pago_fra > this.totalVenta) this.errorDatosVenta.push('*La suma de los pagos fraccionados supera el monto total de la venta');
                        if (suma_pago_fra < this.totalVenta) this.errorDatosVenta.push('*La suma de los pagos fraccionados es inferior al monto total de la venta');
                        this.mostrarProgresoGuardado = false;
                    }

                    if (this.errorDatosVenta.length > 0){
                        return false;
                    }

                    axios.post('/ventas/update_tipo_pago',{
                        'idventa':this.idventa,
                        'tipo_pago_contado':this.tipoPagoContado,
                        'pago_fraccionado': JSON.stringify(this.pago_fraccionado)
                    })
                        .then(response => {
                            this.mostrarProgresoGuardado = false;
                            alert(response.data);
                            window.location.reload();
                            this.$refs['modal-editarPago'].hide()
                        })
                        .catch(error => {
                            this.mostrarProgresoGuardado = false;
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });
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
                obtener_notificaciones(){
                    app_menu.$refs['panelNotificacion'].countNotifications();
                },
            }
        });
    </script>
@endsection