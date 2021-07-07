<?php
ini_set("memory_limit", "1024M");
/**
 * 
 */
class CustomerController extends Zend_Controller_Action
{
    public $token;

    public function init(){
        try {
            //$this->_helper->layout()->disableLayout();
           $this->_helper->layout->setLayout('bootstrap');           
        } catch (Zend_Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

    private function getCargaInicial(){

        $token = new Token();
        $tokenTemp = $token->getToken();
        $this->token = $tokenTemp[0]->Token;

        $datosinicio = new Application_Model_Userinfo();
        /* -------------- Carga de Segmentos --------------------------------- */
        // $query = $datosinicio->_adapter->query(GET_CONJUNTO_CLIENTES);
        // $query->execute();

        $query = $this->getSegmentosClie();
        $this->view->getSegmentos = json_encode($query);//exit();
        /* -------------- Carga de Clientes --------------------------------- */
        // $query = $datosinicio->_adapter->query(GET_CONJUNTO_CLIENTES);
        // $query->execute();

        $query = $this->getCustomerGroup();
        $this->view->getConjuntoClientes = json_encode($query);//exit();
        /* -------------- Carga de Moneda ---------------------------------- */
        // $query = $datosinicio->_adapter->query(GET_MONEDA);
        // $query->execute();
        $query = $this->getCurrencyCode();
        $this->view->getMoneda = json_encode($query);
        /* -------------- Carga de Sitios ---------------------------------- */
        // $query = $datosinicio->_adapter->query(GET_SITIO_VENTA);
        // $query->execute();
        $query = $this->getSiteId();
        $this->view->getSitios = json_encode($query);

        /* -------------- Carga de ZonaVenta ---------------------------------- */
        // $query = $datosinicio->_adapter->query(GET_ZONA_VENTA);
        // $query->execute();
         $query = $this->getSaleZone();
        $this->view->getZonaVenta = json_encode($query);
        /* -------------- Carga de Proposito ---------------------------------- */

        // $query = $datosinicio->_adapter->query(GET_PROPOSITO);
        // $query->execute();
        // $this->view->getProposito = json_encode($query->fetchAll());

        /* ---------------- Menu de sitio -------------------------------------- */
        $this->view->solicita = filter_input(INPUT_POST, 'submit');
        $this->view->map = "Nuevo Cliente";
        
    }

    public function indexAction(){
        if (isset($_SESSION['userInax'])) {
        $request = $this->getRequest();
        $model = new Application_Model_CostomerModel();
        $kardex= new Application_Model_Userinfo();
        $token = $data = "";
        $this->getCargaInicial();
        
        if ($request->isGet()) {
            $token = filter_input(INPUT_GET, 'token');
        } else if ($request->isPost()) {
            $token = filter_input(INPUT_POST, 'token');
        }
        switch ($token) {
            case 'getAlmacen':
                $site = filter_input(INPUT_GET, 'sitio');
                //print_r($site);
                //print_r(json_encode($model->getArrayData(GET_ALMACEN,$site)));
                print_r(json_encode($this->getWarehouse($site)));
                exit();
            break;
            case 'getDescuento':
                $site = filter_input(INPUT_GET, 'sitio');
                print_r(json_encode($this->getDiscount($site)));
                exit();
                break;
            case 'getPais':
                print_r(json_encode($model->getPais()));
                exit();
                break;
            case 'getColonia':
                $estado = filter_input(INPUT_GET, 'key');

                //print_r(json_encode($model->getArrayData(GET_CUIDAD,$estado)));
                print_r(json_encode($this->getCounty($estado)));

                exit();
                break;
            case 'getEstados':
                $pais = filter_input(INPUT_GET, 'key');
                print_r(json_encode($model->getArrayData(GET_ESTADOS,$pais)));
                exit();
                break;
            case 'getCiudad':
                $estado = filter_input(INPUT_GET, 'key');

                //print_r(json_encode($model->getArrayData(GET_CUIDAD,$estado)));
                print_r(json_encode($this->getCity($estado)));
                exit();
                break;
            case 'getZipcode':
                $zip = filter_input(INPUT_GET, 'key');
                // print_r(json_encode($model->getArrayData(GET_ZIP_CODE,$zipCode)));
               // $zip=$_POST['key'];
                //print_r($zip);exit();
                print_r(json_encode($this->getZipCode($zip)));
                exit();
                break;
            case 'guardarCliente':
                $datos = filter_input(INPUT_POST, 'datos');
                $data = $datos;
                break;
            case 'saveInfo':

                // try {


                $this->_helper->viewRenderer->setNoRender(true);
                $this->_helper->layout->disableLayout();
                $registro = filter_input(INPUT_POST, 'registro');
                $nombreCliente = filter_input(INPUT_POST, 'nombreCliente');
                $rfc = filter_input(INPUT_POST, 'rfc');
                $sitioVenta = filter_input(INPUT_POST, 'sitioVenta');
                $conjuntoCliente = filter_input(INPUT_POST, 'conjuntoCliente');
                $zonaVenta = filter_input(INPUT_POST, 'zonaVenta');
                $almacen = filter_input(INPUT_POST, 'almacen');
                $tipoDescuento = filter_input(INPUT_POST, 'tipoDescuento');
                $moneda = filter_input(INPUT_POST, 'moneda');
                $segmento = filter_input(INPUT_POST, 'segmento');

               

///////////////////////////////////para Lideart////////////////////////////////////////
                //  $cuentaCustomer = $this->getLastCLient();
                //  $cuentaCustomer=json_decode($cuentaCustomer);
                //  $CustomerCuenta = $cuentaCustomer->value[0]->CustomerAccount;
                // $x = explode('C',$CustomerCuenta);    //diez digitos en total comienza C    
                //  // print_r($x);exit(); 
                // $siguiente = $x[1]+1;
                // $cerosNum = 10 - strlen($siguiente);//aqui debe de ir un 10 en lugar de 8 para completar
                // $ceros="";
                // for ($i = 1; $i < $cerosNum ; $i++) {
                // $ceros=$ceros."0";
                // }
                // $cuentaCliente="C".$ceros.$siguiente;
                function santanderSetCharValues($char){
                    $assignValuesArray = array(
                        'A' => '1','B' => '2','C' => '3','D' => '4',
                        'E' => '5','F' => '6','G' => '7','H' => '8',
                        'I' => '9','J' => '1','K' => '2','L' => '3',
                        'M' => '4','N' => '5','O' => '6','P' => '7',
                        'Q' => '8','R' => '9','S' => '1','T' => '2',
                        'U' => '3','V' => '4','W' => '5','X' => '6',
                        'Y' => '7','Z' => '8'
                    );
                    if($assignValuesArray[$char]) return $assignValuesArray[$char];
                    return $char;
                }
                
                function santanderVerificationDigit($array){
                    //print_r($array);
                    $multiplierArray = [11,13,17,19,23];
                    $resultArray = array();
                    $index = 0;
                    foreach ($array as $key => $value) {
                        if($index > 4) $index = 0;
                        $resultArray[] = $value*$multiplierArray[$index];
                        $index++;      
                    }
                    $toDivide = array_sum($resultArray) + 330;
                    //echo $toDivide;
                    $residual = ($toDivide % 97) + 1;
                    //echo $residual;
                    $formatCode = str_pad($residual, 2, 0, STR_PAD_LEFT);
                    return $formatCode;
                }
                
                function getSantanderConstant($clientNumber){
                    //echo "dentro de santander";
                    $temporalString = str_split($clientNumber);
                    //print_r($assignValuesArray);
                    $formatedChars = array_map("santanderSetCharValues", $temporalString);
                    //print_r($formatedChars);
                    $verificationCode = santanderVerificationDigit(array_reverse($formatedChars));
                    return $clientNumber.$verificationCode; 
                }

                $cuentaCliente = $this->getLastCLient();
                //echo $cuentaCliente;
                $clientSantanderCode = getSantanderConstant($cuentaCliente);
                // print_r($cuentaCliente);exit();

////////////////////////////para ATP///////////////////////////////////////////////
                // $x = explode('ATP-',$CustomerCuenta);    //nueve digitos en total comienza C    
                // $siguiente = $x[1]+1;
                // $cerosNum = 7 - strlen($siguiente);
                // $ceros="";
                // for ($i = 1; $i < $cerosNum ; $i++) {
                // $ceros=$ceros."0";
                // }
                // $cuentaCliente="ATP-".$ceros.$siguiente;
                //$x=str_split($CustomerCuenta); 
///////////////////////////////////////////////////////////////////////////              
               //$cuentaCliente = "ATP-000020";
               
               // print_r($nombreCliente." ");
               // print_r($cuentaCliente." ");
               // print_r($moneda." ");
               // print_r($conjuntoCliente." ");
               // print_r($zonaVenta." ");
               // print_r($registro." ");               
               // print_r($rfc." ");
               // print_r($sitioVenta." ");
               // print_r($almacen." ");
               // print_r($tipoDescuento." ");exit();
                $almacen = $this->getWarehouse($sitioVenta);
                //print_r($almacen[0]->Warehouse);exit();
                $parametros[] = array(  'Name'=>$nombreCliente,
                                        'CustomerAccount'=> $cuentaCliente,
                                        'SalesCurrencyCode'=> $moneda,  
                                        'CustomerGroupId'=> $conjuntoCliente,
                                        'SalesTaxGroup'=> 'VTAS' ,
                                        'SalesDistrict'=> $zonaVenta,  
                                        'CompanyType'=>$registro,     
                                        'RFCNumber'=>$rfc,   
                                        'SiteId'=>$sitioVenta, 
                                        'LineDiscountCode'=> $tipoDescuento,
                                        'DeliveryTerms'=>'CONTADO',
                                        'PaymentTerms'=> 'CONTADO',
                                        'IsElectronicInvoice'=> 'Yes', 
                                        'DiscountPriceGroupId'=> 'CHIH-A',
                                        'SalesSegmentId'=> $segmento,
                                        'WarehouseId' => $almacen[0]->Warehouse,
                                        'PrimaryContactPhoneDescription'=> $descripcion,//'Test Cuen Cont',
                                        'PrimaryContactPhone'=> $telefono,//'6116515121',
                                        'PrimaryContactPhoneExtension'=> $extension,//'2145',
                                        'PrimaryContactPhonePurpose'=> 'Business',
                                        'OrganizationNumber'=> $clientSantanderCode,
                                        "SATPurpose_MX" => "G01",
                           // 'Address'       => $adresses,///////////////////////////////////////////////////////////
                           // 'Contact'       => $contactos/////////////////////////////////////////////////////////////
                );

                     /* direccion */
                $cp = $_POST['cp'];
                $estado = $_POST['estado'];
                $pais = $_POST['pais'];
                $calle = $_POST['calle'];
                $numero = $_POST['numero'];
                $ciudad = $_POST['ciudad'];
                $colonia = $_POST['colonia'];
                $proposito = $_POST['propositoVal'];
                $propositos="";
                // print_r($proposito);exit();
                // foreach($proposito as $prop){
                //     $propositos=$propositos.$prop.";";
                // }
                $adresses=array();
                foreach ($cp as $k => $v) {
                $adresses[$k]=array( 
                     'CustomerAccountNumber'=> $cuentaCliente,
                     'dataAreaId'=> COMPANY,
                     'CustomerLegalEntityId'=> COMPANY,
                     'AddressDescription'=> $nombreCliente,
                     'AddressZipCode'=> $cp[$k],
                     'AddressState'=>$estado[$k],
                     'AddressCountryRegionId'=>$pais[$k],
                     'AddressStreet'=>$calle[$k],
                     'AddressStreetNumber'=>$numero[$k],
                     'AddressCity'=>$ciudad[$k],
                     'AddressCountyId'=>$colonia[$k],
                     'AddressLocationRoles'=> $proposito[$k]//'Business;Delivery;Invoice'
                );                   
                }
                //print_r(COMPANY);exit();
                    //exit('bye');
                     $respo = $this->saveCustomer($parametros,$adresses);
                    $respo=json_decode($respo);
                     /* Contacto */
                $descripcion = $_POST['descripcion'];
                $telefono = $_POST['telefono'];
                $formaContacto = $_POST['formaContacto'];
                $extension = $_POST['extension'];
                $contactos = array();
                foreach ($descripcion as $k => $v) {
                    if($k<=2){ $primary='Yes';}
                    else{ $primary='No'; }
                    $contactos[$k]= array( 'Description'=>$descripcion[$k],
                        'PartyNumber'=> $respo->PartyNumber,
                        'Locator'=>$telefono[$k],
                        'Type'=>$formaContacto[$k],
                        'LocatorExtension'=>(string)$extension[$k],
                        'isPrimary'=>$primary);                    
                    }
                    // print_r($cuentaCliente);exit();

                    $recontact = $this->saveContacts($contactos);
                    $this->saveDataCLient($cuentaCliente);
                    // $client= $model->isClientExist($rfc,COMPANY);
                    // if(is_array($client)&& empty($client)){
                    //     $custAccount = $model->setDataClient($parametros);
                    //     $model->setKardexCliente('ALTA CLIENTE con RFC: '.$rfc);
                    //     $kardex->kardexLog("cliente nuevo parametros: ".json_encode($parametros)." resultado: ".json_encode($custAccount),json_encode($parametros),json_encode($custAccount),1,'ALTACLIENTE');
                    //    echo $cuentaCliente;
                    // }
                    // else{
                    //     $kardex->kardexLog("cliente nuevo parametros: ".json_encode($parametros)." resultado: ".json_encode($custAccount),json_encode($parametros),'YA EXISTE CLIENTE CON CLAVE '.$client[0]['ACCOUNTNUM'],1,'ALTACLIENTE');
                    echo json_encode(array("status"=>"Exito","msg"=>$cuentaCliente,"respuesta"=>$respo));
                    // }                                        
                    exit();                    
                // } catch (Exception $objError) {
                //     echo $objError->getMessage();
                //     exit();
                // }               

                break;
            default:
                break;
        }
        }
        else {
            return $this->_helper->redirector->gotoUrl('../public/login');
        }
    }


    public function saveDataCLient($custo){
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/Data/CustomersV3?%24select=CustomerAccount%2COrganizationName%2CSiteId%2CDeliveryMode%2CDeliveryTerms%2CFullPrimaryAddress%2CPaymentBankAccount%2CPaymentMethod%2CPaymentTerms%2CRFCNumber%2CPrimaryContactPhone%2CPartyNumber%2CPrimaryContactEmail%2CCreditLimit%2CCustomerGroupId%2CCompanyType%2CAddressLocationRoles%2CLineDiscountCode%2CDiscountPriceGroupId&%24filter=CustomerAccount%20eq%20'".$custo."'",
        CURLOPT_RETURNTRANSFER => true,
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
        curl_close($curl);
        $response = json_decode($response);

//         print_r($response->value[0]->CustomerAccount);
// print_r($response->value[0]->OrganizationName);
// print_r($response->value[0]->SiteId);
// print_r($response->value[0]->DeliveryMode);
// print_r($response->value[0]->DeliveryTerms);
// print_r($response->value[0]->FullPrimaryAddress);
// print_r($response->value[0]->PaymentBankAccount);
// print_r($response->value[0]->PaymentMethod);
// print_r($response->value[0]->PaymentTerms);
// print_r($response->value[0]->RFCNumber);
// print_r($response->value[0]->PrimaryContactPhone);
// print_r($response->value[0]->PrimaryContactEmail);
// print_r($response->value[0]->CreditLimit);
//         print_r("https://".DYNAMICS365."/Data/CustomersV3?%24select=CustomerAccount%2COrganizationName%2CSiteId%2CDeliveryMode%2CDeliveryTerms%2CFullPrimaryAddress %2CPaymentBankAccount%2CPaymentMethod%2CPaymentTerms%2CRFCNumber%2CPrimaryContactPhone%2CPrimaryContactEmail%2CCreditLimit&%24filter=CustomerAccount%20eq%20'".$custo."'");exit();
        // Application_Model_InicioModel::$db = new Application_Model_UserinfoMapper();
        //  $adapterDb = Application_Model_InicioModel::$db->getAdapter();
        // $tipofiesta = "Organization";
        // if($response->value[0]->CompanyType=="LegalPerson"){
        //     $tipofiesta = "Person";
        // }

        (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

         $resul = $conn->prepare("insert into clientesInax(CustomerAccount,OrganizationName,SiteId,DeliveryMode,DeliveryTerms,FullPrimaryAddress ,PaymentBankAccount,PaymentMethod,PaymentTerms,RFCNumber,PrimaryContactPhone,PrimaryContactEmail,CreditLimit,isBlocked,CustomerGroupId,PartyNumber,LineDiscountCode,DiscountPriceGroupId,FechaCreacion) VALUES ('".$response->value[0]->CustomerAccount."','".$response->value[0]->OrganizationName."','".$response->value[0]->SiteId."','".$response->value[0]->DeliveryMode."','".$response->value[0]->DeliveryTerms."','".$response->value[0]->FullPrimaryAddress."','".$response->value[0]->PaymentBankAccount."','".$response->value[0]->PaymentMethod."','".$response->value[0]->PaymentTerms."','".$response->value[0]->RFCNumber."','".$response->value[0]->PrimaryContactPhone."','". $response->value[0]->PrimaryContactEmail."','".$response->value[0]->CreditLimit."','".$response->value[0]->Blocked."','".$response->value[0]->CustomerGroupId."','".$response->value[0]->PartyNumber."','".$response->value[0]->LineDiscountCode."','".$response->value[0]->DiscountPriceGroupId."',GETDATE())");
         $resul->execute();
         // $resultado = $resul->fetchAll(PDO::FETCH_ASSOC);

    }

    public function getLastCLient(){
        
        $curl = curl_init();
        // curl_setopt_array($curl, array(
        // CURLOPT_URL => "https://".DYNAMICS365."/Data/Customers?%24orderby=CustomerAccount%20desc&%24top=1&%24select=CustomerAccount&%24filter=dataAreaId%20eq%20'".COMPANY."'",
        // CURLOPT_RETURNTRANSFER => true,
        // CURLOPT_ENCODING => "",
        // CURLOPT_MAXREDIRS => 10,
        // CURLOPT_TIMEOUT => 30,
        // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        // CURLOPT_CUSTOMREQUEST => "GET",
        // CURLOPT_POSTFIELDS => "",
        // CURLOPT_HTTPHEADER => array(
        // "authorization: Bearer ".$this->token."",
        // "content-type: application/json; odata.metadata=minimal",
        // "odata-version: 4.0"
        // ),
        // ));
        
        // $response = curl_exec($curl);
        // $err = curl_error($curl);

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/api/services/STF_INAX/STF_Cotizacion/getNextCustAccountNum",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "{\n\"_dataAreaId\": \"ATP\"\n}",
        CURLOPT_HTTPHEADER => array(
        "authorization: Bearer ".$this->token."",
        "content-type: application/json"
        ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);


        // print_r($response);exit();
        curl_close($curl);
        
        if ($err) {
        return "cURL Error #:" . $err;
        } else {
        return json_decode($response);
        }
        //return $response;
    }


    public function saveCustomer($params,$addre){
        //exit($params[0]['Name']);
        $curl = curl_init();
        $nombre=$params[0]['Name'];
        $nombre = preg_replace('/( ){2,}/u',' ',$nombre);
        $nombre = trim($nombre);
        $params[0]['Name'] = $nombre;
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/Data/CustomersV3",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "{\n\t\"dataAreaId\":\"".strtolower(COMPANY)."\",\n\t\"OrganizationName\":\"".$params[0]['Name']."\",\n\t\"CustomerAccount\": \"".$params[0]['CustomerAccount']."\",\n\t\"OrganizationNumber\": \"".$params[0]['OrganizationNumber']."\",\n\t\"SalesCurrencyCode\": \"".$params[0]['SalesCurrencyCode']."\",\n\t\"CustomerGroupId\": \"".$params[0]['CustomerGroupId']."\",\n\t\"SalesTaxGroup\": \"".$params[0]['SalesTaxGroup']."\" ,\n\t\"SalesDistrict\": \"".$params[0]['SalesDistrict']."\",\n\t\"CompanyType\":\"".$params[0]['CompanyType']."\",\n\t\"RFCNumber\":\"".$params[0]['RFCNumber']."\" ,\n\t\"SiteId\":\"".$params[0]['SiteId']."\",\n\t\"LineDiscountCode\":\"".$params[0]['LineDiscountCode']."\",\t\n\t\"DeliveryTerms\":\"CONTADO\",\n  \"PaymentTerms\": \"CONTADO\",\n  \"IsElectronicInvoice\": \"Yes\", \n  \"DiscountPriceGroupId\": \"".$params[0]['DiscountPriceGroupId']."\",\n  \"SalesSegmentId\": \"".$params[0]['SalesSegmentId']."\",\n  \"WarehouseId\" : \"".$params[0]['WarehouseId']."\",\n  \"PrimaryContactPhoneDescription\": \"".$params[0]['PrimaryContactPhoneDescription'][1]."\",\n  \"PrimaryContactPhone\": \"".$params[0]['PrimaryContactPhone'][1]."\",\n  \"PrimaryContactPhoneExtension\": \"".$params[0]['PrimaryContactPhoneExtension'][1]."\",\n  \"PrimaryContactPhonePurpose\": \"".$params[0]['PrimaryContactPhonePurpose']."\"\n}",

        CURLOPT_HTTPHEADER => array(
        "authorization: Bearer ".$this->token."",
        "content-type: application/json; odata.metadata=minimal",
        "odata-version: 4.0"
        ),
        ));
    
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/data/CustomersV3(dataAreaId=%27".COMPANY."%27,CustomerAccount=%27".$params[0]['CustomerAccount']."%27)",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "PATCH",
        CURLOPT_POSTFIELDS => "{\n\t\"SATPurpose_MX\" : \"G01\"\n}\n\n\n\n\t",
        CURLOPT_HTTPHEADER => array(
        "authorization: Bearer ".$this->token."",
        "content-type: application/json; odata.metadata=minimal"
        ),
        ));
        // print_r("https://".DYNAMICS365."/Data/CustomersV3(dataAreaId=%27".COMPANY."%27,CustomerAccount=%27".$params[0]['CustomerAccount']."%27)?cross-company=true");exit();
        $response2 = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
        return "cURL Error #:" . $err;
        } else {
        $this->saveAddress($addre);     
        // $this->jsonSave(
        // $response->CustomerAccount,
        // $response->OrganizationName,
        // $response->SiteId,
        // $response->DeliveryMode,
        // $response->DeliveryTerms,
        // $response->FullPrimaryAddress, 
        // $response->PaymentBankAccount,
        // $response->PaymentMethod,
        // $response->PaymentTerms,
        // $response->RFCNumber,
        // $response->PrimaryContactPhone,
        // $response->PrimaryContactEmail,
        // $response->CreditLimit);
        return $response;        
        }

    }

