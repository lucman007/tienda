<?php

namespace sysfact\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use sysfact\Exports\ComparacionSire;
use ZipArchive;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Spipu\Html2Pdf\Html2Pdf;
use sysfact\Caja;
use sysfact\Emisor;
use sysfact\Exports\CajaExport;
use sysfact\Exports\GastosDiariosExport;
use sysfact\Exports\GastosMensualExport;
use sysfact\Exports\MasVendidosExport;
use sysfact\Exports\ProductosResumenDiarioExport;
use sysfact\Exports\ReporteComprobantes;
use sysfact\Exports\StockBajoExport;
use sysfact\Exports\VentasDiariasExport;
use sysfact\Exports\VentasEliminadasExport;
use sysfact\Exports\VentasMensualExport;
use sysfact\Exports\VentasResumenExport;
use sysfact\Gastos;
use sysfact\Http\Controllers\Helpers\DataTipoPago;
use sysfact\Http\Controllers\Helpers\MainHelper;
use sysfact\Http\Controllers\Helpers\PdfHelper;
use sysfact\Mail\ReporteResumenVentas;
use sysfact\Opciones;
use sysfact\Producto;
use sysfact\Trabajador;
use sysfact\User;
use sysfact\Venta;

class ReporteController extends Controller
{
    private $hora_inicio;
    private $hora_fin;
    private $hour_find = false;
    private $solo_comprobantes;

    public function __construct()
    {
        $this->middleware('auth');
        $this->solo_comprobantes = false;
        $this->hora_inicio = (json_decode(cache('config')['interfaz'], true)['atencion_inicio'] ?? '00:00') . ':00';
        $this->hora_fin = (json_decode(cache('config')['interfaz'], true)['atencion_fin'] ?? '23:59') . ':59';
    }

    public function getHasta($hasta)
    {
        $fecha = new Carbon($hasta);

        if ($this->hora_fin < $this->hora_inicio) {
            $fecha->addDays(1);
        }

        return $fecha->format('Y-m-d');
    }

    public function func_filter($query)
    {
        if ($this->solo_comprobantes) {
            $query->whereIn('codigo_tipo_documento', [01, 03])
                ->where(function ($query) {
                    $query->where('estado', 'ACEPTADO')
                        ->orWhere('estado', 'PENDIENTE');
                });
        } else {
            $query->whereIn('codigo_tipo_documento', [01, 03, 30])
                ->where(function ($query) {
                    $query->where('estado', 'ACEPTADO')
                        ->orWhere('estado', 'PENDIENTE')
                        ->orWhere('estado', '-');
                });
        }
    }

    public function reporte_ventas_data($desde, $hasta, $filtro, $buscar, $esExportable)
    {
        try {

            $ventas = null;

            $filtros = [
                'desde' => $desde,
                'hasta' => $hasta,
                'filtro' => $filtro,
                'buscar' => $buscar,
                'hdesde' => $this->hora_inicio,
                'hhasta' => $this->hora_fin,
            ];

            switch ($filtro) {
                case 'fecha':
                    $ventas = Venta::whereBetween('fecha', [$desde . ' ' . $this->hora_inicio, $hasta . ' ' . $this->hora_fin])
                        ->where('eliminado', '=', 0)
                        ->whereHas('facturacion', function ($query) {
                            $this->func_filter($query);
                        })
                        ->orderby('fecha', 'desc')
                        ->orderby('idventa', 'desc')
                        ->when($esExportable, function ($query) {
                            return $query->get();
                        }, function ($query) {
                            return $query->paginate(30);
                        });
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
                    $ventas = Venta::whereBetween('fecha', [$desde . ' ' . $this->hora_inicio, $this->getHasta($hasta) . ' ' . $this->hora_fin])
                        ->where('eliminado', '=', 0)
                        ->orderby('fecha', 'desc')
                        ->orderby('idventa', 'desc')
                        ->whereHas('facturacion', function ($query) use ($filtro, $buscar) {
                            $query
                                ->where($filtro, $buscar)
                                ->where(function ($query) {
                                    $this->func_filter($query);
                                });
                        })
                        ->when($esExportable, function ($query) {
                            return $query->get();
                        }, function ($query) {
                            return $query->paginate(30);
                        });
                    break;

                case 'tipo-de-pago':
                    $pago = DataTipoPago::getTipoPago();
                    $find = array_search($buscar, array_column($pago, 'text_val'));
                    $buscar = $pago[$find]['num_val'];

                    $ventas = Venta::whereBetween('fecha', [$desde . ' ' . $this->hora_inicio, $this->getHasta($hasta) . ' ' . $this->hora_fin])
                        ->where('eliminado', 0)
                        ->whereHas('pago', function ($query) use ($buscar) {
                            $query->where('tipo', $buscar);
                        })
                        ->whereHas('facturacion', function ($query) {
                            $this->func_filter($query);
                        })
                        ->orderby('fecha', 'desc')
                        ->orderby('idventa', 'desc')
                        ->when($esExportable, function ($query) {
                            return $query->get();
                        }, function ($query) {
                            return $query->paginate(30);
                        });
                    break;
                case 'cliente':
                    $filtro = 'nombre';
                    $ventas = Venta::whereBetween('fecha', [$desde . ' ' . $this->hora_inicio, $this->getHasta($hasta) . ' ' . $this->hora_fin])
                        ->where('eliminado', '=', 0)
                        ->orderby('fecha', 'desc')
                        ->orderby('idventa', 'desc')
                        ->whereHas('persona', function ($query) use ($filtro, $buscar) {
                            $query->where($filtro, 'LIKE', '%' . $buscar . '%');
                        })
                        ->whereHas('facturacion', function ($query) {
                            $this->func_filter($query);
                        })
                        ->when($esExportable, function ($query) {
                            return $query->get();
                        }, function ($query) {
                            return $query->paginate(30);
                        });
                    break;
                case 'vendedor':
                    $ventas = Venta::whereBetween('fecha', [$desde . ' ' . $this->hora_inicio, $this->getHasta($hasta) . ' ' . $this->hora_fin])
                        ->where('eliminado', 0)
                        ->where('idempleado', $buscar)
                        ->whereHas('facturacion', function ($query) {
                            $this->func_filter($query);
                        })
                        ->orderby('fecha', 'desc')
                        ->orderby('idventa', 'desc')
                        ->when($esExportable, function ($query) {
                            return $query->get();
                        }, function ($query) {
                            return $query->paginate(30);
                        });
                    break;
                case 'cajero':
                    $ventas = Venta::whereBetween('fecha', [$desde . ' ' . $this->hora_inicio, $this->getHasta($hasta) . ' ' . $this->hora_fin])
                        ->where('eliminado', 0)
                        ->where('idcajero', $buscar)
                        ->whereHas('facturacion', function ($query) {
                            $this->func_filter($query);
                        })
                        ->orderby('fecha', 'desc')
                        ->orderby('idventa', 'desc')
                        ->when($esExportable, function ($query) {
                            return $query->get();
                        }, function ($query) {
                            return $query->paginate(30);
                        });
                    break;
            }

            if (!$esExportable) {
                $ventas->appends($_GET)->links();
            }

            foreach ($ventas as $item) {
                $item->facturacion;
                $item->cliente->persona;
                $emisor = new Emisor();
                $item->nombre_fichero = $emisor->ruc . '-' . $item->facturacion['codigo_tipo_documento'] . '-' . $item->facturacion['serie'] . '-' . $item->facturacion['correlativo'];

                $tipo_pago = DataTipoPago::getTipoPago();
                if ($item->tipo_pago == 4 && $filtro == 'tipo-de-pago') {
                    if ($item->facturacion->codigo_moneda == 'PEN') {
                        $suma_soles = 0;
                        foreach ($item->pago as $pago) {
                            $index = array_search($pago->tipo, array_column($tipo_pago, 'num_val'));
                            if ($filtros['buscar'] == $tipo_pago[$index]['text_val']) {
                                $suma_soles += $pago->monto;
                            }
                        }
                        $item->total_venta_aux = $suma_soles;
                    }
                }

                //pago credito
                $suma_cuotas = 0;
                $cuotas = $item->pago;
                foreach ($cuotas as $pago) {
                    if ($pago->estado == 2) {
                        $suma_cuotas += $pago->monto;
                    }
                    if ($suma_cuotas >= $item->total_venta) {
                        if ($item->facturacion->codigo_tipo_documento != '07' || $item->facturacion->codigo_tipo_documento != '08') {
                            $item->estado_credito = 'PAGADO';
                        }
                    }
                }

            }

            return ['ventas' => $ventas, 'filtros' => $filtros, 'usuario' => auth()->user()->persona];
        } catch (\Exception $e) {
            Log::error($e);
            return $e;
        }
    }

