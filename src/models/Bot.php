<?php

namespace Dyalogo\Script\models;

use Dyalogo\Script\models\Estrategia;
use Dyalogo\Script\utils\EjecutarSQL;

class Bot extends Estrategia
{

    private $strEstrategia;
    private $llave;
    private $bots;

    function __construct(string $strEstrategia, string $llave)
    {
        parent::__construct($strEstrategia, $llave);
        $this->strEstrategia = $strEstrategia;
        $this->llave = $llave;
        $this->bots = [];
        $this->guionBot = [];
    }

    public function getBot(): array
    {
        $this->obtenerBot();
        return $this->bots;
    }

    public function setBot(array $bots)
    {
        $this->bots = $bots;
    }

    // public function getGuionBot(): array
    // {
    //     $this->obtenerGuion();
    //     return $this->guionBot;
    // }

    // public function setGuionBot(object $guionBot)
    // {
    //     array_push($this->guionBot, $guionBot);
    // }


    private function obtenerBot()
    {
        $tipo = "ivrTexto";

        $consulta = "SELECT est.ESTPAS_Comentari_b AS nombre, est.ESTPAS_Nombre__b AS nombreTipo, est.ESTPAS_ConsInte__ESTRAT_b AS idEstrategia, est.ESTPAS_ConsInte__b AS idPaso, bot.id_guion_gestion AS idGuionGestion, bot.id AS idBot FROM DYALOGOCRM_SISTEMA.ESTPAS est LEFT JOIN dyalogo_canales_electronicos.dy_bot bot ON est.ESTPAS_ConsInte__b = bot.id_estpas WHERE est.ESTPAS_ConsInte__ESTRAT_b = '{$this->getIdEstrategia()}' AND bot.id_guion_gestion IS NOT NULL AND bot.id_guion_gestion <> '' HAVING ESTPAS_Nombre__b = '{$tipo}'";

        // echo "\n obtenerBot->consulta => ", $consulta, "\n\n";

        $sql = mysqli_query($this->getMysqli(), $consulta);
        if ($sql && mysqli_num_rows($sql) > 0) {
            $result = mysqli_fetch_all($sql, MYSQLI_ASSOC);
            // echo "\n\n result->obtenerBot ==>" . json_encode($result) . "\n\n";
            $this->setBot($result);
        } else {
            $result = [];
            $this->setBot($result);
        }

        echo "\n obtenerBot->consulta {$consulta} \n\n";
        echo "\n -----------------TERMINA obtenerBot()--------------------- \n\n";
    }

    //BUSCAMOS LAS TABLAS DEL BOT 
    public function deleteRecords(array $repetido)
    {
        // echo  "obj ==> " . json_encode($repetido) . "\n\n";
        // echo "\n obtenerGuion->this->bots ", json_encode($this->guionBot), "\n\n";

        try {

            foreach ($this->bots as $bot) {

                // echo "deleteRecords->bot", json_encode($bot), "\n\n";

                $guion = $bot['idBot'] === $bot['idGuionGestion'] ? "B{$bot['idGuionGestion']}" : "G{$bot['idGuionGestion']}";
                $bd = "G{$this->getIdBD()}";

                // echo "\n guion ==> {$guion} \n";
                // echo " bd ==> {$bd} \n";

                $this->updateGestionesBot($guion, $bd, $repetido);
                // $this->deleteBDBot($bd, $repetido);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }



    private function updateGestionesBot(string $guion, string $bd, array $repetido): bool
    {
        echo  "\n updateGestionesBot->repetido ==> " . json_encode($repetido) . "\n\n";

        $sqlGestionesBot = "UPDATE DYALOGOCRM_WEB.{$guion} LEFT JOIN DYALOGOCRM_WEB.{$bd} ON {$guion}_CodigoMiembro = {$bd}_ConsInte__b SET {$guion}_CodigoMiembro = {$repetido['id']} WHERE {$guion}_CodigoMiembro IN({$repetido['grupo_repetidos']}) AND {$guion}_CodigoMiembro IS NOT NULL";

        echo  "sqlGestionesBot->updateGestionesBot ==> $sqlGestionesBot \n";
        echo "\n -------------------------------------- \n\n";

        // EjecutarSQL::runSQLAll($this->getMysqli(), $sqlGestionesBot);

        mysqli_query($this->getMysqli(), $sqlGestionesBot);


        return $sqlGestionesBot;
    }

    // private function deleteBDBot(string $bd,  array $repetido): bool
    // {
    //     // echo  "\n deleteBD->repetido ==> " . json_encode($repetido) . "\n\n";

    //     $sqlDeleteBD = "DELETE FROM DYALOGOCRM_WEB.{$bd} WHERE {$this->getLlave()}='{$repetido['llave']}' AND {$bd}_ConsInte__b !={$repetido['id']}";

    //     // $valido=true;
    //     // if ($sqlDeleteBD) {
    //     //     $valido = true;
    //     // } else {
    //     //     echo "Fallo al actualizar la bd -> " . $this->getMysqli()->error . "  --- consulta -> " . $sqlDeleteBD;
    //     // }

    //     // echo  " \n sqlDeleteBD ==> $sqlDeleteBD \n";
    //     // echo "\n -------------------------------------- \n\n";

    //     return $sqlDeleteBD;
    // }


    // private function obtenerGuion()
    // {
    //     // echo "\n obtenerGuion->this->bots ", json_encode($this->bots), "\n\n";

    //     array_filter(
    //         $this->bots,
    //         fn ($bot) => $this->obtenerBotIdpaso($bot['idPaso'])
    //     );
    // }


    // private function obtenerBotIdpaso($idpaso)
    // {
    //     // echo "\n BOT ==>" . json_encode($idpaso) . "\n";
    //     $consulta = "SELECT id_guion_gestion AS idGuionGestion, id_estpas AS idpaso FROM dyalogo_canales_electronicos.dy_bot WHERE id_estpas = {$idpaso}";

    //     echo "\n obtenerBotIdpaso->consulta ", $consulta, "\n\n";

    //     $sql = mysqli_query($this->getMysqli(), $consulta);
    //     if ($sql && mysqli_num_rows($sql) > 0) {
    //         $result = mysqli_fetch_object($sql);
    //         // echo "\n\n result->obtenerBotIdpaso ==>" . json_encode($result) . "\n\n";
    //         $this->setGuionBot($result);
    //     }
    // }
}
