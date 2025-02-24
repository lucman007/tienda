<?php

namespace sysfact;


use sysfact\Http\Controllers\Helpers\MainHelper;

class Emisor
{
    public $razon_social;

    /* @var $nombre_comercial | String
     * para no imprimirla en el xml poner a false.
     */
    public $nombre_comercial;
    public $ruc;

    /* @var $tipo_documento | String
     * Revisar catálogo N° 6 Códigos de tipo de documento de identidad
     * RUC: 6 | DNI: 1
     */
    public $tipo_documento='6';
    public $ubigeo;
    public $codigo_establecimiento;
    public $direccion;
    public $urbanizacion;
    public $departamento;
    public $provincia;
    public $distrito;
    public $pais='PERU';
    public $codigo_pais='PE';
    public $direccion_resumida;
    public $telefono_1;
    public $telefono_2;
    public $email;

    /* @var $nombre_publicitario | String
     * para no imprimirla en el pdf poner a null.
     * @var $texto_publicitario | String
     * para no imprimirla en el pdf poner a null.
     */
    public $nombre_publicitario;
    public $texto_publicitario;
    public $logo;

    //Cuentas
    public $cuenta_detraccion;
    public $cuenta_1;
    public $cuenta_2;

    public $cuentas;


    public function __construct()
    {
        $this->emisor_model();
    }

    public function emisor_model(){
        $config = MainHelper::configuracion('emisor');
        $cuentas = json_decode(MainHelper::configuracion('cuentas'), true);
        $logo = MainHelper::configuracion('logo_comprobantes');

        if($config){
            $emisor = json_decode($config, true);

            if (isset($emisor['codigo_establecimiento'])) {
                $codigo_establecimiento = empty($emisor['codigo_establecimiento']) ? '0000' : $emisor['codigo_establecimiento'];
            } else {
                $codigo_establecimiento = '0000';
            }

            $this->razon_social = $emisor['razon_social'];
            $this->nombre_comercial = $emisor['nombre_comercial']==''?false:$emisor['nombre_comercial'];
            $this->ruc = $emisor['ruc'];
            $this->codigo_establecimiento = $codigo_establecimiento;
            $this->ubigeo = $emisor['ubigeo'];
            $this->direccion = $emisor['direccion'];
            $this->urbanizacion = $emisor['urbanizacion'];
            $this->departamento = $emisor['departamento'];
            $this->provincia = $emisor['provincia'];
            $this->distrito = $emisor['distrito'];
            $this->direccion_resumida = $emisor['direccion_resumida'];
            $this->telefono_1 = $emisor['telefono_1'];
            $this->telefono_2 = $emisor['telefono_2'];
            $this->email = $emisor['email'];
            $this->nombre_publicitario = $emisor['nombre_publicitario'];
            $this->texto_publicitario = $emisor['texto_publicitario'];
            $this->logo = $logo==''?false:$logo;
            $this->cuentas = $cuentas;
            $this->cuenta_detracciones = $emisor['cuenta_detracciones'];
            $this->cuenta_1 = $emisor['cuenta_1'];
            $this->cuenta_2 = $emisor['cuenta_2'];
        }

    }


}

?>