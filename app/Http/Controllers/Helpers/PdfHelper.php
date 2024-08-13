<?php
/**
 * Created by PhpStorm.
 * User: Luciano
 * Date: 21/06/2022
 * Time: 19:50
 */

namespace sysfact\Http\Controllers\Helpers;


use Illuminate\Support\Facades\Log;
use Luecano\NumeroALetras\NumeroALetras;
use Spipu\Html2Pdf\Html2Pdf;
use sysfact\Emisor;
use sysfact\Guia;
use sysfact\Http\Controllers\OpcionController;
use sysfact\libraries\QrCodeGenerador;
use sysfact\Util\CreditNote;
use sysfact\Util\DebitNote;
use sysfact\Util\Despatch;
use sysfact\Util\Invoice;
use sysfact\Venta;

class PdfHelper
{

    private static $formato;
    private static $ruta_formato;

    public static function setFormatoImpresion(){
        $config = MainHelper::configuracion('impresion');
        $formato =  MainHelper::getFormatoImpresionComprobantes($config);
        self::$formato = $formato['medidas'];
        self::$ruta_formato = $formato['ruta'];
    }

    public static function generarPdf($idventa, $rawbt, $dest = false, $formato = false){

        self::setFormatoImpresion();

        if($formato){
            if($formato == '80_1'){
                self::$ruta_formato = $formato;
                self::$formato = [72,250];
            } else {
                if(!str_contains(self::$ruta_formato,'A4')){
                    self::$ruta_formato = 'A4_1';
                    self::$formato = 'A4';
                }
            }
        }

        $venta=Venta::find($idventa);
        $venta->color = json_decode(cache('config')['impresion'], true)['color']??false;

        if($venta->facturacion->codigo_tipo_documento == '30'){

            $items=$venta->productos;
            $venta->facturacion;

            if($venta->facturacion->codigo_moneda=='PEN'){
                $moneda_letras='SOLES';
                $venta->facturacion->codigo_moneda='S/';
            } else{
                $moneda_letras='DÓLARES';
            }

            if($venta->facturacion->emitir_como_contado === 1){
                $venta->tipo_pago = 1;
            }

            $venta->leyenda=NumeroALetras::convert($venta->total_venta, $moneda_letras,true);
            $usuario=$venta->cliente;
            $emisor=new Emisor();
            $nombre_fichero = $venta->facturacion->serie.'-'.$venta->facturacion->correlativo;

            $view = view('sunat/plantillas-pdf/'.self::$ruta_formato.'/recibo',['documento'=>$venta, 'emisor'=>$emisor,'usuario'=>$usuario,'items'=>$items]);
            $html=$view->render();

        } else {

            $venta->productos;
            $venta->facturacion;
            $venta->cliente;
            $venta->persona;
            $venta->emisor=new Emisor();


            switch ($venta->facturacion->codigo_tipo_documento){
                case '01':
                case '03':
                    $doc=new Invoice($venta);
                    break;
                case '07':
                    $doc=new CreditNote($venta);
                    break;
                default:
                    $doc=new DebitNote($venta);
                    break;

            }

            $doc->generar_xml(false);
            $documento=$doc->getVenta();
            $nombre_fichero=$doc->getNombreFichero();

            /*OBTENER CÓDIGO HASH*/
            $filename = storage_path().'/app/sunat/xml/'.$nombre_fichero.'.xml';
            if(file_exists($filename)){
                $dom = new \DOMDocument();
                $dom->load($filename);
                $digest = $dom->getElementsByTagName('DigestValue')->item(0);
                $documento->hash=$digest->nodeValue;
            }

            $documento->qr=$nombre_fichero.'.png';

            /*CREAR CÓDIGO QR*/
            /*-----------------------------------------------------------------------------*/
            $data=$documento->emisor->ruc."|01|".$documento->serie ."|". $documento->correlativo."|".
                $documento->facturacion->igv ."|". $documento->total_venta ."|". $documento->fecha ."|".
                $documento->cliente->tipo_documento ."|". $documento->cliente->num_documento ."|". $documento->hash;
            /*-----------------------------------------------------------------------------*/
            $qr = new QrCodeGenerador($data);
            $qr->generar($nombre_fichero);

            /*SELECCIONAR PLANTILLA*/

            switch ($documento->facturacion->codigo_tipo_documento){
                case 01:
                    $documento->titulo_doc = 'Factura';
                    $plantilla_pdf = 'factura';
                    break;
                case 03:
                    $documento->titulo_doc = 'Boleta de venta';
                    $plantilla_pdf = 'boleta';
                    break;
                case 07:
                    $documento->titulo_doc = 'Nota de Crédito';
                    $plantilla_pdf = 'nota_credito';
                    break;
                default:
                    $documento->titulo_doc = 'Nota de Débito';
                    $plantilla_pdf = 'nota_debito';
            }

            if($documento->codigo_moneda=='PEN'){
                $documento->codigo_moneda='S/';
            } else{
                $documento->codigo_moneda='USD';
            }

            $opcion=new OpcionController();
            $tipo_cambio=$opcion->obtener_tipo_de_cambio();

            $datos = ['documento' => $documento, 'usuario' => $documento->cliente, 'items' => $documento->productos, 'emisor' => $documento->emisor,'tipo_cambio'=>$tipo_cambio];

            $view = view('sunat/plantillas-pdf/'.self::$ruta_formato.'/' . $plantilla_pdf, $datos);
            $html = $view->render();

        }

        $pdf=new Html2Pdf('P',self::$formato,'es');
        $pdf->pdf->SetTitle($nombre_fichero.'.pdf');
        $pdf->writeHTML($html);

        if($dest){
            if($dest == 'F'){
                $pdf->output(storage_path().'/app/sunat/pdf/' . $nombre_fichero . '.pdf', $dest);
            }
            if($dest == 'D'){
                $pdf->output($nombre_fichero . '.pdf', $dest);
            }

        } else {
            if($rawbt){
                $fromFile = $pdf->output($nombre_fichero.'.pdf','S');
                return 'rawbt:data:application/pdf;base64,'.base64_encode($fromFile);
            } else {
                $pdf->output($nombre_fichero.'.pdf');
            }
        }

        if(file_exists(public_path('images/qr/'.$nombre_fichero.'.png'))){
            unlink(public_path('images/qr/'.$nombre_fichero.'.png'));
        }

    }

