@extends('layouts.main')
@section('titulo', 'Gestionar créditos')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1">Créditos</h3>
            </div>
            <div class="col-lg-3">
                @include('caja.buscador')
            </div>
        </div>
        @if($textoBuscado!='')
            <div class="row">
                <div class="col-lg-12 mt-5">
                    <div class="alert alert-dark" role="alert"><h5 class="mb-0">Resultados de búsqueda
                            para: {{$textoBuscado}}
                            <a href="{{url('/caja')}}"><i class="fa fa-times float-right"></i></a></h5></div>
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-sm-12 mt-4">
                <div class="card">
                    <div class="card-header">
                        Lista de ventas a crédito
                    </div>
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
                                    <th scope="col">Vencimiento</th>
                                    <th scope="col" style="width: 12%">Comprobante</th>
                                    <th scope="col">Estado</th>
                                    <th scope="col">Opciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($creditos) > 0)
                                    @foreach($creditos as $venta)
                                        <tr>
                                            <td></td>
                                            <td style="width: 5%">{{$venta->idventa}}</td>
                                            <td style="width: 15%">{{date("d-m-Y",strtotime($venta->fecha))}}</td>
                                            <td>{{$venta->cliente->persona->nombre}}</td>
                                            <td>{{$venta->total_venta}}</td>
                                            <td>{{$venta->facturacion->codigo_moneda}}</td>
                                            <td>{{date("d-m-Y",strtotime($venta->fecha_vencimiento))}}</td>
                                            <td>{{$venta->facturacion->serie}}-{{$venta->facturacion->correlativo}}<br>
                                                {{$venta->guia_relacionada['correlativo']}}
                                            </td>
                                            <td><span class="badge {{$venta->estado=='ADEUDA'?'badge-danger':'badge-success'}}">{{$venta->estado}}</span></td>
                                            <td class="botones-accion" style="width: 10%">
                                                <button @click="editarCredito({{$venta->idventa}},{{$venta->total_venta}})" class="btn btn-success" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
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
                        {{$creditos->links('layouts.paginacion')}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--INICIO MODAL PRODUCTOS-->
    <b-modal id="modal-1" ref="modal-1" size="lg"
             title="" @hidden="resetModal" ok-only>
    <template slot="modal-title">
        Pagar crédito
    </template>
    <div class="container">
        <div class="row">
            <div class="col-lg-3">
                <div class="form-group">
                    <label>Tipo:</label>
                    <select :disabled="disabled_tipo_operacion" v-model="tipo_operacion" class="custom-select">
                        <option value="1">Pago total</option>
                        <option value="2">Amortización</option>
                    </select>
                </div>
            </div>
            <div v-show="tipo_operacion==2" class="col-lg-2">
                <div class="form-group">
                    <label for="importe">Importe:</label>
                    <input autocomplete="off" type="text" v-model="importe" name="importe" class="form-control">
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <label>Tipo pago:</label>
                    <select v-model="tipo_pago" class="custom-select">
                        <option value="1">Efectivo</option>
                        <option value="2">Depósito</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-3 mb-3">
                <label for="importe">Agregar pago:</label>
                <button :disabled="estado == 'PAGADO' || (suma_cuotas >= total_venta)" @click="agregarPago" class="btn btn-primary"><i v-show="!agregarPagoSpinner" class="fas fa-plus"></i>
                    <b-spinner v-show="agregarPagoSpinner" small label="Loading..." ></b-spinner> Agregar
                </button>
            </div>
            <div class="col-lg-12">
                <div class="table-responsive tabla-gestionar">
                    <table class="table table-striped table-hover table-sm">
                        <thead class="bg-custom-green">
                        <tr>
                            <th scope="col">Fecha</th>
                            <th scope="col">Importe</th>
                            <th scope="col">Tipo de pago</th>
                            <th scope="col"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="cuota in cuotas">
                            <td>@{{ cuota.fecha }}</td>
                            <td>@{{ cuota.importe }}</td>
                            <td>@{{ cuota.tipo_pago==1?'Efectivo':'Depósito' }}</td>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="alert alert-success text-right" role="alert">
                    <div class="row">
                        <div class="col-lg-6"><strong>Total pagado: @{{ suma_cuotas.toFixed(2) }} </strong></div>
                        <div class="col-lg-6"><strong>Saldo: @{{ saldo.toFixed(2) }} </strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <template #modal-footer="{ok}">
        <b-button size="sm" variant="secondary" @click="ok()">
            Listo
        </b-button>
    </template>
    </b-modal>
    <!--FIN MODAL PRODUCTOS -->


@endsection
@section('script')
    <script>
        let app = new Vue({
            el: '.app',
            data: {
                importe:'',
                total_venta:'',
                idventa: -1,
                tipo_operacion: 1,
                disabled_tipo_operacion:false,
                tipo_pago: 1,
                cuotas: [],
                agregarPagoSpinner:false,
                suma_cuotas:0,
                saldo:0,
                estado:'ADEUDA'
            },
            methods: {
                editarCredito(id, total_venta){
                    this.idventa = id;
                    this.total_venta = total_venta;

                    _this=this;

                    axios.get("{{url('/caja/obtener_data_creditos')}}"+'/'+id)
                        .then(function (response) {
                            if(response.data){
                                let data = JSON.parse(response.data);
                                _this.cuotas= data.cuotas;
                                _this.tipo_operacion = data.tipo_operacion;
                                _this.estado = data.estado;
                                for(let cuota of _this.cuotas){
                                    _this.suma_cuotas+=Number(cuota.importe);
                                }

                                if(_this.tipo_operacion=="2"){
                                    _this.importe='';
                                    _this.disabled_tipo_operacion=true;
                                } else {

                                    if(_this.estado == 'PAGADO'){
                                        _this.disabled_tipo_operacion=true;
                                    } else{
                                        _this.disabled_tipo_operacion=false;
                                        _this.importe=total_venta;
                                    }
                                }

                                _this.saldo = _this.total_venta - _this.suma_cuotas;
                            } else{
                                _this.saldo = _this.total_venta;
                                _this.importe=total_venta;
                            }


                        })
                        .catch(function (error) {
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });

                    this.$refs['modal-1'].show();
                },
                agregarPago(){
                    if (isNaN(this.importe)){
                        alert('El importe debe ser un número');
                        return;
                    }
                    this.agregarPagoSpinner=true;
                    _this=this;
                    let dataset = {
                        'tipo_operacion': this.tipo_operacion,
                        'idventa': this.idventa,
                        'importe': this.importe,
                        'tipo_pago': this.tipo_pago,
                        'suma_cuotas':Number(this.suma_cuotas) + Number(this.importe),
                        'total_venta':this.total_venta
                    };

                    let tipo_accion = "{{action('CajaController@agregar_pago_creditos')}}";

                    axios.post(tipo_accion, dataset)
                        .then(function (response) {
                            _this.cuotas.push(response.data);

                            let suma= 0;

                            if(_this.tipo_operacion == "2"){
                                for(let cuota of _this.cuotas){
                                    suma+=Number(cuota.importe);
                                }
                                _this.suma_cuotas = suma;
                                _this.saldo = _this.total_venta - _this.suma_cuotas;
                                _this.importe = '';
                            } else{
                                _this.suma_cuotas = _this.total_venta;
                                _this.saldo = 0;
                            }
                            _this.disabled_tipo_operacion=true;
                            _this.agregarPagoSpinner=false;
                        })
                        .catch(function (error) {
                            alert('Ha ocurrido un error.');
                            _this.agregarPagoSpinner=false;
                            console.log(error);
                        });

                },
                borrarPago(index){
                    this.cuotas.splice(index,1);
                },
                resetModal(){
                    this.cuotas = [];
                    this.tipo_operacion = 1;
                    this.tipo_pago = 1;
                    this.suma_cuotas=0;
                    this.saldo=0;
                    this.importe='';
                    this.idventa= -1;
                    this.disabled_tipo_operacion=false;
                    this.estado='ADEUDA';

                    window.location.reload();
                }
            },
            watch:{
                tipo_operacion(val){
                    if(val=='1'){
                        this.importe=this.total_venta;
                    } else{
                        this.importe='';
                    }
                },
            }

        });
    </script>
@endsection