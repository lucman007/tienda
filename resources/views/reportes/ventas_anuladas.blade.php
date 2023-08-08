@extends('layouts.main')
@section('titulo', 'Reporte de ventas anuladas')
@section('contenido')
    @php $agent = new \Jenssegers\Agent\Agent() @endphp
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1">Reporte de ventas</h3>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <b-nav tabs>
                    <b-nav-item href="{{action('ReporteController@reporte_ventas')}}">Resumen de ventas</b-nav-item>
                    <b-nav-item href="{{url('/reportes/ventas/diario').'/'.date('Y-m')}}">Ventas por día</b-nav-item>
                    <b-nav-item href="{{url('/reportes/ventas/mensual').'/'.date('Y')}}">Ventas por mes</b-nav-item>
                    <b-nav-item href="{{url('/reportes/anulados').'?tipo=ventas'}}" active>Ventas anuladas</b-nav-item>
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
                                    <select disabled class="custom-select">
                                        <option value="fecha">Fecha</option>
                                    </select>
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
                                        @php
                                            $tipo_pago = \sysfact\Http\Controllers\Helpers\DataTipoPago::getTipoPago();
                                        @endphp
                                        @if(count($ventas)!=0)
                                            @foreach($ventas as $venta)
                                                <tr>
                                                    <td></td>
                                                    <td style="width: 5%">{{$venta->idventa}}</td>
                                                    <td style="width: 15%">{{date('d/m/Y H:i:s', strtotime($venta->fecha))}}</td>
                                                    <td>{{$venta->cliente->persona->nombre}}</td>
                                                    <td>{{$venta->total_venta}}</td>
                                                    <td>{{$venta->facturacion->codigo_moneda}}</td>
                                                    <td>
                                                        @foreach($venta->pago as $pago)
                                                            @php
                                                                $index = array_search($pago->tipo, array_column($tipo_pago,'num_val'));
                                                            @endphp
                                                            {{mb_strtoupper($tipo_pago[$index]['label'])}} {{$pago->monto}}
                                                            <br>
                                                        @endforeach
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
                desde:'{{$filtros['desde']}}',
                hasta:'{{$filtros['hasta']}}',
                tipo:'{{$filtros['tipo']}}',
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
                        window.location.href='/reportes/anulados?tipo='+this.tipo+'&desde='+this.desde+'&hasta='+this.hasta;
                    }
                },
            },

        })
    </script>
@endsection