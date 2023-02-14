<?php
/**
 * Created by PhpStorm.
 * User: Luciano
 * Date: 3/01/2021
 * Time: 09:06
 */

namespace sysfact\Http\Controllers\Helpers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use sysfact\AppConfig;
use sysfact\Caja;
use sysfact\Categoria;
use sysfact\Cliente;
use sysfact\Descuento;
use sysfact\Http\Controllers\ClienteController;
use sysfact\Http\Controllers\Controller;
use sysfact\Http\Controllers\OpcionController;
use sysfact\Http\Controllers\ProductoController;
use sysfact\Inventario;
use sysfact\Producto;

class MainHelper extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public static function opciones($key = null){
        //Cache::forget('opciones');
        if(!Cache::has('opciones')){
            $optionCon = new OpcionController();
            $tipo_cambio = $optionCon->obtener_tipo_de_cambio();

            if(!$tipo_cambio){
                //Si no se obtiene tipo de cambio seteamos a 1
                $opcion['tipo_cambio_fecha'] = date('Y-m-d');
                $opcion['tipo_cambio_compra'] = 1;
                $opcion['tipo_cambio_venta'] = 1;
                $tipo_cambio = $opcion;
            }
            Cache::remember('opciones', 24*60, function() use ($tipo_cambio) {
                return $tipo_cambio;
            });

        } else{
            if(date('Y-m-d',strtotime(Cache::get('opciones')['tipo_cambio_fecha'])) != date('Y-m-d') ){
                $optionCon = new OpcionController();
                $tipo_cambio = $optionCon->obtener_tipo_de_cambio();

                if(!$tipo_cambio){
                    //Si no se obtiene tipo de cambio, usamos el del día anterior
                    $opcion['tipo_cambio_fecha'] = date('Y-m-d');
                    $opcion['tipo_cambio_compra'] = cache('opciones')['tipo_cambio_compra'];
                    $opcion['tipo_cambio_venta'] = cache('opciones')['tipo_cambio_venta'];
                    $tipo_cambio = $opcion;
                }

                Cache::forget('opciones');
                Cache::remember('opciones', 24*60, function() use ($tipo_cambio) {
                    return $tipo_cambio;
                });

            }
        }

