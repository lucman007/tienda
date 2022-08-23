<?php
/**
 * Created by PhpStorm.
 * User: Luciano
 * Date: 9/08/2022
 * Time: 15:00
 */

namespace sysfact\Http\Controllers\Helpers;


class DataTipoPago
{
    public static function getTipoPago(){
        return [
            ['num_val'=>'1','text_val'=>'efectivo','label'=>'Efectivo'],
            ['num_val'=>'3','text_val'=>'visa','label'=>'Tarjeta visa'],
            ['num_val'=>'7','text_val'=>'mastercard','label'=>'Tarjeta Mastercard'],
            ['num_val'=>'5','text_val'=>'yape','label'=>'Yape'],
            ['num_val'=>'6','text_val'=>'plin','label'=>'Plin'],
            ['num_val'=>'9','text_val'=>'transferencia','label'=>'Transferencia'],
            ['num_val'=>'4','text_val'=>'otros','label'=>'Fraccionado'],
            ['num_val'=>'2','text_val'=>'credito','label'=>'Crédito'],
            ['num_val'=>'8','text_val'=>'otros','label'=>'Otros'],
        ];
    }
}