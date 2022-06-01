<?php

namespace sysfact\Util;

class Summary {

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

		$ventas->idresumen='RC-'.date('Ymd').'-'.$this->correlativo;
		$ventas->fecha_emision_boletas=$this->fecha_emision;
		$ventas->fecha_generacion_resumen=date('Y-m-d');

		$i=1;
		foreach ($ventas as $venta){
			$venta->ruc=$venta->cliente['num_documento'];
			$venta->num_item=$i;
			$i++;
		}

		$view = view('sunat/docs/summary',['documento'=>$ventas,'emisor'=>$this->emisor,'items'=>$ventas]);
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