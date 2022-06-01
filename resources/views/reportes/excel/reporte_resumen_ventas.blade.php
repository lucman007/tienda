<table>
    <thead>
    <tr>
        <th scope="col">Venta</th>
        <th scope="col">Fecha</th>
        <th scope="col">Tipo</th>
        <th scope="col">Cliente</th>
        <th scope="col">Importe</th>
        <th scope="col">Moneda</th>
        <th scope="col">Pago</th>
        <th scope="col">Comprobante</th>
    </tr>
    </thead>
    <tbody>
    @foreach($ventas as $venta)
        <tr>
            <td>{{$venta->idventa}}</td>
            <td>{{$venta->fecha}}</td>
            <td>{{$venta->tipo_doc}}</td>
            <td>{{$venta->cliente->persona->nombre}}</td>
            <td>{{$venta->total_venta}}</td>
            <td>{{$venta->facturacion->codigo_moneda}}</td>
            <td>{{$venta->tipo_pago}}</td>
            <td>{{$venta->facturacion->serie}}-{{$venta->facturacion->correlativo}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
