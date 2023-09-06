<?php

namespace sysfact\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spipu\Html2Pdf\Html2Pdf;
use sysfact\Categoria;
use sysfact\Descuento;
use sysfact\Emisor;
use sysfact\Http\Controllers\Helpers\DataTipoPago;
use sysfact\Http\Controllers\Helpers\MainHelper;
use sysfact\Orden;
use sysfact\Producto;
use sysfact\User;
use sysfact\Venta;
use Jenssegers\Agent\Agent;

class PedidoController extends Controller
{
    private $interfaz;

    public function __construct()
    {
        $this->middleware('auth');
        $this->interfaz = json_decode(MainHelper::configuracion('interfaz_pedidos'),true);
    }

    public function obtener_vendedor(){
        $idvendedor = -1;
        $acceso = auth()->user()->getRoleNames()->first();
        if($acceso == 'Vendedor'){
            $idvendedor = auth()->user()->persona->idpersona;
        }
        return $idvendedor;
    }

    public function index(Request $request){
        $categorias=Categoria::orderby('nombre','asc')->get();
        $idvendedor= $this->obtener_vendedor();
        $agent = new Agent();
        $data = ['categorias'=>$categorias,'idvendedor'=>$idvendedor,'usuario'=>auth()->user()->persona,'agent'=>$agent];

        switch ($this->interfaz['tipo']){
            case 'modo_4':
                $data['idbody']='pedidos-style';
                return redirect('/');
            case 'modo_3':
                $data['idbody']='pedidos-style';
                return view('pedidos.interfaz_3.index',$data);
            case 'modo_2':
                $data['idbody']='pedidos-style';
                return view('pedidos.interfaz_2.index',$data);
            default:
                $data['idbody']='pedidos-style';
                return view('pedidos.interfaz_1.index',$data);
        }
    }

    public function obtener_pedidos(){
        if(auth()->user()->getRoleNames()->first() == 'Vendedor'){
            $ordenes= Orden::where('estado','EN COLA')
                ->where('idempleado',auth()->user()->idempleado)
                ->orderby('orden.idorden', 'desc')
                ->take(30)
                ->get();
        } else {
            $ordenes= Orden::where('estado','EN COLA')
                ->orderby('orden.idorden', 'desc')
                ->take(30)
                ->get();
        }


        foreach ($ordenes as $orden){
            $orden->fecha = date('d-m-Y h:i', strtotime($orden->fecha));
            $orden->empleado = $orden->idempleado==-1?'-':strtoupper($orden->trabajador->persona->nombre);
            $orden->cliente->persona;
            $orden->direccion = json_decode($orden->datos_entrega, true)['direccion'];
            $orden->datos_entrega = json_decode($orden->datos_entrega, true);
        }

        return response()->json($ordenes);
    }

     public function productos_por_categoria(Request $request){

        if($request->idcategoria != -1){
            $productos=Producto::where('idcategoria', $request->idcategoria)
                ->where('eliminado',0)
                ->orderby('productos.nombre','asc')
                ->skip($request->skip)
                ->take(30)
                ->get();
            foreach ($productos as $producto) {
                $producto->stock = $producto->inventario()->first()->saldo;
                $producto->moneda = $producto->moneda=='PEN'?'S/':'USD';
                $producto->unidad = explode('/',$producto->unidad_medida)[1];
                $producto->badge_stock = 'badge-success';
                $producto->items_kit = json_decode($producto->items_kit, true);
                $descuento=$producto->descuento()->orderby('monto_desc','asc')->first();
                $producto->precioPorMayor = $descuento['monto_desc'];
                $producto->cantidadPorMayor = $descuento['cantidad_min'];
                $producto->etiqueta = $descuento['etiqueta'];
                if($producto->stock <= 0){
                    $producto->badge_stock = 'badge-danger';
                } else if($producto->stock <= $producto->stock_bajo){
                    $producto->badge_stock = 'badge-warning';
                }
            }
            return $productos->toJson();
        } else {
            return $this->mas_vendidos($request->skip);
        }

    }

