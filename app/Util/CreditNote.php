<?php

namespace sysfact\Util;
use Luecano\NumeroALetras\NumeroALetras;

class CreditNote {

    private $venta;
    private $nombre_fichero;

    public function __construct($venta) {
        $this->venta=$venta;
    }

    public function generar_xml(){

        $documento=$this->venta;
        $usuario=$this->venta->cliente;
        $emisor=$this->venta->emisor;
        $detalle=$this->venta->productos;

        $i=1;

        foreach ($detalle as $item){

            $medida=explode('/',$item->unidad_medida);

            /*DATOS DE ITEMS*/

            $item->num_item=$i;
            $item->codigo=$item->cod_producto;
            $item->descripcion=$item->nombre.' '.strtoupper($item->detalle->descripcion);
            $item->cantidad=$item->detalle->cantidad;
            $item->unidad_medida=$medida[1];
            $item->precio=$item->detalle->monto;
            $item->descuento=$item->detalle->descuento;
            $item->total_item=$item->detalle->total;

            /*DATOS DE ITEMS SOLO PARA XML*/

            $item->valor_venta_unitario_por_item=$item->detalle->subtotal; //PRECIO * CANTIDAD  SIN IGV
            $item->precio_venta_unitario_por_item=$item->detalle->total; //PRECIO * CANTIDAD  CON IGV

            $porcentaje_descuento=round($item->detalle->porcentaje_descuento/100,2);

            if($item->detalle->afectacion=='10'){
                //VERIFICAMOS SI EL PRECIO DE PRODUCTO INCLUYE O NO EL IGV
                if($documento->igv_incluido){
                    // Si incluir igv es true
                    $item->valor_venta_bruto_unitario = round($item->detalle->monto/1.18,2);//PRECIO UNITARIO DE PRODUCTO SIN IGV
                    $item->base_descuento=round($item->detalle->monto*$item->detalle->cantidad/1.18,2);
                    $item->valor_referencial=round($item->detalle->monto-$item->descuento,2);
                } else{
                    // Si incluir igv es false
                    $item->valor_venta_bruto_unitario = $item->detalle->monto;
                    $item->base_descuento=round($item->detalle->monto*$item->detalle->cantidad,2);
                    $item->valor_referencial=round(($item->detalle->monto-($item->detalle->monto*$porcentaje_descuento))*1.18,2);
                }
            } else{
                // Si tipo de afectación es diferente de gravado
                $item->valor_venta_bruto_unitario = $item->detalle->monto;
                $item->base_descuento=round($item->detalle->monto*$item->detalle->cantidad,2);
                $item->valor_referencial=$item->detalle->monto;
            }

            $item->tipo_afectacion_igv=$item->detalle->afectacion;// Catálago N° 07: Tipo de afectacion del IGV
            $item->codigo_medida=$medida[0];
            $item->porcentaje_igv=18;
            $item->tipo_precio_venta_unitario_por_item='02';//Catálogo N° 16: Tipo de precio de venta unitario (02 si es venta gratuita)
            $item->igv=$item->detalle->igv;

            switch ($item->detalle->afectacion){
                case '10':
                    $item->tax_id='S';
                    $item->tax_code='1000';
                    $item->tax_siglas='IGV';
                    $item->tax_name_code='VAT';
                    $item->tipo_precio_venta_unitario_por_item='01';
                    $item->igv=$item->detalle->igv;
                    break;
                case '11':
                case '12':
                case '13':
                case '14':
                case '15':
                case '16':
                    $item->tax_id='Z';
                    $item->tax_code='9996';
                    $item->tax_siglas='GRA';
                    $item->tax_name_code='FRE';
                    $item->tipo_precio_venta_unitario_por_item='02';
                    $item->igv=$item->detalle->subtotal*0.18;
                    break;
                case '20':
                    $item->tax_id='E';
                    $item->tax_code='9997';
                    $item->tax_siglas='EXO';
                    $item->tax_name_code='VAT';
                    $item->tipo_precio_venta_unitario_por_item='01';
                    $item->igv=$item->detalle->igv;
                    break;
                case '21':
                    $item->tax_id='Z';
                    $item->tax_code='9996';
                    $item->tax_siglas='GRA';
                    $item->tax_name_code='FRE';
                    $item->tipo_precio_venta_unitario_por_item='02';
                    $item->igv=$item->detalle->igv;
                    break;
                case '30':
                    $item->tax_id='O';
                    $item->tax_code='9998';
                    $item->tax_siglas='INA';
                    $item->tax_name_code='FRE';
                    $item->tipo_precio_venta_unitario_por_item='01';
                    $item->igv=$item->detalle->igv;
                    break;
                case '31':
                case '32':
                case '33':
                case '34':
                case '35':
                case '36':
                    $item->tax_id='Z';
                    $item->tax_code='9996';
                    $item->tax_siglas='GRA';
                    $item->tax_name_code='FRE';
                    $item->tipo_precio_venta_unitario_por_item='02';
                    $item->igv=$item->detalle->igv;
                    break;

            }

            $i++;

        }

        switch($documento->facturacion->tipo_nota_electronica){
            case 01:
                $documento->leyenda_nota = 'Anulación de la operación';
                break;
            case 02:
                $documento->leyenda_nota = 'Anulación por error en el RUC';
                break;
            case 03:
                $documento->leyenda_nota = 'Corrección por error en la descripción';
                break;
            case 04:
                $documento->leyenda_nota = 'Descuento global';
                break;
            case 05:
                $documento->leyenda_nota = 'Descuento por ítem';
                break;
            case 06:
                $documento->leyenda_nota = 'Devolución total';
                break;
            case 07:
                $documento->leyenda_nota = 'Devolución por ítem';
                break;
            case '08':
                $documento->leyenda_nota = 'Bonificación';
                break;
            case '09':
                $documento->leyenda_nota = 'Disminución en el valor';
                break;
            case '10':
                $documento->leyenda_nota = 'Otros Conceptos';
                break;
        }

        if($documento->facturacion->codigo_moneda=='PEN'){
            $moneda_letras='SOLES';
        } else{
            $moneda_letras='DÓLARES';
        }

        $documento->codigo_tipo_factura='0101'; //Catálogo N° 51: Código de tipo de factura
        $documento->serie=$documento->facturacion->serie;
        $documento->correlativo=$documento->facturacion->correlativo;
        $documento->fecha_emision=date('Y-m-d', strtotime($documento->fecha));
        $documento->hora_emision=date('H:i:s', strtotime($documento->fecha));
        $documento->fecha_vencimiento=date('Y-m-d', strtotime($documento->fecha_vencimiento));
        $documento->codigo_tipo_documento=$documento->facturacion->codigo_tipo_documento; //Catálago N° 01: Código tipo de documento
        //Generar leyenda
        $documento->leyenda=NumeroALetras::convert($documento->total_venta, $moneda_letras,true);
        $documento->codigo_leyenda=1000;
        $documento->codigo_moneda=$documento->facturacion->codigo_moneda;
        $documento->tipo_guia='09';
        $documento->total_impuestos=$documento->facturacion->igv+$documento->facturacion->isc+$documento->facturacion->ivap;


        $usuario->razon_social=$usuario->persona['nombre'];
        $this->nombre_fichero=$emisor->ruc.'-'.$documento->codigo_tipo_documento.'-'.$documento->serie.'-'.$documento->correlativo;

        $view = view('sunat/docs/creditnote',['documento'=>$documento,'usuario'=>$usuario,'items'=>$detalle,'emisor'=>$emisor]);
        return $view->render();

    }

    public function setNombreFichero($nombre){
        $this->nombre_fichero=$nombre;
    }

    public function getNombreFichero(){
        return $this->nombre_fichero;
    }

    public function getVenta(){
        return $this->venta;
    }
}

?>