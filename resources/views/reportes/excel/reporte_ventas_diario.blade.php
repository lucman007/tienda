<table>
    <thead>
    <tr>
        <th scope="col">Fecha</th>
        <th scope="col">Moneda</th>
        <th scope="col">Ventas brutas</th>
        @if($moneda == 'USD')
            <th scope="col">Tipo de cambio</th>
        @endif
        <th scope="col">Impuestos</th>
        <th scope="col">Ventas netas</th>
        <th scope="col">Costo de bienes</th>
        <th scope="col">Utilidad bruta</th>
    </tr>
    </thead>
    <tbody>
    @foreach($ventas as $item)
        <tr>
            <td>{{ $item['fecha']}}</td>
            <td>{{ $moneda}}</td>
            <td>{{round($item['ventas_brutas'],2)}}</td>
            @if($moneda == 'USD')
                <td>{{round($item['tipo_cambio'],2)}}</td>
            @endif
            <td>{{round($item['impuestos'],2)}}</td>
            <td>{{round($item['ventas_netas'],2)}}</td>
            <td>{{round($item['costos'],2)}}</td>
            <td style="color:{{$item['utilidad']<0?'red':'inherit'}}">{{round($item['utilidad'],2)}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
