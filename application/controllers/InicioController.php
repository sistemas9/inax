<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
ini_set('display_errors', '1'); 
ini_set("memory_limit", "1024M");
// server should keep session data for AT LEAST 1 hour
session_set_cookie_params(86400);
date_default_timezone_set('America/Chihuahua');
ini_set('session.gc_maxlifetime', 86400);


class InicioController extends Zend_Controller_Inax{
    private $makeTiket;
    private $infoCliente = ':v';
    public function init(){
        if(empty($_SESSION['userInax'])){
            $this->_redirect('/login');
        }
        else{
            require_once (LIBRARY_PATH.'/includes/makeTicket.php');
            $this->makeTiket = new makeTicket();
        }
    }
    
    public function indexAction(){
        $date=new DateTime();
        $sucursales = array(
            'CHIHUAHUA'         => 'CHIH',
            'AGUASCALIENTES'    => 'AGSC',
            'HERMOSILLO'        => 'HERM',
            'OBREGON'           => 'OBRG',
            'TIJUANA'           => 'TJNA',
            'CULIACAN'          => 'CULN',
            'JUAREZ'            => 'JURZ',
            'MEXICALI'          => 'MEXL',
            'MONTERREY'         => 'MTRY',
            'DURANGO'           => 'DURN',
            'GUADALAJARA'       => 'GDLJ',
            'ZACATECAS'         => 'ZACS',
            'TORREON'           => 'TORN',
            'EDO MEX'           => 'EDMX',
            'SALTILLO'          => 'SALT',
            'VERACRUZ'          => 'VCRZ',
            'SAN LUIS POTOSI'   => 'SLPS',
            'QUERETARO'         => 'QRTO',
            'LEON'              => 'LEON',
            'PUEBLA'            => 'PBLA',
            'TUXTLA'            => 'TXLA'
        );
        // $model= new Application_Model_InicioModel();
        $request = $this->getRequest();
        $token = "";
        $fecha = new Datetime();
        $_SESSION['idProcesoVenta'] = 'POV-'.$fecha->format('dmHis');
        if ($request->isGet()) {
            $token = filter_input(INPUT_GET,'token');
        }
        else {
            $token      = filter_input(INPUT_POST,'token');
            $docType    = filter_input(INPUT_POST,'documentType');
        }
        $datosinicio    = new Application_Model_Userinfo();
        $fechaIni       = new Datetime();
        $parametros     = array('usuario'            => $_SESSION['userInax'],
                                'evento'             => 'Nueva/Edicion Cotizacion',
                                'pantalla'           => 'Cabecera Cotizacion',
                                'documentoCreado'    => 'N/A',
                                'fechaInicio'        => $fechaIni,
                                'idPOV'              => $_SESSION['idProcesoVenta']
                        );
        $datosinicio->saveHistory($parametros);
        $this->view->cliente        = json_encode(Application_Model_InicioModel::getClients(''));
        $this->view->sitios         = $datosinicio->Sitios(COMPANY);
        $this->view->sitio          = json_encode($this->view->sitios);
        //print json_encode($this->view->sitios);
        $this->view->cargos         = $datosinicio->Cargos();
        $this->view->modosentrega   = $datosinicio->ModosEntrega();
        $this->view->editarOV       = "NoResults";
        $this->view->origenVenta    = $datosinicio->getOrigenesVenta();
        $this->view->map            = "Datos De Cliente";
        $this->view->sucursalActual = Application_Model_InicioModel::getSucursal();
        $sucuAct                    = $this->view->sucursalActual;
        $sucu                       = $sucursales[$sucuAct[0][0]];
        $this->view->art            = json_encode(Application_Model_InicioModel::getItems($sucu));
        $this->view->art2           = Application_Model_InicioModel::getItemsCommon();
        $this->view->artNotLocked   = json_encode(Application_Model_InicioModel::getArtNotLocked());
        $this->view->usoCFDI        = Application_Model_InicioModel::getUsoCDFI();
        $this->view->payTerm        = Application_Model_InicioModel::getPayTerm();
        $this->view->payMode        = Application_Model_InicioModel::getPayMode();
        $this->view->fechaActual    = $date->format('d/m/Y');
        $this->view->userSes        = $_SESSION['userInax'];
        $this->view->montos_minimos = json_encode(Application_Model_InicioModel::getmontosMinimos());
        $this->view->offline        = Application_Model_InicioModel::checkOffline();
        $_SESSION['edicion']        = 0;
        $_SESSION['offline']        = $this->view->offline;
        if ($request->isPost()) {
            if (!isset($docType)){ 
                $this->view->documentType = 'ORDVTA';
                $this->view->titulo ="Orden de Venta";
            } 
            else { 
                $this->view->documentType = $docType; 
                if($docType=="ORDVTA"){ $this->view->titulo ="Orden de Venta"; }
                else {$this->view->titulo ="Cotización";}
            }
            $editar=  filter_input(INPUT_POST,'editar');
            if (!empty($editar)){
                $_SESSION['edicion'] = 1;
                if ($docType == 'CTZN') {
                    $cotizacion = filter_input(INPUT_POST, 'DocumentId');
                    $result=Application_Model_InicioModel::getCTZNDataClient($cotizacion);
                    $result2 = Application_Model_InicioModel::getCTZNDataContent($cotizacion);
                    //print_r($result2);
                    //exit();
                    $this->view->cuenta_pago=Application_Model_InicioModel::getCuentaPagoTarjeta($cotizacion);
                    if(!empty($result)){ Application_Model_InicioModel::setCTZNDataClient($result,$cotizacion); }
                    if(!empty($result2)){
                        $clte = $result[0]['CUSTACCOUNT'];
                        $dlvTerm = $result[0]['DLVTERM'];
                        $fecha = strtotime(date('c', time()));
                        $DateTrans = $fecha;
                        $punitario = 0;
                        $cargo = $result[0]['CARGO'];
                        foreach ($result2 as $index => $Data){
                            
                            $parametros = array(
                                '_CustAccount'      => $clte,
                                '_ItemId'           => $Data['ITEMID'],
                                '_SalesPrice'       => (double) $Data['SALESQTY'],
                                '_Date'             => $fecha,
                                '_amountQty'        => (double) $punitario,
                                '_currencyCode'     => $Data['CURRENCYCODE'],
                                '_InventSiteId'     => $Data['INVENTSITEID'],
                                '_InventLocationId' => $Data['INVENTLOCATIONID'],
                                '_PercentCharges'   => (double) $cargo);
                            $precioWS = Application_Model_InicioModel::checarPreciosNew($parametros);
                            if (($precioWS['precio'] != $Data['STF_SALESPRICE']) && ($Data['BLOCKSALESPRICES'] != '1')) {
                                $result2[$index]['CAMBIOWS'] = 1;
                                $result2[$index]['PRECIOWS'] = $precioWS['precio'];
                            }

                        }
                        $json=json_encode($result2);
                        //print_r($json);
                        $this->view->editarOV = str_replace("'",'',$json);
                    } 
                    else {
                        $this->view->editarOV = 'NoResults';
                    }
                } 
                else if ($docType == 'ORDVTA') {
                    $OV = $_POST['DocumentId'];
                    $result = Application_Model_InicioModel::getORDVTADataClient($OV);
                    $result2 = Application_Model_InicioModel::getSalesORDVTAContent($OV);
                    $is_released = 0;
                    //print_r($result2);
                    //print_r($_POST);
                    //exit();
                    if(!empty($result)){ Application_Model_InicioModel::setORDVTADataClient($result,$OV); };
                    if(!empty($result2)) {
                        $clte = $result[0]['CUSTACCOUNT'];
                        $dlvTerm = $result[0]['DLVTERM'];
                        $fecha = strtotime(date('c', time()));
                        $DateTrans = $fecha;
                        $punitario = 0;
                        $cargo = $result[0]['CARGO'];
                       // print_r($result2[0]);
                       // echo '<br>';
                        if($result2[1] == "Open"){
                            switch($result2[0]){
                                case "Canceled":
                                case "Delivered":  
                                case "Invoiced":
                                    $this->view->editarOV = 'NoResults';
                                    break;
                                    case "Backorder":                            
                                    default:
                                    
                                    foreach ($result2[2] as $index => $Data) {
                                        //print_r($index);
                                        //exit();
                                        $item = $Data['ITEMID'];
                                        $qty = $Data['SALESQTY'];
                                        $moneda = $Data['CURRENCYCODE'];
                                        $sitio = $Data['INVENTSITEID'];
                                        $almacen = $Data['INVENTLOCATIONID'];
                                        $parametros = array(
                                            '_CustAccount'      => $clte,
                                            '_ItemId'           => $item,
                                            '_SalesPrice'       => (double) $punitario,
                                            '_Date'             => $fecha,
                                            '_amountQty'        => (double) $qty,
                                            '_currencyCode'     => $moneda,
                                            '_InventSiteId'     => $sitio,
                                            '_InventLocationId' => $almacen,
                                            '_PercentCharges'   => (double) $cargo);
            
                                        $precioWS = Application_Model_InicioModel::checarPreciosNew($parametros);
                                        if ($precioWS['precio'] != $Data['STF_SALESPRICE']) {
                                            $result2[2][$index]['CAMBIOWS'] = 1;
                                            $result2[2][$index]['PRECIOWS'] = $precioWS['precio'];
                                        }
                                    }
                                    $json=json_encode($result2);
                                    //print_r($json);
                                    //exit();
                                    $this->view->editarOV = str_replace("'",'',$json);                                        break;
                            }

                        }else{
                            $is_released = 1;
                            $this->view->editarOV = 'NoResults';
                            $this->view->isReleased = $is_released;                            
                        }                        
                    } else {
                        $this->view->editarOV = 'NoResults';
                    }
                }
            }
            else {
                $this->view->editarOV = 'NoResults';
            }
            if (!is_null($docType) && $token==""){
                Application_Model_InicioModel::setKardex($docType);
            }
        }
        switch ($token) {
            case 'IsPriceBlocked':
                echo json_encode(Application_Model_InicioModel::isPriceBloked(filter_input(INPUT_GET, 'item')));
                exit();
            break;
            case 'existenciaLote':
                echo json_encode(Application_Model_InicioModel::getExistenciaLote(filter_input(INPUT_GET,'item'), filter_input(INPUT_GET, 'sitio')));
                exit();
            break;
            case 'existenciasFamilia':
                $familia = filter_input(INPUT_GET,'familia');
                $sitio = filter_input(INPUT_GET,'sitio');
                $sitioCliente = filter_input(INPUT_GET,'sitioCliente');
                $seccionOV = filter_input(INPUT_GET,'seccionOV');

               // print ("$familia, $sitio, $sitioCliente, $seccionOV");
                //exit();
                // $query = $datosinicio->_adapter->prepare("EXECUTE ExistenciasFamilia '$sitio','$familia'");
                // $query->execute();
                // $result = $query->fetchAll();
                $result = Application_Model_InicioModel::getExistenciasFamilia($familia,$sitio, $sitioCliente, $seccionOV);
                if (empty($result)) {
                    $ExistenciaFamilia['noresult'] = "Sin Resultados!";
                }
                foreach ($result as $k => $v) {
                    $ExistenciaFamilia[$k] = $v;
                }
                echo json_encode($ExistenciaFamilia);
                exit();
                break;
            case 'generarNegado':                    
                $nombre = str_replace("'", '', $_GET['nombre']);
                $vendedor = $_SESSION['userInax'];
                $comentarioNegado = $_GET['comentarioNegado'];
                $sitioNegado = $_GET['sitio'];
                $unidadNegada = $_GET['unidad'];
                $result="";
                (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                foreach ($_POST['data'] as $k => $v){
                    $query = $conn->query("INSERT INTO AYT_Negados(codigo_articulo,nombre,vendedor,cliente,almacen,fecha,status,cantidad_exist,cantidad_negada,comentario,sitio,uom) 
                    VALUES('".$_POST['data'][$k]['artNegado']."','".str_replace("'", "''",$_POST['data'][$k]['descripcion'])."','$vendedor','".$_POST['data'][$k]['cliente']."','".$_POST['data'][$k]['almacen']."',GETDATE(),'0',".$_POST['data'][$k]['cantDisp'].",".$_POST['data'][$k]['cantNegada'].",'".$_POST['data'][$k]['comentario']."','".$_POST['data'][$k]['sitio']."','".$_POST['data'][$k]['unidad']."');");
                    $result = $query->rowCount();
                }
                if ($result > 0) {
                    $resultado = 'OK';
                } else {
                    $resultado = 'FAIL';
                }
                echo json_encode($resultado);
                exit();
                break;
            
            case 'detDataNegados':
                $item=Application_Model_InicioModel::getDataNegados();
                print_r($item);
                exit();
                break;
        }
        // $_SESSION['tipoC']=Application_Model_InicioModel::getTipoCambio();
    }
    public function getArchivoAdjuntoAction() {
       //$model= new Application_Model_InicioModel();
        $item=Application_Model_InicioModel::getArchivoAdjunto(filter_input(INPUT_GET,'transaction'),filter_input(INPUT_GET,'id'));
        $this->json($item);
    }
    public function refreshlinesAction(){
        // $this->_helper->layout->disableLayout();
        // $model= new Application_Model_InicioModel();
        print_r(json_encode(Application_Model_InicioModel::getRefreshLines(filter_input(INPUT_POST,'docType'),filter_input(INPUT_POST, 'docId'))));
        exit();
    }
    public function checaralertaAction() {
        // $this->_helper->layout->disableLayout();
        // $model= new Application_Model_InicioModel();
        print_r(json_encode(Application_Model_InicioModel::getStatusAlerta(filter_input(INPUT_POST,'ov'))));
        exit();
    }
   public function isServiceAction() {
       print_r(Application_Model_InicioModel::isService(filter_input(INPUT_POST,'item')));
       exit();
   }
    public function checarbloqueoAction() {
        // $this->_helper->layout->disableLayout();
        // $model= new Application_Model_InicioModel();
        print_r(json_encode(Application_Model_InicioModel::getStatusBloqueo(filter_input(INPUT_GET,'ov'))));
        exit();
    }
    public function sandboxvalidateAction() {
        print_r(json_encode(Application_Model_InicioModel::sandboxvalidate()));
        exit();
    }
    public function getultimasventasAction() {
        // $this->_helper->layout->disableLayout();
        // $model= new Application_Model_InicioModel();
        print_r(Application_Model_InicioModel::getUltimasVentas(filter_input(INPUT_POST,'cliente')));
        exit();
    }
    /**
     * TODO: modificar para que regrese lista co articulo y costo promedio
     */
    public function familiasAction(){
        $this->_helper->layout->disableLayout();        
        $datosinicio = new Application_Model_Userinfo();
        //$model= new Application_Model_InicioModel();
        $familia = filter_input(INPUT_GET,'familia');
        $sitio = filter_input(INPUT_GET,'sitio');
        $query = $datosinicio->_adapter->prepare("EXECUTE ExistenciasFamilia '$sitio','$familia'");
        $query->execute();
        $result = $query->fetchAll();
        if (empty($result)) {
            $ExistenciaFamilia['noresult'] = "Sin Resultados!";
        }
        foreach ($result as $k => $v) {
            $ExistenciaFamilia[$k] = $v;
        }
        echo json_encode($ExistenciaFamilia);
        exit();
    }

    public function detalleventaAction(){
        $variable = Application_Model_InicioModel::getUltimasVentas2(
            filter_input(INPUT_POST,'ov'), 
            filter_input(INPUT_POST,'transaction'), 
            filter_input(INPUT_POST,'sitio')
        );
        print_r($variable);
        exit();
    }

    public function getalternativosAction() {
        // $this->_helper->layout->disableLayout(); 
        $alternativos = Application_Model_InicioModel::getAlternativos(filter_input(INPUT_POST,'itemId'), filter_input(INPUT_POST,'sitio'));
        print_r($alternativos);
        exit();        
    }
    
    public function resumentestAction() {
        //$this->_helper->layout->disableLayout();
        $model= new Application_Model_InicioModel();
        $claveclte = filter_input(INPUT_POST, 'claveclte');
        $direcciones = $model->getInvoiceDeliveryAddress($claveclte);
        if ($direcciones != 'NoResults') {
            $RecIdDelivery = filter_input(INPUT_POST, 'RecIdDireccion');
            $RecIdInvoiced = filter_input(INPUT_POST, 'RecIdDireccion');
            if (!empty($direcciones['entrega'])) { $RecIdDelivery = $direcciones['entrega']; } 
            if (!empty($direcciones['factura'])) { $RecIdInvoiced = $direcciones['factura']; }
        }
        $CurrencyCode = filter_input(INPUT_POST, 'moneda');
        $SiteId = filter_input(INPUT_POST, 'sitio');
        $LocationId = filter_input(INPUT_POST, 'almacen');
        $PaymMode = filter_input(INPUT_POST, 'CargoDesc');
        $DeliveryMode = filter_input(INPUT_POST, 'modoentrega');
        $DeliveryTerm = filter_input(INPUT_POST, 'condiEntrega');
        $WorkerResponsible = filter_input(INPUT_POST, 'responsableventa');
        $WorkerTaker = filter_input(INPUT_POST, 'secretarioventa');
        $User = $_SESSION['userInax'];
        $comentariosCabecera = filter_input(INPUT_POST, 'comentariosCabecera');
        $ocCliente = filter_input(INPUT_POST, 'OrdenCliente');
        $referenciaCliente = filter_input(INPUT_POST,'ReferenciaCliente');
        $documentType = filter_input(INPUT_POST, 'documentType');
        $seguridadCR = filter_input(INPUT_POST, 'digitos');
        $edicion = filter_input(INPUT_POST, 'edit');
        $docId = filter_input(INPUT_POST, 'id');
        // Datos de configuración del WebService
        try {                        
            $options = array('claveclte'=> $claveclte,
                      'company'             => COMPANY,
                      'RecIdDelivery'       => $RecIdDelivery,
                      'RecIdInvoiced'       => $RecIdInvoiced,
                      'CurrencyCode'        => $CurrencyCode,
                      'SiteId'              => $SiteId,
                      'LocationId'          => $LocationId,
                      'PaymMode'            => $PaymMode,
                      'DeliveryMode'        => $DeliveryMode,
                      'DeliveryTerm'        => $DeliveryTerm,
                      'WorkerResponsible'   => $WorkerResponsible,
                      'WorkerTaker'         => $WorkerTaker,
                      '_User'               => $User,
                      'comentariosCabecera' => $comentariosCabecera,
                      'ocCliente'           => $ocCliente,
                      'referenciaCliente'   => $referenciaCliente,
                      'seguridadCR'         => $seguridadCR,
                      'workerTaker'         => $WorkerTaker,
                      'user'                => $User,
                      'idCurrency'          => $CurrencyCode,
                      'documentId'          =>$docId,
                      'cliente'             =>$claveclte );
            $res=Application_Model_InicioModel::setHeader($options, $documentType);  
            $resultado = array('OV' => $res['OV'],'encabezadoOV' => $res['encabezado'],'documentType' => $documentType);
            echo json_encode($resultado);
        }
        catch (Exception $objError) {
            echo '<b>'.$objError->getMessage().'</b>';                
        }
        exit();
    }

    public function liberarAlmacenAction(){
        // $this->_helper->layout->disableLayout();
        // $model = new Application_Model_InicioModel();
        $ov                 = filter_input(INPUT_POST, 'ov');
        $sitio              = filter_input(INPUT_POST, 'sitio');
        $impuestos          = 'VTAS';
        $dlvMode            = filter_input(INPUT_POST, 'dlvMode');
        $condiEntrega       = filter_input(INPUT_POST, 'condiEntrega');
        $moneda             = filter_input(INPUT_POST, 'moneda');
        $metodoPagoCode     = filter_input(INPUT_POST, 'metodoPagoCode');
        $proposito          = filter_input(INPUT_POST, 'proposito');
        $paymentTermName    = filter_input(INPUT_POST, 'paymentTermName');

        $result = Application_Model_InicioModel::liberar($ov,$sitio,$impuestos,$dlvMode,$condiEntrega,$moneda,$metodoPagoCode,$proposito,$paymentTermName);
        $this->json($result);
        //print_r($result);
        exit();
    }
    public function reviewOrdenAction(){
        // $this->_helper->layout->disableLayout();
        // $model = new Application_Model_InicioModel();
        // {ov:ov,paymode:paymentmode,modoent:modoentrega,condi:condientre},
        $ov = filter_input(INPUT_POST, 'ov');
        $paymode = filter_input(INPUT_POST, 'paymode');
        $modoent = filter_input(INPUT_POST, 'modoent');
        $condi = filter_input(INPUT_POST, 'condi');
        $result = Application_Model_InicioModel::reviewOrder($ov,$paymode,$modoent,$condi);
        $this->json($result);
        //print_r($result);
        exit();
    }

    public function generarremisionAction(){
        try {
            $this->_helper->layout->disableLayout();
            $model= new Application_Model_InicioModel();
            $ov = filter_input(INPUT_POST, 'ov');
            $dimensiones = $_POST['dimensiones'];
            $remision = $model->setNewRemision($ov,$dimensiones);
            echo $remision;   
        }
        catch (Exception $exc) {
            echo $exc->getMessage();
        }
        exit();
    }

    public function validarlimitecreditoAction() {
        $this->_helper->layout->disableLayout();
        $model= new Application_Model_InicioModel();
        $ov = filter_input(INPUT_POST,'ov');
        $usuario = filter_input(INPUT_POST, 'usuario');
        $res=$model->setCotToOv($ov, $usuario);
        $this->json($res);
    }

    public function mandaralertaAction() {
        $result = Application_Model_InicioModel::mandaralerta(filter_input(INPUT_POST, 'ov'),filter_input(INPUT_POST, 'cliente'));
        $this->json($result);
        //print_r($result);
        exit();
    }


    public function reviewCreditLimitAction() {
        //$this->_helper->layout->disableLayout();
       // $model= new Application_Model_InicioModel();
        $customer = filter_input(INPUT_POST,'customer');
        $factur = filter_input(INPUT_POST,'factura');
        //Se manda el monto Coti diferente de '' para calcular el limite cuando es cotizacion
        $montoCoti = (isset($_POST['montocoti'])) ? filter_input(INPUT_POST, 'montocoti') :'';
        $tipopago = (isset($_POST['tipopago'])) ? filter_input(INPUT_POST, 'tipopago') :'';
        $res= Application_Model_InicioModel::reviewCreditLimit($customer, $factur,$montoCoti,$tipopago);
        $this->json($res);
    }

    public function parcharproAction() {
        $orventa = filter_input(INPUT_POST,'ov');
        $proposito = filter_input(INPUT_POST, 'proposito');
        $dlvMode = filter_input(INPUT_POST, 'dlvmode');
        $paymentmode = filter_input(INPUT_POST, 'paymentmode');
        $res= Application_Model_InicioModel::parcharProp($orventa, $proposito,$dlvMode,$paymentmode);
        $this->json($res);
    }

    public function setdimensionlinesAction(){
        $orventa = filter_input(INPUT_POST,'ov');
        $dimLin = $_POST['dimLin'];
        $dimAtp = $_POST['dimAtp'];
        $res = Application_Model_InicioModel::setDimensionLines($orventa,$dimLin,$dimAtp);
        $this->json($res);
    }

    public function convertircotovAction() {
        try {
            //$this->_helper->layout->disableLayout();
            $cotizacion         = filter_input(INPUT_POST, 'cotizacion');
            $noCuenta           = filter_input(INPUT_POST, 'cuenta');
            $cliente            = filter_input(INPUT_POST, 'cliente');
            $sitio              = filter_input(INPUT_POST, 'sitio');
            $impuestos          = 'VTAS';
            $dlvMode            = filter_input(INPUT_POST, 'dlvMode');
            $condiEntrega       = filter_input(INPUT_POST, 'condiEntrega');
            $moneda             = filter_input(INPUT_POST, 'moneda');
            $metodoPagoCode     = filter_input(INPUT_POST, 'metodoPagoCode');
            $proposito          = filter_input(INPUT_POST, 'proposito');
            $paymentTermName    = filter_input(INPUT_POST, 'paymentTermName');
            $comentarioCabecera = filter_input(INPUT_POST, 'comentarioCabecera');
            $saleresponsible    = filter_input(INPUT_POST, 'saleresponsible');
            $clientname         = filter_input(INPUT_POST, 'clientname');
            $promoCodeName      = filter_input(INPUT_POST, 'promoCodeName');
            $promoCodeEnable      = filter_input(INPUT_POST, 'promoCodeEnable');
            if($sitio== 'MEXL'||$sitio== 'TJNA'||$sitio== 'JURZ'){
               ///////////////////////////checar el codigo postal del cliente en la entity/////////////////////////////////////
                $token = new Token();
                curl_setopt_array(CURL1, array(
                    CURLOPT_URL => "https://".DYNAMICS365."/data/CustomersV3?%24filter=CustomerAccount%20eq%20'".$cliente."'&%24select=AddressZipCode",
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
                    $impuestos = 'FRONT';
                }
            }
            ///////nuevo parametro conversion de cotizacion a OV/////////
            $parametro = array( '_QuotationId'       => $cotizacion,
                                'noCuenta'          => $noCuenta,
                                'company'           => COMPANY,
                                'cliente'           => $cliente,
                                'metodoPagoCode'    => $metodoPagoCode,
                                'dlvMode'           => $dlvMode,
                                'comentarioCabecera'=> $comentarioCabecera,
                                'proposito'         => $proposito,
                                'sitio'             => $sitio,
                                'impuestos'         => $impuestos,
                                'condiEntrega'      => $condiEntrega,
                                'moneda'            => $moneda,
                                'paymentTermName'   => $paymentTermName,
                                'FreightZone'       => $promoCodeName,
                                'promoCodeEnable'   => $promoCodeEnable,
                                'clientname'        => $clientname
                            );
            $ovQuot = Application_Model_InicioModel::setCot2Ov($parametro);
            /* obtiene forma de pago*/
            if($dlvMode == 'PAQUETERIA')
                Application_Model_InicioModel::updateOvLable($ovQuot['msg'],$cotizacion,$clientname,$saleresponsible,$sitio);
            $paymode = Application_Model_InicioModel::getPayModeByOV($ovQuot['msg']); 
            // if($paymode[0]["PAYMMODE"] == "02" || $paymode[0]["PAYMMODE"] == "99" || $paymode[0]["PAYMMODE"] == "04"){
            //     /* -- verifica numero de compras del cliente -- */
            //    $num=$model->getNumeroCompras(filter_input(INPUT_POST,'cliente'));
            //    $tot=$num[0][0];
            //    if((integer)$tot<=3){                
            //        //$cotizacionModel = new Application_Model_CotizacionModel();
            //        $items=Application_Model_InicioModel::getItems($cotizacion);
            //        /* -- verifica montos de cotizacion */
            //        $srt=0;
            //        $detalle='<table border="1" ><tr><th style="padding:12px;">Código</th><th style="padding:12px;">Cantidad</th><th style="padding:12px;">Descripción</th></tr>';
            //        foreach ($items as $k => $v) {
            //            $srt+=$v['Importe'];
            //            $detalle.='<tr ><td style="padding:12px;">'.$v['CodigoArticulo'].'</td><td style="padding:12px;" >'.number_format($v['Cantidad'],2).'</td><td style="padding:12px;">'.$v['DescripcionArticulo'].'</td></tr>';
            //        }
            //        $detalle.='</table>';
            //        if($srt>=45000){
            //            $user=["tecnologias@avanceytec.com.mx","gerenteventas@avanceytec.com.mx","gerentecomercial@avanceytec.com.mx","caja2@avanceytec.com.mx"];
            //            $asunto='Alerta de cliente nuevo';
            //            $titulo='Se ha creado una orden de venta '.$ovQuot['msg'].' de la cotizacion: '.$cotizacion;
            //            $mensaje=  '<h1 style="color:red">Favor de verificar y dar seguimiento a este proceso.</h1>';                    
            //            $mensaje.='<br><b>Usuario:</b>'.$_SESSION['userInax'].'<br><b>Nombre Completo:</b>'.$_SESSION['fullname'];
            //            $body = file_get_contents(APPLICATION_PATH.'/configs/traspasosSolicitud.html');
            //            $body .= '<br><h4>Datos de Cotizacion:</h4><br>'.$detalle;
            //            $css =  file_get_contents(BOOTSTRAP_PATH.'css/bootstrap.min.css');
            //            $bodytag = str_replace("{MENSAJE}", $mensaje, $body);
            //            $bodytag2 = str_replace("{TITULO}", $titulo, $bodytag);
            //            $bodytag3 = str_replace("<style></style>",'<style>'.$css.'</style>', $bodytag2);
            //            $model->sendMail($user,$asunto, $bodytag3);
            //        }                
            //    }
            // }
            $this->json($ovQuot);
        } catch (Exception $objError) {
            $this->json($objError->getMessage());
        }
    }
    public function confirmarovAction() {
        $this->_helper->layout->disableLayout();
        $datosinicio = new Application_Model_Userinfo();
        $model= new Application_Model_InicioModel();
        $ctaBanco = filter_input(INPUT_POST, 'ctaBanco');
        $xml = filter_input(INPUT_POST, 'lineaXML');
        $encabezadoov = filter_input(INPUT_POST, 'encabezadoov');
        $metodoPago = filter_input(INPUT_POST, 'metodoPago');
        $origenVenta = filter_input(INPUT_POST, 'origenVenta');
        $OV =$xml;
        if($OV !=""){
            $model->setKardex("ORDVTAC");
            $confirmacion = $OV;
        }
        else {$confirmacion = 'FAIL';}
        /*
         * Query para insertar origen del historial de la venta o cotizacion
         */
        $query = $datosinicio->_adapter->prepare(KARDEX_VENTAS_OV);
        $query->bindParam(1,$confirmacion);
        $query->bindParam(2,$ctaBanco);
        $query->bindParam(3,$xml);
        $query->bindParam(4,$encabezadoov);
        $query->bindParam(5,$metodoPago);
        $query->bindParam(6,$origenVenta);
        $query->bindParam(7,$_SESSION['userInax']);
        $query->execute();
        $this->json($confirmacion);
    }
    
    public function confirmarCotizacionAction() {
        //$this->_helper->layout->disableLayout();
        //$datosinicio = new Application_Model_Userinfo();
        // $confirmacion =  filter_input(INPUT_POST,'lineaXML');
        // $encabezadoov = filter_input(INPUT_POST, 'encabezadoov');
        // $metodoPago = filter_input(INPUT_POST, 'metodoPago');
        // $ctaBanco = filter_input(INPUT_POST, 'ctaBanco');
        // $origenVenta = filter_input(INPUT_POST, 'origenVenta');
        
        //  Query para insertar origen del historial de la venta o cotizacion
         
        // $query = $datosinicio->_adapter->prepare(KARDEX_VENTAS_COT);
        // $query->bindParam(1,$confirmacion);
        // $query->bindParam(2,$ctaBanco);
        // $query->bindParam(3,$confirmacion);
        // $query->bindParam(4,$encabezadoov);
        // $query->bindParam(5,$metodoPago);
        // $query->bindParam(6,$origenVenta);
        // $query->bindParam(7,$_SESSION['userInax']);
        // $query->execute();
        // $this->json($confirmacion);
        // $model= new Application_Model_InicioModel();
        //if (!empty($result)){
          $salesQuotationNumber = filter_input(INPUT_POST, 'salesQuotationNumber');
          $resultCot = Application_Model_InicioModel::enviarCotizacion($salesQuotationNumber);
          $this->json($resultCot);
        //}
    }
    public function modoentregaAction(){
        $docId = filter_input(INPUT_POST,'documentId');
        $modoEntrega = filter_input(INPUT_POST,'modoEntrega');
        $res = Application_Model_InicioModel::setModoEntrega($docId,$modoEntrega);
        $this->json($res);
    }
    public function newdocumentAction() {
        try {
            // $this->_helper->layout->disableLayout();
            // $model= new Application_Model_InicioModel();
            $RecIdDelivery = filter_input(INPUT_POST,'cliente');
            $datosinicio = new Application_Model_Userinfo();
            $fechaIni    = new Datetime();
            $parametros  = array('usuario'           => $_SESSION['userInax'],
                                'evento'             => 'Nueva/Edicion Cotizacion',
                                'pantalla'           => 'Resumen Cotizacion',
                                'documentoCreado'    => 'N/A',
                                'fechaInicio'        => $fechaIni,
                                'idPOV'              => $_SESSION['idProcesoVenta']
                            );
            $datosinicio->saveHistory($parametros);

            $docId = filter_input(INPUT_POST,'id');
            $tipo = filter_input(INPUT_POST,'tipo');
            if ($docId == 'N/A') { $docId = '';}
            $documentType = filter_input(INPUT_POST,'documentType');                    
            $options = array(
                    'claveclte'=> filter_input(INPUT_POST, 'claveclte'),
                    'RecIdDelivery'       => $RecIdDelivery,
                    'RecIdInvoiced'       => $RecIdDelivery,
                    'CurrencyCode'        => filter_input(INPUT_POST,'moneda'),
                    'SiteId'              => filter_input(INPUT_POST,'sitio'),
                    'LocationId'          => filter_input(INPUT_POST,'almacen'),
                    'PaymMode'            => filter_input(INPUT_POST,'MetodoPago'), 
                    'DeliveryMode'        => filter_input(INPUT_POST,'modoentrega'),
                    'DeliveryTerm'        => filter_input(INPUT_POST,'condiEntrega'),
                    'WorkerResponsible'   => filter_input(INPUT_POST,'responsableventa'),
                    'WorkerTaker'         => filter_input(INPUT_POST,'secretarioventa'),
                    'cliente'             => filter_input(INPUT_POST,'cliente'),
                    'direccion'           => filter_input(INPUT_POST,'direccion'),
                    '_User'               => $_SESSION['userInax'],
                    'comentariosCabecera' => filter_input(INPUT_POST,'comentariosCabecera'),
                    'ocCliente'           => filter_input(INPUT_POST,'OrdenCliente'),
                    'referenciaCliente'   => filter_input(INPUT_POST,'ReferenciaCliente'),
                    'seguridadCR'         => filter_input(INPUT_POST,'ctaBanco'),
                    'origenVenta'         => filter_input(INPUT_POST,'origenVenta'),
                    'documentId'          => $docId,
                    'Payment'             =>filter_input(INPUT_POST,'payment'),
                    'edicion'             =>filter_input(INPUT_POST,'edit'),
                    'promoCodeEnable'     =>$_POST['promoCodeEnable'],
                    'promoCodeName'       =>$_POST['promoCodeName']
                );
            $res = Application_Model_InicioModel::setHeader($options, $documentType);
            if($documentType == 'CTZN'){
                $result = array(
                    'CTZN' => $res->SalesQuotationNumber,
                    'documentType' => $documentType
                );
            }else{
                $result = array(
                    'OV' => $res->SalesQuotationNumber,
                    'documentType' => $documentType
                );
            }
            $datosinicio->updateHistory($_SESSION['idProcesoVenta'],$res->SalesQuotationNumber);
            $this->json($result);
        } catch (Exception $objError) {
            print_r($objError);
            $this->json('FAIL');
        }
    }
    public function resumentestlineasAction() {
        $this->_helper->layout->disableLayout();
        $model= new Application_Model_InicioModel();
        $NumFilas = $_POST['str']['NumFilas'];
        foreach ($_POST['str'] as $key => $value) {
            $lineas[$key] = $value;
        }
        $moneda = $_POST['str']['monedaLine'];
        $DocumentId = $_POST['str']['DocumentId'];
        $DocumentType = $_POST['str']['DocumentType'];
        $cliente = $_POST['str']['clte'];
        $lineasArr = array();
        $lineAttr = [];

        if($_POST['promoCodeBeenRemoved'] == 'true'){
            $res=$model->removePromo($DocumentId);
        } 

        for ($i = 1; $i < ($NumFilas + 1); $i ++) {
            ////////////////////////////calculo de precios T.C//////////////////////////////////////////////////////
            $lineasArr = [];
            if ( $_POST['str']['PorcentCargo'] == '7.16' || $_POST['str']['PorcentCargo'] == '13.11' ){
                $clte = $_POST['str']['clte'];
                $item = $lineas['item'.$i];
                $qty = $lineas['cantidad'.$i];
                $fecha1 = date('c', time());
                $fecha = strtotime($fecha1);
                $moneda = $_POST['str']['monedaLine'];
                $sitio = $lineas['sitio'.$i];
                $almacen = $lineas['almacen'.$i];
                $punitario = $lineas['punitariolinea'.$i];
                $cargo = number_format(((((($_POST['str']['PorcentCargo']/100) + 1 ) / 1.018) - 1) * 100),3);// aqui se resta el 1.8% de cargo para que dynamics lo calcule 
                $parametros = array(
                '_CustAccount'      => $clte,
                '_ItemId'           => $item,
                '_SalesPrice'       => (double) $punitario,
                '_Date'             => $fecha,
                '_amountQty'        => (double) $qty,
                '_currencyCode'     => $moneda,
                '_InventSiteId'     => $sitio,
                '_InventLocationId' => $almacen,
                '_PercentCharges'   => (double) $cargo);
            
                //$precioWS = $model->checarPreciosNew($parametros);
                $lineas['punitariolinea'.$i] = $precioWS['precio'];
            }
            $SalesPrice = str_replace('$', '', $lineas['preciovta' . $i]);
            $SalesPrice = str_replace(',', '', $SalesPrice);
            $lineasArr[]=array( 'numLine'         => $i,
                                'item'            => $lineas['item' . $i],
                                'sitio'           => $lineas['sitio' . $i],
                                'almacen'         => $lineas['almacen' . $i],
                                'localidad'       => $lineas['localidad' . $i],
                                'lote'            => $lineas['lote' . $i] ,
                                'matricula'       => $lineas['matricula' . $i] ,
                                'cantidad'        => $lineas['cantidad' . $i],
                                'punitariolinea'  => $lineas['punitariolinea' . $i],
                                'comentariolinea' => $lineas['comentariolinea' . $i],
                                'descripcion'     => $lineas['descripcion' . $i],
                                'documentId'      => $DocumentId,
                                '_dataAreaId'     => $lineas['dataAreaId' . $i],
                                '_InventoryLotId' => $lineas['InventoryLotId' . $i],
                                '_cargoMoneda'    => $lineas['cargoMoneda' . $i],
                                '_unidad'         => $lineas['unidad' . $i],
                                'dire'            => $_POST['dire'],
                                'moneda'          => $_POST['moneda'],
                                '_SalesPrice'     => $SalesPrice,
                                '_documentType'   => $DocumentType,
                                'categoria'       => $lineas['categoria' . $i],
                                'cliente'         => $cliente
                            );

            if($_POST['promoCodeEnable'] != 'false'){
                if($_POST['promoCodeEnable']['ItemsList'][$lineas['item' . $i]]){
                    $res=$model->setLineas($lineasArr,$DocumentId,$DocumentType,true);
                }else{
                    $res=$model->setLineas($lineasArr,$DocumentId,$DocumentType,false);
                }
            }else{
                if($_POST['promoCodeBeenRemoved'] == 'true'){
                    if($_POST['itemsList'][$lineas['item' . $i]]){
                        $res=$model->setLineas($lineasArr,$DocumentId,$DocumentType,false,true);
                    }else{
                        $res=$model->setLineas($lineasArr,$DocumentId,$DocumentType,false);
                    }
                }else{
                    $res=$model->setLineas($lineasArr,$DocumentId,$DocumentType,false);
                }
            }
            array_push($lineAttr, array('dataAreaId' => $res['dataAreaId'],'InventoryLotId' => $res['InventoryLotId']));
        }//fin del for

        $id = $res['SalesQuotationNumber'];
        $CURLOPT_URL = "https://".DYNAMICS365."/Data/SalesQuotationLines?%24filter=SalesQuotationNumber%20eq%20'".$DocumentId."'&%24top=0&%24count=true";
        if ($DocumentType != 'CTZN'){
            $id = $res['SalesQuotationNumber'];
            $CURLOPT_URL = "https://".DYNAMICS365."/Data/SalesOrderLines?%24filter=SalesOrderNumber%20eq%20'".$DocumentId."'&%24top=0&%24count=true";
        }

        if($id !="" && !isset($res->error) && strpos($id, 'no coincide') === false){
            $model->setKardex("CTZNC");
            $token = new Token();
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

            $responseCuantas = curl_exec(CURL1);
            $err = curl_error(CURL1);

            if ($err) {
                $resultCount = "cURL Error #:" . $err;
            } else {
                $resultCountTemp = json_decode($responseCuantas);
                $resultCount =  json_decode(json_encode($resultCountTemp), True);
                $cuantasLineas = $resultCount["@odata.count"];
                if ($cuantasLineas == $NumFilas){
                    $resultado=array("fail"=>false,"res"=>$res['SalesQuotationNumber'],"art"=>$lineasArr,'lineAttr' => $lineAttr);
                    if ($documenType != 'CTZN'){
                        $resultado=array("fail"=>false,"res"=>$res['SalesOrderNumber'],"art"=>$lineasArr,'lineAttr' => $lineAttr);
                    }
                }else{
                    $resultado=array("fail"=>true,"res"=>$res['SalesQuotationNumber'],"art"=>$lineasArr,'lineAttr' => $lineAttr);
                    if ($documenType != 'CTZN'){
                        $resultado=array("fail"=>false,"res"=>$res['SalesOrderNumber'],"art"=>$lineasArr,'lineAttr' => $lineAttr);
                    }
                }
            }
        }
        else{
            $resultado=array("fail"=>true,"res"=>$res['SalesQuotationNumber'],"art"=>$lineasArr,'lineAttr' => $lineAttr);
        }
        $this->json($resultado);
    }
    public function vercreditoAction() {
        try{
            $this->_helper->layout->disableLayout();
            $datosinicio = new Application_Model_Userinfo();
            $datosinicio->_adapter->query(ANSI_NULLS);
            $datosinicio->_adapter->query(ANSI_WARNINGS);
            $clte = filter_input(INPUT_POST,'cliente');
            $queryLimite =  $datosinicio->_adapter->prepare(PRECIOS_LIMITE);
            $queryLimite->bindParam(1,$clte);
            $queryLimite->execute();
            $resultLimite = $queryLimite->fetchAll();
            $this->json($resultLimite);
        }
        catch (Exception $e){
            echo 'ERROR: '. $e->getMessage();
        }                
        exit();
    }
    public function ticketAction(){


        $curl = curl_init();

        // $mensaje = str_replace(" ", "%20",$_POST["textTicket"]);
        $mensaje = $_POST["textTicket"];
        print_r(htmlentities($mensaje));
        $mensaje = rawurlencode($mensaje);
        $mensaje = str_replace("%0D", "",$mensaje);
        // print_r($mensaje);
        // exit();

        curl_setopt_array($curl, array(
        CURLOPT_URL => "http://tickets.aytcloud.com/api/v3/requests?input_data=%7Brequest%3A%7Bsubject%3A%22Prueba%20del%20post%20Insomnia%22%2Crequester%3A%7Bname%3A%22".$_SESSION['userInax']."%22%7D%2Cpriority%3A%7Bname%3A%22Alta%22%2Cid%3A%2281506000000006803%22%7D%2Ccategory%3A%7Bname%3A%22Aplicaciones%20Internas%20(Intranet)%22%2Cid%3A%2281506000000123001%22%7D%2Csubcategory%3A%7Bname%3A%22INAX%22%2Cid%3A%2281506000000123389%22%7D%2Citem%3A%7Bname%3A%22Otros%22%2Cid%3A%2281506000000123421%22%7D%2Ctechnician%3A%7Bemail_id%3A%22sistemas6%40avanceytec.com.mx%22%2Cname%3A%22Edgar%20Mu%C3%B1oz%22%2Cid%3A%2281506000000116641%22%7D%2Ctemplate%3A%7Bname%3A%22Incidencia%20con%20InAX%22%2Cid%3A%2281506000000181205%22%7D%2Cdescription%3A%22".$mensaje."%22%7D%7D",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_COOKIE => "24653837a9=c320b0dbaa04d4d00cbd19348c61f432; sdpcscook=4959536b-f6bf-492a-be5d-36dea7e78761; JSESSIONID=4A31D40363C621534B18B46A9A403A63",
        CURLOPT_HTTPHEADER => array(
        "accept: application/vnd.manageengine.sdp.v3+json",
        "authorization: 595c9832c96377f3fe23a0886e6a8da4"
        ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        print_r($mensaje);
        print_r($response);exit();

         $this->_helper->layout()->disableLayout();
         $this->makeTiket->setText($_POST["textTicket"]);
         $this->makeTiket->setUser($_SESSION['fullname']);
         $this->makeTiket->loadLog("/var/log/httpd/error_log");
         $this->makeTiket->setElemento($_POST["elemento"]);
         if($_POST["select_send_atachment"] == 1 || $_POST["select_send_atachment"] == "1"){
            $this->makeTiket->setAttachment($this->makeTiket->base64_to_jpeg($_POST["base64"], "/tmp/atachment.png"));
         }
        $res = $this->makeTiket->send();
        $data["woid"] = 0;
        $data["message"] = "error";
        $data["response"] = $res;
        $resp = simplexml_load_string($res);
        if (isset($resp->response->operation->Details[0]->workorderid)) {
            $woId = $resp->response->operation->Details[0]->workorderid;
             $data["woid"] = $woId;
             $data["message"] = "success";
        } 
        /*
         *    TODO:agregar funcion de envio de correo
         */
        header('Content-Type: application/json');
        $this->json($data);
    }
    public function direccionesAction() {
        // $this->_helper->layout->disableLayout();
        // $model= new Application_Model_InicioModel();
        $cliente = filter_input(INPUT_POST, 'cliente');
        $items = Application_Model_InicioModel::getDirecciones($cliente);
        print_r($items); 
        exit();        
    }
    /*
     * regresa la lista de sitios al proporcionarle el estado 
     */
    public function sitiosAction() {
        // $this->_helper->layout->disableLayout();
        // $model= new Application_Model_InicioModel();
        $sitio = filter_input(INPUT_POST, 'sitio');
        $this->json(Application_Model_InicioModel::getSitio($sitio));
    }
    /*
     * obtiene el detalle del cliente
     */
    public function clienteAction() {
        // $this->_helper->layout->disableLayout();
        // $model= new Application_Model_InicioModel();
        $cliente = filter_input(INPUT_POST, 'cliente');
        $autoSel = filter_input(INPUT_POST, 'autoSel');
        $this->json(Application_Model_InicioModel::getClients($cliente,$autoSel));
    }
    /**
     * regresa el detalle del articulo al pasarle el item
     */
    public function productodetalleAction() {
        // $this->_helper->layout->disableLayout();
        //$model= new Application_Model_InicioModel();
        $this->json(Application_Model_InicioModel::getProductDetail(filter_input(INPUT_POST,'articulo')));
    }

    public function saveguideAction() {
        //  $ov,$paqueteria,$guia,$descripcion
        // $this->_helper->layout->disableLayout();
        //$model= new Application_Model_InicioModel();
        $this->json(Application_Model_InicioModel::saveguide(filter_input(INPUT_POST,'ov'),filter_input(INPUT_POST,'paqueteria'),filter_input(INPUT_POST,'guia'),filter_input(INPUT_POST,'descripcion'),filter_input(INPUT_POST,'cliente')));
    }

    public function paqueteriasAction() {
        $modelo = new Application_Model_InicioModel();
        $this->json($modelo->paqueterias(filter_input(INPUT_POST,'ov')));
    }
    /**
    * guarda historial inicio
    **/
    public function savehistoryAction(){
        $datosinicio = new Application_Model_Userinfo();
        $evento      = $_POST['evento'];
        $pantalla    = $_POST['pantalla'];
        $documento   = $_POST['docCreado'];
        $fechaIni    = new Datetime();
        $parametros  = array('usuario'           => $_SESSION['userInax'],
                            'evento'             => $evento,
                            'pantalla'           => $pantalla,
                            'documentoCreado'    => $documento,
                            'fechaInicio'        => $fechaIni,
                            'idPOV'              => $_SESSION['idProcesoVenta']
                        );
        $datosinicio->saveHistory($parametros);
        print_r(json_encode('exito'));
        exit();
    }

    /**
     * regresa las existencias para el modal de existencias
     */
    public function existenciasAction() {
        // $this->_helper->layout->disableLayout();
        //$model= new Application_Model_InicioModel();
        $item = filter_input(INPUT_POST, 'item');
        $sitio = filter_input(INPUT_POST,'sitio');
        $almacen = filter_input(INPUT_POST,'almacen');
        $localidad = '';
        $documenType = filter_input(INPUT_POST,'documenType');
        
        $data = Application_Model_InicioModel::getExistencias($item,$sitio,$almacen,$localidad,$documenType,COMPANY);
        $data = json_decode($data,true);
        $data['complements'] = array();
        $complements = json_decode(Application_Model_InicioModel::getItemComplements($item));

        if(!empty($complements->value)){
            foreach($complements->value as $complement){
                $complementStock = json_decode(Application_Model_InicioModel::getExistencias($complement->Product2DisplayProductNumber,$sitio,$almacen,$localidad,$documenType,COMPANY));
                $complementStock->complementType = $complement->ProductRelationTypeName;
                $complementStock->complementName = $complement->Description;
                array_push($data['complements'], $complementStock);
            }
        }
        $res = Application_Model_InicioModel::getMinimoVenta($item);
        
        if($res){
            $map = $res;
            
            // foreach ($res as $r){
            //     $key = $r["INVENTSITEID"];
            //     $map[$key] = $r["MULTIPLEQTY"];
            // }
            // print_r($map);
            foreach ($data["datos"] as &$d){
                $key = $d["Articulo"];
                if($map['ITEMID'] == $key){
                    $d["minimo"] = $map['MULTIPLEQTY'];
                }else{
                    $d["minimo"] = false;
                }
            }
            
        } else {
            foreach ($data["datos"] as &$d){
                $d["minimo"] = "";//$res;
            }
        }
        //print_r($data);
        //die();
        if($_POST['onlylp'] == "true"){
            //print_r($data['lisencePlates']['value']);
            $platesArray = array( 'freePlates' => array(), 'reservePlates' => array() );
            //print_r($data['lisencePlates']['value']);
            //die();
            foreach ($data['lisencePlates']['value'] as $lisencePlate) {
                $lisencePlate = (object)$lisencePlate;
                if($lisencePlate->wMSLocationId == "" && $lisencePlate->LicensePlateId == "" && $lisencePlate->AvailPhysical < 0 && $lisencePlate->InventoryWarehouseId == $almacen && $lisencePlate->InventorySiteId == $sitio){
                    $platesArray['reservePlates'][] = $lisencePlate;
                }
                if($lisencePlate->InventorySiteId == $sitio && $lisencePlate->wMSLocationId != "" && $lisencePlate->AvailPhysical > 0 && $lisencePlate->LicensePlateId != "" && strtoupper($lisencePlate->InventoryStatusId) == "DISPONIBLE" && $lisencePlate->InventoryWarehouseId == $almacen ){
                    $platesArray['freePlates'][] = $lisencePlate;
                    //print_r($lisencePlate);
                }else{
                    continue;
                }
            }
            $this->json($platesArray);
        }else{
            $this->json($data);
        }
    }
    public function claveclienteAction() {
        // $this->_helper->layout->disableLayout();
        // $model= new Application_Model_InicioModel();
        $clveclte = filter_input(INPUT_POST, 'clveclte');

        //$this->json($model->getClientByClave($clveclte));
        //$client = $this->json(Application_Model_InicioModel::getClients($clveclte));
        $this->json(Application_Model_InicioModel::getClients($clveclte,'1'));

    }
    public function fraccionadoAction(){
        // $this->_helper->layout->disableLayout();
        //$model= new Application_Model_InicioModel();
        $this->json(Application_Model_InicioModel::getFraccionado(filter_input(INPUT_POST, 'almacen'),filter_input(INPUT_POST, 'item'))); 
    }
    public function devexistenciasAction() {
        try{

            // $datosinicio = new Application_Model_Userinfo();
            // $datosinicio->_adapter->query(ANSI_NULLS);
            // $datosinicio->_adapter->query(ANSI_WARNINGS);
            // $this->_helper->layout->disableLayout();
            // $model= new Application_Model_InicioModel();
            // /*tipo de articulo*/
            // $q = $datosinicio->_adapter->prepare(GET_ITEM_TYPE);
            // $q->bindParam(1,$item);
            // $q->execute();
            // $resq = $q->fetch();
            // $itemType=$resq['ITEMTYPE'];
            // /*conteo de articulo*/
            // $q2 = $datosinicio->_adapter->prepare(GET_ITEM_CONTEO);
            // $q2->bindParam(1,$item);
            // $q2->bindParam(2,$sitio);
            // $q2->bindParam(3,$almacen);
            // $q2->execute();
            // $resq2 = $q2->fetch();
            // $itemConteo=$resq2['CONTEO'];
            // /*PROCESO COMPLETO HERE*/
            // if ( ($itemType == 0) && ($itemConteo > 0)){
            //     $queryDisponible = $datosinicio->_adapter->prepare(GET_ITEM_EXISTENCIA);
            //     $queryDisponible->bindParam(1,$item);
            //     $queryDisponible->bindParam(2,$sitio); 
            //     $queryDisponible->bindParam(3,$almacen);
            //     $queryDisponible->bindParam(4,$localidad);
            //     $queryDisponible->execute();
            //     $resultDisponible = $queryDisponible->fetchAll();
            //     $existencia = 0;
            //     if ($resultDisponible[0]['Existencia']!=.0000000000000000) {
            //         $existencia = $resultDisponible[0]['Existencia'];
            //     }
            //     if ($qty > $existencia){
            //         $disponible = 'excedido';
            //     }
            //     else {
            //         $disponible = 'OK';
            //     }
            //     $precios=array('disponible' => $disponible,'cantDisp' => $existencia);
            //     $this->json($precios);
            // }
            // else{
            //     $precios=array('disponible' => 'excedido','cantDisp' => 0);
            //     $this->json($precios);
            // }
            $item = filter_input(INPUT_POST,'item');
            $sitio = filter_input(INPUT_POST,'sitio');
            $almacen = filter_input(INPUT_POST,'almacen');
            // $localidad = filter_input(INPUT_POST,'localidad');
            $qty =  filter_input(INPUT_POST,'cant');
            $datos = Application_Model_InicioModel::getDevExistenciasEntity(trim($item),trim($sitio),trim($almacen));
            if ($datos[0]->OnHandQuantity > 0){
                $disponible = 'OK';
                if ($qty > $datos[0]->OnHandQuantity){
                    $disponible  = 'excedido';
                }
                $precios=array('disponible' => $disponible,'cantDisp' => $datos[0]->OnHandQuantity);
            }
            else{
                $precios=array('disponible' => 'excedido','cantDisp' => 0);
            }
            $this->json($precios);

        }
        catch (Exception $e){
            $this->json($e);
        }
    }

    /**
     * test para comparar precios con ws vs db
     */
    public function listadoAction() {
        $model= new Application_Model_InicioModel();
        $items= $model->getClients();
        $this->json($items);
    }
    
    public function preciosAction(){
        try {

            // $objError="";
            // $this->_helper->layout->disableLayout();
            // $model          = new Application_Model_InicioModel();
            $item           = filter_input(INPUT_POST,'item');
            $clte           = filter_input(INPUT_POST,'cliente');
            $qty            =  filter_input(INPUT_POST,'qty');
            $fecha1         = date('c', time());
            $fecha          = strtotime($fecha1);
            $moneda         = filter_input(INPUT_POST,'moneda');
            $cargo          = filter_input(INPUT_POST,'cargo');
            $sitio          = filter_input(INPUT_POST,'sitio');
            $almacen        = filter_input(INPUT_POST,'almacen');
            $punitario      = filter_input(INPUT_POST,'punitario');
            $docId          = filter_input(INPUT_POST,'documentId');
            $dataAreaId     = filter_input(INPUT_POST,'dataAreaId');
            $InventoryLotId = filter_input(INPUT_POST,'InventoryLotId');
            $docType        = filter_input(INPUT_POST,'documenType');
            $unidad         = filter_input(INPUT_POST,'unidad');
            $paymMode       = filter_input(INPUT_POST, 'paymMode');
            $productGroupId = filter_input(INPUT_POST, 'productGroupId');
            $productType    = filter_input(INPUT_POST, 'productType');
            $getPriceFromWs = filter_input(INPUT_POST, 'getPriceFromWs');
            $arrOptions = array(
                '_CustAccount'      => $clte,
                '_ItemId'           => $item,
                '_SalesPrice'       => (double) $punitario,
                '_Date'             => $fecha,
                '_amountQty'        => (double) $qty,
                '_currencyCode'     => $moneda,
                '_InventSiteId'     => $sitio,
                '_InventLocationId' => $almacen,
                '_PercentCharges'   => (double) $cargo,
                '_documentId'       => $docId,
                '_dataAreaId'       => $dataAreaId,
                '_InventoryLotId'   => $InventoryLotId,
                '_documentType'     => $docType,
               '_unitId'            => $unidad,
               '_productGroupId'    => $productGroupId,
               '_productType'       => $productType,
               '_paymMode'          => $paymMode,
               '_getPriceFromWs'    => $getPriceFromWs
            );
            
            $result         = Application_Model_InicioModel::checarPreciosNew($arrOptions);
            $preciounit     = $result['precio'];
            $precioiva      = $result['precio_iva'];
            $dataAreaId     = $result['dataAreaId'];
            $InventoryLotId = $result['InventoryLotId'];
            $diferencia     = $result['diferencia'];
            $tipo           = $result['tipo'];
            $error="";
            if(isset($result['error'])){
                $error=$result['error'];
            }

            $impuestos = 'IVA';
            if(trim($sitio) == 'MEXL'|| trim($sitio) == 'TJNA'|| trim($sitio) == 'JURZ'){
                ///////////////////////////checar el codigo postal del cliente en la entity/////////////////////////////////////
                $token = new Token();
                curl_setopt_array(CURL1, array(
                    CURLOPT_URL => "https://".DYNAMICS365."/data/CustomersV3?%24filter=CustomerAccount%20eq%20'".$clte."'&%24select=AddressZipCode",
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
                    $impuestos = 'FRONT';
                }
            }

            $fechaPU = new DateTime();
            $parametrosPU = array(
                "CustAccount"       => $clte,
                "ItemId"            => $item,
                "amountQty"         => 1,
                "transDate"         => $fechaPU->format('m/d/Y'),
                "currencyCode"      => $moneda,
                "InventSiteId"      => $sitio,
                "InventLocationId"  => $almacen,
                "company"           => "atp",
                "PercentCharges"    => 0
            );
            
            $precioUnitario = self::getUnitPrice($parametrosPU);

            $precios = array(
                'preciocargo'    => $preciounit,
                'precioiva'      => $precioiva,
                'dataAreaId'     => $dataAreaId,
                'diferencia'     => $diferencia,
                'InventoryLotId' => $InventoryLotId,
                'tipo'           => $tipo,
                'error'          => $error,
                'impuestos'      => $impuestos,
                'precioUnitario' => $precioUnitario
            );
            //$result=$model->getPriceFromDB(filter_input(INPUT_POST,'cliente'), filter_input(INPUT_POST,'item'),filter_input(INPUT_POST,'moneda'), COMPANY, filter_input(INPUT_POST,'cargo'),$objError);
            //$this->json($result);
            $this->json($precios);
        } catch (Exception $objError) { 
            $model= new Application_Model_InicioModel();
            $result=$model->getPriceFromDB(filter_input(INPUT_POST,'cliente'), filter_input(INPUT_POST,'item'),filter_input(INPUT_POST,'moneda'), COMPANY, filter_input(INPUT_POST,'cargo'),$objError);
            $this->json($result);
        }
    }

    public static function getUnitPrice($parametros){
        $token = new Token();
        $CURLOPT_POSTFIELDS = json_encode((object)$parametros);
        $curl = curl_init();

        curl_setopt_array($curl, [
          CURLOPT_URL => "https://".DYNAMICS365."/api/services/STF_INAX/STF_ItemSalesPrice/getSalesPriceUnitPrice",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $CURLOPT_POSTFIELDS,
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
          $response = json_decode($response);
        }
        return $response;
    }

    public function emailAction() {
        $this->_helper->layout->disableLayout();
        $user="tecnologias@avanceytec.com.mx";
        $asunto='Alerta de falla InAX';
        $titulo=filter_input(INPUT_POST,'titulo');
        $mensaje=  filter_input(INPUT_POST,'mensaje');
        $body = '';
        $mensaje.='<br><b>Usuario:</b>'.$_SESSION['userInax'].'<br><b>Nombre Completo:</b>'.$_SESSION['fullname'];
        $body .= file_get_contents(APPLICATION_PATH.'/configs/correoBody.php');
        $css =  file_get_contents(BOOTSTRAP_PATH.'css/bootstrap.min.css');
        $bodytag = str_replace("{MENSAJE}", $mensaje, $body);
        $bodytag2 = str_replace("{TITULO}", $titulo, $bodytag);
        $bodytag3 = str_replace("<style></style>",'<style>'.$css.'</style>', $bodytag2);
        $model= new Application_Model_InicioModel();
        echo $model->sendMail($user, $asunto, $bodytag3);
        exit();
    } 

    public function emailservicioAction() {
        $this->_helper->layout->disableLayout();
        $user = array("coordinadorsoportetecnico@avanceytec.com.mx","gerenciaequipo@avancetec.com.mx","coordinadorequipo@avanceytec.com.mx","sistemas12@avanceytec.com.mx");
        $asunto = 'Peticion de servicio';
        $titulo = filter_input(INPUT_POST,'titulo');
        $mensaje = filter_input(INPUT_POST,'mensaje');
        $tecnico = filter_input(INPUT_POST,'tecnico');
        $numEmpleado = filter_input(INPUT_POST,'numEmpleado');
        $ov = filter_input(INPUT_POST,'ov');
        $factura = filter_input(INPUT_POST,'factura');
        $destinatario = implode(',', $user);
        $fecha = new DateTime(filter_input(INPUT_POST,'feha_ini'));
        $body = '';
        $mensaje.='<br><b>Usuario:</b>'.$_SESSION['userInax'].'<br><b>Nombre Completo:</b>'.$_SESSION['fullname'];
        $body .= file_get_contents(APPLICATION_PATH.'/configs/correoBodyServicio.php');
        $css =  file_get_contents(BOOTSTRAP_PATH.'css/bootstrap.min.css');
        $bodytag = str_replace("{MENSAJE}", $mensaje, $body);
        $bodytag2 = str_replace("{TITULO}", $titulo, $bodytag);
        $bodytag3 = str_replace("{FECHA}", $fecha->format('d/m/Y'), $bodytag2);
        $bodytag4 = str_replace("{DESTINATARIO}", $destinatario, $bodytag3);
        $bodytag5 = str_replace("{TECNICO}", $tecnico, $bodytag4);
        $bodytag6 = str_replace("{EMPLEADO}", $numEmpleado, $bodytag5);
        $bodytag7 = str_replace("{OV}", $ov, $bodytag6);
        $bodytag8 = str_replace("<style></style>",'<style>'.$css.'</style>', $bodytag7);
        $bodytag9 = str_replace("{FACTURA}",$factura, $bodytag8);
        $model= new Application_Model_InicioModel();

        $file = $_FILES;
        $this->json($model->sendMail($user, $asunto, $bodytag9, $file));
    } 
    /**
     * 
     * @param type $param
     */
    public function facturarAction(){
        $model= new Application_Model_InicioModel();
        $ov=  filter_input(INPUT_POST,'ov');
        /*agregar validacion de factura*/ 
        $isInvoice=$model->existFactura($ov);
        if(count($isInvoice)){
            $this->json(["resultado"=>"ok","respuesta"=>$isInvoice[0][0]]);
        }
        else{
            $remision=filter_input(INPUT_POST,'remision');
            $ordenCliente=filter_input(INPUT_POST,'ordenCliente');//aqui
            $refCliente=filter_input(INPUT_POST,'refCliente');//aqui
            $comentariosCabecera=filter_input(INPUT_POST,'comentariosCabecera');//aqui
            $direccion=filter_input(INPUT_POST,'direccion');
            $usoCFDI=filter_input(INPUT_POST,'usoCFDi');
            $modoPago=filter_input(INPUT_POST,'pagoModo');
            $pago=filter_input(INPUT_POST,'pago');
            $this->_helper->layout->disableLayout();
            $_SESSION['totalFactura']=0;
            $this->json($model->setFactura($ov,$remision,$ordenCliente,$refCliente,$comentariosCabecera,$direccion,$usoCFDI,$modoPago,$pago)); 
        }
        
    }
    
public function getDireccionesAction() {
    try {
        // $this->_helper->layout->disableLayout();
        // $model= new Application_Model_InicioModel();
        $this->json(Application_Model_InicioModel::getDireccionesCliente($_SESSION['userInax'],  filter_input(INPUT_POST,'ov'), filter_input(INPUT_POST,'cliente')));
    } catch (Exception $exc) {
        $this->json($exc->getTraceAsString());
    }
}

    public function diarioAction(){
        try {

            $timbre = "";
            if(filter_input(INPUT_POST,"timbrar")){
               $timbre="1";// print_r("se va timbrar");exit();
            }
            //$this->_helper->layout->disableLayout();
            //$model= new Application_Model_InicioModel();
            $this->json(Application_Model_InicioModel::crearDiario(
                            filter_input(INPUT_POST,"factura"),
                            filter_input(INPUT_POST,"contrapartida"),
                            filter_input(INPUT_POST,"descripcion"),
                            filter_input(INPUT_POST,"diarioMontoFactura"),
                            filter_input(INPUT_POST,"diarioCuentaContra"),
                            filter_input(INPUT_POST,'diarioFPago'),
                            filter_input(INPUT_POST,'customerDiario'),
                            $timbre,
                            filter_input(INPUT_POST,'tipoPago'),
                            filter_input(INPUT_POST,'dlvMode'),
                            filter_input(INPUT_POST,'referenciap'),
                            filter_input(INPUT_POST,'digitostarjeta')
                        )
                    );
        } catch (Exception $exc) {
           $this->json($exc);
        }
    }
    public function cuentaContrapartidaAction() {
        try{
            // $this->_helper->layout->disableLayout();
            // $model= new Application_Model_InicioModel();
            // $this->json($model->getCuentaContrapartida());
            // print_r($respuesta);exit();
            $respuesta = Application_Model_InicioModel::getCuentaContrapartida();
            $this->json($respuesta);
            exit();
        }
        catch (Exception $e){
            $this->json($e);
        }
    }
    public function cuentaContrapartidaLineaAction() {
        try {
            //print_r($_SESSION);exit();
            //$this->_helper->layout->disableLayout();
            //$model= new Application_Model_InicioModel();
            $this->json(Application_Model_InicioModel::getCuentaContrapartidaLinea(filter_input(INPUT_POST,'selec')));
            exit();
        } catch (Exception $exc) {
            $this->json($exc->getTraceAsString());
        }
    }

     public function generateWebPayLink($referenciaCliente, $cliente, $email,$ov, $sitio, $totalAmount){
        //$totalAmount = 1.0;////comentar para probar con montos de la ov
        $originalString = '<?xml version="1.0" encoding="UTF-8"?>
        <P>
        <business>
            <id_company>Z691</id_company>
            <id_branch>0031</id_branch>
            <user>Z691SIUS1</user>
            <pwd>C2V8SXKNRZ</pwd>
        </business>
        <url>
            <reference>'.$referenciaCliente.'</reference>
            <amount>'.$totalAmount.'</amount>
            <moneda>MXN</moneda>
            <canal>W</canal>
            <omitir_notif_default>1</omitir_notif_default>
            <promociones>C,3,6,9</promociones>
            <st_correo>1</st_correo>
            <fh_vigencia></fh_vigencia>
            <mail_cliente>'.$email.'</mail_cliente>
            <st_cr>A</st_cr>
            <datos_adicionales>
            <data id="1" display="true">
                <label>Orden de venta</label>
                <value>'.$ov.'</value>
            </data>
            <data id="2" display="true">
                <label>Cliente</label>
                <value>'.$cliente.'</value>
            </data>
            <data id="3" display="false">
                <label>Fuente</label>
                <value>inax</value>
            </data>
            <data id="4" display="false">
                <label>Sitio</label>
                <value>'.$sitio.'</value>
            </data>
            <data id="5" display="false">
                <label>Destino</label>
                <value>PROD</value>
            </data>
            </datos_adicionales>
        </url>
        </P>';
        $key = '326c6a039b80b35dbad8e552cb2dcb46'; //Llave de 128 bits
        $encryptedString = AESCrypto::encriptar($originalString, $key);
        $url = "<pgs><data0>9265655197</data0><data>$encryptedString</data></pgs>";
        //echo $url = urlencode($url);
        $encodedString = urlencode($url);
        //print_r($encodedString);exit();
        $curl = curl_init();

        curl_setopt_array($curl, array(
        // CURLOPT_URL => "https://wppsandbox.mit.com.mx/gen",
        CURLOPT_URL => "https://bc.mitec.com.mx/p/gen",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "xml=\n{$encodedString}",
        CURLOPT_HTTPHEADER => array(
            "content-type: application/x-www-form-urlencoded"
        ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $originalString = $response;
            $key = '326c6a039b80b35dbad8e552cb2dcb46'; //Llave de 128 bits
            $decryptedString = AESCrypto::desencriptar($originalString, $key);
            //print_r($decryptedString);
            $respuesta = simplexml_load_string($decryptedString);
            return $respuesta;
        }
    }

    public function getwebpayAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        extract($_GET);
        $response = $this->generateWebPayLink(($clientReference != '')? $clientReference:'C00000458902', $clientCode, ($emailInput != '')? $emailInput:"", $ov, $sitioLineas, str_replace('$', '',$totalOvAmount));
        $webPayLink = (string)$response->nb_url;
        $model= new Application_Model_InicioModel();
        $html = '
            <!doctype html>
            <html>
            <head>
                <meta name="viewport" content="width=device-width">
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                <title>Simple Transactional Email</title>
                <style>
                /* -------------------------------------
                    INLINED WITH htmlemail.io/inline
                ------------------------------------- */
                /* -------------------------------------
                    RESPONSIVE AND MOBILE FRIENDLY STYLES
                ------------------------------------- */
                @media only screen and (max-width: 620px) {
                table[class=body] h1 {
                    font-size: 28px !important;
                    margin-bottom: 10px !important;
                }
                table[class=body] p,
                        table[class=body] ul,
                        table[class=body] ol,
                        table[class=body] td,
                        table[class=body] span,
                        table[class=body] a {
                    font-size: 16px !important;
                }
                table[class=body] .wrapper,
                        table[class=body] .article {
                    padding: 10px !important;
                }
                table[class=body] .content {
                    padding: 0 !important;
                }
                table[class=body] .container {
                    padding: 0 !important;
                    width: 100% !important;
                }
                table[class=body] .main {
                    border-left-width: 0 !important;
                    border-radius: 0 !important;
                    border-right-width: 0 !important;
                }
                table[class=body] .btn table {
                    width: 100% !important;
                }
                table[class=body] .btn a {
                    width: 100% !important;
                }
                table[class=body] .img-responsive {
                    height: auto !important;
                    max-width: 100% !important;
                    width: auto !important;
                }
                }
                /* -------------------------------------
                    PRESERVE THESE STYLES IN THE HEAD
                ------------------------------------- */
                @media all {
                .ExternalClass {
                    width: 100%;
                }
                .ExternalClass,
                        .ExternalClass p,
                        .ExternalClass span,
                        .ExternalClass font,
                        .ExternalClass td,
                        .ExternalClass div {
                    line-height: 100%;
                }
                .apple-link a {
                    color: inherit !important;
                    font-family: inherit !important;
                    font-size: inherit !important;
                    font-weight: inherit !important;
                    line-height: inherit !important;
                    text-decoration: none !important;
                }
                #MessageViewBody a {
                    color: inherit;
                    text-decoration: none;
                    font-size: inherit;
                    font-family: inherit;
                    font-weight: inherit;
                    line-height: inherit;
                }
                .btn-primary table td:hover {
                    background-color: #34495e !important;
                }
                .btn-primary a:hover {
                    background-color: #34495e !important;
                    border-color: #34495e !important;
                }
                }
                </style>
            </head>
            <body class="" style="background-color: #f6f6f6; font-family: sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">
                <table border="0" cellpadding="0" cellspacing="0" class="body" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background-color: #f6f6f6;">
                <tr>
                    <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>
                    <td class="container" style="font-family: sans-serif; font-size: 14px; vertical-align: top; display: block; Margin: 0 auto; max-width: 580px; padding: 10px; width: 580px;">
                    <div class="content" style="box-sizing: border-box; display: block; Margin: 0 auto; max-width: 580px; padding: 10px;">

                        <!-- START CENTERED WHITE CONTAINER -->
                        <span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">This is preheader text. Some clients will show this text as a preview.</span>
                        <table class="main" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background: #ffffff; border-radius: 3px;">

                        <!-- START MAIN CONTENT AREA -->
                        <tr>
                            <td class="wrapper" style="font-family: sans-serif; font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 20px;">
                            <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">
                                <tr>
                                <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">
                                    <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Hola,</p>
                                    <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Hemos Generado una liga con la que podras acceder a tu pago, favor de dar click en la liga o en el boton de "Pagar" para continuar.</p>
                                    <a href='.$webPayLink.'>'.$webPayLink.'<a>
                                    <table border="0" cellpadding="0" cellspacing="0" class="btn btn-primary" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; box-sizing: border-box;">
                                    <tbody>
                                        <tr>
                                        <td align="left" style="font-family: sans-serif; font-size: 14px; vertical-align: top; padding-bottom: 15px;">
                                            <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: auto;">
                                            <tbody>
                                                <tr>
                                                <td style="font-family: sans-serif; font-size: 14px; vertical-align: top; background-color: #3498db; border-radius: 5px; text-align: center;"> <a href="'.$webPayLink.'" target="_blank" style="display: inline-block; color: #ffffff; background-color: #3498db; border: solid 1px #3498db; border-radius: 5px; box-sizing: border-box; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: bold; margin: 0; padding: 12px 25px; text-transform: capitalize; border-color: #3498db;">Pagar</a> </td>
                                                </tr>
                                            </tbody>
                                            </table>
                                        </td>
                                        </tr>
                                    </tbody>
                                    </table>
                                </td>
                                </tr>
                            </table>
                            </td>
                        </tr>

                        <!-- END MAIN CONTENT AREA -->
                        </table>

                        <!-- START FOOTER -->
                        <div class="footer" style="clear: both; Margin-top: 10px; text-align: center; width: 100%;">
                        <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">
                            <tr>
                            <td class="content-block" style="font-family: sans-serif; vertical-align: top; padding-bottom: 10px; padding-top: 10px; font-size: 12px; color: #999999; text-align: center;">
                                <span class="apple-link" style="color: #999999; font-size: 12px; text-align: center;">Avance y tecnologia en plasticos</span>
                                
                            </td>
                            </tr>
                            <tr>
                            
                            </tr>
                        </table>
                        </div>
                        <!-- END FOOTER -->

                    <!-- END CENTERED WHITE CONTAINER -->
                    </div>
                    </td>
                    <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>
                </tr>
                </table>
            </body>
            </html>     
        ';
        if($sendMail == 'true'){
            $model= new Application_Model_InicioModel();
            $model->sendMail($emailInput, 'Web Pay Message', $html);
        }
        if($sendSMS == 'true'){
            $messageBody = "Avance y Tecnologia en Plasticos, ".$clientCode.", ".$ov.", para completar tu compra accede a: ".$webPayLink;
            $modoEntrega = Application_Model_InicioModel::getDeliveryModeCode($ov);
            $this->sendSMS($messageBody, array($smsInput));
            //$this->sendSMS($messageBody, array($modoEntrega['Telefonos'],'6143982184','6142474230'));
        }
        $model::insertWebPayLog($ov, $clientCode, $webPayLink, str_replace('$', '',$totalOvAmount));
        echo json_encode(['response' => true]);
    }

    public static function sendSMS($messageBody,$numbersArray){        
        //$ws= new Metodos();
           $curl = curl_init();
           $jsonPostField = '{"content": "'.$messageBody.'", "to": ["'.implode(',',$numbersArray).'"]}';
           //echo $jsonPostField;
           //die();
           curl_setopt_array($curl, array(
           CURLOPT_URL => "https://platform.clickatell.com/messages",
           CURLOPT_RETURNTRANSFER => true,
           CURLOPT_ENCODING => "",
           CURLOPT_MAXREDIRS => 10,
           CURLOPT_TIMEOUT => 30,
           CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
           CURLOPT_CUSTOMREQUEST => "POST",
           CURLOPT_POSTFIELDS => $jsonPostField,
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
            return ['response' => false, 'error' => $err];
           } else {
            return ['response' => true, "message" => $response];
           }            
    }

    public function getclientinfoAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        //print_r($_GET);
        extract($_GET);
        $clientResponse = Application_Model_InicioModel::getClientInfo($clientCode);
        $clientResponse = $clientResponse->value[0];
        echo json_encode($clientResponse);
    }



    public function diarioDataAction() {
        try {
            $this->_helper->layout->disableLayout();
            $model= new Application_Model_InicioModel();
            $this->json($model->getCuentaContrapartida());
            exit();
        }catch (Exception $exc) {
            $this->json($exc->getTraceAsString());
        }
    }
    public function facturaLinesAction() {
        try {
           // $this->_helper->layout->disableLayout();
            // $model= new Application_Model_InicioModel();
            //print_r(filter_input(INPUT_POST,'customer'));exit();
            $this->json(Application_Model_InicioModel::getLinesFactura(filter_input(INPUT_POST,'ov')));
        }catch (Exception $exc) {
           $this->json($exc->getTraceAsString());
        }
    }
   /* public function actualiza2Action(){
        try {
            $this->_helper->layout->disableLayout();
            $model= new Application_Model_InicioModel();
            $this->json($model->actualiza());
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }*/

    public function eliminarLineasAction(){
        // $this->_helper->layout->disableLayout();
        // $model= new Application_Model_InicioModel();
        // $dataAreaId = filter_input(INPUT_POST, 'dataAreaId');
        // $InventoryLotId = filter_input(INPUT_POST, 'InventoryLotId');
        // $this->json($model->eliminarLineas($dataAreaId,$InventoryLotId));

        $dataAreaId     = filter_input(INPUT_POST, 'dataAreaId');
        $InventoryLotId = filter_input(INPUT_POST, 'InventoryLotId');
        $tipo           = filter_input(INPUT_POST, 'tipo');
        $docId          = filter_input(INPUT_POST, 'documentId');
        $this->json(Application_Model_InicioModel::eliminarLineas($dataAreaId,$InventoryLotId,$tipo,$docId));
    }

    public function seccionAction(){
        $cliente = filter_input(INPUT_POST, 'cliente');
        $this->json(Application_Model_InicioModel::getSeccion($cliente));
    }

    public function statusAction(){
        $ov = filter_input(INPUT_POST, 'ov');
        $this->json(Application_Model_InicioModel::getStatus($ov));
    }

    //****funcion para agregar los clientes que no estan en inax, pero si estan en cuscustomerv3
    public function addnewsAction(){
        (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $queryAdd = "EXECUTE AYT_ActualizarClientesInax 1;";
        $query = $conn->prepare($queryAdd);
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        print_r(json_encode($result[0]['Mensaje']));
        exit();
    }

    //****funcion para actualizar los clientes que estan en inax con los datos de dynamics
    public function updateclienteinaxAction(){
        $codigoCliente = filter_input(INPUT_POST, 'codigoCliente');
        $result = Application_Model_InicioModel::updateClientesInax($codigoCliente);
        print_r(json_encode($result));
        exit();
    }

    public function getpromosAction(){
        extract($_GET);
        if($edition == 'true'){
            $selectedPromoCode = Application_Model_InicioModel::getCotPromo($documentId);
            print_r(json_encode(array(
                "promoList" => Application_Model_InicioModel::getPromoCodes(),
                "promoSelected" => $selectedPromoCode
            )));
        }else{
            print_r(json_encode(Application_Model_InicioModel::getPromoCodes()));
        }
        exit();
    }

    public function postlabelAction(){
        extract($_POST);
        if($cot){
            Application_Model_ZPLTemplateModel::setZPLData($dataZPL,$cot);
            $result = Application_Model_InicioModel::postLabel($cot,$html);
        }else{
            Application_Model_ZPLTemplateModel::setZPLData($dataZPL,$ov);
            $result = Application_Model_InicioModel::postLabel($ov,$html,true);
        }
        exit();
    }

    public function haslabelAction(){
        extract($_GET);
        $result = Application_Model_InicioModel::hasLabel($docID);
        echo json_encode(array('hasLabel'=> $result));
        exit();
    }

    public function getestadosAction(){
        $estado = filter_input(INPUT_POST, 'idEstado');
        (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $queryEstado = "SELECT * FROM AYT_RentasIngNobleEstados;";
        $resourceEstado = $conn->query($queryEstado);
        $resourceEstado->execute();
        $resultEstado = $resourceEstado->fetchAll(PDO::FETCH_ASSOC);

        $estadosOption = '<option value="0">Seleccione Ciudad</option>';
        foreach($resultEstado AS $DataEstados){
            $estadosOption .= '<option value="'.$DataEstados['estadoId'].'">'.strtoupper(utf8_encode($DataEstados['descripcion'])).'</option>';
        }
        print_r(json_encode($estadosOption));
        exit();
    }

    public function getciudadesAction(){
        $estado = filter_input(INPUT_POST, 'idEstado');
        (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $queryCiudad = "SELECT * FROM AYT_RentasIngNobleMunicipios WHERE estadoId = '".$estado."'";
        $resourceCiudad = $conn->query($queryCiudad);
        $resourceCiudad->execute();
        $resultCiudad = $resourceCiudad->fetchAll(PDO::FETCH_ASSOC);

        $ciudadesOption = '<option value="0">Seleccione Ciudad</option>';
        foreach($resultCiudad AS $DataCiudad){
            $ciudadesOption .= '<option value="'.$DataCiudad['municipioId'].'">'.utf8_encode($DataCiudad['descripcion']).'</option>';
        }
        print_r(json_encode($ciudadesOption));
        exit();
    }

    public function getserviciosAction(){
        (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $queryServicios = "SELECT * FROM AYT_Calendario_Tecnicos_TipoServicio WHERE status = 1;";
        $resourceServicios = $conn->query($queryServicios);
        $resourceServicios->execute();
        $resultServicios = $resourceServicios->fetchAll(PDO::FETCH_ASSOC);

        $serviciosOption = '<option value="0">Seleccione Servicio</option>';
        foreach($resultServicios AS $DataServicio){
            $serviciosOption .= '<option value="'.$DataServicio['idTipoServicio'].'">'.utf8_encode($DataServicio['tipo']).'</option>';
        }
        print_r(json_encode($serviciosOption));
        exit();
    }

    public function gettecnicosAction(){
        $token = new Token();
        curl_setopt_array(CURL1, array(
            CURLOPT_URL => "https://".DYNAMICS365."/Data/Employees?%24filter=KnownAs%20eq%20'Tecnico'&%24select=PersonnelNumber%2CNameAlias%2COfficeLocation%2CKnownAs%2CName",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array(
              "accept: application/json",
              "authorization: Bearer ".$token->getToken()[0]->Token."",
              "content-type: application/json"
            ),
        ));

        $responseTecs = curl_exec(CURL1);
        $err = curl_error(CURL1);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $resultTecnicos = json_decode($responseTecs);
        }

        $tecnicosOption = '<option value="0">Seleccione Tecnico</option>';
        foreach($resultTecnicos->value AS $DataTecnico){
            $tecnicosOption .= '<option value="'.$DataTecnico->PersonnelNumber.'">'.utf8_encode($DataTecnico->Name).'</option>';
        }
        print_r(json_encode($tecnicosOption));
        exit();
    }

    public function getinvoiceAction(){
       $invoiceNumber = filter_input(INPUT_POST, 'invoiceNumber');
       $cabecera = Application_Model_InicioModel::getInvoiceHeader($invoiceNumber);
       $cabeceraOV = Application_Model_InicioModel::getSalesOrderHeader($cabecera->value[0]->SalesOrderNumber);
       $lineasOV = Application_Model_InicioModel::getSalesOrderLines($cabecera->value[0]->SalesOrderNumber);
       $secretarioOV = Application_Model_InicioModel::getOrderTaker($cabeceraOV->value[0]->OrderTakerPersonnelNumber);
       $inUse = Application_Model_InicioModel::isInUse($invoiceNumber);
       $existe = true;
       if(
            sizeof($cabecera)     == 0 ||
            sizeof($cabeceraOV)   == 0 ||
            sizeof($lineasOV)     == 0 ||
            sizeof($secretarioOV) == 0 
        ){
        $existe = false;
       }
       $optLineas = '<option value="0">Seleccione Equipo</option>';
       foreach ($lineasOV->value as $DataLinea) {
           $optLineas .= '<option value="'.$DataLinea->ItemNumber.'">'.$DataLinea->LineDescription.'</option>';
       }
       $data = array(   "cliente" => $cabeceraOV->value[0]->SalesOrderName,
                        "codigoCliente" => $cabeceraOV->value[0]->OrderingCustomerAccountNumber,
                        "codigoVendedor" => $secretarioOV->value[0]->PersonnelNumber,
                        "equipo" => $optLineas,
                        "fechaFactura" => $cabecera->value[0]->InvoiceDate,
                        "itemId" => $lineasOV->value[0]->ItemNumber,
                        "vendedor" => $secretarioOV->value[0]->NameAlias,
                        "inUse" => $inUse
                    );
       $result = array('data' =>$data, 'status' => $existe);
       print_r(json_encode($result));exit();
    }

    public function saveserviceAction(){
        $servicio = $_POST['service'];
        $servicio = (array)json_decode($servicio);
        $result = Application_Model_InicioModel::SaveService($servicio);
        $msj = "";
        switch ($result)
        {
            case 1:
                  $msj = "La operacion se ha relizado con exito!.";
                  break;
            case 0:
                  $msj = "Existe una asignacion para ese tecnico en las fechas seleccionadas.";
                  break;
            case -1:
                  $msj = "Ocurrio un error al insertar el servicio.";
                  break;
        }
        $data = array('status' => $result, 'mensaje' => $msj);
        print_r(json_encode($data));
        exit();
    }

    //////////////cyr 03-05-2021///////////////////
    public function guardarcyrAction(){
        $correo     = filter_input(INPUT_POST, 'correo');
        $telefono   = filter_input(INPUT_POST, 'telefono');
        $customer   = filter_input(INPUT_POST, 'customer');
        $custName   = filter_input(INPUT_POST, 'customerName');
        $contact    = array(
                array(
                    'Description'       => 'CyR_M', 
                    'Locator'           => $correo,
                    'Type'              => 2,
                    'IsPrimary'         => 0,
                    'LocatorExtension'  => ''
                ),
                array(
                    'Description'       => 'CyR_T',
                    'Locator'           => $telefono,
                    'Type'              => 1,
                    'isPrimary'         => 0,
                    'LocatorExtension'  => ''
                )
            );
        $result = Application_Model_InicioModel::guardarCYR($contact,$customer,$custName);

        print_r(json_encode($result));exit();
    }

    public function existeinfocyrAction(){
        $customer = filter_input(INPUT_POST, 'customer');

        $result = Application_Model_InicioModel::getInfoCYR($customer);

        print_r(json_encode($result));exit();
    }
    
    public function applycreditrequestAction(){
        $creditRequest = filter_input_array(INPUT_POST);
        $files = $_FILES;
        $resultCredit = Application_Model_InicioModel::ApplyCreditRequest($creditRequest['data2'],$files);
        print_r($resultCredit);
        exit();
    }
}

class AESCrypto{

    /**

     * Permite cifrar una cadena a partir de un llave proporcionada

     * @param strToEncrypt

     * @param key

     * @return String con la cadena encriptada

     */



    public static function encriptar($plaintext, $key128){

        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-128-cbc'));

        $cipherText = openssl_encrypt ( $plaintext, 'AES-128-CBC', hex2bin($key128), 1, $iv);

        return base64_encode($iv.$cipherText);

      }





    /**

     * Permite descifrar una cadena a partir de un llave proporcionada

     * @param strToDecrypt

     * @param key

     * @return String con la cadena descifrada

     */



    public static function desencriptar($encodedInitialData, $key128){

      $encodedInitialData =  base64_decode($encodedInitialData);

      $iv = substr($encodedInitialData,0,16);

      $encodedInitialData = substr($encodedInitialData,16);

      $decrypted = openssl_decrypt($encodedInitialData, 'AES-128-CBC', hex2bin($key128), 1, $iv);

      return $decrypted;

    }

}