@extends('layouts.main')
@section('titulo', 'Reporte de comprobantes')
@section('contenido')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1">Reporte de gastos</h3>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-2">
                <b-dropdown variant="primary">
                    <template #button-content>
                        <i class="far fa-list-alt"></i> Resumen de gastos
                    </template>
                    <b-dropdown-item href="{{action('ReporteController@reporte_gastos')}}">Resumen de gastos</b-dropdown-item>
                    <b-dropdown-item href="{{url('/reportes/gastos/diario').'/'.date('Y-m')}}">Gastos diarios</b-dropdown-item>
                    <b-dropdown-item href="{{url('/reportes/gastos/mensual').'/'.date('Y')}}">Gastos mensuales</b-dropdown-item>
                </b-dropdown>
            </div>
            <div class="col-lg-10">
                <div class="row">
                    <div class="col-lg-3">
                        <b-input-group>
                            <b-input-group-prepend>
                                <b-input-group-text>
                                    <i class="fas fa-filter"></i>
                                </b-input-group-text>
                            </b-input-group-prepend>
                            <select v-model="filtro" class="custom-select">
                                <option value="fecha">Fecha</option>
                                <option value="proveedor">Proveedor</option>
                            </select>
                        </b-input-group>
                    </div>
                    <div class="col-lg-5 form-group" v-show="filtro=='proveedor'">
                        <b-input-group>
                            <b-input-group-prepend>
                                <b-input-group-text>
                                    <i class="fas fa-user"></i>
                                </b-input-group-text>
                            </b-input-group-prepend>
                            <input v-model="buscar" type="text" class="form-control" placeholder="Buscar..." @keyup="buscar_proveedor">
                            <b-input-group-append>
                                <b-button :disabled="buscar.length==0" @click="filtrar" variant="primary" ><i class="fas fa-search"></i></b-button>
                            </b-input-group-append>
                        </b-input-group>
                    </div>
                    <div class="col-lg-3 form-group">
                        <range-calendar :inicio="desde + ' 00:00:00'" :fin="hasta + ' 00:00:00'" v-on:setparams="setParams"></range-calendar>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 mt-4">
                <div class="card">
                    <div class="card-header">
                        <span class="titulo-tabla-reporte">Lista de gastos</span>
                        <div class="form-group float-right mb-0">
                            @if(count($gastos)!=0)
                                <a href="{{str_contains(url()->full(),'?')?url()->full().'&export=true':url()->current().'?export=true'}}" class="btn btn-primary d-block"><i class="fas fa-file-export"></i> Exportar excel</a>
                            @else
                                <button disabled class="btn btn-primary d-block"><i class="fas fa-file-export"></i> Exportar excel</button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">

                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col">Compra</th>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Proveedor</th>
                                    <th scope="col">Total</th>
                                    <th scope="col">Moneda</th>
                                    <th scope="col">Comprobante</th>
                                    <th scope="col">Imagen</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($gastos)!=0)
                                    @foreach($gastos as $gasto)
                                        <tr>
                                            <td></td>
                                            <td style="width: 5%">{{$gasto->idgasto}}</td>
                                            <td style="width: 15%">{{$gasto->fecha}}</td>
                                            <td>PROVEEDOR</td>
                                            <td>{{$gasto->monto}}</td>
                                            <td>{{$gasto->num_comprobante}}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="text-center">
                                        <td colspan="11">No hay datos para mostrar</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                        {{$gastos->links('layouts.paginacion')}}
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
                filtro:'{{$filtros['filtro']}}',
                desde:'{{$filtros['desde']}}',
                hasta:'{{$filtros['hasta']}}',
                buscar:'{{$filtros['buscar']}}',
            },
            methods: {
                setParams(obj){
                    let d1 = new Date(obj.startDate).toISOString().split('T')[0];
                    let d2 = new Date(obj.endDate).toISOString().split('T')[0];
                    this.desde=d1;
                    this.hasta=d2;
                    this.filtrar();
                },
                filtrar(){
                    if(this.buscar!=='n'){
                        window.location.href='/reportes/gastos/'+this.desde+'/'+this.hasta+'?filtro='+this.filtro+'&buscar='+this.buscar;
                    }
                },
                buscar_proveedor(event){
                    switch (event.code) {
                        case 'Enter':
                        case 'NumpadEnter':
                            this.filtrar();
                            break;
                    }
                },
            },
            watch:{
                filtro(){
                    this.buscar='n';
                    switch (this.filtro){
                        case 'proveedor':
                            this.buscar='';
                            break;
                        case 'fecha':
                            window.location.href='/reportes/gastos';
                            break;
                    }
                    this.desde = '{{date('Y-m-d')}}';
                    this.hasta = '{{date('Y-m-d')}}';
                },
            }

        })
    </script>
@endsection