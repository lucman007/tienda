@extends('layouts.main')
@section('titulo', 'Pedidos')
@section('contenido')
    @php $agent = new \Jenssegers\Agent\Agent() @endphp
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout'] ? 'container-fluid' : 'container'}}">
        <div class="row">
            <div class="col-lg-9 mb-2 mb-lg-2">
                <h3 class="titulo-admin-1">
                    <a href="{{url()->previous()}}"><i class="fas fa-arrow-circle-left"></i></a>
                    Lista de pedidos
                </h3>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row mt-4">
                    <div class="col-lg-9">
                        <div class="row">
                            <div class="col-lg-3 mb-2 mb-lg-0">
                                <b-input-group>
                                    <b-input-group-prepend>
                                        <b-input-group-text>
                                            <i class="fas fa-filter"></i>
                                        </b-input-group-text>
                                    </b-input-group-prepend>
                                    <select @change="filtrar" v-model="buscar" class="custom-select">
                                        <option value="EN COLA">Pedidos en cola</option>
                                        <option value="ATENDIDO">Pedidos atendidos</option>
                                    </select>
                                </b-input-group>
                            </div>
                            <div class="col-lg-3 form-group">
                                <range-calendar :inicio="desde + ' 00:00:00'" :fin="hasta + ' 00:00:00'" v-on:setparams="setParams"></range-calendar>
                            </div>
                           {{-- <div class="col-lg-3 form-group">
                                @if(count($pedidos)!=0)
                                    <a href="{{str_contains(url()->full(),'?')?url()->full().'&export=true':url()->current().'?export=true'}}" class="btn btn-primary"><i class="fas fa-file-export"></i> Exportar excel</a>
                                @else
                                    <button disabled class="btn btn-primary"><i class="fas fa-file-export"></i> Exportar excel</button>
                                @endif
                            </div>--}}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 my-2">
                        <div class="card no-shadow">
                            <div class="card-body">
                                <div class="table-responsive tabla-gestionar">
                                    <table class="table table-striped table-hover table-sm">
                                        <thead class="bg-custom-green">
                                        <tr>
                                            <th></th>
                                            <th scope="col">
                                                <a href="{{ app('sysfact\Http\Controllers\Helpers\MainHelper')->urlOrdenamiento(request(), 'idorden') }}">
                                                    N° Ped. <span class="icon-hover @if($orderby == 'idorden') icon-hover-active @endif">{!! $order_icon !!}</span>
                                                </a>
                                            </th>
                                            <th scope="col">
                                                <a href="{{ app('sysfact\Http\Controllers\Helpers\MainHelper')->urlOrdenamiento(request(), 'fecha') }}">
                                                    Fecha <span class="icon-hover @if($orderby == 'fecha') icon-hover-active @endif">{!! $order_icon !!}</span>
                                                </a>
                                            </th>
                                            <th scope="col">Vendedor</th>
                                            <th scope="col">Cliente</th>
                                            <th scope="col">Total</th>
                                            <th scope="col" style="width: 20%">Nota</th>
                                            <th scope="col">Despacho</th>
                                            <th scope="col">
                                                <a href="{{ app('sysfact\Http\Controllers\Helpers\MainHelper')->urlOrdenamiento(request(), 'fecha_entrega') }}">
                                                    Fecha entrega <span class="icon-hover @if($orderby == 'fecha_entrega') icon-hover-active @endif">{!! $order_icon !!}</span>
                                                </a>
                                            </th>
                                            <th scope="col"></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(count($pedidos) > 0)
                                            @foreach($pedidos as $pedido)
                                                <tr @if(!$agent->isDesktop()) @click="editarPedido({{$pedido->idorden}})" @endif>
                                                    <td></td>
                                                    <td>{{$pedido->idorden}}</td>
                                                    <td>{{date('d/m/Y', strtotime($pedido->fecha))}}</td>
                                                    <td>{{$pedido->vendedor->idpersona == -1?'-':mb_strtoupper($pedido->vendedor->nombre)}}</td>
                                                    <td>{{$pedido->alias}}</td>
                                                    <td>{{$pedido->total}}</td>
                                                    <td>{{$pedido->nota}}</td>
                                                    <td><span class="badge {{$pedido->badge_class}}">{{$pedido->despacho}}</span></td>
                                                    <td>{{$pedido->fecha_entrega?date('d/m/Y', strtotime($pedido->fecha_entrega)):''}}</td>
                                                    <td @click.stop class="botones-accion" style="text-align: right">
                                                        <b-button @click="editarPedido({{$pedido->idorden}})" class="btn btn-success" title="Editar pedido"><i class="fas fa-edit"></i></b-button>
                                                        <button @click="borrarPedido({{$pedido->idorden}})" class="btn btn-danger" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr class="text-center">
                                                <td colspan="10">No hay datos que mostrar</td>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                                {{$pedidos->links('layouts.paginacion')}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--INICIO MODAL -->
    <b-modal id="modal-1" ref="modal-1"
             title="" @@ok="guardarPedido" @@hidden="resetModal">
        <template slot="modal-title">
            @{{tituloModal}}
        </template>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="total">Fecha entrega:</label>
                        <input type="date" v-model="fecha_entrega" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="total">Despacho:</label>
                        <select v-model="despacho" class="custom-select">
                            <option value="1">En proceso</option>
                            <option value="2">Entregado</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="nota">Nota:</label>
                        <textarea v-model="nota" class="form-control" cols="30" rows="4"></textarea>
                    </div>
                    <div v-for="error in errorDatosPedido">
                        <p class="texto-error">@{{ error }}</p>
                    </div>
                </div>
            </div>
        </div>
    </b-modal>
    <!--FIN MODAL -->

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
                errorDatosPedido: [],
                errorPedido: 0,
                tituloModal:'Agregar pedido',
                accion:'insertar',
                nota: "",
                fecha_entrega: "",
                estado: "",
                idorden: -1,
                despacho:1
            },
            methods: {
                busqueda(id){
                    this.buscar = id;
                    this.filtrar();
                },
                setParams(obj){
                    let d1 = new Date(obj.startDate).toISOString().split('T')[0];
                    let d2 = new Date(obj.endDate).toISOString().split('T')[0];
                    this.desde=d1;
                    this.hasta=d2;
                    this.filtrar();
                },
                filtrar(){
                    window.location.href='/lista-pedidos/'+this.desde+'/'+this.hasta+'?filtro='+this.filtro+'&buscar='+this.buscar;
                },
                guardarPedido(e){
                    if (this.validarPedido()) {
                        e.preventDefault();
                        return;
                    }

                    let data = {
                        'idorden': this.idorden,
                        'nota': this.nota,
                        'despacho': this.despacho,
                        'fecha_entrega': this.fecha_entrega,
                    };
                    axios.put('{{action('PedidoListaController@update')}}',data)
                        .then(() => {
                            window.location.reload(true)
                        })
                        .catch(error => {
                            this.alerta('Ha ocurrido un error.');
                            console.log(error);
                        });

                },
                editarPedido(id){
                    this.tituloModal='Editar pedido';
                    this.accion='editar';
                    this.idorden=id;
                    axios.get('{{url('/lista-pedidos/edit')}}' + '/' + id)
                        .then(response => {
                            let datos = response.data;
                            this.nota=datos.nota;
                            this.fecha_entrega=datos.fecha_entrega||'{{date('Y-m-d')}}';
                            this.estado=datos.estado;
                            this.despacho = datos.despacho;
                            this.$refs['modal-1'].show();
                        })
                        .catch(error => {
                            this.alerta('Ha ocurrido un error.');
                            console.log(error);
                        });

                },
                borrarPedido(id){
                    if (confirm('¿Está seguro de eliminar el pedido?')) {
                        axios.delete('{{url('/lista-pedidos/destroy')}}' + '/' + id)
                            .then(() => {
                                window.location.reload(true)
                            })
                            .catch((error) => {
                                console.log(error);
                            });
                    }
                },
                validarPedido(){
                    this.errorPedido = 0;
                    this.errorDatosPedido = [];
                    if (!this.fecha_entrega) this.errorDatosPedido.push('Fecha no tiene el formato correcto');
                    if (this.errorDatosPedido.length) this.errorPedido = 1;
                    return this.errorPedido;
                },
                resetModal(){
                    this.errorDatosPedido=[];
                    this.errorPedido= 0;
                    this.tituloModal='Agregar pedido';
                    this.accion='insertar';
                    this.nota= '';
                    this.fecha_entrega= null;
                    this.estado= '';
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
