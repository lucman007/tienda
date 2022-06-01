<?php

namespace sysfact\Util;

class Voided {

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

		$documento['idanulacion']='RA-'.date('Ymd').'-'.$this->correlativo;
		$documento['fecha_emision_documentos']=date('Y-m-d', strtotime($this->fecha_emision));
		$documento['fecha_generacion_anulacion']=date('Y-m-d');


		$items=[];
		for($i=0;$i<count($ventas); $i++){
			$items[$i]['num_item']=$i+1;
			$items[$i]['codigo_tipo_documento']=$ventas[$i]['facturacion']['codigo_tipo_documento'];
            $items[$i]['serie']=$ventas[$i]['facturacion']['serie'];
            $items[$i]['correlativo']=$ventas[$i]['facturacion']['correlativo'];
            $items[$i]['motivo_baja']=$ventas[$i]['motivo_baja'];
		}

		$view = view('sunat/docs/voided',['documento'=>$documento,'emisor'=>$this->emisor,'items'=>$items]);
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