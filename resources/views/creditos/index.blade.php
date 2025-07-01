@extends('layouts.main')
@section('titulo', 'Gestionar créditos')
@section('contenido')
    @php $agent = new \Jenssegers\Agent\Agent() @endphp
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1">Créditos</h3>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row mt-4">
                            <div class="col-lg-12">
                                <div class="row">
                                    <div class="col-lg-3 mb-2 mb-lg-0">
                                        <b-input-group>
                                            <b-input-group-prepend>
                                                <b-input-group-text>
                                                    <i class="fas fa-filter"></i>
                                                </b-input-group-text>
                                            </b-input-group-prepend>
                                            <select @change="filtrar" v-model="filtro" class="custom-select">
                                                <option value="fecha">Fecha</option>
                                                <option value="cliente">Cliente</option>
                                            </select>
                                        </b-input-group>
                                    </div>
                                    <div class="col-lg-3 form-group mb-2 mb-lg-0" v-show="filtro=='cliente'">
                                        <autocomplete-cliente v-on:agregar_cliente="verCliente"
                                                              v-on:borrar_cliente="borrarCliente"
                                                              ref="suggestCliente"></autocomplete-cliente>
                                    </div>
                                    <div class="col-lg-3 form-group" v-show="filtro=='fecha'">
                                        <range-calendar :inicio="desde + ' 00:00:00'" :fin="hasta + ' 00:00:00'" v-on:setparams="setParams"></range-calendar>
                                    </div>
                                    <div class="col-lg-6 form-group">
                                        @if(count($creditos)!=0)
                                            <a href="{{str_contains(url()->full(),'?')?url()->full().'&export=true':url()->current().'?export=true'}}" class="btn btn-primary"><i class="fas fa-file-export"></i> Exportar excel</a>
                                        @else
                                            <button disabled class="btn btn-primary"><i class="fas fa-file-export"></i> Exportar excel</button>
                                        @endif
                                            <button v-if="totales" @click="compartir" class="btn btn-success ml-2"><i class="fas fa-share-square"></i> Enviar deuda</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-2" v-if="totales">
                            <div class="col-lg-12">
                                <div class="card no-shadow">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-12 mb-3">
                                                <h5 class="d-inline" style="font-size: 18px"><strong>Resumen de créditos</strong></h5>
                                            </div>
                                            <div class="col-lg-12">
                                                {{--<span class="badge badge-primary mt-2">Transacciones en efectivo / transferencia:</span>--}}
                                                <div class="row" v-if="totales">
                                                    <div class="col-lg-12 d-flex flex-wrap flex-md-nowrap">
                                                        <div class="mr-5">
                                                            <p class="mb-0"><span class="badge badge-primary">Total deuda</span><br>
                                                                <span style="font-size: 30px;"><strong>S/@{{totales.total_credito.toFixed(2)}}</strong></span>
                                                            </p>
                                                        </div>
                                                        <div class="mr-5">
                                                            <p class="mb-0"><span class="badge badge-success">Total pagado</span><br>
                                                                <span style="font-size: 30px;"><strong>S/@{{totales.pagado.toFixed(2)}}</strong></span>
                                                            </p>
                                                        </div>
                                                        <div class="mr-5">
                                                            <p class="mb-0"><span class="badge badge-warning">Total por pagar</span><br>
                                                                <span style="font-size: 30px;"><strong>S/@{{totales.adeuda.toFixed(2)}}</strong></span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 mt-1">
                                <div class="card no-shadow">
                                    <div class="card-body">
                                        <div class="table-responsive tabla-gestionar">
                                            <table class="table table-striped table-hover table-sm">
                                                <thead class="bg-custom-green">
                                                <tr>
                                                    <th scope="col"></th>
                                                    <th scope="col"><a href="?orderby=idventa&order={{$order}}">Venta <span class="icon-hover @if($orderby=='idventa') icon-hover-active @endif">{!!$order_icon!!}</span></a></th>
                                                    <th scope="col"><a href="?orderby=fecha&order={{$order}}">Fecha <span class="icon-hover @if($orderby=='fecha') icon-hover-active @endif">{!!$order_icon!!}</span></a></th>
                                                    <th scope="col">Vend.</th>
                                                    <th scope="col" style="width:25%"><a href="?orderby=cliente&order={{$order}}">Cliente <span class="icon-hover @if($orderby=='cliente') icon-hover-active @endif">{!!$order_icon!!}</span></a></th>
                                                    <th scope="col">Importe</th>
                                                    <th scope="col">Pagado</th>
                                                    <th scope="col">Saldo</th>
                                                    <th scope="col">Próximo pago</th>
                                                    <th scope="col" style="width: 12%">Comprobante</th>
                                                    <th scope="col">Estado</th>
                                                    <th scope="col">Opciones</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @if(count($creditos) > 0)
                                                    @foreach($creditos as $venta)
                                                        <tr @if(!$agent->isDesktop()) @click="abrirCredito({{$venta->idventa}})" @endif>
                                                            <td></td>
                                                            <td>{{$venta->idventa}}</td>
                                                            <td style="width: 120px">{{date("d/m/Y",strtotime($venta->fecha))}}</td>
                                                            <td>{{mb_strtoupper($venta->empleado->nombre)}}</td>
                                                            <td>{{$venta->cliente}} {{$venta->personaAlias?'('.$venta->personaAlias->nombre.')':''}}</td>
                                                            <td>{{$venta->facturacion->codigo_moneda=='PEN'?'S/':'USD'}}{{$venta->total_venta}}</td>
                                                            <td>{{$venta->facturacion->codigo_moneda=='PEN'?'S/':'USD'}}{{number_format($venta->pagado,2)}}</td>
                                                            <td>{{$venta->facturacion->codigo_moneda=='PEN'?'S/':'USD'}}{{number_format($venta->saldo,2)}}</td>
                                                            <td>{{$venta->proximo_pago}}</td>
                                                            <td><a href="/facturacion/documento/{{$venta->idventa}}" target="_blank">{{$venta->facturacion->serie}}-{{$venta->facturacion->correlativo}}</a><br>
                                                            </td>
                                                            <td><span class="badge {{$venta->estado_badge_class}}">{{$venta->estado}}</span></td>
                                                            <td @click.stop class="botones-accion" style="width: 10%">
                                                                <a href="{{url('creditos/editar').'/'.$venta->idventa}}">
                                                                    <button class="btn btn-success" title="Abrir">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr class="text-center">
                                                        <td colspan="11">No hay datos que mostrar</td>
                                                    </tr>
                                                @endif
                                                </tbody>
                                            </table>
                                        </div>
                                        {{$creditos->links('layouts.paginacion')}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <b-modal id="modal-compartir" ref="modal-compartir">
        <template slot="modal-title">
            Compartir
        </template>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <b-button class="mb-2" @click="copiar" target="_blank"  variant="primary">
                        <i class="fas fa-copy"></i> Copiar link
                    </b-button>
                    <p style="word-wrap: break-word;"><strong>@{{ sharedLink }}</strong></p>
                </div>
                <div class="col-lg-12">
                    @php
                        $codigos_pais = \sysfact\Http\Controllers\Helpers\DataGeneral::getCodigoPais();
                    @endphp
                    <input-whatsapp :text="sharedLink" :codigos="{{json_encode($codigos_pais)}}" :link="'{{$agent->isDesktop()?'https://web.whatsapp.com':'https://api.whatsapp.com'}}'"></input-whatsapp>
                </div>
            </div>
        </div>
        <template #modal-footer="{ ok, cancel}">
            <b-button variant="secondary" @click="cancel()">Cancelar</b-button>
        </template>
    </b-modal>

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
                totales:null,
                disabledSearch:false,
                sharedLink:'',
            },
            created(){
                if(this.filtro === 'cliente'){
                    this.disabledSearch = true;
                }
            },
            mounted(){
              if(this.filtro === 'cliente'){
                  this.$refs['suggestCliente'].agregarCliente(<?php echo $cliente ?>);
                  this.obtenerBadget();
              }
            },
            methods: {
                compartir(){
                    axios.get('/creditos/generar-url'+'/'+this.buscar)
                        .then(response => {
                            this.sharedLink = response.data;
                            this.$refs['modal-compartir'].show();
                        })
                        .catch(error => {
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });
                },
                obtenerBadget(){
                    axios.get('/creditos/get-badget'+'/'+this.buscar)
                        .then(response => {
                            this.totales = response.data;
                        })
                        .catch(error => {
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });
                },
                verCliente(obj){
                    if(!this.disabledSearch){
                        window.location.href='/creditos/?filtro='+this.filtro+'&buscar='+obj.idcliente;
                    }
                },
                borrarCliente(){
                    this.disabledSearch = false;
                },
                setParams(obj){
                    let d1 = new Date(obj.startDate).toISOString().split('T')[0];
                    let d2 = new Date(obj.endDate).toISOString().split('T')[0];
                    this.desde=d1;
                    this.hasta=d2;
                    this.filtrar();
                },
                filtrar(){
                    if(this.filtro === 'fecha') {
                        window.location.href='/creditos/?filtro=fecha&desde='+this.desde+'&hasta='+this.hasta;
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
                abrirCredito(idventa){
                    location.href = '/creditos/editar'+'/'+idventa;
                },
                copiar(){
                    navigator.clipboard.writeText(this.sharedLink)
                        .then(() => {
                            this.$swal({
                                position: 'top',
                                icon: 'success',
                                title: 'Se ha copiado el link',
                                timer: 2000,
                                showConfirmButton: false,
                                toast:true
                            })
                        })
                        .catch(err => {
                            this.$swal({
                                position: 'top',
                                icon: 'error',
                                title: 'Error al copiar al portapapeles',
                                timer: 2000,
                                showConfirmButton: false,
                                toast:true
                            })
                        })
                }
            }
        });
    </script>
@endsection