    public function mas_vendidos($skip){

        $fechaHasta = Carbon::now();
        $fechaDesde = $fechaHasta->clone()->subDays(10);

        $productos = DB::table('productos')
            ->join('inventario', 'productos.idproducto', '=', 'inventario.idproducto')
            ->select(
                'productos.*',
                DB::raw('SUM(inventario.cantidad) as total_vendido'),
                DB::raw('(SELECT saldo FROM inventario AS i2 WHERE i2.idproducto = productos.idproducto ORDER BY i2.fecha DESC LIMIT 1) as stock')
            )
            ->where('productos.eliminado', 0)
            ->whereNotNull('inventario.idventa')
            ->whereBetween('inventario.fecha', [$fechaDesde, $fechaHasta])
            ->groupBy('productos.idproducto')
            ->orderBy('total_vendido', 'asc')
            ->skip($skip)
            ->limit(10)
            ->get();

        foreach ($productos as $producto) {
            $producto->moneda = $producto->moneda=='PEN'?'S/':'USD';
            $producto->unidad = explode('/',$producto->unidad_medida)[1];
            $producto->badge_stock = 'badge-success';
            $producto->items_kit = json_decode($producto->items_kit, true);
            $descuento = Descuento::where('idproducto',$producto->idproducto)->orderby('monto_desc','asc')->first();
            $producto->precioPorMayor = $descuento['monto_desc'];
            $producto->cantidadPorMayor = $descuento['cantidad_min'];
            $producto->etiqueta = $descuento['etiqueta'];
            if($producto->stock <= 0){
                $producto->badge_stock = 'badge-danger';
            } else if($producto->stock <= $producto->stock_bajo){
                $producto->badge_stock = 'badge-warning';
            }
        }

        return $productos;
    }

    /*public function obtenerProductos($search)
    {
        $consulta=trim($search);

        $productos=Producto::where('eliminado',0)
            ->where(function ($query) use ($consulta) {
                $query->where('nombre','LIKE','%'.$consulta.'%')
                    ->orWhere('cod_producto','like','%'.$consulta.'%')
                    ->orWhere('presentacion','like','%'.$consulta.'%');
            })
            ->orderby('idproducto','desc')
            ->take(8)
            ->get();

        foreach ($productos as $producto){
            $unidad = explode('/',$producto->unidad_medida);
            $producto->unidad_medida = $unidad[1];

            $descuento=$producto->descuento()->orderby('monto_desc','asc')->first();
            $producto->precioPorMayor = $descuento['monto_desc'];
            $producto->cantidadPorMayor = $descuento['cantidad_min'];
            $producto->etiqueta = $descuento['etiqueta'];
            $producto->items_kit = json_decode($producto->items_kit, true);
            $producto->stock = $producto->inventario()->first()->saldo;
        }

        return response()->json($productos);
    }*/

