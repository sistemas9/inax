<?php
	class BuscarPreciosController extends Zend_Controller_Inax{
		public function init(){
	        /* Initialize action controller here */
	        try {
	            //$this->_helper->layout()->disableLayout();
	           $this->_helper->layout->setLayout('bootstrap');           
	        } catch (Zend_Exception $exc) {
	            echo $exc->getTraceAsString();
	        }
	    }

		public function indexAction(){
			$this->view->cliente 	= json_encode($this->getClients());//json_encode(Application_Model_InicioModel::getClients(''));
			$this->view->art 		= json_encode(Application_Model_InicioModel::getItems($sucu));
        	$this->view->art2 		= json_encode(Application_Model_InicioModel::getItemsCommon());
		}

		public function minimoventaAction(){
			$item 		 	= filter_input(INPUT_POST, 'item');
			$minimoVenta 	= Application_Model_InicioModel::getMinimoVenta($item);
			$resultMinimo 	= $minimoVenta;
			if (empty($minimoVenta)){
				$resultMinimo = array('ITEMID' => $item,'INVENTSITEID' => 'CHIH','MULTIPLEQTY' => '1');
			}
			print_r(json_encode($resultMinimo));
			exit();
		}

		public function datosprecioAction(){
			$articulo 		 = filter_input(INPUT_POST, 'articulo');
			$linedisc 		 = filter_input(INPUT_POST, 'linedisc');
			$conjuntoprecios = filter_input(INPUT_POST, 'conjuntoprecios');
			$cantidad 		 = filter_input(INPUT_POST, 'cantidad');
			$data 			 = Application_Model_BuscarPreciosModel::getPriceData($articulo,$cantidad,$linedisc,$conjuntoprecios);
			print_r(json_encode($data));
			exit();
		}

		public function getClients(){
			$conn = new DB_ConexionExport();
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $query = $conn->prepare("SELECT OrganizationName,CustomerAccount,LineDiscountCode,DiscountPriceGroupId FROM [aytExport].[dbo].[CustCustomerV3Staging]");
            $query->execute();
            $resultadoSinUTF = $query->fetchAll(PDO::FETCH_ASSOC);
			
			$resultClientes = [];
        	foreach($resultadoSinUTF as $Data){
                array_push($resultClientes, array('ClaveCliente' =>$Data['CustomerAccount'],
                                                  'Nombre'=> utf8_encode($Data['OrganizationName']),
                                                  'DescuentoLinea' => $Data['LineDiscountCode'],
                                                  'ConjuntoPrecios' => $Data['DiscountPriceGroupId']
                                            ));
            }
            return $resultClientes;
		}
	}
?>