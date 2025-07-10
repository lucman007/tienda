@extends('layouts.main')
@section('titulo', 'Reporte de ventas mensual')
@section('contenido')
    <div class="container-fluid">
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
                    <b-nav-item href="{{url('/reportes/anulados').'?tipo=ventas'}}">Ventas anuladas</b-nav-item>
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
                            <div class="col-lg-12 mt-3">
                                <div class="card no-shadow">
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-lg-12">
                                                <div class="card no-shadow">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-2 col-sm-6">
                                                                <p class="mb-0">Total ventas {{$moneda=='USD'?'dólares':''}} <br>
                                                                    <span style="font-size: 30px;"><strong>{{$moneda=='USD'?'USD':'S/'}} {{number_format($ventas[1]['bruto'],2)}}</strong></span>
                                                                </p>
                                                            </div>
                                                            <div class="col-md-2 col-sm-6">
                                                                <p class="mb-0">Total impuestos<br>
                                                                    <span style="font-size: 30px;"><strong>S/ {{number_format($ventas[1]['impuesto'],2)}}</strong></span>
                                                                </p>
                                                            </div>
                                                            <div class="col-md-2 col-sm-6">
                                                                <p class="mb-0">Total neto<br>
                                                                    <span style="font-size: 30px;"><strong>S/ {{number_format($ventas[1]['neto'],2)}}</strong></span>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="table-responsive tabla-gestionar">
                                            <table class="table table-striped table-hover table-sm">
                                                <thead class="bg-custom-green">
                                                <tr>
                                                    <th scope="col">Fecha</th>
                                                    <th scope="col">Ventas brutas</th>
                                                    <th scope="col">Impuestos</th>
                                                    <th scope="col">Ventas netas</th>
                                                    <th scope="col">Precio compra</th>
                                                    <th scope="col">Utilidad bruta</th>
                                                    <th scope="col">Reporte</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @if($ventas[0])
                                                    @foreach($ventas[0] as $item)
                                                        <tr>
                                                            <td>{{ $item['fecha']}}</td>
                                                            <td>{{$moneda=='PEN'?'S/':'USD '}}{{number_format($item['ventas_brutas'],2)}}</td>
                                                            <td>S/{{number_format($item['impuestos'],2)}}</td>
                                                            <td>S/{{number_format($item['ventas_netas'],2)}}</td>
                                                            <td>S/{{number_format($item['costos'],2)}}</td>
                                                            <td style="color:{{$item['utilidad']<0?'red':'inherit'}}">S/{{number_format($item['utilidad'],2)}}</td>
                                                            <td class="d-flex align-items-center">
                                                                <b-button title="Actualizar reporte" href="/reportes/ventas/generar-mes/{{date('Y-m', strtotime($item['fecha']))}}?moneda={{$moneda}}&tc={{$usar_tipo_cambio}}" style="padding: 4px 10px;" class="btn btn-warning"><i class="fas fa-sync"></i></b-button>
                                                                @if(isset($item['fecha_actualizacion']))
                                                                <p class="mb-0 ml-1 text-black-50">Actualizado el <br> {{$item['fecha_actualizacion']}}</p>
                                                                @endif
                                                            </td>
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
                ventas: <?php echo json_encode($ventas[0]) ?> ,
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
                    this.ventas.reverse();
                    if(this.ventas) {
                        for (let venta of this.ventas) {
                            let fecha = (venta.fecha).split('-');
                            labels.push(fecha[0]);
                            datos.push(venta.ventas_brutas);
                        }
                        this.chartValues=[
                            {
                                data: datos.reverse(),
                                type: 'line'
                            }
                        ];
                        this.labels = labels.reverse();
                    }

                },
            }
        });
    </script>
@endsection