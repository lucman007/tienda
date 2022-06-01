<table>
    <thead>
    <tr>
        <th>Fecha</th>
        <th>Venta dólares</th>
        <th>Venta soles</th>
    </tr>
    </thead>
    <tbody>
    @foreach($ventas as $venta)
        <tr>
            <td>{{$venta['fecha']}}</td>
            <td>{{$venta['total_dolares']}}</td>
            <td>{{$venta['total_soles']}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
