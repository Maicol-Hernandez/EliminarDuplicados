<?php
require 'vendor/autoload.php';

// ini_set('display_errors', 'On');
// ini_set('display_errors', 1);
date_default_timezone_set('America/Bogota');
include("../pages/conexion.php");

use Dyalogo\Script\models\Estrategia;
use Dyalogo\Script\models\Campanas;
use Dyalogo\Script\models\BackOffice;
use Dyalogo\Script\models\Bot;
use Dyalogo\Script\models\EmailSalientes;
use Dyalogo\Script\models\SMSsalientes;

/**
 * ?idEstrategia=4&llave=4555
 */


// $strEstrategia = $_GET['id_estrategia'];
// $llave = $_GET['campoLlave'];

$strEstrategia = "709b56147c6aa5a629864b5cf946c65f";
$llave = "G2318_C44061";

$estrategia = new Estrategia($strEstrategia, $llave);

$estrategia->getIdEstrategia();
$estrategia->getIdBD();
$estrategia->getRepetidos();
$estrategia->getRepetidosId();
$estrategia->buscarRepetidosId();

// if (isset($_GET['getDuplicados']) == true) {
//     echo json_encode($estrategia->getRepetidosId());
// }



// echo "estrategia->getRepetidos()", json_encode($estrategia->getRepetidos()), "\n";
// echo json_encode();
// echo "estrategia->buscarRepetidos()", $estrategia->buscarRepetidos() ;

// echo json_encode($estrategia->getRepetidos());
//  echo "<br> <br> ==> sql estrategia->getIdBD()  {$estrategia->getIdBD()} <br>";
//  echo "<br> <br> ==> sql estrategia->validateByCedula()  {$estrategia->validateByCedula()} <br>";
// echo "<br> <br> ==> sql estrategia->getCampanas()" . json_encode($estrategia->getCampanas()) . "<br>";

   // echo "depurar";

   $campana =  new Campanas($strEstrategia, $llave);
   $campana->getCampanas();
   // $campana->getInfoByIdCampana();


   $backOffice = new BackOffice($strEstrategia, $llave);
   $backOffice->getBackOffice();


   $emailSalientes  = new EmailSalientes($strEstrategia, $llave);
   $emailSalientes->getEmailSalientes();


   $smsSalientes  = new SMSsalientes($strEstrategia, $llave);
   $smsSalientes->getSMS();

   $bot = new Bot($strEstrategia, $llave);
   $bot->getBot();

   $estrategia->buscarRepetidos($campana, $backOffice, $emailSalientes, $smsSalientes, $bot);
   // echo json_encode();


// if (isset($_GET['depurar'])) {

 
// }
