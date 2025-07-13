<?php

namespace sysfact\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use sysfact\Caja;
use sysfact\Emisor;
use sysfact\Http\Controllers\CajaController;
use sysfact\Http\Controllers\Cpe\CpeController;
use sysfact\Http\Controllers\CreditoController;
use sysfact\Http\Controllers\ReporteController;
use sysfact\Mail\ReporteErroresVentas;
use sysfact\Opciones;
use sysfact\Venta;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReenvioComprobante extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reenviarComprobante {--domain=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Este comando reenviará comprobantes con estado pendiente';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cierre_automatico = json_decode(cache('config')['interfaz'], true)['cierre_automatico'] ?? true;
        if ($cierre_automatico) {
            $this->cerrar_caja();
        }
        $this->reenviar_comprobantes();
        $this->notificacion_creditos();
        $this->verificar_rechazados_del_dia();

        if (date('H') >= 1 && date('H') <= 3) {
            $this->checkVentasConsistentes();
        }
        if ($this->debeGenerarResumen()) {
            $this->generarResumenMensual();
        }

    }

    protected function debeGenerarResumen(): bool
    {
        $hoy = now();

        // Solo entre la 1 y 3 am
        $hora = (int) $hoy->format('H');
        if ($hora < 1 || $hora > 3) {
            return false;
        }

        // Solo en días pares
        return $hoy->day % 2 === 0;
    }

    public function generarResumenMensual()
    {
        $reporte    = new ReporteController();
        $anio        = date('Y');
        $mes_actual = date('n');

        foreach (['PEN','USD'] as $moneda) {

            $opcion = Opciones::where('nombre_opcion', 'reporte-mensual-' . $anio . '-' . $moneda)->orderby('valor', 'asc')->get();

            if (count($opcion) == 0) {
                $reporte->inicializarMesesParaReporteMensual($anio, $moneda);
            }

            for ($mes = 1; $mes <= $mes_actual; $mes++) {
                $fecha_inicio_mes = Carbon::createFromDate($anio, $mes, 1)->format('Y-m-d');

                // Creamos un Request con el parámetro moneda
                $rq = Request::create('/', 'GET', ['moneda' => $moneda]);

                try {
                    $reporte->reporte_mensual_generar_mes($rq, $fecha_inicio_mes);
                    Log::info("✅ [$moneda] Resumen de ventas generado: $fecha_inicio_mes");
                } catch (\Exception $e) {
                    Log::error("❌ [$moneda] Error al generar resumen $fecha_inicio_mes: " . $e->getMessage());
                }
            }
        }
    }


    public function notificacion_creditos()
    {
        $opcion = DB::table('opciones')->where('nombre_opcion', 'notificacion_creditos')->first();

        if (!$opcion || Carbon::now()->diffInDays($opcion->fecha) >= 2) {
            $credito = new CreditoController();
            $credito->creditos_notificacion();

            DB::table('opciones')->updateOrInsert(
                ['nombre_opcion' => 'notificacion_creditos'],
                ['fecha' => date('Y-m-d') . ' 09:00:00']
            );
        }
    }

    public function cerrar_caja()
    {
        try {
            if (date('H') >= 3 && date('H') <= 5) {
                $caja = Caja::orderby('fecha_a', 'desc')
                    ->where('estado', 0)
                    ->first();
                if ($caja) {
                    $cajaCon = new CajaController();
                    $cajaCon->cierre_automatico($caja->idcaja);
                }
            }
        } catch (\Exception $e) {
            Log::error($e);
        }
    }

    public function reenviar_comprobantes()
    {
        try {
            $ventas = Venta::where('eliminado', '=', 0)
                ->orderby('idventa', 'desc')
                ->whereHas('facturacion', function ($query) {
                    $query->where('estado', 'PENDIENTE');
                })
                ->get();

            $pendientes = 0;

            foreach ($ventas as $venta) {
                $cpe = new CpeController();
                $emisor = new Emisor();
                $nombre_fichero = $emisor->ruc . '-' . $venta->facturacion->codigo_tipo_documento . '-' . $venta->facturacion->serie . '-' . $venta->facturacion->correlativo;
                $respuesta = $cpe->reenviar($venta->idventa, $nombre_fichero, $venta->facturacion->num_doc_relacionado);

                if (is_string($respuesta[0])) {
                    if (!(str_contains(strtolower($respuesta[0]), 'aceptado') || str_contains(strtolower($respuesta[0]), 'aceptada'))) {
                        $pendientes++;
                    }
                }

            }

            if ($pendientes > 0) {
                Mail::to('ces.des007@gmail.com')->send(new \sysfact\Mail\MailPendientes($pendientes));
            }

        } catch (\Exception $e) {
            $ventas = Venta::where('eliminado', 0)
                ->orderby('idventa', 'desc')
                ->whereHas('facturacion', function ($query) {
                    $query->where('estado', 'PENDIENTE');
                })
                ->get();

            $pendientes = count($ventas);

            if ($pendientes > 0) {
                try {
                    Mail::to('ces.des007@gmail.com')->send(new \sysfact\Mail\MailPendientes($pendientes));
                } catch (\Swift_TransportException $e) {
                    return $e;
                }
            }
            Log::error($e);
        }
    }

    public function verificar_rechazados_del_dia()
    {
        try {
            // Filtra las ventas con estado RECHAZADO y fecha de hoy
            $rechazadas = Venta::where('eliminado', 0)
                ->whereDate('fecha', date('Y-m-d'))
                ->whereHas('facturacion', function ($query) {
                    $query->where('estado', 'RECHAZADO');
                })
                ->with('facturacion')
                ->get();

            if ($rechazadas->count() > 0) {
                Mail::to('ces.des007@gmail.com')->send(new \sysfact\Mail\MailRechazados($rechazadas->count()));
            }

        } catch (\Exception $e) {
            Log::error('Error al verificar facturas rechazadas: ' . $e->getMessage());
        }
    }

    public function checkVentasConsistentes()
    {
        $errores = [];
        $porcentaje_igv = json_decode(cache('config')['interfaz'], true)['porcentaje_igv'] ?? 18;
        $inicio = Carbon::yesterday()->startOfDay()->format('Y-m-d H:i:s');
        $fin = Carbon::now()->format('Y-m-d H:i:s');

        $ventas = Venta::where('eliminado', 0)
            ->whereBetween('fecha', [$inicio, $fin])
            ->with(['productos', 'facturacion'])
            ->get();

        foreach ($ventas as $venta) {
            $suma_total = 0;

            foreach ($venta->productos as $producto) {
                $monto = $producto->detalle->monto;
                $cantidad = $producto->detalle->cantidad;
                $suma_total += (round($monto * $cantidad, 2) - $producto->detalle->descuento);
            }

            if ($venta->igv_incluido) {
                $subtotal = round($suma_total / (1 + $porcentaje_igv / 100), 2);
                $igv = round($suma_total - $subtotal, 2);
            } else {
                $subtotal = round($suma_total, 2);
                $igv = round($subtotal * ($porcentaje_igv / 100), 2);
                $suma_total = round($subtotal + $igv, 2);
            }

            $venta_guardada = round($venta->total_venta, 2);
            $fact = $venta->facturacion;

            if (
                abs($venta_guardada - $suma_total) > 0.01 ||
                abs($fact->total_gravadas - $subtotal) > 0.01 ||
                abs($fact->igv - $igv) > 0.01
            ) {
                $errores[] = [
                    'venta_id' => $venta->idventa,
                    'total_registrado' => $venta_guardada,
                    'total_calculado' => $suma_total,
                    'subtotal_registrado' => $fact->total_gravadas,
                    'subtotal_calculado' => $subtotal,
                    'igv_registrado' => $fact->igv,
                    'igv_calculado' => $igv,
                ];
            }
        }

        if (count($errores)) {
            Mail::to('ces.des007@gmail.com')->send(new ReporteErroresVentas($errores));
            Log::info('Correo de inconsistencias enviado.');
        } else {
            Log::info('✅ Todas las ventas están consistentes.');
        }
    }


}
