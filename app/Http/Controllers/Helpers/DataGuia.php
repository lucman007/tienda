<?php
/**
 * Created by PhpStorm.
 * User: Luciano
 * Date: 9/08/2022
 * Time: 15:00
 */

namespace sysfact\Http\Controllers\Helpers;


class DataGuia
{
    public static function getMotivoTraslado(){
        return [
            ['num_val'=>'01','text_val'=>'','label'=>'Venta'],
            ['num_val'=>'03','text_val'=>'','label'=>'Venta con entrega a terceros'],
            ['num_val'=>'14','text_val'=>'','label'=>'Venta sujeta a confirmacion del comprador'],
            ['num_val'=>'02','text_val'=>'','label'=>'Compra'],
            ['num_val'=>'04','text_val'=>'','label'=>'Traslado entre establecimientos de la misma empresa'],
            ['num_val'=>'05','text_val'=>'','label'=>'Consignación'],
            ['num_val'=>'06','text_val'=>'','label'=>'Devolución'],
            ['num_val'=>'07','text_val'=>'','label'=>'Recojo de bienes transformados'],
            ['num_val'=>'08','text_val'=>'','label'=>'Importación'],
            ['num_val'=>'09','text_val'=>'','label'=>'Exportación'],
            ['num_val'=>'18','text_val'=>'','label'=>'Traslado emisor itinerante cp'],
            ['num_val'=>'13','text_val'=>'','label'=>'Otros'],
        ];
    }

    public static function getDocumentoRelacionado(){
        return [
            ['num_val'=>'01','text_val'=>'','label'=>'Factura'],
            ['num_val'=>'03','text_val'=>'','label'=>'Boleta de Venta'],
            ['num_val'=>'04','text_val'=>'','label'=>'Liquidación de Compra'],
            ['num_val'=>'09','text_val'=>'','label'=>'Guía de Remisión Remitente'],
            ['num_val'=>'12','text_val'=>'','label'=>'Ticket o cinta emitido por máquina registradora'],
            ['num_val'=>'48','text_val'=>'','label'=>'Comprobante de Operaciones – Ley N° 29972'],
            ['num_val'=>'49','text_val'=>'','label'=>'Constancia de Depósito - IVAP (Ley 28211)'],
            ['num_val'=>'50','text_val'=>'','label'=>'Declaración Aduanera de Mercancías'],
            //['num_val'=>'52','text_val'=>'','label'=>'Declaración Simplificada (DS)'],
            //['num_val'=>'71','text_val'=>'','label'=>'Resolución de Adjudicación de bienes – SUNAT'],
            //['num_val'=>'72','text_val'=>'','label'=>'Resolución de Comiso de bienes – SUNAT'],
            //['num_val'=>'73','text_val'=>'','label'=>'Guía de Transporte Forestal o de Fauna - SERFOR'],
            //['num_val'=>'74','text_val'=>'','label'=>'Guía de Tránsito – SUCAMEC'],
            //['num_val'=>'76','text_val'=>'','label'=>'Autorización para manejo y recojo de residuos sólidos peligrosos y no peligrosos'],
            //['num_val'=>'77','text_val'=>'','label'=>'Certificado fitosanitario la movilización de plantas, productos vegetales, y otros artículos reglamentados'],
            //['num_val'=>'78','text_val'=>'','label'=>'Registro Único de Usuarios y Transportistas de Alcohol Etílico'],
            ['num_val'=>'80','text_val'=>'','label'=>'Constancia de Depósito – Detracción'],
            //['num_val'=>'81','text_val'=>'','label'=>'Código de autorización emitida por el SCOP'],
            ['num_val'=>'-30','text_val'=>'','label'=>'Otros'],
        ];
    }
}



