@php
    $texto = isset($esRechazados) && $esRechazados ? 'rechazado(s)' : 'pendiente(s)';
@endphp

<p>Hay {{ $num_comprobantes }} comprobante{{ $num_comprobantes != 1 ? 's' : '' }} {{ $texto }} en el sistema de {{ $emisor }}.</p>
<p>Enviado desde {{ $domain }}</p>