    public function store(Request $request)
    {
        try{
            DB::beginTransaction();
            $orden=new Orden();
            $orden->idempleado=$request->idvendedor;
            $orden->idcliente=$request->idcliente??-1;
            $orden->fecha=date('Y-m-d H:i:s');
            $orden->total=$request->total;
            $observaciones=preg_replace("/[\r\n|\n|\r]+/", " ", $request->observaciones);
            $orden->observaciones=mb_strtoupper($observaciones);
            $orden->moneda=$request->moneda;
            $orden->igv_incluido=$request->igv_incluido;
            $orden->comprobante=$request->comprobante;
            $orden->estado='EN COLA';

            if(isset($request->datos_entrega)){
                $datos_entrega = json_decode($request->datos_entrega, TRUE);
                $datos_entrega['direccion'] = mb_strtoupper(trim($datos_entrega['direccion']));
                $datos_entrega['referencia'] = mb_strtoupper(trim($datos_entrega['referencia']));
                $datos_entrega['contacto'] = mb_strtoupper(trim($datos_entrega['contacto']));
                $datos_entrega['telefono'] = mb_strtoupper(trim($datos_entrega['telefono']));
                $datos_entrega['costo'] = trim($datos_entrega['costo']);
                $orden->datos_entrega = json_encode($datos_entrega);
            }

            $orden->save();
            $idorden=$orden->idorden;

            $detalle=[];
            $items=json_decode($request->items, TRUE);
            $i=1;
            $suma_detalle = 0;
            foreach ($items as $item){
                $suma_detalle += $item['precio'] * $item['cantidad'];
                $detalle['num_item']=$i;
                $detalle['cantidad']=$item['cantidad'];
                $detalle['monto']=$item['precio'];
                $detalle['descuento']=0;
                $detalle['descripcion']=strtoupper($item['presentacion']);
                $detalle['idproducto']=$item['idproducto'];
                $detalle['idorden']=$idorden;
                DB::table('orden_detalle')->insert($detalle);
                $i++;
            }

            if(round($suma_detalle,2) != round($request->total,2)){
                $orden=Orden::find($idorden);
                $orden->total=round($suma_detalle,2);
                $orden->update();
            }

            DB::commit();

            switch ($orden->comprobante){
                case '01':
                    $orden->comprobante='FACTURA';
                    break;
                case '03':
                    $orden->comprobante='BOLETA';
                    break;
                default:
                    $orden->comprobante='RECIBO';
            }

            $respuesta = '<div class="container"><div class="row"><div class="col-lg-12">Pedido N°: '.$orden->idorden. '<br>Importe: '.$orden->moneda.$orden->total.'<br>Comprobante: '.$orden->comprobante.'</div><div class="col-lg-12"><a href="/pedidos/imprimir/'.$orden->idorden.'" target="_blank"><button class="btn btn-success mt-3">Imprimir pedido</button></a></div></div></div>';

            return ['idorden'=>$idorden,'total'=>$orden->total, 'respuesta'=>$respuesta];

        } catch (\Exception $e){
            DB::rollBack();
            Log::error($e);
            return $e->getMessage();
        }


    }

    public function update(Request $request){

        try {
            DB::beginTransaction();
            $orden = Orden::find($request->idorden);
            $orden->idempleado=$request->idvendedor;
            $orden->idcliente = $request->idcliente ? $request->idcliente : -1;
            $orden->total = $request->total;
            $observaciones = preg_replace("/[\r\n|\n|\r]+/", " ", $request->observaciones);
            $orden->observaciones = mb_strtoupper($observaciones);
            $orden->moneda = $request->moneda;
            $orden->igv_incluido = $request->igv_incluido;
            $orden->comprobante = $request->comprobante;
            $orden->estado = 'EN COLA';

            if(isset($request->datos_entrega)){
                $datos_entrega = json_decode($request->datos_entrega, TRUE);
                $datos_entrega['direccion'] = mb_strtoupper(trim($datos_entrega['direccion']));
                $datos_entrega['referencia'] = mb_strtoupper(trim($datos_entrega['referencia']));
                $datos_entrega['contacto'] = mb_strtoupper(trim($datos_entrega['contacto']));
                $datos_entrega['telefono'] = mb_strtoupper(trim($datos_entrega['telefono']));
                $datos_entrega['costo'] = trim($datos_entrega['costo']);
                $orden->datos_entrega = json_encode($datos_entrega);
            }

            $orden->save();

            $detalle = [];
            $items = json_decode($request->items, TRUE);
            $i = 1;

            DB::table('orden_detalle')->where('idorden', '=', $request->idorden)->delete();
            $suma_detalle = 0;
            foreach ($items as $item) {
                $suma_detalle += $item['precio'] * $item['cantidad'];
                $detalle['num_item'] = $i;
                $detalle['cantidad'] = $item['cantidad'];
                $detalle['monto'] = $item['precio'];
                $detalle['descuento'] = 0;
                $detalle['descripcion'] = mb_strtoupper($item['presentacion']);
                $detalle['idproducto'] = $item['idproducto'];
                $detalle['idorden'] = $request->idorden;
                $detalle['items_kit'] = json_encode($item['items_kit']);
                DB::table('orden_detalle')->insert($detalle);

                $i++;
            }

            if(round($suma_detalle,2) == round($request->total,2)){
                $orden=Orden::find($request->idorden);
                $orden->total=round($suma_detalle,2);
                $orden->update();
            }

            DB::commit();

            switch ($orden->comprobante){
                case '01':
                    $orden->comprobante='FACTURA';
                    break;
                case '03':
                    $orden->comprobante='BOLETA';
                    break;
                default:
                    $orden->comprobante='RECIBO';
            }

            $respuesta = '<div class="container"><div class="row"><div class="col-lg-12">Pedido N°: '.$orden->idorden. '<br>Importe: '.$orden->moneda.$orden->total.'<br>Comprobante: '.$orden->comprobante.'</div><div class="col-lg-12"><a href="/pedidos/imprimir/'.$orden->idorden.'" target="_blank"><button class="btn btn-success mt-3">Imprimir pedido</button></a></div></div></div>';

            return ['idorden' => $request->idorden, 'total' => $orden->total, 'respuesta'=>$respuesta];

        } catch (\Exception $e){
            DB::rollBack();
            Log::error($e);
           return $e->getMessage();
        }
    }

