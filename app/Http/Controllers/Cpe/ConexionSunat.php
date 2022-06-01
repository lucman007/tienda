<?php
/**
 * Created by PhpStorm.
 * User: Luciano
 * Date: 21/03/2020
 * Time: 19:56
 */

namespace sysfact\Http\Controllers\Cpe;

use sysfact\libraries\sunat\SunatServices;

class ConexionSunat
{

    private $credenciales=['usuario'=>'20526679416MODDATOS','clave'=>'MODDATOS','esProduccion'=>false];
    
    public function __construct($credenciales){

        if($credenciales['esProduccion']){
            $this->credenciales = $credenciales;
        }
        
    }

    public function obtenerConexion($documento){

        if($this->credenciales['esProduccion']){

            switch ($documento){
                case '01':
                case '03':
                case '07':
                case '08':
                    return [
                        'endpoint'=>SunatServices::FACT_PRODUCCION,
                        'service'=>'sendBill',
                        'credenciales'=>$this->credenciales
                    ];
                    break;
                case '09':
                    return [
                        'endpoint'=>SunatServices::GUIA_PRODUCCION,
                        'service'=>'sendBill',
                        'credenciales'=>$this->credenciales
                    ];
                    break;
                case 'sendSummary':
                    return [
                        'endpoint'=>SunatServices::FACT_PRODUCCION,
                        'service'=>'sendSummary',
                        'credenciales'=>$this->credenciales
                    ];
                    break;
                case 'status':
                    return [
                        'endpoint'=>SunatServices::FACT_PRODUCCION,
                        'service'=>'getStatus',
                        'credenciales'=>$this->credenciales
                    ];
                    break;
                case 'getStatus':
                    return [
                        'endpoint'=>SunatServices::FACT_CONSULTA_CDR,
                        'service'=>'getStatus',
                        'credenciales'=>$this->credenciales
                    ];
                    break;
                case 'getStatusCdr':
                    return [
                        'endpoint'=>SunatServices::FACT_CONSULTA_CDR,
                        'service'=>'getStatusCdr',
                        'credenciales'=>$this->credenciales
                    ];
                    break;
            }

        } else{

            switch ($documento){
                case '01':
                case '03':
                case '07':
                case '08':
                    return [
                        'endpoint'=>SunatServices::FACT_BETA,
                        'service'=>'sendBill',
                        'credenciales'=>$this->credenciales
                    ];
                    break;
                case '09':
                    return [
                        'endpoint'=>SunatServices::GUIA_BETA,
                        'service'=>'sendBill',
                        'credenciales'=>$this->credenciales
                    ];
                    break;
                case 'sendSummary':
                    return [
                        'endpoint'=>SunatServices::FACT_BETA,
                        'service'=>'sendSummary',
                        'credenciales'=>$this->credenciales
                    ];
                    break;
                case 'status':
                    return [
                        'endpoint'=>SunatServices::FACT_BETA,
                        'service'=>'getStatus',
                        'credenciales'=>$this->credenciales
                    ];
                    break;
                case 'getStatus':
                    return [
                        'endpoint'=>SunatServices::FACT_CONSULTA_CDR,
                        'service'=>'getStatus',
                        'credenciales'=>$this->credenciales
                    ];
                    break;
                case 'getStatusCdr':
                    return [
                        'endpoint'=>SunatServices::FACT_CONSULTA_CDR,
                        'service'=>'getStatusCdr',
                        'credenciales'=>$this->credenciales
                    ];
                    break;
            }

        }

    }

}