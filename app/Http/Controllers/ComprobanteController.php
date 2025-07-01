<?php
/**
 * Created by PhpStorm.
 * User: Luciano
 * Date: 28/03/2020
 * Time: 10:32
 */

namespace sysfact\Http\Controllers;


use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use sysfact\Emisor;
use sysfact\Facturacion;
use sysfact\Guia;
use sysfact\Http\Controllers\Cpe\CpeController;
use sysfact\Http\Controllers\Helpers\DataTipoPago;
use sysfact\Http\Controllers\Helpers\MainHelper;
use sysfact\Resumen;
use sysfact\Serie;
use sysfact\Venta;

class ComprobanteController extends Controller
{

    private $solo_comprobantes;
    private $hora_inicio;
    private $hora_fin;

    public function __construct()
    {
        $this->middleware('auth');
        $this->hora_inicio = (json_decode(cache('config')['interfaz'], true)['atencion_inicio']??'00:00').':00';
        $this->hora_fin = (json_decode(cache('config')['interfaz'], true)['atencion_fin']??'23:59').':59';
    }

    public function getHasta($hasta){
        if($this->hora_fin < $this->hora_inicio){
            $fecha = new Carbon($hasta);
            $fecha->addDays(1);
            return $fecha->format('Y-m-d');
        }
        return $hasta;
    }

    public function func_filter($query){
        if($this->solo_comprobantes) {
            $query->where('codigo_tipo_documento','!=','30');
        }

        $modulo_nota_venta = json_decode(cache('config')['interfaz'], true)['modulo_nota_de_venta']??false;

        if($modulo_nota_venta) {
            $query->where('codigo_tipo_documento','!=','30');
        }
    }

    public function comprobantes(Request $request, $desde=null,$hasta=null){

        $filtro = $request->filtro;
        $buscar = $request->buscar;
        $notasDeVenta = filter_var($request->notasDeVenta, FILTER_VALIDATE_BOOLEAN);

        if(!$filtro){
            $filtro='fecha';
            $desde=date('Y-m-d');
            $hasta=date('Y-m-d');
        }

        if($notasDeVenta){
            $filtro='documento';
            $buscar='recibo';
            $desde='2017-01-01';
            $hasta=date('Y-m-d');

        }

        if ($notasDeVenta && $texto = trim($request->textoBuscado)) {

            $ventas = Venta::where('eliminado', 0)
                ->whereHas('facturacion', function ($q) use ($filtro, $buscar) {
                    $q->where('codigo_tipo_documento','30');
                })
                ->where(function ($q) use ($texto) {
                    $q->whereHas('persona', function ($q2) use ($texto) {
                        $q2->where('nombre', 'like', "%{$texto}%");
                    })
                        ->orWhereHas('facturacion', function ($q3) use ($texto) {
                            $q3->where('correlativo', 'like', "%{$texto}%");
                        });
                })
                ->orderByDesc('fecha')
                ->orderByDesc('idventa')
                ->paginate(30)
                ->appends(request()->query());

            $ventas = [
                'usuario' => auth()->user()->persona,
                'ventas'=>$ventas,
                'filtros'=>null,
                'textoBuscado'=>$texto,
            ];

        } else {
            $ventas=$this->comprobantes_data($desde, $hasta, $filtro, $buscar);
            $ventas['textoBuscado'] = "";
        }



        return view('comprobantes.index',$ventas);
    }

