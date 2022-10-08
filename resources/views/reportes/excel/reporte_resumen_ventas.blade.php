<table>
    <tr>
        <td colspan="2"><strong>Reporte del {{date('d/m/Y',strtotime($fecha['desde']))}} al {{date('d/m/Y',strtotime($fecha['hasta']))}}</strong></td>
    </tr>
</table>
<table>
    <thead>
    <tr>
        <td colspan="2"><strong>VENTAS EN SOLES</strong></td>
    </tr>
    <tr>
        <th scope="col"><strong>Venta</strong></th>
        <th scope="col"><strong>Monto</strong></th>
    </tr>
    </thead>
    <tbody>
        <tr>
            <td>Ventas brutas</td>
            <td>{{$totales['ventas_brutas']}}</td>
        </tr>
        <tr>
            <td>Impuestos</td>
            <td>{{$totales['impuestos']}}</td>
        </tr>
        <tr>
            <td>Ventas netas</td>
            <td>{{$totales['ventas_netas']}}</td>
        </tr>
        <tr>
            <td>Costos</td>
            <td>{{$totales['costos']}}</td>
        </tr>
        <tr>
            <td>Utilidad</td>
            <td>{{$totales['utilidad']}}</td>
        </tr>
    </tbody>
</table>
<table>
    <thead>
    <tr>
        <td colspan="2"><strong>VENTAS POR TIPO DE PAGO</strong></td>
    </tr>
    <tr>
        <th scope="col"><strong>Tipo de pago</strong></th>
        <th scope="col"><strong>Monto</strong></th>
    </tr>
    </thead>
    <tbody>
    @foreach($tipo_pago as $key=>$pago)
        <tr>
            <td>{{$key}}</td>
            <td>{{$pago}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
<table>
    <thead>
    <tr>
        <td colspan="8"><strong>LISTA DE VENTAS</strong></td>
    </tr>
    <tr>
        <th scope="col"><strong>Venta</strong></th>
        <th scope="col"><strong>Fecha</strong></th>
        <th scope="col"><strong>Cliente</strong></th>
        <th scope="col"><strong>Importe</strong></th>
        <th scope="col"><strong>Moneda</strong></th>
        <th scope="col"><strong>Tipo de pago</strong></th>
        <th scope="col"><strong>Pago</strong></th>
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
            <td>{{$venta->fecha}}</td>
            <td>{{$venta->cliente->persona->nombre}}</td>
            <td>{{$venta->total_venta}}</td>
            <td>{{$venta->facturacion->codigo_moneda}}</td>
            @php
                $find = array_search($venta->tipo_pago, array_column($tipo_de_pago,'num_val'));
            @endphp
            <td>{{strtoupper($tipo_de_pago[$find]['label'])}}</td>
            <td>
                @php
                    $i = 0;
                @endphp
                @foreach($venta->pago as $pago)
                    @if($i != 0)
                        <br>
                    @endif
                    @php
                        $index = array_search($pago->tipo, array_column($tipo_de_pago,'num_val'));
                        $i++;
                    @endphp
                    {{strtoupper($tipo_de_pago[$index]['label'])}} {{$pago->monto}}
                @endforeach
            </td>
            <td>{{$venta->facturacion->serie}}-{{$venta->facturacion->correlativo}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