    public function reporte_ventas(Request $request, $desde = null, $hasta = null)
    {

        $esExportable = $request->get('export', false);
        $esMail = $request->get('mail', 'false');
        $filtro = $request->filtro;
        $buscar = $request->buscar;

        if (!$filtro) {
            $filtro = 'fecha';
            $desde = date('Y-m-d');
            $hasta = date('Y-m-d');
        }

        $ventas = $this->reporte_ventas_data($desde, $this->getHasta($hasta), $filtro, $buscar, $esExportable);

        if ($esExportable == 'true') {
            $otros_reportes = $this->reporte_ventas_badge($request, $desde, $hasta);
            $tipo_pago = $otros_reportes[2];
            $total_soles = $otros_reportes[0];
            $fecha = ['desde' => $desde, 'hasta' => $hasta];
            if ($esMail == 'true') {
                return [$ventas['ventas'], $tipo_pago, $total_soles, $fecha, $buscar];
            } else {
                return Excel::download(new VentasResumenExport($ventas['ventas'], $tipo_pago, $total_soles, $fecha), 'reporte_resumen_ventas.xlsx');
            }

        } else {
            return view('reportes.ventas', $ventas);
        }

    }

    public function reporte_ventas_badge(Request $request, $desde = null, $hasta = null)
    {
        $filtro = $request->filtro;
        $buscar = $request->buscar;

        if (!$filtro) {
            $filtro = 'fecha';
            $desde = date('Y-m-d');
            $hasta = date('Y-m-d');
        }

        $reporte_ventas_manual = json_decode(cache('config')['interfaz'], true)['reporte_ventas_manual'] ?? false;
        if ($reporte_ventas_manual) {
            $d1 = Carbon::parse($desde);
            $d2 = Carbon::parse($hasta);
            $daysDiff = $d1->diffInDays($d2);
            if ($daysDiff > 35) {
                return -1;
            }

        }

        $badge_data = $this->reporte_ventas_data($desde, $hasta, $filtro, $buscar, true);

        $ventas = $badge_data['ventas'];

        $totales_soles = [
            'fecha' => 0,
            'ventas_brutas' => 0,
            'ventas_netas' => 0,
            'adelantos' => 0,
            'costos' => 0,
            'utilidad' => 0,
            'impuestos' => 0,
            'tipo_cambio' => 0
        ];
        $totales_dolares = [
            'fecha' => 0,
            'ventas_brutas' => 0,
            'ventas_netas' => 0,
            'adelantos' => 0,
            'costos' => 0,
            'utilidad' => 0,
            'impuestos' => 0,
            'tipo_cambio' => 0
        ];

        foreach ($ventas as $item) {

            if ($item->tipo_pago == 4 && $filtro == 'tipo-de-pago') {
                $item->total_venta = $item->total_venta_aux;
            }

            //costos
            $inventario = $item->inventario;
            $costo = 0;
            $tc = cache('opciones')['tipo_cambio_compra'];

            foreach ($inventario as $inv) {
                if ($inv->moneda == 'USD') {
                    $costo += $inv->costo * $inv->tipo_cambio * ($inv->cantidad * -1);
                } else {
                    $costo += $inv->costo * ($inv->cantidad * -1);
                }
            }

            //impuestos
            $factura = $item->facturacion;
            $igv = 0;

            if ($factura->codigo_tipo_documento == 01 || $factura->codigo_tipo_documento == 03) {
                if ($factura->codigo_moneda == 'USD') {
                    $igv = $factura->igv * $tc;
                } else {
                    $igv = $factura->igv;
                }
            }

            //ventas
            $fecha_venta = date("d-m-Y", strtotime($item->fecha));
            if ($item->facturacion->codigo_moneda == 'PEN') {

                if ($item->adelanto <= 0) {
                    $totales_soles['ventas_brutas'] += $item->total_venta;
                }
                $adelantoPago = $item->adelanto;
                $totales_soles['fecha'] = $fecha_venta;
                $totales_soles['costos'] += $costo;
                $totales_soles['impuestos'] += $igv;
                $totales_soles['adelantos'] += $adelantoPago;

                if ($totales_soles['ventas_brutas'] > 0) {
                    $totales_soles['ventas_netas'] = $totales_soles['ventas_brutas'] - $totales_soles['impuestos'] + $totales_soles['adelantos'];
                }

                $totales_soles['utilidad'] = $totales_soles['ventas_netas'] - $totales_soles['costos'];

            } else {

                if ($item->adelanto <= 0) {
                    $totales_dolares['ventas_brutas'] += $item->total_venta;
                }
                $adelantoPago = $item->adelanto * $tc;
                $totales_dolares['fecha'] = $fecha_venta;
                $totales_dolares['costos'] += $costo;
                $totales_dolares['impuestos'] += $igv;
                $totales_dolares['adelantos'] += $adelantoPago;

                if ($totales_dolares['ventas_brutas'] > 0) {
                    $totales_dolares['ventas_netas'] = ($totales_dolares['ventas_brutas'] * $tc) - $totales_dolares['impuestos'] + $totales_dolares['adelantos'];
                }

                $totales_dolares['utilidad'] = $totales_dolares['ventas_netas'] - $totales_dolares['costos'];

            }
        }

        if ($totales_soles['ventas_brutas'] <= 0 && $totales_soles['adelantos'] == 0) {
            $totales_soles = null;
        }

        if ($totales_dolares['ventas_brutas'] <= 0 && $totales_dolares['adelantos'] == 0) {
            $totales_dolares = null;
        }

        $total_por_tipo_pago = $this->obtener_total_tipo_pago($badge_data['ventas'], $filtro, $buscar);

        return [$totales_soles, $totales_dolares, $total_por_tipo_pago];

    }

    public function obtener_total_tipo_pago($ventas, $filtro, $buscar)
    {

        $dataTipoPago = DataTipoPago::getTipoPago();
        $suma = [];
        foreach ($ventas as $venta) {
            foreach ($venta->pago as $pago) {
                $index = array_search($pago->tipo, array_column($dataTipoPago, 'num_val'));
                if ($venta->tipo_pago == 4 && $filtro == 'tipo-de-pago' && $buscar != $dataTipoPago[$index]['text_val']) {
                    $pago->monto = 0;
                }
                if ($venta->facturacion->codigo_moneda == 'PEN') {

                    foreach ($dataTipoPago as $item) {
                        if ($pago->tipo == $item['num_val']) {
                            if (!isset($suma[$item['text_val']])) {
                                $suma[$item['text_val']] = $pago->monto;
                            } else {
                                $suma[$item['text_val']] += $pago->monto;
                            }
                            break;
                        }
                    }

                }
            }
        }

        return $suma;

    }

