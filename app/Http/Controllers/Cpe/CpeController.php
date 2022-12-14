<?php
/**
 * Created by PhpStorm.
 * User: Luciano
 * Date: 22/03/2020
 * Time: 15:44
 */

namespace sysfact\Http\Controllers\Cpe;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use sysfact\Emisor;
use sysfact\Guia;
use sysfact\Http\Controllers\Controller;
use sysfact\Http\Controllers\Helpers\MainHelper;
use sysfact\Http\Controllers\Helpers\PdfHelper;
use sysfact\Opciones;
use sysfact\Venta;

class CpeController extends Controller
{
    private $config;

    public function __construct() {
        $this->middleware('auth');
        $this->config = $this->getConexionData();
    }

    public function getConexionData(){
        $config = MainHelper::configuracion('conexion');
        return json_decode($config,true);
    }

    public function generarArchivos($idventa){
        //Generar archivos
        $util=new Util($this->config['esProduccion']);
        $xml=$util->generarXml($idventa);
        $util->generarZip($xml);
    }

    //Facturas, boletas, nota de crédito y nota de débito
    public function sendBill($idventa, $doc_relacionado=null){

        //Generar archivos
        $util=new Util($this->config['esProduccion']);
        $xml=$util->generarXml($idventa);
        $zip=$util->generarZip($xml);
        $codigo_documento = $util->getTipoDocumento();

        //Enviar a sunat
        $see=new ConexionSunat($this->config);
        $conexion=$see->obtenerConexion($codigo_documento);
        $sunat=new EnvioSunat($conexion);
        $respuesta=$sunat->enviar($zip);

        $resp=new ProcesarRespuestas($idventa);

        if($doc_relacionado){
            //Si es una nota de crédito o débito
            return $resp->mensajeNotas($respuesta,$zip['nombre'],$doc_relacionado);
        }

        return $resp->mensaje($respuesta,$zip['nombre']);

    }

    //Guia de remisión
    public function sendGuia($idguia){

        return $this->enviarGuiaApi($idguia);

        /*//Generar archivos
        $util=new Util($this->config['esProduccion']);
        $xml=$util->generarXmlGuia($idguia);
        $zip=$util->generarZip($xml);

        //Enviar a sunat
        $see=new ConexionSunat($this->config);
        $conexion=$see->obtenerConexion('09');
        $sunat=new EnvioSunat($conexion);
        $respuesta=$sunat->enviar($zip);

        //Procesar respuesta de sunat
        $resp=new ProcesarRespuestas($idguia);
        return $resp->mensaje($respuesta,$zip['nombre']);*/
    }

    //Resumen diario de boletas
    public function sendSummary($fecha){

        //Generar archivos
        $util=new Util($this->config['esProduccion']);
        $xml=$util->generarXmlResumen($fecha);
        $zip=$util->generarZip($xml);

        //Enviar a sunat
        $see=new ConexionSunat($this->config);
        $conexion=$see->obtenerConexion('sendSummary');
        $sunat=new EnvioSunat($conexion);
        $respuesta=$sunat->enviar($zip);

        //Procesar respuesta de sunat
        $resp=new ProcesarRespuestas();
        return $resp->mensajeSummary($respuesta,$util->getDocumento());

    }

    //Comunicacion de baja de boletas y notas vinculadas a través de resumen diario
    public function sendSummaryVoided(Request $request){

        //Generar archivos
        $util=new Util($this->config['esProduccion']);
        $items=json_decode($request->items,true);
        $xml=$util->generarXmlResumenBaja($items);
        $zip=$util->generarZip($xml);

        //Enviar a sunat
        $see=new ConexionSunat($this->config);
        $conexion=$see->obtenerConexion('sendSummary');
        $sunat=new EnvioSunat($conexion);
        $respuesta=$sunat->enviar($zip);

        //Procesar respuesta de sunat
        $resp=new ProcesarRespuestas();
        return $resp->mensajeSummary($respuesta,$util->getDocumento(),$items);

    }

