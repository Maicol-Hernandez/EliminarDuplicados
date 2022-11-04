<?php

namespace Dyalogo\Script\models;

use Dyalogo\Script\models\Estrategia;

/**
 * Archivo de logica de negocio tareas BackOffice
 */


class BackOffice extends Estrategia
{
    private $strEstrategia;
    private $llave;
    private $backOffices;

    function __construct(string $strEstrategia, string $llave)
    {
        parent::__construct($strEstrategia, $llave);
        $this->strEstrategia = $strEstrategia;
        $this->llave = $llave;
        $this->backOffices = [];
    }

    public function getBackOffice(): array
    {
        $this->obtenerBackOffice();
        return $this->backOffices;
    }
    public function setBackOffice(array $infoBackOffice)
    {
        $this->backOffices = $infoBackOffice;
        // array_push($this->backOffices, $infoBackOffice);
    }

    public function buscarMuestra(array $repetido)
    {
        // echo "\n deleteMuestra->this->backOffices", json_encode($this->backOffices), "\n";
        array_filter(
            $this->backOffices,
            function ($backOffice) use ($repetido) {
                return $this->runMuestra("G{$this->getIdBD()}_M{$backOffice['muestra']}", $repetido);
            }
        );
    }

    private function runMuestra(string $muestra, array $repetido)
    {

        // echo  "\n runMuestra->repetido ==> " . json_encode($repetido) . "\n\n";
        echo  "\n runMuestra->muestra ==> " . json_encode($muestra) . "\n\n";

        $this->calcularValoresMuestra($muestra, $repetido);
        $this->deleteMuestra($muestra, $repetido);

        // echo  " \n consulta ==> $consulta \n";
        echo "\n --------------TERMINA runMuestra()------------------------ \n\n";
    }

    private function obtenerBackOffice()
    {
        $tipo = "salCheck";
        $consulta = "SELECT ESTPAS_Comentari_b AS nombre, ESTPAS_Nombre__b AS nombreTipo, ESTPAS_ConsInte__b AS idPaso, ESTPAS_ConsInte__MUESTR_b AS muestra, ESTPAS_ConsInte__ESTRAT_b AS idEstrategia FROM DYALOGOCRM_SISTEMA.ESTPAS WHERE ESTPAS_ConsInte__ESTRAT_b = {$this->getIdEstrategia()}  HAVING ESTPAS_Nombre__b = '{$tipo}'";

        $sql = mysqli_query($this->getMysqli(), $consulta);
        if ($sql && mysqli_num_rows($sql) > 0) {
            $result = mysqli_fetch_all($sql, MYSQLI_ASSOC);
            $this->setBackOffice($result);
        } else {
            $result = [];
            $this->setBackOffice($result);
        }

        echo  " \n consulta ==> $consulta \n";
        echo $sql == true ? $this->menssage($consulta, true) : $this->menssage($consulta, false);
        echo "\n --------------TERMINA obtenerBackOffice()------------------------ \n\n";
    }
}
