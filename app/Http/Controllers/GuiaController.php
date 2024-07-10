<?php

namespace sysfact\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use sysfact\Categoria;
use sysfact\Emisor;
use sysfact\Facturacion;
use sysfact\Guia;
use sysfact\Http\Controllers\Cpe\CpeController;
use sysfact\Http\Controllers\Helpers\DataGuia;
use sysfact\Http\Controllers\Helpers\PdfHelper;
use sysfact\Presupuesto;
use sysfact\Producto;
use sysfact\Venta;
use Illuminate\Support\Facades\Mail;
use sysfact\Mail\EnviarDocumentos;

class GuiaController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request, $desde=null,$hasta=null)
    {
        $filtro = $request->filtro;
        $buscar = $request->buscar;

        if(!$filtro){
            $filtro='fecha';
            $desde=date('Y-m-d');
            $hasta=date('Y-m-d');
        }
        return $this->guiasEmitidas($desde, $hasta, $filtro, $buscar);
    }

    public function guiasEmitidas($desde, $hasta, $filtro, $buscar)
    {

        try {

            $guias = null;
            $filtros = ['desde' => $desde, 'hasta' => $hasta, 'filtro'=>$filtro,'buscar'=>$buscar];


            switch ($filtro) {
                case 'estado':
                    $guias = Guia::whereBetween('fecha_emision', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
                        ->orderby('idguia', 'desc')
                        ->where('estado', strtoupper($buscar))
                        ->paginate(30);
                    break;
                case 'cliente':
                    $guias = Guia::whereBetween('fecha_emision', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
                        ->orderby('idguia', 'desc')
                        ->whereHas('persona', function ($query) use ($filtro, $buscar) {
                            $query->where('nombre', 'LIKE', '%' . $buscar . '%');
                        })
                        ->paginate(30);
                    break;
                default:
                    $guias = Guia::whereBetween('fecha_emision', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
                        ->orderby('idguia', 'desc')
                        ->paginate(30);

            }

            foreach ($guias as $item) {
                $item->cliente->persona;
                $emisor = new Emisor();

                $datos_adicionales = json_decode($item->guia_datos_adicionales, TRUE);
                $item->num_doc_relacionado=$datos_adicionales['num_doc_relacionado'];
                $item->num_oc=$datos_adicionales['oc']??'';

                if($item->num_doc_relacionado == $item->num_oc){
                    $item->num_oc = null;
                }

                $item->nombre_fichero = $emisor->ruc . '-09-' . $item->correlativo;
                $ticket_json = json_decode($item->ticket, true)??[];
                $item->ticket = $ticket_json[count($ticket_json) - 1]['numTicket']??0;

                switch ($item->estado) {
                    case 'PENDIENTE':
                        $item->badge_class = 'badge-warning';
                        break;
                    case 'ACEPTADO':
                        $item->badge_class = 'badge-success';
                        break;
                    case 'ANULADO':
                        $item->badge_class = 'badge-dark';
                        break;
                    case 'RECHAZADO':
                        $item->badge_class = 'badge-danger';
                }

            }

            $guias->appends($_GET)->links();

            return view('guia.index', ['usuario' => auth()->user()->persona, 'guias' => $guias, 'filtros' => $filtros]);

        } catch (\Exception $e) {
            Log::error($e);
            return $e->getMessage();
        }

    }

    public function nuevo()
    {
        $emisor=new Emisor();
        return view('guia.nuevo', ['ruc_emisor'=>json_encode($emisor->ruc), 'usuario' => auth()->user()->persona]);
    }

    public function obtenerCorrelativo()
    {
        $guia = DB::table('guia')
            ->select('correlativo')
            ->orderby('correlativo', 'desc')
            ->first();

        if ($guia) {
            $guia = explode('-', $guia->correlativo);
            $correlativo = 'T001-' . str_pad($guia[1] + 1, 8, '0', STR_PAD_LEFT);
        } else {
            //si es la primera guia a emitir en modo migracion de tabla
            $guia_tabla_facturacion = DB::table('facturacion')
                ->select('guia_relacionada')
                ->where('guia_relacionada', 'LIKE','T%')
                ->orderby('idventa', 'desc')
                ->first();
            if($guia_tabla_facturacion){
                $num=explode('-',$guia_tabla_facturacion->guia_relacionada)[1];
                $correlativo = 'T001-'.str_pad($num + 1, 8, '0', STR_PAD_LEFT);
            } else{
                $correlativo = 'T001-00000001';
            }
        }

        return json_encode($correlativo);
    }

    public function store(Request $request, Venta $venta=null)
    {
        $correlativo = $this->obtenerCorrelativo();

        DB::beginTransaction();
        try {
            $guia = new Guia();
            $guia->idempleado = auth()->user()->idempleado;
            $guia->idcliente = $request->idcliente;
            $guia->fecha_emision = $request->fecha . ' ' . date('H:i:s');
            $guia->correlativo = json_decode($correlativo);
            $guia->observacion = $request->observacion??'';

            $datos_partida = json_decode($request->direccion_partida, true);

            $guia->direccion_partida = $datos_partida['direccion'];
            $guia->direccion_partida_ubigeo = $datos_partida['ubigeo'];

            if ($request->fecha > date('Y-m-d')) {
                $guia->fecha_emision = date('Y-m-d H:i:s');
            }

            //Limpiar espacios en guia
            $datos_guia = json_decode($request->guia_datos_adicionales, TRUE);
            $datos_guia['direccion'] = mb_strtoupper(trim($datos_guia['direccion']));
            $datos_guia['ubigeo'] = trim($datos_guia['ubigeo']);
            $datos_guia['peso'] = trim($datos_guia['peso']);
            $datos_guia['bultos'] = trim($datos_guia['bultos']);
            $datos_guia['num_doc_transportista'] = trim($datos_guia['num_doc_transportista']);
            $datos_guia['razon_social_transportista'] = strtoupper(trim($datos_guia['razon_social_transportista']));
            $datos_guia['placa_vehiculo'] = strtoupper(trim($datos_guia['placa_vehiculo']));
            $datos_guia['num_doc_relacionado'] = strtoupper(trim($datos_guia['num_doc_relacionado']));
            $datos_guia['dni_conductor'] = trim($datos_guia['dni_conductor']);
            $datos_guia['licencia_conductor'] = trim($datos_guia['licencia_conductor']);
            $datos_guia['fecha_traslado'] = $datos_guia['fecha_traslado'] . ' ' . date('H:i:s');
            $datos_guia['oc'] = $datos_guia['oc']??$request->num_oc;

            $motivo_traslado = DataGuia::getMotivoTraslado();
            foreach ($motivo_traslado as $motivo){
                if($datos_guia['codigo_traslado'] == $motivo['num_val']){
                    $datos_guia['motivo_traslado'] = strtoupper($motivo['label']);
                    break;
                }
            }

            $guia->guia_datos_adicionales = json_encode($datos_guia);
            $guia->estado = 'PENDIENTE';

            if(!$venta){
                $guardado = $guia->save();
            } else{
                $guardado = $venta->guia()->save($guia);
            }

            $idguia = $guia->idguia;

            //procesar detalle de guia
            if($guardado){
                $detalle = [];
                $items = json_decode($request->items, TRUE);
                $i = 1;
                foreach ($items as $item) {
                    $detalle['num_item'] = $i;
                    $detalle['cantidad'] = trim($item['cantidad']);
                    $detalle['precio'] = trim($item['precio']);
                    $detalle['descripcion'] = nl2br(trim($item['presentacion']));
                    $guia->productos()->attach($item['idproducto'], $detalle);
                    $i++;
                }
            }

            DB::commit();

            //Si es guia independiente lo enviamos a sunat
            if(!$venta){
                $cpe = new CpeController();
                $respuesta = $cpe->sendGuia($idguia);
                return json_encode(['idguia' => $idguia, 'respuesta' => $respuesta]);
            } else {
                //Si es guia con factura solo devolvemos el id
                return $idguia;
            }

        } catch (\Exception $e) {

            DB::rollback();
            Log::error($e);
            //En caso de error borramos guia relacionada de tabla venta
            if($venta){
                $ultima_venta=Facturacion::find($venta->idventa);
                $ultima_venta->guia_relacionada='';
                $ultima_venta->save();
            }
            return json_encode(['idguia' => -1, 'respuesta' => $e->getMessage()]);
        }

    }

    public function update(Request $request)
    {
        $respuesta = 'Se ha actualizado la guía correctamente';

        DB::beginTransaction();
        try {

            $idguia=$request->idguia;
            $guia = Guia::find($idguia);
            $guia->idcliente = $request->idcliente;
            $guia->fecha_emision = $request->fecha . ' ' . date('H:i:s');
            $guia->observacion = $request->observacion??'';
            if ($request->fecha > date('Y-m-d')) {
                $guia->fecha_emision = date('Y-m-d H:i:s');
            }

            //Limpiar espacios en guia
            $datos_guia = json_decode($request->guia_datos_adicionales, TRUE);
            $datos_guia['direccion'] = mb_strtoupper(trim($datos_guia['direccion']));
            $datos_guia['ubigeo'] = trim($datos_guia['ubigeo']);
            $datos_guia['peso'] = trim($datos_guia['peso']);
            $datos_guia['bultos'] = trim($datos_guia['bultos']);
            $datos_guia['num_doc_transportista'] = trim($datos_guia['num_doc_transportista']);
            $datos_guia['razon_social_transportista'] = strtoupper(trim($datos_guia['razon_social_transportista']));
            $datos_guia['placa_vehiculo'] = strtoupper(trim($datos_guia['placa_vehiculo']));
            $datos_guia['num_doc_relacionado'] = strtoupper(trim($datos_guia['num_doc_relacionado']));
            $datos_guia['dni_conductor'] = trim($datos_guia['dni_conductor']);
            $datos_guia['licencia_conductor'] = trim($datos_guia['licencia_conductor']);
            $datos_guia['fecha_traslado'] = $datos_guia['fecha_traslado'] . ' ' . date('H:i:s');

            $motivo_traslado = DataGuia::getMotivoTraslado();
            foreach ($motivo_traslado as $motivo){
                if($datos_guia['codigo_traslado'] == $motivo['num_val']){
                    $datos_guia['motivo_traslado'] = strtoupper($motivo['label']);
                    break;
                }
            }

            $guia->guia_datos_adicionales = json_encode($datos_guia);
            $guia->estado = 'PENDIENTE';
            $guia->save();

            if ($guia) {
                $cpe = new CpeController();
                $respuesta = $cpe->sendGuia($idguia);
            }

            DB::commit();

            return json_encode(['idguia' => $idguia, 'respuesta' => $respuesta]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e);
            return $e->getMessage();
        }

    }

    public function obtenerProductos(Request $request)
    {
        $consulta = trim($request->get('textoBuscado'));

        $productos = Producto::with('inventario:idproducto,cantidad')
            ->where('eliminado', '=', 0)
            ->where(function ($query) use ($consulta) {
                $query->where('nombre', 'like', '%' . $consulta . '%')
                    ->orWhere('cod_producto', 'like', '%' . $consulta . '%');
            })
            ->orderby('idproducto', 'desc')
            ->take(5)->get();

        foreach ($productos as $producto) {
            foreach ($producto->inventario as $kardex) {
                $producto->stock += $kardex->cantidad;
            }
        }

        return json_encode($productos);
    }

    public function obtenerClientes(Request $request)
    {
        if (!$request->ajax()) {
            return redirect('/');
        }

        $consulta = trim($request->get('textoBuscado'));

        $cliente = DB::table('cliente')
            ->join('persona', 'persona.idpersona', '=', 'cliente.idcliente')
            ->select('cliente.*', 'persona.nombre', 'persona.direccion')
            ->where('eliminado', '=', 0)
            ->where(function ($query) use ($consulta) {
                $query->where('nombre', 'like', '%' . $consulta . '%')
                    ->orWhere('num_documento', 'like', '%' . $consulta . '%');
            })
            ->orderby('idcliente', 'desc')
            ->take(5)
            ->get();

        return json_encode($cliente);
    }

    public function copiarDocumento(Request $request)
    {

        try {

            if($request->idventa){
                //si es venta a copiar
                $doc = Venta::find($request->idventa);
                $productos = $doc->productos;
                $doc->cliente->persona;
                $doc->facturacion;

                foreach ($productos as $producto) {

                    $producto->tipoAfectacion = $producto->detalle->afectacion;
                    $producto->porcentaje_descuento = $producto->detalle->porcentaje_descuento;
                    $producto->descuento = $producto->detalle->descuento;
                    $producto->precio = $producto->detalle->monto;
                    $producto->cantidad = $producto->detalle->cantidad;
                    $producto->presentacion = strip_tags($producto->detalle->descripcion);
                    $producto->subtotal = $producto->detalle->subtotal;
                    $producto->igv = $producto->detalle->igv;
                    $producto->total = $producto->detalle->total;
                }

                $doc->tipo_comprobante = $doc->facturacion->codigo_tipo_documento;
                if($doc->tipo_comprobante != 30){
                    $doc->comprobante_relacionado = $doc->facturacion->serie . '-' . $doc->facturacion->correlativo;
                }

                $doc->cliente->nombre = $doc->cliente->persona->nombre;
                $doc->cliente->direccion = $doc->cliente->persona->direccion;
            } else if($request->idguia){
                //si es guia a copiar
                $doc = Guia::find($request->idguia);
                $productos = $doc->productos;
                $doc->cliente->persona;
                $doc->guia_datos_adicionales = json_decode($doc->guia_datos_adicionales,TRUE);

                foreach ($productos as $producto) {
                    $producto->precio = $producto->detalle->precio;
                    $producto->cantidad = $producto->detalle->cantidad;
                    $producto->presentacion = strip_tags($producto->detalle->descripcion);
                }

                $doc->cliente->nombre = $doc->cliente->persona->nombre;
                $doc->cliente->direccion = $doc->cliente->persona->direccion;
            } else{
                //si es presupuesto a copiar
                $doc = Presupuesto::find($request->idpresupuesto);
                $productos = $doc->productos;
                $doc->cliente->persona;

                foreach ($productos as $producto) {
                    $producto->precio = round($producto->detalle->precio,2);
                    $producto->cantidad = $producto->detalle->cantidad;
                    $producto->presentacion = strip_tags($producto->detalle->descripcion);
                }

                $doc->cliente->nombre = $doc->cliente->persona->nombre;
                $doc->cliente->direccion = $doc->cliente->persona->direccion;
            }

            return json_encode($doc);

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function obtenerDocumentos(Request $request)
    {
        $consulta = trim($request->get('textoBuscado'));

        if ($request->comprobante == -1) {
            $copiar = DB::table('ventas')
                ->join('persona', 'persona.idpersona', '=', 'ventas.idcliente')
                ->join('facturacion', 'facturacion.idventa', '=', 'ventas.idventa')
                ->select('ventas.idventa', 'facturacion.estado', 'facturacion.serie', 'facturacion.correlativo', 'facturacion.codigo_tipo_documento', 'persona.nombre', 'ventas.total_venta')
                ->whereRaw('(persona.nombre LIKE "%' . $consulta . '%" or CONCAT_WS("-",facturacion.serie,facturacion.correlativo) LIKE "%' . $consulta . '%")')
                ->where(function ($query) {
                    $query->where('facturacion.codigo_tipo_documento', '03')
                        ->orWhere('facturacion.codigo_tipo_documento', '01')
                        ->orWhere('facturacion.codigo_tipo_documento', '30');
                })
                ->where('eliminado', '=', 0)
                ->orderby('idventa', 'desc')
                ->take(10)
                ->get();
        } else {
            $copiar = DB::table('guia')
                ->join('persona', 'persona.idpersona', '=', 'guia.idcliente')
                ->select('guia.idguia', 'guia.estado', 'guia.correlativo', 'persona.nombre')
                ->whereRaw('(persona.nombre LIKE "%' . $consulta . '%" or guia.correlativo LIKE "%' . $consulta . '%")')
                ->orderby('idguia', 'desc')
                ->take(10)
                ->get();
        }


        return $copiar;
    }

    public function show($id)
    {
        $guia = Guia::find($id);
        $productos = $guia->productos;
        $guia->cliente;
        $guia->persona;
        $emisor = new Emisor();
        $datos_adicionales = json_decode($guia->guia_datos_adicionales, TRUE);

        $guia->peso_bruto=$datos_adicionales['peso'];
        $guia->unidad_medida_peso_bruto='KGM';
        $guia->cantidad_bultos=$datos_adicionales['bultos'];
        $guia->indicador_transbordo_programado='false';
        $guia->categoria_vehiculo=$datos_adicionales['categoria_vehiculo'];
        $guia->tipo_transporte=$datos_adicionales['tipo_transporte']=='01'?'PÚBLICO':'PRIVADO'; //01 Publico y 02 privado catalogo 18 sunat
        $guia->tipo_doc_transportista=$datos_adicionales['tipo_doc_transportista'];
        $guia->num_doc_transportista=$datos_adicionales['num_doc_transportista'];
        $guia->razon_social_transportista=$datos_adicionales['razon_social_transportista'];
        $guia->placa_vehiculo=$datos_adicionales['placa_vehiculo'];
        $guia->dni_conductor=$datos_adicionales['dni_conductor'];
        $guia->direccion_llegada=$datos_adicionales['direccion'];
        $guia->ubigeo_direccion_llegada=$datos_adicionales['ubigeo'];
        $guia->motivo_traslado=$datos_adicionales['motivo_traslado'];
        $guia->num_doc_relacionado=$datos_adicionales['num_doc_relacionado'];
        $guia->num_oc=$datos_adicionales['oc']??'';

        if($guia->num_doc_relacionado == $guia->num_oc){
            $guia->num_oc = null;
        }

        $guia->fecha_traslado=date('Y-m-d', strtotime($datos_adicionales['fecha_traslado']));
        $ticket_json = json_decode($guia->ticket, true)??[];
        $guia->ticket = $ticket_json[count($ticket_json) - 1]['numTicket']??0;

        $guia->nombre_fichero = $emisor->ruc . '-09-' . $guia->correlativo;

        foreach ($productos as $producto){
            $producto->detalle->descripcion = strip_tags($producto->detalle->descripcion);
        }

        switch ($guia->estado){
            case 'PENDIENTE':
                $guia->badge_class='badge-warning';
                break;
            case 'ACEPTADO':
                $guia->badge_class='badge-success';
                break;
            case 'ANULADO':
            case 'MODIFICADO':
                $guia->badge_class='badge-dark';
                break;
            case 'RECHAZADO':
                $guia->badge_class='badge-danger';
        }


        /*LEER EL CDR*/
        $file_xml=storage_path().'/app/sunat/cdr/R-'.$guia->nombre_fichero.'.xml';
        if(file_exists($file_xml)){
            $cdr_xml = simplexml_load_file($file_xml);
            $Note=$cdr_xml->xpath('//cbc:Note');
            $guia->nota = $Note[0]??false;
        }

        return view('guia.visualizar', ['guia' => $guia, 'usuario' => auth()->user()->persona]);
    }

    public function enviar_comprobantes_por_email(Request $request)
    {
        try {
            PdfHelper::generarPdfGuia($request->idguia, false, 'F');
            Mail::to($request->mail)->send(new EnviarDocumentos($request));
            if(file_exists(storage_path() . '/app/sunat/pdf/' . $request->guia . '.pdf')){
                unlink(storage_path() . '/app/sunat/pdf/' . $request->guia . '.pdf');
            }
            return 'Se envió el correo con éxito';
        } catch (\Swift_TransportException $e) {
            return response(['mensaje'=>$e->getMessage()],500);
        }
    }

    public function correccion($id){

        $guia = Guia::find($id);
        $guia->cliente;
        $guia->persona;
        $persona = $guia->cliente->persona;
        $guia->cliente->nombre = $persona->nombre;

        $datos_adicionales = json_decode($guia->guia_datos_adicionales, TRUE);
        $guia->peso_bruto=$datos_adicionales['peso'];
        $guia->unidad_medida_peso_bruto='KGM';
        $guia->cantidad_bultos=$datos_adicionales['bultos'];
        $guia->indicador_transbordo_programado='false';
        $guia->tipo_transporte=$datos_adicionales['tipo_transporte']; //01 Publico y 02 privado catalogo 18 sunat
        $guia->tipo_doc_transportista=$datos_adicionales['tipo_doc_transportista'];
        $guia->num_doc_transportista=$datos_adicionales['num_doc_transportista'];
        $guia->razon_social_transportista=$datos_adicionales['razon_social_transportista'];
        $guia->placa_vehiculo=$datos_adicionales['placa_vehiculo'];
        $guia->dni_conductor=$datos_adicionales['dni_conductor'];
        $guia->licencia_conductor=$datos_adicionales['licencia_conductor']??'';
        $guia->categoria_vehiculo=$datos_adicionales['categoria_vehiculo']??'';
        $guia->nombre_conductor=$datos_adicionales['nombre_conductor']??'';
        $guia->apellido_conductor=$datos_adicionales['apellido_conductor']??'';
        $guia->direccion_llegada=$datos_adicionales['direccion'];
        $guia->ubigeo_direccion_llegada=$datos_adicionales['ubigeo'];
        $guia->motivo_traslado=$datos_adicionales['motivo_traslado'];
        $guia->doc_relacionado=$datos_adicionales['doc_relacionado'];
        $guia->num_doc_relacionado=$datos_adicionales['num_doc_relacionado'];
        $guia->oc=$datos_adicionales['oc']??'';
        $guia->fecha_traslado=date('Y-m-d', strtotime($datos_adicionales['fecha_traslado']));

        $motivo_traslado = DataGuia::getMotivoTraslado();
        foreach ($motivo_traslado as $motivo){
            if($guia->motivo_traslado == strtoupper($motivo['label'])){
                $guia->motivo_traslado = $motivo['num_val'];
                break;
            }
        }

        $emisor=new Emisor();
        return view('guia.correccion', ['ruc_emisor'=>json_encode($emisor->ruc),'guia' => $guia,'usuario' => auth()->user()->persona]);

     }

    public function categorias()
    {
        return ['categorias'=>Categoria::where('eliminado',0)->get()];

    }

    public function guardarProducto(Request $request){
        $producto = new ProductoController();
        $producto->store($request);
    }

    public function imprimir_guia(Request $request, $idguia)
    {
        try{
            return PdfHelper::generarPdfGuia($idguia, $request->rawbt, false, $request->formato);
        } catch (\Exception $e){
            Log::info($e);
            return response(['idventa'=>-1,'respuesta'=>$e->getMessage()],500);
        }
    }

}
