<table>
    <thead>
    <tr>
        <th scope="col">Venta</th>
        <th scope="col">Fecha</th>
        <th scope="col">Vend.</th>
        <th scope="col">Cliente</th>
        <th scope="col">Importe</th>
        <th scope="col">Moneda</th>
        <th scope="col">Comprobante</th>
        <th scope="col">Estado</th>
    </tr>
    </thead>
    <tbody>
    @foreach($ventas as $venta)
        <tr>
            <td>{{$venta->idventa}}</td>
            <td>{{date("d/m/Y",strtotime($venta->fecha))}}</td>
            <td>{{mb_strtoupper($venta->empleado->nombre)}}</td>
            <td>{{$venta->cliente}} {{$venta->alias?'('.$venta->alias.')':''}}</td>
            <td>{{$venta->total_venta}}</td>
            <td>{{$venta->facturacion->codigo_moneda}}</td>
            <td>{{$venta->facturacion->serie}}-{{$venta->facturacion->correlativo}}</td>
            <td>{{$venta->estado}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
