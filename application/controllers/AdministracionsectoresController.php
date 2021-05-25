<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ComisionesController
 *
 * @author sistemas6
 */
class AdministracionSectoresController extends Zend_Controller_Inax{
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

    public function xdebug($what) {
        echo '<pre>';
        if ( is_array( $what ) )  {
            print_r ( $what );
        } else {
            var_dump ( $what );
        }
        echo '</pre>';
    }

    public function indexAction() {
        //$customerPurchases = Application_Model_ComprasClienteModel::getClientPurchases($custid);
        //$this->xdebug($customer);
        $this->view->segmentsArray =  Application_Model_AdministracionSectoresModel::getSegment();

    }

    public function getfamilyAction(){
        $this->_helper->layout()->disableLayout();
        $this->view->familyArray =  Application_Model_AdministracionSectoresModel::getFamily();
        extract($_POST);       
        $this->view->familyRelationsArray =  Application_Model_AdministracionSectoresModel::getSegmentFamilies($segment);
        $this->view->segmentId = $segment;

    }

    public function setrelationAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        extract($_POST);
        $familyString = implode(',',$familySelect);
        $this->xdebug($familyString);
        $this->xdebug(Application_Model_AdministracionSectoresModel::setRelation($segmentId,$familyString));
    }
}
