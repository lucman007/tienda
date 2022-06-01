@php /** Modelo 7 - habilitado**/@endphp
<!doctype html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- CSS personalizado -->
    <link rel="stylesheet" href="{{asset('css/style_pdf.css')}}">
    <title>@yield('titulo') </title>
</head>
<body>
<div class="header">
    <div class="box-header" style="width: 200mm; background: #dfdfdf; height: 8mm; border-radius: 8px; margin-top: 10mm; position: relative">
        @if($emisor->logo)
            <div class="logo" style="position: absolute; width: 250px; top:-10mm">
                <img src="{{'images/'.$emisor->logo}}" style="width: 100%">
            </div>
        @endif
        <div class="fecha" style="position: absolute; right: 10mm">
            <p>FECHA: {{ date('d/m/Y',strtotime($presupuesto->fecha)) }} HORA: {{ date('g:i A',strtotime($presupuesto->fecha)) }}</p>
        </div>
    </div>
</div>
<div class="num-cotizacion" style="width: 190mm; background: #2831ff; padding: 2mm 5mm; text-align: center; border-radius: 15px 15px 0 0;">
    <h3 style="color:white; font-size: 22px"><span>COTIZACIÓN N° {{$presupuesto['correlativo']}}</span></h3>
</div>
<div class="info-usuario" style="width: 190mm; background: #dfdfdf; border-radius: 0 0 15px 15px; padding: 5mm; margin-bottom: 5mm">
    <table cellpadding="0">
        <tr>
            <td><strong>Cliente:</strong></td>
            <td colspan="3">{{$usuario->persona->nombre}}</td>
        </tr>
        <tr>
            <td><strong>Dirección:</strong></td>
            <td style="width: 120mm" colspan="3">{{ $usuario->persona->direccion }}</td>
        </tr>
        <tr>
            <td><strong>Ruc:</strong></td>
            <td>{{ $usuario->num_documento }}</td>
        </tr>
    </table>
</div>
<div class="info-cotizacion" style="width: 190mm; background: #dfdfdf; border-radius: 15px 15px 15px 15px; padding: 5mm">
    <p>
        <strong>Moneda: </strong>
        @if($presupuesto->moneda=='S/')
            SOLES / &nbsp;
        @else
            DÓLARES /
        @endif
        <strong>Atención:</strong>{{ mb_strtoupper($presupuesto->atencion) }} <br>
        <strong>Forma de pago:</strong> {{strtoupper($presupuesto->condicion_pago)}} /
        <strong>Tiempo de entrega:</strong> {{strtoupper($presupuesto->tiempo_entrega)}} /
        <strong>Punto de entrega:</strong> {{mb_strtoupper($presupuesto->lugar_entrega)}} <br>
        @if($presupuesto->exportacion)
            <strong>Tipo venta:</strong> Exportación <br>
            <strong>Incoterm:</strong> {{$presupuesto->incoterm}}
        @endif
        <br>
        @if($presupuesto->exportacion)
            <strong>Beneficiario : </strong> LINE TECH EIRL<br>
            <strong>Código SWIFT:</strong> BCPLPEPL <br>
            <strong>Cuenta N°:</strong> 192-2669185-1-73 <br>
            <strong>Banco:</strong> BANCO DE CREDITO DEL PERU <br>
        @else

            <strong>Cta. detracciones:</strong> {{$emisor->cuenta_detracciones}} <br>
            <strong>Cta. Soles:</strong> {{$emisor->cuenta_1}} <br>
            <strong>Cta. Dólares:</strong> {{$emisor->cuenta_2}} <br>
        @endif
    </p>
