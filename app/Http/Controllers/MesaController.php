<?php

namespace sysfact\Http\Controllers;

use Illuminate\Http\Request;
use sysfact\Mesa;
use sysfact\Orden;

class MesaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if ($request){
            $consulta=trim($request->get('textoBuscado'));

            $mesas=Mesa::where('numero','LIKE','%'.$consulta.'%')
                ->where('idmesa','!=',-1)
                ->orderby('numero','desc')
                ->paginate(60);

            return view('mesas.index',['mesas'=>$mesas,'textoBuscado'=>$consulta,'usuario'=>auth()->user()->persona]);

        }
    }

    public function store(Request $request) {

        try{
            $mesa=Mesa::where('numero',$request->numero)
                ->exists();
            if($mesa){
                return "El número de mesa ya existe";
            } else{
                $mesa = new Mesa();
                $mesa->numero = $request->numero;
                $mesa->piso = $request->piso;
                $mesa->estado=0;
                $mesa->observacion = mb_strtoupper($request->observacion);
                $success = $mesa->save();
                return $success?1:0;
            }
        } catch (\Exception $e){
            return $e;
        }
    }

    public function update(Request $request)
    {
        $mesa = Mesa::find($request->idmesa);
        $mesa->numero = $request->numero;
        $mesa->piso = $request->piso;
        $mesa->observacion = mb_strtoupper($request->observacion);
        $mesa->save();
    }

    public function edit(Request $request,$id)
    {
        if ( ! $request->ajax() ) {
            return redirect( '/' );
        }

        $mesa=Mesa::find($id);

        return $mesa;
    }

    public function destroy($id)
    {
        $mesa=Mesa::find($id);

        $pedido=Orden::where('idmesa',$id)
            ->orderby('idorden','desc')
            ->exists();

        if($pedido){
            return "La mesa no se puede eliminar porque se está usando en algunos pedidos";
        } else{
            $mesa->delete();
            return 1;
        }

    }

}
