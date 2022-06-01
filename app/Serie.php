<?php
/**
 * Created by PhpStorm.
 * User: Luciano
 * Date: 16/02/2022
 * Time: 09:53
 */

namespace sysfact;


use sysfact\Http\Controllers\Helpers\MainHelper;

class Serie
{
    public $serie_boleta = 'B001';
    public $serie_factura = 'F001';
    public $serie_nota_credito_boleta = 'BC01';
    public $serie_nota_credito_factura = 'FC01';
    public $serie_nota_debito_boleta = 'BD01';
    public $serie_nota_debito_factura = 'FD01';
    public $serie_guia_remision = 'T001';
    public $serie_recibo = 'REC';

    public function __construct()
    {
        $this->setSeries();
    }

    public function setSeries(){
        $config = MainHelper::configuracion('series');
        if($config){
            $serie = json_decode($config, true);
            $this->serie_boleta = $serie['boleta'] ?? 'B001';
            $this->serie_factura = $serie['factura'] ?? 'F001';
            $this->serie_nota_credito_boleta = $serie['nc_boletas'] ?? 'BC01';
            $this->serie_nota_credito_factura = $serie['nc_facturas'] ?? 'FC01';
            $this->serie_nota_debito_boleta = $serie['nd_boletas'] ?? 'BD01';
            $this->serie_nota_debito_factura = $serie['nd_facturas'] ?? 'FD01';
            $this->serie_guia_remision = $serie['guia_remision'] ?? 'T001';
            $this->serie_recibo = $serie['recibo'] ?? 'REC';
        }

    }

    public function getSeries(){
        return [
            'boleta'=>$this->serie_boleta,
            'factura'=>$this->serie_factura,
            'nota_credito_boleta'=>$this->serie_nota_credito_boleta,
            'nota_credito_factura'=>$this->serie_nota_credito_factura,
            'nota_debito_boleta'=>$this->serie_nota_debito_boleta,
            'nota_debito_factura'=>$this->serie_nota_debito_factura,
            'guia'=>$this->serie_guia_remision,
            'recibo'=>$this->serie_recibo
        ];
    }

}