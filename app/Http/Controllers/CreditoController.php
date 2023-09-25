<?php

namespace sysfact\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Matrix\Exception;
use sysfact\Cliente;
use sysfact\Exports\CreditosExport;
use sysfact\Http\Controllers\Helpers\DataTipoPago;
use sysfact\Mail\MailCreditos;
use sysfact\Pago;
use sysfact\Venta;

class CreditoController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function actualizar_pagos(){
       try{
           $ventas = Venta::where('tipo_pago',2)->get();
           $arr = [];

           foreach ($ventas as $venta){
                $pago=$venta->pago;
                if(count($pago) == 0){
                    $pago = new Pago();
                    $pago->idventa = $venta->idventa;
                    $pago->tipo = $venta->tipo_pago;
                    $pago->monto = $venta->total_venta;
                    $pago->estado = 20;
                    $pago->save();
                }

            }

           foreach ($ventas as $venta){
               $p=$venta->pago()->first();
               if($venta->datos_adicionales){

                   $det = json_decode($venta->datos_adicionales, true)['cuotas'][0];
                   $detalle = [
                       'fecha'=>$det['fecha'],
                       'metodo_pago'=>1,
                       'num_operacion'=>'',
                       'monto'=>$venta->total_venta,
                   ];
                   $pago = Pago::find($p->idpago);
                   $pago->detalle = [$detalle];
                   $pago->estado = 2;
                   $pago->update();
               }

           }

           return 'success';
       } catch (\Exception $e){
           return $e->getMessage();
       }

    }

    public function index(Request $request){
        try{

            $filtro = $request->get('filtro', 'fecha');
            $buscar = $request->buscar;
            $desde = $request->get('desde', date('Y-m-d',strtotime(date('Y-m-d').' - 30 days')));
            $hasta = $request->get('hasta', date('Y-m-d'));
            $esExportable = $request->get('export',false);

            $filtros = [
                'desde' => $desde,
                'hasta' => $hasta,
                'filtro'=>$filtro,
                'buscar'=>$buscar,
            ];

            $orderby=$request->get('orderby','idventa');
            $order=$request->get('order', 'desc');

            $ventas = Venta::join('persona', 'persona.idpersona', '=', 'ventas.idcliente')
                ->select('ventas.*','persona.nombre as cliente')
                ->when($filtro == 'fecha', function ($query) use ($desde, $hasta) {
                    return $query->whereBetween('fecha', [$desde.' 00:00:00', $hasta.' 23:59:59']);
                }, function ($query) use ($buscar) {
                    return $query->where('idcliente',$buscar);
                })
                ->where('eliminado', 0)
                ->where('tipo_pago', 2)
                ->whereHas('facturacion', function($query) {
                    $query->whereIn('codigo_tipo_documento', [01, 03, 30])
                        ->where(function ($query){
                            $query->where('estado','ACEPTADO')
                                ->orWhere('estado','PENDIENTE')
                                ->orWhere('estado','-');
                        });
                })
                ->orderby($orderby,$order)
                ->when($esExportable, function ($query) {
                    return $query->get();
                }, function ($query) {
                    return $query->paginate(30);
                });

            if(!$esExportable){
                $ventas->appends($_GET)->links();
            }

            foreach ($ventas as $venta){
                $cuotas=$venta->pago;
                $minimo_dias = 5;

                foreach ($cuotas as $pago){
                    $fecha_hoy = Carbon::now();
                    $dias_restantes=$fecha_hoy->diffInDays(Carbon::parse($pago->fecha),false);
                    $venta->estado = 'PAGO PENDIENTE';
                    $venta->estado_badge_class = 'badge-info';

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

                    if($pago->estado == 2){
                        $venta->estado = 'PAGADO';
                        $venta->estado_badge_class = 'badge-success';
                    } else {
                        $venta->estado = 'PAGO PENDIENTE';
                        $venta->estado_badge_class = 'badge-info';
                        break;
                    }

                }

            }

            if($filtro == 'cliente'){
                $cliente = Cliente::find($buscar);
                $cliente->persona;
            } else {
                $cliente = null;
            }

            if($esExportable){
                return Excel::download(new CreditosExport($ventas), 'resumen_creditos.xlsx');
            } else {
                return view('creditos.index', [
                    'creditos' => $ventas,
                    'usuario' => auth()->user()->persona,
                    'filtros'=>$filtros,
                    'cliente'=>$cliente,
                    'order'=>$order=='desc'?'asc':'desc',
                    'orderby'=>$orderby,
                    'order_icon'=>$order=='desc'?'<i class="fas fa-caret-square-up"></i>':'<i class="fas fa-caret-square-down"></i>'
                ]);
            }



        } catch (\Exception $e){
            if($e->getCode()=='42S22'){
                return redirect('/creditos');
            }
            return $e->getMessage();
        }

    }

    public function getBadget($idcliente){
        try{

            $ventas = Venta::where('idcliente',$idcliente)
                ->where('eliminado',0)
                ->where('tipo_pago',2)
                ->whereHas('facturacion', function($query) {
                    $query->whereIn('codigo_tipo_documento', [01, 03, 30])
                        ->where(function ($query){
                            $query->where('estado','ACEPTADO')
                                ->orWhere('estado','PENDIENTE')
                                ->orWhere('estado','-');
                        });
                })
                ->get();

            $totales = [
                'total_credito'=>0,
                'adeuda'=>0,
                'pagado'=>0,
            ];

            foreach ($ventas as $item) {

                $cuotas = $item->pago;
                foreach ($cuotas as $pago){
                    $totales['total_credito'] +=  $pago->monto;
                    if($pago->estado == 1){
                        $totales['adeuda'] +=  $pago->monto;
                    }
                    if($pago->estado == 2){
                        $totales['pagado'] +=  $pago->monto;
                    }
                }
            }

            return $totales;

        } catch (\Exception $e){
            Log::info($e);
            return $e->getMessage();
        }
    }

    public function creditos_notificacion(){
        $ventas = Venta::where('eliminado', 0)
            ->where('tipo_pago', 2)
            ->whereHas('facturacion', function($query) {
                $query->where(function ($query) {
                    $query->where('codigo_tipo_documento', 01)
                        ->orWhere('codigo_tipo_documento', 03)
                        ->orWhere('codigo_tipo_documento', 30);
                })
                    ->where(function ($query) {
                        $query->where('estado', 'ACEPTADO')
                            ->orWhere('estado', 'PENDIENTE');
                    })
                    ->orWhere('estado', '-');
            })
            ->orderby('idventa', 'desc')
            ->get();

        if(count($ventas) > 0){
            $seleccionadas = [];
            foreach ($ventas as $key=>$venta){
                $pagos = $venta->pago;
                $minimo_dias = 5;
                $suma_cuotas = 0;
                $dias_restantes = 0;
                $estado = 0;
                foreach ($pagos as $pago){
                    $estado = $pago->estado;
                    $fecha_hoy = Carbon::now();
                    $dias_restantes=$fecha_hoy->diffInDays(Carbon::parse($pago->fecha),false);

                    if($pago->estado == 2){
                        $suma_cuotas +=  $pago->monto;
                    }

                    if($dias_restantes <= 0 && $pago->estado == 1){
                        $venta->estado = 'CUOTA VENCIDA '.$dias_restantes.' DÍA(S)';
                        $venta->bg_color = '#ffbfbf';
                        break;
                    }
                    if($dias_restantes <= $minimo_dias && $pago->estado == 1){
                        $venta->estado = 'CUOTA POR VENCER EN '.$dias_restantes.' DÍA(S)';
                        $venta->bg_color = '#ffe047';
                        break;
                    }

                }

                if($estado == 1){
                    if($dias_restantes >= -3 && $dias_restantes <= 3){
                        $seleccionadas[] = $venta;
                    }
                }
            }

            if(count($seleccionadas) > 0){
                $mail = json_decode(cache('config')['mail_contact'], true)['notificacion_caja']??false;
                if($mail && !empty($mail)){
                    Mail::to($mail)->send(new MailCreditos($seleccionadas));
                }
            }

        }

    }

    public function editar(Request $request, $id)
    {
        $venta=Venta::find($id);
        $venta->pago;
        $venta->persona;

        foreach ($venta->pago as $pago){
            $pago->fecha = date('d/m/Y', strtotime($pago->fecha));
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

                        /*$pago = new Pago();
                        $pago->idventa = $request->idventa;
                        $pago->monto = $d['monto'];
                        $pago->tipo = $d['metodo_pago'];
                        $pago->fecha = date('Y-m-d H:i:s');
                        $pago->save();*/
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
                $dataTipoPago = DataTipoPago::getTipoPago();
                $find = array_search($d['metodo_pago'], array_column($dataTipoPago,'num_val'));
                $d['metodo_pago'] = mb_strtoupper($dataTipoPago[$find]['label']);
                $d['fecha']=date('d-m-Y',strtotime($d['fecha']));
                $suma += $d['monto'];
                $data[] = $d;
            }
        }
        $total_pagado = $suma;
        $total_adeuda = $pago->monto - $suma;
        return json_encode(['detalle'=>$data,'pagado'=>$total_pagado,'adeuda'=>$total_adeuda]);
    }

    public function set_alias(Request $request){
       try{
           $venta = Venta::find($request->idventa);
           $venta->alias = mb_strtoupper($request->alias);
           $venta->save();
       } catch (Exception $e){
           Log::error($e);
           return $e->getMessage();
       }
    }

    public function get_alias($idventa){
        try{
            $venta = Venta::find($idventa);
            return $venta->alias;
        } catch (Exception $e){
            Log::error($e);
            return $e->getMessage();
        }
    }

    public function generarUrl($idcliente){
        try{

            $cliente = Cliente::find($idcliente);

            if($cliente->tocken){
                $uuid = $cliente->tocken;
            } else {
                $uuid = $this->generar_token(60);
                $cliente->tocken = $uuid;
                $cliente->save();
            }

            return 'https://'.app()->domain().'/consulta/creditos/'.$uuid;

        } catch (Exception $e){
            Log::error($e);
            return $e->getMessage();
        }
    }

    function generar_token($longitud)
    {
        if ($longitud < 4) {
            $longitud = 4;
        }
        return bin2hex(openssl_random_pseudo_bytes(($longitud - ($longitud % 2)) / 2));
    }

}