    public function jsonSave($Name, $CustomerAccount){

        $salvar = new ClientSave();
        $salvar->guardaCliente($Name,$CustomerAccount);
    }

    public function saveAddress($addr){
        //$addr[0]['']
        $curl = curl_init();

        foreach ($addr as $key) {
            
        
        //print_r($key);
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/data/CustomerPostalAddresses",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "{\n\t\"dataAreaId\":\"".strtolower(COMPANY)."\",\n\t \"CustomerAccountNumber\": \"".$key['CustomerAccountNumber']."\",\n\t \"CustomerLegalEntityId\": \"".$key['CustomerLegalEntityId']."\",\n\t \"AddressDescription\": \"".$key['AddressDescription']."\",\n   \"AddressZipCode\":\"".$key['AddressZipCode']."\",\n   \"AddressState\":\"".$key['AddressState']."\",\n   \"AddressCountryRegionId\":\"".$key['AddressCountryRegionId']."\",\n   \"AddressStreet\":\"".$key['AddressStreet']." ".$key['AddressStreetNumber']."\",\n   \"AddressStreetNumber\":\"".$key['AddressStreetNumber']."\",\n   \"AddressCity\":\"".$key['AddressCity']."\",\n   \"AddressCountyId\":\"".$key['AddressCountyId']."\",\n   \"AddressLocationRoles\": \"".$key['AddressLocationRoles']."\"\n}",
        CURLOPT_HTTPHEADER => array(
        "authorization: Bearer ".$this->token."",
        "content-type: application/json"
        ),
        ));
        // AddressLocationId
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $response = json_decode($response);

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/api/services/STF_INAX/STF_TaxGroup/setTaxGroup",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "{\n\t\"_locationId\" : \"".$response->AddressLocationId."\",\n\"_taxGroup\" : \"VTAS\",\n\t\"company\":\"".strtolower(COMPANY)."\"\n}",
        CURLOPT_HTTPHEADER => array(
        "authorization: Bearer ".$this->token."",
        "content-type: application/json"
        ),
        ));
        
