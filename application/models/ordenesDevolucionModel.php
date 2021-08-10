<?php
ini_set("memory_limit", "1024M");
class Application_Model_ordenesDevolucionModel{
    public $db;
    public $_adapter;

    public function returnReason(){
    $curl = curl_init();

    $token = new Token();
    curl_setopt_array($curl, array(
    CURLOPT_URL => "https://".DYNAMICS365."/data/STF_ReturnReasonCode?%24select=ReasonCodeId%2CDescription",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_POSTFIELDS => "",
    CURLOPT_HTTPHEADER => array(
    "authorization: Bearer ".$token->getToken()[0]->Token."",
    "odata-version: 4.0"
    ),
    ));
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);
    return json_decode($response);
    }

    public function getReturnOrderHeaders($cliente,$factura){    
        $curl = curl_init();
        $token = new Token();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://".DYNAMICS365."/data/SalesInvoiceHeaders?%24filter=dataAreaId%20eq%20'".COMPANY."'%20and%20InvoiceNumber%20eq%20'".$factura."'&%24select=SalesOrderNumber",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "authorization: Bearer ".$token->getToken()[0]->Token.""
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);

        $response=json_decode($response);
        // print_r();exit();

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/data/SalesOrderLines?%24filter=SalesOrderNumber%20eq%20'".$response->value[0]->SalesOrderNumber."'",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
        "authorization: Bearer ".$token->getToken()[0]->Token.""
        ),
        ));
        $totales=[];
        $response2 = curl_exec($curl);
        $err = curl_error($curl);
        $lotes = json_decode($response2);        
      // print_r($response2);exit();
      foreach ($lotes->value as $key => $value) {
      curl_setopt_array($curl, array(
      CURLOPT_URL => "https://".DYNAMICS365."/api/services/STF_INAX/STF_OrdenDevolucion/checkQuantity",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => "{\n\t\"inventoryLotId\": \"".$value->InventoryLotId."\",\n \"company\": \"".COMPANY."\"\n}",
      CURLOPT_HTTPHEADER => array(
      "authorization: Bearer ".$token->getToken()[0]->Token.""
      ),
      ));
      
      $response = curl_exec($curl);
      $err = curl_error($curl);
      // $total = explode("La cantidad disponible para la asignación de abono: ", $response);
      // $totalbueno = explode(".", $total[1]);
      $totalbueno = (float) filter_var($response, FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
      array_push($totales, str_replace(',', '', $totalbueno));
      }
      // print_r($totales);exit('111111');
      curl_close($curl);
      $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://".DYNAMICS365."/data/Workers?%24select=PersonnelNumber%2CWorkerStatus%2CName",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_HTTPHEADER => array(
          "authorization: Bearer ".$token->getToken()[0]->Token.""
          ),
        ));

        $responseEmployees = curl_exec($curl);
        $errEmployees = curl_error($curl);

        curl_close($curl);

        if ($err) {
          return "cURL Error #:" . $err;
        } else {
          return array('datos'=>json_decode($response2),'totales'=>$totales, 'employees' =>json_decode($responseEmployees));
        }
    }

    public function devolverFact(){
      //print_r($_POST);
      $cliente=$_POST["cliente"];
      $lotes = $_POST["lote"];
      $cantidades = $_POST["cantidad"];
      $razon = $_POST["razon"];
      $ovOrigen = $_POST["ovOrigen"];
      $curl = curl_init();
      $token = new Token();   
      $responsible = $_POST["responsableventa"];  
      $secretary = $_POST["secretarioventa"];
      $doWork = false;
      foreach ($lotes as $key => $data){
        if($cantidades[$key]==0 || $cantidades[$key]=='' || $cantidades[$key]<0.0001){
          //print_r('no voy a devolver nada');
        }else{
          $doWork = true;
          break;
        }
      }

      if (count($lotes) > 0 && $doWork){ 
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://".DYNAMICS365."/api/services/STF_INAX/STF_OrdenDevolucion/returnOrderHeader",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => "{\n\t\"custAccount\":\"".$cliente."\",\n\t\"returnReasonCodeId\":\"".$razon."\",\n\t\"company\":\"".COMPANY."\"\n,\n\t\"WorkerResponsible\":\"".$responsible."\"\n,\n\t\"WorkerTaker\":\"".$secretary."\"\n}",
          CURLOPT_HTTPHEADER => array(
          "authorization: Bearer ".$token->getToken()[0]->Token.""
          ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $response=json_decode($response);
        // print_r($response);exit('ppp');
        foreach ($lotes as $key => $value) {
          # code...
          if($cantidades[$key]==0 || $cantidades[$key]=='' || $cantidades[$key]<0.0001){
            //print_r('no voy a devolver nada');
          }else{
            curl_setopt_array($curl, array(
              CURLOPT_URL => "https://".DYNAMICS365."/api/services/STF_INAX/STF_OrdenDevolucion/returnOrderLine",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => "{\n\t\"inventoryLotId\": \"".$value."\",\n\t\"qty\": \"".$cantidades[$key]."\",\n \"returnOrderNumber\": \"".$response."\",\n \"company\": \"".COMPANY."\"\n}",
              CURLOPT_HTTPHEADER => array(
              "authorization: Bearer ".$token->getToken()[0]->Token.""
              ),
            ));            
            $response2 = curl_exec($curl);
            $err = curl_error($curl);
          }
        }
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://".DYNAMICS365."/data/ReturnOrderHeaders?%24filter=ReturnOrderNumber%20eq%20'".$response."'&%24select=RMANumber%2CDefaultReturnSiteId%2CDefaultReturnWarehouseId",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_HTTPHEADER => array(
          "authorization: Bearer ".$token->getToken()[0]->Token."",
          "odata-version: 4.0"
          ),
        ));
        
        $response3 = curl_exec($curl);
        $err = curl_error($curl);
        $response3=json_decode($response3);
        // print_r("https://ayt.operations.dynamics.com/data/ReturnOrderHeaders?%24filter=ReturnOrderNumber%20eq%20'".$response."'&%24select=RMANumber");

        curl_close($curl);
      }else{
        return array('respuesta'=>'La cantidad','ordenventa'=>null);
      }
      
      if ($err) {
      return "cURL Error #:" . $err;
      } else {
        $dataAlma = self::getInventDataOvOrigenLines($ovOrigen);
        $salesTaxGroupCodeOrigen = self::getTaxGroupOvOrigen($ovOrigen);
        // if ($salesTaxGroupCodeOrigen == 'FRONT'){
          $returnOrderNumber = self::getReturnOrderNumber($response3->value[0]->RMANumber);
          self::patchReturnOrderNumber($returnOrderNumber,$salesTaxGroupCodeOrigen,$dataAlma['sitio'],$dataAlma['almacen']);
        // }
        return array('respuesta'=>$response2,'ordenventa'=>$response3->value[0]->RMANumber);
      }      
    }

    public static function validateInvoice($factura){
      $curl = curl_init();
      $token = new Token();
      curl_setopt_array($curl, array(
          CURLOPT_URL => "https://".DYNAMICS365."/data/SalesInvoiceHeaders?%24filter=dataAreaId%20eq%20'".COMPANY."'%20and%20InvoiceNumber%20eq%20'".$factura."'%20and%20CurrencyCode%20eq%20'MXN'&%24select=SalesOrderNumber,InvoiceCustomerAccountNumber",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_HTTPHEADER => array(
          "accept: application/json",
          "authorization: Bearer ".$token->getToken()[0]->Token.""
          ),
      ));
      $response = curl_exec($curl);
      $err = curl_error($curl);

      if ($error){
        return array('status' => 'error','msg' => 'Error #cURL: '.$error);
      }else{
        $response = json_decode($response);
        if (isset($response->value) && !empty($response->value)){
          $result = array('status' => 'exito', 'msg' => array( $factura, $response->value[0]->InvoiceCustomerAccountNumber, $response->value[0]->SalesOrderNumber ));
        }else{
          $result = array('status' => 'error', 'msg' => 'Esta operación no es posible, favor de revisarlo con su supervisor.');
        }
        return $result;
      }
    }

    public static function getReturnOrderNumber($RMANumber){
      $token = new Token();
      $curl = curl_init();

      curl_setopt_array($curl, [
        CURLOPT_URL => "https://".DYNAMICS365."/Data/ReturnOrderHeaders?=&%24filter=RMANumber%20eq%20'".$RMANumber."'",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_COOKIE => "ApplicationGatewayAffinity=e7fb295f94cb4b5e0cd1e2a4712e4a803fc926342cc4ecca988f29125dbd4b04",
        CURLOPT_HTTPHEADER => [
          "Authorization: Bearer ".$token->getToken()[0]->Token."",
        ],
      ]);

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);

      if ($err) {
        echo "cURL Error #:" . $err;
      } else {
        $result = json_decode($response);
        return $result->value[0]->ReturnOrderNumber;
      }
    }

    public static function patchReturnOrderNumber($returnOrderNumber,$TaxGroup,$sitio,$almacen){
      $token = new Token();
      $curl = curl_init();
      curl_setopt_array($curl, [
        CURLOPT_URL => "https://".DYNAMICS365."/Data/AYT_SalesTableV2(SalesId=%27".$returnOrderNumber."%27,dataAreaId=%27atp%27)",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "PATCH",
        // CURLOPT_POSTFIELDS => "{\n\t\"TaxGroup\":\"".$TaxGroup."\"\n}",
        CURLOPT_POSTFIELDS => '{
                                "PaymMode"          : "'.$TaxGroup->CustomerPaymentMethodName.'",
                                "DlvMode"           : "'.$TaxGroup->DeliveryModeCode.'",
                                "SalesOriginId"     : "'.$TaxGroup->SalesOrderOriginCode.'",
                                "TaxGroup"          : "'.$TaxGroup->SalesTaxGroupCode.'",
                                "InventSiteId"      : "'.$sitio.'",
                                "InventLocationId"  : "'.$almacen.'"
                              }',
        CURLOPT_COOKIE => "ApplicationGatewayAffinity=e7fb295f94cb4b5e0cd1e2a4712e4a803fc926342cc4ecca988f29125dbd4b04",
        CURLOPT_HTTPHEADER => [
          "Authorization: Bearer ".$token->getToken()[0]->Token."",
          "Content-Type: application/json"
        ],
      ]);

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);

      if ($err) {
        echo "cURL Error #:" . $err;
      } else {
        echo $response;
        self::patchReturnOrderNumberLines($returnOrderNumber,$TaxGroup->SalesTaxGroupCode);
      }
    }

    public static function patchReturnOrderNumberLines($returnOrderNumber,$TaxGroup){
      $token = new Token();
      $curl = curl_init();

      curl_setopt_array($curl, [
        CURLOPT_URL => "https://".DYNAMICS365."/Data/AYT_SalesLines?%24filter=SalesId%20eq%20'".$returnOrderNumber."'",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_COOKIE => "ApplicationGatewayAffinity=e7fb295f94cb4b5e0cd1e2a4712e4a803fc926342cc4ecca988f29125dbd4b04",
        CURLOPT_HTTPHEADER => [
          "Authorization: Bearer ".$token->getToken()[0]->Token."",
          "Content-Type: application/json"
        ],
      ]);

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);

      if ($err) {
        echo "cURL Error #:" . $err;
      } else {
        $resultLineas = json_decode($response);
        foreach($resultLineas->value AS $DataLinea){
          $curl = curl_init();

          curl_setopt_array($curl, [
            CURLOPT_URL => "https://".DYNAMICS365."/Data/AYT_SalesLines(InventTransId=%27".$DataLinea->InventTransId."%27,dataAreaId=%27atp%27)",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PATCH",
            CURLOPT_POSTFIELDS => "{\"TaxGroup\":\"".$TaxGroup."\"}",
            CURLOPT_COOKIE => "ApplicationGatewayAffinity=e7fb295f94cb4b5e0cd1e2a4712e4a803fc926342cc4ecca988f29125dbd4b04",
            CURLOPT_HTTPHEADER => [
              "Authorization: Bearer ".$token->getToken()[0]->Token."",
              "Content-Type: application/json"
            ],
          ]);

          $response2 = curl_exec($curl);
          $err = curl_error($curl);

          curl_close($curl);
           if ($err) {
            echo "cURL Error #:" . $err;
          }else{
            echo $response2;
          }
        }
      }
    }

    public static function getTaxGroupOvOrigen($ovOrigen){
      $token= new Token();
      $curl = curl_init();

      curl_setopt_array($curl, [
        CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesOrderHeadersV2?%24filter=SalesOrderNumber%20eq%20'".$ovOrigen."'",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_COOKIE => "ApplicationGatewayAffinity=e7fb295f94cb4b5e0cd1e2a4712e4a803fc926342cc4ecca988f29125dbd4b04",
        CURLOPT_HTTPHEADER => [
          "Authorization: Bearer ".$token->getToken()[0]->Token."",
        ],
      ]);

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);

      if ($err) {
        echo "cURL Error #:" . $err;
      } else {
        $result = json_decode($response);
        return $result->value[0];
      }
    }

    public static function getInventDataOvOrigenLines($ovOrigen){
      $token= new Token();
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/data/SalesOrderLines?%24filter=SalesOrderNumber%20eq%20'".$ovOrigen."'",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
        "authorization: Bearer ".$token->getToken()[0]->Token.""
        ),
        ));
        $totales=[];
        $response2 = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        $resultLineas = json_decode($response2);

        return array('sitio' => $resultLineas->value[0]->ShippingSiteId, 'almacen' => $resultLineas->value[0]->ShippingWarehouseId);
    }
}