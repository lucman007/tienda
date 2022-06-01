@extends('layouts.main')
@section('titulo', 'Consulta CDR')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-lg-6 offset-lg-3">
                <h3 class="titulo-admin-1">Consulta CDR</h3>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 mt-4 mb-3 offset-lg-3">
                @if($errors->any())
                    <p style="color:red;">{{$errors->first()}}</p>
                @endif
                <div class="card">
                    <div class="card-header">
                        Consulta estado de facturas y notas vinculadas
                    </div>
                    <div class="card-body">
                        <div class="col-lg-12 form-group">
                            <label>Tipo</label>
                            <select v-model="tipo" class="custom-select">
                                <option value="03">Boleta</option>
                                <option value="01">Factura</option>
                                <option value="07">Nota de crédito</option>
                                <option value="08">Nota de débito</option>
                            </select>
                        </div>
                        <div class="col-lg-12 form-group">
                            <label>Serie</label>
                            <input maxlength="4" type="text" v-model="serie" class="form-control">
                        </div>
                        <div class="col-lg-12 form-group">
                            <label>Correlativo</label>
                            <input maxlength="8" type="text" v-model="numero" class="form-control">
                        </div>
                        <div class="col-lg-12">
                            <b-button @click="consultarCdr('estado')" class="mb-4" variant="primary"><i class="fas fa-search-plus"
                                                                                                        v-show="!mostrarSpinnerCon"></i>
                                <b-spinner v-show="mostrarSpinnerCon" small label="Loading..."></b-spinner>
                                Consultar estado
                            </b-button>
                            <b-button @click="consultarCdr('cdr')" class="mb-4" variant="primary"><i class="fas fa-search-plus"
                                                                                                     v-show="!mostrarSpinnerCdr"></i>
                                <b-spinner v-show="mostrarSpinnerCdr" small label="Loading..."></b-spinner>
                                Consultar CDR
                            </b-button>
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
                mostrarSpinnerCon:false,
                mostrarSpinnerCdr:false,
                ruc:'',
                tipo:'01',
                serie:'{{$serie_comprobantes['factura']}}',
                numero:''
            },
            methods: {
                consultarCdr(tipo_consulta){

                    if (this.validar()) {
                        return;
                    }
                    if(tipo_consulta == 'cdr'){
                        this.mostrarSpinnerCdr = true;
                    } else{
                        this.mostrarSpinnerCon = true;
                    }

                    axios.post('{{url('ventas/getStatusCdr')}}', {
                        'ruc': this.ruc,
                        'tipo': this.tipo,
                        'serie': this.serie,
                        'numero': this.numero,
                        'tipo_consulta':tipo_consulta
                    })
                        .then(response => {
                            if(tipo_consulta == 'cdr'){
                                this.mostrarSpinnerCdr = false;
                            } else{
                                this.mostrarSpinnerCon = false;
                            }
                            alert(response.data);
                        })
                        .catch(error => {
                            if(tipo_consulta == 'cdr'){
                                this.mostrarSpinnerCdr = false;
                            } else{
                                this.mostrarSpinnerCon = false;
                            }
                            alert('Ha ocurrido un error al procesar la operación.');
                            console.log(error);
                        });
                },
                validar(){
                    let errorVenta = 0;
                    let errorDatosVenta = [];
                    let errorString = '';
                    if (this.serie.length == 0 || this.numero.length == 0) errorDatosVenta.push('*Serie y correlativo no puede estar vacio');

                    if (errorDatosVenta.length) {
                        errorVenta = 1;
                        for (let error of errorDatosVenta) {
                            errorString += error + '\n';
                        }
                        alert(errorString);
                    }

                    return errorVenta;
                }
            },
            watch:{
                tipo(comp){
                    if(comp=='01'){
                        this.serie='{{$serie_comprobantes['factura']}}';
                    } else if(comp=='03'){
                        this.serie='{{$serie_comprobantes['boleta']}}';
                    } else if(comp=='07'){
                        this.serie='{{$serie_comprobantes['nota_credito_factura']}}';
                    } else if(comp=='08'){
                        this.serie='{{$serie_comprobantes['nota_debito_factura']}}';
                    }
                }
            }

        })
    </script>
@endsection