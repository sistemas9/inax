<?php

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
ini_set('display_errors', '1'); 

ini_set("memory_limit","-1");
class IndexController extends Zend_Controller_Inax{

    public function init(){
        if(empty($_SESSION['userInax'])){
            $this->_redirect('/login');
        }
        // if(COMPANY=='LIN'){
        //    $this->_redirect('/errorFactura');
        // } 
    }
    public function setClientsToFileAction() {
        $model= new Application_Model_InicioModel();
        $model->setClientsToFile();
        $model->setItemsToFile();
        $this->json(array("res"=>"clientes cargado"));
    }
    public function aAction() {
        // $model= new Application_Model_InicioModel();
        // $this->json($model->getPriceItemClient());
        $this->json(Application_Model_InicioModel::getPriceItemClient());
    }
    public function indexAction() {  
        $date=new DateTime();
        // $model= new Application_Model_InicioModel();
        $this->view->payTerm        =  Application_Model_InicioModel::getPayTerm();
        $this->view->usoCFDI        = Application_Model_InicioModel::getUsoCDFI();
        $this->view->payMode        = Application_Model_InicioModel::getPayMode();
        $datosinicio                = new Application_Model_Userinfo();
        $this->view->cargos         = $datosinicio->Cargos();
        $this->view->fechaActual    = $date->format('d/m/Y');
        $this->view->userSes        = $_SESSION['userInax'];
        $this->view->sucursalActual = Application_Model_InicioModel::getSucursal();
        $this->view->offline        = Application_Model_InicioModel::checkOffline();
        $_SESSION['offline']        = $this->view->offline;
    }
    public function getDataOvAction() {
        // $this->_helper->layout->disableLayout();
        // $model = new Application_Model_IndexModel();
        // $this->json($model->getDataPaymentOv(filter_input(INPUT_POST, 'ov')));
        $this->json(Application_Model_IndexModel::getDataPaymentOv(filter_input(INPUT_POST, 'ov')));
    }
    public function datosetiquetaAction(){
        // $this->_helper->layout->disableLayout();
        // $model = new Application_Model_IndexModel(); 
        $sitio = filter_input(INPUT_POST, 'sitio');
        $ov = filter_input(INPUT_POST, 'ov');                   
        $isCot = filter_input(INPUT_POST, 'isCot')? filter_input(INPUT_POST, 'isCot'): ''; 
        if($isCot == '')
            $this->json(Application_Model_IndexModel::getDatosEtiqueta($_SESSION['userInax'], $sitio, $ov));
        else
            $this->json(Application_Model_IndexModel::getDatosEtiqueta($_SESSION['userInax'], $sitio, $ov, true));
        // $this->json($model->getDatosEtiqueta($_SESSION['userInax'], $sitio, $ov));
    }
    public function datospropoAction() {
        $this->_helper->layout->disableLayout();
        $model = new Application_Model_IndexModel(); 
        $ov = filter_input(INPUT_POST, 'ov');                
        $cot = filter_input(INPUT_POST, 'cot')? filter_input(INPUT_POST, 'cot'): '';
        if($cot == '') 
            print_r(json_encode($model->getDatosPropo($_SESSION['userInax'],$ov)));
        else
            print_r(json_encode($model->getDatosPropo($_SESSION['userInax'],$cot, true)));    
        exit();
    }
    public function todasov2Action() {
        // $this->_helper->layout->disableLayout();
        // $model = new Application_Model_IndexModel();  
        // $this->json($model->getTodasOV2());
        $this->json(Application_Model_IndexModel::getTodasOV2());
    }
    public function ovuser2Action() {
        // $this->_helper->layout->disableLayout();
        // $model = new Application_Model_IndexModel();        
        // echo json_encode($model->getOVporUsuario2($_SESSION['userInax']));
        $this->json(Application_Model_IndexModel::getOVporUsuario2($_SESSION['userInax']));
        exit();
    }
    public function miscot2Action(){
        // $this->_helper->layout->disableLayout();
        // $model = new Application_Model_IndexModel();  
        // $this->json($model->getCotPorUsuario2($_SESSION['userInax']));
        $this->json(Application_Model_IndexModel::getCotPorUsuario2($_SESSION['userInax']));
    }
    public function todascot2Action() {
        // $this->_helper->layout->disableLayout();
        // $model = new Application_Model_IndexModel(); |
        // $this->json($model->getTodasCot2());
        $this->json(Application_Model_IndexModel::getTodasCot2());
    }    
    public function emailAction() {
        $titulo=filter_input(INPUT_POST,'titulo');
        $mensaje=  filter_input(INPUT_POST,'mensaje');
        $asunto=filter_input(INPUT_POST,'asunto');
        $formato=filter_input(INPUT_POST,'formato');
        $almacen = filter_input(INPUT_POST,'almacen');
        $model='';
        $this->_helper->layout->disableLayout();
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
        $mail->FromName = $titulo;
        if($formato=="fallasinax.php"){
            $model= new Application_Model_FallasinaxModel();
             $mail->addAddress('fdelgado@avanceytec.com.mx'); 
        } 
        $res=false;
        if($formato=='traspasosSolicitud.html'){
            $type=filter_input(INPUT_POST,'type');
            $solicita=  filter_input(INPUT_POST,'solicita');
            $model= new Application_Model_SolicitudescedisModel();
            if($type==0){
                $solicita=filter_input(INPUT_POST,'user');
                $res=$model->insertSolicitudNew(filter_input(INPUT_POST,'cliente'), filter_input(INPUT_POST,'vendedor'),filter_input(INPUT_POST,'item'),filter_input(INPUT_POST,'cant'),filter_input(INPUT_POST,'almacen'), $solicita,  filter_input(INPUT_POST,'comenta'),filter_input(INPUT_POST,'motivo'),filter_input(INPUT_POST,'venta'));
            }
            if($type==1){
                $res=$model->updateEstatus(filter_input(INPUT_POST,'folio'),2);
                if($res){
                    $res=$model->update("UPDATE dbo.AYT_TraspasosinaxDetalle SET usuario= :user , modificacion=GETDATE() where folio= :id ", array(":id"=>filter_input(INPUT_POST,'folio'),":user"=>$_SESSION['userInax']));
                }
            }
            if($type==2){
                $res=$model->update(UPDATE_TRASPASO_DETALLE,array(":id"=>filter_input(INPUT_POST,'folio'),":comentarios"=> filter_input(INPUT_POST,'comentarios'),":user"=>$_SESSION['userInax']));
                if($res){
                    $res=$model->updateEstatus(filter_input(INPUT_POST,'folio'),3);
                }
            }
            if($type==3){
                $res=$model->updateEstatus(filter_input(INPUT_POST,'folio'),4);
            }
            if ($type==5) {
                $res=$model->update(UPDATE_TRASPASO_DETALLE,array(":id"=>filter_input(INPUT_POST,'folio'),":comentarios"=> filter_input(INPUT_POST,'comentarios'),":user"=>$_SESSION['userInax']));
                if($res){
                    $res=$model->updateEstatus(filter_input(INPUT_POST,'folio'),5);
                }
            }
            if($solicita===''){ $solicita=$_SESSION['email'];}
            if(CONFIG!=DESARROLLO){                
                $pos = strpos($almacen, 'VEQP');              
                if( $pos === false ){
                    $direcciones = array('traspasosconsumibles@avanceytec.com.mx',$solicita);
                }else{
                    $direcciones = array('traspasosequipo@avanceytec.com.mx',$solicita);
                }
                // $direcciones=array('analistacedis@avanceytec.com.mx','asistentealmacenchih@avanceytec.com.mx','servsucursales1@avanceytec.com.mx','gerentecedis@avanceytec.com.mx',$solicita,'analistachih@avanceytec.com.mx','almacenchih@avanceytec.com.mx','reabastecimiento2@avanceytec.com.mx','embarques2@avanceytec.com.mx','almacencedis@avanceytec.com.mx'); //
            foreach ($direcciones as $key => $value){
                $mail->addAddress($value);
            }
        }
            else {
                 $mail->addAddress($solicita);
                 $mail->addAddress('sistemas9@avanceytec.com.mx');
                 $mail->addAddress('servsucursales1@avanceytec.com.mx');
            }
        }
        $body = '';           
        $mail->Subject = $asunto;
        $body .= file_get_contents(APPLICATION_PATH.'/configs/'.$formato);
        $bodytag = str_replace("{MENSAJE}", $mensaje, $body);
        $bodytag2 = str_replace("{TITULO}", $titulo, $bodytag);
        $bodytag3 = str_replace("<style></style>",'<style></style>', $bodytag2);
        $mail->msgHTML(utf8_decode($bodytag3));
        $mail->AltBody = '';
       if (!$mail->send()) {
            echo "Mailer Error: " . $mail->ErrorInfo;
        } else {
           $d='enviado por correo pero no dado de alta en solicitudes';
           if($res){
               $d='enviado';
        }
           echo $d;
        }
        exit();
    }
    public function clientePasaAction() {
        // $this->_helper->layout->disableLayout();
        // $model = new Application_Model_IndexModel();  
        // $this->json($model->getTotasOVCliente('%'.  filter_input(INPUT_POST,'nombre').'%','%'.  filter_input(INPUT_POST,'ov').'%'));
        $this->json(Application_Model_IndexModel::getTotasOVCliente('%'.  filter_input(INPUT_POST,'nombre').'%','%'.  filter_input(INPUT_POST,'ov').'%'));
    }
    public function condiEntregaAction() {
        // $this->_helper->layout->disableLayout();
        // $model = new Application_Model_IndexModel();
        // $this->json($model->condicionesEntrega(filter_input(INPUT_POST, 'ovta')));
        $this->json(Application_Model_IndexModel::condicionesEntrega(filter_input(INPUT_POST, 'ovta')));
    }

