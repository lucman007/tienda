<?php

namespace sysfact\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use sysfact\Almacen;
use sysfact\Ubicacion;

class AlmacenController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request){

        if ($request){
            try{

                $consulta=trim($request->get('textoBuscado'));

                $almacen = Almacen::where('eliminado',0)
                    ->where('nombre','like','%'.$consulta.'%')
                    ->orderby('idalmacen','desc')
                    ->paginate(30);

                return view('almacen.index',[
                    'usuario'=>auth()->user()->persona,
                    'almacen'=>$almacen,
                    'textoBuscado'=>$consulta,
                ]);

            } catch (\Exception $e){
                Log::error($e);
                return $e->getMessage();
            }
        }


    }

    public function store(Request $request){
        try{
            $codigo = $this->generar_codigo();
            $almacen = new Almacen();
            $almacen->codigo = $codigo;
            $almacen->nombre = mb_strtoupper($request->nombre);
            $almacen->save();
            return response('Se ha guardado el almacén', 200);

        } catch (\Exception $e){
            Log::error($e);
            return response($e->getMessage(), 500);
        }
    }

    public function edit(Request $request, $id){
        if (!$request->ajax()) {
            return redirect('/');
        }
        $almacen = Almacen::find($id);
        return $almacen;
    }

    public function editarUbicacion(Request $request, $idalmacen){
        if (!$request->ajax()) {
            return redirect('/');
        }
        $almacen = Almacen::find($idalmacen);
        $ubicacion = $almacen->ubicacion;
        return response()->json($ubicacion);
    }

    public function storeUbicacion(Request $request){
        try{

            $data = json_decode($request->ubicacion, true);

            foreach ($data as $item){
                $ubicacion = Ubicacion::find($item['idubicacion']);
                if(!$ubicacion){
                    $ubicacion = new Ubicacion();

                    if($item['eliminado']){
                        break;
                    }

                } else {
                    if($item['eliminado']){
                        $ubicacion->eliminado = 1;
                    }
                }

                $ubicacion->nombre = mb_strtoupper($item['nombre']);
                $ubicacion->idalmacen = mb_strtoupper($item['idalmacen']);
                $ubicacion->descripcion = mb_strtoupper($item['descripcion']);
                $ubicacion->save();
                $idubicacion = $ubicacion->idubicacion;

                if($request->origen){
                    return $idubicacion;
                }

            }

            return response('Se ha registrado la ubicación', 200);

        } catch (\Exception $e){
            Log::error($e);
            return response($e->getMessage(), 500);
        }
    }

    public function update(Request $request){
        try{
            $almacen = Almacen::find($request->idalmacen);
            $almacen->nombre = mb_strtoupper($request->nombre);
            $almacen->save();
            return response('Se ha actuailizado el almacén', 200);

        } catch (\Exception $e){
            Log::error($e);
            return response($e->getMessage(), 500);
        }
    }

    public function destroy($id){
        $almacen = Almacen::find($id);
        $almacen->eliminado = 1;
        $almacen->save();
    }

    public function generar_codigo(){
        $ultimo_id_registrado=DB::table('almacen')
            ->select('idalmacen')
            ->orderby('idalmacen','desc')
            ->first();
        if($ultimo_id_registrado){
            return str_pad($ultimo_id_registrado->idalmacen + 1,3,'0', STR_PAD_LEFT);
        } else {
            return '001';
        }
    }
}