    public function imprimir_pedido(Request $request, $id)
    {
        $pedido=Orden::find($id);
        $pedido->productos;
        $pedido->persona;

        $view = view('pedidos/imprimir_pedido',['orden'=>$pedido]);
        $html=$view->render();
        $pdf=new Html2Pdf('P',[72,350],'es');
        $pdf->pdf->SetTitle('Pedido N° '.$pedido->idorden);
        $pdf->writeHTML($html);

        if($request->rawbt){
            $fromFile = $pdf->output('pedido_'.$pedido->idorden.'.pdf','S');
            return 'rawbt:data:application/pdf;base64,'.base64_encode($fromFile);
        } else {
            $pdf->output('pedido_'.$pedido->idorden.'.pdf');
        }


    }

    public function destroy($id)
    {
        $pedido=Orden::findOrFail($id);
        $pedido->delete();

    }

    public function obtenerCategorias()
    {
        return ['categorias'=>Categoria::all()];

    }

    public function guardarProducto(Request $request){
        $producto = new ProductoController();
        $producto->store($request);
    }

    public function nuevo_cliente(Request $request){
        $cliente=new ClienteController();
        return $cliente->store($request);
    }

    public function imprimir_historial(Request $request){

        $idcaja = $request->idcaja?$request->idcaja:$caja= Cache::get('caja_abierta');
        $ventas = Venta::where('eliminado', 0)
            ->where('idcaja',$idcaja)
            ->whereHas('facturacion', function($query) {
                $query->where(function ($query) {
                    $query->where('codigo_tipo_documento',01)
                        ->orWhere('codigo_tipo_documento',03)
                        ->orWhere('codigo_tipo_documento',30);
                })
                    ->where(function ($query){
                        $query->where('estado','ACEPTADO')
                            ->orWhere('estado','PENDIENTE');
                    })
                    ->orWhere('estado','-');
            })
            ->orderby('idventa','desc')
            ->get();

        $view = view('pedidos/imprimir_historial',['pedidos'=>$ventas]);
        $html=$view->render();
        $pdf=new Html2Pdf('P',[72,250],'es');
        $pdf->pdf->SetTitle('Historial de pedidos');
        $pdf->writeHTML($html);

        if($request->rawbt){
            $fromFile = $pdf->output('resumen.pdf','S');
            return 'rawbt:data:application/pdf;base64,'.base64_encode($fromFile);
        } else {
            $pdf->output('resumen.pdf');
        }

    }

