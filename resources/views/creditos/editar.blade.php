@extends('layouts.main')
@section('titulo', 'Editar crédito')
@section('contenido')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <h3 class="titulo-admin-1">
                    <a href="{{url()->previous()}}"><i class="fas fa-arrow-circle-left"></i></a>
                    {{$credito->facturacion->comprobante.' '.$credito->facturacion->serie.'-'.$credito->facturacion->correlativo}}
                </h3>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 mt-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <strong>Fecha emisión:</strong> {{date("d/m/Y H:i:s",strtotime($credito->fecha))}}
                                <hr>
                                <strong>Moneda:</strong>
                                @if($credito->facturacion->codigo_moneda=='PEN')
                                    SOLES <hr>
                                @else
                                    DÓLARES <hr>
                                @endif
                                <strong>Cliente:</strong> {{$credito->cliente['num_documento']}} - {{$credito->persona['nombre']}} {{$credito->personaAlias?'('.$credito->personaAlias->nombre.')':''}}<a href="javascript:void(0)" @click="abrir_modal()" class="ml-3"><i class="fas fa-user"></i> Alias de cliente</a>
                                <hr>
                                @if($credito->observacion)
                                    <strong>Observación:</strong> {{$credito->observacion}} <hr>
                                @endif
                                @if($credito->nota)
                                    <strong>Nota:</strong> {{$credito->nota}} <hr>
                                @endif
                            </div>
                            <div class="col-lg-6">
                                <p class="mb-0 float-right ml-5"><span class="badge badge-warning">Total por pagar</span><br>
                                    <span style="font-size: 30px;"><strong>S/{{number_format($credito->saldo,2)}}</strong></span>
                                </p>
                                <p class="mb-0 float-right ml-5"><span class="badge badge-success">Total Pagado</span><br>
                                    <span style="font-size: 30px;"><strong>S/{{number_format($credito->pagado,2)}}</strong></span>
                                </p>
                                <p class="mb-0 float-right"><span class="badge badge-primary">Total crédito</span><br>
                                    <span style="font-size: 30px;"><strong>S/{{$credito->total_venta}}</strong></span>
                                </p>
                            </div>
                            {{--
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
                                    <th scope="col">Cuota</th>
                                    <th scope="col">Vencimiento</th>
                                    <th scope="col">Monto</th>
                                    <th scope="col">Estado</th>
                                    <th scope="col">Opciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(cuota,index) in cuotas" :key="index" :class="{'tr-pagado':'pagado'==cuota.estado}">
                                    <td></td>
                                    <td>00@{{index + 1}}</td>
                                    <td>@{{ cuota.fecha }}</td>
                                    <td>@{{ cuota.monto}}</td>
                                    <td>
                                        <span v-show="cuota.total_pagado != 0" class="badge badge-success">PAGADO @{{ (Number(cuota.total_pagado)).toFixed(2) }}</span>
                                        <span v-show="cuota.total_adeuda != 0" class="badge badge-danger">SALDO @{{ (Number(cuota.total_adeuda)).toFixed(2) }}</span>
                                    </td>
                                    <td>
                                        <button @click="abrir_modal(cuota.idpago)" class="btn btn-warning" title="Agregar pago"><i class="fas fa-money-bill-wave"></i> Pagar
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
                    <input type="date" v-model="fecha"
                           class="form-control">
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <label>Método de pago:</label>
                    @php
                        $tipo_pago = \sysfact\Http\Controllers\Helpers\DataTipoPago::getTipoPago();
                    @endphp
                    <select v-model="metodo_pago" class="custom-select">
                        @foreach($tipo_pago as $pago)
                            @if(!($pago['num_val'] == 4 || $pago['num_val'] == 2))
                            <option value="{{$pago['num_val']}}">{{$pago['label']}}</option>
                            @endif
                        @endforeach
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
                    <input autocomplete="off" type="text" v-model="monto" class="form-control" onfocus="this.select()">
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
                            <td>@{{ formatDate(pago.fecha) }}</td>
                            <td>@{{ (Number(pago.monto)).toFixed(2) }}</td>
                            <td>@{{ pago.label}}</td>
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
    <b-modal id="modal-alias" ref="modal-alias" @hidden="alias = ''">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 mb-3">
                    <h5>{{$credito->cliente['num_documento']}} - {{$credito->persona['nombre']}}</h5>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <p>Escribe un alias de cliente para control interno</p>
                </div>
                <div class="col-lg-12">
                    <autocomplete-cliente-pedido v-on:agregar_cliente="agregarCliente"
                                                 v-on:borrar_cliente="borrarCliente"
                                                 ref="suggestCliente"></autocomplete-cliente-pedido>
                </div>
            </div>
        </div>
        <template #modal-footer="{ ok, cancel}">
            <b-button variant="secondary" @click="cancel()">Cancelar</b-button>
            <b-button variant="primary" @click="agregar_alias">Ok
            </b-button>
        </template>
    </b-modal>
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
                estado: 1,
                alias: {
                    idcliente:null,
                    nombre:null
                }
            },
            methods: {
                agregarCliente(obj) {
                    console.log(obj);
                    this.alias = {
                        idcliente: obj['idcliente'],
                        nombre: obj['nombre']
                    }
                },
                borrarCliente() {
                    this.alias = {
                        idcliente: null,
                        nombre: null
                    }
                },
                formatDate(date) {
                    const d = new Date(date);
                    const correctedDate = new Date(d.getTime() + d.getTimezoneOffset() * 60000);
                    const day = ('0' + correctedDate.getDate()).slice(-2);
                    const month = ('0' + (correctedDate.getMonth() + 1)).slice(-2);
                    const year = correctedDate.getFullYear();
                    return `${day}/${month}/${year}`;
                },
                agregar_alias() {
                    if(!this.alias.nombre){
                        alert("Alias de cliente no puede estar vacío.")
                        return;
                    }
                    axios.post("{{action('CreditoController@set_alias')}}", {
                        'idventa': this.idventa,
                        'alias': JSON.stringify(this.alias)
                    })
                        .then(() => {
                            location.reload(true)
                        })
                        .catch(error => {
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });
                },
                abrir_modal(idpago){
                    if(idpago){
                        this.idpago=idpago;
                        this.$refs['modal-1'].show();
                        this.obtenerPagos();
                    } else{
                        this.obtenerAlias();
                        this.$refs['modal-alias'].show();
                    }

                },
                borrarPago(index){
                    this.detalle.splice(index,1);
                    this.disabledButtonPago = true;
                    this.estado = 1;
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
                obtenerAlias(){
                    axios.get('/creditos/get-alias'+'/'+this.idventa)
                        .then(response => {
                            this.alias = response.data;
                            let obj = {idcliente:this.alias.idcliente,nombre:this.alias.nombre};
                            if(obj.idcliente){
                                this.$refs['suggestCliente'].setCliente(obj);
                            }
                        })
                        .catch(error => {
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });
                },
                obtenerPagos(){
                    axios.post("{{action('CreditoController@ver_pagos')}}",{
                            'idpago':this.idpago
                        })
                        .then(response => {
                            this.detalle = response.data.detalle;
                            this.suma_cuotas=response.data.pagado;
                            this.monto = (response.data.adeuda).toFixed(2);
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
                            this.total_pagado = this.cuotas.total_pagado;
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
                    if (!this.fecha) errorDatos.push('*El campo fecha no tiene el formato correcto');
                    if (this.monto == 0) errorDatos.push('*El monto debe ser mayor a 0.00');
                    if (isNaN(this.monto)) errorDatos.push('*El monto debe ser un número');
                    let saldo = (this.saldo).toFixed(2);
                    if(this.monto>Number(saldo)) errorDatos.push('*El monto excede el saldo o total de la cuota');
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