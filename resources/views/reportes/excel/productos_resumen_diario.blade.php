<table>
    <thead>
    <tr>
        <td colspan="8"><strong>REPORTE PRODUCTOS VENDIDOS POR DÍA DEL {{date('d/m/Y', strtotime($desde))}} al {{date('d/m/Y', strtotime($hasta))}}</strong></td>
    </tr>
    <tr>
        <th scope="col">Fecha</th>
        <th scope="col">Código</th>
        <th scope="col">Producto</th>
        <th scope="col">Precio del producto</th>
        <th scope="col">Cantidad vendida</th>
        <th scope="col">Ver detalle</th>
    </tr>
    </thead>
    <tbody>
    @if(count($productos) != 0)
        @foreach($productos as $producto)
            @php
                $unidad = explode('/',$producto->unidad_medida);
            @endphp
            @if($producto->tipo_producto != 4)
                <tr>
                    <td>{{date('d/m/Y', strtotime($producto->fecha))}}</td>
                    <td style="text-align: left">{{ $producto->cod_producto }}</td>
                    <td>{{ $producto->nombre }} {{ $producto->presentacion}}</td>
                    <td>{{ $producto->precio}}</td>
                    <td>{{ floatval(abs($producto->vendidos))}}</td>
                    <td><a href="{{'https://'.app()->domain().'/reportes/productos/resumen-diario?desde='.$desde.'&hasta='.$hasta.'&external=true&idproducto='.$producto->idproducto.'&fecha='.date('Y-m-d', strtotime($producto->fecha))}}">Ver detalle</a></td>
                </tr>
            @endif
        @endforeach
    @endif
    </tbody>
</table>