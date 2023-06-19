<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Cód.</th>
        <th>Clasificación</th>
        <th>Producto</th>
        <th>Descripción</th>
        <th>Stock</th>
        <th>Compra</th>
        <th>Moneda compra</th>
        <th>Precio venta</th>
        <th>Moneda precio venta</th>
        <th>Montaje</th>
        <th>Cápsula</th>
        <th>Tipo</th>
        <th>Marca</th>
        <th>Modelo</th>
        <th>Precio min</th>
        <th>Moneda precio min</th>
    </tr>
    </thead>
    <tbody>
    @foreach($productos as $producto)
        <tr>
            <td>{{$producto->idproducto}}</td>
            <td>{{$producto->cod_producto}}</td>
            @if($producto->tipo_producto==1)
                <td >PRODUCTO</td>
            @else
                <td>SERVICIO</td>
            @endif
            <td>{{$producto->nombre}}</td>
            <td>{{preg_replace("/[^A-Za-zÁÉÍÓÚáéíóú0-9.!? ]/",'',$producto->presentacion)}}</td>
            @if($producto->tipo_producto==1)
                <td >{{$producto->cantidad}}</td>
            @else
                <td>-</td>
            @endif
            <td>{{$producto->costo}}</td>
            <td>{{$producto->moneda_compra}}</td>
            <td>{{$producto->precio}}</td>
            <td>{{$producto->moneda}}</td>
            <td>{{$producto->param_1}}</td>
            <td>{{$producto->param_2}}</td>
            <td>{{$producto->param_3}}</td>
            <td>{{$producto->marca}}</td>
            <td>{{$producto->modelo}}</td>
            <td>{{$producto->param_4}}</td>
            <td>{{$producto->param_5}}</td>
            <td></td>
        </tr>
    @endforeach
    </tbody>
</table>
