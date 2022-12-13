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
                    <b-nav-item href="{{action('ReporteController@reporte_ventas')}}">Resumen de ventas</b-nav-item>
                    <b-nav-item href="{{url('/reportes/ventas/diario').'/'.date('Y-m')}}">Ventas por día</b-nav-item>
                    <b-nav-item href="{{url('/reportes/ventas/mensual').'/'.date('Y')}}" active>Ventas por mes</b-nav-item>
                </b-nav>
                <div class="row mt-4">
                    <div class="col-lg-9">
                        <div class="row">
                            <div class="col-lg-3 form-group">
                                <b-input-group>
                                    <b-input-group-prepend>
                                        <b-input-group-text>
                                            <i class="fas fa-calendar"></i>
                                        </b-input-group-text>
                                    </b-input-group-prepend>
                                    <select @change="obtenerReporte" v-model="anio" name="anio" class="custom-select" id="anio">
                                        <option value="2020">2020</option>
                                        <option value="2021">2021</option>
                                        <option value="2022">2022</option>
                                        <option value="2023">2023</option>
                                        <option value="2024">2024</option>
                                        <option value="2025">2025</option>
                                        <option value="2026">2026</option>
                                        <option value="2027">2027</option>
                                        <option value="2028">2028</option>
                                        <option value="2029">2029</option>
                                        <option value="2030">2030</option>
                                    </select>
                                </b-input-group>
                            </div>
                            <div class="col-lg-3 form-group">
                                <b-input-group>
                                    <b-input-group-prepend>
                                        <b-input-group-text>
                                            <i class="fas fa-filter"></i>
                                        </b-input-group-text>
                                    </b-input-group-prepend>
                                    <template #append>
                                        <b-dropdown variant="outline-secondary" class="variant-alt" text="{{$moneda=='PEN'?'Facturadas en soles':'Facturadas en dólares'}}">
                                            <b-dropdown-item href="?moneda=PEN">Ventas facturadas en soles</b-dropdown-item>
                                            <b-dropdown-item href="?moneda=USD">Ventas facturadas en dólares</b-dropdown-item>
                                        </b-dropdown>
                                    </template>
                                </b-input-group>
                            </div>
                            @if($moneda=='USD')
                                <div class="col-lg-3 form-group">
                                    <b-input-group>
                                        <b-input-group-prepend>
                                            <b-input-group-text>
                                                <i class="fas fa-dollar-sign"></i>
                                            </b-input-group-text>
                                        </b-input-group-prepend>
                                        <template #append>
                                            <b-dropdown variant="outline-secondary" class="variant-alt" text="{{$usar_tipo_cambio =='fecha-actual'?'Tipo cambio de hoy':'Tipo cambio de venta'}}">
                                                <b-dropdown-item href="?moneda=USD&tc=fecha-actual">Usar tipo cambio de hoy</b-dropdown-item>
                                                <b-dropdown-item href="?moneda=USD&tc=fecha-venta">Usar tipo cambio de fecha de venta</b-dropdown-item>
                                            </b-dropdown>
                                        </template>
                                    </b-input-group>
                                </div>
                            @endif
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
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card no-shadow">
                                    <div class="card-body">
                                        <line-chart :chart-data="chartValues" :labels="labels" :height="400"></line-chart>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 mt-3 text-center">
                                <h4 style="color:#119527">El volumen de ventas es demasiado grande para ser analizado.</h4>
                                <p>Genera el reporte de cada mes manualmente haciendo click en el botón <strong>GENERAR</strong> a la derecha de cada mes en la tabla inferior.
                                    <br> No tendrás que hacerlo siempre, pues se guardará en la base de datos. Solo vuelve a generarlo
                                    <br> si eliminas o editas ventas de algún mes en específico</p>
                            </div>
                            <div class="col-lg-12 mt-3">
                                <div class="card no-shadow">
                                    <div class="card-body">
                                        <div class="table-responsive tabla-gestionar">
                                            <table class="table table-striped table-hover table-sm">
                                                <thead class="bg-custom-green">
                                                <tr>
                                                    <th scope="col">Fecha</th>
                                                    <th scope="col">Ventas brutas</th>
                                                    <th scope="col">Impuestos</th>
                                                    <th scope="col">Ventas netas</th>
                                                    <th scope="col">Costo de bienes</th>
                                                    <th scope="col">Utilidad bruta</th>
                                                    <th scope="col">Generar reporte</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @if($ventas)
                                                    @foreach($ventas as $item)
                                                        <tr>
                                                            <td>{{ $item['fecha']}}</td>
                                                            <td>{{$moneda=='PEN'?'S/':'USD '}}{{number_format($item['ventas_brutas'],2)}}</td>
                                                            <td>S/{{number_format($item['impuestos'],2)}}</td>
                                                            <td>S/{{number_format($item['ventas_netas'],2)}}</td>
                                                            <td>S/{{number_format($item['costos'],2)}}</td>
                                                            <td style="color:{{$item['utilidad']<0?'red':'inherit'}}">S/{{number_format($item['utilidad'],2)}}</td>
                                                            <td><b-button href="/reportes/ventas/generar-mes/{{date('Y-m', strtotime($item['fecha']))}}" style="padding: 4px 10px;" class="btn btn-warning">Generar</b-button></td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="7" class="text-center">No hay datos para mostrar</td>
                                                    </tr>
                                                @endif
                                                </tbody>
                                            </table>
                                        </div>
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
                anio: '{{$anio}}',
                ventas: <?php echo json_encode($ventas) ?> ,
                chartValues: [],
                labels:[],
            },
            created(){
                this.set_data_chart();
            },
            methods:{
                obtenerReporte(){
                    window.location.href="/reportes/ventas/mensual/"+this.anio;
                },
                set_data_chart(){
                    labels=[];
                    datos=[];
                    if(this.ventas) {
                        for (let venta of this.ventas) {
                            let fecha = (venta.fecha).split('-');
                            labels.push(fecha[0]);
                            datos.push(venta.ventas_brutas);
                        }
                        this.chartValues=[
                            {
                                data: datos,
                                type: 'line'
                            }
                        ];
                        this.labels = labels;
                    }

                },
            }
        });
    </script>
@endsection