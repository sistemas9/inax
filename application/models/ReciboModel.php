<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once (LIBRARY_PATH.'/includes/tokenClass/tokenClass.php');
class Application_Model_ReciboModel {
    
    public $db;
    public $_adapter;
    public $token;
    
    public function __construct(array $options = null){
        if (is_array($options)) {
            $this->setOptions($options);
        }
        $this->db = new Application_Model_UserinfoMapper();
        $this->_adapter = $this->db->getAdapter();
        $query=$this->_adapter->query(ANSI_NULLS);
        $query=$this->_adapter->query(ANSI_WARNINGS);
        $query->execute();
        $token = new Token();
        $tokenTemp = $token->getToken();
        $this->token = $tokenTemp[0]->Token;
        return $this->_adapter;
    }
    public function getCargo($cotizacionID){
        $query = $this->_adapter->prepare(GET_CARGOS_COT);
        $query->bindParam(1,$cotizacionID);
        $query->execute();      
        $result=$query->fetchAll();
        return $result;
    }
    /*
     *obtiene la cabcera de la cotizacion 
     */
    public function getCabezeraCotizacion($param) {
        // $query = $this->_adapter->prepare(GET_COTIZACION_CABEZERA);
        // $query->bindParam(1,$param);
        // $query->execute();      
        // $result=$query->fetch();
        $result = $this->getCabezeraCotizacionEntity($param); 
        return $result;
    }

    public function getCabezeraCotizacionEntity($param){
        $curl = curl_init();
        # &%24expand=SalesQuotationLine&cross-company=true

        // https://tes-ayt.sandbox.operations.dynamics.com/Data/SalesQuotationHeaders?%24
        //   select=
        //     SalesQuotationNumber%2C
        //     QuotationTakerPersonnelNumber%2C
        //     QuotationTotalChargesAmount%2C
        //     SalesQuotationExpiryDate%2C
        //     SalesQuotationFollowUpDate%2C
        //     DeliveryAddressName%2C
        //     PaymentTermsName%2C
        //     DeliveryAddressStreet%2C
        //     DeliveryAddressCountyId%2C
        //     DeliveryAddressCity%2C
        //     DeliveryAddressStateId%2C
        //     DeliveryAddressCountryRegionId%2C
        //     DeliveryAddressZipCode%2C
        //     FormattedDeliveryAddress%2C
        //     DefaultShippingSiteId%2C
        //     DefaultShippingWarehouseId%2C
        //     CurrencyCode%2C
        //     TotalDiscountPercentage&%24
        //   filter=
        //     SalesQuotationNumber%20
        //       eq%20
        //         'ATP-001924'%20and%20
        //       dataAreaId%20
        //       eq%20
        //         'ATP'

        # https://tes-ayt.sandbox.operations.dynamics.com/Data/SalesQuotationLine$filter=SalesQuotationNumber%20eq%20'ATP-001924'%20and%20dataAreaId%20eq%20'ATP'



        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesQuotationHeaders?%24select=SalesQuotationNumber%2CQuotationTakerPersonnelNumber%2CQuotationTotalChargesAmount%2CSalesQuotationExpiryDate%2CSalesQuotationFollowUpDate%2CDeliveryAddressName%2CPaymentTermsName%2CDeliveryAddressStreet%2CDeliveryAddressCountyId%2CDeliveryAddressCity%2CDeliveryAddressStateId%2CDeliveryAddressCountryRegionId%2CDeliveryAddressZipCode%2CFormattedDeliveryAddress%2CDefaultShippingSiteId%2CDefaultShippingWarehouseId%2CCurrencyCode%2CTotalDiscountPercentage&%24filter=SalesQuotationNumber%20eq%20'".$param."'%20and%20dataAreaId%20eq%20'" .COMPANY."'",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "authorization: Bearer ".$this->token.""
          ),
        ));
        # https://tes-ayt.sandbox.operations.dynamics.com/data/CustomerPaymentJournalLines?$filter=STF_RefSalesId eq 'OV000001914'&$select=TransactionDate,Voucher,dataAreaId,CreditAmount,CurrencyCode,OffsetAccountType,OffsetAccountDisplayValue
        # https://tes-ayt.sandbox.operations.dynamics.com/data/CustomerPaymentJournalLines?$  filter=STF_RefSalesId eq     'OV000001914'&$    select=TransactionDate,Voucher,dataAreaId,CreditAmount,CurrencyCode,OffsetAccountType,OffsetAccountDisplayValue
        # https://tes-ayt.sandbox.operations.dynamics.com/data/CustomerPaymentJournalLines?%24filter=STF_RefSalesId%20eq%20%27OV000001914%27&%24select=TransactionDate,Voucher,dataAreaId,CreditAmount,CurrencyCode,OffsetAccountType,OffsetAccountDisplayValue
        // print_r(curl_getinfo($curl)['url']);
        // print_r('<hr>');
        // exit();
        
        $response = curl_exec($curl);
        $err = curl_error($curl);

        if ($err) {
          $resultR = "cURL Error #:" . $err;
        } else {
          $resultR = json_decode($response);
        }
          /* Nuevo cURL */

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesQuotationLines?%24filter=SalesQuotationNumber%20eq%20%27".$param."%27%20and%20dataAreaId%20eq%20%27" .COMPANY."%27",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_POSTFIELDS => "",
  CURLOPT_HTTPHEADER => array(
    "authorization: Bearer ".$this->token."",
    "Content-Type: application/json"
  ),
));
$response2 = curl_exec($curl);
$err = curl_error($curl);


