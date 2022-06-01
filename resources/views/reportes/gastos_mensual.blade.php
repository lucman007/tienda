@extends('layouts.main')
@section('titulo', 'Reporte de gastos')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1">Reporte de gastos</h3>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-2">
                <label style="width: 100%"><i class="far fa-list-alt"></i> Tipo</label>
                <b-dropdown variant="primary" text="Gastos mensuales">
                    <b-dropdown-item href="{{action('ReporteController@reporte_gastos')}}">Resumen de gastos</b-dropdown-item>
                    <b-dropdown-item href="{{url('/reportes/gastos/diario').'/'.date('Y-m')}}">Gastos diarios</b-dropdown-item>
                    <b-dropdown-item href="{{url('/reportes/gastos/mensual').'/'.date('Y')}}">Gastos mensuales</b-dropdown-item>
                </b-dropdown>
            </div>
            <div class="col-lg-9">
                <div class="row">
                    <div class="col-lg-4 form-group">
                        <label for="anio">Seleccionar año</label>
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
                    </div>
                    <div class="col-lg-3 form-group">
                        <label style="width: 100%" for="start">Exportar a excel</label>
                        <b-button :disabled="!gastos" :href="'/reportes/exportar/gastos_mensual/'+anio" variant="primary"><i class="fas fa-file-export"></i> Exportar...</b-button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                {{--Reporte 2--}}
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                Reporte de gastos mensual
                            </div>
                            <div class="card-body">
                                <div class="table-responsive tabla-gestionar">
                                    <table class="table table-striped table-hover table-sm">
                                        <thead class="bg-custom-green">
                                        <tr>
                                            <th scope="col">Mes</th>
                                            <th scope="col">Total del mes</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="item of gastos">
                                            <td>@{{ item.fecha }}</td>
                                            <td>@{{ (item.total_mes).toFixed(2)}}</td>
                                        </tr>
                                        <tr v-show="!gastos">
                                            <td colspan="3" class="text-center">No hay datos para mostrar</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="alert alert-success text-right" role="alert">
                                    <div class="row">
                                        <div class="offset-lg-6"></div>
                                        <div class="col-lg-6"><strong>Total: S/ @{{ total_soles }}</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                Gráfico de gastos
                            </div>
                            <div class="card-body">
                                <line-chart :chart-data="chartValues" :options="chartOptions"/>
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
                gastos: <?php echo json_encode($gastos) ?> ,
                total_soles:0,
                chartValues: {},
                chartOptions: {},
            },
            created(){
                this.set_data_chart();
            },
            methods:{
                obtenerReporte(){
                    window.location.href="/reportes/gastos/mensual/"+this.anio;
                },
                set_data_chart(){

                    labels=[];
                    datos=[];
                    suma_soles=0;
                    suma_dolares=0;

                    if(this.gastos) {
                        for (let venta of this.gastos) {
                            let fecha = (venta.fecha).split(' ');
                            labels.push(fecha[0]);
                            datos.push(venta.total_mes);
                            //Calcular total
                            suma_soles += venta.total_mes;
                        }
                        this.total_soles=suma_soles;

                        this.chartValues = {
                            labels: labels.reverse(),
                            datasets: [
                                {
                                    label: 'Gastos mensuales S/',
                                    backgroundColor: '#f83d2a',
                                    data: datos.reverse()
                                }
                            ]
                        };

                    }

                },
            }
        });
    </script>
@endsection