    public function miscotoffAction(){
        $result = Application_Model_IndexModel::getTodasCotOff();
        $this->json($result);
    }

    public function convertirmiscotoffAction(){
        $cot    =  filter_input(INPUT_POST,'cot');
        $ov     =  filter_input(INPUT_POST,'ov');
        $result = Application_Model_IndexModel::convertirCotOffline($ov,$cot);
        print_r(json_encode($result));
        exit();
    }
    public function enviarcorreofacturaAction(){
        $url        = filter_input(INPUT_POST,'url');
        $email      = filter_input(INPUT_POST,'email');
        $transdate  = filter_input(INPUT_POST,'transdate');
        $urlGetPDF  = str_replace('type=inax', 'type=InvoiceSender', $url);
        $factura    = $this->getFactura($urlGetPDF);
        $this->_helper->layout->disableLayout();
        $urlPDF = 'inax.aytcloud.com';
        $urlXML = str_replace('type=inax', 'type=xmlCliente', $url);
        if (CONFIG == 'AOS06'){
            $urlPDF = str_replace('inax.aytcloud.com', 'svr02:8989', $url);
        }
        include (LIBRARY_PATH.'/includes/phpMailer/PHPMailerAutoload.php');
        $body = '<br>
                <font face="Calibri">Estimado Cliente:<br><br></font>
                <font face="Calibri">Usted está  recibiendo un comprobante fiscal digital (Factura Electrónica) de Avance y Tecnologia en Plasticos S.A. de C.V.<br><br></font>
                <font face="Calibri">Dicha factura se entrega en archivo XML conforme lo marcan las disposiciones fiscales y usted podrá  visualizarlo en PDF e imprimirlo libremente para incluirlo en su contabilidad y/o resguardar la impresión y archivo XML<br><br></font>
                <font face="Calibri">Si usted desea que llegue el comprobante fiscal a otro correo electrónico distinto a aquel en el que estamos notificándole hasta hoy, por favor escriba su petición al siguiente correo electrónico: cuentafacturacion@avanceytec.com.mx<br><br><br></font>
                ________________________________________<br></font>
                <font face="Calibri">Saludos cordiales.<br></font>
                <font face="Calibri">Avance y Tecnologia en Plasticos S.A. de C.V.<br></font>';
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
        $mail->FromName = 'Facturacion Electronica';
        $this->addRecipientMails($mail,$email);
        $attachmentPDF = $urlPDF;
        $attachmentXML = $urlXML;
        $mail->addStringAttachment(file_get_contents($url), $factura.'.pdf');
        $mail->addStringAttachment(file_get_contents($attachmentXML), $factura.'.xml');
        $mail->Subject  = 'Envio de Factura. Fecha de Factura: '.$transdate;
        $mail->msgHTML(utf8_decode($body));
        $mail->AltBody = '';
       if (!$mail->send()) {
            $d = 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            $d='enviado';
        }
        print_r(json_encode($d));
        exit();
    }

