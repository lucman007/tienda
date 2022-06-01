<?php

namespace sysfact\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Spipu\Html2Pdf\Html2Pdf;
use sysfact\Exports\PagosExport;
use sysfact\Gastos;
use sysfact\Persona;
use sysfact\Trabajador;
use sysfact\User;

class TrabajadorController extends Controller
{

	public function __construct()
	{
		$this->middleware('auth');
	}

    public function index(Request $request)
    {
	    if ($request){
		    $consulta=trim($request->get('textoBuscado'));

            $trabajadores = DB::table('empleado')
                ->join('persona', 'persona.idpersona', '=', 'empleado.idempleado')
                ->select('empleado.*', 'persona.*')
                ->where('eliminado', '=', 0)
                ->where(function ($query) use ($consulta) {
                    $query->where('nombre', 'LIKE', '%' . $consulta . '%')
                        ->orWhere('dni', 'like', '%' . $consulta . '%');
                })
                ->orderby('idempleado', 'desc')
                ->paginate(30);

		    $fecha_de_pago=null;

            $fin_de_mes = Carbon::now()->endOfMonth()->toDateString();
            $dia_fin_de_mes=explode('-',$fin_de_mes);

		    foreach ($trabajadores as $trabajador){

                $dia_de_pago=$trabajador->dia_pago;
                //Si el mes tiene menos de 31 dias, adaptamos los pagos de ese dia a un dia menos
                if($dia_de_pago>(int)$dia_fin_de_mes[2]){
                    $dia_de_pago=$dia_de_pago-($dia_de_pago-$dia_fin_de_mes[2]);
                }

                $fecha_de_pago=$dia_de_pago.'-'.date('m-Y');
		        $trabajador->dia_pago=$fecha_de_pago;

            }
		    return view('trabajadores.index',[
		        'trabajadores'=>$trabajadores,
                'textoBuscado'=>$consulta,
                'usuario'=>auth()->user()->persona,
                'acceso'=>auth()->user()->acceso
            ]);

	    }
    }


    public function store(Request $request)
    {
	    $persona=new Persona();
	    $persona->nombre=$request->nombre;
	    $persona->apellidos=$request->apellidos;
	    $persona->direccion=$request->direccion;
	    $persona->ciudad=$request->ciudad;
	    $persona->telefono=$request->telefono;
	    $persona->correo=$request->email;
	    $persona->save();
	    $idpersona=$persona->idpersona;

	    $trabajador=new Trabajador();
	    $trabajador->fecha_ingreso=$request->fecha_ingreso;
        $trabajador->dia_pago=$request->dia_pago;
	    $trabajador->ciclo_pago=$request->ciclo_pago;
	    $trabajador->sueldo=$request->sueldo;
	    $trabajador->dni=$request->dni;
        $trabajador->cargo=$request->cargo;

	    $persona->trabajador()->save($trabajador);
    }

    public function edit(Request $request,$id)
    {
	    if ( ! $request->ajax() ) {
		    return redirect( '/' );
	    }

	    $trabajador=Trabajador::where('eliminado',0)
            ->where('idempleado',$id)
            ->first();
	    $trabajador->persona;

	    /*$trabajador=DB::table('empleado')
				    ->join('persona', 'persona.idpersona', '=', 'empleado.idempleado')
				    ->select('empleado.*','persona.*')
				    ->where('eliminado','=',0)
	                ->where('idempleado','=',$id)
	                ->first();*/
	    $trabajador->fecha_ingreso=date('Y-m-d', strtotime($trabajador->fecha_ingreso));

	    return response()->json($trabajador);
    }

    public function update(Request $request)
    {
	    $persona=Persona::find($request->idtrabajador);
	    $persona->nombre=$request->nombre;
	    $persona->apellidos=$request->apellidos;
	    $persona->direccion=$request->direccion;
	    $persona->ciudad=$request->ciudad;
	    $persona->telefono=$request->telefono;
	    $persona->correo=$request->email;
	    $persona->save();

	    $trabajador=Trabajador::find($request->idtrabajador);
	    $trabajador->fecha_ingreso=$request->fecha_ingreso;
        $trabajador->dia_pago=$request->dia_pago;
	    $trabajador->ciclo_pago=$request->ciclo_pago;
	    $trabajador->sueldo=$request->sueldo;
	    $trabajador->dni=$request->dni;
        $trabajador->cargo=$request->cargo;

	    $persona->trabajador()->save($trabajador);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
	    $trabajador=Trabajador::findOrFail($id);
	    $trabajador->eliminado=1;
	    $trabajador->update();
    }