    public static function generarPdfGuia($idguia, $rawbt, $dest = false, $formato = false){

        self::setFormatoImpresion();

        if($formato){
            if($formato == '80_1'){
                self::$ruta_formato = $formato;
                self::$formato = [72,250];
            } else {
                if(!str_contains(self::$ruta_formato,'A4')){
                    self::$ruta_formato = 'A4_1';
                    self::$formato = 'A4';
                }
            }
        }

        $guia=Guia::find($idguia);
        $guia->productos;
        $guia->cliente;
        $guia->persona;
        $guia->emisor=new Emisor();

        $doc=new Despatch($guia);
        $doc->generar_xml(false);
        $documento=$doc->getVenta();
        $nombre_fichero=$doc->getNombreFichero();

        /*OBETENER CÓDIGO HASH*/
        $dom = new \DOMDocument();
        $dom->load(storage_path().'/app/sunat/xml/'.$nombre_fichero.'.xml');
        $digest = $dom->getElementsByTagName('DigestValue')->item(0);
        $documento->hash=$digest->nodeValue;
        $documento->titulo_doc = 'Guia de Remisión Remitente';

        //GENERAR QR
        $file_xml=storage_path().'/app/sunat/cdr/R-'.$nombre_fichero.'.xml';
        if(file_exists($file_xml)){
            $cdr_xml = simplexml_load_file($file_xml);
            $qr_response=$cdr_xml->xpath('//cbc:DocumentDescription');
            $data = $qr_response[0]??false;
            if($data){
                $data = $data."";
                $qr = new QrCodeGenerador($data);
                $documento->qr = $nombre_fichero.'.png';
                $qr->generar($nombre_fichero);
            }
        }

        $datos = ['documento' => $documento, 'usuario' => $documento->cliente, 'items' => $documento->productos, 'emisor' => $documento->emisor];
        $view = view('sunat/plantillas-pdf/'.self::$ruta_formato.'/guia_remision', $datos);
        $html = $view->render();
        $pdf = new Html2Pdf('P', self::$formato, 'es');
        $pdf->pdf->SetTitle($nombre_fichero.'.pdf');
        $pdf->writeHTML($html);

        if($dest){
            if($dest == 'F'){
                $pdf->output(storage_path().'/app/sunat/pdf/' . $nombre_fichero . '.pdf', $dest);
            }
            if($dest == 'D'){
                $pdf->output($nombre_fichero . '.pdf', $dest);
            }
        } else {
            if($rawbt){
                $fromFile = $pdf->output($nombre_fichero.'.pdf','S');
                return 'rawbt:data:application/pdf;base64,'.base64_encode($fromFile);
            } else {
                $pdf->output($nombre_fichero.'.pdf');
            }
        }

        if(file_exists(public_path('images/qr/'.$nombre_fichero.'.png'))){
            unlink(public_path('images/qr/'.$nombre_fichero.'.png'));
        }

    }
}