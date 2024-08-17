@extends('layouts.main')
@section('titulo', 'Dashboard')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-pie"></i> Gráfico de ventas
                    </div>
                    <div class="card-body">
                        <strong>Ventas {{date('M Y')}}</strong>
                        <doughnut-chart v-if="chartValues" :chart-data="chartValues"></doughnut-chart>
                    </div>
                    <div class="col-lg-12 mt-3">
                        <p>*Las ventas en dólares se muestran convertidos al tipo de cambio actual.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 mb-2">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-bell"></i> Notificaciones
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            @can('Créditos')
                                <li v-for="credito in ventasCredito">
                                    <div v-if="credito.dias > 0" class="alert alert-warning d-flex justify-content-between align-items-center px-4 py-2">
                                        <span>
                                            <i class="fas fa-receipt"></i>
                                            @{{ credito.comprobante }} a crédito @{{ credito.correlativo }} tiene pagos por vencer
                                        </span>
                                        <a :href="'/creditos/editar/'+credito.idventa" class="btn btn-warning btn-sm" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                    <div v-else class="alert alert-danger d-flex justify-content-between align-items-center px-4 py-1">
                                        <span>
                                            <i class="fas fa-receipt"></i>
                                            @{{ credito.comprobante }} a crédito @{{ credito.correlativo }} ha vencido
                                        </span>
                                        <a :href="'/creditos/editar/'+credito.idventa" class="btn btn-warning btn-sm" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </li>
                                <li v-show="ventasCredito.length" style="display: grid"><b-button variant="primary" href="/creditos" class="mb-3">Ver todos los créditos</b-button></li>
                            @endcan
                            @can('Inventario: kardex')
                                <li v-for="producto in productosStock">
                                    <div class="alert alert-danger" v-if="producto.cantidad<=0"><i class="fas fa-exclamation-circle" style="color: #ff5e00;"></i> Producto @{{ producto.nombre }} está agotado (@{{producto.cantidad}} UND)</div>
                                    <div class="alert alert-primary" v-else="producto.cantidad<0"><i class="fas fa-frown" style="color: #19b77d;;"></i> Producto @{{ producto.nombre }} está por agotarse (@{{producto.cantidad}} UND)</div>
                                </li>
                            @endcan
                            @can('Mantenimiento: empleados')
                                <li v-for="empleado in empleadosPago">
                                    <div class="alert alert-danger" v-if="empleado.dias_restantes==0"><i class="fas fa-exclamation-circle" style="color: #ff5e00;"></i> ¡Hoy! Pago de trabajador: @{{empleado.persona.nombre}} @{{empleado.persona.apellidos}}
                                        (@{{empleado.dia_pago}})</div>
                                    <div class="alert alert-primary" v-else="empleado.dias_restantes!=0"><i class="fas fa-hand-holding-usd" style="color: #19b77d;"></i> Próximo pago de trabajador: @{{empleado.persona.nombre}} @{{empleado.persona.apellidos}}
                                        (@{{empleado.dia_pago}})</div>
                                </li>
                            @endcan
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 mb-2">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-line"></i> Tipo de cambio
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="alert alert-primary">
                                    Compra:
                                    <h3>S/ {{cache('opciones')['tipo_cambio_compra']}}</h3>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="alert alert-success">
                                    Venta:
                                    <h3>S/ {{cache('opciones')['tipo_cambio_venta']}}</h3>
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
            data:{
                chartValues: null,
                mes: '{{date('Y-m')}}',
                productosStock:[],
                empleadosPago:[],
                ventasCredito:[],
            },
            created(){
                this.obtenerReporte();
                this.obtenerStockBajo();
                this.obtenerPagoEmpleados();
                this.obtenerVentasCredito();
            },
            methods:{
                obtenerReporte(){
                    axios.get('{{action('HomeController@obtenerReporte')}}')
                        .then(response => {
                            let data = response.data;
                            //Setear datos en chart
                            this.chartValues = [
                                {value: data.total_neto.toFixed(2), name: 'Ventas netas S/'},
                                {value: data.total_impuestos.toFixed(2), name: 'Impuestos S/'}
                            ];
                        })
                        .catch(error => {
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });
                },
                obtenerStockBajo(){
                    axios.get('{{action('HomeController@obtener_stock_bajo')}}')
                    .then(response => {
                        this.productosStock=response.data;
                    })
                    .catch(error => {
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });
                },
                obtenerPagoEmpleados(){
                    axios.get('{{action('HomeController@obtener_pago_empleados')}}')
                        .then(response => {
                            this.empleadosPago=response.data;
                        })
                        .catch(error => {
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });
                },
                obtenerVentasCredito(){
                    axios.get('{{action('HomeController@obtener_ventas_credito')}}')
                        .then(response => {
                            this.ventasCredito=response.data;
                        })
                        .catch(error => {
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });
                },
            }
        })
    </script>

@endsection