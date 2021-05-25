<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once (LIBRARY_PATH.'/includes/tokenClass/tokenClass.php');
class Application_Model_OrdenModel {
    
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
    public function getCabezeraOrden($param, $isPublic = false) {
        // $query = $this->_adapter->prepare(GET_COTIZACION_CABEZERA);
        // $query->bindParam(1,$param);
        // $query->execute();      
        // $result=$query->fetch();
        if($isPublic)
          $result = $this->getCabezeraOrdenEntity($param, true);
        else 
          $result = $this->getCabezeraOrdenEntity($param); 
        return $result;
    }

    public function getThisByThis($x){

      $startWith = substr($x, 0, 2);

      $curl = curl_init();
      if ($startWith == 'OV' || $startWith == 'AT') {
        # QuotationNumber 4 SalesOrderNumber
        $url = "https://".DYNAMICS365."/Data/SalesOrderHeaders?%24select=QuotationNumber&%24filter=SalesOrderNumber%20eq%20'".$x."'%20and%20dataAreaId%20eq%20'" .COMPANY."'";
        $selectResult = 'QuotationNumber';
        }
      else {
        $url = "https://".DYNAMICS365."/Data/SalesQuotationHeaders?%24select=GeneratedSalesOrderNumber&%24filter=SalesQuotationNumber%20eq%20'".$x."'%20and%20dataAreaId%20eq%20'ATP'&%24top=1";
        $selectResult = 'GeneratedSalesOrderNumber';
      }

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
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
        $response = curl_exec($curl);

        $err = curl_error($curl);
        if ($err) {
          $resultR = "cURL Error #:" . $err;
        } else {
          $resultR = json_decode($response);
        }

        $err = curl_error($curl);
        curl_close($curl);

        $r = $resultR->value[0]->$selectResult;
      
      return $r;
    }

    public function dataType($x){
      $startWith = substr($x, 0, 2);
      if ($startWith == 'OV' || $startWith == 'AT') {
        $dataType = 'OV';
      }
      else {
        $dataType = 'COT';
      }
      return $dataType;
    }

