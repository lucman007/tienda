<?php

namespace sysfact\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use sysfact\Persona;
use sysfact\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{

	public function __construct()
	{
        $this->middleware('auth');
	}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
	    if ($request){
		    $consulta=trim($request->textoBuscado);

            $proveedor = DB::table('proveedores')
                ->join('persona', 'persona.idpersona', '=', 'proveedores.idproveedor')
                ->select('proveedores.*', 'persona.*')
                ->where('eliminado', '=', 0)
                ->where(function ($query) use ($consulta) {
                    $query->where('nombre', 'LIKE', '%' . $consulta . '%')
                        ->orWhere('codigo', 'like', '%' . $consulta . '%');
                })
                ->orderby('idproveedor', 'desc')
                ->paginate(30);

            $ultimo_id_registrado=DB::table('proveedores')
                ->select('idproveedor')
                ->where('eliminado','=',0)
                ->orderby('idproveedor','desc')
                ->first();

            if($ultimo_id_registrado==null)$ultimo_id_registrado=['idproveedor'=>1];

		    return view('proveedores.index',['proveedores'=>$proveedor,'textoBuscado'=>$consulta,
                'ultimo_id'=>json_encode($ultimo_id_registrado),'usuario'=>auth()->user()->persona]);

	    }
    }


    public function store(Request $request)
    {
        try{
            if(Proveedor::where('num_documento', $request->num_documento)
                ->where('eliminado',0)
                ->exists()
            ){
                return 1;
            }
            $persona=new Persona();
            $persona->nombre=mb_strtoupper($request->nombre);
            $persona->direccion=mb_strtoupper($request->direccion);
            $persona->telefono=$request->telefono;
            $persona->correo=$request->correo;
            $persona->save();

            $proveedor=new Proveedor();
            $codigo=$this->generar_codigo_proveedor();
            $proveedor->codigo=$codigo;
            $proveedor->num_documento=$request->num_documento;
            $proveedor->tipo_documento=$request->tipo_documento;
            $proveedor->contacto=mb_strtoupper($request->contacto);
            $proveedor->telefono_2=$request->telefono_2;
            $proveedor->web=$request->web;
            $proveedor->cuentas=$request->cuentas;
            $proveedor->observaciones=$request->observacion;
            $proveedor->eliminado=0;
            $persona->proveedor()->save($proveedor);

            $proveedor = Proveedor::find($persona->idpersona);
            $proveedor->persona;
            return $proveedor;

        } catch (\Exception $e){
            Log::error($e);
            return $e->getMessage();
        }
    }


    public function edit(Request $request, $id)
    {
	    if ( ! $request->ajax() ) {
		    return redirect( '/' );
	    }

        $proveedor = DB::table('proveedores')
            ->join('persona', 'persona.idpersona', '=', 'proveedores.idproveedor')
            ->select('proveedores.*', 'persona.*')
            ->where('eliminado', '=', 0)
            ->where('idproveedor', '=', $id)
            ->first();

	    //$proveedor->cuentas = json_decode($proveedor->observaciones, true);

	    return json_encode($proveedor);
    }

    public function update(Request $request)
    {
	    $persona=Persona::find($request->idproveedor);
	    $persona->nombre=mb_strtoupper($request->nombre);
	    $persona->direccion=mb_strtoupper($request->direccion);
	    $persona->telefono=$request->telefono;
	    $persona->correo=$request->correo;
	    $persona->save();

	    $proveedor=Proveedor::find($request->idproveedor);
	    $proveedor->codigo=mb_strtoupper($request->codigo);
	    $proveedor->num_documento=$request->num_documento;
	    $proveedor->tipo_documento=$request->tipo_documento;
	    $proveedor->contacto=mb_strtoupper($request->contacto);
	    $proveedor->telefono_2=$request->telefono_2;
	    $proveedor->web=$request->web;
	    $proveedor->cuentas=$request->cuentas;
        $proveedor->observaciones=$request->observacion;

	    $persona->proveedor()->save($proveedor);
    }


    public function generar_codigo_proveedor(){
        $ultimo_id_registrado=DB::table('proveedores')
            ->select('idproveedor')
            ->where('eliminado',0)
            ->orderby('idproveedor','desc')
            ->first();
        if(!$ultimo_id_registrado){
            $ultimo_id_registrado = 1;
        } else {
            $ultimo_id_registrado = $ultimo_id_registrado->idproveedor;
        }
        return 'PRV'.date('Y').$ultimo_id_registrado;
    }

    public function destroy($id)
    {
	    $proveedor=Proveedor::findOrFail($id);
	    $proveedor->eliminado=1;
	    $proveedor->update();
    }
}
