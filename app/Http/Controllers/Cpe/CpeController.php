<?php
/**
 * Created by PhpStorm.
 * User: Luciano
 * Date: 22/03/2020
 * Time: 15:44
 */

namespace sysfact\Http\Controllers\Cpe;

use Illuminate\Http\Request;
use sysfact\Emisor;
use sysfact\Http\Controllers\Controller;
use sysfact\Http\Controllers\Helpers\MainHelper;
use sysfact\Http\Controllers\Helpers\PdfHelper;
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

        //Generar archivos
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
        return $resp->mensaje($respuesta,$zip['nombre']);
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


}