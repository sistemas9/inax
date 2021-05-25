<?php
	class MinimoVentasDomicilioController extends Zend_Controller_Inax{
		public function init(){
	        /* Initialize action controller here */
	        try {
	           $this->_helper->layout->setLayout('bootstrap');           
	        } catch (Zend_Exception $exc) {
	            echo $exc->getTraceAsString();
	        }
	    }

	    public function indexAction(){

	    }

	    public function getdataminimosAction(){
	    	$result    = Application_Model_MinimoVentasDomicilioModel::getDataMinimos();
	    	$dataTable = array('data' => $result);
	    	print_r(json_encode($dataTable));
	    	exit();
	    }

	    public function updateminimosAction(){
	    	$montoEfe  = filter_input(INPUT_POST, 'montoEfe');
	    	$montoTarj = filter_input(INPUT_POST, 'montoTarj');
	    	$idMonto   = filter_input(INPUT_POST, 'idMonto');

	    	$result    = Application_Model_MinimoVentasDomicilioModel::updateMinimos($montoEfe,$montoTarj,$idMonto);
	    	print_r(json_encode($result));
	    	exit();
	    }

	    public function updatestatusAction(){
	    	$idMonto   = filter_input(INPUT_POST, 'idMonto');
	    	$status    = filter_input(INPUT_POST, 'status');

	    	$result    = Application_Model_MinimoVentasDomicilioModel::updateStatus($idMonto, $status);
	    	print_r(json_encode($result));
	    	exit();
	    }

	    public function getmininosventasdataAction(){
	    	$sitio  = filter_input(INPUT_POST, 'sitio');
	    	$result = Application_Model_MinimoVentasDomicilioModel::getMininosVentasData($sitio);
	    	print(json_encode($result));
	    	exit();
	    }
	}
?>