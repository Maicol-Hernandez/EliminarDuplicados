<?php

namespace Dyalogo\Script\models;

use Dyalogo\Script\models\Estrategia;

/**
 * Archivo logica de negocio SMS salientes
 */

class SMSsalientes extends Estrategia
{
    private $strEstrategia;
    private $llave;
    private $SMS;

    function __construct(string $strEstrategia, string $llave)
    {
        parent::__construct($strEstrategia, $llave);
        $this->strEstrategia = $strEstrategia;
        $this->llave = $llave;
        $this->SMS = [];
    }

    public function getSMS(): array
    {
        $this->obtenerSMS();
        return $this->SMS;
    }

    public function setSMS(array $sms)
    {
        $this->SMS = $sms;
    }

    public function buscarMuestra(array $repetido)
    {
        // echo "\n deleteMuestra->this->SMS", json_encode($this->SMS), "\n";
        array_filter(
            $this->SMS,
            function ($SMS) use ($repetido) {
                return $this->runMuestra("G{$this->getIdBD()}_M{$SMS['muestra']}", $repetido);
            }
        );
    }


    private function runMuestra(string $muestra, array $repetido)
    {

        echo  "\n runMuestra->repetido ==> " . json_encode($repetido) . "\n\n";
        echo  "\n runMuestra->muestra ==> " . json_encode($muestra) . "\n\n";

        $this->calcularValoresMuestra($muestra, $repetido);
        $this->deleteMuestra($muestra, $repetido);

        // echo  " \n consulta ==> $consulta \n";
        echo "\n ----------------TERMINA runMuestra()---------------------- \n\n";
    }

    private function obtenerSMS()
    {
        $tipo = "salSms";
        $consulta = "SELECT ESTPAS_Comentari_b AS nombre, ESTPAS_Nombre__b AS nombreTipo, ESTPAS_ConsInte__b AS idPaso, ESTPAS_ConsInte__MUESTR_b AS muestra, ESTPAS_ConsInte__ESTRAT_b AS idEstrategia FROM DYALOGOCRM_SISTEMA.ESTPAS WHERE ESTPAS_ConsInte__ESTRAT_b = {$this->getIdEstrategia()}  HAVING ESTPAS_Nombre__b = '{$tipo}'";

        $sql = mysqli_query($this->getMysqli(), $consulta);
        if ($sql && mysqli_num_rows($sql) > 0) {
            $result = mysqli_fetch_all($sql, MYSQLI_ASSOC);
            // echo "\n\n result->obtenerSMS ==>" . json_encode($result) . "\n\n";
            $this->setSMS($result);
        } else {
            $result = [];
            $this->setSMS($result);
        }

        echo  " \n obtenerSMS->consulta ==> $consulta \n";
        echo $sql == true ? $this->menssage($consulta, true) : $this->menssage($consulta, false);
        echo "\n ----------------TERMINA obtenerSMS()---------------------- \n\n";
    }
}
