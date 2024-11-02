<?php

namespace sysfact\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spipu\Html2Pdf\Html2Pdf;
use sysfact\AppConfig;
use sysfact\Cliente;
use sysfact\Emisor;
use sysfact\Http\Controllers\Helpers\MainHelper;
use sysfact\Persona;
use sysfact\Presupuesto;
use sysfact\Producto;
use sysfact\Trabajador;
use sysfact\Venta;

class ConfiguracionController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {

        $old_parent = null;
        $permisos_array = null;
        $roles = Role::all();
        $permisos = Permission::orderby('name')->get();

        foreach ($permisos as $permiso){

            $valor=explode(':',$permiso['name']);
            $parent = count($valor)>1?$valor[0]:null;
            $permiso->text = $parent==null?$valor[0]:$valor[1];
            $permiso->value = $permiso['name'];
            $permiso->parent = $parent;

        }

        $config = AppConfig::all();
        $configuracion = new Collection();

        foreach ($config as $c){
            $configuracion->{$c->clave}=$c->valor;
        }

        $templates = [];
        $files=scandir(resource_path('views/presupuesto/imprimir/'));
        natsort($files);
        foreach ($files as $file){
            if (strpos($file, 'plantilla_') !== false) {
                $readfile=file(resource_path('views/presupuesto/imprimir/').$file);
                $plantilla_titulo=$readfile[0];

                if(strpos($plantilla_titulo, '**') !== false){
                    $title=explode('**',$plantilla_titulo)[1];
                    $name=explode('.',$file);
                    $vars = explode('-', $title);
                    if(count($vars)>1){
                        $templates[]=['text'=>trim($vars[0]),'value'=>$name[0],'disabled'=>trim($vars[1])=='habilitado'?'':'disabled'];
                    }
                }

            }
        }

        $tenantPermision = json_decode(cache('config')['emisor'], true)['superuser']??false;