    //Comunicacion de baja de facturas y notas vinculadas
    public function sendVoided(Request $request){

        //Generar archivos
        $util=new Util($this->config['esProduccion']);
        $items=json_decode($request->items,true);
        $xml=$util->generarXmlComunicacionBaja($items);
        $zip=$util->generarZip($xml);

        //Enviar a sunat
        $see=new ConexionSunat($this->config);
        $conexion=$see->obtenerConexion('sendSummary');
        $sunat=new EnvioSunat($conexion);
        $respuesta=$sunat->enviar($zip);

        //Procesar respuesta de sunat
        $resp=new ProcesarRespuestas();
        return $resp->mensajeSummary($respuesta,$util->getDocumento(),$items);

    }

    //Obtener estado de resumen enviado
    public function getStatus($ticket,$nombre){

        //Enviar a sunat
        $see=new ConexionSunat($this->config);
        $conexion=$see->obtenerConexion('status');
        $sunat=new EnvioSunat($conexion);
        $respuesta=$sunat->factGetStatus($ticket);

        //Procesar respuesta de sunat
        $resp=new ProcesarRespuestas();
        return $resp->mensajeGetStatus($respuesta,$nombre,$ticket);

    }

    //Obtener estado cdr de factura, nota de crédito o débito
    public function getStatusCdr(Request $request){

        $emisor=new Emisor();
        $nombre_archivo=$emisor->ruc.'-'.$request->tipo.'-'.$request->serie.'-'.str_pad($request->numero,8,'0',STR_PAD_LEFT);
        $request->ruc_emisor=$emisor->ruc;

        //Enviar a sunat
        $see=new ConexionSunat($this->config);
        $doc = 'getStatusCdr';
        if($request->tipo_consulta=='estado'){
            $doc = 'getStatus';
        }
        $conexion=$see->obtenerConexion($doc);
        $sunat=new EnvioSunat($conexion);
        $respuesta=$sunat->factGetStatusCdr($request);

        //Procesar respuesta de sunat
        $resp=new ProcesarRespuestas();
        return $resp->mensajeGetStatusCdr($respuesta,$request,$nombre_archivo);

    }

    public function reenviar($idventa,$comprobante,$doc_relacionado=null)
    {
        //Se obtienen los xml ya generados:
        $url_zip=storage_path()."/app/sunat/zip/".$comprobante.'.zip';
        if (!file_exists($url_zip)) {
            //Si por algún error no se ha generado el xml previamente
            return [$this->sendBill($idventa),''];
        }
        $zip=['nombre'=>$comprobante,'contenido'=>base64_encode(file_get_contents($url_zip))];
        $codigo_documento = explode('-',$comprobante)[1];

        //Enviar a sunat
        $see=new ConexionSunat($this->config);
        $conexion=$see->obtenerConexion($codigo_documento);
        $sunat=new EnvioSunat($conexion);
        $response=$sunat->enviar($zip);

        $procesar=new ProcesarRespuestas($idventa);
        $respuesta = $procesar->mensaje($response,$zip['nombre']);
        if($codigo_documento=='07' || $codigo_documento=='08'){
            $respuesta =  $procesar->mensajeNotas($response,$zip['nombre'],$doc_relacionado);
        }

        //Si falla envío, verificamos con getStatusCDR
        if(!$respuesta){
            $request = new Request();
            $explode = explode('-',$comprobante);
            $request->idventa = $idventa;
            $request->tipo_consulta='cdr';
            $request->tipo = $explode[1];
            $request->serie = $explode[2];
            $request->numero = $explode[3];
            $respuesta = $this->getStatusCdr($request);
            if(str_contains(strtolower($respuesta),'aceptado') || str_contains(strtolower($respuesta),'aceptada')){
                $venta = Venta::find($idventa);
                $venta->facturacion()->update([
                    "estado"=>'ACEPTADO'
                ]);
            }
        }

        $venta = Venta::find($idventa);
        $estado = $venta->facturacion->estado;

        return [$respuesta,$estado];
    }

    public function regenerar_y_enviar($doc,$id)
    {
        if ($doc == 'guia') {
            return $this->sendGuia($id);
        }
        return $this->sendBill($id);
    }

    public function regenerarArchivos($doc,$id){

        try{
            $util=new Util($this->config['esProduccion']);
            if($doc=='guia'){
                $xml=$util->generarXmlGuia($id);
            } else{
                $xml=$util->generarXml($id);
            }
            $zip=$util->generarZip($xml);

            return 'Se regeneraron los archivos';
        } catch (\Exception $e){
            return $e;
        }
    }

