@extends('layouts.main')
@section('titulo', 'Sin acceso')
@section('contenido')
<div class="flex-center position-ref full-height text-center" style="font-size: 20px;">
    <i class="fas fa-ban" style="font-size: 80px;margin-bottom: 30px;"></i>
    <div class="code">
        403
    </div>
    <div class="message" style="padding: 10px;">
        No tienes permiso para acceder a este módulo del sistema
    </div>
</div>
@endsection
@section('script')
    <script>
        let app = new Vue({
            el: '.app',
            data: {
            }
        });
    </script>
@endsection
