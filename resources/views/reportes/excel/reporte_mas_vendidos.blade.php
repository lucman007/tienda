<table>
    <thead>
    <tr>
        <th scope="col">Código</th>
        <th scope="col">Producto</th>
        <th scope="col">Características</th>
        <th scope="col">Total vendidos</th>
        <th scope="col">Monto total</th>
    </tr>
    </thead>
    <tbody>
    @foreach($productos as $producto)
        <tr>
            <td>{{$producto->cod_producto}}</td>
            <td>{{$producto->nombre}}</td>
            <td>{{$producto->presentacion}}</td>
            <td>{{$producto->vendidos}}</td>
            <td>{{$producto->monto_total}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