        $response2 = curl_exec($curl);
        $err = curl_error($curl);

        }
        curl_close($curl);
        //print_r($response);exit();
        if ($err) {
        return "cURL Error #:" . $err;
        } else {
        return $response;
        }


    }


    public function getSegmentosClie(){
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/data/STF_smmBusRelSegmentGroup",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
        "authorization: Bearer ".$this->token.""
        ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
        return "cURL Error #:" . $err;
        } else {
        $x=json_decode($response);
           //print_r($x->value);exit();
        return $x->value;
        }
    }


    public function getSaleZone(){
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/data/STF_smmSalesDistrictEntity?%24filter=dataAreaId%20eq%20'".strtolower(COMPANY)."'",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
        "authorization: Bearer ".$this->token."",
        "content-type: application/json; odata.metadata=minimal"
        ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
        return "cURL Error #:" . $err;
        } else {
         $x=json_decode($response);
           //print_r($x->value);exit();
        return $x->value;
        }
    }
    public function getSiteId(){
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/data/OperationalSites?%24select=SiteId,SiteName&%24filter=dataAreaId%20eq%20'".COMPANY."'",
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
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
        return "cURL Error #:" . $err;
        } else {
            $x=json_decode($response);
           //print_r($x->value);exit();
        return $x->value;
        }

    }

    public function getCustomerGroup(){
        $curl = curl_init();
        # https://tes-ayt.sandbox.operations.dynamics.com/data/CustomerGroups?$select=CustomerGroupId&$filter=dataAreaId eq 'ATP'
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/data/CustomerGroups?%24select=CustomerGroupId&%24filter=dataAreaId%20eq%20'".COMPANY."'",
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
        

        $response = curl_exec($curl);
        $err = curl_error($curl);

        // print_r(curl_getinfo($curl));
        // print_r($response);
        // print_r($this->token);
        // exit();
        
        curl_close($curl);
        
        if ($err) {
        return "cURL Error #:" . $err;
        } else {
            $x=json_decode($response);
           //print_r($x->value);exit();
        return $x->value;
        }

    }


    public function getCurrencyCode(){
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/data/CurrencyISOCodes?%24select=ISOCurrencyCode",
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
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
        return "cURL Error #:" . $err;
        } else {
         $x=json_decode($response);
           //print_r($x->value);exit();
        return $x->value;
        }
    }


    public function getZipCode($zip){        
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/data/AddressPostalCodes?%24filter=ZipCode%20eq%20'".$zip."'&%24select=ZipCode%2CCountryRegionId%2CStateId",
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
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
        return "cURL Error #:" . $err;
        } else {
        $x=json_decode($response);
           //print_r($x->value);exit();
        return $x->value;
        }
    }

    public function getCounty($estado){
        
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/data/AddressCounties?%24filter=StateId%20eq%20'".$estado."'",
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
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
        return "cURL Error #:" . $err;
        } else {
        $x=json_decode($response);
           //print_r($x->value);exit();
        return $x->value;
        }
    }
    public function getDiscount($sitio){
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/data/STF_PriceDiscGroupEntity?%24filter=dataAreaId%20eq%20'".COMPANY."'%20and%20Type%20eq%20Microsoft.Dynamics.DataEntities.PriceGroupType'LineDiscGroup'",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
        "authorization: Bearer ".$this->token."",
        "content-type: application/json; odata.metadata=minimal"
        ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
        return "cURL Error #:" . $err;
        } else {
        $x=json_decode($response);
           //print_r($x->value);exit();
        return $x->value;
        }

    }
    public function getWarehouse($sitio){
      $curl = curl_init();

      curl_setopt_array($curl, array(
      CURLOPT_URL => "https://".DYNAMICS365."/data/STF_InventWarehouseEntity?%24filter=Site%20eq%20'".$sitio."'%20and%20Warehouse%20eq%20'*CONS'",
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
      
      $response = curl_exec($curl);
      $err = curl_error($curl);      
      curl_close($curl);
      
      if ($err) {
      return "cURL Error #:" . $err;
      } else {
       $x=json_decode($response);
           //print_r($x->value);exit();
        return $x->value;
      }  
    }

    public function getCity($estado){
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://".DYNAMICS365."/data/AddressCities?%24filter=StateId%20eq%20'".$estado."'",
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
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
        return "cURL Error #:" . $err;
        } else {
         $x=json_decode($response);
           //print_r($x->value);exit();
        return $x->value;
        }
    }

    public function saveContacts($contac){
      $curl = curl_init();
      foreach ($contac as $key) {
      curl_setopt_array($curl, array(
      CURLOPT_URL => "https://".DYNAMICS365."/Data/PartyContacts",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => "{\n\t\"Description\":\"".$key['Description']."\",\n  \"PartyNumber\":\"".$key['PartyNumber']."\",\n  \"Locator\":\"".$key['Locator']."\",\n  \"Type\":\"".$key['Type']."\",\n\t\"IsPrimary\": \"".$key['isPrimary']."\",\n  \"LocatorExtension\":\"".$key['LocatorExtension']."\"\n \n}",
      CURLOPT_HTTPHEADER => array(
      "authorization: Bearer ".$this->token."",
      "content-type: application/json; odata.metadata=minimal",
      "odata-version: 4.0"
      ),
      ));
      
      $response = curl_exec($curl);
      // print_r($response);exit();
      $err = curl_error($curl);
      }
      curl_close($curl);
      
      if ($err) {
      return "cURL Error #:" . $err;
      } else {
      return $response;
      }      
    }

}