@extends('layouts.main')
@section('titulo', 'Notificaciones')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1">Notificaciones</h3>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 mt-4">
                <div class="card">
                    <div class="card-body">
                        @forelse($notifications as $notification)
                            <div class="alert {{$notification->read_at==null?'alert-primary':'alert-secondary'}}" role="alert">
                                @if($notification->data['tipo_notificacion'] == 1)
                                El comprobante <strong>{{ $notification->data['comprobante'] }}</strong> está en estado <strong>{{ $notification->data['estado'] }}</strong> <br>
                                Mensaje: {{ $notification->data['mensaje'] }}
                                @endif
                                @if($notification->data['tipo_notificacion'] == 2)
                                    <strong>Control de stock</strong> <br>
                                    {{ $notification->data['mensaje'] }}
                                @endif
                                <br>
                                <span>{{ date('d/m/Y', strtotime($notification->created_at)) }}</span>
                                @if($notification->read_at==null)
                                <a href="{{url('/notificaciones/marcar-como-leido/'.$notification->id)}}" class="float-right mark-as-read">
                                    Marcar como leído
                                </a>
                                @endif
                            </div>
                            @if($loop->last)
                                <a href="{{url('/notificaciones/marcar-todo-como-leido')}}" id="mark-all">
                                    Marcar todo como leído
                                </a>
                            @endif
                        @empty
                            <p class="text-center">No tiene notificaciones</p>
                        @endforelse
                            <div class="mt-4">
                                {{$notifications->links('layouts.paginacion')}}
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
            data: {}
        });
    </script>
@endsection
