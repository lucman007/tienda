@extends('layouts.main')
@section('titulo', 'Reporte de comprobantes')
@section('contenido')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1">Reporte de caja</h3>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-10">
                <div class="row">
                    <div class="col-lg-3 mb-2 mb-lg-0">
                        <b-input-group>
                            <b-input-group-prepend>
                                <b-input-group-text>
                                    <i class="fas fa-filter"></i>
                                </b-input-group-text>
                            </b-input-group-prepend>
                            <select v-model="filtro" class="custom-select">
                                <option value="fecha">Fecha</option>
                            </select>
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
                        <span class="titulo-tabla-reporte">Lista de cajas</span>
                        <div class="form-group float-right mb-0">
                            @if(count($cajas)!=0)
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
                                    <th scope="col">Cajero</th>
                                    <th scope="col">Fecha apertura</th>
                                    <th scope="col">Fecha cierre</th>
                                    <th scope="col">Turno</th>
                                    <th scope="col">Saldo inic.</th>
                                    <th scope="col">Total ventas</th>
                                    <th scope="col">Total teórico</th>
                                    <th scope="col">Total real</th>
                                    <th scope="col">Descuadre</th>
                                    <th scope="col"></th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($cajas)!=0)
                                    @foreach($cajas as $caja)
                                        <tr>
                                            <td></td>
                                            <td>{{mb_strtoupper($caja->empleado->nombre)}}</td>
                                            <td>{{date('d/m/Y H:m',strtotime($caja->fecha_a))}}</td>
                                            <td>{{!$caja->fecha_c?'-':date('d/m/Y H:m',strtotime($caja->fecha_c))}}</td>
                                            <td><a href="/caja/{{date('Y-m-d',strtotime($caja->fecha_a))}}?turno={{$caja->turno}}">TURNO {{$caja->turno}}</a></td>
                                            <td>S/ {{$caja->apertura}}</td>
                                            <td>S/ {{$caja->total_ventas}}</td>
                                            <td>S/ {{$caja->efectivo_teorico}}</td>
                                            <td>S/ {{$caja->efectivo_real}}</td>
                                            <td style="color:{{$caja->descuadre >= 0?'inherit':'red'}}">S/ {{$caja->descuadre}}</td>
                                            <td style="width: 10%"><a href="{{url('/caja/ventas?idcaja=').$caja->idcaja}}">Ver resumen de ventas</a></td>
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
                        {{$cajas->links('layouts.paginacion')}}
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
                        window.location.href='/reportes/caja/'+this.desde+'/'+this.hasta+'?filtro='+this.filtro+'&buscar='+this.buscar;
                    }
                },
                buscar_cliente(event){
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
                        case 'cliente':
                            this.buscar='';
                            break;
                        case 'fecha':
                            window.location.href='/reportes/caja';
                            break;
                    }
                    this.desde = '{{date('Y-m-d')}}';
                    this.hasta = '{{date('Y-m-d')}}';
                },
            }

        })
    </script>
@endsection