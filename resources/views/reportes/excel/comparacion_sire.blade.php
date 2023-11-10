<table>
    <thead>
    <tr>
        <th scope="col">Serie</th>
        <th scope="col">Correlativo</th>
        <th scope="col">Total</th>
        <th scope="col">Estado</th>
        <th scope="col">Diferencia</th>
    </tr>
    </thead>
    <tbody>
    @foreach($comprobantes as $comprobante)
        <tr>
            <td>{{$comprobante['serie']}}</td>
            <td>{{$comprobante['correlativo']}}</td>
            <td>{{$comprobante['totalDB']}}</td>
            <td>{{$comprobante['estado']}}</td>
            <td>{{$comprobante['diferencia']}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
