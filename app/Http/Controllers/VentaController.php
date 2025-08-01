<?php

namespace sysfact\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use sysfact\Categoria;
use sysfact\Emisor;
use sysfact\Facturacion;
use sysfact\Guia;
use sysfact\Http\Controllers\Cpe\CpeController;
use sysfact\Http\Controllers\Helpers\MainHelper;
use sysfact\Http\Controllers\Helpers\PdfHelper;
use sysfact\Http\Controllers\Helpers\UsesSmtpOverride;
use sysfact\Inventario;
use sysfact\Mail\EnviarDocumentos;
use sysfact\Orden;
use sysfact\Pago;
use sysfact\Presupuesto;
use sysfact\Produccion;
use sysfact\Producto;
use sysfact\Serie;
use sysfact\Venta;
use Illuminate\Support\Facades\Log;

class VentaController extends Controller
{
    private $serie_comprobante;
    private $interfaz;
    private $idcaja;
    private $porcentaje_igv = 18;
    use UsesSmtpOverride;

    public function __construct()
    {
        $this->middleware('auth');
        $this->serie_comprobante = new Serie();
        $this->interfaz = json_decode(MainHelper::configuracion('interfaz_pedidos'), true);
        //$this->idcaja = MainHelper::obtener_idcaja();
    }

    public function registrar(Request $request)
    {
        $serie_comprobates = $this->serie_comprobante->getSeries();
        $emisor = new Emisor();
        $notaDeVenta = filter_var($request->notaDeVenta, FILTER_VALIDATE_BOOLEAN);

        if ($notaDeVenta) {
            $view = 'ventas/nota_de_venta';
        } else {
            $view = 'ventas/registrar';
        }

        return view($view, [
            'ruc_emisor' => json_encode($emisor->ruc),
            'usuario' => auth()->user()->persona,
            'disabledVentas' => MainHelper::disabledVentas()[1],
            'serie_comprobantes' => $serie_comprobates
        ]);

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

    public function obtenerDecuentoNc()
    {
        $productos = Producto::where('idproducto', -2)
            ->first();
        return $productos;
    }

    public function obtenerDocumentos(Request $request)
    {
        $consulta = trim($request->get('textoBuscado'));

        switch ($request->comprobante) {
            //copiar venta
            case -1:
                $ventas = DB::table('ventas')
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
                break;
            case -2:
                $ventas = DB::table('ventas')
                    ->join('persona', 'persona.idpersona', '=', 'ventas.idcliente')
                    ->join('facturacion', 'facturacion.idventa', '=', 'ventas.idventa')
                    ->select('ventas.idventa', 'facturacion.estado', 'ventas.ticket', 'facturacion.codigo_tipo_documento', 'persona.nombre', 'ventas.total_venta')
                    ->whereRaw('(persona.nombre LIKE "%' . $consulta . '%" or ventas.ticket LIKE "%' . $consulta . '%")')
                    ->where(function ($query) {
                        $query->where('facturacion.codigo_tipo_documento', '30');
                    })
                    ->where('eliminado', 0)
                    ->orderby('idventa', 'desc')
                    ->take(30)
                    ->get();
                break;
            case -3:
                $ventas = DB::table('ventas')
                    ->join('persona', 'persona.idpersona', '=', 'ventas.idcliente')
                    ->join('facturacion', 'facturacion.idventa', '=', 'ventas.idventa')
                    ->select('ventas.idventa', 'facturacion.estado', 'facturacion.serie', 'facturacion.correlativo', 'facturacion.codigo_tipo_documento', 'persona.nombre', 'ventas.total_venta')
                    ->whereRaw('(persona.nombre LIKE "%' . $consulta . '%" or CONCAT_WS("-",facturacion.serie,facturacion.correlativo) LIKE "%' . $consulta . '%")')
                    ->where(function ($query) {
                        $query->where('facturacion.codigo_tipo_documento', '30');
                    })
                    ->where('adelanto', '>', 0)
                    ->where('eliminado', '=', 0)
                    ->orderby('idventa', 'desc')
                    ->take(10)
                    ->get();
                break;
            default:
                $ventas = DB::table('ventas')
                    ->join('persona', 'persona.idpersona', '=', 'ventas.idcliente')
                    ->join('facturacion', 'facturacion.idventa', '=', 'ventas.idventa')
                    ->select('ventas.idventa', 'facturacion.estado', 'facturacion.serie', 'facturacion.correlativo', 'facturacion.codigo_tipo_documento', 'persona.nombre', 'ventas.total_venta')
                    ->whereRaw('(persona.nombre LIKE "%' . $consulta . '%" or CONCAT_WS("-",facturacion.serie,facturacion.correlativo) LIKE "%' . $consulta . '%") and  eliminado=0 and facturacion.estado = "ACEPTADO" and facturacion.codigo_tipo_documento = ' . $request->comprobante)
                    ->orderby('idventa', 'desc')
                    ->take(10)
                    ->get();
        }


        return $ventas;
    }

    public function obtenerCorrelativoGuia()
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
                ->where('guia_relacionada', 'LIKE', 'T%')
                ->orderby('idventa', 'desc')
                ->first();
            if ($guia_tabla_facturacion) {
                $num = explode('-', $guia_tabla_facturacion->guia_relacionada)[1];
                $correlativo = 'T001-' . str_pad($num + 1, 8, '0', STR_PAD_LEFT);
            } else {
                $correlativo = 'T001-00000001';
            }
        }

