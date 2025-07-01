@extends('layouts.consulta')
@section('titulo', 'Créditos')
@section('contenido')
    @php $agent = new \Jenssegers\Agent\Agent() @endphp
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row mt-4">
                            <div class="col-lg-12">
                                <div class="row">
                                    <div class="col-lg-12 mb-2 mb-lg-0">
                                        <div class="alert alert-primary">
                                            <strong>Cliente: {{$cliente}}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-2" v-if="totales">
                            <div class="col-lg-12">
                                <div class="card no-shadow">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-12 mb-3">
                                                <h5 class="d-inline" style="font-size: 18px"><strong>Resumen de deuda</strong></h5>
                                            </div>
                                            <div class="col-lg-12">
                                                {{--<span class="badge badge-primary mt-2">Transacciones en efectivo / transferencia:</span>--}}
                                                <div class="row" v-if="totales">
                                                    <div class="col-lg-12 d-flex flex-wrap flex-md-nowrap">
                                                        {{--<div class="mr-5">
                                                            <p class="mb-0"><span class="badge badge-primary">Total deuda</span><br>
                                                                <span style="font-size: 30px;"><strong>S/@{{totales.total_credito.toFixed(2)}}</strong></span>
                                                            </p>
                                                        </div>
                                                        <div class="mr-5">
                                                            <p class="mb-0"><span class="badge badge-success">Total pagado</span><br>
                                                                <span style="font-size: 30px;"><strong>S/@{{totales.pagado.toFixed(2)}}</strong></span>
                                                            </p>
                                                        </div>--}}
                                                        <div class="mr-5">
                                                            <p class="mb-0"><span class="badge badge-warning">Total por pagar</span><br>
                                                                <span style="font-size: 30px;"><strong>S/@{{totales.adeuda.toFixed(2)}}</strong></span>
                                                            </p>
                                                        </div>
                                                        <div class="ml-md-4">
                                                            <b-form-checkbox value="productos" unchecked-value="ventas" @change="filtrar" v-model="mostrar" switch size="lg" class="mt-3 mt-md-0float-md-right">
                                                                <p style="font-size: 1rem;">Ver lista de productos</p>
                                                            </b-form-checkbox>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
                                                    <th scope="col"><a href="?orderby=fecha&order={{$order}}">Fecha <span class="icon-hover @if($orderby=='fecha') icon-hover-active @endif">{!!$order_icon!!}</span></a></th>
                                                    @if($mostrar == 'productos')
                                                        <th scope="col">Precio</th>
                                                        <th scope="col">Cantidad</th>
                                                    @endif
                                                    <th scope="col">Importe</th>
                                                    @if($mostrar == 'productos')
                                                    <th scope="col">Producto</th>
                                                    @endif
                                                    <th scope="col" style="width: 12%">Comprobante</th>
                                                    <th scope="col">Estado</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @if(count($creditos) > 0)
                                                    @foreach($creditos as $venta)
                                                        @if($mostrar == 'ventas')
                                                        <tr>
                                                            <td></td>
                                                            <td style="width: 120px">{{date("d/m/Y",strtotime($venta->fecha))}}</td>
                                                            <td>{{$venta->facturacion->codigo_moneda=='PEN'?'S/':$venta->facturacion->codigo_moneda}}{{$venta->total_venta}}</td>
                                                            <td>{{$venta->facturacion->serie}}-{{$venta->facturacion->correlativo}}</td>
                                                            <td><span class="badge badge-warning">DEBE</span></td>
                                                        </tr>
                                                        @else
                                                            <tr>
                                                                <td></td>
                                                                <td style="width: 120px">{{date("d/m/Y",strtotime($venta->fecha))}}</td>
                                                                <td>{{$venta->monto}}</td>
                                                                <td>{{$venta->cantidad}}</td>
                                                                <td>{{$venta->codigo_moneda=='PEN'?'S/':$venta->codigo_moneda}}{{number_format($venta->monto*$venta->cantidad, 2)}}</td>
                                                                <td>{{$venta->producto.' '.mb_strtoupper($venta->descripcion)}}</td>
                                                                <td>{{$venta->serie}}-{{$venta->correlativo}}</td>
                                                                <td><span class="badge badge-warning">DEBE</span></td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    <tr class="text-center">
                                                        <td colspan="11">No hay datos que mostrar</td>
                                                    </tr>
                                                @endif
                                                </tbody>
                                            </table>
                                        </div>
                                        {{$creditos->links('layouts.paginacion')}}
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
                totales:null,
                mostrar:'{{$mostrar}}',
                segment:''
            },
            mounted(){
                this.obtenerBadget();
            },
            methods: {
                obtenerBadget(){
                    const url = location.href;
                    const partes = url.split('/');
                    this.segment = partes[5];
                    axios.get('/consulta/get-badget'+'/'+this.segment)
                        .then(response => {
                            this.totales = response.data;
                        })
                        .catch(error => {
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });
                },
                filtrar(){
                    window.location.href = '/consulta/creditos/'+this.segment+'/?mostrar='+this.mostrar;
                }
            }

        });
    </script>
    <style>
        .wrapper{
            padding-top: 30px !important;
        }
        p{
            font-family: 'Roboto', sans-serif;
        }
    </style>
@endsection