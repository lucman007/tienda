<?php

namespace sysfact\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spipu\Html2Pdf\Html2Pdf;
use sysfact\Emisor;
use sysfact\Http\Controllers\Helpers\MainHelper;
use sysfact\Inventario;
use sysfact\Producto;
use sysfact\Requerimiento;

class RequerimientoController extends Controller
{

	public function __construct()
	{
		$this->middleware('auth');
	}


    public function index(Request $request)
    {
	    $consulta=trim($request->get('textoBuscado'));

	    $requerimiento=DB::table('requerimiento as r')
            ->select('r.*','e.nombre as empleado','p.nombre as proveedor')
            ->join('persona as p','p.idpersona','r.idproveedor')
            ->join('persona as e','e.idpersona','r.idempleado')
            ->where('r.eliminado','=',0)
            ->where(function ($query) use ($consulta) {
                $query->where('p.nombre','like','%'.$consulta.'%')
                    ->orWhere('idrequerimiento','like','%'.$consulta.'%');
            })
            ->orderby('r.idrequerimiento','desc')
            ->paginate(30);

        return view('requerimientos.index',['requerimientos'=>$requerimiento,'textoBuscado'=>$consulta,'usuario'=>auth()->user()->persona]);
    }

    public function nuevo_requerimiento(){
	    return view('requerimientos.nuevo',['usuario'=>auth()->user()->persona]);
    }

    public function obtenerProveedores(Request $request)
    {
        if ( ! $request->ajax() ) {
            return redirect( '/' );
        }

        $consulta=trim($request->get('textoBuscado'));

        $proveedor=DB::table('proveedores')
            ->join('persona', 'persona.idpersona', '=', 'proveedores.idproveedor')
            ->select('proveedores.*','persona.nombre','persona.direccion')
            ->where('eliminado','=',0)
            ->where('nombre','like','%'.$consulta.'%')
            ->orderby('idproveedor','desc')
            ->take(5)
            ->get();

        return json_encode($proveedor);
    }

    public function editar(Request $request, $id)
    {
        $requerimiento=Requerimiento::find($id);
        $requerimiento->productos;
        $requerimiento->proveedor;
        $requerimiento->proveedor->persona;

        return view('requerimientos.editar',['requerimiento'=>$requerimiento,'usuario'=>auth()->user()->persona]);
    }

    public function store(Request $request)
    {
        $requerimiento=new Requerimiento();
        $requerimiento->idempleado=auth()->user()->idempleado;

        if($request->idproveedor==''){
            $requerimiento->idproveedor=-1;
        } else{
            $requerimiento->idproveedor=$request->idproveedor;
        }
        $requerimiento->fecha_requerimiento=date('Y-m-d H:i:s');
        $requerimiento->estado='PENDIENTE';
        $requerimiento->total_compra=$request->total_compra;
        $requerimiento->save();
        $idrequerimiento=$requerimiento->idrequerimiento;

        $detalle=[];
        $items=json_decode($request->items, TRUE);
        $i=1;
        foreach ($items as $item){
            $detalle['num_item']=$i;
            $detalle['cantidad']=$item['cantidad'];
            $detalle['monto']=$item['costo'];
            $detalle['cantidad_recepcion']=$item['cantidad'];
            $detalle['monto_recepcion']=$item['costo'];
            $detalle['total_recepcion']=$item['costo']*$item['cantidad'];
            $detalle['descuento']=$item['descuento'];
            $detalle['descripcion']=strtoupper($item['presentacion']);
            $detalle['idproducto']=$item['idproducto'];
            $detalle['idrequerimiento']=$idrequerimiento;

            DB::table('requerimiento_detalle')->insert($detalle);
            $i++;
        }

        return $idrequerimiento;
    }


    public function update(Request $request)
    {
        $requerimiento=Requerimiento::find($request->idrequerimiento);
        $requerimiento->idproveedor=$request->idproveedor;
        $requerimiento->total_compra=$request->total_compra;
        $requerimiento->save();

        $detalle=[];
        $items=json_decode($request->items, TRUE);
        $i=1;

        DB::table('requerimiento_detalle')->where('idrequerimiento', '=', $request->idrequerimiento)->delete();

        foreach ($items as $item){
            $detalle['num_item']=$i;
            $detalle['cantidad']=$item['detalle']['cantidad'];
            $detalle['monto']=$item['detalle']['monto'];
            $detalle['cantidad_recepcion']=$item['detalle']['cantidad'];
            $detalle['monto_recepcion']=$item['detalle']['monto'];
            $detalle['total_recepcion']=$item['detalle']['monto']*$item['detalle']['cantidad'];
            $detalle['descuento']=$item['detalle']['descuento'];
            $detalle['descripcion']=strtoupper($item['detalle']['descripcion']);
            $detalle['idproducto']=$item['idproducto'];
            $detalle['idrequerimiento']=$request->idrequerimiento;

            DB::table('requerimiento_detalle')->insert($detalle);
            $i++;
        }
    }

