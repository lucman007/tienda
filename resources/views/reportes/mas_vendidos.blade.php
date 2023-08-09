@extends('layouts.main')
@section('titulo', 'Más vendidos')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1">Reporte de productos</h3>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <b-nav tabs>
                    <b-nav-item href="{{url('/reportes/productos/resumen-diario')}}">Resumen diario</b-nav-item>
                    <b-nav-item href="{{url('/reportes/productos/mas-vendidos')}}" active>Más vendidos</b-nav-item>
                    <b-nav-item href="{{url('/reportes/productos/stock_bajo')}}">Stock bajo</b-nav-item>
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
                                @if(count($productos)!=0)
                                    <a href="{{str_contains(url()->full(),'?')?url()->full().'&export=true':url()->current().'?export=true'}}" class="btn btn-primary"><i class="fas fa-file-export"></i> Exportar excel</a>
                                @else
                                    <button disabled class="btn btn-primary"><i class="fas fa-file-export"></i> Exportar excel</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3" v-if="resumen">
                    <div class="col-lg-12">
                        <div class="card no-shadow">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-2 col-sm-6">
                                        <p class="mb-0">Productos vendidos <br>
                                            <span style="font-size: 30px;"><strong>@{{resumen['cantidad']}}</strong></span>
                                        </p>
                                    </div>
                                    <div class="col-md-2 col-sm-6">
                                        <p class="mb-0">Total venta<br>
                                            <span style="font-size: 30px;"><strong>S/ @{{resumen['total'].toFixed(2)}}</strong></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card no-shadow">
                                    <div class="card-body">
                                        <div class="table-responsive tabla-gestionar">
                                            <table class="table table-striped table-hover table-sm">
                                                <thead class="bg-custom-green">
                                                <tr>
                                                    <th scope="col"></th>
                                                    <th scope="col">Producto</th>
                                                    <th scope="col">Características</th>
                                                    <th scope="col">Cantidad</th>
                                                    <th scope="col">Total</th>
                                                    <th scope="col">Kardex</th>
                                                    <th></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @if(count($productos) != 0)
                                                    @foreach($productos as $producto)
                                                        @php
                                                            $unidad = explode('/',$producto->unidad_medida);
                                                        @endphp
                                                        @if($producto->tipo_producto != 4)
                                                            <tr>
                                                                <td></td>
                                                                <td>{{ str_pad($producto->cod_producto,5,'0',STR_PAD_LEFT) }} - {{ $producto->nombre }}</td>
                                                                <td  style="width: 30%">{{ \Illuminate\Support\Str::words($producto->presentacion,40,'...')}}</td>
                                                                <td>{{ floatval($producto->vendidos).' '.$unidad[1]}}</td>
                                                                <td>S/ {{ number_format($producto->monto_total,3)}}</td>
                                                                <td><a href="{{url('/productos/inventario/'.$producto->idproducto.'?desde='.$filtros['desde'].'&hasta='.$filtros['hasta'])}}"><i class="fas fa-indent"></i> Ver kardex</a></td>
                                                                <td></td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="8" class="text-center">No hay datos para mostrar</td>
                                                    </tr>
                                                @endif
                                                </tbody>
                                            </table>
                                        </div>
                                        {{$productos->links('layouts.paginacion')}}
                                    </div>
                                </div>
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
                spinner:false,
                resumen:null
            },
            mounted(){
                this.getReportBadge();
            },
            methods:{
                setParams(obj){
                    let d1 = new Date(obj.startDate).toISOString().split('T')[0];
                    let d2 = new Date(obj.endDate).toISOString().split('T')[0];
                    this.desde=d1;
                    this.hasta=d2;
                    this.filtrar();
                },
                filtrar(){
                    if(this.buscar!=='n'){
                        window.location.href='/reportes/productos/mas-vendidos?desde='+this.desde+'&hasta='+this.hasta;
                    }
                },
                getReportBadge(){
                    this.spinner = true;
                    axios.get('/reportes/productos/badge?desde='+this.desde+'&hasta='+this.hasta)
                        .then(response => {
                            this.resumen = response.data;
                            this.spinner = false;
                        })
                        .catch(error => {
                            alert('Ha ocurrido un error');
                            console.log(error);
                            this.spinner = false;
                        });
                },
            }
        });
    </script>
@endsection