$param = 'OV000001914';
curl_setopt_array($curl, array(
  CURLOPT_URL => "https://".DYNAMICS365."/Data/CustomerPaymentJournalLines?%24filter=STF_RefSalesId%20eq%20%27".$param."%27&%24select=TransactionDate,Voucher,dataAreaId,CreditAmount,CurrencyCode,OffsetAccountType,OffsetAccountDisplayValue",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_POSTFIELDS => "",
  CURLOPT_HTTPHEADER => array(
    "authorization: Bearer ".$this->token."",
    "Content-Type: application/json"
  ),
));
$response3 = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);



$resultR2 = json_decode($response2);
$resultR3 = json_decode($response3);



          /**/

        $resultR->value[0]->SalesQuotationLine = $resultR2->value;
        $resultR->value[0]->Pago = $resultR3->value;
        $result = array('vendedor'=> $resultR->value[0]->QuotationTakerPersonnelNumber
                        ,'Fecha'            => $resultR->value[0]->SalesQuotationFollowUpDate
                        ,'FechaVencimiento' => $resultR->value[0]->SalesQuotationExpiryDate
                        ,'Pago'             => $resultR->value[0]->PaymentTermsName
                        ,'NombreEntrega'    => $resultR->value[0]->DeliveryAddressName
                        ,'Sitio'            => $resultR->value[0]->DefaultShippingSiteId
                        ,'Almacen'          => $resultR->value[0]->DefaultShippingWarehouseId
                        ,'Moneda'           => $resultR->value[0]->CurrencyCode
                        ,'ComentarioLinea'  => ''
                        ,'cargos'           => $resultR->value[0]->QuotationTotalChargesAmount
                        ,'porcDesc'         => $resultR->value[0]->TotalDiscountPercentage
                        ,'CALLE'            => $resultR->value[0]->DeliveryAddressStreet
                        ,'COLONIA'          => $resultR->value[0]->DeliveryAddressCountyId
                        ,'CIUDAD'           => $resultR->value[0]->DeliveryAddressCity
                        ,'ESTADO'           => $resultR->value[0]->DeliveryAddressStateId
                        ,'PAIS'             => $resultR->value[0]->DeliveryAddressCountryRegionId
                        ,'ZIPCODE'          => $resultR->value[0]->DeliveryAddressZipCode
                        ,'COMPLETA'         => $resultR->value[0]->FormattedDeliveryAddress
                        ,'LINES'            => $resultR->value[0]->SalesQuotationLine
                        ,'PAGO'            => $resultR->value[0]->Pago
                    );
        return $result;
    }

    public function getVendedor($id) {
        $result = $this->getVendedorEntity($id);
        return $result;
    }

    public function getVendedorEntity($id){
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://".DYNAMICS365."/Data/Workers?%24select=Name%2CPersonnelNumber&%24filter=PersonnelNumber%20eq%20'".$id."'",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "authorization: Bearer ".$this->token."",
            "content-type: application/xml",
            "prefer: odata.maxpagesize=10"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
          $resultR = "cURL Error #:" . $err;
        } else {
          $resultR = json_decode($response);
        }
        $result = array('NAME' => $resultR->value[0]->Name);
        return $result;
    }

    public function getDireccion($cotizacion){
        $queryDirecion = $this->_adapter->prepare(QUERY_COTIZACION_2);
        $queryDirecion->bindParam(1,$cotizacion);
        $queryDirecion->execute();
        $resultDireccion = $queryDirecion->fetch();
        return  $resultDireccion;
    } 

    public function getItems($cotizacion){
        $queryDirecion = $this->_adapter->prepare(GET_ITEMS_COT);
        $queryDirecion->bindParam(1,$cotizacion);
        $queryDirecion->execute();
        $resultDireccion = $queryDirecion->fetchAll();
        return  $resultDireccion;
    }
    
    public function getAlmacenCotiza($id) {
        $queryDirecion = $this->_adapter->prepare(GET_ALMACEN_COTIZACION);
        $queryDirecion->bindParam(1,$id);
        $queryDirecion->execute();
        $resultDireccion = $queryDirecion->fetch();
        return  $resultDireccion;
    }
}
