<?php

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
ini_set('display_errors', '1'); 
set_time_limit(0);
// define('DYNAMICS365','".DYNAMICS365."');
// define('PRODUCCION365','ayt.operations.dynamics.com');
// define('DYNAMICS365');
date_default_timezone_set('America/Chihuahua');

class Application_Model_InicioModel {
    public static $db;
    public $_adapter;
    public static $log;

    public static $token;// 

    public static $ecoResProduct;
    public static $items;


    public function __construct(array $options = null){
        if (is_array($options)) {
            $this->setOptions($options);
        }
        Application_Model_InicioModel::$db = new Application_Model_UserinfoMapper();
        Application_Model_InicioModel::$log = new Application_Model_Userinfo();
        $this->_adapter = Application_Model_InicioModel::$db->getAdapter();

        //$token = = new Token();
        // $token = Token()->getToken();
        // $tokenTemp = $token->getToken();
        // $token = $tokenTemp[0]->Token;
        //$ecoResProduct = Application_Model_InicioModel::getNombresEntity();
        $items = Application_Model_InicioModel::GetItemsEntity();

        $query=$this->_adapter->query(ANSI_NULLS);
        $query=$this->_adapter->query(ANSI_WARNINGS);
        $query->execute();
        return $this->_adapter;
    }

    public static function getItems($sucu){
        //$ecoResProduct = Application_Model_InicioModel::getNombresEntity();
        $items = Application_Model_InicioModel::GetItemsEntity('',$sucu);
        // $query = $this->_adapter->query(GET_ITEMS2);
        // $query->execute();
        // $result = $query->fetchAll();
        // if (empty($result)) {
        //     $result['noresult'] = "NoResults";
        // }        
        $result = array();
        $tempVal = '';
        foreach($items as $Data){
          //$nombre = $this->getNombresItems($Data->ItemNumber);
          if ($tempVal != $Data->ItemNumber){
            array_push($result,array('value' => $Data->ItemNumber,'label' => $Data->SearchName, 'salesUnitSymbol' => strtoupper($Data->SalesUnitSymbol), 'productSearchName' => $Data->SearchName, 'productGroupId' => $Data->ProductGroupId,'productType' => $Data->ProductType));
          }
          $tempVal = $Data->ItemNumber;
        }
        return $result;
    }

    public static function setModoEntrega($docid,$modoEntrega){
      $token = new Token();
      if ($modoEntrega != ''){
        curl_setopt_array(CURL1, array(
          CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesQuotationHeaders(dataAreaId=%27".COMPANY."%27,SalesQuotationNumber=%27".$docid."%27)?%24select=DeliveryModeCode",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "PATCH",
          CURLOPT_POSTFIELDS => "{'DeliveryModeCode':'".$modoEntrega."'}",
          CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$token->getToken()[0]->Token."",
            "content-type: application/json",
            "preference-applied: odata.maxpagesize=10"
          ),
        ));

        $response = curl_exec(CURL1);
        $err = curl_error(CURL1);

        if ($err) {
          $result = "cURL Error #:" . $err;
        } else {
          $result = json_decode($response);
        }
        if ($result == ''){
          $result = 'Exito';
        }
      }else{
        $model= new Application_Model_InicioModel();
        $user=array('1'=>'sistemas9@avanceytec.com.mx','2'=>'sistemas6@avanceytec.com.mx','3'=>'sistemas11@avanceytec.com.mx');//"sistemas6@avanceytec.com.mx;sistemas@avanceytec.com.mx;telemarketing14@avanceytec.com.mx";
        $asunto='InaxLog Set modoEntrega';
        $model->sendMail($user, $asunto, 'Fallo en set modoEntrega vacio');
        $result = 'Fallo';
      }
      return $result;
    }

    public static function getItemsOleadaTodos(){
      $token = new Token();
      curl_setopt_array(CURL1, array(
              CURLOPT_URL => "https://ayt.operations.dynamics.com/Data/WarehousesOnHand?%24count=true&%24top=0",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "GET",
              CURLOPT_POSTFIELDS => "",
              CURLOPT_HTTPHEADER => array(
                "authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsIng1dCI6Ii1zeE1KTUxDSURXTVRQdlp5SjZ0eC1DRHh3MCIsImtpZCI6Ii1zeE1KTUxDSURXTVRQdlp5SjZ0eC1DRHh3MCJ9.eyJhdWQiOiJodHRwczovL2F5dC5vcGVyYXRpb25zLmR5bmFtaWNzLmNvbSIsImlzcyI6Imh0dHBzOi8vc3RzLndpbmRvd3MubmV0Lzk3ZWY4M2JlLTc1ZmUtNGRlMi05M2NkLTIxZGY5NzhiMjRjMS8iLCJpYXQiOjE1NDkxMzgxNDEsIm5iZiI6MTU0OTEzODE0MSwiZXhwIjoxNTQ5MTQyMDQxLCJhaW8iOiI0MkpnWU5qZHQrcnIrM2ovZHg2OHYwMThYaHVGQXdBPSIsImFwcGlkIjoiZWVkOGE1ZDYtYzAxOC00MmExLWI1ZWYtZTE2YzNkZDJhNDg5IiwiYXBwaWRhY3IiOiIxIiwiaWRwIjoiaHR0cHM6Ly9zdHMud2luZG93cy5uZXQvOTdlZjgzYmUtNzVmZS00ZGUyLTkzY2QtMjFkZjk3OGIyNGMxLyIsIm9pZCI6Ijc1NDVjMDJmLWRkMzItNGM4MS05NTExLTg2NWVjODg4MDYyYSIsInN1YiI6Ijc1NDVjMDJmLWRkMzItNGM4MS05NTExLTg2NWVjODg4MDYyYSIsInRpZCI6Ijk3ZWY4M2JlLTc1ZmUtNGRlMi05M2NkLTIxZGY5NzhiMjRjMSIsInV0aSI6InJjbFh3bHQxREVxN0YtX0VZRXdyQUEiLCJ2ZXIiOiIxLjAifQ.Fw-0v-1ux3gV16lFaFIt9YNa1GMaQ6x3QjhINxqtwOY-_1BXAJCSdCKdsyjSnpqXy50wrIbqNSPO3BJRd_-S5R6cEWJKiB8qq_iiODTlYtO7-tkU43DclCg6NyNz4TD6ilMb3-avX1jCEVcUNBzSUTzoA2UpNHXyCPLQza9LNakIShsZuLkJCq6bx-yhz12xdfo9ieAevGMDznWxIL-4HcEVM3aMnccYg9tNTSBP-gXQRsseDY3f-Oq9cU47v2IOCgC-neS6IOYsW5Wb0Bigaf7UwBj238prENPY74G7w5KRuRgyBExxQ2UO5q_dqf5YE6YRK2hvKPeadFLGC3a8sg",
              ),
            ));
      $response = curl_exec(CURL1);
      $err = curl_error(CURL1);

      $result = json_decode($response);
      $conteo = json_decode(json_encode($result), True);
      $cuantos = $conteo["@odata.count"];
      $step = 9000;
      $dataTotal = array('value'=>array());

      for($i = 0;$i <= $cuantos; $i += $step){
        curl_setopt_array(CURL1, array(
              CURLOPT_URL => "https://ayt.operations.dynamics.com/Data/WarehousesOnHand?%24skip=$i&%24top=$step",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "GET",
              CURLOPT_POSTFIELDS => "",
              CURLOPT_HTTPHEADER => array(
                "authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsIng1dCI6Ii1zeE1KTUxDSURXTVRQdlp5SjZ0eC1DRHh3MCIsImtpZCI6Ii1zeE1KTUxDSURXTVRQdlp5SjZ0eC1DRHh3MCJ9.eyJhdWQiOiJodHRwczovL2F5dC5vcGVyYXRpb25zLmR5bmFtaWNzLmNvbSIsImlzcyI6Imh0dHBzOi8vc3RzLndpbmRvd3MubmV0Lzk3ZWY4M2JlLTc1ZmUtNGRlMi05M2NkLTIxZGY5NzhiMjRjMS8iLCJpYXQiOjE1NDkxMzgxNDEsIm5iZiI6MTU0OTEzODE0MSwiZXhwIjoxNTQ5MTQyMDQxLCJhaW8iOiI0MkpnWU5qZHQrcnIrM2ovZHg2OHYwMThYaHVGQXdBPSIsImFwcGlkIjoiZWVkOGE1ZDYtYzAxOC00MmExLWI1ZWYtZTE2YzNkZDJhNDg5IiwiYXBwaWRhY3IiOiIxIiwiaWRwIjoiaHR0cHM6Ly9zdHMud2luZG93cy5uZXQvOTdlZjgzYmUtNzVmZS00ZGUyLTkzY2QtMjFkZjk3OGIyNGMxLyIsIm9pZCI6Ijc1NDVjMDJmLWRkMzItNGM4MS05NTExLTg2NWVjODg4MDYyYSIsInN1YiI6Ijc1NDVjMDJmLWRkMzItNGM4MS05NTExLTg2NWVjODg4MDYyYSIsInRpZCI6Ijk3ZWY4M2JlLTc1ZmUtNGRlMi05M2NkLTIxZGY5NzhiMjRjMSIsInV0aSI6InJjbFh3bHQxREVxN0YtX0VZRXdyQUEiLCJ2ZXIiOiIxLjAifQ.Fw-0v-1ux3gV16lFaFIt9YNa1GMaQ6x3QjhINxqtwOY-_1BXAJCSdCKdsyjSnpqXy50wrIbqNSPO3BJRd_-S5R6cEWJKiB8qq_iiODTlYtO7-tkU43DclCg6NyNz4TD6ilMb3-avX1jCEVcUNBzSUTzoA2UpNHXyCPLQza9LNakIShsZuLkJCq6bx-yhz12xdfo9ieAevGMDznWxIL-4HcEVM3aMnccYg9tNTSBP-gXQRsseDY3f-Oq9cU47v2IOCgC-neS6IOYsW5Wb0Bigaf7UwBj238prENPY74G7w5KRuRgyBExxQ2UO5q_dqf5YE6YRK2hvKPeadFLGC3a8sg",
              ),
            ));
        $responseTotal = curl_exec(CURL1);
        $errTot = curl_error(CURL1);
        $resultTot =json_decode($responseTotal);
        if ($errTot){
          return "cURL Error #:" . $errTot;
        }else{
          $dataTotal['value'] = array_merge($dataTotal['value'], $resultTot->value);
        }
      }
      return json_encode($dataTotal);
    }

    // public static function array_map_recursive($callback, $array) {
    //     foreach ($array as $key => $value) {
    //         if (is_array($array[$key])) {
    //             $array[$key] = Application_Model_InicioModel::array_map_recursive($callback, $array[$key]);
    //         }
    //         else {
    //             $array[$key] = call_user_func($callback, $array[$key]);
    //         }
    //     }
    //     return $array;
    // }
    public static function GetItemsEntity($item = '',$sucu = ''){
      $token = new Token();
      $conn = new DB_Conexion();
        // CURL1 = curl_init();
        if ($item == ''){
            // curl_setopt_array(CURL1, array(
            //   //CURLOPT_URL => "https://".DYNAMICS365."/Data/InventItemPrices?%24select=ItemNumber%2CPrice%2CProductUnitSymbol",
            //   CURLOPT_URL => "https://".DYNAMICS365."/Data/ReleasedDistinctProducts?%24select=InventoryUnitSymbol%2CItemNumber%2CSearchName",
            //   CURLOPT_RETURNTRANSFER => true,
            //   CURLOPT_ENCODING => "",
            //   CURLOPT_MAXREDIRS => 10,
            //   CURLOPT_TIMEOUT => 30,
            //   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //   CURLOPT_CUSTOMREQUEST => "GET",
            //   CURLOPT_POSTFIELDS => "",
            //   CURLOPT_HTTPHEADER => array(
            //     "authorization: Bearer ".$token->getToken()[0]->Token.""
            //   ),
            // ));
			
            (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();      
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // $query = $conn->prepare("SELECT * FROM articulos ORDER BY ItemNumber ASC");
            $query = $conn->prepare("EXECUTE AYT_GetArticulos '';");
            $query->execute();
            $result = $query->fetchAll();
            $filtrado = array_filter($result,function($item){
              if ($item['PRODUCTLIFECYCLESTATEID'] != 'INACTIVO'){
                return $item;
              }
            });
            // $resultao = array_map('json_encode', $result);           
            $resultao = array_map('json_encode', $filtrado);          
            $articulos = [];
            foreach ($resultao as $key) {
              $res2 = $key;
              $res2 = json_encode(json_decode($res2));
              $res = json_decode($res2);
              array_push($articulos,$res);
            }

            // $response=file_get_contents('articulos.json');
          //$response = Application_Model_InicioModel::getItemsOleadaTodos();
        }else{
        //     curl_setopt_array(CURL1, array(
        //       CURLOPT_URL => "https://".DYNAMICS365."/Data/ReleasedDistinctProducts?%24filter=ItemNumber%20eq%20'".$item."'%20&%24select=ItemNumber%2CSalesUnitSymbol",
        //       CURLOPT_RETURNTRANSFER => true,
        //       CURLOPT_ENCODING => "",
        //       CURLOPT_MAXREDIRS => 10,
        //       CURLOPT_TIMEOUT => 30,
        //       CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //       CURLOPT_CUSTOMREQUEST => "GET",
        //       CURLOPT_POSTFIELDS => "",
        //       CURLOPT_HTTPHEADER => array(
        //         "authorization: Bearer ".$token->getToken()[0]->Token.""
        //       ),
        //     ));
        // $response = curl_exec(CURL1);
        // $err = curl_error(CURL1);
            (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();      
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // $query = $conn->prepare("SELECT * FROM articulos WHERE ItemNumber = '".$item."'");
            $query = $conn->prepare("EXECUTE AYT_GetArticulos '".$item."';");
            $query->execute();
            $result2 = $query->fetchAll();
            $articulos = [];
            foreach ($result2 as $key) {
            $res2 = (object)$key;
            array_push($articulos, $res2);
            }
        }
       
       // curl_close(CURL1);

            $response = $articulos;
            return $response;
        // if ($err) {
        //   print_r($response);
        //   exit();
        //   return "cURL Error #:" . $err;
        // } else {
        //   return json_decode($response);
        // }
    }

    public static function getItemComplements($item){
      $curl = curl_init();
      $token = new Token();
      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/Data/AYT_EcoResProductRelationTables?%24filter=Product1DisplayProductNumber%20eq%20'".$item."'",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
                "authorization: Bearer ".$token->getToken()[0]->Token."",
              "content-type: application/json; odata.metadata=minimal"
          ),
      ));      
      $response = curl_exec($curl);
      $err = curl_error($curl);
     
      curl_close($curl);

      $complements = json_decode($response);
      $conn = new DB_ConexionExport();

      $resultitems = [];
      return json_encode($complements);
    }


    public static function getNombresEntity($articulo){
      $token = new Token();
        //CURL1 = curl_init();
      $db = new Application_Model_UserinfoMapper();
      $adapter = $db->getAdapter();
      if (!$_SESSION['offline']){
        curl_setopt_array(CURL1, array(
          //CURLOPT_URL => "https://".DYNAMICS365."/Data/RetailEcoResProduct?%24filter=ProductType%20eq%20Microsoft.Dynamics.DataEntities.EcoResProductType'Item'",
          CURLOPT_URL => "https://".DYNAMICS365."/Data/RetailEcoResProduct?%24filter=DisplayProductNumber%20eq%20'".$articulo."'",
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

        $response = curl_exec(CURL1);
        $err = curl_error(CURL1);
      }else{
        $queryNombre = $adapter->query("SELECT SearchName FROM releasedDistinctProducts WHERE itemNumber = '".$articulo."';");
        $queryNombre->execute();
        $response = [];
        $response['value'] = $queryNombre->fetchAll(PDO::FETCH_OBJ);
        $response = json_encode($response);
      }

       // curl_close(CURL1);
        // print_r($response);exit();
        if ($err) {
          print_r($response);
          exit();
          return "cURL Error #:" . $err;
        } else {
            $result = json_decode($response);
            return $result->value;
        }
    }

    public static function getNombresItems($item){
    $nombre = Application_Model_InicioModel::getNombresEntity($item);  
    return $nombre;        
    }

    public static function getUsuariosEntity(){
      $token = new Token();
      (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();      
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $query = $conn->prepare("SELECT * FROM HcmEmployeeStaging");
            $query->execute();
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            $resultao = array_map('json_encode', $result);          
            $empleados = [];
            foreach ($resultao as $key) {
              $res2 = $key;
              $res2 = json_encode(json_decode($res2));
              $res = json_decode($res2);
              array_push($empleados,$res);
            }
            return $empleados;
          // print_r($empleados);exit();
       
        $CURLOPT_URL = "https://".DYNAMICS365."/Data/Employees?%24filter=PersonnelNumber%20ne%20'1'%20and%20PersonnelNumber%20ne%20'000001'%20and%20EmploymentLegalEntityId%20eq%20'".$_SESSION['company']."'&%24select=NameAlias%2CPersonnelNumber%2CEducation";
        curl_setopt_array(CURL1, array(
          CURLOPT_URL => $CURLOPT_URL,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "authorization: Bearer ".$token->getToken()[0]->Token."",
            "content-type: application/json"
          ),
        ));

        $response = curl_exec(CURL1);
        $err = curl_error(CURL1);
        // print_r($response);exit();
       curl_close(CURL1);

        if ($err) {
          print_r($response);
          exit();
          $result = "cURL Error #:" . $err;
        } else {
          $result = json_decode($response);
        }
        return $result->value;
    }

    public static function getUsoCDFI(){
        // $query = $this->_adapter->query("select a.CFDIUSECODE, a.DESCRIPTION from STF_CFDI_CFDIuse a;");
        // $query->execute();
        // $re=$query->fetchAll();
        $re = array(
          array('CFDIUSECODE' => 'G01', 'DESCRIPTION' => 'Adquisición de mercancias'),
          array('CFDIUSECODE' => 'G02', 'DESCRIPTION' => '"Devoluciones'),
          array('CFDIUSECODE' => 'G03', 'DESCRIPTION' => 'Gastos en general'),
          array('CFDIUSECODE' => 'I01', 'DESCRIPTION' => 'Construcciones'),
          array('CFDIUSECODE' => 'I02', 'DESCRIPTION' => 'Mobilario y equipo de oficina por inversiones'),
          array('CFDIUSECODE' => 'I03', 'DESCRIPTION' => 'Equipo de transporte'),
          array('CFDIUSECODE' => 'I04', 'DESCRIPTION' => 'Equipo de computo y accesorios'),
          array('CFDIUSECODE' => 'I05', 'DESCRIPTION' => '"Dados'),
          array('CFDIUSECODE' => 'I06', 'DESCRIPTION' => 'Comunicaciones telefónicas'),
          array('CFDIUSECODE' => 'I07', 'DESCRIPTION' => 'Comunicaciones satelitales'),
          array('CFDIUSECODE' => 'I08', 'DESCRIPTION' => 'Otra maquinaria y equipo'),
          array('CFDIUSECODE' => 'D02', 'DESCRIPTION' => 'Gastos médicos por incapacidad o discapacidad'),
          array('CFDIUSECODE' => 'D03', 'DESCRIPTION' => 'Gastos funerales.'),
          array('CFDIUSECODE' => 'D04', 'DESCRIPTION' => 'Donativos.'),
          array('CFDIUSECODE' => 'D05', 'DESCRIPTION' => 'Intereses reales efectivamente pagados por créditos hipoteca'),
          array('CFDIUSECODE' => 'D06', 'DESCRIPTION' => 'Aportaciones voluntarias al SAR.'),
          array('CFDIUSECODE' => 'D07', 'DESCRIPTION' => 'Primas por seguros de gastos médicos.'),
          array('CFDIUSECODE' => 'D08', 'DESCRIPTION' => 'Gastos de transportación escolar obligatoria.'),
          array('CFDIUSECODE' => 'D09', 'DESCRIPTION' => '"Depósitos en cuentas para el ahorro'),
          array('CFDIUSECODE' => 'D10', 'DESCRIPTION' => 'Pagos por servicios educativos (colegiaturas)'),
          array('CFDIUSECODE' => 'P01', 'DESCRIPTION' => 'Por definir')
        );
        return $re;        
    }

    public static function getPayTerm(){
        // $query = $this->_adapter->query("select p.PAYMTERMID,p.DESCRIPTION from PaymTerm p where p.DATAAREAID='".COMPANY."';");
        // $query->execute();
        // $re=$query->fetchAll();
        (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();      
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = $conn->prepare("SELECT NAME AS PAYMTERMID,DESCRIPTION FROM PaymentTermStaging");
        $query->execute();
        $re=$query->fetchAll();
        if (!empty($re)){
          return $re;
        }else{
          $re = array(
            array('PAYMTERMID' => "10D",'DESCRIPTION'=> "10 DIAS"),
            array('PAYMTERMID' => "15D",'DESCRIPTION'=> "15 DIAS"),
            array('PAYMTERMID' => "180D2P",'DESCRIPTION'=> "180 DIAS 2 PAGOS"),
            array('PAYMTERMID' => "180D3P",'DESCRIPTION'=> "180 DIAS 3 PAGOS"),
            array('PAYMTERMID' => "180D6P",'DESCRIPTION'=> "180 DIAS 6 PAGOS"),
            array('PAYMTERMID' => "1D",'DESCRIPTION'=> "1 DIA"),
            array('PAYMTERMID' => "20D",'DESCRIPTION'=> "20 DIAS"),
            array('PAYMTERMID' => "25D",'DESCRIPTION'=> "25 DIAS"),
            array('PAYMTERMID' => "30D",'DESCRIPTION'=> "30 DIAS"),
            array('PAYMTERMID' => "360D12P",'DESCRIPTION'=> "360 DIAS 12 PAGOS"),
            array('PAYMTERMID' => "360D6P",'DESCRIPTION'=> "360 DIAS 6 PAGOS"),
            array('PAYMTERMID' => "45D",'DESCRIPTION'=> "45 DIAS"),
            array('PAYMTERMID' => "60D",'DESCRIPTION'=> "60 DIAS"),
            array('PAYMTERMID' => "90D",'DESCRIPTION'=> "90 DIAS"),
            array('PAYMTERMID' => "90D3P",'DESCRIPTION'=> "90 DIAS 3 PAGOS"),
            array('PAYMTERMID' => "CONTADO",'DESCRIPTION'=> "CONTADO"),
            array('PAYMTERMID' => "CONTADO PD",'DESCRIPTION'=> "CONTADO PPD"),
            array('PAYMTERMID' => "CREDITO",'DESCRIPTION'=> "CREDITO"),
            array('PAYMTERMID' => "PREM",'DESCRIPTION'=> "PREM")
          );
          return $re;
        }
    }

    public static function sandboxvalidate() {
      $token = new Token();
      $curl = curl_init();
      
      curl_setopt_array($curl, array(
      CURLOPT_URL => "https://".DYNAMICS365."/Data/OperationalSites",
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
      
      $response = curl_exec($curl);
      $err = curl_error($curl);
      
      curl_close($curl);
      
      if ($err) {
      $result = "cURL Error #:" . $err;
      } else {
      $result = json_decode($response);
      }
      return $result->value;
    }

    public static function getPayMode() {
        // $query = $this->_adapter->query("select p.PAYMMODE,p.name from CustPaymModeTable p where p.DATAAREAID='".COMPANY."' and p.PAYMMODE not in('30','na');");
        // $query->execute();
        // $re=$query->fetchAll();
        // return $re;
      $token = new Token();
      //CURL1 = curl_init();
      
      curl_setopt_array(CURL1, array(
      CURLOPT_URL => "https://".DYNAMICS365."/data/CustomerPaymentMethods?%24filter=Description%20ne%20'NA'%20&%24select=Description,Name",
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
      
      $response = curl_exec(CURL1);
      $err = curl_error(CURL1);
      
     // curl_close(CURL1);
      
      if ($err) {
      $result = "cURL Error #:" . $err;
      } else {
      $result = json_decode($response);
      }
      // print_r($result);exit();
      return $result;
    }

    public static function getSucursal(){
        try{
            //return $this->db->Query("select nombre from ".INTERNA.".dbo.sucursal where idsuc = :id",array(":id"=>SUCURSAL));
          $res = array(
            array('CHIHUAHUA'),
            array('AGUASCALIENTES'),
            array('HERMOSILLO'),
            array('OBREGON'),
            array('TIJUANA'),
            array('CULIACAN'),
            array('JUAREZ'),
            array('MEXICALI'),
            array('MONTERREY'),
            array('DURANGO'),
            array('GUADALAJARA'),
            array('ZACATECAS'),
            array('TORREON'),
            array('EDO MEX'),
            array('SALTILLO'),
            array('VERACRUZ'),
            array('SAN LUIS POTOSI'),
            array('QUERETARO'),
            array('LEON'),
            array('PUEBLA'),
            array('TUXTLA')
          );
          return $res;
        }
        catch(Exception $e){
            return $e;
        }
    }

    public static function getItemsCommon(){
        // $query = $this->_adapter->query(GET_ITEMS_COMMON);
        // $query->execute();
        // $result = $query->fetchAll();
        // if (empty($result)) {
        //     $result['noresult'] = "NoResults";
        // }

        (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $queryComunes = "SELECT ITEMID as value, EXTERNALITEMTXT as label FROM AYT_CustVendExternalItem ORDER BY value;";
        $query = $conn->query($queryComunes);
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
       
        $comunesJson = '[';
        foreach($result as $Data){
          $comunesJson .= json_encode($Data).',';
        }
        $comunesJson = substr($comunesJson, 0, -1);
        $comunesJson .= ']';

        // exit();
        // $result = array(
        //   array('value' => '0100-0010-0050', 'label' => '180X120 1.5 CRISTAL PP/SR'),
        //   array('value' => '0100-0010-0050', 'label' => 'LAMINA DE ACRILICO'),
        //   array('value' => '0100-0010-0050', 'label' => 'LAMINA CRISTAL'),
        //   array('value' => '0100-0010-0050', 'label' => 'MICA TRANSPARENTE'),
        //   array('value' => '0100-0010-0050', 'label' => 'ACRILICO TRANSPARENTE'),
        //   array('value' => '0100-0010-0050', 'label' => 'PLACA DE ACRILICO TRANSPARENTE')
        // );
        return $comunesJson;
    }

    public static function getProductDetail($articulo){

        // $query = $this->_adapter->prepare(GET_PRODUCT_DETAIL);
        // $query->bindParam(1,$articulo);
        // $query->execute();
        //$ecoResProduct = Application_Model_InicioModel::getNombresEntity();
        $item = Application_Model_InicioModel::GetItemsEntity($articulo);
        //print_r($item);exit();
        if($articulo != ''){          
          $nombre = Application_Model_InicioModel::getNombresItems($articulo);
          $result[] = array('label'=>$item[0]->SearchName,'unidad'=>($item[0]->SalesUnitSymbol == '')?'PZA':$item[0]->SalesUnitSymbol,'PrecioBloqueado'=>1);
        }else{
          $result[] = array('label'=>$item[0]->SearchName,'unidad'=>$item[0]->SalesUnitSymbol,'PrecioBloqueado'=>1);
        }
        return $result;
    }

    public static function getClients($cliente = '',$autoSel = '') {
        if ($cliente!='') {
            //$query = $this->_adapter->prepare(CLIENTE_POR_CODIGO);
            //$query->bindParam(1,$cliente);
          if ($autoSel != '1'){
            $result = Application_Model_InicioModel::getClientesEntity($cliente, true);
          }else{
            $result = Application_Model_InicioModel::getClientesEntity($cliente, false);
          }
        } 
        else {
            if(empty(COMPANY)){
                $CLIENTES="SELECT DISTINCT T0.accountnum 'ClaveCliente' FROM custtable T0 INNER JOIN dirpartytable T1 ON T0.party = T1.recid LEFT JOIN logisticslocation T2 ON T1.primaryaddresslocation = T2.recid AND T2.ispostaladdress = '1' LEFT JOIN logisticspostaladdress T3 ON T2.recid = T3.location AND Dateadd(hour, -7, T3.validfrom) < Getdate() AND T3.validto > Getdate() where DATAAREAID='ATP';";
                $query = $this->_adapter->prepare($CLIENTES);
            }
            else{

                //$query = $this->_adapter->prepare(CLIENTES_INICIAL);
                // $result =Application_Model_InicioModel::getClientesEntity();
              $result =Application_Model_InicioModel::getClientesEntity();
            }
        }
        //$query->execute();
        //$result = $query->fetchAll();
        //if (empty($result)){$result['noresult'] = "NoResults";}
        // print_r($result);exit();
        return $result;
    }

    public static function getClientesEntity($cliente,$autocomp = false){
      Application_Model_InicioModel::$db = new Application_Model_UserinfoMapper();
      $adapterDb = Application_Model_InicioModel::$db->getAdapter();
      $token = new Token();
        
      if ($cliente == ''){
        // $conn = new DB_ConexionExport();
        (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // $resul = $conn->query("SELECT OrganizationName,CustomerAccount,LineDiscountCode,DiscountPriceGroupId FROM AYT_CustCustomerV3Staging");
         $resul = $conn->query("SELECT TRIM(OrganizationName) AS OrganizationName,CustomerAccount,LineDiscountCode,DiscountPriceGroupId FROM AYT_CustCustomerV3Staging");
        $resul->execute();
        $resultadoSinUTF = $resul->fetchAll(PDO::FETCH_ASSOC);
        $resultado = array_map('json_encode', $resultadoSinUTF);
        $clientes = [];

        /*** 
        **   descomentar segun sea el caso
        **/
        //////cuando la base de datos tiene caracteres especiales como MUESTRARIOS FOLLETOS MATERIALES PARA TRÃFICO Y SEÃ‘ALIZACIÃ“N/////
        foreach ($resultado as $key) {
          $res2 = $key;
          $res2 = json_encode(json_decode($res2));
          $res = json_decode($res2);
          array_push($clientes, $res);
        }
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////si tiene Ñ y acentos en la bd///////////////////////////////////////////
        // foreach ($resultadoSinUTF as $key) {
        //   $res2 = $key;
        //   // $res = array_map('utf8_encode',$res2);
        //   $res = (object) $res2;
        //   array_push($clientes, $res);
        // }
        ///////////////////////////////////////////////////////////////////////////////////////////////////

        // print_r($clientes);exit();
        //         // $clie = json_decode($clie);
        // print_r($clie);exit();
            // curl_setopt_array(CURL1, array(
            //   CURLOPT_URL => "https://".DYNAMICS365."/Data/Customers?%24select=Name%2CCustomerAccount",
            //   CURLOPT_RETURNTRANSFER => true,
            //   CURLOPT_ENCODING => "",
            //   CURLOPT_MAXREDIRS => 10,
            //   CURLOPT_TIMEOUT => 30,
            //   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //   CURLOPT_CUSTOMREQUEST => "GET",
            //   CURLOPT_POSTFIELDS => "",
            //   CURLOPT_HTTPHEADER => array(
            //     "authorization: Bearer ".$token->getToken()[0]->Token."",
            //     "content-type: application/json"
            //   ),
            // ));
      }
      else{

            // CURLOPT_URL => "https://".DYNAMICS365."/Data/Customers?$select=Name,CustomerAccount&$filter=Name%20eq%20%27".$cliente."%27%20or%20CustomerAccount%20eq%20%27".$cliente."%27",

        //     $CURLOPT_URL  = "https://".DYNAMICS365."/Data/CustomersV3?%24select=OrganizationName,CustomerAccount&%24filter=Name%20eq%20%27*".$cliente."*%27%20or%20CustomerAccount%20eq%20%27".$cliente."%27%20";
        //     if (!$autocomp){
        $CURLOPT_URL = "https://".DYNAMICS365."/Data/CustomersV3?%24filter=CustomerAccount%20eq%20'".$cliente."'%20&%24select=OrganizationName%2CSiteId%2CDeliveryMode%2CDeliveryTerms%2CCustomerAccount%2CFullPrimaryAddress%2CPaymentBankAccount%2CPaymentMethod%2CPaymentTerms%2CRFCNumber%2CPrimaryContactPhone%2CPrimaryContactEmail%2CCreditLimit%2CBlocked%2CCustomerGroupId%2CPartyNumber%2CLineDiscountCode%2CDiscountPriceGroupId";
        //     }
        $CURL1 = curl_init();
         curl_setopt_array($CURL1, array(
           CURLOPT_URL => $CURLOPT_URL,
           CURLOPT_RETURNTRANSFER => true,
           CURLOPT_ENCODING => "",
           CURLOPT_MAXREDIRS => 10,
           CURLOPT_TIMEOUT => 30,
           CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
           CURLOPT_CUSTOMREQUEST => "GET",
           CURLOPT_POSTFIELDS => "",
           CURLOPT_HTTPHEADER => array(
             "authorization: Bearer ".$token->getToken()[0]->Token."",
             "content-type: application/json"
           ),
          ));

          $response = curl_exec($CURL1);
          $err = curl_error($CURL1);
          $jsonResponse = json_decode($response);
          if ($err) {
            echo "cURL Error #:" . $err; 
          } else {
            if(!isset($jsonResponse->value)){
              $xml = new SimpleXMLElement($response);
            }
            print_r($clientInfo);
            if(!$xml){
              //echo "dentro de la entity";
              $clientInfo = $jsonResponse->value;
              (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
              $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
              $queryCliente = " SELECT  puntosAvance 
                              FROM PuntosAvanceLealtad 
                              WHERE codigoCliente='".$cliente."'";
              // $resul2 = $conn->query("SELECT * FROM AYT_CustCustomerV3Staging WHERE CustomerAccount='".$cliente."'");
              $resul2 = $conn->query($queryCliente);
              $resul2->execute();
              $resul2 = $resul2->fetchAll(PDO::FETCH_OBJ);
            }else{
              //echo 'hay error';
              (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
              $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
              $queryCliente = " SELECT T0.CustomerAccount 
                                      ,TRIM(T0.OrganizationName) AS OrganizationName
                                      ,T0.SiteId 
                                      ,T0.DeliveryMode 
                                      ,T0.DeliveryTerms 
                                      ,T0.FullPrimaryAddress 
                                      ,T0.PaymentBankAccount 
                                      ,T0.PaymentMethod
                                      ,T0.PaymentTerms 
                                      ,T0.RFCNumber  
                                      ,T0.PrimaryContactPhone  
                                      ,T0.PrimaryContactEmail  
                                      ,T0.CreditLimit
                                      ,T0.isBlocked  --(IIF(T0.BLOCKEDAYT IN (1,2),'Si','No')) AS isBlocked 
                                      ,T0.CustomerGroupId  
                                      ,T0.PartyNumber  
                                      ,T0.LineDiscountCode  
                                      ,T0.DiscountPriceGroupId  
                                      ,ISNULL(T1.puntosAvance,0) AS puntosAvance 
                              FROM AYT_CustCustomerV3Staging  T0
                              LEFT JOIN PuntosAvanceLealtad T1 ON (T0.CustomerAccount COLLATE DATABASE_DEFAULT = T1.CodigoCliente COLLATE DATABASE_DEFAULT)
                              WHERE T0.CustomerAccount='".$cliente."'";
              // $resul2 = $conn->query("SELECT * FROM AYT_CustCustomerV3Staging WHERE CustomerAccount='".$cliente."'");
              $result = $conn->query($queryCliente);
              $result->execute();
              $result = $result->fetchAll(PDO::FETCH_ASSOC);
              $clientInfo = [];
              foreach ($result as $key) {
                $res2 = $key;
                // $res = array_map('utf8_encode',$res2);
                $res = (object) $res2;
                array_push($clientInfo, $res);
              }
            }
            //die();
          }
          //die();
          // $conn = new DB_ConexionExport();
          
        }
        // $i=1;
        // while($i<=100){
        //         $i++;
        // }
               // curl_close(CURL1);

        // if ($err) {
        //   print_r($response);
        //   exit();
        //   $result =  "cURL Error #:" . $err;
        // } else {
        //   $result = json_decode($response);
        // }
        $resultClientes = [];
        if ($cliente == ''){
            foreach($clientes as $Data){
                array_push($resultClientes, array('ClaveCliente' =>$Data->CustomerAccount,
                                                  'Nombre'=> utf8_encode(str_replace('?','',$Data->OrganizationName)),
                                                  'DescuentoLinea' => $Data->LineDiscountCode,
                                                  'ConjuntoPrecios' => $Data->DiscountPriceGroupId
                                            ));
            }
          // $resultClientes = $clientes;//$result->value;
        // print_r($resultClientes);exit('hola');
        //   print_r($clientes2);
        }else{
          print_r();
            foreach($clientInfo as $Data){
                if (!$autocomp){
                  array_push($resultClientes, array(
                      'Almacen'       => $Data->SiteId.'CONS',
                      'Bloqueo'       => 0,
                      'ClaveCliente'  => $Data->CustomerAccount,
                      'CondEntrega'   => ($Data->DeliveryTerms == ' ')?'CONTADO':$Data->DeliveryTerms,
                      'CuentaBanco'   => $Data->PaymentBankAccount,
                      'Direccion'     => $Data->FullPrimaryAddress,
                      'MetodoPago'    => $Data->PaymentMethod,
                      'ModoEntrega'   => ($Data->DeliveryMode == ' ')?'PASA':$Data->DeliveryMode,
                      'Nombre'        => strtoupper(utf8_encode(str_replace('?','',$Data->OrganizationName))),
                      'PAYMTERMID'    => $Data->PaymentTerms,
                      'Sitio'         => $Data->SiteId,
                      'ACCOUNTNUM'    => $Data->CustomerAccount,
                      'Correo'        => $Data->PrimaryContactEmail == ' '?'No definido':$Data->PrimaryContactEmail,
                      'RFC_MX'        => $Data->RFCNumber == ' '?'No definido':$Data->RFCNumber,
                      'Telefono'      => $Data->PrimaryContactPhone == ' '?'No definido':$Data->PrimaryContactPhone,
                      'PuntosAvance'  => ($resul2)? $resul2[0]->puntosAvance : $Data->puntosAvance 
                  ));
                }
                else{                  
                  array_push($resultClientes, array(
                      'value'  => $Data->CustomerAccount,
                      'label'  => $Data->CustomerAccount.' - '.utf8_encode(str_replace('?','',$Data->OrganizationName))
                  ));
                }
            }
        }
        //print_r($resultClientes);
        return $resultClientes;
    }

    public static function setKardex($type) {
            /**
     * inserta en la tabla de kardex para poder llevar estadisticas de uso de   inax
     * @param String $type ya sea CTZN para cotizacion o ORDVTA orden de venta
     */
       try{
        // $query=$this->_adapter->query(ANSI_NULLS);
        // $query=$this->_adapter->query(ANSI_WARNINGS);
        // $query = $this->_adapter->prepare(INSERT_KARDEX);
        // $query->bindParam(1,$_SESSION['userInax']);
        // $query->bindParam(2,$_SESSION['nomuser']);
        // $query->bindParam(3,$type);
        // $query->execute();
       } catch (Exception $ex) {
           echo $ex->getMessage();
       }
       
    }

    public function getSitios() {
        // $optSitio = '<option value="">Selecciona...</option>';
        // if ($this->view->sitios[0] != 'NoResults') {
        //     foreach ($this->view->sitios as $Data) {
        //         $optSitio .= '<option value="' . $Data['SITEID'] . '">' . $Data['NAME'] . '</option>';
        //     }
        // } else {
        //     $optSitio = '<option value="">Error (sin registros)</option>';
        // }
        // return $optSitio;
        $sitiosEnt = $this->getSitiosEntity();
        $sitios = [];
        foreach($sitios as $Data){
            $siteid = $Data->SiteId;
            $name = $Data->SiteName;
            array_push($sitios, array(
                                        'SITEID' => $siteid,
                                        'NAME' => $name
                                    ));
        }
        return $sitios;
    }

    public function getSitiosEntity(){
        $token = new Token();
        $tokenTemp = $token->getToken();
        //CURL1 = curl_init();

        curl_setopt_array(CURL1, array(
          CURLOPT_URL => "https://".DYNAMICS365."/Data/OperationalSites?%24select=SiteId%2CSiteName",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "authorization: Bearer ".$tokenTemp[0]->Token."",
            "x-total-count: application/json"
          ),
        ));

        $response = curl_exec(CURL1);
        $err = curl_error(CURL1);

       // curl_close(CURL1);

        if ($err) {
          $result = "cURL Error #:" . $err;
        } else {
          $result = json_decode($response);
        }
        return $result->value;
    }


    public function getSitio($sitio) {
        // $query = $this->_adapter->prepare(SITIO_FILTRO);
        // $query->bindParam(1,$sitio); 
        // $query->execute();
        // $result = $query->fetchAll();
        // $almacenes[0] = "NoResults";
        // if (!empty($result)){
        //     foreach ($result as $k => $v) { $almacenes[$k] = $v;}
        // }
        // return $almacenes;
        $sitiosEnt = $this->getSitiosEntity();
        $sitios = [];
        foreach($sitios as $Data){
            $siteid = $Data->SiteId;
            $name = $Data->SiteName;
            array_push($sitios, array(
                                        'SITEID' => $siteid,
                                        'NAME' => $name
                                    ));
        }
        return $sitios;
    }

    public function getClientByClave($clave) {
        $query = $this->_adapter->prepare(INICIO_CLVECLTE);
        $query->bindParam(1,$clave); 
        $query->execute();
        $result = $query->fetchAll();
        if (empty($result)) { 
             $result['noresult'] = "NoResults";       
        }
        return $result;
    }
 
    public static function getFraccionado($almacen,$item) {
        // $query = $this->_adapter->prepare(GET_FRACCIONADOS);
        // $query->bindParam(1,$almacen); 
        // $query->bindParam(2,$item); 
        // $query->execute();
        // $result = $query->fetchAll();
        $result = array();
        if (empty($result)) {
            $fraccionado['noresult'] = "NoResults";
        }
        foreach ($result as $k => $v) { $fraccionado[$k] = $v; }
        return $fraccionado;        
    }

    public static function getDirecciones($cliente) {

        // $query = $this->_adapter->prepare(INICIO_CLVECLTE_DETALLE);
        // $query->bindParam(1,$cliente);
        // $query->execute();
        // $result = $query->fetchAll(PDO::FETCH_ASSOC);
        // $resultado = array('resultado' => 'NoResults');
        // if (!empty($result)) {
        //     $resultado = array('resultado' => '1','data' => $result,'size' => count($result));
        // }
        $resultado = Application_Model_InicioModel::getDirecionesEntity($cliente);
        return json_encode($resultado);
    }

    public static function getDirecionesEntity($cliente){
      $token = new Token();
        //CURL1 = curl_init();
        curl_setopt_array(CURL1, array(
          CURLOPT_URL => "https://".DYNAMICS365."/Data/CustomerPostalAddresses?%24filter=CustomerAccountNumber%20eq%20'".$cliente."'%20&%24select=CustomerAccountNumber%2CAddressDescription%2CFormattedAddress%2CIsRoleDelivery%2CIsRoleBusiness%2CIsRoleHome%2CIsRoleInvoice%2CIsPrimary",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$token->getToken()[0]->Token."",
            "content-type: application/json"
          ),
        ));

        $response = curl_exec(CURL1);
        $err = curl_error(CURL1);

       // curl_close(CURL1);

        if ($err) {
          print_r($response);
          exit();
          $result = "cURL Error #:" . $err;
        } else {
          $result = json_decode($response);
        }

        $resultDirs = [];
        $proposito = '';
        $size = count($result->value);
        foreach($result->value as $Data){
            $cusAccount  = $Data->CustomerAccountNumber;
            $address = $Data->AddressDescription;
            $descripcion = $Data->FormattedAddress;
            if ($Data->IsRoleDelivery === 'Yes'){
                $proposito .= 'Entrega,';
            }
            if ($Data->IsRoleBusiness === 'Yes'){
                $proposito .= 'Negocio,';
            }
            if ($Data->IsRoleHome === 'Yes'){
                $proposito .= 'Hogar,';
            }
            if ($Data->IsRoleInvoice === 'Yes'){
                $proposito .= 'Facturacion,';
            }
            if ($Data->IsPrimary){
                $flag = 1;
            }else{
                $flag = 0;
            }
        }
        substr($proposito, 0,-1);
        $data = [];
        array_push($data, array('ACCOUNTNUM' => $cusAccount,'ADDRESS' => $address,'DESCRIPTION' => $descripcion,'Proposito' => $proposito,'flagInvoice' => $flag));
        $resultDirs['resultado'] = "1";
        $resultDirs['data'] = $data;
        $resultDirs['size'] = $size;
        
        return $resultDirs;
    }

    public function getCredito($ov) {
        $query = $this->_adapter->prepare(GET_CREDITO);
        $query->bindParam(1,$ov);
        $query->execute();
        $result = $query->fetchAll();
        return $result;
    }

    public static function getUltimasVentas($cliente) {
        $azureConn = (CONFIG==DESARROLLO)? new DB_Conexion(): new DB_ConexionExport();
        $azureConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT);
        $queryAzure = $azureConn->prepare("SELECT TOP 4 * FROM dbo.CustCustomerV3Staging WHERE CustomerAccount = '{$cliente}'");  
        $queryAzure->execute();
        $resultAzure = $queryAzure->fetchAll(PDO::FETCH_ASSOC);
        // print_r($resultAzure);
        $conn = new DB_Conect(); 
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT);
        $query = $conn->prepare("SELECT TOP 300 '<i class=\"material-icons\" style=\"color:green;cursor:pointer\" onclick=\"detalleClienteVenta('+char(39)+T1.SALESID+char(39)+',$(this))\">add_circle</i>' '0',T1.SALESID '1',CONVERT(VARCHAR(255),T0.TRANSDATE,103) '2',T0.ACCOUNTNUM '3','".$resultAzure[0]['ORGANIZATIONNAME']."' '4'--,T2.NAMESECRETARIOVENTAS '5'  
        FROM AYT_CustTransV2Staging T0 
        INNER JOIN AYT_CustInvoiceJourV2Staging T1 ON T1.INVOICEID COLLATE Database_Default = T0.INVOICE COLLATE Database_Default 
        --INNER JOIN AYT_SalesTableStaging T2 ON T1.SALESID COLLATE Database_Default = T2.SALESID 
        INNER JOIN AYT_CustTable1Staging T3 ON T3.ACCOUNTNUM = T0.ACCOUNTNUM COLLATE Database_Default 
        WHERE T0.ACCOUNTNUM = '{$cliente}' AND T0.INVOICE COLLATE Database_Default LIKE 'FV%' ORDER BY T0.TRANSDATE DESC;");
        // print_r($query);
        $query->execute();
        $result = $query->fetchAll();
        //xdebug($result);
        $result = json_encode($result);
        //xdebug($result);
        ;
        //$this->log->kardexLog("ULTIMAS VENTAS parametros: ".$cliente." resultado: ", $cliente,'',1,'Ultimas Ventas');
        return $result;
    } 

    public static function getArchivoAdjunto($type,$doc) {
        if ($type == 'CTZN') {
            $query = $this->_adapter->prepare(GET_ARCHIVO_ADJUNTO_COT);
        } 
        else {
            $query = $this->_adapter->prepare(GET_ARCHIVO_ADJUNTO_OV);
        }
        $query->bindParam(1,$doc);
        $query->execute();
        $result = $query->fetchAll();
        return $result;                
    }
    
    public static function getDataNegados() {
        $query = $this->_adapter->prepare(GET_DATA_NEGADOS);
        $query->execute();
        $result = $query->fetchAll();
        if (!empty($result)) {
           $res=json_encode(array('result' => 'OK', 'data' => json_encode($result)));
        } else {
           $res=json_encode(array('result' => 'FAIL','data' => '["NoResults","NoResults","NoResults","NoResults","NoResults","NoResults","NoResults","NoResults","NoResults"]'));
        }
        return $res;
    }

    public static function getRefreshLines($docType,$docId) {
      $token = new Token();
      $db = new Application_Model_UserinfoMapper();
      $adapter = $db->getAdapter();
      if ($docType == 'CTZN') {
        //$query = $this->_adapter->prepare(GET_REFRESH_LINES_COT);
        ///////////////get quotation data expand quotation lines/////////////////////
        if (!$_SESSION['offline']){
          curl_setopt_array(CURL1, array(
            CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesQuotationLines?%24select=ItemNumber%2CLineDescription%2CRequestedSalesQuantity%2CSalesUnitSymbol%2CShippingSiteId%2CShippingWarehouseId%2CSalesPrice%2CLineAmount%2CInventoryLotId%2CRequestingCustomerAccountNumber&%24filter=SalesQuotationNumber%20eq%20'".$docId."'",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
              "authorization: Bearer ".$token->getToken()[0]->Token."",
              "content-type: application/json"
            ),
          ));

          $response = curl_exec(CURL1);
          $err = curl_error(CURL1);
        }else{
          $query = $adapter->query("SELECT * FROM CotizacionesLinesOffline WHERE SalesQuotationNumber = '".$docId."';");
          $query->execute();
          $response['value'] = $query->fetchAll(PDO::FETCH_ASSOC);
          $response = json_encode($response);
        }

        if ($err) {
          $result = "cURL Error #:" . $err;
        } else {
          $result = json_decode($response);
          $datos = [];
          foreach($result->value as $Data){
            if (!$_SESSION['offline']){
              curl_setopt_array(CURL1, array(
                  CURLOPT_URL => "https://".DYNAMICS365."/Data/ReleasedDistinctProducts?%24select=ProductType%2CItemNumber%2CSearchName&%24filter=ItemNumber%20eq%20'".$Data->ItemNumber."'",
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

              $responsePriceProd = curl_exec(CURL1);
            }else{
              $query = $adapter->query("SELECT * FROM releasedDistinctProducts WHERE ItemNumber = '".$Data->ItemNumber."';");
              $query->execute();
              $resultPriceProd = $query->fetchAll(PDO::FETCH_ASSOC);
              $resultPriceProd = json_encode($resultPriceProd);
            }
            $resultPriceProd = json_decode($responsePriceProd);
            $iva = 1.16;
            if(strpos($Data->ShippingWarehouseId, 'MEXL')===0||strpos($Data->ShippingWarehouseId, 'TJNA')===0||strpos($Data->ShippingWarehouseId, 'JURZ')===0){
              ///////////////////////////checar el codigo postal del cliente en la entity/////////////////////////////////////
              curl_setopt_array(CURL1, array(
                CURLOPT_URL => "https://".DYNAMICS365."/data/CustomersV3?%24filter=CustomerAccount%20eq%20'".$Data->RequestingCustomerAccountNumber."'&%24select=AddressZipCode",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_POSTFIELDS => "",
                CURLOPT_COOKIE => "ApplicationGatewayAffinity=e7fb295f94cb4b5e0cd1e2a4712e4a803fc926342cc4ecca988f29125dbd4b04",
                CURLOPT_HTTPHEADER => array(
                  "accept: application/json",
                  "authorization: Bearer ".$token->getToken()[0]->Token."",
                  "content-type: application/json",
                  "x-total-count: application/json"
                ),
              ));

              $response = curl_exec(CURL1);
              $err = curl_error(CURL1);

                if ($err) {
                  echo "cURL Error #:" . $err;
                } else {
                  $response = json_decode($response);
                  //////////////////checar si el codigo postal es valido para el 1.08 de IVA/////////////////////////////
                  $zipCodeValido = false;
                  if(!empty($response->value)){
                    $zipCliente = $response->value[0]->AddressZipCode;
                    $querySTR = "EXECUTE AYT_CodigoPostalFronterizo '".$zipCliente."';";
                    (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $query = $conn->prepare($querySTR);
                    $query->execute();
                    $result = $query->fetchAll(PDO::FETCH_ASSOC);
                    if ($result[0]['Cuantas'] > 0){
                      $zipCodeValido = true;
                    }
                  }
                  //////////////////////////////////////////////////////////////////////////////////////////////////////
                }
              ///////////////////////////////////////////////////////////////////////////////////////////////////
              if ($zipCodeValido){
                $iva = 1.08;
              }
            }
            // if ($resultPriceProd->value[0]->ProductType == 'Service'){
            //   $iva = 1.00;
            // }
            $dataComent = explode(' - ',$Data->LineDescription);            
            /////////////////////traer el nombre del producto////////////////////////////////
            if (!$_SESSION['offline']){
              curl_setopt_array(CURL1, array(
                CURLOPT_URL => "https://".DYNAMICS365."/Data/ReleasedDistinctProducts?%24filter=ItemNumber%20eq%20'".$Data->ItemNumber."'&%24select=ProductSearchName",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_POSTFIELDS => "",
                CURLOPT_HTTPHEADER => array(
                "accept: application/json",
                "authorization: Bearer ".$token->getToken()[0]->Token."",
                "content-type: application/json"
                ),
              ));

              $response = curl_exec(CURL1);
              $err = curl_error(CURL1);
            }else{
              $query = $adapter->query("SELECT * FROM releasedDistinctProducts WHERE ItemNumber = '".$Data->ItemNumber."';");
              $query->execute();
              $response2 = $query->fetchAll(PDO::FETCH_ASSOC);
              $response = [];
              $response['value'][] = (object)$response2[0];
              $response = json_encode($response);
            }

            if ($err) {
              $resultNombre = "cURL Error #:" . $err;
            } else {
              $resultNombre = json_decode($response);
              //$name =$resultNombre->value[0]->ProductSearchName;
              $name =$Data->LineDescription;
            }
            /////////////////////////////////////////////////////////////////////////////////
            ////////////////consultar el comentario en base de datos/////////////////////////////
            try{
              $db = new Application_Model_UserinfoMapper();
              $adapter = $db->getAdapter();
              $query = $adapter->query(ANSI_NULLS);
              $query = $adapter->query(ANSI_WARNINGS);
              $query = $adapter->prepare("SELECT * FROM dbo.AYT_ComentariosLineas WHERE documentId = '".$docId."' AND inventoryLotId = '".$Data->InventoryLotId."';");
              $query->execute();
              $resultQuery = $query->fetchAll(PDO::FETCH_ASSOC);
              $comentario = (isset($resultQuery[0]['comentario']))?$resultQuery[0]['comentario']:'';
            }catch(Exception $e){
              print_r($e->getMessage());
            }
            /////////////////////////////////////////////////////////////////////////////////////
            $dataTemp = array('ITEMID'            => $Data->ItemNumber,
                              'NAME'              => $name,
                              'SALESQTY'          => $Data->RequestedSalesQuantity,
                              'SALESUNIT'         => $Data->SalesUnitSymbol,
                              'INVENTSITEID'      => $Data->ShippingSiteId,
                              'INVENTLOCATIONID'  => $Data->ShippingWarehouseId,
                              'MONTOCARGO'        => $Data->LineAmount,
                              'MONTOCARGOIVA'     => ($Data->LineAmount*$iva),
                              'STF_OBSERVATIONS'  => $comentario
                        );
            array_push($datos,$dataTemp);
          }
        }
          /////////////////////////////////////////////////////////////////////////////
      } else {
        //$query = $this->_adapter->prepare(GET_REFRESH_LINES_OV);
        ///////////////get salesorder data expand quotation lines/////////////////////
        if (!$_SESSION['offline']){
          curl_setopt_array(CURL1, array(
            CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesOrderLines?%24sysparm_display_value=true&%24filter=SalesOrderNumber%20eq%20'".$docId."'&%24select=ItemNumber%2CLineDescription%2COrderedSalesQuantity%2CSalesUnitSymbol%2CShippingSiteId%2CShippingWarehouseId%2CSalesPrice%2CLineAmount%2CInventoryLotId",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
              "authorization: Bearer ".$token->getToken()[0]->Token."",
              "content-type: application/json"
            ),
          ));

          $response = curl_exec(CURL1);
          $err = curl_error(CURL1);
        }else{
          $query = $adapter->query("SELECT * FROM SalesOrderLinesOffline WHERE SalesOrderNumber = '".$docId."';");
          $query->execute();
          $response['value'] = $query->fetchAll(PDO::FETCH_ASSOC);
          $response = json_encode($response);
        }

        if ($err) {
          $result = "cURL Error #:" . $err;
        } else {
          $result = json_decode($response);
          $datos = [];
          foreach($result->value as $Data){
            if (!$_SESSION['offline']){
              curl_setopt_array(CURL1, array(
                  CURLOPT_URL => "https://".DYNAMICS365."/Data/ReleasedDistinctProducts?%24select=ProductType%2CItemNumber%2CSearchName&%24filter=ItemNumber%20eq%20'".$Data->ItemNumber."'",
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

              $responsePriceProd = curl_exec(CURL1);
              $resultPriceProd = json_decode($responsePriceProd);
              $name = $resultPriceProd->value[0]->SearchName;
            }else{
              $query = $adapter->query("SELECT * FROM releasedDistinctProducts WHERE ItemNumber = '".$Data->ItemNumber."';");
              $query->execute();
              $resultPriceProd = $query->fetchAll(PDO::FETCH_ASSOC);
              $name = $resultPriceProd[0]['SearchName'];
              $resultPriceProd = json_encode($resultPriceProd);
            }
            $iva = 1.16;
            if(strpos($Data->ShippingWarehouseId, 'MEXL')===0||strpos($Data->ShippingWarehouseId, 'TJNA')===0||strpos($Data->ShippingWarehouseId, 'JURZ')===0){
              ///////////////////////////checar el codigo postal del cliente en la entity/////////////////////////////////////
              curl_setopt_array(CURL1, array(
                CURLOPT_URL => "https://".DYNAMICS365."/data/SalesOrderHeadersV2?%24filter=SalesOrderNumber%20eq%20'".$docId."'&%24select=OrderingCustomerAccountNumber",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_POSTFIELDS => "",
                CURLOPT_COOKIE => "ApplicationGatewayAffinity=e7fb295f94cb4b5e0cd1e2a4712e4a803fc926342cc4ecca988f29125dbd4b04",
                CURLOPT_HTTPHEADER => array(
                  "accept: application/json",
                  "authorization: Bearer ".$token->getToken()[0]->Token."",
                  "content-type: application/json",
                  "x-total-count: application/json"
                ),
              ));
              $responseClte = curl_exec(CURL1);
              $err = curl_error(CURL1);

              $resultClte = json_decode($responseClte);

              curl_setopt_array(CURL1, array(
                CURLOPT_URL => "https://".DYNAMICS365."/data/CustomersV3?%24filter=CustomerAccount%20eq%20'".$resultClte->value[0]->OrderingCustomerAccountNumber."'&%24select=AddressZipCode",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_POSTFIELDS => "",
                CURLOPT_COOKIE => "ApplicationGatewayAffinity=e7fb295f94cb4b5e0cd1e2a4712e4a803fc926342cc4ecca988f29125dbd4b04",
                CURLOPT_HTTPHEADER => array(
                  "accept: application/json",
                  "authorization: Bearer ".$token->getToken()[0]->Token."",
                  "content-type: application/json",
                  "x-total-count: application/json"
                ),
              ));

              $response = curl_exec(CURL1);
              $err = curl_error(CURL1);

                if ($err) {
                  echo "cURL Error #:" . $err;
                } else {
                  $response = json_decode($response);
                  //////////////////checar si el codigo postal es valido para el 1.08 de IVA/////////////////////////////
                  $zipCodeValido = false;
                  if(!empty($response->value)){
                    $zipCliente = $response->value[0]->AddressZipCode;
                    $querySTR = "EXECUTE AYT_CodigoPostalFronterizo '".$zipCliente."';";
                    (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $query = $conn->prepare($querySTR);
                    $query->execute();
                    $result = $query->fetchAll(PDO::FETCH_ASSOC);
                    if ($result[0]['Cuantas'] > 0){
                      $zipCodeValido = true;
                    }
                  }
                  //////////////////////////////////////////////////////////////////////////////////////////////////////
                }
              ///////////////////////////////////////////////////////////////////////////////////////////////////
              if ($zipCodeValido){
                $iva = 1.08;
              }
            }
            // if ($resultPriceProd->value[0]->ProductType == 'Service'){
            //   $iva = 1.00;
            // }
            ////////////////consultar el comentario en base de datos/////////////////////////////
            try{
              $db = new Application_Model_UserinfoMapper();
              $adapter = $db->getAdapter();
              $query = $adapter->query(ANSI_NULLS);
              $query = $adapter->query(ANSI_WARNINGS);
              $query = $adapter->prepare("SELECT * FROM dbo.AYT_ComentariosLineas WHERE documentId = '".$docId."' AND inventoryLotId = '".$Data->InventoryLotId."';");
              $query->execute();
              $resultQuery = $query->fetchAll(PDO::FETCH_ASSOC);
              $comentario = (isset($resultQuery[0]['comentario']))?$resultQuery[0]['comentario']:'';
            }catch(Exception $e){
              print_r($e->getMessage());
            }
            /////////////////////////////////////////////////////////////////////////////////////
            $dataTemp = array('ITEMID'            => $Data->ItemNumber,
                              'NAME'              => $name,
                              'SALESQTY'          => $Data->OrderedSalesQuantity,
                              'SALESUNIT'         => $Data->SalesUnitSymbol,
                              'INVENTSITEID'      => $Data->ShippingSiteId,
                              'INVENTLOCATIONID'  => $Data->ShippingWarehouseId,
                              'MONTOCARGO'        => $Data->LineAmount,
                              'MONTOCARGOIVA'     => ($Data->LineAmount*$iva),
                              'STF_OBSERVATIONS'  => $comentario
                        );
            array_push($datos,$dataTemp);
          }
        }
      }
      // $query->bindParam(1,$docId);
      // $query->execute();
      // $result = $query->fetchAll();
      // if (! empty($result)) {
      //     $datos = $result;
      // } else {
      //     $datos[0] = 'SinResultados';
      // }
      return $datos;
    }

    public static function getStatusAlerta($ov) {
        $query = $this->_adapter->prepare(GET_STATUS_ALERTA);
        $query->bindParam(1,$ov);
        $query->execute();
        $result = $query->fetchAll();
        if (! empty($result)) {
            $resultado = array('resultado' => $result);
        } else {
            $resultado = array('resultado' => 'NoResults');
        }
        return $resultado;
    }

    public static function getStatusBloqueo($ov) {
        $query = $this->_adapter->prepare(GET_STATUS_BLOQUEO);
        $query->bindParam(1,$ov);
        $query->execute();
        $result = $query->fetchAll();
        if (! empty($result)) { $resultado = array('resultado' => $result ); } 
        else { $resultado = array('resultado' => 'NoResults' ); }
        return $resultado;
    }

    public static function getTipoCambio() {
        // $query = $this->_adapter->query(TIPO_CAMBIO);
        // $query->execute();
        // $result = $query->fetchAll(); 
        // $tp=$result[0]['tipoCambio'];
        // return $tp;

        $tp = Application_Model_InicioModel::getTipoCambioEntity();
        return $tp;
    }

    public static function getTipoCambioEntity(){
      $token = new Token();
      $date  = new DateTime();
      $date2  = $date->format('Y-m-d');
      $date2 .= 'T12%3A00%3A00Z';

      $CURL1 = curl_init();

      curl_setopt_array($CURL1, array(
        CURLOPT_URL => "https://".DYNAMICS365."/Data/ExchangeRates?%24filter=StartDate%20eq%20".$date2."%20and%20RateTypeName%20eq%20'".$_SESSION['company']."'",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
          "accept: application/json",
          "authorization: Bearer ".$token->getToken()[0]->Token."",
          "content-type: application/json"
        ),
      ));

      $response = curl_exec($CURL1);
      $err = curl_error($CURL1);
      curl_close($CURL1);

      if ($err) {
        $result = "cURL Error #:" . $err;
      } else {
        $result = json_decode($response);
      }
      $tipoCambio = '$ '.$result->value[0]->Rate.' '.$result->value[0]->ToCurrency;
      return $tipoCambio;
    }
    /**
     * 
     * @param type $cliente
     * @return string
     */

    public function getInvoiceDeliveryAddress($cliente){
        $query = $this->_adapter->prepare(GET_INVOICE_DELIVERY_ADDRESS);
        $query->bindParam(1,$cliente);
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        if (! empty($result)) {
            $dire = array( 'factura' => '','entrega' => '');
            foreach ($result as $dirs) {
                if ($dirs['NAME'] == 'Invoice') {
                    $dire['factura'] = $dirs['RECID'];
                }
                if ($dirs['NAME'] == 'Delivery') {
                    $dire['entrega'] = $dirs['RECID'];
                }
            }
            return $dire;
        } else {
            return 'NoResults';
        }
    }

    public static function getExistenciaLote($item,$sitio) {
        $query = $this->_adapter->prepare(EXISTENCIA_LOTE);
        $query->bindParam(1,$item);
        $query->bindParam(2,$sitio);
        $query->execute();
        $result = $query->fetchAll();
        if (empty($result)) {
            $datos[0]['Articulo'] = "";
        }
        foreach ($result as $k => $v) {
            $datos[$k] = $v;
        }
        return $datos;
    }

    public static function getMinimoVenta($item){
        // $queryStr = "select T1.ITEMID, T2.INVENTSITEID,T1.MULTIPLEQTY from INVENTITEMSALESSETUP t1
      //         left JOIN INVENTDIM t2 on t2.INVENTDIMID = t1.INVENTDIMID
      //         where t1.DATAAREAID = '".COMPANY."'
      //         and t2.DATAAREAID = '".COMPANY."'
      //         and ITEMID = ?
      //         and t1.MULTIPLEQTY != 0.0000000;";
      // $query = $this->_adapter->prepare($queryStr);
      // $query->bindParam(1,$item);
      // $query->execute();
      // $result = $query->fetchAll();

      $token = new Token();
      curl_setopt_array(CURL1, array(
        CURLOPT_URL => "https://".DYNAMICS365."/Data/AYT_InventItemSalesSetups?%24filter=ItemId%20eq%20'".$item."'",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
          "accept: application/json",
          "authorization: Bearer ".$token->getToken()[0]->Token."",
          "content-type: application/json"
        ),
      ));

      $response = curl_exec(CURL1);
      $err = curl_error(CURL1);

      if ($err) {
        $result = "cURL Error #:" . $err;
      } else {
        $result = json_decode($response);
        $result = $result->value;
      }
      // print_r($result);echo "\r\n";
      ///////consulta json/////////////////////////////////////
      // $resultJS = file_get_contents('minimoVenta.json');
      // $resultJS = json_decode($resultJS);
      //print_r($resultJS);echo "\r\n";
      if (empty($result) || $result[0]->LowestQty == 0) {
          return false;
      }
      // foreach ($resultJS as $Data) {
      //     if ($Data->ITEMID == $item){
      //       $datos = $Data;
      //       break;
      //     }
      // }
      /////////////////////////////////////////////////////////
      $datos2 = array(
        'ITEMID'        => $result[0]->ItemId,
        'INVENTSITEID'  => 'CHIH',
        'MULTIPLEQTY'   => $result[0]->LowestQty,
      );
      // print_r($datos2);exit();
      // print_r((array)$datos);exit('ppp');
      return $datos2; 
    }
    
    public static function getExistencias($item,$sitio,$almacen,$localidad,$documenType,$company) {
        /**
         *regresa las existencias segun el articulo con sus variantes
         * @return JSON con los datos
        */

        // $query = $this->_adapter->prepare("EXECUTE devGetExistenciasExiCompany ?,?,?,?,?,?;");
        // $query->bindParam(1,$item);
        // $query->bindParam(2,$sitio);
        // $query->bindParam(3,$almacen);
        // $query->bindParam(4,$localidad);
        // $query->bindParam(5,$documenType);
        // $query->bindParam(6,$company);
        // $query->execute();
        // $result = $query->fetchAll();
        // if (empty($result)){ $datos['noresult'] = "NoResults"; }
        // foreach ($result as $k => $v) { $datos[$k] = $v; }
        // return  json_encode(array('datos' => $datos));        
        $existencias = Application_Model_InicioModel::getExistenciasEntity($item,$sitio,$almacen);
        $datos = [];
        $categoria = ($existencias['categoria']->ProductCategoryName == null)?'':$existencias['categoria']->ProductCategoryName;
        $existe = false;
        $existeCons = false;
        $existeEqps = false;
        foreach($existencias['existencias'] as $Data){
          if ($Data->InventorySiteId == $sitio){
            $existe = true;
          }
          if (!$existeCons){
            if (strpos($Data->InventoryWarehouseId, $sitio.'CONS') !== false ){
              $existeCons = true;
            }
          }
          if (!$existeEqps){
            if (strpos($Data->InventoryWarehouseId, 'VEQPEQPS') !== false ){
              $existeEqps = true;
            }
          }
            array_push($datos, array(   'Articulo'    => $Data->ItemNumber
                                        ,'Existencia' => ($Data->AvailableOnHandQuantity < 0)?0:$Data->AvailableOnHandQuantity
                                        ,'Sitio'      => $Data->InventorySiteId
                                        ,'Almacen'    => $Data->InventoryWarehouseId
                                        ,'Localidad'  => 'GRAL'
                                        ,'cantCaduco' => '0'
                                        ,'disponible' => ($Data->AvailableOnHandQuantity < 0)?0:$Data->AvailableOnHandQuantity
                                        ,'categoria'  => $categoria
                                        ,'nombre' => $Data->Nombre
                                    ));
        };
        if (!$existe || !$existeCons || !$existeEqps){
          if (!$existeCons){
            array_push($datos, array(   'Articulo'    => $item
                                        ,'Existencia' => '0.00'
                                        ,'Sitio'      => $sitio
                                        ,'Almacen'    => $almacen
                                        ,'Localidad'  => 'GRAL'
                                        ,'cantCaduco' => '0'
                                        ,'disponible' => '0'
                                        ,'categoria'  => ''
                                      ));
          }
          if (!$existeEqps){
            array_push($datos, array(   'Articulo'    => $item
                                        ,'Existencia' => '0.00'
                                        ,'Sitio'      => $sitio
                                        ,'Almacen'    => $sitio.'EQPS'
                                        ,'Localidad'  => 'GRAL'
                                        ,'cantCaduco' => '0'
                                        ,'disponible' => '0'
                                        ,'categoria'  => ''
                                      ));
          }
        }

        return  json_encode(array('datos' => $datos, 'lisencePlates'=>$existencias['lisencePlates']));
    }

    public static function getExistenciasEntity($item,$sitio,$almacen){
      $token = new Token();
      $db = new Application_Model_UserinfoMapper();
      $adapter = $db->getAdapter();
      if(!$_SESSION['offline']){
        curl_setopt_array(CURL1, array(
          // CURLOPT_URL => "https://".DYNAMICS365."/Data/WarehouseInventoryStatusesOnHand?%24filter=(InventoryWarehouseId%20ne%20''%20)%20and%20ItemNumber%20eq%20'".$item."'%20and%20dataAreaId%20eq%20'".COMPANY."'%20and%20InventoryStatusId%20eq%20'Disponible'%20and%20not(InventoryWarehouseId%20eq%20'*RECM')&%24select=ItemNumber%2CAvailableOnHandQuantity%2CInventorySiteId%2CInventoryWarehouseId%2COnHandQuantity",
          CURLOPT_URL => "https://".DYNAMICS365."/Data/AYT_InventOnHandByWarehouseStatus?%24filter=(InventoryWarehouseId%20ne%20''%20)%20and%20ItemNumber%20eq%20'".$item."'%20and%20dataAreaId%20eq%20'".COMPANY."'%20and%20InventoryStatusId%20eq%20'Disponible'%20and%20not(InventoryWarehouseId%20eq%20'*RECM')%20and%20AvailableOnHandQuantity%20ne%200%20and%20wMSLocationId%20ne%20'ASIGNACION'&%24count=true",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "authorization: Bearer ".$token->getToken()[0]->Token."",
            "content-type: application/json"
          ),
        ));

        $response = curl_exec(CURL1);
        ////////////////////////ciclo para existencias///////////////////////////////////////////////////////////////////////
        $existenciasTotal = json_decode($response);
        $result = [];
        $existSitioTemp = '';
        $existAlmacTemp = '';
        $existencia = 0;
        $existencia2 = 0;
        foreach($existenciasTotal->value as $existencias){
          if ($existencias->LicensePlateId == '' && $existencias->wMSLocationId !='' && $existencias->AvailableOnHandQuantity < 0){continue;}
          if ($existSitioTemp != $existencias->InventorySiteId){
            if ($existAlmacTemp != $existencias->InventoryWarehouseId){
              $existencia = 0;
              $existencia2 = 0;
            }
          }
          $existencia += $existencias->AvailableOnHandQuantity;
          $existencia2 += $existencias->OnHandQuantity;
          $result[$existencias->InventoryWarehouseId]->ItemNumber               = $existencias->ItemNumber;
          $result[$existencias->InventoryWarehouseId]->AvailableOnHandQuantity += $existencia;
          $result[$existencias->InventoryWarehouseId]->InventorySiteId          = $existencias->InventorySiteId;
          $result[$existencias->InventoryWarehouseId]->InventoryWarehouseId     = $existencias->InventoryWarehouseId;
          $result[$existencias->InventoryWarehouseId]->OnHandQuantity          += $existencia2;
          $existSitioTemp = $existencias->InventoryWarehouseId;
        }
        $resultClean = [];
        foreach($result as $cleanData){
          $resultClean[] = $cleanData; 
        }
        $result2->value = $resultClean;
        $response = json_encode($result2);
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $err = curl_error(CURL1);
      }else{        
        ////////////////consultar la existencia en base de datos/////////////////////////////
        try{
          $conn = new DB_ConexionExport();
          $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          $query = $conn->prepare(" SELECT ItemNumber, AvailableOnHandQuantity,InventorySiteId,InventoryWarehouseId,OnHandQuantity
                                    FROM InventWarehouseInventoryStatusOnHandStaging
                                    WHERE ItemNumber = '".$item."'
                                    AND InventoryWarehouseId != ''
                                    AND InventoryWarehouseId NOT LIKE '%RECM'
                                    AND InventoryStatusId = 'Disponible';");
          $query->execute();
          $resultQuery = $query->fetchAll(PDO::FETCH_ASSOC);
          $response = [];
          $response['value'] = $resultQuery;
          $response = json_encode($response);
        }catch(Exception $e){
          print_r($e->getMessage());
        }
        /////////////////////////////////////////////////////////////////////////////////////
      }

       // curl_close(CURL1);

        if ($err) {
          print_r($response);
          exit();
          $result = "cURL Error #:" . $err;
        } else {
          $result = json_decode($response);

          if (empty($result->value)){
            if(!$_SESSION['offline']){
              curl_setopt_array(CURL1, array(
                CURLOPT_URL => "https://".DYNAMICS365."/Data/ReleasedDistinctProducts?%24select=InventoryUnitSymbol%2CItemNumber%2CSearchName&%24filter=ItemNumber%20eq%20'".$item."'",
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

              $responseProduct = curl_exec(CURL1);
            }else{
              ////////////////consultar la existencia en base de datos/////////////////////////////
              try{
                $query = $adapter->prepare("SELECT InventoryUnitSymbol,ItemNumber,SearchName
                                            FROM releasedDistinctProducts
                                            WHERE ItemNumber = '".$item."';");
                $query->execute();
                $resultQuery = $query->fetchAll(PDO::FETCH_ASSOC);
                $resultProduct = [];
                $resultProduct['value'] = $resultQuery;
                $resultProduct = json_encode($resultProduct);
              }catch(Exception $e){
                print_r($e->getMessage());
              }
              /////////////////////////////////////////////////////////////////////////////////////
            }
            $resultProduct = json_decode($responseProduct);
            $result->value[] = (object)array(
                                'ItemNumber' => $resultProduct->value[0]->ItemNumber,
                                'AvailableOnHandQuantity' => 0,
                                'InventorySiteId' => $sitio,
                                'InventoryWarehouseId' => $almacen
            );
          }
          ///////////////////consultar la categoria proyecto/////////////////////////////////////////////

          curl_setopt_array(CURL1, array(
            CURLOPT_URL => "https://".DYNAMICS365."/Data/ProductCategoryAssignments?%24filter=ProductNumber%20eq%20'".$item."'%20and%20ProductCategoryHierarchyName%20eq%20'CATEGORIA%20PROYECTO'",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
              "accept: application/json",
              "authorization: Bearer ".$token->getToken()[0]->Token."",
              "content-type: application/json"
            ),
          ));

          $response = curl_exec(CURL1);
          $err = curl_error(CURL1);

          if ($err) {
            $resultProy = "cURL Error #:" . $err;
          } else {
            $resultProy = json_decode($response);
          }
        }
       return array('existencias' => $result->value,'categoria' => $resultProy->value[0], 'lisencePlates' => $existenciasTotal);
    }

    public static function getDevExistenciasEntity($item,$sitio,$almacen){
      $token = new Token();
      $db = new Application_Model_UserinfoMapper();
      $adapter = $db->getAdapter();
        //CURL1 = curl_init();
      if (!$_SESSION['offline']){
        curl_setopt_array(CURL1, array(
          CURLOPT_URL => "https://".DYNAMICS365."/Data/WarehousesOnHand?%24select=OnHandQuantity%2CItemNumber&%24filter=ItemNumber%20eq%20'".$item."'%20and%20InventorySiteId%20eq%20'".$sitio."'%20and%20InventoryWarehouseId%20eq%20'".$almacen."'%20and%20dataAreaId%20eq%20'".COMPANY."'",
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

        $response = curl_exec(CURL1);
        $err = curl_error(CURL1);
      }else{
        ////////////////consultar la existencia en base de datos/////////////////////////////
        try{
          $conn  = new DB_ConexionExport();
          $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          $query = $conn->prepare(" SELECT ItemNumber, AvailableOnHandQuantity,InventorySiteId,InventoryWarehouseId,OnHandQuantity
                                    FROM dbo.InventWarehouseInventoryStatusOnHandStaging
                                    WHERE ItemNumber = '".$item."'
                                    AND InventoryWarehouseId != ''
                                    AND InventoryWarehouseId NOT LIKE '%RECM'
                                    AND InventoryStatusId = 'Disponible';");
          $query->execute();
          $resultQuery = $query->fetchAll(PDO::FETCH_ASSOC);
          $response = [];
          $response['value'] = $resultQuery;
          $response = json_encode($response);
        }catch(Exception $e){
          print_r($e->getMessage());
        }
        /////////////////////////////////////////////////////////////////////////////////////
      }

       // curl_close(CURL1);

        if ($err) {
          print_r($response);
          exit();
          $result = "cURL Error #:" . $err;
        } else {
          $result = json_decode($response);
        }

        return $result->value;
    }

    public static function getCTZNDataClient($param) {
        /**
        * obtiene los datos del cliente de la cotizacion
        * @param array $param con los datos necesarios para llenar los datos del cliente.
        * @return array  con los datos del cliente
        */
        // $query = $this->_adapter->prepare(GET_COTIZACION_CLIENT_DATA);
        // $query->bindParam(1,$param);
        // $query->bindParam(2,$param);
        // $query->execute();
        // $r=$query->fetchAll();
        $result = Application_Model_InicioModel::getCotizacionDataEntity($param);
        $r = $result;
        return $r;
    }

    public static function getORDVTADataClient($param){
      /**
        * obtiene los datos del cliente de la cotizacion
        * @param array $param con los datos necesarios para llenar los datos del cliente.
        * @return array  con los datos del cliente
        */
        // $query = $this->_adapter->prepare(GET_COTIZACION_CLIENT_DATA);
        // $query->bindParam(1,$param);
        // $query->bindParam(2,$param);
        // $query->execute();
        // $r=$query->fetchAll();
        $result = Application_Model_InicioModel::getOrdenVentaDataEntity($param);
        $r = $result;
        return $r;
    }

    public static function getCotizacionDataEntity($quotationId){
      $token = new Token();
      if(!$_SESSION['offline']){
        curl_setopt_array(CURL1, array(
          CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesQuotationHeaders(dataAreaId=%27".COMPANY."%27,SalesQuotationNumber=%27".$quotationId."%27)?%24select=CustomerPaymentMethodName%2CDefaultShippingSiteId%2CDefaultShippingWarehouseId%2CCurrencyCode%2CDeliveryModeCode%2CDeliveryTermsCode%2CQuotationTakerPersonnelNumber%2CQuotationResponsiblePersonnelNumber%2CRequestingCustomerAccountNumber%2CPaymentTermsName%2CSalesOrderOriginCode%2CCustomersReference",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$token->getToken()[0]->Token."",
            "content-type: application/json"
          ),
        ));

        $response = curl_exec(CURL1);
        $err = curl_error(CURL1);
      }else{
        $db = new Application_Model_UserinfoMapper();
        $adapter = $db->getAdapter();
        $query = $adapter->query("SELECT * FROM CotizacionesHeadersOffline WHERE SalesQuotationNumber = '".$quotationId."';");
        $query->execute();
        $resultQuery = $query->fetchAll(PDO::FETCH_ASSOC);
        $resultQuery = (object) $resultQuery[0];
        $response = json_encode($resultQuery);
      }

      if ($err) {
        $result = "cURL Error #:" . $err;
      } else {
        $result = json_decode($response);
      }
      $DataHeader = [];
      $arrayData = array(
        'PAYMMODE'               => $result->CustomerPaymentMethodName,
        'INVENTSITEID'           => $result->DefaultShippingSiteId,
        'INVENTLOCATIONID'       => $result->DefaultShippingWarehouseId,
        'CURRENCYCODE'           => $result->CurrencyCode,
        'DLVMODE'                => $result->DeliveryModeCode,
        'DLVTERM'                => $result->DeliveryTermsCode,
        'WORKERSALESTAKER'       => $result->QuotationTakerPersonnelNumber,
        'WORKERSALESRESPONSIBLE' => $result->QuotationResponsiblePersonnelNumber,
        'STF_OBSERVATIONS'       => $result->CustomersReference,
        'CUSTOMERREF'            => '',
        'CUSTPURCHASEORDER'      => '',
        'CUSTACCOUNT'            => $result->RequestingCustomerAccountNumber,
        'CARGO'                  => 0.0000000000000000,
        'BANKACCOUNT'            => 000,
        'PAYMENT'                => $result->PaymentTermsName,
        'SALESORIGIN'            => $result->SalesOrderOriginCode
      );
      array_push($DataHeader, $arrayData);
      return $DataHeader;
    }

    public static function getOrdenVentaDataEntity($ovId){
      $token = new Token();
      //CURL1 = curl_init();
      $db = new Application_Model_UserinfoMapper();
      $adapter = $db->getAdapter();

      if (!$_SESSION['offline']){
        curl_setopt_array(CURL1, array(
          CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesOrderHeadersV2?%24filter=SalesOrderNumber%20eq%20'".$ovId."'&%24select=CustomerPaymentMethodName%2CDefaultShippingSiteId%2CDefaultShippingWarehouseId%2CCurrencyCode%2CDeliveryModeCode%2CDeliveryTermsCode%2COrderingCustomerAccountNumber%2COrderTotalChargesAmount%2COrderTakerPersonnelNumber%2COrderResponsiblePersonnelNumber%2CCustomersOrderReference%2CCustomerRequisitionNumber%2CSalesOrderOriginCode%2CPaymentTermsName%2CSATPurpose_MX",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$token->getToken()[0]->Token."",
            "content-type: application/json"
          ),
        ));

        $response = curl_exec(CURL1);
        $err = curl_error(CURL1);
      }else{
        $queryHeader = "SELECT * FROM SalesOrderHeadersOffline WHERE SalesOrderNumber = '".$ovId."';";
        $query = $adapter->query($queryHeader);
        $query->execute();
        $resultHeader = $query->fetchAll(PDO::FETCH_OBJ);
        $response = [];
        $response['value'] = $resultHeader;
        $response = json_encode($response);
      }

     // curl_close(CURL1);

      if ($err) {
        $result = "cURL Error #:" . $err;
      } else {
        $result = json_decode($response);
      }
      $DataHeader = [];
      $arrayData = array(
        'PAYMMODE'               => $result->value[0]->CustomerPaymentMethodName,
        'INVENTSITEID'           => $result->value[0]->DefaultShippingSiteId,
        'INVENTLOCATIONID'       => $result->value[0]->DefaultShippingWarehouseId,
        'CURRENCYCODE'           => $result->value[0]->CurrencyCode,
        'DLVMODE'                => $result->value[0]->DeliveryModeCode,
        'DLVTERM'                => $result->value[0]->DeliveryTermsCode,
        'WORKERSALESTAKER'       => $result->value[0]->OrderTakerPersonnelNumber,
        'WORKERSALESRESPONSIBLE' => $result->value[0]->OrderResponsiblePersonnelNumber,
        'PURPOSE'                => $result->value[0]->SATPurpose_MX,
        'SALESORIGIN'            => $result->value[0]->SalesOrderOriginCode,
        'STF_OBSERVATIONS'       => ($result->value[0]->CustomersOrderReference == '')?'':$result->value[0]->CustomersOrderReference,
        'CUSTOMERREF'            => '',
        'CUSTPURCHASEORDER'      => '',
        'CUSTACCOUNT'            => $result->value[0]->RequestingCustomerAccountNumber,
        'CARGO'                  => 0.0000000000000000,
        'BANKACCOUNT'            => 000,
        'PAYMENT'                => $result->value[0]->PaymentTermsName
      );
      array_push($DataHeader, $arrayData);
      return $DataHeader;
    }

    public static function getCTZNDataContent($param){
        /**
        * trae los datos de la cotizacion 
        * @abstract
        * @param array $param con los datos necesarios para obtener los datos 
        * @return CTZN
        * @throws Exception si la operacion falla regresara cero '0'
        */
        $res = Application_Model_InicioModel::getCTZNDataContentEntity($param);
        $result = $res;
        return $result;
    }

    public static function getSalesORDVTAContent($param){
      /**
        * trae los datos de la cotizacion 
        * @abstract
        * @param array $param con los datos necesarios para obtener los datos 
        * @return OV
        * @throws Exception si la operacion falla regresara cero '0'
        */
        $res = Application_Model_InicioModel::getORDVTADataContentEntity($param);
        $result = $res;
        return $result;
    }

    public static function getCTZNDataContentEntity($param){
      $token = new Token();
      $db = new Application_Model_UserinfoMapper();
      $adapter = $db->getAdapter();
      $resultHead = "";
      if(!$_SESSION['offline']){
        curl_setopt_array(CURL1, array(
          CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesQuotationLines?%24filter=SalesQuotationNumber%20eq%20'".$param."'&%24select=ItemNumber%2CLineDescription%2CSalesUnitSymbol%2CRequestedSalesQuantity%2CLineAmount%2CShippingSiteId%2CShippingWarehouseId%2CInventoryLotId%2CdataAreaId%2CFixedPriceCharges",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "authorization: Bearer ".$token->getToken()[0]->Token."",
            "content-type: application/json"
          ),
        ));

        $response = curl_exec(CURL1);
        $err = curl_error(CURL1);
      }else{
        $query = $adapter->query("SELECT * FROM CotizacionesLinesOffline WHERE SalesQuotationNumber = '".$param."';");
        $query->execute();
        $resultQuery = $query->fetchAll(PDO::FETCH_OBJ);
        $response = [];
        $response['value'] = $resultQuery;
        $response = json_encode($response);
      }

      if ($err) {
        $result = "cURL Error #:" . $err;
      } else {
        $result = json_decode($response);
        if (!$_SESSION['offline']){
          curl_setopt_array(CURL1, array(
            CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesQuotationHeaders?%24filter=SalesQuotationNumber%20eq%20'".$param."'&%24select=CurrencyCode%2CSalesOrderOriginCode",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
              "accept: application/json",
              "authorization: Bearer ".$token->getToken()[0]->Token."",
              "content-type: application/json"
            ),
          ));

          $responseHeader = curl_exec(CURL1);
          $err = curl_error(CURL1);
        }else{
          $query = $adapter->query("SELECT * FROM CotizacionesHeadersOffline WHERE SalesQuotationNumber = '".$param."';");
          $query->execute();
          $resultQuery = $query->fetchAll(PDO::FETCH_OBJ);
          $responseHeader = [];
          $responseHeader['value'] = $resultQuery;
          $responseHeader = json_encode($responseHeader);
        }

        if ($err) {
          $resultHeader = "cURL Error #:" . $err;
        } else {
          $resultHeader = json_decode($responseHeader);
        }
      }
      $origen = $resultHeader->value[0]->SalesOrderOriginCode;
      $currency = $resultHeader->value[0]->CurrencyCode;
      $DataLine = [];
      foreach($result->value as $Data){
        ///////////////////////////////si es servicio/////////////////////////////////////////////////
        if (!$_SESSION['offline']){
          curl_setopt_array(CURL1, array(
            CURLOPT_URL => "https://".DYNAMICS365."/Data/ReleasedDistinctProducts?%24select=ProductType%2CItemNumber%2CSearchName%2CProductGroupId&%24filter=ItemNumber%20eq%20'".$Data->ItemNumber."'",
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

          $responsePriceProd = curl_exec(CURL1);
        }else{
          $query = $adapter->query("SELECT * FROM releasedDistinctProducts WHERE ItemNumber = '".$Data->ItemNumber."';");
          $query->execute();
          $result = $query->fetchAll(PDO::FETCH_ASSOC);
          $responsePriceProd = [];
          $responsePriceProd['value'] = $result;
          $responsePriceProd = json_encode($responsePriceProd);
        }
        $resultPriceProd = json_decode($responsePriceProd);
        $LineAmount = $Data->LineAmount;
        //if ($resultPriceProd->value[0]->ProductType == 'Service'){
        if ( ($resultPriceProd->value[0]->ProductType == 'Service') || ($resultPriceProd->value[0]->ProductGroupId == 'PRENDAS') || (($resultPriceProd->value[0]->ProductGroupId == 'ARTICULOS' &&( strpos($resultPriceProd->value[0]->ItemNumber,'9999') >= 0 ))) || (($resultPriceProd->value[0]->ProductGroupId == 'LIQUID' &&( strpos($resultPriceProd->value[0]->ItemNumber,'9910') >= 0 || strpos($resultPriceProd->value[0]->ItemNumber,'9915') >=0 || strpos($resultPriceProd->value[0]->ItemNumber,'9999') >=0 ))) ){
          $LineAmount = $Data->LineAmount - round($Data->FixedPriceCharges,2);
        }
        ////////////////////////////////////////////////////////////////////////////////////////////////
        $dataComent = explode(' - ',$Data->LineDescription);
        /////////////////////traer el nombre del producto////////////////////////////////
        if (!$_SESSION['offline']){
          curl_setopt_array(CURL1, array(
            CURLOPT_URL => "https://".DYNAMICS365."/Data/ReleasedDistinctProducts?%24filter=ItemNumber%20eq%20'".$Data->ItemNumber."'&%24select=ProductSearchName",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "authorization: Bearer ".$token->getToken()[0]->Token."",
            "content-type: application/json"
            ),
          ));

          $response = curl_exec(CURL1);
          $err = curl_error(CURL1);
        }else{
          $response = $responsePriceProd;
        }

        if ($err) {
          $resultNombre = "cURL Error #:" . $err;
        } else {
          $resultNombre = json_decode($response);
          $name =$resultNombre->value[0]->ProductSearchName;
          $name =$Data->LineDescription;
        }
        /////////////////////////////////////////////////////////////////////////////////
        ////////////////consultar el comentario en base de datos/////////////////////////////
        try{
          $db = new Application_Model_UserinfoMapper();
          $adapter = $db->getAdapter();
          $query = $adapter->prepare("SELECT * FROM dbo.AYT_ComentariosLineas WHERE documentId = '".$param."' AND inventoryLotId = '".$Data->InventoryLotId."';");
          $query->execute();
          $resultQuery = $query->fetchAll(PDO::FETCH_ASSOC);
          $comentario = (isset($resultQuery[0]['comentario']))?$resultQuery[0]['comentario']:'';
        }catch(Exception $e){
          print_r($e->getMessage());
        }
        ///////////////consultar el minimo de venta///////////////////////////////////////////
        $minimo = Application_Model_InicioModel::getMinimoVenta($Data->ItemNumber);
        /////////////////////////////////////////////////////////////////////////////////////
        $arrayData = array(
          'ITEMID'           => $Data->ItemNumber,
          'NAME'             => $name,
          'ITEMID'           => $Data->ItemNumber,
          'SALESUNIT'        => $Data->SalesUnitSymbol,
          'SALESQTY'         => $Data->RequestedSalesQuantity,
          'STF_SALESPRICE'   => $LineAmount,
          'INVENTSITEID'     => $Data->ShippingSiteId,
          'INVENTLOCATIONID' => $Data->ShippingWarehouseId,
          'INVENTORYLOTID'   => $Data->InventoryLotId,
          'DATAAREAID'       => $Data->dataAreaId,
          'ORIGENVENTA'      => $origen,
          'CAMBIOWS'         => '0',
          'PRECIOWS'         => '0',
          'CURRENCYCODE'     => $currency,
          'STF_OBSERVATIONS' => $comentario,
          'BLOCKSALESPRICES' => '1',
          'MINIMOVENTA'      => $minimo
        );
        array_push($DataLine, $arrayData);
      }

      $data = [];      
      array_push($data, "Backorder");
      array_push($data, "Open"); 
      array_push($data, $DataLine); 
      return $data;
      //return $DataLine;
    }

    public static function getORDVTADataContentEntity($param){
      $token = new Token();
      $db = new Application_Model_UserinfoMapper();
      $adapter = $db->getAdapter();
      $resultHead = "";
      if (!$_SESSION['offline']){
      ///////////////////////////////////////datos cabecera//////////////////////////////////////////////////////
        curl_setopt_array(CURL1, array(
          CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesOrderHeadersV2?%24filter=SalesOrderNumber%20eq%20'".$param."'&%24select=CurrencyCode%2CSalesOrderOriginCode%2CSalesOrderStatus%2CReleaseStatus",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$token->getToken()[0]->Token."",
            "content-type: application/json"
          ),
        ));

        $response = curl_exec(CURL1);
        $err = curl_error(CURL1);

        if ($err) {
          $resultHead = "cURL Error #:" . $err;
        } else {
          $resultHead = json_decode($response);
          
        }
        //return $resultHead;
      /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

      //////////////////////datos lineas///////////////////////////////////////////////////////////////////////////////////
        curl_setopt_array(CURL1, array(
          CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesOrderLines?%24filter=SalesOrderNumber%20eq%20'".$param."'&%24select=ItemNumber%2CLineDescription%2CSalesUnitSymbol%2COrderedSalesQuantity%2CLineAmount%2CShippingSiteId%2CShippingWarehouseId%2CdataAreaId%2CInventoryLotId%2CFixedPriceCharges",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$token->getToken()[0]->Token."",
            "content-type: application/json"
          ),
        ));

        $response = curl_exec(CURL1);
        $err = curl_error(CURL1);
        if ($err) {
          $result = "cURL Error #:" . $err;
        } else {
          $result = json_decode($response);
        }
      /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
      }else{
        ///////////////////////////////////////datos cabecera////////////////////////////////////////////////////////////////
        $queryCabecera = "SELECT * FROM SalesOrderHeadersOffline WHERE SalesOrderNumber = '".$param."';";
        $query = $adapter->query($queryCabecera);
        $resultCabe = $query->fetchAll(PDO::FETCH_OBJ);
        $response = [];
        $response['value'] = $resultCabe;
        $response = json_encode($response);
        $resultHead = json_decode($response);
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //////////////////////datos lineas///////////////////////////////////////////////////////////////////////////////////
        $queryLineas = "SELECT * FROM SalesOrderLinesOffline WHERE SalesOrderNumber = '".$param."';";
        $query = $adapter->query($queryLineas);
        $resultLinea = $query->fetchAll(PDO::FETCH_OBJ);
        $response = [];
        $response['value'] = $resultLinea;
        $response = json_encode($response);
        $result = json_decode($response);
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
      }

      $origen = $resultHead->value[0]->SalesOrderOriginCode;
      $currency = $resultHead->value[0]->CurrencyCode;
      $DataLine = [];
      foreach($result->value as $Data){
        ///////////////////////////////si es servicio/////////////////////////////////////////////////
        if (!$_SESSION['offline']){
          curl_setopt_array(CURL1, array(
            CURLOPT_URL => "https://".DYNAMICS365."/Data/ReleasedDistinctProducts?%24select=ProductType%2CItemNumber%2CSearchName%2CProductGroupId&%24filter=ItemNumber%20eq%20'".$Data->ItemNumber."'",
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

          $responsePriceProd = curl_exec(CURL1);
          $resultPriceProd = json_decode($responsePriceProd);
        }else{
          $queryProds = "SELECT ProductType,ItemNumber,SearchName,ProductGroupId FROM releasedDistinctProducts WHERE ItemNumber = '".$Data->ItemNumber."';";
          $query = $adapter->query($queryProds);
          $query->execute();
          $resultPrice = $query->fetchAll(PDO::FETCH_OBJ);
          $responsePriceProd = [];
          $responsePriceProd['value'] = $resultPrice;
          $responsePriceProd = json_encode($responsePriceProd);
          $resultPriceProd = json_decode($responsePriceProd);
        }

        $LineAmount = $Data->LineAmount;
        //if ($resultPriceProd->value[0]->ProductType == 'Service'){
        if ( ($resultPriceProd->value[0]->ProductType == 'Service') || ($resultPriceProd->value[0]->ProductGroupId == 'PRENDAS') || (($resultPriceProd->value[0]->ProductGroupId == 'ARTICULOS' &&( strpos($resultPriceProd->value[0]->ItemNumber,'9999') >= 0 ))) || (($resultPriceProd->value[0]->ProductGroupId == 'LIQUID' &&( strpos($resultPriceProd->value[0]->ItemNumber,'9910') >= 0 || strpos($resultPriceProd->value[0]->ItemNumber,'9915') >=0 || strpos($resultPriceProd->value[0]->ItemNumber,'9999') >=0 ))) ){
          $LineAmount = $Data->LineAmount - round($Data->FixedPriceCharges,2);
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////
        $dataComent = explode(' - ',$Data->LineDescription);
        //$name = $resultPriceProd->value[0]->SearchName;
        $name = $Data->LineDescription;
        $comentario = (isset($dataComent[1]))?$dataComent[1]:'';
        ////////////////consultar el comentario en base de datos/////////////////////////////
        try{
          $db = new Application_Model_UserinfoMapper();
          $adapter = $db->getAdapter();
          $query = $adapter->prepare("SELECT * FROM dbo.AYT_ComentariosLineas WHERE documentId = '".$param."' AND inventoryLotId = '".$Data->InventoryLotId."';");
          $query->execute();
          $resultQuery = $query->fetchAll(PDO::FETCH_ASSOC);
          $comentario = (isset($resultQuery[0]['comentario']))?$resultQuery[0]['comentario']:'';
        }catch(Exception $e){
          print_r($e->getMessage());
        }
        ///////////////consultar el minimo de venta///////////////////////////////////////////
        $minimo = Application_Model_InicioModel::getMinimoVenta($Data->ItemNumber);
        /////////////////////////////////////////////////////////////////////////////////////
        $arrayData = array(
          'ITEMID'           => $Data->ItemNumber,
          'NAME'             => $name,
          'SALESUNIT'        => $Data->SalesUnitSymbol,
          'SALESQTY'         => $Data->OrderedSalesQuantity,
          'STF_SALESPRICE'   => $LineAmount,
          'INVENTSITEID'     => $Data->ShippingSiteId,
          'INVENTLOCATIONID' => $Data->ShippingWarehouseId,
          'INVENTORYLOTID'   => $Data->InventoryLotId,
          'DATAAREAID'       => $Data->dataAreaId,
          'ORIGENVENTA'      => $origen,
          'CAMBIOWS'         => '0',
          'PRECIOWS'         => '0',
          'CURRENCYCODE'     => $currency,
          'STF_OBSERVATIONS' => $comentario,
          'BLOCKSALESPRICES' => '1',
          'MINIMOVENTA'      => $minimo
        );
        array_push($DataLine, $arrayData);               
      }
      $data = [];      
      array_push($data, $resultHead->value[0]->SalesOrderStatus);
      array_push($data, $resultHead->value[0]->ReleaseStatus); 
      array_push($data, $DataLine); 
      return $data;
    }

    public function getPriceFromDB($cliente,$item,$moneda,$company,$cargo,$err=null) {
        /**
        * funcion de prueba para calcular precios desde la base de datos
        * @param type $cliente
        * @param type $item
        * @param type $moneda
        * @param type $company
        * @param type $cargo
        * @param type $err
        * @return type
        */
        $listaPrecios=$listaDescuento=$porcentaje=$precioLista='';
        // consulta lista de precios y descuento del cliente
        $q = $this->_adapter->prepare("select t0.PRICEGROUP,t0.LINEDISC from CUSTTABLE t0 where t0.ACCOUNTNUM=?;");
        $q->bindParam(1,$cliente);
        $q->execute();
        $r =$q->fetchAll();
        foreach ($r as $k => $v) {
            $listaPrecios=$v['PRICEGROUP'];
            $listaDescuento=$v['LINEDISC'];
        }
        $q2 = $this->_adapter->prepare("select top 1 t0.AMOUNT from PriceDiscTable t0 where t0.ITEMRELATION= ? and t0.ACCOUNTRELATION= ? and t0.DATAAREAID= ? order by t0.MODIFIEDDATETIME desc;");
        $q2->bindParam(1,$item);
        $q2->bindParam(2,$listaPrecios);
        $q2->bindParam(3,$company);
        $q2->execute();
        $r2 =$q2->fetchAll();
        foreach ($r2 as $k => $v) {
            $precioLista=$v['AMOUNT'];
        }
        // 
        $q3 = $this->_adapter->prepare("select t0.PERCENT1,t0.FROMDATE,t0.TODATE,t0.QUANTITYAMOUNTFROM,t0.QUANTITYAMOUNTTO from PriceDiscTable t0 where t0.ITEMRELATION=? and t0.ACCOUNTRELATION=? and t0.CURRENCY=? and t0.QUANTITYAMOUNTFROM=0;");
        $q3->bindParam(1,$item);
        $q3->bindParam(2,$listaDescuento);
        $q3->bindParam(3,$moneda);
        $q3->execute();
        $r3 =$q3->fetchAll();
        foreach($r3 as $k => $v){
            $porcentaje=$v['PERCENT1'];
        }
        $price=($precioLista-($precioLista*($porcentaje/100)));
        if((integer)$cargo!=0){
            $price=$price+($price*((double)$cargo/100)); 
        }
        $e="";
        if(!empty($err)){
            $e=$err->getTraceAsString();
        }
        return array('preciocargo'=>($price),'precioiva'=>$price*1.16,"error"=>$e,"lista_precios"=>$listaPrecios,"descuento"=>$listaDescuento,"precio_lista"=>$precioLista); //round($price, 3);
    }

    /**
    * setea los datos del cliente en la cotizacion
    * @param array $result con los datos necesarios para llenar los datos del cliente.
    */
    public static function setCTZNDataClient($result,$coti){

      $token = new Token();
      $db = new Application_Model_UserinfoMapper();
      $adapter = $db->getAdapter();
      if (!$_SESSION['offline']){
        curl_setopt_array(CURL1, array(
          CURLOPT_URL => "https://".DYNAMICS365."/data/SalesQuotationLines?%24filter=SalesQuotationNumber%20eq%20'".$coti."'%20and%20FixedPriceCharges%20gt%200&%24top=0&%24count=true",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_HTTPHEADER => array(
          "authorization: Bearer ".$token->getToken()[0]->Token."",
          "content-type: application/json; charset=utf-8"
          ),
        ));

        $response = curl_exec(CURL1);
        $err = curl_error(CURL1);
      }else{
        $query = $adapter->query("SELECT * FROM CotizacionesHeadersOffline WHERE SalesQuotationNumber = '".$coti."';");
        $query->execute();
        $resultQuery        = $query->fetchAll(PDO::FETCH_OBJ);
        $response           = [];
        $response['@odata.count'] = sizeof($resultQuery);
        $response['value']  = $resultQuery;
        $response           = json_encode($response);
      }

      $resultado = json_decode($response);
      $conteo = json_decode(json_encode($resultado), True);     
      $metodo = $result[0]['PAYMMODE'];
      if($result[0]['PAYMMODE']=='02' && $conteo["@odata.count"] == 0 ){//aquí estaba lo de cheque sin cargos con 02a
        $metodo = '02';
      }

      $_POST['PAYMENT']                 = $result[0]['PAYMENT'];
      $_POST['PAYMMODE']                = $metodo;//$result[0]['PAYMMODE'];
      $_POST['INVENTSITEID']            = $result[0]['INVENTSITEID'];
      $_POST['INVENTLOCATIONID']        = $result[0]['INVENTLOCATIONID'];
      $_POST['CURRENCYCODE']            = $result[0]['CURRENCYCODE'];
      $_POST['DLVMODE']                 = $result[0]['DLVMODE'];
      $_POST['DLVTERM']                 = $result[0]['DLVTERM'];
      $_POST['WORKERSALESTAKER']        = $result[0]['WORKERSALESTAKER'];
      $_POST['WORKERSALESRESPONSIBLE']  = $result[0]['WORKERSALESRESPONSIBLE'];
      $_POST['STF_OBSERVATIONS']        = $result[0]['STF_OBSERVATIONS'];
      $_POST['CUSTOMERREF']             = $result[0]['CUSTOMERREF'];
      $_POST['CUSTPURCHASEORDER']       = $result[0]['CUSTPURCHASEORDER'];
      $_POST['BANKACCOUNT']             = $result[0]['BANKACCOUNT'];
      $_POST['ORIGENVENTA']             = $result[0]['SALESORIGIN'];
      $_POST['PURPOSE']                 = $result[0]['PURPOSE'];
    }

    public static function setORDVTADataClient($result,$ov){
      /**
      * setea los datos del cliente en la cotizacion
      * @param array $result con los datos necesarios para llenar los datos del cliente.
      */

      $token = new Token();
      $db = new Application_Model_UserinfoMapper();
      $adapter = $db->getAdapter();
      if (!$_SESSION['offline']){
        curl_setopt_array(CURL1, array(
          CURLOPT_URL => "https://".DYNAMICS365."/data/SalesOrderLines?%24filter=SalesOrderNumber%20eq%20'".$ov."'%20and%20FixedPriceCharges%20gt%200&%24top=0&%24count=true",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_HTTPHEADER => array(
          "authorization: Bearer ".$token->getToken()[0]->Token."",
          "content-type: application/json; charset=utf-8"
          ),
        ));

        $response = curl_exec(CURL1);
        $err = curl_error(CURL1);
      }else{
        $query = $adapter->query("SELECT * FROM SalesOrderHeadersOffline WHERE SalesOrderNumber = '".$ov."';");
        $query->execute();
        $resultQuery        = $query->fetchAll(PDO::FETCH_OBJ);
        $response           = [];
        $response['@odata.count'] = sizeof($resultQuery);
        $response['value']  = $resultQuery;
        $response           = json_encode($response);
      }

      $resultado = json_decode($response);
      $conteo = json_decode(json_encode($resultado), True);     
      $metodo = $result[0]['PAYMMODE'];
      if($result[0]['PAYMMODE']=='02' && $conteo["@odata.count"] == 0 ){//aquí estaba lo de cheque sin cargos con 02a en vez de 02
        $metodo = '02';
      }

      $_POST['PAYMENT']                 = $result[0]['PAYMENT'];
      $_POST['PAYMMODE']                = $metodo;//$result[0]['PAYMMODE'];
      $_POST['INVENTSITEID']            = $result[0]['INVENTSITEID'];
      $_POST['INVENTLOCATIONID']        = $result[0]['INVENTLOCATIONID'];
      $_POST['CURRENCYCODE']            = $result[0]['CURRENCYCODE'];
      $_POST['DLVMODE']                 = $result[0]['DLVMODE'];
      $_POST['DLVTERM']                 = $result[0]['DLVTERM'];
      $_POST['WORKERSALESTAKER']        = $result[0]['WORKERSALESTAKER'];
      $_POST['WORKERSALESRESPONSIBLE']  = $result[0]['WORKERSALESRESPONSIBLE'];
      $_POST['STF_OBSERVATIONS']        = $result[0]['STF_OBSERVATIONS'];
      $_POST['CUSTOMERREF']             = $result[0]['CUSTOMERREF'];
      $_POST['CUSTPURCHASEORDER']       = $result[0]['CUSTPURCHASEORDER'];
      $_POST['BANKACCOUNT']             = $result[0]['BANKACCOUNT'];
      $_POST['ORIGENVENTA']             = $result[0]['SALESORIGIN'];
      $_POST['PURPOSE']                 = $result[0]['PURPOSE'];
    }

    public static function checarPreciosNew($params){
        /**
        * @param array $params arreglo con los datos necesarios para consultar precios desde el web service directo de dynamics
        * @return array tipo JSON con el precio y el precio con iva
        * @throws Exception SOAP Exception si la operacion falla regresara cero '0'
        */
        try {
            $ws= new Metodos();
            $params['_company']=COMPANY; 
            $result = $ws->getPriceItems($params);
            return $result;

            // $result         = Application_Model_InicioModel::setLineasEntity($params);
            // $precio         = $result->SalesPrice;
            // $monto          = ($result->LineAmount / $result->RequestedSalesQuantity);
            // $dataAreaId     = $result->dataAreaId;
            // $InventoryLotId = $result->InventoryLotId;
            // return array('precio'=>$precio,'precio_iva'=>$monto,'dataAreaId' =>$dataAreaId,'InventoryLotId'=>$InventoryLotId);
        } catch (Exception $objError) {
           throw new Exception ($objError->getTraceAsString());
           //return $this->getPriceFromDB($params['_CustAccount'], $params['_ItemId'],$params['_currencyCode'],COMPANY, $params['_PercentCharges'],$objError);
        }
    }

    public static function reviewOrder($ov,$paymode,$modoent,$condi){
      $token = new Token();
      $tipoPago='PUE';
      if($paymode=="99"){
          $tipoPago='PPD';
      }else{
          $tipoPago='PUE';
      }

      if (!$_SESSION['offline']){
        if ($tipoPago != '' && $modoent != '' && $paymode != '' && $condi != ''){

            curl_setopt_array(CURL1, array(
             CURLOPT_URL => "https://".DYNAMICS365."/data/SalesOrderHeadersV2(dataAreaId=%27".COMPANY."%27,SalesOrderNumber=%27".$ov."%27)",
             CURLOPT_RETURNTRANSFER => true,
             CURLOPT_ENCODING => "",
             CURLOPT_MAXREDIRS => 10,
             CURLOPT_TIMEOUT => 30,
             CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
             CURLOPT_CUSTOMREQUEST => "PATCH",
             CURLOPT_POSTFIELDS => "{\"SATPaymMethod_MX\":\"".$tipoPago."\",
                                     \"DeliveryModeCode\":\"".$modoent."\",
                                     \"CustomerPaymentMethodName\":\"".$paymode."\",
                                     \"DeliveryTermsCode\" : \"".$condi."\"
                                    }",
             CURLOPT_HTTPHEADER => array(
             "authorization: Bearer ".$token->getToken()[0]->Token."",
              "content-type: application/json"
             ),
            ));
        }else{
          $model= new Application_Model_InicioModel();
          $user=array('1'=>'sistemas9@avanceytec.com.mx','2'=>'sistemas6@avanceytec.com.mx','3'=>'sistemas11@avanceytec.com.mx');//"sistemas6@avanceytec.com.mx;sistemas@avanceytec.com.mx;telemarketing14@avanceytec.com.mx";
          $asunto='InaxLog reviewOrder';
          $model->sendMail($user, $asunto, 'Fallo en reviewOrder datos vacios docId: '.$ov.'<br>{"SATPaymMethod_MX":"'.$tipoPago.'","DeliveryModeCode":"'.$modoent.'","CustomerPaymentMethodName":"'.$paymode.'","DeliveryTermsCode" : "'.$condi.'"}"');
          $result = 'Fallo';
        }
        
        $response2 = curl_exec(CURL1);
        $err = curl_error(CURL1);

        curl_setopt_array(CURL1, array(
          CURLOPT_URL => "https://".DYNAMICS365."/data/SalesOrderHeadersV2?%24filter=SalesOrderNumber%20eq%20'".$ov."'&%24select=DeliveryModeCode%2CSATPaymMethod_MX%2CSATPurpose_MX%2CDeliveryTermsCode%2CCustomerPaymentMethodName",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_HTTPHEADER => array(
          "authorization: Bearer ".$token->getToken()[0]->Token."",
          "content-type: application/json"
          ),
        ));
        
        $responseCheck = curl_exec(CURL1);
        $err = curl_error(CURL1);
        $responseCheck = json_decode($responseCheck);

        

        $falta="";
        if($responseCheck->value[0]->CustomerPaymentMethodName=="" || $responseCheck->value[0]->DeliveryModeCode=="" || $responseCheck->value[0]->DeliveryTermsCode=="" || $responseCheck->value[0]->SATPaymMethod_MX=="" || $responseCheck->value[0]->SATPurpose_MX==""){
          // $falta="La orden de venta no tiene el campo : </br>" .  PHP_EOL;
          // if ($responseCheck->value[0]->CustomerPaymentMethodName==""){
          //   $falta =$falta ."Metodo de pago </br>". PHP_EOL;
          // }
          // if ($responseCheck->value[0]->DeliveryModeCode==""){
          //   $falta =$falta ."Modo de entrega </br>". PHP_EOL;
          // }
          // if ($responseCheck->value[0]->DeliveryTermsCode==""){
          //   $falta =$falta ."Condiciones de entrega </br>". PHP_EOL;
          // }
          // if ($responseCheck->value[0]->SATPaymMethod_MX==""){
          //   $falta =$falta ."Tipo de pago </br>". PHP_EOL;
          // }
          // if ($responseCheck->value[0]->SATPurpose_MX==""){
          //   $falta =$falta ."Proposito </br>". PHP_EOL;
          // }
          if ($tipoPago != '' && $modoent != '' && $paymode != '' && $condi != ''){
            curl_setopt_array(CURL1, array(
             CURLOPT_URL => "https://".DYNAMICS365."/data/SalesOrderHeadersV2(dataAreaId=%27".COMPANY."%27,SalesOrderNumber=%27".$ov."%27)",
             CURLOPT_RETURNTRANSFER => true,
             CURLOPT_ENCODING => "",
             CURLOPT_MAXREDIRS => 10,
             CURLOPT_TIMEOUT => 30,
             CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
             CURLOPT_CUSTOMREQUEST => "PATCH",
             CURLOPT_POSTFIELDS => "{\"SATPaymMethod_MX\":\"".$tipoPago."\",
                                     \"DeliveryModeCode\":\"".$modoent."\",
                                     \"CustomerPaymentMethodName\":\"".$paymode."\",
                                     \"DeliveryTermsCode\" : \"".$condi."\"
                                   }",
             CURLOPT_HTTPHEADER => array(
             "authorization: Bearer ".$token->getToken()[0]->Token."",
              "content-type: application/json"
             ),
           ));      
           
           $response2 = curl_exec(CURL1);
           $err = curl_error(CURL1);
         }else{
            $model= new Application_Model_InicioModel();
            $user=array('1'=>'sistemas9@avanceytec.com.mx','2'=>'sistemas6@avanceytec.com.mx','3'=>'sistemas11@avanceytec.com.mx');//"sistemas6@avanceytec.com.mx;sistemas@avanceytec.com.mx;telemarketing14@avanceytec.com.mx";
            $asunto='InaxLog reviewOrder';
            $model->sendMail($user, $asunto, 'Fallo en reviewOrder datos vacios docId: '.$ov."{\"SATPaymMethod_MX\":\"".$tipoPago."\",\"DeliveryModeCode\":\"".$modoent."\",\"CustomerPaymentMethodName\":\"".$paymode."\",\"DeliveryTermsCode\" : \"".$condi."\"}");
          }
        }else{
          // $model= new Application_Model_InicioModel();
          // $user=array('1'=>'sistemas9@avanceytec.com.mx','2'=>'sistemas6@avanceytec.com.mx','3'=>'sistemas11@avanceytec.com.mx');//"sistemas6@avanceytec.com.mx;sistemas@avanceytec.com.mx;telemarketing14@avanceytec.com.mx";
          // $asunto='reviewOrder';
          // $model->sendMail($user, $asunto, 'Fallo en reviewOrder datos vacios docId: '.$ov."{\"SATPaymMethod_MX\":\"".$tipoPago."\",\"DeliveryModeCode\":\"".$modoent."\",\"CustomerPaymentMethodName\":\"".$paymode."\",\"DeliveryTermsCode\" : \"".$condi."\"}");
        }
      }else{
        $falta = '<b>OFFLINE!.</b>';
      }

      // CustomerPaymentMethodName: "99"
      // DeliveryModeCode: "PASA"
      // DeliveryTermsCode: "CREDITO"
      // SATPaymMethod_MX: "PPD"

      return $falta;
    }

    public static function saveguide($ov,$paqueteria,$guia,$descripcion,$cliente){
      Application_Model_InicioModel::$log = new Application_Model_Userinfo();
      Application_Model_InicioModel::$log->guardarguia($ov,$paqueteria,$guia,$descripcion);
      $token = new Token();
           curl_setopt_array(CURL1, array(
           CURLOPT_URL => "https://".DYNAMICS365."/Data/CustomersV3?%24filter=CustomerAccount%20eq%20'".$cliente."'&%24select=PrimaryContactEmail",
           CURLOPT_RETURNTRANSFER => true,
           CURLOPT_ENCODING => "",
           CURLOPT_MAXREDIRS => 10,
           CURLOPT_TIMEOUT => 30,
           CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
           CURLOPT_CUSTOMREQUEST => "GET",
           CURLOPT_POSTFIELDS => "",
           CURLOPT_HTTPHEADER => array(
             "authorization: Bearer ".$token->getToken()[0]->Token."",
             "content-type: application/json"
           ),
         ));
       
         $response = curl_exec(CURL1);
         $err = curl_error(CURL1);
         $response = json_decode($response);
        $user=$response->value[0]->PrimaryContactEmail;
        curl_setopt_array(CURL1, array(
            CURLOPT_URL => "https://".DYNAMICS365."/data/SalesOrderHeaders?%24filter=SalesOrderNumber%20eq%20'".$ov."'&%24select=OrderTakerPersonnelNumber,RequestedShippingDate",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$token->getToken()[0]->Token."",
            "content-type: application/json"
            ),
        ));
                   
         $response2 = curl_exec(CURL1);
         $err = curl_error(CURL1);
         $response2 = json_decode($response2);

         $fecha = explode("T",$response2->value[0]->RequestedShippingDate);
         $fecha = $fecha[0];

         curl_setopt_array(CURL1, array(
            CURLOPT_URL => "https://".DYNAMICS365."/data/Employees?%24filter=PersonnelNumber%20eq%20'".$response2->value[0]->OrderTakerPersonnelNumber."'&%24select=Name",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$token->getToken()[0]->Token."",
            "content-type: application/json"
            ),
        ));
                   
         $response3 = curl_exec(CURL1);
         $err = curl_error(CURL1);
         $response3 = json_decode($response3);



        curl_setopt_array(CURL1, array(
            CURLOPT_URL => "https://".DYNAMICS365."/data/SalesOrderLines?%24filter=SalesOrderNumber%20eq%20'".$ov."'&%24select=LineDescription,OrderedSalesQuantity",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$token->getToken()[0]->Token."",
            "content-type: application/json"
            ),
        ));
                   
         $response = curl_exec(CURL1);
         $err = curl_error(CURL1);
         $response = json_decode($response);
         $lineas = '<table style="width:100%;border:1px solid black; padding:5px;" cellspadding="3" border="1">
              <tr>
                <th style="background-color: #334f8b; color: White; text-align:left; padding:5px;">Descripción</th>
                <th style="background-color: #334f8b; color: White; text-align:center; padding:5px;">Cantidad</th>
              </tr>
         ';

          foreach ($response->value as $value){
            $lineas = $lineas .'
              <tr>
                <td style="width:90%; padding:5px;">'.$value->LineDescription.'</td>
                <th style="width:10%; text-align:center;">'.$value->OrderedSalesQuantity.'</th>
              </tr>';
          }
        $lineas = $lineas . '</table>';
        //,'2'=>'sistemas@avanceytec.com.mx','3'=>'telemarketing14@avanceytec.com.mx', '4'=>'ventascat21@avanceytec.com.mx','5'=>'sistemas11@avanceytec.com.mx'
        $user=array('1'=>'sistemas7@avanceytec.com.mx','9'=>$user,'3'=>'telemarketing14@avanceytec.com.mx', '4'=>'ventascat21@avanceytec.com.mx');//"sistemas6@avanceytec.com.mx;sistemas@avanceytec.com.mx;telemarketing14@avanceytec.com.mx";
        $asunto='Seguimiento de envios';
        $titulo='Sus artículos fueron enviados con la paquetería: ' . $paqueteria;
        $mensaje.='<b># Guía: </b>'.$guia.'<br><br><b>Paquetería: </b>'.$paqueteria.'<br><br><b>Descripcion: </b>'.$descripcion.'<br><br><b>Orden de venta: </b>'.$ov.'<br><br><b>Vendedor: </b>'.$response3->value[0]->Name.'<br><br><b>Fecha: </b>'.$fecha.'<br><br><hr><h3>Articulos: </hr>'.$lineas.'<br><br>';
        $body .= file_get_contents(APPLICATION_PATH.'/configs/correoBody3.php');
        $css =  file_get_contents(BOOTSTRAP_PATH.'css/bootstrap.min.css');
        $bodytag = str_replace("{MENSAJE}", $mensaje, $body);
        $bodytag2 = str_replace("{TITULO}", $titulo, $bodytag);
        $bodytag3 = str_replace("<style></style>",'<style>'.$css.'</style>', $bodytag2);
        $model= new Application_Model_InicioModel();
        $model->sendMailGuias($user, $asunto, $bodytag3);
        return "o";
        exit();

    }

    public function paqueterias($ov){
      try {
            $q = "SELECT TOP(1)* FROM inax365_paqueterias WHERE ov = '$ov'";
            $query = $this->_adapter->prepare($q);
            $query->execute();
            $result = $query->fetchAll();
            return $result;
            exit();
        } catch (Exception $exc) {
             throw new Exception ($exc);
        }
    }

    public static function checkOV($ov){
      $token = new Token();
      curl_setopt_array(CURL1, array(
          CURLOPT_URL => "https://".DYNAMICS365."/data/SalesOrderHeadersV2?%24filter=SalesOrderNumber%20eq%20%27".$ov."%27&%24select=DefaultShippingSiteId%2CSalesTaxGroupCode%2CDeliveryModeCode%2CDeliveryTermsCode%2CCurrencyCode%2CCustomerPaymentMethodName%2CSATPurpose_MX%2CSATPaymMethod_MX",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_HTTPHEADER => array(
          "authorization: Bearer ".$token->getToken()[0]->Token."",
           "content-type: application/json"
          ),
        ));

        $response2        = curl_exec(CURL1);
        $err              = curl_error(CURL1);
        $resultOV         = json_decode($response2);
      $ws= new Metodos();
      $noFaltan = $ws->checkOV($resultOV);
      return $noFaltan;
    }

    public static function liberar($ov,$sitio,$impuestos,$dlvMode,$condiEntrega,$moneda,$metodoPagoCode,$proposito,$paymentTermName){
      Application_Model_InicioModel::$log = new Application_Model_Userinfo();
      $ws = new Metodos();
      $tipoPago='PUE';
      if($metodoPagoCode=="99"){
        $tipoPago='PPD';
      }else{
        $tipoPago='PUE';
      }
      $token = new Token();

      curl_setopt_array(CURL1, array(
        CURLOPT_URL => "https://".DYNAMICS365."/api/services/STF_INAX/STF_LiberacionOV/releaseToWarehouseV2",//"Error desconocido"
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "{'salesId' : '".$ov."',
                                'company' : '".COMPANY."'}",
        CURLOPT_HTTPHEADER => array(
          "authorization: Bearer ".$token->getToken()[0]->Token."",
          "content-type: application/json"
        ),
      ));

      $response = curl_exec(CURL1);
      $response = str_replace('\\', '', $response);
      $response = substr($response, 1,-1);
      $response = json_decode($response);
      $err = curl_error(CURL1);

      // $noFaltan = Application_Model_InicioModel::checkOV($ov);
      $noFaltan = $ws->checkOV($response);
      if(!$noFaltan && ($sitio != '' && $impuestos != '' && $dlvMode != '' && $condiEntrega != '' && $metodoPagoCode != '' && $tipoPago != '' && $proposito != '' && $paymentTermName != '')){
        Application_Model_InicioModel::$log->kardexLog("Parchar OV por falta de datos(liberacion): ".$ov,
                            "{\"sitio\" : \"".$sitio."\",\"impuestos\" : \"".$impuestos."\",\"DeliveryModeCode\":\"".$dlvMode."\",\"condiEntrega\" : \"".$condiEntrega."\",\"moneda\" : \"".$moneda."\",\"SATPaymMethod_MX\":\"".$tipoPago."\",\"CustomerPaymentMethodName\":\"".$metodoPagoCode."\",\"proposito\" : \"".$proposito."\",\"paymentTermName\" : \"".$paymentTermName."\"}",
                            "exito",
                            1,
                            'Parchar OV por falta de datos(liberacion)');

        curl_setopt_array(CURL1, array(
          CURLOPT_URL => "https://".DYNAMICS365."/data/SalesOrderHeadersV2(dataAreaId=%27".COMPANY."%27,SalesOrderNumber=%27".$ov."%27)",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "PATCH",
          CURLOPT_POSTFIELDS => "{
                                  \"DefaultShippingSiteId\":\"".$sitio."\",
                                  \"SalesTaxGroupCode\":\"".$impuestos."\",
                                  \"DeliveryModeCode\":\"".$dlvMode."\",
                                  \"DeliveryTermsCode\":\"".$condiEntrega."\",
                                  \"CustomerPaymentMethodName\":\"".$metodoPagoCode."\",
                                  \"SATPaymMethod_MX\":\"".$tipoPago."\",
                                  \"SATPurpose_MX\":\"".$proposito."\",
                                  \"PaymentTermsName\":\"".$paymentTermName."\"
                                }",
          CURLOPT_HTTPHEADER => array(
          "authorization: Bearer ".$token->getToken()[0]->Token."",
           "content-type: application/json"
          ),
        ));

        $response2 = curl_exec(CURL1);
        $err = curl_error(CURL1);
      }else if (!$noFaltan){
        $model= new Application_Model_InicioModel();
        $user=array('1'=>'sistemas9@avanceytec.com.mx','2'=>'sistemas6@avanceytec.com.mx','3'=>'sistemas11@avanceytec.com.mx');//"sistemas6@avanceytec.com.mx;sistemas@avanceytec.com.mx;telemarketing14@avanceytec.com.mx";
        $asunto='InaxLog liberar';
        $model->sendMail($user, $asunto, 'Fallo en liberar datos vacios docId: '.$ov."{\"SATPaymMethod_MX\":\"".$tipoPago."\",\"DeliveryModeCode\":\"".$modoent."\",\"CustomerPaymentMethodName\":\"".$paymode."\",\"DeliveryTermsCode\" : \"".$condi."\"}");
      }

        curl_setopt_array(CURL1, array(
          CURLOPT_URL => "https://".DYNAMICS365."/api/services/STF_INAX/STF_LiberacionOV/releaseToWarehouseV2",//"Error desconocido"
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => "{'salesId' : '".$ov."',
                                  'company' : '".COMPANY."'}",
          CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$token->getToken()[0]->Token."",
            "content-type: application/json"
          ),
        ));

        $response = curl_exec(CURL1);
        $response = str_replace('\\', '', $response);
        $response = substr($response, 1,-1);
        $response = json_decode($response);
        $err = curl_error(CURL1);
        sleep(5);
      curl_setopt_array(CURL1, array(
        CURLOPT_URL =>"https://".DYNAMICS365."/Data/STF_WHSWorkLineEntity?%24filter=OrderNum%20eq%20%27".$ov."%27",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
        "authorization: Bearer ".$token->getToken()[0]->Token."",
        "content-type: application/json"
        ),
      ));
      $response2 = curl_exec(CURL1);
      $err = curl_error(CURL1);

      $response2=json_decode($response2);
      // print_r($response2->value[0]->WorkId);exit();

     // curl_close(CURL1);

      if ($err) {
        $result = "cURL Error #:" . $err;
      } else {
        if ($response2->value[0]->WorkId != ''){
          $res = array('status' => 'exito','msg' => $response->response);
          ///////////////envio de mensajes WPAY/////////////////////////////////////////////////////////
          $modoEntrega = Application_Model_InicioModel::getDeliveryModeCode($ov);
          (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
          $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          $querySucu = "SELECT * FROM AYT_CierreSucursales WHERE sucursal = '$sitio' AND status = 1";
          $resourceSucu = $conn->query($querySucu);
          $resourceSucu->execute();
          $resultSucu = $resourceSucu->fetchAll(PDO::FETCH_ASSOC);

          $flagSitio    = false;
          if (!empty( $resultSucu )){

            $sucursal     = $resultSucu[0]['sucursal'];
            $descripcion  = $resultSucu[0]['descripcion'];
            $horaApertura = new DateTime($resultSucu[0]['horaApertura']);
            $horaCierre   = new DateTime($resultSucu[0]['horaCierre']);
            $horaLimite   = new DateTime($resultSucu[0]['limiteEntrega']);
            $listaDistr   = $resultSucu[0]['listaDistribucion'];
            $gmt_estandar = $resultSucu[0]['gmt_estandar'];
            $isVerano     = $resultSucu[0]['isVerano'];
            $gmt_verano   = $resultSucu[0]['gmt_verano'];
            $horaApertura = $horaApertura->format('H:i:s');
            $horaCierre   = $horaCierre->format('H:i:s');
            $horaLimite   = $horaLimite->format('H:i:s');
            $flagSitio    = true;
          }

          if ($modoEntrega['DeliveryModeCode'] == 'C&R' && $flagSitio){
            $emailOv = $modoEntrega['Email'];
            array_push($emailOv, $listaDistr);
            $telefonos = $modoEntrega['Telefonos'];
            /////////////////////////////////////calculo de horas//////////////////////////////
            $timezone = $gmt_estandar;
            if ($isVerano == 1){
              $timezone = $gmt_verano;
            }
            $semanaArr = array(1,2,3,4);
            $fecha2 = new DateTime( gmdate("Y-m-d H:i:s", time() + 3600*($timezone+date("I"))) );
            $fecha = new DateTime($fecha2->format('Y-m-d').' '.$horaLimite);
            $flagFechaMayor = ($fecha2 > $fecha) ?true:false;
            $interval = date_diff($fecha2, $fecha);
            $diffMinutes = $interval->format('%i');
            $diffHours = $interval->format('%H');
            $horaAperturaMail = new DateTime($fecha2->format('Y-m-d '.$horaApertura));
            $horaAperturaMail = $horaAperturaMail->add(new DateInterval('PT1H'));
            if ( $fecha2 < new DateTime($fecha2->format('Y-m-d '.$horaApertura)) )
            {
              $fechaMail = $horaAperturaMail->format('d/m/Y H:i');
              $fechaSMS = $horaAperturaMail->format('H:i').' del dia '.$horaAperturaMail->format('d/m/Y');
            }else{
              $fechaMail = $fecha2->add(new DateInterval('PT1H'))->format('d/m/Y H:i');
              $fechaSMS = $fecha2->format('H:i').' del dia '.$fecha2->format('d/m/Y');
            }

            if ( $flagFechaMayor ){
              $day = date('w');
              $fecha2 = new DateTime( gmdate("Y-m-d H:i:s", time() + 3600*($timezone+date("I"))) );
              if ( in_array($day, $semanaArr) ){
                $fechaMail = 'a partir de las ('.$horaAperturaMail->add(new DateInterval('P1D'))->format('d/m/Y H:i:s').')';
                $fechaSMS = $horaAperturaMail->format('H:i').' del dia '.$horaAperturaMail->format('d/m/Y');
              }else{
                switch ($day) {
                  case 0:
                    $fechaMail = 'a partir de las ('.$horaAperturaMail->add(new DateInterval('P1D'))->format('d/m/Y H:i:s').')';
                    $fechaSMS = $horaAperturaMail->format('H:i');
                  break;
                  case 5:
                    $fechaMail = 'a partir de las ('.$horaAperturaMail->add(new DateInterval('P3D'))->format('d/m/Y H:i:s').')';
                    $fechaSMS = $horaAperturaMail->format('H:i').' del siguiente Lunes';
                  break;
                  case 6:
                    $fechaMail = 'a partir de las ('.$horaAperturaMail->add(new DateInterval('P2D'))->format('d/m/Y H:i:s').')';
                    $fechaSMS = $horaAperturaMail->format('H:i');
                  break;
                }
              }
            }
            ///////////////////////////////////////////////////////////////////////////////////
            $msgPickUp = '';
            if ($sitio == 'CHIH'){
              $msgPickUp = 'Nuestros espacios exclusivos de <b>PICK-UP</b> o directamente a ';
            }
            $mensaje = '
                        <table class="table" style="border-collapse: collapse;width:100%;">
                          <tbody>
                            <tr>
                              <td style="width:20%; text-align:left; background-color:white;"><img src="../application/assets/img/logo_wpay3.png" /></td>
                              <td style="width:80%;text-align:right;background-color:white;"><span style="font-size: 25px; font-family: Arial;">Número de Orden de Venta:</span><br><span style="font-size: 25px; font-family: Arial;"><b>'.$ov.'</b></span></td>
                            </tr>
                            <tr>
                              <td colspan="2" style="background-color:#006699; color:white; width:100%;">
                                <br>
                                <br>
                                <table class="table" style="width:100%;">
                                  <tbody>
                                    <tr>
                                      <td style="width:25%;">&nbsp;</td> 
                                      <td style="text-align:center; font-size: 25px; font-family: Arial; width:50%; color:white"><b>!Gracias por Comprar en AVANCE! </b><br><br></td>
                                      <td style="width:25%;">&nbsp;</td>
                                    </tr>
                                    <tr>
                                      <td style="width:25%;">&nbsp;</td>
                                      <td style="text-align:center; font-size: 17px; font-family: Arial; color: white;">
                                        <span>Ya estamos trabajando en su orden </span><br><br>
                                        <span>Puede recoger su pedido en: </span><br><br>
                                        <span>'.$msgPickUp.'ventanilla!.</span><br/><br>
                                        <span>A partir de la siguiente fecha y horario:</span><br><br>
                                        <span><b>'.$fechaMail.' hrs.</b></span><br><br>
                                        </td>
                                        <td style="width:25%;">&nbsp;</td>
                                    </tr>
                                    <tr>
                                      <td colspan="3" style="text-align:center; font-size: 17px; font-family: Arial; color:white"><b>Avance agradece su preferencia.</b><br><br></td>
                                    </tr>
                                  </tbody>
                                </table>
                              </td>
                            </tr>
                            <tr>
                              <td colspan="2" style="text-align:center;background-color:#AEB6BF;color:white;">www.avanceytec.com.mx</td>
                            </tr>
                          </tbody>
                        </table>';

            $mensajeSMS = "Gracias por Comprar en AVANCE!!! \\n\\n Estamos trabajando en su orden $ov, puede recoger su pedido en nuestros espacios exclusivo de PICK UP o directamente en ventanilla a partir de las ".$fechaSMS."!!!.\\n\\n https://avanceytec.com.mx/";
            $enviadoMail = Application_Model_InicioModel::sendMailWPAY($emailOv,'Notificaciones WPAY (inAX)',$mensaje);
            $sms = InicioController::sendSMS($mensajeSMS,$telefonos);
          }
          ///////////////////////////////////////////////////////////////////////////////////////////////
          Application_Model_InicioModel::$log->kardexLog("Liberacion a almacen ov: ".$ov." resultado: ".  json_encode(array('status' => 'exito','msg' => $response->response)),$ov,json_encode(array('status' => 'exito','msg' => $response->response)),1,'Liberacion a almacen');
        }else{
          $res = array('status' => 'fail','msg' => $response->response);
          Application_Model_InicioModel::$log->kardexLog("Liberacion a almacen ov: ".$ov." resultado: ".  json_encode(array('status' => 'fail','msg' => $ov,'error' => $result.'-'.json_encode($response))),$ov,json_encode(array('status' => 'fail','msg' => $ov,'error' => $result.'-'.json_encode($response))),0,'Liberacion a almacen');
        }
      }
      return json_encode($res);
    }

    public static function getDeliveryModeCode($ov){
      $token = new Token();
      ///////////////////////////////////consultar el delivery mode code////////////////////////////////////////////////////////////////////////////
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesOrderHeadersV2?%24filter=SalesOrderNumber%20eq%20'".$ov."'&%24select=DeliveryModeCode,OrderingCustomerAccountNumber",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_COOKIE => "ApplicationGatewayAffinity=e7fb295f94cb4b5e0cd1e2a4712e4a803fc926342cc4ecca988f29125dbd4b04",
        CURLOPT_HTTPHEADER => array(
          "authorization: Bearer ".$token->getToken()[0]->Token,
          "content-type: application/json"
        ),
      ));

      $response = curl_exec($curl);
      $err = curl_error($curl);
      curl_close($curl);

      if ($err) {
        $error = "cURL Error #:" . $err;
        return $error;
      } else {
        $response = json_decode($response);
      }
      /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
      /////////////////////////////////////////////consulta del correo y telefono//////////////////////////////////////////////////////////////////////////
      $curl = curl_init();
      curl_setopt_array($curl, [
        CURLOPT_URL => "https://".DYNAMICS365."/Data/CustomersV3?%24filter=CustomerAccount%20eq%20'".$response->value[0]->OrderingCustomerAccountNumber."'&%24select=PrimaryContactEmail%2CPrimaryContactPhone",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_COOKIE => "ApplicationGatewayAffinity=e7fb295f94cb4b5e0cd1e2a4712e4a803fc926342cc4ecca988f29125dbd4b04",
        CURLOPT_HTTPHEADER => [
          "accept: application/json",
          "authorization: Bearer ".$token->getToken()[0]->Token,
          "content-type: application/json"
        ],
      ]);

      $responseCustomer = curl_exec($curl);
      $err = curl_error($curl);
      curl_close($curl);

      if ($err) {
        $error = "cURL Error #:" . $err;
        return $error;
      } else {
        $responseCustomer = json_decode($responseCustomer);
      }

      $correos = explode(';',$responseCustomer->value[0]->PrimaryContactEmail);
      //$correos = [];
      // array_push($correos, 'sistemas9@avanceytec.com.mx');
      // array_push($correos, 'sistemas12@avanceytec.com.mx');
      // array_push($correos, 'PickUp_Traking@avanceytec.com.mx');
      // array_push($correos, 'coordinador.tic@avanceytec.com.mx');
      //$correos = array($mails);

      $telefonos = array($responseCustomer->value[0]->PrimaryContactEmail);
      //$telefonos = array('6143982184','6142246940');
      return array('DeliveryModeCode'=>$response->value[0]->DeliveryModeCode,'Email'=>$correos, 'Telefonos' => $telefonos);
    }

    /**
     * @param String $param Recibe un String con la OV a convertir 
     * @return String Regresa el id de la remision generada
     */
    public function setNewRemision($param,$dimensions) {
        /**
        * @param String $param Recibe un String con la OV a convertir 
        * @return String Regresa el id de la remision generada
        */
        $ws= new Metodos();
        $ov = $ws->SetRemision($param,$dimensions);
        return $ov;
    }
    
    public static function setHeader($params,$tipo){
        try {

            // $ws= new Metodos();
            // $ov = $ws->SetEncabezadoDynamics($params,$tipo);
            // return $ov;
          // print_r($params['RecIdDelivery']);exit();
          $token = new Token();
          Application_Model_InicioModel::$log = new Application_Model_Userinfo();
          $db = new Application_Model_UserinfoMapper();
          $adapter = $db->getAdapter();
          if ($params['PaymMode']=='02a'){//aqui estaba lo del cheque sin cargos como 02a
          $params['PaymMode']='02';
          }
          $tax = 'VTAS';
          if(strpos($params['LocationId'], 'MEXL')===0||strpos($params['LocationId'], 'TJNA')===0||strpos($params['LocationId'], 'JURZ')===0){
           ///////////////////////////checar el codigo postal del cliente en la entity/////////////////////////////////////
            curl_setopt_array(CURL1, array(
              CURLOPT_URL => "https://".DYNAMICS365."/data/CustomersV3?%24filter=CustomerAccount%20eq%20'".$params['claveclte']."'&%24select=AddressZipCode",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "GET",
              CURLOPT_POSTFIELDS => "",
              CURLOPT_COOKIE => "ApplicationGatewayAffinity=e7fb295f94cb4b5e0cd1e2a4712e4a803fc926342cc4ecca988f29125dbd4b04",
              CURLOPT_HTTPHEADER => array(
                "accept: application/json",
                "authorization: Bearer ".$token->getToken()[0]->Token."",
                "content-type: application/json",
                "x-total-count: application/json"
              ),
            ));

            $response = curl_exec(CURL1);
            $err = curl_error(CURL1);

              if ($err) {
                echo "cURL Error #:" . $err;
              } else {
                $response = json_decode($response);
                //////////////////checar si el codigo postal es valido para el 1.08 de IVA/////////////////////////////
                $zipCodeValido = false;
                if(!empty($response->value)){
                  $zipCliente = $response->value[0]->AddressZipCode;
                  $querySTR = "EXECUTE AYT_CodigoPostalFronterizo '".$zipCliente."';";
                  (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
                  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                  $query = $conn->prepare($querySTR);
                  $query->execute();
                  $result = $query->fetchAll(PDO::FETCH_ASSOC);
                  if ($result[0]['Cuantas'] > 0){
                    $zipCodeValido = true;
                  }
                }
                //////////////////////////////////////////////////////////////////////////////////////////////////////
              }
            ///////////////////////////////////////////////////////////////////////////////////////////////////
            if ($zipCodeValido){
              $tax = 'FRONT';
            }
          }
          // print_r($tax);exit();
          $docId = json_decode($params['documentId']);
          if ($docId == ''){
            $sucursales = array(
                 'CHIH' =>   "UN_00001",
                 'AGSC' =>   "UN_00011",
                 'HERM' =>   "UN_00002",
                 'OBRG' =>   "UN_00010",
                 'TJNA' =>   "UN_00014",
                 'CULN' =>   "UN_00003",
                 'JURZ' =>   "UN_00007",
                 'MEXL' =>   "UN_00006",
                 'MTRY' =>   "UN_00015",
                 'DURN' =>   "UN_00008",
                 'GDLJ' =>   "UN_00018",
                 'ZACS' =>   "UN_00020",
                 'TORN' =>   "UN_00004",
                 'EDMX' =>   "UN_00019",
                 'SALT' =>   "UN_00005",
                 'VCRZ' =>   "UN_00017",
                 'SLPS' =>   "UN_00013",
                 'QRTO' =>   "UN_00012",
                 'LEON' =>   "UN_00009",
                 'PBLA' =>   "UN_00016",
                 'TXLA' =>   "UN_00021",
                 'VEQP' =>   "UN_00999", 
                  );
            if(!$_SESSION['offline']){
              curl_setopt_array(CURL1, array(
                CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesQuotationHeaders",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "{ 'CurrencyCode': '".$params['CurrencyCode']."'
                                        ,'LanguageId':'es-MX'
                                        ,'dataAreaId': '".COMPANY."'
                                        ,'DefaultShippingSiteId': '".$params['SiteId']."'
                                        ,'RequestingCustomerAccountNumber':'".$params['claveclte']."'
                                        ,'QuotationTakerPersonnelNumber' : '".$params['WorkerTaker']."'
                                        ,'QuotationResponsiblePersonnelNumber' : '".$params['WorkerResponsible']."'
                                        ,'SalesOrderOriginCode' : '".$params['origenVenta']."'
                                        ,'SalesTaxGroupCode': '".$tax."'
                                        ,'DeliveryModeCode' : '".$params['DeliveryMode']."'
                                        ,'CustomersReference' : '".$params['comentariosCabecera']."'
                                        ,'CustomerPaymentMethodName' : '".$params['PaymMode']."'
                                        ,'PaymentTermsName' :'".$params['Payment']."'
                                      }",
                CURLOPT_HTTPHEADER => array(
                  "authorization: Bearer ".$token->getToken()[0]->Token."",
                  "content-type: application/json"
                ),
              ));

              $response = curl_exec(CURL1);
              $err = curl_error(CURL1);
              $response2=json_decode($response);

              curl_setopt_array(CURL1, array(
                CURLOPT_URL => "https://".DYNAMICS365."/api/services/STF_INAX/STF_Cotizacion/SetHeaderDefaultDimension",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "{\n    \"_dimensionName\": \"BusinessUnits\",\n    \"_dimensionValue\": \"".$sucursales[$response2->DefaultShippingSiteId]."\",\n    \"_quotationId\": \"".$response2->SalesQuotationNumber."\",\n    \"_dataAreaId\": \"ATP\"\n  }",
                 CURLOPT_HTTPHEADER => array(
                "authorization: Bearer ".$token->getToken()[0]->Token."",
                 "content-type: application/json"
                ),
              ));
              $response55 = curl_exec(CURL1);
              $err = curl_error(CURL1);
            }else{
              $querySTR = "INSERT INTO CotizacionesHeadersOffline (CurrencyCode,LanguageId,dataAreaId,DefaultShippingSiteId,RequestingCustomerAccountNumber,QuotationTakerPersonnelNumber,QuotationResponsiblePersonnelNumber,SalesOrderOriginCode,SalesTaxGroupCode,DeliveryModeCode,CustomersReference,CustomerPaymentMethodName,BusinessUnits,SalesQuotationNumber,CreatedDatetime,PaymentTermsName,SalesQuotationName,FormattedDeliveryAddress)
                          VALUES ('".$params['CurrencyCode']."','es-MX','".COMPANY."','".$params['SiteId']."','".$params['claveclte']."','".$params['WorkerTaker']."','".$params['WorkerResponsible']."',
                                  '".$params['origenVenta']."','".$tax."','".$params['DeliveryMode']."','".$params['comentariosCabecera']."','".$params['PaymMode']."','".$sucursales[trim($params['SiteId'])]."',dbo.getNextSalesQuotationNumber(),GETDATE(),'".$params['Payment']."','".$params['cliente']."','".$params['direccion']."')";
              $query = $adapter->prepare($querySTR);
              $query->execute();
              $result = $query->rowCount();
              $stmt = $adapter->query("SELECT * FROM CotizacionesHeadersOffline WHERE idCotizacionOff = @@IDENTITY");
              $lastInsert = $stmt->fetchAll();
              if ($result > 0){
                $response = json_encode($lastInsert[0]);
              }
            }
            // print_r($response);exit();

            //   curl_setopt_array(CURL1, array(
            //     CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesQuotationHeaders(dataAreaId=%27".COMPANY."%27,SalesQuotationNumber=%27".$response2->SalesQuotationNumber."%27)",
            //     CURLOPT_RETURNTRANSFER => true,
            //     CURLOPT_ENCODING => "",
            //     CURLOPT_MAXREDIRS => 10,
            //     CURLOPT_TIMEOUT => 30,
            //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //     CURLOPT_CUSTOMREQUEST => "PATCH",
            //     CURLOPT_POSTFIELDS => "{ 
            //                             'DefaultShippingSiteId': '".$params['SiteId']."'
            //                           }",
            //     CURLOPT_HTTPHEADER => array(
            //       "authorization: Bearer ".$token->getToken()[0]->Token."",
            //       "content-type: application/json"
            //     ),
            //   ));
            //   $response2 = curl_exec(CURL1);
            //   $err = curl_error(CURL1);

            // curl_close(CURL1);

            if ($err) {
              print_r($response);
              exit();
              $result = "cURL Error #:" . $err;
            } else {
              if (!isset($result->error)){
                $result = json_decode($response);
                $paramsEnviados = '{ "CurrencyCode": "'.$params['CurrencyCode'].'","LanguageId":"es-MX","dataAreaId": "'.COMPANY.'","DefaultShippingSiteId": "'.$params['SiteId'].'","RequestingCustomerAccountNumber":"'.$params['claveclte'].'","QuotationTakerPersonnelNumber" : "'.$params['WorkerResponsible'].'","QuotationResponsiblePersonnelNumber" : "'.$params['WorkerTaker'].'","SalesOrderOriginCode" : "'.$params['origenVenta'].'","SalesTaxGroupCode":"'.$tax.'","DeliveryModeCode" : "'.$params['DeliveryMode'].'","CustomersReference" : "'.$params['comentariosCabecera'].'","CustomerPaymentMethodName" : "'.$params['PaymMode'].'","DeliveryTermsCode":"'.$params['DeliveryTerm'].'"}';
                Application_Model_InicioModel::$log->kardexLog("Creacion Cotizacion: ".$result->SalesQuotationNumber." resultado: ".  $result->SalesQuotationNumber,$paramsEnviados,$result->SalesQuotationNumber,1,'Creacion Cotizacion');
              }
            }
          }else{
            $tipoPago='PUE';
            if($params['PaymMode']=="99"){
              $tipoPago='PPD';
            }else{
              $tipoPago='PUE';
            }
            if ($tipo == 'CTZN'){
              curl_setopt_array(CURL1, array(
                    CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesQuotationHeaders(dataAreaId=%27".COMPANY."%27,SalesQuotationNumber=%27".$docId."%27)",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_POSTFIELDS => "",
                    CURLOPT_HTTPHEADER => array(
                      "accept: application/json",
                      "authorization: Bearer ".$token->getToken()[0]->Token."",
                      "content-type: application/json"
                    ),
                  ));
  
                  $responseAddress = curl_exec(CURL1);
                  $err = curl_error(CURL1);
                  $responseAddress = json_decode($responseAddress);
            }else{
              curl_setopt_array(CURL1, array(
                    CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesOrderHeadersV2(dataAreaId=%27".COMPANY."%27,SalesOrderNumber=%27".$docId."%27)",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_POSTFIELDS => "",
                    CURLOPT_HTTPHEADER => array(
                      "accept: application/json",
                      "authorization: Bearer ".$token->getToken()[0]->Token."",
                      "content-type: application/json"
                    ),
                  ));
  
                  $responseAddress = curl_exec(CURL1);
                  $err = curl_error(CURL1);
                  $responseAddress = json_decode($responseAddress);
                  //print_r($responseAddress);
                  
            }
            $frontCities = ['MEXL','TJNA','JURZ'];
            $tax = 'VTAS';
            if ( in_array($params['SiteId'],$frontCities) ){
              ///////////////////////////checar el codigo postal del cliente en la entity/////////////////////////////////////
              curl_setopt_array(CURL1, array(
                CURLOPT_URL => "https://".DYNAMICS365."/data/CustomersV3?%24filter=CustomerAccount%20eq%20'".$params['claveclte']."'&%24select=AddressZipCode",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_POSTFIELDS => "",
                CURLOPT_COOKIE => "ApplicationGatewayAffinity=e7fb295f94cb4b5e0cd1e2a4712e4a803fc926342cc4ecca988f29125dbd4b04",
                CURLOPT_HTTPHEADER => array(
                  "accept: application/json",
                  "authorization: Bearer ".$token->getToken()[0]->Token."",
                  "content-type: application/json",
                  "x-total-count: application/json"
                ),
              ));

              $response = curl_exec(CURL1);
              $err = curl_error(CURL1);

                if ($err) {
                  echo "cURL Error #:" . $err;
                } else {
                  $response = json_decode($response);
                  //////////////////checar si el codigo postal es valido para el 1.08 de IVA/////////////////////////////
                  $zipCodeValido = false;
                  if(!empty($response->value)){
                    $zipCliente = $response->value[0]->AddressZipCode;
                    $querySTR = "EXECUTE AYT_CodigoPostalFronterizo '".$zipCliente."';";
                    (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $query = $conn->prepare($querySTR);
                    $query->execute();
                    $result = $query->fetchAll(PDO::FETCH_ASSOC);
                    if ($result[0]['Cuantas'] > 0){
                      $zipCodeValido = true;
                    }
                  }
                  //////////////////////////////////////////////////////////////////////////////////////////////////////
                }
              ///////////////////////////////////////////////////////////////////////////////////////////////////
              if ($zipCodeValido){
                $tax = 'FRONT';
              }
            }
            $CURLOPT_URL = "https://".DYNAMICS365."/Data/SalesQuotationHeaders(dataAreaId=%27".COMPANY."%27,SalesQuotationNumber=%27".$docId."%27)";
            $CURLOPT_POSTFIELDS = "{ 'CurrencyCode': '".$params['CurrencyCode']."'
                                      ,'LanguageId':'es-MX'
                                      ,'RequestingCustomerAccountNumber':'".$params['claveclte']."'
                                      ,'SalesOrderOriginCode' : '".$params['origenVenta']."'
                                      ,'DeliveryModeCode' : '".$params['DeliveryMode']."'
                                      ,'CustomerPaymentMethodName' : '".$params['PaymMode']."'
                                      ,'SalesTaxGroupCode' : '".$tax."'
                                      ,'CustomersReference' : '".$params['comentariosCabecera']."'
                                      ,'DefaultShippingSiteId' : '".$params['SiteId']."'
                                      ,'PaymentTermsName' :'".$params['Payment']."'
                                      ,'DeliveryAddressName' : '".$responseAddress->DeliveryAddressName."'
                                      ,'DeliveryAddressStreetNumber' : '".$responseAddress->DeliveryAddressStreetNumber."'
                                      ,'DeliveryAddressCity' : '".$responseAddress->DeliveryAddressCity."'
                                      ,'DeliveryAddressDescription' : '".$responseAddress->DeliveryAddressDescription."'
                                      ,'DeliveryAddressZipCode' : '".$responseAddress->DeliveryAddressZipCode."'
                                      ,'DeliveryAddressCountryRegionId' : '".$responseAddress->DeliveryAddressCountryRegionId."'
                                      ,'DeliveryAddressStreet' : '".$responseAddress->DeliveryAddressStreet."'
                                      ,'DeliveryAddressLatitude': ".$responseAddress->DeliveryAddressLatitude."
                                      ,'DeliveryAddressDistrictName': '".$responseAddress->DeliveryAddressLatitude."'
                                      ,'DeliveryAddressCountyId': '".$responseAddress->DeliveryAddressCountyId."'
                                      ,'DeliveryAddressDunsNumber': '".$responseAddress->DeliveryAddressDunsNumber."'
                                      ,'IsDeliveryAddressPrivate': '".$responseAddress->IsDeliveryAddressPrivate."'
                                      ,'DeliveryAddressCountryRegionISOCode': '".$responseAddress->DeliveryAddressCountryRegionISOCode."'
                                      ,'DeliveryAddressLocationId': '".$responseAddress->DeliveryAddressLocationId."'
                                      ,'IsDeliveryAddressOrderSpecific': '".$responseAddress->IsDeliveryAddressOrderSpecific."'
                                      ,'DeliveryAddressLongitude': ".$responseAddress->DeliveryAddressLongitude."
                                      ,'DeliveryTermsCode': '".$responseAddress->DeliveryTermsCode."'
                                      ,'DeliveryAddressTimeZone': '".$responseAddress->DeliveryAddressTimeZone."'
                                      ,'DeliveryAddressStateId': '".$responseAddress->DeliveryAddressStateId."'
                                      ,'DeliveryBuildingCompliment': '".$responseAddress->DeliveryBuildingCompliment."'
                                      ,'DeliveryAddressPostBox': '".$responseAddress->DeliveryAddressPostBox."'
                                      ,'DeliveryReasonCode': '".$responseAddress->DeliveryReasonCode."'                                      
                                    }";
            if ($tipo != 'CTZN'){
              $CURLOPT_URL = "https://".DYNAMICS365."/Data/SalesOrderHeadersV2(dataAreaId=%27".COMPANY."%27,SalesOrderNumber=%27".$docId."%27)";
              $CURLOPT_POSTFIELDS = "{ 'CurrencyCode': '".$params['CurrencyCode']."'
                                      ,'LanguageId':'es-MX'
                                      ,'OrderingCustomerAccountNumber':'".$params['claveclte']."'
                                      ,'SalesOrderOriginCode' : '".$params['origenVenta']."'
                                      ,'DeliveryModeCode' : '".$params['DeliveryMode']."'
                                      ,'CustomerPaymentMethodName': '".$params['PaymMode']."'
                                      ,'SATPaymMethod_MX': '".$tipoPago."'
                                      ,'CustomersOrderReference' : '".$params['comentariosCabecera']."'
                                      ,'OrderTakerPersonnelNumber' : '".$params['WorkerTaker']."'
                                      ,'OrderResponsiblePersonnelNumber' : '".$params['WorkerResponsible']."'
                                      ,'DefaultShippingSiteId' : '".$params['SiteId']."'
                                      ,'SalesTaxGroupCode' : '".$tax."'
                                      ,'PaymentTermsName' :'".$params['Payment']."'
                                      ,'DeliveryAddressName' : '".$responseAddress->DeliveryAddressName."'
                                      ,'DeliveryAddressStreetNumber' : '".$responseAddress->DeliveryAddressStreetNumber."'
                                      ,'DeliveryAddressCity' : '".$responseAddress->DeliveryAddressCity."'
                                      ,'DeliveryAddressDescription' : '".$responseAddress->DeliveryAddressDescription."'
                                      ,'DeliveryAddressZipCode' : '".$responseAddress->DeliveryAddressZipCode."'
                                      ,'DeliveryAddressCountryRegionId' : '".$responseAddress->DeliveryAddressCountryRegionId."'
                                      ,'DeliveryAddressStreet' : '".$responseAddress->DeliveryAddressStreet."'
                                      ,'DeliveryAddressLatitude': ".(($responseAddress->DeliveryAddressLatitude)? $responseAddress->DeliveryAddressLatitude : 0)."
                                      ,'DeliveryAddressDistrictName': '".$responseAddress->DeliveryAddressLatitude."'
                                      ,'DeliveryAddressCountyId': '".$responseAddress->DeliveryAddressCountyId."'
                                      ,'DeliveryAddressDunsNumber': '".$responseAddress->DeliveryAddressDunsNumber."'
                                      ,'IsDeliveryAddressPrivate': '".$responseAddress->IsDeliveryAddressPrivate."'
                                      ,'DeliveryAddressCountryRegionISOCode': '".$responseAddress->DeliveryAddressCountryRegionISOCode."'
                                      ,'DeliveryAddressLocationId': '".$responseAddress->DeliveryAddressLocationId."'
                                      ,'IsDeliveryAddressOrderSpecific': '".$responseAddress->IsDeliveryAddressOrderSpecific."'
                                      ,'DeliveryAddressLongitude': ".(($responseAddress->DeliveryAddressLongitude)? $responseAddress->DeliveryAddressLongitude : 0)."
                                      ,'DeliveryTermsCode': '".$responseAddress->DeliveryTermsCode."'
                                      ,'DeliveryAddressTimeZone': '".$responseAddress->DeliveryAddressTimeZone."'
                                      ,'DeliveryAddressStateId': '".$responseAddress->DeliveryAddressStateId."'
                                      ,'DeliveryBuildingCompliment': '".$responseAddress->DeliveryBuildingCompliment."'
                                      ,'DeliveryAddressPostBox': '".$responseAddress->DeliveryAddressPostBox."'
                                      ,'DeliveryReasonCode': '".$responseAddress->DeliveryReasonCode."'
                                    }";
            }
            if( $params['CurrencyCode'] != '' && $params['claveclte'] != '' && $params['origenVenta'] != '' && $params['DeliveryMode'] != '' && $params['PaymMode'] != '' && $tipoPago != '' && $params['WorkerTaker'] != '' && $params['WorkerResponsible'] != '' && $params['SiteId'] != '' ){
              if (!$_SESSION['offline']){
                curl_setopt_array(CURL1, array(
                  CURLOPT_URL => $CURLOPT_URL,
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => "",
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 30,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => "PATCH",
                  CURLOPT_POSTFIELDS => $CURLOPT_POSTFIELDS,
                  CURLOPT_HTTPHEADER => array(
                    "accept: application/json",
                    "authorization: Bearer ".$token->getToken()[0]->Token."",
                    "content-type: application/json"
                  ),
                ));

                $response = curl_exec(CURL1);
                $err = curl_error(CURL1);
                // print_r($response);exit('ppp');
                //print_r($CURLOPT_POSTFIELDS);
                //print_r($responseAddress);
                //die();
              }else{
                $query = $adapter->prepare("UPDATE CotizacionesHeadersOffline 
                                          SET CurrencyCode = '".$params['CurrencyCode']."',
                                          LanguageId = 'es-MX',
                                          RequestingCustomerAccountNumber = '".$params['claveclte']."',
                                          SalesOrderOriginCode = '".$params['origenVenta']."',
                                          DeliveryModeCode = '".$params['DeliveryMode']."',
                                          CustomerPaymentMethodName = '".$params['PaymMode']."',
                                          CustomersReference = '".$params['comentariosCabecera']."',
                                          DefaultShippingSiteId = '".$params['SiteId']."' 
                                          WHERE SalesQuotationNumber = '".$docId."';");
                $query->execute();
                $result = $query->rowCount();
                if ($result > 0){
                  $response = '';
                }
              }
            }else{
              $model= new Application_Model_InicioModel();
              $user=array('1'=>'sistemas9@avanceytec.com.mx','2'=>'sistemas6@avanceytec.com.mx','3'=>'sistemas11@avanceytec.com.mx');//"sistemas6@avanceytec.com.mx;sistemas@avanceytec.com.mx;telemarketing14@avanceytec.com.mx";
              $asunto='InaxLog setHeader';
              $model->sendMail($user, $asunto, 'Fallo en setHeader datos vacios docid:'.$docId.'-'.$CURLOPT_POSTFIELDS);
              $result = 'Fallo';
            }

           // curl_close(CURL1);

            if ($err) {
              $result = "cURL Error #:" . $err;
            } else {
              if ($response == ''){
                $result = (object) array('SalesQuotationNumber' => $docId);
                if ($tipo == 'CTZN'){
                  $paramsEnviados = '{ "CurrencyCode": "'.$params['CurrencyCode'].'","LanguageId":"es-MX","dataAreaId": "'.COMPANY.'","DefaultShippingSiteId": "'.$params['SiteId'].'","RequestingCustomerAccountNumber":"'.$params['claveclte'].'","QuotationTakerPersonnelNumber" : "'.$params['WorkerResponsible'].'","QuotationResponsiblePersonnelNumber" : "'.$params['WorkerTaker'].'","SalesOrderOriginCode" : "'.$params['origenVenta'].'","SalesTaxGroupCode":"'.$tax.'","DeliveryModeCode" : "'.$params['DeliveryMode'].'","CustomersReference" : "'.$params['comentariosCabecera'].'","CustomerPaymentMethodName" : "'.$params['PaymMode'].'"}';
                  Application_Model_InicioModel::$log->kardexLog("Edicion Cotizacion: ".$docId." resultado: ".  $docId,$paramsEnviados,$docId,1,'Edicion Cotizacion');
                }else{
                  $paramsEnviados = '{ "CurrencyCode": "'.$params['CurrencyCode'].'","LanguageId":"es-MX","OrderingCustomerAccountNumber":"'.$params['claveclte'].'","SalesOrderOriginCode" : "'.$params['origenVenta'].'","DeliveryModeCode" : "'.$params['DeliveryMode'].'","CustomerPaymentMethodName": "'.$params['PaymMode'].'","SATPaymMethod_MX": "'.$tipoPago.'","CustomersOrderReference" : "'.$params['comentariosCabecera'].'"}';
                  Application_Model_InicioModel::$log->kardexLog("Edicion OV: ".$docId." resultado: ".  $docId,$paramsEnviados,$docId,1,'Edicion Cotizacion');
                }
              }
            }
          }
          if($params['promoCodeEnable'] == 'true'){
                (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                // $resul = $conn->query("SELECT OrganizationName,CustomerAccount,LineDiscountCode,DiscountPriceGroupId FROM CustCustomerV3Staging");
                $resul = $conn->prepare("INSERT INTO AYT_PromoRedeemedLog (PromoCode,Ov,ClientCode,ClientName) VALUES('".$params['promoCodeName']."','".$result->SalesQuotationNumber."','".$params['claveclte']."','".$params['cliente']."')");
                $resul->execute();
          }
          return $result;
        } catch (Exception $objError) {
          throw new Exception($objError);
        }
    }

    public function setFactura($ov,$remision,$ordenCliente,$refCliente,$comentariosCabecera,$direccion,$usoCFDI,$modoPago,$pago){
        try {
          $token = new Token();
            $ws= new Metodos();            
            $lines=$this->getInvoiceLines($ov);
            $line="";
            $user=$this->getIdByNetWorkAlias($_SESSION['userInax']);
            foreach ($lines as $k => $v) {
                $line.='<Line  LineNum="'.$v['LINENUM'].'" SalesPrice="'.$v['SALESPRICE'].'" LinePercent="'.$v['LINEPERCENT'].'" TaxGroup="'.$v['TAXGROUP'].'" ></Line>';
            }        
            $xml=  '<?xml version="1.0" encoding="utf-8"?>
                    <SalesOrder>
                        <Table>
                            <Company>' . COMPANY . '</Company><SalesId>'.$ov.'</SalesId><PackingSlipId>'.$remision.'</PackingSlipId><PurchOrderFormNum>'.$ordenCliente.'</PurchOrderFormNum><CustomerRef>'.$refCliente.'</CustomerRef>
                            <Observations>'.$comentariosCabecera.'</Observations><Description>'.$direccion.'</Description><STF_CFDIuseCode>'.$usoCFDI.'</STF_CFDIuseCode><STF_RelType></STF_RelType><UUID></UUID><PaymMode>'.$modoPago.'</PaymMode><Payment>'.$pago.'</Payment>
                            <User>'.$user[0]['ID'].'</User>
                        </Table>
                        <Lines>'.$line.'</Lines>
                    </SalesOrder>';
            return $ws->createFactura($xml);
        } catch (Exception $objError) {
            return array("resultado"=>"bad","respuesta"=>$objError->getMessage());
        }   
    }

    public function postDocumentAttachment($comentario,$ov,$itemId){
      if ( (strrpos('OV-', $ov) >= 0) && $itemId == '9999-9000'){
        $token = new Token();
        $curl = curl_init();

        curl_setopt_array($curl, [
          CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesOrderHeaderDocumentAttachments",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => "{\"dataAreaId\": \"".COMPANY."\",\"FileType\": \"\",\"IsDefaultAttachment\": \"No\",\"SalesOrderNumber\": \"".$ov."\",\"AccessRestriction\": \"Internal\",\"AttachmentDescription\": \"Nota\",\"Notes\": \"".$comentario."\",\"DocumentAttachmentTypeCode\": \"Nota\",\"FileName\": \"\",\"Attachment\": null}",
          CURLOPT_COOKIE => "ApplicationGatewayAffinity=e7fb295f94cb4b5e0cd1e2a4712e4a803fc926342cc4ecca988f29125dbd4b04; ARRAffinity=dcb76979b784a1f85f17f2b977b7d851aad42c68a3aba6e28de4029069113332",
          CURLOPT_HTTPHEADER => [
          "Accept: application/json",
          "Authorization: Bearer ".$token->getToken()[0]->Token."",
          "Content-Type: application/json"
          ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
          return "cURL Error #:" . $err;
        } else {
          return true;
        }
      }
    }
    
    public function setLineas($params,$idDoc,$tipo,$promoCodeEnable,$removeDynamicsPrice = false) {
         try {
            // $ws= new Metodos();
            // $ov = $ws->SetLineasDynamics($params,$idDoc,$tipo); 
            // return $ov['result'];
            $resultSet = $this->setLineasEntity($params,$promoCodeEnable, $removeDynamicsPrice);
            $result = $resultSet['result'];
            $flagPatch = $resultSet['flagPatch'];
            if ($result == 'FalloCant'){
              return array('FAIL','Existen articulos sin existencias.!');
            }
            $id =$result->SalesQuotationNumber;
            if ($tipo != 'CTZN'){
              $id = $result->SalesOrderNumber;
            }
            //////////////////guarda comentarios////////////////////////////////////////////
            try{
              $db = new Application_Model_UserinfoMapper();
              $adapter = $db->getAdapter();
              $query = $adapter->prepare("INSERT INTO dbo.AYT_ComentariosLineas (inventoryLotId,comentario,tipoDoc,documentId,fechaCreacion,fechaModificacion,usuario,LineSequenceNumber) VALUES('".$result->InventoryLotId."','".$params[0]['comentariolinea']."','".$tipo."','".$id."',GETDATE(),NULL,'".$_SESSION['userInax']."','".$result->LineCreationSequenceNumber."');");
              if ($flagPatch){
                $queryExiste = $adapter->prepare("SELECT COUNT(*) AS cuantos FROM dbo.AYT_ComentariosLineas WHERE documentId = '".$id."' AND InventoryLotId = '".$result->InventoryLotId."';");
                $queryExiste->execute();
                $cuantos = $queryExiste->fetchAll(PDO::FETCH_ASSOC);
                if ($cuantos[0]['cuantos'] > 0){
                  $query = $adapter->prepare("UPDATE dbo.AYT_ComentariosLineas SET comentario = '".$params[0]['comentariolinea']."',usuario = '".$_SESSION['userInax']."', fechaModificacion = GETDATE(), LineSequenceNumber = '".$result->LineCreationSequenceNumber."' WHERE documentId = '".$id."' AND InventoryLotId = '".$result->InventoryLotId."';");
                }else{
                  $query = $adapter->prepare("INSERT INTO dbo.AYT_ComentariosLineas (inventoryLotId,comentario,tipoDoc,documentId,fechaCreacion,fechaModificacion,usuario,LineSequenceNumber) VALUES('".$result->InventoryLotId."','".$params[0]['comentariolinea']."','".$tipo."','".$id."',GETDATE(),NULL,'".$_SESSION['userInax']."','".$result->LineCreationSequenceNumber."');");
                  $this->postDocumentAttachment($params[0]['comentariolinea'],$id,$params[0]['item']);
                }
              }else{
                $this->postDocumentAttachment($params[0]['comentariolinea'],$id,$params[0]['item']);
              }
              $query->execute();
              $resultQuery = $query->rowCount();
            }catch(Exception $e){
              print_r($e->getMessage());
            }
            ///////////////////////////////////////////////////////////////////////////////
            return array('SalesQuotationNumber' => $id,'dataAreaId' => $result->dataAreaId,'InventoryLotId' => $result->InventoryLotId);
        } catch (Exception $objError) {
            $d=$objError;
            print_r($d);
            return 'FAIL';
        }       
    }
    public static function isService($item){
      $token = new Token();
      curl_setopt_array(CURL1, array(
                CURLOPT_URL => "https://".DYNAMICS365."/Data/ReleasedDistinctProducts?%24select=ProductType&%24filter=ItemNumber%20eq%20'".$item."'",
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

              $responsePriceProd = curl_exec(CURL1);
              $resultPriceProd = json_decode($responsePriceProd);
              if ($resultPriceProd->value[0]->ProductType == 'Service'){
                return 'true';
              }
                return 'false';
    }

    public static function setLineasEntity($params, $promoCodeEnable, $removeDynamicsPrice = false){
      $token = new Token();
      $flagPatch = true;
      $dataAreaId = $params[0]['_dataAreaId'];
      $InventoryLotId = $params[0]['_InventoryLotId'];
      $docType = $params[0]['_documentType'];
      if ( ($dataAreaId == 'nodefinido') && ($InventoryLotId == 'nodefinido') ){
        $flagPatch = false;
      }
      Application_Model_InicioModel::$log = new Application_Model_Userinfo();
      $db = new Application_Model_UserinfoMapper();
      $adapter = $db->getAdapter();
      //CURL1 = curl_init();
      $tax = 'VTAS';
      if(strpos($params[0]['almacen'], 'MEXL')===0||strpos($params[0]['almacen'], 'TJNA')===0||strpos($params[0]['almacen'], 'JURZ')===0){
        ///////////////////////////checar el codigo postal del cliente en la entity/////////////////////////////////////
        curl_setopt_array(CURL1, array(
          CURLOPT_URL => "https://".DYNAMICS365."/data/CustomersV3?%24filter=CustomerAccount%20eq%20'".$params[0]['cliente']."'&%24select=AddressZipCode",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_COOKIE => "ApplicationGatewayAffinity=e7fb295f94cb4b5e0cd1e2a4712e4a803fc926342cc4ecca988f29125dbd4b04",
          CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "authorization: Bearer ".$token->getToken()[0]->Token."",
            "content-type: application/json",
            "x-total-count: application/json"
          ),
        ));

        $response = curl_exec(CURL1);
        $err = curl_error(CURL1);

          if ($err) {
            echo "cURL Error #:" . $err;
          } else {
            $response = json_decode($response);
            //////////////////checar si el codigo postal es valido para el 1.08 de IVA/////////////////////////////
            $zipCodeValido = false;
            if(!empty($response->value)){
              $zipCliente = $response->value[0]->AddressZipCode;
              $querySTR = "EXECUTE AYT_CodigoPostalFronterizo '".$zipCliente."';";
              (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
              $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
              $query = $conn->prepare($querySTR);
              $query->execute();
              $result = $query->fetchAll(PDO::FETCH_ASSOC);
              if ($result[0]['Cuantas'] > 0){
                $zipCodeValido = true;
              }
            }
            //////////////////////////////////////////////////////////////////////////////////////////////////////
          }
        ///////////////////////////////////////////////////////////////////////////////////////////////////
        if ($zipCodeValido){
          $tax = 'FRONT';
        }
      }
      // print_r(strpos($params[0]['almacen'], 'TJNA'));
      // var_dump(strpos($params[0]['almacen'], 'TJNA'));
      // print_r($tax);exit();
      // curl_setopt_array(CURL1, array(
      //         CURLOPT_URL => "https://".DYNAMICS365."/Data/Customers?%24filer=CustomerAccount%2eq%20%27ATP-000020%27",
      //         CURLOPT_RETURNTRANSFER => true,
      //         CURLOPT_ENCODING => "",
      //         CURLOPT_MAXREDIRS => 10,
      //         CURLOPT_TIMEOUT => 30,
      //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      //         CURLOPT_CUSTOMREQUEST => "GET",
      //         CURLOPT_POSTFIELDS => "",
      //         CURLOPT_HTTPHEADER => array(
      //           "authorization: Bearer ".$token->getToken()[0]->Token."",
      //           "content-type: application/json"
      //         ),
      //       ));
      //  $response = curl_exec(CURL1);
      // $err = curl_error(CURL1);

      // $direccc=Application_Model_InicioModel::getClientesEntity('ATP-000020');

      // print_r($direccc);exit();
      $date = new DateTime();
      if (!$flagPatch){
        $salesPrice = '';
        $lineDisc   = '';
        if ($params[0]['punitariolinea'] > 0){
          $salesPrice = ",'SalesPrice' : ".$params[0]['punitariolinea'];
          $salesPriceEnv = '"SalesPrice" : '.$params[0]['punitariolinea'];
          if (strpos($params[0]['almacen'], 'MRMA') !== false || $promoCodeEnable){
            $lineDisc = ",'LineDiscountPercentage' : 0";
          }
        }
        $CURLOPT_URL = "https://".DYNAMICS365."/Data/SalesQuotationLines";
        $CURLOPT_POSTFIELDS = "{'SalesQuotationNumber' : '".$params[0]['documentId']."'
                                ,'dataAreaId': '".COMPANY."'
                                ,'ItemNumber' : '".$params[0]['item']."'
                                ,'RequestedSalesQuantity' : ".$params[0]['cantidad']."
                                ,'ShippingWarehouseId' : '".$params[0]['almacen']."'
                                ,'FixedPriceCharges': ".$params[0]['_cargoMoneda']."
                                ,'SalesTaxGroupCode': '".$tax."'
                                ,'STF_Category' : '".$params[0]['categoria']."'
                                ".$salesPrice."
                                ".$lineDisc."
                              }";
        if ($docType != 'CTZN'){
          $CURLOPT_URL = "https://".DYNAMICS365."/Data/SalesOrderLines";
          $CURLOPT_POSTFIELDS = "{'SalesOrderNumber' : '".$params[0]['documentId']."'
                                ,'dataAreaId': '".COMPANY."'
                                ,'ItemNumber' : '".$params[0]['item']."'
                                ,'OrderedSalesQuantity' : ".$params[0]['cantidad']."
                                ,'SalesTaxGroupCode': '".$tax."'
                                ,'FixedPriceCharges': ".$params[0]['_cargoMoneda']."
                                ,'ShippingWarehouseId' : '".$params[0]['almacen']."'
                                ".$salesPrice."
                                ".$lineDisc."
                              }";
        }
        curl_setopt_array(CURL1, array(
          CURLOPT_URL => $CURLOPT_URL,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $CURLOPT_POSTFIELDS,
          CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$token->getToken()[0]->Token."",
            "content-type: application/json"
          ),
        ));
      }else{
        ///////////////////cambiar la moneda en la cabecera///////////////////////////////////
        if (!$_SESSION['offline']){
          curl_setopt_array(CURL1, array(
            CURLOPT_URL => "https://".DYNAMICS365."/api/Services/STF_INAX/STF_ItemSalesPrice/getSalesPriceUnitPrice",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{'CustAccount' : '".$params[0]['cliente']."'
                                    ,'ItemId': '".$params[0]['item']."'
                                    ,'amountQty' : 1
                                    ,'transDate' : '".$date->format('m/d/Y')."'
                                    ,'currencyCode' : '".$params[0]['moneda']."' 
                                    ,'InventSiteId' : '".$params[0]['sitio']."'
                                    ,'InventLocationId' : '".$params[0]['almacen']."'
                                    ,'company' : '".COMPANY."'
                                    ,'PercentCharges' : 0
                                     }",
            CURLOPT_HTTPHEADER => array(
              "accept: application/json",
              "authorization: Bearer ".$token->getToken()[0]->Token."",
              "content-type: application/json"
            ),
          ));

          $responseUnitPrice = curl_exec(CURL1);
          $err = curl_error(CURL1);
        }else{
          try{
            ////////////////////consulta atributos de cliente lista de precios y descuento de linea/////////////////////
            $queryCliente         = $adapter->prepare("SELECT LineDiscountCode,DiscountPriceGroupId FROM dbo.CustCustomerV3Staging WHERE CustomerAccount = '".$params[0]['cliente']."';");
            $queryCliente->execute();
            $datacliente          = $queryCliente->fetchAll(PDO::FETCH_ASSOC);
            $LineDiscountCode     = $datacliente[0]['LineDiscountCode'];
            $DiscountPriceGroupId = $datacliente[0]['DiscountPriceGroupId'];
            ///////////////////////////////////////////////////////////////////////////////////////////////////////////
            //////////////////////////////////ejecuta el procedimiento de precios//////////////////////////////////////
            $queryPrecio       = $adapter->prepare("EXECUTE dbo.getPriceData '".$DiscountPriceGroupId."','".$LineDiscountCode."','".$params[0]['item']."',1;");
            $queryPrecio->execute();
            $dataPrecio        = $queryPrecio->fetchAll(PDO::FETCH_ASSOC);
            $LineAmountOff     = $dataPrecio[0]['MontoNeto'];
            $PrecioAcuerdoOff  = $dataPrecio[0]['PrecioAcuerdo'];
            $responseUnitPrice = $PrecioAcuerdoOff;
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////
          }catch(Exception $e){
            print_r($e->getMessage());
          }
        }

        if ($err) {
          $resultPrecioUnit = "cURL Error #:" . $err;
        } else {
          $resultPrecioUnit = json_decode($responseUnitPrice);
        }
        ////////////////////////////////////////////////////////////////////////////////////////////        
        $precio = $resultPrecioUnit;
        if ($params[0]['punitariolinea'] > 0){
          $precio = $params[0]['punitariolinea'];
        }

        $CURLOPT_URL = "https://".DYNAMICS365."/Data/SalesQuotationLines(dataAreaId=%27".strtolower(COMPANY)."%27,InventoryLotId=%27".$InventoryLotId."%27)";
        $CURLOPT_POSTFIELDS = "{'RequestedSalesQuantity' : ".$params[0]['cantidad'].",'ShippingSiteId' : '".$params[0]['sitio']."','ShippingWarehouseId' : '".$params[0]['almacen']."','FixedPriceCharges': ".$params[0]['_cargoMoneda'].",'SalesTaxGroupCode': '".$tax."'}";
        if (!$_SESSION['offline']){
          curl_setopt_array(CURL1, array(
              CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesQuotationLines?%24filter=SalesQuotationNumber%20eq%20'".$params[0]['documentId']."'%20and%20ItemNumber%20eq%20'".$params[0]['item']."'&%24select=LineAmount,SalesPrice,RequestedSalesQuantity,LineDiscountPercentage,FixedPriceCharges,ShippingSiteId,ShippingWarehouseId,FixedPriceCharges",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "GET",
              CURLOPT_POSTFIELDS => "",
              CURLOPT_HTTPHEADER => array(
                "accept: application/json",
                "authorization: Bearer ".$token->getToken()[0]->Token."",
                "content-type: application/json"
              ),
            ));

          $responseLine = curl_exec(CURL1);
          $err = curl_error(CURL1);
        }else{
          $query = $adapter->query("SELECT * FROM CotizacionesLinesOffline WHERE SalesQuotationNumber = '".$params[0]['documentId']."' AND InventoryLotId = '".$InventoryLotId."';");
          $query->execute();
          $result = $query->fetchAll(PDO::FETCH_OBJ);
          $responseLine = [];
          $responseLine['value'] = $result;
          $responseLine = json_encode($responseLine);
        }
        
        if ($err) {
          $resultLineAmount = "cURL Error #:" . $err;
        } else {
          $resultLineAmount = json_decode($responseLine);
        }
        
        if (!empty($resultLineAmount->value)){
          // if (!$_SESSION['offline']){
            $precio = $resultLineAmount->value[0]->SalesPrice;
            if ($promoCodeEnable && $params[0]['punitariolinea'] > 0){
              $precio = $params[0]['punitariolinea'];
            }
          // }else{
          //   $precio = ($LineAmountOff*$params[0]['cantidad']);
          // }
        }

        if($removeDynamicsPrice){
          $precio = $params[0]['_SalesPrice'];
        }

        if ($params[0]['_SalesPrice'] > 0){
           $oldValues = array(
              // 'LineAmount'              => $resultLineAmount->value[0]->LineAmount,
              'RequestedSalesQuantity'  => $resultLineAmount->value[0]->RequestedSalesQuantity,
              'FixedPriceCharges'       => $resultLineAmount->value[0]->FixedPriceCharges,
              'ShippingSiteId'          => trim($resultLineAmount->value[0]->ShippingSiteId),
              'ShippingWarehouseId'     => trim($resultLineAmount->value[0]->ShippingWarehouseId)
            );
            $newValues = array(
              // 'LineAmount'              => $result,
              'RequestedSalesQuantity'  => $params[0]['cantidad'],
              'FixedPriceCharges'       => $params[0]['_cargoMoneda'],
              'ShippingSiteId'          => trim($params[0]['sitio']),
              'ShippingWarehouseId'     => trim($params[0]['almacen'])
            );
            $cambio = Metodos::validateChangeInLine($oldValues,$newValues);
            $LineDiscountPercentage = Metodos::getLineDiscountCode($params[0]['cliente'],$params[0]['item'],$params[0]['cantidad']);
            if (strpos($params[0]['almacen'], 'MRMA') !== false || $promoCodeEnable){
              $LineDiscountPercentage = 0;
            }
            
            $CURLOPT_POSTFIELDS = "{'RequestedSalesQuantity' : ".$params[0]['cantidad'].",'SalesPrice' : ".$precio.",'ShippingSiteId' : '".$params[0]['sitio']."','ShippingWarehouseId' : '".$params[0]['almacen']."','FixedPriceCharges': ".$params[0]['_cargoMoneda'].",'SalesTaxGroupCode': '".$tax."'}";
            if ($cambio){
              $CURLOPT_POSTFIELDS = "{'RequestedSalesQuantity' : ".$params[0]['cantidad'].",'SalesPrice' : ".$precio.",'ShippingSiteId' : '".$params[0]['sitio']."','ShippingWarehouseId' : '".$params[0]['almacen']."','FixedPriceCharges': ".$params[0]['_cargoMoneda'].",'SalesTaxGroupCode': '".$tax."','LineDiscountPercentage' : ".$LineDiscountPercentage."}";
            }
          if ($params[0]['punitariolinea'] > 0 && !$promoCodeEnable){
            $CURLOPT_POSTFIELDS = "{'RequestedSalesQuantity' : ".$params[0]['cantidad'].",'SalesPrice' : ".$params[0]['punitariolinea'].",'ShippingSiteId' : '".$params[0]['sitio']."','ShippingWarehouseId' : '".$params[0]['almacen']."','FixedPriceCharges': ".$params[0]['_cargoMoneda'].",'SalesTaxGroupCode': '".$tax."'}";
          }
        }

        if($docType != 'CTZN'){
          $CURLOPT_URL = "https://".DYNAMICS365."/Data/SalesOrderLines(dataAreaId=%27".strtolower(COMPANY)."%27,InventoryLotId=%27".$InventoryLotId."%27)";
          $CURLOPT_POSTFIELDS = "{'OrderedSalesQuantity' : ".$params[0]['cantidad'].",'FixedPriceCharges': ".$params[0]['_cargoMoneda'].",'ShippingSiteId' : '".$params[0]['sitio']."','ShippingWarehouseId' : '".$params[0]['almacen']."','SalesTaxGroupCode': '".$tax."'}";
          if (!$_SESSION['offline']){
            curl_setopt_array(CURL1, array(
                CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesOrderLines?%24filter=SalesOrderNumber%20eq%20'".$params[0]['documentId']."'%20and%20ItemNumber%20eq%20'".$params[0]['item']."'&%24select=LineAmount,SalesPrice,OrderedSalesQuantity,LineDiscountPercentage,FixedPriceCharges,ShippingSiteId,ShippingWarehouseId,FixedPriceCharges",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_POSTFIELDS => "",
                CURLOPT_HTTPHEADER => array(
                  "accept: application/json",
                  "authorization: Bearer ".$token->getToken()[0]->Token."",
                  "content-type: application/json"
                ),
              ));

            $responseLine = curl_exec(CURL1);
            $err = curl_error(CURL1);
          }else{
            $queryLines = "SELECT LineAmount , SalesPrice FROM SalesOrderLinesOffline WHERE SalesOrderNumber = '".$params[0]['documentId']."' AND inventoryLotId = '".$InventoryLotId."';";
            $query = $adapter->query($queryLines);
            $query->execute();
            $resulAmount = $query->fetchAll(PDO::FETCH_OBJ);
            $responseLine = [];
            $responseLine['value'] = $resulAmount;
            $responseLine = json_encode($responseLine);
          }

          if ($err) {
            $resultLineAmount = "cURL Error #:" . $err;
          } else {
            $resultLineAmount = json_decode($responseLine);
          }

          if (!empty($resultLineAmount->value)){
            $precio = $resultLineAmount->value[0]->SalesPrice;
            if ($promoCodeEnable && $params[0]['punitariolinea'] > 0){
              $precio = $params[0]['punitariolinea'];
            }
          }

          if($removeDynamicsPrice){
            $precio = $params[0]['_SalesPrice'];
          }

          if ($params[0]['_SalesPrice'] > 0){
            $oldValues = array(
              // 'LineAmount'              => $resultLineAmount->value[0]->LineAmount,
              'RequestedSalesQuantity'  => $resultLineAmount->value[0]->OrderedSalesQuantity,
              'FixedPriceCharges'       => $resultLineAmount->value[0]->FixedPriceCharges,
              'ShippingSiteId'          => trim($resultLineAmount->value[0]->ShippingSiteId),
              'ShippingWarehouseId'     => trim($resultLineAmount->value[0]->ShippingWarehouseId)
            );
            $newValues = array(
              // 'LineAmount'              => $result,
              'RequestedSalesQuantity'  => $params[0]['cantidad'],
              'FixedPriceCharges'       => $params[0]['_cargoMoneda'],
              'ShippingSiteId'          => trim($params[0]['sitio']),
              'ShippingWarehouseId'     => trim($params[0]['almacen'])
            );
            $cambio = Metodos::validateChangeInLine($oldValues,$newValues);
            $LineDiscountPercentage = Metodos::getLineDiscountCode($params[0]['cliente'],$params[0]['item'],$params[0]['cantidad']);
            if (strpos($params[0]['almacen'], 'MRMA') !== false  || $promoCodeEnable){
              $LineDiscountPercentage = 0;
            }

            $CURLOPT_POSTFIELDS = "{'OrderedSalesQuantity' : ".$params[0]['cantidad'].",'SalesPrice' : ".$precio.",'FixedPriceCharges': ".$params[0]['_cargoMoneda'].",'ShippingSiteId' : '".$params[0]['sitio']."','ShippingWarehouseId' : '".$params[0]['almacen']."','SalesTaxGroupCode': '".$tax."'}";
            if ($cambio){
              $CURLOPT_POSTFIELDS = "{'OrderedSalesQuantity' : ".$params[0]['cantidad'].",'SalesPrice' : ".$precio.",'FixedPriceCharges': ".$params[0]['_cargoMoneda'].",'ShippingSiteId' : '".$params[0]['sitio']."','ShippingWarehouseId' : '".$params[0]['almacen']."','SalesTaxGroupCode': '".$tax."','LineDiscountPercentage' : ".$LineDiscountPercentage."}";
            }
            if ($params[0]['punitariolinea'] > 0 && !$promoCodeEnable){
              $CURLOPT_POSTFIELDS = "{'OrderedSalesQuantity' : ".$params[0]['cantidad'].",'SalesPrice' : ".$params[0]['punitariolinea'].",'FixedPriceCharges': ".$params[0]['_cargoMoneda'].",'ShippingSiteId' : '".$params[0]['sitio']."','ShippingWarehouseId' : '".$params[0]['almacen']."','SalesTaxGroupCode': '".$tax."'}";
            }
          }
        }
        /////////////cheacr existencias//////////////////////////////////////////////////////
        $existencias = Application_Model_InicioModel::getExistenciasEntity($params[0]['item'],$params[0]['sitio'],$params[0]['almacen']);
        $existe = true;
        $cant = $params[0]['cantidad'];
        foreach($existencias['existencias'] AS $DataExis){
          if( $params[0]['sitio'] == $DataExis->InventorySiteId && $params[0]['almacen'] == $DataExis->InventoryWarehouseId){
            $existe = false;
            $cant = $DataExis->AvailableOnHandQuantity;
          }
        }
        ///////////////////////////////////////////////////////////////////////////////////////
        if ( $params[0]['cantidad'] != '' && $params[0]['punitariolinea'] != '' && $precio != '' && $params[0]['_cargoMoneda'] != '' ){          
          curl_setopt_array(CURL1, array(
            CURLOPT_URL => $CURLOPT_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PATCH",
            CURLOPT_POSTFIELDS => $CURLOPT_POSTFIELDS,
            CURLOPT_HTTPHEADER => array(
              "authorization: Bearer ".$token->getToken()[0]->Token."",
              "content-type: application/json",
              "prefer: return=representation",
            ),
          ));
          $diferencia = $params[0]['cantidad'] - $resultLineAmount->value[0]->OrderedSalesQuantity;
          if ( $cant < $diferencia && $docType != 'CTZN'){
          // if ( $cant < $params[0]['cantidad'] && $docType != 'CTZN'){
            $result = 'FalloCant';
            return array('result' => $result,'flagPatch' => $flagPatch);
          }
        }else{
          $model= new Application_Model_InicioModel();
          $user=array('1'=>'sistemas9@avanceytec.com.mx','2'=>'sistemas6@avanceytec.com.mx','3'=>'sistemas11@avanceytec.com.mx');//"sistemas6@avanceytec.com.mx;sistemas@avanceytec.com.mx;telemarketing14@avanceytec.com.mx";
          $asunto='InaxLog setLineasEntity';
          $model->sendMail($user, $asunto, 'Fallo en setLineasEntity entity datos vacios InventoryLotId: '.$InventoryLotId.'-'.$CURLOPT_POSTFIELDS);
          $result = 'Fallo';
        }
      }
      
      /****
      Este exec ejecuta los dos curl ya sea POST o PATCH para OV o CTZN
      ***/
      if (!$_SESSION['offline']){
        if ($result != 'fallo'){
          $response = curl_exec(CURL1);
          $err = curl_error(CURL1);
        }
        /////// actualizar lote license y loalidad////////////////////////////////////////
        if ($docType != 'CTZN'){
          if (!$flagPatch){ 
            $dataResponse = json_decode($response);
            $InventoryLotId = $dataResponse->InventoryLotId;
          }
          $lote       = $params[0]['lote'];
          $localidad  = $params[0]['localidad'];
          $matricula  = $params[0]['matricula'];
          $postFields = "{'inventoryLotId' : '".$InventoryLotId."','itemBatchNumber' : '".$lote."','wMSLocationId' : '".$localidad."','licensePlateId' : '".$matricula."','company': '".strtolower(COMPANY)."'}";
          $curl = curl_init();

          curl_setopt_array($curl, array(
            CURLOPT_URL => "https://".DYNAMICS365."/api/services/STF_INAX/STF_LineaVentaInventDim/setInventDim",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => array(
              "authorization: Bearer ".$token->getToken()[0]->Token."",
              "content-type: application/json"
            ),
          ));
          $responseDimensions = curl_exec($curl);
          $errDimensiones = curl_error($curl);

          curl_close($curl);

          if ($errDimensiones) {
            $resultDimensiones = "cURL Error #:" . $errDimensiones;
          } else {
            $resultDimensiones = json_decode($responseDimensions);
          }
        }
        //////////////////////////////////////////////////////////////////////////////////////////
      }else{
        ////////////////////////////////////solo para inert lineas///////////////////////
        try{
            ////////////////////consulta atributos de cliente lista de precios y descuento de linea/////////////////////
            $queryCliente         = $adapter->prepare("SELECT LineDiscountCode,DiscountPriceGroupId FROM dbo.CustCustomerV3Staging WHERE CustomerAccount = '".$params[0]['cliente']."';");
            $queryCliente->execute();
            $datacliente          = $queryCliente->fetchAll(PDO::FETCH_ASSOC);
            $LineDiscountCode     = $datacliente[0]['LineDiscountCode'];
            $DiscountPriceGroupId = $datacliente[0]['DiscountPriceGroupId'];
            ///////////////////////////////////////////////////////////////////////////////////////////////////////////
            //////////////////////////////////ejecuta el procedimiento de precios//////////////////////////////////////
            $queryPrecio2        = $adapter->prepare("EXECUTE dbo.getPriceData '".$DiscountPriceGroupId."','".$LineDiscountCode."','".$params[0]['item']."',1;");
            $queryPrecio2->execute();
            $dataPrecio2         = $queryPrecio2->fetchAll(PDO::FETCH_ASSOC);
            $LineAmountOff2      = $dataPrecio2[0]['MontoNeto'];
            $PrecioAcuerdoOff2   = $dataPrecio2[0]['PrecioAcuerdo'];
            $PorcentajDescuento2 = $dataPrecio2[0]['PorcentajeDescuento1'];
            $responseUnitPrice2  = $PrecioAcuerdoOff2;
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////
          }catch(Exception $e){
            print_r($e->getMessage());
          }
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //////////////////guarda lineas en bd///////////////////////////////////////////////////////////////////////////
        try{
          $precio = $PrecioAcuerdoOff2;
          if ($params[0]['punitariolinea'] > 0){
            $precio = $params[0]['punitariolinea'];
          }
          $db = new Application_Model_UserinfoMapper();
          $adapter = $db->getAdapter();
          $query = $adapter->prepare("INSERT INTO dbo.CotizacionesLinesOffline (SalesQuotationNumber,dataAreaId,ItemNumber,RequestedSalesQuantity,ShippingWarehouseId,FixedPriceCharges,SalesTaxGroupCode,STF_Category,LineAmount,CreatedDatetime,InventoryLotId,LineCreationSequenceNumber,SalesUnitSymbol,ShippingSiteId,LineDescription,SalesPrice,LineDiscountPercentage) 
                                      VALUES('".$params[0]['documentId']."','".COMPANY."','".$params[0]['item']."',".$params[0]['cantidad'].",'".$params[0]['almacen']."',
                                            '".$params[0]['_cargoMoneda']."','".$tax."','".$params[0]['categoria']."','".($params[0]['_SalesPrice']*$params[0]['cantidad'])."',GETDATE(),(SELECT CONCAT('OFFCOTV-',COUNT(*)+1) FROM CotizacionesLinesOffline WHERE SalesQuotationNumber = '".$params[0]['documentId']."'),(SELECT COUNT(*)+1 FROM CotizacionesLinesOffline WHERE SalesQuotationNumber = '".$params[0]['documentId']."'),'".strtoupper($params[0]['_unidad'])."','".$params[0]['sitio']."','".$params[0]['descripcion']."',".$precio.",".$PorcentajDescuento2.");");
          if ($flagPatch){
            if ($params[0]['_SalesPrice'] > 0){
              $precioUPD = "LineAmount = ".($params[0]['_SalesPrice']*$params[0]['cantidad']).",";
              if ($params[0]['punitariolinea'] > 0){
                $precioUPD = "LineAmount = ".($params[0]['punitariolinea']*$params[0]['cantidad']).",";
              }
            }
            if ($docType == 'CTZN'){
              $query = $adapter->prepare("UPDATE dbo.CotizacionesLinesOffline 
                                          SET RequestedSalesQuantity = ".$params[0]['cantidad'].",
                                          ".$precioUPD."
                                          ShippingSiteId = '".$params[0]['sitio']."',
                                          ShippingWarehouseId = '".$params[0]['almacen']."',
                                          FixedPriceCharges = ".$params[0]['_cargoMoneda'].",
                                          SalesTaxGroupCode = '".$tax."',
                                          SalesPrice = ".$precio."
                                          OUTPUT INSERTED.idCotizacionLineOff
                                          WHERE SalesQuotationNumber = '".$params[0]['documentId']."' 
                                          AND InventoryLotId = '".$InventoryLotId."';");
            }else{
              $query = $adapter->prepare("UPDATE dbo.SalesOrderLinesOffline 
                                          SET OrderedSalesQuantity = ".$params[0]['cantidad'].",
                                          ".$precioUPD."
                                          ShippingSiteId = '".$params[0]['sitio']."',
                                          ShippingWarehouseId = '".$params[0]['almacen']."',
                                          FixedPriceCharges = ".$params[0]['_cargoMoneda'].",
                                          SalesTaxGroupCode = '".$tax."',
                                          SalesPrice = ".$precio."
                                          OUTPUT INSERTED.idSalesOrderLineOff
                                          WHERE SalesOrderNumber = '".$params[0]['documentId']."' 
                                          AND InventoryLotId = '".$InventoryLotId."';");
            }
          }
          $query->execute();
          if (!$flagPatch){
            $resultQuery = $query->rowCount();
            $stmt = $adapter->query("SELECT * FROM CotizacionesLinesOffline WHERE idCotizacionLineOff = @@IDENTITY;");
            $lastInsert = $stmt->fetchAll();
          }else{
            $resultQuery = $query->fetchAll(PDO::FETCH_ASSOC);
            if ($docType == 'CTZN'){
              $stmt = $adapter->query("SELECT * FROM CotizacionesLinesOffline WHERE idCotizacionLineOff = '".$resultQuery[0]['idCotizacionLineOff']."';");
            }else{
              $stmt = $adapter->query("SELECT * FROM SalesOrderLinesOffline WHERE idSalesOrderLineOff = '".$resultQuery[0]['idSalesOrderLineOff']."';");
            }
            $lastInsert = $stmt->fetchAll();
          }
          if ($resultQuery > 0){
            $response = json_encode($lastInsert[0]);
          }
        }catch(Exception $e){
          print_r($e->getMessage());
        }
        ///////////////////////////////////////////////////////////////////////////////
      }
    
      if ($err) {
        exit();
        $result = "cURL Error #:" . $err;
      } else {
        if (!$flagPatch){
          $result = json_decode($response);
          $paramsEnviados = '{"SalesQuotationNumber" : "'.$params[0]['documentId'].'","dataAreaId": "'.COMPANY.'","ItemNumber" : "'.$params[0]['item'].'","RequestedSalesQuantity" : '.$params[0]['cantidad'].',"ShippingWarehouseId" : "'.$params[0]['almacen'].'","FixedPriceCharges": '.$params[0]['_cargoMoneda'].',"SalesTaxGroupCode": '.$tax.',"STF_Category" : "'.$params[0]['categoria'].'"'.$salesPriceEnv.'"}';
          if ($docType != 'CTZN'){
            $paramsEnviados = '{"SalesOrderNumber" : "'.$params[0]['documentId'].'","dataAreaId": "'.COMPANY.'","ItemNumber" : "'.$params[0]['item'].'","OrderedSalesQuantity" : '.$params[0]['cantidad'].',"SalesTaxGroupCode": '.$tax.',"FixedPriceCharges": '.$params[0]['_cargoMoneda'].',"ShippingWarehouseId" : "'.$params[0]['almacen'].'"}';
          }
          Application_Model_InicioModel::$log->kardexLog("Creacion Cotizacion lineas: ".$result->value[0]->SalesQuotationNumber." resultado: ".  $result->SalesQuotationNumber,$paramsEnviados,$result->SalesQuotationNumber,1,'Creacion Cotizacion lineas');
        }else{
          //CURL1 = curl_init();
          $CURLOPT_URL = "https://".DYNAMICS365."/Data/SalesQuotationLines(dataAreaId=%27".$dataAreaId."%27,InventoryLotId=%27".$InventoryLotId."%27)?%24select=dataAreaId%2CInventoryLotId%2CLineAmount%2CSalesPrice%2CRequestedSalesQuantity%2CSalesQuotationNumber%2CItemNumber,LineCreationSequenceNumber";
          if($docType != 'CTZN'){
            $CURLOPT_URL = "https://".DYNAMICS365."/Data/SalesOrderLines(dataAreaId=%27".$dataAreaId."%27,InventoryLotId=%27".$InventoryLotId."%27)?%24select=dataAreaId%2CInventoryLotId%2CLineAmount%2CSalesPrice%2CSalesOrderNumber%2CItemNumber,LineCreationSequenceNumber";
          }
          if(!$_SESSION['offline']){
            curl_setopt_array(CURL1, array(
              CURLOPT_URL => $CURLOPT_URL,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "GET",
              CURLOPT_POSTFIELDS => "",
              CURLOPT_HTTPHEADER => array(
                "accept: application/json",
                "authorization: Bearer ".$token->getToken()[0]->Token."",
                "content-type: application/json",
                "prefer: return=representation"
              ),
            ));

            $response = curl_exec(CURL1);
            $err = curl_error(CURL1);
          }else{
            $stmt = $adapter->query("SELECT * FROM CotizacionesLinesOffline WHERE SalesQuotationNumber = '".$params[0]['documentId']."' AND InventoryLotId = '".$result->InventoryLotId."';");
            $result = $stmt->fetchAll(PDO::FETCH_OBJ);
            $result = json_encode($result);
          }

          if ($err) {
            $result = "cURL Error #:" . $err;
          } else {
            $result = json_decode($response);
            if ($docType == 'CTZN'){
              $paramsEnviados = '{"SalesQuotationNumber" : "'.$params[0]['documentId'].'","dataAreaId": "'.COMPANY.'","ItemNumber" : "'.$params[0]['item'].'","RequestedSalesQuantity" : '.$params[0]['cantidad'].',"ShippingWarehouseId" : "'.$params[0]['almacen'].'","FixedPriceCharges": '.$params[0]['_cargoMoneda'].',"SalesTaxGroupCode": "'.$tax.'","STF_Category" : "'.$params[0]['categoria'].'"'.$salesPriceEnv.'"}';
              Application_Model_InicioModel::$log->kardexLog("Edicion Cotizacion lineas: ".$response." resultado: ".  $response,$paramsEnviados,$response,1,'Edicion Cotizacion lineas');
            }else{
              $paramsEnviados = '{"SalesOrderNumber" : "'.$params[0]['documentId'].'","dataAreaId": "'.COMPANY.'","ItemNumber" : "'.$params[0]['item'].'","OrderedSalesQuantity" : '.$params[0]['cantidad'].',"SalesTaxGroupCode": "'.$tax.'","FixedPriceCharges": '.$params[0]['_cargoMoneda'].',"ShippingWarehouseId" : "'.$params[0]['almacen'].'"}';
              Application_Model_InicioModel::$log->kardexLog("Edicion OV lineas: ".$response." resultado: ".  $response,$paramsEnviados,$response,1,'Edicion Cotizacion lineas');
            }
          }
        }
      }
      
      ///////////////////////////////check de la ov completa//////////////////////////////////////////////////////////////
      if($docType == 'CTZN'){
        if (!$_SESSION['offline']){
          curl_setopt_array(CURL1, array(
            CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesQuotationLines?%24sysparm_display_value=true&%24filter=SalesQuotationNumber%20eq%20'".$params[0]['documentId']."'%20and%20ItemNumber%20eq'".$params[0]['item']."'&%24select=SalesUnitSymbol%2CItemNumber%2CSalesPrice%2CRequestedSalesQuantity%2CdataAreaId%2CInventoryLotId",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
              "authorization: Bearer ".$token->getToken()[0]->Token."",
              "content-type: application/json"
            ),
          ));

          $responseCompleto = curl_exec(CURL1);
          $err = curl_error(CURL1);
        }else{
          $stmt = $adapter->query("SELECT * FROM CotizacionesLinesOffline WHERE SalesQuotationNumber = '".$params[0]['documentId']."' AND InventoryLotId = '".$result->InventoryLotId."';");
          $stmt->execute();
          $result2 = $stmt->fetchAll(PDO::FETCH_OBJ);
          $result = $result2[0];
          $responseCompleto = [];
          $responseCompleto['value'] = $result2;
          $responseCompleto = json_encode($responseCompleto);
        }

        if ($err) {
          $resultCompleto = "cURL Error #:" . $err;
        } else {
          $resultCompleto = json_decode($responseCompleto);
        }
        if (!empty($resultCompleto->value)){
          if ($resultCompleto->value[0]->ItemNumber != $params[0]['item']){
            return array('SalesOrderNumber' => "no coincide articulos",'dataAreaId' => $resultCompleto->value[0]->dataAreaId,'InventoryLotId' => $resultCompleto->value[0]->InventoryLotId);
          }
          if ($resultCompleto->value[0]->RequestedSalesQuantity != $params[0]['cantidad']){
            return array('SalesOrderNumber' => "no coincide cantidad",'dataAreaId' => $resultCompleto->value[0]->dataAreaId,'InventoryLotId' => $resultCompleto->value[0]->InventoryLotId);
          }
          if ($resultCompleto->value[0]->SalesPrice != $result->SalesPrice){
            return array('SalesOrderNumber' => "no coincide precio",'dataAreaId' => $resultCompleto->value[0]->dataAreaId,'InventoryLotId' => $resultCompleto->value[0]->InventoryLotId);
          }
          if (strtoupper($resultCompleto->value[0]->SalesUnitSymbol) != strtoupper($params[0]['_unidad'])){
            return array('SalesOrderNumber' => "no coincide precio",'dataAreaId' => $resultCompleto->value[0]->dataAreaId,'InventoryLotId' => $resultCompleto->value[0]->InventoryLotId);
          }
        }
      }else{
        curl_setopt_array(CURL1, array(
          CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesOrderLines?%24sysparm_display_value=true&%24filter=SalesOrderNumber%20eq%20'".$params[0]['documentId']."'&%24select=SalesUnitSymbol%2CItemNumber%2CSalesPrice%2CRequestedSalesQuantity%2CdataAreaId%2CInventoryLotId",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 60,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$token->getToken()[0]->Token."",
            "content-type: application/json"
          ),
        ));

        $responseCompleto = curl_exec(CURL1);
        $err = curl_error(CURL1);

        if ($err) {
          $resultCompleto = "cURL Error #:" . $err;
        } else {
          $resultCompleto = json_decode($responseCompleto);
          if (!empty($resultCompleto->value)){
            if ($resultCompleto->value[0]->ItemNumber != $params[0]['item']){
              return array('SalesOrderNumber' => "no coincide articulos",'dataAreaId' => $resultCompleto->value[0]->dataAreaId,'InventoryLotId' => $resultCompleto->value[0]->InventoryLotId);
            }
            if ($resultCompleto->value[0]->OrderedSalesQuantity != $params[0]['cantidad']){
              return array('SalesOrderNumber' => "no coincide articulos",'dataAreaId' => $resultCompleto->value[0]->dataAreaId,'InventoryLotId' => $resultCompleto->value[0]->InventoryLotId);
            }
            if ($resultCompleto->value[0]->SalesPrice != $result->value[0]->cantidad){
              return array('SalesOrderNumber' => "no coincide precio",'dataAreaId' => $resultCompleto->value[0]->dataAreaId,'InventoryLotId' => $resultCompleto->value[0]->InventoryLotId);
            }
            if ($resultCompleto->value[0]->SalesUnitSymbol != $params[0]['_unidad']){
              return array('SalesOrderNumber' => "no coincide precio",'dataAreaId' => $resultCompleto->value[0]->dataAreaId,'InventoryLotId' => $resultCompleto->value[0]->InventoryLotId);
            }
          }
        }
      }
      return array('result' => $result,'flagPatch' => $flagPatch);
    }

    public static function eliminarLineas($dataAreaId,$InventoryLotId,$tipo,$docId){
      $db       = new Application_Model_UserinfoMapper();
      $adapter  = $db->getAdapter();
      if (!$_SESSION['offline']){
        $CURLOPT_URL = "https://".DYNAMICS365."/Data/SalesQuotationLines(dataAreaId=%27".$dataAreaId."%27,InventoryLotId=%27".$InventoryLotId."%27)";
        if ($tipo != 'CTZN'){
         $CURLOPT_URL = "https://".DYNAMICS365."/Data/SalesOrderLines(dataAreaId=%27".$dataAreaId."%27,InventoryLotId=%27".$InventoryLotId."%27)"; 
        }
        $token = new Token();
        curl_setopt_array(CURL1, array(
          CURLOPT_URL => $CURLOPT_URL,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "DELETE",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "authorization: Bearer ".$token->getToken()[0]->Token."",
            "content-type: application/json",
            "prefer: return=representation",
          ),
        ));

        $response = curl_exec(CURL1);
        $err = curl_error(CURL1);
      }else{
        $query = "DELETE CotizacionesLinesOffline WHERE SalesQuotationNumber = '".$docId."' AND InventoryLotId = '".$InventoryLotId."';";
        if ($tipo != 'CTZN'){
          $query = "DELETE SalesOrderLinesOffline WHERE SalesOrderNumber = '".$docId."' AND InventoryLotId = '".$InventoryLotId."';";
        }
        $queryDel = $adapter->prepare($query);
        $queryDel->execute();
        $result = $queryDel->rowCount();
      }

      if ($err) {
        $result = "cURL Error #:" . $err;
      } else {
        $result = $response;
      }
      return $result;
    }
    /**
    * Obtiene los ultimos 4 digitos de la tarjeta con la cual se esta cotizando,
    * de la tabla kardexventas en vase a el documento original
    * @example path  $model->getCuentaPagoTarjeta('ATP-842638');
    * @param string $document con los datos necesarios para verificar un precio 
    * @return string
    */
    public static function getCuentaPagoTarjeta($document) {
        // $query2 = $this->_adapter->prepare(TARJETA_PAGO_COTIZACION);
        // $query2->bindParam(1,$document);
        // $query2->execute();
        // $result =$query2->fetchAll();
        $result = array();
        return $result[0]['CPAGO'];
    }

    public function getDataForEditOV($ov){
        $query = $this->_adapter->prepare(EDITAR_OV);
        $query->bindParam(1,$ov);
        $query->execute();
        $result=$query->fetchAll();
        if(empty($result)){
            $result[0] = "NoResults";
        }
        return  $result;
    }


    public static function mandaralerta($param,$cliente) {
           //$ws= new Metodos();
      Application_Model_InicioModel::$db = new Application_Model_UserinfoMapper();
      $adapterDb = Application_Model_InicioModel::$db->getAdapter();
        (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // $resul = $conn->query("SELECT OrganizationName,CustomerAccount,LineDiscountCode,DiscountPriceGroupId FROM CustCustomerV3Staging");
        $resul = $conn->query("SELECT telefono FROM telefonoscredito");
        $resul->execute();
        $resultadoSinUTF = $resul->fetchAll(PDO::FETCH_ASSOC);
        print_r($resultadoSinUTF[0]['telefono']);exit();
           $curl = curl_init();
           
           curl_setopt_array($curl, array(
           CURLOPT_URL => "https://platform.clickatell.com/messages",
           CURLOPT_RETURNTRANSFER => true,
           CURLOPT_ENCODING => "",
           CURLOPT_MAXREDIRS => 10,
           CURLOPT_TIMEOUT => 30,
           CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
           CURLOPT_CUSTOMREQUEST => "POST",
           CURLOPT_POSTFIELDS => "{\"content\": \"Avance y Tecnología en Plasticos. Se a generado una orden de venta a credito con el número ".$param." a su cuenta ".$cliente." , Si no reconoce esta transaccion comuniquese con su ejecutivo.\", \"to\": [\"".$resultadoSinUTF[0]['telefono']."\"]}",
           CURLOPT_HTTPHEADER => array(
           "accept: application/json",
           "authorization: n_kVGNItQ5KswjQvRxIodA==",
           "content-type: application/json"
           ),
           ));
           
           $response = curl_exec($curl);
           $err = curl_error($curl);
           
           curl_close($curl);
           
           if ($err) {
           echo "cURL Error #:" . $err;
           } else {
           echo $response;
           }            
           return $ov;  
    }

    public static function setCot2Ov($param) {
           //$ws= new Metodos();
      $token = new Token();
      $datos = $param['cliente'];
      $_AccountNum = $datos;
       curl_setopt_array(CURL1, array(
      CURLOPT_URL => "https://".DYNAMICS365."/Data/CustomersV3?%24select=Blocked&%24filter=CustomerAccount%20eq%20'".$_AccountNum."'",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_POSTFIELDS => "",
      CURLOPT_HTTPHEADER => array(
      "authorization: Bearer ".$token->getToken()[0]->Token."",
      "content-type: application/json; odata.metadata=minimal"
      ),
      ));

      $response2 = curl_exec(CURL1);
      $err = curl_error(CURL1);

      $response2=json_decode($response2);
        if($response2->value[0]->Blocked=='No' or $response2->value[0]->Blocked=='Invoice'){          
           $ov = Metodos::ConvertirCotizacion($param);     
          //  curl_setopt_array(CURL1, array(
          //   CURLOPT_URL => "https://".DYNAMICS365."/data/SalesOrderHeadersV2(dataAreaId=%27".COMPANY."%27,SalesOrderNumber=%27".$ov['msg']."%27)",
          //   CURLOPT_RETURNTRANSFER => true,
          //   CURLOPT_ENCODING => "",
          //   CURLOPT_MAXREDIRS => 10,
          //   CURLOPT_TIMEOUT => 30,
          //   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          //   CURLOPT_CUSTOMREQUEST => "PATCH",
          //   CURLOPT_POSTFIELDS => "{
          //                           \"FreightZone\":\"".$param['FreightZone']."\"
          //                         }",
          //   CURLOPT_HTTPHEADER => array(
          //   "authorization: Bearer ".$token->getToken()[0]->Token."",
          //   "content-type: application/json"
          //   ),
          // ));
          
          // $patchPromoCodeResult = curl_exec(CURL1);  
          if($param['promoCodeEnable'] == 'true'){
                (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                // $resul = $conn->query("SELECT OrganizationName,CustomerAccount,LineDiscountCode,DiscountPriceGroupId FROM CustCustomerV3Staging");
                $resul = $conn->prepare("INSERT INTO AYT_PromoRedeemedLog (PromoCode,Ov,ClientCode,ClientName) VALUES('".$param['FreightZone']."','".$ov['msg']."','".$param['cliente']."','".$param['clientname']."')");
                $resul->execute();
          }       
          return $ov;  
        }else{
          return array('cambio'=>false,'status'=>'Fallo','bloqueado'=>true,'msg'=>'El cliente esta bloqueado por credito');
        }
    }

    public static function setDimensionLines($ov,$dimLin,$dimAtp){
      $ws= new Metodos();
      $dimensiones = $dimAtp;
      if (COMPANY == 'lin'){
        $dimensiones = $dimLin;
      }
      $params = array('_SalesId' => $ov,'_company' => COMPANY);
      $update = $ws->setProductData($params,$dimensiones);
      if($update['status'] == 0){
        return "Exito";
      }else{
        return "Fail";
      }
    }

  public static function parcharProp($orventa,$proposito,$dlvMode,$tipoPago)
    {
      $paymx="PUE";
      $token = new Token();
      if($tipoPago=="99"){
        $paymx="PPD";
      }else{
        $paymx="PUE";
      }
      $db = new Application_Model_UserinfoMapper();
      $adapter = $db->getAdapter();
      if ($proposito != ''){
        if (!$_SESSION['offline']){
          curl_setopt_array(CURL1, array(
          CURLOPT_URL => "https://".DYNAMICS365."/data/SalesOrderHeadersV2(dataAreaId=%27".COMPANY."%27,SalesOrderNumber=%27".$orventa."%27)",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "PATCH",
          CURLOPT_POSTFIELDS => "{\"SATPurpose_MX\":\"".$proposito."\",\"DeliveryModeCode\":\"".$dlvMode."\",\"SATPaymMethod_MX\":\"".$paymx."\"}",
          CURLOPT_HTTPHEADER => array(
          "authorization: Bearer ".$token->getToken()[0]->Token."",
          "content-type: application/json"
          ),
          ));
          
          $response28 = curl_exec(CURL1);
          $err = curl_error(CURL1);
        }else{
          $queryUPD = "UPDATE SalesOrderHeadersOffline 
                        SET SATPurpose_MX = '".$proposito."',
                        DeliveryModeCode = '".$dlvMode."',
                        SATPaymMethod_MX = '".$paymx."'
                        WHERE dataAreaId = '".COMPANY."'
                        AND SalesOrderNumber = '".$orventa."';";
          $query = $adapter->prepare($queryUPD);
          $query->execute();
          $result = $query->rowCount();
        }
        return 1;
      }else{
        $model= new Application_Model_InicioModel();
        $user=array('1'=>'sistemas9@avanceytec.com.mx','2'=>'sistemas6@avanceytec.com.mx','3'=>'sistemas11@avanceytec.com.mx');//"sistemas6@avanceytec.com.mx;sistemas@avanceytec.com.mx;telemarketing14@avanceytec.com.mx";
        $asunto='InaxLog parcharProp';
        $model->sendMail($user, $asunto, 'Fallo en parcharProp vacio docId: '. $orventa.'-'.'{"SATPurpose_MX":"'.$proposito.'"}');
        $result = 'Fallo';
        return 0;
      }
    }
    public function getDataForAutoComplete($item){
        $query = $this->_adapter->prepare("SELECT TOP 100 T0.ITEMID 'Articulo',T1.NAME 'Nombre',T2.UNITID 'Unidad',T4.BLOCKSALESPRICES 'PrecioBloqueado', '1' Ordenacion FROM INVENTTABLE T0 INNER JOIN ECORESPRODUCTTRANSLATION T1 ON T0.PRODUCT=T1.PRODUCT INNER JOIN INVENTTABLEMODULE T2 ON T0.ITEMID=T2.ITEMID INNER JOIN INVENTITEMGROUPITEM T3 ON T0.ITEMID=T3.ITEMID AND T3.ITEMDATAAREAID='".COMPANY."'  INNER JOIN INVENTITEMGROUP T4 ON T3.ITEMGROUPID=T4.ITEMGROUPID AND T4.DATAAREAID='".COMPANY."' WHERE T0.DATAAREAID='".COMPANY."' AND T2.MODULETYPE='2' AND T0.ITEMID LIKE ('?%') UNION SELECT TOP 100 T0.ITEMID 'Articulo',T1.NAME 'Nombre',T2.UNITID 'Unidad',T4.BLOCKSALESPRICES 'PrecioBloqueado', '2' Ordenacion FROM INVENTTABLE T0 INNER JOIN ECORESPRODUCTTRANSLATION T1 ON T0.PRODUCT=T1.PRODUCT INNER JOIN INVENTTABLEMODULE T2 ON T0.ITEMID=T2.ITEMID INNER JOIN INVENTITEMGROUPITEM T3 ON T0.ITEMID=T3.ITEMID AND T3.ITEMDATAAREAID='".COMPANY."' INNER JOIN INVENTITEMGROUP T4 ON T3.ITEMGROUPID=T4.ITEMGROUPID AND T4.DATAAREAID='".COMPANY."' WHERE T0.DATAAREAID='".COMPANY."' AND T2.MODULETYPE='2'  AND T1.NAME LIKE '%?%' ORDER BY 5,T0.ITEMID;");
        $query->bindParam(1,$item);
        $query->bindParam(2, $item);
        $query->execute();
        $result = $query->fetchAll();                    
        $datos['noresult'] = "NoResults";
        if (!empty($result)) {
            $datos=$result;
        }
        return  $datos;
    }

    public function setCotToOv($doc,$usuario){
        try {
            $st="";
            $ws= new Metodos();
            $credito = $ws->setSalesOrderCreditLimit($doc,$usuario);
            if ($credito === "") {
                $result = $this->getCredito($doc);
                $bloqueo = $result[0]['BLOCKED'];
                if($bloqueo === '0') {
                    $st = 'OK';
                } else if ($bloqueo == '1') {
                    $st = 'FAIL_BLOCK';
                    }
            }else {
                $st="bloqueado";
            }
            $this->log->kardexLog("Limite de credito parametros: ".$doc."-".$usuario." resultado: ".  json_encode(array("res"=>$st,"msj"=>$credito)),$doc,json_encode(array("res"=>$st,"msj"=>$credito)),1,'Limite de credito');
            return array("res"=>$st,"msj"=>$credito);
        } catch (Exception $e) {
            $this->log->kardexLog("Limite de credito parametros: ".$doc."-".$usuario." resultado: ".  json_encode(array("res"=>'error',"msj"=>'Intente de nuevo si el problema persiste verifique con sistemas','exception'=>$e)),$doc,json_encode(array("res"=>$st,"msj"=>$credito)),1,'Limite de credito');
            return array("res"=>'error',"msj"=>'Intente de nuevo si el problema persiste verifique con sistemas','exception'=>$e);
        }
    }

    public static function isPriceBloked($item){
        $query = $this->_adapter->prepare(IS_PRICE_BLOKED);
        $query->bindParam(1,$item);
        $query->execute();
        $b="NO";
        $result = $query->fetchAll();
        if (empty($result)){
            $b="SI";
            $result=array("ITEMID"=>"$item","BLOCKSALESPRICES"=>"1");
        }
        $this->log->kardexLog("Precio Bloqueado: ".$item." resultado: ".$b, $item,  json_encode($result),1,'PrecioBloqueado');
        return $result;
    }

    public static function getUltimasVentas2($ov,$trans,$sitio=null) {
            $token = new Token();
            if ($trans == 'CTZN') {
                      curl_setopt_array(CURL1, array(
                      CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesQuotationLines?%24filter=SalesQuotationNumber%20eq%20'".$ov."'"  ,
                      CURLOPT_RETURNTRANSFER => true,
                      CURLOPT_ENCODING => "",
                      CURLOPT_MAXREDIRS => 10,
                      CURLOPT_TIMEOUT => 30,
                      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                      CURLOPT_CUSTOMREQUEST => "GET",
                      CURLOPT_HTTPHEADER => array(
                        "Authorization: Bearer " . $token->getToken()[0]->Token."",
                        "Content-Type: application/json",
                        "Postman-Token: e4cd7f45-0dce-4b96-885a-afe52d57650c",
                        "cache-control: no-cache"
                      ),
                    ));
                    $response = curl_exec(CURL1);
                    $err = curl_error(CURL1);
                   // curl_close(CURL1);
                    if ($err) {
                      return "cURL Error #:" . $err;
                    } else {
                     $Johnson=json_decode($response,true);
                    $Johnson=$Johnson['value'];
                    $Johnson=json_encode($Johnson);
                    
                    return $Johnson;



                     // return $response;
                    }
            }else if ($trans == 'ORDVTADET' || $trans == 'ORDVTA') {
            //CURL1 = curl_init();

                      curl_setopt_array(CURL1, array(
                      CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesOrderLines?%24filter=SalesOrderNumber%20eq%20'".$ov."'"  ,
                      //  CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesOrderLines?filter=SalesOrderNumber%20eq%20'".$ov."'"  ,
                      
                      CURLOPT_RETURNTRANSFER => true,
                      CURLOPT_ENCODING => "",
                      CURLOPT_MAXREDIRS => 10,
                      CURLOPT_TIMEOUT => 30,
                      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                      CURLOPT_CUSTOMREQUEST => "GET",
                      CURLOPT_HTTPHEADER => array(
                        "Authorization: Bearer " . $token->getToken()[0]->Token."",
                        "Content-Type: application/json",
                        "Postman-Token: e4cd7f45-0dce-4b96-885a-afe52d57650c",
                        "cache-control: no-cache"
                      ),
                    ));
                       $responseOV = curl_exec(CURL1);
                    $err = curl_error(CURL1);
                   // curl_close(CURL1);



                    if ($err) {
                      return "cURL Error #:" . $err;
                    } else {
                     $JohnsonOV=json_decode($responseOV,true);
                    $JohnsonOV=$JohnsonOV['value'];
                    $JohnsonOV=json_encode($JohnsonOV);
                    
                    return $JohnsonOV;



                     // return $response;
                    }
            }
            ////

            
      //  return $result;

    }

    public static function getDireccionesCliente($user,$ov,$cliente) {
        // $queryCliente = $this->_adapter->prepare(DATOS_ETIQUETA);
        // $queryCliente->bindParam(1,$user);
        // $queryCliente->bindParam(2,$ov);
        // $queryCliente->execute();
        // $resultOV = $queryCliente->fetchAll();
        // $clte = $resultOV[0]['CUSTACCOUNT'];
        // $query = $this->_adapter->prepare(GET_INVOICE_DIREC);
        // $query->bindParam(1,$clte);
        // $query->execute();
        // $direcc=$query->fetchAll();
        // return $direcc;
        $resultado = Application_Model_InicioModel::getDirecionesEntity($cliente);
        return json_encode($resultado);
    }

    public function getInvoiceLines($ov) {
         $queryCliente = $this->_adapter->prepare(GET_INVOICE_LINES);
         $queryCliente->bindParam(1,$ov);
         $queryCliente->execute();
         return $queryCliente->fetchAll();   
    }

    public static function getPayModeByOV($ov){        
        //return $this->db->QueryResulSet(GET_PAYMODE_FROM_OV, [":id"=>$ov]);
      return array('PAYMMODE'=>'01');
    }
    
    function getIdByNetWorkAlias($user) {
        $queryCliente = $this->_adapter->prepare(USER_BY_NETWORK_ALIAS);
         $queryCliente->bindParam(1,$user);
         $queryCliente->execute();
         return $queryCliente->fetchAll(PDO::FETCH_ASSOC);
    }


    public static function isCustomerBlocked($customer){
        $token = new Token();  
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://".DYNAMICS365."/Data/CustomersV3?%24filter=CustomerAccount%20eq%20'".$customer."'&%24select=CustomerAccount%2CBlockedAYT",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_COOKIE => "ApplicationGatewayAffinity=e7fb295f94cb4b5e0cd1e2a4712e4a803fc926342cc4ecca988f29125dbd4b04",
          CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "authorization: Bearer ".$token->getToken()[0]->Token."",
            "content-type: application/json"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
          echo "cURL Error #:" . $err;
        } else {
            $valido = true;
            $customerInfo = json_decode($response);
            if ($customerInfo->value[0]->BlockedAYT != 'No'){
                $valido = false;
            }
            return $valido;
        }
    }

    /**
     * 
     * @param type $factura
     * @param type $journalName
     * @param type $descripcion
     * @param type $montoFactura
     * @param type $diarioCuentaContra
     * @param type $diarioFPago
     * @return string
     * @throws Exception
     */
    static function crearDiario($factura,$journalName,$descripcion,$montoFactura,$diarioCuentaContra,$diarioFPago,$customer,$timbre,$tipoPago,$dlvMode,$referenciap,$digitosTarjeta){
            // $saldocliente=$this->reviewCreditLimit($customer,$montoFactura);
            // if($saldocliente){
            //   print_r('simon');exit();
            // }else{
            // }
            //CURL1 = curl_init();
            $validoBlock = Application_Model_InicioModel::isCustomerBlocked($customer);
            if ( !$validoBlock ){
              return "Bloqueado";
            }   
            //Proceso de agregar los digitos de la tarjeta
            $digitosTarjeta = 'T-'.$digitosTarjeta;
            $descripcionCabezera = $descripcion;
            $descripcionLineas = $descripcion;
            if($digitosTarjeta != ''){
              $descripcionCabezera .= ', '.$digitosTarjeta;
            }
            $token = new Token();       
            //////consulta para obtener el nombre del diario para poder obtener el bussines unit///////////////////////////////////////// 
            curl_setopt_array(CURL1, array(
            CURLOPT_URL => "https://".DYNAMICS365."/data/JournalNames?%24select=DocumentNumber&%24filter=Name%20eq%20'".$journalName."'",
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
            
            $response45 = curl_exec(CURL1);
            $err = curl_error(CURL1);
            $response45=json_decode($response45);
            // print_r($response45);exit();
            ////////arreglo para mapear el bussines unit en los diarios de pago ///////////////////
            $sucursales = array(
               'Credito' => "UN_00001",
               'Caja' => "UN_00001",
               'Caja2' => "UN_00001",
               'Caja3' => "UN_00001",
               'Caja4' => "UN_00001",
               'Caja5' => "UN_00001",
               'CHIH' =>   "UN_00001",
               'AGSC' =>   "UN_00011",
               'HERM' =>   "UN_00002",
               'OBRG' =>   "UN_00010",
               'TJNA' =>   "UN_00014",
               'CULN' =>   "UN_00003",
               'JURZ' =>   "UN_00007",
               'MEXL' =>   "UN_00006",
               'MTRY' =>   "UN_00015",
               'DURN' =>   "UN_00008",
               'GDLJ' =>   "UN_00018",
               'ZACS' =>   "UN_00020",
               'TORN' =>   "UN_00004",
               'EDMX' =>   "UN_00019",
               'SALT' =>   "UN_00005",
               'VCRZ' =>   "UN_00017",
               'SLPS' =>   "UN_00013",
               'QRTO' =>   "UN_00012",
               'LEON' =>   "UN_00009",
               'PBLA' =>   "UN_00016",
               'TXLA' =>   "UN_00021", 
               'VEQP' =>   "UN_00999", 
                );

            // print_r($sucursales[$response->value[0]->DocumentNumber]);exit();
            ////////////   creacion de la cabecera del diario de pago con los datos mandados desde la vista  /////////////////////
            curl_setopt_array(CURL1, array(
            CURLOPT_URL => "https://".DYNAMICS365."/data/CustomerPaymentJournalHeaders",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\n\t \t\t\"dataAreaId\": \"".COMPANY."\",\n      \"JournalName\": \"".$journalName."\",\n      \"Description\": \"".$descripcionCabezera.", ".$referenciap."\"\n}",
            CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$token->getToken()[0]->Token."",
            "content-type: application/json; odata.metadata=minimal",
            "odata-version: 4.0"
            ),
            ));

            $response = curl_exec(CURL1);
            $err = curl_error(CURL1);
            $response=json_decode($response); ////////// el json decode sirve para poder acceder a los elementos de la respuesta ///////////     
            // print_r($response);
            // print_r("{\n\t \t\t\"dataAreaId\": \"".COMPANY."\",\n\t\t\t\"LineNumber\": 1,\n      \"JournalBatchNumber\": \"".$response->JournalBatchNumber."\",\n\t\t\t\"OffsetAccountType\": \"Bank\",\n\t\t\t\"PaymentReference\": \"".$factura."\",\n\t\t\t\"STF_RefSalesId\": \"".$factura."\",\n\t\t\t\"AccountDisplayValue\": \"\",\n\t\t\t\"OffsetAccountDisplayValue\": \"".$diarioCuentaContra."\",\n\t \t\t\"CreditAmount\": ".$montoFactura.",\n\t\t\t\"PaymentMethodName\": \"".$diarioFPago."\",\n\t\t\t\"TransactionText\": \"".$descripcion.", ".$response->JournalBatchNumber."\",\n\"CurrencyCode\": \"MXN\"\n");

            /////////////// post para crear la linea del diario de pago con los datos obtenidos en la vista ///////////////////////////
            $descripcionLineas .= ', '.$response->JournalBatchNumber;
            if($digitosTarjeta != ''){
              $descripcionLineas .= ', '.$digitosTarjeta;
            }
            curl_setopt_array(CURL1, array(
            CURLOPT_URL => "https://".DYNAMICS365."/data/CustomerPaymentJournalLines",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,   
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\n\t \t\t\"dataAreaId\": \"".COMPANY."\",\n\t\t\t\"LineNumber\": 1,\n      \"JournalBatchNumber\": \"".$response->JournalBatchNumber."\",\n\t\t\t\"OffsetAccountType\": \"Bank\",\n\t\t\t\"PaymentReference\": \"".$factura."\",\n\t\t\t\"STF_RefSalesId\": \"".$factura."\",\n\t\t\t\"AccountDisplayValue\": \"\",\n\t\t\t\"OffsetAccountDisplayValue\": \"".$diarioCuentaContra."\",\n\t \t\t\"CreditAmount\": ".$montoFactura.",\n\t\t\t\"PaymentMethodName\": \"\",\n\t\t\t\"TransactionText\": \"".$descripcionLineas.", ".$referenciap."\",\n\"CurrencyCode\": \"MXN\"\n}",
            CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$token->getToken()[0]->Token."",
            "content-type: application/json; odata.metadata=minimal",
            "odata-version: 4.0"
            ),
            ));

            $response2 = curl_exec(CURL1);
            $err = curl_error(CURL1);
            /////////////// parche para poder guardar el cliente en la linea del diario ya que no se impacta en el post por razones desconocidas de dynamics ///////////////////////////
            if ($customer != ''){
              curl_setopt_array(CURL1, array(
              CURLOPT_URL => "https://".DYNAMICS365."/data/CustomerPaymentJournalLines(dataAreaId=%27".COMPANY."%27,LineNumber=1,JournalBatchNumber=%27".$response->JournalBatchNumber."%27)",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "PATCH",
              CURLOPT_POSTFIELDS => "{\"AccountDisplayValue\": \"".$customer."\"}",
              CURLOPT_HTTPHEADER => array(
              "authorization: Bearer ".$token->getToken()[0]->Token."",
              "content-type: application/json; odata.metadata=minimal",
              "odata-version: 4.0"
              ),
              ));
            }else{
              $model= new Application_Model_InicioModel();
              $user=array('1'=>'sistemas9@avanceytec.com.mx','2'=>'sistemas6@avanceytec.com.mx','3'=>'sistemas11@avanceytec.com.mx');//"sistemas6@avanceytec.com.mx;sistemas@avanceytec.com.mx;telemarketing14@avanceytec.com.mx";
              $asunto='InaxLog crearDiario';
              $model->sendMail($user, $asunto, 'Fallo en crearDiario: <b>'.$response->JournalBatchNumber.'</b> datos vacios customer: '.$customer.'-'.'{"AccountDisplayValue": "'.$customer.'"}');
              $result = 'Fallo';
            }
//,\n\t\t\t\"DefaultDimensionsForOffsetAccountDisplayValue\": \"".$sucursales[$response45->value[0]->DocumentNumber]."\"\n
            $response3 = curl_exec(CURL1);
            $err = curl_error(CURL1);

            // print_r($response3);exit();
            // curl_setopt_array(CURL1, array(
            // CURLOPT_URL => "https://".DYNAMICS365."/data/SalesOrderHeadersV2(dataAreaId=%27".COMPANY."%27,SalesOrderNumber=%27".$factura."%27)",
            // CURLOPT_RETURNTRANSFER => true,
            // CURLOPT_ENCODING => "",
            // CURLOPT_MAXREDIRS => 10,
            // CURLOPT_TIMEOUT => 30,
            // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            // CURLOPT_CUSTOMREQUEST => "PATCH",
            // CURLOPT_POSTFIELDS => "{\"SATPaymMethod_MX\":\"".$tipoPago."\",
            //                         \"DeliveryModeCode\":\"".$dlvMode."\"}",
            // CURLOPT_HTTPHEADER => array(
            // "authorization: Bearer ".$token->getToken()[0]->Token."",
            //  "content-type: application/json"
            // ),
            // ));
            
            // $response9 = curl_exec(CURL1);
            // $err = curl_error(CURL1);
            // print_r($response3);

      ///////////// web service que realiza el posteo de los diarios(registro) para su posterior timbrado//////////////////////////
             /////////////////////////////////objetos de la cabecera y lineas para parchar/////////////////////////////
             $postfieldLines = (object) array(  'dataAreaId' => COMPANY,
                                                'LineNumber' => 1,
                                                'JournalBatchNumber' => $response->JournalBatchNumber,
                                                'OffsetAccountType' => "Bank",
                                                'PaymentReference' => $factura,
                                                'STF_RefSalesId' => $factura,
                                                'OffsetAccountDisplayValue' => $diarioCuentaContra,
                                                'CreditAmount' => (float)$montoFactura,
                                                'PaymentMethodName' => "",
                                                'TransactionText' => $descripcionLineas.", ".$referenciap,
                                                'CurrencyCode' => "MXN",
                                                'AccountDisplayValue' => $customer
                                              );
             $postfieldsHeader = (object) array(  'dataAreaId' => COMPANY,
                                                  'Description' => $descripcionCabezera.", ".$referenciap
                                                );
             ///////////////////////////////////////////////////////////////////////////////////////////////////////////
             $a = $response->JournalBatchNumber;
             $validoDiario = Application_Model_InicioModel::validarDiarios($a);
             if ($validoDiario){
               curl_setopt_array(CURL1, array(
                 CURLOPT_URL => "https://".DYNAMICS365."/api/services/STF_INAX/STF_DiariosPagos/postPaymentJournal",
                 CURLOPT_RETURNTRANSFER => true,
                 CURLOPT_ENCODING => "",
                 CURLOPT_MAXREDIRS => 10,
                 CURLOPT_TIMEOUT => 30,
                 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                 CURLOPT_CUSTOMREQUEST => "POST",
                 CURLOPT_POSTFIELDS => "{\n\t\"journal\": \"".$response->JournalBatchNumber."\",\n\t\"company\": \"".COMPANY."\"\n}",
                 CURLOPT_HTTPHEADER => array(
                 "authorization: Bearer ".$token->getToken()[0]->Token."",
                 "content-type: application/json"
                 ),
               ));

               $response4 = curl_exec(CURL1);
               $err = curl_error(CURL1);
             }else{
              for ($i = 0;$i < 10; $i++){
                $validoParche = Application_Model_InicioModel::parcharDiario($postfieldsHeader,$postfieldLines);
                if (!$validoParche['status']){
                  curl_setopt_array(CURL1, array(
                     CURLOPT_URL => "https://".DYNAMICS365."/api/services/STF_INAX/STF_DiariosPagos/postPaymentJournal",
                     CURLOPT_RETURNTRANSFER => true,
                     CURLOPT_ENCODING => "",
                     CURLOPT_MAXREDIRS => 10,
                     CURLOPT_TIMEOUT => 30,
                     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                     CURLOPT_CUSTOMREQUEST => "POST",
                     CURLOPT_POSTFIELDS => "{\n\t\"journal\": \"".$response->JournalBatchNumber."\",\n\t\"company\": \"".COMPANY."\"\n}",
                     CURLOPT_HTTPHEADER => array(
                     "authorization: Bearer ".$token->getToken()[0]->Token."",
                     "content-type: application/json"
                     ),
                   ));
                  $response4 = curl_exec(CURL1);
                  $err = curl_error(CURL1);
                }
                $validoDiario2 = Application_Model_InicioModel::validarDiarios($a);
                if ($validoDiario2){
                  break;
                }
              }
             }
             // print_r("{\n\t\"journal\": \"".$response->JournalBatchNumber."\",\n\t\"company\": \"".COMPANY."\"\n}");
             // print_r("https://".DYNAMICS365."/api/services/STF_INAX/STF_DiariosPagos/postPaymentJournal");
             //print_r($response4);exit();
              Application_Model_InicioModel::$log = new Application_Model_Userinfo();
              Application_Model_InicioModel::$log->kardexLog("Creacion de diario: ".$response->JournalBatchNumber, "{\n\t \t\t\"dataAreaId\": \"".COMPANY."\",\n\t\t\t\"LineNumber\": 1,\n      \"JournalBatchNumber\": \"".$response->JournalBatchNumber."\",\n\t\t\t\"OffsetAccountType\": \"Bank\",\n\t\t\t\"PaymentReference\": \"".$factura."\",\n\t\t\t\"STF_RefSalesId\": \"".$factura."\",\n\t\t\t\"AccountDisplayValue\": \"\",\n\t\t\t\"OffsetAccountDisplayValue\": \"".$diarioCuentaContra."\",\n\t \t\t\"CreditAmount\": ".$montoFactura.",\n\t\t\t\"PaymentMethodName\": \"\",\n\t\t\t\"TransactionText\": \"".$descripcion.", ".$response->JournalBatchNumber.", ".$referenciap."\",\n\"CurrencyCode\": \"MXN\"\n}",'',1,'Creacion de diario');
           // curl_close(CURL1);
            if ($err) {
            return "cURL Error #:" . $err;
            }else {
              // $resp = json_decode($response2);
              // if (!isset($resp->error)){
              //   Application_Model_InicioModel::puntosAvance($factura,$customer,$montoFactura);
              // }
              return json_decode($response2);
            }

            try{

                $saldoQ=$this->db->Query("SELECT SUM(T0.INVOICEAMOUNT - T1.SETTLEAMOUNTCUR) 'SALDO' FROM CUSTINVOICEJOUR T0 INNER JOIN CUSTTRANS T1 ON T1.VOUCHER=T0.LEDGERVOUCHER WHERE T1.INVOICE LIKE 'FV%' AND T0.DATAAREAID = '".COMPANY."' AND T1.DATAAREAID = '".COMPANY."' and T0.INVOICEID = :factura GROUP BY T1.INVOICE", [":factura"=>$factura]);
                //$saldoQ=$this->db->Query("SELECT SUM(T0.INVOICEAMOUNTMST) AS 'SALDO' from CUSTINVOICEJOUR T0 WHERE T0.DATAAREAID='".COMPANY."' and T0.INVOICEID = :factura GROUP BY T0.INVOICEID ", [":factura"=>$factura]);
                $saldo=  floatval($saldoQ[0][0]);  
                if(count($saldoQ)==0){$saldo=$montoFactura;}
                $arr=["resultado"=>["resultado"=>"No se aceptan cantidades negativas : $".$saldo,"saldo"=>$saldo]];
                if($montoFactura >0 ){
                    $facturaData=  $this->db->Query("select T1.INVOICEAMOUNT,T1.INVOICEACCOUNT,T0.PAYMMODE,T0.PAYMENT from SALESTABLE T0 inner join CUSTINVOICEJOUR T1 on T0.SALESID=T1.SALESID where T1.INVOICEID= :id ",array(":id"=>$factura));
                    $ws= new Metodos();  
                    $credCont="false";
                    if($facturaData[0][3]=="CONTADO"){ $credCont="true"; }
                    $xml='<?xml version="1.0" encoding="utf-8"?>
                            <JournalPayment>
                                 <Table>
                                <Company>'.COMPANY.'</Company>
                                    <JournalName>'.$journalName.'</JournalName>
                                    <Name>'.$descripcion.'</Name>
                                    <Post>'.$credCont.'</Post>
                                 </Table>
                                 <Lines>
                                   <Line LedgerDimension="'.$facturaData[0][1].'" MarkedInvoice="'.$factura.'" Txt="'.$descripcion.'" AmountCurCredit="'.$montoFactura.'" PaymMode="'.$diarioFPago.'" OffsetLedgerDimension="'.$diarioCuentaContra.'"></Line>                               
                                 </Lines>
                            </JournalPayment>';
                    if(count($saldoQ)===0){$saldo=$facturaData[0][0]-$montoFactura;}
                    else{
                        $saldo=$saldo-$montoFactura;
                    }
                    
                    if($credCont==="false"){
                        $flagCredito=$this->db->Query("select diario from ".INTERNA.".dbo.diariosCredito where cajero= :cajero and fecha = :fecha ;",[":cajero"=>$journalName,":fecha"=>date("Y-m-d")]);
                        if(count($flagCredito)==0){
                            $r=0;
                            $diario=$ws->createDiario($xml);
                            if($diario['resultado']=='ok'){
                                $r=$this->db->Insert("insert into  ".INTERNA.".dbo.diariosCredito values( :diario ,:name,GETDATE())",[":diario"=>$diario['respuesta'],":name"=>$journalName]);
                            }                            
                            $arr=["resultado"=>$diario,"saldo"=>$saldo,"insert"=>$r,"metodo"=>$credCont]; 
                        }
                        else{
                           $arr=["resultado"=>["respuesta"=>$this->addLinesToDiario($flagCredito[0][0],$facturaData[0][1],$factura,$descripcion,$montoFactura,$diarioFPago,$diarioCuentaContra),"resultado"=>"ok"],"saldo"=>$saldo,"metodo"=>$credCont]; 
                        }
                    }
                    else{
                       //crea un diario de contado
                       $arr=["resultado"=>$ws->createDiario($xml),"saldo"=>$saldo,"metodo"=>$credCont]; 
                    }                    
                }
                return $arr;                 
        }   
        catch (Exception $e){
            throw new Exception ($e); 
        }
    }

public static function puntosAvance($ov,$cliente,$monto){
  (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $lineasOrden            = Application_Model_InicioModel::getLinesFactura($ov);
  $totalPuntos            = 0;
  $existe                 = false;
  $queryPuntos            = "SELECT ItemNumber FROM articulos WHERE AplicaPuntos = 1;";
  $query                  = $conn->query($queryPuntos);
  $query->execute();
  $resultArticulosAplica  = $query->fetchAll(PDO::FETCH_ASSOC);
  foreach($lineasOrden->value as $Data){
      $key = array_search($Data->ItemNumber, array_column($resultArticulosAplica, 'ItemNumber'));
      if($key !== false){
          $existe         = true;
          $totalPuntos    = number_format( (intval($Data->LineAmount / 200) * 2), 2 );
          $puntosTemp     = array(
                              'codigoCliente'     => $cliente,
                              'itemId'            => $Data->ItemNumber,
                              'cantidad'          => $Data->OrderedSalesQuantity,
                              'salesOrderNumber'  => $ov,
                              'lineAmount'        => $Data->LineAmount,
                              'puntosAsignados'   => $totalPuntos
                            );
          $puntos[]       = $puntosTemp;
          //break;
      }
  };
  // $totalPuntos            = number_format( (intval($monto / 200) * 2), 2 );
  $totalPuntos            = intval($totalPuntos);
  $queryNombreCte         = "SELECT OrganizationName FROM CustCustomerV3Staging WHERE CustomerAccount = '".$cliente."';";
  $query                  = $conn->query($queryNombreCte);
  $query->execute();
  $nombreCte              = $query->fetchAll(PDO::FETCH_ASSOC);
  $queryRegistraPuntos    = "EXECUTE dbo.RegistrarPuntosAvance '".$cliente."','".$nombreCte[0]['OrganizationName']."',".$totalPuntos.";";
  $query                  = $conn->prepare($queryRegistraPuntos);
  $query->execute();
  /////////////log de puntos transaccion////////////////////////////////////////////
  foreach($puntos as $DataPuntos){
    $queryLog = "INSERT INTO AYT_LogPuntosAvance VALUES('".$DataPuntos['codigoCliente']."','".$DataPuntos['itemId']."','".$DataPuntos['salesOrderNumber']."',".$DataPuntos['cantidad'].",".$DataPuntos['lineAmount'].",".$DataPuntos['puntosAsignados'].",GETDATE());";
    // print_r($queryLog);exit();
    $query    = $conn->prepare($queryLog);
    $query->execute();
  }
  /////////////////////////////////////////////////////////////////////////////////
}
    
static function reviewCreditLimit($customer,$ordenVenta,$montoCoti='',$tipopago)
{ 
      if($tipopago=='PPD'){//si el tipo de pago es PPD hace la revision , si no, regresa verdadero
      if ($montoCoti == ''){
        $lineasOrden=Application_Model_InicioModel::getLinesFactura($ordenVenta);//se lee la orden de venta para saber el total y hacer el calculo solo en caso que el monto se mande en vacio
        foreach ($lineasOrden->value as $key) {
          $montoFactura+=$key->LineAmount;   
        }
        }else{
        $montoFactura = $montoCoti;
        }
      $token = new Token();
      // $consul=5;
      // $respuesta=array($consul);
      // $Insomnios = array("https://".DYNAMICS365."/Data/GeneralLedgerCustInvoiceJournalLines?%24select=DebitAmount&%24filter=CustomerAccountDisplayValue%20eq%20'".$customer."'&cross-company=true",
      // "https://".DYNAMICS365."/Data/SalesInvoiceHeaders?%24select=TotalInvoiceAmount&%24filter=InvoiceCustomerAccountNumber%20eq%20'".$customer."'&cross-company=true",
      // "https://".DYNAMICS365."/Data/ReturnOrderLines?%24select=ReturnedSalesQuantity%2CLineAmount%2CSalesPrice&%24filter=CustomerAccountNumber%20eq%20'".$customer."'&cross-company=true",
      // "https://".DYNAMICS365."/Data/CustomerPaymentJournalLines?%24select=CreditAmount&%24filter=AccountDisplayValue%20eq%20'".$customer."'&cross-company=true",
      // "https://".DYNAMICS365."/Data/Customers?%24select=Name%2CPaymentTerms%2CCreditLimit&%24filter=CustomerAccount%20eq%20'".$customer."'&cross-company=true");
      // $suma=0;
      // for($j=0;$j<$consul;$j++){
      //   //CURL1 = curl_init();
        
      //   curl_setopt_array(CURL1, array(
      //   CURLOPT_URL =>$Insomnios[$j] ,
      //   CURLOPT_RETURNTRANSFER => true,
      //   CURLOPT_ENCODING => "",
      //   CURLOPT_MAXREDIRS => 10,
      //   CURLOPT_TIMEOUT => 30,
      //   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      //   CURLOPT_CUSTOMREQUEST => "GET",
      //   CURLOPT_POSTFIELDS => "",
      //   CURLOPT_HTTPHEADER => array(
      //   "authorization: Bearer ".$token->getToken()[0]->Token."",
      //   "content-type: application/json"
      //   ),
      //   ));
        
      //   //$response = curl_exec(CURL1);
      //   $respuesta[$j] = curl_exec(CURL1);
      // }
      // $err = curl_error(CURL1);
      
      //// curl_close(CURL1);
      
      // if ($err) {
      //   echo "cURL Error #:" . $err;
      // } else {
      // // echo $response;
      // }   
      // //var_dump($respuesta);
      // for($r=0;$r<4;$r++){
      //   $Johnson[$r]=json_decode($respuesta[$r],true);
      //   $Johnson[$r]=$Johnson[$r]['value'];
      // }
      // $campos= array("DebitAmount", "TotalInvoiceAmount", "SalesPrice","CreditAmount");
      // //var_dump($Johnson);
      // for($i=0;$i<count($Johnson);$i++){
      // //print_r($Johnson[$i]['{$campos}']);
      //   for($t=0;$t<count($Johnson[$i]);$t++){
      //     if($i<2){
      //       $suma+=$Johnson[$i][$t][$campos[$i]];
      //     }else if($i==2){
      //       $suma+=(($Johnson[$i][$t][$campos[$i]]*$Johnson[$i][$t]['ReturnedSalesQuantity'])*1.16);
      //     }else{
      //       $suma-=$Johnson[$i][$t][$campos[$i]];
      //     }
      //   }
      // }
      // $Johnson2=json_decode($respuesta[4],true);
      // $suma=(($Johnson2['value'][0]['CreditLimit'])-$suma);
      // $isCredit = $Johnson2['value'][0]['PaymentTerms'];
      //CURL1 = curl_init();

      ///esta consulta se hace para traer los datos de las transacciones del cliente para saber si tiene saldo pendiente////
      curl_setopt_array(CURL1, array(
      CURLOPT_URL => "https://".DYNAMICS365."/data/STF_CustTrans?%24filter=dataAreaId%20eq%20'".COMPANY."'%20and%20OrderAccount%20eq%20'".$customer."'&%24select=AmountCur",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_POSTFIELDS => "",
      CURLOPT_HTTPHEADER => array(
      "authorization: Bearer ".$token->getToken()[0]->Token."",
      "content-type: application/json; odata.metadata=minimal"
      ),
      ));
      
      $response = curl_exec(CURL1);
      $err = curl_error(CURL1);
      ////tambien se revisa el limite de credito en el cliente///////
      curl_setopt_array(CURL1, array(
      CURLOPT_URL => "https://".DYNAMICS365."/Data/CustomersV3?%24select=OrganizationName%2CDeliveryTerms%2CCreditLimit&%24filter=CustomerAccount%20eq%20'".$customer."'",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_POSTFIELDS => "",
      CURLOPT_HTTPHEADER => array(
      "authorization: Bearer ".$token->getToken()[0]->Token."",
      "content-type: application/json; odata.metadata=minimal"
      ),
      ));

      $response2 = curl_exec(CURL1);
      $err = curl_error(CURL1);

     // curl_close(CURL1);
      $response=json_decode($response);
      $response2=json_decode($response2);
      // if ($err) {
      // return "cURL Error #:" . $err;
      // } else {
      // return $response;
      // }
      $suma=0;
      foreach ($response->value as $value) {//se suman las transacciones pendientes del cliente para sbaer el total
        $suma+=$value->AmountCur;
      }
      $suma=$response2->value[0]->CreditLimit-$suma;//calcula el saldo del cliente en base al credito que tiene declarado contra el saldo calculado de todas las transacciones que tiene el cliente
      // print_r($suma."  ".$response2->value[0]->CreditLimit);exit();
      $isCredit=$response2->value[0]->DeliveryTerms;//en la informacion del cliente s es de contado o credito
    
      }
      else{
        return array('status' => true,'monto' => 0,'total' => 0);
      }

      //print_r($montoFactura." ".$suma." ".$isCredit);exit();
      if($montoFactura<=$suma or $isCredit == 'CONTADO'){
        return array('status' => true,'monto' => $suma,'total' => $response2->value[0]->CreditLimit);//si cuenta con credito suficiente regresa true
      } else{
        return array('status' => false,'monto' => $suma,'total' => $response2->value[0]->CreditLimit);//si no cuenta con credito suficiente regresa falso
      }  
}

    function modificarDiario($JournalNum) {
        try{
            $ws= new Metodos();
            $xml='<?xml version="1.0" encoding="utf-8"?><JournalPayment><Table><Company>'.COMPANY."</Company><JournalNum>".$JournalNum."</JournalNum></Table><Lines>";
            foreach ($_POST['LineNum'] as $key => $value) {
                $xml.='<Line LineNum = "'.(integer)$value.'" Delete = "False"  LedgerDimension="'.$_POST['LedgerDimension'][$key].'" MarkedInvoice="'.$_POST['MarkedInvoice'][$key].'" Txt="'.$_POST['Txt'][$key].'" AmountCurCredit="'.$_POST['AmountCurCredit'][$key].'" PaymMode="'.$_POST['PaymMode'][$key].'" OffsetLedgerDimension="'.$_POST['OffsetLedgerDimension'][$key].'"></Line>';
            }                
            $xml.='</Lines></JournalPayment>';
            return $ws->editDiario($xml);
        }
        catch (Exception $e){
            return $e;
        }            

    }
    
    function cerrarDiario($diario) {
        try{
            $ws= new Metodos();
            return $ws->cerrarDiario($diario);
        }
        catch (Exception $e){
            return $e;
        }   
    }

    /**
     * 
     * @return \Exception
     */
    static function getCuentaContrapartida() {//Journalnames
        // try{
        //     //return $this->db->Query("select cuenta from ".INTERNA.".dbo.ctaContrapartida where id=:id order by cuenta",array(":id"=>SUCURSAL)); 
        //     return $this->db->Query(DIARIO_NAME);             
        // }
        // catch (Exception $e){
        //     return $e;
        // }
      $token = new Token();
      //print_r($token->getToken()[0]->Token);exit();
        //CURL1 = curl_init();   
        //////////// consulta para obtener los nombres de las cuentas e contrapartida(los nombres de las cajas) //////////////////     
        curl_setopt_array(CURL1, array(
        CURLOPT_URL => "https://".DYNAMICS365."/data/JournalNames",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
        "authorization: Bearer ".$token->getToken()[0]->Token."",
        "Content-Type:application/json; odata.metadata=minimal",
        "Host: tes-ayt.sandbox.operations.dynamics.com",
        "Accept: */*"
        ),
        ));        
        $response = curl_exec(CURL1);
        $err = curl_error(CURL1);  

        ////////////////////////////////////////////NOTA IMPORTANTE///////////////////////////////////////////////////////////////////////
        //////ay que recordar que para que exista la relacion entre los nombres de los diarios y los grupos de usuarios el grupo debe estar especificado en el campo Documento en la configuración de los nombres de los diarios en dynamics //////////////////
        ////////////////////////////////////////////NOTA IMPORTANTE////////////////////////////////////////////////////////////////////////

        //// consulta para obtener los grupos de usuarios donde esta asignado el empleado para poder hacer la relacion de cuales nombres de cuentas mostrar en la creacion del diario //////////////////////////////////////////
        curl_setopt_array(CURL1, array(
        CURLOPT_URL => "https://".DYNAMICS365."/data/UserGroupUserLists?%24filter=userId%20eq%20'".$_SESSION['userInax']."'",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
        "authorization: Bearer ".$token->getToken()[0]->Token."",
        "odata-version: 4.0",
        "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/json"
        ),
        ));        
        $response2 = curl_exec(CURL1);
        $err = curl_error(CURL1);  

       // curl_close(CURL1);        

        $respo=array('JournalName'=>json_decode($response),'GroupUser'=>json_decode($response2));

        //print_r($token);exit();
        if ($err) {
        return "cURL Error #:" . $err;
        } else {
          //print_r($respo);exit();
        return $respo;//json_decode($response);
        }
    }

    static function getCuentaContrapartidaLinea($PayType) {//bankaccount  Cuentas de manco a donde se deposita el dinero
     
      //print_r(new DateTime());
      $token = new Token();
      //$suc = Application_Model_InicioModel::getSucursal();
      $filtro="";
      $PayType = explode(' ',$PayType);
      if($PayType[0] == ''){//si es metodo de pago transferencia
        $filtro="https://".DYNAMICS365."/data/BankAccounts?&%24select=BankAccountId%2CName%2CBankGroupId%2CAddressCity&%24filter=dataAreaId%20eq%20'".'ATP'."'%20and%20CurrencyCode%20eq%20'MXN'%20and%20(AddressCity%20eq%20'COMUN'%20or%20AddressCity%20eq%20'".$_SESSION['sucursal']."*')";//abria que revisar la ciudad del usuario para que le aparescan sus cuentas especificas
      }
      if($PayType[0] == 'TRANSFERENCIA' || $PayType[0] == 'CHEQUE'){//si es metodo de pago transferencia o cheque
        $filtro="https://".DYNAMICS365."/data/BankAccounts?&%24select=BankAccountId%2CName%2CBankGroupId%2CAddressCity&%24filter=dataAreaId%20eq%20'".'ATP'."'%20and%20CurrencyCode%20eq%20'MXN'%20and%20BankGroupId%20ne%20'EFECTIVO'%20and%20BankGroupId%20ne%20'E-COMMERCE'%20and%20BankGroupId%20ne%20'CHEQUE'%20and%20BankGroupId%20ne%20'TARJETAS'%20and%20(AddressCity%20eq%20'COMUN'%20or%20AddressCity%20eq%20'".$_SESSION['sucursal']."*')";
      }
      else if ( $PayType[0] == 'TARJETAS' || $PayType[0] == 'TARJETA'){
        $filtro = "https://".DYNAMICS365."/data/BankAccounts?&%24filter=dataAreaId%20eq%20'".'ATP'."'%20and%20CurrencyCode%20eq%20'MXN'%20and%20(BankGroupId%20eq%20'".$PayType[0]."*'or%20Name%20eq%20'*WEB-PAY*')%20and%20(AddressCity%20eq%20'".$_SESSION['sucursal']."*'%20or%20AddressCity%20eq%20'COMUN')&%24select=BankAccountId%2CName%2CBankGroupId";
      }
      else if($PayType[0] !== ''){
        $filtro = "https://".DYNAMICS365."/data/BankAccounts?&%24filter=dataAreaId%20eq%20'".'ATP'."'%20and%20CurrencyCode%20eq%20'MXN'%20and%20BankGroupId%20eq%20'".$PayType[0]."*'%20and%20(AddressCity%20eq%20'".$_SESSION['sucursal']."*'%20or%20AddressCity%20eq%20'COMUN')&%24select=BankAccountId%2CName%2CBankGroupId";
      }

      //print_r($filtro);exit();

      
      //CURL1 = curl_init();        
        curl_setopt_array(CURL1, array(
        CURLOPT_URL => "".$filtro,
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

        $response = curl_exec(CURL1);
        $err = curl_error(CURL1);        
       // curl_close(CURL1);      
       // print_r(new DateTime());
        if ($_SESSION['sucursal'] != 'CHIHUAHUA'){
          $resultJson = json_decode($response);
          $filtro = "";
          foreach($resultJson->value as $Data){
            $filtro .= ','."'".$Data->BankAccountId."'";
          }
          $filtro = substr($filtro, 1);     
          if ($filtro != ''){
            (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $queryBancos = "SELECT * FROM AYT_CuentasBancariasMatch WHERE status = '1' AND sucursal LIKE '".$_SESSION['sucursal']."%' AND formaPago LIKE '%".$PayType[0]."%' AND idBanco IN (".$filtro.");";
            $query = $conn->query($queryBancos);
            $query->execute();
            $resultBancos = $query->fetchAll(PDO::FETCH_ASSOC);//print_r($queryBancos);exit();
          }
          foreach ($resultJson->value as $key=>$Data){
            $encontro = array_search($Data->BankAccountId,array_column($resultBancos,'idBanco'));
            if ($encontro === false){
              unset($resultJson->value[$key]);
            }
          }
          $response = json_encode($resultJson);
        }
        if ($err) {
        return "cURL Error #:" . $err;
        } else {
        return json_decode($response);
        }
        exit();
    }
    /**
     * 
     * @param string $factura
     * @return array regresa array simplificado
     */
    static function getLinesFactura($OV){
        // try{
        //     return $this->db->Query("select T1.INVOICEAMOUNT,T1.INVOICEACCOUNT,T0.PAYMMODE from SALESTABLE T0 inner join CUSTINVOICEJOUR T1 on T0.SALESID=T1.SALESID where T1.INVOICEID= :id ",array(":id"=>$customer)); 
        // }
        // catch (Exception $e){
        //     return $e;
        // }
       // print_r(new DateTime());
      $token = new Token();
        ///////////consulta para obtener la lineas de la orden de venta uy poder calcular el total para el diario de pago///////////////////////     
        curl_setopt_array(CURL1, array(
        CURLOPT_URL => "https://".DYNAMICS365."/data/SalesOrderLines?%24filter=dataAreaId%20eq%20'".COMPANY."'%20and%20SalesOrderNumber%20eq%20'".$OV."'&%24select=LineAmount%2CShippingSiteId,ItemNumber,OrderedSalesQuantity",
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
        
        $response = curl_exec(CURL1);
        $err = curl_error(CURL1);        
       // curl_close(CURL1);        
        if ($err) {
        return "cURL Error #:" . $err;
        } else {
          // print_r(new DateTime());exit();
        return json_decode($response);
        }
       
    }

    public static function checkOffline(){
      $token = new Token();
     
      curl_setopt_array(CURL1, array(
      CURLOPT_URL => "https://".DYNAMICS365."/Data/Employees?%24top=1",
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
      
      $response = curl_exec(CURL1);
      $err = curl_error(CURL1);
      $response2 = json_decode($response);
      if (!isset($response2->value)){
        $xml = new SimpleXMLElement($response);
        foreach($xml as $Data){
          if ( (string)$Data->div[0]['id'] == 'header' ){
            $error = (string)$Data->div[0]->h1;
          }
        }
        if ( $error == 'Server Error' ){
          return true;
        }else{
          return false;
        }
      }else{
        return false;
      }
    }
    
    function getNumeroCompras($client) {
        try{
            return $this->db->Query(NUM_CLIENT_SALES,array(":cliente"=>$client));
        }
        catch(Exception $e){
            return $e;
        }
    }
    
    public static function getArtNotLocked() {
        try{
          // $token = new Token();

          // curl_setopt_array(CURL1, array(
          //     //CURLOPT_URL => "https://".DYNAMICS365."/Data/ReleasedDistinctProducts?%24filter=ProductType%20eq%20Microsoft.Dynamics.DataEntities.EcoResProductType'Service'%20and%20((ProductGroupId%20eq%20'SERVICIOS'%20and%20not(ItemNumber%20eq%20'COMP-*')%20)%20and%20(ProductGroupId%20eq%20'SERVICIOS'%20and%20not(ItemNumber%20eq%20'CRED-*')%20and%20not(ItemNumber%20eq%20'GTOS*')%20and%20not(ItemNumber%20eq%20'ACTV*')%20)%20)%20or%20ProductGroupId%20eq%20'PRENDAS'&%24select=ItemNumber",
          //     CURLOPT_URL => "https://".DYNAMICS365."/Data/ReleasedDistinctProducts?%24filter=ProductType%20eq%20Microsoft.Dynamics.DataEntities.EcoResProductType'Service'%20%20or%20ProductGroupId%20eq%20'PRENDAS'%20or%20(ProductGroupId%20eq%20'LIQUID'%20and%20(ItemNumber%20eq%20'9910*'%20or%20ItemNumber%20eq%20'9915*'%20or%20ItemNumber%20eq%20'9920*'%20or%20ItemNumber%20eq%20'9999*'))%20or%20(ProductGroupId%20eq%20'ARTICULOS'%20and%20ItemNumber%20eq%20'9999*')&%24select=ItemNumber%2CProductGroupId&$count=true",
          //     CURLOPT_RETURNTRANSFER => true,
          //     CURLOPT_ENCODING => "",
          //     CURLOPT_MAXREDIRS => 10,
          //     CURLOPT_TIMEOUT => 30,
          //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          //     CURLOPT_CUSTOMREQUEST => "GET",
          //     CURLOPT_POSTFIELDS => "",
          //     CURLOPT_HTTPHEADER => array(
          //       "authorization: Bearer ".$token->getToken()[0]->Token.""
          //     ),
          //   ));

          // $responseService = curl_exec(CURL1);
          // $resultService = json_decode($responseService);
          $db = new Application_Model_UserinfoMapper();
          $adapter = $db->getAdapter();
          $query = $adapter->query("SELECT ItemNumber,ProductGroupId
                                    FROM EcoResReleasedProductV2Staging 
                                    WHERE ProductType = 2 
                                    OR ProductGroupId = 'PRENDAS' 
                                    OR (ProductgroupId = 'LIQUID' AND (ItemNumber LIKE '9910%' OR ItemNumber LIKE '9915%' OR ItemNumber LIKE '9920%' OR ItemNumber LIKE '9999%'))
                                    OR (ProductGroupId = 'ARTICULOS' AND (ItemNumber LIKE '9999%'))
                                    ORDER BY ItemNumber");
          $query->execute();
          $re=$query->fetchAll();
          $artNoBloq = array();
          // foreach($resultService->value as $Data){
          foreach($re as $Data){
            array_push($artNoBloq, $Data['ItemNumber']);
            // array_push($artNoBloq, $Data->ItemNumber);
          }
          return $artNoBloq;
          //return array("0100-0010-0050","0641-0063-0024");
          // return $this->db->Query(GET_ITEMS_NOT_BLOKED);
        }
        catch(Exception $e){
            return $e ;
        }
    }
    
    function sendMail($adress,$asunto,$body,$file=null) {
        try{
            include (LIBRARY_PATH.'/includes/phpMailer/PHPMailerAutoload.php');
            $mail = new PHPMailer;
            $mail->isSMTP();
            $mail->SMTPDebug = 0;
            $mail->Debugoutput = 'html';
            $mail->Host = 'ssl://smtp.gmail.com';//'187.141.228.93:25';
            $mail->Port = '465';//25;
            $mail->SMTPSecure = 'ssl';
            $mail->SMTPAuth = true;
            $mail->Username = "ayt_notificacion@avanceytec.com.mx";
            $mail->Password = "3BzB2QV5iYx8";
            $mail->FromName = "Avance y tecnologia en plasticos";  
            if(is_array($adress)){
              foreach ($adress as $k=>$v){
                if ($v != ''){
                  if (filter_var($v, FILTER_VALIDATE_EMAIL)) {
                    $mail->addAddress($v);
                  }else{
                    $emailErr = "Invalid email format";
                  }
                }
              }
            }
            else{
                $mail->addAddress($adress);
            }
            $mail->Subject = $asunto;
            $mail->msgHTML(utf8_decode($body));
            $mail->AltBody = 'Ventas Equipos';

            if ($file != null){
              foreach($file as $adjunto){
                $mail->addAttachment($adjunto['tmp_name'], $adjunto['name']);
              }
            }
            if (!$mail->send()) {
                return "Mailer Error: " . $mail->ErrorInfo;
            } else {
                return "enviado";
            }
        }
        catch (Exception $e){
            return $e->getMessage();
        }
    }

    public static function sendMailWPAY($adress,$asunto,$body) {
      try{
          include (LIBRARY_PATH.'/includes/phpMailer/PHPMailerAutoload.php');
          $mail = new PHPMailer;
          $mail->isSMTP();
          $mail->SMTPDebug = 0;
          $mail->Debugoutput = 'html';
          $mail->Host = 'ssl://smtp.gmail.com';//'187.141.228.93:25';
          $mail->Port = '465';//25;
          $mail->SMTPSecure = 'ssl';
          $mail->SMTPAuth = true;
          $mail->Username = "ayt_notificacion@avanceytec.com.mx";
          $mail->Password = "3BzB2QV5iYx8";
          $mail->FromName = "Avance y tecnologia en plasticos";  
          if(is_array($adress)){
            foreach ($adress as $k=>$v){
              if ($v != ''){
                if (filter_var($v, FILTER_VALIDATE_EMAIL)) {
                  $mail->addAddress($v);
                }else{
                  $emailErr = "Invalid email format";
                }
              }
            }
          }
          else{
              $mail->addAddress($adress);
          }
          // $mail->addAddress('coordinador.tic@avanceytec.com.mx');
          $mail->addAddress('sistemas9@avanceytec.com.mx');
          $mail->addAddress('PickUp_Traking@avanceytec.com.mx');
          $mail->Subject = $asunto;
          $mail->msgHTML(utf8_decode($body));
          $mail->AltBody = 'Envio de guía';
          if (!$mail->send()) {
              return "Mailer Error: " . $mail->ErrorInfo;
          } else {
              return "enviado";
          }
      }
      catch (Exception $e){
          return $e->getMessage();
      }
    }

  function sendMailGuias($adress,$asunto,$body) {
        try{
            include (LIBRARY_PATH.'/includes/phpMailer/PHPMailerAutoload.php');
            $mail = new PHPMailer;
            $mail->isSMTP();
            $mail->SMTPDebug = 0;
            $mail->Debugoutput = 'html';
            $mail->Host = 'ssl://smtp.gmail.com';//'187.141.228.93:25';
            $mail->Port = '465';//25;
            $mail->SMTPSecure = 'ssl';
            $mail->SMTPAuth = true;
           // $mail->Username = "ventascat21@avanceytec.com.mx";
            //$mail->Password = "BC331DA5367";
            $mail->Username = "notificaciones@avanceytec.com.mx";
             $mail->Password = "avanceytec";
            $mail->FromName = "Avance y tecnologia en plasticos";  
            if(is_array($adress)){
                foreach ($adress as $k=>$v){
                    $mail->addAddress($v);
                }
            }
            else{
                $mail->addAddress($adress);
            }            
            $mail->Subject = $asunto;
            $mail->msgHTML(utf8_decode($body));
            $mail->AltBody = 'Envio de guía';
            if (!$mail->send()) {
                return "Mailer Error: " . $mail->ErrorInfo;
            } else {
                return "enviado";
            }
        }
        catch (Exception $e){
            return $e->getMessage();
        }
    }
    
    function addLinesToDiario($diario,$cliente,$factura,$txt,$monto,$formaPago,$cuentaContra) {
        try{
            $model=new Application_Model_DiariosModel();
            $lineas=$model->getDiarioDetalle($diario);
            $ws= new Metodos();
            $xml='<?xml version="1.0" encoding="utf-8"?><JournalPayment><Table><Company>'.COMPANY."</Company><JournalNum>".$diario."</JournalNum></Table><Lines>";
            $linea=1;
            foreach ($lineas as $key => $value) {
                $linea++;
                $xml.='<Line LineNum = "'.(integer)$value[9].'" Delete = "False"  LedgerDimension="'.$value[2].'" MarkedInvoice="'.$value[3].'" Txt="'.$value[4].'" AmountCurCredit="'.$value[5].'" PaymMode="'.$value[8].'" OffsetLedgerDimension="'.$value[7].'"></Line>';
            }
            $xml.='<Line LineNum = "" Delete = "False"  LedgerDimension="'.$cliente.'" MarkedInvoice="'.$factura.'" Txt="'.$txt.'" AmountCurCredit="'.$monto.'" PaymMode="'.$formaPago.'" OffsetLedgerDimension="'.$cuentaContra.'"></Line>';
            $xml.='</Lines></JournalPayment>';
            //return $xml;
            $res= $ws->editDiario($xml);
            if($res['resultado']=='ok'){
                $res=$diario;
            }
            else{
                $res=$res['resultado'];
            }
            return $res;
        }
        catch (Exception $e){
            return $e->getMessage();
        }       
    }
    
    public static function getAlternativos($item,$sitio) {
      try{ 
          $html="";
          (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
          $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          $queryAlter = "SELECT *,'0' AS Existencia FROM AYT_Alternativos WHERE ItemId = '".$item."'";
          $query = $conn->query($queryAlter);
          $query->execute();
          $resultAlter = $query->fetchAll(PDO::FETCH_ASSOC);
          $db = new Application_Model_UserinfoMapper();
          $adapter = $db->getAdapter();
          $token = new Token();
          foreach($resultAlter as &$Data){            
            if(!$_SESSION['offline']){
              // curl_setopt_array(CURL1, array(
              //   CURLOPT_URL => "https://".DYNAMICS365."/Data/WarehouseInventoryStatusesOnHand?%24filter=(InventoryWarehouseId%20ne%20''%20)%20and%20ItemNumber%20eq%20'".$Data['ItemIdAlternativo']."'%20and%20dataAreaId%20eq%20'".COMPANY."'%20and%20InventoryStatusId%20eq%20'Disponible'%20and%20not(InventoryWarehouseId%20eq%20'*RECM')%20and%20InventorySiteId%20eq%20'".$sitio."'&%24select=ItemNumber%2CAvailableOnHandQuantity%2CInventorySiteId%2CInventoryWarehouseId%2COnHandQuantity",
              //   CURLOPT_RETURNTRANSFER => true,
              //   CURLOPT_ENCODING => "",
              //   CURLOPT_MAXREDIRS => 10,
              //   CURLOPT_TIMEOUT => 30,
              //   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              //   CURLOPT_CUSTOMREQUEST => "GET",
              //   CURLOPT_POSTFIELDS => "",
              //   CURLOPT_HTTPHEADER => array(
              //     "accept: application/json",
              //     "authorization: Bearer ".$token->getToken()[0]->Token."",
              //     "content-type: application/json"
              //   ),
              // ));

              // $response = curl_exec(CURL1);
              // $err = curl_error(CURL1);
              curl_setopt_array(CURL1, array(
                // CURLOPT_URL => "https://".DYNAMICS365."/Data/WarehouseInventoryStatusesOnHand?%24filter=(InventoryWarehouseId%20ne%20''%20)%20and%20ItemNumber%20eq%20'".$item."'%20and%20dataAreaId%20eq%20'".COMPANY."'%20and%20InventoryStatusId%20eq%20'Disponible'%20and%20not(InventoryWarehouseId%20eq%20'*RECM')&%24select=ItemNumber%2CAvailableOnHandQuantity%2CInventorySiteId%2CInventoryWarehouseId%2COnHandQuantity",
                CURLOPT_URL => "https://".DYNAMICS365."/Data/AYT_InventOnHandByWarehouseStatus?%24filter=(InventoryWarehouseId%20ne%20''%20)%20and%20ItemNumber%20eq%20'".$Data['ItemIdAlternativo']."'%20and%20dataAreaId%20eq%20'".COMPANY."'%20and%20InventoryStatusId%20eq%20'Disponible'%20and%20not(InventoryWarehouseId%20eq%20'*RECM')%20and%20AvailableOnHandQuantity%20ne%200%20and%20wMSLocationId%20ne%20'ASIGNACION'&%24count=true",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_POSTFIELDS => "",
                CURLOPT_HTTPHEADER => array(
                  "accept: application/json",
                  "authorization: Bearer ".$token->getToken()[0]->Token."",
                  "content-type: application/json"
                ),
              ));

              $response = curl_exec(CURL1);
              ////////////////////////ciclo para existencias///////////////////////////////////////////////////////////////////////
              $existenciasTotal = json_decode($response);
              $result = [];
              $existSitioTemp = '';
              $existAlmacTemp = '';
              $existencia = 0;
              $existencia2 = 0;
              foreach($existenciasTotal->value as $existencias){
                if ($existencias->LicensePlateId == '' && $existencias->wMSLocationId !='' && $existencias->AvailableOnHandQuantity < 0){continue;}
                if ($existSitioTemp != $existencias->InventorySiteId){
                  if ($existAlmacTemp != $existencias->InventoryWarehouseId){
                    $existencia = 0;
                    $existencia2 = 0;
                  }
                }
                $existencia += $existencias->AvailableOnHandQuantity;
                $existencia2 += $existencias->OnHandQuantity;
                $result[$existencias->InventoryWarehouseId]->ItemNumber               = $existencias->ItemNumber;
                $result[$existencias->InventoryWarehouseId]->AvailableOnHandQuantity += $existencia;
                $result[$existencias->InventoryWarehouseId]->InventorySiteId          = $existencias->InventorySiteId;
                $result[$existencias->InventoryWarehouseId]->InventoryWarehouseId     = $existencias->InventoryWarehouseId;
                $result[$existencias->InventoryWarehouseId]->OnHandQuantity          += $existencia2;
                $existSitioTemp = $existencias->InventoryWarehouseId;
              }
              $resultClean = [];
              foreach($result as $cleanData){
                if ($cleanData->InventorySiteId == trim($sitio)){
                  $resultClean[] = $cleanData; 
                }
              }
              $result2->value = $resultClean;
              $response = json_encode($result2);
              /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            }else{
              ////////////////consultar la existencia en base de datos/////////////////////////////
              try{
                $conn = new DB_ConexionExport();
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $query = $conn->prepare(" SELECT ItemNumber, AvailableOnHandQuantity,InventorySiteId,InventoryWarehouseId,OnHandQuantity
                                          FROM InventWarehouseInventoryStatusOnHandStaging
                                          WHERE ItemNumber = '".$item."'
                                          AND InventoryWarehouseId != ''
                                          AND InventoryWarehouseId NOT LIKE '%RECM'
                                          AND InventoryStatusId = 'Disponible';");
                $query->execute();
                $resultQuery = $query->fetchAll(PDO::FETCH_ASSOC);
                $response = [];
                $response['value'] = $resultQuery;
                $response = json_encode($response);
              }catch(Exception $e){
                print_r($e->getMessage());
              }
              /////////////////////////////////////////////////////////////////////////////////////
            }
            if ($err) {
              $result = "cURL Error #:" . $err;
            } else {
              $response = json_decode($response);
            }
            $Data['Existencia'] = $response->value[0]->AvailableOnHandQuantity;
            $Data['sitio'] = $response->value[0]->InventorySiteId;
            $Data['almacen'] = $response->value[0]->InventoryWarehouseId;
          }
          if (!empty($resultAlter)){
            $existe = false;
            $html = '';
            foreach ($resultAlter as $DataExist) {
              if($DataExist['Existencia'] > 0){
                $existe = true;
              }
            }
            if ($existe){
              $html .= '<tr><td>&nbsp;<br/></td></tr>';
              $html .= '<tr><td>&nbsp;<br/></td></tr>';
              $html .= '<tr style="border-bottom:solid 1px #BDBDBD"><td>&nbsp;<br/></td></tr>';
              $html .= '<tr>';
              $html .= '  <td colspan="6">';
              $html .= '      <div class="col l12 m12 s12" style="width:100%;padding:0">';
              $html .= '          <h5 style="font-size:25px;background: #FE642E;text-align:center;padding:10px 0 10px 0"><span class="blk" style="color:white">Productos Alternativos</span></h5>';
              $html .= '      </div>';
              $html .= '  </td>';
              $html .= '</tr>';
              $html .= '<tr><td colspan="6">** Por el momento no tenemos existencia del producto <b>' . $item . '</b>, pero podemos ofrecerle: <br/></td></tr>';
              $html .= '<tr>';
              $html .= '	<th>Código de Articulo</th>';
              $html .= '	<th>Nombre del Producto</th>';
              $html .= '	<th>Sitio</th>';
              $html .= '	<th>Almacen</th>';
              $html .= '	<th>Existencia</th>';
              $html .= '	<th></th>';
              $html .= '</tr>';
              $i = 0;
              foreach ($resultAlter as $i => $data) {
                if ($data['Existencia'] > 0){
                  $html .= '<tr>';
                  $html .= '	<td class="itemid">' . $data['ItemIdAlternativo'] . '</td>';
                  $html .= '	<td class="namealias">' . $data['DescripcionAlternativo'] . '</td>';
                  $html .= '	<td class="sitio" data-localidad="GRAL">' . $data['sitio'] . '</td>';
                  $html .= '	<td class="almacen">' . $data['almacen'] . '</td>';
                  $html .= '	<td>' .  number_format($data['Existencia'], 2). '</td>';
                  $html .= '  <td><p><input type="radio" name="RadioExist" value="' . $i . '" class="ExistRadio alternativo" id="radio'.$i.'Alt"><label for="radio' . $i . 'Alt"></label></p></td>';
                  $html .= '</tr>';
                }
              }
            }
          }
          return $html;            
        }
        catch (Exception $e){
            return $e->getMessage();
        }
    }
    
    function existFactura($ov){
        //return $this->db->Query("select INVOICEID from CustInvoiceJour where SALESID= :ov",[":ov"=>$ov]);
      return array();
    }

    /**
    *
    * @param type SalesQuotationNumber
    * @return type SalesQuotationNumber
    */

    static function enviarCotizacion($salesQuotationNumber){
      $ws= new Metodos();
      $cotizacion = Metodos::enviarCotizacion($salesQuotationNumber);
      return $cotizacion;
    }

     public static function getExistenciasFamilia($familia,$sitio, $sitioCliente, $seccionOV){       
      ///////////////////////////existencias de entity///////////////////////////////////////////////////
      $token = new Token();
      (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $queryItemsFamilia = "SELECT DISTINCT(T0.ITEMNUMBER),T1.SALESUNITSYMBOL,T0.PRODUCTNAME
                            FROM InventWarehouseInventoryStatusOnHandStaging T0
                            INNER JOIN EcoResReleasedProductV2Staging T1 ON (T0.ITEMNUMBER = T1.ITEMNUMBER)
                            WHERE T0.ITEMNUMBER LIKE '".$familia."%'";
      $query = $conn->query($queryItemsFamilia);
      $query->execute();
      $resultItemsFamily = $query->fetchAll(PDO::FETCH_ASSOC);
      $allExistencias = [];
      foreach($resultItemsFamily as $DataFamilyItems){
        curl_setopt_array(CURL1, array(
            // CURLOPT_URL => "https://".DYNAMICS365."/Data/WarehouseInventoryStatusesOnHand?%24filter=(InventoryWarehouseId%20ne%20''%20)%20and%20ItemNumber%20eq%20'".$DataFamilyItems['ITEMNUMBER']."'%20and%20dataAreaId%20eq%20'".COMPANY."'%20and%20InventoryStatusId%20eq%20'Disponible'%20and%20not(InventoryWarehouseId%20eq%20'*RECM')%20and%20InventorySiteId%20eq%20'".$sitio."'&%24select=ItemNumber%2CAvailableOnHandQuantity%2CInventorySiteId%2CInventoryWarehouseId%2COnHandQuantity%2CProductName",
            CURLOPT_URL => "https://".DYNAMICS365."/Data/AYT_InventOnHandByWarehouseStatus?%24filter=ItemNumber%20eq%20'".$familia."*'%20and%20InventorySiteId%20eq%20'".$sitio."'%20and%20not(InventoryWarehouseId%20eq%20'*RECM')&%24orderby=InventorySiteId%20asc%2CInventoryWarehouseId%20asc%2CItemNumber%20asc&%24count=true",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
              "accept: application/json",
              "authorization: Bearer ".$token->getToken()[0]->Token."",
              "content-type: application/json"
            ),
        ));        
        $response = curl_exec(CURL1);
        $err = curl_error(CURL1);
        ////////////////////////ciclo para existencias///////////////////////////////////////////////////////////////////////
        $existenciasTotal = json_decode($response);
        $result = [];
        $existSitioTemp = '';
        $existAlmacTemp = '';
        $itemNumberTemp = '';
        $existencia = 0;
        $existencia2 = 0;
        foreach($existenciasTotal->value as $existencias){
          if ($existencias->LicensePlateId == '' && $existencias->wMSLocationId !='' && $existencias->AvailableOnHandQuantity < 0){continue;}
          if ($existSitioTemp != $existencias->InventorySiteId){
            if ($existAlmacTemp != $existencias->InventoryWarehouseId){
              $existencia = 0;
              $existencia2 = 0;
              if ($itemNumberTemp != $existencias->ItemNumber){
                $existencia = 0;
                $existencia2 = 0;
              }
            }
          }
          $existencia += $existencias->AvailableOnHandQuantity;
          $existencia2 += $existencias->OnHandQuantity;
          $result[$existencias->InventoryWarehouseId][$existencias->ItemNumber]->ItemNumber               = $existencias->ItemNumber;
          $result[$existencias->InventoryWarehouseId][$existencias->ItemNumber]->AvailableOnHandQuantity += $existencia;
          $result[$existencias->InventoryWarehouseId][$existencias->ItemNumber]->InventorySiteId          = $existencias->InventorySiteId;
          $result[$existencias->InventoryWarehouseId][$existencias->ItemNumber]->InventoryWarehouseId     = $existencias->InventoryWarehouseId;
          $result[$existencias->InventoryWarehouseId][$existencias->ItemNumber]->ProductName              = $existencias->ProductName;
          $result[$existencias->InventoryWarehouseId][$existencias->ItemNumber]->OnHandQuantity          += $existencia2;
          $existSitioTemp = $existencias->InventoryWarehouseId;
          $itemNumberTemp = $existencias->ItemNumber;
        }
        $resultClean = [];
        foreach($result as $almacenes){
          foreach($almacenes as $cleanData){
            $resultClean[] = $cleanData; 
          }
        }
        $result2->value = $resultClean;
        $response = json_encode($result2);
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if ($err){
          $resultItems = "cURL Error #:" . $err;
        }else{
          $resultItems = json_decode($response);
          foreach($resultItems->value as $DataItemsExistencias){
            if($seccionOV == "LOCAL"){
              //if($sitioCliente == $sitio){
                $existencia = array(
                  'Almacen'             => ($DataItemsExistencias->InventoryWarehouseId !== NULL) ?$DataItemsExistencias->InventoryWarehouseId : $sitio.'CONS',
                  'Articulo'            => ($DataItemsExistencias->ItemNumber !== NULL)? $DataItemsExistencias->ItemNumber : $DataFamilyItems['ITEMNUMBER'],
                  'DescripcionArticulo' => ($DataItemsExistencias->ProductName !== NULL)? $DataItemsExistencias->ProductName : $DataFamilyItems['PRODUCTNAME'],
                  'Existencia'          => ($DataItemsExistencias->AvailableOnHandQuantity !== NULL)? $DataItemsExistencias->AvailableOnHandQuantity : 0,
                  'Sitio'               => ($DataItemsExistencias->InventorySiteId !== NULL)? $DataItemsExistencias->value[0]->InventorySiteId : $sitio,
                  'Unidad'              => $DataFamilyItems['SALESUNITSYMBOL']
                );
                $allExistencias[] = $existencia;
              /*}else{
                if (strpos($DataItemsExistencias->ProductName, 'FLETE') === false) {
                  $existencia = array(
                    'Almacen'             => ($DataItemsExistencias->InventoryWarehouseId !== NULL) ?$DataItemsExistencias->InventoryWarehouseId : $sitio.'CONS',
                    'Articulo'            => ($DataItemsExistencias->ItemNumber !== NULL)? $DataItemsExistencias->ItemNumber : $DataFamilyItems['ITEMNUMBER'],
                    'DescripcionArticulo' => ($DataItemsExistencias->ProductName !== NULL)? $DataItemsExistencias->ProductName : $DataFamilyItems['PRODUCTNAME'],
                    'Existencia'          => ($DataItemsExistencias->AvailableOnHandQuantity !== NULL)? $DataItemsExistencias->AvailableOnHandQuantity : 0,
                    'Sitio'               => ($DataItemsExistencias->InventorySiteId !== NULL)? $DataItemsExistencias->value[0]->InventorySiteId : $sitio,
                    'Unidad'              => $DataFamilyItems['SALESUNITSYMBOL']
                  );
                  $allExistencias[] = $existencia;
                }
              }*/
            }else{
              $existencia = array(
                'Almacen'             => ($DataItemsExistencias->InventoryWarehouseId !== NULL) ?$DataItemsExistencias->InventoryWarehouseId : $sitio.'CONS',
                'Articulo'            => ($DataItemsExistencias->ItemNumber !== NULL)? $DataItemsExistencias->ItemNumber : $DataFamilyItems['ITEMNUMBER'],
                'DescripcionArticulo' => ($DataItemsExistencias->ProductName !== NULL)? $DataItemsExistencias->ProductName : $DataFamilyItems['PRODUCTNAME'],
                'Existencia'          => ($DataItemsExistencias->AvailableOnHandQuantity !== NULL)? $DataItemsExistencias->AvailableOnHandQuantity : 0,
                'Sitio'               => ($DataItemsExistencias->InventorySiteId !== NULL)? $DataItemsExistencias->value[0]->InventorySiteId : $sitio,
                'Unidad'              => $DataFamilyItems['SALESUNITSYMBOL']
              );
              $allExistencias[] = $existencia;
            }                               
          }
             
        }        
      }   
      $result = $allExistencias;
      return $result;
      ///////////////////////////////////////////////////////////////////////////////////////////////////
  }

  public static function getSeccion($cliente){
    $token = new Token();
    //$curl = curl_init();

    curl_setopt_array(CURL1 , array(
      CURLOPT_URL => "https://".DYNAMICS365."/Data/CustomersV3?%24filter=dataAreaId%20eq%20%27atp%27%20and%20CustomerAccount%20eq%20%27".$cliente."%27",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 60,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_POSTFIELDS => "",
      CURLOPT_HTTPHEADER => array(
        "accept: application/json",
        "authorization: Bearer ".$token->getToken()[0]->Token."",
        "content-type: application/json"
      ),
    ));        
    $response = curl_exec(CURL1 );
    $err = curl_error(CURL1);
    if ($err) {
      $result = "cURL Error #:" . $err;
    } else {
      $result = json_decode($response);
    }
    return $result;  
  }
  public static function getStatus($ov){
    $token = new Token();
    //$curl = curl_init();

    curl_setopt_array(CURL1 , array(
      CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesOrderHeadersV2(dataAreaId='atp',SalesOrderNumber='".$ov."')",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 60,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_POSTFIELDS => "",
      CURLOPT_HTTPHEADER => array(
        "accept: application/json",
        "authorization: Bearer ".$token->getToken()[0]->Token."",
        "content-type: application/json"
      ),
    ));        
    $response = curl_exec(CURL1 );
    $err = curl_error(CURL1);
    if ($err) {
      $result = "cURL Error #:" . $err;
    } else {
      $result = json_decode($response);
    }
    return $result;  
  }

  public static function validarDiarios($journalBatchNumber){
    $token = new Token();
    (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $validoCabecera = true;
    $validoLineas   = true;
    $urlHeader      = "https://".DYNAMICS365."/Data/CustomerPaymentJournalHeaders?%24filter=JournalBatchNumber%20eq%20'".$journalBatchNumber."'";
    curl_setopt_array(CURL1, array(
        CURLOPT_URL => $urlHeader,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
          "accept: application/json",
          "authorization: Bearer ".$token->getToken()[0]->Token."",
          "content-type: application/json"
        ),
    ));        
    $response = curl_exec(CURL1);
    $response = json_decode($response);
    $err = curl_error(CURL1);
    if ($err){
      $response = "cURL Error #:" . $err;
    }else{
      if( !empty($response->value) ){
        $dataAreaIdEmpty          = ($response->value[0]->dataAreaId === '')?true:false;
        $JournalBatchNumberEmpty  = ($response->value[0]->JournalBatchNumber === '')?true:false;
        $JournalNameEmpty         = ($response->value[0]->JournalName === '')?true:false;
        $DescriptionEmpty         = ($response->value[0]->Description === '')?true:false;
        if ( $dataAreaIdEmpty || $JournalBatchNumberEmpty || $JournalNameEmpty || $DescriptionEmpty ){
          $validoCabecera = false;
        }
        $urlLineas = "https://".DYNAMICS365."/Data/CustomerPaymentJournalLines?%24filter=JournalBatchNumber%20eq%20'".$journalBatchNumber."'&%24select=dataAreaId%2CLineNumber%2CJournalBatchNumber%2COffsetAccountType%2CPaymentReference%2CSTF_RefSalesId%2CAccountDisplayValue%2COffsetAccountDisplayValue%2CCreditAmount%2CPaymentMethodName%2CTransactionText%2CCurrencyCode";
        curl_setopt_array(CURL1, array(
          CURLOPT_URL => $urlLineas,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 60,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "authorization: Bearer ".$token->getToken()[0]->Token."",
            "content-type: application/json"
          ),
        ));        
        $responseLineas = curl_exec(CURL1);
        $responseLineas = json_decode($responseLineas);
        $err = curl_error(CURL1);
        if ($err){
          $responseLineas = "cURL Error #:" . $err;
        }else{
          if ( !empty($responseLineas->value) ){
            foreach($responseLineas->value AS $DataLinea){
              $dataAreaIdEmpty                 = ($DataLinea->dataAreaId === '')?true:false;
              $LineNumberEmpty                 = ($DataLinea->LineNumber === '')?true:false;
              $JournalBatchNumberEmpty         = ($DataLinea->JournalBatchNumber === '')?true:false;
              $OffsetAccountTypeEmpty          = ($DataLinea->OffsetAccountType === '')?true:false;
              $PaymentReferenceEmpty           = ($DataLinea->PaymentReference === '')?true:false;
              $STF_RefSalesIdEmpty             = ($DataLinea->STF_RefSalesId === '')?true:false;
              $AccountDisplayValueEmpty        = ($DataLinea->AccountDisplayValue === '')?true:false;
              $OffsetAccountDisplayValueEmpty  = ($DataLinea->OffsetAccountDisplayValue === '')?true:false;
              $CreditAmountEmpty               = ($DataLinea->CreditAmount === '')?true:false;
              $PaymentMethodNameEmpty          = ($DataLinea->PaymentMethodName === '')?true:false;
              $TransactionTextEmpty            = ($DataLinea->TransactionText === '')?true:false;
              $CurrencyCodeEmpty               = ($DataLinea->CurrencyCode === '')?true:false;
              if ($dataAreaIdEmpty || $LineNumberEmpty || $JournalBatchNumberEmpty || $OffsetAccountTypeEmpty || $PaymentReferenceEmpty || $STF_RefSalesIdEmpty || $AccountDisplayValueEmpty || $OffsetAccountDisplayValueEmpty || $CreditAmountEmpty || $PaymentMethodNameEmpty || $TransactionTextEmpty || $CurrencyCodeEmpty){
                $validoLineas = false;
              }
            }
          }else{
            $validoLineas = false;
          }
        }
      }else{
        $validoCabecera = false;
      }
    }
    return $validoCabecera && $validoLineas;
  }

  public static function parcharDiario($postfieldsHeader,$postfieldLines){
    $token = new Token();
    $errorLineas = false;
    $errorHeader = false;
    ///////////////////////////////////parchar lineas///////////////////////////////////////////////////////////////////////////////////////
    curl_setopt_array(CURL1, array(
      CURLOPT_URL => "https://".DYNAMICS365."/data/CustomerPaymentJournalLines(dataAreaId=%27".COMPANY."%27,LineNumber=".$postfieldLines->LineNumber.",JournalBatchNumber=%27".$postfieldLines->JournalBatchNumber."%27)",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "PATCH",
      CURLOPT_POSTFIELDS => json_encode($postfieldLines),
      CURLOPT_HTTPHEADER => array(
      "authorization: Bearer ".$token->getToken()[0]->Token."",
      "content-type: application/json; odata.metadata=minimal",
      "odata-version: 4.0"
      ),
    ));
    $responseLineas = curl_exec(CURL1);
    $responseLineas = json_encode($responseLineas);
    $err = curl_error(CURL1);
    if ($err){
      $errorLineas = true;
    }else{
      if ( !empty($responseLineas) ){
        if ($responseLineas !== '""') $errorLineas = true;
      }
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////parchar header////////////////////////////////////////////////////////////////////////////////////////////////
    curl_setopt_array(CURL1, array(
      CURLOPT_URL => "https://".DYNAMICS365."/data/CustomerPaymentJournalHeaders(dataAreaId=%27".COMPANY."%27,JournalBatchNumber=%27".$postfieldLines->JournalBatchNumber."%27)",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "PATCH",
      CURLOPT_POSTFIELDS => json_encode($postfieldsHeader),
      CURLOPT_HTTPHEADER => array(
      "authorization: Bearer ".$token->getToken()[0]->Token."",
      "content-type: application/json; odata.metadata=minimal",
      "odata-version: 4.0"
      ),
    ));

    $responseHeader = curl_exec(CURL1);
    $errH = curl_error(CURL1);
    $responseHeader=json_decode($responseHeader);
    if ($errH){
      $errorHeader = true;
    }else{
      if ( !empty($responseHeader) ){
        if ($responseHeader !== '""')$errorHeader = true;
      }
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    return array('status' => ($errorLineas || $errorHeader),'errorHeader' => $errorHeader, 'errorLineas' => $errorLineas, 'JournalBatchNumber' => $postfieldLines->JournalBatchNumber);
  }

  //****funcion para actualizar los clientes que estan en inax con los datos de dynamics
    public static function updateClientesInax($codigoCliente){
      $token = new Token();
      (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      
      //////////////////consulta de los datos en la entity///////////////////////////
      curl_setopt_array(CURL1, array(
        CURLOPT_URL => "https://".DYNAMICS365."/data/CustomersV3?%24filter=CustomerAccount%20eq%20'".$codigoCliente."'&%24select=CustomerAccount%2COrganizationName%2CSiteId%2CDeliveryMode%2CDeliveryTerms%2CFullPrimaryAddress%2CPaymentBankAccount%2CPaymentMethod%2CPaymentTerms%2CRFCNumber%2CPrimaryContactPhone%2CPrimaryContactEmail%2CCreditLimit%2CBlockedAYT%20%2CCustomerGroupId%2CPartyNumber%2CLineDiscountCode%2CDiscountPriceGroupId",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
          "accept: application/json",
          "authorization: Bearer ".$token->getToken()[0]->Token."",
          "content-type: application/json",
          "x-total-count: application/json"
        ),
      ));

      $responseDataCliente = curl_exec(CURL1);
      $err = curl_error(CURL1);
      $DataCliente = json_decode($responseDataCliente);
      $DataCliente = $DataCliente->value[0];
      ///////////////////////////////////////////////////////////////////////////////

      ///////////////////////update de la base de datos//////////////////////////////
      $CustomerAccount      = $DataCliente->CustomerAccount;
      $OrganizationName     = $DataCliente->OrganizationName;
      $SiteId               = $DataCliente->SiteId;
      $Deliverymode         = $DataCliente->Deliverymode;
      $DeliveryTerms        = $DataCliente->DeliveryTerms;
      $FullPrimaryAddress   = $DataCliente->FullPrimaryAddress;
      $PaymentBankAccount   = $DataCliente->PaymentBankAccount;
      $PaymentMethod        = $DataCliente->PaymentMethod;
      $PaymentTerms         = $DataCliente->PaymentTerms;
      $RFCNumber            = $DataCliente->RFCNumber;
      $PrimaryContactPhone  = $DataCliente->PrimaryContactPhone;
      $PrimaryContactEmail  = $DataCliente->PrimaryContactEmail;
      $CreditLimit          = $DataCliente->CreditLimit;
      $BlockedAYT           = $DataCliente->BlockedAYT ;
      $CustomerGroupId      = $DataCliente->CustomerGroupId;
      $PartyNumber          = $DataCliente->PartyNumber;
      $LineDiscountCode     = $DataCliente->LineDiscountCode;
      $DiscountPriceGroupId = $DataCliente->DiscountPriceGroupId;
      $queryUPDCliente = "UPDATE clientesinax
                      SET CustomerAccount       = '$CustomerAccount',
                          OrganizationName      = '$OrganizationName',
                          SiteId                = '$SiteId',
                          Deliverymode          = '$Deliverymode',
                          DeliveryTerms         = '$DeliveryTerms',
                          FullPrimaryAddress    = '$FullPrimaryAddress',
                          PaymentBankAccount    = '$PaymentBankAccount',
                          PaymentMethod         = '$PaymentMethod',
                          PaymentTerms          = '$PaymentTerms',
                          RFCNumber             = '$RFCNumber',
                          PrimaryContactPhone   = '$PrimaryContactPhone',
                          PrimaryContactEmail   = '$PrimaryContactEmail',
                          CreditLimit           = '$CreditLimit',
                          isBlocked             = '$BlockedAYT',
                          CustomerGroupId       = '$CustomerGroupId',
                          PartyNumber           = '$PartyNumber',
                          LineDiscountCode      = '$LineDiscountCode',
                          DiscountPriceGroupId  = '$DiscountPriceGroupId'
                      WHERE CustomerAccount     = '$codigoCliente';";
      $query = $conn->prepare($queryUPDCliente);
      $query->execute();
      $resultUPD = $query->rowCount();
      if ($resultUPD > 0){
        return 'Actualizacion correcta!.';
      }
      //////////////////////////////////////////////////////////////////////////////
      return 'Ocurrio un error al actualizar el cliente.';
    }

    public static function getClientInfo($id){
      //echo 'dentro de get client';
      $curl = curl_init();
      $token = new Token();
      //print_r($token->getToken()[0]->Token);
      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/Data/CustomersV3?%24select=PrimaryContactEmail%2CPrimaryContactPhone%2COrganizationNumber&%24filter=CustomerAccount%20eq%20'".$id."'",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
          "authorization: Bearer ".$token->getToken()[0]->Token."",
          "content-type: application/json; odata.metadata=minimal"
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

    public static function insertWebPayLog($ov, $clientCode, $wpayLink, $amount){
      try{
          (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport(); 
          $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT);
          $username = $_SESSION['userInax'];
          $query = $conn->prepare("INSERT INTO [dbo].[AYT_WebPayLog]
                                                  ([Ov]
                                                  ,[ClientCode]
                                                  ,[WpayLink]
                                                  ,[InsertDate]
                                                  ,[User]
                                                  ,[Amount])
                                            VALUES
                                                  ('".$ov."'
                                                  ,'".$clientCode."'
                                                  ,'".$wpayLink."'
                                                  ,'".date("Y-m-d H:i:s")."'
                                                  ,'".$username."'
                                                  ,'".$amount."')");
          $query->execute();
          return $query->rowCount();
      }catch(Exception $e){
        echo 'ocurrio un error';
      }
    }
    
    public static function postLabel($cot, $html, $isOv = false){
      (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      if($isOv){
        $query = $conn->prepare("UPDATE [dbo].[AYT_Paqueterias] 
                                  SET [LabelHTML] = '".$html."',
                                  [Status] = '6' 
                                  WHERE [Ov] = '".$cot."'");
      }else{
        if(self::hasLabel($cot)){
          $query = $conn->prepare("UPDATE [dbo].[AYT_Paqueterias] 
                                  SET [LabelHTML] = '".$html."'
                                  WHERE [Cot] = '".$cot."'");
        }else{
          $query = $conn->prepare("INSERT INTO [dbo].[AYT_Paqueterias]
                                        ([Cot],[LabelHTML])
                                  VALUES
                                        ('".$cot."','".$html."')");
        }
      }
      $query->execute();
      echo json_encode(array('success'=>'true'));
    }

    public static function updateOvLable($ov,$cot, $clientname, $responsible, $site){
      (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $query = $conn->prepare("UPDATE [dbo].[AYT_Paqueterias]
                                SET  [Ov] = '".$ov."'
                                    ,[ClientName] = '".$clientname."'
                                    ,[Responsible] = '".$responsible."'
                                    ,[SiteId] = '".$site."'
                                    ,[Status] = '1'
                                    ,[UpdateDateTime] = '".date("Y-m-d H:i:s")."'
                              WHERE [Cot] = '".$cot."'
                              ");
      $query->execute();                        
    }

    public static function getPromoCodes(){
      (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();      
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $now = date('Y-m-d');
      $query = $conn->prepare("SELECT * FROM [AYT_PromoCodes] WHERE [ExpireDate] >= '".$now."' AND [StartDate] <= '".$now."' AND [Enabled] = 1 ");
      $query->execute();
      return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getCotPromo($cotId){
      (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();      
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $now = date('Y-m-d');
      $query = $conn->prepare("SELECT Ov,PromoCode FROM [AYT_PromoRedeemedLog] WHERE Ov = '".$cotId."' AND PromoCode != '' ORDER BY Id DESC");
      $query->execute();
      $foundRows = $query->fetchAll(PDO::FETCH_ASSOC);
      if(empty($foundRows)){
        return false;
      }else{
        return $foundRows;
      }
    }

    public static function hasLabel($docID){
      (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();      
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $now = date('Y-m-d');
      $query = $conn->prepare("SELECT * FROM [AYT_Paqueterias] WHERE [Cot] = '".$docID."'");
      $query->execute();
      $foundRows = $query->fetchAll(PDO::FETCH_ASSOC);
      if(empty($foundRows)){
        return false;
      }else{
        return true;
      }
    }

    public function removePromo($documentId){
      (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();   
      $query = $conn->prepare("DELETE FROM [dbo].[AYT_PromoRedeemedLog] WHERE Ov = '".$documentId."'");
      $query->execute();
    }

    public static function getmontosMinimos(){
      (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $queryMontosMin = " SELECT DISTINCT STUFF((SELECT  ',' + CONVERT(VARCHAR(max),monto_minimo)
                                      FROM AYT_CargoTarjetaCredito T0
                                      WHERE  T0.valor_porc > 0
                                  FOR XML PATH('')), 1, 1, '') AS montos_minimos
                          FROM AYT_CargoTarjetaCredito T1;";
      $query = $conn->query($queryMontosMin);
      $query->execute();
      $result = $query->fetchAll(PDO::FETCH_ASSOC);
      $arrResult = explode(',', $result[0]['montos_minimos']);
      return $arrResult;
    }

    public static function GetEventosCalendario(){
      (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $queryEvTecs = " SELECT  T0.nombre_tecnico AS title
                                  , T1.color AS backgroundColor
                                  , T0.fecha_ini AS [start]
                                  , T0.fecha_fin AS [end]
                                  , T0.factura
                                  , dbo.AYT_GetServiceName(t0.tipoServicio) AS servicio
                                  , T0.equipo
                                  , dbo.AYT_GetEstadoName(T0.estado) AS estado
                                  , dbo.AYT_GetCiudadName(T0.ciudad) AS ciudad
                          FROM AYT_Calendario_Tecnicos_Servicio T0
                          INNER JOIN AYT_Calendario_Tecnicos_ColorTecnico T1 ON (T1.tecnico = T0.tecnico);";
      $query = $conn->query($queryEvTecs);
      $query->execute();
      $result = $query->fetchAll(PDO::FETCH_ASSOC);
      $result = Application_Model_InicioModel::encode_items($result);
      return $result;
    }

    public static function getTecnicos(){
      $curl = curl_init();
      $token = new Token();
      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/Data/Employees?%24filter=KnownAs%20eq%20'Tecnico'&%24select=PersonnelNumber%2CName",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
          "authorization: Bearer ".$token->getToken()[0]->Token."",
          "content-type: application/json; odata.metadata=minimal"
          ),
        ));

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);

      if ($err) {
        return "cURL Error #:" . $err;
      } else {
        $response = json_decode($response);
        $tecnicos = [];
        foreach($response->value AS $Data){
          array_push($tecnicos, array('value' => $Data->PersonnelNumber,'label' => $Data->Name));
        }
        return $tecnicos;
      }
    }

    public static function getInvoiceHeader($invoice){
      $curl = curl_init();
      $token = new Token();
      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesInvoiceHeaders?%24filter=InvoiceNumber%20eq%20'".$invoice."'",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
          "authorization: Bearer ".$token->getToken()[0]->Token."",
          "content-type: application/json; odata.metadata=minimal"
          ),
        ));

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);

      if ($err) {
        return "cURL Error #:" . $err;
      } else {
        $result = json_decode($response);        
        return $result;
      }
    }

    public static function isInUse($invoice){
      (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $queryExiste = " EXECUTE InUseInvoice '$invoice';";
      $query = $conn->prepare($queryExiste);
      $query->execute();
      $resultExiste = $query->fetchAll(PDO::FETCH_ASSOC);
      if ($resultExiste[0]['cuantasFact'] > 0){
          return true;
        }else{
          return false;
        }
    }

    public static function getSalesOrderHeader($ov){
      $curl = curl_init();
      $token = new Token();
      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesOrderHeadersV2?%24select=OrderingCustomerAccountNumber%2COrderTakerPersonnelNumber%2CSalesOrderName&%24filter=SalesOrderNumber%20eq%20'".$ov."'",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
          "authorization: Bearer ".$token->getToken()[0]->Token."",
          "content-type: application/json; odata.metadata=minimal"
          ),
        ));

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);

      if ($err) {
        return "cURL Error #:" . $err;
      } else {
        $result = json_decode($response);
        return $result;
      }
    }

    public static function getSalesOrderLines($ov){
      $curl = curl_init();
      $token = new Token();
      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesOrderLines?%24select=ItemNumber%2CShippingWarehouseId%2CSalesUnitSymbol%2COrderedSalesQuantity%2CShippingSiteId%2CLineAmount%2CSTF_Category%2CLineDescription&%24filter=SalesOrderNumber%20eq%20'".$ov."'",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
          "authorization: Bearer ".$token->getToken()[0]->Token."",
          "content-type: application/json; odata.metadata=minimal"
          ),
        ));

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);

      if ($err) {
        return "cURL Error #:" . $err;
      } else {
        $result = json_decode($response);
        return $result;
      }
    }

    public static function getOrderTaker($personnelNumber){
      $curl = curl_init();
      $token = new Token();
      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/Data/Employees?%24filter=PersonnelNumber%20eq%20'" . $personnelNumber . "'&%24select=PersonnelNumber%2CKnownAs%2CNameAlias,OfficeLocation",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
          "authorization: Bearer ".$token->getToken()[0]->Token."",
          "content-type: application/json; odata.metadata=minimal"
          ),
        ));

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);

      if ($err) {
        return "cURL Error #:" . $err;
      } else {
        $result = json_decode($response);
        return $result;
      }
    }

    public static function SaveService($servicio){
      (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $queryFolio = " SELECT siguiente FROM AYT_Calendario_Tecnicos_Folio WHERE tipo = 'Servicio';";
      $query = $conn->prepare($queryFolio);
      $query->execute();
      $resultFolio = $query->fetchAll(PDO::FETCH_ASSOC);
      $comentario = str_replace("\n", " ", $servicio['comentario']);
      $equipo = str_replace('\'', ' ft.', $servicio['equipo']);
      $equipo = str_replace('\'\'', ' inch.', $equipo);
      $equipo = str_replace('"', ' inch.', $equipo);

      $queryServicios = "INSERT INTO AYT_Calendario_Tecnicos_Servicio ( folio,vendedor,nombre_vendedor,equipo,codigoCliente,nombreCliente,estado,ciudad,
                                                                        factura,fechaCreacion,status,comentario,tipoServicio)
                          VALUES('".$resultFolio[0]['siguiente']."','".$servicio['vendedor']."','".$servicio['nombre_vendedor']."','".$equipo."',
                          '".$servicio['codigoCliente']."','".$servicio['nombreCliente']."','".$servicio['estado']."','".$servicio['ciudad']."',
                          '".$servicio['factura']."',GETDATE(),2,'".htmlentities($comentario)."','".$servicio['tipoServicio']."')";
      $resourceServicios = $conn->prepare($queryServicios);
      $resourceServicios->execute();
      $resultServicios = $resourceServicios->rowCount();
      if ($resultServicios > 0){
        $queryFolio2 = " UPDATE AYT_Calendario_Tecnicos_Folio SET actual = siguiente, siguiente = (siguiente + 1);";
        $query = $conn->prepare($queryFolio2);
        $query->execute();
        $resultFolio2 = $query->rowCount();
        if ($resultFolio2 > 0){
          return 1;
        }
      }else{
        return -1;
      }
    }

    ///////////////cyr////////////////////// 03-05-2021
    public static function guardarCYR($contact,$customer,$custName){
      (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $existeInfo = self::getInfoCYR($customer);
      $correo = $contact[0]['Locator'];
      $telefono = $contact[1]['Locator'];
      if ($existeInfo['existe']){
        $querySaveCYRInfo = "UPDATE AYT_CyR_Info SET cyrCorreo = '$correo', cyrTelefono = '$telefono' WHERE customerAccount = '$customer';";
      }else{
        $querySaveCYRInfo = "INSERT INTO AYT_CyR_Info (customerAccount, customerName, cyrCorreo, cyrTelefono) VALUES ('$customer','$custName','$correo','$telefono');";
      }
      $query = $conn->prepare($querySaveCYRInfo);
      $query->execute();
      $saveStatus = $query->rowCount();

      return $saveStatus;
    }

    public static function getInfoCYR($customer){
      $token = new Token();

      (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $queryParty = "SELECT * FROM AYT_CyR_Info WHERE customerAccount = '$customer'";
      $query = $conn->prepare($queryParty);
      $query->execute();
      $clientCYRInfo = $query->fetchAll(PDO::FETCH_ASSOC);

      $info = array('existe' => false,'correo' => '', 'telefono' => '');
      if (!empty($clientCYRInfo)){
        $info['correo'] = $clientCYRInfo[0]['cyrCorreo'];
        $info['telefono'] = $clientCYRInfo[0]['cyrTelefono'];
        $info['existe'] = true;
      }
      return $info;
    }

    public static function ApplyCreditRequest($creditRequest, $files){
      $token = new Token();
      $creditRequest = (array)$creditRequest;
      $ov = $creditRequest['OV'];

      $curl = curl_init();
      curl_setopt_array($curl, [
        CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesOrderHeadersV2?%24filter=SalesOrderNumber%20eq%20'".$ov."'",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_COOKIE => "ApplicationGatewayAffinity=e7fb295f94cb4b5e0cd1e2a4712e4a803fc926342cc4ecca988f29125dbd4b04; ARRAffinity=dcb76979b784a1f85f17f2b977b7d851aad42c68a3aba6e28de4029069113332",
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
        $resultOv = json_decode($response);
        $date = new DateTime($resultOv->value[0]->OrderCreationDateTime);
        $date->setTimezone(new DateTimeZone('America/Chihuahua'));

        (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $queryCheckCR = "SELECT COUNT(Id) AS cuantos, Id
                         FROM AYT_CreditRequest
                         WHERE Ov = '".$creditRequest['OV']."' 
                         AND ClientCode = '".$creditRequest['ClientCode']."' 
                         AND OrderTakerCode = '".$creditRequest['OrderTakerCode']."'
                         AND Zone = '".$creditRequest['Zone']."'
                         GROUP BY Id;";
        $query2 = $conn->prepare($queryCheckCR);
        $query2->execute();
        $resultCuantos = $query2->fetchAll(PDO::FETCH_ASSOC);

        $resultInsert = 0;
        if ($resultCuantos[0]['cuantos'] == 0){
          $queryInsertCredit = "INSERT INTO [dbo].[AYT_CreditRequest]
                                       ([Ov]
                                       ,[RequestType]
                                       ,[ClientCode]
                                       ,[ClientName]
                                       ,[OrderTakerCode]
                                       ,[InsertDate]
                                       ,[Status]
                                       ,[OvAmmount]
                                       ,[OrderTakerName]
                                       ,[Zone]
                                       ,[OrderCreationDateTime])
                                OUTPUT inserted.Id
                                VALUES
                                       ('".$creditRequest['OV']."'
                                       ,'".$creditRequest['RequestType']."'
                                       ,'".$creditRequest['ClientCode']."'
                                       ,'".$creditRequest['ClientName']."'
                                       ,'".$creditRequest['OrderTakerCode']."'
                                       , GETDATE()
                                       ,'".$creditRequest['Status']."'
                                       ,'".$creditRequest['OvAmount']."'
                                       ,'".$creditRequest['OrderTakerName']."'
                                       ,'".$creditRequest['Zone']."'
                                       ,'".$date->format('Y-m-d H:i:s')."');";
          $query = $conn->prepare($queryInsertCredit);
          $query->execute();
          $resultRows = $query->rowCount();
          $resultInsert = $query->fetchAll(PDO::FETCH_ASSOC);
        }else{
          $resultInsert = $resultCuantos[0]['cuantos'];
        }

        return ($resultInsert > 0) ? $resultInsert[0]['Id'] : 'Error';
      }
    }
}   
