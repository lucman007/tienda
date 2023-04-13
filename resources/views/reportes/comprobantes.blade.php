@extends('layouts.main')
@section('titulo', 'Reporte de comprobantes')
@section('contenido')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1">Reporte de comprobantes</h3>
            </div>
        </div>
        <div class="row">
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
                                <option value="documento">Comprobante</option>
                                <option value="estado">Estado</option>
                            </select>
                        </b-input-group>
                    </div>
                    <div class="col-lg-2" v-show="filtro=='documento'">
                        <b-input-group>
                            <b-input-group-prepend>
                                <b-input-group-text>
                                    <i class="fas fa-check"></i>
                                </b-input-group-text>
                            </b-input-group-prepend>
                            <select @change="filtrar" v-model="buscar" class="custom-select">
                                <option value="n">Seleccionar</option>
                                <option value="boleta">Boleta</option>
                                <option value="factura">Factura</option>
                                <option value="nota-de-credito">Nota de crédito</option>
                                <option value="nota-de-debito">Nota de débito</option>
                            </select>
                        </b-input-group>
                    </div>
                    <div class="col-lg-2" v-show="filtro=='estado'">
                        <b-input-group>
                            <b-input-group-prepend>
                                <b-input-group-text>
                                    <i class="fas fa-check"></i>
                                </b-input-group-text>
                            </b-input-group-prepend>
                            <select @change="filtrar" v-model="buscar" class="custom-select">
                                <option value="n">Seleccionar</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="aceptado">Aceptado</option>
                                <option value="anulado">Anulado</option>
                                <option value="rechazado">Rechazado</option>
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
            @if($errors->any())
                <div class="col-lg-12 mt-4">
                    <div class="alert alert-danger" style="text-align: center">{{$errors->first()}}</div>
                </div>
            @endif
            <div class="col-sm-12 mt-4">
                <div class="card">
                    <div class="card-header">
                        <span class="titulo-tabla-reporte">Lista de comprobantes</span>
                        <div class="form-group float-right mb-0">
                            @if(count($comprobantes)!=0)
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
                                    <th scope="col">Venta</th>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Cliente</th>
                                    <th scope="col">Doc</th>
                                    <th scope="col">Total</th>
                                    <th scope="col">Moneda</th>
                                    <th scope="col">Comprobante</th>
                                    <th scope="col">XML</th>
                                    <th scope="col">CDR</th>
                                    <th scope="col">PDF</th>
                                    <th v-show="buscar=='nota-de-credito' || buscar=='nota-de-debito'" scope="col">Doc. modificado</th>
                                    <th scope="col" style="width: 12%">Estado</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($comprobantes)!=0)
                                    @foreach($comprobantes as $comprobante)
                                        <tr :class="{'td-anulado':'{{$comprobante->facturacion->estado}}'=='ANULADO CON NC' || '{{$comprobante->facturacion->estado}}'=='ANULADO (COMUNICACIÓN DE BAJA)' || '{{$comprobante->facturacion->estado}}'=='MODIFICADO CON ND'}">
                                            <td></td>
                                            <td style="width: 5%">{{$comprobante->idventa}}</td>
                                            <td style="width: 15%">{{$comprobante->fecha}}</td>
                                            <td>{{$comprobante->cliente->persona->nombre}}</td>
                                            <td>{{$comprobante->cliente->num_documento}}</td>
                                            <td>{{$comprobante->total_venta}}</td>
                                            <td>{{$comprobante->facturacion->codigo_moneda}}</td>
                                            <td><a target="_blank" href="/facturacion/documento/{{$comprobante->idventa}}">{{$comprobante->facturacion->serie}}-{{$comprobante->facturacion->correlativo}}</a><br>
                                                {{$comprobante->guia_relacionada['correlativo']}}
                                            </td>
                                            <td><a href="{{url('reportes/descargar/comprobante').'/'.$comprobante->nombre_fichero.'.xml'}}"><span class="badge badge-warning">DESCARGAR <i class="fas fa-download"></i></span></a></td>
                                            <td><a href="{{url('reportes/descargar/comprobante').'/'.$comprobante->nombre_fichero.'.cdr'}}"><span class="badge badge-primary">DESCARGAR <i class="fas fa-download"></i></span></a></td>
                                            <td><a href="{{url('reportes/descargar/comprobante').'/'.$comprobante->idventa}}"><span class="badge badge-info">DESCARGAR <i class="fas fa-download"></i></span></a></td>
                                            <td v-show="buscar=='nota-de-credito' || buscar=='nota-de-debito'">{{$comprobante->facturacion->num_doc_relacionado?$comprobante->facturacion->num_doc_relacionado:'-'}}</td>
                                            <td><span class="badge {{$comprobante->badge_class}}">{{$comprobante->facturacion->estado}}</span></td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="text-center">
                                        <td colspan="12">No hay datos para mostrar</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                        {{$comprobantes->links('layouts.paginacion')}}
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
                        window.location.href='/reportes/comprobantes/'+this.desde+'/'+this.hasta+'?filtro='+this.filtro+'&buscar='+this.buscar;
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
                            window.location.href='/reportes/comprobantes';
                            break;
                    }
                    this.desde = '{{date('Y-m-d')}}';
                    this.hasta = '{{date('Y-m-d')}}';
                },
            }

        })
    </script>
@endsection