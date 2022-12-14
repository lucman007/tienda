@extends('layouts.main')
@section('titulo', 'Guías emitidas')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-lg-9 mb-3">
                <h3 class="titulo-admin-1">Guía de remisión electrónica</h3>
                <b-button href="{{action('GuiaController@nuevo')}}" class="mr-2"  variant="primary"><i class="fas fa-plus"></i> Nueva guía</b-button>
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
                                <option value="estado">Estado</option>
                                <option value="cliente">Cliente</option>
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
                    <div class="col-lg-5 form-group" v-show="filtro=='cliente'">
                        <b-input-group>
                            <b-input-group-prepend>
                                <b-input-group-text>
                                    <i class="fas fa-user"></i>
                                </b-input-group-text>
                            </b-input-group-prepend>
                            <input v-model="buscar" type="text" class="form-control" placeholder="Buscar..." @keyup="buscar_cliente">
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
                        Lista de comprobantes
                    </div>
                    <div class="card-body">
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col">N°</th>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Cliente</th>
                                    <th scope="col" style="width: 12%">Comprobante</th>
                                    <th scope="col" style="width: 12%">Num OC</th>
                                    <th scope="col">Estado</th>
                                    <th scope="col">Opciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($guias))
                                    @foreach($guias as $guia)
                                        <tr :class="{'td-anulado':'{{$guia->estado}}'=='ANULADO'}">
                                            <td></td>
                                            <td style="width: 5%">{{$guia->idguia}}</td>
                                            <td style="width: 15%">{{$guia->fecha_emision}}</td>
                                            <td>{{$guia->cliente->persona->nombre}}</td>
                                            <td>{{$guia->correlativo}}</td>
                                            <td>{{json_decode($guia->guia_datos_adicionales,true)['oc']??''}}</td>
                                            <td><span class="badge {{$guia->badge_class}}">{{$guia->estado}}</span></td>
                                            <td class="botones-accion" style="width: 10%">
                                                <a href="{{url('guia/emision/').'/'.$guia->idguia}}">
                                                    <button class="btn btn-info" title="Ver detalle de guia">
                                                        <i class="fas fa-folder-open"></i>
                                                    </button>
                                                </a>
                                                <button class="btn btn-success" v-if="'{{$guia->estado}}'=='PENDIENTE'"
                                                        @click="consultar_guia('{{$guia->ticket}}',{{$guia->idguia}},'{{$guia->nombre_fichero}}')"
                                                        title="Enviar a Sunat">
                                                    <i :id="'icon_{{$guia->idguia}}'" class="fas fa-paper-plane d-inline-block"></i>
                                                    <span :id="'spinner_{{$guia->idguia}}'" class="d-none"><b-spinner small label="Loading..." ></b-spinner></span>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="text-center">
                                        <td colspan="8">No hay datos que mostrar</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                        {{$guias->links('layouts.paginacion')}}
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
                        window.location.href='/guia/'+this.desde+'/'+this.hasta+'?filtro='+this.filtro+'&buscar='+this.buscar;
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
                consultar_guia(ticket, idguia, file){
                    let icon = document.getElementById("icon_"+idguia);
                    icon.classList.remove('d-inline-block');
                    icon.classList.add('d-none');

                    let spinner = document.getElementById("spinner_"+idguia);
                    spinner.classList.remove('d-none');
                    spinner.classList.add('d-inline-block');

                    axios.post('{{url('guia/consultar-ticket')}}',{
                        'ticket':ticket,
                        'idguia':idguia,
                        'file':file,
                    })
                        .then(response =>  {
                            window.location.reload(true);
                        })
                        .catch(error =>  {
                            alert('error');
                            console.log(error);
                        });
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
                            window.location.href='/guia';
                            break;
                    }
                    this.desde = '{{date('Y-m-d')}}';
                    this.hasta = '{{date('Y-m-d')}}';
                },
            },

        })
    </script>
@endsection