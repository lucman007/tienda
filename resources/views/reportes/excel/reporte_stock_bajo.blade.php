<table>
    <thead>
    <tr>
        <th scope="col">Producto</th>
        <th scope="col">Características</th>
        <th scope="col">Stock mínimo</th>
        <th scope="col">Stock actual</th>
    </tr>
    </thead>
    <tbody>
    @foreach($productos as $producto)
        <tr>
            <td>{{$producto->nombre}}</td>
            <td>{{$producto->presentacion}}</td>
            <td>{{$producto->stock_bajo}}</td>
            <td>{{$producto->cantidad}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
