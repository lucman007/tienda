<table>
    <thead>
    <tr>
        <th scope="col">Serie</th>
        <th scope="col">Correlativo</th>
        <th scope="col">Total venta</th>
        <th scope="col">Total en propuesta</th>
        <th scope="col">Diferencia</th>
        <th scope="col">Estado</th>
    </tr>
    </thead>
    <tbody>
    @foreach($comprobantes as $comprobante)
        <tr>
            <td>{{$comprobante['serie']}}</td>
            <td>{{$comprobante['correlativo']}}</td>
            <td>{{$comprobante['totalDB']}}</td>
            <td>{{$comprobante['totalArchivo']}}</td>
            <td>{{$comprobante['diferencia']}}</td>
            <td>{{$comprobante['estado']}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
