<?php

class JsonController extends Zend_Controller_Inax {
	public function init(){
		try {
			$this->_helper->layout->setLayout('bulma');
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

}