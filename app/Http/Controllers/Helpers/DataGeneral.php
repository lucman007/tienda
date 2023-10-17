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
        ];
    }
}
