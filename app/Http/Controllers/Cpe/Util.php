<?php
/**
 * Created by PhpStorm.
 * User: Luciano
 * Date: 22/03/2020
 * Time: 15:46
 */

namespace sysfact\Http\Controllers\Cpe;


use Illuminate\Support\Facades\DB;
use sysfact\Emisor;
use sysfact\Guia;
use sysfact\Http\Controllers\Helpers\MainHelper;
use sysfact\libraries\xmldsig\XMLSecurityDSig;
use sysfact\libraries\xmldsig\XMLSecurityKey;
use sysfact\Util\CreditNote;
use sysfact\Util\DebitNote;
use sysfact\Util\Despatch;
use sysfact\Util\Invoice;
use sysfact\Util\Summary;
use sysfact\Util\SummaryVoided;
use sysfact\Util\Voided;
use sysfact\Venta;

class Util
{
    private $tipoDocumento;
    private $documento;
    private $esProduccion;
    private $formato;
    private $ruta_formato;

    public function __construct($esProduccion)
    {
        $this->esProduccion = $esProduccion;
        $this->setFormatoImpresion();
    }

    public function setFormatoImpresion(){
        $config = MainHelper::configuracion('impresion');
        $formato =  MainHelper::getFormatoImpresionComprobantes($config);
        $this->formato = $formato['medidas'];
        $this->ruta_formato = $formato['ruta'];
    }

    public function generarXml($idventa){

        $venta=Venta::find($idventa);
        $venta->productos;
        $venta->facturacion;
        $venta->cliente;
        $venta->persona;
        $venta->emisor=new Emisor();


        switch ($venta->facturacion['codigo_tipo_documento']){
            case '01':
            case '03':
                $documento=new Invoice($venta);
                break;
            case '07':
                $documento=new CreditNote($venta);
                break;
            default:
                $documento=new DebitNote($venta);
        }

        $this->tipoDocumento=$venta->facturacion['codigo_tipo_documento'];
        $this->documento=$documento;

        $xml = $documento->generar_xml();
        $this->firmar($xml);
        //$this->generarPdf();
        return $documento->getNombreFichero();

    }

    public function generarXmlGuia($idguia){

        $guia=Guia::find($idguia);
        $guia->productos;
        $guia->cliente;
        $guia->persona;
        $guia->emisor=new Emisor();

        $documento=new Despatch($guia);

        $this->documento=$documento;

        $xml = $documento->generar_xml();
        $this->firmar($xml);
        //$this->generarPdfGuia($documento);
        return $documento->getNombreFichero();
    }

    public function generarXmlResumen($fecha){

        $ventas=Venta::with('facturacion')
            ->whereBetween('fecha',[$fecha.' 00:00:00',$fecha.' 23:59:59'])
            ->where('eliminado','=',0)
            ->whereHas('facturacion', function($query) {
                $query->where('serie', 'LIKE','B%')->where('estado','PENDIENTE');

            })
            ->orderby('idventa','desc')
            ->get();

        $documento=new Summary($ventas);
        $emisor=new Emisor();

        $venta = DB::table('resumen')
            ->select('lote')
            ->whereBetween('fecha_generacion',[date('Y-m-d').' 00:00:00',date('Y-m-d').' 23:59:59'])
            ->where('tipo','!=','BAJA')
            ->orderby('idresumen','desc')
            ->first();

        if($venta){
            $correlativo=$venta->lote+1;
        } else{
            $correlativo=1;
        }

        $documento->setEmisor($emisor);
        $documento->setFechaEmision($fecha);
        $documento->setCorrelativo($correlativo);
        $documento->setNombreFichero($emisor->ruc.'-RC-'.date('Ymd').'-'.$correlativo);

        $this->documento=$documento;

        $xml = $documento->generar_xml();
        $this->firmar($xml);
        return $documento->getNombreFichero();

    }