    public function descargarArchivo(Request $request, $file_or_id)
    {

        if (is_numeric($file_or_id)) {
            if ($request->guia) {
                PdfHelper::generarPdfGuia($file_or_id, false, 'D');
            } else {
                PdfHelper::generarPdf($file_or_id, false, 'D');
            }

        } else {
            $archivo = explode('.', $file_or_id);
            switch ($archivo[1]) {
                case 'xml':
                    $pathtoFile = storage_path() . '/app/sunat/xml/' . $file_or_id;
                    return response()->download($pathtoFile);
                    break;
                case 'cdr':
                    $pathtoFile = storage_path() . '/app/sunat/cdr/' . $archivo[0] . '.xml';
                    if (!file_exists($pathtoFile)) {
                        return redirect('/comprobantes/consulta-cdr')->withErrors(['No se ha obtenido el CDR del comprobante. LLena los datos abajo, dale al botón CONSULTAR CDR y vuelve a descargar desde la página anterior.']);
                    }
                    return response()->download($pathtoFile);
                    break;
                default:
                    return null;
            }
        }
    }

    public function verificar_estado_comprobante($idventa){

        $request = new Request();
        $facturacion = Facturacion::find($idventa);
        $request->idventa = $idventa;
        $request->tipo_consulta='cdr';
        $request->tipo = $facturacion->codigo_tipo_documento;
        $request->serie = $facturacion->serie;
        $request->numero = $facturacion->correlativo;
        $respuesta = $this->getStatusCdr($request);
        if(str_contains(strtolower($respuesta),'aceptado') || str_contains(strtolower($respuesta),'aceptada')){
            $venta = Venta::find($idventa);
            $venta->facturacion()->update([
                "estado"=>'ACEPTADO'
            ]);
        }
        return $respuesta;
    }

    public function verificar_estado_guia($idguia){

        $request = new Request();
        $guia = Guia::find($idguia);
        $request->idventa = $idguia;
        $request->tipo_consulta='cdr';
        $request->tipo = '09';
        $request->serie = 'T001';
        $correlativo = explode('-', $guia->correlativo)[1];
        $request->numero = $correlativo;
        $respuesta = $this->getStatusCdr($request);
        if(str_contains(strtolower($respuesta),'aceptado') || str_contains(strtolower($respuesta),'aceptada')){
            $venta = Venta::find($idguia);
            $venta->facturacion()->update([
                "estado"=>'ACEPTADO'
            ]);
        }
        return $respuesta;
    }

    public function generarTockenGRE(){
        try{
            $config = $this->getConexionData();
            $cliente_id = $config['client_id']??'';
            $client_secret = $config['client_secret']??'';
            $url = 'https://api-seguridad.sunat.gob.pe/v1/clientessol/'.$cliente_id.'/oauth2/token/';
            $scope = 'https://api-cpe.sunat.gob.pe';
            $username = $this->config['usuario'];
            $password = $this->config['clave'];

            $expire = date('Y-m-d H:i:s', (strtotime(date('Y-m-d H:i:s')) + 3400));
            $opcion = Opciones::where('nombre_opcion','expire_tocken')->first();

            if(!$opcion || date('Y-m-d H:i:s') >= date('Y-m-d H:i:s', strtotime($opcion->valor))){

                $curl = curl_init();
                $data = [
                    "grant_type" => "password",
                    "scope" => $scope,
                    "client_id" => $cliente_id,
                    "client_secret" => $client_secret,
                    "username" => $username,
                    "password" => $password
                ];

                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_HEADER => false,
                    CURLOPT_HTTPHEADER => ['application/x-www-form-urlencoded'],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => http_build_query($data),
                ));

                if(env('APP_ENV') == 'local'){
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                }

                $response = curl_exec($curl);
                //$err = curl_error($curl);

                curl_close($curl);
                if(!$opcion){
                    $opcion = new Opciones();
                    $opcion->nombre_opcion = 'expire_tocken';
                }
                $opcion->valor = $expire;
                $opcion->valor_json = $response;
                $opcion->save();
                $tocken = json_decode($response, true);
            } else {
                $tocken = json_decode($opcion->valor_json, true);
            }

