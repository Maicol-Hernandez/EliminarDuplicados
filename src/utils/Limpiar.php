<?php

namespace Dyalogo\Script\utils;

class Limpiar
{


    static public function limpiarVacios($objecto)
    {


        // $objecto->fechaultimaGestionResultante = empty($objecto->fechaultimaGestionResultante) == false ? "{$objecto}" : "null";

        foreach ($objecto as $key => $value) {
            # code...
            // echo  "\n limpiarVacios->value ==> " . json_encode($value) . "\n\n";
            // echo  "\n limpiarVacios->key ==> " . json_encode($key) . "\n\n";

            $objecto->$key = $value == null ? "null" : "'{$value}'";

            // echo  "\n limpiarVacios->key ==> " . json_encode($key) . "\n\n";
        }

        // echo  "\n limpiarVacios->objecto ==> " . json_encode($objecto) . "\n\n";

        return $objecto;
    }
}
