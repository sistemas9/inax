<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ConsultaguiaModel
 *
 * @author sistemas10
 */
class Application_Model_ConsultaguiaModel {
    
    public $db;
    public $_adapter;
    
    public function __construct(array $options = null){
        if (is_array($options)) {
            $this->setOptions($options);
        }
        $this->db = new Application_Model_UserinfoMapper();
        $this->_adapter = $this->db->getAdapter();
        $query=$this->_adapter->query(ANSI_NULLS);
        $query=$this->_adapter->query(ANSI_WARNINGS);
        $query->execute();
        return $this->_adapter;
    }
    /**
     * 
     */
    static function getListByClient($params){
        //return $this->db->Query(CONSULTA_VENTAS_MES, $params);
        //print_r($params['fecha']);exit();
        $token = new Token();
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/data/SalesOrderHeaders?%24filter=InvoiceCustomerAccountNumber%20eq%20'".$params['cliente']."'%20and%20DeliveryModeCode%20eq%20'PAQUETERIA'%20and%20RequestedReceiptDate%20gt%20".$params['fecha']."",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
       "Authorization: Bearer " . $token->getToken()[0]->Token."",
        "content-type: application/json; odata.metadata=minimal",
        "odata-version: 4.0"
        ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);       
        curl_close($curl);
        $response = json_decode($response);
        $nombres = Application_Model_ConsultaguiaModel::getName();
        foreach ($response->value as $key => $value) {
            if($value->OrderTakerPersonnelNumber!=''){
                foreach ($nombres->value as $key2 => $value2) {
                if($value->OrderTakerPersonnelNumber==$value2->PersonnelNumber){    
                $value->OrderTakerPersonnelNumber=$value2->NameAlias;
                }
                }
            }
        }
        // print_r($value->OrderTakerPersonnelNumber);exit(' pp');
        if ($err) {
        return "cURL Error #:" . $err;
        } else {
        return $response;
        }
    }

    static function getName(){
            $token = new Token();
            $curl = curl_init();            
            
            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://".DYNAMICS365."/data/Workers?%24select=NameAlias%2CPersonnelNumber",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "Authorization: Bearer " . $token->getToken()[0]->Token."",
            "content-type: application/json; charset=utf-8",
            "odata-version: 4.0",
            "prefer: return=representation"
            ),
            ));
                   
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $response = json_decode($response);

            if ($err) {
            return "cURL Error #:" . $err;
            } else {
            return $response;//->value[0]->NameAlias;
            }
    }


    
    static function getDetailFromOV($params) {
        //return $this->db->Query(DETALLE_OV,$params);
        $curl = curl_init();
        $token = new Token();
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/data/SalesOrderLines?%24filter=SalesOrderNumber%20eq%20'".$params['ov']."'",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer " . $token->getToken()[0]->Token."",
        "content-type: application/json; odata.metadata=minimal",
        "odata-version: 4.0"
        ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
        return "cURL Error #:" . $err;
        } else {
        return json_decode($response);
        }



    }
}