        return view('configuracion.index', [
            'permissions' => json_encode($permisos),
            'roles' => $roles,
            'permisos' => $permisos,
            'configuracion'=>$configuracion,
            'templates'=>$templates,
            'usuario' => auth()->user()->persona,
            'tenantPermision' => $tenantPermision
        ]);
    }

    public function cacheSetting(){
        MainHelper::updateconfiguracion();
        dd(MainHelper::configuracion());
    }

    public function permisos($idrol){

        $permisos = Permission::select('id','name')
            ->orderby('name')
            ->get();

        $permisos_de_rol = Role::findById($idrol)
            ->permissions()
            ->select('id','name')
            ->orderby('name')
            ->get();

        $rol = Role::findById($idrol);
        $permiso_parent=null;

        foreach ($permisos as $permiso) {

            $permiso->isSelected='';
            foreach ($permisos_de_rol as $per_rol) {
                if ($per_rol->id === $permiso->id) {
                    $permiso->isSelected = $per_rol->name;
                    break;
                }
            }

            $pos = strpos($permiso['name'],':');

            if($pos === false){
                if($permiso->isSelected==''){
                    $permiso->isSelected = false;
                } else{
                    $permiso->isSelected = true;
                }
                $permiso_parent[]= $permiso;

            } else{
                $e=explode(':',$permiso['name']);
                $permiso->parent=$e[0];
                $permiso->child_name=$e[1];
            }


        }

        return view('configuracion.permisos',[
            'permisos'=>json_encode($permisos),
            'permisos_seleccionados'=>json_encode($permisos_de_rol),
            'rol'=>$rol,
            'permisos_parent'=>json_encode($permiso_parent),
            'usuario' => auth()->user()->persona]);
    }

    public function asignar_privilegios(Request $request)
    {
        $array_p=[];


        foreach ($request->privilegios as $privilegio){

            if($privilegio['isSelected']){
                $p['id']=$privilegio['id'];
                $p['name']=$privilegio['name'];
                $array_p[]=$p;
            }

        }
        $role = Role::findById($request->idrol);
        $role->syncPermissions([$array_p]);
    }

    public function crear_rol(Request $request)
    {
        $role = Role::create(['name' => $request->rol]);
    }

    public function eliminar_rol($idrol)
    {
        $rol = Role::findById($idrol);
        $rol->delete();
        $artisan = Artisan::call('permission:cache-reset');
        return back();
    }

    public function crear_permiso(Request $request)
    {
        $permission = Permission::create(['name' => $request->permiso]);
    }

    public function editar_privilegios(Request $request)
    {
        $permisos_de_rol = Role::findById($request->idrol)->permissions()->orderby('name')->get();
        $privilegios = [];
        foreach ($permisos_de_rol as $permiso) {
            $privilegios[] = $permiso['name'];
        }

        return json_encode($privilegios);

    }

    public function borrar_permiso(Request $request){
        $permiso = Permission::findById($request->idpermiso);
        $permiso->delete();
    }

    public function actualizar_permiso(Request $request){
        $permiso = Permission::findById($request->idpermiso);
        $permiso->name=$request->nombre;
        $permiso->save();
    }

    public function getAppConfig($clave){
        $appConfig = AppConfig::find($clave);

        if(!$appConfig){
            $appConfig = new AppConfig();
            $appConfig->clave = $clave;
        }

        return $appConfig;
    }

    public function guardarConfiguracion(Request $request){

        try{

            $data = json_decode($request->valor,true);
            $appConfig = $this->getAppConfig($request->clave);

            $json = [];

            foreach ($data as $key=>$d){
                $json[$key] = is_string($d)?trim($d):$d;
            }

            $appConfig->valor = json_encode($json);

            if($appConfig->save()){
                MainHelper::updateconfiguracion();
                return "Datos guardados con éxito";
            }else{
                return "Ocurrió un error al guardar los datos";
            }

        } catch (\Exception $e){
            return response(['mensaje'=>$e->getMessage()],500);
        }
    }

    public function agregarImagen(Request $request){
        try{
            if($request->hasFile('imagen')){
                if ($request->file('imagen')->isValid()) {

                    $validated = $request->validate([
                        'name' => 'string|max:100',
                        'image' => 'mimes:jpeg,png|max:1014',
                    ]);

                    $emisor = new Emisor();
                    $image=$request->file('imagen');
                    $filename   = $emisor->ruc.'-'.time() . '-logo.' . $image->getClientOriginalExtension();

                    // Verificar las dimensiones de la imagen
                    list($width, $height) = getimagesize($image);
                    if ($width > 500 || $height > 500) {
                        $img = Image::make($image->getRealPath());
                        $img->resize(500, 500, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                        $img->save(public_path('images/' . $filename));
                    } else {
                        // Si la imagen es lo suficientemente pequeña, guardarla sin cambios
                        $image->move(public_path('images/'), $filename);
                    }

                    $config = AppConfig::find($request->tipo_logo);
                    $config->valor=$filename;
                    if($config->save()){
                        MainHelper::updateconfiguracion();
                    }
                    return 1;
                }
                return 'La imagen no es válida';
            } else{
                return 'La imagen es demasido grande, tamaño máximo 1MB';
            }
        } catch (\Exception $e){
            return $e;
        }
    }

    public function borrarImagen(Request $request){
        $config = AppConfig::find($request->tipo_logo);
        if(file_exists('images/'.$config->valor)){
            unlink('images/'.$config->valor);
        }
        $config->valor='';
        if($config->save()){
            MainHelper::updateconfiguracion();
        }
    }

    public function mostrar_plantilla($plantilla){
        $venta = new Venta();
        $emisor=new Emisor();
        $cliente=new Collection();
        $persona = new Collection();
        $persona->direccion = 'CA. BOLOGNESI, NRO 1452, URB LOS CEDROS, LIMA, LIMA, LIMA';
        $cliente->persona = $persona;
        $cliente->razon_social = 'EMPRESA XYZ S.A.C.';
        $cliente->num_documento = '20520679400';

        $producto = new Collection();
        $detalle = new Collection();
        $detalle->subtotal = '100.00';
        $detalle->monto = '11.80';
        $detalle->total = '118.00';
        $detalle->cantidad = '10';
        $detalle->porcentaje_descuento = 0;
        $detalle->tipo_descuento = 0;
        $detalle->descuento = 0;
        $detalle->descripcion = '';

        $caja = new Collection();
        $caja->nombre = 'MARIELA';

        $empleado = new Collection();
        $empleado->idpersona = 3;
        $empleado->nombre = 'JUAN CARLO';

        $pago = new Collection();
        $pago->monto = '114.46';
        $pago->fecha = date('m/d/Y',strtotime(date('Y-m-d'). ' + 15 days'));

        $producto->num_item = 1;
        $producto->codigo = 'ASD8727';
        $producto->nombre = 'CONTROLADOR DE TEMPERATURA';
        $producto->descripcion = 'ENTRADA DE SENSOR UNIVERSAL';
        $producto->cantidad = 5;
        $producto->detalle = $detalle;
        $producto->unidad_medida = 'UND';
        $producto->precio = '20.00';
        $producto->total = '100.00';

        $facturacion = new Collection();
        $facturacion->oc_relacionada='001-4561232';
        $facturacion->guia_relacionada='T001-0000032';
        $facturacion->guia_fisica='';
        $facturacion->descuento_global = '0.00';
        $facturacion->codigo_tipo_factura = '0101';
        $facturacion->igv = '18.00';
        $facturacion->retencion = 1;
        $facturacion->total_gratuitas = 0;
        $facturacion->serie = 'F001';
        $facturacion->correlativo = '00010307';
        $facturacion->total_inafectas = 0;
        $facturacion->total_exoneradas = 0;
        $facturacion->total_descuentos = 0;
        $facturacion->total_gravadas = '100.00';

        $venta->titulo_doc = 'Factura';
        $venta->serie = 'F001';
        $venta->correlativo = '00010307';
        $venta->fecha = date('m/d/Y');
        $venta->leyenda = 'CIENTO DIECIOCHO CON 00/100 SOLES';
        $venta->hash = 'GHkTcQPmmPSMtlKHfTa+STbXmmM=';
        $venta->qr = 'PLANTILLA.png';
        $venta->total_venta = '118.00';
        $venta->codigo_moneda = 'S/';
        $venta->tipo_pago = 1;
        $venta->retencion = 3.54;
        $venta->monto_menos_retencion = 114.46;
        $venta->facturacion = $facturacion;
        $venta->pago = [$pago];
        $venta->caja = $caja;
        $venta->empleado = $empleado;
        $venta->color = json_decode(cache('config')['impresion'], true)['color']??false;

        $datos = [
            'documento'=>$venta,
            'emisor'=>$emisor,
            'usuario'=>$cliente,
            'items'=>[$producto]
        ];


        switch ($plantilla){
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

        $view = view('sunat/plantillas-pdf/'.$plantilla.'/factura', $datos);
        $html = $view->render();
        $pdf = new Html2Pdf('P', $formato_impresion, 'es');
        $pdf->writeHTML($html);
        $pdf->output('PLANTILLA-FACTURA.pdf');

    }

    public function mostrar_plantilla_cotizacion($plantilla){
        $presupuesto = new Presupuesto();
        $presupuesto->atencion = 'JUAN GUTIERREZ';
        $presupuesto->presupuesto = 100;
        $presupuesto->moneda = 'USD';
        $presupuesto->correlativo = '001-00057';
        $presupuesto->fecha = date('Y-m-d');
        $presupuesto->validez = '15';
        $presupuesto->condicion_pago = 'CRÉDITO 30 DÍAS';
        $presupuesto->tiempo_entrega = '03 DÍAS';
        $presupuesto->garantia = '01 año';
        $presupuesto->impuesto = 'incluye IGV';
        $presupuesto->lugar_entrega = 'En almacén';
        $presupuesto->contacto = 'CARLOS TORRES';
        $presupuesto->telefonos = '01 87934555';
        $presupuesto->color = json_decode(cache('config')['cotizacion'], true)['color']??false;

        $emisor=new Emisor();
        $cliente=new Collection();
        $persona = new Collection();
        $persona->nombre = 'EMPRESA XYZ S.A.C.';
        $persona->direccion = 'CA. BOLOGNESI, NRO 1452, URB LOS CEDROS, LIMA, LIMA, LIMA';
        $cliente->persona = $persona;
        $cliente->num_documento = '20520679400';
        $cliente->telefono = '996861131';
        $cliente->correo = 'ventas@miempresa.com';
        $cliente->idcliente = 20;

        $producto = new Collection();
        $detalle['descripcion'] = 'ENTRADA DE SENSOR UNIVERSAL';
        $detalle['cantidad'] = 5;
        $detalle['monto'] = 20;
        $detalle['descuento'] = 0;

        $producto->cod_producto = 'ASD8727';
        $producto->nombre = 'CONTROLADOR DE TEMPERATURA';
        $producto->detalle = $detalle;
        $producto->unidad_medida = 'NIU/UND';
        $producto->total = 100;
        $producto->monto = 100;
        $producto->monto_descuento = 0;
        $producto->imagen = '';

        $presupuesto->productos = [$producto];
        $config = MainHelper::configuracion('mail_contact');

        $view = view('presupuesto/imprimir/'.$plantilla, [
            'emisor'=>$emisor,
            'usuario'=>$cliente,
            'config'=>json_decode($config, true),
            'presupuesto'=>$presupuesto
        ]);
        $html = $view->render();
        $pdf = new Html2Pdf('P', 'A4', 'es');
        $pdf->writeHTML($html);
        $pdf->output('Ejemplo-plantilla.pdf');

    }

    public function cerrarSesiones(Request $request){
        $request->session()->flush();
        $empleados=Trabajador::all();

        foreach ($empleados as $empleado){
            $emp = Trabajador::find($empleado->idempleado);
            $emp->remember_token = NULL;
            $emp->save();
        }

        return redirect('/');
    }

    public function reiniciar_vistas(Request $request) {
        $artisan = Artisan::call("view:clear");
        $output = Artisan::output();
        return redirect('/configuracion?tab=sistema');
    }

    public function guardar_mensaje_tenant(Request $request){
        Cache::forever('mensaje_tenant',$request->mensaje);
    }

    public function guardar_cuentas(Request $request){
        $config = AppConfig::find('cuentas');
        if(!$config){
            $config = new AppConfig();
            $config->clave = 'cuentas';
        }
        $config->valor = $request->cuentas;
        $config->save();
        if($config->save()){
            MainHelper::updateconfiguracion();
            return "Datos guardados con éxito";
        }else{
            return "Ocurrió un error al guardar los datos";
        }
    }

    public function obtener_cuentas(Request $request){
        $config = AppConfig::find('cuentas');
        return $config->valor;

    }

    public function procesarXmlPorCorrelativo($serie, $correlativoInicio, $correlativoFin)
    {
        $serie = strtoupper($serie);

        DB::table('facturacion')
            ->join('ventas', 'facturacion.idventa', '=', 'ventas.idventa')
            ->where('ventas.observacion', 'like', 'Último correlativo%')
            ->where('facturacion.serie', $serie)
            ->delete();

        // Crear una instancia del Emisor y obtener el RUC
        $emisor = new Emisor();
        $ruc = $emisor->ruc;  // Obtenemos el RUC del emisor

        // Determinar el tipo de documento basado en la serie
        if (str_starts_with($serie, 'F0')) {
            $tipo_documento = '01';  // Factura
        } elseif (str_starts_with($serie, 'B0')) {
            $tipo_documento = '03';  // Boleta
        } elseif (str_starts_with($serie, 'BC') || str_starts_with($serie, 'FC')) {
            $tipo_documento = '07';  // Nota de crédito
        } else {
            return response()->json(['error' => 'Serie no reconocida.'], 400);
        }

        // Recorrer el rango de correlativos
        $contadorMinutos = 0;
        for ($correlativo = $correlativoInicio; $correlativo <= $correlativoFin; $correlativo++) {
            // Asegurar que el correlativo tenga 8 dígitos, llenando con ceros a la izquierda
            $correlativoFormateado = str_pad($correlativo, 8, '0', STR_PAD_LEFT);

            // Verificar si la serie y correlativo ya existen en la tabla facturacion
            $facturacionExistente = DB::table('facturacion')
                ->where('serie', $serie)
                ->where('correlativo', $correlativoFormateado)
                ->exists();

            if ($facturacionExistente) {
                // Si ya existe la combinación de serie y correlativo, saltar este registro
                echo "Registro con serie $serie y correlativo $correlativoFormateado ya existe. Saltando.\n";
                continue;
            }

            // Construir el nombre del archivo dinámicamente
            $nombreArchivo = "$ruc-$tipo_documento-$serie-$correlativoFormateado.xml";
            $filePath = storage_path("app/sunat/xml/$nombreArchivo");

            // Verificar si el archivo existe
            if (!file_exists($filePath)) {
                continue; // Si el archivo no existe, saltar al siguiente correlativo
            }

            // Cargar el archivo XML
            $xmlContent = file_get_contents($filePath);
            $xml = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA);
            $namespaces = $xml->getNamespaces(true);
            $xml->registerXPathNamespace('cbc', $namespaces['cbc']);
            $xml->registerXPathNamespace('cac', $namespaces['cac']);

            // Extraer la fecha del XML <cbc:IssueDate>
            $fechaEmisionArray = $xml->xpath('//cbc:IssueDate');
            $fechaEmision = isset($fechaEmisionArray[0]) ? (string) $fechaEmisionArray[0] : null;

            if (!$fechaEmision) {
                continue; // Si no hay fecha, saltar este archivo
            }

            // Ajustar la fecha de emisión para que sea a las 23:00:00
            $fechaEmisionConHora = Carbon::createFromFormat('Y-m-d H:i:s', $fechaEmision . ' 20:00:00')
                ->addMinutes($contadorMinutos); // Añadir el número de minutos basado en el contador

            // Incrementar el contador de minutos para el próximo registro
            $contadorMinutos++;

            // Extraer el RUC o DNI del cliente
            $clienteIDArray = $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID');
            $clienteNombreArray = $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName');

            if (!$clienteIDArray || !$clienteNombreArray || !isset($clienteIDArray[0]) || !isset($clienteNombreArray[0])) {
                continue; // Si no se encuentran los datos del cliente, saltar este archivo
            }

            $rucDni = (string) $clienteIDArray[0];
            $nombreCliente = (string) $clienteNombreArray[0];

            // Determinar si es RUC (11 dígitos) o DNI (8 dígitos)
            $tipoDocumento = strlen($rucDni) == 11 ? '6' : (strlen($rucDni) == 8 ? '1' : null);
            if (!$tipoDocumento) {
                continue; // Si el tipo de documento no es válido, saltar el archivo
            }

            // Verificar si el cliente ya existe en la base de datos
            $clienteExistente = Cliente::where('num_documento', $rucDni)
                ->where('eliminado', 0)
                ->first();

            if (!$clienteExistente) {
                // Si el cliente no existe, crearlo
                $persona = new Persona();
                $persona->nombre = mb_strtoupper($nombreCliente);
                $persona->direccion = ''; // Puedes modificarlo si tienes la dirección en el XML
                $persona->telefono = '';  // Si tienes el teléfono en el XML
                $persona->correo = '';    // Si tienes el correo en el XML
                $persona->save();

                $cliente = new Cliente();
                $codigoCliente = '';  // Generar el código del cliente
                $cliente->cod_cliente = $codigoCliente;
                $cliente->num_documento = $rucDni;
                $cliente->tipo_documento = $tipoDocumento;  // 6 = RUC, 1 = DNI
                $cliente->eliminado = 0;
                $persona->cliente()->save($cliente);

                $clienteExistente = Cliente::where('num_documento', $rucDni)
                    ->where('eliminado', 0)
                    ->first();

                // Verificar que el cliente se asignó correctamente
                if ($clienteExistente) {
                    Log::info($clienteExistente->idcliente); // Comprobación del ID del cliente
                } else {
                    Log::error("El cliente no pudo ser recuperado correctamente.");
                }
            }

            // Extraer datos del XML para la venta
            $payableAmountArray = $xml->xpath('//cac:LegalMonetaryTotal/cbc:PayableAmount');
            $totalVenta = isset($payableAmountArray[0]) ? (float) $payableAmountArray[0] : 0;

            // Iniciar la transacción para insertar los datos en la tabla ventas y facturacion
            DB::beginTransaction();

            try {
                // Insertar en la tabla ventas
                $idVenta = DB::table('ventas')->insertGetId([
                    'idempleado' => -1,
                    'idcliente' => $clienteExistente->idcliente, // ID del cliente creado o encontrado
                    'idcajero' => -1,
                    'idcaja' => '-1',
                    'fecha' => $fechaEmisionConHora, // Fecha extraída del XML
                    'fecha_vencimiento' => $fechaEmisionConHora, // Usamos la misma fecha de emisión como vencimiento
                    'total_venta' => $totalVenta, // Total de venta extraído del XML
                    'tipo_pago' => '1', // Según tu requerimiento
                    'observacion' => '',
                    'igv_incluido' => 1,
                    'eliminado' => '0',
                    'tipo_cambio' => '0.00',
                ]);

                // Insertar en la tabla facturacion
                $porcentaje_igv = json_decode(cache('config')['interfaz'], true)['porcentaje_igv']??18;
                $_igv = ($porcentaje_igv/100+1);
                $subtotalVenta = $totalVenta/$_igv;
                DB::table('facturacion')->insert([
                    'idventa' => $idVenta,
                    'codigo_tipo_documento' => $tipo_documento,
                    'codigo_tipo_factura' => '0101', // Determinado a partir de la serie
                    'serie' => $serie, // Serie pasada como parámetro
                    'correlativo' => $correlativoFormateado, // Correlativo formateado a 8 dígitos
                    'codigo_moneda' => 'PEN', // Asumimos PEN, ajusta si es necesario
                    'estado' => 'ACEPTADO',  // Estado
                    'total_gravadas' => round($subtotalVenta, 2),
                    'igv' => round($totalVenta - $subtotalVenta, 2)
                ]);

                DB::table('pagos')->insert([
                    'idventa' => $idVenta,
                    'tipo' => 1,
                    'monto' => $totalVenta,
                    'fecha' => $fechaEmisionConHora,
                    'estado' => 1
                ]);

            // Insertar los detalles de la venta
            $items = ($tipo_documento === '07') ? $xml->xpath('//cac:CreditNoteLine') : $xml->xpath('//cac:InvoiceLine');
            $i = 1;
            foreach ($items as $item) {
                // Extraer la descripción del producto
                $descripcionProductoArray = $item->xpath('cac:Item/cbc:Description');
                $descripcionProducto = isset($descripcionProductoArray[0]) ? (string)$descripcionProductoArray[0] : '';

                // Buscar el producto en la base de datos
                $productoExistente = Producto::where('nombre', mb_strtoupper($descripcionProducto))->first();

                if (!$productoExistente) {
                    // Si no existe, crearlo
                    $producto = new Producto();
                    $producto->nombre = mb_strtoupper($descripcionProducto);
                    $producto->precio = 0;
                    $producto->costo = 0;
                    $producto->eliminado = 0;
                    $producto->stock_bajo = 0;
                    $producto->idcategoria = -1; // ID categoría predeterminada
                    $producto->tipo_producto = 2;
                    $producto->unidad_medida = 'NIU/UND';
                    $producto->save();

                    $productoExistente = $producto;
                }

                // Extraer los datos del detalle del XML
                $cantidadArray = $item->xpath('cbc:InvoicedQuantity | cbc:CreditedQuantity');
                $cantidad = isset($cantidadArray[0]) ? (float)$cantidadArray[0] : 1;

                $precioArray = $item->xpath('cac:Price/cbc:PriceAmount');
                $precio = isset($precioArray[0]) ? (float)$precioArray[0] : 0;

                $subtotalArray = $item->xpath('cac:TaxTotal/cac:TaxSubtotal/cbc:TaxableAmount');
                $subtotal = isset($subtotalArray[0]) ? (float)$subtotalArray[0] : 0;

                $igvArray = $item->xpath('cac:TaxTotal/cac:TaxSubtotal/cbc:TaxAmount');
                $igv = isset($igvArray[0]) ? (float)$igvArray[0] : 0;

                $total = $subtotal + $igv;

                // Insertar el detalle del producto en la tabla pivot
                DB::table('ventas_detalle')->insert([
                    'idventa' => $idVenta, // ID de la venta
                    'idproducto' => $productoExistente->idproducto, // ID del producto
                    'num_item' => $i,
                    'cantidad' => $cantidad,
                    'monto' => $precio,
                    'descuento' => 0,
                    'descripcion' => mb_strtoupper($descripcionProducto),
                    'afectacion' => 10, // Código de afectación de IGV
                    'porcentaje_descuento' => 0,
                    'subtotal' => $subtotal,
                    'igv' => $igv,
                    'total' => $total
                ]);


                $i++;
            }

            DB::commit();

                echo "Datos insertados correctamente para el archivo: $nombreArchivo.\n";
            } catch (\Exception $e) {
                DB::rollback();
                Log::error($e);
                echo "Error al procesar el archivo $nombreArchivo: " . $e->getMessage() . "\n";
            }
        }

        return response()->json(['message' => 'Proceso completado']);
    }


}
