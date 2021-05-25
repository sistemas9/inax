<?php

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
ini_set('display_errors', '1'); 

ini_set("memory_limit","-1");
class StatuswhsworksController extends Zend_Controller_Inax{

    public function init(){
        if(empty($_SESSION['userInax'])){
            $this->_redirect('/login');
        }
    }

    public function indexAction(){
    	
    }

    public function getdataworklinesAction(){
    	$ov = filter_input(INPUT_POST, 'ov');
    	$result = Application_Model_StatuswhsworksModel::getDataWorkLines($ov);
    	print_r(json_encode($result));
    	exit();
    }

    public function getdataovAction(){
    	$ov = filter_input(INPUT_POST, 'ov');
    	$result = Application_Model_StatuswhsworksModel::getDataOv($ov);
    	print_r(json_encode($result));
    	exit();
    }
}