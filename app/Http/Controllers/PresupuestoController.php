<?php

namespace sysfact\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Luecano\NumeroALetras\NumeroALetras;
use Spipu\Html2Pdf\Html2Pdf;
use sysfact\AppConfig;
use sysfact\Categoria;
use sysfact\Emisor;
use sysfact\Http\Controllers\Helpers\MainHelper;
use sysfact\Mail\EnviarPresupuesto;
use sysfact\Orden;
use sysfact\Presupuesto;
use Illuminate\Http\Request;
use sysfact\Producto;
use Illuminate\Support\Facades\Mail;

class PresupuestoController extends Controller
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
                $orderby=$request->get('orderby','idpresupuesto');
                $order=$request->get('order', 'desc');

                if(auth()->user()->getRoleNames()->first() == 'Preventa'){
                    $presupuestos=Presupuesto::where('eliminado',0)
                        ->where(function ($query) use ($consulta) {
                            $query->whereHas('persona', function ($query) use ($consulta){
                                $query->where('nombre','like','%'.$consulta.'%');
                            })
                                ->orWhere('correlativo','like','%'.$consulta.'%');
                        })
                        ->where('idempleado',auth()->user()->idempleado)
                        ->orderby($orderby,$order)
                        ->paginate(30);
                } else {
                    $presupuestos=Presupuesto::where('eliminado',0)
                        ->where(function ($query) use ($consulta) {
                            $query->whereHas('persona', function ($query) use ($consulta){
                                $query->where('nombre','like','%'.$consulta.'%');
                            })
                                ->orWhere('correlativo','like','%'.$consulta.'%');
                        })
                        ->orderby($orderby,$order)
                        ->paginate(30);
                }

                $presupuestos->appends($_GET)->links();

                return view('presupuesto.index',[
                    'presupuesto'=>$presupuestos,
                    'usuario'=>auth()->user()->persona,
                    'textoBuscado'=>$consulta,
                    'order'=>$order=='desc'?'asc':'desc',
                    'orderby'=>$orderby,
                    'order_icon'=>$order=='desc'?'<i class="fas fa-caret-square-up"></i>':'<i class="fas fa-caret-square-down"></i>'
                ]);

            } catch (\Exception $e){
                if($e->getCode()=='42S22'){
                    return redirect('/presupuestos');
                }
                return $e->getMessage();
            }

	    }
    }

    public function obtenerCorrelativo()
    {

        $presupuesto = DB::table('presupuesto')
            ->select('correlativo')
            ->orderby('idpresupuesto', 'desc')
            ->first();

        if ($presupuesto) {
            $presupuesto = explode('-', $presupuesto->correlativo);
            $correlativo = '001-' . str_pad($presupuesto[1] + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $correlativo = '001-000001';
        }

        return json_encode($correlativo);
    }

	public function store(Request $request)
	{
        //Antes de procesar verificamos el correlativo
        $correlativo = $this->verificar_correlativo($request->correlativo);

        DB::beginTransaction();
	    try{
            $presupuesto=new Presupuesto();
            $presupuesto->idempleado=auth()->user()->idempleado;
            $presupuesto->idcliente=$request->idcliente??-1;
            if ($request->moneda == 'S/') {
                $moneda = 'PEN';
            } else {
                $moneda = 'USD';
            }
            $presupuesto->fecha=date('Y-m-d H:i:s');
            $presupuesto->presupuesto=$request->presupuesto;
            $presupuesto->descuento=$request->descuento?$request->descuento:'0.00';
            $presupuesto->porcentaje_descuento=$request->porcentaje_descuento?$request->porcentaje_descuento:'0.00';
            $presupuesto->tipo_descuento=$request->tipo_descuento;
            $presupuesto->correlativo=$correlativo;
            $presupuesto->moneda=$moneda;
            $observaciones=preg_replace("/[\r\n|\n|\r]+/", " ", $request->observaciones);
            $presupuesto->observaciones=mb_strtoupper($observaciones);
            $presupuesto->atencion = $request->atencion;
            $presupuesto->validez = $request->validez;
            $presupuesto->condicion_pago = $request->condicion_pago;
            $presupuesto->tiempo_entrega = $request->tiempo_entrega;
            $presupuesto->garantia = $request->garantia;
            $presupuesto->impuesto = $request->impuesto;
            $presupuesto->lugar_entrega = $request->lugar_entrega;
            $presupuesto->contacto = $request->contacto;
            $presupuesto->telefonos = $request->telefonos;
            $presupuesto->igv_incluido=$request->igv_incluido;
            $presupuesto->exportacion=$request->exportacion==true?1:0;
            $presupuesto->incoterm=$request->incoterm;
            $presupuesto->referencia=$request->referencia;
            $presupuesto->flete=$request->flete;
            $presupuesto->seguro=$request->seguro;
            $presupuesto->ocultar_impuestos=$request->ocultar_impuestos;
            $presupuesto->ocultar_precios=$request->ocultar_precios;
            $presupuesto->save();
            $idpresupuesto=$presupuesto->idpresupuesto;

            $detalle=[];
            $items=json_decode($request->items, TRUE);
            $i=1;
            foreach ($items as $item){
                $detalle['num_item']=$i;
                $detalle['cantidad']=$item['cantidad']?$item['cantidad']:'0';
                $detalle['monto']=$item['precio']?$item['precio']:'0.00';
                $detalle['tipo_descuento']=$item['tipo_descuento'];
                $detalle['descuento_por_und']=$item['descuento_por_und'];
                $detalle['porcentaje_descuento']=$item['porcentaje_descuento']?$item['porcentaje_descuento']:0;
                $detalle['descuento']=$item['descuento']?$item['descuento']:0;
                $detalle['descripcion']=nl2br(mb_strtoupper($item['presentacion']));
                $detalle['idproducto']=$item['idproducto'];
                $detalle['idpresupuesto']=$idpresupuesto;
                DB::table('presupuesto_detalle')->insert($detalle);

                $i++;
            }

            DB::commit();

            return $idpresupuesto;

        } catch (\Exception $e){
            DB::rollback();
            return $e->getMessage();
        }


	}

    public function editar(Request $request, $id)
    {
        $presupuesto=Presupuesto::find($id);
        $pro = $presupuesto->productos;
        $persona = $presupuesto->cliente->persona;
        $presupuesto->cliente->nombre = $persona->nombre;

        if($presupuesto->moneda=='PEN'){
            $presupuesto->moneda='S/';
        }

        $productos=[];
        $i=0;
        foreach($pro as $product){

            $productos[$i]['cantidad']=floatval($product->detalle->cantidad);
            $productos[$i]['cod_producto']=$product->cod_producto;
            $productos[$i]['costo']=$product->costo;
            $productos[$i]['descuento']=$product->detalle->descuento;
            $productos[$i]['eliminado']=$product->eliminado;
            $productos[$i]['fecha']=null;
            $productos[$i]['idcategoria']=$product->idcategoria;
            $productos[$i]['idproducto']=$product->idproducto;
            $productos[$i]['igv']=null;
            $productos[$i]['imagen']=null;
            $productos[$i]['inventario']=null;
            $productos[$i]['nombre']=$product->nombre;
            $productos[$i]['num_item']=$product->detalle->num_item;
            $productos[$i]['precio']=$product->detalle->monto;
            $productos[$i]['presentacion']=strip_tags($product->detalle->descripcion);
            $productos[$i]['stock']=null;
            $productos[$i]['stock_bajo']=$product->stock_bajo;
            $productos[$i]['subtotal']=null;
            $productos[$i]['moneda']=$presupuesto->moneda=='S/'?'PEN':'USD';
            $productos[$i]['tipo_producto']=$product->tipo_producto;
            $productos[$i]['total']=null;
            $productos[$i]['unidad_medida']=$product->detalle->unidad_medida;
            $productos[$i]['prev_precio']=$product->detalle->monto;
            $productos[$i]['tipo_descuento']=$product->detalle->tipo_descuento;
            $productos[$i]['descuento_por_und']=$product->detalle->descuento_por_und;
            $productos[$i]['porcentaje_descuento']=floatval($product->detalle->porcentaje_descuento);
            $i++;

        }

        $ultimo_id_registrado=DB::table('productos')
            ->select('idproducto')
            ->where('eliminado','=',0)
            ->orderby('idproducto','desc')
            ->first();
        if($ultimo_id_registrado==null)$ultimo_id_registrado=['idproducto'=>1];

        return view('presupuesto.editar',['presupuesto'=>$presupuesto,'productos'=>json_encode($productos),'usuario'=>auth()->user()->persona,'ultimo_id'=>json_encode($ultimo_id_registrado)]);
    }


    public function update(Request $request)
    {
        DB::beginTransaction();
        try{

            $presupuesto=Presupuesto::find($request->idpresupuesto);
            $presupuesto->idcliente=$request->idcliente??-1;
            $presupuesto->presupuesto=$request->presupuesto;
            $presupuesto->descuento=$request->descuento?$request->descuento:'0.00';
            $presupuesto->porcentaje_descuento=$request->porcentaje_descuento?$request->porcentaje_descuento:'0.00';
            $presupuesto->tipo_descuento=$request->tipo_descuento;
            $presupuesto->descuento=$request->descuento?$request->descuento:'0.00';
            $observaciones=preg_replace("/[\r\n|\n|\r]+/", " ", $request->observaciones);
            $presupuesto->observaciones=mb_strtoupper($observaciones);
            $presupuesto->atencion = $request->atencion;
            $presupuesto->validez = $request->validez;
            $presupuesto->condicion_pago = $request->condicion_pago;
            $presupuesto->tiempo_entrega = $request->tiempo_entrega;
            $presupuesto->garantia = $request->garantia;
            $presupuesto->impuesto = $request->impuesto;
            $presupuesto->lugar_entrega = $request->lugar_entrega;
            $presupuesto->contacto = $request->contacto;
            $presupuesto->telefonos = $request->telefonos;
            $presupuesto->igv_incluido=$request->igv_incluido;
            $presupuesto->exportacion=$request->exportacion==true?1:0;
            $presupuesto->incoterm=$request->incoterm;
            $presupuesto->flete=$request->flete;
            $presupuesto->seguro=$request->seguro;
            $presupuesto->referencia=$request->referencia;
            $presupuesto->fecha=$request->fecha.' '.date('H:i:s');
            $presupuesto->ocultar_impuestos=$request->ocultar_impuestos;
            $presupuesto->ocultar_precios=$request->ocultar_precios;

            if ($request->moneda == 'S/') {
                $moneda = 'PEN';
            } else {
                $moneda = 'USD';
            }
            $presupuesto->moneda=$moneda;
            $presupuesto->save();

            $detalle=[];
            $items=json_decode($request->items, TRUE);
            $i=1;

            DB::table('presupuesto_detalle')->where('idpresupuesto', '=', $request->idpresupuesto)->delete();

            foreach ($items as $item){
                $detalle['num_item']=$i;
                $detalle['cantidad']=$item['cantidad']?$item['cantidad']:'0';
                $detalle['monto']=$item['precio']?$item['precio']:'0.00';
                $detalle['tipo_descuento']=$item['tipo_descuento'];
                $detalle['descuento_por_und']=$item['descuento_por_und'];
                $detalle['porcentaje_descuento']=$item['porcentaje_descuento']?$item['porcentaje_descuento']:0;
                $detalle['descuento']=$item['descuento']?$item['descuento']:0;
                $detalle['descripcion']=nl2br(mb_strtoupper($item['presentacion']));
                $detalle['idproducto']=$item['idproducto'];
                $detalle['idpresupuesto']=$request->idpresupuesto;
                DB::table('presupuesto_detalle')->insert($detalle);

                $i++;
            }

            DB::commit();

            return 1;

        } catch (\Exception $e){
            DB::rollback();
            return $e->getMessage();
        }

    }

    public function generarPdf($id){
        $presupuesto=Presupuesto::find($id);
        $presupuesto->productos;
        $usuario=$presupuesto->cliente;
        $emisor=new Emisor();
        $config = MainHelper::configuracion('mail_contact');

        if($presupuesto->moneda=='PEN'){
            $moneda_letras='SOLES';
            $presupuesto->moneda='S/';
        } else{
            $moneda_letras='DÓLARES';
        }

        $presupuesto->leyenda=NumeroALetras::convert($presupuesto->presupuesto, $moneda_letras,true);
        $presupuesto->descuento_global = $presupuesto->tipo_descuento?floatval($presupuesto->porcentaje_descuento).'%':$presupuesto->moneda.' '.$presupuesto->descuento;

        /*foreach ($presupuesto->productos as $item){
            $item->monto = $presupuesto->igv_incluido && !$presupuesto->exportacion?round($item->detalle['monto'] / 1.18,3):round($item->detalle['monto'],2);
            $item->monto_descuento=$item->detalle['tipo_descuento']?floatval($item->detalle['porcentaje_descuento']).'%':$item->detalle['descuento'];
            $subtotal = $item->detalle['cantidad']*$item->monto;
            $monto_descuento = $item->detalle['tipo_descuento']?$subtotal*$item->detalle['porcentaje_descuento']/100:$item->detalle['descuento'];
            $item->total = $subtotal - $monto_descuento;
        }*/
        $i = 0;
        foreach ($presupuesto->productos as $item){
            $item->monto = $item->detalle['monto'];
            $item->monto_descuento=$item->detalle['tipo_descuento']?floatval($item->detalle['porcentaje_descuento']).'%':$item->detalle['descuento'];
            $subtotal = $item->detalle['cantidad']*$item->detalle['monto'];
            if($item->imagen){
                MainHelper::procesar_imagen($item->imagen,$emisor->ruc.'-cotizacion-'.$i.'.jpg');
            }

            if($item->detalle['tipo_descuento']){
                $monto_descuento = $subtotal*$item->detalle['porcentaje_descuento']/100;
            } else {
                if($presupuesto->igv_incluido){
                    $monto_descuento = round($item->detalle['descuento'] * 1.18, 2);
                } else {
                    $monto_descuento = $item->detalle['descuento'];
                }

            }

            $item->total = $subtotal - $monto_descuento;
            $i++;
        }

        $print = MainHelper::configuracion('cotizacion');
        $plantilla = json_decode($print, true)['formato'];

        $view = view('presupuesto/imprimir/'.$plantilla,['presupuesto'=>$presupuesto,'emisor'=>$emisor,'usuario'=>$usuario,'config'=>json_decode($config, true)]);
        $html=$view->render();
        $pdf=new Html2Pdf('P','A4','es');
        $pdf->pdf->SetTitle('COTIZACION-'.str_pad($presupuesto['correlativo'],5,'0',STR_PAD_LEFT));
        $pdf->writeHTML($html);

        $files = glob(public_path('images/temporal/*'));
        foreach($files as $file){
            if(is_file($file) && strpos($file,$emisor->ruc.'-cotizacion')!==false) {
                unlink($file);
            }
        }

        return [
            'file'=>$pdf,
            'name'=>'COTIZACION-'.str_pad($presupuesto['correlativo'],5,'0',STR_PAD_LEFT).'.pdf'
        ];
    }

	public function imprimir_presupuesto($id)
	{
		$pdf=$this->generarPdf($id);
		$pdf['file']->output($pdf['name']);
	}

	public function descargar_presupuesto($id){
        $pdf=$this->generarPdf($id);
        $pdf['file']->output($pdf['name'],'D');
    }

    public function nuevo_presupuesto(){
        $ultimo_id_registrado=DB::table('productos')
            ->select('idproducto')
            ->where('eliminado','=',0)
            ->orderby('idproducto','desc')
            ->first();
        if($ultimo_id_registrado==null)$ultimo_id_registrado=['idproducto'=>1];

        $config = json_decode(cache('config')['cotizacion'], true);
        $configuracion = Collect($config);


        return view('presupuesto.nuevo',
            [
                'usuario'=>auth()->user()->persona,'ultimo_id'=>json_encode($ultimo_id_registrado),
                'config'=>$configuracion
            ]);
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

    public function destroy($id)    {
        $presupuesto=Presupuesto::findOrFail($id);
        $presupuesto->eliminado=1;
        $presupuesto->update();
    }

    public function duplicar($id){

        $presupuesto=Presupuesto::find($id);
        $duplicado = $presupuesto->replicate();
        $duplicado->fecha = date('Y-m-d H:i:s');

        $corr = explode('-', $duplicado->correlativo);
        $corr = '001-' . str_pad($corr[1] + 1, 6, '0', STR_PAD_LEFT);
        $correlativo = $this->verificar_correlativo($corr);

        $duplicado->correlativo = $correlativo;
        $duplicado->idempleado = auth()->user()->idempleado;
        $duplicado->save();
        $idpresupuesto=$duplicado->idpresupuesto;

        $i=0;

        foreach ($presupuesto->productos as $item){
            $detalle['num_item']=$i;
            $detalle['cantidad']=$item->detalle['cantidad'];
            $detalle['monto']=$item->detalle['monto'];
            $detalle['descuento']=$item->detalle['descuento'];
            $detalle['descripcion']=mb_strtoupper($item->detalle['descripcion']);
            $detalle['idproducto']=$item['idproducto'];
            $detalle['idpresupuesto']=$idpresupuesto;

            DB::table('presupuesto_detalle')->insert($detalle);

            $i++;
        }

        return $i;

    }

    public  function verificar_correlativo($correlativo){
        $existe_correlativo = DB::table('presupuesto')
            ->where('correlativo', $correlativo)
            ->exists();

        if ($existe_correlativo) {
            $presupuesto = DB::table('presupuesto')
                ->select('correlativo')
                ->orderby('idpresupuesto', 'desc')
                ->first();
            $presupuesto = explode('-', $presupuesto->correlativo);
            return '001-' . str_pad($presupuesto[1] + 1, 6, '0', STR_PAD_LEFT);
        }

        return $correlativo;

    }


    public function enviar_presupuesto_por_email(Request $request){

        $pdf=$this->generarPdf($request->idpresupuesto);
        $pdf['file']->output(storage_path() . '/app/' .$pdf['name'],'F');

        try{
            $cc = json_decode($request->destinatarios);
            Mail::to($request->mail)->cc($cc)->send(new EnviarPresupuesto($request->mensaje,$pdf['name'],$request->conCopia));
            unlink(storage_path() . '/app/' .$pdf['name']);

            $presupuesto = Presupuesto::find($request->idpresupuesto);
            $data = $presupuesto->datos_adicionales;
            if(!$data){
                $mail_data = [
                    'mail'=>[
                        [
                            'direccion'=>$request->mail,
                            'fecha'=>date('Y-m-d H:i:s')
                        ]
                    ]
                ];
                if(count($cc)>0){
                    foreach ($cc as $item){
                        $mail_data['mail'][] = [
                            'direccion'=>$item,
                            'fecha'=>date('Y-m-d H:i:s')
                        ];
                    }
                }
                $presupuesto->datos_adicionales = json_encode($mail_data);
                $presupuesto->save();
            } else {
                $mail_data = json_decode($data, true)['mail'];
                $mail_data[] = [
                    'direccion'=>$request->mail,
                    'fecha'=>date('Y-m-d H:i:s')
                ];
                if(count($cc)>0){
                    foreach ($cc as $item){
                        $mail_data[] = [
                            'direccion'=>$item,
                            'fecha'=>date('Y-m-d H:i:s')
                        ];
                    }
                }
                $presupuesto->datos_adicionales = json_encode(['mail'=>$mail_data]);
                $presupuesto->save();
            }

            return 'Se envió el correo con éxito';
        } catch (\Swift_TransportException $e){
            unlink(storage_path() . '/app/' .$pdf['name']);
            return response(['mensaje'=>$e->getMessage()],500);
        }
    }

    public function obtenerCategorias()
    {
        return ['categorias'=>Categoria::all()];

    }

    public function guardarProducto(Request $request){
        $producto = new ProductoController();
        $producto->store($request);
    }

}