    public function getCabezeraOrdenEntity($param, $isPublic){

        $dataType = $this->dataType($param);

        if ($dataType == 'OV') {
          $quotationNumber = $this->getThisByThis($param); 
          $orderNumber = $param;
        }
        else {
          $quotationNumber = $param; 
          $orderNumber = $this->getThisByThis($param); ;
        }

        

        $dataOV = $orderNumber;
        $company = $isPublic? 'ATP' : COMPANY;
        $curl = curl_init();
        $consulta = "https://".DYNAMICS365."/Data/SalesQuotationHeaders?%24select=CustomersOrderReference%2CGeneratedSalesOrderNumber%2CReceiptDateRequested%2CSalesQuotationNumber%2CQuotationTakerPersonnelNumber%2CQuotationTotalChargesAmount%2CSalesQuotationExpiryDate%2CSalesQuotationFollowUpDate%2CDeliveryAddressName%2CPaymentTermsName%2CDeliveryAddressStreet%2CDeliveryAddressCountyId%2CDeliveryAddressCity%2CDeliveryAddressStateId%2CDeliveryAddressCountryRegionId%2CDeliveryAddressZipCode%2CFormattedDeliveryAddress%2CDefaultShippingSiteId%2CDefaultShippingWarehouseId%2CCurrencyCode%2CTotalDiscountPercentage&%24filter=SalesQuotationNumber%20eq%20'".$quotationNumber."'%20and%20dataAreaId%20eq%20'" .COMPANY."'";

        $consulta = "https://".DYNAMICS365."/Data/SalesOrderHeaders?%24filter=SalesOrderNumber%20eq%20'".$dataOV."'%20and%20dataAreaId%20eq%20'".$company."'";
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => $consulta,
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

        $response = curl_exec($curl);
        $err = curl_error($curl);

        if ($err) {
          $resultR = "cURL Error #:" . $err;
        } else {
          $resultR = json_decode($response);
        }
          /* Nuevo cURL */

          $queryDataCte = "SELECT ADDRESSSTREET,ADDRESSCITY,'N/A' AS ADDRESSCOUNTY,ADDRESSZIPCODE,DELIVERYADDRESSCOUNTRYREGIONID,DELIVERYADDRESSSTATE,ORGANIZATIONNUMBER FROM CustCustomerV3Staging WHERE CUSTOMERACCOUNT = '".$resultR->value[0]->InvoiceCustomerAccountNumber."';";
        $conn         = new DB_ConexionExport();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query2       = $conn->query($queryDataCte);
        $query2->execute();
        $r2           = $query2->fetchAll(PDO::FETCH_OBJ);

        // $ORGANIZATIONNUMBER = $r2[0]->ORGANIZATIONNUMBER;
        $model = new Application_Model_CotizacionModel();
        $ORGANIZATIONNUMBER = $model->getOrganizationNumber($resultR->value[0]->InvoiceCustomerAccountNumber);
        $sohInvoiceCustomerAccountNumber = $resultR->value[0]->InvoiceCustomerAccountNumber;
        $ov = $resultR->value[0]->GeneratedSalesOrderNumber;

                  $urlLines = "https://".DYNAMICS365."/Data/SalesOrderLines?%24filter=SalesOrderNumber%20eq%20%27".$dataOV."%27%20and%20dataAreaId%20eq%20%27" .$company."%27";

        curl_setopt_array($curl, array(
          CURLOPT_URL => $urlLines,
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



      $resultR2 = json_decode($response2);

      

      $resultR->value[0]->SalesQuotationLine = $resultR2->value;
      $ov = $resultR->value[0]->GeneratedSalesOrderNumber;
        if ($ov == '') {
        $ov = 'SIN OV';
        }

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://".DYNAMICS365."/Data/STF_WHSWorkLineEntity?%24filter=OrderNum%20eq%20%27".$dataOV."%27",
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
  $responseArr = json_decode($response3);
  $workID = [];
  foreach ($responseArr->value as $key => $value) {
    array_push($workID,$value->WorkId);
  }
        
  $err = curl_error($curl);
  curl_close($curl);

        // ($resultR->value[0]->GeneratedSalesOrderNumber=='')?'SIN OV':$resultR->value[0]->GeneratedSalesOrderNumber;
        $result = array('vendedor'          => $resultR->value[0]->OrderTakerPersonnelNumber
                        ,'Fecha'            => $resultR->value[0]->RequestedReceiptDate
                        ,'FechaVencimiento' => ''
                        ,'Pago'             => $resultR->value[0]->PaymentTermsName
                          ,'NombreEntrega'    => $resultR->value[0]->SalesOrderName
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
                        ,'OV'               => $dataOV
                        ,'WORKERSID'        => $workID
                        ,'impuestos'        => $resultR->value[0]->SalesTaxGroupCode
                        ,'QUOTATIONNUMBER'  => $quotationNumber
                        ,'COMENTARIOS'      => $resultR->value[0]->CustomersOrderReference
                        ,'InvoiceCustomerAccountNumber'      => $sohInvoiceCustomerAccountNumber
                        ,'OrganizationNumber' => $ORGANIZATIONNUMBER
                        ,'DeliveryModeCode' => $resultR->value[0]->DeliveryModeCode
                        ,'PaymentMethodName'=> $resultR->value[0]->CustomerPaymentMethodName
                        
                    );
        return $result;
    }
    /**
     * 
     * @param type $id
     * @return Array
     */
    public function getVendedor($id) {
        // $query = $this->_adapter->prepare(GET_VENDEDOR);
        // $query->bindParam(1,$id);
        // $query->execute();      
        // $result=$query->fetch();
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
    /**
     * 
     * @param type $cotizacion
     * @return Array
     */
    public function getDireccion($cotizacion){
        $queryDirecion = $this->_adapter->prepare(QUERY_COTIZACION_2);
        $queryDirecion->bindParam(1,$cotizacion);
        $queryDirecion->execute();
        $resultDireccion = $queryDirecion->fetch();
        return  $resultDireccion;
    } 
    /**
     * 
     * @param type $cotizacion
     * @return type
     */
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
