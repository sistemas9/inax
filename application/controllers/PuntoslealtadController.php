<?php
	ini_set("memory_limit", "1024M");
	// server should keep session data for AT LEAST 1 hour
	session_set_cookie_params(86400);
	date_default_timezone_set('America/Chihuahua');
	ini_set('session.gc_maxlifetime', 86400);
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
	class PuntoslealtadController extends Zend_Controller_Inax{
		public function init(){
	        if(empty($_SESSION['userInax'])){
	            $this->_redirect('/login');
	        }
	        else{
	            require_once (LIBRARY_PATH.'/includes/makeTicket.php');
	            /* Initialize action controller here */
		        try {
		            //$this->_helper->layout()->disableLayout();
		           $this->_helper->layout->setLayout('bootstrap');           
		        } catch (Zend_Exception $exc) {
		            echo $exc->getTraceAsString();
		        }
	        }
	    }

	    public function indexAction(){
			$this->view->cliente    = json_encode($this->getClients());
			$this->view->artPremio  = json_encode($this->getArticulos());
			$workers 				= Application_Model_InicioModel::getUsuariosEntity();
			$personnelNumber 		= array_filter($workers,function($k,$v){
														$usuario = $_SESSION['userInax'];
														if ($k->EDUCATION === $usuario){
															$perNumber = array($k->PERSONNELNUMBER);
															return ($perNumber);
														}
													});
			foreach($personnelNumber as $Data){
				$perNum = $Data->PERSONNELNUMBER;
			}
			$this->view->worker 	= (empty($personnelNumber)) ? '2465' : $perNum;
		}

		public function getClients(){
			$resultadoSinUTF = Application_Model_PuntoslealtadModel::getClientes();
			
			$resultClientes  = [];
        	foreach($resultadoSinUTF as $Data){
                array_push($resultClientes, array('ClaveCliente' =>$Data['CustomerAccount'],
                                                  'Nombre'=> utf8_encode($Data['OrganizationName']),
                                                  'DescuentoLinea' => $Data['LineDiscountCode'],
                                                  'ConjuntoPrecios' => $Data['DiscountPriceGroupId'],
                                                  'PuntosAvanceLealtad' => $Data['puntosAvance']
                                            ));
            }
            return $resultClientes;
		}

		public function getArticulos(){
			$resultadoSinUTF = Application_Model_PuntoslealtadModel::getArticulosEntity();
            return $resultadoSinUTF;
		}

		public function newdocumentAction(){
		    $result = Application_Model_PuntoslealtadModel::setHeader($_POST);
			print_r(json_encode($result));
			exit();
		}

		public function setlinesAction(){
			$lineas = [];
			foreach($_POST['lines'] as $Data){
				$result = Application_Model_PuntoslealtadModel::setLines($Data);
				array_push($lineas, $result);
			}
			print_r(json_encode($lineas));
			exit();
		}
		public function updatepuntosAction(){
			$puntosRestantes = filter_input(INPUT_POST, 'puntosRestantes');
			$cliente = filter_input(INPUT_POST, 'cliente');
			$result = Application_Model_PuntoslealtadModel::updatePuntos($cliente,$puntosRestantes);
			print_r(json_encode($result));
			exit();
		}
	}
?>