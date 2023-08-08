<table>
    <thead>
    <tr>
        <td colspan="8"><strong>LISTA DE VENTAS ELIMINADAS</strong></td>
    </tr>
    <tr>
        <th scope="col"><strong>Venta</strong></th>
        <th scope="col"><strong>Fecha</strong></th>
        <th scope="col"><strong>Moneda</strong></th>
        <th scope="col"><strong>Total</strong></th>
        <th scope="col"><strong>Comprobante</strong></th>
    </tr>
    </thead>
    <tbody>
    @php
        $tipo_de_pago = \sysfact\Http\Controllers\Helpers\DataTipoPago::getTipoPago();
    @endphp
    @foreach($ventas as $venta)
        <tr>
            <td>{{$venta->idventa}}</td>
            <td>{{date('d-m-Y', strtotime($venta->fecha))}}</td>
            <td>{{$venta->facturacion->codigo_moneda}}</td>
            <td>{{$venta->total_venta}}</td>
            <td>{{$venta->facturacion->serie}}-{{$venta->facturacion->correlativo}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
