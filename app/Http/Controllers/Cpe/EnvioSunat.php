<?php

namespace sysfact\Http\Controllers\Cpe;

use Illuminate\Http\Request;
use SoapClient;
use sysfact\Emisor;

class EnvioSunat
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion=$conexion;
    }

    public function enviar($zip){

        $documento = new \DOMDocument();
        $documento->load(app_path()."/libraries/sunat/operation/".$this->conexion['service'].".xml");
        $sendBill=$documento->getElementsByTagName($this->conexion['service'])->item(0);
        $credenciales=$documento->getElementsByTagName('UsernameToken')->item(0);
        $username=$documento->createElement('wsse:Username',$this->conexion['credenciales']['usuario']);
        $password=$documento->createElement('wsse:Password',$this->conexion['credenciales']['clave']);
        $credenciales->appendChild($username);
        $credenciales->appendChild($password);
        $fileName=$documento->createElement('fileName',$zip['nombre'].'.zip');
        $contentFile=$documento->createElement('contentFile',$zip['contenido']);
        $sendBill->appendChild($fileName);
        $sendBill->appendChild($contentFile);
        $request=$documento->saveXML();

        try {

            $cliente = new SoapClient(app_path().'/libraries/sunat/services/billService.wsdl',['trace'=>1]);
            $response=$cliente->__doRequest($request,$this->conexion['endpoint'],$this->conexion['service'],SOAP_1_1,0);
            return $response;

        } catch ( \SoapFault $e ) {
            return $e->getMessage();
        }
    }

    public function factGetStatus($ticket){

        $documento = new \DOMDocument();
        $documento->load(app_path()."/libraries/sunat/operation/".$this->conexion['service'].".xml");
        $getStatus=$documento->getElementsByTagName($this->conexion['service'])->item(0);
        $credenciales=$documento->getElementsByTagName('UsernameToken')->item(0);
        $username=$documento->createElement('wsse:Username',$this->conexion['credenciales']['usuario']);
        $password=$documento->createElement('wsse:Password',$this->conexion['credenciales']['clave']);
        $credenciales->appendChild($username);
        $credenciales->appendChild($password);
        $num_ticket=$documento->createElement('ticket',$ticket);
        $getStatus->appendChild($num_ticket);
        $request=$documento->saveXML();

        try {

            $cliente = new SoapClient(app_path().'/libraries/sunat/services/billService.wsdl',['trace'=>1]);
            $response=$cliente->__doRequest($request,$this->conexion['endpoint'],$this->conexion['service'],SOAP_1_1,0);
            return $response;

        } catch ( \SoapFault $e ) {
            return $e->getMessage();
        }

    }

    public function factGetStatusCdr(Request $req)
    {
        $documento = new \DOMDocument();
        $documento->load(app_path()."/libraries/sunat/operation/".$this->conexion['service'].".xml");
        $getStatusCdr=$documento->getElementsByTagName($this->conexion['service'])->item(0);
        $credenciales=$documento->getElementsByTagName('UsernameToken')->item(0);
        $username=$documento->createElement('wsse:Username',$this->conexion['credenciales']['usuario']);
        $password=$documento->createElement('wsse:Password',$this->conexion['credenciales']['clave']);
        $credenciales->appendChild($username);
        $credenciales->appendChild($password);
        $ruc=$documento->createElement('rucComprobante',$req->ruc_emisor);
        $tipo=$documento->createElement('tipoComprobante',$req->tipo);
        $serie=$documento->createElement('serieComprobante',$req->serie);
        $numero=$documento->createElement('numeroComprobante',$req->numero);
        $getStatusCdr->appendChild($ruc);
        $getStatusCdr->appendChild($tipo);
        $getStatusCdr->appendChild($serie);
        $getStatusCdr->appendChild($numero);
        $request=$documento->saveXML();

        try {

            $cliente = new SoapClient(app_path() . '/libraries/sunat/services/billService.wsdl', ['trace' => 1]);
            $response = $cliente->__doRequest($request, $this->conexion['endpoint'], $this->conexion['service'], SOAP_1_1, 0);
            return $response;

        } catch (\SoapFault $e) {
            return $e->getMessage();
        }

    }

}