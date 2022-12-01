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
            ['num_val'=>'14','text_val'=>'','label'=>'Venta sujeta a confirmacion del comprador'],
            ['num_val'=>'02','text_val'=>'','label'=>'Compra'],
            ['num_val'=>'04','text_val'=>'','label'=>'Traslado entre establecimientos de la misma empresa'],
            ['num_val'=>'18','text_val'=>'','label'=>'Traslado emisor itinerante cp'],
            ['num_val'=>'08','text_val'=>'','label'=>'Importación'],
            ['num_val'=>'09','text_val'=>'','label'=>'Exportación'],
            ['num_val'=>'13','text_val'=>'','label'=>'Otros'],
        ];
    }
}