    public function getFactura($url){
        $token = new Token();
        $curl = curl_init();
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
            "authorization: Bearer ".$token->getToken()[0]->Token."",
            "content-type: application/json",
            "x-total-count: application/json"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        return $response;
    }

    public function addRecipientMails($mail,$emails){
        $emailsTemp = explode(';',$emails);
        foreach($emailsTemp AS $Data){
            $mail->addAddress($Data);
        }
    }

    public function enviarcorreocotizacionAction(){
        $url        = filter_input(INPUT_POST,'url');
        $email      = filter_input(INPUT_POST,'email');
        $fecha      = filter_input(INPUT_POST,'fecha');
        $cotizacion = filter_input(INPUT_POST,'cotizacion');
        $this->_helper->layout->disableLayout();
        include (LIBRARY_PATH.'/includes/phpMailer/PHPMailerAutoload.php');
        $body  = '<br>';
        $body .= '<font face="Calibri">Estimado Cliente:</font>';
        $body .= '<br>';
        $body .= '<font face="Calibri">Reciba un cordial saludo, así mismo adjunto cotización del material solicitado.</font>';
        $body .= '<br>';
        $body .= '<br>';
        $body .= '<font face="Calibri">            Consideraciones IMPORTANTES.</font>';
        $body .= '<br>';
        $body .= '<ul>';
        $body .= '    <li><font face="Calibri">  La cotización tiene vigencia de 24 horas.</font></li>';
        $body .= '    <li><font face="Calibri">  Es importante validar existencias, antes de realizar pago.</font></li>';
        $body .= '    <li><font face="Calibri">  Indicar método de pago y uso de CFDI, en caso de requerir factura.</font></li>';
        $body .= '    <li><font face="Calibri">  En caso de confirmar su pedido, y este sea enviado por paquetería, el pago se deberá realizar antes de las 2 pm, de lo contrario, viajará el día siguiente.</font></li>';
        $body .= '    <li><font face="Calibri">  Cuando el material sea enviado por embarque, deberá validar con nosotros las salidas programadas así como realizar el pago 2 días hábiles antes de la salida del embarque.</font></li>';
        $body .= '    <li><font face="Calibri">  Material enviado por paquetería, antes de recibir y firmar de conformidad, es muy importante que lo revise, en caso de detectar alguna anomalía favor de no aceptarlo y comunicarse con nosotros.</font></li>';
        $body .= '    <li><font face="Calibri">  En caso de la devolución de material sin cortar, se generará un cargo del 20% del sub total del producto y el flete será responsabilidad del cliente. Toda devolución se deberá de reportar dentro de las primeras 72 horas de la compra, de lo contario no procederá.</font></li>';
        $body .= '    <li><font face="Calibri">  En material cortado no hay cambios ni devoluciones.</font></li>';
        $body .= '</ul>';
        $body .= '<br>';
        $body .= '<br>';
        $body .= '<font face="Calibri">Por último, lo invitamos a visitar nuestra página web www.avanceytec.com.mx, en donde encontrara nuestra extensa gama de productos y promociones.</font>';
        $body .= '<br>';
        $body .= '<font face="Calibri">Agradeciendo su interés y el tiempo, quedo a la orden para cualquier duda y/o aclaración.</font>';
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
        $mail->FromName = 'Cotizacion Electronica';
        $this->addRecipientMails($mail,$email);
        $mail->addStringAttachment(file_get_contents($url), $cotizacion.'.pdf');
        $mail->Subject  = 'Envio de Cotizacion. Fecha de Cotizacion: '.$fecha;
        $mail->msgHTML(utf8_decode($body));
        $mail->AltBody = '';
       if (!$mail->send()) {
            $d = 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            $d='enviado';
        }
        print_r(json_encode($d));
        exit();
    }
}