<?php
/**
 * Created by PhpStorm.
 * User: Luciano
 * Date: 31/12/2021
 * Time: 10:29
 */

namespace sysfact\Http\Controllers\Helpers;


final class DatosComprobantes
{
    const SERIE_BOLETA = 'B001';
    const SERIE_FACTURA = 'F001';
    const SERIE_NOTA_CREDITO_BOLETA = 'BC01';
    const SERIE_NOTA_CREDITO_FACTURA = 'FC01';
    const SERIE_NOTA_DEDITO_BOLETA = 'BD01';
    const SERIE_NOTA_DEDITO_FACTURA = 'FD01';
    const SERIE_GUIA_REMISION = 'T001';
    const SERIE_RECIBO = 'REC';

    public static function getSeries(){
        return [
            'boleta'=>'B001',
            'factura'=>'F001',
            'nota_credito_boleta'=>'BC01',
            'nota_credito_factura'=>'FC01',
            'nota_debito_boleta'=>'BD01',
            'nota_debito_factura'=>'FD01',
            'guia'=>'T001',
            'recibo'=>'REC'
        ];
    }


}