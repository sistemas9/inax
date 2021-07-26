<?php 
class NTLMSoapClient extends SoapClient {
    function __doRequest($request, $location, $action, $version) {
        $headers = array(
            'Method: POST',
            'Connection: Keep-Alive',
            'User-Agent: PHP-SOAP-CURL',
            'Content-Type: text/xml; charset=utf-8',
            'SOAPAction: "'.$action.'"',
        );
        $this->__last_request_headers = $headers;
        $ch = curl_init($location);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_NTLM);
        curl_setopt($ch, CURLOPT_USERPWD, USERPWD);
        $response = curl_exec($ch);
        return $response;
    }

    function __getLastRequestHeaders() {
        return implode("\n", $this->__last_request_headers)."\n";
    }
}

class NTLMStream{
    private $path;
    private $mode;
    private $options;
    private $opened_path;
    private $buffer;
    private $pos;
    /**
     * Open the stream
      *
     * @param unknown_type $path
     * @param unknown_type $mode
     * @param unknown_type $options
     * @param unknown_type $opened_path
     * @return unknown
     */
    public function stream_open($path, $mode, $options, $opened_path) {
        $this->path = $path;
        $this->mode = $mode;
        $this->options = $options;
        $this->opened_path = $opened_path;
        $this->createBuffer($path);
        return true;
    }
    /**
     * Close the stream
     *
     */
    public function stream_close() {
        curl_close($this->ch);
    }
    /**
     * Read the stream
     *
     * @param int $count number of bytes to read
     * @return content from pos to count
     */
    public function stream_read($count) {
        if(strlen($this->buffer) == 0) {
            return false;
        }
        $read = substr($this->buffer,$this->pos, $count);
        $this->pos += $count;
        return $read;
    }
    /**
     * write the stream
     *
     * @param int $count number of bytes to read
     * @return content from pos to count
     */
    public function stream_write($data) {
        if(strlen($this->buffer) == 0) {
            return false;
        }
        return true;
    }
    /**
     *
     * @return true if eof else false
     */
    public function stream_eof() {
        return ($this->pos > strlen($this->buffer));
    }
    /**
     * @return int the position of the current read pointer
     */
    public function stream_tell() {
        return $this->pos;
    }
    /**
     * Flush stream data
     */
    public function stream_flush() {
        $this->buffer = null;
        $this->pos = null;
    }
    /**
     * Stat the file, return only the size of the buffer
     *
     * @return array stat information
     */
    public function stream_stat() {
        $this->createBuffer($this->path);
        $stat = array(
            'size' => strlen($this->buffer),
        );
        return $stat;
    }
    /**
     * Stat the url, return only the size of the buffer
     *
     * @return array stat information
     */
    public function url_stat($path, $flags) {
        $this->createBuffer($path);
        $stat = array(
            'size' => strlen($this->buffer),
        );
        return $stat;
    }
    /**
     * Create the buffer by requesting the url through cURL
     *
     * @param unknown_type $path
     */
    private function createBuffer($path) {
        if($this->buffer) {
            return;
        }
        $this->ch = curl_init($path);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_NTLM);
        curl_setopt($this->ch, CURLOPT_USERPWD, USERPWD);
        $this->buffer = curl_exec($this->ch);
        $this->pos = 0;
    }
}

class Metodos{
    private $cliente;
    private static $db;
    
    public function __construct(){
        
    }
    
     
    private function cortarDecimales($numero,$cantidadDecimales){
        return substr($numero, 0, (strpos($numero, '.')+$cantidadDecimales+1) );
    }
    
    private static function cargarConexionDB(){
        include_once APPLICATION_PATH.'/models/Userinfo.php';
        Metodos::$db = new Application_Model_Userinfo();
    }

