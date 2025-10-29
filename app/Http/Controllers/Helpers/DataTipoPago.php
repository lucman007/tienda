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
            ['clasificacion'=>'1','num_val'=>'1','text_val'=>'efectivo','label'=>'Efectivo','abreviatura'=>'Efect.'],
            ['clasificacion'=>'2','num_val'=>'3','text_val'=>'visa','label'=>'Tarj. visa','abreviatura'=>'Visa'],
            ['clasificacion'=>'2','num_val'=>'7','text_val'=>'mastercard','label'=>'Tarj. Mastercard','abreviatura'=>'MastCard'],
            ['clasificacion'=>'1','num_val'=>'5','text_val'=>'yape','label'=>'Yape','abreviatura'=>'Yape'],
            ['clasificacion'=>'1','num_val'=>'6','text_val'=>'plin','label'=>'Plin','abreviatura'=>'Plin'],
            ['clasificacion'=>'1','num_val'=>'9','text_val'=>'transferencia','label'=>'Transferencia','abreviatura'=>'Transf.'],
            ['clasificacion'=>'3','num_val'=>'4','text_val'=>'fraccionado','label'=>'Fraccionado','abreviatura'=>'Frac.'],
            ['clasificacion'=>'1','num_val'=>'2','text_val'=>'credito','label'=>'Crédito','abreviatura'=>'Créd.'],
            ['clasificacion'=>'1','num_val'=>'8','text_val'=>'otros','label'=>'Otros','abreviatura'=>'Otros']
        ];
    }
}