    public function comprobantes_data($desde, $hasta, $filtro, $buscar){

        $this->solo_comprobantes = json_decode(cache('config')['interfaz_pedidos'], true)['solo_comprobantes']??false;

        try{

            $ventas=null;
            $filtros = ['desde' => $desde, 'hasta' => $hasta, 'filtro'=>$filtro,'buscar'=>$buscar];

            switch ($filtro){
                case 'fecha':
                    $ventas = Venta::whereBetween('fecha', [$desde.' '.$this->hora_inicio, $this->getHasta($hasta).' '.$this->hora_fin])
                        ->where('eliminado','=',0)
                        ->whereHas('facturacion', function($query) {
                            $this->func_filter($query);
                        })
                        ->orderby('fecha','desc')
                        ->orderby('idventa','desc')
                        ->paginate(30);
                    break;
                case 'documento':
                case 'moneda':
                case 'estado':
                    switch ($filtro){
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

                $ventas = Venta::whereBetween('fecha', [$desde.' '.$this->hora_inicio, $this->getHasta($hasta).' '.$this->hora_fin])
                        ->where('eliminado','=',0)
                        ->orderby('fecha','desc')
                        ->orderby('idventa','desc')
                        ->whereHas('facturacion', function($query) use($filtro,$buscar){
                            $query->where($filtro, $buscar);
                        })
                        ->paginate(30);


                    break;

                case 'tipo-de-pago':
                    $pago = DataTipoPago::getTipoPago();
                    $find = array_search($buscar, array_column($pago,'text_val'));
                    $buscar = $pago[$find]['num_val'];
                    $filtro = 'tipo_pago';

                    $ventas = Venta::whereBetween('fecha', [$desde.' '.$this->hora_inicio, $this->getHasta($hasta).' '.$this->hora_fin])
                        ->where('eliminado', 0)
                        ->whereHas('pago', function($query) use ($buscar){
                            $query->where('tipo',$buscar);
                        })
                        ->orderby('fecha','desc')
                        ->orderby('idventa','desc')
                        ->paginate(30);

                    break;
                case 'cliente':
                    $filtro = 'nombre';
                    $ventas = Venta::whereBetween('fecha', [$desde.' '.$this->hora_inicio, $this->getHasta($hasta).' '.$this->hora_fin])
                        ->where('eliminado','=',0)
                        ->orderby('fecha','desc')
                        ->orderby('idventa','desc')
                        ->whereHas('persona', function($query) use($filtro,$buscar){
                            $query->where($filtro,'LIKE', '%'.$buscar.'%');
                        })
                        ->whereHas('facturacion', function($query) {
                            $this->func_filter($query);
                        })
                        ->paginate(30);
                    break;

            }

            foreach ($ventas as $item){
                $item->facturacion;
                $item->cliente->persona;
                $emisor=new Emisor();
                $item->nombre_fichero=$emisor->ruc.'-'.$item->facturacion['codigo_tipo_documento'].'-'.$item->facturacion['serie'].'-'.$item->facturacion['correlativo'];

                $tipo_pago = DataTipoPago::getTipoPago();
                if($item->tipo_pago == 4 && $filtro == 'tipo-de-pago') {
                    if($item->facturacion->codigo_moneda=='PEN'){
                        $suma_soles = 0;
                        foreach ($item->pago as $pago){
                            $index = array_search($pago->tipo, array_column($tipo_pago,'num_val'));
                            if($filtros['buscar'] == $tipo_pago[$index]['text_val']){
                                $suma_soles += $pago->monto;
                            }
                        }
                        $item->total_venta_aux = $suma_soles;
                    }
                }

                switch ($item->facturacion->estado){
                    case 'PENDIENTE':
                        $item->badge_class='badge-warning';
                        break;
                    case 'ACEPTADO':
                        $item->badge_class='badge-success';
                        break;
                    case 'ANULADO':
                    case 'ANULADO (BAJA)':
                    case 'MODIFICADO':
                        $item->badge_class='badge-dark';
                        break;
                    case 'RECHAZADO':
                        $item->badge_class='badge-danger';
                }

                switch ($item->facturacion->codigo_tipo_documento){
                    case 01:
                        $item->badge_class_documento='badge-warning';
                        break;
                    case 03:
                        $item->badge_class_documento='badge-success';
                        break;
                    case 07:
                    case '08':
                        $item->badge_class_documento='badge-danger';
                        break;
                    case 30:
                        $item->badge_class_documento='badge-secondary';
                        break;
                }

                $item->guia_relacionada=$item->guia->first();

                //inicio código para version antigua del sistema tabla guia
                if(!$item->guia_relacionada){
                    $item->guia_relacionada = Guia::where('correlativo',$item->facturacion->guia_relacionada)->first();
                    if(!$item->guia_relacionada){
                        $correlativo = $item->facturacion->guia_relacionada;
                        if($correlativo){
                            $item->guia_relacionada = ['correlativo'=>$correlativo,'estado'=>$item->facturacion->estado_guia];
                        } else{
                            $item->guia_relacionada=false;
                        }
                    }
                }
                //fin código para version antigua del sistema tabla guia

                switch ($item->guia_relacionada['estado']){
                    case 'PENDIENTE':
                        $item->badge_class_guia='badge-warning';
                        break;
                    case 'ACEPTADO':
                        $item->badge_class_guia='badge-success';
                        break;
                    case 'ANULADO':
                        $item->badge_class_guia='badge-dark';
                        break;
                    case 'RECHAZADO':
                        $item->badge_class_guia='badge-danger';
                }

                //pago credito
                $suma_cuotas = 0;
                $cuotas = $item->pago;
                foreach ($cuotas as $pago){
                    if($pago->estado == 2){
                        $suma_cuotas +=  $pago->monto;
                    }
                    if($suma_cuotas >= $item->total_venta){
                        if($item->facturacion->codigo_tipo_documento != '07' || $item->facturacion->codigo_tipo_documento != '08'){
                            $item->estado_credito = 'PAGADO';
                        }
                    }
                }


            }

            $ventas->appends($_GET)->links();

            return ['usuario' => auth()->user()->persona,'ventas'=>$ventas,'filtros'=>$filtros];

        } catch (\Exception $e){
            Log::error($e);
            return $e->getMessage();
        }

    }

    public function consulta()
    {
        $serie = new Serie();
        $serie_comprobates = $serie->getSeries();
        return view('comprobantes.consulta', [
            'usuario' => auth()->user()->persona,
            'serie_comprobantes'=>$serie_comprobates
        ]);
    }

    public function anular()
    {
        return view('comprobantes.anulacion', ['usuario' => auth()->user()->persona]);
    }

    public function obtenerComprobantes(Request $request){

        try{

            $ventas=null;
            $fecha_in=$request->fecha_in;
            $fecha_out=$request->fecha_out;

            switch ($request->tipo_comprobante){

                case -1:

                    $ventas=Venta::whereBetween('fecha',[$request->fecha_in.' 00:00:00',$request->fecha_out.' 23:59:59'])
                        ->where('eliminado','=',0)
                        ->orderby('idventa','desc')
                        ->whereHas('facturacion', function($query) use($request) {
                            $query->where('codigo_tipo_documento', '!=',20);
                        })
                        ->paginate(25);
                    break;

                case '01':
                case '03':
                case '07':
                case '08':
                case '20':
                case '30':
                    $ventas=Venta::with('facturacion')
                        ->whereBetween('fecha',[$fecha_in.' 00:00:00',$fecha_out.' 23:59:59'])
                        ->where('eliminado','=',0)
                        ->whereHas('facturacion', function($query) use($request) {
                            $query->where('codigo_tipo_documento', '=',$request->tipo_comprobante);
                        })
                        ->orderby('idventa','desc')
                        ->paginate(25);
                    break;

                case '10':
                    $fecha_out=$request->fecha_in;
                    $ventas=Venta::with('facturacion')
                        ->whereBetween('fecha',[$fecha_in.' 00:00:00',$fecha_out.' 23:59:59'])
                        ->where('eliminado','=',0)
                        ->whereHas('facturacion', function($query) use($request) {
                            $query->where('serie', 'LIKE','B%')->where('estado','PENDIENTE');
                        })
                        ->orderby('idventa','desc')
                        ->paginate(100);
                    break;

                case '40':
                    $fecha_out=$request->fecha_in;
                    $ventas=Venta::whereBetween('fecha',[$request->fecha_in.' 00:00:00',$request->fecha_out.' 23:59:59'])
                        ->where('eliminado','=',0)
                        ->whereHas('facturacion', function($query) use($request) {
                            $query->where('serie', 'LIKE','F%')->where('estado','ACEPTADO');
                        })
                        ->orderby('idventa','desc')
                        ->paginate(100);
                    break;
                case '50':
                    $fecha_out=$request->fecha_in;
                    $ventas=Venta::with('facturacion')
                        ->whereBetween('fecha',[$fecha_in.' 00:00:00',$fecha_out.' 23:59:59'])
                        ->where('eliminado','=',0)
                        ->whereHas('facturacion', function($query) use($request) {
                            $query->where('serie', 'LIKE','B%');
                        })
                        ->orderby('idventa','desc')
                        ->paginate(100);
                    break;
            }

            foreach ($ventas as $item){
                $item->facturacion;
                $item->cliente->persona;
                $emisor=new Emisor();
                $item->nombre_fichero=$emisor->ruc.'-'.$item->facturacion['codigo_tipo_documento'].'-'.$item->facturacion['serie'].'-'.$item->facturacion['correlativo'];
                switch ($item->tipo_pago){
                    case 1:
                        $item->tipo_pago='EFECTIVO';
                        break;
                    case 2:
                        $item->tipo_pago='CRÉDITO';
                        break;
                    case 3:
                        $item->tipo_pago='DEPÓSITO';
                        break;
                    default:
                        $item->tipo_pago='OTROS';
                }
            }

            return $ventas;

        } catch (\Exception $e){
            return $e;
        }

    }

    public function resumenes_enviados(){
        return view('comprobantes.resumenes', ['usuario' => auth()->user()->persona]);
    }

    public function obtener_resumen(Request $request){

        try{
            $fecha_in=$request->fecha_in;
            $fecha_out=$request->fecha_out;

            if($request->tipo_resumen=='diario'){
                $tipo='RESUMEN';
            } else{
                $tipo='BAJA';
            }

            $resumenes = Resumen::where('tipo','LIKE',$tipo.'%')
                ->whereBetween('fecha_generacion',[$fecha_in.' 00:00:00',$fecha_out.' 23:59:59'])
                ->paginate(25);
            $emisor=new Emisor();

            foreach ($resumenes as $resumen){
                $siglas_resumen='-RC-';
                if($resumen->lote==null){
                    $resumen->lote=$resumen->lote_baja;
                    $siglas_resumen='-RA-';
                }
                $resumen->nombre=$emisor->ruc.$siglas_resumen.date('Ymd',strtotime($resumen->fecha_generacion)).'-'.$resumen->lote;
                if($resumen->tipo=='RESUMEN'){
                    $resumen->tipo='ADICIÓN DE BOLETAS';
                } else if($resumen->tipo=='RESUMEN-BAJA'){
                    $resumen->tipo='ANULACIÓN DE BOLETAS';
                } else{
                    $resumen->tipo='BAJA DE FACTURAS';
                }
            }

            return json_encode($resumenes);

        } catch (\Exception $e){
            return $e;
        }
    }

    public function detalle_resumen($idresumen){
        $ventas=Facturacion::where('idresumen',$idresumen)
            ->orderby('idventa','desc')
            ->get();
        return json_encode($ventas);
    }

    public function anular_facturas(Request $request){

        try{
            $cpe = new CpeController();
            $rpta = $cpe->sendVoided($request);

            $items = json_decode($request->items,TRUE);

            foreach ($items as $item) {
                $venta = Venta::find($item['idventa']);
                $facturacion = $venta->facturacion;

                if ($facturacion->codigo_tipo_documento === '07' && $facturacion->num_doc_relacionado) {
                    // Separar serie y correlativo desde el num_doc_relacionado (ej. F001-00012345)
                    [$serie, $correlativo] = explode('-', $facturacion->num_doc_relacionado);

                    $factura_original = Facturacion::where('serie', $serie)
                        ->where('correlativo', $correlativo)
                        ->where('codigo_tipo_documento', '!=', '07') // evitar otra NC
                        ->first();

                    if ($factura_original) {
                        $factura_original->estado = 'ACEPTADO';
                        $factura_original->save();
                        Log::info("Documento relacionado actualizado a ACEPTADO: $serie-$correlativo");
                    } else {
                        Log::warning("No se encontró documento relacionado: $serie-$correlativo");
                    }
                }

                // Anular orden si existe
                $venta->orden()->update(['estado' => 'VENTA ANULADA']);
            }

            return $rpta;

        } catch (\Exception $e){
            Log::error($e);
            return $e->getMessage();
        }

    }

    public function anular_boletas(Request $request){
        $cpe = new CpeController();
        $rpta = $cpe->sendSummaryVoided($request);

        $items = json_decode($request->items,TRUE);

        //Actualizar pedido
        foreach ($items as $item){
            $venta=Venta::find($item['idventa']);
            $venta->orden()->update([
                'estado'=>'VENTA ANULADA'
            ]);
        }

        return $rpta;
    }

    public function temp_update_serie(){
        try{
            DB::beginTransaction();
            $ventas = Venta::all();
            foreach ($ventas as $venta) {
                if($venta->facturacion->serie == 'REC'){
                    $explode = explode('-',$venta->ticket);
                    $facturacion = Facturacion::find($venta->idventa);
                    $facturacion->serie = 'TIC';
                    $facturacion->correlativo = str_pad($explode[1], 8, '0', STR_PAD_LEFT);
                    $facturacion->save();
                }
            }
            DB::commit();
            return 'Éxito';
        } catch (\Exception $e){
            DB::rollBack();
            return $e->getMessage();
        }

    }

}