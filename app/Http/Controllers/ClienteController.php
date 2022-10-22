<?php

namespace sysfact\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use sysfact\Cliente;
use Illuminate\Http\Request;
use sysfact\Exports\ClientesExport;
use sysfact\Imports\ClientesImport;
use sysfact\Persona;

class ClienteController extends Controller
{

	public function __construct()
	{
		$this->middleware('auth');
	}

    public function index(Request $request)
    {
	    if ($request){

	        try{
                $consulta=trim($request->textoBuscado);
                $orderby=$request->get('orderby','idcliente');
                $order=$request->get('order', 'desc');

                $clientes=DB::table('cliente')
                    ->join('persona', 'persona.idpersona', '=', 'cliente.idcliente')
                    ->select('cliente.*','persona.*')
                    ->where('eliminado',0)
                    ->where(function ($query) use ($consulta) {
                        $query->where('nombre','like','%'.$consulta.'%')
                            ->orWhere('cod_cliente','like','%'.$consulta.'%')
                            ->orWhere('num_documento','like','%'.$consulta.'%');
                    })
                    ->orderby($orderby,$order)
                    ->paginate(30);

                $clientes->appends($_GET)->links();

                return view('clientes.index',[
                    'clientes'=>$clientes,
                    'usuario'=>auth()->user()->persona,
                    'textoBuscado'=>$consulta,
                    'order'=>$order=='desc'?'asc':'desc',
                    'orderby'=>$orderby,
                    'order_icon'=>$order=='desc'?'<i class="fas fa-caret-square-up"></i>':'<i class="fas fa-caret-square-down"></i>'
                ]);
            } catch (\Exception $e){
                if($e->getCode()=='42S22'){
                    return redirect('/clientes');
                }
                return $e->getMessage();
            }

	    }
    }

    public function store(Request $request)
    {
        try{
            if(Cliente::where('num_documento', $request->num_documento)
                ->where('eliminado',0)
                ->exists()
            ){
                return 1;
            }

	    $persona=new Persona();
	    $persona->nombre=mb_strtoupper($request->nombre);
	    $persona->direccion=mb_strtoupper($request->direccion);
	    $persona->telefono=$request->telefono;
	    $persona->correo=$request->email;
	    $persona->save();
	    $id=$persona->idpersona;

	    $cliente=new Cliente();
	    $codigo=$this->generar_codigo_cliente();
	    $cliente->cod_cliente=$codigo;
	    $cliente->num_documento=$request->num_documento??$codigo;
	    $cliente->tipo_documento=$request->tipo_documento;
	    $cliente->eliminado=0;
	    $persona->cliente()->save($cliente);

	    return response()->json([
            "idcliente"=>$id,
            "cod_cliente"=>$codigo,
            "nombre"=>$request->nombre,
            "persona"=>["nombre"=>$request->nombre],
            "num_documento"=>$request->num_documento??$codigo
        ],200);

        } catch (\Exception $e){
            return response(['mensaje'=>'Ha ocurrido un error al guardar el cliente'], 500);
        }
    }

    public function edit(Request $request,$id)
    {
	    if ( ! $request->ajax() ) {
		    return redirect( '/' );
	    }

        $cliente = DB::table('cliente')
            ->join('persona', 'persona.idpersona', '=', 'cliente.idcliente')
            ->select('cliente.*', 'persona.*')
            ->where('eliminado', '=', 0)
            ->where('idcliente', '=', $id)
            ->first();

	    return json_encode($cliente);
    }

    public function update(Request $request)
    {
	    $persona=Persona::find($request->idcliente);
	    $persona->nombre=mb_strtoupper($request->nombre);
	    $persona->direccion=mb_strtoupper($request->direccion);
	    $persona->telefono=$request->telefono;
	    $persona->correo=$request->email;
	    $persona->save();

	    $cliente=Cliente::find($request->idcliente);
	    $cliente->cod_cliente=mb_strtoupper($request->cod_cliente);
	    $cliente->num_documento=$request->num_documento??'';
	    $cliente->tipo_documento=$request->tipo_documento;
	    $persona->cliente()->save($cliente);
    }

    public function destroy($id)
    {
	    $cliente=Cliente::findOrFail($id);
	    $cliente->eliminado=1;
	    $cliente->update();
    }

    public function exportar(){
	    return Excel::download(new ClientesExport, 'clientes.xlsx');
    }

    public function importar_clientes(Request $request){

        try{

            if($request->hasFile('excel_file')){
                Excel::import(new ClientesImport(), $request->file('excel_file'));
                return 1;
            }
            return 0;

        } catch (\Exception $e){
            return $e;
        }

    }

    public function descargar_formato_importacion(){

        $pathtoFile = public_path().'/files/formato-importacion-de-clientes.xlsx';
        return response()->download($pathtoFile);

    }

    public function generar_codigo_cliente(){
        $ultimo_id_registrado=DB::table('cliente')
            ->select('idcliente')
            ->where('eliminado',0)
            ->orderby('idcliente','desc')
            ->first();
        if(!$ultimo_id_registrado){
            $ultimo_id_registrado = 1;
        } else {
            $ultimo_id_registrado = $ultimo_id_registrado->idcliente;
        }
        return 'CL'.date('Y').$ultimo_id_registrado;
    }

    public function store_alt(Request $request)
    {
        if(Cliente::where('num_documento', $request->num_documento)
            ->where('eliminado',0)
            ->exists()
        ){
            return 1;
        }

        $persona=new Persona();
        $persona->nombre=mb_strtoupper($request->nombre);
        $persona->direccion=mb_strtoupper($request->direccion);
        $persona->telefono=$request->telefono;
        $persona->correo=$request->email;
        $persona->save();
        $id=$persona->idpersona;

        $cliente=new Cliente();
        $codigo=$this->generar_codigo_cliente();
        $cliente->cod_cliente=$codigo;
        $cliente->num_documento=$request->num_documento;
        $cliente->tipo_documento=$request->tipo_documento;
        $cliente->eliminado=0;
        $persona->cliente()->save($cliente);

        return $id;
    }
}
