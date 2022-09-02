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

        $caja_abierta = null;

        $caja=Caja::where('fecha_a','LIKE',$fecha.'%')
            ->where('turno',$turno)
            ->orderby('fecha_a','desc')
            ->first();

        if($caja){
            $caja->email = json_decode(cache('config')['mail_contact'], true)['notificacion_caja'];
            $caja->moneda = 'S/';
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
                'fecha'=>$fecha
            ]);
    }

    public function abrir_caja(Request $request){
        try{
            $caja=new Caja();
            $caja->idempleado=auth()->user()->idempleado;
            $caja->apertura=$request->apertura;
            $caja->observacion_a=$request->observacion_a;
            $caja->fecha_a=date('Y-m-d H:i:s');
            $caja->estado=0;
            $caja->turno=$request->turno;
            $success = $caja->save();
            $idcaja = $caja->idcaja;

            if($success){
                Cache::forever('caja_abierta', $idcaja);
                if($request->notificacion && json_decode(cache('config')['mail_contact'], true)['notificacion_caja']){
                    $email = json_decode(cache('config')['mail_contact'], true)['notificacion_caja'];
                    Mail::to($email)->send(new MovimientoCaja($caja));
                }
                return 1;
            }

            return 0;

        } catch (\Exception $e){
            return $e->getMessage();
        }

    }

    public function procesar_cierre(Request $request){
        try{
            $idcaja = $request->idcaja;
            $caja=Caja::find($idcaja);
            $caja->efectivo_teorico=$request->efectivo_teorico;
            $caja->efectivo=$request->efectivo;
            $caja->tarjeta=$request->tarjeta_visa;
            $caja->tarjeta_1=$request->tarjeta_mastercard;
            $caja->plin=$request->plin;
            $caja->yape=$request->yape;
            $caja->transferencia=$request->transferencia;
            $caja->devoluciones=$request->devoluciones;
            $caja->credito=$request->credito;
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
            return $e->getMessage();
        }

    }

    public function obtener_datos_cierre(Request $request)
    {
        $suma_ventas_efectivo=0;
        $suma_ventas_credito=0;
        $suma_ventas_tarjeta_visa=0;
        $suma_ventas_tarjeta_mastercard=0;
        $suma_ventas_yape=0;
        $suma_ventas_plin=0;
        $suma_ventas_tranferencia=0;
        $suma_otros=0;
        $suma_devoluciones=0;
        $suma_gastos=0;
        $suma_extras=0;
        $suma_dolares=0;
        $idcaja = $request->idcaja;

        $ventas = Venta::with("pago")
            ->where('eliminado', 0)
            ->where('idcaja', $idcaja)
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

        $gastos = Gastos::where('idcaja', $idcaja)->get();

        $caja = Caja::find($idcaja);

        foreach ($ventas as $venta) {

            foreach ($venta->pago as $pago){
                if($venta->facturacion->codigo_moneda=='PEN'){
                    switch($pago->tipo){
                        case 1:
                            $suma_ventas_efectivo += $pago->monto;
                            break;
                        case 2:
                            $suma_ventas_credito += $pago->monto;
                            break;
                        case 3:
                            $suma_ventas_tarjeta_visa += $pago->monto;
                            break;
                        case 5:
                            $suma_ventas_yape += $pago->monto;
                            break;
                        case 6:
                            $suma_ventas_plin += $pago->monto;
                            break;
                        case 7:
                            $suma_ventas_tarjeta_mastercard += $pago->monto;
                            break;
                        case 8:
                            $suma_otros += $pago->monto;
                            break;
                        case 9:
                            $suma_ventas_tranferencia += $pago->monto;
                            break;
                    }
                } else{
                    $suma_dolares += $pago->monto;
                }
            }

        }

        foreach ($gastos as $gasto) {
            switch($gasto->tipo){
                case 1:
                    $suma_gastos += $gasto->monto;
                    break;
                case 2:
                    $suma_extras += $gasto->monto;
                    break;
                case 3:
                    $suma_devoluciones += $gasto->monto;
                    break;
            }

        }

        $total_cierre=$caja->apertura+$suma_ventas_efectivo+$suma_extras-$suma_gastos-$suma_devoluciones;

        return [
            'idcaja'=>$caja->idcaja,
            'apertura'=>$caja->apertura,
            'efectivo'=>$suma_ventas_efectivo,
            'tarjeta_visa'=>$suma_ventas_tarjeta_visa,
            'tarjeta_mastercard'=>$suma_ventas_tarjeta_mastercard,
            'yape'=>$suma_ventas_yape,
            'plin'=>$suma_ventas_plin,
            'transferencia'=>$suma_ventas_tranferencia,
            'otros'=>$suma_otros,
            'credito'=>$suma_ventas_credito,
            'gastos'=>$suma_gastos,
            'extras'=>$suma_extras,
            'total_cierre'=>$total_cierre,
            'devoluciones'=>$suma_devoluciones,
            'dolares'=>$suma_dolares
        ];

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
            return $e->getMessage();
        }

    }



}
