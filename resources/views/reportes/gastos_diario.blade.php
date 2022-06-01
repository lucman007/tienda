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
                <b-dropdown variant="primary" text="Gastos diarios">
                    <b-dropdown-item href="{{action('ReporteController@reporte_gastos')}}">Resumen de gastos</b-dropdown-item>
                    <b-dropdown-item href="{{url('/reportes/gastos/diario').'/'.date('Y-m')}}">Gastos diarios</b-dropdown-item>
                    <b-dropdown-item href="{{url('/reportes/gastos/mensual').'/'.date('Y')}}">Gastos mensuales</b-dropdown-item>
                </b-dropdown>
            </div>
            <div class="col-lg-9">
                <div class="row">
                    <div class="col-lg-4 form-group">
                        <label for="start">Seleccionar mes</label>
                        <input @change="obtenerReporte" v-model="mes" type="month" id="start" name="start" min="2020-01" class="form-control">
                    </div>
                    <div class="col-lg-3 form-group">
                        <label style="width: 100%" for="start">Exportar a excel</label>
                        <b-button :disabled="!gastos" :href="'/reportes/exportar/gastos_diario/'+mes" variant="primary"><i class="fas fa-file-export"></i> Exportar...</b-button>
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
                                Reporte gastos diario
                            </div>
                            <div class="card-body">
                                <div class="table-responsive tabla-gestionar">
                                    <table class="table table-striped table-hover table-sm">
                                        <thead class="bg-custom-green">
                                        <tr>
                                            <th scope="col">Día</th>
                                            <th scope="col">Total del día</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="item of gastos">
                                            <td>@{{ item.fecha }}</td>
                                            <td>@{{ (item.total_dia).toFixed(2)}}</td>
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
                mes: '{{$mes}}',
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
                    window.location.href="/reportes/gastos/diario/"+this.mes;
                },
                set_data_chart(){

                    labels=[];
                    datos=[];
                    suma_soles=0;
                    suma_dolares=0;

                    if(this.gastos) {
                        for (let venta of this.gastos) {
                            let fecha = (venta.fecha).split('-');
                            labels.push(fecha[0] + '/' + fecha[1]);
                            datos.push(venta.total_dia);
                            //Calcular total
                            suma_soles += venta.total_dia;
                        }
                        this.total_soles=suma_soles;

                        this.chartValues = {
                            labels: labels.reverse(),
                            datasets: [
                                {
                                    label: 'Gastos diarios S/',
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