@extends('layouts.main')
@section('titulo', 'Anulación')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1">Anulación de comprobantes</h3>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-2">
                <label><i class="far fa-list-alt"></i> Acciones</label>
                <b-dropdown variant="primary" text="Anulaciones">
                    <b-dropdown-item href="{{action('ComprobanteController@comprobantes')}}"><i class="fas fa-file-alt"></i> Comprobantes</b-dropdown-item>
                    <b-dropdown-item href="{{action('ComprobanteController@anular')}}"><i class="fas fa-ban"></i> Anulaciones</b-dropdown-item>
                    <b-dropdown-item href="{{action('ComprobanteController@consulta')}}"><i class="fas fa-external-link-square-alt"></i> Consulta CDR</b-dropdown-item>
                    <b-dropdown-item href="{{action('ComprobanteController@resumenes_enviados')}}"><i class="fas fa-external-link-square-alt"></i> Consulta anulación</b-dropdown-item>
                    <b-dropdown-item href="{{action('ReporteController@reporte_ventas')}}"><i class="fas fa-chart-line"></i> Reporte de ventas</b-dropdown-item>
                </b-dropdown>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <label><i class="fas fa-file-alt"></i> Comprobantes</label>
                    <select @change="cambiarComprobante" v-model="tipo_comprobante" name="tipo_comprobante"
                            class="custom-select" id="tipo_comprobante">
                        <option value="40">Anular facturas y notas vinculadas</option>
                        <option value="50">Anular boletas y notas vinculadas</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="row">
                    <div class="col-lg-4 form-group">
                        <label><i class="far fa-calendar-alt"></i> Fecha</label>
                        <input @change="obtenerComprobantes" type="date" v-model="fecha_in" name="fecha_in"
                               class="form-control">
                    </div>
                    <div class="col-lg-4 form-group">
                        <label for="start"><i class="fas fa-external-link-square-alt"></i> Consultar</label>
                        <b-button href="{{url('comprobantes/resumenes')}}" variant="primary"><i
                                    class="fas fa-file"></i> Estado de anulaciones
                        </b-button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        Lista de comprobantes
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
                            <th scope="col">Pago</th>
                            <th scope="col">Comprobante</th>
                            <th scope="col">Estado</th>
                            <th v-show="tipo_comprobante==40">Motivo</th>
                            <th scope="col">Opciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(venta,index) in ventas.data" v-if="!mostrarMensaje" :key="venta.index"
                            :class="{'td-anulado':venta.facturacion.estado=='ANULADO'}">
                            <td></td>
                            <td style="width: 5%">@{{venta.idventa}}</td>
                            <td style="width: 15%">@{{venta.fecha}}</td>
                            <td>@{{venta.cliente.persona.nombre}}</td>
                            <td>@{{venta.total_venta}}</td>
                            <td>@{{venta.facturacion.codigo_moneda}}</td>
                            <td>@{{venta.tipo_pago}}</td>
                            <td>@{{venta.facturacion.serie}}-@{{venta.facturacion.correlativo}}</td>
                            <td>
                                <p class="badge"
                                   :class="{'badge-warning':venta.facturacion.estado=='PENDIENTE',
                                   'badge-success' : venta.facturacion.estado=='ACEPTADO',
                                   'badge-dark' : venta.facturacion.estado=='ANULADO',
                                   'badge-danger' : venta.facturacion.estado=='RECHAZADO'}">
                                    @{{venta.facturacion.estado}}
                                </p>
                            </td>
                            <td v-show="tipo_comprobante==40"><input v-model="venta.motivo_baja" maxlength="150"
                                                                     :disabled="!venta.anular" type="text"
                                                                     class="form-control"></td>
                            <td class="botones-accion">
                                <b-form-checkbox :disabled="venta.facturacion.estado=='ANULADO' || venta.facturacion.estado=='RECHAZADO'" v-model="venta.anular"
                                                 v-show="tipo_comprobante==40 || tipo_comprobante==50" switch size="sm">Anular
                                </b-form-checkbox>
                            </td>
                        </tr>
                        <tr v-if="mostrarMensaje" class="text-center">
                            <td colspan="11">@{{mensajeTabla}}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                        <paginacion-js v-on:paginate="obtenerComprobantes" v-bind:pagination="ventas"></paginacion-js>
                        <div class="row">
                            <div class="col-lg-3 offset-lg-9 form-group">
                                <b-button class="float-right" @click="anularComprobantes" variant="danger">
                                    <b-spinner v-show="mostrarProgreso" small label="Loading..." ></b-spinner>
                                    <i v-show="!mostrarProgreso" class="fas fa-paper-plane"></i> Anular comprobantes
                                </b-button>
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
                fecha_in: '{{date('Y-m-d')}}',
                fecha_out: '{{date('Y-m-d')}}',
                ventas: {
                    current_page: 1
                },
                productos: [],
                mostrarProgreso: false,
                blockResumen: false,
                loteBoletas: false,
                mensajeTabla: 'Cargando...',
                mostrarMensaje: false,
                tipo_comprobante: 40,
            },
            created(){
                this.obtenerComprobantes();
                let today = new Date().toISOString().split('T')[0];
                document.getElementsByName("fecha_in")[0].setAttribute('max', today);
            },
            methods: {
                obtenerComprobantes(){
                    let _this = this;
                    this.mostrarMensaje = true;
                    this.mensajeTabla = 'Cargando...';
                    axios.post('/comprobantes/obtener-comprobantes?page=' + this.ventas.current_page, {
                        'tipo_comprobante': this.tipo_comprobante,
                        'fecha_in': this.fecha_in,
                        'fecha_out': this.fecha_in
                    })
                        .then(function (response) {
                            let ventas = response.data;
                            let suma_soles = 0;
                            let suma_usd = 0;
                            _this.ventas = ventas;
                            for (let val of ventas.data) {
                                //Anulado?
                                if (_this.tipo_comprobante == 40 ||_this.tipo_comprobante == 50) {
                                    _this.$set(val, 'anular', false);
                                    _this.$set(val, 'motivo_baja', '');
                                }
                            }

                            if (ventas.data.length === 0) {
                                _this.mensajeTabla = 'No se han encontrado registros';
                                _this.blockResumen = true;
                            } else {
                                _this.mostrarMensaje = false;
                                _this.blockResumen = false;
                            }

                        })
                        .catch(function (error) {
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });

                },
                cambiarComprobante(){
                    this.fecha_in = '{{date('Y-m-d')}}';
                    this.fecha_out = '{{date('Y-m-d')}}';
                    this.obtenerComprobantes();
                },
                enviarResumen(){
                    if(confirm('¿Está seguro de enviar los documentos a SUNAT? Recuerde revisar sus datos detalladamente')) {
                        if (this.ventas.data.length !== 0) {
                            _this = this;
                            axios.get('{{url('ventas/generar/resumenBoletas')}}' + '/' + this.fecha_in)
                                .then(function (response) {
                                    alert(response.data);
                                    _this.obtenerComprobantes();
                                })
                                .catch(function (error) {
                                    alert('Ha ocurrido un error al enviar el resumen.');
                                    console.log(error);
                                });
                        } else {
                            alert('No hay documentos para crear el resumen');
                        }
                    }
                },
                anularComprobantes(){

                    let items = [];
                    let i = 0;

                    if (this.tipo_comprobante == 40) {
                        for (let item of this.ventas.data) {
                            if (item.anular) {
                                if (item.motivo_baja.length == 0) {
                                    alert('Motivo de anulación no puede estar vacío')
                                    return;
                                }
                                items[i] = item;
                                i++;
                            }
                        }

                        if (items.length > 0) {
                            if (confirm('Esta operación no se puede revertir ¿Está seguro de anular los comprobantes?')) {
                                _this = this;
                                this.mostrarProgreso=true;
                                axios.post('{{url('comprobantes/anular-facturas')}}', {
                                    'items': JSON.stringify(items)
                                })
                                    .then(function (response) {
                                        alert(response.data);
                                        _this.mostrarProgreso=false;
                                        _this.obtenerComprobantes();
                                    })
                                    .catch(function (error) {
                                        alert('Ha ocurrido un error al procesar la operación.');
                                        _this.mostrarProgreso=false;
                                        console.log(error);
                                    });
                            }

                        } else {
                            alert('No se han seleccionados documentos para anular')
                        }
                    } else if (this.tipo_comprobante == 50) {

                        for (let item of this.ventas.data) {
                            if (item.anular) {
                                items[i] = item;
                                i++;
                            }
                        }
                        if (items.length > 0) {
                            if (confirm('Esta operación no se puede revertir ¿Está seguro de anular los documentos?')) {
                                _this = this;
                                this.mostrarProgreso=true;
                                axios.post('{{url('comprobantes/anular-boletas')}}', {
                                    'items': JSON.stringify(items)
                                })
                                    .then(function (response) {
                                        alert(response.data);
                                        _this.mostrarProgreso=false;
                                        _this.obtenerComprobantes();
                                    })
                                    .catch(function (error) {
                                        alert('Ha ocurrido un error al procesar la operación.');
                                        _this.mostrarProgreso=false;
                                        console.log(error);
                                    });
                            }
                        } else {
                            alert('No se han seleccionados documentos para anular')
                        }

                    }
                }
            }

        })
    </script>
@endsection