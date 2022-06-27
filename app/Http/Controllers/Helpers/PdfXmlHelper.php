<?php
/**
 * Created by PhpStorm.
 * User: Luciano
 * Date: 21/06/2022
 * Time: 17:43
 */

namespace sysfact\Http\Controllers\Helpers;


use sysfact\Http\Controllers\Controller;

class PdfXmlHelper extends Controller
{
    public $file_name;

    public function __construct($file)
    {
        $this->middleware('auth');
        $this->file_name = $file;
    }

    public function read_file(){


        $file_xml=storage_path().'/app/sunat/xml/'.$this->file_name.'.xml';
        //$file_xml=storage_path().'/app/sunat/prueba.xml';
        $file = file_get_contents($file_xml);

        $xml = new \DOMDocument();
        $xml->loadXML($file);
        $xmlArray = $this->xml_to_array($xml);




        //$xml = simplexml_load_string((string) $file);

        //$json = json_encode($xml);

        //$xml = new \SimpleXMLElement($file_xml);

        //$XML = Storage::disk('local')->get('data\XML.xml');
        //$random = collect(json_decode(json_encode((array) simplexml_load_string($XML)), true));

        dd($xmlArray);


    }

    public function xml_to_array($root) {
        $result = array();

        if ($root->hasAttributes()) {
            $attrs = $root->attributes;
            foreach ($attrs as $attr) {
                $result['@attributes'][$attr->name] = $attr->value;
            }
        }

        if ($root->hasChildNodes()) {
            $children = $root->childNodes;
            if ($children->length == 1) {
                $child = $children->item(0);
                //if ($child->nodeType == XML_TEXT_NODE) {
                if (in_array($child->nodeType,[XML_TEXT_NODE,XML_CDATA_SECTION_NODE])){
                    $result['_value'] = $child->nodeValue;
                    return count($result) == 1
                        ? $result['_value']
                        : $result;
                }
            }
            $groups = array();
            foreach ($children as $child) {
                if($child->nodeType == XML_TEXT_NODE && empty(trim($child->nodeValue))) continue;
                if (!isset($result[$child->nodeName])) {
                    $result[$child->nodeName] = $this->xml_to_array($child);
                } else {
                    if (!isset($groups[$child->nodeName])) {
                        $result[$child->nodeName] = array($result[$child->nodeName]);
                        $groups[$child->nodeName] = 1;
                    }
                    $result[$child->nodeName][] = $this->xml_to_array($child);
                }
            }
        }

        return $result;
    }

}