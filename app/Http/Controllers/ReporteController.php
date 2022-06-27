<?php

namespace sysfact\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use sysfact\Caja;
use sysfact\Emisor;
use sysfact\Exports\CajaExport;
use sysfact\Exports\GastosDiariosExport;
use sysfact\Exports\GastosMensualExport;
use sysfact\Exports\MasVendidosExport;
use sysfact\Exports\ReporteComprobantes;
use sysfact\Exports\StockBajoExport;
use sysfact\Exports\VentasDiariasExport;
use sysfact\Exports\VentasMensualExport;
use sysfact\Exports\VentasResumenExport;
use sysfact\Gastos;
use sysfact\Http\Controllers\Helpers\PdfHelper;
use sysfact\Producto;
use sysfact\Venta;

class ReporteController extends Controller
{

	public function __construct()
	{
		$this->middleware('auth');
	}

    public function reporte_ventas_data($desde, $hasta, $filtro, $buscar, $esExportable){
        try {

            $ventas = null;
            $suma_soles=0;
            $suma_dolares=0;

            $filtros = ['desde' => $desde, 'hasta' => $hasta, 'filtro'=>$filtro,'buscar'=>$buscar];

            if($esExportable == 'true'){
                switch ($filtro) {
                    case 'fecha':
                        $ventas = Venta::whereBetween('fecha', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
                            ->where('eliminado', '=', 0)
                            ->whereHas('facturacion', function($query) {
                                $query
                                    ->where(function ($query) {
                                        $query->where('codigo_tipo_documento',01)
                                            ->orWhere('codigo_tipo_documento',03)
                                            ->orWhere('codigo_tipo_documento',30);
                                        })
                                    ->where('estado','ACEPTADO')
                                    ->orWhere('estado','-');
                            })
                            ->orderby('idventa', 'desc')
                            ->get();
                        break;
                    case 'documento':
                    case 'moneda':
                        switch ($filtro) {
                            case 'documento':
                                switch ($buscar) {
                                    case 'factura':
                                        $buscar = '01';
                                        break;
                                    case 'boleta':
                                        $buscar = '03';
                                        break;
                                    case 'recibo':
                                        $buscar = '30';
                                        break;
                                }
                                $filtro = 'codigo_tipo_documento';
                                break;
                            case 'moneda':
                                $filtro = 'codigo_moneda';
                                break;
                        }
                        $ventas = Venta::whereBetween('fecha', [$desde. ' 00:00:00', $hasta. ' 23:59:59'])
                        ->where('eliminado', '=', 0)
                        ->orderby('idventa', 'desc')
                        ->whereHas('facturacion', function ($query) use ($filtro, $buscar) {
                            $query
                                ->where($filtro, $buscar)
                                ->where(function ($query) {
                                    $query->where(function ($query){
                                        $query->where('estado','ACEPTADO')
                                            ->orWhere('estado','PENDIENTE');
                                    })
                                        ->orWhere('estado','-');
                                });
                        })
                        ->get();
                        break;

                    case 'tipo-de-pago':
                        switch ($buscar) {
                            case 'efectivo':
                                $buscar = '1';
                                break;
                            case 'credito':
                                $buscar = '2';
                                break;
                            case 'tarjeta':
                                $buscar = '3';
                                break;
                            case 'otros':
                                $buscar = '4';
                        }
                        $filtro = 'tipo_pago';
                        $ventas = Venta::whereBetween('fecha', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
                            ->where('eliminado', '=', 0)
                            ->where($filtro, $buscar)
                            ->whereHas('facturacion', function($query) {
                                $query->where(function ($query) {
                                    $query->where('codigo_tipo_documento',01)
                                        ->orWhere('codigo_tipo_documento',03)
                                        ->orWhere('codigo_tipo_documento',30);
                                })
                                    ->where(function ($query){
                                        $query->where('estado','ACEPTADO')
                                            ->orWhere('estado','PENDIENTE');
                                    })
                                    ->orWhere('estado','-');
                            })
                            ->orderby('idventa', 'desc')
                            ->get();
                        break;
                    case 'cliente':
                        $filtro = 'nombre';
                        $ventas = Venta::whereBetween('fecha', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
                            ->where('eliminado', '=', 0)
                            ->orderby('idventa', 'desc')
                            ->whereHas('persona', function ($query) use ($filtro, $buscar) {
                                $query->where($filtro, 'LIKE', '%' . $buscar . '%');
                            })
                            ->whereHas('facturacion', function($query) {
                                $query->where(function ($query) {
                                    $query->where('codigo_tipo_documento',01)
                                        ->orWhere('codigo_tipo_documento',03)
                                        ->orWhere('codigo_tipo_documento',30);
                                    })
                                    ->where(function ($query){
                                        $query->where('estado','ACEPTADO')
                                            ->orWhere('estado','PENDIENTE');
                                    })
                                    ->orWhere('estado','-');

                            })
                            ->get();
                        break;
                }
            }
            else{
                switch ($filtro) {
                    case 'fecha':
                        $ventas = Venta::whereBetween('fecha', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
                            ->where('eliminado', '=', 0)
                            ->whereHas('facturacion', function($query) {
                                $query
                                    ->where(function ($query) {
                                        $query->where('codigo_tipo_documento',01)
                                            ->orWhere('codigo_tipo_documento',03)
                                            ->orWhere('codigo_tipo_documento',30);
                                    })
                                    ->where('estado','ACEPTADO')
                                    ->orWhere('estado','-');
                            })
                            ->orderby('idventa', 'desc')
                            ->paginate(30);
                        break;
                    case 'documento':
                    case 'moneda':
                        switch ($filtro) {
                            case 'documento':
                                switch ($buscar) {
                                    case 'factura':
                                        $buscar = '01';
                                        break;
                                    case 'boleta':
                                        $buscar = '03';
                                        break;
                                    case 'recibo':
                                        $buscar = '30';
                                        break;
                                }
                                $filtro = 'codigo_tipo_documento';
                                break;
                            case 'moneda':
                                $filtro = 'codigo_moneda';
                                break;
                        }
                        $ventas = Venta::whereBetween('fecha', [$desde. ' 00:00:00', $hasta. ' 23:59:59'])
                            ->where('eliminado', '=', 0)
                            ->orderby('idventa', 'desc')
                            ->whereHas('facturacion', function ($query) use ($filtro, $buscar) {
                                $query
                                    ->where($filtro, $buscar)
                                    ->where(function ($query) {
                                        $query->where(function ($query){
                                            $query->where('estado','ACEPTADO')
                                                ->orWhere('estado','PENDIENTE');
                                        })
                                            ->orWhere('estado','-');
                                    });
                            })
                            ->paginate(30);
                        break;

                    case 'tipo-de-pago':
                        switch ($buscar) {
                            case 'efectivo':
                                $buscar = '1';
                                break;
                            case 'credito':
                                $buscar = '2';
                                break;
                            case 'tarjeta':
                                $buscar = '3';
                                break;
                            case 'otros':
                                $buscar = '4';
                        }
                        $filtro = 'tipo_pago';
                        $ventas = Venta::whereBetween('fecha', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
                            ->where('eliminado', '=', 0)
                            ->where($filtro, $buscar)
                            ->whereHas('facturacion', function($query) {
                                $query->where(function ($query) {
                                    $query->where('codigo_tipo_documento',01)
                                        ->orWhere('codigo_tipo_documento',03)
                                        ->orWhere('codigo_tipo_documento',30);
                                })
                                    ->where(function ($query){
                                        $query->where('estado','ACEPTADO')
                                            ->orWhere('estado','PENDIENTE');
                                    })
                                    ->orWhere('estado','-');
                            })
                            ->orderby('idventa', 'desc')
                            ->paginate(30);
                        break;
                    case 'cliente':
                        $filtro = 'nombre';
                        $ventas = Venta::where('eliminado', '=', 0)
                            ->orderby('idventa', 'desc')
                            ->whereHas('persona', function ($query) use ($filtro, $buscar) {
                                $query->where($filtro, 'LIKE', '%' . $param_1 . '%');
                            })
                            ->whereHas('facturacion', function($query) {
                                $query->where(function ($query) {
                                    $query->where('codigo_tipo_documento',01)
                                        ->orWhere('codigo_tipo_documento',03)
                                        ->orWhere('codigo_tipo_documento',30);
                                })
                                    ->where(function ($query){
                                        $query->where('estado','ACEPTADO')
                                            ->orWhere('estado','PENDIENTE');
                                    })
                                    ->orWhere('estado','-');

                            })
                            ->paginate(30);
                        break;
                }
                $ventas->appends($_GET)->links();
            }

            foreach ($ventas as $item) {
                $item->facturacion;
                $item->cliente->persona;
                $emisor = new Emisor();
                $item->nombre_fichero = $emisor->ruc . '-' . $item->facturacion['codigo_tipo_documento'] . '-' . $item->facturacion['serie'] . '-' . $item->facturacion['correlativo'];

                if($item->facturacion->codigo_moneda=='PEN'){
                    $suma_soles += $item->total_venta;
                } else{
                    $suma_dolares += $item->total_venta;
                }

                switch ($item->tipo_pago) {
                    case 1:
                        $item->tipo_pago = 'EFECTIVO';
                        break;
                    case 2:
                        $item->tipo_pago = 'CRÉDITO';
                        break;
                    case 3:
                        $item->tipo_pago = 'TARJETA';
                        break;
                    default:
                        $item->tipo_pago = 'OTROS';
                }

            }

            $ventas->total_soles=$suma_soles==0?'0.00':$suma_soles;
            $ventas->total_usd=$suma_dolares==0?'0.00':$suma_dolares;

            return ['ventas'=>$ventas,'filtros'=>$filtros,'usuario'=>auth()->user()->persona];
        } catch (\Exception $e){
            return $e;
        }
    }

    public function reporte_ventas(Request $request, $desde=null,$hasta=null){

        $esExportable = $request->get('export','false');
        $filtro = $request->filtro;
        $buscar = $request->buscar;

        if(!$filtro){
            $filtro='fecha';
            $desde=date('Y-m-d');
            $hasta=date('Y-m-d');
        }

        $ventas=$this->reporte_ventas_data($desde,$hasta,$filtro,$buscar,$esExportable);

        if($esExportable == 'true'){
            return Excel::download(new VentasResumenExport($ventas['ventas']), 'reporte_resumen_ventas.xlsx');
        } else {
            return view('reportes.ventas',$ventas);
        }

    }

    public function reporte_ventas_badge(Request $request, $desde=null,$hasta=null){
        $filtro = $request->filtro;
        $buscar = $request->buscar;

        if(!$filtro){
            $filtro='fecha';
            $desde=date('Y-m-d');
            $hasta=date('Y-m-d');
        }
        $badge_data=$this->reporte_ventas_data($desde,$hasta,$filtro,$buscar,true);

        $ventas = $badge_data['ventas'];

        $fecha_anterior=null;

        $totales_soles=[
            'fecha'=>0,
            'ventas_brutas'=>0,
            'ventas_netas'=>0,
            'costos'=>0,
            'utilidad'=>0,
            'impuestos'=>0,
            'tipo_cambio'=>0
        ];
        $totales_dolares=[
            'fecha'=>0,
            'ventas_brutas'=>0,
            'ventas_netas'=>0,
            'costos'=>0,
            'utilidad'=>0,
            'impuestos'=>0,
            'tipo_cambio'=>0
        ];

        foreach ($ventas as $item){

            //costos
            $inventario = $item->inventario;
            $costo = 0;
            $tc = cache('opciones')['tipo_cambio_compra'];

            foreach ($inventario as $inv){
                if($inv->moneda == 'USD'){
                    $costo += $inv->costo * $inv->tipo_cambio * ($inv->cantidad * -1);
                } else{
                    $costo += $inv->costo * ($inv->cantidad * -1);
                }
            }

            //impuestos
            $factura = $item->facturacion;
            $igv = 0;

            if($factura->codigo_tipo_documento == 01 || $factura->codigo_tipo_documento == 03){
                if($factura->codigo_moneda == 'USD'){
                    $igv = $factura->igv * $tc;
                } else{
                    $igv = $factura->igv;
                }
            }

            //ventas
            $fecha_venta=date("d-m-Y",strtotime($item->fecha));
            if($item->facturacion->codigo_moneda=='PEN'){

                $totales_soles['fecha']=$fecha_venta;
                $totales_soles['ventas_brutas'] += $item->total_venta;
                $totales_soles['costos'] += $costo;
                $totales_soles['impuestos'] += $igv;
                $totales_soles['ventas_netas'] = $totales_soles['ventas_brutas'] - $totales_soles['impuestos'];
                $totales_soles['utilidad'] =  $totales_soles['ventas_netas'] - $totales_soles['costos'];

            } else{

                $totales_dolares['fecha']=$fecha_venta;
                $totales_dolares['ventas_brutas'] += $item->total_venta;
                $totales_dolares['costos'] += $costo;
                $totales_dolares['impuestos'] += $igv;
                $totales_dolares['ventas_netas'] = $totales_dolares['ventas_brutas'] * $tc - $totales_dolares['impuestos'];
                $totales_dolares['utilidad'] =  $totales_dolares['ventas_netas'] - $totales_dolares['costos'];

            }
        }

        if($totales_soles['ventas_brutas']<=0){
            $totales_soles = null;
        }

        if($totales_dolares['ventas_brutas']<=0){
            $totales_dolares = null;
        }

        return [$totales_soles,$totales_dolares];

    }

    public function reporte_ventas_diario_data($mes, $moneda, $tipo_cambio){

        $ventas=Venta::whereBetween('fecha',[$mes.'-01 00:00:00',$mes.'-31 23:59:59'])
            ->where('eliminado', '=', 0)
            ->orderby('fecha', 'desc')
            ->whereHas('facturacion', function ($query) use ($moneda) {
                $query
                    ->where('codigo_moneda', $moneda)
                    ->where(function ($query){
                        $query
                            //COMPROBANTE ACEPTADO
                            ->where(function ($query) {
                                $query->where('codigo_tipo_documento',01)
                                    ->orWhere('codigo_tipo_documento',03)
                                    ->orWhere('codigo_tipo_documento',30);
                            })
                            ->where('estado','ACEPTADO')
                            //O UN RECIBO
                            ->orWhere('estado','-');
                    });

            })
            ->orderBy('fecha', 'desc')->get();

        $ventas_brutas=0;
        $costos = 0;
        $impuestos = 0;
        $fecha_anterior=null;
        $totales_del_dia=[];

        foreach ($ventas as $item){

            //costos
            $inventario = $item->inventario;
            $costo = 0;
            $tc = $tipo_cambio=='fecha-actual'?cache('opciones')['tipo_cambio_compra']:$item->tipo_cambio;

            foreach ($inventario as $inv){
                if($inv->moneda == 'USD'){
                    $costo += $inv->costo * $inv->tipo_cambio * ($inv->cantidad * -1);
                } else{
                    $costo += $inv->costo * ($inv->cantidad * -1);
                }
            }

            //impuestos
            $factura = $item->facturacion;
            $igv = 0;

            if($factura->codigo_tipo_documento == 01 || $factura->codigo_tipo_documento == 03){
                if($factura->codigo_moneda == 'USD'){
                    $igv = $factura->igv * $tc;
                } else{
                    $igv = $factura->igv;
                }
            }

            //ventas
            $fecha_venta=date("d-m-Y",strtotime($item->fecha));

            if($fecha_venta!=$fecha_anterior){
                $ventas_brutas = $item->total_venta;
                $costos = $costo;
                $impuestos = $igv;

                if($item->facturacion->codigo_moneda=='PEN'){
                    $ventas_netas = $ventas_brutas - $impuestos;
                } else{
                    $ventas_netas = $ventas_brutas * $tc - $impuestos;
                }

                $utilidad = $ventas_netas - $costos;

                $totales_del_dia[]=[
                    'fecha'=>$fecha_venta,
                    'ventas_brutas'=>$ventas_brutas,
                    'ventas_netas'=>$ventas_netas,
                    'costos'=>$costos,
                    'utilidad'=>$utilidad,
                    'impuestos'=>$impuestos,
                    'tipo_cambio'=>$tc
                ];
                $fecha_anterior=date("d-m-Y",strtotime($item->fecha));

            } else{
                $ventas_brutas += $item->total_venta;
                $costos += $costo;
                $impuestos += $igv;

                if($item->facturacion->codigo_moneda=='PEN'){
                    $ventas_netas = $ventas_brutas - $impuestos;
                } else{
                    $ventas_netas = $ventas_brutas * $tc - $impuestos;
                }

                $utilidad = $ventas_netas - $costos;

                $totales_del_dia[count($totales_del_dia)-1]['fecha']=$fecha_venta;
                $totales_del_dia[count($totales_del_dia)-1]['ventas_brutas']=$ventas_brutas;
                $totales_del_dia[count($totales_del_dia)-1]['costos']=$costos;
                $totales_del_dia[count($totales_del_dia)-1]['utilidad']=$utilidad;
                $totales_del_dia[count($totales_del_dia)-1]['impuestos']=$impuestos;
                $totales_del_dia[count($totales_del_dia)-1]['ventas_netas']=$ventas_netas;

            }

        }

        return $totales_del_dia;
    }

    public function reporte_ventas_diario(Request $request, $mes=null){

        $esExportable = $request->get('export','false');
        $moneda = $request->moneda??'PEN';
        $tipo_cambio = $request->tc??'fecha-actual';

        if(!$mes){
            $mes=date('Y-m');
        }

        $ventas=$this->reporte_ventas_diario_data($mes, $moneda, $tipo_cambio);

        if($esExportable == 'true'){
            return Excel::download(new VentasDiariasExport($this->reporte_ventas_diario_data($mes, $moneda, $tipo_cambio),$moneda), 'ventas_diario.xlsx');
        } else {
            return view('reportes.ventas_diario',
                [
                    'usuario'=>auth()->user()->persona,
                    'ventas'=>$ventas,
                    'moneda'=>$moneda,
                    'mes'=>$mes,
                    'usar_tipo_cambio'=>$tipo_cambio
                ]);
        }

    }

    public function reporte_ventas_mensual_data($anio, $moneda, $tipo_cambio){

        $ventas=Venta::whereBetween('fecha',[$anio.'-01-01 00:00:00',$anio.'-12-31 23:59:59'])
            ->where('eliminado', '=', 0)
            ->orderby('fecha', 'desc')
            ->whereHas('facturacion', function ($query) use ($moneda) {
                $query
                    ->where('codigo_moneda', $moneda)
                    ->where(function ($query){
                        $query
                            //COMPROBANTE ACEPTADO
                            ->where(function ($query) {
                                $query->where('codigo_tipo_documento',01)
                                    ->orWhere('codigo_tipo_documento',03)
                                    ->orWhere('codigo_tipo_documento',30);
                            })
                            ->where('estado','ACEPTADO')
                            //O UN RECIBO
                            ->orWhere('estado','-');
                    });

            })
            ->orderBy('fecha', 'desc')->get();

        $ventas_brutas=0;
        $costos = 0;
        $impuestos = 0;
        $fecha_anterior=null;
        $totales_del_mes=[];

        foreach ($ventas as $item){

            //costos
            $inventario = $item->inventario;
            $costo = 0;
            $tc = $tipo_cambio=='fecha-actual'?cache('opciones')['tipo_cambio_compra']:$item->tipo_cambio;

            foreach ($inventario as $inv){
                if($inv->moneda == 'USD'){
                    $costo += $inv->costo * $inv->tipo_cambio * ($inv->cantidad * -1);
                } else{
                    $costo += $inv->costo * ($inv->cantidad * -1);
                }
            }

            //impuestos
            $factura = $item->facturacion;
            $igv = 0;

            if($factura->codigo_tipo_documento == 01 || $factura->codigo_tipo_documento == 03){
                if($factura->codigo_moneda == 'USD'){
                    $igv = $factura->igv * $tc;
                } else{
                    $igv = $factura->igv;
                }
            }

            //ventas
            $fecha_venta=date("M Y",strtotime($item->fecha));

            if($fecha_venta!=$fecha_anterior){
                $ventas_brutas = $item->total_venta;
                $costos = $costo;
                $impuestos = $igv;

                if($item->facturacion->codigo_moneda=='PEN'){
                    $ventas_netas = $ventas_brutas - $impuestos;
                } else{
                    $ventas_netas = $ventas_brutas * $tc - $impuestos;
                }

                $utilidad = $ventas_netas - $costos;

                $totales_del_mes[]=[
                    'fecha'=>$fecha_venta,
                    'ventas_brutas'=>$ventas_brutas,
                    'ventas_netas'=>$ventas_netas,
                    'costos'=>$costos,
                    'utilidad'=>$utilidad,
                    'impuestos'=>$impuestos,
                ];
                $fecha_anterior=date("M Y",strtotime($item->fecha));

            } else{
                $ventas_brutas += $item->total_venta;
                $costos += $costo;
                $impuestos += $igv;

                if($item->facturacion->codigo_moneda=='PEN'){
                    $ventas_netas = $ventas_brutas - $impuestos;
                } else{
                    $ventas_netas = $ventas_brutas * $tc - $impuestos;
                }

                $utilidad = $ventas_netas - $costos;

                $totales_del_mes[count($totales_del_mes)-1]['fecha']=$fecha_venta;
                $totales_del_mes[count($totales_del_mes)-1]['ventas_brutas']=$ventas_brutas;
                $totales_del_mes[count($totales_del_mes)-1]['costos']=$costos;
                $totales_del_mes[count($totales_del_mes)-1]['utilidad']=$utilidad;
                $totales_del_mes[count($totales_del_mes)-1]['impuestos']=$impuestos;
                $totales_del_mes[count($totales_del_mes)-1]['ventas_netas']=$ventas_netas;

            }

        }

        return $totales_del_mes;
    }

    public function reporte_ventas_mensual(Request $request, $anio=null){

        $esExportable = $request->get('export','false');
        $moneda = $request->moneda??'PEN';
        $tipo_cambio = $request->tc??'fecha-actual';

        if(!$anio){
            $anio=date('Y');
        }

        $ventas=$this->reporte_ventas_mensual_data($anio, $moneda, $tipo_cambio);

        if($esExportable == 'true'){
            return Excel::download(new VentasMensualExport($this->reporte_ventas_mensual_data($anio, $moneda, $tipo_cambio),$moneda), 'ventas_mensual.xlsx');
        } else {
            return view('reportes.ventas_mensual',
                [
                    'usuario'=>auth()->user()->persona,
                    'ventas'=>$ventas,
                    'moneda'=>$moneda,
                    'anio'=>$anio,
                    'usar_tipo_cambio'=>$tipo_cambio
                ]);
        }
    }

    //funciones para reporte de gastos
    public function reporte_gastos_data($desde, $hasta, $filtro, $buscar, $esExportable){
        try {

            $gastos = null;
            $filtros = ['desde' => $desde, 'hasta' => $hasta, 'filtro'=>$filtro,'buscar'=>$buscar];


            if($esExportable == 'true'){
                switch ($filtro) {
                    case 'fecha':
                        $gastos = Gastos::whereBetween('fecha', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
                            ->where('eliminado', 0)
                            ->orderby('idgasto', 'desc')
                            ->paginate(30);
                        break;
                    case 'proveedor':
                        $filtro = 'nombre';
                        $gastos = Gastos::whereBetween('fecha', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
                            ->where('eliminado', 0)
                            ->orderby('idgasto', 'desc')
                            ->whereHas('persona', function ($query) use ($filtro, $buscar) {
                                $query->where($filtro, 'LIKE', '%' . $buscar . '%');
                            })
                            ->paginate(30);
                        break;
                }
            } else{
                switch ($filtro) {
                    case 'fecha':
                        $gastos = Gastos::whereBetween('fecha', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
                            ->orderby('idgasto', 'desc')
                            ->paginate(30);
                        break;
                    case 'proveedor':
                        $filtro = 'nombre';
                        $gastos = Gastos::whereBetween('fecha', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
                            ->orderby('idgasto', 'desc')
                            ->whereHas('persona', function ($query) use ($filtro, $buscar) {
                                $query->where($filtro, 'LIKE', '%' . $buscar . '%');
                            })
                            ->paginate(30);
                        break;
                }
            }



            return [
                'gastos'=>$gastos,
                'filtros'=>$filtros,
                'usuario'=>auth()->user()->persona
            ];

        } catch (\Exception $e){
            return $e->getMessage();
        }
    }

    public function reporte_gastos(Request $request, $desde=null,$hasta=null){

        $esExportable = $request->get('export','false');
        $filtro = $request->filtro;
        $buscar = $request->buscar;

        if(!$filtro){
            $filtro='fecha';
            $desde=date('Y-m-d');
            $hasta=date('Y-m-d');
        }

        $gastos=$this->reporte_gastos_data($desde, $hasta, $filtro, $buscar, $esExportable);

        if($esExportable == 'true'){
            return Excel::download(new ReporteComprobantes($gastos['comprobantes']), 'reporte_gastos.xlsx');
        } else {
            return view('reportes.gastos',$gastos);
        }

    }

    public function reporte_gastos_diario_data($mes){

        $suma=0;
        $fecha_anterior=null;
        $suma_por_dia=null;

        $gastos=Gastos::whereBetween('fecha',[$mes.'-01 00:00:00',$mes.'-31 23:59:59'])
            ->orderBy('fecha', 'desc')->get();

        foreach ($gastos as $item){

            $fecha_venta=date("d-m-Y",strtotime($item->fecha));

            if($fecha_venta==$fecha_anterior){
                $suma += $item->monto;
                $suma_por_dia[count($suma_por_dia)-1]['total_dia']=$suma;
            } else{
                $suma=0;
                $suma += $item->monto;
                $suma_por_dia[]=['fecha'=>$fecha_venta,'total_dia'=>$suma];
                $fecha_anterior=date("d-m-Y",strtotime($item->fecha));
            }

        }

        return $suma_por_dia;
    }

    public function reporte_gastos_diario($mes){

        $suma_por_dia=$this->reporte_gastos_diario_data($mes);

        return view('reportes.gastos_diario',
            [
                'usuario'=>auth()->user()->persona,
                'gastos'=>$suma_por_dia,
                'mes'=>$mes
            ]);
    }

    public function reporte_gastos_diario_export($mes){
        return Excel::download(new GastosDiariosExport($this->reporte_gastos_diario_data($mes)), 'gastos_diario.xlsx');
    }

    public function reporte_gastos_mensual_data($anio){

        $suma=0;
        $fecha_anterior=null;
        $suma_por_mes=null;

        $gastos=Gastos::whereBetween('fecha',[$anio.'-01-01 00:00:00',$anio.'-12-31 23:59:59'])
            ->orderBy('fecha', 'desc')->get();

        foreach ($gastos as $item){

            $fecha_venta=date("M Y",strtotime($item->fecha));

            if($fecha_venta==$fecha_anterior){
                $suma += $item->monto;
                $suma_por_mes[count($suma_por_mes)-1]['total_mes']=$suma;
            } else{
                $suma=0;
                $suma += $item->monto;
                $suma_por_mes[]=['fecha'=>$fecha_venta,'total_mes'=>$suma];
                $fecha_anterior=date("M Y",strtotime($item->fecha));
            }

        }

        return $suma_por_mes;
    }

    public function reporte_gastos_mensual($anio){

        $suma_por_dia=$this->reporte_gastos_mensual_data($anio);

        return view('reportes.gastos_mensual',
            [
                'usuario'=>auth()->user()->persona,
                'gastos'=>$suma_por_dia,
                'anio'=>$anio
            ]);
    }

    public function reporte_gastos_mensual_export($mes){
        return Excel::download(new GastosMensualExport($this->reporte_gastos_mensual_data($mes)), 'gastos_mensual.xlsx');
    }


    //funciones para reporte de productos

    public function reporte_stock_bajo(Request $request){

        $productos=Producto::with('inventario')->where('eliminado',0)
            ->where('tipo_producto',1)
            ->where('idproducto','!=',-1)
            ->get();

        $seleccionados=[];

        foreach ($productos as $key=>$producto) {
            $item = $producto->inventario->first();
            $saldo = $item->saldo??0;
            if($producto->stock_bajo>=$saldo){
                $producto->saldo = $saldo;
                $seleccionados[]=$productos->pull($key);
            }
        }

        usort($seleccionados, function($a, $b) {
            return $b['saldo'] <=> $a['saldo'];
        });

        $esExportable = $request->get('export','false');

        if($esExportable == 'true'){
            return Excel::download(new StockBajoExport($seleccionados), 'stock_bajo.xlsx');
        } else{
            $page = $request->get('page', 1);
            $perPage = 30;
            $offset = ($page * $perPage) - $perPage;

            $collection = new LengthAwarePaginator(
                array_slice($seleccionados, $offset, $perPage, true),
                count($seleccionados),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            return view('reportes.stock_bajo',
                [
                    'usuario'=>auth()->user()->persona,
                    'productos'=>$collection
                ]);
        }


    }

    public function mas_vendidos(Request $request){
        $productos=$ventas = DB::table('ventas')
            ->join('ventas_detalle', 'ventas_detalle.idventa', '=', 'ventas.idventa')
            ->join('productos', 'productos.idproducto', '=', 'ventas_detalle.idproducto')
            ->selectRaw('sum(ventas_detalle.cantidad) as vendidos,ventas_detalle.idproducto, productos.nombre, productos.cod_producto, productos.imagen, productos.precio, productos.presentacion')
            ->where('ventas.eliminado', 0)
            ->where('productos.eliminado', 0)
            ->groupBy('ventas_detalle.idproducto')
            ->orderby('vendidos','desc')
            ->limit(20)
            ->get();

        $esExportable = $request->get('export','false');

        if($esExportable == 'true'){
            return Excel::download(new MasVendidosExport($productos), 'mas_vendidos.xlsx');
        } else {
            return view('reportes.mas_vendidos',
                [
                    'usuario'=>auth()->user()->persona,
                    'productos'=>$productos
                ]);
        }

    }

    //funciones para reporte de comprobantes

    public function reporte_comprobantes_data($desde, $hasta, $filtro, $buscar, $esExportable){
        try {

            $ventas = null;
            $filtros = ['desde' => $desde, 'hasta' => $hasta, 'filtro'=>$filtro,'buscar'=>$buscar];


            if($esExportable == 'true'){
                switch ($filtro) {
                    case 'fecha':
                        $ventas = Venta::whereBetween('fecha', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
                            ->where('eliminado', '=', 0)
                            ->whereHas('facturacion', function($query) {
                                $query->where('codigo_tipo_documento',01)
                                    ->orWhere('codigo_tipo_documento',03)
                                    ->orWhere('codigo_tipo_documento',07)
                                    ->orWhere('codigo_tipo_documento','08');
                            })
                            ->orderby('fecha', 'desc')
                            ->get();
                        break;
                    case 'documento':
                    case 'estado':
                        switch ($filtro) {
                            case 'documento':
                                switch ($buscar) {
                                    case 'factura':
                                        $buscar = '01';
                                        break;
                                    case 'boleta':
                                        $buscar = '03';
                                        break;
                                    case 'nota-de-credito':
                                        $buscar = '07';
                                        break;
                                    case 'nota-de-debito':
                                        $buscar = '08';
                                        break;
                                }
                                $filtro = 'codigo_tipo_documento';
                                break;
                        }

                        $ventas = Venta::whereBetween('fecha', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
                        ->where('eliminado', '=', 0)
                        ->orderby('fecha', 'desc')
                        ->whereHas('facturacion', function ($query) use ($filtro, $buscar) {
                            $query->where($filtro, $buscar);
                        })
                        ->get();
                        break;
                }
            } else{
                switch ($filtro) {
                    case 'fecha':
                        $ventas = Venta::whereBetween('fecha', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
                            ->where('eliminado', '=', 0)
                            ->whereHas('facturacion', function($query) {
                                $query->where('codigo_tipo_documento',01)
                                    ->orWhere('codigo_tipo_documento',03)
                                    ->orWhere('codigo_tipo_documento',07)
                                    ->orWhere('codigo_tipo_documento','08');
                            })
                            ->orderby('fecha', 'desc')
                            ->paginate(30);
                        break;
                    case 'documento':
                    case 'estado':
                        switch ($filtro) {
                            case 'documento':
                                switch ($buscar) {
                                    case 'factura':
                                        $buscar = '01';
                                        break;
                                    case 'boleta':
                                        $buscar = '03';
                                        break;
                                    case 'nota-de-credito':
                                        $buscar = '07';
                                        break;
                                    case 'nota-de-debito':
                                        $buscar = '08';
                                        break;
                                }
                                $filtro = 'codigo_tipo_documento';
                                break;
                        }

                        $ventas = Venta::whereBetween('fecha', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
                            ->where('eliminado', '=', 0)
                            ->orderby('fecha', 'desc')
                            ->whereHas('facturacion', function ($query) use ($filtro, $buscar) {
                                $query->where($filtro, $buscar);
                            })
                            ->paginate(30);
                        break;
                }

                $ventas->appends($_GET)->links();
            }

            foreach ($ventas as $item) {
                $item->facturacion;
                $item->cliente->persona;
                $emisor = new Emisor();
                $item->nombre_fichero = $emisor->ruc . '-' . $item->facturacion['codigo_tipo_documento'] . '-' . $item->facturacion['serie'] . '-' . $item->facturacion['correlativo'];

                switch ($item->tipo_pago) {
                    case 1:
                        $item->tipo_pago = 'EFECTIVO';
                        break;
                    case 2:
                        $item->tipo_pago = 'CRÉDITO';
                        break;
                    case 3:
                        $item->tipo_pago = 'TARJETA';
                        break;
                    default:
                        $item->tipo_pago = 'OTROS';
                }

                switch ($item->facturacion->codigo_tipo_documento){
                    case 01:
                        $item->tipo_doc='FACTURA';
                        $item->facturacion->num_doc_relacionado = '-';
                        break;
                    case 03:
                        $item->tipo_doc='BOLETA';
                        $item->facturacion->num_doc_relacionado = '-';
                        break;
                    case 07:
                        $item->tipo_doc='NOTA DE CRÉDITO';
                        break;
                    case '08':
                        $item->tipo_doc='NOTA DE DÉBITO';
                        break;
                    default:
                        $item->tipo_doc='RECIBO';
                }

                switch ($item->facturacion->estado){
                    case 'PENDIENTE':
                        $item->badge_class='badge-warning';
                        break;
                    case 'ACEPTADO':
                        $item->badge_class='badge-success';
                        break;
                    case 'ANULADO':
                        $item->facturacion->estado='ANULADO CON NC';
                        $item->badge_class='badge-dark';
                        break;
                    case 'MODIFICADO':
                        $item->facturacion->estado='MODIFICADO CON ND';
                        $item->badge_class='badge-dark';
                        break;
                    case 'RECHAZADO':
                        $item->badge_class='badge-danger';
                }

            }

            return [
                'comprobantes'=>$ventas,
                'filtros'=>$filtros,
                'usuario'=>auth()->user()->persona
            ];

        } catch (\Exception $e){
            return $e->getMessage();
        }
    }

    public function reporte_comprobantes(Request $request, $desde=null,$hasta=null){

        $esExportable = $request->get('export','false');
        $filtro = $request->filtro;
        $buscar = $request->buscar;

        if(!$filtro){
            $filtro='fecha';
            $desde=date('Y-m-d');
            $hasta=date('Y-m-d');
        }

        $comprobantes=$this->reporte_comprobantes_data($desde, $hasta, $filtro, $buscar, $esExportable);

        if($esExportable == 'true'){
            return Excel::download(new ReporteComprobantes($comprobantes['comprobantes']), 'reporte_comprobantes.xlsx');
        } else {
            return view('reportes.comprobantes',$comprobantes);
        }

    }

    //funciones para reporte de caja

    public function reporte_caja_data($desde, $hasta, $filtro, $buscar, $esExportable){
        try {

            $cajas = null;
            $filtros = ['desde' => $desde, 'hasta' => $hasta, 'filtro'=>$filtro,'buscar'=>$buscar];


            if($esExportable == 'true'){
                $cajas = Caja::whereBetween('fecha_a', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
                    ->orderby('fecha_a', 'desc')
                    ->get();
            } else{
                $cajas = Caja::whereBetween('fecha_a', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
                    ->orderby('fecha_a', 'desc')
                    ->paginate(30);
                $cajas->appends($_GET)->links();
            }

            return [
                'cajas'=>$cajas,
                'filtros'=>$filtros,
                'usuario'=>auth()->user()->persona
            ];

        } catch (\Exception $e){
            return $e->getMessage();
        }
    }

    public function reporte_caja(Request $request, $desde=null,$hasta=null){

        $esExportable = $request->get('export','false');
        $filtro = $request->filtro;
        $buscar = $request->buscar;

        if(!$filtro){
            $filtro='fecha';
            $desde=date('Y-m-d');
            $hasta=date('Y-m-d');
        }

        $cajas=$this->reporte_caja_data($desde, $hasta, $filtro, $buscar, $esExportable);

        if($esExportable == 'true'){
            return Excel::download(new CajaExport($cajas['cajas']), 'reporte_caja.xlsx');
        } else {
            return view('reportes.caja',$cajas);
        }

    }

    public function descargar_archivo($file_or_id){

        if(is_numeric($file_or_id)){
            PdfHelper::generarPdf($file_or_id,false, 'D');
        } else {
            $archivo=explode('.',$file_or_id);
            switch($archivo[1]) {
                case 'xml':
                    $pathtoFile = storage_path().'/app/sunat/xml/' . $file_or_id;
                    return response()->download($pathtoFile);
                    break;
                case 'cdr':
                    $pathtoFile = storage_path().'/app/sunat/cdr/' .$archivo[0].'.xml';
                    if (!file_exists($pathtoFile)) {
                        return redirect('/comprobantes/consulta-cdr')->withErrors(['No se ha obtenido el CDR del comprobante. LLena los datos abajo, dale al botón CONSULTAR CDR y vuelve a descargar desde la página anterior.']);
                    }
                    return response()->download($pathtoFile);
                    break;
                default:
                    return null;
            }
        }

    }

}