    public function reporte_ventas_diario_data($mes, $moneda)
    {
        // 1) Rango real del mes
        $firstDay = Carbon::parse("{$mes}-01");
        $start = $firstDay->copy()->setTimeFromTimeString($this->hora_inicio);
        $end = $firstDay->copy()->endOfMonth()->setTimeFromTimeString($this->hora_fin);

        // 2) Subconsulta A: ventas_brutas + impuestos
        $aggVentas = function ($query) use ($start, $end, $moneda) {
            $query->from('ventas as v')
                ->join('facturacion as f', 'f.idventa', '=', 'v.idventa')
                ->whereBetween('v.fecha', [$start, $end])
                ->where('v.eliminado', 0)
                ->where('f.codigo_moneda', $moneda)
                ->where(function ($q) {
                    // aquí invocamos tu filtro original
                    $this->func_filter($q);
                })
                ->selectRaw("
                  DATE(v.fecha) AS fecha_venta,
                  ROUND(MAX(v.tipo_cambio),4) AS tipo_cambio,
                  SUM(
                    CASE 
                      WHEN f.codigo_tipo_documento IN ('01','03','30')
                      THEN v.total_venta ELSE 0 
                    END
                  ) AS ventas_brutas,
                  ROUND(
                    SUM(
                      CASE 
                        WHEN f.codigo_tipo_documento IN ('01','03') 
                        THEN f.igv 
                        ELSE 0 
                      END
                    )
                    *
                    MAX(
                      CASE 
                        WHEN f.codigo_moneda = 'USD' 
                        THEN v.tipo_cambio 
                        ELSE 1 
                      END
                    )
                  , 2) AS impuestos
              ")
                ->groupBy(DB::raw('DATE(v.fecha)'));
        };

        // 3) Subconsulta B: costos

        $aggCostos = function ($query) use ($start, $end, $moneda) {
            $query->from('inventario as i')
                ->join('facturacion as f', 'f.idventa', '=', 'i.idventa')
                ->join('ventas as v',       'v.idventa', '=', 'i.idventa')
                ->where('v.eliminado', 0)
                ->whereBetween('i.fecha', [$start, $end])
                ->where('operacion','LIKE','VENTA N° %')
                ->where('f.codigo_moneda', $moneda)
                ->whereIn('f.estado', ['ACEPTADO','PENDIENTE','-'])
                ->selectRaw("
            DATE(i.fecha) AS fecha_venta,
            SUM(
              CASE
                WHEN i.moneda = 'USD' 
                THEN i.costo * i.tipo_cambio * (i.cantidad)
                ELSE i.costo * (i.cantidad * -1)
              END
            ) AS costos
        ")
                ->groupBy(DB::raw('DATE(i.fecha)'));
        };

        // 4) Unimos las dos subconsultas
        $rows = DB::query()
            ->fromSub($aggVentas, 'a')
            ->leftJoinSub($aggCostos, 'c', function ($join) {
                $join->on('c.fecha_venta', '=', 'a.fecha_venta');
            })
            ->select([
                'a.fecha_venta',
                'a.tipo_cambio',
                'a.ventas_brutas',
                'a.impuestos',
                'c.costos',
            ])
            ->orderBy('a.fecha_venta', 'desc')
            ->get();

        // 5) Mapear formato y calcular neto/utilidad (igual que antes)
        $totales_del_dia = $rows->map(function ($r) use ($moneda){
            if($moneda == 'PEN'){
                $ventas_netas = $r->ventas_brutas - $r->impuestos;
            } else {
                $ventas_netas = $r->ventas_brutas * $r->tipo_cambio - $r->impuestos;
            }

            return [
                'fecha' => Carbon::parse($r->fecha_venta)->format('d-m-Y'),
                'ventas_brutas' => (float)$r->ventas_brutas,
                'impuestos' => (float)$r->impuestos,
                'costos' => (float)$r->costos,
                'ventas_netas' => $ventas_netas,
                'utilidad' => $ventas_netas - $r->costos,
                'tipo_cambio' => $r->tipo_cambio,
            ];
        })->values()->all();

        $totales_badge = [
            'bruto' => array_sum(array_column($totales_del_dia, 'ventas_brutas')),
            'impuesto' => array_sum(array_column($totales_del_dia, 'impuestos')),
            'neto' => array_sum(array_column($totales_del_dia, 'ventas_netas')),
        ];

        return [$totales_del_dia, $totales_badge];
    }

    public function reporte_ventas_diario(Request $request, $mes = null)
    {

        $esExportable = $request->get('export', false);
        $moneda = $request->moneda ?? 'PEN';

        if (!$mes) {
            $mes = date('Y-m');
        }

        $ventas = $this->reporte_ventas_diario_data($mes, $moneda);

        if ($esExportable) {
            return Excel::download(new VentasDiariasExport($ventas, $moneda), 'ventas_diario.xlsx');
        } else {
            return view('reportes.ventas_diario',
                [
                    'usuario' => auth()->user()->persona,
                    'ventas' => $ventas,
                    'moneda' => $moneda,
                    'mes' => $mes,
                    'usar_tipo_cambio' => 'fecha-venta'
                ]);
        }

    }

    private function calcularImpuestos($factura)
    {
        $igv = 0;

        if ($factura->codigo_tipo_documento == 01 || $factura->codigo_tipo_documento == 03) {
            $tc = cache('opciones')['tipo_cambio_compra'];

            if ($factura->codigo_moneda == 'USD') {
                $igv = $factura->igv * $tc;
            } else {
                $igv = $factura->igv;
            }
        }

        return $igv;
    }

    private function calcularVentasNetas($factura, $ventas_brutas, $igv, $tipo_cambio)
    {
        if ($factura->codigo_moneda == 'PEN') {
            return $ventas_brutas - $igv;
        } else {
            return $ventas_brutas * $tipo_cambio - $igv;
        }
    }

    private function calcularUtilidad($ventas_netas, $costos)
    {
        return $ventas_netas - $costos;
    }


    public function reporte_ventas_mensual(Request $request, $anio = null)
    {

        $esExportable = $request->get('export', false);
        $moneda = $request->moneda ?? 'PEN';
        $tipo_cambio = $request->tc ?? 'fecha-actual';

        if (!$anio) {
            $anio = date('Y');
        }

        $totales_del_mes = [];

        $opcion = Opciones::where('nombre_opcion', 'reporte-mensual-' . $anio . '-' . $moneda)->orderby('valor', 'asc')->get();

        if (count($opcion) == 0) {
            $this->inicializarMesesParaReporteMensual($anio, $moneda);
        } else {
            foreach ($opcion as $item) {
                $data = json_decode($item->valor_json, true);
                $data['fecha_actualizacion'] = Carbon::parse($item->fecha)
                    ->locale('es')
                    ->isoFormat('D [de] MMMM [a las] HH:mm');

                $totales_del_mes[] = $data;
            }

        }

        $totales_badge = [
            'bruto' => 0,
            'impuesto' => 0,
            'neto' => 0,
        ];

        foreach ($totales_del_mes as $total) {
            $totales_badge['bruto'] += $total['ventas_brutas'];
            $totales_badge['impuesto'] += $total['impuestos'];
            $totales_badge['neto'] += $total['ventas_netas'];
        }


        $ventas = [$totales_del_mes, $totales_badge];
        $manual = 1;

        if ($esExportable) {
            return Excel::download(new VentasMensualExport($ventas, $moneda), 'ventas_mensual.xlsx');
        } else {
            return view('reportes.ventas_mensual',
                [
                    'usuario' => auth()->user()->persona,
                    'ventas' => $ventas,
                    'moneda' => $moneda,
                    'anio' => $anio,
                    'manual' => $manual,
                    'usar_tipo_cambio' => $tipo_cambio
                ]);
        }
    }

    public function reporte_mensual_generar_mes(Request $request, $mes = null)
    {
        $moneda = $request->moneda ?? 'PEN';

        $ex = explode('-', $mes);
        $anio = $ex[0];
        $valor_mes = $ex[1];

        $ventas = $this->reporte_ventas_diario_data($mes, $moneda);
        $ventas = $ventas[0];

        if (count($ventas) > 0) {

            $data = [];
            $ventas_brutas = 0;
            $costos = 0;
            $utilidad = 0;
            $impuestos = 0;
            $ventas_netas = 0;
            $tipo_cambio = 0;

            foreach ($ventas as $venta) {
                $ventas_brutas += $venta['ventas_brutas'];
                $costos += $venta['costos'];
                $utilidad += $venta['utilidad'];
                $impuestos += $venta['impuestos'];
                $ventas_netas += $venta['ventas_netas'];
            }

            $data['fecha'] = date("M Y", strtotime($mes));
            $data['ventas_brutas'] = $ventas_brutas;
            $data['costos'] = $costos;
            $data['utilidad'] = $utilidad;
            $data['impuestos'] = $impuestos;
            $data['ventas_netas'] = $ventas_netas;
            $data['tipo_cambio'] = $tipo_cambio;

            Opciones::where('nombre_opcion', 'reporte-mensual-' . $anio . '-' . $moneda)
                ->where('valor', $valor_mes)
                ->update([
                    'fecha' => now(),
                    'valor_json' => json_encode($data)
                ]);
        }

        return redirect('/reportes/ventas/mensual/' . date('Y', strtotime($mes)) . '?moneda=' . $moneda);

    }

    public function inicializarMesesParaReporteMensual($anio, $moneda){

        for ($i = 0; $i < 12; $i++) {
            $mes = str_pad($i + 1, 2, '0', STR_PAD_LEFT);
            $totales_del_mes[$i]['fecha'] = date("M Y", strtotime($anio . '-' . $mes . '-01'));
            $totales_del_mes[$i]['ventas_brutas'] = 0;
            $totales_del_mes[$i]['costos'] = 0;
            $totales_del_mes[$i]['utilidad'] = 0;
            $totales_del_mes[$i]['impuestos'] = 0;
            $totales_del_mes[$i]['ventas_netas'] = 0;
            $totales_del_mes[$i]['tipo_cambio'] = 0;

            $opt = new Opciones();
            $opt->nombre_opcion = 'reporte-mensual-' . $anio . '-' . $moneda;
            $opt->valor = $mes;
            $opt->valor_json = json_encode($totales_del_mes[$i]);
            $opt->save();

        }

    }

    //funciones para reporte de gastos
    public function reporte_gastos_data($desde, $hasta, $filtro, $buscar, $esExportable)
    {
        try {

            $gastos = null;
            $filtros = ['desde' => $desde, 'hasta' => $hasta, 'filtro' => $filtro, 'buscar' => $buscar];


            if ($esExportable == 'true') {
                switch ($filtro) {
                    case 'fecha':
                        $gastos = Gastos::whereBetween('fecha', [$desde . ' ' . $this->hora_inicio, $this->getHasta($hasta) . ' ' . $this->hora_fin])
                            ->where('eliminado', 0)
                            ->orderby('idgasto', 'desc')
                            ->paginate(30);
                        break;
                    case 'proveedor':
                        $filtro = 'nombre';
                        $gastos = Gastos::whereBetween('fecha', [$desde . ' ' . $this->hora_inicio, $this->getHasta($hasta) . ' ' . $this->hora_fin])
                            ->where('eliminado', 0)
                            ->orderby('idgasto', 'desc')
                            ->whereHas('persona', function ($query) use ($filtro, $buscar) {
                                $query->where($filtro, 'LIKE', '%' . $buscar . '%');
                            })
                            ->paginate(30);
                        break;
                }
            } else {
                switch ($filtro) {
                    case 'fecha':
                        $gastos = Gastos::whereBetween('fecha', [$desde . ' ' . $this->hora_inicio, $this->getHasta($hasta) . ' ' . $this->hora_fin])
                            ->orderby('idgasto', 'desc')
                            ->paginate(30);
                        break;
                    case 'proveedor':
                        $filtro = 'nombre';
                        $gastos = Gastos::whereBetween('fecha', [$desde . ' ' . $this->hora_inicio, $this->getHasta($hasta) . ' ' . $this->hora_fin])
                            ->orderby('idgasto', 'desc')
                            ->whereHas('persona', function ($query) use ($filtro, $buscar) {
                                $query->where($filtro, 'LIKE', '%' . $buscar . '%');
                            })
                            ->paginate(30);
                        break;
                }
            }


            return [
                'gastos' => $gastos,
                'filtros' => $filtros,
                'usuario' => auth()->user()->persona
            ];

        } catch (\Exception $e) {
            Log::error($e);
            return $e->getMessage();
        }
    }

    public function reporte_gastos(Request $request, $desde = null, $hasta = null)
    {

        $esExportable = $request->get('export', 'false');
        $filtro = $request->filtro;
        $buscar = $request->buscar;

        if (!$filtro) {
            $filtro = 'fecha';
            $desde = date('Y-m-d');
            $hasta = date('Y-m-d');
        }

        $gastos = $this->reporte_gastos_data($desde, $hasta, $filtro, $buscar, $esExportable);

        if ($esExportable == 'true') {
            return Excel::download(new ReporteComprobantes($gastos['comprobantes']), 'reporte_gastos.xlsx');
        } else {
            return view('reportes.gastos', $gastos);
        }

    }

    public function reporte_gastos_diario_data($mes)
    {

        $suma = 0;
        $fecha_anterior = null;
        $suma_por_dia = null;

        $gastos = Gastos::whereBetween('fecha', [$mes . '-01 ' . $this->hora_inicio, $mes . '-31 ' . $this->hora_fin])
            ->orderBy('fecha', 'desc')->get();

        foreach ($gastos as $item) {

            $fecha_venta = date("d-m-Y", strtotime($item->fecha));

            if ($fecha_venta == $fecha_anterior) {
                $suma += $item->monto;
                $suma_por_dia[count($suma_por_dia) - 1]['total_dia'] = $suma;
            } else {
                $suma = 0;
                $suma += $item->monto;
                $suma_por_dia[] = ['fecha' => $fecha_venta, 'total_dia' => $suma];
                $fecha_anterior = date("d-m-Y", strtotime($item->fecha));
            }

        }

        return $suma_por_dia;
    }

    public function reporte_gastos_diario($mes)
    {

        $suma_por_dia = $this->reporte_gastos_diario_data($mes);

        return view('reportes.gastos_diario',
            [
                'usuario' => auth()->user()->persona,
                'gastos' => $suma_por_dia,
                'mes' => $mes
            ]);
    }

    public function reporte_gastos_diario_export($mes)
    {
        return Excel::download(new GastosDiariosExport($this->reporte_gastos_diario_data($mes)), 'gastos_diario.xlsx');
    }

    public function reporte_gastos_mensual_data($anio)
    {

        $suma = 0;
        $fecha_anterior = null;
        $suma_por_mes = null;

        $gastos = Gastos::whereBetween('fecha', [$anio . '-01-01 ' . $this->hora_inicio, $anio . '-12-31 ' . $this->hora_fin])
            ->orderBy('fecha', 'desc')->get();

        foreach ($gastos as $item) {

            $fecha_venta = date("M Y", strtotime($item->fecha));

            if ($fecha_venta == $fecha_anterior) {
                $suma += $item->monto;
                $suma_por_mes[count($suma_por_mes) - 1]['total_mes'] = $suma;
            } else {
                $suma = 0;
                $suma += $item->monto;
                $suma_por_mes[] = ['fecha' => $fecha_venta, 'total_mes' => $suma];
                $fecha_anterior = date("M Y", strtotime($item->fecha));
            }

        }

        return $suma_por_mes;
    }

    public function reporte_gastos_mensual($anio)
    {

        $suma_por_dia = $this->reporte_gastos_mensual_data($anio);

        return view('reportes.gastos_mensual',
            [
                'usuario' => auth()->user()->persona,
                'gastos' => $suma_por_dia,
                'anio' => $anio
            ]);
    }

    public function reporte_gastos_mensual_export($mes)
    {
        return Excel::download(new GastosMensualExport($this->reporte_gastos_mensual_data($mes)), 'gastos_mensual.xlsx');
    }


    //funciones para reporte de productos

    public function reporte_stock_bajo(Request $request)
    {

        $productos = Producto::with('inventario')->where('eliminado', 0)
            ->where('tipo_producto', 1)
            ->where('idproducto', '!=', -1)
            ->get();

        $seleccionados = [];

        foreach ($productos as $key => $producto) {
            $item = $producto->inventario->first();
            $saldo = $item->saldo ?? 0;
            if ($producto->stock_bajo >= $saldo) {
                $producto->saldo = $saldo;
                $seleccionados[] = $productos->pull($key);
            }
        }

        usort($seleccionados, function ($a, $b) {
            return $a['saldo'] <=> $b['saldo'];
        });

        $esExportable = $request->get('export', 'false');

        if ($esExportable == 'true') {
            return Excel::download(new StockBajoExport($seleccionados), 'stock_bajo.xlsx');
        } else {
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
                    'usuario' => auth()->user()->persona,
                    'productos' => $collection
                ]);
        }


    }

