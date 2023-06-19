<?php

namespace sysfact\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spipu\Html2Pdf\Html2Pdf;
use sysfact\Emisor;
use sysfact\Presupuesto;
use sysfact\Produccion;
use sysfact\Producto;
use Intervention\Image\ImageManagerStatic as Image;

class ProduccionController extends Controller
{

    private $dropbox;

    public function __construct()
    {
        $this->middleware('auth');
        //$this->dropbox = Storage::disk('dropbox')->getDriver()->getAdapter()->getClient();
    }

    public function index(Request $request,$estado)
    {
        if($estado=='pendientes'){
            $estado='pendiente';
        } else if($estado=='completadas'){
            $estado='completada';
        }

        if ($request){
            $consulta=trim($request->get('textoBuscado'));

            $produccion=DB::table('produccion')
                ->join('persona', 'persona.idpersona', '=', 'produccion.idcliente')
                ->select('produccion.*','persona.nombre')
                ->where('produccion.eliminado',0)
                ->where('produccion.estado',$estado)
                ->where(function ($query) use ($consulta) {
                    $query->where('persona.nombre','like','%'.$consulta.'%')
                        ->orWhere('produccion.correlativo','like','%'.$consulta.'%');
                })
                ->orderby('produccion.idproduccion','desc')
                ->paginate(30);


            return view('produccion.index',['produccion'=>$produccion,'textoBuscado'=>$consulta,'active'=>$estado,'usuario'=>auth()->user()->persona]);

        }
    }

    public function nueva_produccion(){
        $ultimo_id_registrado=DB::table('productos')
            ->select('idproducto')
            ->where('eliminado','=',0)
            ->orderby('idproducto','desc')
            ->first();
        if($ultimo_id_registrado==null)$ultimo_id_registrado=['idproducto'=>1];
        return view('produccion.nuevo',['usuario'=>auth()->user()->persona,'ultimo_id'=>json_encode($ultimo_id_registrado)]);
    }

    public function nuevo_desde_cotizacion($idcotizacion){
        $presupuesto = Presupuesto::find($idcotizacion);
        $productos = $presupuesto->productos;
        $presupuesto->cliente->persona;

        foreach ($productos as $producto) {

            $producto->codigo_fabricacion = '';
            $producto->cantidad = $producto->detalle->cantidad;
            $producto->presentacion = strip_tags($producto->detalle->descripcion);
        }

        return json_encode($presupuesto);

    }

