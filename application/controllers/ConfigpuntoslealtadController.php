<?php

class ConfigpuntoslealtadController extends Zend_Controller_Inax{
    
    public function init(){
        try {
            $this->_helper->layout->setLayout('bootstrap');           
        } catch (Zend_Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }
    
    public function indexAction(){
        $this->view->articulos  	= json_encode($this->getArticulos());
        $this->view->datosDataTable = [];//json_encode($this->getDatosDataTable());
    }
    
    public function getArticulos(){
		$resultadoSinUTF = Application_Model_ConfigpuntoslealtadModel::getArticulosEntity();
        return $resultadoSinUTF;
	}

	public function getdatosdatatableAction(){
		$datos = $this->getArticulos();
		foreach ($datos as $Data){
			$aplicaPuntos  = '<i class="fa fa-check" style="color:green"></i>';
			$aplicaPuntos .= '<i class="fa fa-trash-o" style="color:red" aria-hidden="true" onclick="removeAplica(\''.$Data['ItemNumber'].'\')"></i>';
			if ($Data['AplicaPuntos'] === '0'){
				$aplicaPuntos = '';
			}
			$puntosCanje  = '<i class="fa fa-shopping-basket" aria-hidden="true"><b> '.$Data['PuntosAvance'].'</b></i>';
			$puntosCanje .= '<i class="fa fa-trash-o" style="color:red" aria-hidden="true" onclick="removePuntos(\''.$Data['ItemNumber'].'\')"></i>';
			if ($Data['PuntosAvance'] === '0'){
				$puntosCanje = '';
			}
			$dataArti[] = array(
				0 => $Data['ItemNumber'],
				1 => $Data['ProductSearchName'],
				2 => $aplicaPuntos,
				3 => $puntosCanje,
				4 => $Data['AplicaPuntos'],
				5 => $Data['PuntosAvance'],
			);
		}
		$dataArtiCompleto = array('data'=>$dataArti);
		print_r(json_encode($dataArtiCompleto));
		exit();
	}

	public function updatearticulosAction(){
		$puntos = filter_input(INPUT_POST, 'puntosPremia');
		$codigo = filter_input(INPUT_POST, 'codigoArti');
		$result = Application_Model_ConfigpuntoslealtadModel::updateArticulo($codigo,$puntos);
		print_r(json_encode($result));
		exit();
	}	

	public function updateaplicaAction(){
		$codigo = filter_input(INPUT_POST, 'codigoArticulo');
		$result = Application_Model_ConfigpuntoslealtadModel::updateAplica($codigo);
		print_r(json_encode($result));
		exit();
	}

	public function removeaplicapuntosAction(){
		$codigo = filter_input(INPUT_POST, 'codigo');
		$result = Application_Model_ConfigpuntoslealtadModel::removePuntosArticulo($codigo);
		print_r(json_encode($result));
		exit();
	}

	public function removepuntosAction(){
		$codigo = filter_input(INPUT_POST, 'codigo');
		$result = Application_Model_ConfigpuntoslealtadModel::removePuntos($codigo);
		print_r(json_encode($result));
		exit();
	}
}