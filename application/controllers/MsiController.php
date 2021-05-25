<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
ini_set('display_errors', '1'); 
ini_set("memory_limit", "1024M");
// server should keep session data for AT LEAST 1 hour
session_set_cookie_params(86400);
date_default_timezone_set('America/Chihuahua');
ini_set('session.gc_maxlifetime', 86400);


class MsiController extends Zend_Controller_Inax{
	public function init(){
        if(empty($_SESSION['userInax'])){
            $this->_redirect('/login');
        }
    }

    public function indexAction(){
    }

    public function loadmsidataAction(){
    	$result = Application_Model_MsiModel::LoadMsiData();
    	print_r(json_encode(array('data'=>$result)));
    	exit();
    }

    public function setcargoAction(){
    	$id_tp  = filter_input(INPUT_POST,'id_tp');
    	$cargo  = filter_input(INPUT_POST,'cargo');
    	$monto  = filter_input(INPUT_POST,'monto');
    	$result = Application_Model_MsiModel::SetCargo($id_tp,$cargo,$monto);
    	print_r(json_encode($result));
    	exit();
    }
}
?>