    public function mas_vendidos_data($desde, $hasta, $esExportable)
    {

        try {
            $filtros = ['desde' => $desde, 'hasta' => $hasta];
            $productos = DB::table('ventas')
                ->join('ventas_detalle', 'ventas_detalle.idventa', '=', 'ventas.idventa')
                ->join('productos', 'productos.idproducto', '=', 'ventas_detalle.idproducto')
                ->join('facturacion', 'ventas.idventa', '=', 'facturacion.idventa')
                ->selectRaw('sum(ventas_detalle.cantidad) as vendidos,sum(ventas_detalle.monto * ventas_detalle.cantidad) as monto_total,ventas_detalle.idproducto, productos.nombre, productos.unidad_medida, productos.presentacion, productos.cod_producto, productos.precio, productos.tipo_producto')
                ->whereBetween('ventas.fecha', [$desde . ' ' . $this->hora_inicio, $this->getHasta($hasta) . ' ' . $this->hora_fin])
                ->where('ventas.eliminado', 0)
                ->where(function ($query) {
                    $query->whereIn('codigo_tipo_documento', [01, 03, 30])
                        ->where(function ($query) {
                            $query->where('facturacion.estado', 'ACEPTADO')
                                ->orWhere('facturacion.estado', 'PENDIENTE')
                                ->orWhere('facturacion.estado', '-');
                        });
                })
                ->groupBy('ventas_detalle.idproducto')
                ->orderby('vendidos', 'desc')
                ->when($esExportable, function ($query) {
                    return $query->get();
                }, function ($query) {
                    return $query->paginate(30);
                });

            if (!$esExportable) {
                $productos->appends($_GET)->links();
            }

            return ['productos' => $productos, 'filtros' => $filtros, 'usuario' => auth()->user()->persona];

        } catch (\Exception $e) {
            Log::error($e);
            return $e->getMessage();
        }
    }

