@extends('layouts.main')
@section('titulo', 'Pago de trabajadores')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1">Pagos: {{$empleado->persona->nombre}} {{$empleado->persona->apellidos}}</h3>
            </div>
            <div class="col-lg-3 form-group">
                <label>Correspondiente a:</label>
                <input @change="obtenerDatos" v-model="fecha_in" type="month" name="fecha_in" min="2020-01" class="custom-select">
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        Pagos efectuados
                    </div>
                    <div class="card-body">
                        <b-button :href="'/trabajadores/exportar-pagos/'+idempleado +'/'+ fecha_in" class="mr-2"  variant="primary"><i class="fas fa-file-export"></i> Exportar...</b-button>
                        <b-button :href="'/trabajadores/imprimir-pagos/'+idempleado +'/' + fecha_in" class="mr-2"  variant="primary" target="_blank"><i class="fas fa-print"></i> Imprimir</b-button>
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Caja</th>
                                    <th scope="col">Tipo</th>
                                    <th scope="col">N° comprobante</th>
                                    <th scope="col">Monto</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-show="!mostrarMensaje" v-for="item in lista_gastos">
                                    <td></td>
                                    <td>@{{item.fecha}}</td>
                                    <td>@{{item.caja}}</td>
                                    <td>@{{item.tipo}}</td>
                                    <td>@{{item.num_comprobante}}</td>
                                    <td>@{{item.monto}}</td>
                                </tr>
                                <tr v-if="mostrarMensaje" class="text-center">
                                    <td colspan="10">@{{mensajeTabla}}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-lg-12 mt-2">
                        <div class="alert alert-success text-right" role="alert">
                            <div class="row">
                                <div class="offset-lg-6"></div>
                                <div class="col-lg-3"><strong>Total sueldo: @{{ total_pagado }}</strong></div>
                                <div class="col-lg-3"><strong>Total bonificaciones: @{{ bonificaciones }}</strong></div>
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
                fecha_in: '{{date('Y-m')}}',
                idempleado:'<?php echo $empleado['idempleado'] ?>',

                mensajeTabla:'Cargando...',
                mostrarMensaje: false,
                lista_gastos:{},
                total_pagado:'0.00',
                bonificaciones:'0.00'
            },
            created(){
                this.obtenerDatos();
                let today = new Date().toISOString().split('T')[0];
                document.getElementsByName("fecha_in")[0].setAttribute('max', today);
            },
            methods: {
                obtenerDatos(){
                    let _this = this;
                    this.mostrarMensaje=true;
                    axios.post('/trabajadores/obtener-pagos',{
                        'fecha_in':this.fecha_in,
                        'idempleado':this.idempleado
                    })
                        .then(function (response) {
                            let datos = response.data['gastos'];
                            if(datos.length==0){
                                _this.mensajeTabla='No se han encontrado registros';
                            } else{
                                _this.lista_gastos=datos;
                                _this.mostrarMensaje=false;
                            }
                            _this.total_pagado=(response.data['total_pagado']).toFixed(2);
                            _this.bonificaciones=(response.data['extras']).toFixed(2);
                        })
                        .catch(function (error) {
                            alert('Ha ocurrido un error al obtener los datos.');
                            console.log(error);
                        });
                },

            }
        });
    </script>
@endsection