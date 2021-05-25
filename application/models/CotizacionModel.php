<?php

require_once (LIBRARY_PATH.'/includes/tokenClass/tokenClass.php');
class Application_Model_CotizacionModel {
    
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
    public function getCabezeraCotizacion($param,$company) {
        $result = $this->getCabezeraCotizacionEntity($param,$company);
        return $result;
    }

    public function getOrganizationNumber($customer){
      $curl = curl_init();

      curl_setopt_array($curl, [
        CURLOPT_URL => "https://ayt.operations.dynamics.com/Data/CustomersV3?%24filter=CustomerAccount%20eq%20'$customer'&%24select=OrganizationNumber%2CAddressStreet%2CAddressCity%2CAddressZipCode%2CDeliveryAddressCountryRegionId%2CDeliveryAddressState",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_COOKIE => "ApplicationGatewayAffinity=e7fb295f94cb4b5e0cd1e2a4712e4a803fc926342cc4ecca988f29125dbd4b04",
        CURLOPT_HTTPHEADER => [
          "Authorization: Bearer ".$this->token."",
          "Content-Type: application/json",
          "accept: application/json"
        ],
      ]);

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);

      if ($err) {
        echo "cURL Error #:" . $err;
      } else {
        $result = json_decode($response);
        return $result->value[0]->OrganizationNumber;
      }
    }

    public function getThisByThis($x){
      $startWith = substr($x, 0, 2);
      $db = new Application_Model_UserinfoMapper();
      $adapter = $db->getAdapter();

      if ($startWith == 'OV' || $startWith == 'AT') {
        # QuotationNumber 4 SalesOrderNumber
        if (!$_SESSION['offline']){
          $curl = curl_init();
          $url = "https://".DYNAMICS365."/Data/SalesOrderHeaders?%24select=QuotationNumber&%24filter=SalesOrderNumber%20eq%20'".$x."'%20and%20dataAreaId%20eq%20'" .COMPANY."'";

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
        }else{
          $queryOV = "SELECT * FROM SalesOrderHeadersOffline WHERE SalesOrderNumber = '".$x."' AND dataAreaId = '".COMPANY."';";
          $query = $adapter->query($queryOV);
          $query->execute();
          $r = $query->fetchAll(PDO::FETCH_ASSOC);
          $r = $r[0]['SalesQuotationNumber'];
        }
        if ($err) {
          $resultR = "cURL Error #:" . $err;
        } else {
          $resultR = json_decode($response);
        }

        $err = curl_error($curl);
        curl_close($curl);

        $r = $resultR->value[0]->QuotationNumber;
      }
      else {
        $r = $x;
      }
      return $r;
    }

    public function getCabezeraCotizacionEntity($param,$comp){
        $quotationNumber = $this->getThisByThis($param);
        $db = new Application_Model_UserinfoMapper();
        $adapter = $db->getAdapter();
        if(!$_SESSION['offline']){
          $company = COMPANY;
          if (COMPANY == ''){
            $company = $comp;
          }
          $curl = curl_init();
          $consulta = "https://".DYNAMICS365."/Data/SalesQuotationHeaders?%24select=CustomersReference%2CGeneratedSalesOrderNumber%2CReceiptDateRequested%2CSalesQuotationNumber%2CQuotationTakerPersonnelNumber%2CQuotationTotalChargesAmount%2CSalesQuotationExpiryDate%2CSalesQuotationFollowUpDate%2CDeliveryAddressName%2CPaymentTermsName%2CDeliveryAddressStreet%2CDeliveryAddressCountyId%2CSalesTaxGroupCode%2CDeliveryAddressCity%2CDeliveryAddressStateId%2CDeliveryAddressCountryRegionId%2CDeliveryAddressZipCode%2CFormattedDeliveryAddress%2CDefaultShippingSiteId%2CDefaultShippingWarehouseId%2CCurrencyCode%2CTotalDiscountPercentage%2CInvoiceCustomerAccountNumber%2CSalesQuotationName%2CInvoiceAddressStreet%2CInvoiceAddressCountyId%2CInvoiceAddressCity%2CInvoiceAddressStateId%2CInvoiceAddressCountryRegionId%2CInvoiceAddressZipCode&%24filter=SalesQuotationNumber%20eq%20'".$quotationNumber."'%20and%20dataAreaId%20eq%20'" .$company."'";

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

          $responseCte = json_decode($response);

          $queryDataCte = "SELECT ADDRESSSTREET,ADDRESSCITY,'N/A' AS ADDRESSCOUNTY,ADDRESSZIPCODE,DELIVERYADDRESSCOUNTRYREGIONID,DELIVERYADDRESSSTATE,ORGANIZATIONNUMBER FROM CustCustomerV3Staging WHERE CUSTOMERACCOUNT = '".$responseCte->value[0]->InvoiceCustomerAccountNumber."';";
          $conn         = new DB_ConexionExport();
          $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          $query2       = $conn->query($queryDataCte);
          $query2->execute();
          $r2           = $query2->fetchAll(PDO::FETCH_OBJ);

          // $ORGANIZATIONNUMBER = $r2[0]->ORGANIZATIONNUMBER;
          $ORGANIZATIONNUMBER =  $this->getOrganizationNumber($responseCte->value[0]->InvoiceCustomerAccountNumber);
        }else{
          $queryOV = "SELECT * FROM CotizacionesHeadersOffline WHERE SalesQuotationNumber = '".$quotationNumber."' AND dataAreaId = '".COMPANY."';";
          $query = $adapter->query($queryOV);
          $query->execute();
          $r = $query->fetchAll(PDO::FETCH_OBJ);
          
          $queryDataCte = "SELECT ADDRESSSTREET,ADDRESSCITY,'N/A' AS ADDRESSCOUNTY,ADDRESSZIPCODE,DELIVERYADDRESSCOUNTRYREGIONID,DELIVERYADDRESSSTATE,ORGANIZATIONNUMBER FROM CustCustomerV3Staging WHERE CUSTOMERACCOUNT = '".$r[0]->RequestingCustomerAccountNumber."';";
          $conn         = new DB_ConexionExport();
          $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          $query2       = $conn->query($queryDataCte);
          $query2->execute();
          $r2           = $query2->fetchAll(PDO::FETCH_OBJ);
          $CALLE        = $r2[0]->ADDRESSSTREET;
          $CIUDAD       = $r2[0]->ADDRESSCITY;
          $COLONIA      = $r2[0]->ADDRESSCOUNTY;
          $ZIPCODE      = $r2[0]->ADDRESSZIPCODE;
          $ESTADO       = $r2[0]->DELIVERYADDRESSSTATE;
          $PAIS         = $r2[0]->DELIVERYADDRESSCOUNTRYREGIONID;
          $r[0]->DeliveryAddressStreet          = $CALLE;
          $r[0]->DeliveryAddressCity            = $CIUDAD;
          $r[0]->DeliveryAddressCountyId        = $COLONIA;
          $r[0]->DeliveryAddressZipCode         = $ZIPCODE;
          $r[0]->DeliveryAddressStateId         = $ESTADO;
          $r[0]->DeliveryAddressCountryRegionId = $PAIS;
          $ORGANIZATIONNUMBER = $r2[0]->ORGANIZATIONNUMBER;
          $response = [];
          $response['value'] = $r;
          $response = json_encode($response);
        }

        if ($err) {
          $resultR = "cURL Error #:" . $err;
        } else {
          $resultR = json_decode($response);
        }
          /* Nuevo cURL */
        if(!$_SESSION['offline']){
          $urlLines = "https://".DYNAMICS365."/Data/SalesQuotationLines?%24filter=SalesQuotationNumber%20eq%20%27".$quotationNumber."%27%20and%20dataAreaId%20eq%20%27" .$company."%27";

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
        }else{
          $queryCOT = "SELECT * FROM CotizacionesLinesOffline WHERE SalesQuotationNumber = '".$quotationNumber."' AND dataAreaId = '".COMPANY."';";
          $query = $adapter->query($queryCOT);
          $query->execute();
          $r = $query->fetchAll(PDO::FETCH_OBJ);
          $response2 = [];
          $response2['value'] = $r;
          $response2 = json_encode($response2);
        }


        $resultR2 = json_decode($response2);
        $resultR->value[0]->SalesQuotationLine = $resultR2->value;
        $sohInvoiceCustomerAccountNumber = (!$_SESSION['offline'])?$resultR->value[0]->InvoiceCustomerAccountNumber:$resultR->value[0]->RequestingCustomerAccountNumber;
        $ov = $resultR->value[0]->GeneratedSalesOrderNumber;
        if ($ov == '') {
          $ov = 'SIN OV';
        }

        if (!$_SESSION['offline']){
          curl_setopt_array($curl, array(
            CURLOPT_URL => "https://".DYNAMICS365."/Data/STF_WHSWorkLineEntity?%24filter=OrderNum%20eq%20%27".$ov."%27",
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
        }else{
          $workID = [];
        }

        if (!$_SESSION['offline']){
          $fechaVencimiento  = $resultR->value[0]->SalesQuotationExpiryDate;
          $fechaVencimiento  = $resultR->value[0]->ReceiptDateRequested;
        }else{
          $fecharVencimiento = $resultR->value[0]->CreatedDatetime;
        }

        $stop_date = substr($fechaVencimiento, 0, -10) .' 20:00:00';
        $stop_date = date('Y-m-d', strtotime($stop_date . ' +1 day'));
        $fechaVencimiento = $stop_date;
        // ($resultR->value[0]->GeneratedSalesOrderNumber=='')?'SIN OV':$resultR->value[0]->GeneratedSalesOrderNumber;
        $result = array('vendedor'                      => $resultR->value[0]->QuotationTakerPersonnelNumber
                        ,'Fecha'                        => (!$_SESSION['offline'])?$resultR->value[0]->ReceiptDateRequested:$resultR->value[0]->CreatedDatetime
                        ,'FechaVencimiento'             => $fechaVencimiento
                        ,'Pago'                         => $resultR->value[0]->PaymentTermsName
                        ,'NombreEntrega'                => $resultR->value[0]->SalesQuotationName
                        ,'Sitio'                        => $resultR->value[0]->DefaultShippingSiteId
                        ,'Almacen'                      => $resultR->value[0]->DefaultShippingWarehouseId
                        ,'Moneda'                       => $resultR->value[0]->CurrencyCode
                        ,'ComentarioLinea'              => ''
                        ,'cargos'                       => $resultR->value[0]->QuotationTotalChargesAmount
                        ,'porcDesc'                     => $resultR->value[0]->TotalDiscountPercentage
                        ,'CALLE'                        => $resultR->value[0]->DeliveryAddressStreet
                        ,'COLONIA'                      => $resultR->value[0]->DeliveryAddressCountyId
                        ,'CIUDAD'                       => $resultR->value[0]->DeliveryAddressCity
                        ,'ESTADO'                       => $resultR->value[0]->DeliveryAddressStateId
                        ,'PAIS'                         => $resultR->value[0]->DeliveryAddressCountryRegionId
                        ,'ZIPCODE'                      => $resultR->value[0]->DeliveryAddressZipCode
                        ,'COMPLETA'                     => $resultR->value[0]->FormattedDeliveryAddress
                        ,'LINES'                        => $resultR->value[0]->SalesQuotationLine
                        ,'impuestos'                    => $resultR->value[0]->SalesTaxGroupCode
                        ,'OV'                           => $ov
                        ,'WORKERSID'                    => $workID
                        ,'QUOTATIONNUMBER'              => $quotationNumber
                        ,'COMENTARIOS'                  => $resultR->value[0]->CustomersReference
                        ,'InvoiceCustomerAccountNumber' => $sohInvoiceCustomerAccountNumber
                        ,'OrganizationNumber'           => $ORGANIZATIONNUMBER
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
