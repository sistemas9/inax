<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
ini_set('display_errors', '1');
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


class Application_Model_DiariosModel {
    public $db;
    public $_adapter;
    public $token;
    
    public function __construct(array $options = null){
        if (is_array($options)) {
            $this->setOptions($options);
        }
        $token = new Token();
        $tokenTemp = $token->getToken();
        $this->token = $tokenTemp[0]->Token;
        $this->db = new Application_Model_UserinfoMapper();
        $this->_adapter = $this->db->getAdapter();
    }
    
/** Funcion para traer saldos de los diarios
*** recibe como parametro la OV
*** y regresa monto, diario y status
*** status = 0 (no hay diario) permite agregar
*** status = 1 (hay diario y valor menor del monto ov) permite agregar
*** status = 2 (hay diario y valor excedido o igual del monto ov) no permite agregar
**/
    public function getsaldo($diario){
        $totalInDollars = 0;
        $totalInPesos = 0;
        $hasDollar = false;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://".DYNAMICS365."/data/SalesOrderLines?%24filter=dataAreaId%20eq%20'".COMPANY."'%20and%20SalesOrderNumber%20eq%20'".$diario."'&%24select=LineAmount%2CShippingSiteId%2CCurrencyCode",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$this->token."",
            "odata-version: 4.0"
            ),
        ));
        
        $response = curl_exec($curl);
       // print_r(curl_getinfo($curl));
        $responseLineas = json_decode($response);
       
        $totalLineas = 0;
        $iva = 1.16;
        if ( $responseLineas->value[0]->ShippingSiteId == 'MEXL' ||$responseLineas->value[0]->ShippingSiteId == 'TJNA' || $responseLineas->value[0]->ShippingSiteId == 'JURZ' ){
            $iva = 1.08;
        }
        foreach($responseLineas->value as $DataLinea){
            $totalLineas += $DataLinea->LineAmount;
        }
        $totalLineas = round( ($totalLineas * $iva), 2 );
        $err = curl_error($curl);   

         curl_setopt_array($curl, array(
            CURLOPT_URL => "https://".DYNAMICS365."/data/CustomerPaymentJournalLines?%24filter=STF_RefSalesId%20eq%20'".$diario."'",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,   
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$this->token."",
            "content-type: application/json; odata.metadata=minimal",
            "odata-version: 4.0"
            ),
        ));
        //DefaultDimensionsForOffsetAccountDisplayValue
        $response2 = curl_exec($curl);
        $response3 = $response2;
        $err = curl_error($curl);
        curl_close($curl);
        $response2 = json_decode($response2);
        $response3 = json_decode($response3);
        $totalDiario=0;
        $diarios = '';
        //Para tipo de cambio es Modulos->Contabilidad General->Tipo de cambio
        (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            for($i=0;$i<sizeof($response2->value);$i++){
                $isLineDollar = false;
                $transactionDate = date("Y-m-d", strtotime($response2->value[$i]->TransactionDate)).'T12%3A00%3A00Z';
                $currency = $response2->value[$i]->CurrencyCode;

                if($currency == 'USD'){
                    $hasDollar = true;
                    $isLineDollar = true;
                    $lineRate = self::getExchangeRate($transactionDate);  
                    $convertedAmount = $response2->value[$i]->CreditAmount * $lineRate;
                    $totalInDolllars += $response2->value[$i]->CreditAmount;
                    //echo 'CurrentDollar '.$response2->value[$i]->CreditAmount.' * '.$lineRate.' = '.$convertedAmount.'<br>'; 
                    
                }else{
                    $totalInPesos += $response2->value[$i]->CreditAmount;
                    $isLineDollar = false;
                    //echo 'CurrentPesos: '.$response2->value[$i]->CreditAmount.'<br>';
                }
                
                $query = "SELECT ACCOUNTNUM,ABS(AMOUNTCUR) AS AMOUNTCUR,CANCELLEDPAYMENT,VOUCHER FROM AYT_CustTransV2Staging WHERE VOUCHER = '".$response2->value[$i]->Voucher."' AND CANCELLEDPAYMENT = 1 ";
                $queryCancel = $conn->query($query);
                $queryCancel->execute();
                $resultCancel = $queryCancel->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($resultCancel)){
                    unset($response3->value[$i]);
                }else{
                    $totalDiario+=(!$isLineDollar)? $response2->value[$i]->CreditAmount : $convertedAmount;
                    //echo 'CurrentTotal: '.$totalDiario.'<br>'; 
                    $diarios .= $response2->value[$i]->JournalBatchNumber.' ,';

                }
            }
            if(!empty($response3->value)){
                // $journal = $response2->value[0]->JournalBatchNumber;
                $journal = substr($diarios, 0, -1);
                if ((float)round($totalDiario,2) < (float)round($totalLineas,2)){
                    $status = '1';
                    $canti = $totalLineas - $totalDiario;
                }else{
                    $status = '2';
                    $canti = $totalDiario;
                }
                //$canti = $response2->value[0]->CreditAmount;
            }else{
                $journal = "no";
                $canti = $totalLineas;
                $status = '0';
            }
            //$canti=$totalDiario;
            $todayDollar = self::getExchangeRate(date('Y-m-d').'T12:00:00Z');
       curl_close($curl);        
        if ($err) {
        return "cURL Error #:" . $err;
        } else {
          // print_r(array('monto'=>json_decode($response),'diario'=>$journal,'cantidad'=>$canti));exit();
            return array('monto'=>json_decode($response),'diario'=>$journal,'cantidad'=>round($canti,2),'status'=>$status, 'totalOV' => $totalLineas,'totaldiarios'=>$totalDiario, 'hasDollars' => $hasDollar ,'inDollars'=> $hasDollar? $totalInDolllars : 0,'inPesos'=> $hasDollar? $totalInPesos : 0, 'todayExchange'=> $todayDollar);
        }
    }

    public function getExchangeRate($date){
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/Data/ExchangeRates?%24select=Rate&%24filter=StartDate%20eq%20".$date,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$this->token."",
            "content-type: application/json; odata.metadata=minimal",
            "odata-version: 4.0"
            ),
        ));

        $exchangeRate = json_decode(curl_exec($curl));
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            return($exchangeRate->value[0]->Rate);
        }
    }

    public function getDiarioDetalle($diario){         
        //return $this->db->Query(DIARIO_DETALLE,[":diario"=>$diario]);

        $curl = curl_init();        
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/data/CustomerPaymentJournalLines?%24select=CreditAmount%2CTransactionText%2COffsetAccountType%2COffsetAccountDisplayValue%2CMarkedInvoice%2CAccountType%2CTransactionDate%2CAccountDisplayValue&%24filter=JournalBatchNumber%20eq%20'".$diario."'%20and%20dataAreaId%20eq%20'".COMPANY."'",
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
        "odata-version: 4.0",
        "prefer: return=representation"
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



    public function getDiarios($fechai,$fechaf) {
        // $resulset=$this->db->Query(DIARIO_LIST);        
        $curl = curl_init();        
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/data/CustomerPaymentJournalHeaders?%24filter=dataAreaId%20eq%20'".COMPANY."'&%24top=250&%24orderby=JournalBatchNumber%20desc",
        CURLOPT_RETURNTRANSFER => true,//&cross-company=true&%24filter=dataAreaId%20eq%20'".COMPANY."'
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
        "authorization: Bearer ".$this->token."",
        "content-type: application/json"
        ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $response=json_decode($response);

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/data/CustomerPaymentJournalLines?%24filter=dataAreaId%20eq%20'".COMPANY."'&%24select=JournalBatchNumber%2CCreditAmount%2CTransactionText%2COffsetAccountType%2COffsetAccountDisplayValue%2CMarkedInvoice%2CAccountType%2CTransactionDate&%24top=250&%24orderby=JournalBatchNumber%20desc",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
        "authorization: Bearer ".$this->token."",
        "content-type: application/json",
        ),
        ));
        
        $response2 = curl_exec($curl);
        $err = curl_error($curl);
        $response2=json_decode($response2);
        //print_r($response2);exit();
        curl_close($curl);

        $arr=[];
        foreach($response->value as $k =>$v){
            //$nuevafecha=strtotime ( '-6 hour' , strtotime ( $v->CustomerPaymentJournalLineHeaderEntity[0]->TransactionDate ) );
            //$fecha = date ( 'd/m/Y H:i:s' , $nuevafecha );
            // if($response2->value[$k]->JournalBatchNumber==$v->JournalBatchNumber){
            $fecha = explode('T',$response2->value[$k]->TransactionDate);
            $fechaYMD = explode('-',$fecha[0]);
            $fechaHMS = explode('Z',$fecha[1]);
            $fechaGood = $fechaYMD[2] . '/' . $fechaYMD[1] . '/' . $fechaYMD[0] . ' ' . $fechaHMS[0]; //"15/11/2018
            $arr[]=[$v->JournalBatchNumber,$v->Description,$v->JournalName,$fechaGood];
            // }
            // print_r($arr);exit();
        }
        return $arr;
    }

    public function saveDiarios($factura,$journalName,$descripcion,$montoFactura,$diarioCuentaContra,$diarioFPago,$timbre, $currency,$digitosTarjeta) {
        $curl = curl_init(); 
        $factura = "OV-".$factura;
        // curl_setopt_array($curl, array(
        // CURLOPT_URL => "https://".DYNAMICS365."/data/SalesInvoiceHeaders?%24select=InvoiceCustomerAccountNumber&%24filter=InvoiceNumber%20eq%20'".$factura."'",
        // CURLOPT_RETURNTRANSFER => true,
        // CURLOPT_ENCODING => "",
        // CURLOPT_MAXREDIRS => 10,
        // CURLOPT_TIMEOUT => 30,
        // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        // CURLOPT_CUSTOMREQUEST => "GET",
        // CURLOPT_POSTFIELDS => "",
        // CURLOPT_HTTPHEADER => array(
        // "accept: application/json",
        // "authorization: Bearer ".$this->token."",
        // "content-type: application/json; charset=utf-8",
        // "odata-version: 4.0",
        // "prefer: return=representation"
        // ),
        // ));
        
        // $cliente = curl_exec($curl);
        // $err = curl_error($curl);

        // $cliente=json_decode($cliente);
        
        // curl_setopt_array($curl, array(
        // CURLOPT_URL => "https://".DYNAMICS365."/data/CustomerPaymentJournalHeaders",
        // CURLOPT_RETURNTRANSFER => true,
        // CURLOPT_ENCODING => "",
        // CURLOPT_MAXREDIRS => 10,
        // CURLOPT_TIMEOUT => 30,
        // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        // CURLOPT_CUSTOMREQUEST => "POST",
        // CURLOPT_POSTFIELDS => "{\n\t \t\t\"dataAreaId\": \"".COMPANY."\",\n      \"JournalName\": \"".$journalName."\",\n      \"Description\": \"".$descripcion."\"\n}",
        // CURLOPT_HTTPHEADER => array(
        // "authorization: Bearer ".$this->token."",
        // "content-type: application/json; odata.metadata=minimal",
        // "odata-version: 4.0"
        // ),
        // ));

        // $response = curl_exec($curl);
        // $err = curl_error($curl);

        // $response=json_decode($response);         


        // curl_setopt_array($curl, array(
        // CURLOPT_URL => "https://".DYNAMICS365."/data/CustomerPaymentJournalLines",
        // CURLOPT_RETURNTRANSFER => true,
        // CURLOPT_ENCODING => "",
        // CURLOPT_MAXREDIRS => 10,
        // CURLOPT_TIMEOUT => 30,
        // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        // CURLOPT_CUSTOMREQUEST => "POST",
        // CURLOPT_POSTFIELDS => "{\n\t \t\t\"dataAreaId\": \"".COMPANY."\",\n\t\t\t\"LineNumber\": 1,\n\t\t\t\"MarkedInvoice\": \"".$factura."\",\n      \"JournalBatchNumber\": \"".$response->JournalBatchNumber."\",\n\t\t\t\"OffsetAccountType\": \"Bank\",\n\t\t\t\"PaymentReference\": \"".$timbre."\",\n\t\t\t\"AccountDisplayValue\": \"\",\n\t\t\t\"OffsetAccountDisplayValue\": \"".$diarioCuentaContra."\",\n\t \t\t\"CreditAmount\": ".$montoFactura.",\n\t\t\t\"PaymentMethodName\": \"\",\n      \"CurrencyCode\": \"MXN\"\n}",
        // CURLOPT_HTTPHEADER => array(
        // "authorization: Bearer ".$this->token."",
        // "content-type: application/json",
        // ),
        // ));

        // $response2 = curl_exec($curl);
        // $err = curl_error($curl);
        // $response2=json_decode($response2); 

        // curl_setopt_array($curl, array(
        // CURLOPT_URL => "https://".DYNAMICS365."/data/CustomerPaymentJournalLines(dataAreaId=%27".COMPANY."%27,LineNumber=1,JournalBatchNumber=%27".$response->JournalBatchNumber."%27)",
        // CURLOPT_RETURNTRANSFER => true,
        // CURLOPT_ENCODING => "",
        // CURLOPT_MAXREDIRS => 10,
        // CURLOPT_TIMEOUT => 30,
        // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        // CURLOPT_CUSTOMREQUEST => "PATCH",
        // CURLOPT_POSTFIELDS => "{\n\t\t\t\"AccountDisplayValue\": \"".$cliente->value[0]->InvoiceCustomerAccountNumber."\"\n}",
        // CURLOPT_HTTPHEADER => array(
        // "authorization: Bearer ".$this->token."",
        // "content-type: application/json; odata.metadata=minimal",
        // "odata-version: 4.0"
        // ),
        // ));

        // $response3 = curl_exec($curl);
        // $err = curl_error($curl);
        // $response3=json_decode($response3); 

        // curl_setopt_array($curl, array(
        //  CURLOPT_URL => "https://".DYNAMICS365."/api/services/STF_INAX/STF_DiariosPagos/postPaymentJournal",
        //  CURLOPT_RETURNTRANSFER => true,
        //  CURLOPT_ENCODING => "",
        //  CURLOPT_MAXREDIRS => 10,
        //  CURLOPT_TIMEOUT => 30,
        //  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //  CURLOPT_CUSTOMREQUEST => "POST",
        //  CURLOPT_POSTFIELDS => "{\n\t\"journal\": \"".$response->JournalBatchNumber."\",\n\t\"company\": \"".COMPANY."\"\n}",
        //  CURLOPT_HTTPHEADER => array(
        //  "authorization: Bearer ".$this->token."",
        //  "content-type: application/json"
        //  ),
        //  ));

        //  $response4 = curl_exec($curl);
        //  $err = curl_error($curl);

            





        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/data/SalesOrderHeadersV2?%24filter=SalesOrderNumber%20eq%20'".$factura."'&%24select=InvoiceCustomerAccountNumber,SATPaymMethod_MX",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
        "authorization: Bearer ".$this->token."",
        "content-type: application/json;"
        ),
        ));
        
        $response9 = curl_exec($curl);
        $err = curl_error($curl);
        $response9 = json_decode($response9);

        $customer = $response9->value[0]->InvoiceCustomerAccountNumber; 
        $tipopago = $response9->value[0]->SATPaymMethod_MX; 

        // $tieneCredito=Application_Model_InicioModel::reviewCreditLimit($customer,$factura,$montoFactura,$tipopago);
        // if(!$tieneCredito['status']){
        // return array('resultado'=>$tieneCredito['status'],'respuesta'=>$response->JournalBatchNumber);
        // }

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/data/JournalNames?%24select=DocumentNumber&%24filter=Name%20eq%20'".$journalName."'",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
        "authorization: Bearer ".$this->token."",
        "content-type: application/json;"
        ),
        ));
        
        $response45 = curl_exec($curl);
        $err = curl_error($curl);
        $response45=json_decode($response45);
        // print_r($response45);exit();
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
            );

        // print_r($sucursales[$response->value[0]->DocumentNumber]);exit();
        //Proceso de agregar los digitos de la tarjeta
        $digitosTarjeta = 'T-'.$digitosTarjeta;
        $descripcionCabezera = $descripcion;
        $descripcionLineas = $descripcion;
        if($digitosTarjeta != ''){
          $descripcionCabezera .= ', '.$digitosTarjeta;
        }
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/data/CustomerPaymentJournalHeaders",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "{\n\t \t\t\"dataAreaId\": \"".COMPANY."\",\n      \"JournalName\": \"".$journalName."\",\n      \"Description\": \"".$descripcionCabezera."\"\n}",
        CURLOPT_HTTPHEADER => array(
        "authorization: Bearer ".$this->token."",
        "content-type: application/json; odata.metadata=minimal",
        "odata-version: 4.0"
        ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $response=json_decode($response);  
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://".DYNAMICS365."/data/CustomerPaymentJournalLines?%24filter=STF_RefSalesId%20eq%20'".$factura."'",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,   
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$this->token."",
            "content-type: application/json;" 
            ),
            ));
            //DefaultDimensionsForOffsetAccountDisplayValue
            $response2tr = curl_exec($curl);
            $err = curl_error($curl);
            
            $response2tr = json_decode($response2tr);  
            //print_r($response2);
            $totalDiario=0;
            for($i=0;$i<sizeof($response2tr->value);$i++){
                $totalDiario+=$response2tr->value[$i]->CreditAmount;
            } 
            
            /////

       //  print_r($response);
        // print_r("{\n\t \t\t\"dataAreaId\": \"".COMPANY."\",\n\t\t\t\"LineNumber\": 1,\n      \"JournalBatchNumber\": \"".$response->JournalBatchNumber."\",\n\t\t\t\"OffsetAccountType\": \"Bank\",\n\t\t\t\"PaymentReference\": \"".$factura."\",\n\t\t\t\"STF_RefSalesId\": \"".$factura."\",\n\t\t\t\"AccountDisplayValue\": \"\",\n\t\t\t\"OffsetAccountDisplayValue\": \"".$diarioCuentaContra."\",\n\t \t\t\"CreditAmount\": ".$montoFactura.",\n\t\t\t\"PaymentMethodName\": \"".$diarioFPago."\",\n\t\t\t\"TransactionText\": \"".$descripcion.", ".$response->JournalBatchNumber."\",\n\"CurrencyCode\": \"MXN\"\n");
        $descripcionLineas .= ', '.$response->JournalBatchNumber;
        if($digitosTarjeta != ''){
          $descripcionLineas .= ', '.$digitosTarjeta;
        }
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/data/CustomerPaymentJournalLines",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,   
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "{\n\t \t\t\"dataAreaId\": \"".COMPANY."\",\n\t\t\t\"LineNumber\": 1,\n      \"JournalBatchNumber\": \"".$response->JournalBatchNumber."\",\n\t\t\t\"OffsetAccountType\": \"Bank\",\n\t\t\t\"PaymentReference\": \"".$factura."\",\n\t\t\t\"STF_RefSalesId\": \"".$factura."\",\n\t\t\t\"AccountDisplayValue\": \"\",\n\t\t\t\"OffsetAccountDisplayValue\": \"".$diarioCuentaContra."\",\n\t \t\t\"CreditAmount\": ".$montoFactura.",\n\t\t\t\"PaymentMethodName\": \"\",\n\t\t\t\"TransactionText\": \"".$descripcionLineas."\",\n\"CurrencyCode\": \"".$currency."\"\n}",
        CURLOPT_HTTPHEADER => array(
        "authorization: Bearer ".$this->token."",
        "content-type: application/json; odata.metadata=minimal",
        "odata-version: 4.0"
        ),
        ));
        $response2 = curl_exec($curl);
        $err = curl_error($curl);
       // print_r($response2);
        // print_r("https://".DYNAMICS365."/data/CustomerPaymentJournalLines(dataAreaId=%27".COMPANY."%27,LineNumber=1,JournalBatchNumber=%27".$response->JournalBatchNumber."%27)");
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/data/CustomerPaymentJournalLines(dataAreaId=%27".COMPANY."%27,LineNumber=1,JournalBatchNumber=%27".$response->JournalBatchNumber."%27)",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "PATCH",
        CURLOPT_POSTFIELDS => "{\n\t\t\t\"AccountDisplayValue\": \"".$customer."\"\n}",
        CURLOPT_HTTPHEADER => array(
        "authorization: Bearer ".$this->token."",
        "content-type: application/json; odata.metadata=minimal",
        "odata-version: 4.0"
        ),
        ));

        $response3 = curl_exec($curl);
        $err = curl_error($curl);
        
        /////////////////////////////////objectos de la cabecera y lineas para parchar/////////////////////////////
        $postfieldLines = (object) array(  'dataAreaId' => COMPANY,
                                        'LineNumber' => 1,
                                        'JournalBatchNumber' => $response->JournalBatchNumber,
                                        'OffsetAccountType' => "Bank",
                                        'PaymentReference' => $factura,
                                        'STF_RefSalesId' => $factura,
                                        'AccountDisplayValue' => "",
                                        'OffsetAccountDisplayValue' => $diarioCuentaContra,
                                        'CreditAmount' => (float)$montoFactura,
                                        'PaymentMethodName' => "",
                                        'TransactionText' => $descripcionLineas.", ".$referenciap,
                                        'CurrencyCode' => $currency,
                                        'AccountDisplayValue' => $customer
                                      );
        $postfieldsHeader = (object) array(  'dataAreaId' => COMPANY,
                                          'Description' => $descripcionCabezera.", ".$referenciap
                                        );
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////
        $a = $response->JournalBatchNumber;
        $validoDiario = Application_Model_InicioModel::validarDiarios($a);
        if ($validoDiario){
            curl_setopt_array($curl, array(
                 CURLOPT_URL => "https://".DYNAMICS365."/api/services/STF_INAX/STF_DiariosPagos/postPaymentJournal",
                 CURLOPT_RETURNTRANSFER => true,
                 CURLOPT_ENCODING => "",
                 CURLOPT_MAXREDIRS => 10,
                 CURLOPT_TIMEOUT => 30,
                 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                 CURLOPT_CUSTOMREQUEST => "POST",
                 CURLOPT_POSTFIELDS => "{\n\t\"journal\": \"".$response->JournalBatchNumber."\",\n\t\"company\": \"".COMPANY."\"\n}",
                 CURLOPT_HTTPHEADER => array(
                 "authorization: Bearer ".$this->token."",
                 "content-type: application/json"
                 ),
            ));

            $response4 = curl_exec($curl);
            $err = curl_error($curl);
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
                        "authorization: Bearer ".$this->token."",
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

//////////  CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesOrderHeaders?%24filter=SalesOrderNumber%20eq%20'".$ov."'&%24select=SalesOrderName%2COrderingCustomerAccountNumber%2COrderTakerPersonnelNumber",
         

            
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://".DYNAMICS365."/data/SalesOrderLines?=&%24select=LineAmount&%24filter=SalesOrderNumber%20eq%20'".$factura."'",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$this->token."",
            "content-type: application/json;"
            ),
            ));
            
            $response45x = curl_exec($curl);
            $err = curl_error($curl);
            $response45x=json_decode($response45x);
           // print_r($response45x->value[0]->LineAmount);
            $total=0;
            for($i=0;$i<sizeof($response45x->value);$i++){
               $total+=$response45x->value[$i]->LineAmount;
            }
           //print_r($total);
          //  print_r($response45x->value);
        //print_r($response3->error);exit();
        curl_close($curl);
       // print_r($response4);exit();
        if($response3->error){   

            return array('resultado'=>'bad','respuesta'=>'Algun dato esta mal','dato1'=>$response,'dato2'=>$response2,'dato3'=>$response3);
        }else{
            if((($total*1.16)-($totalDiario*1)+3)<($montoFactura*1)){
            include (LIBRARY_PATH.'/includes/phpMailer/PHPMailerAutoload.php');
            $mail = new PHPMailer;
            $mail->isSMTP();
            $mail->SMTPDebug = 0;
            $mail->Debugoutput = 'html';
            $mail->Host = 'ssl://smtp.gmail.com';//'187.141.228.93:25';
            $mail->Port = '465';//25;
            $mail->SMTPSecure = 'ssl';
            $mail->SMTPAuth = true;
            $mail->Username = "notificaciones@avanceytec.com.mx";
            $mail->Password = "avanceytec";
            $mail->FromName = "Avance y Tecnología en Plásticos";  
            //$mail->addAddress("mfoglio@avanceytec.com.mx");            
            $mail->addAddress("earmendariz@avanceytec.com.mx");
            // $mail->addCC("sistemas12@avanceytec.com.mx");
            //$mail->addCC("sistemas11@avanceytec.com.mx");
            $mail->Subject = "Diario:".$response->JournalBatchNumber;
            $mail->msgHTML('Se ha creado el diario: '.$response->JournalBatchNumber.' con un pago '.$JournalName.' por la cantidad de $'.$montoFactura.' el cual exede el total de la '.$factura.'<br>'.'Descripción: '.$descripcion.'<br>'.'Usuario: '.$_SESSION['userInax']);
            $mail->AltBody = 'Envio de guía';
            if (!$mail->send()) {
             echo 'error';

             //.'Usuario: '.$_SESSION['userInax']
              //  return "Mailer Error: " . $mail->ErrorInfo;
            } else {
          //   echo 'enviado';
              //  return "enviado";
            }
        }
            try{
                Application_Model_InicioModel::$log = new Application_Model_Userinfo();
                Application_Model_InicioModel::$log->kardexLog("Creacion de diario: ".$response->JournalBatchNumber, "{\"dataAreaId\": \"".COMPANY."\",\"LineNumber\": 1,\"JournalBatchNumber\": \"".$response->JournalBatchNumber."\",\"OffsetAccountType\": \"Bank\",\"PaymentReference\": \"".$factura."\",\"STF_RefSalesId\": \"".$factura."\",\"AccountDisplayValue\": \"\",\"OffsetAccountDisplayValue\": \"".$diarioCuentaContra."\",\"CreditAmount\": ".$montoFactura.",\"PaymentMethodName\": \"\",\"TransactionText\": \"".$descripcion.", ".$response->JournalBatchNumber.", ".$factura."\",\"CurrencyCode\": \"".$currency."\"}",$response->JournalBatchNumber,1,'Creacion de diario');
            }catch(Exception $ex){
                
            }
            return array('resultado'=>'ok','respuesta'=>$response->JournalBatchNumber,'dato1'=>$response,'dato2'=>$response2,'dato3'=>$response3);
        }

}


    public function getFacturasSaldo(){
        try{
            ini_set('memory_limit', '-1');
            // $arr=$this->db->Query(FACTURAS_SALDO);

            $curl = curl_init();
            
            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://".DYNAMICS365."/data/SalesInvoiceHeaders?%24filter=dataAreaId%20eq%20'".COMPANY."'&%24select=InvoiceNumber%2CTotalInvoiceAmount%2CInvoiceCustomerAccountNumber&%24top=1000&%24orderby=InvoiceNumber%20desc",
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
            "content-type: application/json",
            ),
            ));
            
            $arr = curl_exec($curl);
            $err = curl_error($curl);
            $arr = json_decode($arr);
            curl_close($curl);
            
            // if ($err) {
            // echo "cURL Error #:" . $err;
            // } else {
            // echo $response;
            // }

            $res=[];
            foreach ( $arr->value as $key => $value) {                
                $total=(double)$value->TotalInvoiceAmount;
                if($total >0){
                    $res[]=$value;
                }
            }
            //print_r($res);exit();
            return $res;
        }
        catch (Exception $e){
            return $e;
        }
        
    }
}