    public function generarXmlResumenBaja($items){

        $documento=new SummaryVoided($items);
        $emisor=new Emisor();

        $venta = DB::table('resumen')
            ->select('lote')
            ->whereBetween('fecha_generacion',[date('Y-m-d').' 00:00:00',date('Y-m-d').' 23:59:59'])
            ->where('tipo','!=','BAJA')
            ->orderby('idresumen','desc')
            ->first();

        if($venta){
            $correlativo=$venta->lote+1;
        } else{
            $correlativo=1;
        }

        $fecha=$items[0]['fecha'];

        $documento->setEmisor($emisor);
        $documento->setFechaEmision($fecha);
        $documento->setCorrelativo($correlativo);
        $documento->setNombreFichero($emisor->ruc.'-RC-'.date('Ymd').'-'.$correlativo);

        $this->documento=$documento;

        $xml = $documento->generar_xml();
        $this->firmar($xml);
        return $documento->getNombreFichero();

    }

    public function generarXmlComunicacionBaja($items){

        $documento=new Voided($items);
        $emisor=new Emisor();

        $venta = DB::table('resumen')
            ->select('lote_baja')
            ->whereBetween('fecha_generacion',[date('Y-m-d').' 00:00:00',date('Y-m-d').' 23:59:59'])
            ->where('tipo','BAJA')
            ->orderby('idresumen','desc')
            ->first();

        if($venta){
            $correlativo=$venta->lote_baja+1;
        } else{
            $correlativo=1;
        }

        $fecha=$items[0]['fecha'];

        $documento->setEmisor($emisor);
        $documento->setFechaEmision($fecha);
        $documento->setCorrelativo($correlativo);
        $documento->setNombreFichero($emisor->ruc.'-RA-'.date('Ymd').'-'.$correlativo);

        $this->documento=$documento;

        $xml = $documento->generar_xml();
        $this->firmar($xml);
        return $documento->getNombreFichero();
    }

    public function firmar($xml){

        $nombre=$this->documento->getNombreFichero();
        $documento = new \DOMDocument();
        //$documento->preserveWhiteSpace=false;
        //$documento->formatOutput=true;
        $documento->loadXML($xml);

        $private_key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, ['type' => 'private']);
        if($this->esProduccion){
            $private_key->loadKey(storage_path().'/app/sunat/certificados/private_key.key',true);
        } else{
            $private_key->loadKey(public_path().'/files/private_key.pem',true);
        }

        $dsig=new XMLSecurityDSig();
        $dsig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
        $dsig->addReference($documento,XMLSecurityDSig::SHA1,['http://www.w3.org/2000/09/xmldsig#enveloped-signature'],['force_uri' => true]);
        $dsig->sign($private_key);

        if($this->esProduccion){
            $dsig->add509Cert(file_get_contents(storage_path().'/app/sunat/certificados/public_key.cer'),true,false,['subjectName' => false]);
        } else{
            $dsig->add509Cert(file_get_contents(public_path().'/files/public_key.pem'),true,false,['subjectName' => false]);
        }

        $dsig->appendSignature($documento->getElementsByTagName('ExtensionContent')->item(0));
        $documento->save(storage_path().'/app/sunat/xml/'.$nombre.'.xml');
        chmod(storage_path().'/app/sunat/xml/'.$nombre.'.xml',0777);

    }

    public function generarZip($nombre){

        $fichero_zip=storage_path().'/app/sunat/zip/'.$nombre.'.zip';
        $zip = new \ZipArchive();
        $zip->open($fichero_zip,\ZipArchive::CREATE);
        $zip->addFile(storage_path().'/app/sunat/xml/'.$nombre.'.xml',$nombre.'.xml');
        $zip->close();
        return ['nombre'=>$nombre,'contenido'=>base64_encode(file_get_contents($fichero_zip))];

    }

