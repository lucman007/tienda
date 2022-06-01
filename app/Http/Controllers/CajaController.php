<?php

namespace sysfact\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Spipu\Html2Pdf\Html2Pdf;
use sysfact\Caja;
use sysfact\Gastos;
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
            $caja->tarjeta=$request->tarjeta;
            $caja->credito=$request->credito;
            $caja->extras=$request->extras;
            $caja->gastos=$request->gastos;
            $caja->efectivo_real=$request->efectivo_real;
            $caja->observacion_c=$request->observacion_c;
            $caja->descuadre=$request->descuadre;
            $caja->fecha_c=date('Y-m-d H:i:s');
            $caja->estado=1;
            $success = $caja->save();
            if($success){
                Cache::put('caja_abierta','idcaja',0);
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
        $suma_ventas_tarjeta=0;
        $suma_gastos=0;
        $suma_extras=0;
        $suma_dolares=0;
        $idcaja = $request->idcaja;

        $ventas = Venta::with("pago")->where('eliminado', 0)
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
                            $suma_ventas_tarjeta += $pago->monto;
                            break;
                    }
                } else{
                    $suma_dolares += $pago->monto;
                }
            }

        }

        foreach ($gastos as $gasto) {
            if($gasto->tipo==1){
                $suma_gastos += $gasto->monto;
            } else{
                $suma_extras += $gasto->monto;
            }

        }

        $total_cierre=$caja->apertura+$suma_ventas_efectivo+$suma_extras-$suma_gastos;

        return ['idcaja'=>$caja->idcaja,'apertura'=>$caja->apertura,'efectivo'=>$suma_ventas_efectivo,
            'tarjeta'=>$suma_ventas_tarjeta,'credito'=>$suma_ventas_credito,
            'gastos'=>$suma_gastos,'extras'=>$suma_extras,'total_cierre'=>$total_cierre,'dolares'=>$suma_dolares];

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

    public function imprimir_cierre($id){

        $caja = Caja::find($id);

        $view = view('caja/imprimir/cierre',['caja'=>$caja]);
        $html=$view->render();
        $pdf=new Html2Pdf('P',[72,250],'es');
        $pdf->pdf->SetTitle('Cierre '.$caja->fecha);
        $pdf->writeHTML($html);
        $pdf->output('Cierre_'.$caja->fecha.'.pdf');
    }

    /*public function gestionar_creditos(Request $request){

        $suma_soles=0;
        $suma_dolares=0;
        $consulta=trim($request->get('textoBuscado'));

       $ventas=Venta::where('eliminado', '=', 0)
           ->whereHas('facturacion', function($query) {
               $query->where(function ($query) {
                   $query->where('codigo_tipo_documento',01)
                       ->orWhere('codigo_tipo_documento',03)
                       ->orWhere('codigo_tipo_documento',30);
               })
                   ->where('estado','ACEPTADO')
                   ->orWhere('estado','-');

           })
           ->where(function ($query){
               $query->where('tipo_pago','2')
                   ->orWhere('data_credito','!=',null);
           })
           ->orderby('idventa','desc')
           ->paginate(30);

        foreach ($ventas as $item) {

            if($item->data_credito){
                $data_credito = json_decode($item->data_credito,true);
                $item->estado = $data_credito['estado'];
            } else{
                $item->estado= 'ADEUDA';
            }

            if($item->facturacion->codigo_moneda=='PEN'){
                $suma_soles += $item->total_venta;
            } else{
                $suma_dolares += $item->total_venta;
            }

            $ventas->total_soles=$suma_soles==0?'0.00':$suma_soles;
            $ventas->total_usd=$suma_dolares==0?'0.00':$suma_dolares;

        }

        return view('caja.creditos',['creditos'=>$ventas,'usuario'=>auth()->user()->persona,'textoBuscado'=>$consulta,]);
    }*/

    /*public function agregar_pago_creditos(Request $request){
        try{

            $venta=Venta::find($request->idventa);

            //Recuperamos informacion ya guardada

            $guardado=[];

            if($venta){
                $guardado=json_decode($venta->data_credito, true);
            }

            //Verificamos si es pago total y cambiamos el tipo de pago de la venta

            if($request->tipo_operacion=='1'){
                $estado='PAGADO';
                $venta->tipo_pago=$request->tipo_pago;
            } else{
                if($request->suma_cuotas >= $request->total_venta){
                    $estado='PAGADO';
                    $venta->tipo_pago=$request->tipo_pago;
                } else{
                    $estado='ADEUDA';
                }
            }

            $data_credito=[
                'fecha'=>date('Y-m-d'),
                'importe'=>$request->importe,
                'tipo_pago'=>$request->tipo_pago
            ];

            $guardado['cuotas'][]=$data_credito;
            $guardado['estado']=$estado;
            $guardado['tipo_operacion']=$request->tipo_operacion;

            $venta->data_credito=json_encode($guardado);
            $venta->save();

            return json_encode($data_credito);

        } catch (\Exception $e){
            return $e;
        }

    }*/

    /*public function obtener_data_creditos($id){
        try{

            $venta=Venta::where('idventa',$id)->first()->data_credito;

            return json_encode($venta);

        } catch (\Exception $e){
            return $e;
        }

    }*/

}