    public function ventas(Request $request){

        $idcaja = $request->idcaja?$request->idcaja:$caja= Cache::get('caja_abierta');

        if ($request) {
            $consulta = trim($request->get('textoBuscado'));

            $ventas = Venta::where('eliminado', 0)
                ->whereHas('persona',function ($query) use ($consulta) {
                    $query->where('nombre','like','%'.$consulta.'%');
                })
                ->whereHas('facturacion', function($query) {
                    $query->where(function ($query) {
                        $query->where('codigo_tipo_documento',01)
                            ->orWhere('codigo_tipo_documento',03)
                            ->orWhere('codigo_tipo_documento',30);
                    })
                        ->where(function ($query){
                            $query->where('estado','ACEPTADO')
                                ->orWhere('estado','PENDIENTE');
                        })
                        ->orWhere('estado','-');
                })
                ->where('idcaja',$idcaja)
                ->orderby('idventa','desc')
                ->paginate(50);

            $total_dolares = 0;
            $total_soles = 0;

            $total_ventas = Venta::where('eliminado', 0)
                ->whereHas('facturacion', function($query) {
                    $query->where(function ($query) {
                        $query->where('codigo_tipo_documento',01)
                            ->orWhere('codigo_tipo_documento',03)
                            ->orWhere('codigo_tipo_documento',30);
                    })
                        ->where(function ($query){
                            $query->where('estado','ACEPTADO')
                                ->orWhere('estado','PENDIENTE');
                        })
                        ->orWhere('estado','-');
                })
                ->where('idcaja',$idcaja)
                ->get();

            foreach ($total_ventas as $venta) {
                if ($venta->facturacion->codigo_moneda === 'PEN') {
                    $total_soles += $venta->total_venta;
                } else {
                    $total_dolares += $venta->total_venta;
                }
            }

            $emisor = new Emisor();

            foreach ($ventas as $item){
                $item->badge_class='badge-success';
                $item->estado_orden='ATENDIDO';
                $item->nombre_fichero=$emisor->ruc.'-'.$item->facturacion->codigo_tipo_documento.'-'.$item->facturacion->serie.'-'.$item->facturacion->correlativo;
                $item->text_whatsapp = MainHelper::texto_whatsap($item,$emisor);
                if($item->facturacion->codigo_moneda == 'PEN'){
                    $item->moneda = 'S/';
                } else {
                    $item->moneda = 'USD';
                }

                switch ($item->facturacion->codigo_tipo_documento){
                    case '01':
                        $item->badge_comp='badge-warning';
                        break;
                    case '03':
                        $item->badge_comp='badge-success';
                        break;
                    case '30':
                        $item->badge_comp='';
                }
                $item->comprobante = $item->facturacion->serie.'-'.$item->facturacion->correlativo;

                $pago = DataTipoPago::getTipoPago();
                $find = array_search($item->tipo_pago, array_column($pago,'num_val'));
                $item->tipo_pago = mb_strtoupper($pago[$find]['label']);

            }
            $agent = new Agent();
            return view('pedidos.ventas', [
                'ventas' => $ventas,
                'textoBuscado'=>$consulta,
                'usuario'=>auth()->user()->persona,
                'total_soles'=>$total_soles,
                'total_dolares'=>$total_dolares,
                'agent'=>$agent,
                'disabledVentas'=>MainHelper::disabledVentas()[1],
                'idcaja'=>$idcaja
            ]);
        }
    }

    public function obtener_data_pedido(Request $request,$id){

        $orden = Orden::where('idorden',$id)->first();

        if($orden){
            $orden->productos;
            $orden->idvendedor = $orden->idempleado==1?-1:$orden->idempleado;
            if($orden->moneda=='PEN'){
                $orden->moneda='S/';
            }

            $productos=[];
            $i=0;
            foreach($orden->productos as $product){
                $stock=0;
                foreach ($product->inventario as $kardex){
                    $stock+=$kardex->cantidad;
                }

                $productos[$i]['cantidad']=$product->detalle->cantidad;
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
                $productos[$i]['warning']=false;
                $productos[$i]['loading']=false;
                $productos[$i]['precio']=$product->detalle->monto;
                $productos[$i]['presentacion']=$product->detalle->descripcion;
                $productos[$i]['stock']=$stock;
                $productos[$i]['stock_bajo']=$product->stock_bajo;
                $productos[$i]['subtotal']=null;
                $productos[$i]['tipo_producto']=$product->tipo_producto;
                $productos[$i]['discounts']=$product->discounts;
                $productos[$i]['total']=null;
                $productos[$i]['unidad_medida']=$product->unidad_medida;
                $productos[$i]['porcentaje_descuento']=null;
                $productos[$i]['items_kit']=json_decode($product->detalle->items_kit, true);
                $i++;

            }

            $data['pedido']=$orden;
            $data['productos_seleccionados']=$productos;
            return response()->json($data);

        } else {

            $data['pedido']=[
                'idorden'=> -1,
                'totalVenta'=>'0.00',
                'idvendedor' => $this->obtener_vendedor(),
            ];
            $data['productos_seleccionados']=[];

            return response()->json($data);
        }

    }

