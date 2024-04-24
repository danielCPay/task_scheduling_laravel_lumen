<?php

namespace App\Class;

class General {
    private const RUTA_ARCHIVO = 'upload/';

    public static function isEmpty($object) {
        if (!isset($object))
            return true;
        if (is_null($object))
            return true;
        if (is_string($object) && strlen($object) <= 0)
            return true;
        if (is_array($object) && empty($object))
            return true;
        if (is_numeric($object) && is_nan($object))
            return true;
        if (is_object($object) && $object == new \stdClass())
            return true;
    
        return false;
    }
    
    public static function convertir_array_a_xml($arrayData) {
        $xml_dato = new \SimpleXMLElement('<?xml version="1.0"?><data></data>');
        self::array_a_xml($arrayData, $xml_dato, 0);
        return $xml_dato->asXML();
    }
    
    private static function array_a_xml( $dato, &$xml_dato,$nivel) {
        foreach( $dato as $llave => $valor ) {
            if( is_numeric($llave) ){
                //$llave = 'item'.$llave; //dealing with <0/>..<n/> issues
                $llave = 'item';
            }else if($nivel==0){
                $llave = $llave;
            }
            if( is_array($valor) ) {
                $subnode = $xml_dato->addChild($llave);
                self::array_a_xml($valor, $subnode, ($nivel+1));
            } else {
                //$xml_dato->addChild("$llave",htmlspecialchars("$valor", ENT_QUOTES, "UTF-8"));
                $xml_dato->addChild("$llave",htmlspecialchars("$valor"));
            }
         }
    }

    public static function subir_archivo($files,$ruta="")
    {
        $_FILES = $files;
        $nombreArchivo = $_FILES['name'];
        $extension = explode(".", $nombreArchivo);
        $extension = $extension[count($extension) - 1];
        $nombreArchivoFinal =
            date("y") . //años
            date("m") . //mes
            date("d") . //dia
            date("H") . //hora 24h
            date("i") . //minutos
            date("s") . //segundos
            date("_") .
            substr(microtime(), 2, 4) .
            '.' . $extension;
        //$rutaArchivoSubido = __DIR__."\..\..\public\upload\\".$ruta. $nombreArchivoFinal;
        //move_uploaded_file($_FILES['tmp_name'], $rutaArchivoSubido);
        //return "upload\\".$ruta.$nombreArchivoFinal;

        $rutaArchivoSubido = self::RUTA_ARCHIVO.$ruta.$nombreArchivoFinal;
        move_uploaded_file($_FILES['tmp_name'], $rutaArchivoSubido);

        return $rutaArchivoSubido;
    }

}
