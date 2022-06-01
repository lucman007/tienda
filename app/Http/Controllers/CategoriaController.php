<?php

namespace sysfact\Http\Controllers;

use Illuminate\Http\Request;
use sysfact\Categoria;

class CategoriaController extends Controller
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
		    $consulta=trim($request->get('textoBuscado'));

		    $categorias=Categoria::where('nombre','LIKE','%'.$consulta.'%')
		                       ->paginate(30);

		    return view('categorias.index',['categorias'=>$categorias,'textoBuscado'=>$consulta,'usuario'=>auth()->user()->persona]);

	    }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {

	    $categoria                  = new Categoria();
	    $categoria->nombre          = mb_strtoupper($request->nombre);
	    $categoria->descripcion = mb_strtoupper($request->descripcion);
	    $categoria->color = $request->color;
	    $categoria->save();

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
	    return ['categorias'=>Categoria::all()];

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request,$id)
    {
	    if ( ! $request->ajax() ) {
		    return redirect( '/' );
	    }

	    $categoria=Categoria::find($id);

	    return $categoria;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
	    $categoria                  = Categoria::find($request->idcategoria);
	    $categoria->nombre          = mb_strtoupper($request->nombre);
	    $categoria->descripcion = mb_strtoupper($request->descripcion);
        $categoria->color = $request->color;
	    $categoria->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
	    $categoria=Categoria::find($id);
	    $categoria->delete();
    }
}
