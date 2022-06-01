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
            <div v-show="tipo_comprobante==-1" class="col-lg-10">
                <div class="row">
                    <div class="col-lg-3 form-group">
                        <label><i class="far fa-calendar-alt"></i> Desde</label>
                        <input @change="filtrar" type="date" v-model="fecha_in" name="fecha_in"
                               class="form-control">
                    </div>
                    <div class="col-lg-3 form-group">
                        <label><i class="far fa-calendar-alt"></i> Hasta</label>
                        <input @change="filtrar" type="date" v-model="fecha_out" name="fecha_out"
                               class="form-control">
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label><i class="fas fa-filter"></i> Filtrar por</label>
                            <select @change="cambiarBusqueda" v-model="tipo_busqueda" class="custom-select">
                                <option value="n">Ninguno</option>
                                <option value="estado">Estado</option>
                                <option value="cliente">Cliente</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3" v-show="tipo_busqueda=='estado'">
                        <div class="form-group">
                            <label><i class="fas fa-check"></i> Estado</label>
                            <select v-model="filtro" class="custom-select">
                                <option value="n">Seleccionar</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="aceptado">Aceptado</option>
                                <option value="anulado">Anulado</option>
                                <option value="rechazado">Rechazado</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-4 form-group" v-show="tipo_busqueda=='cliente'">
                        <label><i class="fas fa-check"></i> Cliente</label>
                        <div class="input-group" id="buscador">
                            <input v-model="filtro" type="text" class="form-control" placeholder="Buscar...">
                            <div class="input-group-append">
                                <button @click="filtrar" class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
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
                                    <th scope="col">Estado</th>
                                    <th v-show="tipo_comprobante==40">Motivo</th>
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
                                            <td><span class="badge {{$guia->badge_class}}">{{$guia->estado}}</span></td>
                                            <td class="botones-accion" style="width: 10%">
                                                <a href="{{url('guia/emision/').'/'.$guia->idguia}}">
                                                    <button class="btn btn-info" title="Ver detalle de guia">
                                                        <i class="fas fa-folder-open"></i>
                                                    </button>
                                                </a>
                                                <button class="btn btn-success" v-if="'{{$guia->estado}}'=='PENDIENTE'"
                                                        @click="enviar({{$guia->idguia}},'guia')"
                                                        title="Enviar a Sunat">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="text-center">
                                        <td colspan="7">No hay datos que mostrar</td>
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
                fecha_in: '{{$filtros['fecha_in']}}',
                fecha_out: '{{$filtros['fecha_out']}}',
                tipo_comprobante: -1,
                tipo_busqueda: '{{$filtros['busqueda']}}',
                filtro:'{{$filtros['tipo']}}'
            },
            created(){
                let today = new Date().toISOString().split('T')[0];
                document.getElementsByName("fecha_in")[0].setAttribute('max', today);
                document.getElementsByName("fecha_out")[0].setAttribute('max', today);
            },
            watch:{
                filtro(){
                    if(this.tipo_busqueda!=='cliente'){
                        window.location.href='/guia/'+this.fecha_in+'/'+this.fecha_out+'/'+this.tipo_busqueda+'/'+this.filtro;
                    }
                }
            },
            methods: {
                cambiarBusqueda(){
                    this.filtro='n';
                    if(this.tipo_busqueda==='n'){
                        window.location.href='/guia/'+this.fecha_in+'/'+this.fecha_out+'/'+this.tipo_busqueda+'/'+this.filtro;
                    }
                    if(this.tipo_busqueda==='cliente'){
                        this.filtro='';
                    }
                },
                filtrar(){
                    if(!(this.fecha_in=='' || this.fecha_out=='')){
                        window.location.href='/guia/'+this.fecha_in+'/'+this.fecha_out+'/'+this.tipo_busqueda+'/'+this.filtro;
                    }
                },
                enviar(idguia, tipo){
                    if(confirm('¿Está seguro de enviar el comprobante a SUNAT?')){
                        axios.get('{{url('ventas/enviar')}}' + '/' + tipo + '/' + idguia)
                            .then(response => {
                                alert(response.data);
                                window.location.reload();
                            })
                            .catch(error => {
                                alert('Ha ocurrido un error al enviar el documento.');
                                console.log(error);
                            });
                    }
                },
                eliminar(idguia){

                    if (confirm('¿Está seguro de eliminar la guia?')) {

                        axios.get('{{url('ventas/eliminar-venta')}}'+'/'+idguia)
                            .then(() => {
                                window.location.reload();
                            })
                            .catch(error => {
                                alert('Ha ocurrido un error al eliminar la guia.');
                                console.log(error);
                            });
                    }
                }

            }

        })
    </script>
@endsection