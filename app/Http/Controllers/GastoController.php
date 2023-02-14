<?php

namespace sysfact\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use sysfact\Gastos;
use sysfact\Http\Controllers\Helpers\MainHelper;
use sysfact\Inventario;
use sysfact\Venta;

class GastoController extends Controller
{
    private $idcaja;

    public function __construct()
    {
        $this->middleware('auth');
        //$this->idcaja = MainHelper::obtener_idcaja();
    }

    public function index(Request $request){

        $tipo = $request->tipo??'gastos';
        $desde= $request->desde??date('Y-m-d');
        $hasta= $request->hasta??date('Y-m-d');

        switch($tipo){
            case 'gastos':
                $tipo_movimiento=1;
                break;
            case 'ingresos':
                $tipo_movimiento=2;
                break;
            default:
                $tipo_movimiento=3;
        }

        $data = $this->obtener_datos($desde,$hasta, $tipo_movimiento);

        return view('caja.egresos',[
            'usuario'=>auth()->user()->persona,
            'tipo_movimiento'=>$tipo_movimiento,
            'tipo'=>$tipo,
            'data'=>$data,
            'desde'=>$desde,
            'hasta'=>$hasta,
        ]);
    }

    public function obtenerTotal(Request $request){
        $tipo = $request->tipo??'gastos';
        $desde= $request->desde??date('Y-m-d');
        $hasta= $request->hasta??date('Y-m-d');

        switch($tipo){
            case 'gastos':
                $tipo_movimiento=1;
                break;
            case 'ingresos':
                $tipo_movimiento=2;
                break;
            default:
                $tipo_movimiento=3;
        }

        $data = $this->obtener_datos($desde,$hasta, $tipo_movimiento, true);
        $suma = 0;
        foreach ($data as $item) {
            $suma += $item->monto;
        }

        return $suma;

    }

    public function obtener_datos($desde, $hasta, $tipo_movimiento,$calcular = false){

        if($calcular){
            $gasto=Gastos::whereBetween('fecha',[$desde.' 00:00:00',$hasta.' 23:59:59'])
                ->where('tipo',$tipo_movimiento)
                ->orderby('idgasto','desc')->get();
        } else {
            $gasto=Gastos::whereBetween('fecha',[$desde.' 00:00:00',$hasta.' 23:59:59'])
                ->where('tipo',$tipo_movimiento)
                ->orderby('idgasto','desc')->paginate(30);
        }

        foreach ($gasto as $item){

            $item->caja=strtoupper($item->cajero['nombre']);

            switch($item->tipo_egreso){
                case '1':
                    $item->tipo_gasto='GASTOS COMUNES';
                    break;
                case'4':
                    $item->tipo_gasto='PAGO DE EMPLEADOS';
                    $item->descripcion=mb_strtoupper('Pago a: '.$item->empleado['nombre'].' '.$item->empleado['apellidos']);
                    break;
            }

        }

        return $gasto;
    }

    public function store(Request $request){
        try{
            DB::beginTransaction();

            $egreso=new Gastos();
            $egreso->idcajero=auth()->user()->idempleado;
            $egreso->fecha=date('Y-m-d H:i:s');
            $egreso->idempleado=$request->idempleado;
            $egreso->idcaja=MainHelper::obtener_idcaja();
            $egreso->descripcion=$request->descripcion;
            $egreso->tipo_pago_empleado=$request->tipo_pago_empleado;
            $egreso->mes_pago_empleado=$request->mes_pago_empleado;
            $egreso->tipo_comprobante=$request->tipo_comprobante;
            $egreso->num_comprobante=strtoupper($request->num_comprobante);
            $egreso->monto=$request->monto;
            $egreso->tipo=$request->tipo_movimiento;
            $egreso->tipo_egreso=$request->tipo_movimiento==1?$request->tipo_egreso:-1;
            $egreso->save();

            DB::commit();

        } catch (\Exception $e){
            DB::rollBack();
            Log::error($e);
            return $e->getMessage();
        }
    }

    public function destroy($id){
        $egreso=Gastos::destroy($id);
    }

    public function obtenerEmpleados(Request $request){
        $consulta=trim($request->get('textoBuscado'));

        $proveedor=DB::table('empleado')
            ->join('persona', 'persona.idpersona', '=', 'empleado.idempleado')
            ->select('empleado.*','persona.*')
            ->where('eliminado','=',0)
            ->where('acceso','!=',1)
            ->where('nombre','like','%'.$consulta.'%')
            ->orderby('idempleado','desc')
            ->take(5)
            ->get();

        return json_encode($proveedor);
    }

    public function obtenerPagoPendiente(Request $request){
        $pendiente=0;
        $sueldo=0;
        $anticipo=0;
        $gasto=DB::table('gastos')
            ->where('idempleado',$request->idempleado)
            ->where('tipo_pago_empleado','!=',3)
            ->where('mes_pago_empleado',$request->mes)
            ->get();


        $empleado=DB::table('empleado')
            ->where('idempleado',$request->idempleado)
            ->first();

        if(!$gasto){
            $anticipo = 0;
        } else{
            foreach ($gasto as $item){
                $anticipo+=$item->monto;
            }
        }


        return json_encode($empleado->sueldo - $anticipo);
    }

    public function obtenerDetalleVenta($idventa){

        $venta = Venta::find($idventa);
        $productos = $venta->productos;
        foreach ($productos as $producto) {
            $producto->devolver = false;
            $producto->cantidad_devolucion = $producto->detalle['cantidad'] - $producto->detalle['devueltos'];
        }
        return $productos;

    }

    public function devolver_productos(Request $request){
        try{
            DB::beginTransaction();

            $productos = json_decode($request->items, true);

            $egreso=new Gastos();
            $egreso->idcajero=auth()->user()->idempleado;
            $egreso->fecha=date('Y-m-d H:i:s');
            $egreso->idempleado=-1;
            $egreso->idcaja=MainHelper::obtener_idcaja();;
            $egreso->tipo_egreso=-1;
            $egreso->descripcion='DEVOLUCIÓN DE PRODUCTOS - VENTA N° '.$request->idventa;
            $egreso->tipo_pago_empleado=null;
            $egreso->mes_pago_empleado=null;
            $egreso->tipo_comprobante=-1;
            $egreso->num_comprobante=null;
            $egreso->idventa=$request->idventa;
            $egreso->monto=$request->total_devolucion;
            $egreso->tipo=3;
            $egreso->save();

            foreach ($productos as $producto) {
                $inv = Inventario::where('idproducto',$producto['idproducto'])->orderby('idinventario', 'desc')->first();
                MainHelper::actualizar_inventario($request->idventa,$producto,$inv,'devolucion');

                DB::statement("UPDATE ventas_detalle SET devueltos = ".($producto['detalle']['devueltos']+$producto['cantidad_devolucion'])." WHERE idventa=".$request->idventa." AND num_item = ".$producto['detalle']['num_item']." AND idproducto=".$producto['idproducto']);

            }

            DB::commit();

            return 'Se procesó exitosamente la devolución';

        } catch (\Exception $e){
            DB::rollBack();
            Log::error($e);
            return $e->getMessage();
        }
    }

}
