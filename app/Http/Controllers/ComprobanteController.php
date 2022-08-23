<?php
/**
 * Created by PhpStorm.
 * User: Luciano
 * Date: 28/03/2020
 * Time: 10:32
 */

namespace sysfact\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use sysfact\Emisor;
use sysfact\Facturacion;
use sysfact\Http\Controllers\Cpe\CpeController;
use sysfact\Http\Controllers\Helpers\DataTipoPago;
use sysfact\Http\Controllers\Helpers\MainHelper;
use sysfact\Resumen;
use sysfact\Serie;
use sysfact\Venta;

class ComprobanteController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function comprobantes(Request $request, $desde=null,$hasta=null){

        $filtro = $request->filtro;
        $buscar = $request->buscar;

        if(!$filtro){
            $filtro='fecha';
            $desde=date('Y-m-d');
            $hasta=date('Y-m-d');
        }

        $ventas=$this->comprobantes_data($desde, $hasta, $filtro, $buscar);

        if(json_decode(MainHelper::configuracion('interfaz_pedidos'),true)['solo_comprobantes']){
            $ventas = $this->comprobantes_data_sin_recibos($desde, $hasta, $filtro, $buscar);
        }

        return view('comprobantes.index',$ventas);
    }

    public function comprobantes_data($desde, $hasta, $filtro, $buscar){

        try{

            $ventas=null;
            $filtros = ['desde' => $desde, 'hasta' => $hasta, 'filtro'=>$filtro,'buscar'=>$buscar];

            switch ($filtro){
                case 'fecha':
                    $ventas=Venta::whereBetween('fecha',[$desde.' 00:00:00',$hasta.' 23:59:59'])
                        ->where('eliminado','=',0)
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

                    $ventas=Venta::whereBetween('fecha',[$desde.' 00:00:00',$hasta.' 23:59:59'])
                        ->where('eliminado','=',0)
                        ->orderby('idventa','desc')
                        ->whereHas('facturacion', function($query) use($filtro,$buscar){
                            $query->where($filtro, $buscar);
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
                    $ventas=Venta::whereBetween('fecha',[$desde.' 00:00:00',$hasta.' 23:59:59'])
                        ->where('eliminado','=',0)
                        ->where($filtro,$buscar)
                        ->orderby('idventa','desc')
                        ->paginate(30);

                    break;
                case 'cliente':
                    $filtro = 'nombre';
                    $ventas=Venta::whereBetween('fecha',[$desde.' 00:00:00',$hasta.' 23:59:59'])
                        ->where('eliminado','=',0)
                        ->orderby('idventa','desc')
                        ->whereHas('persona', function($query) use($filtro,$buscar){
                            $query->where($filtro,'LIKE', '%'.$buscar.'%');
                        })
                        ->paginate(30);
                    break;

            }

            foreach ($ventas as $item){
                $item->facturacion;
                $item->cliente->persona;
                $emisor=new Emisor();
                $item->nombre_fichero=$emisor->ruc.'-'.$item->facturacion['codigo_tipo_documento'].'-'.$item->facturacion['serie'].'-'.$item->facturacion['correlativo'];

                $pago = DataTipoPago::getTipoPago();
                $find = array_search($item->tipo_pago, array_column($pago,'num_val'));
                $item->tipo_pago = mb_strtoupper($pago[$find]['label']);

                switch ($item->facturacion->estado){
                    case 'PENDIENTE':
                        $item->badge_class='badge-warning';
                        break;
                    case 'ACEPTADO':
                        $item->badge_class='badge-success';
                        break;
                    case 'ANULADO':
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

            }

            $ventas->appends($_GET)->links();

            return ['usuario' => auth()->user()->persona,'ventas'=>$ventas,'filtros'=>$filtros];

        } catch (\Exception $e){
            Log::error($e);
            return $e->getMessage();
        }

    }

    public function comprobantes_data_sin_recibos($desde, $hasta, $filtro, $buscar){

        try{

            $ventas=null;
            $filtros = ['desde' => $desde, 'hasta' => $hasta, 'filtro'=>$filtro,'buscar'=>$buscar];

            switch ($filtro){
                case 'fecha':
                    $ventas=Venta::whereBetween('fecha',[$desde.' 00:00:00',$hasta.' 23:59:59'])
                        ->where('eliminado','=',0)
                        ->whereHas('facturacion', function ($query){
                            $query->where('codigo_tipo_documento','!=','30');
                        })
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

                    $ventas=Venta::whereBetween('fecha',[$desde.' 00:00:00',$hasta.' 23:59:59'])
                        ->where('eliminado','=',0)
                        ->whereHas('facturacion', function ($query){
                            $query->where('codigo_tipo_documento','!=','30');
                        })
                        ->orderby('idventa','desc')
                        ->whereHas('facturacion', function($query) use($filtro,$buscar){
                            $query->where($filtro, $buscar);
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
                    $ventas=Venta::whereBetween('fecha',[$desde.' 00:00:00',$hasta.' 23:59:59'])
                        ->where('eliminado','=',0)
                        ->whereHas('facturacion', function ($query){
                            $query->where('codigo_tipo_documento','!=','30');
                        })
                        ->where($filtro,$buscar)
                        ->orderby('idventa','desc')
                        ->paginate(30);

                    break;
                case 'cliente':
                    $filtro = 'nombre';
                    $ventas=Venta::whereBetween('fecha',[$desde.' 00:00:00',$hasta.' 23:59:59'])
                        ->where('eliminado','=',0)
                        ->whereHas('facturacion', function ($query){
                            $query->where('codigo_tipo_documento','!=','30');
                        })
                        ->orderby('idventa','desc')
                        ->whereHas('persona', function($query) use($filtro,$buscar){
                            $query->where($filtro,'LIKE', '%'.$buscar.'%');
                        })
                        ->paginate(30);
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
                        $item->tipo_pago='TARJETA';
                        break;
                    default:
                        $item->tipo_pago='OTROS';
                }

                switch ($item->facturacion->estado){
                    case 'PENDIENTE':
                        $item->badge_class='badge-warning';
                        break;
                    case 'ACEPTADO':
                        $item->badge_class='badge-success';
                        break;
                    case 'ANULADO':
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

        $cpe = new CpeController();
        $rpta = $cpe->sendVoided($request);

        //Actualizar pedido

        $items = json_decode($request->items,TRUE);

        foreach ($items as $item){
            $venta=Venta::find($item['idventa']);
            $venta->orden()->update([
                'estado'=>'VENTA ANULADA'
            ]);
        }

        return $rpta;
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