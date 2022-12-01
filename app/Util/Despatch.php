<?php

namespace sysfact\Util;

class Despatch {

	private $guia;
	private $nombre_fichero;

    public function __construct($guia) {
        $this->guia=$guia;
    }

	public function generar_xml($render = true){

		$documento=$this->guia;
		$usuario=$this->guia->cliente;
		$emisor=$this->guia->emisor;
		$detalle=$this->guia->productos;

		$datos_adicionales=json_decode($documento->guia_datos_adicionales,TRUE);

		$i=1;

		foreach ($detalle as $item){

			$medida=explode('/',$item->unidad_medida);

			/*DATOS DE ITEMS*/

			$item->num_item=$i;
			$item->codigo=$item->cod_producto;
			$item->descripcion=$item->nombre.' '.strtoupper($item->detalle->descripcion);
			$item->cantidad=$item->detalle->cantidad;
			$item->codigo_medida=$medida[0];
            $i++;

		}

        $documento->serie_correlativo=$documento->correlativo;
		$documento->fecha_emision=date('Y-m-d', strtotime($documento->fecha_emision));
		$documento->hora_emision=date('H:i:s', strtotime($documento->fecha_emision));
        $documento->tipo_guia='09';
        $documento->peso_bruto=$datos_adicionales['peso'];
        $documento->unidad_medida_peso_bruto='KGM';
        $documento->cantidad_bultos=$datos_adicionales['bultos'];
        $documento->indicador_transbordo_programado='false';
        $documento->codigo_transporte=$datos_adicionales['tipo_transporte']; //01 Publico y 02 privado catalogo 18 sunat
        $documento->tipo_doc_transportista=$datos_adicionales['tipo_doc_transportista'];
        $documento->num_doc_transportista=$datos_adicionales['num_doc_transportista'];
        $documento->razon_social_transportista=$datos_adicionales['razon_social_transportista'];
        $documento->placa_vehiculo=$datos_adicionales['placa_vehiculo'];
        $documento->dni_conductor=$datos_adicionales['dni_conductor'];
        $documento->licencia_conductor=$datos_adicionales['licencia_conductor']??'000000';
        $documento->nombre_conductor=$datos_adicionales['nombre_conductor']??'';
        $documento->apellido_conductor=$datos_adicionales['apellido_conductor']??'';
        $documento->registro_mtc=$datos_adicionales['registro_mtc']??'000000';
        $documento->direccion_llegada=$datos_adicionales['direccion'];
        $documento->ubigeo_direccion_llegada=$datos_adicionales['ubigeo'];
        $documento->motivo_traslado=$datos_adicionales['motivo_traslado'];
        $documento->codigo_traslado=$datos_adicionales['codigo_traslado'];
        $documento->num_doc_relacionado=$datos_adicionales['num_doc_relacionado'];
        $documento->doc_relacionado=$datos_adicionales['doc_relacionado'];
        $documento->fecha_traslado=date('Y-m-d', strtotime($datos_adicionales['fecha_traslado']));

		$usuario->razon_social=$usuario->persona['nombre'];
		$this->nombre_fichero=$emisor->ruc.'-09-'.$documento->correlativo;

        if($render) {
            //$view = view('sunat/docs/despatch', ['documento' => $documento, 'usuario' => $usuario, 'items' => $detalle, 'emisor' => $emisor]);
            $view = view('sunat/docs/despatch_v2', ['documento' => $documento, 'usuario' => $usuario, 'items' => $detalle, 'emisor' => $emisor]);
            return $view->render();
        }

	}

	public function setNombreFichero($nombre){
		$this->nombre_fichero=$nombre;
	}

	public function getNombreFichero(){
		return $this->nombre_fichero;
	}

    public function getVenta(){
        return $this->guia;
    }
}

?>
