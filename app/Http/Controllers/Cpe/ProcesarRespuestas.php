<?php
/**
 * Created by PhpStorm.
 * User: Luciano
 * Date: 22/03/2020
 * Time: 18:31
 */

namespace sysfact\Http\Controllers\Cpe;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use sysfact\Facturacion;
use sysfact\Guia;
use sysfact\Http\Controllers\Helpers\MainHelper;
use sysfact\Inventario;
use sysfact\Notifications\NotificacionesSistema;
use sysfact\Resumen;
use sysfact\User;
use sysfact\Util\Summary;
use sysfact\Util\SummaryVoided;
use sysfact\Venta;

class ProcesarRespuestas
{
    private $idventa;

    public function __construct($idventa=-1)
    {
        $this->idventa=$idventa;
    }

    public function mensaje($response,$nombre_zip){

        if(!$response){
            Log::info('SUNAT no ha podido procesar su solicitud. Inténtalo nuevamente en unos minutos. Response: '.$response);
            return 'SUNAT no ha podido procesar su solicitud. Se intentará el reenvío automático en el transcurso del día.';
        } else{
            //Verificación de errores
            $xml = simplexml_load_string($response);
            $xml_fault = $xml->xpath('//soap-env:Fault');
            $error=isset($xml_fault[0])?$xml_fault[0]:false;

            if($error){
                switch ($error->faultcode){
                    case 'soap-env:Client.1033':
                        $venta=Facturacion::find($this->idventa);
                        $venta->estado='ACEPTADO';
                        $venta->save();
                        unlink(storage_path('/app/sunat/zip/').$nombre_zip.'.zip');
                        return 'El comprobante ha sido aceptado.';
                        break;
                }

                if($error->detail->message){
                    //Si es error en guía
                    $mensaje = 'Error: '.$error->faultcode.' - '.$error->faultstring.' - '.$error->detail->message;
                } else {
                    //Si es error en boleta o factura
                    $mensaje = 'Error: '.$error->faultcode.' - '.$error->faultstring;
                }

                $user =  User::whereHas('roles', function ($query) {
                    $query->where('id', 5);
                })->get();

                return $mensaje;

            } else {
                /*CONVERTIR A ZIP */
                $cdr=base64_decode($xml->xpath('//applicationResponse')[0]);
                $zip_cdr = fopen(storage_path().'/app/sunat/cdr/R-'.$nombre_zip.'.zip','w+');
                fputs($zip_cdr,$cdr);
                fclose($zip_cdr);

                /*EXTRAER EL ZIP*/
                $file = storage_path().'/app/sunat/cdr/R-'.$nombre_zip.'.zip';
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
                    Log::info($this->idventa.' archivo: '.$nombre_zip.' Error: No se ha recibido CDR desde sunat');
                    return 'No se ha recibido CDR desde sunat, intenta el reenvío.';
                }

                /*LEER EL CDR*/
                $file_xml=storage_path().'/app/sunat/cdr/R-'.$nombre_zip.'.xml';
                if(file_exists($file_xml)){
                    $cdr_xml = simplexml_load_file($file_xml);
                    $ReferenceID = $cdr_xml->xpath('//cbc:ReferenceID')[0];
                    $ResponseCode = $cdr_xml->xpath('//cbc:ResponseCode')[0];
                    $Description=$cdr_xml->xpath('//cbc:Description')[0];
                    $Note=$cdr_xml->xpath('//cbc:Note');
                    $observación=isset($Note[0])?' *Observación: '.$Note[0]:'';
                    unlink(storage_path('/app/sunat/zip/').$nombre_zip.'.zip');
                } else{
                    Log::info($this->idventa.' archivo: '.$nombre_zip.' Error: No se ha generado el xml del CDR, se ejecutará getStatusCDR');
                    return false;
                }

                /*ACTUALIZAR ESTADO Y NOTIFICAR RESPUESTA CDR*/

                if($ResponseCode == '0'){

                    if(strpos($ReferenceID,'T00')!== false){
                        //Si es guía
                        $venta=Guia::find($this->idventa);
                    } else {
                        $venta=Facturacion::find($this->idventa);
                    }

                    $venta->estado='ACEPTADO';
                    $venta->save();
                    return $Description.$observación;

                }  else{
                    $venta=Facturacion::find($this->idventa);
                    $venta->estado='RECHAZADO';
                    $venta->save();

                    //Actualizar inventario
                    $venta = Venta::find($this->idventa);
                    $productos=$venta->productos;

                    foreach ($productos as $item){
                        $inv = $item->inventario()->first();
                        MainHelper::actualizar_inventario($this->idventa,$item,$inv,'rechazo_doc');
                    }

                    $user =  User::whereHas('roles', function ($query) {
                        $query->where('id', 5);
                    })->get();
                    $venta->mensaje = $Description.$observación;
                    $venta->estado = 'RECHAZADO';
                    Notification::send($user, new NotificacionesSistema($venta));

                    return 'El documento ha sido rechazado. '.$Description.$observación;
                }
            }

        }

    }

    public function mensajeNotas($response,$nombre_zip,$doc_relacionado){

        if(!$response){
            Log::info('SUNAT no ha podido procesar su solicitud. Inténtalo nuevamente en unos minutos. Response: '.$response);
            return 'SUNAT no ha podido procesar su solicitud. Inténtalo nuevamente en unos minutos.';
        } else{

            //Verificación de errores
            $xml = simplexml_load_string($response);
            $xml_fault = $xml->xpath('//soap-env:Fault');
            $error=isset($xml_fault[0])?$xml_fault[0]:false;

            if($error){

                Log::info('Error de facturacion - idventa: '.$this->idventa.' archivo: '.$nombre_zip.' Mensaje: '.$error->faultcode.$error->faultstring.' '.$error->detail->message);

                switch ($error->faultcode){
                    case 'soap-env:Client.1033':
                        $venta=Facturacion::find($this->idventa);
                        $venta->estado='ACEPTADO';
                        $venta->save();

                        //Actualizar estado de factura anulada con nota de credito
                        $codigo_documento = explode('-',$nombre_zip)[1];
                        $correlativo_factura=explode('-',$doc_relacionado);
                        $factura_a_anular=Facturacion::where('serie',$correlativo_factura[0])->where('correlativo',$correlativo_factura[1])->first();

                        if($codigo_documento == '07'){
                            $factura_a_anular->estado='ANULADO';
                        } else{
                            $factura_a_anular->estado='MODIFICADO';
                        }

                        $factura_a_anular->save();

                        return 'El comprobante ha sido aceptado.';
                        break;
                }

                if($error->detail->message){
                    return 'Error: '.$error->faultcode.$error->faultstring.' '.$error->detail->message;
                } else {
                    return 'Error: '.$error->faultcode.' '.$error->faultstring;
                }

            } else {
                /*CONVERTIR A ZIP */
                $cdr=base64_decode($xml->xpath('//applicationResponse')[0]);
                $zip_cdr = fopen(storage_path().'/app/sunat/cdr/R-'.$nombre_zip.'.zip','w+');
                fputs($zip_cdr,$cdr);
                fclose($zip_cdr);

                /*EXTRAER EL ZIP*/
                $file = storage_path().'/app/sunat/cdr/R-'.$nombre_zip.'.zip';
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
                    return 'No se ha recibido CDR desde sunat, intenta el reenvío.';
                }

                /*LEER EL CDR*/
                $file_xml=storage_path().'/app/sunat/cdr/R-'.$nombre_zip.'.xml';
                if(file_exists($file_xml)){
                    $cdr_xml = simplexml_load_file($file_xml);
                    $ReferenceID = $cdr_xml->xpath('//cbc:ReferenceID')[0];
                    $ResponseCode = $cdr_xml->xpath('//cbc:ResponseCode')[0];
                    $Description=$cdr_xml->xpath('//cbc:Description')[0];
                    $Note=$cdr_xml->xpath('//cbc:Note');
                    $observación=isset($Note[0])?' *Observación: '.$Note[0]:'';
                } else{
                    Log::info($this->idventa.' archivo: '.$nombre_zip.' Error: No se ha generado el xml del CDR, se ejecutará getStatusCDR');
                    return false;
                }

                /*ACTUALIZAR ESTADO Y NOTIFICAR RESPUESTA CDR*/

                if($ResponseCode == '0'){

                    $venta=Facturacion::find($this->idventa);
                    $venta->estado='ACEPTADO';
                    $venta->save();

                    //Actualizar estado de factura anulada con nota de credito
                    $codigo_documento = explode('-',$nombre_zip)[1];
                    $correlativo_factura=explode('-',$doc_relacionado);
                    $factura_a_anular=Facturacion::where('serie',$correlativo_factura[0])->where('correlativo',$correlativo_factura[1])->first();

                    if($codigo_documento == '07'){
                        $factura_a_anular->estado='ANULADO';
                    } else{
                        $factura_a_anular->estado='MODIFICADO';
                    }

                    $factura_a_anular->save();

                    return $Description.$observación;

                } else{
                    $venta=Facturacion::find($this->idventa);
                    $venta->estado='RECHAZADO';
                    $venta->save();

                    //Actualizar inventario
                    $venta = Venta::find($this->idventa);
                    $productos=$venta->productos;

                    foreach ($productos as $item){
                        $inv = $item->inventario()->first();
                        MainHelper::actualizar_inventario($this->idventa,$item,$inv,'rechazo_nc');
                    }

                    return 'El documento ha sido rechazado. '.$Description.$observación;
                }
            }
        }

    }

    public function mensajeSummary($response,$obj, $items = null){

        //VERIFICAR SI NO HAY ERRORES PARA CONTINUAR, SI LOS HAY SE DEVUELVE EL TEXTO DE ERROR
        //Y LO GUARDAMOS EN LA VARIABLE $faultstring
        if(!$response){
            Log::info('SUNAT no ha podido procesar su solicitud. Inténtalo nuevamente en unos minutos. Response: '.$response);
            return 'SUNAT no ha podido procesar su solicitud. Inténtalo nuevamente en unos minutos.';
        } else{
            $xml = simplexml_load_string($response);
            $faultstring=$xml->xpath('//faultstring');
            $mensaje=$xml->xpath('//message');

            if(!isset($faultstring[0])){
                $num_ticket_resumen=$xml->xpath('//ticket')[0];

                $resumen=new Resumen();
                if($obj instanceof Summary){

                    //GUARDAR RESUMEN DE BOLETAS
                    $resumen->estado='ENVIADO';
                    $resumen->lote=$obj->getCorrelativo();
                    $resumen->num_ticket=$num_ticket_resumen;
                    $resumen->tipo='RESUMEN';
                    $resumen->save();
                    $idresumen=$resumen->idresumen;

                    foreach ($obj->getVenta() as $venta){
                        $fact=Facturacion::find($venta->idventa);
                        $fact->estado='ACEPTADO';
                        $fact->idresumen=$idresumen;
                        $fact->save();
                    }

                } else {

                    if($obj instanceof SummaryVoided){
                        $resumen->tipo='RESUMEN-BAJA';
                        $resumen->lote = $obj->getCorrelativo();

                    } else{
                        $resumen->tipo = 'BAJA';
                        $resumen->lote_baja = $obj->getCorrelativo();
                    }

                    $resumen->estado = 'ENVIADO';
                    $resumen->num_ticket = $num_ticket_resumen;
                    $resumen->save();
                    $idresumen = $resumen->idresumen;

                    foreach ($items as $venta) {
                        $fact = Facturacion::find($venta['idventa']);
                        $fact->estado = 'ANULADO (BAJA)';
                        $fact->motivo_baja = $venta['motivo_baja'];
                        $fact->idresumen = $idresumen;
                        $fact->save();

                        $detalle = Venta::find($venta['idventa']);

                        $esNotaCredito = $fact->codigo_tipo_documento === '07';
                        $operacionTexto = $esNotaCredito
                            ? 'RESTAURACIÓN POR ANULACIÓN DE NOTA DE CRÉDITO N° ' . $fact->serie.'-'.$fact->correlativo
                            : 'ANULACIÓN DE VENTA N° ' . $venta['idventa'];

                        foreach ($detalle->productos as $producto) {
                            $cantidad = $producto['detalle']['cantidad'];

                            $inventario = new Inventario();
                            $inventario->idproducto = $producto['idproducto'];
                            $inventario->idempleado = auth()->user()->idempleado ?? -1;
                            $inventario->cantidad = $esNotaCredito ? -$cantidad : $cantidad;

                            $saldoActual = $producto->inventario()->first()->saldo ?? 0;
                            $inventario->saldo = $saldoActual + $inventario->cantidad;
                            $inventario->operacion = $operacionTexto;
                            $inventario->save();
                        }
                    }

                }

                return 'Enviado correctamente a SUNAT, N° de ticket '.$num_ticket_resumen;

            } else{
                return 'Error: '.$faultstring[0].' - '.$mensaje[0];
            }
        }



    }

    public function mensajeGetStatus($response,$nombre,$ticket){
        if(!$response){
            Log::info('SUNAT no ha podido procesar su solicitud. Inténtalo nuevamente en unos minutos. Response: '.$response);
            return 'SUNAT no ha podido procesar su solicitud. Inténtalo nuevamente en unos minutos.';
        } else {
            $xml = simplexml_load_string($response);
            $faultstring=$xml->xpath('//faultstring');

            if(!isset($faultstring[0])){

                $cdr=base64_decode($xml->xpath('//content')[0]);
                $zip_cdr = fopen(storage_path().'/app/sunat/cdr/R-'.$nombre.'.zip','w+');
                fputs($zip_cdr,$cdr);
                fclose($zip_cdr);

                $zip = new \ZipArchive();
                if ($zip->open(storage_path().'/app/sunat/cdr/R-'.$nombre.'.zip') === TRUE) {
                    $zip->extractTo(storage_path().'/app/sunat/cdr/');
                    $zip->close();
                } else {
                    return 'Error al descomprimir el CDR';
                }

                /*LEER EL CDR*/
                $cdr_xml=simplexml_load_file(storage_path().'/app/sunat/cdr/R-'.$nombre.'.xml');
                $description=$cdr_xml->xpath('//cbc:Description');

                if(strpos($description[0], 'aceptado') !== false){

                    $resumen=Resumen::where('num_ticket',$ticket)->first();
                    $resumen->estado='ACEPTADO';
                    $resumen->save();

                }

                return $description[0];

            } else{
                return 'Error: '.$faultstring[0];
            }
        }
    }

    public function mensajeGetStatusCdr($response,$request,$nombre_archivo){
        if(!$response){
            Log::info('SUNAT no ha podido procesar su solicitud. Inténtalo nuevamente en unos minutos. Response: '.$response);
            return 'SUNAT no ha podido procesar su solicitud. Inténtalo nuevamente en unos minutos.';
        } else{
            $xml = simplexml_load_string($response);
            if(isset($xml->xpath('//statusMessage')[0])){
                $mensaje=$xml->xpath('//statusMessage')[0];
            } else{
                $mensaje = $xml->xpath('//faultcode')[0].' '.$xml->xpath('//faultstring')[0];
            }

            if($request->tipo_consulta=='cdr' && $xml->xpath('//statusCode')[0]!='0127'){

                $cdr=base64_decode($xml->xpath('//content')[0]);
                $zip_cdr = fopen(storage_path().'/app/sunat/cdr/R-'.$nombre_archivo.'.zip','w+');
                fputs($zip_cdr,$cdr);
                fclose($zip_cdr);

                $zip = new \ZipArchive();
                if ($zip->open(storage_path().'/app/sunat/cdr/R-'.$nombre_archivo.'.zip') === TRUE) {
                    $zip->extractTo(storage_path().'/app/sunat/cdr/');
                    $zip->close();
                } else {
                    return 'Error al descomprimir el CDR';
                }

                /*LEER EL CDR*/
                $cdr_xml=simplexml_load_file(storage_path().'/app/sunat/cdr/R-'.$nombre_archivo.'.xml');
                $description=$cdr_xml->xpath('//cbc:Description');
                $nota=$cdr_xml->xpath('//cbc:Note');
                $observación=isset($nota[0])?' *Observación: '.$nota[0]:'';
                return $description[0].$observación;

            }
            return $mensaje;
        }
    }

}