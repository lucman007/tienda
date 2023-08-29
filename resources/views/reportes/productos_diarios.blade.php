@extends('layouts.main')
@section('titulo', 'Resumen diario')
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
                    <b-nav-item href="{{url('/reportes/productos/resumen-diario')}}" active>Resumen diario</b-nav-item>
                    <b-nav-item href="{{url('/reportes/productos/mas-vendidos')}}">Más vendidos</b-nav-item>
                    <b-nav-item href="{{url('/reportes/productos/stock_bajo')}}">Stock bajo</b-nav-item>
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
                    <div class="col-lg-3">
                        <p style="display: inline;float: left;" class="mb-0">Productos vendidos
                        </p>
                        <span style="font-size: 41px;margin-top: -20px;float: left;"><strong>@{{resumen}}</strong></span>
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
                                                    <th scope="col">Fecha</th>
                                                    <th scope="col">Producto</th>
                                                    <th scope="col">Cantidad</th>
                                                    <th scope="col">Ver detalle</th>
                                                    <th></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @if(count($productos) != 0)
                                                    @foreach($productos as $producto)
                                                        @php
                                                            $unidad = explode('/',$producto->unidad_medida);
                                                        @endphp
                                                        <tr>
                                                            <td></td>
                                                            <td>{{date('d/m/Y', strtotime($producto->fecha))}}</td>
                                                            <td>{{ str_pad($producto->cod_producto,5,'0',STR_PAD_LEFT) }} - {{ $producto->nombre }} {{ $producto->presentacion}}</td>
                                                            <td>{{ floatval(abs($producto->vendidos)).' '.$unidad[1]}}</td>
                                                            <td><button @click="obtenerDetalle({{$producto->idproducto}},'{{$producto->fecha}}')" class="btn btn-success"><i class="fas fa-list"></i></button></td>
                                                            <td></td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="6" class="text-center">No hay datos para mostrar</td>
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
    <!--INICIO MODAL DETALLE -->
    <b-modal size="xl" id="detalle-report" ref="detalle-report" ok-only>
        <template slot="modal-title">
            Detalle
        </template>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="table-responsive tabla-gestionar">
                        <table class="table table-striped table-hover table-sm">
                            <thead class="bg-custom-green">
                            <tr>
                                <th scope="col">Idventa</th>
                                <th scope="col">Fecha</th>
                                <th scope="col">Caja</th>
                                <th scope="col">Atiende</th>
                                <th scope="col">Cliente</th>
                                <th scope="col">Producto</th>
                                <th scope="col">Precio</th>
                                <th scope="col">Cantidad</th>
                                <th scope="col">Monto</th>
                                <th scope="col">Total</th>
                                <th scope="col"></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="item in detalle">
                                <td><a :href="'{{url('/facturacion/documento')}}/'+item.idventa" target="_blank">@{{item.idventa}}</a></td>
                                <td>@{{item.fecha}}</td>
                                <td>@{{item.caja.toUpperCase()}}</td>
                                <td>@{{item.atiende == 'GENERICO'?'-':item.atiende.toUpperCase()}}</td>
                                <td>@{{item.cliente}}</td>
                                <td>@{{item.nombre}}</td>
                                <td>S/@{{item.precio}}</td>
                                <td>@{{Number(item.cantidad).toFixed(0)}}</td>
                                <td v-show="item.monto != -1">@{{item.codigo_moneda=='PEN'?'S/':'USD'}}@{{(Number(item.monto)).toFixed(2)}}</td>
                                <td v-show="item.monto != -1">@{{item.codigo_moneda=='PEN'?'S/':'USD'}}@{{(Number(item.total)).toFixed(2)}}</td>
                                <td v-show="item.monto == -1">-</td>
                                <td v-show="item.monto == -1">-</td>
                                <td></td>
                            </tr>
                            <tr v-show="!detalle || detalle.length == 0" class="text-center">
                                <td colspan="11">No hay datos que mostrar</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </b-modal>
    <!--FIN MODAL DETALLE -->
@endsection
@section('script')
    <script>
        let app = new Vue({
            el: '.app',
            data: {
                desde:'{{$filtros['desde']}}',
                hasta:'{{$filtros['hasta']}}',
                spinner:false,
                resumen:null,
                detalle:[],
                external:'<?php echo $_GET['external'] ?? false ?>',
                external_idproducto:'<?php echo $_GET['idproducto'] ?? false ?>',
                external_fecha:'<?php echo $_GET['fecha'] ?? false ?>',
            },
            mounted(){
                if(this.external){
                    this.obtenerDetalle(this.external_idproducto,this.external_fecha);
                }
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
                        window.location.href='/reportes/productos/resumen-diario?desde='+this.desde+'&hasta='+this.hasta;
                    }
                },
                obtenerDetalle(idproducto,fecha){
                    axios.post('/reportes/productos/resumen-diario-detalle'+'?idproducto='+idproducto+'&fecha='+fecha,{
                        'desde':this.desde,
                        'hasta':this.hasta
                    })
                        .then(response => {
                            this.detalle = response.data;
                            this.$refs['detalle-report'].show()
                        })
                        .catch(error => {
                            alert('Ha ocurrido un error');
                            console.log(error);
                        });
                },
                getReportBadge(){
                    this.spinner = true;
                    axios.get('/reportes/productos/resumen-diario-badge?desde='+this.desde+'&hasta='+this.hasta)
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