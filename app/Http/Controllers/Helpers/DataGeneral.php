<?php
/**
 * Created by PhpStorm.
 * User: Luciano
 * Date: 9/08/2022
 * Time: 15:00
 */

namespace sysfact\Http\Controllers\Helpers;


class DataGeneral
{
    public static function getBancos(){
        return [
            ['num_val'=>'1','text_val'=>'','label'=>'BCP'],
            ['num_val'=>'2','text_val'=>'','label'=>'BBVA'],
            ['num_val'=>'3','text_val'=>'','label'=>'INTERBANK'],
            ['num_val'=>'4','text_val'=>'','label'=>'SCOTIABANK'],
            ['num_val'=>'5','text_val'=>'','label'=>'PICHINCHA'],
            ['num_val'=>'6','text_val'=>'','label'=>'BANBIF'],
            ['num_val'=>'7','text_val'=>'','label'=>'BANCO DE LA NACIÓN'],
            ['num_val'=>'8','text_val'=>'','label'=>'CUENTA DE EXPORTACIÓN'],
        ];
    }

    public static function getCodigoPais(){
        return [
            ['num_val'=>'+54','text_val'=>'','label'=>'Argentina +54'],
            ['num_val'=>'+591','text_val'=>'','label'=>'Bolivia +591'],
            ['num_val'=>'+593','text_val'=>'','label'=>'Ecuador +593'],
            ['num_val'=>'+1','text_val'=>'','label'=>'EEUU +1'],
            ['num_val'=>'+34','text_val'=>'','label'=>'España +34'],
            ['num_val'=>'+56','text_val'=>'','label'=>'Chile +56'],
            ['num_val'=>'+57','text_val'=>'','label'=>'Colombia +57'],
            ['num_val'=>'+52','text_val'=>'','label'=>'México +52'],
            ['num_val'=>'+51','text_val'=>'','label'=>'Perú +51'],
        ];
    }
}