    public function verificarUsuario(Request $request){
	    $usuario=DB::table('empleado')
	             ->select('usuario')
	             //->where('eliminado','=',0)
	             ->where('usuario','=',$request->usuario)
	             ->first();
	    if($usuario){
	    	return 1;
	    } else{
	    	return 0;
	    }
    }

    public function gestionar_usuario($id){
	    $trabajador=Trabajador::findOrFail($id);
	    $trabajador->persona;
        $roles = Role::all();
        $user = User::find($id);
        $rol_de_usuario=$user->getRoleNames()->first();
	    return view('trabajadores.usuario',['trabajador'=>$trabajador,'roles'=>$roles,'acceso'=>$rol_de_usuario,'usuario'=>auth()->user()->persona]);
    }

    public function guardar_credenciales(Request $request){
	    $trabajador=Trabajador::find($request->idtrabajador);
	    $trabajador->usuario=$request->usuario;
	    $trabajador->es_usuario=$request->es_usuario;
	    //$trabajador->acceso=$request->acceso;
	    if($request->edicion_password || $request->existe_password==null){
		    $trabajador->password=bcrypt($request->password);
	    }

        $user=User::find($request->idtrabajador);
        $user->syncRoles($request->acceso);


	    $trabajador->save();
    }

	public function eliminar_credenciales(Request $request){
		$trabajador=Trabajador::find($request->idtrabajador);
		$trabajador->usuario='';
		$trabajador->es_usuario=0;
		$trabajador->password=null;
		$trabajador->save();
	}

	public function pagos($idempleado){
        $empleado=Trabajador::find($idempleado);
	    return view('trabajadores.pagos',['usuario'=>auth()->user()->persona,'empleado'=>$empleado]);

    }

    public function obtenerPagos(Request $request){

        return $this->obtener_gastos_empleado($request->idempleado,$request->fecha_in);

	}

	public function imprimir_pagos($id,$fecha){
        $gastos=$this->obtener_gastos_empleado($id,$fecha);
        $trabajador=Trabajador::find($id);
        $trabajador->persona;
        $gastos['trabajador']=$trabajador;

        $view = view('trabajadores/imprimir',$gastos);
        $html=$view->render();
        $pdf=new Html2Pdf('P','A4','es');
        $pdf->pdf->SetTitle('Pago trabajador');
        $pdf->writeHTML($html);
        $pdf->output('Pago-trabajador.pdf');
    }

    public function exportar_pagos($id,$fecha){
        $gastos=$this->obtener_gastos_empleado($id,$fecha);
        return Excel::download(new PagosExport($gastos), 'pagos-trabajador.xlsx');
    }

    public function obtener_gastos_empleado($id,$fecha){
        $gastos=Gastos::where('mes_pago_empleado',explode('-',$fecha)[1])//whereBetween('fecha',[$fecha.'-01 00:00:00',$fecha.'-31 23:59:59'])
        ->where('idempleado',$id)
            ->where('tipo_egreso',4)
            ->orderby('idgasto','desc')->get();

        $total_pagado=0;
        $extras=0;

        foreach ($gastos as $item){

            $item->caja=$item->cajero['nombre'];

            switch($item->tipo_pago_empleado){
                case '1':
                    $item->tipo='Pago de sueldo';
                    $total_pagado+=$item->monto;
                    break;
                case'2':
                    $item->tipo='Adelanto de sueldo';
                    $total_pagado+=$item->monto;
                    break;
                case'3':
                    $item->tipo='Bonificación / aguinaldo';
                    $extras+=$item->monto;
                    break;
            }


        }

        return ['gastos'=>$gastos,'total_pagado'=>$total_pagado,'extras'=>$extras];
    }

}