    public function mas_vendidos(Request $request)
    {


        $esExportable = $request->get('export', false);
        $desde = $request->get('desde', date('Y-m-d'));
        $hasta = $request->get('hasta', date('Y-m-d'));

        $productos = $this->mas_vendidos_data($desde, $hasta, $esExportable);

        if ($esExportable == 'true') {
            return Excel::download(new MasVendidosExport($productos['productos']), 'mas_vendidos.xlsx');
        } else {
            return view('reportes.mas_vendidos', $productos);
        }

    }

    public function mas_vendidos_badge(Request $request)
    {

        $desde = $request->get('desde', date('Y-m-d'));
        $hasta = $request->get('hasta', date('Y-m-d'));

        $productos = $this->mas_vendidos_data($desde, $hasta, true);

        $resumen = ['cantidad' => 0, 'total' => 0];

        foreach ($productos['productos'] as $producto) {
            if ($producto->tipo_producto != 4) {
                $resumen['cantidad'] += $producto->vendidos;
                $resumen['total'] += $producto->monto_total;
            }
        }

        return $resumen;

    }

    //funciones para reporte de comprobantes

    public function reporte_comprobantes_data($desde, $hasta, $filtro, $buscar, $esExportable)
    {
        try {

            $ventas = null;
            $filtros = ['desde' => $desde, 'hasta' => $hasta, 'filtro' => $filtro, 'buscar' => $buscar];

            switch ($filtro) {
                case 'fecha':
                    $ventas = Venta::whereBetween('fecha', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
                        ->where('eliminado', '=', 0)
                        ->whereHas('facturacion', function ($query) {
                            $query->where('codigo_tipo_documento', 01)
                                ->orWhere('codigo_tipo_documento', 03)
                                ->orWhere('codigo_tipo_documento', 07)
                                ->orWhere('codigo_tipo_documento', '08');
                        })
                        ->orderby('fecha', 'desc')
                        ->orderby('idventa', 'desc')
                        ->when($esExportable, function ($query) {
                            return $query->get();
                        }, function ($query) {
                            return $query->paginate(30);
                        });
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
                        ->orderby('idventa', 'desc')
                        ->whereHas('facturacion', function ($query) use ($filtro, $buscar) {
                            $query->where($filtro, $buscar);
                        })
                        ->when($esExportable, function ($query) {
                            return $query->get();
                        }, function ($query) {
                            return $query->paginate(30);
                        });
                    break;
            }


            if (!$esExportable) {
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

                switch ($item->facturacion->codigo_tipo_documento) {
                    case 01:
                        $item->tipo_doc = 'FACTURA';
                        $item->facturacion->num_doc_relacionado = '-';
                        break;
                    case 03:
                        $item->tipo_doc = 'BOLETA';
                        $item->facturacion->num_doc_relacionado = '-';
                        break;
                    case 07:
                        $item->tipo_doc = 'NOTA DE CRÉDITO';
                        break;
                    case '08':
                        $item->tipo_doc = 'NOTA DE DÉBITO';
                        break;
                    default:
                        $item->tipo_doc = 'RECIBO';
                }

                switch ($item->facturacion->estado) {
                    case 'PENDIENTE':
                        $item->badge_class = 'badge-warning';
                        break;
                    case 'ACEPTADO':
                        $item->badge_class = 'badge-success';
                        break;
                    case 'ANULADO':
                        $item->facturacion->estado = 'ANULADO CON NC';
                        $item->badge_class = 'badge-dark';
                        break;
                    case 'ANULADO (BAJA)':
                        $item->facturacion->estado = 'ANULADO (COMUNICACIÓN DE BAJA)';
                        $item->badge_class = 'badge-dark';
                        break;
                    case 'MODIFICADO':
                        $item->facturacion->estado = 'MODIFICADO CON ND';
                        $item->badge_class = 'badge-dark';
                        break;
                    case 'RECHAZADO':
                        $item->badge_class = 'badge-danger';
                }

            }

            return [
                'comprobantes' => $ventas,
                'filtros' => $filtros,
                'usuario' => auth()->user()->persona
            ];

        } catch (\Exception $e) {
            Log::error($e);
            return $e->getMessage();
        }
    }

    public function reporte_comprobantes(Request $request, $desde = null, $hasta = null)
    {

        $esExportable = $request->get('export', false);
        $filtro = $request->filtro;
        $buscar = $request->buscar;

        if (!$filtro) {
            $filtro = 'fecha';
            $desde = date('Y-m-d');
            $hasta = date('Y-m-d');
        }

        $comprobantes = $this->reporte_comprobantes_data($desde, $hasta, $filtro, $buscar, $esExportable);

        if ($esExportable) {
            $type = $request->get('type', 'excel');
            if ($type == 'excel') {
                return Excel::download(new ReporteComprobantes($comprobantes['comprobantes']), 'reporte_comprobantes.xlsx');
            } else {

                //verificar_comprobantes_mismo_periodo();

                $emisor = new Emisor();
                $txtContent = "";
                $periodo = date('Ym', strtotime($desde));

                foreach ($comprobantes['comprobantes'] as $venta) {

                    if ($venta->facturacion->estado != 'RECHAZADO') {

                        $doc_modificado = [
                            'fecha' => '',
                            'tipo' => '',
                            'serie' => '',
                            'correlativo' => '',
                        ];

                        switch ($venta->facturacion->estado) {
                            case 'ANULADO (BAJA)':
                                $estado = 2;
                                break;
                            case 'ANULADO':
                                $estado = 4;
                                break;
                            default:
                                $estado = 1;
                        }

                        if ($venta->facturacion->codigo_tipo_documento == '07' || $venta->facturacion->codigo_tipo_documento == '08') {
                            $doc_modificado = Venta::whereHas('facturacion', function ($query) use ($venta) {
                                $query->where('serie', $venta->facturacion->serie)
                                    ->where('correlativo', $venta->facturacion->correlativo);
                            })
                                ->first();

                            if (!$doc_modificado) {
                                $doc_modificado = [
                                    'fecha' => '',
                                    'tipo' => '',
                                    'serie' => '',
                                    'correlativo' => '',
                                ];
                            }

                        }

                        $txtContent .=
                            $emisor->ruc//1 Ruc
                            . "|" . $emisor->razon_social//2 Razon social
                            . "|" . $periodo//3 Periodo
                            . "|"//4 CAR
                            . "|" . date('d/m/Y', strtotime($venta->fecha))//5
                            . "|" . date('d/m/Y', strtotime($venta->fecha_vencimiento))//6
                            . "|" . $venta->facturacion->codigo_tipo_documento//7
                            . "|" . $venta->facturacion->serie//8
                            . "|" . $venta->facturacion->correlativo//9 Nro CP o Doc. Nro Inicial (Rango)
                            . "|"//Nro Final (Rango)
                            . "|" . $venta->cliente->tipo_documento//11
                            . "|" . $venta->cliente->num_documento//12
                            . "|" . $venta->cliente->persona->nombre//13
                            . "|0"//14 Valor Facturado Exportación
                            . "|" . $venta->facturacion->total_gravadas//15 BASE IMPONIBLE
                            . "|0"//16 Descuento base imponible
                            . "|" . $venta->facturacion->igv//17
                            . "|0"//18 Descuento igv
                            . "|" . $venta->facturacion->total_exoneradas//19
                            . "|" . $venta->facturacion->total_inafectas//20
                            . "|0"//21 ISC
                            . "|0"//22 BI Grav IVAP
                            . "|0"//23 IVAP
                            . "|0"//24 ICBPER
                            . "|0"//25 Otros Tributos
                            . "|" . $venta->total_venta//26 Total CP
                            . "|" . $venta->facturacion->codigo_moneda//27 Moneda
                            . "|" . ($venta->facturacion->codigo_moneda == 'USD' ? $venta->tipo_cambio : '')//28 Tipo Cambio
                            //
                            . "|" . $doc_modificado['fecha']//29 Fecha Emisión Doc Modificado
                            . "|" . $doc_modificado['tipo']//30 Tipo CP Modificado
                            . "|" . $doc_modificado['serie']//31 Serie CP Modificado
                            . "|" . $doc_modificado['correlativo']//32 Nro CP Modificado

                            . "|"//33 ID Proyecto Operadores Atribución
                            . "|" . $venta->facturacion->tipo_nota_electronica//34 Tipo de Nota
                            . "|" . $estado//35 Est. Comp
                            . "|0"//36 Valor FOB Embarcado
                            . "|" . $venta->facturacion->total_gratuitas//37 Valor OP Gratuitas
                            . "|" . $venta->facturacion->codigo_tipo_factura//38 Tipo Operación
                            . "|"//39 CLU
                            . "|"//40 ID Proyecto Operadores Atribución
                            . "|" . $emisor->ruc . $venta->facturacion->codigo_tipo_documento . $venta->facturacion->serie . $venta->facturacion->correlativo //41 CAR
                            . "\n";
                    }
                }

                $tempFileName = 'LE' . $emisor->ruc . $periodo . '00140400021112.txt';
                file_put_contents(storage_path('app/' . $tempFileName), $txtContent);

                $zipFileName = 'LE' . $emisor->ruc . $periodo . '00140400021112.zip';
                $zip = new ZipArchive;
                $zip->open(storage_path('app/' . $zipFileName), ZipArchive::CREATE);
                $zip->addFile(storage_path('app/' . $tempFileName), $tempFileName);
                $zip->close();

                unlink(storage_path('app/' . $tempFileName));

                return response()->download(storage_path('app/' . $zipFileName))->deleteFileAfterSend(true);

            }
        } else {
            return view('reportes.comprobantes', $comprobantes);
        }

    }

    //funciones para reporte de caja

    public function reporte_caja_data($desde, $hasta, $filtro, $buscar, $esExportable)
    {
        try {

            $cajas = null;
            $filtros = ['desde' => $desde, 'hasta' => $hasta, 'filtro' => $filtro, 'buscar' => $buscar];

            $cajas = Caja::whereBetween('fecha_a', [$desde . ' ' . $this->hora_inicio, $this->getHasta($hasta) . ' ' . $this->hora_fin])
                ->orderby('fecha_a', 'desc')
                ->when($esExportable, function ($query) {
                    return $query->get();
                }, function ($query) {
                    return $query->paginate(30);
                });

            if (!$esExportable) {
                $cajas->appends($_GET)->links();
            }

            foreach ($cajas as $caja) {
                $suma = Venta::where('idcaja', $caja->idcaja)
                    ->where('eliminado', '=', 0)
                    ->where('tipo_pago', '!=', 2)
                    ->sum('total_venta');

                $caja->total_ventas = number_format($suma, 2);
                $caja->otros_medios = $caja->yape + $caja->plin + $caja->otros +
                    $caja->transferencia + $caja->tarjeta + $caja->tarjeta_1 + $caja->tarjeta_2 + $caja->rappi + $caja->pedidosya + $caja->deliverygo;
            }

            return [
                'cajas' => $cajas,
                'filtros' => $filtros,
                'usuario' => auth()->user()->persona
            ];

        } catch (\Exception $e) {
            Log::error($e);
            return $e->getMessage();
        }
    }

    public function reporte_caja(Request $request, $desde = null, $hasta = null)
    {

        $esExportable = $request->get('export', false);
        $filtro = $request->filtro;
        $buscar = $request->buscar;

        if (!$filtro) {
            $filtro = 'fecha';
            $desde = date('Y-m-d');
            $hasta = date('Y-m-d');
        }

        $cajas = $this->reporte_caja_data($desde, $hasta, $filtro, $buscar, $esExportable);

        if ($esExportable == 'true') {
            return Excel::download(new CajaExport($cajas['cajas']), 'reporte_caja.xlsx');
        } else {
            return view('reportes.caja', $cajas);
        }

    }

    public function descargar_archivo($file_or_id)
    {

        if (is_numeric($file_or_id)) {
            PdfHelper::generarPdf($file_or_id, false, 'D');
        } else {
            $archivo = explode('.', $file_or_id);

            if (MainHelper::check_doc_up_to_year($archivo)) {
                return redirect()->back()->withErrors(['El archivo no existe o supera un año de antiguedad. Comunícate con el administrador del sistema.']);
            }

            switch ($archivo[1]) {
                case 'xml':
                    $pathtoFile = storage_path() . '/app/sunat/xml/' . $file_or_id;
                    return response()->download($pathtoFile);
                    break;
                case 'cdr':
                    $pathtoFile = storage_path() . '/app/sunat/cdr/' . $archivo[0] . '.xml';
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

    public function reporte_ventas_por_email(Request $request, $desde, $hasta)
    {
        try {
            $request['mail'] = 'true';
            $request['export'] = 'true';
            $data = $this->reporte_ventas($request, $desde, $hasta);

            $view = view('mail/pdf/reporte_resumen_ventas', ['ventas' => $data[0], 'tipo_pago' => $data[1], 'totales' => $data[2], 'fecha' => $data[3], 'buscar' => $data[4]]);
            $html = $view->render();

            $pdf = new Html2Pdf('P', 'A4', 'es');
            $pdf->pdf->SetTitle('RESUMEN DE VENTAS');
            $pdf->writeHTML($html);
            $pdf->output(public_path() . '/pdf/reporte_resumen_ventas.pdf', 'F');

            $email = $request->email;
            Mail::to($email)->send(new ReporteResumenVentas());
            if (file_exists(public_path() . '/pdf/reporte_resumen_ventas.pdf')) {
                unlink(public_path() . '/pdf/reporte_resumen_ventas.pdf');
            }
            return 'Se envió al correo correctamente';
        } catch (\Exception $e) {
            Log::error($e);
            return $e->getMessage();
        }
    }

    public function reporte_ventas_imprimir(Request $request, $desde, $hasta)
    {
        try {
            $data = $this->reporte_ventas_badge($request, $desde, $hasta);
            $fecha = ['desde' => $desde, 'hasta' => $hasta];
            $view = view('reportes/imprimir/resumen_ventas', ['totales' => $data, 'tipo_pago' => $data[2], 'fecha' => $fecha, 'tipo' => $request->reporte, 'moneda' => $request->moneda]);
            $html = $view->render();

            $pdf = new Html2Pdf('P', [72, 250], 'es');
            $pdf->pdf->SetTitle('REPORTE DE VENTAS');
            $pdf->writeHTML($html);

            if ($request->rawbt) {
                $fromFile = $pdf->output('resumen_ventas.pdf', 'S');
                return 'rawbt:data:application/pdf;base64,' . base64_encode($fromFile);
            } else {
                $pdf->output('resumen_ventas.pdf');
            }

        } catch (\Exception $e) {
            Log::error($e);
            return $e->getMessage();
        }
    }

    public function obtener_vendedores()
    {
        $vendedores = Trabajador::where('eliminado', 0)->where('cargo', 1)->get();
        foreach ($vendedores as $vendedor) {
            $vendedor->persona;
        }
        return $vendedores;
    }

    public function obtener_cajeros()
    {
        $cajeros = User::role('Caja')
            ->where('acceso', '!=', '1')
            ->where('eliminado', 0)
            ->get();

        foreach ($cajeros as $cajero) {
            $cajero->persona;
        }
        return $cajeros;
    }

    public function reporte_anulados(Request $request)
    {

        $esExportable = $request->get('export', false);
        $tipo = $request->tipo;
        $desde = $request->get('desde', date('Y-m-d'));
        $hasta = $request->get('hasta', date('Y-m-d'));

        $filtros = ['desde' => $desde, 'hasta' => $hasta, 'tipo' => $tipo];

        $ventas = Venta::whereBetween('fecha', [$desde . ' ' . $this->hora_inicio, $this->getHasta($hasta) . ' ' . $this->hora_fin])
            ->where(function ($q) {
                $q->where('eliminado', 1)
                    ->orwhereHas('facturacion', function ($query) {
                        $query->whereIn('codigo_tipo_documento', [01, 03])
                            ->where(function ($query) {
                                $query->where('estado', 'ANULADO');
                            });
                    });
            })
            ->orderby('idventa', 'desc')
            ->when($esExportable, function ($query) {
                return $query->get();
            }, function ($query) {
                return $query->paginate(30);
            });

        if ($esExportable) {
            return Excel::download(new VentasEliminadasExport($ventas), 'reporte_ventas_eliminadas.xlsx');
        } else {
            $ventas->appends($_GET)->links();

            return view('reportes.ventas_anuladas', [
                    'ventas' => $ventas,
                    'filtros' => $filtros,
                    'usuario' => auth()->user()->persona
                ]
            );
        }

    }

    public function productos_resumen_diario(Request $request)
    {

        $esExportable = $request->get('export', false);
        $tipo = $request->tipo;
        $desde = $request->get('desde', date('Y-m-d'));
        $hasta = $request->get('hasta', date('Y-m-d'));

        $filtros = ['desde' => $desde, 'hasta' => $hasta, 'tipo' => $tipo];

        $productos = DB::table('inventario')
            ->join('productos', 'inventario.idproducto', '=', 'productos.idproducto')
            ->select(
                'inventario.fecha', 'inventario.cantidad',
                'productos.idproducto', 'productos.cod_producto', 'productos.nombre', 'productos.precio', 'productos.presentacion', 'unidad_medida', 'tipo_producto',
                DB::raw('SUM(inventario.cantidad) as vendidos')
            )
            ->where('tipo_producto', '!=', 4)
            ->whereNotNull('inventario.idventa')
            ->whereBetween('inventario.fecha', [$desde . ' ' . $this->hora_inicio, $this->getHasta($hasta) . ' ' . $this->hora_fin])
            ->groupBy(DB::raw('DATE(inventario.fecha)'), 'inventario.idproducto')
            ->orderBy(DB::raw('DATE_FORMAT(inventario.fecha, "%Y-%m-%d")'), 'desc')
            ->orderBy('vendidos', 'asc')
            ->when($esExportable, function ($query) {
                return $query->get();
            }, function ($query) {
                return $query->paginate(30);
            });

        if ($esExportable) {
            return Excel::download(new ProductosResumenDiarioExport($productos, $desde, $hasta), 'productos_resumen_diario.xlsx');
        } else {

            $productos->appends($_GET)->links();

            return view('reportes.productos_diarios', [
                    'productos' => $productos,
                    'filtros' => $filtros,
                    'usuario' => auth()->user()->persona
                ]
            );

        }

    }

    public function productos_resumen_diario_badge(Request $request)
    {

        $desde = $request->get('desde', date('Y-m-d'));
        $hasta = $request->get('hasta', date('Y-m-d'));

        $productos = DB::table('inventario')
            ->join('productos', 'inventario.idproducto', '=', 'productos.idproducto')
            ->select(
                'inventario.fecha', 'inventario.cantidad',
                'productos.idproducto', 'productos.cod_producto', 'productos.nombre', 'productos.precio', 'productos.presentacion', 'unidad_medida', 'tipo_producto',
                DB::raw('SUM(inventario.cantidad) as vendidos')
            )
            ->where('tipo_producto', '!=', 4)
            ->whereNotNull('inventario.idventa')
            ->whereBetween('inventario.fecha', [$desde . ' ' . $this->hora_inicio, $this->getHasta($hasta) . ' ' . $this->hora_fin])
            ->groupBy(DB::raw('DATE(inventario.fecha)'), 'inventario.idproducto')
            ->orderBy(DB::raw('DATE_FORMAT(inventario.fecha, "%Y-%m-%d")'), 'desc')
            ->orderBy('vendidos', 'asc')
            ->get();

        $suma = 0;

        foreach ($productos as $producto) {
            $suma += $producto->vendidos;
        }

        return abs($suma);


    }

    public function productos_resumen_diario_detalle(Request $request)
    {

        $fecha = date('Y-m-d', strtotime($request->fecha));
        $hasta = date('Y-m-d', strtotime($request->hasta));
        $desde = date('Y-m-d', strtotime($request->desde));

        $productosDetalle = DB::table('ventas_detalle')
            ->join('productos', 'ventas_detalle.idproducto', '=', 'productos.idproducto')
            ->join('ventas', 'ventas_detalle.idventa', '=', 'ventas.idventa')
            ->join('facturacion', 'ventas.idventa', '=', 'facturacion.idventa')
            ->join('persona as client', 'ventas.idcliente', '=', 'client.idpersona')
            ->join('persona as cajero', 'ventas.idcajero', '=', 'cajero.idpersona')
            ->join('persona as mozo', 'ventas.idempleado', '=', 'mozo.idpersona')
            ->select(
                'ventas.idventa', 'ventas.fecha', 'productos.nombre', 'productos.precio', 'productos.tipo_producto', 'ventas_detalle.items_kit', 'client.nombre as cliente', 'mozo.nombre as atiende', 'cajero.nombre as caja', 'ventas_detalle.monto', 'ventas_detalle.cantidad', 'facturacion.codigo_moneda')
            ->where(function ($q) use ($request) {
                $q->where('ventas_detalle.idproducto', $request->idproducto)
                    ->orWhereRaw("JSON_CONTAINS(ventas_detalle.items_kit, '{\"idproducto\": $request->idproducto}')");
            })
            ->whereBetween('ventas.fecha', [$desde . ' ' . $this->hora_inicio, $this->getHasta($hasta) . ' ' . $this->hora_fin])
            ->where('ventas.fecha', 'LIKE', $fecha . '%')
            ->where('ventas.eliminado', 0)
            ->whereIn('facturacion.codigo_tipo_documento', [01, 03, 30])
            ->where(function ($query) {
                $query->where('facturacion.estado', 'ACEPTADO')
                    ->orWhere('facturacion.estado', 'PENDIENTE')
                    ->orWhere('facturacion.estado', '-');
            })
            ->orderBy('idventa', 'desc')
            ->get();


        $productosDetalleFinal = [];

        foreach ($productosDetalle as $producto) {
            $producto->total = $producto->monto * $producto->cantidad;
            $producto->fecha = date('d/m/Y H:i:s', strtotime($producto->fecha));
            if ($producto->tipo_producto == 3) {
                $producto->monto = -1;
                $kit = json_decode($producto->items_kit, true);
                foreach ($kit as $item) {
                    if (isset($item['idproducto']) && $item['idproducto'] == $request->idproducto) {
                        // Duplicar el producto
                        $nuevoProducto = clone $producto;

                        // Modificar las propiedades del producto duplicado
                        $nuevoProducto->cantidad = $item['cantidad'] * $producto->cantidad;
                        $nuevoProducto->nombre = $item['nombre'] . " (KIT: " . $nuevoProducto->nombre . ")";
                        $nuevoProducto->precio = $nuevoProducto->precio . " (KIT)";

                        // Agregar el producto duplicado a la lista final
                        $productosDetalleFinal[] = $nuevoProducto;
                    }
                }

            } else {
                $productosDetalleFinal[] = $producto;
            }
        }

        return $productosDetalleFinal;

    }

    public function comparar_txt(Request $request)
    {
        $comprobantes = $this->reporte_comprobantes_data($request->desde, $request->hasta, 'fecha', null, true);
        $archivoZip = $request->file('archivo');

        // Ruta temporal para extraer
        $extractPath = storage_path('app/temp_extracted');
        if (!file_exists($extractPath)) mkdir($extractPath, 0777, true);

        $zip = new ZipArchive;
        $zip->open($archivoZip->path());
        $zip->extractTo($extractPath);
        $zip->close();

        // Obtener archivo TXT extraído
        $archivos = array_diff(scandir($extractPath), ['.', '..']);
        $archivo = array_values($archivos)[0]; // primer archivo real
        $rutaArchivo = $extractPath . '/' . $archivo;

        // Leer líneas
        $lineas = file($rutaArchivo, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
        if (count($lineas) < 2) {
            unlink($rutaArchivo);
            rmdir($extractPath);
            throw new \Exception("El archivo no contiene datos válidos.");
        }

        // Procesar cabecera
        $cabecera = explode('|', $lineas[0]);
        $indices = array_flip($cabecera); // Mapea nombres a índices

        $resultados = [];
        foreach ($comprobantes['comprobantes'] as $venta) {
            $serie = $venta->facturacion->serie;
            $correlativo = ltrim($venta->facturacion->correlativo, '0');
            $totalVentaDB = $venta->total_venta;
            $ventaEncontrada = false;

            foreach (array_slice($lineas, 1) as $linea) {
                $datos = explode('|', $linea);
                if (count($datos) < count($cabecera)) continue;

                $tipoComprobante = $datos[$indices['Tipo CP/Doc.']];
                $serieArchivo = $datos[$indices['Serie del CDP']];
                $correlativoArchivo = ltrim($datos[$indices['Nro CP o Doc. Nro Inicial (Rango)']], '0');
                $totalVentaArchivo = floatval($datos[$indices['Total CP']]);

                if ($tipoComprobante == '07') {
                    $totalVentaArchivo *= -1;
                }

                if ($serie == $serieArchivo && $correlativo == $correlativoArchivo) {
                    $resultados[] = [
                        'serie' => $serie,
                        'correlativo' => $correlativo,
                        'totalDB' => $totalVentaDB,
                        'totalArchivo' => $totalVentaArchivo,
                        'diferencia' => $totalVentaDB - $totalVentaArchivo,
                        'estado' => 'Encontrado',
                    ];
                    $ventaEncontrada = true;
                    break;
                }
            }

            if (!$ventaEncontrada) {
                $resultados[] = [
                    'serie' => $serie,
                    'correlativo' => $correlativo,
                    'totalDB' => $totalVentaDB,
                    'totalArchivo' => '',
                    'diferencia' => null,
                    'estado' => 'No encontrado',
                ];
            }
        }

        // Limpieza
        if (file_exists($rutaArchivo)) {
            unlink($rutaArchivo);
        }
        if (file_exists($extractPath)) {
            rmdir($extractPath);
        }

        // Generar y mover Excel
        Excel::store(new ComparacionSire($resultados), 'comparacion_sire.xlsx');
        $origen = storage_path('app/comparacion_sire.xlsx');
        $destino = public_path('files/comparacion_sire.xlsx');
        rename($origen, $destino);

        return '/files/comparacion_sire.xlsx';
    }

}