        /*$opciones = Cache::get('opciones');
        if(!$key){
            return $opciones;
        }

        return (is_array($key)) ? array_only($opciones, $key) : $opciones[$key];*/
    }


    public static function configuracion($key=null)
    {

        if(!Cache::has('config')){
            $val = Arr::pluck(AppConfig::all()->toArray(), 'valor', 'clave');
            Cache::forever('config', $val);
        }

        $config = Cache::get('config');

        if(!$key){
            return $config;
        }

        return (is_array($key)) ? array_only($config, $key) : $config[$key];
    }

    public static function setCacheConfigOnBoot(){
        if(!Cache::has('config')){
            Log:info('Guardando configuracion on boot()');
            $val = Arr::pluck(AppConfig::all()->toArray(), 'valor', 'clave');
            Cache::forever('config', $val);
        }
    }

    public static function updateconfiguracion(){
        Cache::forget('config');
        MainHelper::configuracion();
    }

    public function obtener_datos_usuario_de_sunat(Request $request){

        try{

            if($request->tipo_doc && $request->tipo_doc == 1){
                $tipo = 'dni';
            } else{
                $tipo = 'ruc';
            }

            $curl = curl_init();

            $data = [
                'token' => 'DC3g8TVxXlyG2cWPJGiP8F8gwtAHKvkdgtnJh8LPXAzRsN4ySpBvou3YqYuh',
                $tipo => $request->num_doc
            ];

            $post_data = http_build_query($data);

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.migo.pe/api/v1/".$tipo,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $post_data,
            ));

            if(env('APP_ENV') == 'local'){
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            }

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            $json = [];

            if($err){
                $json['nombre_o_razon_social'] = '';
                $json['ruc'] = '';
                $json['direccion'] = '';
                $json['num_documento'] = '';
            } else{
                $response = json_decode($response, true);
                if($response['success']){
                    if($request->tipo_doc == 1){
                        $json['nombre_o_razon_social'] = $response['nombre'];
                        $json['ruc'] = $request->num_doc;
                        $json['direccion'] = '-';
                    } else{
                        $json['nombre_o_razon_social'] = $response['nombre_o_razon_social'];
                        $json['ruc'] = $request->num_doc;
                        $json['direccion'] = $response['direccion_simple'].' '.$response['departamento'].' '.$response['provincia'].' '.$response['distrito'];
                    }
                    $json['success'] = true;
                    $json['num_documento'] = $request->num_doc;
                } else {
                    $json['nombre_o_razon_social'] = '';
                    $json['ruc'] = $request->num_doc;
                    $json['num_documento'] = $request->num_doc;
                    $json['direccion'] = '';
                    $json['success'] = false;
                }

            }

            $json['esNuevo']=true;
            $json['tipo_doc'] = $request->tipo_doc;
            return json_encode($json);


        } catch (\Exception $e){
            return $e;
        }
    }

    public function obtener_clientes($search=""){

        $consulta=trim($search);

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

    public function obtener_proveedores($search=""){

        $consulta=trim($search);

        $proveedor = DB::table('proveedores')
            ->join('persona', 'persona.idpersona', '=', 'proveedores.idproveedor')
            ->select('proveedores.*', 'persona.nombre', 'persona.direccion')
            ->where('eliminado', 0)
            ->where(function ($query) use ($consulta) {
                $query->where('nombre', 'like', '%' . $consulta . '%')
                    ->orWhere('num_documento','like','%'.$consulta.'%');
            })
            ->orderby('idproveedor', 'desc')
            ->take(5)
            ->get();

        return json_encode($proveedor);
    }

    public function agregar_cliente($id){
        $cliente=Cliente::find($id);
        return response()->json([
            "idcliente"=>$id,
            "cod_cliente"=>$cliente->cod_cliente,
            "nombre"=>$cliente->persona->nombre,
            "persona"=>["nombre"=>$cliente->persona->nombre,],
            "num_documento"=>$cliente->num_documento
        ]);
    }

    public function obtener_productos(Request $request, $search="")
    {
        $consulta=trim($search);
        $filtro = $request->filtro;

        if($filtro && $filtro != -1){
            switch ($filtro) {
                case 'categoria':
                    $productos = Producto::where('eliminado', 0)
                        ->whereHas('categoria', function ($query) use ($consulta){
                            $query->where('nombre', 'LIKE', '%' . $consulta . '%');
                        })
                        ->orderby('nombre', 'asc')
                        ->take(10)
                        ->get();
                    break;
                default:
                    $productos = Producto::where('eliminado', 0)
                        ->where(function ($query) use ($filtro, $consulta) {
                            $query->where($filtro, 'LIKE', '%' . $consulta . '%');
                        })
                        ->orderby('nombre', 'asc')
                        ->take(10)
                        ->get();
            }
        } else {
            if(is_numeric($search)){
                $productos=Producto::where('eliminado',0)
                    ->where(function ($query) use ($consulta) {
                        $query->orWhere('cod_producto',$consulta);
                    })
                    ->orderby('nombre','asc')
                    ->take(10)
                    ->get();
            } else {
                $productos=Producto::where('eliminado',0)
                    ->where(function ($query) use ($consulta) {
                        $query->where('nombre','LIKE','%'.$consulta.'%')
                            ->orWhere('cod_producto',$consulta)
                            ->orWhere('presentacion','like','%'.$consulta.'%');
                    })
                    ->orderby('idproducto','desc')
                    ->take(10)
                    ->get();
            }
        }



        foreach ($productos as $producto) {
            $producto->stock = $producto->inventario()->first()->saldo;
            $producto->moneda = $producto->moneda=='PEN'?'S/':'USD';
            $producto->unidad = explode('/',$producto->unidad_medida)[1];
            $descuento=$producto->descuento()->orderby('monto_desc','asc')->first();
            $producto->precioPorMayor = $descuento['monto_desc'];
            $producto->cantidadPorMayor = $descuento['cantidad_min'];
            $producto->items_kit = json_decode($producto->items_kit, true);
            $producto->badge_stock = 'badge-success';
            if($producto->stock <= 0){
                $producto->badge_stock = 'badge-danger';
            } else if($producto->stock <= $producto->stock_bajo){
                $producto->badge_stock = 'badge-warning';
            }
        }

        return response()->json($productos);
    }

    public function agregar_producto($search){
        $producto = Producto::where('cod_producto',$search)->first();
        return response()->json($producto);
    }

    public function nuevo_cliente(Request $request){
        $cliente=new ClienteController();
        return $cliente->store($request);
    }

    public function guardarProducto(Request $request){
        $producto = new ProductoController();
        return $producto->store($request);
    }

    public function buscar_clientes($num_doc){
        $consulta=trim($num_doc);

        $cliente = DB::table('cliente')
            ->join('persona', 'persona.idpersona', 'cliente.idcliente')
            ->select('cliente.*', 'persona.nombre', 'persona.direccion')
            ->where('eliminado', 0)
            ->where(function ($query) use ($consulta) {
                $query->where('num_documento', $consulta);
            })
            ->first();



        if(!$cliente){
            $request = new Request();
            $request->tipo_doc = 1;
            $request->num_doc = $num_doc;

            if(strlen($num_doc) == 11){
                $request->tipo_doc = 6;
            }

            return $this->obtener_datos_usuario_de_sunat($request);

        } else {
            $cliente->nombre_o_razon_social = $cliente->nombre;
            $cliente->esNuevo = false;
        }

        return json_encode($cliente);
    }

    public static function getFormatoImpresionComprobantes($config){
        $formato = json_decode($config, true)['formato'];
        $ruta_formato = $formato;
        switch ($formato){
            case 'A5_1':
                $formato_impresion = 'A5';
                break;
            case 'A6_1':
                $formato_impresion = 'A6';
                break;
            case '80_1':
                $formato_impresion = [72,250];
                break;
            case '55_1':
                $formato_impresion = [45,250];
                break;
            default:
                $formato_impresion = 'A4';
        }
        return ['ruta'=>$ruta_formato,'medidas'=>$formato_impresion];
    }

    public function categorias()
    {
        return ['categorias'=>Categoria::all()];
    }

    public static function obtener_idcaja(){

        $caja= Cache::get('caja_abierta');

        if(!$caja){
            try{

                $cajas_del_dia = Caja::where('fecha_a','LIKE',date('Y-m-d').'%')
                    ->get();

                $turno = count($cajas_del_dia) + 1;

                $caja=new Caja();
                $caja->idempleado = -1;
                $caja->apertura=0;
                $caja->observacion_a='Caja abierta de manera automática';
                $caja->fecha_a=date('Y-m-d H:i:m');
                $caja->turno = $turno;
                $caja->estado=0;
                $caja->save();
                $idcaja = $caja->idcaja;
                Cache::forever('caja_abierta', $idcaja);
                return $idcaja;

            } catch (\Exception $e){
                Log::error($e);
                return -1;
            }
        }

        return $caja;

    }

    public function obtenerDescuentos($idproducto){
        $descuentos = Descuento::where('idproducto',$idproducto)->get();
        return response()->json($descuentos);
    }

    public static function texto_whatsap($item, $emisor){
        $total_venta = str_replace('.','-',$item->total_venta);
        $url_comp = url('/consulta/descargar-comprobante');
        $serie = $item->facturacion->serie;
        if(strlen($item->facturacion->serie) == 3){
            $serie = $serie.'-';
        }
        $nombre_comp = $emisor->ruc.$item->facturacion->codigo_tipo_documento.$serie.$item->facturacion->correlativo.date('dmY',strtotime($item->fecha)).$total_venta;
        if($item->facturacion->codigo_tipo_documento == 30){
            $text ='¡Hola! 😃  Descarga el detalle de tu compra aquí: 👇🏻 %0A%0A✅ '.$url_comp.'/pdf/'.$nombre_comp;
            $text .= '%0A%0A'.($emisor->nombre_publicitario==""?$emisor->razon_social:$emisor->nombre_publicitario);
        } else {
            $text ='¡Hola! 😃  Descarga tu comprobante aquí: 👇🏻 %0A%0A✅ PDF: '.$url_comp.'/pdf/'.$nombre_comp.'%0A%0A✅ XML: '.$url_comp.'/xml/'.$nombre_comp;
            $text .= '%0A%0A'.($emisor->nombre_publicitario==""?$emisor->razon_social:$emisor->nombre_publicitario);
        }

        return $text;
    }

    public static function extracto($text, $max_length = 100, $cut_off = '...', $keep_word = false)
    {
        if(strlen($text) <= $max_length) {
            return $text;
        }

        if(strlen($text) > $max_length) {
            if($keep_word) {
                $text = substr($text, 0, $max_length + 1);

                if($last_space = strrpos($text, ' ')) {
                    $text = substr($text, 0, $last_space);
                    $text = rtrim($text);
                    $text .=  $cut_off;
                }
            } else {
                $text = substr($text, 0, $max_length);
                $text = rtrim($text);
                $text .=  $cut_off;
            }
        }

        return $text;
    }

    public static function procesar_imagen($link_imagen, $nombre){
        $ch = curl_init($link_imagen);
        $fp = fopen(public_path('images/temporal/'.$nombre), 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if(env('APP_ENV') == 'local'){
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
    }

    public static function check_doc_up_to_year($file){

        $pathtoFile = storage_path() . '/app/sunat/'.$file[1].'/' . $file[0].'.xml';

        if(file_exists($pathtoFile)){
            $desde = date ("Y-m-d", filemtime($pathtoFile));
            $hasta = date("Y-m-d");
            $d1 = Carbon::parse($desde);
            $d2 = Carbon::parse($hasta);
            $daysDiff = $d1->diffInDays($d2);
            return $daysDiff > 365;
        } else {
            return false;
        }
    }

    public static function actualizar_inventario($idventa, $item, $inv, $operacion){

        try{
            switch($operacion){
                case 'anulacion':
                    $descripcion_operacion = 'ANULACIÓN DE VENTA N° ' . $idventa;
                    if($item['detalle']['devueltos'] > 0){
                        $cantidad = ($item['detalle']['cantidad']??$item['cantidad']) - $item['detalle']['devueltos'];
                    } else {
                        $cantidad = $item['cantidad']??$item['detalle']['cantidad'];
                    }
                    $tipo_operacion = 'ingreso';
                    break;
                case 'anulacion_nc':
                    $descripcion_operacion = 'ANULACIÓN (NC) DE VENTA N° ' . $idventa;
                    if($item['detalle']['devueltos'] > 0){
                        $cantidad = ($item['cantidad']??$item['detalle']['cantidad']) - $item['detalle']['devueltos'];
                    } else {
                        $cantidad = $item['cantidad']??$item['detalle']['cantidad'];
                    }
                    $tipo_operacion = 'ingreso';
                    break;
                case 'devolucion':
                    $descripcion_operacion = 'DEVOLUCIÓN DE PRODUCTO - VENTA ' . $idventa;
                    $cantidad = $item['cantidad_devolucion'];
                    $tipo_operacion = 'ingreso';
                    break;
                case 'rechazo_doc':
                    $descripcion_operacion = 'DOCUMENTO RECHAZADO - VENTA N ° ' . $idventa;
                    $cantidad = $item['cantidad']??$item['detalle']['cantidad'];
                    $tipo_operacion = 'ingreso';
                    break;
                case 'rechazo_nc':
                    $descripcion_operacion = 'NOTA DE CREDITO RECHAZADA - VENTA N° ' . $idventa;
                    $cantidad = $item['cantidad']??$item['detalle']['cantidad'];
                    $tipo_operacion = 'salida';
                    break;
                default:
                    $descripcion_operacion = 'VENTA N° ' . $idventa;
                    $cantidad = $item['cantidad']??$item['detalle']['cantidad'];
                    $tipo_operacion = 'salida';
            }

            if($item['tipo_producto'] == 3){
                $kits = json_decode($item['detalle']['items_kit']);
                foreach ($kits as $kit){
                    $inv_kit = Inventario::where('idproducto',$kit->idproducto)->orderby('idinventario', 'desc')->first();
                    $inventario = new Inventario();
                    $inventario->idproducto = $kit->idproducto;
                    $inventario->idempleado = auth()->user()->idempleado??-1;
                    $inventario->idventa = $idventa;
                    if($tipo_operacion == 'ingreso'){
                        $inventario->cantidad = $kit->cantidad * $cantidad;
                        $inventario->saldo = $inv_kit->saldo + ($kit->cantidad * $cantidad);
                    } else {
                        $inventario->cantidad = $kit->cantidad * $cantidad * -1;
                        $inventario->saldo = $inv_kit->saldo - ($kit->cantidad * $cantidad);
                    }
                    $inventario->operacion = $descripcion_operacion.' (KIT)';
                    $inventario->save();
                }
            } else {
                $inventario = new Inventario();
                $inventario->idproducto = $item['idproducto'];
                $inventario->idempleado = auth()->user()->idempleado??-1;
                $inventario->idventa = $idventa;
                $inventario->operacion = $descripcion_operacion;
                $inventario->costo = $item['costo'];
                $inventario->moneda = $item['moneda_compra'];
                $inventario->tipo_cambio = $item['tipo_cambio'];
                if($tipo_operacion == 'ingreso'){
                    $inventario->cantidad = $cantidad;
                    $inventario->saldo = $inv->saldo + $cantidad;
                } else {
                    $inventario->cantidad = $cantidad * -1;
                    $inventario->saldo = $inv->saldo - $cantidad;
                }
                $inventario->save();
            }

        } catch (\Exception $e){
            Log::error($e);
            return $e->getMessage();
        }


    }

}