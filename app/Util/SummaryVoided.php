<?php

namespace sysfact\Util;

class SummaryVoided {

	private $ventas;
	private $emisor;
	private $nombre_fichero;
	private $fecha_emision;
	private $correlativo;

    public function __construct($ventas) {
        $this->ventas=$ventas;
    }

	public function generar_xml(){

		$ventas=$this->ventas;

        $documento['idresumen']='RC-'.date('Ymd').'-'.$this->correlativo;
        $documento['fecha_emision_boletas']=date('Y-m-d',strtotime($this->fecha_emision));
        $documento['fecha_generacion_resumen']=date('Y-m-d');

		$items=[];
		for($i=0;$i<count($ventas); $i++){
            $items[$i]['num_item']=$i+1;
            $items[$i]['codigo_tipo_documento']=$ventas[$i]['facturacion']['codigo_tipo_documento'];
            $items[$i]['codigo_moneda']=$ventas[$i]['facturacion']['codigo_moneda'];
            $items[$i]['serie']=$ventas[$i]['facturacion']['serie'];
            $items[$i]['correlativo']=$ventas[$i]['facturacion']['correlativo'];
            $items[$i]['num_doc_relacionado']=$ventas[$i]['facturacion']['num_doc_relacionado'];
            $items[$i]['tipo_doc_relacionado']=$ventas[$i]['facturacion']['tipo_doc_relacionado'];
            $items[$i]['total_gravadas']=$ventas[$i]['facturacion']['total_gravadas'];
            $items[$i]['total_exoneradas']=$ventas[$i]['facturacion']['total_exoneradas'];
            $items[$i]['total_inafectas']=$ventas[$i]['facturacion']['total_inafectas'];
            $items[$i]['total_gravadas']=$ventas[$i]['facturacion']['total_gravadas'];
            $items[$i]['total_gratuitas']=$ventas[$i]['facturacion']['total_gratuitas'];
            $items[$i]['igv']=$ventas[$i]['facturacion']['igv'];
            $items[$i]['ruc']=$ventas[$i]['cliente']['num_documento'];
            $items[$i]['cliente_tipo_documento']=$ventas[$i]['cliente']['tipo_documento'];
            $items[$i]['total_venta']=$ventas[$i]['total_venta'];

        }

		$view = view('sunat/docs/summary_voided',['documento'=>$documento,'emisor'=>$this->emisor,'items'=>$items]);
		return $view->render();

	}

	public function setVenta($venta){
		$this->venta=$venta;
	}

	public function setEmisor($emisor){
		$this->emisor=$emisor;
	}

    public function setCorrelativo($correlativo){
        $this->correlativo=$correlativo;
    }

    public function getCorrelativo(){
        return $this->correlativo;
    }

	public function setNombreFichero($nombre){
		$this->nombre_fichero=$nombre;
	}

	public function setFechaEmision($fecha){
		$this->fecha_emision=$fecha;
	}

	public function getVenta(){
		return $this->ventas;
	}

	public function getEmisor(){
		return $this->emisor;
	}

	public function getNombreFichero(){
		return $this->nombre_fichero;
	}

	public function getFechaEmision(){
		return $this->fecha_emision;
	}
}

?>