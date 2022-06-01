@extends('layouts.main')
@section('titulo', 'Editar crédito')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-sm-12">
                <h3 class="titulo-admin-1">{{$credito->facturacion->comprobante.' '.$credito->facturacion->serie.'-'.$credito->facturacion->correlativo}}</h3>
                <b-button href="{{action('CreditoController@index')}}" class="mr-2"  variant="primary"><i class="fas fa-list"></i> Ver créditos</b-button>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 mt-4 mb-3">
                <div class="card">
                    <div class="card-header">
                        Detalle
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-8">
                                <strong>Fecha emisión:</strong> {{date("d/m/Y H:i:s",strtotime($credito->fecha))}}
                                <hr>
                                <strong>Moneda:</strong>
                                @if($credito->facturacion->codigo_moneda=='S/')
                                    SOLES <hr>
                                @else
                                    DÓLARES <hr>
                                @endif
                                <strong>Cliente:</strong> {{$credito->cliente['num_documento']}} - {{$credito->persona['nombre']}}
                            </div>{{--
                            <div class="col-lg-3 offset-lg-3">
                                <button class="btn btn-primary float-right" title="Pagar"><i class="fas fa-dollar-sign"></i> Pago total
                                </button>
                            </div>--}}
                        </div>
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col">N° de cuota</th>
                                    <th scope="col">Monto</th>
                                    <th scope="col">Vencimiento</th>
                                    <th scope="col">Pagado</th>
                                    <th scope="col">Saldo</th>
                                    <th scope="col">Opciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(cuota,index) in cuotas" :key="index" :class="{'tr-pagado':'pagado'==cuota.estado}">
                                    <td></td>
                                    <td>00@{{index + 1}}</td>
                                    <td>@{{ cuota.monto}}</td>
                                    <td>@{{ cuota.fecha }}</td>
                                    <td>@{{ (Number(cuota.total_pagado)).toFixed(2) }}</td>
                                    <td>@{{ (Number(cuota.total_adeuda)).toFixed(2) }}</td>
                                    <td>
                                        <button @click="abrir_modal(cuota.idpago)" class="btn btn-success" title="Agregar pago"><i class="fas fa-dollar-sign"></i> Pagar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td v-show="cuotas.length == 0" class="text-center" colspan="6">
                                        No hay datos que mostrar
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--INICIO MODAL AGREGAR PAGO-->
    <b-modal id="modal-1" ref="modal-1" size="lg"
             title="" @hidden="resetModal" ok-only>
    <template slot="modal-title">
        Pagar crédito
    </template>
    <div class="container">
        <div class="row">
            <div class="col-lg-3">
                <div class="form-group">
                    <label>Fecha de pago:</label>
                    <input min="{{date('Y-m-d')}}" type="date" v-model="fecha"
                           class="form-control">
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <label>Método de pago:</label>
                    <select v-model="metodo_pago" class="custom-select">
                        <option value="1">Efectivo</option>
                        <option value="2">Tarjeta</option>
                        <option value="3">Depósito</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <label for="importe">N° operación:</label>
                    <input autocomplete="off" type="text" v-model="num_operacion" class="form-control">
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <label for="importe">Monto:</label>
                    <input autocomplete="off" type="text" v-model="monto" class="form-control">
                </div>
            </div>
            <div class="col-lg-2">
                <label></label>
                <button :disabled="disabledButtonPago" @click="agregarPago" class="btn btn-info d-block"><i v-show="!agregarPagoSpinner" class="fas fa-check"></i>
                    <b-spinner v-show="agregarPagoSpinner" small label="Loading..." ></b-spinner>
                </button>
            </div>
            <div class="col-lg-12">
                <div class="table-responsive tabla-gestionar">
                    <table class="table table-striped table-hover table-sm">
                        <thead class="bg-custom-green">
                        <tr>
                            <th scope="col">Fecha de pago</th>
                            <th scope="col">Monto</th>
                            <th scope="col">Tipo de pago</th>
                            <th scope="col">N° operación</th>
                            <th scope="col"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(pago,index) in detalle">
                            <td>@{{ pago.fecha }}</td>
                            <td>@{{ (Number(pago.monto)).toFixed(2) }}</td>
                            <td>@{{ pago.metodo_pago}}</td>
                            <td>@{{ pago.num_operacion }}</td>
                            <td>
                                <button :disabled="disabledButtonPago" @click="borrarPago(index)" class="btn btn-danger" title="Borrar pago"><i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="text-center">
                            <td colspan="5" v-show="detalle.length==0">No se ha realizado ningún pago</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="alert alert-success text-right" role="alert">
                    <div class="row">
                        <div class="col-lg-6"><strong>Pagado: @{{ suma_cuotas.toFixed(2) }} </strong></div>
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
    <!--FIN MODAL AGREGAR PAGO -->
