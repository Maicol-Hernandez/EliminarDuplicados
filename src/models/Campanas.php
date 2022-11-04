<?php

namespace Dyalogo\Script\models;

use Dyalogo\Script\models\Estrategia;
use Dyalogo\Script\utils\EjecutarSQL;

/**
 * Archivo logica de negocio campañas
 */

class Campanas extends Estrategia
{
    private $strEstrategia;
    private $llave;
    private $campanas;

    function __construct(string $strEstrategia, string $llave)
    {
        parent::__construct($strEstrategia, $llave);
        $this->strEstrategia = $strEstrategia;
        $this->llave = $llave;
        $this->campanas = [];
    }


    public function getCampanas(): array
    {
        $this->obtenerCampanas();
        return $this->campanas;
    }

    public function setCampanas(array $campanas)
    {
        $this->campanas = $campanas;
    }


    private function obtenerCampanas()
    {
        $salPhone = "salPhone";
        $EnPhone = "EnPhone";

        $consulta = "SELECT ESTPAS_Comentari_b AS nombre, ESTPAS_Nombre__b AS nombreTipo, ESTPAS_ConsInte__ESTRAT_b AS idEstrategia, ESTPAS_ConsInte__CAMPAN_b AS idCampana, ESTPAS_ConsInte__b AS idPaso, CAMPAN_ConsInte__GUION__Gui_b AS guion, CAMPAN_ConsInte__GUION__Pob_b AS bd, CAMPAN_ConsInte__MUESTR_b AS muestra FROM DYALOGOCRM_SISTEMA.ESTPAS LEFT JOIN DYALOGOCRM_SISTEMA.CAMPAN ON ESTPAS_ConsInte__CAMPAN_b = CAMPAN_ConsInte__b WHERE  ESTPAS_ConsInte__ESTRAT_b =  {$this->getIdEstrategia()} AND CAMPAN_ConsInte__b <> '' AND CAMPAN_ConsInte__b IS NOT NULL AND ESTPAS_ConsInte__b <> '' AND ESTPAS_ConsInte__b IS NOT NULL HAVING (ESTPAS_Nombre__b = '{$salPhone}' OR ESTPAS_Nombre__b = '{$EnPhone}')";

        // echo "\n obtenerCampanas->consulta {$consulta} \n";

        $sql = mysqli_query($this->getMysqli(), $consulta);

        echo $sql == true ? $this->menssage($consulta, true) : $this->menssage($consulta, false);

        if ($sql && mysqli_num_rows($sql) > 0) {
            $result = mysqli_fetch_all($sql, MYSQLI_ASSOC);
            // echo "result->obtenerCampanas ==>" . json_encode($result) . "\n\n";
            $this->setCampanas($result);
        } else {
            $result = [];
            $this->setCampanas($result);
        }
        echo "\n setRepetidos->consulta {$consulta} \n\n";
        echo "\n ------------------TERMINA obtenerCampanas()-------------------- \n\n";
    }


    //BUSCAMOS LAS TABLAS DE LA CAMPAÑA
    public function deleteRecords(array $repetido)
    {
        // echo  "obj ==> " . json_encode($repetido) . "\n\n";

        // echo "deleteRecords->campana", json_encode($this->campanas), "\n\n";

        $count = 1;

        try {

            foreach ($this->campanas as $campana) {

                // echo "deleteRecords->campana", json_encode($campana), "\n\n";
                $guion = "G{$campana['guion']}";
                $bd = "G{$this->getIdBD()}";
                $muestra = "G{$this->getIdBD()}_M{$campana['muestra']}";
                $campan = $campana['idCampana'];

                // echo "\n guion ==> {$guion} \n";
                // echo " bd ==> {$bd} \n";
                // echo " muestra ==> {$muestra} \n";
                // echo " campan ==> {$campan} \n";

                // echo  "\n campana XXXX ==> " . json_encode($campana), "\n";

                $this->updateCondia($bd, $campan, $repetido);
                $this->updateGestiones($guion, $bd, $repetido);
                $this->calcularValoresMuestra($muestra, $repetido);
                $this->deleteMuestra($muestra, $repetido);

                echo " \n count por campañas ==> ", $count++, "\n";
                // echo  "\n campan ==> {$campan}";
                // echo " \n -------------------------------------- \n\n";
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    // 3.ACTUALIZAR TABLA DE CONDIA
    private function updateCondia(string $bd, int $campan, $repetido)
    {

        // echo  "\n updateCondia->repetido ==> " . json_encode($repetido) . "\n\n";

        $sqlCondia = "UPDATE DYALOGOCRM_SISTEMA.CONDIA LEFT JOIN DYALOGOCRM_WEB.{$bd} ON CONDIA_CodiMiem__b={$bd}_ConsInte__b SET CONDIA_CodiMiem__b={$repetido['id']} WHERE CONDIA_ConsInte__CAMPAN_b={$campan} AND CONDIA_ConsInte__GUION__Pob_b={$this->getIdBD()} AND {$this->getLlave()}='{$repetido['llave']}' AND CONDIA_CodiMiem__b IN({$repetido['grupo_repetidos']})";


        echo mysqli_query($this->getMysqli(), $sqlCondia) == true ? $this->menssage($sqlCondia, true) : $this->menssage($sqlCondia, false);

        echo "\n sqlCondia ==> $sqlCondia \n";
        echo "\n ----------------TERMINA updateCondia()---------------------- \n\n";
    }


    // 2.ACTUALIZAR LA TABLA DE GESTIONES
    private function updateGestiones(string $guion, string $bd, $repetido)
    {

        // echo  "\n updateGestiones->repetido ==> " . json_encode($repetido) . "\n\n";

        // echo "\n dentro del funcion =>", json_encode($value), "\n\n";

        $sqlGestiones = "UPDATE DYALOGOCRM_WEB.{$guion} LEFT JOIN DYALOGOCRM_WEB.{$bd} ON {$guion}_CodigoMiembro = {$bd}_ConsInte__b SET {$guion}_CodigoMiembro = {$repetido['id']} WHERE {$guion}_CodigoMiembro IN({$repetido['grupo_repetidos']}) AND {$guion}_CodigoMiembro IS NOT NULL";


        echo mysqli_query($this->getMysqli(), $sqlGestiones) == true ? $this->menssage($sqlGestiones, true) : $this->menssage($sqlGestiones, false);

        echo  "sqlGestiones ==> $sqlGestiones \n";
        echo "\n ---------------TERMINA updateGestiones()----------------------- \n\n";
    }
}
