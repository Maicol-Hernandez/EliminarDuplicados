<?php

/**
 * Archivo de logica de negocio estrategias 
 */

namespace Dyalogo\Script\models;


use Dyalogo\Script\utils\Mysql;
use Dyalogo\Script\utils\EjecutarSQL;
use Dyalogo\Script\utils\Limpiar;

class Estrategia
{
    private $idEstrategia;
    private $strEstrategia;
    private $mysqli;
    private $idBD;
    private $llave;
    private $repetidos;
    private $agrupadosRepetidos;

    public function __construct(
        string $strEstrategia,
        string $llave

    ) {
        // var_dump("Se creo un nuevo objecto mysqli \n ", Mysql::mysqli(), "\n\n");
        $this->mysqli = Mysql::mysqli();
        $this->llave = $llave;
        $this->strEstrategia = $strEstrategia;
        $this->idEstrategia = 0;
        $this->idBD = 0;
        $this->repetidos = [];
        $this->agrupadosRepetidos = [];
    }

    public function getRepetidos()
    {
        $this->buscarByCampoLlave();
        return $this->repetidos;
    }

    public function setRepetidos($repetidos)
    {
        // echo "\n setRepetidos->repetidos ", json_encode($repetidos), "\n\n";
        $this->repetidos = $repetidos;
    }

    public function setIdEstrategia(int $id)
    {
        $this->idEstrategia = $id;
    }

    public function getIdEstrategia(): int
    {
        $this->obtenerIdEstrategia();
        return $this->idEstrategia;
    }

    public function getRepetidosId()
    {
        return $this->agrupadosRepetidos;
    }

    public function setRepetidosId($repetidos)
    {
        // echo "\n setRepetidos->repetidos ", json_encode($repetidos), "\n\n";
        array_push($this->agrupadosRepetidos, $repetidos);
    }

    public function getIdBD(): int
    {
        $this->obtenerIdBD();
        return $this->idBD;
    }

    public function setIdBD(int $id)
    {
        $this->idBD = $id;
    }

    public function getLlave(): string
    {
        return $this->llave;
    }

    public function getMysqli(): object
    {
        return $this->mysqli;
    }

    private function obtenerIdEstrategia()
    {
        // echo "○this->strEstrategia ", $this->strEstrategia, "\n";
        $consulta = "SELECT G2_ConsInte__b AS idEstrategia FROM DYALOGOCRM_SISTEMA.G2 WHERE MD5(CONCAT('p.fs@3!@M', G2_ConsInte__b)) = '{$this->strEstrategia}'";
        // echo $consulta, "\n\n";
        $result = EjecutarSQL::runSQLObject($this->getMysqli(), $consulta);
        // echo "\n obtenerIdEstrategia-> {$result->idEstrategia}\n";

        $this->setIdEstrategia($result->idEstrategia);

        echo "\n obtenerIdEstrategia->consulta {$consulta} \n\n";
        echo "\n -----------------TERMINA obtenerIdEstrategia()--------------------- \n\n";
    }

    private function obtenerIdBD()
    {
        $consulta = "SELECT ESTRAT_ConsInte_GUION_Pob AS idBD FROM DYALOGOCRM_SISTEMA.ESTRAT WHERE ESTRAT_ConsInte__b ={$this->getIdEstrategia()}";

        $sql = mysqli_query($this->getMysqli(), $consulta);
        if ($sql && mysqli_num_rows($sql) > 0) {
            $idBD = mysqli_fetch_object($sql)->idBD;
            $this->setIdBD($idBD);
        } else {
            $idBD = 0;
            $this->setIdBD($idBD);
        }

        echo "\n obtenerIdBD->consulta {$consulta} \n\n";
        echo $sql == true ? $this->menssage($consulta, true) : $this->menssage($consulta, false);
        echo "\n -----------------TERMINA obtenerIdBD()--------------------- \n\n";
    }

