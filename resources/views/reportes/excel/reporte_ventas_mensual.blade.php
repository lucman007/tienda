<table>
    <thead>
    <tr>
        <th scope="col">Fecha</th>
        <th scope="col">Ventas brutas</th>
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
            <td>{{$moneda=='PEN'?'S/':'USD '}}{{number_format($item['ventas_brutas'],2)}}</td>
            <td>S/{{number_format($item['impuestos'],2)}}</td>
            <td>S/{{number_format($item['ventas_netas'],2)}}</td>
            <td>S/{{number_format($item['costos'],2)}}</td>
            <td style="color:{{$item['utilidad']<0?'red':'inherit'}}">S/{{number_format($item['utilidad'],2)}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
