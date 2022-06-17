@extends('layouts.main')
@section('titulo', 'Caja')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-md-6 col-lg-8 mb-md-2">
                <h3 class="titulo-admin-1">Turnos</h3>
            </div>
            <div class="col-md-6 col-lg-4">
                <b-button href="/reportes/caja" class="btn btn-success float-right" title="Reportes">
                    <i class="fas fa-chart-line"></i> Ir a reportes
                </b-button>
                <b-input-group class="d-inline-flex float-right w-50 mr-3">
                    <b-input-group-prepend>
                        <b-input-group-text>
                            <i class="fas fa-calendar"></i>
                        </b-input-group-text>
                    </b-input-group-prepend>
                    <input @change="buscar" type="date" v-model="fecha" class="form-control" max="{{date('Y-m-d')}}">
                </b-input-group>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 mt-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            @if(!$caja)
                                <div class="col-lg-12 text-center">
                                    <h4 class="m-0 p-0">No has abierto caja :( </h4>
                                    @if($caja_abierta)
                                    <div class="row">
                                        <div class="col-md-6 offset-md-3 mt-3">
                                            <div class="alert alert-danger">
                                                <i class="fas fa-exclamation-triangle"></i> Hay una caja que no se ha cerrado. Debes cerrarla para poder abrir una nueva
                                            </div>
                                            <a href="/caja/{{date('Y-m-d',strtotime($caja_abierta->fecha_a))}}?turno={{$caja_abierta->turno}}" class="btn btn-primary">Ir a la caja abierta</a>
                                        </div>
                                    </div>
                                        @else
                                        @if($fecha == date('Y-m-d'))
                                        <div class="row">
                                            <div class="col-md-6 offset-md-3 mt-3">
                                                <b-button class="mr-2" v-b-modal.modal-1 variant="primary"><i class="fas fa-plus"></i> Abrir caja</b-button>
                                            </div>
                                        </div>
                                        @endif
                                    @endif
                                </div>
                            @else
                            <div class="col-lg-12 mb-4">
                                <b-nav tabs>
                                    @for($i=1; $i<=count($cajas); $i++)
                                    <b-nav-item href="/caja/{{$fecha}}?turno={{$i}}" @if($i == $caja->turno) active @endif>TURNO {{$i}}</b-nav-item>
                                    @endfor
                                    @if($fecha == date('Y-m-d'))
                                    <b-nav-item href="javascript:void(0)" @if($caja->estado) v-b-modal.modal-1 @else disabled onclick="alert('Debes cerrar el primer turno para abrir otro')" @endif><i class="fas fa-plus"></i> Abrir caja</b-nav-item>
                                    @endif
                                </b-nav>
                            </div>
                            <div class="col-lg-6">
                                <div class="card" style="box-shadow: none">
                                    <div class="card-header">
                                        Apertura
                                    </div>
                                    <div class="card-body" style="min-height: 430px">
                                        <p><strong>Estado de caja:</strong>
                                            @if($caja)
                                                <span class="badge badge-success">Abierta</span>
                                                @else
                                                <span class="badge badge-warning">Sin abrir</span>
                                            @endif
                                        </p>
                                        <div class="table-responsive tabla-gestionar">
                                            <table class="table table-striped table-hover table-sm">
                                                <tbody>
                                                @if($caja)
                                                    <tr>
                                                        <td><strong>Fecha y hora de apertura:</strong></td>
                                                        <td>{{ date('d/m/Y H:i:s', strtotime($caja->fecha_a)) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Responsable de caja:</strong></td>
                                                        <td>{{ $caja->empleado->nombre }} {{ $caja->empleado->apellidos }}</td>
                                                    </tr>
                                                @else
                                                    <tr>
                                                        <td><strong>Fecha y hora de apertura:</strong></td>
                                                        <td>0.00</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Responsable de caja:</strong></td>
                                                        <td>0.00</td>
                                                    </tr>
                                                @endif
                                                </tbody>
                                            </table>
                                        </div>
                                        @if($caja)
                                        <div class="alert alert-primary">
                                            <strong>Saldo inicial: @if($caja) {{$caja->moneda}} {{ $caja->apertura }} @endif</strong>
                                        </div>
                                        <p><strong>Observación:</strong> {{$caja->observacion_a}}</p>
                                            @if(!$caja->estado)
                                            <b-button @click="editarApertura" class="btn btn-success" style="position: absolute;right: 30px;bottom: 30px;"><i class="fas fa-edit"></i> Editar</b-button>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card" style="box-shadow: none">
                                    <div class="card-header">
                                        Cierre
                                    </div>
                                    <div class="card-body" style="min-height: 430px">
                                        <div class="row">
                                            <div class="col-lg-5">
                                                <p><strong>Estado de caja:</strong>
                                                    @if($caja && $caja->estado)
                                                        <span class="badge badge-success">Cerrada</span>
                                                    @else
                                                        <span class="badge badge-warning">Sin cerrar</span>
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="col-lg-7">
                                                <b-button @if(!$caja) disabled @endif  target="_blank" :href="'/caja/imprimir/' + idcaja" class="btn btn-warning float-right mr-3" title="Imprimir">
                                                    <i class="fas fa-print"></i> Imprimir
                                                </b-button>
                                                <b-button  @if(!$caja || ($caja && $caja->estado)) disabled @endif class="mr-2 float-right" @click="cerrar_caja" variant="primary">
                                                    <i class="fas fa-check"></i> Cerrar caja
                                                </b-button>
                                            </div>
                                        </div>
                                        <div class="table-responsive tabla-gestionar">
                                            <table class="table table-striped table-hover table-sm">
                                                <tbody>
                                                @if($caja && $caja->estado)
                                                    <tr>
                                                        <td><strong>Fecha y hora de cierre:</strong></td>
                                                        <td>{{ date('d/m/Y H:i:s', strtotime($caja->fecha_c)) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Saldo inicial:</strong></td>
                                                        <td>{{$caja->moneda}} {{ $caja->apertura }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Total efectivo:</strong></td>
                                                        <td>{{$caja->moneda}} {{ $caja->efectivo }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Total tarjeta:</strong></td>
                                                        <td>{{$caja->moneda}} {{ $caja->tarjeta }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Total crédito:</strong></td>
                                                        <td>{{$caja->moneda}} {{ $caja->credito }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Total gastos:</strong></td>
                                                        <td>{{$caja->moneda}} {{ $caja->gastos }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Total devoluciones:</strong></td>
                                                        <td>{{$caja->moneda}} {{ $caja->devoluciones }}</td>
                                                    </tr>
                                                @endif
                                                </tbody>
                                            </table>
                                        </div>
                                        @if($caja && $caja->estado)
                                        <div class="alert alert-primary">
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <i class="fas fa-check-circle" style="font-size: 65px;color: #28a745;"></i>
                                                </div>
                                                <div class="col-md-5">
                                                    <p style="font-size: 18px; margin: 0"><strong>Total efectivo teórico: <br>
                                                            <span style="font-size: 25px">{{$caja->moneda}} {{ $caja->efectivo_teorico }}</span></strong></p>
                                                </div>
                                                <div class="col-md-5 d-flex align-items-center">
                                                    <strong>
                                                        Total efectivo real: {{$caja->moneda}} {{ $caja->efectivo_real }} <br>
                                                        Descuadre: <span style="color:{{$caja->descuadre >= 0?'green':'red'}}"> {{$caja->moneda}} {{ $caja->descuadre }}</span>
                                                    </strong>
                                                </div>
                                            </div>
                                        </div>
                                        <p><strong>Observación:</strong> {{ $caja->observacion_c }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--INICIO MODAL APERTURA-->
    <b-modal id="modal-1" ref="modal-1" size="md" @hidden="resetModal">
    <template slot="modal-title">
        @{{ tituloCaja }}
    </template>
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <b-input-group>
                    <b-input-group-prepend>
                        <b-input-group-text>
                            <i class="fas fa-clock"></i>
                        </b-input-group-text>
                    </b-input-group-prepend>
                    <input type="text" class="form-control" disabled :value="'TURNO ' + (accion=='editar'?turno:nuevo_turno)">
                </b-input-group>
            </div>
            <div class="col-lg-6">
                <b-form-checkbox @change="" v-model="modo_arqueo" switch size="lg"><p style="font-size: 1rem;">Modo arqueo</p></b-form-checkbox>
            </div>
            <div v-show="!modo_arqueo" class="col-lg-12">
                <div class="row">
                    <div class="col-lg-6">
                        <label for="total_apertura">Ingrese saldo inicial:</label>
                        <b-input-group>
                            <b-input-group-prepend>
                                <b-input-group-text>
                                    S/
                                </b-input-group-text>
                            </b-input-group-prepend>
                            <input type="number" v-model="total_apertura" class="form-control" autocomplete="off" onclick="this.select()">
                        </b-input-group>
                    </div>
                </div>
            </div>
            <div v-show="modo_arqueo" class="col-lg-12">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="pen_1">Monedas de S/0.10</label>
                            <input @keyup="calcular" type="number" v-model="arqueo.pen_1" class="form-control" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="pen_2">Monedas de S/0.20</label>
                            <input @keyup="calcular" type="number" v-model="arqueo.pen_2" class="form-control" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="pen_3">Monedas de S/0.50</label>
                            <input @keyup="calcular" type="number" v-model="arqueo.pen_3" class="form-control" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="pen_4">Monedas de S/1.00</label>
                            <input @keyup="calcular" type="number" v-model="arqueo.pen_4" class="form-control" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="pen_5">Monedas de S/2.00</label>
                            <input @keyup="calcular" type="number" v-model="arqueo.pen_5" class="form-control" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="pen_6">Monedas de S/5.00</label>
                            <input @keyup="calcular" type="number" v-model="arqueo.pen_6" class="form-control" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="pen_7">Billetes de S/10.00</label>
                            <input @keyup="calcular" type="number" v-model="arqueo.pen_7" class="form-control" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="pen_8">Billetes de S/20.00</label>
                            <input @keyup="calcular" type="number" v-model="arqueo.pen_8" class="form-control" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="pen_9">Billetes de S/50.00</label>
                            <input @keyup="calcular" type="number" v-model="arqueo.pen_9" class="form-control" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="pen_10">Billetes de S/100.00</label>
                            <input @keyup="calcular" type="number" v-model="arqueo.pen_10" class="form-control" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="pen_11">Billetes de S/200.00</label>
                            <input @keyup="calcular" type="number" v-model="arqueo.pen_11" class="form-control" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="total_apertura_arqueo">Total apertura:</label>
                            <input @keyup="calcular" type="number" v-model="total_apertura_arqueo" class="form-control" disabled>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 mt-3">
                <div class="form-group">
                    <label for="total_apertura">Observación:</label>
                    <textarea v-model="observacion_a" class="form-control" rows="4"></textarea>
                </div>
            </div>
            <div class="col-lg-12">
                <b-form-checkbox @change="" v-model="notificacion" switch size="lg"><p style="font-size: 1rem;">Notificar al administrador</p></b-form-checkbox>
            </div>
            <div class="col-lg-12" v-show="notificacion">
                <div class="form-group">
                    @if(json_decode(cache('config')['mail_contact'], true)['notificacion_caja'])
                        <label for="total_apertura">Correo:</label>
                        <input disabled type="text" class="form-control" autocomplete="off" value="{{json_decode(cache('config')['mail_contact'], true)['notificacion_caja']}}">
                    @else
                        <p>No se ha configurado el correo del administrador. <a href="/configuracion?tab=email">Configurar aquí</a></p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <template #modal-footer="{ ok, cancel}">
        <b-button variant="secondary" @click="cancel()">
            Cancel
        </b-button>
        <b-button :disabled="!total_apertura" @click="abrirCaja" variant="primary">
            <b-spinner v-show="mostrarProgreso" small label="Loading..." ></b-spinner>
            <span v-show="!mostrarProgreso">OK</span>
        </b-button>
    </template>
    </b-modal>
    <!--FIN MODAL APERTURA-->
    <!--INICIO MODAL CIERRE-->
    <b-modal id="modal-2" ref="modal-2" size="md"
             title="" @ok="procesar_cierre" @hidden="resetModal" @shown="focus()">
    <template slot="modal-title">
        Cerrar caja
    </template>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="apertura">Saldo inicial:</label>
                    <input type="text" v-model="apertura" class="form-control" disabled>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="efectivo">Efectivo extra:</label>
                    <input type="text" v-model="extras" class="form-control" disabled>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="efectivo">Ventas en efectivo:</label>
                    <input type="text" v-model="efectivo" class="form-control" disabled>
                </div>
            </div>            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="gastos">Total gastos:</label>
                    <input type="text" v-model="gastos" class="form-control" disabled>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="credito">Ventas a crédito:</label>
                    <input type="text" v-model="credito" class="form-control" disabled>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tarjeta">Ventas con tarjeta:</label>
                    <input type="text" v-model="tarjeta" class="form-control" disabled>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tarjeta">Total devoluciones:</label>
                    <input type="text" v-model="devoluciones" class="form-control" disabled>
                </div>
            </div>
            <div class="col-md-6">
                <div class="alert alert-success">
                    <label><strong>Efectivo teórico en caja:</strong></label>
                    <h3 class="mb-0">S/ @{{ efectivo_teorico }}</h3>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label><strong>Efectivo real en caja:</strong></label>
                    <input v-model="total_real" @keyup="calcularDescuadre" class="form-control" type="number" id="focusthis">
                </div>
            </div>
            <div v-show="dolares > 0" class="col-md-6">
                <div class="form-group">
                    <label>Ventas en dólares:</label>
                    <h3>USD @{{ dolares }}</h3>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tarjeta">Descuadre:</label>
                    <input type="text" v-model="descuadre" class="form-control" disabled>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="observacion_c">Observación:</label>
                    <textarea v-model="observacion_c" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="col-md-12">
                <b-form-checkbox @change="" v-model="notificacion" switch size="lg"><p style="font-size: 1rem;">Notificar al administrador</p></b-form-checkbox>
            </div>
            <div class="col-md-12" v-show="notificacion">
                <div class="form-group">
                    @if(json_decode(cache('config')['mail_contact'], true)['notificacion_caja'])
                        <label for="total_apertura">Correo:</label>
                        <input disabled type="text" class="form-control" autocomplete="off" value="{{json_decode(cache('config')['mail_contact'], true)['notificacion_caja']}}">
                    @else
                        <p>No se ha configurado el correo del administrador. <a href="/configuracion?tab=email">Configurar aquí</a></p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <template #modal-footer="{ ok, cancel}">
        <b-button variant="secondary" @click="cancel()">
            Cancel
        </b-button>
        <b-button :disabled="!total_real" @click="procesar_cierre" variant="primary">
            <b-spinner v-show="mostrarProgreso" small label="Loading..." ></b-spinner>
            <span v-show="!mostrarProgreso">OK</span>
        </b-button>
    </template>
    </b-modal>
    <!--FIN MODAL CIERRE-->
@endsection
@section('script')
    <script>

        let app = new Vue({
            el: '.app',
            data: {
                mostrarProgreso:false,
                tituloCaja:'Abrir caja',
                fecha: '{{$fecha}}',
                modo_arqueo:0,
                accion:'insertar',
                monto:'',
                arqueo:{
                    pen_1:'',
                    pen_2:'',
                    pen_3:'',
                    pen_4:'',
                    pen_5:'',
                    pen_6:'',
                    pen_7:'',
                    pen_8:'',
                    pen_9:'',
                    pen_10:'',
                    pen_11:''
                },
                total_apertura:'0.00',
                total_apertura_arqueo:'0.00',
                idcaja:'{{$caja->idcaja??-1}}',
                apertura:'',
                efectivo:'',
                extras:'',
                credito:'',
                tarjeta:'',
                devoluciones:'',
                gastos:'',
                efectivo_teorico:'',
                dolares:'',
                observacion_c:'',
                notificacion: false,
                observacion_a:'',
                descuadre:'0.00',
                total_real:'',
                turno: "{{$caja->turno??1}}",
                nuevo_turno:"{{count($cajas)+1??1}}",
            },
            methods: {
                focus(){
                    let input = document.getElementById("focusthis");
                    input.focus();
                },
                buscar(){
                    window.location.href='/caja/'+this.fecha;
                },
                calcularDescuadre(){
                    if (this.timer) {
                        clearTimeout(this.timer);
                        this.timer = null;
                    }
                    this.timer = setTimeout(() => {
                        this.descuadre = ((this.efectivo_teorico - this.total_real) * -1).toFixed(2);
                    }, 400);
                },
                abrirCaja(){
                    let apertura=0;
                    this.mostrarProgreso=true;
                    if(this.modo_arqueo){
                        apertura=this.total_apertura_arqueo;
                    } else{
                        apertura=this.total_apertura;
                    }

                    let dataset = {
                        'idcaja':this.idcaja,
                        'apertura':apertura,
                        'observacion_a':this.observacion_a,
                        'turno':this.nuevo_turno,
                        'notificacion':this.notificacion,
                    };

                    let tipo_accion = this.accion == 'insertar' ? '{{action('CajaController@abrir_caja')}}' : '{{action('CajaController@update')}}';

                    axios.post(tipo_accion, dataset)
                        .then((response) => {
                            if(response.data===1){
                                window.location.href = "/caja/"+this.fecha+"?turno="+(this.accion=='editar'?this.turno:this.nuevo_turno);
                            } else {
                                this.mostrarProgreso=false;
                                alert('Ha ocurrido un error al abrir caja.');
                                console.log(response.data);
                            }

                        })
                        .catch((error) => {
                            this.mostrarProgreso=false;
                            alert('Ha ocurrido un error al abrir caja.');
                            console.log(error);
                        });
                },
                cerrar_caja(){
                    axios.post('{{action('CajaController@obtener_datos_cierre')}}',{'idcaja':this.idcaja})
                        .then(response => {
                            let cierre = response.data;
                            this.$refs['modal-2'].show();
                            this.idcaja=cierre.idcaja;
                            this.apertura=cierre.apertura;
                            this.efectivo=cierre.efectivo.toFixed(2);
                            this.tarjeta=cierre.tarjeta.toFixed(2);
                            this.devoluciones=cierre.devoluciones.toFixed(2);
                            this.credito=cierre.credito.toFixed(2);
                            this.gastos=cierre.gastos.toFixed(2);
                            this.dolares=cierre.dolares.toFixed(2);
                            this.extras=cierre.extras.toFixed(2);
                            this.efectivo_teorico=cierre.total_cierre.toFixed(2);
                            this.total_real = cierre.total_cierre.toFixed(2);
                        })
                        .catch(function (error) {
                            alert('Ha ocurrido un error al obtener los datos de cierre.');
                            console.log(error);
                        });
                },
                procesar_cierre(){
                    this.mostrarProgreso=true;
                    axios.post('{{action('CajaController@procesar_cierre')}}',{
                        'idcaja':this.idcaja,
                        'apertura':this.apertura,
                        'efectivo':this.efectivo,
                        'tarjeta':this.tarjeta,
                        'devoluciones':this.devoluciones,
                        'credito':this.credito,
                        'gastos':this.gastos,
                        'extras':this.extras,
                        'efectivo_teorico':this.efectivo_teorico,
                        'efectivo_real':this.total_real,
                        'descuadre':this.descuadre,
                        'observacion_c':this.observacion_c,
                        'notificacion':this.notificacion,
                    })
                        .then((response) => {
                            if(response.data===1){
                                window.location.reload(true);
                            } else {
                                this.mostrarProgreso=false;
                                alert('Ha ocurrido un error al cerrar caja.');
                                console.log(response.data);
                            }

                        })
                        .catch((error) => {
                            this.mostrarProgreso=false;
                            alert('Ha ocurrido un error al procesar el cierre.');
                            console.log(error);
                        });
                },
                editarApertura(){
                    this.tituloCaja = 'Editar caja';
                    axios.get('/caja/editar-apertura'+'/'+this.idcaja)
                        .then(response => {
                            let datos = response.data;
                            this.total_apertura=datos.apertura;
                            this.turno =datos.turno;
                            this.observacion_a=datos.observacion_a;
                            this.accion = 'editar';
                            this.$refs['modal-1'].show();
                        })
                        .catch(function (error) {
                            alert('Ha ocurrido un error al obtener los datos');
                            console.log(error);
                        });
                },
                calcular(){
                    let suma=0;
                    for(let i=1; i<=11; i++) {
                        switch(i){
                            case 1:
                                val=(this.arqueo['pen_1']*0.1).toFixed(2);
                                break;
                            case 2:
                                val=(this.arqueo['pen_2']*0.2).toFixed(2);
                                break;
                            case 3:
                                val=(this.arqueo['pen_3']*0.5).toFixed(2);
                                break;
                            case 4:
                                val=(this.arqueo['pen_4']*1).toFixed(2);
                                break;
                            case 5:
                                val=(this.arqueo['pen_5']*2).toFixed(2);
                                break;
                            case 6:
                                val=(this.arqueo['pen_6']*5).toFixed(2);
                                break;
                            case 7:
                                val=(this.arqueo['pen_7']*10).toFixed(2);
                                break;
                            case 8:
                                val=(this.arqueo['pen_8']*20).toFixed(2);
                                break;
                            case 9:
                                val=(this.arqueo['pen_9']*50).toFixed(2);
                                break;
                            case 10:
                                val=(this.arqueo['pen_10']*100).toFixed(2);
                                break;
                            case 11:
                                val=(this.arqueo['pen_11']*200).toFixed(2);
                                break;
                        }
                        suma+=Number(val);
                    }

                    this.total_apertura_arqueo=suma.toFixed(2);

                },
                resetModal(){
                    this.observacion_a='';
                    this.monto='';
                    //this.accion='insertar';
                    this.tituloCaja = 'Abrir caja';
                }
            }
        });
    </script>
@endsection