@extends('layouts.main')
@section('titulo', 'Guia '.$guia->correlativo)
@section('contenido')
    @php $agent = new \Jenssegers\Agent\Agent() @endphp
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-sm-12">
                <h3 class="titulo-admin-1">Guía {{$guia->correlativo}}</h3>
                <b-button href="{{action('GuiaController@nuevo')}}" class="mr-2"  variant="primary"><i class="fas fa-plus"></i> Nueva guía</b-button>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 my-3">
                <div class="card">
                    <div class="card-header">
                        Detalle
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4">
                                <strong>Fecha:</strong> {{date("d/m/Y H:i:s",strtotime($guia->fecha_emision))}}<hr>
                                <strong>Dirección de llegada:</strong> {{$guia->direccion_llegada}}<hr>
                                <strong>Fecha de traslado:</strong> {{date("d/m/Y",strtotime($guia->fecha_traslado))}} <hr>
                            </div>
                            <div class="col-lg-4">
                                <strong>Transporte: </strong>{{$guia->tipo_transporte}}  <hr>
                                <strong>Motivo de traslado:</strong> {{$guia->motivo_traslado}}  <hr>
                                <strong>Peso y bultos: </strong>{{$guia->peso_bruto.' KG / '.$guia->cantidad_bultos.' UND'}}<hr>
                            </div>
                            <div class="col-lg-4">
                                <strong>Estado de guía:</strong> <span class="badge {{$guia->badge_class}}">{{$guia->estado}}</span>
                                @if($guia->estado=='PENDIENTE')
                                    <a href="/guia/correccion/{{$guia->idguia}}"><span class="badge badge-primary"><i class="fas fa-edit"></i> CORREGIR</span></a><hr>
                                @else
                                    <hr>
                                @endif
                                @if($guia->num_doc_relacionado)
                                    <strong>Documento relacionado:</strong> {{$guia->num_doc_relacionado}}<hr>
                                @endif
                                @if($guia->estado=='PENDIENTE')
                                <strong>Mensaje:</strong> @{{mensaje}}  <hr>
                                @endif
                                @if($guia->estado=='ACEPTADO' && $guia->nota)
                                    <strong>Observación:</strong> {{$guia->nota}}  <hr>
                                @endif
                            </div>
                            <div class="col-lg-8">
                                <strong>Cliente:</strong> {{$guia->cliente['num_documento']}} - {{$guia->persona['nombre']}} <hr>
                            </div>
                        </div>
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm tabla-facturar">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col" style="width: 10px"></th>
                                    <th scope="col" style="width: 200px">Producto</th>
                                    <th scope="col" style="width: 250px">Caracteristicas</th>
                                    <th scope="col" style="width: 90px">Cantidad</th>
                                    <th scope="col" style="width: 50px"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(producto,index) in productosSeleccionados" :key="producto.index">
                                    <td></td>
                                    <td style="display:none">@{{producto.idproducto}}</td>
                                    <td v-if="producto.idproducto!=-1">@{{ producto.nombre }}</td>
                                    <td v-if="producto.idproducto==-1">@{{ producto.detalle.producto_nombre }}</td>
                                    <td style="white-space: pre">@{{ producto.detalle.descripcion}}</td>
                                    <td>@{{ producto.detalle.cantidad }}</td>
                                    <td>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 mb-5">
                <div class="card">
                    <div class="card-header">
                        Acciones
                    </div>
                    <div class="card-body pt-5">
                        <div v-if="!(estado=='ANULADO' || estado=='RECHAZADO')" class="form-group text-center">
                            <b-button v-if="estado=='PENDIENTE'" class="mb-2" href="/guia/correccion/{{$guia->idguia}}"
                                      variant="primary">
                                <i v-show="!mostrarProgreso" class="fas fa-edit"></i>
                                <b-spinner v-show="mostrarProgreso" small label="Loading..." ></b-spinner> Corregir guía

                            </b-button>
                            <b-button v-if="estado=='PENDIENTE'" class="mb-2" @click="enviar_guia('{{$guia->ticket}}','{{$guia->idguia}}')"
                                      variant="success">
                                <i v-show="!mostrarProgresoEnvio" class="fas fa-paper-plane"></i>
                                <b-spinner v-show="mostrarProgresoEnvio" small label="Loading..." ></b-spinner>

                            </b-button>
                            <b-button class="mb-2"  href="{{url('ventas/descargar').'/'.$guia->idguia.'?guia=true'}}" title="Descargar PDF" variant="warning">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-pdf" viewBox="0 0 16 16">
                                    <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                                    <path d="M4.603 14.087a.81.81 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.68 7.68 0 0 1 1.482-.645 19.697 19.697 0 0 0 1.062-2.227 7.269 7.269 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .477.365c.088.164.12.356.127.538.007.188-.012.396-.047.614-.084.51-.27 1.134-.52 1.794a10.954 10.954 0 0 0 .98 1.686 5.753 5.753 0 0 1 1.334.05c.364.066.734.195.96.465.12.144.193.32.2.518.007.192-.047.382-.138.563a1.04 1.04 0 0 1-.354.416.856.856 0 0 1-.51.138c-.331-.014-.654-.196-.933-.417a5.712 5.712 0 0 1-.911-.95 11.651 11.651 0 0 0-1.997.406 11.307 11.307 0 0 1-1.02 1.51c-.292.35-.609.656-.927.787a.793.793 0 0 1-.58.029zm1.379-1.901c-.166.076-.32.156-.459.238-.328.194-.541.383-.647.547-.094.145-.096.25-.04.361.01.022.02.036.026.044a.266.266 0 0 0 .035-.012c.137-.056.355-.235.635-.572a8.18 8.18 0 0 0 .45-.606zm1.64-1.33a12.71 12.71 0 0 1 1.01-.193 11.744 11.744 0 0 1-.51-.858 20.801 20.801 0 0 1-.5 1.05zm2.446.45c.15.163.296.3.435.41.24.19.407.253.498.256a.107.107 0 0 0 .07-.015.307.307 0 0 0 .094-.125.436.436 0 0 0 .059-.2.095.095 0 0 0-.026-.063c-.052-.062-.2-.152-.518-.209a3.876 3.876 0 0 0-.612-.053zM8.078 7.8a6.7 6.7 0 0 0 .2-.828c.031-.188.043-.343.038-.465a.613.613 0 0 0-.032-.198.517.517 0 0 0-.145.04c-.087.035-.158.106-.196.283-.04.192-.03.469.046.822.024.111.054.227.09.346z"/>
                                </svg>
                                PDF
                            </b-button>
                            <b-button class="mb-2" href="{{url('ventas/descargar').'/'.$guia->nombre_fichero.'.xml'}}" title="Descargar XML" variant="warning">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-code-slash" viewBox="0 0 16 16">
                                    <path d="M10.478 1.647a.5.5 0 1 0-.956-.294l-4 13a.5.5 0 0 0 .956.294l4-13zM4.854 4.146a.5.5 0 0 1 0 .708L1.707 8l3.147 3.146a.5.5 0 0 1-.708.708l-3.5-3.5a.5.5 0 0 1 0-.708l3.5-3.5a.5.5 0 0 1 .708 0zm6.292 0a.5.5 0 0 0 0 .708L14.293 8l-3.147 3.146a.5.5 0 0 0 .708.708l3.5-3.5a.5.5 0 0 0 0-.708l-3.5-3.5a.5.5 0 0 0-.708 0z"/>
                                </svg>
                                XML
                            </b-button>
                            <b-button class="mb-2"
                                      @if(json_decode(cache('config')['interfaz'], true)['tipo_impresion'] == 1 && $agent->isDesktop())
                                      target="_blank" href="{{url('guia/imprimir').'/'.$guia->idguia}}"
                                      @else
                                      @click="imprimir('{{$guia->idguia}}')"
                                      @endif
                                      variant="secondary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16">
                                    <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                                    <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
                                </svg>
                            </b-button>
                        </div>
                        <div class="col-lg-8 offset-lg-2 mt-5">
                            <div class="form-group">
                                <label>Enviar a correo electrónico:</label>
                                <input v-model="mail" type="email" class="form-control">
                                <b-button @click="enviar_a_correo" variant="primary" class="boton_adjunto">
                                    <i v-show="!mostrarProgresoMail" class="fas fa-envelope"></i>
                                    <b-spinner v-show="mostrarProgresoMail" small label="Loading..." ></b-spinner> Enviar
                                </b-button>
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
            data: {
                accion: 'insertar',
                mostrarProgreso: false,
                mostrarProgresoMail: false,
                mostrarProgresoEnvio: false,
                fecha: '{{date('Y-m-d')}}',
                estado: '<?php echo $guia->estado?>',
                clienteSeleccionado: {},
                nombreCliente: '',
                buscar: '',
                mostrarSpinnerCliente: false,
                productosSeleccionados: <?php echo$guia['productos'] ?>,
                mostrarSpinnerProducto: false,
                comprobanteReferencia:'',
                mail:"<?php echo $guia->persona->correo ?>",
                mensaje:'<?php echo $guia->response ?>'
            },
            created(){
                if('<?php echo $guia->estado ?>' == 'PENDIENTE' && (('<?php echo basename(url()->previous()) ?>').includes('nuevo') || ('<?php echo url()->previous() ?>').includes('correccion'))){

                    this.enviar_guia(<?php echo '"'.$guia->ticket.'",'.$guia->idguia ?>);
                }
            },
            methods: {
                enviar_a_correo(){
                    if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(this.mail))
                    {
                        let file_guia= '<?php echo$guia->nombre_fichero ?>';
                        this.mostrarProgresoMail = true;
                        axios.post('{{url('guia/mail')}}',{
                            'guia':file_guia,
                            'mail':this.mail,
                            'idguia':'{{$guia->idguia}}'
                        })
                            .then(response => {
                                this.alerta(response.data, 'success');
                                this.mail='';
                                this.mostrarProgresoMail = false;
                            })
                            .catch(error => {
                                this.alerta(error.response.data.mensaje,'error');
                                console.log(error);
                                this.mostrarProgresoMail = false;
                            });
                    } else{
                        this.alerta("El correo electrónico ingresado no es válido");
                    }
                },
                imprimir(file){
                    let src = "/guia/imprimir/"+file+'.pdf';
                    @if(!$agent->isDesktop())
                        @if(isset(json_decode(cache('config')['interfaz'], true)['rawbt']) && json_decode(cache('config')['interfaz'], true)['rawbt'])
                            axios.get(src+'?rawbt=true')
                            .then(response => {
                                window.location.href = response.data;
                            })
                            .catch(error => {
                                alert('Ha ocurrido un error al imprimir con RawBT.');
                                console.log(error);
                            });
                        @else
                            window.open(src, '_blank');
                        @endif
                    @else
                    let iframe = document.createElement('iframe');
                    document.body.appendChild(iframe);
                    iframe.style.display = 'none';
                    iframe.onload = () => {
                        setTimeout(() => {
                            iframe.focus();
                            iframe.contentWindow.print();
                        }, 0);
                    };
                    iframe.src = src;
                    @endif
                },
                enviar_guia(ticket, idguia){
                    this.mostrarProgresoEnvio = true;
                    axios.post('{{url('guia/consultar-ticket')}}',{
                        'ticket':ticket,
                        'idguia':idguia,
                        'file':"<?php echo $guia->nombre_fichero ?>",
                    })
                        .then(response =>  {
                            this.$bvToast.toast(response.data[0], {
                                title: 'Envío de guía',
                                variant: 'primary',
                                solid: true
                            });
                            this.estado = response.data[1];
                            this.mensaje = response.data[0];
                            this.mostrarProgresoEnvio = false;
                        })
                        .catch(error =>  {
                            alert('error');
                            console.log(error);
                            this.mostrarProgresoEnvio = false;
                        });
                },
                alerta(texto, icon){
                    this.$swal({
                        position: 'top',
                        icon: icon || 'warning',
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