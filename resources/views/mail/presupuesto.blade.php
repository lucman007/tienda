@if($saludo_mensaje)
    <p>{!! $saludo_mensaje !!}@if($contacto)<strong>{{' '.$contacto.','}}</strong>@else','@endif</p>
@else
    @php
        $time = date("H");
    @endphp
    @if($time < "12")
        <p>Buenos días estimado cliente{!! $contacto?' <strong>'.$contacto.'</strong>,':',' !!}</p>
    @else
        @if($time >= "12" && $time < "19")
            <p>Buenas tardes estimado cliente{!! $contacto?' <strong>'.$contacto.'</strong>,':',' !!}</p>
        @else
            @if($time >= "19")
                <p>Buenas noches estimado cliente{!! $contacto?' <strong>'.$contacto.'</strong>,':',' !!}</p>
            @endif
        @endif
    @endif
@endif
@if($cuerpo_mensaje)
    <p style="white-space: break-spaces">{!! $cuerpo_mensaje !!}</p>
    <br>
@else
    <br><br>
    Es grato dirigirme a usted, para saludarle cordialmente y a la vez aprovechar la oportunidad para hacerle llegar nuestra cotización, estamos para resolverle cualquier consulta o duda.
    <br><br>
    Agradeciendo su atención, reiteramos nuestro compromiso de ofrecerle siempre el mejor servicio que usted merece.
    <br><br>
    Cordiales saludos,
    <br><br>
    Área de ventas <br>
@endif
Email: {{$config['email']}} <br>
Telf.: {{$config['telefono']}} <br>
Página web: {{$config['website']}} <br>

