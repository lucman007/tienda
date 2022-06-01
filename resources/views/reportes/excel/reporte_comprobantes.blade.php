<table>
    <thead>
    <tr>
        <th scope="col">ID venta</th>
        <th scope="col">Fecha</th>
        <th scope="col">Cliente</th>
        <th scope="col">N° documento</th>
        <th scope="col">Total Exonerado</th>
        <th scope="col">Total Inafecto</th>
        <th scope="col">Total Gratuito</th>
        <th scope="col">Total Gravado</th>
        <th scope="col">IGV</th>
        <th scope="col">Total Venta</th>
        <th scope="col">Moneda</th>
        <th scope="col">Comprobante</th>
        <th scope="col">Correlativo</th>
        <th scope="col">Doc. que modifica</th>
        <th scope="col">Estado Sunat</th>
    </tr>
    </thead>
    <tbody>
    @foreach($comprobantes as $comprobante)
        <tr>
            <td>{{$comprobante->idventa}}</td>
            <td>{{date('d/m/Y', strtotime($comprobante->fecha))}}</td>
            <td>{{$comprobante->cliente->persona->nombre}}</td>
            <td>{{$comprobante->cliente->num_documento}}</td>
            <td>{{$comprobante->facturacion->total_exoneradas}}</td>
            <td>{{$comprobante->facturacion->total_inafectas}}</td>
            <td>{{$comprobante->facturacion->total_gratuitas}}</td>
            <td>{{$comprobante->facturacion->total_gravadas}}</td>
            <td>{{$comprobante->facturacion->igv}}</td>
            <td>{{$comprobante->total_venta}}</td>
            <td>{{$comprobante->facturacion->codigo_moneda}}</td>
            <td>{{$comprobante->tipo_doc}}</td>
            <td>{{$comprobante->facturacion->serie}}-{{$comprobante->facturacion->correlativo}}</td>
            <td>{{$comprobante->facturacion->num_doc_relacionado}}</td>
            <td>{{$comprobante->facturacion->estado}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
