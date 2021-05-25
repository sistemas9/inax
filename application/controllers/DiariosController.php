<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class DiariosController extends Zend_Controller_Inax{
    public function init(){
        try {
            if(empty($_SESSION['userInax'])){
                $this->_redirect('/login');
            }
            $this->_helper->layout->setLayout('bootstrap');           
        } catch (Zend_Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }
    public function indexAction() {
        $model=new Application_Model_DiariosModel();
        $inicioModel= new Application_Model_InicioModel();
        $this->view->fechaInput = date("Y-m-d");  
        $this->view->facturas= json_encode($model->getFacturasSaldo());
        $this->view->payMode=$inicioModel->getPayMode();        
    } 
    public function getInfoAction(){
        // $model=new Application_Model_DiariosModel();
        // $this->json($model->getDiarios(filter_input(INPUT_POST,'fechai'),filter_input(INPUT_POST,'fechaf')));
    }
    public function getDetailAction() {
        $model=new Application_Model_DiariosModel();
        $this->json($model->getDiarioDetalle(filter_input(INPUT_POST,'diario')));
    }
    public function getsaldoAction() {
        $model=new Application_Model_DiariosModel();
        $this->json($model->getsaldo(filter_input(INPUT_POST,'diario')));
    }
    public function saveDiarioAction(){
        // print_r('hola');exit();    
        // $model=new Application_Model_InicioModel(); 
         $timbre = "";
            if(filter_input(INPUT_POST,"timbrar")){
               $timbre="1";// print_r("se va timbrar");exit();
            } 
        $model=new Application_Model_DiariosModel();  
        $this->json($model->saveDiarios(
                            filter_input(INPUT_POST,"factura"),
                            filter_input(INPUT_POST,"contrapartida"),
                            filter_input(INPUT_POST,"descripcion"),
                            filter_input(INPUT_POST,"diarioMontoFactura"),
                            filter_input(INPUT_POST,"diarioCuentaContra"),
                            filter_input(INPUT_POST,'diarioFPago'),
                            $timbre,
                            filter_input(INPUT_POST,'currencyInput'),
                            filter_input(INPUT_POST,'digitostarjeta')
                        )
                    );
    }

    public function cerrarDiarioAction(){
        $model=new Application_Model_InicioModel();        
        $this->json($model->cerrarDiario(filter_input(INPUT_POST,'diario')));
    }
}