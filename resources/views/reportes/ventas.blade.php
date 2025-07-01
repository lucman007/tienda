@extends('layouts.main')
@section('titulo', 'Reporte de ventas')
@section('contenido')
    @php $agent = new \Jenssegers\Agent\Agent() @endphp
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1">Reporte de ventas</h3>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <b-nav tabs>
                    <b-nav-item href="{{action('ReporteController@reporte_ventas')}}" active>Resumen de ventas</b-nav-item>
                    <b-nav-item href="{{url('/reportes/ventas/diario').'/'.date('Y-m')}}">Ventas por día</b-nav-item>
                    <b-nav-item href="{{url('/reportes/ventas/mensual').'/'.date('Y')}}">Ventas por mes</b-nav-item>
                    <b-nav-item href="{{url('/reportes/anulados').'?tipo=ventas'}}">Ventas anuladas</b-nav-item>
                </b-nav>
                <div class="row mt-4">
                    <div class="col-lg-9">
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
                                        <option value="cliente">Cliente</option>
                                        <option value="vendedor">Vendedor</option>
                                        <option value="cajero">Cajero</option>
                                    </select>
                                </b-input-group>
                            </div>
                            <div class="col-lg-2 mb-2 mb-lg-0" v-show="filtro=='vendedor'">
                                <b-input-group>
                                    <b-input-group-prepend>
                                        <b-input-group-text>
                                            <i class="fas fa-check"></i>
                                        </b-input-group-text>
                                    </b-input-group-prepend>
                                    <select @change="filtrar" v-model="buscar" class="custom-select">
                                        <option value="n">Seleccionar</option>
                                        <option v-for="vendedor in vendedores" :value="vendedor.idempleado">@{{vendedor.persona.nombre}}</option>
                                    </select>
                                </b-input-group>
                            </div>
                            <div class="col-lg-2 mb-2 mb-lg-0" v-show="filtro=='cajero'">
                                <b-input-group>
                                    <b-input-group-prepend>
                                        <b-input-group-text>
                                            <i class="fas fa-check"></i>
                                        </b-input-group-text>
                                    </b-input-group-prepend>
                                    <select @change="filtrar" v-model="buscar" class="custom-select">
                                        <option value="n">Seleccionar</option>
                                        <option v-for="cajero in cajeros" :value="cajero.idempleado">@{{cajero.persona.nombre}}</option>
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
                            <div class="col-lg-3 form-group" v-show="!errorMensaje">
                                @if(count($ventas)!=0)
                                    <a href="{{str_contains(url()->full(),'?')?url()->full().'&export=true':url()->current().'?export=true'}}" class="btn btn-primary"><i class="fas fa-file-export"></i> Exportar excel</a>
                                @else
                                    <button disabled class="btn btn-primary"><i class="fas fa-file-export"></i> Exportar excel</button>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <b-input-group>
                            <b-input-group-prepend>
                                <b-input-group-text>
                                    <i class="fas fa-envelope"></i>
                                </b-input-group-text>
                            </b-input-group-prepend>
                            <input v-model="mail" type="text" class="form-control" placeholder="Enviar por email" >
                            <b-input-group-append>
                                <b-button :disabled="mail.length==0" @click="enviar_por_email" variant="primary" >
                                    <span v-show="!spinnerMail" ><i class="fas fa-paper-plane"></i></span>
                                    <span v-show="spinnerMail"><b-spinner small label="Loading..." ></b-spinner></span>
                                </b-button>
                            </b-input-group-append>
                        </b-input-group>
                    </div>
                </div>
                <div class="row" v-if="penReport">
                    <div class="col-lg-12">
                        <div class="card no-shadow" style="background: #dcffe5;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12 d-flex justify-content-between align-items-start">
                                        <div style="width: 350px">
                                            <h5 style="font-size: 24px; margin-bottom: 0"><strong>Total ventas en soles</strong></h5>
                                            <p>Del {{date('d/m/Y', strtotime($filtros['desde']))}} {{date('h:iA', strtotime($filtros['hdesde']))}} al {{date('d/m/Y', strtotime($filtros['hasta']))}} {{date('h:iA', strtotime($filtros['hhasta']))}}</p>
                                        </div>
                                        <button class="btn btn-success float-right" title="Imprimir" @click="imprimir_reporte('totales','pen')">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16">
                                                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                                                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="col-md col-sm-6">
                                        <p class="mb-0">Ventas brutas <br>
                                            <span style="font-size: 25px;">S/@{{ penReport.ventas_brutas.toFixed(2) }}</span>
                                        </p>
                                    </div>
                                    <div class="col-md col-sm-6">
                                        <p class="mb-0">Impuestos <i class="fas fa-info-circle" id="tooltip-impuesto-pen"></i><br>
                                            <span style="font-size: 25px;">S/@{{ penReport.impuestos.toFixed(2) }}</span>
                                        </p>
                                    </div>
                                    <b-tooltip target="tooltip-impuesto-pen" triggers="hover">
                                        Impuesto de las facturas y boletas (No recibos)
                                    </b-tooltip>
                                    <div class="col-md col-sm-6">
                                        <p class="mb-0 d-flex flex-column">Adelantos
                                            <span style="font-size: 25px;">S/@{{ penReport.adelantos.toFixed(2) }}</span>
                                        </p>
                                    </div>
                                    <div class="col-md col-sm-6">
                                        <p class="mb-0"><strong>Ventas netas </strong><i class="fas fa-info-circle" id="tooltip-v-netas-pen"></i><br>
                                            <span style="font-size: 25px;"><strong>S/@{{ penReport.ventas_netas.toFixed(2) }}</strong></span>
                                        </p>
                                    </div>
                                    <b-tooltip target="tooltip-v-netas-pen" triggers="hover">
                                        Ventas brutas - impuestos
                                    </b-tooltip>
                                    <div class="col-md col-sm-6">
                                        <p class="mb-0">Precio de compra <br>
                                            <span style="font-size: 25px;">S/@{{ penReport.costos.toFixed(2) }}</span>
                                        </p>
                                    </div>
                                    <div class="col-md col-sm-6">
                                        <p class="mb-0">Utilidad <i class="fas fa-info-circle" id="tooltip-utilidad-pen"></i><br>
                                            <span style="font-size: 25px;" :style="penReport.utilidad<0?'color:red':'color:inherit'">S/@{{ penReport.utilidad.toFixed(2) }}</span>
                                        </p>
                                    </div>
                                    <b-tooltip target="tooltip-utilidad-pen" triggers="hover">
                                        Ventas netas - costos
                                    </b-tooltip>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" v-if="usdReport">
                    <div class="col-lg-12 mt-1">
                        <div class="card no-shadow" style="background: #dcffe5;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12 d-flex justify-content-between align-items-start">
                                        <div style="width: 350px">
                                            <h5 style="font-size: 24px; margin-bottom: 0"><strong>Total ventas en dólares</strong></h5>
                                            <p>Del {{date('d/m/Y', strtotime($filtros['desde']))}} {{date('h:iA', strtotime($filtros['hdesde']))}} al {{date('d/m/Y', strtotime($filtros['hasta']))}} {{date('h:iA', strtotime($filtros['hhasta']))}}</p>
                                        </div>
                                        <button class="btn btn-success float-right" title="Imprimir" @click="imprimir_reporte('totales','usd')">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16">
                                                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                                                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="col-md col-sm-6">
                                        <p class="mb-0">Ventas brutas <br>
                                            <span style="font-size: 25px;">USD@{{usdReport.ventas_brutas.toFixed(2) }}</span>
                                        </p>
                                    </div>
                                    <div class="col-md col-sm-6">
                                        <p class="mb-0">Impuestos <i class="fas fa-info-circle" id="tooltip-impuesto-usd"></i><br>
                                            <span style="font-size: 25px;">S/@{{ usdReport.impuestos.toFixed(2) }}</span>
                                        </p>
                                    </div>
                                    <b-tooltip target="tooltip-impuesto-usd" triggers="hover">
                                        Impuesto de las facturas y boletas (No recibos)
                                    </b-tooltip>
                                    <div class="col-md col-sm-6">
                                        <p class="mb-0 d-flex flex-column">Adelantos
                                            <span style="font-size: 25px;">S/@{{ usdReport.adelantos.toFixed(2) }}</span>
                                        </p>
                                    </div>
                                    <div class="col-md col-sm-6">
                                        <p class="mb-0"><strong>Ventas netas </strong><i class="fas fa-info-circle" id="tooltip-v-netas-usd"></i><br>
                                            <span style="font-size: 25px;"><strong>S/@{{ usdReport.ventas_netas.toFixed(2) }}</strong></span>
                                        </p>
                                    </div>
                                    <b-tooltip target="tooltip-v-netas-usd" triggers="hover">
                                        Ventas brutas - impuestos
                                    </b-tooltip>
                                    <div class="col-md col-sm-6">
                                        <p class="mb-0">Precio de compra <br>
                                            <span style="font-size: 25px;">S/@{{ usdReport.costos.toFixed(2) }}</span>
                                        </p>
                                    </div>
                                    <div class="col-md col-sm-6">
                                        <p class="mb-0">Utilidad <i class="fas fa-info-circle" id="tooltip-utilidad-usd"></i><br>
                                            <span style="font-size: 25px;" :style="usdReport.utilidad<0?'color:red':'color:inherit'">S/@{{ usdReport.utilidad.toFixed(2) }}</span>
                                        </p>
                                    </div>
                                    <b-tooltip target="tooltip-utilidad-usd" triggers="hover">
                                        Ventas netas - costos
                                    </b-tooltip>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" v-if="tipoPagoReport">
                    <div class="col-lg-12 my-2">
                        <div class="card no-shadow">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <h5 class="d-inline" style="font-size: 18px"><strong>Detalle de pagos</strong></h5>
                                        <button class="btn btn-success float-right" title="Imprimir" @click="imprimir_reporte('tipo_pago','pen')">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16">
                                                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                                                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
                                            </svg>
                                        </button>
                                    </div>
                                    @php
                                        $tipo_pago = \sysfact\Http\Controllers\Helpers\DataTipoPago::getTipoPago();
                                    @endphp
                                    <div class="col-lg-12">
                                        <span class="badge badge-primary mt-2">Transacciones en efectivo / transferencia:</span>
                                        <div class="row">
                                            @foreach($tipo_pago as $pago)
                                                @if($pago['num_val'] == 1 || $pago['num_val'] == 5 ||$pago['num_val'] == 6 ||$pago['num_val'] == 9 ||$pago['num_val'] == 8 ||$pago['num_val'] == 2)
                                                    <div class="col-md col-sm-6">
                                                        <p class="mb-0">{{$pago['label']}}<br>
                                                            <span style="font-size: 25px;">S/<?php echo '{{tipoPagoReport["'.$pago['text_val'].'"]?Number(tipoPagoReport["'.$pago['text_val'].'"]).toFixed(2):"0.00"}}' ?></span>
                                                        </p>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <span class="badge badge-success mt-2">Transacciones por tarjeta / apps:</span>
                                        <div class="row">
                                            @foreach($tipo_pago as $pago)
                                                @if($pago['num_val'] == 3 || $pago['num_val'] == 7 ||$pago['num_val'] == 30 ||$pago['num_val'] == 31 ||$pago['num_val'] == 32)
                                                    <div class="col-md col-sm-6">
                                                        <p class="mb-0">{{$pago['label']}}<br>
                                                            <span style="font-size: 25px;">S/<?php echo '{{tipoPagoReport["'.$pago['text_val'].'"]?Number(tipoPagoReport["'.$pago['text_val'].'"]).toFixed(2):"0.00"}}' ?></span>
                                                        </p>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" v-if="errorMensaje">
                    <div class="col-lg-12 my4" style="margin-bottom: 20px">
                        <strong>No se pueden mostrar los gráficos y sumatorias porque el volumen de ventas es demasiado grande. Selecciona un rango de fecha no mayor a un mes o extrae el reporte completo desde el menú VENTAS POR MES.</strong>
                    </div>
                </div>
                <div class="row" v-show="spinner">
                    <div class="col-lg-12 my4" style="margin-bottom: 20px">
                        <b-spinner small label="Loading..." ></b-spinner> Cargando resumen...
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 mt-1">
                        <div class="card no-shadow">
                            <div class="card-body">
                                <div class="table-responsive tabla-gestionar">
                                    <table class="table table-striped table-hover table-sm">
                                        <thead class="bg-custom-green">
                                        <tr>
                                            <th scope="col"></th>
                                            <th scope="col">Venta</th>
                                            <th scope="col">Fecha</th>
                                            <th scope="col">Caja</th>
                                            <th scope="col">Vend.</th>
                                            <th scope="col">Cliente</th>
                                            <th scope="col">Importe</th>
                                            <th scope="col">Moneda</th>
                                            <th scope="col">Pago</th>
                                            <th scope="col" style="width: 12%">Comprobante</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(count($ventas)!=0)
                                            @foreach($ventas as $venta)
                                                <tr :class="{'td-anulado':'{{$venta->facturacion->estado}}'=='ANULADO' || '{{$venta->facturacion->estado}}'=='MODIFICADO'}">
                                                    <td></td>
                                                    <td style="width: 5%">{{$venta->idventa}}</td>
                                                    <td style="width: 15%">{{date('d/m/Y H:i', strtotime($venta->fecha))}}</td>
                                                    <td>{{$venta->caja->idpersona == -1?'-':strtoupper($venta->caja->nombre)}}</td>
                                                    <td>{{$venta->empleado->idpersona == -1?'-':strtoupper($venta->empleado->nombre)}}</td>
                                                    <td>{{$venta->cliente->persona->nombre}} {{$venta->clienteAlias?'('.$venta->clienteAlias->persona->nombre.')':''}}</td>
                                                    <td>{{$venta->total_venta}}</td>
                                                    <td>{{$venta->facturacion->codigo_moneda}}</td>
                                                    <td>
                                                        @if($venta->adelanto < 0)
                                                            <span style="opacity: 0.6">
                                                                ADELANTO {{number_format(abs($venta->adelanto),2)}}
                                                            </span>
                                                            <br>
                                                        @endif
                                                        @foreach($venta->pago as $pago)
                                                            @php
                                                                $index = array_search($pago->tipo, array_column($tipo_pago,'num_val'));
                                                            @endphp
                                                                @if($venta->adelanto > 0)
                                                                    <span title="Adelanto de pago" class="badge badge-info"><i class="fas fa-coins"></i></span>@endif
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
                                                    <td>
                                                        <a target="_blank" href="/facturacion/documento/{{$venta->idventa}}">{{$venta->facturacion->serie.'-'.$venta->facturacion->correlativo}}</a>
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
        </div>

    </div>

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
                usdReport:null,
                penReport:null,
                tipoPagoReport:null,
                spinner:false,
                delivery:[],
                mail:'',
                spinnerMail: false,
                errorMensaje: false,
                vendedores:[],
                cajeros:[]
            },
            mounted(){
                this.getReportBadge();
                this.getVendedores();
                this.getCajeros();
            },
            methods: {
                getVendedores(){
                    axios.get('/reportes/obtener-vendedores')
                        .then(response => {
                            this.vendedores = response.data;
                        })
                        .catch(error => {
                            alert('Ha ocurrido un error al obtener los vendedores');
                            console.log(error);
                        });
                },
                getCajeros(){
                    axios.get('/reportes/obtener-cajeros')
                        .then(response => {
                            this.cajeros = response.data;
                        })
                        .catch(error => {
                            alert('Ha ocurrido un error al obtener los cajeros');
                            console.log(error);
                        });
                },
                setParams(obj){
                    let d1 = new Date(obj.startDate).toISOString().split('T')[0];
                    let d2 = new Date(obj.endDate).toISOString().split('T')[0];
                    this.desde=d1;
                    this.hasta=d2;
                    this.filtrar();
                },
                filtrar(){
                    if(this.buscar!=='n'){
                        window.location.href='/reportes/ventas/'+this.desde+'/'+this.hasta+'?filtro='+this.filtro+'&buscar='+this.buscar;
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
                getReportBadge(){
                    this.spinner = true;
                    axios.get('/reportes/ventas/badge/'+this.desde+'/'+this.hasta+'?filtro='+this.filtro+'&buscar='+this.buscar)
                        .then(response => {
                            if(response.data == -1){
                                this.spinner = false;
                                this.errorMensaje = true;
                            } else {
                                this.penReport = response.data[0];
                                this.usdReport = response.data[1];
                                this.tipoPagoReport = response.data[2];
                                this.spinner = false;
                                this.errorMensaje = false;
                            }

                        })
                        .catch(error => {
                            alert('Ha ocurrido un error');
                            console.log(error);
                            this.spinner = false;
                        });
                },
                enviar_por_email(){
                    this.spinnerMail = true;
                    axios.get('/reportes/ventas/mail/'+this.desde+'/'+this.hasta+'?filtro='+this.filtro+'&buscar='+this.buscar+'&email='+this.mail)
                        .then(response => {
                            this.spinnerMail = false;
                            alert(response.data);
                        })
                        .catch(error => {
                            alert('Ha ocurrido un error');
                            console.log(error);
                            this.spinnerMail = false;
                        });
                },
                imprimir_reporte(tipo, moneda){
                    let src = '/reportes/ventas/imprimir/'+this.desde+'/'+this.hasta+'?filtro='+this.filtro+'&buscar='+this.buscar+'&reporte='+tipo+'&moneda='+moneda;
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
                        window.open(src, '_blank');
                    @endif
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
                            window.location.href='/reportes/ventas';
                            break;
                    }
                    this.desde = '{{date('Y-m-d')}}';
                    this.hasta = '{{date('Y-m-d')}}';
                },
            }

        })
    </script>
@endsection