@endsection
@section('script')
    <script>

        let app = new Vue({
            el: '.app',
            data: {
                cuotas:<?php echo json_encode($credito->pago) ?>,
                metodo_pago: 1,
                num_operacion: '',
                agregarPagoSpinner:false,
                suma_cuotas:0,
                saldo:0,
                fecha: '{{date('Y-m-d')}}',
                monto: '',
                idventa: <?php echo json_encode($credito->idventa) ?>,
                idpago: -1,
                detalle:[],
                disabledButtonPago:false,
                estado: 1
            },
            methods: {
                abrir_modal(idpago){
                    this.idpago=idpago;
                    this.$refs['modal-1'].show();
                    this.obtenerPagos();
                },
                borrarPago(index){
                    this.detalle.splice(index,1);
                    this.disabledButtonPago = true;
                    this.estado = 1
                    this.procesarPago()
                },
                agregarPago(){
                    if (this.validar()) {
                        return;
                    }
                    this.detalle.push({
                        'fecha':this.fecha,
                        'metodo_pago':this.metodo_pago,
                        'num_operacion':this.num_operacion,
                        'monto':this.monto,
                    });
                    this.disabledButtonPago = true;
                    this.agregarPagoSpinner=true;

                    if(Number(this.monto) + this.suma_cuotas == this.suma_cuotas + this.saldo){
                        this.estado = 2
                    }

                    this.procesarPago()

                },
                obtenerPagos(){

                    axios.post("{{action('CreditoController@ver_pagos')}}",{
                            'idpago':this.idpago
                        })
                        .then(response => {
                            this.detalle = response.data.detalle;
                            this.suma_cuotas=response.data.pagado;
                            this.saldo=response.data.adeuda;
                        })
                        .catch(error => {
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });
                },
                procesarPago(){
                    let dataset = {
                        'idpago': this.idpago,
                        'idventa': this.idventa,
                        'detalle': JSON.stringify(this.detalle),
                        'estado' : this.estado
                    };

                    let tipo_accion = "{{action('CreditoController@agregar_pago')}}";

                    axios.post(tipo_accion, dataset)
                        .then(response => {
                            this.cuotas = response.data;
                            this.obtenerPagos();
                            this.num_operacion = '';
                            this.monto = '';
                            this.disabledButtonPago = false;
                            this.agregarPagoSpinner=false;
                        })
                        .catch(error => {
                            alert('Ha ocurrido un error.');
                            this.agregarPagoSpinner=false;
                            this.disabledButtonPago = false;
                            console.log(error);
                        });
                },
                validar(){
                    let error = 0;
                    let errorDatos = [];
                    let errorString = '';
                    if (this.monto.length == 0) errorDatos.push('*El campo monto no puede estar vacío');
                    if (this.monto == 0) errorDatos.push('*El monto debe ser mayor a 0.00');
                    if (isNaN(this.monto)) errorDatos.push('*El monto debe ser un número');
                    if(this.monto>this.saldo) errorDatos.push('*El monto excede el saldo o total de la cuota');
                    if (errorDatos.length) {
                        error = 1;
                        for (let error of errorDatos) {
                            errorString += error + '\n';
                        }
                        alert(errorString);
                    }

                    return error;
                },
                resetModal(){
                    this.detalle = [];
                    this.monto = '';
                    this.fecha = '{{date('Y-m-d')}}';
                    this.metodo_pago = 1;
                    this.num_operacion = '';
                    this.disabledButtonPago = false;
                }
            }

        });
    </script>
@endsection