<?php

namespace sysfact\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spipu\Html2Pdf\Html2Pdf;
use sysfact\AppConfig;
use sysfact\Emisor;
use sysfact\Http\Controllers\Helpers\MainHelper;
use sysfact\Presupuesto;
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
        unlink('images/'.$config->valor);
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
}