            return $tocken;

        } catch (\Exception $e){
            return $e->getMessage();
        }
    }

    public function enviarGuiaApi($idguia){

        //Generar archivos
        $util=new Util($this->config['esProduccion']);
        $xml=$util->generarXmlGuia($idguia);
        $zip=$util->generarZip($xml);

        //Obtener tocken
        $tocken = $this->generarTockenGRE();

        $curl = curl_init();
        $url = 'https://api-cpe.sunat.gob.pe/v1/contribuyente/gem/comprobantes/'.$zip['nombre'];
        $data = [
            "archivo"=>[
            "nomArchivo" => $zip['nombre'].'.zip',
            "arcGreZip" => $zip['contenido'],
            "hashZip" => $zip['hash']
            ]
        ];

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer '.$tocken['access_token'],
                'Content-Type: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),

        ));

        if(env('APP_ENV') == 'local'){
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }

        $response = curl_exec($curl);

        curl_close($curl);
        $guia = Guia::find($idguia);
        if($guia->ticket){
            $array_ticket = json_decode($guia->ticket, true);
            $new_ticket = json_decode($response, true);
            array_push($array_ticket, $new_ticket);
            $guia->ticket = $array_ticket;
        } else {
            $guia->ticket = '['.$response.']';
        }

        $guia->save();
        return 'Número de ticket Sunat: '.json_decode($response, true)['numTicket']??'-';
    }

    public function consultarGRE(Request $request)    {

        //Obtener tocken
        $tocken = $this->generarTockenGRE();
        $url = 'https://api-cpe.sunat.gob.pe/v1/contribuyente/gem/comprobantes/envios/'.$request->ticket;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer '.$tocken['access_token']
            ],
        ));

        if(env('APP_ENV') == 'local'){
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }

        $response = curl_exec($curl);

        curl_close($curl);
        Log::info('ticket: '.$request->ticket);

        $response = json_decode($response, true);

        switch ($response['codRespuesta']){
            case '0':
                $respuesta = 'La guía se ha enviado correctamente';
                $estado = 'ACEPTADO';
                $file = storage_path('/app/sunat/zip/') . $request->file . '.zip';
                if (file_exists($file)) {
                    unlink($file);
                }
                break;
            case '98':
                $respuesta = 'Código:98 - El envío de la guía está en proceso';
                $estado = 'PENDIENTE';
                break;
            case '99':
                $respuesta = 'Código:99 - Error:';
                $respuesta .= $response['error']['numError'].' - '.$response['error']['desError'];
                $estado = 'PENDIENTE';

                if($response['error']['numError'] == '1033'){
                    $estado = 'ACEPTADO';
                    $respuesta = 'La guía se ha enviado correctamente';
                    $guia = Guia::find($request->idguia);
                    $num_ticket = json_decode($guia->ticket,true);
                    $penultimo_ticket = count($num_ticket)-2;
                    $request->ticket = $num_ticket[$penultimo_ticket]['numTicket'];
                    $this->consultarGRE($request);
                }

                break;
            default:
                $respuesta = '';
                $estado = 'PENDIENTE';
        }

        if($response['codRespuesta'] != 98 && $response['indCdrGenerado']=='1'){
            /*CONVERTIR A ZIP */
            $cdr=base64_decode($response['arcCdr']);
            $zip_cdr = fopen(storage_path().'/app/sunat/cdr/'.$request->file.'.zip','w+');
            fputs($zip_cdr,$cdr);
            fclose($zip_cdr);

            /*EXTRAER EL ZIP*/
            $file = storage_path().'/app/sunat/cdr/'.$request->file.'.zip';
            if (file_exists($file)) {
                $zip_cdr = new \ZipArchive();
                if ($zip_cdr->open($file)=== TRUE) {
                    $zip_cdr->extractTo(storage_path().'/app/sunat/cdr/');
                    $zip_cdr->close();
                    unlink($file);
                } else {
                    return 'Error al descomprimir el CDR';
                }
            } else {
                Log::info($request->idguia.' Error: No se ha recibido CDR desde sunat');
                return 'No se ha recibido CDR desde sunat';
            }

        }

        $guia = Guia::find($request->idguia);
        $guia->response = $respuesta;
        $guia->estado = $estado;
        $guia->save();


        return [$respuesta,$estado];

    }


}