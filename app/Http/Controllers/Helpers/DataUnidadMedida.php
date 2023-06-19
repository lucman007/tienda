<?php
/**
 * Created by PhpStorm.
 * User: Luciano
 * Date: 9/08/2022
 * Time: 15:00
 */

namespace sysfact\Http\Controllers\Helpers;


class DataUnidadMedida
{
    public static function getUnidadMedida(){
        return [
            ['num_val'=>'1','text_val'=>'NIU/UND','label'=>'Unidad'],
            ['num_val'=>'2','text_val'=>'MTR/M','label'=>'Metro'],
            ['num_val'=>'3','text_val'=>'MTR/ML','label'=>'Metro lineal'],
            ['num_val'=>'4','text_val'=>'RO/ROL','label'=>'Rollo'],
            ['num_val'=>'5','text_val'=>'KGM/KG','label'=>'Kilogramo'],
            ['num_val'=>'6','text_val'=>'GRM/G','label'=>'Gramo'],
            ['num_val'=>'7','text_val'=>'LTR/L','label'=>'Litro'],
            ['num_val'=>'8','text_val'=>'GLL/GL','label'=>'Galón'],
            ['num_val'=>'9','text_val'=>'NIU/PZA','label'=>'Pieza'],
            ['num_val'=>'10','text_val'=>'MTK/M2','label'=>'Metro cuadrado'],
            ['num_val'=>'11','text_val'=>'MTQ/M3','label'=>'Metro cúbico'],
            ['num_val'=>'12','text_val'=>'PK/PQ','label'=>'Paquete'],
            ['num_val'=>'13','text_val'=>'BX/CJ','label'=>'Caja'],
            ['num_val'=>'14','text_val'=>'NIU/JG','label'=>'Juego'],
            ['num_val'=>'19','text_val'=>'NIU/KT','label'=>'Kit'],
            ['num_val'=>'15','text_val'=>'NIU/PR','label'=>'Par'],
            ['num_val'=>'16','text_val'=>'BE/BE','label'=>'Fardo'],
            ['num_val'=>'17','text_val'=>'BG/BG','label'=>'Bolsa'],
            ['num_val'=>'18','text_val'=>'BJ/BJ','label'=>'Balde'],
        ];
    }
}
