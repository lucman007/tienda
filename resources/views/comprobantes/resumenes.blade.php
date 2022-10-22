@extends('layouts.main')
@section('titulo', 'Resúmenes')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-lg-9 mb-4">
                <h3 class="titulo-admin-1">Estado de anulación</h3>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-2">
                        <label><i class="far fa-list-alt"></i> Acciones</label>
                        <b-dropdown variant="primary" text="Consulta anulación">
                            <b-dropdown-item href="{{action('ComprobanteController@comprobantes')}}"><i class="fas fa-file-alt"></i> Comprobantes</b-dropdown-item>
                            <b-dropdown-item href="{{action('ComprobanteController@anular')}}"><i class="fas fa-ban"></i> Anulaciones</b-dropdown-item>
                            <b-dropdown-item href="{{action('ComprobanteController@consulta')}}"><i class="fas fa-external-link-square-alt"></i> Consulta CDR</b-dropdown-item>
                            <b-dropdown-item href="{{action('ComprobanteController@resumenes_enviados')}}"><i class="fas fa-external-link-square-alt"></i> Consulta anulación</b-dropdown-item>
                            <b-dropdown-item href="{{action('ReporteController@reporte_ventas')}}"><i class="fas fa-chart-line"></i> Reporte de ventas</b-dropdown-item>
                            <b-dropdown-item href="{{action('GuiaController@index')}}"><i class="fas fa-dolly"></i> Guía de remisión</b-dropdown-item>
                        </b-dropdown>
                    </div>
                    <div class="col-lg-3 form-group">
                        <label><i class="fas fa-file-alt"></i> Comprobantes</label>
                        <select @change="obtenerResumen" v-model="tipo_resumen" name="tipo_resumen" class="custom-select" id="selectMotivo">
                            <option value="diario">Resumen diario de boletas</option>
                            <option value="baja">Comunicación de baja facturas</option>
                        </select>
                    </div>
                    <div class="col-lg-2 form-group">
                        <label><i class="far fa-calendar-alt"></i> Desde</label>
                        <input @change="obtenerResumen" type="date" v-model="fecha_in" name="fecha_in"
                               class="form-control">
                    </div>
                    <div class="col-lg-2 form-group">
                        <label><i class="far fa-calendar-alt"></i> Hasta</label>
                        <input @change="obtenerResumen" type="date" v-model="fecha_out" name="fecha_out"
                               class="form-control">
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        Anulaciones enviadas
                    </div>
                    <div class="card-body">
                        <div class="table-responsive tabla-gestionar">
                    <table class="table table-striped table-hover table-sm">
                        <thead class="bg-custom-green">
                        <tr>
                            <th scope="col"></th>
                            <th scope="col">Generado</th>
                            <th scope="col">Fecha emisión</th>
                            <th scope="col">Lote</th>
                            <th scope="col">Tipo resumen</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Ticket Sunat</th>
                            <th scope="col">Estado</th>
                            <th scope="col" style="width: 300px">Opciones</th>
                        </tr>
                        </thead>
                        <tbody>

                            <tr v-for="(item,index) in resumen.data" :key="index">
                                <td></td>
                                <td>@{{item.fecha_generacion }}</td>
                                <td>@{{item.fecha_emision}}</td>
                                <td>@{{item.lote}}</td>
                                <td>@{{item.tipo}}</td>
                                <td>@{{item.nombre}}</td>
                                <td>@{{item.num_ticket}}</td>
                                <td>@{{item.estado}}</td>
                                <td class="botones-accion">
                                    <b-button @click="detalle_resumen(item.idresumen)"  class="btn btn-info" title="Ver comprobantes">
                                        <i class="fas fa-list"></i>
                                    </b-button>
                                    <b-button @click="descargar(item.nombre+'.xml')"  class="btn btn-warning" title="Descargar XML">
                                        <i class="fas fa-code"></i>
                                    </b-button>
                                    <b-button style="width: auto" @click="getStatus(item.num_ticket,item.nombre)" class="btn btn-success mt-1" title="Ver estado de resumen en SUNAT">
                                        <i class="fas fa-sign-in-alt"></i> Status
                                    </b-button>
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
    <!--INICIO MODAL DETALLE -->
    <b-modal size="md" id="modal-detalle" ref="modal-detalle" ok-only >
        <template slot="modal-title">
            Detalle
        </template>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="table-responsive tabla-gestionar">
                        <table class="table table-striped table-hover table-sm">
                            <thead class="bg-custom-green">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Serie/correlativo</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(doc,index) in listaComprobantes" :key="index">
                                <td>@{{ index+1 }}</td>
                                <td class="bol-hover"><a @click="abrirDoc(doc.idventa)">@{{doc.serie}}-@{{doc.correlativo}}</a></td>
                                <td></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </b-modal>
    <!--FIN MODAL DETALLE -->

@endsection
@section('script')
    <script>
        let app = new Vue({
            el: '.app',
            data: {
                fecha_in: '{{date('Y-m-d')}}',
                fecha_out: '{{date('Y-m-d')}}',
                tipo_resumen: 'diario',
                resumen: {
                    current_page: 1
                },
                listaComprobantes:[]
            },
            created(){
                this.obtenerResumen();
                let today = new Date().toISOString().split('T')[0];
                document.getElementsByName("fecha_in")[0].setAttribute('max', today);
                document.getElementsByName("fecha_out")[0].setAttribute('max', today);
            },
            methods:{
                obtenerResumen(){
                    axios.post('/comprobantes/obtener-resumen?page=' + this.resumen.current_page, {
                        'tipo_resumen': this.tipo_resumen,
                        'fecha_in': this.fecha_in,
                        'fecha_out': this.fecha_out
                    })
                        .then(response => {
                            this.resumen = response.data;
                        })
                        .catch(error => {
                            this.alerta('Ha ocurrido un error.');
                            console.log(error);
                        });
                },
                detalle_resumen(id){
                    this.$refs['modal-detalle'].show();
                    axios.get('/comprobantes/detalle-resumen' +'/'+id)
                        .then(response => {
                            this.listaComprobantes = response.data;
                        })
                        .catch(error => {
                            this.alerta('Ha ocurrido un error.');
                            console.log(error);
                        });
                },
                descargar(file){
                    location.href='/ventas/descargar'+'/'+file;
                },
                getStatus(ticket,nombre){
                    axios.get('/ventas/getstatus' +'/'+ticket+'/'+nombre)
                        .then(response => {
                            this.alerta(response.data);
                            this.obtenerResumen();
                        })
                        .catch(error => {
                            this.alerta('Ha ocurrido un error al consultar estado de resumen.');
                            console.log(error);
                        });
                },
                abrirDoc(idventa){
                    window.open(
                        '/facturacion/documento/' + idventa,
                        '_blank'
                    );
                },
                alerta(texto){
                    this.$swal({
                        position: 'top',
                        icon: 'warning',
                        title: texto,
                        timer: 6000,
                        toast:true,
                        confirmButtonColor: '#007bff',
                    });
                }
            }

        });
    </script>
@endsection
