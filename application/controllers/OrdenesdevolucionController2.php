<?php
ini_set("memory_limit", "1024M");
class OrdenesdevolucionController extends Zend_Controller_Inax {
	public function init(){
		try {
			$this->_helper->layout->setLayout('bootstrap');           
		} catch (Zend_Exception $exc) {
			echo $exc->getTraceAsString();
		}
	}
    
    public function indexAction(){
		if (! isset($_SESSION['userInax'])) {
			return $this->_helper->redirector->gotoUrl('../public/login');
		}		
		//print_r($cliente);exit();
	}

    public function getReturnOrderHeadersAction(){
		$model = new Application_Model_ordenesDevolucionModel();  
    	// print_r("expression");exit();
		$this->json($model->getReturnOrderHeaders(filter_input(INPUT_POST,'cliente'),filter_input(INPUT_POST,'factura')));
    	// print_r("expression");exit();
	}

	public function devolverAction(){
		$model = new Application_Model_ordenesDevolucionModel();  
		$this->json($model->devolverFact());
    	// print_r("expression");exit();
	}

	public function razonesAction(){
		$model = new Application_Model_ordenesDevolucionModel();  
		$this->json($model->returnReason());
    	// print_r("expression");exit();
	}

	public function clientesAction(){
		$cliente = json_encode(Application_Model_InicioModel::getClients(''));	
		print_r($cliente);exit();
		return $cliente;	  
		$this->json($cliente);
    	// print_r("expression");exit();
	}

	public function validateAction(){
		$factura = filter_input(INPUT_POST,'factura');
		$result = Application_Model_ordenesDevolucionModel::validateInvoice($factura);
		print_r(json_encode($result));
		exit();
	}
}