    public function borrarItemPedido(Request $request)
    {
        try {
            DB::beginTransaction();
            $orden = Orden::find($request->idorden);
            if($orden){
                $orden->update(['total' => $request->total]);

                $items = collect(json_decode($request->items, true));
                DB::table('orden_detalle')->where('idorden', $request->idorden)->delete();

                $detalle = [];
                $items->each(function ($item, $index) use ($detalle, $request) {
                    $detalle = [
                        'num_item' => $index + 1,
                        'cantidad' => $item['cantidad'],
                        'monto' => $item['precio'],
                        'descuento' => 0,
                        'descripcion' => mb_strtoupper($item['presentacion']),
                        'idproducto' => $item['idproducto'],
                        'idorden' => $request->idorden,
                        'items_kit' => json_encode($item['items_kit']),
                    ];
                    DB::table('orden_detalle')->insert($detalle);
                });

                DB::commit();

                $orden = Orden::find($request->idorden);
                $productos = $orden->productos;

                $productos->each(function ($producto) {
                    $detalle = $producto->detalle;
                    $producto->precio = $detalle->monto;
                    $producto->total = number_format($detalle->monto * $detalle->cantidad, 2);
                    $producto->cantidad = $detalle->cantidad;
                    $producto->num_item = $detalle->num_item;
                    $producto->presentacion = $detalle->descripcion;
                    $producto->items_kit = json_decode($detalle->items_kit, true);
                    $producto->warning = false;
                    $producto->loading = false;
                });

                return $productos;
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return $e->getMessage();
        }
    }


    public function actualizarDetalle(Request $request){

        try {
            DB::beginTransaction();
            $orden = Orden::find($request->idorden);
            $orden->total = $request->total;
            $orden->save();

            $item = json_decode($request->item, TRUE);
            DB::table('orden_detalle')
                ->where('num_item',$item['num_item'])
                ->where('idorden',$request->idorden)
                ->update([
                    'cantidad'=>$item['cantidad'],
                    'monto'=>$item['precio'],
                    'descripcion'=>mb_strtoupper($item['presentacion'])
                ]);

            DB::commit();
            return 1;

        } catch (\Exception $e){
            DB::rollBack();
           return $e->getMessage();
        }
    }

    public function obtenerEmpleados(){

        if (Role::where('name', 'Vendedor')->exists()) {
            $empleados = User::role('Vendedor')
                ->orWhere('cargo', 1)
                ->where('acceso', '!=', '1')
                ->where('eliminado', 0)
                ->get();
        } else {
            $empleados = User::where('cargo', 1)
                ->where('acceso', '!=', '1')
                ->where('eliminado', 0)
                ->get();
        }

        foreach ($empleados as $empleado) {
            $empleado->persona;
        }

        $idvendedor= $this->obtener_vendedor();

        return response()->json(['empleados'=>$empleados,'idvendedor'=>$idvendedor]);
    }

    public function cambiar_vendedor(Request $request){
        try{
            $pedido = Orden::find($request->idpedido);
            $pedido->idempleado = $request->idvendedor;
            $pedido->save();
            return 1;
        } catch (\Exception $e){
           return $e->getMessage();
        }

    }

    public function obtener_datos_entrega($idpedido){
        try{
            $pedido = Orden::find($idpedido);
            return $pedido->datos_entrega;
        } catch (\Exception $e){
           return $e->getMessage();
        }
    }

    public function guardar_datos_entrega(Request $request){
        try{
            $pedido = Orden::find($request->idpedido);
            $datos_entrega = json_decode($request->datos_entrega, TRUE);
            $datos_entrega['direccion'] = mb_strtoupper(trim($datos_entrega['direccion']));
            $datos_entrega['referencia'] = mb_strtoupper(trim($datos_entrega['referencia']));
            $datos_entrega['contacto'] = mb_strtoupper(trim($datos_entrega['contacto']));
            $datos_entrega['telefono'] = mb_strtoupper(trim($datos_entrega['telefono']));
            $datos_entrega['costo'] = trim($datos_entrega['costo'])==""?0:trim($datos_entrega['costo']);
            $pedido->datos_entrega = json_encode($datos_entrega);
            $success=$pedido->save();
            return $success?1:0;
        } catch (\Exception $e){
           return $e->getMessage();
        }
    }

    public function imprimir_datos_entrega(Request $request,$idpedido){
        try{
            $pedido = Orden::find($idpedido);
            $view = view('pedidos/imprimir_entrega',['orden'=>$pedido,'entrega'=>json_decode($pedido->datos_entrega, true)]);
            $html=$view->render();
            $pdf=new Html2Pdf('P',[72,250],'es');
            $pdf->pdf->SetTitle('Pedido N° '.$pedido->idorden);
            $pdf->writeHTML($html);
            if($request->rawbt){
                $fromFile = $pdf->output('pedido_'.$pedido->idorden.'.pdf','S');
                return 'rawbt:data:application/pdf;base64,'.base64_encode($fromFile);
            } else {
                $pdf->output('pedido_'.$pedido->idorden.'.pdf');
            }

        } catch (\Exception $e){
           return $e->getMessage();
        }
    }

    public function nueva_orden(){
        $ultimo_id_registrado=DB::table('productos')
            ->select('idproducto')
            ->where('eliminado','=',0)
            ->orderby('idproducto','desc')
            ->first();
        if($ultimo_id_registrado==null)$ultimo_id_registrado=['idproducto'=>1];
        $idvendedor = $this->obtener_vendedor();

        return view('pedidos/interfaz_2/nuevo',['ultimo_id'=>json_encode($ultimo_id_registrado),'idvendedor'=>$idvendedor,'usuario'=>auth()->user()->persona]);
    }

    public function imprimir_lista(){

        $ordenes = Orden::where('estado','PROCESADO')
            ->orderby('idorden','DESC')->get();

        foreach ($ordenes as $item){

            switch ($item->estado){
                case 'EN COLA':
                    $item->badge_class='badge-warning';
                    break;
                case 'PROCESADO':
                    $item->badge_class='badge-success';
                    break;
                case 'NO PROCESADO':
                    $item->badge_class='badge-danger';
                    break;
            }

        }

        $view = view('pedidos/interfaz_2/imprimir/imprimir_historial',['ordenes'=>$ordenes]);
        $html=$view->render();
        $pdf=new Html2Pdf('P','A4','es');
        $pdf->pdf->SetTitle('Historial de pedidos');
        $pdf->writeHTML($html);
        $pdf->output('Historial-de-pedidos.pdf');
    }

    public function editar_pedido(Request $request, $id)
    {
        $orden=Orden::find($id);
        $orden->productos;
        $orden->vehiculo;
        $orden->persona;

        if($orden->moneda=='PEN'){
            $orden->moneda='S/';
        }

        $productos=[];
        $i=0;
        foreach($orden->productos as $product){
            $stock=0;
            foreach ($product->inventario as $kardex){
                $stock+=$kardex->cantidad;
            }

            $productos[$i]['cantidad']=$product->detalle->cantidad;
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
            $productos[$i]['presentacion']=$product->detalle->descripcion;
            $productos[$i]['stock']=$stock;
            $productos[$i]['stock_bajo']=$product->stock_bajo;
            $productos[$i]['subtotal']=null;
            $productos[$i]['tipo_producto']=$product->tipo_producto;
            $productos[$i]['total']=null;
            $productos[$i]['unidad_medida']=$product->unidad_medida;
            $productos[$i]['porcentaje_descuento']=null;
            $productos[$i]['items_kit']=json_decode($product->detalle->items_kit, true);
            $i++;

        }

        $ultimo_id_registrado=DB::table('productos')
            ->select('idproducto')
            ->where('eliminado','=',0)
            ->orderby('idproducto','desc')
            ->first();
        if($ultimo_id_registrado==null)$ultimo_id_registrado=['idproducto'=>1];
        $idvendedor = $this->obtener_vendedor();

        return view('pedidos.interfaz_2.editar',['ultimo_id'=>json_encode($ultimo_id_registrado),'orden'=>$orden,'idvendedor'=>$idvendedor,'productos'=>json_encode($productos),'usuario'=>auth()->user()->persona]);
    }

}
