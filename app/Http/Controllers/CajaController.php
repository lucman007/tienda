<?php

namespace sysfact\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Spipu\Html2Pdf\Html2Pdf;
use sysfact\Caja;
use sysfact\Gastos;
use sysfact\Http\Controllers\Helpers\DataTipoPago;
use sysfact\Inventario;
use sysfact\Mail\MovimientoCaja;
use sysfact\Venta;

class CajaController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request, $fecha=null){

        $fecha=$fecha??date('Y-m-d');
        $turno = $request->get('turno')??1;
        $total_caja = ['total_cierre'=>0,'total_ventas'=>0];

        $caja_abierta = null;

        $caja=Caja::where('fecha_a','LIKE',$fecha.'%')
            ->where('turno',$turno)
            ->orderby('fecha_a','desc')
            ->first();

        if($caja){
            $caja->email = json_decode(cache('config')['mail_contact'], true)['notificacion_caja'];
            $caja->moneda = 'S/';
            $rq = new Request();
            $rq->idcaja = $caja->idcaja;
            $total_caja = $this->obtener_datos_cierre($rq);
        }

        if($fecha == date('Y-m-d')){
            $caja_abierta=Caja::where('estado',0)
                ->orderby('fecha_a','desc')
                ->first();
        }

        $cajas_del_dia = Caja::where('fecha_a','LIKE',$fecha.'%')
            ->get();

