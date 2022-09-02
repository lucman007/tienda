@extends('layouts.main')
@section('titulo', 'Reporte de ventas')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
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
                </b-nav>
                <div class="row mt-4">
                    <div class="col-lg-9">
                        <div class="row">
                            <div class="col-lg-3">
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
                                    </select>
                                </b-input-group>
                            </div>
                            <div class="col-lg-2" v-show="filtro=='documento'">
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
                            <div class="col-lg-2" v-show="filtro=='tipo-de-pago'">
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
                                            <option value="{{$pago['text_val']}}">{{$pago['label']}}</option>
                                        @endforeach
                                    </select>
                                </b-input-group>
                            </div>
                            <div class="col-lg-2" v-show="filtro=='moneda'">
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
                            <div class="col-lg-5 form-group" v-show="filtro=='cliente'">
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
                            <div class="col-lg-3 form-group">
                                @if(count($ventas)!=0)
                                    <a href="{{str_contains(url()->full(),'?')?url()->full().'&export=true':url()->current().'?export=true'}}" class="btn btn-primary"><i class="fas fa-file-export"></i> Exportar excel</a>
                                @else
                                    <button disabled class="btn btn-primary"><i class="fas fa-file-export"></i> Exportar excel</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" v-if="penReport">
                    <div class="col-lg-12">
                        <div class="card no-shadow">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <h5><strong>Ventas facturadas en soles</strong></h5>
                                    </div>
                                    <div class="col-md col-sm-6">
                                        <p class="mb-0">Ventas brutas <br>
                                            <span style="font-size: 25px;">S/@{{ penReport.ventas_brutas.toFixed(2) }}</span>
                                        </p>
                                    </div>
                                    <div class="col-md col-sm-6">
                                        <p class="mb-0">Impuestos <br>
                                            <span style="font-size: 25px;">S/@{{ penReport.impuestos.toFixed(2) }}</span>
                                        </p>
                                    </div>
                                    <div class="col-md col-sm-6">
                                        <p class="mb-0">Ventas netas <br>
                                            <span style="font-size: 25px;">S/@{{ penReport.ventas_netas.toFixed(2) }}</span>
                                        </p>
                                    </div>
                                    <div class="col-md col-sm-6">
                                        <p class="mb-0">Precio de compra <br>
                                            <span style="font-size: 25px;">S/@{{ penReport.costos.toFixed(2) }}</span>
                                        </p>
                                    </div>
                                    <div class="col-md col-sm-6">
                                        <p class="mb-0">Utilidad<br>
                                            <span style="font-size: 25px;">S/@{{ penReport.utilidad.toFixed(2) }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" v-if="usdReport">
                    <div class="col-lg-12 mt-1">
                        <div class="card no-shadow">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <h5><strong>Ventas facturadas en dólares</strong></h5>
                                    </div>
                                    <div class="col-md col-sm-6">
                                        <p class="mb-0">Ventas brutas <br>
                                            <span style="font-size: 25px;">USD@{{usdReport.ventas_brutas.toFixed(2) }}</span>
                                        </p>
                                    </div>
                                    <div class="col-md col-sm-6">
                                        <p class="mb-0">Impuestos <br>
                                            <span style="font-size: 25px;">S/@{{ usdReport.impuestos.toFixed(2) }}</span>
                                        </p>
                                    </div>
                                    <div class="col-md col-sm-6">
                                        <p class="mb-0">Ventas netas <br>
                                            <span style="font-size: 25px;">S/@{{ usdReport.ventas_netas.toFixed(2) }}</span>
                                        </p>
                                    </div>
                                    <div class="col-md col-sm-6">
                                        <p class="mb-0">Costos <br>
                                            <span style="font-size: 25px;">S/@{{ usdReport.costos.toFixed(2) }}</span>
                                        </p>
                                    </div>
                                    <div class="col-md col-sm-6">
                                        <p class="mb-0">Utilidad<br>
                                            <span style="font-size: 25px;">S/@{{ usdReport.utilidad.toFixed(2) }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                                                    <td style="width: 15%">{{$venta->fecha}}</td>
                                                    <td>{{$venta->cliente->persona->nombre}}</td>
                                                    <td>{{$venta->total_venta}}</td>
                                                    {{--<td>{{$venta->total_venta}} {{$venta->tipo_pago=='OTROS'?'(EFECT.'.$venta->pago[0]->monto.'/TARJ.'.$venta->pago[1]->monto.')':''}}</td>--}}
                                                    <td>{{$venta->facturacion->codigo_moneda}}</td>
                                                    <td>{{$venta->tipo_pago}}</td>
                                                    <td><a target="_blank" href="/facturacion/documento/{{$venta->idventa}}">{{$venta->facturacion->serie}}-{{$venta->facturacion->correlativo}}</a><br>
                                                        {{$venta->guia_relacionada['correlativo']}}
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
                spinner:false,
            },
            mounted(){
                this.getReportBadge();
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
                            this.penReport = response.data[0];
                            this.usdReport = response.data[1];
                            this.spinner = false;
                        })
                        .catch(error => {
                            alert('Ha ocurrido un error');
                            console.log(error);
                            this.spinner = false;
                        });
                }
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