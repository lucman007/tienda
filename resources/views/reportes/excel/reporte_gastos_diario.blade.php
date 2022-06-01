<table>
    <thead>
    <tr>
        <th>Fecha</th>
        <th>Monto</th>
    </tr>
    </thead>
    <tbody>
    @foreach($gastos as $gasto)
        <tr>
            <td>{{$gasto['fecha']}}</td>
            <td>{{$gasto['total_dia']}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
