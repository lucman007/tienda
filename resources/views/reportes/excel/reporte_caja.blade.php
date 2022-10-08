<table>
    <thead>
    <tr>
        <th scope="col">idcaja</th>
        <th scope="col">Cajero</th>
        <th scope="col">Fecha apertura</th>
        <th scope="col">Fecha cierre</th>
        <th scope="col">Turno</th>
        <th scope="col">Saldo inic.</th>
        <th scope="col">Efectivo.</th>
        <th scope="col">Tarjeta(Visa)</th>
        <th scope="col">Tarjeta(Mastercard)</th>
        <th scope="col">Yape</th>
        <th scope="col">Plin</th>
        <th scope="col">Transferencia</th>
        <th scope="col">Otros</th>
        <th scope="col">Crédito</th>
        <th scope="col">Extras</th>
        <th scope="col">Gastos</th>
        <th scope="col">Devoluciones</th>
        <th scope="col">Total teórico</th>
        <th scope="col">Total real</th>
        <th scope="col">Descuadre</th>
        <th scope="col">Obervación al abrir</th>
        <th scope="col">Observación al cerrar</th>
    </tr>
    </thead>
    <tbody>
    @foreach($cajas as $caja)
        <tr>
            <td>{{$caja->idcaja}}</td>
            <td>{{$caja->empleado->nombre}}</td>
            <td>{{$caja->fecha_a}}</td>
            <td>{{$caja->fecha_c}}</td>
            <td>TURNO {{$caja->turno}}</td>
            <td>{{$caja->apertura}}</td>
            <td>{{$caja->efectivo}}</td>
            <td>{{$caja->tarjeta}}</td>
            <td>{{$caja->tarjeta_1}}</td>
            <td>{{$caja->yape}}</td>
            <td>{{$caja->plin}}</td>
            <td>{{$caja->transferencia}}</td>
            <td>{{$caja->otros}}</td>
            <td>{{$caja->credito}}</td>
            <td>{{$caja->extras}}</td>
            <td>{{$caja->gastos}}</td>
            <td>{{$caja->devoluciones}}</td>
            <td>{{$caja->efectivo_teorico}}</td>
            <td>{{$caja->efectivo_real}}</td>
            <td>{{$caja->descuadre}}</td>
            <td>{{$caja->observacion_a}}</td>
            <td>{{$caja->observacion_c}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
