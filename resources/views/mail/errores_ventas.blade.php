<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        td, th {
            padding: 8px;
            border: 1px solid #ccc;
            text-align: left;
            font-size: 14px;
        }
    </style>
</head>
<body>
<p>Se han detectado inconsistencias en las siguientes ventas:</p>

<table>
    <thead>
    <tr>
        <th>ID Venta</th>
        <th>Total guardado</th>
        <th>Total calculado</th>
        <th>Subtotal guardado</th>
        <th>Subtotal calculado</th>
        <th>IGV guardado</th>
        <th>IGV calculado</th>
    </tr>
    </thead>
    <tbody>
    @foreach($errores as $e)
        <tr>
            <td>{{ $e['venta_id'] }}</td>
            <td>{{ number_format($e['total_registrado'], 2) }}</td>
            <td>{{ number_format($e['total_calculado'], 2) }}</td>
            <td>{{ number_format($e['subtotal_registrado'], 2) }}</td>
            <td>{{ number_format($e['subtotal_calculado'], 2) }}</td>
            <td>{{ number_format($e['igv_registrado'], 2) }}</td>
            <td>{{ number_format($e['igv_calculado'], 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
<p>Enviado desde {{ app()->domain() }}</p>
</body>
</html>