        return view('caja.index',['usuario'=>auth()->user()->persona],
            [
                'caja'=>$caja,
                'caja_abierta'=>$caja_abierta,
                'cajas'=>$cajas_del_dia,
                'fecha'=>$fecha,
                'total_caja'=>$total_caja['total_cierre'],
                'total_ventas'=>$total_caja['total_ventas']
            ]);
    }

    public function abrir_caja(Request $request)
    {
        try {

            $cajaAbierta = Caja::where('estado', 0)
                ->whereDate('fecha_a', date('Y-m-d'))
                ->first();

            if ($cajaAbierta) {
                return response()->json(['message' => 'Existe una caja abierta en el día actual.'], 400);
            }

            if (strtotime($request->fecha) < strtotime(date('Y-m-d'))) {
                return response()->json(['message' => 'No se puede abrir una caja con una fecha anterior a la actual.'], 400);
            }

            $caja = new Caja();
            $caja->idempleado = auth()->user()->idempleado;
            $caja->apertura = $request->apertura;
            $caja->observacion_a = $request->observacion_a;
            $caja->fecha_a = date('Y-m-d H:i:s');
            $caja->estado = 0;
            $caja->turno = $request->turno;
            $success = $caja->save();
            $idcaja = $caja->idcaja;

            if ($success) {
                Cache::forever('caja_abierta', $idcaja);
                if ($request->notificacion && json_decode(cache('config')['mail_contact'], true)['notificacion_caja']) {
                    $email = json_decode(cache('config')['mail_contact'], true)['notificacion_caja'];
                    Mail::to($email)->send(new MovimientoCaja($caja));
                }
                return 1;
            }

            return response()->json(['message' => 'Error al abrir la caja.'], 500);

        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['message' => $e->getMessage()], 500);
        }

    }

    public function procesar_cierre(Request $request){
        try{
            $idcaja = $request->idcaja;
            $caja=Caja::find($idcaja);
            $caja->efectivo_teorico=$request->efectivo_teorico;
            $caja->efectivo=$request->efectivo;
            $caja->tarjeta=$request->visa;
            $caja->tarjeta_1=$request->mastercard;
            $caja->plin=$request->plin;
            $caja->yape=$request->yape;
            $caja->transferencia=$request->transferencia;
            $caja->devoluciones=$request->devoluciones;
            $caja->credito=$request->credito;
            $caja->credito_usd=$request->credito_usd;
            $caja->efectivo_usd=$request->efectivo_usd;
            $caja->extras=$request->extras;
            $caja->gastos=$request->gastos;
            $caja->otros=$request->otros;
            $caja->efectivo_real=$request->efectivo_real;
            $caja->observacion_c=$request->observacion_c;
            $caja->descuadre=$request->descuadre;
            $caja->fecha_c=date('Y-m-d H:i:s');
            $caja->estado=1;
            $success = $caja->save();

            if($success){

                Cache::put('caja_abierta','idcaja',0);
                if($request->notificacion && json_decode(cache('config')['mail_contact'], true)['notificacion_caja']){

                    //PDF RESUMEN VENTAS CAJA
                    $resumen_ventas = $this->resumen_ventas($idcaja);
                    $view = view('mail/reporte_caja', $resumen_ventas);
                    $html = $view->render();

                    $pdf=new Html2Pdf('P','A4','es');
                    $pdf->pdf->SetTitle('REPORTE CAJA');
                    $pdf->writeHTML($html);
                    $pdf->output(public_path().'/pdf/resumen_ventas.pdf', 'F');

                    //PDF CIERRE CAJA

                    $view = view('mail/cierre_caja', ['caja'=>$caja]);
                    $html = $view->render();

                    $pdf=new Html2Pdf('P','A4','es');
                    $pdf->pdf->SetTitle('CIERRE CAJA');
                    $pdf->writeHTML($html);
                    $pdf->output(public_path().'/pdf/cierre_caja.pdf', 'F');

                    //RESUMEN DE PRODUCTOS

                    $productos = $this->resumen_productos($idcaja);
                    $view = view('mail/reporte_productos', ['productos'=>$productos]);
                    $html = $view->render();

                    $pdf=new Html2Pdf('P','A4','es');
                    $pdf->pdf->SetTitle('PRODUCTOS VENDIDOS');
                    $pdf->writeHTML($html);
                    $pdf->output(public_path().'/pdf/reporte_productos.pdf', 'F');

                    $email = json_decode(cache('config')['mail_contact'], true)['notificacion_caja'];
                    Mail::to($email)->send(new MovimientoCaja($caja));

                    if(file_exists(public_path().'/pdf/cierre_caja.pdf')){
                        unlink(public_path().'/pdf/cierre_caja.pdf');
                    }
                    if(file_exists(public_path().'/pdf/resumen_ventas.pdf')){
                        unlink(public_path().'/pdf/resumen_ventas.pdf');
                    }
                    if(file_exists(public_path().'/pdf/reporte_productos.pdf')){
                        unlink(public_path().'/pdf/reporte_productos.pdf');
                    }
                }

                return 1;
            }

            return 0;

        } catch (\Exception $e){
            Log::error($e);
            return $e->getMessage();
        }

    }

    public function resumen_ventas($idcaja){
        $ventas = Venta::where('idcaja',$idcaja)
            ->where('eliminado',0)
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
            ->orderby('fecha','asc')
            ->get();

        $suma = 0;

        foreach ($ventas as $item){
            $pago = DataTipoPago::getTipoPago();
            $find = array_search($item->tipo_pago, array_column($pago,'num_val'));
            $item->tipo_pago = mb_strtoupper($pago[$find]['label']);
            $suma += $item->total_venta;
        }

        return ['ventas'=>$ventas, 'total'=> $suma];
    }

    public function resumen_productos($idcaja){
        $productos=$ventas = DB::table('ventas')
            ->join('ventas_detalle', 'ventas_detalle.idventa', '=', 'ventas.idventa')
            ->join('productos', 'productos.idproducto', '=', 'ventas_detalle.idproducto')
            ->join('facturacion', 'ventas.idventa', '=', 'facturacion.idventa')
            ->selectRaw('sum(ventas_detalle.cantidad) as vendidos,sum(ventas_detalle.monto * ventas_detalle.cantidad) as monto_total,ventas_detalle.idproducto, productos.nombre, productos.precio, productos.tipo_producto, facturacion.codigo_tipo_documento, facturacion.estado')
            ->where('ventas.eliminado', 0)
            ->where('ventas.idcaja', $idcaja)
            ->where(function($query) {
                $query->where(function ($query) {
                    $query->where('facturacion.codigo_tipo_documento',01)
                        ->orWhere('facturacion.codigo_tipo_documento',03)
                        ->orWhere('facturacion.codigo_tipo_documento',30);
                })
                    ->where(function ($query){
                        $query->where('facturacion.estado','ACEPTADO')
                            ->orWhere('facturacion.estado','PENDIENTE');
                    })
                    ->orWhere('facturacion.estado','-');
            })
            ->groupBy('ventas_detalle.idproducto')
            ->orderby('vendidos','desc')
            ->get();

        return $productos;
    }

    public function ver_cache(){
        dd(Cache::get('caja_abierta'));
    }

    public function editar_caja($id){
        try{
            $caja=Caja::find($id);
            return response()->json($caja);

        } catch (\Exception $e){
            Log::error($e);
            return $e->getMessage();
        }

    }

    public function update(Request $request){
        try{
            $caja=Caja::find($request->idcaja);
            $caja->idempleado=auth()->user()->idempleado;
            $caja->apertura=$request->apertura;
            $caja->observacion_a=$request->observacion_a;
            $success = $caja->save();

            if($success){
                if($request->notificacion && json_decode(cache('config')['mail_contact'], true)['notificacion_caja']){
                    $email = json_decode(cache('config')['mail_contact'], true)['notificacion_caja'];
                    Mail::to($email)->send(new MovimientoCaja($caja));
                }
                return 1;
            }

            return 0;

        } catch (\Exception $e){
            Log::error($e);
            return $e->getMessage();
        }

    }

    public function obtener_datos_cierre(Request $request)
    {
        $idcaja = $request->idcaja;

        $ventas = Venta::with("pago")
            ->where('eliminado', 0)
            ->where('idcaja', $idcaja)
            ->whereHas('facturacion', function($query) {
                $query->whereIn('codigo_tipo_documento', [01, 03, 30])
                    ->where(function ($query){
                        $query->where('estado','ACEPTADO')
                            ->orWhere('estado','PENDIENTE')
                            ->orWhere('estado','-');
                    });

            })
            ->get();

        $gastos = Gastos::where('idcaja', $idcaja)->get();

        $caja = Caja::find($idcaja);

        $sumas = [
            'efectivo' => 0,
            'visa' => 0,
            'mastercard' => 0,
            'yape' => 0,
            'plin' => 0,
            'transferencia' => 0,
            'otros' => 0,
            'credito' => 0,
            'credito_usd' => 0,
            'gastos' => 0,
            'extras' => 0,
            'devoluciones' => 0,
            'efectivo_usd' => 0,
        ];

        $tipo_pago = DataTipoPago::getTipoPago();

        foreach ($ventas as $venta) {
            foreach ($venta->pago as $pago) {
                $tipoPago = collect($tipo_pago)->firstWhere('num_val', $pago->tipo);
                if ($venta->facturacion->codigo_moneda === 'PEN') {
                    if ($tipoPago) {
                        $sumas[$tipoPago['text_val']] += $pago->monto;
                    } else {
                        $sumas['otros'] += $pago->monto;
                    }
                } else {
                    if($tipoPago['text_val'] == 'credito'){
                        $sumas['credito_usd'] += $pago->monto;
                    } else {
                        $sumas['efectivo_usd'] += $pago->monto;
                    }
                }
            }
        }


        foreach ($gastos as $gasto) {
            switch ($gasto->tipo) {
                case 1:
                    $sumas['gastos'] += $gasto->monto;
                    break;
                case 2:
                    $sumas['extras'] += $gasto->monto;
                    break;
                case 3:
                    $sumas['devoluciones'] += $gasto->monto;
                    break;
            }
        }

        $total_ventas = $sumas['efectivo'] + $sumas['visa'] + $sumas['mastercard'] +
            $sumas['plin'] + $sumas['yape'] + $sumas['transferencia'] + $sumas['otros'];

            $total_cierre = $caja->apertura + $sumas['efectivo'] + $sumas['extras'] - $sumas['gastos'] - $sumas['devoluciones'];

        return array_merge(['idcaja' => $caja->idcaja, 'apertura' => $caja->apertura, 'total_cierre' => $total_cierre, 'total_ventas'=>$total_ventas], $sumas);
    }

    public function cierre_automatico($idcaja){
        $request = new Request();
        $request->idcaja = $idcaja;
        $data = $this->obtener_datos_cierre($request);
        $data['efectivo_real'] = $data['total_cierre'];
        $data['efectivo_teorico'] = $data['total_cierre'];
        $data['descuadre'] = 0;
        $data['observacion_c'] = 'Caja cerrada de manera automática';
        $data_cierre=new Request($data);
        $success = $this->procesar_cierre($data_cierre);
        return $success;
    }

    public function imprimir_cierre(Request $request, $id){

        try{
            $caja = Caja::find($id);
            $resumen_ventas = $this->resumen_ventas($id);
            $productos = $this->resumen_productos($id);

            $data['caja']=$caja;
            $data['productos']=$productos;
            $data['total']=$resumen_ventas['total'];
            $data['detallado']=$request->detallado;

            $view = view('caja/imprimir/cierre',$data);
            $html=$view->render();
            $pdf=new Html2Pdf('P',[72,350],'es');
            $pdf->pdf->SetTitle('Cierre '.$caja->fecha);
            $pdf->writeHTML($html);

            if($request->rawbt){
                $fromFile = $pdf->output('Cierre_'.$caja->fecha.'.pdf','S');
                return 'rawbt:data:application/pdf;base64,'.base64_encode($fromFile);
            } else {
                $pdf->output('Cierre_'.$caja->fecha.'.pdf');
            }

        } catch (\Exception $e){
            Log::error($e);
            return $e->getMessage();
        }

    }



}
