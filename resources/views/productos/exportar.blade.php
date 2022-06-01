<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Cód.</th>
        <th>Tipo</th>
        <th>Producto</th>
        <th>Características</th>
        <th>Stock</th>
        <th>Costo</th>
        <th>Precio</th>
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
            <td>{{$producto->presentacion}}</td>
            @if($producto->tipo_producto==1)
                <td >{{$producto->cantidad}}</td>
            @else
                <td>-</td>
            @endif
            <td>{{$producto->costo}}</td>
            <td>{{$producto->precio}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
