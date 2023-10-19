<?php

namespace sysfact\Http\Controllers;

use Illuminate\Http\Request;
use sysfact\Categoria;
use sysfact\Producto;

class CategoriaController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if ($request) {
            $consulta = trim($request->get('textoBuscado'));

            $categorias = Categoria::where('nombre', 'LIKE', '%' . $consulta . '%')
                ->where('eliminado', 0)
                ->where('idcategoria', '!=', -1)
                ->paginate(30);

            $categorias->each(function ($categoria) {
                $cantidadProductos = $categoria->producto()
                    ->where('eliminado', 0)
                    ->count();

                $categoria->cantidad_productos = $cantidadProductos;
            });

            return view('categorias.index', ['categorias' => $categorias, 'textoBuscado' => $consulta, 'usuario' => auth()->user()->persona]);

        }
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {

        $categoria = new Categoria();
        $categoria->nombre = mb_strtoupper($request->nombre);
        $categoria->descripcion = mb_strtoupper($request->descripcion);
        $categoria->color = $request->color;
        $categoria->save();

    }

    public function show()
    {
        return ['categorias' => Categoria::where('eliminado',0)->get()];

    }

    public function edit(Request $request, $id)
    {
        if (!$request->ajax()) {
            return redirect('/');
        }

        $categoria = Categoria::find($id);

        return $categoria;
    }

    public function update(Request $request)
    {
        $categoria = Categoria::find($request->idcategoria);
        $categoria->nombre = mb_strtoupper($request->nombre);
        $categoria->descripcion = mb_strtoupper($request->descripcion);
        $categoria->color = $request->color;
        $categoria->save();
    }

    public function check_productos($id){
        $productos = Producto::where('eliminado',0)
            ->where('idcategoria',$id)
            ->exists();

        if($productos){
            return 1;
        }

        return 0;

    }

    public function destroy($id)
    {
        $categoria = Categoria::find($id);
        $categoria->update([
            'eliminado'=>1
        ]);

    }
}
