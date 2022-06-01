<table>
    <tr>
        <th>Fecha</th>
        <th>Caja</th>
        <th>Tipo</th>
        <th>N° comprobante</th>
        <th>Monto</th>
    </tr>
    @foreach($gastos as $gasto)
        <tr>
            <td>{{date("d-m-Y",strtotime($gasto->fecha))}}</td>
            <td>{{$gasto->caja}}</td>
            <td>{{$gasto->tipo}}</td>
            <td>{{$gasto->num_comprobante}}</td>
            <td>{{$gasto->monto}}</td>
        </tr>
    @endforeach
</table>