    // BackOffice $backOffice
    public function buscarRepetidos(Campanas $campanas, BackOffice $backOffice, EmailSalientes $emailSalientes, SMSsalientes $smsSalientes, Bot $bot)
    {

        $count = 1;
        try {

            // echo "\n this->agrupadosRepetidos", json_encode($this->agrupadosRepetidos), "\n" ;

            foreach ($this->agrupadosRepetidos as $repetido) {

                // echo "buscarRepetidos->reperepetido", json_encode($repetido), "\n\n";

                $campanas->deleteRecords($repetido);
                $bot->deleteRecords($repetido);
                $backOffice->buscarMuestra($repetido);
                $emailSalientes->buscarMuestra($repetido);
                $smsSalientes->buscarMuestra($repetido);
                $this->calcularValoresBD("G{$this->getIdBD()}", $repetido);
                $this->deleteBD("G{$this->getIdBD()}", $repetido);

                echo " \n count ==> ", $count++, "\n";
                // echo "buscarRepetidos->reperepetido", json_encode($repetido), "\n\n";

                echo  "this->agrupadosRepetidos ==> \n -------------------------------------- \n\n";
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    // 1. BUSCAMOS EL MAX(ID) DE CADA GRUPO DE REGISTROS AGUPADOS POR CEDULA EN LA BD 
    protected function buscarByCampoLlave()
    {
        $consulta = "SELECT COUNT(1) AS veces, {$this->getLlave()} AS llave, MAX(G{$this->getIdBD()}_ConsInte__b) AS id FROM DYALOGOCRM_WEB.G{$this->getIdBD()} WHERE {$this->getLlave()} <> '' AND G{$this->getIdBD()}_ConsInte__b <> '-1' AND {$this->getLlave()} IS NOT NULL GROUP BY {$this->getLlave()} HAVING veces > 1";


        $sql = mysqli_query($this->getMysqli(), $consulta);

        if ($sql && mysqli_num_rows($sql) > 0) {
            $result = mysqli_fetch_all($sql, MYSQLI_ASSOC);
            $this->setRepetidos($result);
        } else {
            $result = [];
            $this->setRepetidos($result);
        }

        echo "\n buscarByCampoLlave->consulta {$consulta} \n\n";
        echo $sql == true ? $this->menssage($consulta, true) : $this->menssage($consulta, false);
        echo "\n ----------------TERMINA buscarByCampoLlave()---------------------- \n\n";
    }


    public function buscarRepetidosId()
    {

        array_filter(
            $this->repetidos,
            function ($repetido) {
                return $this->buscarRepetidoID($repetido);
            }
        );
    }

    private function buscarRepetidoID(array $repetido)
    {

        // echo "\n setRepetidos->repetidos ", json_encode($repetido), "\n\n";

        $consulta = "SELECT G{$this->getIdBD()}_ConsInte__b AS id_registro_bd FROM DYALOGOCRM_WEB.G{$this->getIdBD()} WHERE {$this->getLlave()} = '{$repetido['llave']}' AND G{$this->getIdBD()}_ConsInte__b <> '{$repetido['id']}' ";


        $sql = mysqli_query($this->getMysqli(), $consulta);

        if ($sql && mysqli_num_rows($sql) > 0) {
            $result = mysqli_fetch_all($sql);

            $result = array_merge($repetido, ["grupo_repetidos" => Estrategia::stringFormat($result)]);
            // echo "\n setRepetidos->repetidos ", json_encode($result), "\n\n";
            $this->setRepetidosId($result);
        } else {
            $result = [];
            $this->setRepetidosId($result);
        }

        echo "\n setRepetidos->consulta {$consulta} \n\n";
        echo $sql == true ? $this->menssage($consulta, true) : $this->menssage($consulta, false);
        echo "\n ----------------TERMINA buscarRepetidoID()---------------------- \n\n";
    }


    private function calcularValoresBD(string $bd, array $repetido)
    {
        echo  "\n calcularValoresBD->repetido ==> " . json_encode($repetido) . "\n\n";

        $consulta = "SELECT {$bd}_FechaInsercion AS fechaInsercionResultante, {$bd}_Usuario AS usuarioResultante, {$bd}_UltiGest__b AS ultiGestResultante, {$bd}_GesMasImp_b AS gesMasImpResultante, {$bd}_IdLlamada AS idLlamadaResultante, MAX({$bd}_FecUltGes_b) AS fecUltGesResultante, MAX({$bd}_FeGeMaIm__b) AS feGeMaImResultante, {$bd}_TipoReintentoUG_b AS tipoReintentoUGResultante, {$bd}_TipoReintentoGMI_b AS tipoReintentoGMIResultante, {$bd}_FecHorAgeUG_b AS fecHorAgeUGResultante, {$bd}_FecHorAgeGMI_b AS fecHorAgeGMIResultante, {$bd}_ClasificacionUG_b AS clasificacionUGResultante, {$bd}_ClasificacionGMI_b AS clasificacionGMIResultante, {$bd}_EstadoUG_b AS estadoUGResultante, {$bd}_EstadoGMI_b AS estadoGMIResultante, {$bd}_UsuarioUG_b AS usuarioUGResultante, {$bd}_UsuarioGMI_b AS usuarioGMIResultante, {$bd}_Canal_____b AS canalResultante, {$bd}_CanalGMI_b AS canalGMIResultante, {$bd}_Sentido___b AS sentidoResultante, {$bd}_SentidoGMI_b AS sentidoGMIResultante, SUM({$bd}_CantidadIntentos) AS cantidadIntentosResultante, SUM({$bd}_CantidadIntentosGMI_b) AS cantidadIntentosGMIResultante, {$bd}_LinkContenidoUG_b AS linkContenidoUGResultante, {$bd}_LinkContenidoGMI_b AS linkContenidoGMIResultante, {$bd}_ComentarioUG_b AS comentarioUGResultante, {$bd}_ComentarioGMI_b AS comentarioGMIResultante, {$bd}_DetalleCanalUG_b AS DetalleCanalUG_b, {$bd}_DetalleCanalGMI_b AS DetalleCanalGMI_b, {$bd}_DatoContactoUG_b AS DatoContactoUG_b, {$bd}_DatoContactoGMI_b AS DatoContactoGMI_b, {$bd}_PasoUG_b AS pasoUGResultante, {$bd}_PasoGMI_b AS pasoGMIResultante, {$bd}_CodigoMiembro AS codigoMiembroResultante, {$bd}_PoblacionOrigen AS poblacionOrigenResultante, {$bd}_EstadoDiligenciamiento AS estadoDiligenciamientoResultante FROM DYALOGOCRM_WEB.{$bd} AS bd LEFT JOIN DYALOGOCRM_SISTEMA.MONOEF AS mono ON bd.{$bd}_GesMasImp_b = mono.MONOEF_ConsInte__b WHERE mono.MONOEF_Importanc_b = (SELECT MAX(mono.MONOEF_Importanc_b) FROM DYALOGOCRM_WEB.MONOEF WHERE G{$this->getIdBD()}_ConsInte__b = {$repetido['id']} OR G{$this->getIdBD()}_ConsInte__b IN ({$repetido['grupo_repetidos']})) HAVING MAX(mono.MONOEF_Importanc_b)";


        echo  " \n calcularValoresBD->consulta ==> $consulta \n";

        $result = EjecutarSQL::runSQLObject($this->getMysqli(), $consulta);


        empty($result) === false ? $this->updateCamposBD($bd, $repetido, $result) : false;

        echo "\n ---------------TERMINA DE calcularValoresBD() Y updateCamposBD()----------------------- \n\n";

        // echo "\n calcularValoresBD->result ", json_encode($result), "\n\n";
    }

    private function updateCamposBD(string $bd, array $repetido, object $values)
    {
        echo  "\n updateCamposBD->repetido ==> " . json_encode($repetido) . "\n\n";

        $values = Limpiar::limpiarVacios($values);

        echo  "\n updateCamposBD->values ==> " . json_encode($values) . "\n\n";

        $consulta = "UPDATE DYALOGOCRM_WEB.{$bd} SET {$bd}_FechaInsercion = {$values->fechaInsercionResultante}, {$bd}_Usuario = {$values->usuarioResultante}, {$bd}_IdLlamada = {$values->idLlamadaResultante}, {$bd}_UltiGest__b = {$values->ultiGestResultante}, {$bd}_GesMasImp_b = {$values->gesMasImpResultante}, {$bd}_FecUltGes_b = {$values->fecUltGesResultante}, {$bd}_FeGeMaIm__b = {$values->feGeMaImResultante}, {$bd}_TipoReintentoUG_b = {$values->tipoReintentoUGResultante}, {$bd}_TipoReintentoGMI_b = {$values->tipoReintentoGMIResultante}, {$bd}_FecHorAgeUG_b = {$values->fecHorAgeUGResultante}, {$bd}_FecHorAgeGMI_b = {$values->fecHorAgeGMIResultante}, {$bd}_ClasificacionUG_b = {$values->clasificacionUGResultante}, {$bd}_ClasificacionGMI_b = {$values->clasificacionGMIResultante}, {$bd}_EstadoUG_b = {$values->estadoUGResultante}, {$bd}_EstadoGMI_b = {$values->estadoGMIResultante}, {$bd}_UsuarioUG_b = {$values->usuarioUGResultante}, {$bd}_UsuarioGMI_b = {$values->usuarioGMIResultante},{$bd}_Canal_____b = {$values->canalResultante}, {$bd}_CanalGMI_b = {$values->canalGMIResultante}, {$bd}_Sentido___b = {$values->sentidoResultante},{$bd}_SentidoGMI_b = {$values->sentidoGMIResultante}, {$bd}_CantidadIntentos = {$values->cantidadIntentosResultante}, {$bd}_CantidadIntentosGMI_b = {$values->cantidadIntentosGMIResultante}, {$bd}_LinkContenidoUG_b = {$values->linkContenidoUGResultante}, {$bd}_LinkContenidoGMI_b = {$values->linkContenidoGMIResultante}, {$bd}_ComentarioUG_b = {$values->comentarioUGResultante}, {$bd}_ComentarioGMI_b = {$values->comentarioGMIResultante}, {$bd}_DetalleCanalUG_b = {$values->DetalleCanalUG_b}, {$bd}_DetalleCanalGMI_b = {$values->DetalleCanalGMI_b}, {$bd}_DatoContactoUG_b = {$values->DatoContactoUG_b}, {$bd}_DatoContactoGMI_b = {$values->DatoContactoGMI_b}, {$bd}_PasoUG_b = {$values->pasoUGResultante}, {$bd}_PasoGMI_b = {$values->pasoGMIResultante}, {$bd}_CodigoMiembro = {$values->codigoMiembroResultante}, {$bd}_PoblacionOrigen = {$values->poblacionOrigenResultante}, {$bd}_EstadoDiligenciamiento = {$values->estadoDiligenciamientoResultante} WHERE {$bd}_ConsInte__b = {$repetido['id']}";

        echo  " \n updateCamposBD->consulta ==> $consulta \n";
        echo mysqli_query($this->getMysqli(), $consulta) == true ? $this->menssage($consulta, true) : $this->menssage($consulta, false);
        echo "\n ---------------TERMINA updateCamposBD()----------------------- \n\n";
    }

    // 5.BORRAR DE LA BASE DE DATO
    private function deleteBD(string $bd, array $repetido)
    {

        echo  "\n deleteBD->repetido ==> " . json_encode($repetido) . "\n\n";

        $sqlDeleteBD = "DELETE FROM DYALOGOCRM_WEB.{$bd} WHERE {$this->getLlave()}='{$repetido['llave']}' AND {$bd}_ConsInte__b IN ({$repetido['grupo_repetidos']})";

        echo  " \n sqlDeleteBD ==> $sqlDeleteBD \n";
        echo mysqli_query($this->getMysqli(), $sqlDeleteBD) == true ? $this->menssage($sqlDeleteBD, true) : $this->menssage($sqlDeleteBD, false);
        echo "\n ----------------TERMINA deleteBD()---------------------- \n\n";
    }


    protected function calcularValoresMuestra(string $muestra, array $repetido)
    {
        echo  "\n calcularValoresMuestra->repetido ==> " . json_encode($repetido) . "\n\n";

        $consulta = "SELECT MONOEF_Importanc_b AS importanciaResultante, {$muestra}_ConIntUsu_b AS conIntUsuResultante, {$muestra}_Activo____b AS activoResultante, {$muestra}_FecHorMinProGes__b AS fecHorMinProGesResultante, {$muestra}_FechaCreacion_b AS fechaCreacionResultante, {$muestra}_FechaAsignacion_b AS fechaAsignacionResultante, {$muestra}_FechaReactivacion_b AS fechaReactivacionResultante, {$muestra}_FechaReactivacion_b AS fechaReactivacionResultante, {$muestra}_UltiGest__b AS ultiGestResultante, {$muestra}_GesMasImp_b AS gesMasImpResultante, MAX({$muestra}_FecUltGes_b) AS fecUltGesResultante, MAX({$muestra}_FeGeMaIm__b) AS feGeMaImResultante, {$muestra}_Estado____b AS estadoResultante, {$muestra}_TipoReintentoGMI_b AS tipoReintentoGMIResultante, {$muestra}_FecHorAge_b AS fecHorAgeResultante, {$muestra}_FecHorAge_b AS fecHorAgeResultante, {$muestra}_FecHorAgeGMI_b AS fecHorAgeGMIResultante, {$muestra}_ConUltGes_b AS conUltGesResultante, {$muestra}_ConUltGes_b AS conUltGesResultante, {$muestra}_CoGesMaIm_b AS coGesMaImResultante, {$muestra}_EstadoUG_b AS estadoUGResultante, {$muestra}_EstadoGMI_b AS estadoGMIResultante, {$muestra}_UsuarioUG_b AS usuarioUGResultante, {$muestra}_UsuarioGMI_b AS usuarioGMIResultante, {$muestra}_CanalUG_b AS canalUGResultante, {$muestra}_CanalGMI_b AS canalGMIResultante, {$muestra}_SentidoUG_b AS sentidoUGResultante, {$muestra}_SentidoGMI_b AS sentidoGMIResultante, SUM({$muestra}_NumeInte__b) AS numeInteResultante, SUM({$muestra}_CantidadIntentosGMI_b) AS cantidadIntentosGMIResultante, {$muestra}_Comentari_b AS comentariResultante, {$muestra}_ComentarioGMI_b AS comentarioGMIResultante, {$muestra}_LinkContenidoUG_b AS linkContenidoUGResultante, {$muestra}_LinkContenidoGMI_b AS linkContenidoGMIResultante, {$muestra}_DetalleCanalUG_b AS detalleCanalUGResultante, {$muestra}_DetalleCanalGMI_b AS detalleCanalGMIResultante, {$muestra}_DatoContactoUG_b AS datoContactoUGResultante, {$muestra}_DatoContactoGMI_b AS datoContactoGMIResultante, {$muestra}_TienGest__b AS tienGestResultante, {$muestra}_MailEnvi__b AS mailEnviResultante, {$muestra}_GruRegRel_b AS gruRegRelResultante, {$muestra}_EfeUltGes_b AS efeUltGesResultante, {$muestra}_EfGeMaIm__b AS efGeMaImResultante FROM DYALOGOCRM_WEB.{$muestra} AS m LEFT JOIN DYALOGOCRM_SISTEMA.MONOEF AS mono ON m.{$muestra}_GesMasImp_b = mono.MONOEF_ConsInte__b WHERE mono.MONOEF_Importanc_b = (SELECT MAX(mono.MONOEF_Importanc_b) FROM DYALOGOCRM_WEB.MONOEF WHERE m.{$muestra}_CoInMiPo__b = {$repetido['id']} OR m.{$muestra}_CoInMiPo__b IN ({$repetido['grupo_repetidos']})) HAVING MAX(mono.MONOEF_Importanc_b)";

        echo  " \n calcularValoresMuestra->consulta ==> $consulta \n";

        $result = EjecutarSQL::runSQLObject($this->getMysqli(), $consulta);

        // count($result) > 0 ? $this->updateCamposMuestra($muestra, $repetido, $result) : false;
        empty($result) === false ? $this->updateCamposMuestra($muestra, $repetido, $result) : false;
        // echo "\n empty ", empty($result), "\n";
        echo "\n calcularValoresMuestra->result ", json_encode($result), "\n\n";
        // empty($result) === true ? $this->updateCamposMuestra($muestra, $repetido, $result) : false;

        echo "\n --------------TERMINA calcularValoresMuestra() Y updateCamposMuestra()------------------------ \n\n";
    }

    private function updateCamposMuestra(string $muestra, array $repetido, $values)
    {
        echo  "\n updateCamposMuestra->repetido ==> " . json_encode($repetido) . "\n\n";
        echo  "\n updateCamposMuestra->values ==> " . json_encode($values) . "\n\n";

        $values = Limpiar::limpiarVacios($values);

        $consulta = "UPDATE DYALOGOCRM_WEB.{$muestra} SET {$muestra}_ConIntUsu_b = {$values->conIntUsuResultante}, {$muestra}_Activo____b = {$values->activoResultante}, {$muestra}_FecHorMinProGes__b = {$values->fecHorMinProGesResultante}, {$muestra}_FechaCreacion_b = {$values->fechaCreacionResultante}, {$muestra}_FechaAsignacion_b = {$values->fechaAsignacionResultante}, {$muestra}_FechaReactivacion_b = {$values->fechaReactivacionResultante}, {$muestra}_FechaReactivacion_b = {$values->fechaReactivacionResultante}, {$muestra}_UltiGest__b = {$values->ultiGestResultante}, {$muestra}_GesMasImp_b = {$values->gesMasImpResultante}, {$muestra}_FecUltGes_b = {$values->fecUltGesResultante}, {$muestra}_FeGeMaIm__b = {$values->feGeMaImResultante}, {$muestra}_Estado____b = {$values->estadoResultante}, {$muestra}_TipoReintentoGMI_b = {$values->tipoReintentoGMIResultante}, {$muestra}_FecHorAge_b = {$values->fecHorAgeResultante}, {$muestra}_FecHorAge_b = {$values->fecHorAgeResultante}, {$muestra}_FecHorAgeGMI_b = {$values->fecHorAgeGMIResultante}, {$muestra}_ConUltGes_b = {$values->conUltGesResultante}, {$muestra}_ConUltGes_b = {$values->conUltGesResultante}, {$muestra}_CoGesMaIm_b = {$values->coGesMaImResultante}, {$muestra}_EstadoUG_b = {$values->estadoUGResultante}, {$muestra}_EstadoGMI_b = {$values->estadoGMIResultante}, {$muestra}_UsuarioUG_b = {$values->usuarioUGResultante}, {$muestra}_UsuarioGMI_b = {$values->usuarioGMIResultante}, {$muestra}_CanalUG_b = {$values->canalUGResultante}, {$muestra}_CanalGMI_b = {$values->canalGMIResultante}, {$muestra}_SentidoUG_b = {$values->sentidoUGResultante}, {$muestra}_SentidoGMI_b = {$values->sentidoGMIResultante}, {$muestra}_NumeInte__b = {$values->numeInteResultante}, {$muestra}_CantidadIntentosGMI_b = {$values->cantidadIntentosGMIResultante}, {$muestra}_Comentari_b = {$values->comentariResultante}, {$muestra}_ComentarioGMI_b = {$values->comentarioGMIResultante}, {$muestra}_LinkContenidoUG_b = {$values->linkContenidoUGResultante}, {$muestra}_LinkContenidoGMI_b = {$values->linkContenidoGMIResultante}, {$muestra}_DetalleCanalUG_b = {$values->detalleCanalUGResultante}, {$muestra}_DetalleCanalGMI_b = {$values->detalleCanalGMIResultante}, {$muestra}_DatoContactoUG_b = {$values->datoContactoUGResultante}, {$muestra}_DatoContactoGMI_b = {$values->datoContactoGMIResultante}, {$muestra}_TienGest__b = {$values->tienGestResultante}, {$muestra}_MailEnvi__b = {$values->mailEnviResultante}, {$muestra}_GruRegRel_b = {$values->gruRegRelResultante}, {$muestra}_EfeUltGes_b = {$values->efeUltGesResultante}, {$muestra}_EfGeMaIm__b = {$values->efGeMaImResultante} WHERE {$muestra}_CoInMiPo__b = {$repetido['id']}";

        echo  " \n updateCamposMuestra->consulta ==> $consulta \n";
        echo mysqli_query($this->getMysqli(), $consulta) == true ? $this->menssage($consulta, true) : $this->menssage($consulta, false);
        echo "\n ------------------TERMINA updateCamposMuestra()-------------------- \n\n";
    }

    // 4.BORRAR DE LA MUESTRA
    protected function deleteMuestra(string $muestra, array $repetido)
    {

        echo "\n deleteMuestra->repetido[string] ", $muestra, "\n\n";

        echo  "\n deleteMuestra->repetido ==> " . json_encode($repetido) . "\n\n";


        $sqlDeleteMuestra = "DELETE FROM DYALOGOCRM_WEB.{$muestra} WHERE {$muestra}_CoInMiPo__b IN({$repetido['grupo_repetidos']})";


        echo  "\n sqlDeleteMuestra ==> $sqlDeleteMuestra \n";
        echo mysqli_query($this->getMysqli(), $sqlDeleteMuestra) == true ? $this->menssage($sqlDeleteMuestra, true) : $this->menssage($sqlDeleteMuestra, false);
        echo "\n -----------------TERMINA deleteMuestra()--------------------- \n\n";
    }


    static function stringFormat(array $repetidos)
    {
        $values = [];
        foreach ($repetidos as $value) {
            array_push($values, implode($value));
        }
        $cadena = implode(",", $values);
        return $cadena;
    }

    public static function menssage(string $consulta, bool $value): string
    {
        # code...
        if ($value) {
            $menssage = "\n\n -------------La consulta se ejecuto correctamente-------------: \n {$consulta} \n";
        } else {
            $menssage = "\n\n -------------La consulta fallo-------------: \n {$consulta} \n";
        }
        return $menssage;
    }
}