    public function obtenerCorrelativo()
    {

        $produccion = DB::table('produccion')
            ->select('correlativo')
            ->orderby('idproduccion', 'desc')
            ->first();

        if ($produccion) {
            $produccion = explode('-', $produccion->correlativo);
            $correlativo = '001-' . str_pad($produccion[1] + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $correlativo = '001-000001';
        }

        return json_encode($correlativo);
    }

    public function obtenerClientes(Request $request)
    {
        if ( ! $request->ajax() ) {
            return redirect( '/' );
        }

        $consulta=trim($request->get('textoBuscado'));

        $cliente = DB::table('cliente')
            ->join('persona', 'persona.idpersona', '=', 'cliente.idcliente')
            ->select('cliente.*', 'persona.nombre', 'persona.direccion')
            ->where('eliminado', '=', 0)
            ->where(function ($query) use ($consulta) {
                $query->where('nombre', 'like', '%' . $consulta . '%')
                    ->orWhere('num_documento','like','%'.$consulta.'%');
            })
            ->orderby('idcliente', 'desc')
            ->take(5)
            ->get();

        return json_encode($cliente);
    }

    public function obtenerProductos(Request $request)
    {
        $consulta=trim($request->get('textoBuscado'));

        $productos=Producto::with('inventario:idproducto,cantidad')
            ->where('eliminado','=',0)
            ->where(function ($query) use ($consulta) {
                $query->where('nombre', 'like', '%' . $consulta . '%')
                    ->orWhere('cod_producto','like','%'.$consulta.'%');
            })
            ->orderby('idproducto','desc')
            ->take(5)->get();

        foreach ($productos as $producto){
            foreach ($producto->inventario as $kardex){
                $producto->stock+=$kardex->cantidad;
            }
        }

        return json_encode($productos);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try{
            $produccion=new Produccion();
            $produccion->idempleado=auth()->user()->idempleado;
            if($request->idcliente==''){
                $produccion->idcliente=-1;
            } else{
                $produccion->idcliente=$request->idcliente;
            }

            $produccion->fecha_emision=date('Y-m-d H:i:s');
            $produccion->fecha_entrega=$request->fecha_entrega . ' ' . date('H:i:s');
            $produccion->correlativo=json_decode($this->obtenerCorrelativo());
            $nota = [];
            $nota['editado_por']=mb_strtoupper($request->editado_por);
            $nota['fabricado_por']=mb_strtoupper($request->fabricado_por);
            $observaciones=preg_replace("/[\r\n|\n|\r]+/", " ", $request->observaciones);
            $produccion->observacion=mb_strtoupper($observaciones);
            $produccion->prioridad = $request->prioridad;
            $produccion->nota = json_encode($nota);
            $produccion->num_oc = $request->num_oc;
            $produccion->estado = 'PENDIENTE';
            $produccion->save();
            $idproduccion=$produccion->idproduccion;

            $detalle=[];
            $items=json_decode($request->items, TRUE);
            $i=1;
            foreach ($items as $item){
                $detalle['num_item']=$i;
                $detalle['cantidad']=$item['cantidad']?$item['cantidad']:'0';
                $detalle['codigo_fabricacion']=$item['codigo_fabricacion'];
                $detalle['descripcion']=nl2br(trim($item['presentacion']));
                $detalle['idproducto']=$item['idproducto'];
                $detalle['idproduccion']=$idproduccion;
                DB::table('produccion_detalle')->insert($detalle);

                $i++;
            }

            DB::commit();

            return $idproduccion;

        } catch (\Exception $e){
            DB::rollback();
            return $e->getMessage();
        }


    }

    public function editar(Request $request, $id)
    {
        $produccion=Produccion::find($id);
        $produccion->productos;
        $produccion->persona;

        $nota = json_decode($produccion->nota, TRUE);
        $adjuntos = json_decode($produccion->adjuntos);

        $a=[];

        if($adjuntos){
            foreach ($adjuntos as $adjunto){
                $filename = basename(parse_url($adjunto, PHP_URL_PATH));
                $file=explode('.',$filename);
                if($file[1] == 'pdf'){
                    $ext='data:application/pdf';
                } else{
                    $ext='data:image/'.$file[1];
                }

                $a[]=['data_file'=>null,'preview'=>'/images/thumbs/'.$filename,'esSubido'=>0, 'esEliminado'=>0, 'type_file'=>$ext, 'name'=>$file[0],'url'=>$adjunto];
            }
        }

        $produccion->adjuntos = json_encode($a);

        $produccion->editado_por=$nota['editado_por'];
        $produccion->fabricado_por=$nota['fabricado_por'];

        $productos=[];
        $i=0;
        foreach($produccion->productos as $product){
            $productos[$i]['cantidad']=$product->detalle->cantidad;
            $productos[$i]['cod_producto']=$product->cod_producto;
            $productos[$i]['idproducto']=$product->idproducto;
            $productos[$i]['nombre']=$product->nombre;
            $productos[$i]['num_item']=$product->detalle->num_item;
            $productos[$i]['codigo_fabricacion']=$product->detalle->codigo_fabricacion;
            $productos[$i]['presentacion']=strip_tags($product->detalle->descripcion);
            $i++;

        }

        $ultimo_id_registrado=DB::table('productos')
            ->select('idproducto')
            ->where('eliminado','=',0)
            ->orderby('idproducto','desc')
            ->first();
        if($ultimo_id_registrado==null)$ultimo_id_registrado=['idproducto'=>1];

        return view('produccion.editar',['produccion'=>$produccion,'productos'=>json_encode($productos),'usuario'=>auth()->user()->persona,'ultimo_id'=>json_encode($ultimo_id_registrado)]);
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try{
            $produccion=Produccion::find($request->idproduccion);
            $produccion->idempleado=auth()->user()->idempleado;
            if($request->idcliente==''){
                $produccion->idcliente=-1;
            } else{
                $produccion->idcliente=$request->idcliente;
            }

            $produccion->fecha_emision=date('Y-m-d H:i:s');
            $produccion->fecha_entrega=$request->fecha_entrega . ' ' . date('H:i:s');
            $nota = [];
            $nota['editado_por']=mb_strtoupper($request->editado_por);
            $nota['fabricado_por']=mb_strtoupper($request->fabricado_por);
            $observaciones=preg_replace("/[\r\n|\n|\r]+/", " ", $request->observaciones);
            $produccion->observacion=mb_strtoupper($observaciones);
            $produccion->prioridad = $request->prioridad;
            $produccion->nota = json_encode($nota);
            $produccion->num_oc = $request->num_oc;
            $produccion->save();
            $idproduccion=$produccion->idproduccion;

            $detalle=[];
            $items=json_decode($request->items, TRUE);
            $i=1;

            DB::table('produccion_detalle')->where('idproduccion', $request->idproduccion)->delete();

            foreach ($items as $item){
                $detalle['num_item']=$i;
                $detalle['cantidad']=$item['cantidad']?$item['cantidad']:'0';
                $detalle['descripcion']=nl2br(trim($item['presentacion']));
                $detalle['idproducto']=$item['idproducto'];
                $detalle['codigo_fabricacion']=$item['codigo_fabricacion'];
                $detalle['idproduccion']=$idproduccion;
                DB::table('produccion_detalle')->insert($detalle);

                $i++;
            }

            DB::commit();

            return $idproduccion;

        } catch (\Exception $e){
            DB::rollback();
            return $e;
        }

    }

    public function destroy($id)    {
        $produccion=Produccion::findOrFail($id);
        $produccion->eliminado=1;
        $produccion->update();
    }

    public function imprimir(Request $request, $id)
    {
        $produccion=Produccion::find($id);
        $produccion->productos;
        $usuario=$produccion->cliente;
        $nota = json_decode($produccion->nota, TRUE);

        $produccion->editado_por=$nota['editado_por'];
        $produccion->fabricado_por=$nota['fabricado_por'];
        $emisor = new Emisor();

        $view = view('produccion/imprimir/plantilla_1',['produccion'=>$produccion,'usuario'=>$usuario, 'emisor'=>$emisor]);
        $html=$view->render();
        $pdf=new Html2Pdf('P','A4','es');
        $pdf->pdf->SetTitle('ORDEN DE PRODUCCION '.$produccion['correlativo']);
        $pdf->writeHTML($html);
        $pdf->output('ORDEN-DE-PRODUCCION-'.$produccion['correlativo'].'.pdf');
    }

    public function agregar_imagen(Request $request){

        try{

            $error=[];
            $adjuntos=[];
            //contamos el numero de input file - el input del idproduccion ($request->all()-1)
            for($i=0; $i < count($request->all())-1; $i++){

                if($request->hasFile('image_'.$i)){
                    if ($request->file('image_'.$i)->isValid()) {

                        $image=$request->file('image_'.$i);
                        $filename   = time() .$i. '.' . $image->getClientOriginalExtension();

                        if($image->getClientOriginalExtension() == 'pdf'){
                            Storage::disk('dropbox')->putFileAs('/', $image, $filename);
                        } else {
                            $image_save_temp_uri = public_path('images/' .$filename);
                            $thumbs = public_path('images/thumbs/' .$filename);

                            $img = Image::make($image->getRealPath());
                            $img->resize(1000, 1000, function ($constraint) {
                                $constraint->aspectRatio();
                            });
                            $img->save($image_save_temp_uri);

                            //Create thumbs
                            $img = Image::make($image->getRealPath());
                            $img->resize(200, 200, function ($constraint) {
                                $constraint->aspectRatio();
                            });
                            $img->save($thumbs);

                            Storage::disk('dropbox')->putFileAs('/', new \Illuminate\Http\File($image_save_temp_uri), $filename);
                            unlink($image_save_temp_uri);
                        }

                        $response = $this->dropbox->createSharedLinkWithSettings($filename,["requested_visibility" => "public"]);
                        $response = str_replace('?dl=0','?raw=1', $response);
                        $adjuntos[] = $response['url'];



                    } else{
                        $error[] = 'La imagen no es válida';
                    }

                } else{
                    $fichero=$request->get('image_'.$i);
                    if($fichero){
                        $adjuntos[] = $fichero;
                    } else{
                        $error[] = 'Error en el adjunto N° ' .($i+1);
                    }
                }
            }

            $produccion=Produccion::find($request->idproduccion);
            $ad=$produccion->adjuntos?json_decode($produccion->adjuntos):[];
            $produccion->adjuntos = json_encode($adjuntos);
            $produccion->save();

            //eliminar las imagenes para liberar espacio
            if(count($adjuntos) >= 0){
                $result = array_diff($ad,$adjuntos);
                foreach ($result as $file){
                    $nombre_imagen_a_eliminar=basename(parse_url($file, PHP_URL_PATH));
                    $this->dropbox->delete($nombre_imagen_a_eliminar);
                    $ext = explode('.', $nombre_imagen_a_eliminar)[1];
                    if($ext != 'pdf'){
                        unlink(public_path('/images/thumbs/'.$nombre_imagen_a_eliminar));
                    }
                }
            }

            if(count($error)>0)
                return $error;
            return 1;

        } catch (\Exception $e){
            return response(['mensaje'=>$e->getMessage()],500);
        }
    }

    public function marcar_completado($id){
        $produccion = Produccion::find($id);
        $produccion->update([
            'estado'=>'COMPLETADA'
        ]);

        return back();
    }
    public function marcar_pendiente($id){
        $produccion = Produccion::find($id);
        $produccion->update([
            'estado'=>'PENDIENTE'
        ]);

        return back();
    }

}
