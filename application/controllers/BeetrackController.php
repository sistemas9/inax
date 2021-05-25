<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BeetrackController
 *
 * @author sistemas12
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
class BeetrackController extends Zend_Controller_Inax{
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
        /*$clienteModel = new Application_Model_InicioModel();
        $this->view->clienteList= json_encode($clienteModel->getClients());   */
    }
}

?>