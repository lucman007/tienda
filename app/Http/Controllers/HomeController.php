<?php

namespace sysfact\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use sysfact\Producto;
use sysfact\Trabajador;
use sysfact\Venta;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if(auth()->user()->hasRole('Contabilidad')){
            return redirect('/reportes/comprobantes');
        }
        //return redirect('/pedidos');
        return view('dashboard.index',['usuario'=>auth()->user()->persona]);
    }

    public function obtener_stock_bajo(){
        $productos=Producto::where('eliminado',0)
            ->where('tipo_producto',1)
            ->get();
        $seleccionados=[];

        foreach ($productos as $key=>$inv){
            foreach ($inv->inventario as $kardex){
                $inv->cantidad+=$kardex->cantidad;
            }
        }

        $productos = array_values(array_sort($productos, function ($value) {
            return $value['cantidad'];
        }));

        //Obtener los primeros 5 productos más bajos del stock
        $i=0;
        foreach ($productos as $producto){
            if($i<5){
                if($producto->cantidad<=$producto->stock_bajo){
                    $seleccionados[]=$producto;
                    $i++;
                }
            }
        }

        return $seleccionados;
    }

    public function obtener_pago_empleados()
    {

        $trabajadores=Trabajador::where('eliminado',0)
            ->where('acceso','!=',1)
            ->orderby('idempleado','desc')
            ->get();


        $dias_restantes=31;
        $seleccionados=[];

        $fin_de_mes = Carbon::now()->endOfMonth()->toDateString();
        $dia_fin_de_mes=explode('-',$fin_de_mes);

        $hoy = Carbon::now()->toDateString();
        $dia_de_hoy = explode('-',$hoy);

        foreach ($trabajadores as $trabajador){
            $trabajador->persona;

            $dia_de_pago=$trabajador->dia_pago;

            //Si el mes tiene menos de 31 dias, adaptamos los pagos de ese dia a un dia menos
            if($dia_de_pago>(int)$dia_fin_de_mes[2]){
                $dia_de_pago=$dia_de_pago-($dia_de_pago-$dia_fin_de_mes[2]);
            }

            $fecha_de_pago=$dia_de_pago.'-'.date('m-Y');
            $trabajador->dia_pago=$fecha_de_pago;

            //Calculamos los días restantes para el pago
            if($dia_de_pago>=(int)$dia_de_hoy[2]){
                $dias_restantes=$dia_de_pago-$dia_de_hoy[2];
            }

            $trabajador->dias_restantes=$dias_restantes;
        }

        $trabajadores = array_values(array_sort($trabajadores, function ($value) {
            return $value['dias_restantes'];
        }));

        //Obtener los empleados más proximos a vencer
        foreach ($trabajadores as $trabajador){
            if($trabajador->dias_restantes<=5){
                $seleccionados[]=$trabajador;
            }
        }

        return $seleccionados;
    }

    public function obtenerReporte(Request $request){

        $reporte = new ReporteController();
        $ventas_usd = $reporte->reporte_ventas_diario_data(date('Y-m'),'USD','fecha-actual');
        $ventas_pen = $reporte->reporte_ventas_diario_data(date('Y-m'),'PEN','fecha-actual');

        $data = [
            'soles'=>[
                'total_neto'=>0,
                'total_costos'=>0,
                'total_impuestos'=>0
            ],
            'dolares'=>[
                'total_neto'=>0,
                'total_costos'=>0,
                'total_impuestos'=>0
            ]
        ];

        $totales = [
            'total_neto'=>0,
            'total_costos'=>0,
            'total_impuestos'=>0
        ];

        foreach ($ventas_usd as $item) {
            $data['dolares']['total_neto'] += $item['ventas_netas'];
            $data['dolares']['total_costos'] += $item['costos'];
            $data['dolares']['total_impuestos'] += $item['impuestos'];
        }

        foreach ($ventas_pen as $item) {
            $data['soles']['total_neto'] += $item['ventas_netas'];
            $data['soles']['total_costos'] += $item['costos'];
            $data['soles']['total_impuestos'] += $item['impuestos'];
        }

        $data['total_neto'] = $data['dolares']['total_neto'] + $data['soles']['total_neto'];
        $data['total_costos'] = $data['dolares']['total_costos'] + $data['soles']['total_costos'];
        $data['total_impuestos'] = $data['dolares']['total_impuestos'] + $data['soles']['total_impuestos'];

        return $data;

    }

    public function obtener_ventas_credito(){
        $creditos=Venta::with("pago")->where('eliminado',0)
            ->where('tipo_pago',2)
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
            ->orderby('idventa','desc')
            ->take(30)
            ->get();

        $seleccionados=[];
        $minimo_dias = 5;
        $break_flag = 1;
        foreach ($creditos as $credito){
            if($break_flag == 5){
                break;
            }
            $suma_cuotas = 0;

            foreach ($credito->pago as $pago){

                if($pago->estado == 2){
                    $suma_cuotas +=  $pago->monto;
                }

            }

            //verificamos que estado de venta sea pagado o adeuda
            if($suma_cuotas == $credito->total_venta){
                $credito->estado = 0; //PAGADO
            } else {
                $credito->estado = 1; //ADEUDA
            }

            foreach ($credito->pago as $pago){
                if($credito->estado == 1){
                    $fecha_hoy = Carbon::now();
                    $dias_restantes=$fecha_hoy->diffInDays(Carbon::parse($pago->fecha),false);

                    if($dias_restantes < $minimo_dias && $pago->estado == 1){
                        $break_flag++;
                        switch($credito->facturacion->codigo_tipo_documento){
                            case '01':
                                $comprobante='La FACTURA';
                                break;
                            case '03':
                                $comprobante='La BOLETA';
                                break;
                            default:
                                $comprobante='El RECIBO';
                        }
                        $seleccionados[] = ['comprobante'=>$comprobante,
                            'correlativo'=>$credito->facturacion->serie.'-'.$credito->facturacion->correlativo,
                            'idventa'=>$credito->idventa, 'dias'=>$dias_restantes];
                        break;
                    }
                }
            }

        }

        return $seleccionados;
    }

}
