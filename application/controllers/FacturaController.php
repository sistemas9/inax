<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class FacturaController extends Zend_Controller_Action{
    public function init(){
       try {
           $this->_helper->layout->setLayout('bootstrap_single');           
        } catch (Zend_Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }
    function indexAction() {
        
    }

    function consultaAction() {
        $token = new Token();
        $folio =  filter_input(INPUT_POST,'folio');
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://ayt.operations.dynamics.com/Data/SalesInvoiceHeaders?%24select=dataAreaId%2CInvoiceNumber%2CInvoiceDate%2CInvoiceCustomerAccountNumber&%24filter=SalesOrderNumber%20eq%20'".$folio."'",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
        "authorization: Bearer ".$token->getToken()[0]->Token."",
        "content-type: application/json;"
        ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        // $response=json_decode($response);
        print_r($response);exit();
        return $response;

        // $model = new Application_Model_FacturaModel();
        // $folio=  filter_input(INPUT_POST,'folio');
        // $tipo= $model->getTipo($folio);
        // print_r(json_encode($tipo));
        exit();
    }

    function getxmlAction(){
        $token = new Token();
        $folio =  filter_input(INPUT_POST,'folio');
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://ayt.operations.dynamics.com/Data/SalesInvoiceHeaders?%24select=dataAreaId%2CInvoiceNumber%2CInvoiceDate%2CInvoiceCustomerAccountNumber&%24filter=InvoiceNumber%20eq%20'".$folio."'",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
        "authorization: Bearer ".$token->getToken()[0]->Token."",
        "content-type: application/json;"
        ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);

        $response = json_decode($response);
        // print_r($response->value[0]->InvoiceDate);
        $fecha = explode('T',$response->value[0]->InvoiceDate);
            $fechaYMD = explode('-',$fecha[0]);
            $fechaHMS = explode('Z',$fecha[1]);
            $fechagood = $fechaYMD[1] . '/' . $fechaYMD[2] . '/' . $fechaYMD[0];

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://ayt.operations.dynamics.com/api/services/STF_INAX/STF_XMLDoc/getXMLDoc",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "{ \n\t\"invoiceId\": \"".$folio."\", \n\t\"transDate\": \"".$fechagood."\", \n\t\"custInvoiceAccount\": \"".$response->value[0]->InvoiceCustomerAccountNumber."\", \n\t\"company\": \"atp\" \n\t}",
        CURLOPT_HTTPHEADER => array(
        "authorization: Bearer ".$token->getToken()[0]->Token."",
        "odata-version: 4.0"
        ),
        ));
        
        $response2 = curl_exec($curl);
        $err = curl_error($curl);

        header("Content-type: text/xml");
        print_r($response2);exit();    
        
        curl_close($curl);
    }
    
}