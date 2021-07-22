<?php

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
ini_set('display_errors', '1'); 

class Application_Model_IndexModel {
    public $db;
    public $_adapter;
    public static $token;

    public function __construct(array $options = null){
        include (LIBRARY_PATH."/includes/SSP.php");
        if (is_array($options)) {
            $this->setOptions($options);
        }
        /**/ 
        // $token = new Token(); // Dynamic Token 
        // $tokenTemp = $token->getToken(); // Dynamic Token 
        // $this->token = $tokenTemp[0]->Token; // Dynamic Token 

        $this->db = new Application_Model_UserinfoMapper();
        $this->_adapter = $this->db->getAdapter();
        $query=$this->_adapter->query(ANSI_NULLS);
        $query=$this->_adapter->query(ANSI_WARNINGS);
        $query->execute();
        return $this->_adapter;
    }

    public static function getDataPaymentOv($ov){
        $query=$this->_adapter->prepare(DATOS_PAGO_OV);
        $query->bindParam(1,$ov);
        $query->execute();
        return $query->fetchAll();
    }

    public static function getDireccionOfSitio($sitio) {
        // $db = new Application_Model_UserinfoMapper();
        // $_adapter = $db->getAdapter();
        $conn  = new DB_ConexionExport();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $querySucu = $conn->prepare("SELECT * FROM DBO.AYT_Domicilios_sucursales WHERE sitio = ? ;");    
        $querySucu->bindParam(1,$sitio);
        $querySucu->execute();
        $resultSucu = $querySucu->fetchAll();
          if (! empty($resultSucu)) {
              $datosSucu = $resultSucu;
          } else {
              $datosSucu = 'NoResults';
          }
        return $datosSucu;
    }

