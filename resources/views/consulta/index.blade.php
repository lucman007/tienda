@extends('layouts.consulta')
@section('titulo', 'Consulta de documentos')
@section('contenido')
    <div class="container">
        <div class="row">
            <div class="col-lg-6 offset-lg-3">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-12">Consulta de documentos</div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="tipo_documento">Tipo de documento:</label>
                                    <select v-model="tipo_documento" name="tipo_documento" class="custom-select" id="tipo_documento">
                                        <option value="03">Boleta</option>
                                        <option value="01">Factura</option>
                                        <option value="07">Nota de crédito</option>
                                        <option value="08">Nota de débito</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="serie">Serie:</label>
                                    <input maxlength="4" autocomplete="off" type="text" v-model="serie" name="serie" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="form-group">
                                    <label for="correlativo">Correlativo:</label>
                                    <input maxlength="8" autocomplete="off" type="text" v-model="correlativo" name="correlativo" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-4 form-group">
                                <label>Fecha de emisión</label>
                                <input type="date" v-model="fecha" name="fecha" class="form-control">
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="total">Monto total:</label>
                                    <input autocomplete="off" type="text" v-model="total" name="total" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <label for="total">Consultar</label>
                                <b-button @click="consultar"
                                          class="mb-4" variant="primary">
                                    <i class="fas fa-search-plus" v-show="!mostrarSpinner"></i>
                                    <b-spinner v-show="mostrarSpinner" small label="Loading..."></b-spinner>
                                    Ver documento
                                </b-button>
                            </div>
                            <div v-if="mostrar" class="col-lg-12">
                                <div class="form-group text-center">
                                    <b-button :href="'/consulta/descargar/'+nombreFichero+'.pdf'" class="mt-4" variant="warning"><i class="fas fa-file-pdf"></i> Descargar PDF
                                    </b-button>
                                    <b-button :href="'/consulta/descargar/'+nombreFichero+'.xml'" class="mt-4" variant="warning"><i class="fas fa-code"></i> Descargar XML
                                    </b-button>
                                </div>
                            </div>
                            <div v-if="mostrarMensaje" class="col-lg-12">
                                <p class="text-center">
                                    No se encontró el documento
                                </p>
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
                tipo_documento:'03',
                serie:'',
                correlativo:'',
                total:'',
                fecha: '{{date('Y-m-d')}}',
                mostrarSpinner:false,
                mostrar:0,
                mostrarMensaje:0,
                nombreFichero:''
            },
            methods:{
                consultar(){

                    if (this.validar()) {
                        return;
                    }

                    let _this = this;
                    axios.post('consulta/obtenerDocumento',{
                        'tipo_documento':this.tipo_documento,
                        'serie':this.serie,
                        'correlativo':this.correlativo,
                        'total':this.total,
                        'fecha':this.fecha
                    })
                        .then(function (response) {
                            _this.mostrar=response.data.mostrar;
                            _this.mostrarMensaje=!response.data.mostrar;
                            _this.nombreFichero=response.data.nombre_fichero;
                            console.log(response.data)
                        })
                        .catch(function (error) {
                            alert('Hubo un error al consultar el documento');
                            console.log(error);
                        });
                },
                validar(){
                    let errorVenta = 0;
                    let errorDatosVenta = [];
                    let errorString = '';
                    if (this.serie.length == 0) errorDatosVenta.push('*La serie no puede estar vacia');
                    if (this.correlativo.length == 0) errorDatosVenta.push('*El número correlativo no puede estar vacio');
                    if (this.fecha.length == 0) errorDatosVenta.push('*La fecha no puede estar vacia');
                    if (this.total.length == 0) errorDatosVenta.push('*El monto total no puede estar vacío');
                    if (isNaN(this.correlativo)) errorDatosVenta.push('*El correlativo debe contener solo números');
                    if (isNaN(this.total)) errorDatosVenta.push('*El monto total debe contener solo números');

                    if (errorDatosVenta.length) {
                        errorVenta = 1;
                        for (let error of errorDatosVenta) {
                            errorString += error + '\n';
                        }
                        alert(errorString);
                    }

                    return errorVenta;
                }
            }
        });
    </script>
@endsection