    public static function getPurpouse($cliente){
      $token = new Token();
      curl_setopt_array(CURL1, array(
        CURLOPT_URL => "https://".DYNAMICS365."/Data/CustomersV3?%24filter=CustomerAccount%20eq%20'".$cliente."'&%24select=SATPurpose_MX",
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

      if ($err){
        return 'G01';
      }else{
        $proposito = json_decode($response);
        if (!empty($proposito->value[0]->SATPurpose_MX)){
          return $proposito->value[0]->SATPurpose_MX;
        }else{
          return 'G01';
        }
      }
    }

    public static function ConvertirCotizacion($parametro){
        Metodos::cargarConexionDB();
        try{   
            //$this->InicializarWebservice();
            // $OV = $this->cliente->SetSalesQuotationToSalesOrder($parametro);
            $OV = Metodos::SetSalesQuotationToSalesOrderEntity($parametro);
            Metodos::$db->kardexLog("COT a OV parametros: ".json_encode($parametro)." resultado: ".json_encode(array("msg"=>$OV['result'])),json_encode($parametro),json_encode(array("msg"=>$OV['result'])),1,'convertir COT a OV');
            if ($OV['result'] == 'Fail'){
              return array("status"=>"Fallo","msg"=>'Existen Articulos sin existencias!.',"cambio"=>$OV['cambio']);
            }else{
              if($OV['result'] == 'NOOV'){
                return array("status"=>"Fallo","msg"=>'No se creo la OV',"cambio"=>$OV['cambio']);
              } else if($OV['result'] == 'NODIR'){
                return array("status"=>"Fallo","msg"=>'Falta direccion en la cotización',"cambio"=>$OV['cambio']);
              }else{
                return array("status"=>"Exito","msg"=>$OV['result'],"cambio"=>$OV['cambio'],'proposito'=>$OV['proposito']);
              }
            }
        }
        catch(Exception $e){ 
            $q= Metodos::$db->_adapter->query(LAST_ERROR);
            $q->execute();
            $res=$q->fetchAll(PDO::FETCH_ASSOC);
            $msg2="";
            $msg = '<b>Exception en Dynamics: ';
            foreach ($res AS $Data){
                $msg2.=mb_convert_encoding($Data['DESCRIPTION'], "UTF-8");
                $msg .= mb_convert_encoding($Data['DESCRIPTION'], "UTF-8").'<br>';
            }
            $msg .= '</b>';
            $st=1;
            if(empty($msg2)){
                $st=2;
            }
            Metodos::$db->kardexLog("COT a OV parametros: ".json_encode($parametro)." resultado: ".$msg,  json_encode($parametro).' - '.$msg, json_encode($e),$st,'convertir COT a OV');            
            return array("status"=>"Fallo","msg"=>$msg);
        }
    }

    public static function SetSalesQuotationToSalesOrderEntity($parametro){
      try{
        $token = new Token();
        // $tokenTemp = $token->getToken();
        // $this->token = $tokenTemp[0]->Token;
        $db       = new Application_Model_UserinfoMapper();
        $adapter  = $db->getAdapter();
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
                  );

        /////////////consultar las lineas para traer existencias/////////////////////////////////////
        $datosQuot    = $parametro['_QuotationId'];
        $quotationId  = $datosQuot;
        if (!$_SESSION['offline']){
          curl_setopt_array(CURL1, array(
            CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesQuotationLines?%24filter=SalesQuotationNumber%20eq%20'".$quotationId."'&%24select=ItemNumber%2CLineDescription%2CSalesUnitSymbol%2CRequestedSalesQuantity%2CLineAmount%2CShippingSiteId%2CShippingWarehouseId%2CInventoryLotId%2CdataAreaId%2CFixedPriceCharges",
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

          $responseLineas = curl_exec(CURL1);
          $err = curl_error(CURL1);


          curl_setopt_array(CURL1, array(
            CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesQuotationHeaders?%24filter=SalesQuotationNumber%20eq%20'".$quotationId."'&%24select=FormattedDeliveryAddress",
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

          $checkdir = curl_exec(CURL1);
          $err = curl_error(CURL1);

          // print_r($checkdir);exit();
          $checkdir = json_decode($checkdir);
          // print_r($checkdir->value[0]->FormattedDeliveryAddress);exit();
          if($checkdir->value[0]->FormattedDeliveryAddress=="MEX"||$checkdir->value[0]->FormattedDeliveryAddress==""){
              return array('result'=>"NODIR",'cambio'=>false);
          }


        }else{
          ///////////////////////////////get lineas COT///////////////////////////////////////////////////////////////////
          $queryGetLineas = "SELECT * FROM CotizacionesLinesOffline WHERE SalesQuotationNumber = '".$quotationId."';";
          $query = $adapter->query($queryGetLineas);
          $query->execute();
          $resultLineas = $query->fetchAll(PDO::FETCH_OBJ);
          $responseLineas = [];
          $responseLineas['value'] = $resultLineas;
          $responseLineas = json_encode($responseLineas);
          //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        }

        $resultLineas = json_decode($responseLineas);
        $noExiste = false;
        if (isset($resultLineas->value) && !empty($resultLineas->value)){
          foreach($resultLineas->value as $Data){
            $item = $Data->ItemNumber;
            $sitio = $Data->ShippingSiteId;
            $almacen = $Data->ShippingWarehouseId;
            $cant = $Data->RequestedSalesQuantity;

            if(!$_SESSION['offline']){
              curl_setopt_array(CURL1, array(
                // CURLOPT_URL => "https://".DYNAMICS365."/Data/WarehouseInventoryStatusesOnHand?%24filter=(InventoryWarehouseId%20ne%20''%20)%20and%20ItemNumber%20eq%20'".$item."'%20and%20dataAreaId%20eq%20'".COMPANY."'%20and%20InventoryStatusId%20eq%20'Disponible'%20and%20InventorySiteId%20eq%20'".$sitio."'%20and%20InventoryWarehouseId%20eq%20'".$almacen."'&%24select=ItemNumber%2CAvailableOnHandQuantity%2CInventorySiteId%2CInventoryWarehouseId%2COnHandQuantity",
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
              $err = curl_error(CURL1);
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
              //////////////si el sitio no eciste en la lista disponible////////////////////////////////////
              $existe = false;
              foreach($resultClean as $Data){
                if ($Data->InventorySiteId == $sitio){
                  $existe = true;
                }
              }
              if (!$existe){
                array_push($resultClean, (object)array(   'ItemNumber'    => $item
                                                          ,'AvailableOnHandQuantity' => '0.00'
                                                          ,'InventorySiteId'      => $sitio
                                                          ,'InventoryWarehouseId'    => $almacen
                                                          ,'OnHandQuantity'  => '0.00'
                                          ));
              }
              /////////////////////////////////////////////////////////////////////////////////////////////////
              $result2->value = $resultClean;
              $response = json_encode($result2);
            }else{
              $queryExistencias = "SELECT * FROM existenciasInax WHERE ItemNumber = '".$item."' AND dataAreaId = '".COMPANY."' AND InventorySiteId = '".$sitio."' AND InventoryWarehouseId = '".$almacen."' AND InventoryStatusId = 'Disponible';";
              $query = $adapter->query($queryExistencias);
              $query->execute();
              $resultExistencias = $query->fetchAll(PDO::FETCH_OBJ);
              $response = [];
              $response['value'] = $resultExistencias;
              $response = json_encode($response);
            }

            $existencias = json_decode($response); 
            if (isset($existencias->value) && !empty($existencias->value)){
              $existencias = $existencias->value;
              // $existencias = Application_Model_InicioModel::getExistenciasEntity($item,$sitio,$almacen);
              $bandServ = false;
              foreach($existencias as $DataExiste){
                (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $queryServicio = "SELECT COUNT(*) AS Existe FROM articulos WHERE ProductType = 'Service' AND ItemNumber = '".$DataExiste->ItemNumber."'";
                $query = $conn->prepare($queryServicio);
                $query->execute();
                $result = $query->fetchAll(PDO::FETCH_ASSOC);
                if ($result[0]['Existe'] > 0){
                  $bandServ = true;
                }
                if ( trim($DataExiste->InventorySiteId) == trim($sitio) && trim($DataExiste->InventoryWarehouseId) == trim($almacen)){
                  if( ((float)$DataExiste->AvailableOnHandQuantity == 0) || ((float)$DataExiste->AvailableOnHandQuantity < $cant) ){
                    if (!$bandServ){
                      $noExiste = true;
                    }
                    break;
                  }
                }
              }
            }else{
              (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();
              $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
              $query = $conn->prepare("SELECT * FROM [articulos] WHERE ItemNumber = '".$item."';");
              $query->execute();
              $result = $query->fetchAll();
              $noExiste = true;
              if (!empty($result)){
                $noExiste = false;
              }
            }
            if ($noExiste){
              break;
            }
          }
        }else{
          $noExiste = true;
        }

        ///////////////////////////////////////////////////////////////////////////////////////////////////
        if (!$noExiste){
          if ($parametro != ''){
              //$datos = explode(',',$parametro['_QuotationId']);
              $_AccountNum        = $parametro['cliente'];
              $quotationId        = $parametro['_QuotationId'];
              $dataAreaId         = $parametro['company'];
              $sitio              = $parametro['sitio'];
              $impuestos          = $parametro['impuestos'];
              $dlvMode            = $parametro['dlvMode'];
              $condiEntrega       = $parametro['condiEntrega'];
              $moneda             = $parametro['moneda'];
              $metodoPagoCode     = $parametro['metodoPagoCode'];
              // $proposito          = $parametro['proposito'];
              $proposito          = Metodos::getPurpouse($parametro['cliente']);
              $paymentTermName    = $parametro['paymentTermName'];
              $comentarioCabecera = $parametro['comentarioCabecera'];
              $promoCodeFreight   = $parametro['FreightZone'];

              $postFields = "{'_AccountNum' : '".$_AccountNum."','quotationId' : '".$quotationId."','dataAreaId' : '".$dataAreaId."'}";
              $tipoPago='PUE';
              if($metodoPagoCode=="99"){
                $tipoPago='PPD';
              }else{
                $tipoPago='PUE';
              }
              if (!$_SESSION['offline']){
                $curl = curl_init();

                curl_setopt_array($curl, array(
                  CURLOPT_URL => "https://".DYNAMICS365."/api/services/STF_INAX/STF_Cotizacion/SetSalesQuotationToSalesOrderV2",
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => "",
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 30,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => "POST",
                  CURLOPT_POSTFIELDS => $postFields,
                  CURLOPT_HTTPHEADER => array(
                    "authorization: Bearer ".$token->getToken()[0]->Token."",
                    "content-type: application/json"
                  ),
                ));

                $response = curl_exec($curl);
                $response = str_replace('\\', '', $response);
                $response = substr($response, 1,-1);
                $response = json_decode($response);
                $err = curl_error($curl);
                $orventa  = $response->response;
              }else{
                ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                $queryInsertHeader = "INSERT INTO dbo.SalesOrderHeadersOffline (dataAreaId,SalesOrderNumber,OrderingCustomerAccountNumber,SalesQuotationNumber,DefaultShippingSiteId,
                                                                                SalesTaxGroupCode,DeliveryModeCode,DeliveryTermsCode,CustomerPaymentMethodName,SATPaymMethod_MX,SATPurpose_MX,
                                                                                PaymentTermsName,CustomersOrderReference,CreatedDatetime,CreadoPor,CurrencyCode,BusinessUnit,
                                                                                OrderTakerPersonnelNumber,OrderResponsiblePersonnelNumber,SalesOrderOriginCode,Status)
                                      VALUES ('".COMPANY."',dbo.getNextSalesOrderNumber(),'".$_AccountNum."','".$quotationId."','".$sitio."','".$impuestos."',
                                              '".$dlvMode."','".$condiEntrega."','".$metodoPagoCode."','".$tipoPago."','".$proposito."','".$paymentTermName."',
                                              '".$comentarioCabecera."',GETDATE(),'".$_SESSION['userInax']."','".$moneda."','".$sucursales[$sitio]."',(SELECT QuotationTakerPersonnelNumber FROM CotizacionesHeadersOffline WHERE SalesQuotationNumber = '".$quotationId."'),
                                              (SELECT QuotationResponsiblePersonnelNumber FROM CotizacionesHeadersOffline WHERE SalesQuotationNumber = '".$quotationId."'),
                                              (SELECT SalesOrderOriginCode FROM CotizacionesHeadersOffline WHERE SalesQuotationNumber = '".$quotationId."'),0);";

                $query                    = $adapter->prepare($queryInsertHeader);
                $query->execute();
                $result                   = $query->rowCount();
                $stmt                     = $adapter->query("SELECT * FROM SalesOrderHeadersOffline WHERE idSalesOrderHeaderOff = @@IDENTITY");
                $lastInsert               = $stmt->fetchAll();
                $queryUpdateCotizaciones  = "UPDATE CotizacionesHeadersOffline SET GeneratedSalesOrderNumber = '".$lastInsert[0]['SalesOrderNumber']."' WHERE SalesQuotationNumber = '".$quotationId."';";
                $query2                   = $adapter->prepare($queryUpdateCotizaciones);
                $query2->execute();
                $result2                  = $query2->rowCount();
                /////////////////////////////////////////////////////////////////////////////////////////////////////////////////

                $DefaultShippingSiteId      = ($lastInsert[0]['DefaultShippingSiteId'] == null)?'1':'0';
                $SalesTaxGroupCode          = ($lastInsert[0]['SalesTaxGroupCode'] == null)?'1':'0';
                $DeliveryModeCode           = ($lastInsert[0]['DeliveryModeCode'] == null)?'1':'0';
                $DeliveryTermsCode          = ($lastInsert[0]['DeliveryTermsCode'] == null)?'1':'0';
                $CurrencyCode               = ($lastInsert[0]['CurrencyCode'] == null)?'1':'0';
                $CustomerPaymentMethodName  = ($lastInsert[0]['CustomerPaymentMethodName'] == null)?'1':'0';
                $SATPaymMethod_MX           = ($lastInsert[0]['SATPaymMethod_MX'] == null)?'1':'0';
                $SATPurpose_MX              = ($lastInsert[0]['SATPurpose_MX'] == null)?'1':'0';
                $PaymentTermsName           = ($lastInsert[0]['PaymentTermsName'] == null)?'1':'0';
                $BusinessUnit               = ($lastInsert[0]['BusinessUnit'] == null)?'1':'0';
                $response = '{"DefaultShippingSiteId":"'.$DefaultShippingSiteId.'",
                              "SalesTaxGroupCode":"'.$SalesTaxGroupCode.'",
                              "DeliveryModeCode":"'.$DeliveryModeCode.'",
                              "DeliveryTermsCode":"'.$DeliveryTermsCode.'",
                              "CurrencyCode":"'.$CurrencyCode.'",
                              "CustomerPaymentMethodName":"'.$CustomerPaymentMethodName.'",
                              "SATPaymMethod_MX":"'.$SATPaymMethod_MX.'",
                              "SATPurpose_MX":"'.$SATPurpose_MX.'",
                              "PaymentTermsName":"'.$PaymentTermsName.'",
                              "BusinessUnit":"'.$BusinessUnit.'"
                }';
                $response = json_decode($response);
                if ($result > 0){
                  $orventa = $lastInsert[0]['SalesOrderNumber'];
                  /////////////////////////////////////inserta lineas de ov////////////////////////////////////////////////////////
                  foreach($resultLineas->value as $Data){
                    $item = $Data->ItemNumber;
                    $sitio = $Data->ShippingSiteId;
                    $almacen = $Data->ShippingWarehouseId;
                    $queryInsertLineas = "INSERT INTO SalesOrderLinesOffline (dataAreaId,SalesOrderNumber,ItemNumber,OrderedSalesQuantity,ShippingSiteId,ShippingWarehouseId,
                                                                              FixedPriceCharges,SalesTaxGroupCode,STF_Category,LineAmount,CreatedDatetime,InventoryLotId,LineCreationSequenceNumber,SalesUnitSymbol)
                                          VALUES ('".COMPANY."','".$orventa."','".$Data->ItemNumber."','".$Data->RequestedSalesQuantity."','".$Data->ShippingSiteId."','".$Data->ShippingWarehouseId."',
                                                  '".$Data->FixedPriceCharges."','".$Data->SalesTaxGroupCode."','".$Data->STF_Category."','".$Data->LineAmount."',GETDATE(),(SELECT CONCAT('OFFOV-',".$Data->LineCreationSequenceNumber.")),
                                                  '".$Data->LineCreationSequenceNumber."','".$Data->SalesUnitSymbol."');";
                    $query = $adapter->prepare($queryInsertLineas);
                    $query->execute();
                    $resultInsertLineas = $query->rowCount();
                    //////////////////////////////actualizar cantidades en existencias/////////////////////////////////////////////
                    $queryUpdateExistencias = " UPDATE existenciasInax 
                                                SET AvailableOnHandQuantity = (AvailableOnHandQuantity - ".$Data->RequestedSalesQuantity."),
                                                TotalAvailableQuantity = (TotalAvailableQuantity - ".$Data->RequestedSalesQuantity.")
                                                WHERE ItemNumber = '".$Data->ItemNumber."' AND InventorySiteId = '".$sitio."' AND InventoryWarehouseId = '".$almacen."';"; 
                    $queryUPDExist = $adapter->prepare($queryUpdateExistencias);
                    $queryUPDExist->execute();
                    $resultUPDExist = $queryUPDExist->rowCount();
                    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
                  }
                  if ($resultInsertLineas > 0){
                    $stmt = $adapter->prepare("SELECT * FROM SalesOrderLinesOffline WHERE SalesOrderNumber = '".$orventa."';");
                    $stmt->execute();
                    $lastInsertLineas = $stmt->fetchAll(PDO::FETCH_OBJ);
                    $resultLineasOV = [];
                    $resultLineasOV['value'] = $lastInsertLineas;
                    $resultLineasOV = json_encode($resultLineasOV);
                  }
                  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                }
              }

              if( strpos($orventa,"OV") < -1 ){
                return array('result'=>"NOOV",'cambio'=>false);
              }

              if(!$_SESSION['offline']){
                curl_setopt_array($curl, array(
                  CURLOPT_URL => "https://".DYNAMICS365."/data/SalesOrderHeadersV2(dataAreaId=%27".COMPANY."%27,SalesOrderNumber=%27".$orventa."%27)",
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
                                          \"PaymentTermsName\":\"".$paymentTermName."\",
                                          \"CustomersOrderReference\" : \"".$comentarioCabecera."\",
                                          \"FreightZone\":\"".$promoCodeFreight."\"
                                        }",
                  CURLOPT_HTTPHEADER => array(
                    "authorization: Bearer ".$token->getToken()[0]->Token."",
                    "content-type: application/json"
                  ),
                ));
                $response2 = curl_exec($curl);
                $err = curl_error($curl);
              }else{
                $queryUpdateHeader = "UPDATE SalesOrderHeadersOffline
                                      SET DefaultShippingSiteId = '".$sitio."',
                                      SalesTaxGroupCode = '".$impuestos."',
                                      DeliveryModeCode = '".$dlvMode."',
                                      DeliveryTermsCode = '".$condiEntrega."',
                                      CustomerPaymentMethodName = '".$metodoPagoCode."',
                                      SATPaymMethod_MX = '".$tipoPago."',
                                      SATPurpose_MX = '".$proposito."',
                                      PaymentTermsName = '".$paymentTermName."',
                                      CustomersOrderReference = '".$comentarioCabecera."'
                                      WHERE SalesOrderNumber = '".$orventa."';";
                $query = $adapter->prepare($queryUpdateHeader);
                $result = $query->rowCount();
                if ($result > 0){
                  $response2 = '';
                }
              }

              $noFaltan = Metodos::checkOV($response);

              if (!$_SESSION['offline']){
                curl_setopt_array($curl, array(
                 CURLOPT_URL => "https://".DYNAMICS365."/data/SalesOrderHeadersV2?%24filter=SalesOrderNumber%20eq%20'".$orventa."'&%24select=DefaultShippingSiteId%2CSalesTaxGroupCode%2CDeliveryModeCode%2CDeliveryTermsCode%2CCustomerPaymentMethodName%2CSATPaymMethod_MX%2CSATPurpose_MX%2CPaymentTermsName%2CCustomersOrderReference",
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

                $responseCheck = curl_exec($curl);
                $err = curl_error($curl);
                $propositoOV = json_decode($responseCheck);
              }else{
                $queryGetSalesOrder = " SELECT  DefaultShippingSiteId,SalesTaxGroupCode,DeliveryModeCode,DeliveryTermsCode
                                                ,CustomerPaymentMethodName,SATPaymMethod_MX,SATPurpose_MX,PaymentTermsName,CustomersOrderReference 
                                        FROM SalesOrderHeadersOffline 
                                        WHERE SalesOrderNumber = '".json_decode($orventa)."';";
                $query = $adapter->query($queryGetSalesOrder);
                $query->execute();
                $responseCheck = $query->fetchAll(PDO::FETCH_OBJ);
                $responseCheck = json_encode($responseCheck[0]);
              }
              Metodos::$db->kardexLog("Respuesta de GET SalesOrder: ".$orventa,
                                        $responseCheck,
                                        $orventa,
                                        1,
                                        'Respuesta de GET SalesOrder conversion');        

              // if($responseCheck->value[0]->SATPurpose_MX!=$proposito and $proposito!=""){
              // curl_setopt_array($curl, array(
              // CURLOPT_URL => "https://".DYNAMICS365."/data/SalesOrderHeadersV2(dataAreaId=%27".COMPANY."%27,SalesOrderNumber=%27".$orventa."%27)",
              // CURLOPT_RETURNTRANSFER => true,
              // CURLOPT_ENCODING => "",
              // CURLOPT_MAXREDIRS => 10,
              // CURLOPT_TIMEOUT => 30,
              // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              // CURLOPT_CUSTOMREQUEST => "PATCH",
              // CURLOPT_POSTFIELDS => "{\"SATPurpose_MX\":\"".$proposito."\"}",
              // CURLOPT_HTTPHEADER => array(
              // "authorization: Bearer ".$token->getToken()[0]->Token."",
              // "content-type: application/json"
              // ),
              // ));
              
              // $response28 = curl_exec($curl);
              // $err = curl_error($curl);
              // }

              // if($responseCheck->value[0]->CustomerPaymentMethodName=="" || $responseCheck->value[0]->DeliveryModeCode=="" || $responseCheck->value[0]->DeliveryTermsCode=="" || $responseCheck->value[0]->SATPaymMethod_MX=="" || $responseCheck->value[0]->SATPurpose_MX==""){
              if(!$noFaltan){
                Metodos::$db->kardexLog("Parchar OV por falta de datos: ".$ov,
                                        "{\"sitio\" : \"".$sitio."\",\"impuestos\" : \"".$impuestos."\",\"DeliveryModeCode\":\"".$dlvMode."\",\"condiEntrega\" : \"".$condiEntrega."\",\"moneda\" : \"".$moneda."\",\"SATPaymMethod_MX\":\"".$tipoPago."\",\"CustomerPaymentMethodName\":\"".$metodoPagoCode."\",\"proposito\" : \"".$proposito."\",\"paymentTermName\" : \"".$paymentTermName."\"}",
                                        $orventa,
                                        1,
                                        'Parchar OV por falta de datos');
                Metodos::$db->kardexLog("Respuesta de WS ConvertirCotizacion: ".$ov,
                                        $response,
                                        $orventa,
                                        1,
                                        'Respuesta de WS ConvertirCotizacion Falta');

                if (!$_SESSION['offline']){
                  curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://".DYNAMICS365."/data/SalesOrderHeadersV2(dataAreaId=%27".COMPANY."%27,SalesOrderNumber=%27".$orventa."%27)",
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
                                            \"PaymentTermsName\":\"".$paymentTermName."\",
                                            \"CustomersOrderReference\" : \"".$comentarioCabecera."\"
                                          }",
                    CURLOPT_HTTPHEADER => array(
                    "authorization: Bearer ".$token->getToken()[0]->Token."",
                     "content-type: application/json"
                    ),
                  ));
                  $response2 = curl_exec($curl);
                  $err = curl_error($curl);
                }else{
                  $queryUpdateHeader = "UPDATE SalesOrderHeadersOffline
                                        SET DefaultShippingSiteId = '".$sitio."',
                                        SalesTaxGroupCode = '".$impuestos."',
                                        DeliveryModeCode = '".$dlvMode."',
                                        DeliveryTermsCode = '".$condiEntrega."',
                                        CustomerPaymentMethodName = '".$metodoPagoCode."',
                                        SATPaymMethod_MX = '".$tipoPago."',
                                        SATPurpose_MX = '".$proposito."',
                                        PaymentTermsName = '".$paymentTermName."',
                                        CustomersOrderReference = '".$comentarioCabecera."'
                                        WHERE SalesOrderNumber = '".$orventa."';";
                  $query = $adapter->prepare($queryUpdateHeader);
                  $result = $query->rowCount();

                  if ($result > 0){
                    $response2 = '';
                  }
                }
              }

              if(!$_SESSION['offline']){
                curl_setopt_array(CURL1, array(
                  CURLOPT_URL => "https://".DYNAMICS365."/data/SalesOrderLines?%24filter=SalesOrderNumber%20eq%20'".$orventa."'",
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
                
                $responseCheck2 = curl_exec(CURL1);
                $err = curl_error(CURL1);
                $responseCheck2 = json_decode($responseCheck2);

                curl_setopt_array(CURL1, array(
                  CURLOPT_URL => "https://".DYNAMICS365."/data/SalesQuotationLines?%24filter=SalesQuotationNumber%20eq%20'".$quotationId."'",
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

                curl_close($curl);
              }else{
                $responseCheck2 = json_decode($resultLineasOV);
                $responseCheck  = json_decode($responseLineas);
              }

              $existChange=0;
              $cambio=true;
              $tamano=sizeof($responseCheck2->value);
              $tamano2=sizeof($responseCheck->value);

              foreach ($responseCheck2->value as $value2) {
                foreach ($responseCheck->value as $value) {
                  if($value->ItemNumber==$value2->ItemNumber){
                    //////////////////guarda comentarios////////////////////////////////////////////
                    try{
                      $db = new Application_Model_UserinfoMapper();
                      $adapter = $db->getAdapter();
                      /////////////////////////////////////select comentario de la linea/////////////////////////////////////////
                      $queryComent = $adapter->prepare("SELECT * FROM dbo.AYT_ComentariosLineas WHERE InventoryLotId = '".$value->InventoryLotId."';");
                      $queryComent->execute();
                      $dataCot = $queryComent->fetchAll(PDO::FETCH_ASSOC);                      
                      ///////////////////////////////////////////////////////////////////////////////////////////////////////////
                      $query = $adapter->prepare("INSERT INTO dbo.AYT_ComentariosLineas (inventoryLotId,comentario,tipoDoc,documentId,fechaCreacion,fechaModificacion,usuario,LineSequenceNumber) VALUES('".$value2->InventoryLotId."','".$dataCot[0]['comentario']."','ORDVTA','".$orventa."',GETDATE(),NULL,'".$_SESSION['userInax']."','".$value2->LineCreationSequenceNumber."');");
                      $query->execute();
                      $resultQuery = $query->rowCount();
                      $model = new Application_Model_InicioModel();
                      $model->postDocumentAttachment($dataCot[0]['comentario'],$orventa,$value2->ItemNumber);
                    }catch(Exception $e){
                      print_r($e->getMessage());
                    }
                    ///////////////////////////////////////////////////////////////////////////////
                    if(($value->RequestedSalesQuantity==$value2->OrderedSalesQuantity)&&($value->LineAmount==$value2->LineAmount)&&($value->SalesUnitSymbol==$value2->SalesUnitSymbol)){
                      $existChange+=1;
                    }
                  }
                }
              }

              if($tamano==$existChange&&$tamano==$tamano2){
                $cambio=false;
              }          

              if ($err) {
                $result = "cURL Error #:" . $err;
              } else {
                $result = $orventa;
              }
          }
        }else{
          $result = 'Fail';
          $cambio = false;
        }
        Metodos::$db->kardexLog("Respuesta de WS ConvertirCotizacion: ".$ov,
                                $response,
                                $orventa,
                                1,
                                'Respuesta de WS ConvertirCotizacion');
        return array('result'=>$result,'cambio'=>$cambio,'proposito'=>$propositoOV->value[0]->SATPurpose_MX);
      }catch(exeption $error){
        $result = json_decode($response);
        return array('result'=>$result,'cambio'=>$cambio);
      }
    }

    public static function checkOV($response){
      foreach($response as $key => $value ){
        $valido = true;
        if ($key != 'response'){
          if ($value != '0'){
            $valido = false;
            return $valido;
          }
        }
      }
      return $valido;
    }

    public static function enviarCotizacion($parametro){
        $token = new Token();
        //$tokenTemp = $token->getToken();
        // print_r($token->getToken()[0]->Token);exit();
        // $token = $token->getToken()->$tokenTemp[0]->Token;
        //$datos = explode(',',$parametro['_QuotationId']);
        $quotationId = $parametro;
        $company = COMPANY;

        $postFields = "{'quotationId' : '".$quotationId."','dataAreaId' : '".$company."'}";

        if (!$_SESSION['offline']){
          $curl = curl_init();

          curl_setopt_array($curl, array(
            CURLOPT_URL => "https://".DYNAMICS365."/api/services/STF_INAX/STF_Cotizacion/SetSalesQuotationToSend",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => array(
              "authorization: Bearer ".$token->getToken()[0]->Token."",
              "content-type: application/json"
            ),
          ));

          $response = curl_exec($curl);
          $err = curl_error($curl);

          curl_close($curl);
        }else{
          $response = '"'.$quotationId.'"';
        }

        if ($err) {
          $result = "cURL Error #:" . $err;
          print_r($response);
          exit();
        } else {
          $result = json_decode($response);
        }
        return $result;
    }

    public function getPriceItems($parametros){
        try{
            // $this->cargarConexionDB();
            // $parametros['_company']=COMPANY;
            // $this->InicializarWebservice();
            // $precio = $this->cliente->GetSalesPriceItem($parametros);
            // $priceTemp = $precio->response;
            // $this->db->kardexLog("parametros: ".  json_encode($parametros)." resultado: ".json_encode(array('precio'=>($priceTemp),'precio_iva'=>$priceTemp*1.16)),json_encode($parametros), json_encode(array('precio'=>($priceTemp),'precio_iva'=>$priceTemp*1.16)),1,'precios');
            $token = new Token();
            $date =new DateTime();
            //////////////////offline consulta precios////////////////////////////////////////////
            if ($_SESSION['offline']){
              try{
                $db = new Application_Model_UserinfoMapper();
                $adapter = $db->getAdapter();
                ////////////////////consulta atributos de cliente lista de precios y descuento de linea/////////////////////
                $queryCliente         = $adapter->prepare("SELECT LineDiscountCode,DiscountPriceGroupId FROM dbo.AYT_CustCustomerV3Staging WHERE CustomerAccount = '".$parametros['_CustAccount']."';");
                $queryCliente->execute();
                $datacliente          = $queryCliente->fetchAll(PDO::FETCH_ASSOC);
                $LineDiscountCode     = $datacliente[0]['LineDiscountCode'];
                $DiscountPriceGroupId = $datacliente[0]['DiscountPriceGroupId'];
                ///////////////////////////////////////////////////////////////////////////////////////////////////////////
                //////////////////////////////////ejecuta el procedimiento de precios//////////////////////////////////////
                $queryPrecio      = $adapter->prepare("EXECUTE dbo.getPriceData '".$DiscountPriceGroupId."','".$LineDiscountCode."','".$parametros['_ItemId']."',".$parametros['_amountQty'].";");
                $queryPrecio->execute();
                $dataPrecio       = $queryPrecio->fetchAll(PDO::FETCH_ASSOC);
                $LineAmountOff    = $dataPrecio[0]['MontoNeto'];
                $PrecioAcuerdoOff = $dataPrecio[0]['PrecioAcuerdo'];
                ////////////////////////////////////////////////////////////////////////////////////////////////////////////
              }catch(Exception $e){
                print_r($e->getMessage());
              }
            }
            ///////////////////////////////////////////////////////////////////////////////
            ////////Set de los campos de POST para CTZN y OV ////////////////////////////////////////////////
            $CURLOPT_POSTFIELDS_ORIG = "{  'CustAccount': '".$parametros['_CustAccount']."',
                                        'ItemId': '".$parametros['_ItemId']."',
                                        'amountQty': '".$parametros['_amountQty']."',
                                        'transDate': '".$date->format('m/d/Y')."',
                                        'currencyCode': '".$parametros['_currencyCode']."',
                                        'InventSiteId': '".$parametros['_InventSiteId']."',
                                        'InventLocationId': '".$parametros['_InventLocationId']."',
                                        'company': '".COMPANY."',
                                        'UnitId' : '".$parametros['_unitId']."',
                                        'PercentCharges': ".$parametros['_PercentCharges']."
                                    }";

            $CURLOPT_POSTFIELDS = "{  'CustAccount': '".$parametros['_CustAccount']."',
                                        'ItemId': '".$parametros['_ItemId']."',
                                        'amountQty': '".$parametros['_amountQty']."',
                                        'transDate': '".$date->format('m/d/Y')."',
                                        'currencyCode': '".$parametros['_currencyCode']."',
                                        'InventSiteId': '".$parametros['_InventSiteId']."',
                                        'InventLocationId': '".$parametros['_InventLocationId']."',
                                        'company': '".COMPANY."',
                                        'UnitId' : '".$parametros['_unitId']."',
                                        'PercentCharges': 0
                                    }";
            ////////////////////////////////////////////////////////////////////////////////////////////////////
            ///////////////////////////////Se calcula el precio base sin descuento del web service//////////////
            if (!$_SESSION['offline']){
              curl_setopt_array(CURL1, array(
                CURLOPT_URL => "https://".DYNAMICS365."/api/services/STF_INAX/STF_ItemSalesPrice/getSalesPriceLineAmountV2?%24sysparm_display_value=true",
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
              $response = curl_exec(CURL1);
              $err = curl_error(CURL1);
            }else{
              $response = $LineAmountOff;
            }
            $diferencia = 0;

            if ($parametros['_PercentCharges'] > 0){
              if (!$_SESSION['offline']){
                curl_setopt_array(CURL1, array(
                  CURLOPT_URL => "https://".DYNAMICS365."/api/services/STF_INAX/STF_ItemSalesPrice/getSalesPriceUnitPrice?%24sysparm_display_value=true",
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
                $responseSinPorc = curl_exec(CURL1);
                $err = curl_error(CURL1);
              }else{
                $responseSinPorc = $PrecioAcuerdoOff;
              }

              $monedaCargo = $responseSinPorc * (($parametros['_PercentCharges']/100)+1);
              $diferencia = ($monedaCargo - $responseSinPorc)*$parametros['_amountQty'];
              if ($parametros['_SalesPrice'] > 0){
                $monedaCargo = $parametros['_SalesPrice'] * (($parametros['_PercentCharges']/100)+1);
                $diferencia = ($monedaCargo - $parametros['_SalesPrice'])*$parametros['_amountQty'];
              }
            }

            if ($err) {
              $result = "cURL Error #:" . $err;
            } else {
              $result = round( (($response)*(($parametros['_PercentCharges']/100)+1)),2 );
            }

            /////////////////////////////////////////////////////////////////////////////////////////////////////
            /**
            Si el precio es 1.0 le da tratamiento cmo si fuera un servicio
            y lo calcula con respecto a lo que se ha mandado en el precio
            manual.
            **/
            //ReleasedDistinctProducts?%24select=ProductType%2CItemNumber%2CSearchName%2CProductGroupId&%24filter=ItemNumber%20eq%20'".$parametros['_ItemId']."'",
                
              // curl_setopt_array(CURL1, array(
              //   CURLOPT_URL => "https://".DYNAMICS365."/Data/ReleasedDistinctProducts?%24filter=ProductType%20eq%20Microsoft.Dynamics.DataEntities.EcoResProductType'Service'%20%20or%20ProductGroupId%20eq%20'PRENDAS'%20or%20(ProductGroupId%20eq%20'LIQUID'%20and%20(ItemNumber%20eq%20'9910*'%20or%20ItemNumber%20eq%20'9915*'%20or%20ItemNumber%20eq%20'9920*'%20or%20ItemNumber%20eq%20'9999*'))%20or%20(ProductGroupId%20eq%20'ARTICULOS'%20and%20ItemNumber%20eq%20'9999*')%20and%20ItemNumber%20eq%20'".$parametros['_ItemId']."'&%24select=ItemNumber%2CProductGroupId%2CSearchName%2CProductType",
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
              // $responsePriceProd = curl_exec(CURL1);
              // $resultPriceProd = json_decode($responsePriceProd);
              // $db = new DB_Conexion();
              // $adapter = $db->getAdapter();

              // (CONFIG==DESARROLLO) ? $db = new DB_Conexion():$db = new DB_ConexionExport();
              // $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
              // $query = $db->query("SELECT ItemNumber,ProductGroupId,SearchName,ProductType
              //                         FROM articulos 
              //                         WHERE ItemNumber = '".$parametros['_ItemId']."'
              //                         ORDER BY ItemNumber");
              // $query->execute();
              // $resultPriceProd = $query->fetchAll();

              $productGroupId = $parametros['_productGroupId'];
              $productType = $parametros['_productType'];
              $itemNumber = $parametros['_ItemId'];
              if ( ($productType == 'Service') || ($productGroupId == 'PRENDAS') || (($productGroupId == 'ARTICULOS' &&( !strpos($itemNumber,'9999') === false ))) || (($productGroupId == 'LIQUID' &&( (!strpos($itemNumber,'9910') === false) || (!strpos($itemNumber,'9915') === false) || (!strpos($itemNumber,'9999') === false) ))) ){
              // if ( ($resultPriceProd->value[0]->ProductType == 'Service') || ($resultPriceProd->value[0]->ProductGroupId == 'PRENDAS') || (($resultPriceProd->value[0]->ProductGroupId == 'ARTICULOS' &&( strpos($resultPriceProd->value[0]->ItemNumber,'9999') >= 0 ))) || (($resultPriceProd->value[0]->ProductGroupId == 'LIQUID' &&( strpos($resultPriceProd->value[0]->ItemNumber,'9910') >= 0 || strpos($resultPriceProd->value[0]->ItemNumber,'9915') >=0 || strpos($resultPriceProd->value[0]->ItemNumber,'9999') >=0 ))) ){
                // $monedaCargo = 0;
                // $result = round($response);

                if ($parametros['_SalesPrice'] > 0){
                  $result = $parametros['_SalesPrice'] * (($parametros['_PercentCharges']/100)+1);
                  $result = (  $result * $parametros['_amountQty'] );
                }
              }

              //return array('precio'=>$result,'precio_iva'=>$result,'diferencia' => $diferencia,'tipo' => $resultPriceProd->value[0]->ProductType);

            ///////////////////////Calcular el precio base de lo que esta calculado en Dynamics con el precio modificado////////////////////
            /***
            Calcula el precio con lo que esta modificado en dynamics
            ***/
            if ($parametros['_documentType'] == 'CTZN'){
              if (!$_SESSION['offline']){
                curl_setopt_array(CURL1, array(
                    CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesQuotationLines?%24filter=SalesQuotationNumber%20eq%20'".$parametros['_documentId']."'%20and%20ItemNumber%20eq%20'".$parametros['_ItemId']."'%20and%20InventoryLotId%20eq%20'".$parametros['_InventoryLotId']."'&%24select=LineAmount,RequestedSalesQuantity,FixedPriceCharges,ShippingSiteId,ShippingWarehouseId",
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
                $responseLine = [];
                $responseLine['value'][0] = (object)array('LineAmount' => $LineAmountOff);
                $responseLine = json_encode($responseLine);
              }

              if ($err) {
                $resultLineAmount = "cURL Error #:" . $err;
              } else {
                $resultLineAmount = json_decode($responseLine);
              }

              /*****
              De acuerdo al precio base calcula el precio con lo que le mandamos por cantidad.
              *****/
              if (!empty($resultLineAmount->value)){
                $oldValues = array(
                  // 'LineAmount'              => $resultLineAmount->value[0]->LineAmount,
                  'RequestedSalesQuantity'  => $resultLineAmount->value[0]->RequestedSalesQuantity,
                  'FixedPriceCharges'       => $resultLineAmount->value[0]->FixedPriceCharges,
                  'ShippingSiteId'          => trim($resultLineAmount->value[0]->ShippingSiteId),
                  'ShippingWarehouseId'     => trim($resultLineAmount->value[0]->ShippingWarehouseId)
                );
                $newValues = array(
                  // 'LineAmount'              => $result,
                  'RequestedSalesQuantity'  => $parametros['_amountQty'],
                  'FixedPriceCharges'       => $parametros['_PercentCharges'],
                  'ShippingSiteId'          => trim($parametros['_InventSiteId']),
                  'ShippingWarehouseId'     => trim($parametros['_InventLocationId'])
                );
                $cambio = self::validateChangeInLine($oldValues,$newValues);

                if ($resultLineAmount->value[0]->LineAmount != $result && $parametros['_getPriceFromWs'] == 'false'){
                  if($cambio){
                    $result = $result;
                  }else{
                    $result = ($resultLineAmount->value[0]->LineAmount / $resultLineAmount->value[0]->RequestedSalesQuantity);
                    $result = $result * $parametros['_amountQty'];
                  }
                  ///////////////////////////traer el modod de pago de dynamics(01,02...etc)////////////////////////////////
                  curl_setopt_array(CURL1, array(
                    CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesQuotationHeaders?%24filter=SalesQuotationNumber%20eq%20'".$parametros['_documentId']."'&%24select=CustomerPaymentMethodName",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_POSTFIELDS => "{\n\t'SalesPrice' : 1500\n}",
                    CURLOPT_HTTPHEADER => array(
                      "accept: application/json",
                      "authorization: Bearer ".$token->getToken()[0]->Token."",
                      "content-type: application/json"
                    ),
                  ));

                  $responsePaymMode = curl_exec(CURL1);
                  $err = curl_error(CURL1);

                  if ($err) {
                    $resultPaymMode =  "cURL Error #:" . $err;
                  } else {
                    $resultPaymMode =  json_decode($responsePaymMode);
                  }
                  if($resultPaymMode->value[0]->CustomerPaymentMethodName=='02' && $resultLineAmount->value[0]->FixedPriceCharges==0){
                    $resultPaymMode->value[0]->CustomerPaymentMethodName='02a';
                  }
                  
                  if ($parametros['_paymMode'] != $resultPaymMode->value[0]->CustomerPaymentMethodName){
                    curl_setopt_array(CURL1, array(
                      CURLOPT_URL => "https://".DYNAMICS365."/api/services/STF_INAX/STF_ItemSalesPrice/getSalesPriceLineAmountV2?%24sysparm_display_value=true",
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
                    $responseEdicion = curl_exec(CURL1);
                    $err = curl_error(CURL1);
                    $result = round( (($responseEdicion)*(($parametros['_PercentCharges']/100)+1)),2 );
                  }
                  //////////////////////////////////////////////////////////////////////////////////////////////
                  if ( ($productType == 'Service') || ($productGroupId == 'PRENDAS') || (($productGroupId == 'ARTICULOS' &&( !strpos($itemNumber,'9999') === false ))) || (($productGroupId == 'LIQUID' &&( (!strpos($itemNumber,'9910') === false) || (!strpos($itemNumber,'9915') === false) || (!strpos($itemNumber,'9999') === false) ))) ){
                  //if ($resultPriceProd->value[0]->ProductType == 'Service'){
                    // $monedaCargo = 0;
                    // $result = round($response);
                    // if ( ($resultPriceProd[0]['ProductType'] == 'Service') || ($resultPriceProd[0]['ProductGroupId'] == 'PRENDAS') || (($resultPriceProd[0]['ProductGroupId'] == 'ARTICULOS' &&( strpos($resultPriceProd[0]['ItemNumber'],'9999') >= 0 ))) || (($resultPriceProd[0]['ProductGroupId'] == 'LIQUID' &&( (strpos($resultPriceProd[0]['ItemNumber'],'9910') >= 0) || (strpos($resultPriceProd[0]['ItemNumber'],'9915') >= 0) || (strpos($resultPriceProd[0]['ItemNumber'],'9999') >= 0) ))) ){
                    if ($parametros['_SalesPrice'] > 0){
                      $result = $parametros['_SalesPrice'] * (($parametros['_PercentCharges']/100)+1);
                      $result = (  $result * $parametros['_amountQty'] );
                    }

                    //$result =$parametros['_SalesPrice'] - $resultLineAmount->value[0]->FixedPriceCharges;
                  }
                }
              }
            }else{
              /***
              Calcula el precio con lo que esta modificado en dynamics
              ***/
              if (!$_SESSION['offline']){
                curl_setopt_array(CURL1, array(
                  CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesOrderLines?%24filter=SalesOrderNumber%20eq%20'".$parametros['_documentId']."'%20and%20ItemNumber%20eq%20'".$parametros['_ItemId']."'&%24select=LineAmount,OrderedSalesQuantity,ShippingSiteId,ShippingWarehouseId,FixedPriceCharges",
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
                $responseLine = '';
              }

              if ($err) {
                $resultLineAmount = "cURL Error #:" . $err;
              } else {
                $resultLineAmount = json_decode($responseLine);
              }

              /*****
              De acuerdo al precio base calcula el precio con lo que le mandamos por cantidad.
              *****/
              if ( !empty($resultLineAmount->value)){
                $oldValues = array(
                  // 'LineAmount'              => $resultLineAmount->value[0]->LineAmount,
                  'RequestedSalesQuantity'  => $resultLineAmount->value[0]->OrderedSalesQuantity,
                  'FixedPriceCharges'       => $resultLineAmount->value[0]->FixedPriceCharges,
                  'ShippingSiteId'          => trim($resultLineAmount->value[0]->ShippingSiteId),
                  'ShippingWarehouseId'     => trim($resultLineAmount->value[0]->ShippingWarehouseId)
                );
                $newValues = array(
                  // 'LineAmount'              => $result,
                  'RequestedSalesQuantity'  => $parametros['_amountQty'],
                  'FixedPriceCharges'       => $parametros['_PercentCharges'],
                  'ShippingSiteId'          => trim($parametros['_InventSiteId']),
                  'ShippingWarehouseId'     => trim($parametros['_InventLocationId'])
                );
                $cambio = self::validateChangeInLine($oldValues,$newValues);
                if ($resultLineAmount->value[0]->LineAmount != $result && $parametros['_getPriceFromWs'] == 'false'){
                  if($cambio){
                    $result = $result;
                  }else{
                    $result = ($resultLineAmount->value[0]->LineAmount / $resultLineAmount->value[0]->OrderedSalesQuantity);
                    $result = $result * $parametros['_amountQty'];
                  }
                  ///////////////////////////traer el modod de pago de dynamics(01,02...etc)////////////////////////////////
                  curl_setopt_array(CURL1, array(
                    CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesOrderHeaders?%24filter=SalesOrderNumber%20eq%20'".$parametros['_documentId']."'&%24select=CustomerPaymentMethodName",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_POSTFIELDS => "{\n\t'SalesPrice' : 1500\n}",
                    CURLOPT_HTTPHEADER => array(
                      "accept: application/json",
                      "authorization: Bearer ".$token->getToken()[0]->Token."",
                      "content-type: application/json"
                    ),
                  ));

                  $responsePaymMode = curl_exec(CURL1);
                  $err = curl_error(CURL1);

                  if ($err) {
                    $resultPaymMode =  "cURL Error #:" . $err;
                  } else {
                    $resultPaymMode =  json_decode($responsePaymMode);
                  }
                  
                  if ($parametros['_paymMode'] != $resultPaymMode->value[0]->CustomerPaymentMethodName){
                    curl_setopt_array(CURL1, array(
                      CURLOPT_URL => "https://".DYNAMICS365."/api/services/STF_INAX/STF_ItemSalesPrice/getSalesPriceLineAmountV2?%24sysparm_display_value=true",
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
                    $responseEdicion = curl_exec(CURL1);
                    $err = curl_error(CURL1);
                    $result = round( (($responseEdicion)*(($parametros['_PercentCharges']/100)+1)),2 );
                  }
                  //////////////////////////////////////////////////////////////////////////////////////////////
                  if ( ($productType == 'Service') || ($productGroupId == 'PRENDAS') || (($productGroupId == 'ARTICULOS' &&( !strpos($itemNumber,'9999') === false ))) || (($productGroupId == 'LIQUID' &&( (!strpos($itemNumber,'9910') === false) || (!strpos($itemNumber,'9915') === false) || (!strpos($itemNumber,'9999') === false) ))) ){
                  //if ($resultPriceProd->value[0]->ProductType == 'Service'){
                    // $monedaCargo = 0;
                    // $result = round($response);
                    if ($parametros['_SalesPrice'] > 0){
                      $result = $parametros['_SalesPrice'] * (($parametros['_PercentCharges']/100)+1);
                      $result = (  $result * $parametros['_amountQty'] );
                    }
                  }
                }
              }
            }
            // print_r($resultPriceProd);exit();
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            return array('precio'=>($result/$parametros['_amountQty']),'precio_iva'=>($result/$parametros['_amountQty']),'diferencia' => $diferencia,'tipo' => $resultPriceProd->value[0]->ProductType,'punitario' => $parametros['_SalesPrice']);
        }
        catch(Exception $e){
          print_r($e);exit();
          Metodos::$db->kardexLog(json_encode(array('precio'=>0,'precio_iva'=>0,"error"=>$e->getTraceAsString())), $parametros, json_encode(array('precio'=>0,'precio_iva'=>0,"error"=>$e->getTraceAsString())),2,'precios');
          return array('precio'=>0,'precio_iva'=>0,"error"=>$e->getTraceAsString());
        }       
    }

    public function SetEncabezadoDynamics($parametro,$tipo){
        try{
            $this->InicializarWebservice();
            $this->cargarConexionDB();
            include('includes/Encabezado.php'); 
            $encabezado = new Encabezado($parametro,  $this->db);
            if ($tipo == 'ORDVTA'){
                $documentId = $this->cliente->SetSalesOrderHeader(array('_XMLSalesOrder'=>$encabezado->getEncabezadoXML($tipo)));
            }else{
                $documentId = $this->cliente->SetSalesQuotationOrderHeader(array('_XMLSalesOrder'=>$encabezado->getEncabezadoXML($tipo)));
                $this->db->kardexLog("lineas[".$tipo."] parametros: ".$encabezado->getEncabezadoXML($tipo)." resultado: ".json_encode($documentId),json_encode($parametro),  json_encode($documentId),1,'lineas');
            }
             return array('OV'=>$documentId->response,'encabezado'=>$encabezado);
        }
        catch(Exception $e){
            $this->cargarConexionDB();
            $this->db->kardexLog("lineas[".$tipo."] parametros: ".json_encode($parametro)." resultado: ".$e->getTraceAsString(),json_encode($parametro),  json_encode($e),2,'lineas');
            throw new Exception($e);
        }       
    }

    public function SetLineasDynamics($lineasRef,$OV,$tipo){
        $this->cargarConexionDB();
        try{            
            include('includes/LineaXML.php');   
            $lineas = new LineaXML($tipo,$OV);
            foreach($lineasRef AS $Data){
                $lineas->addLine($Data);                
            }
            $lineas->endLineaXml();
            $lineasArr = array('lineaXML' => $lineas->lineaXML);
            $OVResult = $this->ConfirmarOV($lineasArr,$tipo);
            $this->db->kardexLog("lineas[".$tipo."] parametros: ".json_encode($lineasRef)." idDoc:".$OV." resultado: ".$OVResult,json_encode($lineasRef), $OVResult,1,'lineas');
            return array("error"=>false,"result"=>$OVResult);
        }
        catch(Exception $e){
            $this->db->kardexLog("parametros: ".json_encode($lineasRef)." idDoc:".$OV." resultado: ".$e->getTraceAsString(),json_encode($lineasRef),  json_encode($e),2,'lineas');
            return array("error"=>true,"result"=>$e->getMessage());
        }
    }

    public function ConfirmarOV($lineas,$tipo){
        $this->cargarConexionDB();
        try{
            $OV="";
            $parametroWebService='';
            if ( is_array($lineas) && $lineas['lineaXML'] != '' ){
                $this->InicializarWebservice();
                if ($tipo == 'ORDVTA'){
                    $parametroWebService = array('_XMLSalesLines' =>$lineas['lineaXML']);
                    $OV = $this->cliente->SetSalesOrderLines($parametroWebService);
                }else{
                    $parametroWebService = array('_XMLQuotationLines' => $lineas['lineaXML']);
                    $OV = $this->cliente->SetSalesQuotationOrderLines($parametroWebService);
                }
            }
            return $OV->response;
        }
        catch(Exception $e){
            throw new Exception($e);
        }
    }

    public function SetRemision($parametros,$dimensiones){
        Metodos::cargarConexionDB();
        $params = array('_SalesId'=> $parametros,'_company'=>COMPANY,'_Company'=>COMPANY);
        try{
            //$this->InicializarWebservice();
            //$remision = $this->cliente->SetSalesOrderPackingSlip($params);
            $flagLicensePlate = $this->setProductData($params,$dimensiones);
            if($flagLicensePlate['status'] == 0){
                $remision = $this->SetSalesOrderPackingSlipEntity($params);
                Metodos::$db->kardexLog("",json_encode($params),json_encode(array("status"=>"Exito","msg"=>$remision)),1,'remision');
                $resTemp = json_decode($remision);
                $result = array("status"=>"Exito","msg"=>$remision);
                if (isset($resTemp->Message)){
                  $result = array("status"=>"Fail","msg"=>$remision->Message,"tipo" =>"remision");
                }
                return json_encode($result);
            }else{
                Metodos::$db->kardexLog("",json_encode($params),json_encode(array("status"=>"Fail","msg"=>$flagLicensePlate['msg'])),0,'remision');
                return json_encode(array("status"=>"Fail","msg"=>$flagLicensePlate['msg'],"tipo" => "liberacion"));
            }
        }
        catch(SoapFault $e){
            $q= $this->db->db->_adapter->query(LAST_ERROR);
            $q->execute();
            $res=$q->fetchAll(PDO::FETCH_ASSOC);
            $msg = 'Exception en Dynamics: '; 
            $msg2="";
            foreach ($res AS $Data){
                $msg2.=$Data['DESCRIPTION'];
                $msg .= $Data['DESCRIPTION'].'<br>';
            }
            $st=1;
            if(empty($msg2)){
                $st=2;
            }
            $this->db->kardexLog("",json_encode($params),json_encode(array("status"=>"Fallo","msg"=>$msg)),$st,'remision');
            throw new Exception (json_encode(array("parametros"=>$params,"status"=>"Fallo","msg"=>$msg,"error"=>$e->getMessage())));
        }       
    }

    public function setProductData($params,$dimensiones){
        $ov = $params['_SalesId'];
        $company = $params['_company'];
        $lote = $dimensiones['lote'];
        $localidad = $dimensiones['localidad'];
        $licensePlateId = $dimensiones['licensePlate'];
        // if (COMPANY == 'ATP'){
        //     $lote = '';
        //     $localidad = 'RECEPCION';
        //     $licensePlateId = 'CHIHEXHB_RECEPCION';            
        // }else{
        //     $lote = '18260810926';
        //     $localidad = 'GRAL';
        //     $licensePlateId = 'CHIHEXHB_RECEPCION';
        // }
        $token = new Token();
        $tokenTemp = $token->getToken();
        $company = COMPANY;

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesOrderLines?%24filter=SalesOrderNumber%20eq%20'".$ov."'",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 60,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$tokenTemp[0]->Token."",
            "content-type: application/json"
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

        if (!empty($result->value)){
            $error = array('status' => 0,'msg'=>$result);
            //$dimensiones['lote'] = '';
            //unset($dimensiones['lote']);
            $datosValido = $this->checkDimensions($dimensiones);
            
            if ($datosValido){
              foreach($result->value as $Data){
                  $postFields = "{'inventoryLotId' : '".$Data->InventoryLotId."','itemBatchNumber' : '".$lote."','wMSLocationId' : '".$localidad."','licensePlateId' : '".$licensePlateId."','company': '".strtolower($company)."'}";
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
                      "authorization: Bearer ".$tokenTemp[0]->Token."",
                      "content-type: application/json"
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
                  if ($result != 'Actualizacion correcta'){
                      $error['status'] = 1;
                      $error['msg'] = $result;
                  }                
              }
            }else{
              $error['status'] = 1;
              $error['msg'] = 'Faltaron datos de dimension de articulo (lote,localidad o license plate)';
            }
            return $error;         
        }else{
          $error['status'] = 1;
          $error['msg'] = $result;
          return $error;
        }
    }

    public function checkDimensions($dimensiones){
      $valido         = true;
      $lote           = isset($dimensiones['lote']);
      $localidad      = isset($dimensiones['localidad']);
      $licensePlateId = isset($dimensiones['licensePlate']);

      $loteEmpty           = $dimensiones['lote'];
      $localidadEmpty      = $dimensiones['localidad'];
      $licensePlateIdEmpty = $dimensiones['licensePlate'];

      if (!$lote || !$localidad || !$licensePlateId || $loteEmpty == '' || $localidadEmpty == '' || $licensePlateIdEmpty == ''){
        $valido = false;
      }
      return $valido;
    }

    public function SetSalesOrderPackingSlipEntity($params){
        $token = new Token();
        $company = COMPANY;
        $postFields = "{'salesId' : '".$params['_SalesId']."','company' : '".$params['_company']."'}";
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://".DYNAMICS365."/api/services/STF_INAX/STF_OrdenVenta/SetSalesOrderPackingSlipV2",
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

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
          $result = "cURL Error #:" . $err;
        } else {
            $exception = json_decode($response);
            $result = $response;
        }

        return $result;
    }
    
    public function mainAction($encabezado,$lineas,$tipo){
        
        $encabezadoResult = $this->SetEncabezadoDynamics($encabezado,$tipo);
        $enc = $encabezadoResult;
        
        foreach ($lineas as &$line){
            $line['sitio'] =  $enc['encabezado']->SiteId;
            $line['almacen'] = $enc['encabezado']->LocationId;
        }
        //return $lineas;
        $this->SetLineasDynamics($lineas,$enc['OV'],$tipo);
        
        $remision = $this->SetRemision($enc['OV']);
        
        return $remision;
    }

    public function crearCliente($parametros){
        try{
            $this->InicializarWebservice();
            $xml = $this->getCustomerXML($parametros);
            $parametroWebService = array('_XMLCustomer' => $xml);
            $OV = $this->cliente->CreateCustomer($parametroWebService);
            return json_encode(array("status"=>"Exito","msg"=>$OV->response));
        }
        catch(SoapFault $e){
            $this->cargarConexionDB();
            $q= $this->db->db->_adapter->query(LAST_ERROR);
            $q->execute();
            $res=$q->fetchAll(PDO::FETCH_ASSOC);
            $msg = '<b>Exception en Dynamics: ';
            $msg2 ="";
            foreach ($res AS $Data){
                $msg2.=$Data['DESCRIPTION'];
                $msg .= $Data['DESCRIPTION'].'<br>';
            }
            $msg .= '</b>';
            $st=1;
            if(empty($msg2)){$st=2;}
            $this->db->kardexLog("",json_encode($parametros),json_encode(array("status"=>"Fallo","msg"=>$msg)),$st,'ALTACLIENTE');
            throw new Exception (json_encode(array("status"=>"Fallo","msg"=>$msg,"Exception"=>$e->getMessage())));
        }
        
    }
    
    public function setSalesOrderCreditLimit($_SalesId, $_user){
        try{
            $this->InicializarWebservice();
            $parametroWebService = array('_SalesId' => $_SalesId,'_user' => $_user,'_company'=>COMPANY);
            $OV = $this->cliente->SetSalesOrderCreditLimit($parametroWebService);
            return $OV->response;
        }
        catch(Exception $e){
            $this->cargarConexionDB();
            $q= $this->db->db->_adapter->query(LAST_ERROR);
            $q->execute();
            $res=$q->fetchAll(PDO::FETCH_ASSOC);
            $msg = '<b>Exception en Dynamics: ';
            foreach ($res AS $Data){
                $msg .= $Data['DESCRIPTION'].'<br>';
            }
            $msg .= '</b>';            
            throw new Exception (json_encode(array("status"=>"Fallo","msg"=>$msg)));
        }
    }

    private function getCustomerXML($DatosCliente){
        if ( is_array($DatosCliente) && !empty($DatosCliente) ){
            $Address = '';$xml = '';$i = 1;
            foreach($DatosCliente[0]['Address'] AS $address){
                $Address .= "   <Address ";
                $Address .= "       DirLine='".$i."' ";
                $Address .= "       DirName='".$DatosCliente[0]['Name']."' ";
                $Address .= "       DirStreet='".$address['calle'].' '.$address['numero']."' ";
                $Address .= "       DirStreetNum='".$address['numero']."' ";
                $Address .= "       DirCity= '".$address['ciudad']."'";
                $Address .= "       DirCounty='".$address['colonia']."' ";
                $Address .= "       DirZipCode='".$address['cp']."' ";
                $Address .= "       DirRoleType='".$address['proposito']."'>";
                $Address .= "   </Address>";
                $i++;
            }
            $contactos = '';
            $i = 1;
            foreach($DatosCliente[0]['Contact'] AS $datoContactos){
                $contactos .= "   <Contact ";
                $contactos .= "       ContactLine='".$i."' ";
                $contactos .= "       ContactDescription='".$datoContactos['descripcion']."'  ";
                $contactos .= "       ContactType='".$datoContactos['formaContacto']."'  ";
                $contactos .= "       ContactLocator='".$datoContactos['telefono']."' ";
                $contactos .= "       ContactLocatorExtension = '".$datoContactos['extension']."' ";
                $contactos .= "       ContactIsPrimary='".$datoContactos['isPrimary']."'";
                $contactos .= "       ContactRoleType='".$datoContactos['proposito']."'>";
                $contactos .= "   </Contact>";
                $i++;
            }
            $xml .= '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<Customer Version="1.0">';
            $xml .= '<Company>'.COMPANY.'</Company>';
            $xml .= "<Name>".$DatosCliente[0]['Name']."</Name>";
            $xml .= "<CustGroup>".$DatosCliente[0]['CustGroup']."</CustGroup> ";
            $xml .= "<CurrencyCode>".$DatosCliente[0]['CurrencyCode']."</CurrencyCode> ";
            $xml .= "<CompanyType>".$DatosCliente[0]['CompanyType']."</CompanyType> ";
            $xml .= "<RFC>".$DatosCliente[0]['RFC']."</RFC>";
            $xml .= "<SalesDistrict>".$DatosCliente[0]['SalesDistrict']."</SalesDistrict>";
            $xml .= "<SiteId>".$DatosCliente[0]['SiteId']."</SiteId>";
            $xml .= "<LocationId>".$DatosCliente[0]['LocationId']."</LocationId>";
            $xml .= "<LineDisc>".$DatosCliente[0]['LineDisc']."</LineDisc>";
            $xml .= "<Addresses>".$Address."</Addresses>";
            $xml .= "<Contacts>".$contactos."</Contacts>";
            $xml .= "</Customer>";
        }
        return $xml;
    }

    private function InicializarWebservice(){
        define('USERPWD', 'atp\\aosuser:AdminAX2012');
        // we unregister the current HTTP wrapper
        stream_wrapper_unregister('http');
        // we register the new HTTP wrapper
        $stream = new NTLMStream();
        //echo ('NTLMStream');
        stream_wrapper_register('http', 'NTLMStream') or die("Failed to register protocol");
        // Initialize Soap Client
        $client = new NTLMSoapClient(WEB_SERVICE_URL);
        //regresa la instancia de webservice
        $this->cliente = $client;
    }
    public function createFactura($xml) {
        $this->cargarConexionDB();
        try {
            $resultado=array();
            $this->InicializarWebserviceFacturacion();
            $res=$this->cliente->invoiceSalesOrderPackingSlip(array('_salesOrder'=>$xml));   
            if($res->response=="No se puede facturar esta remisión para esta OV"){
                $resultado=array("resultado"=>"bad","respuesta"=>$res->response);
            }
            else {
                $resultado=array("resultado"=>"ok","respuesta"=>$res->response); 
            }
            $this->db->kardexLog("",json_encode($xml),json_encode($resultado),1,'factura');
            return $resultado; 
        } 
        catch (Exception $e) {
            
            $q= $this->db->db->_adapter->query(LAST_ERROR);
            $q->execute();
            $res=$q->fetchAll(PDO::FETCH_ASSOC);
            $msg = '<b>Exception en Dynamics: ';
            foreach ($res AS $data){
                $msg .= $data['DESCRIPTION'].'<br>';
            }
            $msg .= '</b>';
            $this->db->kardexLog("",json_encode($xml),json_encode($msg),2,'factura');
            throw new Exception ($msg);
        }        
    }
    public function createDiario($xml){
        $this->cargarConexionDB();
        try{
            $this->InicializarWebserviceFacturacion();
            $res=$this->cliente->journalPayment(array('_salesOrder'=>$xml));   
            $resultado=array("resultado"=>"ok","respuesta"=>$res->response); 
            $this->db->kardexLog("",json_encode($xml),json_encode($resultado),1,'diario');
            return $resultado; 
        }
        catch (Exception $e){
            $q= $this->db->db->_adapter->query(LAST_ERROR);
            $q->execute();
            $res=$q->fetchAll(PDO::FETCH_ASSOC);
            $msg = '<b>Exception en Dynamics: ';
            foreach ($res AS $data){
                $msg .= $data['DESCRIPTION'].'<br>';
            }
            $msg .= '</b>';
           $this->db->kardexLog("",json_encode($xml),json_encode($msg),2,'diario');
           return $arr=["resultado"=>$msg,"error"=>$e];  
        }
    }
    
    public function editDiario($xml){
        $this->cargarConexionDB();
        try{
            $this->InicializarWebserviceFacturacion();
            $res=$this->cliente->JournalPaymentEdit(array('_xml'=>$xml));   
            $resultado=array("resultado"=>"ok","respuesta"=>$res->response); 
            $this->db->kardexLog("",json_encode($xml),json_encode($resultado),1,'diarioEditar');
            return $resultado;
        }
        catch (Exception $e){
            $q= $this->db->db->_adapter->query(LAST_ERROR);
            $q->execute();
            $res=$q->fetchAll(PDO::FETCH_ASSOC);
            $msg = '<b>Exception en Dynamics: ';
            foreach ($res AS $data){
                $msg .= $data['DESCRIPTION'].'<br>';
            }
            $msg .= '</b>';
           $this->db->kardexLog("",json_encode($xml),json_encode($msg),2,'diarioEditar');
           return array("resultado"=>"not","respuesta"=>$msg,"exeption"=>$e);
        }
    }
    public function cerrarDiario($diario) {
        $this->cargarConexionDB();
        try{
            $this->InicializarWebserviceFacturacion();
            $res=$this->cliente->JournalPaymentPost(array('_parm'=>COMPANY."|$diario"));   
            $resultado=array("resultado"=>"ok","respuesta"=>$res->response); 
            $this->db->kardexLog("",json_encode($xml),json_encode($resultado),1,'diarioCerrar');
            return $resultado;
        }
        catch (Exception $e){
            $q= $this->db->db->_adapter->query(LAST_ERROR);
            $q->execute();
            $res=$q->fetchAll(PDO::FETCH_ASSOC);
            $msg = '<b>Exception en Dynamics: ';
            foreach ($res AS $data){
                $msg .= $data['DESCRIPTION'].'<br>';
            }
            $msg .= '</b>';
           $this->db->kardexLog("",json_encode($xml),json_encode($msg),2,'diarioCerrar');
           return array("resultado"=>"not","respuesta"=>$msg,"exeption"=>$e);
        }
    }

    private function InicializarWebserviceFacturacion(){
        define('USERPWD', 'atp\\aosuser:AdminAX2012');
        // we unregister the current HTTP wrapper
        stream_wrapper_unregister('http');
        // we register the new HTTP wrapper
        $stream = new NTLMStream();
        //echo ('NTLMStream');
        stream_wrapper_register('http', 'NTLMStream') or die("Failed to register protocol");
        // Initialize Soap Client
        $client = new NTLMSoapClient(WEB_SERVICE_INVOICE_URL);
        //regresa la instancia de webservice
        $this->cliente = $client;
    }

    public static function validateChangeInLine($oldValues,$newValues){
      $resultado = array_diff($oldValues, $newValues);
      $noChange = false;
      if (!empty($resultado)){
        $noChange = true;
      }
      return $noChange;
    }

    public static function getLineDiscountCode($cliente,$articulo,$cantidad){
      $token = new Token();

      ///////////////////////////get descuentos de cliente////////////////////////////////
      $curl = curl_init();

      curl_setopt_array($curl, [
        CURLOPT_URL => "https://".DYNAMICS365."/Data/CustomersV3?%24filter=CustomerAccount%20eq%20'".$cliente."'&%24select=LineDiscountCode%2CDiscountPriceGroupId",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_COOKIE => "ApplicationGatewayAffinity=e7fb295f94cb4b5e0cd1e2a4712e4a803fc926342cc4ecca988f29125dbd4b04",
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
        echo "cURL Error #:" . $err;
      } else {
        $dataDiscount = json_decode($response);
      }
      /////////////////////////////////////////////////////////////////
      ////////////////////get porcentaje descuento articulo///////////
      $curl = curl_init();

      curl_setopt_array($curl, [
        CURLOPT_URL => "https://".DYNAMICS365."/Data/AYT_ListaPrecios?%24filter=dataAreaId%20eq%20'atp'%20and%20Articulo%20eq%20'".$articulo."'%20and%20NombreLista%20eq%20'".$dataDiscount->value[0]->LineDiscountCode."'",
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
        $porcDescOriginal = json_decode($response);
        $descuentodelinea = $porcDescOriginal->value[0]->Percent1;

        foreach( $porcDescOriginal->value AS $DataLista ){
          $desde = (float) $DataLista->CantidadDesde;
          $hasta = ($DataLista->CantidadHasta == '' )? 9999999999 :(float) $DataLista->CantidadHasta;
          if ( $cantidad >= $desde && $cantidad < $hasta ){
            $descuentodelinea = $DataLista->Percent1;
          }
        }
      }
      //////////////////////////////////////////////////////////////////////
      return $descuentodelinea;
    }
}