    public static function getMontoEtiqueta($ov) {
        // $query = $this->_adapter->prepare(MONTO_ETIQUETA);
        // $query->bindParam(1,$sitio);
        // $query->execute();
        // $resultMonto = $query->fetchAll();  
        // $monto = '0';
        // if (!empty($resultMonto)) {
        //     foreach ($resultMonto as $key => $value) {
        //         $monto +=  strval(($value['MONTO']));
        //     }            
        // }
        $token = new Token();
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesOrderLines?%24filter=SalesOrderNumber%20eq%20'".$ov."'&%24select=ItemNumber%2CLineAmount",
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

        $responseLineas = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
          $resultLineas = "cURL Error #:" . $err;
        } else {
            $resultLineas = json_decode($responseLineas);
            $monto = 0;
            if ( isset($resultLineas->value) && !empty($resultLineas->value) ){
                foreach($resultLineas->value as $Data){
                    curl_setopt_array(CURL1, array(
                        CURLOPT_URL => "https://".DYNAMICS365."/Data/ReleasedDistinctProducts?%24select=ProductType&%24filter=ItemNumber%20eq%20'".$Data->ItemNumber."'",
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
                    $itemEtiqueta = substr($Data->ItemNumber, 0,4);
                    if ($resultPriceProd->value[0]->ProductType != 'Service' && $itemEtiqueta != '9999'){
                        $monto += strval($Data->LineAmount);
                    }
                }
            }
        }
        return $monto.'';
    }

    public static function getDatosEtiqueta($user,$sitio,$ov,$isCot = false) {
        $datosSucu = Application_Model_IndexModel::getDireccionOfSitio($sitio);
        $monto = Application_Model_IndexModel::getMontoEtiqueta($ov);
        $curl = curl_init();
        if(empty($monto)){$monto='0.0';}
        // $queryCliente = $this->_adapter->prepare("SELECT SALES.EMAIL,SALES.CUSTACCOUNT,DBO.getClienteNombre(SALES.CUSTACCOUNT) AS 'NOMBRECLIENTE',DBO.vendedorName(DBO.getRecidDynamicUser( ? )) AS 'NOMBREVENDEDOR',CUST.RFC_MX AS RFC,LOGEL.LOCATOR AS TELEFONO,LOGEL.LOCATOREXTENSION AS EXTENSION FROM SALESTABLE SALES INNER JOIN CUSTTABLE CUST ON (CUST.ACCOUNTNUM = SALES.CUSTACCOUNT) LEFT JOIN DIRPARTYTABLE DIRP ON (DIRP.RECID = CUST.PARTY) LEFT JOIN LOGISTICSELECTRONICADDRESS LOGEL ON (LOGEL.RECID = DIRP.PRIMARYCONTACTPHONE) WHERE SALES.SALESID = ? ;");
        // $queryCliente->bindParam(1,$user);
        // $queryCliente->bindParam(2,$ov);
        // $queryCliente->execute();
        // $resultOV = $queryCliente->fetchAll();
        // if (! empty($resultOV)) {
        //     $datosOV=$resultOV;
        //     $queryDirCliente = $this->_adapter->prepare("SELECT T0.ACCOUNTNUM,T5.ADDRESS,T5.RECID,(CASE T6.NAME WHEN 'Business' THEN 'Negocio' WHEN 'Delivery' THEN 'Entrega' WHEN 'Fixed asset' THEN 'Activo Fijo' WHEN 'Home' THEN 'Particular' WHEN 'Other' THEN 'Otros' WHEN 'Invoice' THEN 'Facturacion' WHEN 'Payment' THEN 'Pago' WHEN 'Remit to' THEN 'Remitir a'  WHEN 'Service' THEN 'Servicio' WHEN 'Third party shipping' THEN 'Direccion de envio a terceros' ELSE T6.NAME END) AS 'PROPOSITO',ISNULL(T5.STREET,'NoDefinido') AS STREET,ISNULL(T7.NAME,'NoDefinido') AS COUNTY,ISNULL(T8.NAME,'NoDefinido') AS STATE ,ISNULL(T5.CITY,'NoDefinido') AS CITY,ISNULL(T5.ZIPCODE,'NoDefinido') AS ZIPCODE,ISNULL(T10.SHORTNAME,'NoDefinido') AS PAIS FROM CUSTTABLE T0 INNER JOIN DIRPARTYTABLE T1 ON T0.PARTY=T1.RECID INNER JOIN DIRPARTYLOCATION T2 ON T2.PARTY=T1.RECID INNER JOIN LOGISTICSLOCATION T3 ON T2.LOCATION=T3.RECID INNER JOIN DIRPARTYLOCATIONROLE T4 ON T4.PARTYLOCATION=T2.RECID INNER JOIN LOGISTICSPOSTALADDRESS T5 ON T5.LOCATION=T3.RECID AND T5.VALIDTO >= GETDATE() INNER JOIN LOGISTICSLOCATIONROLE T6 ON T4.LOCATIONROLE=T6.RECID LEFT JOIN LOGISTICSADDRESSCOUNTY T7 ON T7.COUNTYID = T5.COUNTY LEFT JOIN LOGISTICSADDRESSSTATE T8 ON T8.STATEID = T5.STATE  LEFT JOIN LOGISTICSADDRESSCOUNTRYREGION T9 ON T9.COUNTRYREGIONID = T8.COUNTRYREGIONID LEFT JOIN LOGISTICSADDRESSCOUNTRYREGIONTRANSLATION T10 ON T10.COUNTRYREGIONID = T9.COUNTRYREGIONID AND T10.LANGUAGEID = 'es' WHERE T0.ACCOUNTNUM = ? GROUP BY T0.ACCOUNTNUM,T5.[ADDRESS],T5.RECID,T3.DESCRIPTION,T2.ISPRIMARY,T0.PARTY,T6.NAME,T7.NAME,T5.STREET,T5.STATE,T5.CITY,T5.ZIPCODE,T8.NAME,T10.SHORTNAME ORDER BY T2.ISPRIMARY DESC;");
        //     $queryDirCliente->bindParam(1,$resultOV[0]['CUSTACCOUNT']);
        //     $queryDirCliente->execute();
        //     $resultDIRS = $queryDirCliente->fetchAll();
        //     if (! empty($resultDIRS)) {$datosDIRS = $resultDIRS;} 
        //     else {$datosDIRS = 'NoResults'; }
        // } else {
        //     $datosOV = 'NoResults'; $datosDIRS = 'NoResults';
        // }
        ///////////////////////////datos OV //////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $token = new Token();

        if($isCot){
            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesQuotationHeaders?%24select=InvoiceCustomerAccountNumber,SalesQuotationName,QuotationTakerPersonnelNumber&%24filter=SalesQuotationNumber%20eq%20'".$ov."'",
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

            $responseOrder = curl_exec($curl);
            $err = curl_error($curl);
        }else{
            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesOrderHeaders?%24filter=SalesOrderNumber%20eq%20'".$ov."'&%24select=SalesOrderName%2COrderingCustomerAccountNumber%2COrderTakerPersonnelNumber",
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

            $responseOrder = curl_exec($curl);
            $err = curl_error($curl);
        }
        // curl_close($curl);

        if ($err) {
          $result = "cURL Error #:" . $err;
        } else {
            $result = json_decode($responseOrder);
            $clienteCod = !$isCot? $result->value[0]->OrderingCustomerAccountNumber : $result->value[0]->InvoiceCustomerAccountNumber;
            $clienteName = !$isCot? $result->value[0]->SalesOrderName : $result->value[0]->SalesQuotationName;
            $secretarioID = !$isCot? $result->value[0]->OrderTakerPersonnelNumber : $result->value[0]->QuotationTakerPersonnelNumber;
            // $curl = curl_init();
            ////////////////////////////entity/////////////////////////////////////////////////////////////////////////////////////
            // curl_setopt_array($curl, array(
            //     CURLOPT_URL => "https://".DYNAMICS365."/Data/CustomersV3?%24filter=CustomerAccount%20eq%20'".$clienteCod."'&%24select=RFCNumber%2CPartyNumber%2CPrimaryContactEmail",
            //     CURLOPT_RETURNTRANSFER => true,
            //     CURLOPT_ENCODING => "",
            //     CURLOPT_MAXREDIRS => 10,
            //     CURLOPT_TIMEOUT => 30,
            //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //     CURLOPT_CUSTOMREQUEST => "GET",
            //     CURLOPT_POSTFIELDS => "",
            //     CURLOPT_HTTPHEADER => array(
            //       "accept: application/json",
            //       "authorization: Bearer ".$token->getToken()[0]->Token."",
            //       "content-type: application/json",
            //       "x-total-count: application/json"
            //     ),
            // ));

            // $responseRfcCli = curl_exec($curl);
            // $err = curl_error($curl);

            // curl_close($curl);
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            ////////////////////////////////////base de datos//////////////////////////////////////////////////////////////////////////
            $db = new Application_Model_UserinfoMapper();
            $_adapter = $db->getAdapter();
            (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $query = $conn->prepare("SELECT T0.CustomerAccount
											  ,T0.OrganizationName
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
											  ,T0.isBlocked
											  ,T0.CustomerGroupId
											  ,T0.PartyNumber
											  ,T0.LineDiscountCode
											  ,T0.DiscountPriceGroupId
											  ,ISNULL(T1.puntosAvance,0) AS puntosAvance 
										FROM AYT_CustCustomerV3Staging  T0
										LEFT JOIN PuntosAvanceLealtad T1 ON (T0.CustomerAccount COLLATE DATABASE_DEFAULT = T1.CodigoCliente COLLATE DATABASE_DEFAULT)
										WHERE T0.CustomerAccount = '".$clienteCod."';");
            $query->execute();
            $responseRfcCli = $query->fetchAll();
            $err = false;
            if (empty($responseRfcCli)){
                $err = true;
            }
            ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            if ($err) {
              $resultClienteRFC = "cURL Error #:" . $err;
            } else {
                ///////////////////////////////////entity////////////////////////////////////////////////////////////////////////////
                // $resultClienteRFC = json_decode($responseRfcCli);
                // $rfcCliente = $resultClienteRFC->value[0]->RFCNumber;
                // $partyNumber = $resultClienteRFC->value[0]->PartyNumber;
                // $emailCli = $resultClienteRFC->value[0]->PrimaryContactEmail;
                // $curl = curl_init();
                ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                //////////////////////////////////////////base de datos//////////////////////////////////////////////////////////////
                $resultClienteRFC = $responseRfcCli;
                $rfcCliente = $resultClienteRFC[0]['RFCNumber'];
                $partyNumber = $resultClienteRFC[0]['PartyNumber'];
                $emailCli = $resultClienteRFC[0]['PrimaryContactEmail'];
                $telefonoCli = $resultClienteRFC[0]['PrimaryContactPhone'];
                $extensionCli = '';
                ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

                /////////////////////////////////////////////////////////////////entity/////////////////////////////////////////////
                // curl_setopt_array($curl, array(
                //     CURLOPT_URL => "https://".DYNAMICS365."/Data/PartyContacts?%24filter=PartyNumber%20eq%20'".$partyNumber."'%20and%20Type%20eq%20Microsoft.Dynamics.DataEntities.LogisticsElectronicAddressMethodType'Phone'&%24select=Locator%2CLocatorExtension",
                //     CURLOPT_RETURNTRANSFER => true,
                //     CURLOPT_ENCODING => "",
                //     CURLOPT_MAXREDIRS => 10,
                //     CURLOPT_TIMEOUT => 30,
                //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                //     CURLOPT_CUSTOMREQUEST => "GET",
                //     CURLOPT_POSTFIELDS => "",
                //     CURLOPT_HTTPHEADER => array(
                //     "accept: application/json",
                //     "authorization: Bearer ".$token->getToken()[0]->Token."",
                //     "content-type: application/json",
                //     "x-total-count: application/json"
                //     ),
                // ));

                // $responseContactPH = curl_exec($curl);
                // $err = curl_error($curl);

                // curl_close($curl);
                ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                if ($err) {
                    $resultContactPH = "cURL Error #:" . $err;
                } else {
                    ///////////////////////////////entity////////////////////////////////////////////////
                    // $resultContactPH = json_decode($responseContactPH);
                    // $telefonoCli = $resultContactPH->value[0]->Locator;
                    // $extensionCli = $resultContactPH->value[0]->LocatorExtension;
                    //////////////////////////////////////////////////////////////////////////////////////

                    // $curl = curl_init();
                    $CURLOPT_URL = "https://".DYNAMICS365."/Data/Employees?%24filter=PersonnelNumber%20eq%20'".$secretarioID."'%20and%20EmploymentLegalEntityId%20eq%20'".$_SESSION['company']."'&%24select=NameAlias%2CPersonnelNumber%2CEducation%2CName";
                    curl_setopt_array($curl, array(
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

                    $responseSecre = curl_exec($curl);
                    $err = curl_error($curl);

                    
                  
                    if ($err) {
                        print_r($response);
                        exit();
                        $resultSecre = "cURL Error #:" . $err;
                    } else {
                        $resultSecre = json_decode($responseSecre);
                        $secretarioVtas = $resultSecre->value[0]->Name;
                    }
                }
            }
            
            $datosOV = array(
                            array(
                                'EMAIL' => $emailCli,
                                'CUSTACCOUNT' => $clienteCod,
                                'NOMBRECLIENTE' => $clienteName,
                                'NOMBREVENDEDOR' => $secretarioVtas,
                                'RFC' => $rfcCliente,
                                'TELEFONO' => $telefonoCli,
                                'EXTENSION' => $extensionCli
                            )
                        );
        }
        curl_close($curl);
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $arrayResult = array('datosSucu' => $datosSucu,'datosCte' => $datosOV,'datosDirs' => $_SESSION['datosDirs'],'datosMonto' => $monto);
        return $arrayResult;
    }

    public function getDatosPropo($user,$ov,$isCot = false){ 
        // $queryCliente = $this->_adapter->prepare(DATOS_ETIQUETA);
        // $queryCliente->bindParam(1,$user);
        // $queryCliente->bindParam(2,$ov);
        // $queryCliente->execute();
        // $resultOV = $queryCliente->fetchAll();
        //////////////datos iniciales de la etiqueta////////////////////////////////////////////////////
        $token = new Token();
        $curl = curl_init();
        if($isCot){
            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesQuotationHeaders?%24filter=SalesQuotationNumber%20eq%20'".$ov."'&%24select=InvoiceCustomerAccountNumber",
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

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
            $resultOV = "cURL Error #:" . $err;
            } else {
            $resultOV = json_decode($response);
            }
            ////////////////////////////////////////////////////////////////////////////////////////////////
            $clte = $resultOV->value[0]->InvoiceCustomerAccountNumber;
        }else{
            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesOrderHeaders?%24select=SalesOrderNumber%2COrderingCustomerAccountNumber%2COrderTakerPersonnelNumber&%24filter=SalesOrderNumber%20eq%20'".$ov."'",
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

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
            $resultOV = "cURL Error #:" . $err;
            } else {
            $resultOV = json_decode($response);
            }
            ////////////////////////////////////////////////////////////////////////////////////////////////
            $clte = $resultOV->value[0]->OrderingCustomerAccountNumber;
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://".DYNAMICS365."/Data/CustomerPostalAddresses?%24filter=CustomerAccountNumber%20eq%20'".$clte."'%20and%20IsPostalAddress%20eq%20Microsoft.Dynamics.DataEntities.NoYes'Yes'&%24select=FormattedAddress%2CAddressCity%2CAddressStreet%2CAddressZipCode%2CAddressState%2CAddressCountryRegionId%2CAddressLocationRoles%2CAddressCountyId%2CAddressLocationId",
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

        $responseDirs = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
          $resultDirs = "cURL Error #:" . $err;
        } else {
          $resultDirs = json_decode($responseDirs);
        }
        $dataArr = [];
        if (isset($resultDirs->value) && !empty($resultDirs->value)){
            foreach($resultDirs->value as $Data){
                $curl = curl_init();

                curl_setopt_array($curl, array(
                  CURLOPT_URL => "https://".DYNAMICS365."/Data/AddressCounties?%24filter=CountyId%20eq%20'".$Data->AddressCountyId."'%20and%20StateId%20eq%20'".$Data->AddressState."'",
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

                $responseColonia = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                  $resultColonia = "cURL Error #:" . $err;
                } else {
                  $resultColonia = json_decode($responseColonia);
                }
                $propositos = Application_Model_IndexModel::translatePropo($Data->AddressLocationRoles);
                $arrTemp = array(
                            'PROPOSITO'    => $propositos['transPropo'],
                            'CALLE'        => $Data->AddressStreet,
                            'COLONIA'      => $resultColonia->value[0]->Description,
                            'CIUDAD'       => $Data->AddressCity,
                            'ESTADO'       => $Data->AddressState,
                            'PAIS'         => $Data->AddressCountryRegionId,
                            'CODIGOPOSTAL' => $Data->AddressZipCode,
                            'LABEL'        => $propositos['Proposito'],
                            'TEXT'         => $propositos['Proposito'],
                            'ADDRESSLOCATIONID' => $Data->AddressLocationId
                        );
                array_push($dataArr, $arrTemp);
            }
        }
        // $propositos = $this->propositoEtiqueta($clte);
        $dataOpt = $dataArr;
        if (strpos($Data->AddressLocationRoles,';')) {
            $dataOpt['PROPOSITO'] = $propositos['arregloProp'];
        }
        $optPropo = '<option value="">Selecciona...</option>';
            foreach ($dataOpt as $DataOpt) {
                //if ($Data == 'Entrega') { $selected = 'selected'; } else { $selected = '';}
                if (strpos($Data->AddressLocationRoles,';') !== false) {
                    $optPropo .= '<option value="' . $DataOpt['PROPOSITO'] . '" data-addresslocationid="'.$DataOpt['ADDRESSLOCATIONID'].'">' . $DataOpt['PROPOSITO'] . '</option>';
                }else{
                    $optPropo .= '<option value="' . $DataOpt['PROPOSITO'] . '" data-addresslocationid="'.$DataOpt['ADDRESSLOCATIONID'].'">' . $DataOpt['PROPOSITO'] . '</option>';
                }
            }
            $optPropo .= '<option value="Otro">Otro</option>';
        // $queryDirCliente = $this->_adapter->prepare("SELECT T0.ACCOUNTNUM,T5.ADDRESS,T5.RECID,(CASE T6.NAME WHEN 'Business' THEN 'Negocio' WHEN 'Delivery' THEN 'Entrega' WHEN 'Fixed asset' THEN 'Activo Fijo' WHEN 'Home' THEN 'Particular' WHEN 'Other' THEN 'Otros'  WHEN 'Invoice' THEN 'Facturacion' WHEN 'Payment' THEN 'Pago' WHEN 'Remit to' THEN 'Remitir a' WHEN 'Service' THEN 'Servicio' WHEN 'Third party shipping' THEN 'Direccion de envio a terceros' ELSE T6.NAME END) AS 'PROPOSITO',ISNULL(T5.STREET,'NoDefinido') AS STREET,ISNULL(T7.NAME,'NoDefinido') AS COUNTY, ISNULL(T8.NAME,'NoDefinido') AS STATE,ISNULL(T5.CITY,'NoDefinido') AS CITY, ISNULL(T5.ZIPCODE,'NoDefinido') AS ZIPCODE,ISNULL(T10.SHORTNAME,'NoDefinido') AS PAIS FROM CUSTTABLE T0 INNER JOIN DIRPARTYTABLE T1 ON T0.PARTY=T1.RECID INNER JOIN DIRPARTYLOCATION T2 ON T2.PARTY=T1.RECID INNER JOIN LOGISTICSLOCATION T3 ON T2.LOCATION=T3.RECID INNER JOIN DIRPARTYLOCATIONROLE T4 ON T4.PARTYLOCATION=T2.RECID INNER JOIN LOGISTICSPOSTALADDRESS T5 ON T5.LOCATION=T3.RECID AND T5.VALIDTO >= GETDATE() INNER JOIN LOGISTICSLOCATIONROLE T6 ON T4.LOCATIONROLE=T6.RECID LEFT JOIN LOGISTICSADDRESSCOUNTY T7 ON T7.COUNTYID = T5.COUNTY LEFT JOIN LOGISTICSADDRESSSTATE T8 ON T8.STATEID = T5.STATE LEFT JOIN LOGISTICSADDRESSCOUNTRYREGION T9 ON T9.COUNTRYREGIONID = T8.COUNTRYREGIONID LEFT JOIN LOGISTICSADDRESSCOUNTRYREGIONTRANSLATION T10 ON T10.COUNTRYREGIONID = T9.COUNTRYREGIONID AND T10.LANGUAGEID = 'es' WHERE T0.ACCOUNTNUM = '$clte' GROUP BY T0.ACCOUNTNUM,T5.[ADDRESS],T5.RECID,T3.DESCRIPTION,T2.ISPRIMARY,T0.PARTY,T6.NAME ,T7.NAME,T5.STREET,T5.STATE,T5.CITY,T5.ZIPCODE,T8.NAME,T10.SHORTNAME ORDER BY T2.ISPRIMARY DESC;");
        // $queryCliente->bindParam(1,$clte);
        // $queryDirCliente->execute();
        // $resultDIRS = $queryDirCliente->fetchAll();
        // if (! empty($resultDIRS)) { 
        //     $datosDIRS = $resultDIRS;} else { $datosDIRS = 'NoResults'; 
        // }
        
        $_SESSION['datosDirs'] = $dataArr;
        $result = array('optPropo' => $optPropo, 'datosDirs' => $dataArr );
        return $result;
    }

    public static function translatePropo($propositos){
        $propos = explode(';',$propositos);
        $result = '';
        $proprPrinc = 'noDefinido';
        $proposTrans = [];
        foreach($propos as $Data){
            switch ($Data) {
                case 'Delivery':
                    $result   .= 'Entrega;';
                    $resultArr = 'Entrega';
                break;                
                case 'Business':
                    $result   .= 'Negocio;';
                    $resultArr = 'Negocio';
                break;
                case 'Invoice':
                    $result    .= 'Factura;';
                    $resultArr  = 'Factura';
                    $propoPrinc = 'Factura';
                break;
            }
            array_push($proposTrans, $resultArr);
        }
        return array('transPropo' => substr($result, 0,-1) , 'Proposito' => $propoPrinc, 'arregloProp' => $proposTrans);
    }

    public function propositoEtiqueta($clte){
        $query = $this->_adapter->prepare(ETIQUETA);
        $query->bindParam(1,$clte);
        $query->execute();
        $result = $query->fetchAll();
        if (!empty($result)){
            $datos = $result;
        }else{
            $datos = 'NoResults';
        }
        return $datos;
    }
    
    public function getComparativa() {
        $fecha = new Date('Y-m-d');
        $queryComparativa = $this->_adapter->prepare("SELECT ISNULL(SUM(CASE WHEN (T0.STF_SALESIDWS = '') THEN 0 ELSE 1 END),0) AS CONTEOWS_VTA , ISNULL(SUM(CASE WHEN (T0.STF_SALESIDWS = '') THEN 1 ELSE 0 END),0) AS CONTEODYN_VTA, COUNT(T0.SALESID) AS TOTAL_VTA FROM SALESTABLE T0 WHERE DATEADD(HOUR,-7,T0.CREATEDDATETIME) BETWEEN ? AND ? AND T0.WORKERSALESTAKER = DBO.getRecidDynamicUser( ? );");
        $queryComparativa->bindParam(1,$fecha.' 00:00:00.00');
        $queryComparativa->bindParam(2,$fecha.' 23:59:59.00');
        $queryComparativa->bindParam(3,$_SESSION['userInax']);
        $queryComparativa->execute();
        $result = $queryComparativa->fetchAll();
        $queryComparativa = $this->_adapter->prepare("SELECT ISNULL(SUM(CASE WHEN (T0.STF_QUOTATIONIDWS = '') THEN 0 ELSE 1 END),0) AS CONTEOWS_COT, ISNULL(SUM(CASE WHEN (T0.STF_QUOTATIONIDWS = '') THEN 1 ELSE 0 END),0) AS CONTEODYN_COT, COUNT(T0.QUOTATIONID) AS TOTAL_COT  FROM SALESQUOTATIONTABLE T0 WHERE DATEADD(HOUR,-7,T0.CREATEDDATETIME) BETWEEN ? AND ? AND T0.WORKERSALESTAKER = DBO.getRecidDynamicUser('" . $_SESSION['userInax'] . "');");
        $queryComparativa->bindParam(1,$fecha.' 00:00:00.00');
        $queryComparativa->bindParam(2,$fecha.' 23:59:59.00');
        $queryComparativa->bindParam(3,$_SESSION['userInax']);
        $queryComparativa->execute();
        $resultCoti = $queryComparativa->fetchAll();
        if (! empty($result)) {
            $datosComp[0] = 'exito';
            $datosComp['WS_VTA'] = $result[0]['CONTEOWS_VTA'];
            $datosComp['DYN_VTA'] = $result[0]['CONTEODYN_VTA'];
            $datosComp['TOTAL_VTA'] = $result[0]['TOTAL_VTA'];
        }
        if (! empty($resultCoti)) {
           $datosComp['WS_COT'] = $resultCoti[0]['CONTEOWS_COT'];
           $datosComp['DYN_COT'] = $resultCoti[0]['CONTEODYN_COT'];
           $datosComp['TOTAL_COT'] = $resultCoti[0]['TOTAL_COT'];
        } 
        else{
            $datosComp[0] = 'NoResults';
        }
        return $datosComp;
    }

    static public function getOVporUsuario2($vendedor){
        $token = new Token();
        $entity = 'SalesQuotationHeaders';
         // $r = Application_Model_IndexModel::entities365($entity);
         /* $use=$_SESSION['userInax'];*/
         // $usuarioNum = Application_Model_IndexModel::idUsuario($vendedor);
        // $usuarioNum = Application_Model_IndexModel::idUsuario($vendedor);
          // $ar[] = ;
              //curl para party num
              $curl = curl_init();
  
                          curl_setopt_array($curl, array(
                          CURLOPT_URL => "https://".DYNAMICS365."/data/Workers?%24select=PersonnelNumber&%24filter=Education%20eq%20'".$vendedor."'",
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => "",
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 30,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => "GET",
                          CURLOPT_POSTFIELDS => "{\n\t\"SalesQuotationNumber\": \"COTV000019058\",\n\t\"dataAreaId\": \"atp\",\n\t\"ShippingSiteId\": \"CHIH\",\n\t\"ItemNumber\": \"5450-6011-0024\",\n\t\"RequestedSalesQuantity\": \"13\"\n}",
                          CURLOPT_HTTPHEADER => array(
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
                        //  echo $response;
                          }
  
  
              ///
  
  
        // print_r("https://tes-ayt.sandbox.operations.dynamics.com/data/Workers?%24select=PersonnelNumber&%24filter=Education%20eq%20'".$vendedor."'");exit();
         $usuarioNumx=json_decode($response);
         //print_r($usuarioNum);exit();
         //$usuarioNum=$usuarioNumx;
         $curl = curl_init();
        // print_r($usuarioNum->value[0]->PersonnelNumber);exit();
         curl_setopt_array($curl, array(
           CURLOPT_URL => "https://".DYNAMICS365."/data/SalesOrderHeadersV2?%24filter=OrderTakerPersonnelNumber%20eq%20'".$usuarioNumx->value[0]->PersonnelNumber."'&%24orderby=SalesOrderNumber%20desc&%24top=20",
           CURLOPT_RETURNTRANSFER => true,
           CURLOPT_ENCODING => "",
           CURLOPT_MAXREDIRS => 10,
           CURLOPT_TIMEOUT => 30,
           CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
           CURLOPT_CUSTOMREQUEST => "GET",
           CURLOPT_POSTFIELDS => "",
           CURLOPT_HTTPHEADER => array(
              "authorization: Bearer ".$token->getToken()[0]->Token."",
           ),
         ));
         
         $response2 = curl_exec($curl);
         $err = curl_error($curl);
         
         curl_close($curl);
         
         if ($err) {
           echo "cURL Error #:" . $err;
         } else {
           //echo $response;
         }
      //   print_r($response2);exit();
       $r=json_decode($response2);
       $usuarioNum=$usuarioNumx;
       // $usuarioNum = Application_Model_IndexModel::datosUsuario();
       //$usuarioNum = Application_Model_IndexModel::datosUsuario();
       foreach($r->value as $dat){
        // $journalNum = Application_Model_IndexModel::getjournal($dat->SalesOrderNumber);
        $journalNum="no";
          $entrega="";
          if($dat->DeliveryModeCode=='PAQUETERIA'){
  
              $buttonColor = array(1 => 'grey', 2 => 'yellow darken-1', 3 => 'blue', 4 => 'red', 5 => 'green', 6 => 'purple darken-3');      
                $labelOv = $dat->SalesOrderNumber;
                (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $parcelInfo = $conn->prepare("SELECT * FROM AYT_Paqueterias WHERE Ov = '".$labelOv."'");
                $parcelInfo->execute();
                $parcelInfo = $parcelInfo->fetchAll();
                $dataTooltip = $parcelInfo[0]['Status'] == 4 ? 'data-position="top" data-tooltip="'.$parcelInfo[0]['StopReason'].'"': $parcelInfo[0]['Status'] == 5 ? 'data-position="top" data-tooltip="'.$parcelInfo[0]['GuideNumber'].'"' : '';
  
              $lbl = "<a href=\"javascript:archivoAdjunto('".$dat->SalesOrderNumber."','ORDVTA');\">".$dat->DeliveryModeCode."</a></br>";

                $lbl = "<span class=\"deliveryModal\" data=\"".$dat->SalesOrderNumber."\">PAQUETERIA</span></br>";

                $desc = $parcelInfo[0]['GuideNumber'].'-'.$parcelInfo[0]['ParcelCompany'];

                if($desc == "WAITNG FOR OV CONVERSION-"){
                     
                    $desc = "";
                }

                $entrega="<div class=\"col l12 m12 s12\">
                            <p class=\"xxx\"><label style=\"color:blue;\"><strong>".$desc."
                                </strong></label></p>
                            <button class=\"btn tooltipped ".$buttonColor[$parcelInfo[0]['Status']]." \" ".$dataTooltip." onclick=\"mostrarModalEti('".$dat->SalesOrderNumber."','".$dat->DefaultShippingSiteId."','".IMAGES_PATH."')\"><i class=\"fa fa-1x fa-eye \"></i></button>
                         </div>";
           }
           else{
               $entrega=$dat->DeliveryModeCode;
           }
  
          $impresion = '';
          // print_r($dat->SalesOrderStatus);exit();
          if($dat->SalesOrderStatus == 'Invoiced'){
              $datosfac=Application_Model_FacturaModel::consultarFac($dat->SalesOrderNumber);
              $fecha = explode('T',$datosfac->value[0]->InvoiceDate);
              $fechaYMD = explode('-',$fecha[0]);
              $fechafin=$fechaYMD[1] . '/'.$fechaYMD[2] . '/'. $fechaYMD[0];
              // print_r($fechafin);exit('hola vaquero');
              $impresion ='
              <a href="http://inax.aytcloud.com/Facturacion365/Facturacion365.php?ov='.$dat->SalesOrderNumber.'&tipo=inax&invoiceId='.$datosfac->value[0]->InvoiceNumber.'&transDate='.$fechafin.'&custInvoiceAccount='.$datosfac->value[0]->InvoiceCustomerAccountNumber.'" target="_blank">Factura</a>
              <br>
              <a href="http://inax.aytcloud.com/Facturacion365/Facturacion365.php?ov='.$dat->SalesOrderNumber.'&tipo=inax&invoiceId='.$datosfac->value[0]->InvoiceNumber.'&transDate='.$fechafin.'&custInvoiceAccount='.$datosfac->value[0]->InvoiceCustomerAccountNumber.'&type=xml" download>XML</a>
              ';
              // $impresion='<a href="http://svr02:8989/Facturacion365/Facturacion365.php?ov='.$dat->SalesOrderNumber.'&amp;tipo=CLIENTE" target="_blank"><i class="fa fa-file-pdf-o" style="color:#840101;"></i> Factura</a>';
          }
          // if($dat->SalesOrderStatus == 'Delivered'){
          //     foreach ($_SESSION['factura'] as $key => $value) {
          //         if($value==1){
          //             $impresion = '<button class="btn blue-grey darken-3" onclick="mostrarModalPago(\''.$dat->SalesOrderNumber.'\',\''.$dat->InvoiceCustomerAccountNumber.'\',\''.$dat->PaymentTermsName./*$dat->SalesOrderNumber.'\',\''.$v['PACKINGSLIPIDJOUR'].'\',\''.$dat->DeliveryModeCode.*/'\')">Facturar</button>';
          //         }
          //     }
          // }
  
  
           $edita='<a style="color:red;" onclick="swal(\'ALTO\', \'No se permite editar\', \'error\');">'.$dat->SalesOrderNumber.'</a>';
          if($dat->ReleaseStatus=='Open' &&  $dat->SalesOrderStatus !='Canceled'){
              $edita="<a onclick=\"editarOV('".$dat->SalesOrderNumber."','".$dat->InvoiceCustomerAccountNumber."');\">".$dat->SalesOrderNumber."</a>";
          }
  
          $data="";
          if($journalNum == "no"){                
          if ($dat->SalesOrderStatus =='Backorder'){ 
              // $data = '<button class="btn blue-grey darken-3" id="GenerarRemision'.$dat->SalesOrderNumber.'" value="'.$dat->SalesOrderNumber.'" onclick="GenerarRemisionBtn(\''.$dat->DeliveryModeCode.'\',\''.$dat->SalesOrderNumber.'\',\''.$_SESSION['userInax'].'\')">Remisionar</button>';
              foreach ($_SESSION['factura'] as $key => $value) {
                  if($value==14){
                      $data = '<button class="btn blue-grey darken-3 btn-small" onclick="mostrarModalPago(\''.$dat->SalesOrderNumber.'\',\''.$dat->InvoiceCustomerAccountNumber.'\',\''.$dat->PaymentTermsName./*$dat->SalesOrderNumber.'\',\''.$v['PACKINGSLIPIDJOUR'].'\',\''.$dat->DeliveryModeCode.*/'\')" disabled>Diario Pago</button>';
                  }
              }
          }
          }
  
          $fechaReal = new DateTime($dat->OrderCreationDateTime);
          $fechaReal->modify('-7 hours');
          
          $fecha = explode('T',$dat->OrderCreationDateTime);
          $fechaYMD = explode('-',$fecha[0]);
          $fechaHMS = explode('Z',$fecha[1]);
  
          $nombre ="";
          if($dat->OrderTakerPersonnelNumber != ""){
              foreach($usuarioNum->value as $usuarNum => $val) {
                  if($dat->OrderTakerPersonnelNumber==$val->PersonnelNumber){                
                      $nombre=$val->NameAlias;
                  }
              }
          }
  
          $dat->ReleaseStatus = Application_Model_IndexModel::translate365($dat->ReleaseStatus);
          $dat->SalesOrderStatus = Application_Model_IndexModel::translate365($dat->SalesOrderStatus);
  
          $btnPDF = '';
          if ($dat->ReleaseStatus == 'Liberado') {
  
              $btnPDF = "<a href='../public/impresion-orden/?id=".$dat->SalesOrderNumber."' target='_blank'>
                          <button class=\"btn blue-grey darken-3 waves-effect waves-light btn-small\" type=\"button\">PDF</button>
              </a>";
          }
  
           if($journalNum == "no"){
              $journalNum = "";
           }
          // $dat->ReleaseStatus     = $this->translate365($dat->ReleaseStatus);
          // $dat->SalesOrderStatus  = $this->translate365($dat->SalesOrderStatus);
  
          // if ($dat->ReleaseStatus == 'Released') {
          //     $dat->ReleaseStatus = 'Liberado';
          // }
          // else if ($dat->ReleaseStatus == 'Open') {
          //     $dat->ReleaseStatus = 'Abierto';
          // }
          // else if ($dat->ReleaseStatus == 'PartialReleased') {
          //     $dat->ReleaseStatus = 'Lanzamiento Parcial';
          // }
          // else {
          // }
          // if ($dat->SalesOrderStatus == 'Backorder') {
          //     $dat->SalesOrderStatus = 'Creada';
          // }
          // else if ($dat->SalesOrderStatus == 'Delivered') {
          //     $dat->SalesOrderStatus = 'Entregado';
          // }
          // else if ($dat->SalesOrderStatus == 'Invoiced') {
          //     $dat->SalesOrderStatus = 'Facturado';
          // }
          // else {
          // }
  
          $dataOV[] = array(
              0   => "<i class=\"material-icons\" style=\"color:green;cursor:pointer\" onclick=\"detalleVenta2('".$dat->SalesOrderNumber."','ORDVTA')\">add_circle</i>",
              1   => $edita,
              2   => $fechaReal->format('d/m/Y h:i'),//$fechaYMD[2] . '/' . $fechaYMD[1] . '/' . $fechaYMD[0] . ' ' . $fechaHMS[0], //"15/11/2018 14:59:34",
              3   => $dat->InvoiceCustomerAccountNumber, //"C000009347",
              4   => $dat->SalesOrderName, //"JOSE GALINDO HINOJOS",
              5   => $dat->DefaultShippingSiteId, //"CHIH",
              6   => $dat->DefaultShippingWarehouseId, // "CHIHCONS",
              7   => $entrega, //"PASA",
              8   => $nombre,//$dat->OrderTakerPersonnelNumber, //"ARMANDO MARISCAL",
              9   => $dat->ReleaseStatus,
              10  => $dat->SalesOrderStatus, //"Facturado",
              11  => $data . '<br><br>' . $btnPDF, //"",
              12  => $impresion,
              13  => $journalNum
          );
          $btnPDF = '';
      }
  
          return $dataOV;
    }

    public static function getTotasOVCliente($nombre,$ov){
        $model = new Application_Model_CotizacionModel();
        $this->_adapter->query(TODAS_OV_PERIODO_USUARIO_1);
        if($nombre!=='%%'){
            $q = $this->_adapter->prepare(CLIENTES_PASA_OV);
            $q->bindParam(1,$nombre);
        }
        else{
            $q = $this->_adapter->prepare(CLIENTES_PASA_OV_2);
            $q->bindParam(1,$ov);
        }        
        $q->execute();      
        $r = $q->fetchAll();
        $this->_adapter->query(TODAS_OV_PERIODO_USUARIO_3);
        $entrega="";
        $estatus=array("",'Orden Abierta','Entregado','Facturado','Cancelado');
        $arr=array();
        foreach ($r as $k => $v) {
            $fecha2 = $v['CREATEDDATETIME'];
            $nuevafecha = strtotime ( '-6 hour' , strtotime ( $fecha2 ) ) ;
            $fecha = date ( 'd/m/Y H:i:s' , $nuevafecha );
            if($v['dlvmode']=='PAQUETERIA'){
               $entrega="<div class=\"col l12 m12 s12\">
                        <a href=\"javascript:archivoAdjunto('".$v['salesid']."','ORDVTA');\">".$v['dlvmode']."</a>
                        <button class=\"btn flat-btn\" onclick=\"mostrarModalEti('".$v['salesid']."','".$v['inventsiteid']."','".IMAGES_PATH."')\"><i class=\"fa fa-1x fa-eye \"></i> Etiqueta</button>
                        </div>"; 
            }
            else{
                $entrega=$v['dlvmode'];
            }
            $sst='';
            if($v['SALESSTATUS']>0){
                $sst=$estatus[$v['SALESSTATUS']];
            }
            if ($v['PROCESO'] == 'ENTREGADO'){
                $data = '<div style="text-align: center; background-color: #4caf50;border-radius: 59px;width: 30px;height: 30px;">&nbsp&nbsp&nbsp&nbsp<div/>';
            }
            $impresion="";
            if($v['PACKINGSLIPIDJOUR']!=''){
                $data="<button class=\"btn blue-grey darken-3\" type=\"button\" onclick=\"imprimirRemision('".$v['PACKINGSLIPIDJOUR']."','Sin Procesar')\">Remisión PDF</button>";
            }
            if($v['SALESSTATUS']==='3'){
                $impresion='<a href="http://svr02:8989/FacturacionCajas/PDFFactura.php?ov='.$v['salesid'].'&amp;tipo=CLIENTE" target="_blank"><i class="fa fa-file-pdf-o" style="color:#840101;"></i> Factura</a>';
            }
            if($v['SALESSTATUS']==='2'){
                foreach ($_SESSION['factura'] as $key => $value) {
                    if($value==1){
                        $impresion = '<button class="btn blue-grey darken-3" onclick="factura2(\''.$v['salesid'].'\',\''.$v['PACKINGSLIPIDJOUR'].'\',\''.$v['dlvmode'].'\')">Facturar</button>';
                    }
                }
            }
            $cotizacion=$this->db->Query("SELECT QUOTATIONID FROM SALESTABLE WHERE SALESID = :id ",[":id"=>$v['salesid']]); 
            $cargoId=$model->getCargo($cotizacion[0][0]);            
            $total=$this->db->Query(TOTAL_OV,[":id"=>$v['salesid']]); 
            $montocargo=$total[0][0]+($total[0][0]*($cargoId[0]['VALUE']/100));
            $to=$montocargo*1.16;
            $arr[$k]=array(
                $v['salesid'],
                $fecha,
                $v['custaccount'],
                $v['salesname'],
                $v['inventsiteid'],
                $v['inventlocationid'],
                $entrega,
                $v['VENDEDOR'],
                $sst,
                $data,
                $impresion, 
                "<i class=\"material-icons\" style=\"color:green;cursor:pointer\" onclick=\"detalleVenta2('".$v['salesid']."','ORDVTA')\">add_circle</i>",
                "$ ".number_format($to,3,'.',','));
        }
        return $arr;
    }

    public function getTodasOV2xx() {
        $this->_adapter->query(TODAS_OV_PERIODO_USUARIO_1);
        $q = $this->_adapter->prepare(TODAS_OV_PERIODO);
        $q->execute();      
        $r = $q->fetchAll();
        $this->_adapter->query(TODAS_OV_PERIODO_USUARIO_3);
        $entrega="";
        $estatus=array("",'Orden Abierta','Entregado','Facturado','Cancelado');
        $arr=array();
        foreach ($r as $k => $v) {
            $fecha2 = $v['CREATEDDATETIME'];
            $nuevafecha = strtotime ( '-6 hour' , strtotime ( $fecha2 ) ) ;
            $fecha = date ( 'd/m/Y H:i:s' , $nuevafecha );
            if($v['dlvmode']=='PAQUETERIA'){
               $entrega="<div class=\"col l12 m12 s12\">
                        <a href=\"javascript:archivoAdjunto('".$v['salesid']."','ORDVTA');\">".$v['dlvmode']."</a>
                        <button class=\"btn flat-btn\" onclick=\"mostrarModalEti('".$v['salesid']."','".$v['inventsiteid']."','".IMAGES_PATH."')\"><i class=\"fa fa-1x fa-eye \"></i> Etiqueta</button>
                        </div>"; 
            }
            else{
                $entrega=$v['dlvmode'];
            }
            $sst='';
            if($v['SALESSTATUS']>0){
                $sst=$estatus[$v['SALESSTATUS']];
            }
            $impresion=""; 
            if($v['PACKINGSLIPIDJOUR']!=''){
                $impresion="<button class=\"btn blue-grey darken-3\" type=\"button\" onclick=\"imprimirRemision('".$v['PACKINGSLIPIDJOUR']."','Sin Procesar')\"><i class=\"mdi-action-print\"></i></button>";
            }
            $data="";
            if ($sst==='Orden Abierta' ){ 
                $query = $this->_adapter->query("SELECT SALES.DLVTERM
                                                FROM SALESTABLE SALES                                                             
                                                WHERE SALESID = '".$v['salesid']."';");
                $query->execute();      
                $result=$query->fetchAll(PDO::FETCH_ASSOC);
                $data = '<button class="btn blue-grey darken-3" id="GenerarRemision'.$v['salesid'].'" value="'.$v['salesid'].'" onclick="GenerarRemisionBtn(\''.$result[0]['DLVTERM'].'\',\''.$v['salesid'].'\',\''.$_SESSION['userInax'].'\')">Remisionar</button>';
                foreach ($_SESSION['factura'] as $key => $value) {
                    if($value==14){
                        $data = '<button class="btn blue-grey darken-3" onclick="factura2(\''.$v['salesid'].'\',\''.$v['PACKINGSLIPIDJOUR'].'\',\''.$v['dlvmode'].'\')">Facturar</button>';
                    }
                }
            }
            if($v['SALESSTATUS']==='3'){
                $impresion='<a href="http://svr02:8989/FacturacionCajas/PDFFactura.php?ov='.$v['salesid'].'&amp;tipo=CLIENTE" target="_blank"><i class="fa fa-file-pdf-o" style="color:#840101;"></i> Factura</a>';
            }
            if($v['SALESSTATUS']==='2'){
                foreach ($_SESSION['factura'] as $key => $value) {
                    if($value==1){
                        $impresion = '<button class="btn blue-grey darken-3" onclick="factura2(\''.$v['salesid'].'\',\''.$v['PACKINGSLIPIDJOUR'].'\',\''.$v['dlvmode'].'\')">Facturar</button>';
                    }
                }
            }
            $arr[$k]=array(
                "<i class=\"material-icons\" style=\"color:green;cursor:pointer\" onclick=\"detalleVenta2('".$v['salesid']."','ORDVTA')\">add_circle</i>",
                $v['salesid'],
                $fecha,
                $v['custaccount'],
                $v['salesname'],
                $v['inventsiteid'],
                $v['inventlocationid'],
                $entrega,
                $v['VENDEDOR'],
                $sst,
                $data,
                $impresion);
        }
        return $arr;
    }

    public static function getTodasOV2() {
        $entity = 'SalesOrderHeadersV2';
        $r =  Application_Model_IndexModel::entities365($entity);

        // if($v['PACKINGSLIPIDJOUR']!=''){
        //     $impresion="<button class=\"btn blue-grey darken-3\" type=\"button\" onclick=\"imprimirRemision('".$v['PACKINGSLIPIDJOUR']."','Sin Procesar')\"><i class=\"mdi-action-print\"></i></button>";
        // }


        $usuarioNum = Application_Model_IndexModel::datosUsuario();
        foreach($r->value as $dat){
            $journalNum = "no";//Application_Model_IndexModel::getjournal($dat->SalesOrderNumber);

            $entrega="";
            if($dat->DeliveryModeCode=='PAQUETERIA'){
                $buttonColor = array(1 => 'grey', 2 => 'yellow darken-1', 3 => 'blue', 4 => 'red', 5 => 'green', 6 => 'purple darken-3');      
                $labelOv = $dat->SalesOrderNumber;
                (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $parcelInfo = $conn->prepare("SELECT * FROM AYT_Paqueterias WHERE Ov = '".$labelOv."'");
                $parcelInfo->execute();
                $parcelInfo = $parcelInfo->fetchAll();
                $dataTooltip = '';
                if($parcelInfo[0]['Status'] == 4){
                    $dataTooltip = 'data-position="top" data-tooltip="'.$parcelInfo[0]['StopReason'].'"';
                }else if($parcelInfo[0]['Status'] == 5){
                    $dataTooltip = 'data-position="top" data-tooltip="'.$parcelInfo[0]['GuideNumber'].'-'.$parcelInfo[0]['ParcelCompany'].'"';
                }
                $lbl = "<a href=\"javascript:archivoAdjunto('".$dat->SalesOrderNumber."','ORDVTA');\">".$dat->DeliveryModeCode."</a></br>";

                $lbl = "<span class=\"deliveryModal\" data=\"".$dat->SalesOrderNumber."\">PAQUETERIA</span></br>";

                $desc = $parcelInfo[0]['GuideNumber'].'-'.$parcelInfo[0]['ParcelCompany'];
                 if($desc == "WAITNG FOR OV CONVERSION-"){
                     
                    $desc = "";
                }
                
                $entrega="<div class=\"col l12 m12 s12\">
                          <p class=\"xxx\"><label style=\"color:blue;\"><strong>".$desc."</strong></label></p>
                            <button class=\"btn tooltipped ".$buttonColor[$parcelInfo[0]['Status']]." \" ".$dataTooltip." onclick=\"mostrarModalEti('".$dat->SalesOrderNumber."','".$dat->DefaultShippingSiteId."','".IMAGES_PATH."')\"><i class=\"fa fa-1x fa-eye \"></i></button>
                         </div>"; 
             }
             else{
                 $entrega=$dat->DeliveryModeCode;
             }

            $impresion = '';
            // print_r($dat->SalesOrderStatus);exit();
            if($dat->SalesOrderStatus == 'Invoiced'){
                $datosfac=Application_Model_FacturaModel::consultarFac($dat->SalesOrderNumber);
                $fecha = explode('T',$datosfac->value[0]->InvoiceDate);
                $fechaYMD = explode('-',$fecha[0]);
                $fechafin = $fechaYMD[1] . '/'.$fechaYMD[2] . '/'. $fechaYMD[0];
                $fechafin2 = $fechaYMD[2] . '/'.$fechaYMD[1] . '/'. $fechaYMD[0];
                $url = 'inax.aytcloud.com';
                $phpFile = 'Facturacion365.php';
                if (CONFIG == 'AOS06'){
                    $url = 'svr02:8989';
                    $phpFile = 'Facturacion365TestTest.php';
                }
                // print_r($fechafin);exit('hola vaquero');
                $impresion ='
                <a href="http://inax.aytcloud.com/Facturacion365/Facturacion365.php?ov='.$dat->SalesOrderNumber.'&tipo=inax&invoiceId='.$datosfac->value[0]->InvoiceNumber.'&transDate='.$fechafin.'&custInvoiceAccount='.$datosfac->value[0]->InvoiceCustomerAccountNumber.'" target="_blank">Factura</a>
                <br>
                <a href="http://inax.aytcloud.com/Facturacion365/Facturacion365.php?ov='.$dat->SalesOrderNumber.'&tipo=inax&invoiceId='.$datosfac->value[0]->InvoiceNumber.'&transDate='.$fechafin.'&custInvoiceAccount='.$datosfac->value[0]->InvoiceCustomerAccountNumber.'&type=xml" download>XML</a>
                <br>
                <a href="#" onclick="EnviarPDFCliente(\'http://'.$url.'/Facturacion365/'.$phpFile.'?ov='.$dat->SalesOrderNumber.'&type=inax&invoiceId='.$datosfac->value[0]->InvoiceNumber.'&transDate='.$fechafin.'&custInvoiceAccount='.$datosfac->value[0]->InvoiceCustomerAccountNumber.'\',\''.$datosfac->value[0]->InvoiceCustomerAccountNumber.'\',\''.$datosfac->value[0]->InvoiceNumber.'\',\''.$dat->SalesOrderName.'\',\''.$dat->Email.'\',\''.$fechafin2.'\')">Enviar PDF</a>
                <br>
                <a href="#" onclick="mostrarModalServicio(\''.$datosfac->value[0]->InvoiceNumber.'\',\''.$dat->SalesOrderNumber.'\')">Servicio</a>';
                // $impresion='<a href="http://svr02:8989/Facturacion365/Facturacion365.php?ov='.$dat->SalesOrderNumber.'&amp;tipo=CLIENTE" target="_blank"><i class="fa fa-file-pdf-o" style="color:#840101;"></i> Factura</a>';
            }
            // if($dat->SalesOrderStatus == 'Delivered'){
            //     foreach ($_SESSION['factura'] as $key => $value) {
            //         if($value==1){
            //             $impresion = '<button class="btn blue-grey darken-3" onclick="mostrarModalPago(\''.$dat->SalesOrderNumber.'\',\''.$dat->InvoiceCustomerAccountNumber.'\',\''.$dat->PaymentTermsName./*$dat->SalesOrderNumber.'\',\''.$v['PACKINGSLIPIDJOUR'].'\',\''.$dat->DeliveryModeCode.*/'\')">Facturar</button>';
            //         }
            //     }
            // }


             $edita='<a style="color:red;" onclick="swal(\'ALTO\', \'No se permite editar\', \'error\');">'.$dat->SalesOrderNumber.'</a>';
            if($dat->ReleaseStatus=='Open' &&  $dat->SalesOrderStatus !='Canceled'){
                $edita="<a onclick=\"editarOV('".$dat->SalesOrderNumber."','".$dat->InvoiceCustomerAccountNumber."');\">".$dat->SalesOrderNumber."</a>";
            }

            $data="";
            if($journalNum == "no"){                
            if ($dat->SalesOrderStatus =='Backorder'){ 
                // $data = '<button class="btn blue-grey darken-3" id="GenerarRemision'.$dat->SalesOrderNumber.'" value="'.$dat->SalesOrderNumber.'" onclick="GenerarRemisionBtn(\''.$dat->DeliveryModeCode.'\',\''.$dat->SalesOrderNumber.'\',\''.$_SESSION['userInax'].'\')">Remisionar</button>';
                foreach ($_SESSION['factura'] as $key => $value) {
                    if($value==14){
                        $data = '<button class="btn blue-grey darken-3 btn-small" onclick="mostrarModalPago(\''.$dat->SalesOrderNumber.'\',\''.$dat->InvoiceCustomerAccountNumber.'\',\''.$dat->PaymentTermsName./*$dat->SalesOrderNumber.'\',\''.$v['PACKINGSLIPIDJOUR'].'\',\''.$dat->DeliveryModeCode.*/'\')" disabled>Diario Pago</button>';
                    }
                }
            }
            }

            $fechaReal = new DateTime($dat->OrderCreationDateTime);
            $fechaReal->modify('-7 hours');
            
            $fecha = explode('T',$dat->OrderCreationDateTime);
            $fechaYMD = explode('-',$fecha[0]);
            $fechaHMS = explode('Z',$fecha[1]);

            $nombre ="";
            if($dat->OrderTakerPersonnelNumber != ""){
                foreach($usuarioNum->value as $usuarNum => $val) {
                    if($dat->OrderTakerPersonnelNumber==$val->PersonnelNumber){                
                        $nombre=$val->NameAlias;
                    }
                }
            }

            $dat->ReleaseStatus = Application_Model_IndexModel::translate365($dat->ReleaseStatus);
            $dat->SalesOrderStatus = Application_Model_IndexModel::translate365($dat->SalesOrderStatus);

            $btnPDF = '';
            if ($dat->ReleaseStatus == 'Liberado') {

                $btnPDF = "<a href='../public/impresion-orden/?id=".$dat->SalesOrderNumber."' target='_blank'>
                            <button class=\"btn blue-grey darken-3 waves-effect waves-light btn-small\" type=\"button\">PDF</button>
                </a>";
            }

             if($journalNum == "no"){
                $journalNum = "";
             }
            // $dat->ReleaseStatus     = $this->translate365($dat->ReleaseStatus);
            // $dat->SalesOrderStatus  = $this->translate365($dat->SalesOrderStatus);

            // if ($dat->ReleaseStatus == 'Released') {
            //     $dat->ReleaseStatus = 'Liberado';
            // }
            // else if ($dat->ReleaseStatus == 'Open') {
            //     $dat->ReleaseStatus = 'Abierto';
            // }
            // else if ($dat->ReleaseStatus == 'PartialReleased') {
            //     $dat->ReleaseStatus = 'Lanzamiento Parcial';
            // }
            // else {
            // }
            // if ($dat->SalesOrderStatus == 'Backorder') {
            //     $dat->SalesOrderStatus = 'Creada';
            // }
            // else if ($dat->SalesOrderStatus == 'Delivered') {
            //     $dat->SalesOrderStatus = 'Entregado';
            // }
            // else if ($dat->SalesOrderStatus == 'Invoiced') {
            //     $dat->SalesOrderStatus = 'Facturado';
            // }
            // else {
            // }

            $dataOV[] = array(
                0   => "<i class=\"material-icons\" style=\"color:green;cursor:pointer\" onclick=\"detalleVenta2('".$dat->SalesOrderNumber."','ORDVTA')\">add_circle</i>",
                1   => $edita,
                2   => $fechaReal->format('d/m/Y h:i'),//$fechaYMD[2] . '/' . $fechaYMD[1] . '/' . $fechaYMD[0] . ' ' . $fechaHMS[0], //"15/11/2018 14:59:34",
                3   => $dat->InvoiceCustomerAccountNumber, //"C000009347",
                4   => $dat->SalesOrderName, //"JOSE GALINDO HINOJOS",
                5   => $dat->DefaultShippingSiteId, //"CHIH",
                6   => $dat->DefaultShippingWarehouseId, // "CHIHCONS",
                7   => $entrega, //"PASA",
                8   => $nombre,//$dat->OrderTakerPersonnelNumber, //"ARMANDO MARISCAL",
                9   => $dat->ReleaseStatus,
                10  => $dat->SalesOrderStatus, //"Facturado",
                11  => $data . '<br><br>' . $btnPDF, //"",
                12  => $impresion,
                13  => $journalNum
            );
            $btnPDF = '';
        }

        return $dataOV;        

    }

    public static function translate365($x){
        if ($x == 'Released') {
            $field = 'Liberado';
        }
        else if ($x == 'Open') {
            $field = 'Abierto';
        }
        else if ($x == 'PartialReleased') {
            $field = 'Lanzamiento Parcial';
        }
        else if ($x == 'Backorder') {
            $field = 'Creada';
        }
        else if ($x == 'Delivered') {
            $field = 'Entregado';
        }
        else if ($x == 'Canceled') {
            $field = 'Cancelado';
        }
        else if ($x == 'Invoiced') {
            $field = 'Facturado';
        }
        else {
        }
        return $field;
    }

    public static function entities365($entity){
        $token = new Token();
        $curl = curl_init();

        $findThis = trim($_POST['findThis']);
        $inThis = trim($_POST['inThis']);

        if ($entity=='SalesQuotationHeaders' OR $entity=='SalesOrderHeadersV2'){

            if ( ($findThis == '0') && ($inThis == '0') ) {
                 return 0;//$finder = '?%24top=10';
            }
            else {
                $finder = '?%24top=100&%24filter='. $inThis .'%20eq%20%27' . $findThis. '%27'. '&%24orderby=SalesOrderNumber%20desc';
                if($entity=='SalesQuotationHeaders'){
                $finder = '?%24top=100&%24filter='. $inThis .'%20eq%20%27' . $findThis. '%27'. '&%24orderby=SalesQuotationNumber%20desc';    
                }
                // ?%24top=100&%24filter=SalesOrderName%20eq%20%27ADOLFO GOMEZ SANCHEZ%27
            }
            $consulta="https://".DYNAMICS365."/Data/" . $entity . "$finder";
        }else{

            $consulta="https://".DYNAMICS365."/Data/" . $entity . "?%24top=200";
        }

        $tkn = $token->getToken()[0]->Token;
        

        curl_setopt_array($curl, array(
          CURLOPT_URL => $consulta,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$token->getToken()[0]->Token."",
            "Content-Type: application/json",
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
    public static function idUsuario($usuarioSesion){
        $token = new Token();
        $curl = curl_init();

                        curl_setopt_array($curl, array(
                          //  CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesOrderLines?%24filter=SalesOrderNumber%20eq%20'".$ov."'"  ,
                          CURLOPT_URL => "https://".DYNAMICS365."/Data/PersonUsers?%24filter=UserId%20eq%20'".$usuarioSesion."'",
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => "",
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 30,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => "GET",
                          CURLOPT_POSTFIELDS => "\n    {\n\t\t\t \"dataAreaId\": \"lin\",\n      \"Name\": \"TEST POSTeeee44\"\n\t\t}\n\t\t\t\n\t\t\t \n",
                          CURLOPT_HTTPHEADER => array(
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
                         // echo $response;
                           // return json_decode($response);
                        }
                            $Johnson=json_decode($response,true);
                            $Johnson=$Johnson['value'];
                            $Johnson=$Johnson[0];
                            //print_r($Johnson);
                            
                            
                            $curl = curl_init();

                        curl_setopt_array($curl, array(
                          CURLOPT_URL => "https://".DYNAMICS365."/Data/Workers?%24filter=PartyNumber%20eq%20'".$Johnson['PartyNumber']."'",
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

                        $response2 = curl_exec($curl);
                        $err = curl_error($curl);

                        curl_close($curl);

                        if ($err) {
                          echo "cURL Error #:" . $err;
                        } else {
                         // echo $response;
                            return json_decode($response2);
                        }
                          
                            
    }
    public static function datosUsuario($PersonnelNumber=''){
                 $token = new Token(); 
                 $curl = curl_init();
                        if ($PersonnelNumber==''){
                            $query="https://".DYNAMICS365."/Data/Workers";
                        }else{
                            $query="https://".DYNAMICS365."/Data/Workers?%24filter=PersonnelNumber%20eq%20'".$PersonnelNumber."'";
                        }
                        curl_setopt_array($curl, array(
                          //  CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesOrderLines?%24filter=SalesOrderNumber%20eq%20'".$ov."'"  ,
                          CURLOPT_URL => $query,
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => "",
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 30,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => "GET",
                          CURLOPT_POSTFIELDS => "\n    {\n\t\t\t \"dataAreaId\": \"lin\",\n      \"Name\": \"TEST POSTeeee44\"\n\t\t}\n\t\t\t\n\t\t\t \n",
                          CURLOPT_HTTPHEADER => array(
                            "authorization: Bearer ".$token->getToken()[0]->Token."",
                            "content-type: application/json"
                          ),
                        ));

                        $response = curl_exec($curl);
                        $err = curl_error($curl);

                        curl_close($curl);
                            //print_r($response);exit();

                        return json_decode($response);
                        if ($err) {
                          echo "cURL Error #:" . $err;
                        } else {
                         // echo $response;
                        }
                            $Johnson=json_decode($response,true);
                            $Johnson=$Johnson['value'];
                            $Johnson=$Johnson[0];
                            
                            
                            $curl = curl_init();

                        curl_setopt_array($curl, array(
                          CURLOPT_URL => "https://".DYNAMICS365."/Data/PersonUsers?%24filter=PartyNumber%20eq%20'".$Johnson['PartyNumber']."'",
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => "",
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 30,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => "GET",
                          CURLOPT_POSTFIELDS => "\n    {\n\t\t\t \"dataAreaId\": \"lin\",\n      \"Name\": \"TEST POSTeeee44\"\n\t\t}\n\t\t\t\n\t\t\t \n",
                          CURLOPT_HTTPHEADER => array(
                            "authorization: Bearer ".$token->getToken()[0]->Token."",
                            "content-type: application/json"
                          ),
                        ));

                        $response2 = curl_exec($curl);
                        $err = curl_error($curl);

                        //print_r($response2);exit();
                        curl_close($curl);

                        if ($err) {
                          echo "cURL Error #:" . $err;
                        } else {
                         // echo $response;
                            return json_decode($response2);
                        }


    }
    public static function getCotPorUsuario2($vendedor){
                $token = new Token();
                $entity = 'SalesQuotationHeaders';
                
              $curl = curl_init();
  
                          curl_setopt_array($curl, array(
                          CURLOPT_URL => "https://".DYNAMICS365."/data/Workers?%24select=PersonnelNumber&%24filter=Education%20eq%20'".$vendedor."'",
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => "",
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 30,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => "GET",
                          CURLOPT_POSTFIELDS => "{\n\t\"SalesQuotationNumber\": \"COTV000019058\",\n\t\"dataAreaId\": \"atp\",\n\t\"ShippingSiteId\": \"CHIH\",\n\t\"ItemNumber\": \"5450-6011-0024\",\n\t\"RequestedSalesQuantity\": \"13\"\n}",
                          CURLOPT_HTTPHEADER => array(
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
                       
                          }
  
  
                $usuarioNum=json_decode($response);
                $curl = curl_init();
                
                curl_setopt_array($curl, array(
                CURLOPT_URL => "https://".DYNAMICS365."/data/SalesQuotationHeaders?%24filter=QuotationTakerPersonnelNumber%20eq%20'".$usuarioNum->value[0]->PersonnelNumber."'&%24orderby=SalesQuotationNumber%20desc&%24top=100",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_POSTFIELDS => "",
                CURLOPT_HTTPHEADER => array(
                    "authorization: Bearer ".$token->getToken()[0]->Token."",
                ),
                ));
                
                $response2 = curl_exec($curl);
                $err = curl_error($curl);
                
                curl_close($curl);
                
                if ($err) {
                echo "cURL Error #:" . $err;
                } else {
                
                }
        
            $r=json_decode($response2);
                
            
          foreach($r->value as $datt){
           if ( $datt->SalesQuotationStatus == 'Confirmed'){
                  
                  $data = "
                  <a href='../public/impresion-cotizacion/?id=".$datt->SalesQuotationNumber."' target='_blank'>
                  <button class=\"btn blue-grey darken-3\" type=\"button\">PDF</button>
                  </a>
                  ";
              }
          else{
              $data = "";
          }
            if ( ($datt->SalesQuotationStatus == 'Created' || $datt->SalesQuotationStatus == 'Sent')  ){
            
              $impresion = "<button class=\"btn blue-grey darken-3\" type=\"button\" onclick=\"imprimirCotizacion('".$datt->SalesQuotationNumber."','0')\"><i class=\"mdi-action-print\"></i></button>";
  
              $impresion = '
                  <a href="../public/impresion-cotizacion/?id='.$datt->SalesQuotationNumber.'" target="_blank">
                  <button class="btn blue-grey darken-3" type="button">PDF</button>
                  </a>';
                  
  
          }else{
              $impresion = "";
          }
         // $arr[] = $datt->SalesQuotationNumber;
           $edita='<a style="color:red;" onclick="swal(\'ALTO\', \'No se permite editar\', \'error\');">'.$datt->SalesQuotationNumber.'</a>';
          if($datt->SalesQuotationStatus=='Created' || $datt->SalesQuotationStatus == 'Sent'){
              $edita="<a onclick=\"editarCot('".$datt->SalesQuotationNumber."','".$datt->RequestingCustomerAccountNumber."');\">".$datt->SalesQuotationNumber."</a>";
          }
             
          $fecha = explode('T',$datt->ReceiptDateRequested);
          $fechaYMD = explode('-',$fecha[0]);
          $fechaHMS = explode('Z',$fecha[1]);
         
           $nombre ="";
            if($datt->QuotationTakerPersonnelNumber != ""){
             foreach($usuarioNum->value as $usuarNum => $val){
             if($datt->QuotationTakerPersonnelNumber==$val->PersonnelNumber){                
              $nombre=$val->NameAlias;
              }
              }
             }
          
           $arr[]=array(
              "<i class=\"material-icons\" style=\"color:green;cursor:pointer\" onclick=\"detalleVenta2('".$datt->SalesQuotationNumber."','CTZN')\">add_circle</i>",
               $edita,
               $fechaYMD[2] . '/' . $fechaYMD[1] . '/' . $fechaYMD[0] . ' ' . $fechaHMS[0], //"15/11/2018 14:59:34",
               $datt->RequestingCustomerAccountNumber,
               $datt->SalesQuotationName,
               $datt->DefaultShippingSiteId,
               $datt->DefaultShippingWarehouseId,
               $datt->DeliveryModeCode,
           //    $UsuarioTest->NameAlias,
              // $datt->QuotationTakerPersonnelNumber,
                //  $nombre,
             
               $datt->SalesQuotationStatus,
              // $data,
               
              $impresion);
      }
  
          return $arr; 
    }

    public static function getTodasCot2(){ 
   

        $entity = 'SalesQuotationHeaders';
        $r = Application_Model_IndexModel::entities365($entity);
        $use=$_SESSION['userInax'];
       //  $usuarioNum = $this->idUsuario($use);
        // $ar[] = ;
        $usuarioNum = Application_Model_IndexModel::datosUsuario($datt->QuotationTakerPersonnelNumber);
      
       /* foreach($usuarioNum->value as $UsuarioTest){
        }*/

        foreach($r->value as $datt){
                if ( $datt->SalesQuotationStatus == 'Created'||$datt->SalesQuotationStatus == 'Sent'){            
                //$data = "<button class=\"btn blue-grey darken-3\" type=\"button\" onclick=\"convertirCot('".$datt->SalesQuotationNumber."',$(this));\"><i class=\"material-icons\">done_all</i>OV</button>";
            }
            else if ( $datt->SalesQuotationStatus == 'Confirmed'){
                    // http://inax.aytcloud.com/inax/public/impresion-cotizacion/?id=COTV000004266
                    $data = "
                    <a href='../public/impresion-cotizacion/?id=".$datt->SalesQuotationNumber."' target='_blank'>
                    <button class=\"btn blue-grey darken-3\" type=\"button\">PDF</button>
                    </a>
                    ";
                }
            else{
                $data = "";   
            }
            $host = (CONFIG == "AOS06")?'inaxTest':'inax';
            if ( ($datt->SalesQuotationStatus == 'Created' || $datt->SalesQuotationStatus == 'Sent')  ){
          
                $impresion = "<button class=\"btn blue-grey darken-3\" type=\"button\" onclick=\"imprimirCotizacion('".$datt->SalesQuotationNumber."','0')\"><i class=\"mdi-action-print\"></i></button>";
                $offline = ($_SESSION['offline'])? '1': '0';
                $impresion = '';
                $impresion .= '<div class="row" style="margin:0px;">';
                $impresion .= '    <div class="col-md-12" style="padding-top:10px;">';
                $impresion .= "        <a href='../public/impresion-cotizacion/?id=".$datt->SalesQuotationNumber."' target='_blank'>";
                $impresion .= '            <button class="btn blue-grey darken-3" type="button">PDF</button>';
                $impresion .= '        </a>';
                $impresion .= '    </div>';
                $impresion .= '    <div class="col-md-12" style="padding-top:10px;">';
                $impresion .= '        <a href="#" onclick="EnviarPDFClienteCot(\'http://inax.aytcloud.com/'.$host.'/public/impresion-cotizacion/?id='.$datt->SalesQuotationNumber.'&tipo=EmailCotizacion&company='.COMPANY.'&offline='.$offline.'\',\''.$datt->RequestingCustomerAccountNumber.'\',\''.$datt->SalesQuotationNumber.'\',\''.$datt->SalesQuotationName.'\',\''.$datt->Email.'\',\''.$fechaCot.'\')">Enviar PDF</a>';
                $impresion .= '    </div>';
                $impresion .= '</div>';
                // $impresion = '
                //     <a href="../public/impresion-cotizacion/?id='.$datt->SalesQuotationNumber.'" target="_blank">
                //     <button class="btn blue-grey darken-3" type="button">PDF</button>
                //     </a>';
            }else{
                $fecha = explode('T',$datt->ReceiptDateRequested);
                $fechaYMD = explode('-',$fecha[0]);
                $fechaCot  = $fechaYMD[2] . '/' . $fechaYMD[1] . '/' . $fechaYMD[0];
                $offline = ($_SESSION['offline'])? '1': '0';
                $impresion = '<a href="#" onclick="EnviarPDFClienteCot(\'http://inax.aytcloud.com/'.$host.'/public/impresion-cotizacion/?id='.$datt->SalesQuotationNumber.'&tipo=EmailCotizacion&company='.COMPANY.'&offline='.$offline.'\',\''.$datt->RequestingCustomerAccountNumber.'\',\''.$datt->SalesQuotationNumber.'\',\''.$datt->SalesQuotationName.'\',\''.$datt->Email.'\',\''.$fechaCot.'\')">Enviar PDF</a>';
            }
           // $arr[] = $datt->SalesQuotationNumber;
             $edita='<a style="color:red;" onclick="swal(\'ALTO\', \'No se permite editar\', \'error\');">'.$datt->SalesQuotationNumber.'</a>';
            if($datt->SalesQuotationStatus=='Created' || $datt->SalesQuotationStatus == 'Sent'){
                $edita="<a onclick=\"editarCot('".$datt->SalesQuotationNumber."','".$datt->RequestingCustomerAccountNumber."');\">".$datt->SalesQuotationNumber."</a>";
            }
               
            $fecha = explode('T',$datt->ReceiptDateRequested);
            $fechaYMD = explode('-',$fecha[0]);
            $fechaHMS = explode('Z',$fecha[1]);
           
             $nombre ="";
              if($datt->QuotationTakerPersonnelNumber != ""){
               foreach($usuarioNum->value as $usuarNum => $val){
               if($datt->QuotationTakerPersonnelNumber==$val->PersonnelNumber){                
                $nombre=$val->NameAlias;
                }
                }
               }
            
             $arr[]=array(
                "<i class=\"material-icons\" style=\"color:green;cursor:pointer\" onclick=\"detalleVenta2('".$datt->SalesQuotationNumber."','CTZN')\">add_circle</i>",
                 $edita,
                 $fechaYMD[2] . '/' . $fechaYMD[1] . '/' . $fechaYMD[0] . ' ' . $fechaHMS[0], //"15/11/2018 14:59:34",
                 $datt->RequestingCustomerAccountNumber,
                 $datt->SalesQuotationName,
                 $datt->DefaultShippingSiteId,
                 $datt->DefaultShippingSiteId."CONS",
                 $datt->DeliveryModeCode,
             //    $UsuarioTest->NameAlias,
                // $datt->QuotationTakerPersonnelNumber,
                    $nombre,
               
                 $datt->SalesQuotationStatus,
                 $data,
                 
                $impresion);
        }

        // SalesQuotationNumber

       // print_r($arr);
       // exit();

       /* foreach ($r as $k => $v) {
            $fecha2 = $v['CREATEDDATETIME'];
            $nuevafecha = strtotime ( '-6 hour' , strtotime ( $fecha2 ) ) ;
            $fecha = date ( 'd/m/Y H:i:s' , $nuevafecha );
            $estatus=array('Creado','Enviado','Confirmado','Perdido','Cancelado');
            $sst=$estatus[$v['QUOTATIONSTATUS']];
            if ( $v['QUOTATIONSTATUS'] == '1' || $v['QUOTATIONSTATUS'] == '2' ){
                $data = "";
            }else{
                $data = "<button class=\"btn blue-grey darken-3\" type=\"button\" onclick=\"convertirCot('".$v['QUOTATIONID']."',$(this));\"><i class=\"material-icons\">done_all</i>OV</button>";
            }
            if ( ($v['QUOTATIONSTATUS'] == '1' || $v['QUOTATIONSTATUS'] == '2') && $v['SALESIDREF'] !=='' ){
                $impresion = "";
            }else{
                $impresion = "<button class=\"btn blue-grey darken-3\" type=\"button\" onclick=\"imprimirCotizacion('".$v['QUOTATIONID']."','0')\"><i class=\"mdi-action-print\"></i></button>";
            }
            $edita='<a style="color:red;" onclick="swal(\'ALTO\', \'No se permite editar\', \'error\');">'.$v['QUOTATIONID'].'</a>';
            if($v['QUOTATIONSTATUS']==0){
                $edita="<a onclick=\"editarCot('".$v['QUOTATIONID']."','".$v['CUSTACCOUNT']."');\">".$v['QUOTATIONID']."</a>";
            }
            
            $arr2[$k]=array(
                "<i class=\"material-icons\" style=\"color:green;cursor:pointer\" onclick=\"detalleVenta2('".$v['QUOTATIONID']."','CTZN')\">add_circle</i>",
                $edita,
                $fecha,
                $v['CUSTACCOUNT'],
                $v['QUOTATIONNAME'],
                $v['INVENTSITEID'],
                $v['INVENTLOCATIONID'],
                $v['DLVMODE'],
                $v['VENDEDOR'],
                $sst,
                $data,
                $impresion);
        }*/
        return $arr;              
    }

    public static function getTodasCotOff(){
        $db            = new Application_Model_UserinfoMapper();
        $adapter       = $db->getAdapter();
        (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();      
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if (!$_SESSION['offline']){
            //////////////////////////////////data secretario////////////////////////////////////////////////////////////
            $querySecreta  = "SELECT * FROM Employees WHERE Education = '".$_SESSION['userInax']."';";
            $query         = $conn->query($querySecreta);
            $query->execute();
            $resultSecreta = $query->fetchAll(PDO::FETCH_ASSOC);
            //////////////////////////////////////////data headers///////////////////////////////////////////////////////
            $queryCotOff   = "SELECT (SELECT NameAlias FROM Employees T1 WHERE T1.PersonnelNumber = T0.OrderTakerPersonnelNumber ) AS NombreVendedor,
                              (SELECT SalesQuotationName FROM CotizacionesHeadersOffline T2 WHERE T2.GeneratedSalesorderNumber = T0.SalesOrderNumber) AS NombreCliente,
                              (SELECT TOP 1 ShippingWareHouseId FROM SalesOrderLinesOffline T3 WHERE T3.SalesOrderNumber = T0.SalesOrderNumber) AS Almacen,
                              * 
                              FROM SalesOrderHeadersOffline T0
                              WHERE T0.OrderTakerPersonnelNumber = '".$resultSecreta[0]['PersonnelNumber']."';";
            $query         = $adapter->query($queryCotOff);
            $query->execute();
            $result        = $query->fetchAll(PDO::FETCH_ASSOC);
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////
            foreach($result AS $Data){
                $CotiRelacionada = 'Cotizacion Relacionada: '.$Data['CotizacionRelacionada'];
                $OVRelacionada   = 'OV Relacionada: '.$Data['OVRelacionada'];
                $btnBloq         = 'disabled';
                if ($Data['Status'] == '0' || $Data['Status'] == '1'){
                    $OVRelacionada   = '';
                    $CotiRelacionada = '';
                    $btnBloq         = '';
                    if ($Data['Status'] == '1'){
                        $CotiRelacionada = 'Cotizacion Relacionada: '.$Data['CotizacionRelacionada'];
                        $btnBloq         = '';
                    }
                }
                $fecha     = new Datetime($Data['CreatedDatetime']);
                $relacion  =    '<div class="row" style="padding: 0px;margin: 0px;">';
                $relacion .=    '    <div class="col l12 m12 s12" style="padding: 0px;margin: 0px;">';
                $relacion .=    '        <input type="text" value="'.$CotiRelacionada.'" id="COTConvertida" style="font-size: 11px;padding: 0px; margin: 0px; height: 20px;" readonly/>';
                $relacion .=    '    </div>';
                $relacion .=    '</div>';
                $relacion .=    '<div class="row" style="padding: 0px;margin: 0px;">';
                $relacion .=    '    <div class="col l12 m12 s12" style="padding: 0px;margin: 0px;">';
                $relacion .=    '        <input type="text" value="'.$OVRelacionada.'" id="OVConvertida" style="font-size: 11px;padding: 0px; margin: 0px; height: 20px;" readonly/>';
                $relacion .=    '    </div>';
                $relacion .=    '</div>';
                $arr[]     = array(
                                $Data['SalesOrderNumber'],
                                $fecha->format('d/m/Y H:i:s'),
                                $Data['OrderingCustomerAccountNumber'],
                                $Data['NombreCliente'],
                                $Data['DefaultShippingSiteId'],
                                $Data['Almacen'],
                                $Data['DeliveryModeCode'],
                                '<button class="btn btn-primary" onclick="convertirCotOffline(\''.$Data['SalesOrderNumber'].'\',\''.$Data['SalesQuotationNumber'].'\',this)" '.$btnBloq.'>convertir</button>',
                                $relacion,
                                $Data['Status']
                             );
            }
            return $arr;
        }else{
            return 'online';
        }
    }

    public static function convertirCotOffline($ov,$cot){
        $db      = new Application_Model_UserinfoMapper();
        $adapter = $db->getAdapter();
        $token   = new Token(); 
        /////////////////////////////////////////////get data cotizacion header y lines///////////////////////////
        $queryCotHeader  = "SELECT * FROM CotizacionesHeadersOffline WHERE SalesQuotationNumber = '".$cot."';";
        $query           = $adapter->query($queryCotHeader);
        $query->execute();
        $resultCotHeader = $query->fetchAll(PDO::FETCH_ASSOC);

        $queryCotLinea  = "SELECT * FROM CotizacionesLinesOffline WHERE SalesQuotationNumber = '".$cot."';";
        $query          = $adapter->query($queryCotLinea);
        $query->execute();
        $resultCotLinea = $query->fetchAll(PDO::FETCH_ASSOC);
        //////////////////////////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////get data ov header y lines///////////////////////////
        $queryOVHeader  = "SELECT * FROM SalesOrderHeadersOffline WHERE SalesOrderNumber = '".$ov."';";
        $query          = $adapter->query($queryOVHeader);
        $query->execute();
        $resultOVHeader = $query->fetchAll(PDO::FETCH_ASSOC);

        $queryOVLinea  = "SELECT * FROM SalesOrderLinesOffline WHERE SalesOrderNumber = '".$ov."';";
        $query         = $adapter->query($queryOVLinea);
        $query->execute();
        $resultOVLinea = $query->fetchAll(PDO::FETCH_ASSOC);
        //////////////////////////////////////////////////////////////////////////////////////////////////////////

        if ($resultOVHeader[0]['Status'] == '0'){
            //////////////////////////////////////crear cabecera cotizacion //////////////////////////////////////////
            $errorCabeceraCot = false;
            $CurrencyCode                           = $resultCotHeader[0]['CurrencyCode'];
            $LanguageId                             = $resultCotHeader[0]['LanguageId'];
            $dataAreaId                             = $resultCotHeader[0]['dataAreaId'];
            $DefaultShippingSiteId                  = $resultCotHeader[0]['DefaultShippingSiteId'];
            $RequestingCustomerAccountNumber        = $resultCotHeader[0]['RequestingCustomerAccountNumber'];
            $QuotationTakerPersonnelNumber          = $resultCotHeader[0]['QuotationTakerPersonnelNumber'];
            $QuotationResponsiblePersonnelNumber    = $resultCotHeader[0]['QuotationResponsiblePersonnelNumber'];
            $SalesOrderOriginCode                   = $resultCotHeader[0]['SalesOrderOriginCode'];
            $SalesTaxGroupCode                      = $resultCotHeader[0]['SalesTaxGroupCode'];
            $DeliveryModeCode                       = $resultCotHeader[0]['DeliveryModeCode'];
            $CustomersReference                     = $resultCotHeader[0]['CustomersReference'];
            $CustomerPaymentMethodName              = $resultCotHeader[0]['CustomerPaymentMethodName'];

            curl_setopt_array(CURL1, array(
                CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesQuotationHeadersV2",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "{ 'CurrencyCode': '".$CurrencyCode."'
                                        ,'LanguageId':'".$LanguageId."'
                                        ,'dataAreaId': '".$dataAreaId."'
                                        ,'DefaultShippingSiteId': '".$DefaultShippingSiteId."'
                                        ,'RequestingCustomerAccountNumber':'".$RequestingCustomerAccountNumbe."'
                                        ,'QuotationTakerPersonnelNumber' : '".$QuotationTakerPersonnelNumber."'
                                        ,'QuotationResponsiblePersonnelNumber' : '".$QuotationResponsiblePersonnelN."'
                                        ,'SalesOrderOriginCode' : '".$SalesOrderOriginCode."'
                                        ,'SalesTaxGroupCode': '".$SalesTaxGroupCode."'
                                        ,'DeliveryModeCode' : '".$DeliveryModeCode."'
                                        ,'CustomersReference' : '".$CustomersReference."'
                                        ,'CustomerPaymentMethodName' : '".$CustomerPaymentMethodName."'
                                      }",
                CURLOPT_HTTPHEADER => array(
                  "authorization: Bearer ".$token->getToken()[0]->Token."",
                  "content-type: application/json"
                ),
            ));

            $response  = curl_exec(CURL1);
            $err       = curl_error(CURL1);
            $response2 = json_decode($response);
            if (isset($response2->error) || !empty($err)){
                $errorCabeceraCot = true;
            }
            //////////////////////////////////////////////////////////////////////////////////////////////////////////
            ////////////////////////////////crear lineas de cotizacion////////////////////////////////////////////////
            $errorLineaCot = false;
            $tax   = 'VTAS';
            $sitio = $resultCotLinea[0]['ShippingSiteId'];
            if(trim($sitio) == 'MEXL' || trim($sitio) == 'TJNA' || trim($sitio) == 'JURZ'){
                $tax = 'FRONT';
            }
            foreach($resultCotLinea AS $Data){
                $CURLOPT_URL = "https://".DYNAMICS365."/Data/SalesQuotationLines";
                $CURLOPT_POSTFIELDS = "{ 'SalesQuotationNumber' : '".$response2->SalesQuotationNumber."'
                                        ,'dataAreaId': '".$Data['dataAreaId']."'
                                        ,'ItemNumber' : '".$Data['ItemNumber']."'
                                        ,'RequestedSalesQuantity' : ".$Data['RequestedSalesQuantity']."
                                        ,'ShippingWarehouseId' : '".$Data['ShippingWarehouseId']."'
                                        ,'FixedPriceCharges': ".(float)$Data['FixedPriceCharges']."
                                        ,'SalesTaxGroupCode': '".$tax."'
                                        ,'STF_Category' : '".$Data['STF_Category']."'
                                        ,'SalesPrice' : ".$Data['SalesPrice']."
                                      }";
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
                $responselin  = curl_exec(CURL1);
                $errlin       = curl_error(CURL1);
                $responselin  = json_decode($responselin);
                if (isset($responselin->error) || !empty($errlin)){
                    $errorLineaCot = true;
                }
            }
        }else{
            $errorCabeceraCot = false;
            $errorLineaCot    = false;
            $response2['SalesQuotationNumber'] = $resultOVHeader[0]['CotizacionRelacionada'];
            $response2 = (object) $response2;
        }

        if ( (!$errorCabeceraCot) && (!$errorLineaCot) ){
            if ($resultOVHeader[0]['Status'] == 0){
                $resultCot = Application_Model_InicioModel::enviarCotizacion($response2->SalesQuotationNumber);
            }else{
                $resultCot = $response2->SalesQuotationNumber;
            }
            if ($resultCot != ''){
                //////////////////////////////actualizar estatus de cotizacion e insertar la nueva cotizacion////////////////////////////////////////////////////////////
                $queryUpdStatus  = "UPDATE SalesOrderHeadersOffline SET Status = 1,CotizacionRelacionada = '".$response2->SalesQuotationNumber."' WHERE SalesQuotationNumber = '".$cot."';";
                $query           = $adapter->prepare($queryUpdStatus);
                $query->execute();
                $resultUpdStatus = $query->rowCount();
                //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                ///////////////////////////////////////convertir cotizacion///////////////////////////////////////////////////////////////
                $response3          = (object)$resultCotHeader[0];
                $cotizacion         = $response2->SalesQuotationNumber;
                $noCuenta           = '';
                $cliente            = $response3->RequestingCustomerAccountNumber;
                $sitio              = $response3->DefaultShippingSiteId;
                $impuestos          = 'VTAS';
                $dlvMode            = $response3->DeliveryModeCode;
                $condiEntrega       = $response3->DeliveryTermsCode;
                $moneda             = $response3->CurrencyCode;
                $metodoPagoCode     = $resultOVHeader->SATPaymMethod_MX;
                $proposito          = $resultOVHeader->SATPurpose_MX;
                $paymentTermName    = $response3->PaymentTermsName;
                $comentarioCabecera = $response3->CustomersReference;
                if($sitio== 'MEXL'||$sitio== 'TJNA'||$sitio== 'JURZ'){
                   $impuestos = 'FRONT';
                }
                ///////nuevo parametro conversion de cotizacion a OV/////////
                $parametro = array('_QuotationId' => $cotizacion.','.$noCuenta.','.COMPANY.','.$cliente.','.$metodoPagoCode.','.$dlvMode.','.$comentarioCabecera.','.$proposito.','.$sitio.','.$impuestos.','.$condiEntrega.','.$moneda.','.$paymentTermName);
                $ovQuot = Application_Model_InicioModel::setCot2Ov($parametro);
                //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                //////////////////////////////////////actualizar estatus e insertar el nuemro de ov si hubo conversion////////////////////
                if ($ovQuot['status'] != 'Fallo'){
                    $queryUpdStatus  = "UPDATE SalesOrderHeadersOffline SET Status = 2,OVRelacionada = '".$ovQuot['msg']."' WHERE SalesQuotationNumber = '".$cot."';";
                    $query           = $adapter->prepare($queryUpdStatus);
                    $query->execute();
                    $resultUpdStatus = $query->rowCount();
                }
                //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            }
        }else{
            return array(   'SalesQuotationHeader'  => null,
                            'SalesQuotationLines'   => null,
                            'SalesOrder'            => null,
                            'estatus'               => 'fallo');
        }
        return array(   'SalesQuotationHeader'  => $response2,
                        'SalesQuotationLines'   => $responselin,
                        'SalesOrder'            => $ovQuot,
                        'estatus'               => 'exito');
        //////////////////////////////////////////////////////////////////////////////////////////////////////////
    }

    public static function getjournal($ov){
        $token = new Token();
        $curl = curl_init();
         curl_setopt_array($curl, array(
            CURLOPT_URL => "https://".DYNAMICS365."/data/CustomerPaymentJournalLines?%24select=JournalBatchNumber$%24filter=STF_RefSalesId%20eq%20'".$ov."'&%24top=5",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,   
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$token->getToken()[0]->Token."",
            "content-type: application/json; odata.metadata=minimal",
            "odata-version: 4.0"
            ),
            ));
//DefaultDimensionsForOffsetAccountDisplayValue
            $response2 = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $response2 = json_decode($response2);
            return ($response2->value[0]->JournalBatchNumber=="")?"no":$response2->value[0]->JournalBatchNumber;
    }

    public static function condicionesEntrega($ov) {
        return $this->db->Query('select DLVTERM from SALESTABLE where SALESID= :ov',[':ov'=>$ov]);        
    }
}
