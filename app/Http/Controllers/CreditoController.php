<?php

namespace sysfact\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use sysfact\Pago;
use sysfact\Venta;

class CreditoController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request){

        if ($request) {
            try{
                $consulta = trim($request->get('textoBuscado'));
                $orderby=$request->get('orderby','idventa');
                $order=$request->get('order', 'desc');

                $ventas = Venta::join('persona', 'persona.idpersona', '=', 'ventas.idcliente')
                    ->select('ventas.*','persona.nombre as cliente')
                    ->where('eliminado', 0)
                    ->where('tipo_pago', 2)
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
                    ->orderby($orderby,$order)
                    ->paginate(30);

                foreach ($ventas as $venta){
                    $cuotas=$venta->pago;
                    $minimo_dias = 5;
                    $suma_cuotas = 0;

                    foreach ($cuotas as $pago){
                        $fecha_hoy = Carbon::now();
                        $dias_restantes=$fecha_hoy->diffInDays(Carbon::parse($pago->fecha),false);

                        if($pago->estado == 2){
                            $suma_cuotas +=  $pago->monto;
                        }

                        if($dias_restantes <= 0 && $pago->estado == 1){
                            $venta->estado = 'CUOTA VENCIDA '.$dias_restantes.' DÍA(S)';
                            $venta->estado_badge_class = 'badge-danger';
                            break;
                        }
                        if($dias_restantes <= $minimo_dias && $pago->estado == 1){
                            $venta->estado = 'CUOTA POR VENCER EN '.$dias_restantes.' DÍA(S)';
                            $venta->estado_badge_class = 'badge-warning';
                            break;
                        }
                        if($suma_cuotas == $venta->total_venta){
                            $venta->estado = 'PAGADO';
                            $venta->estado_badge_class = 'badge-success';
                        } else{
                            $venta->estado = 'PAGO PENDIENTE';
                            $venta->estado_badge_class = 'badge-info';
                        }

                    }
                }


                return view('creditos.index', [
                    'creditos' => $ventas,
                    'usuario' => auth()->user()->persona,
                    'textoBuscado' => $consulta,
                    'order'=>$order=='desc'?'asc':'desc',
                    'orderby'=>$orderby,
                    'order_icon'=>$order=='desc'?'<i class="fas fa-caret-square-up"></i>':'<i class="fas fa-caret-square-down"></i>'
                ]);
            } catch (\Exception $e){
                if($e->getCode()=='42S22'){
                    return redirect('/creditos');
                }
                return $e->getMessage();
            }

        }

    }

    public function editar(Request $request, $id)
    {
        $venta=Venta::find($id);
        $venta->pago;
        $venta->persona;

        foreach ($venta->pago as $pago){
            $pago->fecha = date('d-m-Y', strtotime($pago->fecha));
            $suma = 0;
            if($pago->detalle){
                $detalle = json_decode($pago->detalle, true);
                foreach ($detalle as $d){
                    $suma += $d['monto'];
                }
            }
            $pago->total_pagado = $suma;
            $pago->total_adeuda = $pago->monto - $suma;
            $pago->total_adeuda == 0 ? $pago->estado = 'pagado':$pago->estado = 'adeuda';
        }

        switch ($venta->facturacion->codigo_tipo_documento) {
            case '03':
                $venta->facturacion->comprobante = 'Boleta';
                break;
            case '01':
                $venta->facturacion->comprobante = 'Factura';
                break;
            case '07':
                $venta->facturacion->comprobante = 'Nota de crédito';
                break;
            case '08':
                $venta->facturacion->comprobante = 'Nota de débito';
                break;
            default:
                $venta->facturacion->comprobante = 'Venta';
        }

        return view('creditos.editar',['credito'=>$venta,'usuario'=>auth()->user()->persona]);
    }

    public function agregar_pago(Request $request){
        try{

            $pago=Pago::find($request->idpago);

            //Agregar pago de cuota
            $pago->detalle = $request->detalle;
            $pago->estado = $request->estado;
            $pago->save();

            $pagos=Pago::where('idventa',$request->idventa)->get();

            foreach ($pagos as $pago){
                $pago->fecha = date('d-m-Y', strtotime($pago->fecha));
                $suma = 0;
                if($pago->detalle){
                    $detalle = json_decode($pago->detalle, true);
                    foreach ($detalle as $d){
                        $suma += $d['monto'];
                    }
                }
                $pago->total_pagado = $suma;
                $pago->total_adeuda = $pago->monto - $suma;
                $pago->total_adeuda == 0 ? $pago->estado = 'pagado':$pago->estado = 'adeuda';
            }

            return json_encode($pagos);

        } catch (\Exception $e){
            return $e;
        }
    }

    public function ver_pagos(Request $request){
        $pago=Pago::find($request->idpago);
        $data = [];
        $suma = 0;
        if($pago->detalle){
            $detalle = json_decode($pago->detalle, true);
            foreach ($detalle as $d){
                switch ($d['metodo_pago']){
                    case '1':
                        $d['metodo_pago']='Efectivo';
                        break;
                    case '2':
                        $d['metodo_pago']='Tarjeta';
                        break;
                    case '3':
                        $d['metodo_pago']='Depósito';
                        break;
                }
                $d['fecha']=date('d-m-Y',strtotime($d['fecha']));
                $suma += $d['monto'];
                $data[] = $d;
            }
        }
        $total_pagado = $suma;
        $total_adeuda = $pago->monto - $suma;
        return json_encode(['detalle'=>$data,'pagado'=>$total_pagado,'adeuda'=>$total_adeuda]);
    }
}