    public function destroy($id)
    {
        $requerimiento=Requerimiento::findOrFail($id);
        $requerimiento->eliminado=1;
        $requerimiento->update();

    }

    public function obtenerProductos(Request $request)
    {
        $consulta=trim($request->get('textoBuscado'));

        $productos=Producto::with('inventario:idproducto,cantidad')
            ->where('nombre','LIKE','%'.$consulta.'%')
            ->where('eliminado',0)
            ->where('tipo_producto',1)
            ->orderby('idproducto','desc')
            ->take(5)->get();

        foreach ($productos as $producto){
            foreach ($producto->inventario as $kardex){
                $producto->stock+=$kardex->cantidad;
            }
        }

        return json_encode($productos);
    }

    public function recibir(Request $request){

        $requerimiento=Requerimiento::find($request->idrequerimiento);
        $requerimiento->idproveedor=$request->idproveedor;
        $requerimiento->estado='RECIBIDO';
        $requerimiento->total_compra=$request->total_compra;
        $requerimiento->num_comprobante=strtoupper($request->num_comprobante);
        $requerimiento->fecha_recepcion=date("Y-m-d H:i:s");
        $requerimiento->save();

        $detalle=[];
        $items=json_decode($request->items, TRUE);
        $i=1;

        DB::table('requerimiento_detalle')->where('idrequerimiento', '=', $request->idrequerimiento)->delete();

        foreach ($items as $item){
            $detalle['num_item']=$i;
            $detalle['cantidad']=$item['detalle']['cantidad'];
            $detalle['monto']=$item['detalle']['monto'];
            $detalle['cantidad_recepcion']=$item['detalle']['cantidad_recepcion'];
            $detalle['monto_recepcion']=$item['detalle']['monto_recepcion'];
            $detalle['total_recepcion']=$item['detalle']['monto_recepcion']*$item['detalle']['cantidad_recepcion'];
            $detalle['descuento']=$item['detalle']['descuento'];
            $detalle['descripcion']=strtoupper($item['detalle']['descripcion']);
            $detalle['idproducto']=$item['idproducto'];
            $detalle['idrequerimiento']=$request->idrequerimiento;

            DB::table('requerimiento_detalle')->insert($detalle);

            //Actualizar inventario
            $inventario=new Inventario();
            $inventario->idproducto=$item['idproducto'];
            $inventario->idempleado=auth()->user()->idempleado;
            $inventario->cantidad=$item['detalle']['cantidad_recepcion'];
            $inventario->operacion='REQUERIMIENTO N° '.$request->idrequerimiento;
            $inventario->save();

            $i++;
        }
    }

    public function generarPdf($id){
        $requerimiento=Requerimiento::find($id);
        $requerimiento->productos;
        $usuario=$requerimiento->cliente;
        $emisor=new Emisor();

        if($requerimiento->moneda=='PEN'){
            $requerimiento->moneda='S/';
        }


        $view = view('requerimientos/imprimir/plantilla_1',['requerimiento'=>$requerimiento,'emisor'=>$emisor,'usuario'=>$usuario]);
        $html=$view->render();
        $pdf=new Html2Pdf('P','A4','es');
        $pdf->pdf->SetTitle('OC-'.str_pad($requerimiento['idrequerimiento'],5,'0',STR_PAD_LEFT));
        $pdf->writeHTML($html);
        return [
            'file'=>$pdf,
            'name'=>'OC-'.str_pad($requerimiento['idrequerimiento'],5,'0',STR_PAD_LEFT).'.pdf'
        ];
    }

    public function imprimir($id)
    {
        $pdf=$this->generarPdf($id);
        $pdf['file']->output($pdf['name']);
    }

    public function descargar($id){
        $pdf=$this->generarPdf($id);
        $pdf['file']->output($pdf['name'],'D');
    }
}