</div>
<div class="div-table-header">
</div>
<div class="body">
    @php($i=1)
        <table class="items" cellpadding="0">
            <thead>
                <tr class="table-header" style="color: white">
                    <td><strong>#</strong></td>
                    <td><strong>CONCEPTO</strong></td>
                    <td><strong>CANT.</strong></td>
                    <td><strong>UND</strong></td>
                    <td><strong>P. UNITARIO</strong></td>
                    <td><strong>DSCTO</strong></td>
                    <td style="text-align: right"><strong>IMPORTE</strong></td>
                </tr>
            </thead>
            <tbody>
            @foreach($presupuesto['productos'] as $item)
                <tr>
                    <td style="width: 5mm">{{$i++}}</td>
                    <td style="width: 65mm"><strong>{{$item->nombre}}</strong><br> {!!$item->detalle['descripcion']!!}</td>
                    <td style="width: 20mm">{{floatval($item->detalle['cantidad'])}}</td>
                    <td style="width: 10mm">{{explode('/',$item->unidad_medida)[1]}}</td>
                    <td style="width: 20mm; text-align: right">{{number_format($item->monto, 3)}}</td>
                    <td style="width: 15mm; text-align: right">{{$item->monto_descuento}}</td>
                    <td style="width: 20mm; text-align: right">{{number_format($item->total,2)}}</td>
                </tr>
            @endforeach
            @if(trim($presupuesto->observaciones)!='')
                <tr>
                    <td><br></td>
                </tr>
                <tr>
                    <td colspan="7" style="margin-top: 5mm">
                        Observación: {{$presupuesto->observaciones}}
                    </td>
                </tr>
            @endif
            @if($presupuesto->descuento > 0)
                <tr>
                    <td colspan="7" style="margin-top: 5mm">
                        Descuento global: {{$presupuesto->descuento_global}}
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
</div>
<table class="footer">
    <tr>
        <td class="footer-l">
            <p><strong>VALIDEZ DE ESTA COTIZACIÓN: {{$presupuesto->validez}} DÍAS</strong></p>
            <p><strong>{{$emisor->razon_social}}</strong><br>@if($emisor->texto_publicitario) {{mb_strtoupper($emisor->texto_publicitario)}}<br><br> @endif  R.U.C.: {{$emisor->ruc}}<br>{{$emisor->direccion}}, {{$emisor->urbanizacion}}, {{$emisor->provincia}},
                {{$emisor->departamento}}, {{$emisor->distrito}} <br> {{$emisor->telefono_1}} / {{$emisor->email}}</p>
        </td>
        <td class="footer-r">
            <table style="background: #dfdfdf; margin-top: -40px;">
                @if($presupuesto->exportacion)
                    <tr>
                        <td><strong>Flete:</strong></td>
                        <td style="width: 28mm; text-align: right">{{$presupuesto->moneda}} {{number_format($presupuesto->flete,2)}}</td>
                    </tr>
                    <tr>
                        <td><strong>Seguro:</strong></td>
                        <td style="width: 28mm; text-align: right">{{$presupuesto->moneda}} {{number_format($presupuesto->seguro,2)}}</td>
                    </tr>
                @else
                    <tr>
                        <td><strong>Subtotal:</strong></td>
                        <td style="width: 28mm; text-align: right">{{$presupuesto->moneda}} {{number_format($presupuesto->presupuesto/1.18,2)}}</td>
                    </tr>
                    <tr>
                        <td><strong>Total IGV 18%:</strong></td>
                        <td style="width: 28mm; text-align: right">{{$presupuesto->moneda}} {{number_format($presupuesto->presupuesto-($presupuesto->presupuesto/1.18),2)}}</td>
                    </tr>
                @endif
                <tr>
                    <td><strong>Importe total:</strong></td>
                    <td style="width: 28mm; text-align: right">{{$presupuesto->moneda}} {{number_format($presupuesto->presupuesto,2)}}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
<style>

    h3{
        font-size: 14pt;
        margin: 0;
        font-weight: lighter;
        margin-top: 3px;
    }
    h3 span{
        font-weight: bold;
    }
    p,td{
        font-size: 8pt;
    }

    .div-table-header{
        width: 190mm; background: #2831ff; padding: 2mm 5mm; text-align: center; border-radius: 15px 15px 0 0; margin-top: 5mm; height: 5mm
    }

    .line-bottom{
        border-bottom: 1px solid #CCC;
        padding: 20px;
    }

    table{
        margin: 0;
        padding: 0;
    }
    .table-header td{
        margin: 0;
        height: 10mm;
    }

    .impuesto{
        width: 60mm;
        position: absolute;
        right: 0;
        top: 0;
    }

    .header{
        position: relative;
        width: 190mm;
        height: 25mm;
        margin-bottom: 4mm;
    }


    .header .info-emisor{
        width: 90mm;
        padding: 25px 20px;
    }
    .header .info-emisor .logo{
        width: 40mm;
    }
    .header .info-emisor .logo img{
        width: 70mm;
        text-align: center;
        margin-left: -5mm;
    }
    .header .info-emisor .texto{
        width: 95mm;
        right: 0;
        top:25mm;
    }

    .body{
        position: relative;
        width: 200mm;
        border-radius: 0 0 15px 15px;
        background: #dfdfdf
    }

    .body .items {
        width: 200mm;
        position: relative;
        padding: 0 32px 20px 32px;
        margin-top: -25px;
    }
    .footer{
        width: 220mm;
        height: 20mm;
        margin-top: 5mm;
        margin-bottom: 10mm;
    }
    .footer .footer-l{
        width: 60%;
        padding: 0 5mm 0 5mm;
    }
    .footer .footer-r{
        width: 30%;
    }
    .footer-r td{
        padding: 2mm 3mm;
    }


</style>