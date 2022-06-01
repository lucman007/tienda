<?php

namespace sysfact\Http\Controllers;

use Illuminate\Filesystem\Cache;
use Illuminate\Http\Request;
use sysfact\Opciones;

class OpcionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function obtener_tipo_de_cambio(){

        $json = json_decode(@file_get_contents("https://deperu.com/api/rest/cotizaciondolar.json"),TRUE);

        if($json){
            if(isset($json['Cotizacion'])){
                $cotizacion = $json['Cotizacion'][0];
                $opcion['tipo_cambio_fecha'] = date('Y-m-d');
                $opcion['tipo_cambio_compra'] = $cotizacion['Compra']==''?1:$cotizacion['Compra'];
                $opcion['tipo_cambio_venta'] = $cotizacion['Venta']==''?1:$cotizacion['Venta'];
                return $opcion;
            }

            return false;

        }

        $json = @file_get_contents("https://www.sunat.gob.pe/a/txt/tipoCambio.txt");
        if($json){
            $explode = explode('|', $json);
            $opcion['tipo_cambio_fecha'] = date('Y-m-d');
            $opcion['tipo_cambio_compra'] = $explode[1];
            $opcion['tipo_cambio_venta'] = $explode[2];
            return $opcion;
        }

        return false;



        /*$tipo_de_cambio=Opciones::where('nombre_opcion','tipo_cambio')
            ->whereBetween('fecha',[date('Y-m-d').' 00:00:00',date('Y-m-d').' 23:59:59'])
            ->first();

        if(!$tipo_de_cambio){
            try{
                $json = json_decode(file_get_contents("https://www.deperu.com/api/rest/cotizaciondolar.json"),TRUE);
                $opcion = new Opciones();
                $opcion->fecha=date('Y-m-d H:i:m');
                $opcion->nombre_opcion='tipo_cambio';
                $opcion->valor_json=json_encode($json['Cotizacion'][0]);
                $opcion->save();
                $tipo_de_cambio['valor_json']=$opcion->valor_json;
            } catch (\Exception $e){
                return ['Compra'=>'-','Venta'=>'-'];
            }

        }*/

        /*if(!$tipo_de_cambio){
            try{
                $json = file_get_contents("https://www.sunat.gob.pe/a/txt/tipoCambio.txt");
                $explode = explode('|', $json);
                $cotizacion= ['Compra'=>$explode[1],'Venta'=>$explode[2]];

                $opcion = new Opciones();
                $opcion->fecha=date('Y-m-d H:i:m');
                $opcion->nombre_opcion='tipo_cambio';

                $opcion->valor_json=json_encode($cotizacion);
                $opcion->save();
                $tipo_de_cambio['valor_json']=$opcion->valor_json;
            } catch (\Exception $e){
                return ['Compra'=>'-','Venta'=>'-'];
            }

        }*/

        //return json_decode($tipo_de_cambio['valor_json'], TRUE);
    }

}
