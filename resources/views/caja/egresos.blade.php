@extends('layouts.main')
@section('titulo', 'Movimientos')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-lg-8">
                <h3 class="titulo-admin-1">Movimientos</h3>
            </div>
            <div class="col-lg-2 form-group">
                <range-calendar class="float-right" :inicio="desde + ' 00:00:00'" :fin="hasta + ' 00:00:00'" v-on:setparams="setParams"></range-calendar>
            </div>
            <div class="col-lg-2">
                <b-button href="/reportes/caja" class="btn btn-success float-right w-100" title="Reportes">
                    <i class="fas fa-chart-line"></i> Ir a reportes
                </b-button>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12 mb-4">
                                <b-nav tabs>
                                    @can('Caja: gestionar')
                                    <b-nav-item href="/caja/movimientos?tipo=gastos" :active="'{{$tipo_movimiento}}'==1">Gastos</b-nav-item>
                                    <b-nav-item href="/caja/movimientos?tipo=ingresos" :active="'{{$tipo_movimiento}}'==2">Ingreso de efectivo</b-nav-item>
                                    @endcan
                                    @can('Inventario: productos')
                                    <b-nav-item href="/caja/movimientos?tipo=devoluciones" :active="'{{$tipo_movimiento}}'==3">Devoluciones</b-nav-item>
                                    @endcan
                                </b-nav>
                            </div>
                            <div class="col-lg-12">
                                <b-button v-show="'{{$tipo_movimiento}}'==1" v-b-modal.modal-1 variant="primary"><i class="fas fa-plus"></i> Agregar gasto</b-button>
                                <b-button v-show="'{{$tipo_movimiento}}'==2" v-b-modal.modal-ingreso variant="primary" class="ml-1"><i class="fas fa-plus"></i> Agregar efectivo</b-button>
                                <b-button v-show="'{{$tipo_movimiento}}'==3" v-b-modal.modal-devolucion variant="primary" class="ml-1"><i class="fas fa-plus"></i> Agregar devolución</b-button>
                                <h4 class="float-right">Total: S/ @{{ total }}</h4>
                                <div class="table-responsive tabla-gestionar">
                                    <table class="table table-striped table-hover table-sm">
                                        <thead class="bg-custom-green">
                                        <tr>
                                            <th scope="col"></th>
                                            <th scope="col">Fecha</th>
                                            <th scope="col">Caja</th>
                                            @if($tipo_movimiento == 1)
                                            <th scope="col">Tipo de gasto</th>
                                            @endif
                                            <th scope="col">Descripción</th>
                                            @if($tipo_movimiento == 1)
                                            <th scope="col">N° comprobante</th>
                                            @endif
                                            <th scope="col">Monto</th>
                                            @if($tipo_movimiento != 3)
                                            <th scope="col">Opciones</th>
                                            @endif
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(count($data))
                                            @foreach($data as $item)
                                                <tr>
                                                    <td></td>
                                                    <td style="width: 130px">{{date('d/m/Y H:i',strtotime($item->fecha))}}</td>
                                                    <td style="width: 130px">{{$item->caja}}</td>
                                                    @if($tipo_movimiento == 1)
                                                    <td>{{$item->tipo_gasto}}</td>
                                                    @endif
                                                    <td>{{$item->descripcion}} @if($tipo_movimiento == 3) <a href="{{url('facturacion/documento/'.$item->idventa)}}"> Ver detalle</a> @endif</td>
                                                    @if($tipo_movimiento == 1)
                                                    <td>{{$item->num_comprobante}}</td>
                                                    @endif
                                                    <td>S/{{$item->monto}}</td>
                                                    @if($tipo_movimiento != 3)
                                                    <td class="botones-accion">
                                                        <a @click="borrarGastoIngreso({{$item->idgasto}})" href="javascript:void(0)">
                                                            <button class="btn btn-danger" title="Eliminar"><i
                                                                        class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </a>
                                                        @can('Mantenimiento: empleados')
                                                            @if($item->tipo_egreso==4)
                                                            <a :href="'/trabajadores/pagos/'+'{{$item->idempleado}}'">
                                                                <button class="btn btn-warning" title="Historial de pagos de empleado"><i class="fas fa-coins"></i></button>
                                                            </a>
                                                            @endif
                                                        @endcan
                                                    </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @else
                                        <tr class="text-center"><td colspan="9">No hay datos para mostrar</td></tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                                {{$data->links('layouts.paginacion')}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--INICIO MODAL GASTOS-->
    <b-modal id="modal-1" ref="modal-1" size="lg" @ok="agregarMovimiento" @@hidden="resetModal">
    <template slot="modal-title">
        Agregar gasto
    </template>
    <div class="container">
        <div class="row">
            <div class="col-lg-3">
                <div class="form-group">
                    <label for="descripcion">Fecha:</label>
                    <input disabled type="date" v-model="fecha_gasto" class="form-control">
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <label>Tipo de egreso:</label>
                    <select v-model="tipo_egreso" class="custom-select">
                        <option value="1">Gastos comunes</option>
                        <option value="4">Pago de empleados</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-12 mb-3">
                <hr class="my-auto flex-grow-1">
            </div>
            <div class="col-lg-12">
                <div v-show="tipo_egreso==1" class="row">
                    <div class="col-lg-9">
                        <div class="form-group">
                            <label for="descripcion">Descripción / concepto:</label>
                            <textarea v-model="descripcion" class="form-control" rows="1"></textarea>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Monto:</label>
                            <input autocomplete="off" type="number" v-model="monto" class="form-control">
                        </div>
                    </div>
                </div>
                <div v-show="tipo_egreso==2" class="row">
                    <div class="col-lg-8 form-group">
                        <label>Razón social / Nombre:</label>
                        <input disabled type="text" v-model="nombre" name="comprobanteReferencia"
                               class="form-control">
                        <b-button @click="abrir_modal('proveedor')" variant="primary" class="boton_adjunto">
                            Seleccionar
                        </b-button>
                    </div>
                    <div class="col-lg-3">
                        <label>Registrar:</label>
                        <b-button href="{{action('ProveedorController@index')}}" target="_blank" class="mr-2"
                                  variant="secondary"><i class="fas fa-plus"></i> Nuevo proveedor
                        </b-button>
                    </div>
                    <div class="col-lg-9">
                        <div class="form-group">
                            <label for="descripcion">Descripción / concepto:</label>
                            <textarea v-model="descripcion" class="form-control" rows="1"></textarea>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Monto:</label>
                            <input autocomplete="off" type="number" v-model="monto" class="form-control">
                        </div>
                    </div>
                </div>
                <div v-show="tipo_egreso==3" class="row">
                    <div class="col-lg-8 form-group">
                        <label>Comprobante de proveedor:</label>
                        <input disabled type="text" v-model="nombre" name="comprobanteReferencia"
                               class="form-control">
                        <b-button @click="abrir_modal('proveedor')" variant="primary" class="boton_adjunto">
                            Seleccionar
                        </b-button>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Monto:</label>
                            <input autocomplete="off" type="text" v-model="monto" class="form-control">
                        </div>
                    </div>
                </div>
                <div v-show="tipo_egreso==4" class="row">
                    <div class="col-lg-8 form-group">
                        <label>Empleado:</label>
                        <input disabled type="text" v-model="nombre" name="comprobanteReferencia"
                               class="form-control">
                        <b-button @click="abrir_modal('empleado')" variant="primary" class="boton_adjunto">
                            Seleccionar
                        </b-button>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Tipo de pago:</label>
                            <select :disabled="!nombre" v-model="tipo_pago_empleado" class="custom-select">
                                <option value="1">Pago de sueldo</option>
                                <option value="2">Adelanto de sueldo</option>
                                <option value="3">Bonificación / Aguinaldo</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Correspondiente a:</label>
                            <select :disabled="!nombre" v-model="mes_pago_empleado" class="custom-select">
                                <option value="-1">Seleccionar mes</option>
                                <option value="01">Enero</option>
                                <option value="02">Febrero</option>
                                <option value="03">Marzo</option>
                                <option value="04">Abril</option>
                                <option value="05">Mayo</option>
                                <option value="06">Junio</option>
                                <option value="07">Julio</option>
                                <option value="08">Agosto</option>
                                <option value="09">Setiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                            </select>
                        </div>
                    </div>
                    <div v-show="tipo_pago_empleado != 3" class="col-lg-3">
                        <div class="form-group">
                            <label>Total pendiente:</label>
                            <h2>@{{ totalPendiente }}</h2>
                        </div>
                    </div>
                    <div v-show="tipo_pago_empleado != 3" class="col-lg-3">
                        <div class="form-group">
                            <label>Monto a pagar:</label>
                            <input :disabled="tipo_pago_empleado==1 || monto==0" autocomplete="off" type="text"
                                   v-model="monto" class="form-control">
                        </div>
                    </div>
                    <div v-show="tipo_pago_empleado == 3" class="col-lg-3">
                        <div class="form-group">
                            <label>Monto a pagar:</label>
                            <input autocomplete="off" type="text" v-model="monto" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <label>Comprobante:</label>
                    <select v-model="comprobante" class="custom-select">
                        <option value="1">Recibo</option>
                        <option value="2">Boleta</option>
                        <option value="3">Factura</option>
                        <option value="4">Ticket</option>
                        <option value="5">Voucher</option>
                        <option value="6">Otro</option>
                        <option value="7">Ninguno</option>
                    </select>
                </div>
            </div>
            <div v-show="comprobante!=7" class="col-lg-3">
                <div class="form-group">
                    <label>N° de comprobante:</label>
                    <input type="text" v-model="num_comprobante" class="form-control">
                </div>
            </div>
            <div class="col-lg-12">
                <div v-for="error in errorDatosGastosIngresos">
                    <p class="texto-error">@{{ error }}</p>
                </div>
            </div>
        </div>
    </div>
    </b-modal>
    <!--FIN MODAL GASTOS-->
    <!--INICIO MODAL PROVEEDOR -->
    <b-modal size="lg" id="modal-proveedor" ref="modal-proveedor" ok-only @hidden="resetModal_2">
        <template slot="modal-title">
            Proveedor
        </template>
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="buscar">Busca por nombre o razón social:</label>
                        <input @keyup="delay('proveedores')" v-model="buscar" type="text" name="buscar"
                               placeholder="Buscar..." class="form-control" autocomplete="off">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">

                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="table-responsive tabla-gestionar">
                        <table class="table table-striped table-hover table-sm">
                            <thead class="bg-custom-green">
                            <tr>
                                <th scope="col">Código</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Ruc</th>
                                <th scope="col">Dirección</th>
                                <th scope="col"></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(proveedor,index) in listaModal" :key="proveedor.idproveedor">
                                <td style="display:none">@{{proveedor.idproveedor}}</td>
                                <td style="width: 5%">@{{proveedor.codigo}}</td>
                                <td style="width: 30%">@{{proveedor.nombre}}</td>
                                <td style="width: 20%">@{{proveedor.num_documento}}</td>
                                <td style="width: 30%">@{{proveedor.direccion}}</td>
                                <td style="width: 5%" class="botones-accion">
                                    <a @click="agregarProveedor(index)" href="javascript:void(0)">
                                        <button class="btn btn-info" title="Seleccionar proveedor"><i
                                                    class="fas fa-check"></i>
                                        </button>
                                    </a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </b-modal>
    <!--FIN MODAL PROVEEDOR -->
    <!--INICIO MODAL EMPLEADO -->
    <b-modal size="lg" id="modal-empleado" ref="modal-empleado" ok-only @hidden="resetModal_2">
        <template slot="modal-title">
            Empleado
        </template>
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="buscar">Busca por nombre:</label>
                        <input @keyup="delay('empleados')" v-model="buscar" type="text" name="buscar"
                               placeholder="Buscar..." class="form-control" autocomplete="off">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">

                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="table-responsive tabla-gestionar">
                        <table class="table table-striped table-hover table-sm">
                            <thead class="bg-custom-green">
                            <tr>
                                <th scope="col">Nombre y apellidos</th>
                                <th scope="col">Dni</th>
                                <th scope="col">Dirección</th>
                                <th scope="col"></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(empleado,index) in listaModal" :key="empleado.idempleado">
                                <td style="width: 30%">@{{empleado.nombre}} @{{empleado.apellidos}}</td>
                                <td style="width: 20%">@{{empleado.dni}}</td>
                                <td style="width: 30%">@{{empleado.direccion}}</td>
                                <td style="width: 5%" class="botones-accion">
                                    <a @click="agregarEmpleado(index)" href="javascript:void(0)">
                                        <button class="btn btn-info" title="Seleccionar proveedor"><i
                                                    class="fas fa-check"></i>
                                        </button>
                                    </a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </b-modal>
    <!--FIN MODAL EMPLEADO -->
    <!--INICIO MODAL INGRESO -->
    <b-modal size="lg" id="modal-ingreso" ref="modal-ingreso" @ok="agregarMovimiento" @hidden="resetModal">
        <template slot="modal-title">
            Ingreso extra
        </template>
        <div class="container">
            <div class="row">
                <div class="col-lg-9">
                    <div class="form-group">
                        <label for="descripcion">Descripción / concepto:</label>
                        <textarea v-model="descripcion" class="form-control" rows="1"></textarea>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">
                        <label>Monto</label>
                        <input autocomplete="off" type="number" v-model="monto" class="form-control">
                    </div>
                </div>
                <div class="col-lg-12">
                    <div v-for="error in errorDatosGastosIngresos">
                        <p class="texto-error">@{{ error }}</p>
                    </div>
                </div>
            </div>
        </div>
    </b-modal>
    <!--FIN MODAL INGRESO -->
    <modal-devolucion></modal-devolucion>
        @endsection
        @section('script')
            <script>

                let app = new Vue({
                    el: '.app',
                    data: {
                        fecha_gasto: '{{date('Y-m-d')}}',
                        errorDatosGastosIngresos: [],
                        errorGastosIngresos: 0,
                        descripcion: '',
                        monto: '',
                        comprobante: '7',
                        tipo_egreso: '1',
                        listaModal: [],
                        personaSeleccionada: {},
                        nombre: '',
                        buscar: '',
                        tipo_pago_empleado: '1',
                        mes_pago_empleado: '-1',
                        num_comprobante: '',
                        totalPendiente: '-',
                        total:'0.00',
                        desde:'{{$desde}}',
                        hasta:'{{$hasta}}',
                    },
                    created(){
                        this.obtenerTotal();
                    },
                    methods: {
                        setParams(obj){
                            let d1 = new Date(obj.startDate).toISOString().split('T')[0];
                            let d2 = new Date(obj.endDate).toISOString().split('T')[0];
                            this.desde=d1;
                            this.hasta=d2;
                            window.location.href='/caja/movimientos?tipo={{$tipo}}'+'&desde='+this.desde+'&hasta='+this.hasta;
                        },

                        obtenerTotal(){
                            axios.get('/caja/movimientos/total?tipo={{$tipo}}'+'&desde='+this.desde+'&hasta='+this.hasta)
                                .then(response => {
                                    this.total = response.data.toFixed(2);
                                })
                                .catch(error => {
                                    alert('Ha ocurrido un error');
                                    console.log(error);
                                });
                        },
                        agregarMovimiento(e){
                            if (this.validarGastos()) {
                                e.preventDefault();
                                return;
                            }

                            let dataset = {
                                'tipo_egreso': this.tipo_egreso,
                                'tipo_pago_empleado': this.tipo_pago_empleado,
                                'mes_pago_empleado': this.mes_pago_empleado,
                                'idempleado': this.personaSeleccionada['idempleado'],
                                'descripcion': this.descripcion,
                                'tipo_comprobante': this.comprobante,
                                'num_comprobante': this.num_comprobante,
                                'monto': this.monto,
                                'tipo_movimiento': '{{$tipo_movimiento}}',
                            };

                            axios.post('{{action('GastoController@store')}}', dataset)
                                .then(function () {
                                    window.location.reload();
                                })
                                .catch(function (error) {
                                    alert('Ha ocurrido un error al guardar los datos.');
                                    console.log(error);
                                });
                        },
                        validarGastos(){
                            this.errorGastosIngresos = 0;
                            this.errorDatosGastosIngresos = [];
                            if (this.tipo_egreso == 1) {
                                if (this.descripcion.length == 0) this.errorDatosGastosIngresos.push('*Agregue una descripción');
                            }
                            if (this.tipo_egreso == 4) {
                                if (this.mes_pago_empleado == '-1') this.errorDatosGastosIngresos.push('*Selecciona un mes');
                            }
                            if (this.monto.length == 0) this.errorDatosGastosIngresos.push('*Monto no puede estar vacio');
                            if (this.monto <= 0) this.errorDatosGastosIngresos.push('*Monto debe ser mayor que cero');
                            if (isNaN(this.monto)) this.errorDatosGastosIngresos.push('*El monto debe contener sólo números');
                            if (this.comprobante != 7) {
                                if (this.num_comprobante.length == 0) this.errorDatosGastosIngresos.push('*Numero de comprobante no puede estar vacio');
                            }

                            if (this.errorDatosGastosIngresos.length) this.errorGastosIngresos = 1;
                            return this.errorGastosIngresos;

                        },
                        borrarGastoIngreso(id){
                            if (confirm('Realmente desea eliminar el registro')) {
                                axios.delete('{{url('/caja/destroy')}}' + '/' + id)
                                    .then(function () {
                                        window.location.reload();
                                    })
                                    .catch(function (error) {
                                        console.log(error);
                                    });
                            }
                        },
                        obtenerProveedores(){
                            let _this = this;
                            axios.post('{{action('RequerimientoController@obtenerProveedores')}}', {
                                'textoBuscado': this.buscar
                            })
                                .then(function (response) {
                                    let datos = response.data;
                                    _this.listaModal = datos;
                                })
                                .catch(function (error) {
                                    alert('Ha ocurrido un error.');
                                    console.log(error);
                                });
                        },
                        agregarProveedor(index){
                            this.personaSeleccionada = this.listaModal[index];
                            this.nombre = this.personaSeleccionada['nombre'];
                            this.$refs['modal-proveedor'].hide();
                        },
                        obtenerEmpleados(){
                            axios.post('{{action('GastoController@obtenerEmpleados')}}', {
                                'textoBuscado': this.buscar
                            })
                                .then(response => {
                                    this.listaModal = response.data;
                                })
                                .catch(function (error) {
                                    alert('Ha ocurrido un error.');
                                    console.log(error);
                                });
                        },
                        agregarEmpleado(index){
                            this.personaSeleccionada = this.listaModal[index];
                            this.nombre = this.personaSeleccionada['nombre'] + ' ' + this.personaSeleccionada['apellidos'];
                            this.$refs['modal-empleado'].hide();
                        },
                        abrir_modal(nombre){
                            if (nombre == 'proveedor') {
                                this.$refs['modal-proveedor'].show();
                                this.obtenerProveedores();
                            } else if (nombre == 'empleado') {
                                this.$refs['modal-empleado'].show();
                                this.obtenerEmpleados();
                            }
                        },
                        resetModal(){
                            this.descripcion = '';
                            this.monto = '';
                            this.nombre = '';
                            this.tipo_egreso = '1';
                            this.tipo_pago_empleado = '1';
                            this.mes_pago_empleado = '-1';
                            this.num_comprobante = '';
                            this.totalPendiente = '-';
                            this.comprobante = '1';
                            this.errorDatosGastosIngresos = [];
                            this.errorGastosIngresos = 0;
                        },
                        resetModal_2(){
                            this.listaModal = [];
                        },
                        obtenerPagoPendiente(){
                            axios.post('{{action('GastoController@obtenerPagoPendiente')}}', {
                                'idempleado': this.personaSeleccionada['idempleado'],
                                'mes': this.mes_pago_empleado,
                                'tipo': this.tipo_pago_empleado
                            })
                                .then(response => {
                                    let datos = response.data;
                                    this.totalPendiente = datos;
                                    this.monto = datos;
                                })
                                .catch(function (error) {
                                    alert('Ha ocurrido un error.');
                                    console.log(error);
                                });
                        }
                    },
                    watch: {
                        mes_pago_empleado(){
                            if (this.tipo_pago_empleado != 3 && this.mes_pago_empleado != -1) {
                                this.obtenerPagoPendiente();
                            }
                        },
                        tipo_pago_empleado(){
                            if (this.tipo_pago_empleado != 3 && this.mes_pago_empleado != -1) {
                                this.obtenerPagoPendiente();
                            }
                        }
                    }
                });
            </script>
@endsection