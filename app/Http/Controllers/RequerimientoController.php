<?php

namespace sysfact\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $productos = $requerimiento->productos;
        $requerimiento->proveedor;
        $requerimiento->proveedor->persona;
        if($requerimiento->moneda=='PEN'){
            $requerimiento->moneda='S/';
        }

        $data_productos=[];
        $i=0;
        foreach($productos as $item){

            $data_productos[$i]['cantidad']=floatval($item->detalle->cantidad);
            $data_productos[$i]['cod_producto']=$item->cod_producto;
            $data_productos[$i]['descuento']=$item->detalle->descuento;
            $data_productos[$i]['fecha']=null;
            $data_productos[$i]['idcategoria']=$item->idcategoria;
            $data_productos[$i]['idproducto']=$item->idproducto;
            $data_productos[$i]['igv']=null;
            $data_productos[$i]['imagen']=null;
            $data_productos[$i]['inventario']=null;
            $data_productos[$i]['nombre']=$item->nombre;
            $data_productos[$i]['num_item']=$item->detalle->num_item;
            $data_productos[$i]['costo']=$item->detalle->monto;
            $data_productos[$i]['presentacion']=strip_tags($item->detalle->descripcion);
            $data_productos[$i]['subtotal']=null;
            $data_productos[$i]['tipo_producto']=$item->tipo_producto;
            $data_productos[$i]['total']=null;
            $data_productos[$i]['unidad_medida']=$item->detalle->unidad_medida;
            $data_productos[$i]['monto_recepcion']=$item->detalle->monto;
            $data_productos[$i]['cantidad_recepcion']=floatval($item->detalle->cantidad);
            $i++;

        }

        return view('requerimientos.editar',['requerimiento'=>$requerimiento,'productos'=>json_encode($data_productos),'usuario'=>auth()->user()->persona]);
    }

    public function store(Request $request)
    {

        $req= DB::table('requerimiento')
            ->select('correlativo')
            ->orderby('correlativo', 'desc')
            ->first();

        if($req){
            $req = explode('-', $req->correlativo);
            $correlativo = '001-' . str_pad($req[1] + 1, 6, '0', STR_PAD_LEFT);
        } else{
            $correlativo = '001-000001';
        }

        $requerimiento=new Requerimiento();
        $requerimiento->idempleado=auth()->user()->idempleado;
        $requerimiento->correlativo = $correlativo;
        $requerimiento->idproveedor=$request->idproveedor??-1;
        if ($request->moneda == 'S/') {
            $moneda = 'PEN';
        } else {
            $moneda = 'USD';
        }
        $requerimiento->moneda = $moneda;
        $requerimiento->tipo_cambio = $request->tipo_cambio;
        $requerimiento->fecha_requerimiento=date('Y-m-d H:i:s');
        $requerimiento->estado='PENDIENTE';
        $requerimiento->atencion=$request->atencion;
        $requerimiento->total_compra=$request->total_compra;
        $requerimiento->observacion=$request->observaciones;
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
        if ($request->moneda == 'S/') {
            $moneda = 'PEN';
        } else {
            $moneda = 'USD';
        }
        $requerimiento->moneda = $moneda;
        $requerimiento->tipo_cambio = $request->tipo_cambio;
        $requerimiento->observacion=$request->observaciones;
        $requerimiento->save();

        $detalle=[];
        $items=json_decode($request->items, TRUE);
        $i=1;

        DB::table('requerimiento_detalle')->where('idrequerimiento', '=', $request->idrequerimiento)->delete();

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

        try{
            DB::beginTransaction();
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
                $detalle['cantidad']=$item['cantidad'];
                $detalle['monto']=$item['costo'];
                $detalle['cantidad_recepcion']=$item['cantidad_recepcion'];
                $detalle['monto_recepcion']=$item['monto_recepcion'];
                $detalle['total_recepcion']=$item['monto_recepcion']*$item['cantidad_recepcion'];
                $detalle['descuento']=$item['descuento'];
                $detalle['descripcion']=strtoupper($item['presentacion']);
                $detalle['idproducto']=$item['idproducto'];
                $detalle['idrequerimiento']=$request->idrequerimiento;

                DB::table('requerimiento_detalle')->insert($detalle);

                //Actualizar inventario
                $saldo = Inventario::where('idproducto',$item['idproducto'])->orderby('idinventario','desc')->first()->saldo;
                Log::info($item['cantidad_recepcion'].' - '.$saldo);
                $inventario=new Inventario();
                $inventario->idproducto=$item['idproducto'];
                $inventario->idempleado=auth()->user()->idempleado;
                $inventario->cantidad=$item['cantidad_recepcion'];
                if ($request->moneda == 'S/') {
                    $moneda = 'PEN';
                } else {
                    $moneda = 'USD';
                }
                $inventario->costo = $item['monto_recepcion'];
                $inventario->saldo = $saldo + $inventario->cantidad;
                $inventario->moneda = $moneda;
                $inventario->tipo_cambio = $request->tipo_cambio;
                $inventario->fecha=date('Y-m-d H:i:m');
                $inventario->operacion='INGRESO CON ORDEN DE COMPRA N° '.$request->correlativo;
                if($request->tipo_producto==2){
                    $inventario->cantidad=0;
                    $inventario->saldo = 0;
                }
                $inventario->save();

                $producto = Producto::find($item['idproducto']);
                $producto->costo = $item['monto_recepcion'];
                $producto->moneda_compra = $moneda;
                $producto->tipo_cambio = $request->tipo_cambio;
                $producto->save();
                $i++;
            }
            DB::commit();
        } catch (\Exception $e){
            DB::rollBack();
            Log::error($e);
            return $e->getMessage();
        }
    }

    public function generarPdf($id){
        $requerimiento=Requerimiento::find($id);
        $requerimiento->productos;
        $usuario=$requerimiento->proveedor;
        $config = MainHelper::configuracion('mail_contact');
        $emisor=new Emisor();

        if($requerimiento->moneda=='PEN'){
            $requerimiento->moneda='S/';
        }

        foreach ($requerimiento->productos as $item){
            $item->monto = $item->detalle['monto_recepcion'];
            $item->total = $item->detalle['cantidad']*$item->monto;
        }

        $view = view('requerimientos/imprimir/plantilla_1',['requerimiento'=>$requerimiento,'emisor'=>$emisor,'usuario'=>$usuario, 'config'=>json_decode($config, true)]);
        $html=$view->render();
        $pdf=new Html2Pdf('P','A4','es');
        $pdf->pdf->SetTitle('OC-'.$requerimiento->correlativo);
        $pdf->writeHTML($html);
        return [
            'file'=>$pdf,
            'name'=>'OC-'.$requerimiento->correlativo.'.pdf'
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
