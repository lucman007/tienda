<?php

namespace sysfact\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use sysfact\Cliente;
use sysfact\Emisor;
use sysfact\Http\Controllers\Helpers\MainHelper;
use sysfact\Http\Controllers\Helpers\PdfHelper;
use sysfact\Presupuesto;
use sysfact\Venta;

class ConsultaController extends Controller
{
    public function index()
    {
        return view('consulta/index');
    }

    public function obtener_documento(Request $request)
    {

        $correlativo = str_pad($request->correlativo, 8, '0', STR_PAD_LEFT);

        $venta = Venta::where('total_venta', $request->total)
            ->whereHas('facturacion', function ($query) use ($request, $correlativo) {
                $query->where('serie', $request->serie)
                    ->where('correlativo', $correlativo)
                    ->where('codigo_tipo_documento', $request->tipo_documento)
                    ->where('fecha', 'LIKE', $request->fecha . '%');
            })
            ->first();

        $emisor = new Emisor();
        if ($venta) {
            $nombre_fichero = $emisor->ruc . '-' . $venta['facturacion']['codigo_tipo_documento'] . '-' . $venta['facturacion']['serie'] . '-' . $venta['facturacion']['correlativo'];
            return json_encode(['mostrar' => 1, 'nombre_fichero' => $nombre_fichero, 'idventa' => $venta->idventa]);
        } else {
            return json_encode(['mostrar' => 0, 'nombre_fichero' => '', 'idventa' => -1]);
        }

    }

    public function descargarArchivo($file_or_id)
    {
        try {
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

            return null;

        } catch (\Exception $e) {
            Log::error($e);
            return $e->getMessage();
        }

    }

    public function descargar_comprobante($tipo, $idventa)
    {
        $venta = Venta::with('facturacion')->findOrFail($idventa);

        $fact = $venta->facturacion;
        $emisor = new Emisor();

        $nombre_fichero = $emisor->ruc . '-' . $fact->codigo_tipo_documento . '-' . $fact->serie . '-' . $fact->correlativo;
        if ($tipo === 'cdr') {
            $nombre_fichero = 'R-' . $nombre_fichero;
        }

        switch ($tipo) {
            case 'pdf':
                return PdfHelper::generarPdf($venta->idventa, false, 'D');
            case 'xml':
            case 'cdr':
                $path = storage_path("app/sunat/{$tipo}/{$nombre_fichero}.xml");
                if (!file_exists($path)) {
                    abort(404, strtoupper($tipo) . ' no encontrado.');
                }
                return response()->download($path);
            default:
                abort(400, 'Tipo de archivo no permitido.');
        }
    }

    public function descargar_cotizacion($idcotizacion)
    {
        $presupuestoController = new PresupuestoController();
        $pdf = $presupuestoController->generarPdf($idcotizacion);
        return $pdf['file']->output($pdf['name'],'D');
    }


    public function verCredito(Request $request, $tocken)
    {

        $cliente = Cliente::where('tocken', $tocken)->first();

        if (!$cliente) {
            return 'La ruta no existe';
        }

        try {

            $orderby = $request->get('orderby', 'ventas.idventa');
            $order = $request->get('order', 'desc');
            $mostrar = $request->get('mostrar', 'ventas');

            if ($mostrar == 'productos') {
                $ventas = DB::table('ventas_detalle')
                    ->join('ventas', 'ventas.idventa', '=', 'ventas_detalle.idventa')
                    ->join('cliente', 'cliente.idcliente', '=', 'ventas.idcliente')
                    ->join('facturacion', 'facturacion.idventa', '=', 'ventas.idventa')
                    ->join('productos', 'productos.idproducto', '=', 'ventas_detalle.idproducto')
                    ->join('pagos', 'pagos.idventa', '=', 'ventas.idventa')
                    ->select('ventas.idventa', 'ventas.fecha', 'ventas_detalle.monto', 'cantidad', 'serie', 'correlativo', 'codigo_moneda', 'productos.nombre as producto', 'descripcion')
                    ->where('cliente.tocken', $tocken)
                    ->where(function ($query) {
                        $query->whereIn('codigo_tipo_documento', [01, 03, 30])
                            ->where(function ($query) {
                                $query->where('facturacion.estado', 'ACEPTADO')
                                    ->orWhere('facturacion.estado', 'PENDIENTE')
                                    ->orWhere('facturacion.estado', '-');
                            });
                    })
                    ->where('ventas.eliminado', 0)
                    ->where('tipo_pago', 2)
                    ->where('pagos.estado', 1)
                    ->orderby($orderby, $order)
                    ->paginate(30);
            } else {
                $ventas = Venta::join('cliente', 'cliente.idcliente', '=', 'ventas.idcliente')
                    ->select('ventas.*', 'cliente.tocken')
                    ->where('ventas.eliminado', 0)
                    ->where('tipo_pago', 2)
                    ->where('cliente.tocken', $tocken)
                    ->whereHas('facturacion', function ($query) {
                        $query->whereIn('codigo_tipo_documento', [01, 03, 30])
                            ->where(function ($query) {
                                $query->where('estado', 'ACEPTADO')
                                    ->orWhere('estado', 'PENDIENTE')
                                    ->orWhere('estado', '-');
                            });
                    })
                    ->whereHas('pago', function ($query) {
                        $query->where('estado', 1);
                    })
                    ->orderby($orderby, $order)
                    ->paginate(30);
            }

            $ventas->appends($_GET)->links();

            $cliente = $cliente->persona->nombre . ' ' . $cliente->persona->apellidos;

            return view('creditos.clientes', [
                'creditos' => $ventas,
                'cliente' => $cliente,
                'mostrar' => $mostrar,
                'order' => $order == 'desc' ? 'asc' : 'desc',
                'orderby' => $orderby,
                'order_icon' => $order == 'desc' ? '<i class="fas fa-caret-square-up"></i>' : '<i class="fas fa-caret-square-down"></i>'
            ]);

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getBadget($tocken)
    {

        $cliente = Cliente::where('tocken', $tocken)->first();

        if (!$cliente) {
            return 'La ruta no existe';
        }

        try {

            $ventas = Venta::join('cliente', 'cliente.idcliente', '=', 'ventas.idcliente')
                ->where('cliente.tocken', $tocken)
                ->where('ventas.eliminado', 0)
                ->where('tipo_pago', 2)
                ->whereHas('facturacion', function ($query) {
                    $query->whereIn('codigo_tipo_documento', [01, 03, 30])
                        ->where(function ($query) {
                            $query->where('estado', 'ACEPTADO')
                                ->orWhere('estado', 'PENDIENTE')
                                ->orWhere('estado', '-');
                        });
                })
                ->get();

            $totales = [
                'total_credito' => 0,
                'adeuda' => 0,
                'pagado' => 0,
            ];

            foreach ($ventas as $item) {
                $cuotas = $item->pago;
                foreach ($cuotas as $pago) {
                    $totales['total_credito'] += $pago->monto;
                    if ($pago->estado == 1) {
                        $totales['adeuda'] += $pago->monto;
                    }
                }
            }

            return $totales;

        } catch (\Exception $e) {
            Log::info($e);
            return $e->getMessage();
        }
    }

}