        return json_encode($correlativo);
    }

    public function obtenerCorrelativo($tipo_comprobante)
    {
        switch ($tipo_comprobante) {
            case '03':
                $serie = $this->serie_comprobante->serie_boleta;
                break;
            case '01':
                $serie = $this->serie_comprobante->serie_factura;
                break;
            case '07.01':
                $serie = $this->serie_comprobante->serie_nota_credito_boleta;
                break;
            case '07.02':
                $serie = $this->serie_comprobante->serie_nota_credito_factura;
                break;
            case '08.01':
                $serie = $this->serie_comprobante->serie_nota_credito_boleta;
                break;
            case '08.02':
                $serie = $this->serie_comprobante->serie_nota_debito_factura;
                break;
            default:
                $serie = $this->serie_comprobante->serie_recibo;
        }

        $venta = DB::table('facturacion')
            ->select('correlativo')
            ->where('serie', $serie)
            ->orderby('correlativo', 'desc')
            ->first();


        if ($venta) {
            $correlativo = str_pad($venta->correlativo + 1, 8, '0', STR_PAD_LEFT);
        } else {
            $correlativo = '00000001';
        }

        return json_encode($correlativo);
    }

    //funcion para agregar la factura o boleta relaciona con nota de credito/debito

    public function copiarVenta(Request $request)
    {

        try {

            $venta = Venta::find($request->idventa);
            $productos = $venta->productos;
            $venta->cliente->persona;
            $venta->facturacion;
            $venta->facturacion->tipo_descuento_global = $venta->facturacion->tipo_descuento;

            if ($venta->tipo_pago == 2) {
                //Si es crédito
                $venta->tipo_pago_contado = 1;
            } else {
                //Si es efectivo u otro tipo
                if ($venta->tipo_pago == 4) {
                    $venta->tipo_pago_contado = 1;
                } else {
                    $venta->tipo_pago_contado = $venta->tipo_pago;
                }
                $venta->tipo_pago = 1;

            }

            foreach ($productos as $producto) {

                $producto->tipoAfectacion = $producto->detalle->afectacion;
                $producto->porcentaje_descuento = $producto->detalle->porcentaje_descuento;
                $producto->descuento = $producto->detalle->descuento;
                $producto->descuento_por_und = $producto->detalle->descuento_por_und;
                $producto->tipo_descuento = $producto->detalle->tipo_descuento;
                $producto->stock = $producto->inventario()->first()->saldo ?? 0;
                $producto->precio = $producto->detalle->monto;
                $producto->cantidad = $producto->detalle->cantidad;
                $producto->presentacion = strip_tags($producto->detalle->descripcion);
                $producto->subtotal = $producto->detalle->subtotal;
                $producto->igv = $producto->detalle->igv;
                $producto->total = $producto->detalle->total;
                $producto->items_kit = json_decode($producto->detalle->items_kit, true);
            }

            $venta->comprobante_referencia = $venta->facturacion->serie . '-' . $venta->facturacion->correlativo;
            $venta->anulacion_factura_exportacion = $venta->facturacion->codigo_tipo_factura == '0200' ? 'EXP' : '';
            $venta->cliente->nombre = $venta->cliente->persona->nombre;
            $venta->cliente->nombre_o_razon_social = $venta->cliente->persona->nombre;
            $venta->cliente->direccion = $venta->cliente->persona->direccion;


            return json_encode($venta);

        } catch (\Exception $e) {
            Log::error($e);
            return $e;
        }
    }

    public function copiarPedido(Request $request)
    {

        try {

            $presupuesto = Orden::find($request->idventa);
            $productos = $presupuesto->productos;
            $presupuesto->cliente->persona;
            $presupuesto->tipo_pago_contado = 1;
            $suma_total = 0;

            foreach ($productos as $producto) {
                $subtotal = round($producto->detalle->monto * $producto->detalle->cantidad, 2);
                $total = round($producto->detalle->monto * $producto->detalle->cantidad * 1.18, 2);
                $producto->tipoAfectacion = '10';
                $producto->porcentaje_descuento = '0';
                $producto->descuento = '0.00';
                $producto->precio = $producto->detalle->monto;
                $producto->cantidad = $producto->detalle->cantidad;
                $producto->presentacion = $producto->detalle->descripcion;
                $producto->subtotal = $subtotal;
                $producto->igv = round($total - $subtotal, 2);
                $producto->total = $total;
                $producto->stock = $producto->inventario()->first()->saldo ?? 0;
                $producto->items_kit = null;
                $suma_total += $total;
            }

            $presupuesto->cliente->nombre = $presupuesto->cliente->persona->nombre;
            $presupuesto->cliente->nombre_o_razon_social = $presupuesto->cliente->persona->nombre;
            $presupuesto->cliente->direccion = $presupuesto->cliente->persona->direccion;
            $presupuesto->facturacion = new Facturacion();
            $presupuesto->facturacion->porcentaje_descuento_global = '0.00';
            $presupuesto->facturacion->descuento_global = '0.00';
            $presupuesto->facturacion->base_descuento_global = '0.00';
            $presupuesto->facturacion->valor_venta_bruto = round($suma_total / 1.18, 2);
            $presupuesto->facturacion->total_exoneradas = '0.00';
            $presupuesto->facturacion->total_inafectas = '0.00';
            $presupuesto->facturacion->total_gratuitas = '0.00';
            $presupuesto->facturacion->total_gravadas = round($suma_total / 1.18, 2);
            $presupuesto->facturacion->total_descuentos = '0.00';
            $presupuesto->facturacion->igv = round($suma_total - ($suma_total / 1.18), 2);
            $presupuesto->facturacion->codigo_moneda = 'PEN';
            $presupuesto->facturacion->codigo_tipo_documento = '30';
            $presupuesto->total_venta = round($suma_total, 2);
            $presupuesto->tipo_pago = 1;

            return json_encode($presupuesto);

        } catch (\Exception $e) {
            Log::error($e);
            return $e;
        }

    }

    public function store(Request $request)
    {

        $serie_venta = DB::table('facturacion')
            ->select('correlativo')
            ->where('serie', $request->serie)
            ->orderby('correlativo', 'desc')
            ->first();

        if ($serie_venta) {
            $correlativo = str_pad($serie_venta->correlativo + 1, 8, '0', STR_PAD_LEFT);
        } else {
            $correlativo = '00000001';
        }

        $objAdelanto = json_decode($request->objAdelanto, TRUE);

        DB::beginTransaction();
        try {
            $venta = new Venta();
            $venta->idempleado = auth()->user()->idempleado;
            $venta->idcliente = $request->idcliente ?? -1;
            $venta->idcajero = auth()->user()->idempleado;
            $venta->idcaja = MainHelper::obtener_idcaja();
            $venta->fecha = $request->fecha . ' ' . date('H:i:s');
            $venta->tipo_cambio = $request->tipo_cambio ?? cache('opciones')['tipo_cambio_compra'];
            if ($request->fecha > date('Y-m-d')) {
                $venta->fecha = date('Y-m-d H:i:s');
            }
            $venta->total_venta = $request->total_venta;
            if ($request->adelanto) {
                $venta->adelanto = $request->total_venta;
            }
            if ($objAdelanto) {
                $venta->adelanto = $objAdelanto['total_venta'] * -1;
            }

            if ($request->tipo_pago == 2) {
                $venta->tipo_pago = $request->tipo_pago;
            } else {
                $venta->tipo_pago = $request->tipo_pago_contado;
            }
            $venta->igv_incluido = $request->esConIgv;
            $venta->observacion = $request->doc_observacion;
            $venta->save();
            $idventa = $venta->idventa;

            if ($request->moneda == 'S/') {
                $moneda = 'PEN';
            } else {
                $moneda = 'USD';
            }

            $facturacion = new Facturacion();
            $facturacion->codigo_tipo_documento = $request->comprobante;
            $facturacion->codigo_tipo_factura = $request->codigo_tipo_factura == '1' ? '0101' : $request->codigo_tipo_factura;
            $facturacion->idventa = $idventa;
            $facturacion->serie = $request->serie;
            $facturacion->correlativo = $correlativo;
            $facturacion->codigo_moneda = $moneda;
            $facturacion->total_exoneradas = $request->exoneradas;
            $facturacion->total_gratuitas = $request->gratuitas;
            $facturacion->total_gravadas = $request->gravadas;
            $facturacion->total_inafectas = $request->inafectas;
            $facturacion->total_descuentos = $request->descuentos;
            $facturacion->igv = $request->igv;
            $facturacion->valor_venta_bruto = $request->subtotal;
            $facturacion->porcentaje_descuento_global = $request->porcentaje_descuento_global / 100;
            $facturacion->descuento_global = $request->monto_descuento_global;
            $facturacion->tipo_descuento = $request->tipo_descuento;
            $facturacion->base_descuento_global = $request->base_descuento_global;
            $facturacion->estado = 'PENDIENTE';

            if ($request->comprobante == '07') {
                $facturacion->motivo_baja = $request->anulacion_factura_exportacion ?? '';
            }

            if ($request->codigo_tipo_factura == 1) {
                $facturacion->retencion = 1;
            } else {
                $facturacion->retencion = 0;
            }

            if ($request->codigo_tipo_factura == '1001') {
                $facturacion->tipo_detraccion = $request->tipo_detraccion;
            } else {
                $facturacion->tipo_detraccion = 0;
            }

            if ($request->comprobante == 20 || $request->comprobante == 30) $facturacion->estado = '-';
            $facturacion->num_doc_relacionado = $request->num_doc_relacionado;
            $facturacion->descripcion_nota = $request->observacion;

            $tipo_doc = explode('-', $request->num_doc_relacionado);
            if ($tipo_doc[0] == $this->serie_comprobante->serie_boleta) {
                $tipo_doc_relacionado = '03';
            } else if ($tipo_doc[0] == $this->serie_comprobante->serie_factura) {
                $tipo_doc_relacionado = '01';
            } else {
                $tipo_doc_relacionado = -1;
            }
            $facturacion->tipo_doc_relacionado = $tipo_doc_relacionado;
            $facturacion->tipo_nota_electronica = $request->tipo_nota_electronica;
            $facturacion->oc_relacionada = trim($request->num_oc);
            $facturacion->guia_relacionada = trim($request->num_guia);

            $guias = json_decode($request->guias_relacionadas, true);
            $parse_guias_string = '';

            for ($i = 0; $i < count($guias); $i++) {
                $parse_guias_string .= $guias[$i]['correlativo'];
                if ($i + 1 < count($guias)) {
                    $parse_guias_string .= ',';
                }
            }

            $facturacion->guia_fisica = strtoupper($parse_guias_string);
            $facturacion->doc_relacionado_nc = $request->doc_relacionado_nc;
            $guardado = $facturacion->save();


            if ($guardado) {
                $detalle = [];
                $items = json_decode($request->items, TRUE);
                $i = 1;
                foreach ($items as $item) {
                    $detalle['num_item'] = $i;
                    $detalle['cantidad'] = trim($item['cantidad']);
                    $detalle['monto'] = trim($item['precio']);
                    $detalle['tipo_descuento'] = $item['tipo_descuento'];
                    $detalle['descuento_por_und'] = $item['descuento_por_und'];
                    $detalle['descuento'] = trim($item['descuento']);
                    $detalle['descripcion'] = nl2br(trim($item['presentacion']));
                    $detalle['afectacion'] = $item['tipoAfectacion'];
                    $detalle['porcentaje_descuento'] = trim($item['porcentaje_descuento']);
                    $detalle['subtotal'] = $item['subtotal'];
                    $detalle['igv'] = $item['igv'];
                    $detalle['total'] = $item['total'];
                    $detalle['items_kit'] = json_encode($item['items_kit']);
                    $venta->productos()->attach($item['idproducto'], $detalle);

                    //Actualizar inventario
                    $item['detalle']['items_kit'] = json_encode($item['items_kit']);
                    $inv = Inventario::where('idproducto', $item['idproducto'])->orderby('idinventario', 'desc')->first();

                    if ($request->comprobante != '07') {
                        MainHelper::actualizar_inventario($idventa, $item, $inv, 'venta');
                    } else {
                        MainHelper::actualizar_inventario($request->idventa_modifica, $item, $inv, 'anulacion_nc');
                    }

                    $i++;
                }

            }

            //Guardar tipo de pago
            if ($request->comprobante != '08') {
                if ($request->tipo_pago == 2) {
                    $cuotas = json_decode($request->cuotas, TRUE);
                    foreach ($cuotas as $cuota) {
                        $pago = new Pago();
                        $pago->monto = $cuota['monto'];
                        $pago->tipo = 2;
                        $pago->fecha = $cuota['fecha'];
                        $venta->pago()->save($pago);
                    }
                } else {
                    if ($request->comprobante != '07') {
                        if ($request->tipo_pago_contado == 4) {
                            $fraccionado = json_decode($request->pago_fraccionado, TRUE);
                            foreach ($fraccionado as $item) {
                                $pago = new Pago();
                                $pago->monto = $item['monto'];
                                $pago->tipo = $item['tipo'];
                                $pago->fecha = date('Y-m-d H:i:s');
                                $venta->pago()->save($pago);
                            }
                        } else {
                            $monto = $request->total_venta;
                            if ($objAdelanto) {
                                $monto = $request->total_venta - $objAdelanto['total_venta'];
                            }
                            $pago = new Pago();
                            $pago->monto = $monto;
                            $pago->tipo = $request->tipo_pago_contado;
                            $pago->fecha = date('Y-m-d H:i:s');
                            $venta->pago()->save($pago);
                        }
                    }

                }
            }

            DB::commit();

            //Guardamos la guía asociada si existe
            $idguia = -1;
            if ($request->esConGuia) {
                $guia_remision = new GuiaController();
                $idguia = $guia_remision->store($request, $venta);
            }

            //Generar archivos
            $response_guia = '';
            if ($request->comprobante != '30') {
                $cpe = new CpeController();
                $cpe->generarArchivos($idventa);

                if ($request->esConGuia) {
                    //Procesar guia:
                    if ($idguia > 0) {
                        $response_guia = $cpe->sendGuia($idguia);
                    } else {
                        $response_guia = 'La guía no ha podido ser procesada.';
                    }

                    $response_guia = ' / Se ha procesado la guia con ' . $response_guia;

                }
            }

            if ($request->comprobante == '01' || $request->comprobante == '03' || $request->comprobante == '30') {
                $texto = 'la venta';
            } else {
                $texto = 'la nota';
            }

            $respuesta_sunat = 'Se ha guardado ' . $texto . ' correctamente: ' . $request->serie . '-' . $correlativo . ' ' . $response_guia;

            return json_encode(['idventa' => $idventa, 'respuesta' => $respuesta_sunat]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::info($e);
            return response(['mensaje' => 'Ha ocurrido un error al guardar la venta: ' . $e->getMessage()], 500);
        }

    }

    public function show($id)
    {
        $venta = Venta::find($id);
        $productos = $venta->productos;
        $venta->facturacion;
        $venta->cliente;
        $venta->persona;
        $venta->retencion = round($venta->total_venta * 0.03, 2);
        $venta->monto_menos_retencion = round($venta->total_venta - $venta->retencion, 2);

        if ($venta->facturacion->tipo_detraccion) {
            $detraccion = explode('/', $venta->facturacion->tipo_detraccion);
            $porcentaje_detraccion = number_format($detraccion[1], 2);

            $venta->detraccion = round($venta->total_venta * ($porcentaje_detraccion / 100), 2);
            $venta->monto_menos_detraccion = round($venta->total_venta - $venta->detraccion, 2);
        }

        $emisor = new Emisor();

        $venta->nombre_fichero = $emisor->ruc . '-' . $venta->facturacion->codigo_tipo_documento . '-' . $venta->facturacion->serie . '-' . $venta->facturacion->correlativo;
        $venta->text_whatsapp = MainHelper::texto_whatsap($venta, $emisor);

        switch ($venta->facturacion->codigo_tipo_documento) {
            case '03':
                $venta->facturacion->comprobante = 'Boleta';
                break;
            case '01':
                $venta->facturacion->comprobante = 'Factura';
                break;
            case '07':
                $venta->facturacion->comprobante = 'Nota de crédito';
                break;
            case '08':
                $venta->facturacion->comprobante = 'Nota de débito';
                break;
            default:
                $venta->facturacion->comprobante = 'Venta';
        }

        foreach ($productos as $producto) {
            $producto->detalle->descripcion = strip_tags($producto->detalle->descripcion);
            $producto->items_kit = json_decode($producto->detalle->items_kit, true);
            $ex = explode('/', $producto->unidad_medida);
            $producto->unidad_medida = $ex[1];
        }

        if ($venta->facturacion->codigo_moneda == 'PEN') {
            $venta->codigo_moneda = 'S/';
        } else {
            $venta->codigo_moneda = 'USD';
        }

        $venta->guia_relacionada = $venta->guia->first();
        if ($venta->guia_relacionada) {
            $ticket_json = json_decode($venta->guia_relacionada['ticket'], true) ?? [];
            $venta->guia_relacionada['ticket'] = $ticket_json[count($ticket_json) - 1]['numTicket'] ?? 0;
        }


        /*LEER EL CDR*/
        $file_xml = storage_path() . '/app/sunat/cdr/R-' . $venta->nombre_fichero . '.xml';
        if (file_exists($file_xml)) {
            $cdr_xml = simplexml_load_file($file_xml);
            $Description = $cdr_xml->xpath('//cbc:Description');
            $venta->motivo_rechazo = $Description[0] ?? false;
        }

        if ($venta->guia_relacionada) {

            $venta->nombre_guia = $emisor->ruc . '-09-' . $venta->guia_relacionada['correlativo'];

        }

        return view('ventas.visualizar', [
            'venta' => $venta,
            'usuario' => auth()->user()->persona
        ]);
    }

    public function copiarPresupuesto(Request $request)
    {

        try {

            $presupuesto = Presupuesto::find($request->idventa);
            $productos = $presupuesto->productos;
            $presupuesto->cliente->persona;
            $suma_total = 0;

            foreach ($productos as $producto) {
                $subtotal = round($producto->detalle->monto * $producto->detalle->cantidad, 2);
                $total = round($producto->detalle->monto * $producto->detalle->cantidad * 1.18, 2);
                $producto->tipoAfectacion = '10';
                $producto->porcentaje_descuento = floatval($producto->detalle->porcentaje_descuento);
                $producto->descuento = $producto->detalle->descuento;
                $producto->descuento_por_und = $producto->detalle->descuento_por_und;
                $producto->tipo_descuento = $producto->detalle->tipo_descuento;
                $producto->precio = $producto->detalle->monto;
                $producto->cantidad = $producto->detalle->cantidad;
                $producto->presentacion = strip_tags($producto->detalle->descripcion);
                $producto->subtotal = $subtotal;
                $producto->igv = round($total - $subtotal, 2);
                $producto->total = $total;
                $producto->stock = $producto->inventario()->first()->saldo ?? 0;
                $producto->items_kit = json_decode($producto->items_kit, true);
                $suma_total += $total;
            }

            $presupuesto->cliente->nombre = $presupuesto->cliente->persona->nombre;
            $presupuesto->cliente->direccion = $presupuesto->cliente->persona->direccion;
            $presupuesto->facturacion = new Facturacion();
            $presupuesto->facturacion->porcentaje_descuento_global = floatval($presupuesto->porcentaje_descuento / 100);
            $presupuesto->facturacion->descuento_global = $presupuesto->descuento;
            $presupuesto->facturacion->tipo_descuento_global = $presupuesto->tipo_descuento;
            $presupuesto->facturacion->base_descuento_global = '0.00';
            $presupuesto->facturacion->valor_venta_bruto = round($suma_total / 1.18, 2);
            $presupuesto->facturacion->total_exoneradas = '0.00';
            $presupuesto->facturacion->total_inafectas = '0.00';
            $presupuesto->facturacion->total_gratuitas = '0.00';
            $presupuesto->facturacion->total_gravadas = round($suma_total / 1.18, 2);
            $presupuesto->facturacion->total_descuentos = '0.00';
            $presupuesto->facturacion->igv = round($suma_total - ($suma_total / 1.18), 2);
            $presupuesto->facturacion->codigo_moneda = $presupuesto->moneda;
            $presupuesto->facturacion->codigo_tipo_documento = '30';
            $presupuesto->total_venta = round($suma_total, 2);
            $presupuesto->tipo_pago = 1;
            $presupuesto->tipo_pago_contado = 1;


            return json_encode($presupuesto);

        } catch (\Exception $e) {
            Log::error($e);
            return $e;
        }

    }

    public function copiarGuia(Request $request)
    {

        try {

            $guia = Guia::find($request->idventa);
            $productos = $guia->productos;
            $guia->cliente->persona;
            $suma_total = 0;

            foreach ($productos as $producto) {
                $subtotal = round($producto->precio * $producto->detalle->cantidad, 2);
                $total = round($producto->precio * $producto->detalle->cantidad * 1.18, 2);
                $producto->tipoAfectacion = '10';
                $producto->porcentaje_descuento = 0;
                $producto->descuento = 0;
                $producto->descuento_por_und = false;
                $producto->tipo_descuento = 1;
                $producto->cantidad = $producto->detalle->cantidad;
                $producto->presentacion = strip_tags($producto->detalle->descripcion);
                $producto->subtotal = $subtotal;
                $producto->igv = round($total - $subtotal, 2);
                $producto->total = $total;
                $suma_total += $total;
                $producto->items_kit = null;
            }

            $guia->cliente->nombre = $guia->cliente->persona->nombre;
            $guia->cliente->direccion = $guia->cliente->persona->direccion;
            $guia->facturacion = new Facturacion();
            $guia->facturacion->tipo_descuento_global = 0;
            $guia->facturacion->descuento_global = 0;
            $guia->facturacion->porcentaje_descuento_global = '0.00';
            $guia->facturacion->valor_venta_bruto = round($suma_total / 1.18, 2);
            $guia->facturacion->total_exoneradas = '0.00';
            $guia->facturacion->total_inafectas = '0.00';
            $guia->facturacion->total_gratuitas = '0.00';
            $guia->facturacion->total_gravadas = round($suma_total / 1.18, 2);
            $guia->facturacion->total_descuentos = '0.00';
            $guia->facturacion->igv = round($suma_total - ($suma_total / 1.18), 2);
            $guia->facturacion->codigo_moneda = 'PEN';
            $guia->facturacion->codigo_tipo_documento = '30';
            $guia->facturacion->oc_relacionada = $guia->num_oc;
            $guia->facturacion->guia_relacionada = $guia->num_guia;
            $guia->facturacion->codigo_tipo_documento = '01';
            $guia->total_venta = round($suma_total, 2);
            $guia->tipo_pago = 1;
            $guia->tipo_pago_contado = 1;


            return json_encode($guia);

        } catch (\Exception $e) {
            Log::error($e);
            return $e;
        }

    }

    public function copiarOrden(Request $request)
    {

        try {

            $orden = Orden::find($request->idventa);
            $productos = $orden->productos;
            $orden->cliente->persona;
            $orden->tipo_pago_contado = 1;
            $suma_total = 0;

            foreach ($productos as $producto) {

                foreach ($producto->inventario as $kardex) {
                    $producto->stock += $kardex->cantidad;
                }

                $subtotal = round($producto->detalle->monto * $producto->detalle->cantidad, 2);
                $total = round($producto->detalle->monto * $producto->detalle->cantidad * 1.18, 2);
                $producto->tipoAfectacion = '10';
                $producto->porcentaje_descuento = '0';
                $producto->descuento = '0.00';
                $producto->precio = $producto->detalle->monto;
                $producto->cantidad = $producto->detalle->cantidad;
                $producto->presentacion = $producto->detalle->descripcion;
                $producto->subtotal = $subtotal;
                $producto->igv = round($total - $subtotal, 2);
                $producto->total = $total;
                $producto->stock = $producto->inventario()->first()->saldo ?? 0;
                $producto->items_kit = null;
                $suma_total += $total;
            }

            $orden->cliente->nombre = $orden->cliente->persona->nombre;
            $orden->cliente->direccion = $orden->cliente->persona->direccion;
            $orden->facturacion = new Facturacion();
            $orden->facturacion->porcentaje_descuento_global = '0.00';
            $orden->facturacion->descuento_global = '0.00';
            $orden->facturacion->base_descuento_global = '0.00';
            $orden->facturacion->valor_venta_bruto = round($suma_total / 1.18, 2);
            $orden->facturacion->total_exoneradas = '0.00';
            $orden->facturacion->total_inafectas = '0.00';
            $orden->facturacion->total_gratuitas = '0.00';
            $orden->facturacion->total_gravadas = round($suma_total / 1.18, 2);
            $orden->facturacion->total_descuentos = '0.00';
            $orden->facturacion->igv = round($suma_total - ($suma_total / 1.18), 2);
            $orden->facturacion->codigo_moneda = $orden->moneda;
            $orden->facturacion->codigo_tipo_documento = $orden->comprobante;
            $orden->total_venta = round($suma_total, 2);
            $orden->tipo_pago = 1;


            return json_encode($orden);

        } catch (\Exception $e) {
            Log::error($e);
            return $e;
        }

    }

    public function copiarProduccion(Request $request)
    {

        try {

            $guia = Produccion::find($request->idventa);
            $productos = $guia->productos;
            $guia->cliente->persona;
            $suma_total = 0;

            foreach ($productos as $producto) {
                $subtotal = round($producto->precio * $producto->detalle->cantidad, 2);
                $total = round($producto->precio * $producto->detalle->cantidad * 1.18, 2);
                $producto->tipoAfectacion = '10';
                $producto->porcentaje_descuento = '0';
                $producto->descuento = '0.00';
                $producto->cantidad = $producto->detalle->cantidad;
                $producto->presentacion = strip_tags($producto->detalle->descripcion);
                $producto->subtotal = $subtotal;
                $producto->igv = round($total - $subtotal, 2);
                $producto->total = $total;
                $suma_total += $total;
                $producto->items_kit = null;
            }

            $guia->cliente->nombre = $guia->cliente->persona->nombre;
            $guia->cliente->direccion = $guia->cliente->persona->direccion;
            $guia->facturacion = new Facturacion();
            $guia->facturacion->porcentaje_descuento_global = '0.00';
            $guia->facturacion->valor_venta_bruto = round($suma_total / 1.18, 2);
            $guia->facturacion->total_exoneradas = '0.00';
            $guia->facturacion->total_inafectas = '0.00';
            $guia->facturacion->total_gratuitas = '0.00';
            $guia->facturacion->total_gravadas = round($suma_total / 1.18, 2);
            $guia->facturacion->total_descuentos = '0.00';
            $guia->facturacion->igv = round($suma_total - ($suma_total / 1.18), 2);
            $guia->facturacion->codigo_moneda = 'PEN';
            $guia->facturacion->codigo_tipo_documento = '30';
            $guia->facturacion->oc_relacionada = $guia->num_oc;
            $guia->facturacion->guia_relacionada = $guia->num_guia;
            $guia->facturacion->codigo_tipo_documento = '01';
            $guia->total_venta = round($suma_total, 2);
            $guia->tipo_pago = 1;
            $guia->tipo_pago_contado = 1;


            return json_encode($guia);

        } catch (\Exception $e) {
            Log::error($e);
            return $e;
        }

    }

    public function imprimir_venta(Request $request, $idventa)
    {
        try {
            return PdfHelper::generarPdf($idventa, $request->rawbt, false, $request->formato);
        } catch (\Exception $e) {
            Log::error($e);
            return response(['idventa' => -1, 'respuesta' => $e->getMessage()], 500);
        }

    }


    public function verificar_cdr_previo_mail(Request $request)
    {
        $cdr_factura = storage_path() . '/app/sunat/cdr/R-' . $request->factura . '.xml';
        $cdr_guia = storage_path() . '/app/sunat/cdr/R-' . $request->guia . '.xml';
        $mensaje = '';
        $error = false;
        if ($request->factura && !file_exists($cdr_factura)) {
            $error = true;
            $mensaje .= 'La factura no tiene el CDR.<br>';
        }
        if ($request->guia && !file_exists($cdr_guia)) {
            $error = true;
            $mensaje .= 'La guía no tiene el CDR.<br>';
        }

        if ($error) {
            $mensaje .= '¿Desea enviar de todas formas? <br><br>';
            return $mensaje . '<a style="text-decoration: underline !important;" href="/comprobantes/consulta-cdr">No, primero obtener el CDR</a>';
        } else {
            return 1;
        }

    }

public function enviar_comprobantes_por_email(Request $request)
    {
        try {
            $cc = json_decode($request->destinatarios) ?: [];

            PdfHelper::generarPdf($request->idventa, false, 'F');
            if ($request->idguia != -1) {
                PdfHelper::generarPdfGuia($request->idguia, false, 'F');
            }

            $smtpOverride = $this->getOverrideConfig();

            $mailable = new EnviarDocumentos($request, $smtpOverride['from_address']);

            $this->sendWithOverride($request->mail, $mailable, $smtpOverride, $cc);

            $venta = Venta::find($request->idventa);
            $data = $venta->datos_adicionales;

            $nuevo_mail = [
                [
                    'direccion' => $request->mail,
                    'fecha'     => date('Y-m-d H:i:s')
                ]
            ];
            foreach ($cc as $item) {
                $nuevo_mail[] = [
                    'direccion' => $item,
                    'fecha'     => date('Y-m-d H:i:s')
                ];
            }

            if (!$data) {
                $venta->datos_adicionales = json_encode(['mail' => $nuevo_mail]);
            } else {
                $existing = json_decode($data, true);
                $mail_array = $existing['mail'] ?? [];
                $mail_array = array_merge($mail_array, $nuevo_mail);
                $venta->datos_adicionales = json_encode(['mail' => $mail_array]);
            }
            $venta->save();

            // limpiar PDFs generados
            foreach (['factura', 'guia', 'recibo'] as $tipo) {
                $valor = $request->{$tipo} ?? null;
                if ($valor && file_exists(storage_path("app/sunat/pdf/{$valor}.pdf"))) {
                    @unlink(storage_path("app/sunat/pdf/{$valor}.pdf"));
                }
            }

            return 'Se envió el correo con éxito';
        } catch (\Swift_TransportException $e) {
            Log::error($e);
            return response(['mensaje' => $e->getMessage()], 500);
        } catch (\Throwable $e) {
            Log::error($e);
            return response(['mensaje' => $e->getMessage()], 500);
        }
    }
    public function eliminar_venta($idventa)
    {
        try {
            $venta = Venta::find($idventa);
            $detalle = $venta->productos;

            foreach ($detalle as $item) {
                $item_inv = $item->inventario()->first();
                MainHelper::actualizar_inventario($idventa, $item, $item_inv, 'anulacion');
            }

            $venta->eliminado = 1;
            $venta->save();

            //Actualizar pedido
            $venta->orden()->update([
                'estado' => 'VENTA ANULADA'
            ]);

        } catch (\Exception $e) {
            Log::info($e);
            return $e->getMessage();
        }

    }

    public function categorias()
    {
        return ['categorias' => Categoria::where('eliminado', 0)->get()];
    }

    public function guardarProducto(Request $request)
    {
        $producto = new ProductoController();
        $producto->store($request);
    }

    public function obtenerPedidos()
    {
        $pedidos = Orden::where('estado', 'EN COLA')
            ->orderby('idorden', 'DESC')->get();
        foreach ($pedidos as $pedido) {

            $pedido->trabajador->persona;
            $pedido->cliente->persona;
            switch ($pedido->comprobante) {
                case '01':
                    $pedido->badge_class = 'badge-warning';
                    $pedido->comprobante = 'FACTURA';
                    break;
                case '03':
                    $pedido->badge_class = 'badge-success';
                    $pedido->comprobante = 'BOLETA';
                    break;
                default:
                    $pedido->badge_class = 'badge-dark';
                    $pedido->comprobante = 'NINGUNO';
            }

        }

        return response()->json($pedidos);
    }

    public function verificar_correlativo($serie, $correlativo)
    {

        $venta = DB::table('facturacion')
            ->select('correlativo')
            ->where('serie', $serie)
            ->orderby('correlativo', 'desc')
            ->first();
        return str_pad($venta->correlativo + 1, 8, '0', STR_PAD_LEFT);

    }

    public function nuevo_cliente(Request $request)
    {
        $cliente = new ClienteController();
        return $cliente->store($request);
    }

    public function facturacion_rapida_alt(Request $request)
    {
        //guardamos el cliente si es nuevo
        $cliente = json_decode($request->cliente, true);
        if ($cliente['esNuevo']) {
            $req = new Request();
            $req->num_documento = $cliente['ruc'];
            $req->nombre = $cliente['nombre_o_razon_social'];
            $req->direccion = $cliente['direccion'];
            $req->tipo_documento = $cliente['tipo_doc'];
            $nuevo_cliente = new ClienteController();
            $idcliente = $nuevo_cliente->store_alt($req);
        } else {
            $idcliente = $cliente['idcliente'];
        }

        $request['idcliente'] = $idcliente;
        $this->verificar_pedido($request->idpedido, $request->items);

        return $this->facturacion_rapida($request);
    }

    public function facturacion_rapida(Request $request)
    {
        $pedido = Orden::find($request->idpedido);

        if (!$pedido) {
            return response()->json([
                'idventa' => -1,
                'respuesta' => 'El pedido que intentas procesar no existe o ha sido anulado previamente. Vuelve a generar el pedido',
                'file' => null,
                'ticket' => null
            ]);
        }

        if (count($pedido->productos) === 0) {
            return response()->json([
                'idventa' => -1,
                'respuesta' => 'El pedido no contiene el detalle de los productos'
            ]);
        }

        switch ($request->comprobante) {
            case 3:
                $serie = $this->serie_comprobante->serie_boleta;
                break;
            case 1:
                $serie = $this->serie_comprobante->serie_factura;
                break;
            default:
                $serie = $this->serie_comprobante->serie_recibo;
                break;
        }

        $ventaSerie = DB::table('facturacion')->select('correlativo')
            ->where('serie', $serie)
            ->orderBy('correlativo', 'desc')
            ->first();
        $correlativo = $ventaSerie ? str_pad($ventaSerie->correlativo + 1, 8, '0', STR_PAD_LEFT) : '00000001';

        $extra = $this->obtener_extradata($pedido);
        $alias = $extra['idcliente'];
        $observacion = $extra['observacion'];
        $nota = $extra['nota'];

        DB::beginTransaction();
        try {

            $i = 1;
            $suma_detalle = 0;

            foreach ($pedido->productos as $item) {
                $cantidad = $item->detalle->cantidad;
                $monto = $item->detalle->monto;
                $total_item = round($monto * $cantidad, 2);
                $suma_detalle += $total_item;

                if ($request->comprobante != 30 && ($total_item <= 0 || $cantidad <= 0)) {
                    return response()->json([
                        'idventa' => -1,
                        'respuesta' => 'No está permitido items con monto o cantidad menor o igual a 0. Edite su pedido',
                        'file' => null
                    ]);
                }

                if ($item->detalle->cantidad <= 0) {
                    return response()->json([
                        'idventa'   => -1,
                        'respuesta' => 'No se permiten cantidades menores o iguales a 0. Edite su pedido',
                        'file'      => null
                    ]);
                }

                if ($item->detalle->monto < 0) {
                    return response()->json([
                        'idventa'   => -1,
                        'respuesta' => 'No se permiten precios negativos. Edite su pedido',
                        'file'      => null
                    ]);
                }
            }

            $subtotal = round($suma_detalle / ($this->porcentaje_igv / 100 + 1), 2);
            $igv = round($suma_detalle - $subtotal, 2);

            if ($request->comprobante != 30 && $suma_detalle == 0) {
                return response()->json([
                    'idventa' => -1,
                    'respuesta' => 'No se puede generar comprobantes con TOTAL VENTA = 0.00, edite su pedido',
                    'file' => null
                ]);
            }

            $venta = new Venta();
            $venta->idempleado = $pedido->idempleado;
            $venta->idcliente = $request->idcliente ?: -1;
            $venta->idcajero = auth()->user()->idempleado;
            $venta->idcaja = MainHelper::obtener_idcaja();
            $venta->fecha = date('Y-m-d H:i:s');
            $venta->total_venta = $suma_detalle;
            $venta->tipo_pago = $request->tipo_pago_contado;
            $venta->observacion = $observacion;
            $venta->nota = $nota;
            $venta->igv_incluido = true;
            $venta->alias = $alias;
            $venta->save();

            $fact = new Facturacion();
            $fact->codigo_tipo_documento = $request->comprobante;
            $fact->idventa = $venta->idventa;
            $fact->serie = $serie;
            $fact->correlativo = $correlativo;
            $fact->codigo_moneda = 'PEN';
            $fact->total_gravadas = $subtotal;
            $fact->total_exoneradas = 0;
            $fact->total_gratuitas = 0;
            $fact->total_inafectas = 0;
            $fact->total_descuentos = 0;
            $fact->igv = $igv;
            $fact->valor_venta_bruto = $subtotal;
            $fact->porcentaje_descuento_global = 0;
            $fact->descuento_global = 0;
            $fact->base_descuento_global = $subtotal;
            $fact->estado = ($request->comprobante == 30) ? '-' : 'PENDIENTE';
            $fact->num_doc_relacionado = '';
            $fact->descripcion_nota = '';
            $fact->tipo_doc_relacionado = -1;
            $fact->tipo_nota_electronica = '01';
            $fact->oc_relacionada = '';
            $fact->guia_relacionada = '';
            $fact->guia_fisica = '';
            $fact->emitir_como_contado = $request->emitir_como_contado ?: null;
            $fact->save();

            foreach ($pedido->productos as $item) {
                $total_item = round($item->detalle->monto * $item->detalle->cantidad, 2);
                $subtotal_item = round($total_item / ($this->porcentaje_igv / 100 + 1), 2);
                $igv_item = round($total_item - $subtotal_item, 2);

                $venta->productos()->attach($item->idproducto, [
                    'num_item' => $i++,
                    'cantidad' => $item->detalle->cantidad,
                    'monto' => $item->detalle->monto,
                    'descripcion' => trim($item->detalle->descripcion),
                    'items_kit' => $item->detalle->items_kit,
                    'descuento' => 0,
                    'afectacion' => 10,
                    'porcentaje_descuento' => 0,
                    'subtotal' => $subtotal_item,
                    'igv' => $igv_item,
                    'total' => $total_item,
                    'num_serie' => $item->detalle->serie,
                ]);

                MainHelper::actualizar_inventario(
                    $venta->idventa,
                    $item,
                    $item->inventario()->first() ?: new Inventario(['saldo' => 0]),
                    'venta'
                );
            }

            $this->registrarPagosComercial($venta, $request, $suma_detalle);

            DB::commit();

            if ($request->idpedido) {
                $pedido->idventa = $venta->idventa;
                $pedido->estado = 'ATENDIDO';
                $pedido->save();
            }

            if ($request->comprobante != 30) {
                (new CpeController())->generarArchivos($venta->idventa);
            }

            $doc = $serie . '-' . $correlativo;
            $file = ($request->comprobante == 30)
                ? $venta->idventa
                : (new Emisor())->ruc . '-' . $request->comprobante . '-' . $doc;

            return response()->json([
                'idventa' => $venta->idventa,
                'respuesta' => 'Se ha generado el documento: ' . $doc,
                'file' => $file
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json(['error' => 'Error interno'], 500);
        }
    }

    private function registrarPagosComercial($venta, Request $request, $total)
    {
        if ($request->tipo_pago_contado == 4) {
            $fraccionado = json_decode($request->pago_fraccionado, true);
            foreach ($fraccionado as $item) {
                if ($item['monto'] > 0) {
                    $pago = new Pago();
                    $pago->monto = $item['monto'];
                    $pago->tipo = $item['tipo'];
                    $pago->fecha = date('Y-m-d H:i:s');
                    $venta->pago()->save($pago);
                }
            }
        } elseif ($request->tipo_pago_contado == 2) {
            $cuotas = json_decode($request->cuotas, true);
            if (!$cuotas) {
                $pago = new Pago();
                $pago->monto = $total;
                $pago->tipo = 2;
                $pago->fecha = date('Y-m-d H:i:s', strtotime(date('Y-m-d') . ' + 30 days'));
                $venta->pago()->save($pago);
            } else {
                foreach ($cuotas as $cuota) {
                    $pago = new Pago();
                    $pago->monto = $cuota['monto'];
                    $pago->tipo = 2;
                    if (isset($cuota['estado'])) $pago->estado = $cuota['estado'];
                    if (isset($cuota['detalle'])) $pago->detalle = $cuota['detalle'];
                    $pago->fecha = $cuota['fecha'];
                    $venta->pago()->save($pago);
                }
            }
        } else {
            $pago = new Pago();
            $pago->monto = $total;
            $pago->tipo = $request->tipo_pago_contado;
            $pago->referencia = $request->num_operacion;
            $pago->fecha = date('Y-m-d H:i:s');
            $venta->pago()->save($pago);
        }
    }

    public function obtener_extradata($pedido)
    {
        try {
            $idcliente = null;
            $datos_entrega = json_decode($pedido->datos_entrega, TRUE);
            if (!(isset($datos_entrega['idcontacto']) && $datos_entrega['idcontacto'])) {
                $contacto = trim($datos_entrega['contacto']);
                if ($contacto != '' && $contacto != '-') {
                    $request = new Request();
                    $request->nombre = $datos_entrega['contacto'];
                    $request->num_documento = -1;
                    $request->tipo_documento = 9;
                    $cliente = new ClienteController();
                    $response = $cliente->store($request);
                    $response = json_decode($response->getContent(), true);

                    $idcliente = $response['idcliente'];

                }
            } else {
                $idcliente = $datos_entrega['idcontacto'];
            }

            return [
                'idcliente' => $idcliente,
                'nota' => $datos_entrega['direccion'],
                'observacion' => $datos_entrega['referencia'],
            ];

        } catch (\Exception $e) {
            Log::error($e);
            return null;
        }

    }

    public function facturacion_desde_ticket_alt(Request $request)
    {
        //guardamos el cliente si es nuevo
        $cliente = json_decode($request->cliente, true);
        if ($cliente['esNuevo']) {
            $req = new Request();
            $req->num_documento = $cliente['ruc'];
            $req->nombre = $cliente['nombre_o_razon_social'];
            $req->direccion = $cliente['direccion'];
            $req->tipo_documento = $cliente['tipo_doc'];
            $nuevo_cliente = new ClienteController();
            $idcliente = $nuevo_cliente->store_alt($req);
        } else {
            $idcliente = $cliente['idcliente'];
        }

        $request['idcliente'] = $idcliente;

        return $this->facturacion_desde_ticket($request);
    }

    public function facturacion_desde_ticket(Request $request)
    {

        switch ($request->comprobante) {
            case 01:
                $serie = $this->serie_comprobante->serie_factura;
                break;
            default:
                $serie = $this->serie_comprobante->serie_boleta;
        }

        //obtener correlativo
        $serie_venta = DB::table('facturacion')
            ->select('correlativo')
            ->where('serie', $serie)
            ->orderby('correlativo', 'desc')
            ->first();
        if ($serie_venta) {
            $correlativo = str_pad($serie_venta->correlativo + 1, 8, '0', STR_PAD_LEFT);
        } else {
            $correlativo = '00000001';
        }

        $venta = Venta::find($request->idventa);

        //verificar montos de item = 0 y venta = 0
        if ($venta->total_venta == 0) {
            return json_encode(['idventa' => -1, 'respuesta' => 'No se puede generar comprobantes con TOTAL VENTA = 0.00', 'file' => null]);
        }

        foreach ($venta->productos as $item) {
            $total_item = round($item->detalle->monto * $item->detalle->cantidad, 2);
            if ($total_item == 0) {
                return json_encode(['idventa' => -1, 'respuesta' => 'La nota de venta no se puede convertir a boleta o factura debido a que contiene items con monto igual a 0.00', 'file' => null]);
            }
        }

        $total_venta = $venta->total_venta; // Guardar total venta para usarlo al actualizar tipo de pago
        $venta->idcliente = $request->idcliente ? $request->idcliente : -1;
        $venta->fecha = date('Y-m-d H:i:s');
        $venta->fecha_vencimiento = date('Y-m-d H:i:s');
        $venta->tipo_pago = $request->tipo_pago_contado;
        $venta->save();

        $venta->facturacion()->update([
            'serie' => $serie,
            'correlativo' => $correlativo,
            'codigo_tipo_documento' => $request->comprobante,
            'estado' => 'PENDIENTE'
        ]);
        $idventa = $venta->idventa;

        //Actualizar tipo de pago
        DB::table('pagos')->where('idventa', $request->idventa)->delete();

        if ($request->tipo_pago_contado == 4) {
            $fraccionado = json_decode($request->pago_fraccionado, TRUE);
            foreach ($fraccionado as $item) {
                $pago = new Pago();
                $pago->monto = $item['monto'];
                $pago->tipo = $item['tipo'];
                $pago->fecha = date('Y-m-d H:i:s');
                $venta->pago()->save($pago);
            }
        } else {
            $pago = new Pago();
            $pago->monto = $total_venta;
            $pago->tipo = $request->tipo_pago_contado;
            $pago->fecha = date('Y-m-d H:i:s');
            $venta->pago()->save($pago);
        }

        $cpe = new CpeController();
        $cpe->generarArchivos($idventa);

        //Enviar respuesta a usuario
        $doc = $serie . '-' . $correlativo;
        $emisor = new Emisor();
        $file = $emisor->ruc . '-' . $request->comprobante . '-' . $doc;
        $respuesta_sunat = 'Se ha generado el documento: ' . $doc;

        return json_encode(['idventa' => $idventa, 'respuesta' => $respuesta_sunat, 'file' => $file]);
    }

    public function update_tipo_pago(Request $request)
    {
        try {
            $venta = Venta::find($request->idventa);
            $total = $venta->total_venta;
            $venta->tipo_pago = $request->tipo_pago_contado;
            $venta->save();

            DB::table('pagos')->where('idventa', $request->idventa)->delete();

            if ($request->tipo_pago_contado == 4) {
                $fraccionado = json_decode($request->pago_fraccionado, TRUE);
                foreach ($fraccionado as $item) {
                    $pago = new Pago();
                    $pago->monto = $item['monto'];
                    $pago->tipo = $item['tipo'];
                    $pago->fecha = date('Y-m-d H:i:s');
                    $venta->pago()->save($pago);
                }
            } else {
                $pago = new Pago();
                $pago->monto = $total;
                $pago->tipo = $request->tipo_pago_contado;
                $pago->fecha = date('Y-m-d H:i:s');
                $venta->pago()->save($pago);
            }

            return 'Se actualizó el tipo de pago';

        } catch (\Exception $e) {
            return $e;
        }

    }

    public function anulacion_rapida(Request $request)
    {

        try {
            $venta = Venta::find($request->idventa);

            //verificar si ya se ha enviado a sunat el comprobante a anular
            if ($venta->facturacion->estado == 'PENDIENTE') {
                return json_encode([
                    'success' => false,
                    'mensaje' => 'El comprobante que intentas anular aún está PENDIENTE de envío a SUNAT. Inténtalo en unos minutos.',
                    'idventa' => null
                ]);
            }

            $duplicado = $venta->replicate();
            $serie = $this->serie_comprobante->serie_nota_credito_boleta;
            $duplicado->fecha = date('Y-m-d H:i:s');
            $duplicado->fecha_vencimiento = date('Y-m-d H:i:s');
            if ($request->comprobante == '01') {
                $serie = $this->serie_comprobante->serie_nota_credito_factura;
            }

            //obtener correlativo
            $serie_venta = DB::table('facturacion')
                ->select('correlativo')
                ->where('serie', $serie)
                ->orderby('correlativo', 'desc')
                ->first();

            if ($serie_venta) {
                $correlativo = str_pad($serie_venta->correlativo + 1, 8, '0', STR_PAD_LEFT);
            } else {
                $correlativo = '00000001';
            }

            $duplicado->save();
            $idventa = $duplicado->idventa;

            $facturacion = $venta->facturacion->replicate();
            $facturacion->idventa = $idventa;
            $facturacion->serie = $serie;
            $facturacion->correlativo = $correlativo;
            $facturacion->codigo_tipo_documento = '07';
            $facturacion->estado = 'PENDIENTE';
            $facturacion->tipo_nota_electronica = '01';
            $facturacion->descripcion_nota = $request->motivo_anulacion;
            $facturacion->num_doc_relacionado = $venta->facturacion->serie . '-' . $venta->facturacion->correlativo;
            $facturacion->tipo_doc_relacionado = $venta->facturacion->codigo_tipo_documento;
            $guardado = $facturacion->save();

            if ($guardado) {
                $detalle = Arr::pluck($venta->productos->toArray(), 'detalle');
                foreach ($detalle as $item) {
                    $item['idventa'] = $idventa;
                    DB::table('ventas_detalle')->insert($item);
                }
            } else {
                return json_encode([
                    'success' => false,
                    'mensaje' => 'No se ha procesado correctamente la anulación',
                    'idventa' => null
                ]);
            }

            $estado = Facturacion::find($request->idventa);
            $estado->estado = 'ANULADO';
            $estado->save();

            $venta = Venta::find($idventa);
            $productos = $venta->productos;

            foreach ($productos as $item) {
                $item_inv = $item->inventario()->first();
                MainHelper::actualizar_inventario($request->idventa, $item, $item_inv, 'anulacion_nc');
            }

            $cpe = new CpeController();
            $cpe->generarArchivos($idventa);
            return json_encode([
                'success' => true,
                'mensaje' => '¡Se anuló el comprobante!
Imprime y entrega la NOTA DE CRÉDITO junto al comprobante anulado.',
                'idventa' => $idventa
            ]);

        } catch (\Exception $e) {
            Log::error($e);
            return $e->getMessage();
        }
    }

    public function verificar_pedido($idpedido, $items)
    {
        try {
            $orden = Orden::find($idpedido);
            $items = json_decode($items, TRUE);
            if ($items) {
                $detalle = [];
                $i = 1;
                $suma = 0;

                DB::table('orden_detalle')->where('idorden', $idpedido)->delete();

                foreach ($items as $item) {
                    $suma += $item['precio'] * $item['cantidad'];
                    $detalle['num_item'] = $i;
                    $detalle['cantidad'] = $item['cantidad'];
                    $detalle['monto'] = $item['precio'];
                    $detalle['descuento'] = 0;
                    $detalle['descripcion'] = mb_strtoupper($item['presentacion']);
                    $detalle['items_kit'] = json_encode($item['items_kit']);
                    $detalle['serie'] = $item['serie'] ?? null;
                    $detalle['fecha'] = date('Y-m-d H:i:s');
                    $detalle['usuario'] = auth()->user()->persona->idpersona;
                    $detalle['idproducto'] = $item['idproducto'];
                    $detalle['idorden'] = $idpedido;
                    DB::table('orden_detalle')->insert($detalle);
                    $i++;
                }

                $orden->total = round($suma, 2);
                $orden->save();
            } else {
                Log::info('idpedido: ' . $idpedido . ' - No existen items...');
            }

        } catch (\Exception $e) {
            Log::error($e);
            return $e->getMessage();
        }
    }

    public function actualizarOC(Request $request)
    {
        $venta = Venta::findOrFail($request->idventa);

        // Actualizar OC en la tabla facturacion
        $venta->facturacion->oc_relacionada = $request->oc_relacionada;
        $venta->facturacion->save();

        // Buscar si hay una guía relacionada
        $guia = Guia::where('idventa', $venta->idventa)->first();

        if ($guia) {
            $datos = json_decode($guia->guia_datos_adicionales, true);

            // Solo actualiza si el campo 'oc' existe
            if (isset($datos['oc'])) {
                $datos['oc'] = $request->oc_relacionada;
                $guia->guia_datos_adicionales = json_encode($datos);
                $guia->save();
            }
        }

        return response()->json(['success' => true]);
    }


}
