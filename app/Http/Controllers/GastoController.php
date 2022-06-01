<?php

namespace sysfact\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use sysfact\Gastos;
use sysfact\Http\Controllers\Helpers\MainHelper;

class GastoController extends Controller
{
    private $idcaja;

    public function __construct()
    {
        $this->middleware('auth');
        $this->idcaja = MainHelper::obtener_idcaja();
    }

    public function index(){

        return view('caja.egresos',['usuario'=>auth()->user()->persona]);
    }

    public function obtener_datos(Request $request){
        $fecha_in=$request->fecha_in;
        $fecha_out=$request->fecha_out;
        $filtro=$request->filtro;
        if($filtro == 3){
            $filtro='';
        }
        $gasto=Gastos::whereBetween('fecha',[$fecha_in.' 00:00:00',$fecha_out.' 23:59:59'])
            ->where('tipo','LIKE','%'.$filtro.'%')
            ->orderby('idgasto','desc')->paginate(30);

        foreach ($gasto as $item){

            $item->caja=$item->cajero['nombre'];

            switch($item->tipo_egreso){
                case '1':
                    $item->tipo='Gastos comunes';
                    break;
                case '2':
                    $item->tipo='Ingreso extra';
                    break;
                case'4':
                    $item->tipo='Pago de empleados';
                    $item->descripcion='Pago a: '.$item->empleado['nombre'].' '.$item->empleado['apellidos'];
                    break;
            }

        }

        return $gasto;
    }

    public function store(Request $request){
        $egreso=new Gastos();
        $egreso->idcajero=auth()->user()->idempleado;
        $egreso->fecha=date('Y-m-d H:i:s');
        $egreso->idempleado=$request->idempleado;
        $egreso->idcaja=$this->idcaja;
        $egreso->tipo_egreso=$request->tipo_egreso;
        $egreso->descripcion=$request->descripcion;
        $egreso->tipo_pago_empleado=$request->tipo_pago_empleado;
        $egreso->mes_pago_empleado=$request->mes_pago_empleado;
        $egreso->tipo_comprobante=$request->tipo_comprobante;
        $egreso->num_comprobante=strtoupper($request->num_comprobante);
        $egreso->monto=$request->monto;
        $egreso->tipo=$request->tipo;
        $egreso->save();
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

}
