<?php

class InventariosController extends Zend_Controller_Inax {
	public function init(){
		try {
			$this->_helper->layout->setLayout('bootstrap');
		} 
		catch (Zend_Exception $exc) {
			echo $exc->getTraceAsString();
		}
	}
    
    public function indexAction(){
		if (! isset($_SESSION['userInax'])) {
			return $this->_helper->redirector->gotoUrl('../public/login');
		}
	}

	public function getInventorySitesOnHandAction(){
		$getInventorySitesOnHand = json_encode(Application_Model_InventariosModel::getInventorySitesOnHand(''));	
		print_r($getInventorySitesOnHand);exit();
		return $getInventorySitesOnHand;	  
		$this->json($getInventorySitesOnHand);
    	// print_r("expression");exit();
	}


}