//    public function generarPdf($esGuia=false){
//
//        $documento=$this->documento->getVenta();
//        $nombre_fichero=$this->documento->getNombreFichero();
//
//
//        /*OBTENER CÓDIGO HASH*/
//        $dom = new \DOMDocument();
//        $dom->load(storage_path().'/app/sunat/xml/'.$nombre_fichero.'.xml');
//        $digest = $dom->getElementsByTagName('DigestValue')->item(0);
//        $documento->hash=$digest->nodeValue;
//        $documento->qr=$nombre_fichero.'.png';
//
//        /*CREAR CÓDIGO QR*/
//        /*-----------------------------------------------------------------------------*/
//        $data=$documento->emisor->ruc."|01|".$documento->serie ."|". $documento->correlativo."|".
//            $documento->facturacion->igv ."|". $documento->total_venta ."|". $documento->fecha ."|".
//            $documento->cliente->tipo_documento ."|". $documento->cliente->num_documento ."|". $documento->hash;
//        /*-----------------------------------------------------------------------------*/
//        $qr = new QrCodeGenerador($data);
//        $qr->generar($nombre_fichero);
//
//        /*SELECCIONAR PLANTILLA*/
//
//        if($esGuia){
//            $plantilla_pdf = 'guia_remision';
//            $documento->titulo_doc = 'Guia de Remisión Remitente';
//        }else{
//            switch ($documento->facturacion->codigo_tipo_documento){
//                case 01:
//                    $documento->titulo_doc = 'Factura';
//                    $plantilla_pdf = 'factura';
//                    break;
//                case 03:
//                    $documento->titulo_doc = 'Boleta de venta';
//                    $plantilla_pdf = 'boleta';
//                    break;
//                case 07:
//                    $documento->titulo_doc = 'Nota de Crédito';
//                    $plantilla_pdf = 'nota_credito';
//                    break;
//                default:
//                    $documento->titulo_doc = 'Nota de Débito';
//                    $plantilla_pdf = 'nota_debito';
//            }
//        }
//
//        if($documento->codigo_moneda=='PEN'){
//            $documento->codigo_moneda='S/';
//        } else{
//            $documento->codigo_moneda='USD';
//        }
//
//        $opcion=new OpcionController();
//        $tipo_cambio=$opcion->obtener_tipo_de_cambio();
//
//        $datos = ['documento' => $documento, 'usuario' => $documento->cliente, 'items' => $documento->productos, 'emisor' => $documento->emisor,'tipo_cambio'=>$tipo_cambio];
//
//        $view = view('sunat/plantillas-pdf/'.$this->ruta_formato.'/' . $plantilla_pdf, $datos);
//        $html = $view->render();
//        $pdf = new Html2Pdf('P', $this->formato, 'es');
//        $pdf->writeHTML($html);
//        $pdf->output(storage_path().'/app/sunat/pdf/' . $nombre_fichero . '.pdf', 'F');
//        unlink(public_path('images/qr/'.$nombre_fichero.'.png'));
//    }

//    public function generarPdfGuia($esGuia=false){
//
//        $documento=$this->documento->getVenta();
//        $nombre_fichero=$this->documento->getNombreFichero();
//
//        /*OBETENER CÓDIGO HASH*/
//        $dom = new \DOMDocument();
//        $dom->load(storage_path().'/app/sunat/xml/'.$nombre_fichero.'.xml');
//        $digest = $dom->getElementsByTagName('DigestValue')->item(0);
//        $documento->hash=$digest->nodeValue;
//        $documento->titulo_doc = 'Guia de Remisión Remitente';
//
//        $datos = ['documento' => $documento, 'usuario' => $documento->cliente, 'items' => $documento->productos, 'emisor' => $documento->emisor];
//        $view = view('sunat/plantillas-pdf/'.$this->ruta_formato.'/guia_remision', $datos);
//        $html = $view->render();
//        $pdf = new Html2Pdf('P', $this->formato, 'es');
//        $pdf->writeHTML($html);
//        $pdf->output(storage_path().'/app/sunat/pdf/' . $nombre_fichero . '.pdf', 'F');
//    }

    public function getTipoDocumento()
    {
        return $this->tipoDocumento;
    }

    public function setTipoDocumento($tipoDocumento)
    {
        $this->tipoDocumento = $tipoDocumento;
    }

    public function getDocumento()
    {
        return $this->documento;
    }

    public function setDocumento($documento)
    {
        $this->documento = $documento;
    }


}