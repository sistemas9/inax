<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
ini_set('display_errors', '1'); 
ini_set("memory_limit", "1024M");
// server should keep session data for AT LEAST 1 hour
session_set_cookie_params(86400);
date_default_timezone_set('America/Chihuahua');
ini_set('session.gc_maxlifetime', 86400);


class CodigospostalesController extends Zend_Controller_Inax{
	public function init(){
        if(empty($_SESSION['userInax'])){
            $this->_redirect('/login');
        }
        else{
            require_once (LIBRARY_PATH.'/includes/makeTicket.php');
            $this->makeTiket = new makeTicket();
        }
    }

    public function indexAction(){
    }

    public function loadzipcodedataAction(){
    	$result = Application_Model_CodigospostalesModel::loadZipCodeData();
    	print_r(json_encode($result));
    	exit();
    }

    public function updatezipcodedataAction(){
    	$zipCode = filter_input(INPUT_POST,'zipCode');
    	$asentamiento = filter_input(INPUT_POST,'asentamiento');
    	$status = filter_input(INPUT_POST,'status');

    	$result = Application_Model_CodigospostalesModel::updateZipCodeData($zipCode,$asentamiento,$status);
    	print_r(json_encode($result));
    	exit();
    }

    public function savezipcodedataAction(){
		$codigoPostal = filter_input(INPUT_POST,'codigoPostal');
		$colonia = filter_input(INPUT_POST,'colonia');
		$tipoCol = filter_input(INPUT_POST,'tipoCol');
		$municipio = filter_input(INPUT_POST,'municipio');
		$ciudad = filter_input(INPUT_POST,'ciudad');

		$result = Application_Model_CodigospostalesModel::saveZipCodeData($codigoPostal,$colonia,$tipoCol,$municipio,$ciudad);
		print_r(json_encode($result));
		exit();
    }

    public function updatezipcodedataallAction(){
    	$codigoPostal = filter_input(INPUT_POST,'codigoPostal');
		$colonia = filter_input(INPUT_POST,'colonia');
		$tipoCol = filter_input(INPUT_POST,'tipoColonia');
		$municipio = filter_input(INPUT_POST,'municipio');
		$ciudad = filter_input(INPUT_POST,'ciudad');
		$idCodigo = filter_input(INPUT_POST,'idCodigo');

		$result = Application_Model_CodigospostalesModel::updateZipCodeDataAll($codigoPostal,$colonia,$tipoCol,$municipio,$ciudad,$idCodigo);
    	print_r(json_encode($result));
    	exit();
    }
}