<?php

namespace sysfact\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use sysfact\Almacen;
use sysfact\Categoria;
use sysfact\Descuento;
use sysfact\Exports\ProductosExport;
use sysfact\Imports\ProductosImport;
use sysfact\Inventario;
use sysfact\Opciones;
use sysfact\Producto;
use Intervention\Image\ImageManagerStatic as Image;
use sysfact\Ubicacion;

class ProductoController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index(Request $request)
    {
        if ($request){

            try{
                $consulta=trim($request->get('textoBuscado'));
                $filtro=$request->get('filtro');
                $orderby=$request->get('orderby','idproducto');
                $order=$request->get('order', 'desc');

                if($filtro){
                    switch ($filtro){
                        case 'categoria':
                            $productos=Producto::join('categorias', 'categorias.idcategoria', '=', 'productos.idcategoria')
                                ->where('eliminado',0)
                                ->where('cod_producto','!=','00NR')
                                ->select('productos.*','categorias.nombre as categoria')
                                ->where('categorias.nombre','LIKE','%'.$consulta.'%')
                                ->orderby($orderby,$order)
                                ->paginate(30);

                            break;
                        case 'ubicacion':
                            $productos=Producto::join('categorias', 'categorias.idcategoria', '=', 'productos.idcategoria')
                                ->where('eliminado',0)
	                            ->where('cod_producto','!=','00NR')
                                ->select('productos.*','categorias.nombre as categoria')
                                ->where('categorias.nombre','LIKE','%'.$consulta.'%')
                                ->orderby($orderby,$order)
                                ->paginate(30);

                            break;
                        default:
                            $productos=Producto::join('categorias', 'categorias.idcategoria', '=', 'productos.idcategoria')
                                ->where('eliminado',0)
	                            ->where('cod_producto','!=','00NR')
                                ->select('productos.*','categorias.nombre as categoria')
                                ->where($filtro,'LIKE','%'.$consulta.'%')
                                ->orderby($orderby,$order)
                                ->paginate(30);
                    }

                } else {
                    $productos=Producto::join('categorias', 'categorias.idcategoria', '=', 'productos.idcategoria')
                        ->where('eliminado',0)
	                    ->where('cod_producto','!=','00NR')
                        ->select('productos.*','categorias.nombre as categoria')
                        ->where(function ($query) use ($consulta) {
                            $query->where('productos.nombre','LIKE','%'.$consulta.'%')
                                ->orWhere('cod_producto','like','%'.$consulta.'%')
                                ->orWhere('presentacion','like','%'.$consulta.'%');
                        })
                        ->orderby($orderby,$order)
                        ->paginate(30);
                }



                foreach ($productos as $producto){
                    $producto->cantidad=$producto->inventario->first()->saldo;
                    $almacen = DB::table('almacen_productos')->where('idproducto', $producto->idproducto)->orderby('fecha','asc')->first();
                    if($almacen){
                        $ubicacion = Ubicacion::find($almacen->idubicacion);
                        $producto->ubicacion = $ubicacion->nombre;
                    } else {
                        $producto->ubicacion = null;
                    }


                }

                $ultimo_id_registrado=DB::table('productos')
                    ->select('idproducto')
                    ->where('eliminado','=',0)
                    ->orderby('idproducto','desc')
                    ->first();
                if($ultimo_id_registrado==null)$ultimo_id_registrado=['idproducto'=>1];

                $productos->appends($_GET)->links();

                $opcion = Opciones::where('nombre_opcion','col_productos')->first();
                if($opcion){
                    $columnas = json_decode($opcion->valor_json, true);
                } else {
                    $columnas = [
                        'ubicacion'=>false,
                        'codigo'=>true,
                        'tipo_producto'=>true,
                        'marca'=>false,
                        'modelo'=>false,
                        'categoria'=>true,
                        'stock'=>true,
                        'costo'=>true,
                        'precio'=>true,
                        'imagen'=>true,
                        'montaje'=>false,
                        'capsula'=>false,
                        'tipo'=>false,
                        'precio_min'=>false,
                    ];
                }

                return view('productos.index',[
                    'productos'=>$productos,
                    'ultimo_id'=>json_encode($ultimo_id_registrado),
                    'usuario'=>auth()->user()->persona,
                    'textoBuscado'=>$consulta,
                    'order'=>$order=='desc'?'asc':'desc',
                    'orderby'=>$orderby,
                    'order_icon'=>$order=='desc'?'<i class="fas fa-caret-square-up"></i>':'<i class="fas fa-caret-square-down"></i>',
                    'domain'=>app()->domain(),
                    'columnas'=>$columnas
                ]);
            } catch (\Exception $e){
                if($e->getCode()=='42S22'){
                    return redirect('/productos');
                }
                return $e->getMessage();
            }

        }

    }

    public function ocultar_columnas(Request $request){
        try {

            $cols = $request->columnas;

            $opcion = Opciones::where('nombre_opcion','col_productos')->first();

            if(!$opcion){
                $opcion = new Opciones();
                $opcion->nombre_opcion = 'col_productos';
            }

            $opcion->valor_json = $cols;
            $opcion->save();



        } catch (\Exception $e) {
            Log::error($e);
            return response($e->getMessage(), 500);
        }
    }


	public function store(Request $request)
	{
        try{
            $producto=new Producto();
            $producto->cod_producto=strtoupper($request->cod_producto);
            $producto->nombre=strtoupper($request->nombre);
            $presentacion=preg_replace("/[\r\n|\n|\r]+/", " ", $request->presentacion);
            $producto->presentacion=mb_strtoupper($presentacion);
            $producto->precio=$request->precio;
            $producto->costo=$request->costo;
            $producto->moneda_compra = $request->moneda_compra;
            $producto->tipo_cambio = $request->tipo_cambio_compra;
            $producto->eliminado=0;
            $producto->imagen='';
            $producto->marca=strtoupper($request->marca);
            $producto->modelo=strtoupper($request->modelo);
            $producto->param_1=strtoupper($request->param_1);
            $producto->param_2=strtoupper($request->param_2);
            $producto->param_3=strtoupper($request->param_3);
            $producto->param_4=$request->param_4;
            $producto->param_5=$request->param_5;
            $producto->stock_bajo=$request->stock_bajo;
            if($request->tipo_producto==2){
                $producto->stock_bajo=0;
            }
            $producto->idcategoria=$request->idcategoria;
            $producto->tipo_producto=$request->tipo_producto;
            $producto->unidad_medida=$request->medida;
            $producto->moneda=$request->moneda;
            $descuentos = json_decode($request->descuentos, TRUE);
            if($descuentos){
                $producto->discounts = 1;
            } else{
                $producto->discounts = 0;
            }
            $producto->save();

            $inventario=new Inventario();
            $inventario->idempleado=auth()->user()->idempleado;
            $inventario->cantidad=$request->cantidad;
            $inventario->costo = $request->costo;
            $inventario->saldo = $request->cantidad;
            $inventario->moneda = $request->moneda_compra;
            $inventario->tipo_cambio = $request->tipo_cambio_compra;
            $inventario->fecha=date('Y-m-d H:i:m');
            if($request->tipo_producto==2){
                $inventario->cantidad=0;
                $inventario->saldo = 0;
            }
            $inventario->operacion='NUEVO PRODUCTO';
            $inventario->descripcion=$request->observacion;
            $producto->inventario()->save($inventario);


            $producto->almacen()->attach($request->idalmacen, [
                'idubicacion'=>$request->idubicacion,
            ]);


            if($request->tipo_producto == 1){
                foreach ($descuentos as $item){
                    $descuento=new Descuento();
                    $descuento->cantidad_min=$item['cantidad'];
                    $descuento->monto_desc=$item['precio'];
                    $producto->descuento()->save($descuento);
                }
            }

            return response(['mensaje'=>'Se ha registrado el producto'], 200);

        } catch (\Exception $e){
            Log::error($e);
            return response(['mensaje'=>$e->getMessage()], 500);
        }

	}


    public function edit(Request $request,$id)
    {

        if ( ! $request->ajax() ) {
            return redirect( '/' );
        }

		$producto=Producto::find($id);
		$descuentos = Descuento::select('cantidad_min as cantidad','monto_desc as precio')->where('idproducto',$id)->get();
		$inventario=Inventario::select('cantidad')->where('idproducto',$id)->get();
		$almacen = DB::table('almacen_productos')
            ->where('idproducto',$id)
            ->orderBy('fecha', 'asc')
            ->first();

        $suma=0;
        foreach ($inventario as $inv){
            $suma+=$inv->cantidad;
        }

        $barcode = new DNS1D();
        $producto->barcode=$barcode->getBarcodePNG($id, "C39+");

		$producto->cantidad=$suma;
        $producto->descuentos=$descuentos;
         if($almacen){
             $producto->almacen = $almacen;
         } else {
             $producto->almacen = ['idalmacen'=>null, 'idubicacion'=>null];
         }

		return $producto;
	}

    public function agregar_desde_barcode(Request $request, $id){
        if ( ! $request->ajax() ) {
            return redirect( '/' );
        }

        $producto=Producto::find($id);

        $inventario=Inventario::select('cantidad')
            ->where('idproducto','=',$id)
            ->get();

        $suma=0;
        foreach ($inventario as $inv){
            $suma+=$inv->cantidad;
        }

        $barcode = new DNS1D();
        $producto->barcode=$barcode->getBarcodePNG($id, "C39+");

        $producto->cantidad=$suma;

        return $producto;
    }

    public function update(Request $request)
    {
        try{
            $producto=Producto::find($request->idproducto);
            $producto->cod_producto=mb_strtoupper($request->cod_producto);
            $producto->nombre=mb_strtoupper($request->nombre);
            $presentacion=preg_replace("/[\r\n|\n|\r]+/", " ", $request->presentacion);
            $producto->presentacion=mb_strtoupper($presentacion);
            $producto->precio=$request->precio;
            $producto->costo=$request->costo;
            $producto->moneda_compra = $request->moneda_compra;
            $producto->tipo_cambio = $request->tipo_cambio_compra;
            $producto->stock_bajo=$request->stock_bajo;
            $producto->moneda=$request->moneda;
            $producto->marca=strtoupper($request->marca);
            $producto->modelo=strtoupper($request->modelo);
            $producto->param_1=strtoupper($request->param_1);
            $producto->param_2=strtoupper($request->param_2);
            $producto->param_3=strtoupper($request->param_3);
            $producto->param_4=$request->param_4;
            $producto->param_5=$request->param_5;
            if($request->tipo_producto==2){
                $producto->stock_bajo=0;
            }
            $producto->idcategoria=$request->idcategoria;
            $producto->tipo_producto=$request->tipo_producto;
            $producto->unidad_medida=$request->medida;
            $descuentos = json_decode($request->descuentos, TRUE);
            if($descuentos){
                $producto->discounts = 1;
            } else {
                $producto->discounts = 0;
            }
            $producto->save();

            if($request->cantidad!=$request->cantidad_aux){
                $inventario=new Inventario();
                $inventario->idempleado=auth()->user()->idempleado;
                $inventario->cantidad=$request->cantidad-$request->cantidad_aux;
                $inventario->saldo = $request->cantidad;
                $inventario->fecha=date('Y-m-d H:i:m');
                if($request->tipo_producto==2){
                    $inventario->cantidad=0;
                    $inventario->saldo = 0;
                }
                $inventario->costo = $request->costo;
                $inventario->moneda = $request->moneda_compra;
                $inventario->tipo_cambio = $request->tipo_cambio_compra;
                $inventario->operacion='EDICIÓN MANUAL';
                $inventario->descripcion=$request->observacion;

                $producto->inventario()->save($inventario);
            }

            DB::table('almacen_productos')
                ->where('idproducto',$request->idproducto)
                ->update([
                    'idalmacen'=>$request->idalmacen,
                    'idubicacion'=>$request->idubicacion
                ]);

            DB::table('descuentos')->where('idproducto',$request->idproducto)->delete();
            if($request->tipo_producto == 1){
                foreach ($descuentos as $item){
                    $descuento=new Descuento();
                    $descuento->cantidad_min=$item['cantidad'];
                    $descuento->monto_desc=$item['precio'];
                    $producto->descuento()->save($descuento);
                }
            }
        } catch (\Exception $e){
            return $e->getMessage();
        }

	}

    public function inventario(Request $request,$id){

        $producto=Producto::find($id);
        $inventario=$producto->inventario()->paginate(30);
        $suma=0;

        if($producto->tipo_producto==2){
            foreach ($inventario as $item){
                $item->cantidad = $item->cantidad * -1;
                $suma+=$item->cantidad;
            }
            $producto->total=$suma;
        }

        return view('productos.inventario',['inventario'=>$inventario,'producto'=>$producto,'usuario'=>auth()->user()->persona]);

    }

    public function destroy($id)
    {
        $producto=Producto::findOrFail($id);
        $producto->eliminado=1;
        $producto->update();
        //return Redirect::to('productos');
    }

    public function exportar(){
        return Excel::download(new ProductosExport, 'productos.xlsx');
    }

    public function importar_productos(Request $request){

        try{

            if($request->hasFile('excel_file')){
                Excel::import(new ProductosImport(), $request->file('excel_file'));
                return 1;
            }
            return 0;

        } catch (\Exception $e){
            Log::error($e);
            return $e->getMessage();
        }

    }

    public function descargar_formato_importacion(){

        $pathtoFile = public_path().'/files/formato-importacion-de-productos.xlsx';
        return response()->download($pathtoFile);

    }

    public function mostrar_categorias()
    {
        return ['categorias'=>Categoria::all()];

    }

    public function mostrar_almacen()
    {
        return ['almacen'=>Almacen::where('eliminado',0)->get()];

    }

    public function mostrar_ubicacion($id)
    {
        return ['ubicacion'=>Ubicacion::where('eliminado',0)->where('idalmacen',$id)->get()];

    }

    public function generar_codigo_producto(){
        $ultimo_id_registrado=DB::table('productos')
            ->select('idproducto')
            ->where('eliminado',0)
            ->orderby('idproducto','desc')
            ->first();
        if($ultimo_id_registrado){
            return 'PR'.substr(date('Y'),2,2).$ultimo_id_registrado->idproducto;
        } else {
            return 'PR'.substr(date('Y'),2,2).'1';
        }

    }

    public function agregar_imagen(Request $request){
        try{

            $producto=Producto::find($request->idproducto);
            $producto->imagen = $request->imagen;
            $producto->save();
            return 1;

            /*if($request->hasFile('imagen')){
                if ($request->file('imagen')->isValid()) {

                   $validated = $request->validate([
                        'name' => 'string|max:100',
                        'image' => 'mimes:jpeg,png|max:1014',
                    ]);

                    $image=$request->file('imagen');
                    $filename   = time() . '.' . $image->getClientOriginalExtension();

                    $img = Image::make($image->getRealPath());
                    $img->resize(200, 200, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                    $img->save(public_path('images/image-products/' .$filename));


                    $producto=Producto::find($request->idproducto);
                    if($producto->imagen){
                        $nombre_imagen_a_eliminar=$producto->imagen;
                        unlink(public_path().'/images/image-products/' .$nombre_imagen_a_eliminar);
                    }
                    $producto->imagen = $filename;
                    $producto->save();

                    return 1;
                }
                return 'La imagen no es válida';
            } else{
                return 'La imagen es demasido grande, tamaño máximo 1MB';
            }*/
        } catch (\Exception $e){
            Log::error($e);
            return $e->getMessage();
        }
    }

    public function temp_saldo_productos(){
        try{
            DB::beginTransaction();
            $data = null;
            $productos = Producto::all();
            foreach ($productos as $producto) {
                $saldo_anterior = 0;
                $saldo = 0;
                $inventario = $producto->inventario->sortby('idinventario');
                $i = 0;
                foreach ($inventario as $item){
                    if($i==0){
                        $saldo = $item->cantidad;
                    } else {
                        $saldo = $saldo_anterior + $item->cantidad;
                    }

                    $inv = Inventario::find($item->idinventario);
                    $inv->update([
                        'saldo'=>$saldo
                    ]);
                    $saldo_anterior = $saldo;
                    $data[]=$item->idinventario;
                    $i++;
                }
            }

            DB::commit();
            return 'success';

        } catch (\Exception $e){
            DB::rollBack();
            return $e->getMessage();
        }
    }

    public function temp_almacen(){
        try{
            DB::beginTransaction();

            $productos = Producto::all();

            foreach ($productos as $producto) {
                $almacen = $producto->almacen;
                if(count($almacen) == 0){
                    $producto->almacen()->attach(1, [
                        'idubicacion'=>1,
                    ]);
                }
            }

            DB::commit();
            return 'success';

        } catch (\Exception $e){
            DB::rollBack();
            return $e->getMessage();
        }
    }
}
