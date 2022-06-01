<?php

namespace sysfact\Http\Controllers;

use Illuminate\Http\Request;
use sysfact\Emisor;
use sysfact\Venta;

class ConsultaController extends Controller
{
    public function index(){
        return view('consulta/index');
    }

    public function obtener_documento(Request $request){

        $correlativo=str_pad($request->correlativo,8,'0',STR_PAD_LEFT);

        $venta=Venta::where('total_venta',$request->total)
            ->whereHas('facturacion', function($query) use($request,$correlativo) {
                $query->where('serie',$request->serie)
                    ->where('correlativo',$correlativo)
                    ->where('codigo_tipo_documento',$request->tipo_documento)
                    ->where('fecha','LIKE',$request->fecha.'%');
            })
            ->first();

        $emisor=new Emisor();
        $nombre_fichero=$emisor->ruc.'-'.$venta['facturacion']['codigo_tipo_documento'].'-'.$venta['facturacion']['serie'].'-'.$venta['facturacion']['correlativo'];

        if($venta){
            return json_encode(['mostrar'=>1,'nombre_fichero'=>$nombre_fichero]);
        } else{
            return json_encode(['mostrar'=>0,'nombre_fichero'=>'']);
        }

    }

    public function descargarArchivo($file){

        $extension=explode('.',$file)[1];
        if($extension=='pdf'){
            $pathtoFile = storage_path().'/app/sunat/pdf/'.$file;
            return response()->download($pathtoFile);
        } else if($extension=='xml'){
            $pathtoFile = storage_path().'/app/sunat/xml/'.$file;
            return response()->download($pathtoFile);
        }

        return null;

    }

    public function descargar_comprobante($tipo,$consulta){

        //2060284886903B001000000021102202230-00
        $ruc_emisor = substr($consulta,0,11);
        $emisor = new Emisor();
        if($emisor->ruc == $ruc_emisor){

            $tipo_documento = substr($consulta,11,2);
            $serie = substr($consulta,13,4);
            $correlativo = substr($consulta,17,8);
            $fecha =  substr($consulta,25,8);
            $total = substr($consulta,33,5);

            $total = str_replace('-','.',$total);

            $dia = substr($fecha,0,2);
            $mes = substr($fecha,2,2);
            $año = substr($fecha,4,4);

            $fecha = $año.'-'.$mes.'-'.$dia;

            $venta=Venta::where('total_venta',$total)
                ->whereHas('facturacion', function($query) use($correlativo, $serie, $tipo_documento, $fecha) {
                    $query->where('serie',$serie)
                        ->where('correlativo',$correlativo)
                        ->where('codigo_tipo_documento',$tipo_documento)
                        ->where('fecha','LIKE',$fecha.'%');
                })
                ->first();
            $nombre_fichero=$emisor->ruc.'-'.$venta['facturacion']['codigo_tipo_documento'].'-'.$venta['facturacion']['serie'].'-'.$venta['facturacion']['correlativo'];
            //return $ruc_emisor.'-'.$tipo_documento.'-'.$serie.'-'.$correlativo.'-'.$fecha.'-'.$total;
            if($venta){
                return response()->download(storage_path('/app/sunat/'.$tipo.'/').$nombre_fichero.'.'.$tipo);
            } else {
                return redirect('https://www.google.com');
            }

        } else {
            return 'El link es incorrecto o ha expirado';
        }


    }

    /*public function printFile($file){
        $fromFile = storage_path().'/app/sunat/pdf/'.$file;
        return response()->file($fromFile);
    }*/

}
