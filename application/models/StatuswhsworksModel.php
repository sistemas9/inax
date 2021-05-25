<?php

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
ini_set('display_errors', '1'); 

class Application_Model_StatuswhsworksModel {
    public $db;
    public $_adapter;
    public static $token;
    public static $WorkTypes = array('Pick' => 'Selecionar','Print' => 'Imprimir','Put' => 'Colocar');
    public static $WorkStatus = array('Open' => 'Abierto','Closed' => 'Cerrado');
    public static $OvStatus = array('Backorder' => 'Abierta','Invoiced' => 'Facturada', 'Delivered' => 'Entregada', 'Cancelled' => 'Cancelada');
    public static $OvReleasedStatus = array('Open' => 'Abierta','Released' => 'Liberada');

    public function __construct(array $options = null){
        $this->db = new Application_Model_UserinfoMapper();
        $this->_adapter = $this->db->getAdapter();
        $query=$this->_adapter->query(ANSI_NULLS);
        $query=$this->_adapter->query(ANSI_WARNINGS);
        $query->execute();
        return $this->_adapter;
    }

    public static function getDataOv($ov){
        $token = new Token();
        (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if (!$_SESSION['offline']){
            curl_setopt_array(CURL1, array(
              CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesOrderHeadersV2?%24filter=ReleaseStatus%20eq%20Microsoft.Dynamics.DataEntities.WHSReleaseStatus'Released'%20and%20SalesOrderStatus%20eq%20Microsoft.Dynamics.DataEntities.SalesStatus'Backorder'&%24select=SalesOrderNumber,DefaultShippingSiteId,SalesOrderStatus,ReleaseStatus,OrderingCustomerAccountNumber,SalesOrderName",
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

            $responseOv = curl_exec(CURL1);
            $err = curl_error(CURL1);
        }else{
            $responseOv = 'nodata';
            return $responseOv;
        }

        $ovData = json_decode($responseOv);

        $body = '';
        foreach($ovData->value as $DataOV){
            $statusOV = Application_Model_StatuswhsworksModel::$OvStatus[$DataOV->SalesOrderStatus];
            $statusRelease = Application_Model_StatuswhsworksModel::$OvReleasedStatus[$DataOV->ReleaseStatus];
            $body .= '<tr>';
            $body .= '    <td class="SalesOrderNumber" data-ov="'.$DataOV->SalesOrderNumber.'">'.$DataOV->SalesOrderNumber.'</td>';
            $body .= '    <td>'.$DataOV->OrderingCustomerAccountNumber.'</td>';
            $body .= '    <td>'.$DataOV->SalesOrderName.'</td>';
            $body .= '    <td>'.$DataOV->DefaultShippingSiteId.'</td>';
            $body .= '    <td>'.$statusOV.'</td>';
            $body .= '    <td>'.$statusRelease.'</td>';
            $body .= '</tr>';
        }

        return $body;
    }

    public static function getDataWorkLines($ov){
        $token = new Token();
        (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if (!$_SESSION['offline']){
            curl_setopt_array(CURL1, array(
              CURLOPT_URL => "https://".DYNAMICS365."/Data/STF_WHSWorkLineEntity?%24filter=OrderNum%20eq%20'".$ov."'&%24orderby=LineNum%20asc",
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

            $responseWorkLines = curl_exec(CURL1);
            $err = curl_error(CURL1);
        }else{
            $responseWorkLines = 'nodata';
            return $responseWorkLines;
        }
        $workLines = json_decode($responseWorkLines);
        $body = '';
        foreach($workLines->value as $DataWorkLine){
            /////////////////////////////////Detalle de las linea////////////////////////////////////////////////
            curl_setopt_array(CURL1, array(
              CURLOPT_URL => "https://".DYNAMICS365."/Data/AYT_WHSWorkNotificaciones?%24filter=WorkId%20eq%20'".$DataWorkLine->WorkId."'%20and%20ItemId%20eq%20'".$DataWorkLine->ItemId."'%20and%20WorkType%20eq%20Microsoft.Dynamics.DataEntities.WHSWorkType'".$DataWorkLine->WorkType."'&%24select=QtyWork%2CUnitId%2CInventSiteId%2CInventLocationId&%24top=1",
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

            $responseWorkLinesDet = curl_exec(CURL1);
            $err = curl_error(CURL1);

            $queryNombre = "SELECT ProductSearchName FROM articulos2 WHERE ItemNumber = '".$DataWorkLine->ItemId."' ";
            $query = $conn->query($queryNombre);
            $query->execute();
            $resultNombre = $query->fetchAll(PDO::FETCH_OBJ);

            $worklineDet = json_decode($responseWorkLinesDet);
            ///////////////////////////////////////////////////////////////////////////////////////////////////
            if ($DataWorkLine->WorkStatus == 'Open'){
                $estatusCode = '<a class="btn-floating pulse" style="background:red; border: solid 1px black"><i class="material-icons dp48" style="color:black">highlight_off</i></a><label style="padding-left:5px">&nbsp; No procesado</label>';
            }else if ($DataWorkLine->WorkStatus == 'Closed'){
                $estatusCode = '<a class="btn-floating pulse" style="background:green; border: solid 1px black;"><i class="material-icons dp48" style="color:white">done_all</i></a><label style="padding-left:5px">&nbsp; Procesado</label>';
            }else if ($DataWorkLine->WorkStatus == 'Cancelled'){
                $estatusCode = '<a class="btn-floating pulse" style="background:yellow; border: solid 1px #FFD700;"><i class="material-icons dp48" style="color:orange">warning</i></a><label style="padding-left:5px">&nbsp; Cancelado</label>';
            }
            $lineNum        = $DataWorkLine->LineNum;
            $workType       = Application_Model_StatuswhsworksModel::$WorkTypes[$DataWorkLine->WorkType];
            $codigoArticulo = $DataWorkLine->ItemId;
            $descripcion    = json_decode(json_encode($resultNombre[0]->ProductSearchName));
            $cantidad       = $worklineDet->value[0]->QtyWork;
            $unidad         = $worklineDet->value[0]->UnitId;
            $sitio          = $worklineDet->value[0]->InventSiteId;
            $almacen        = $worklineDet->value[0]->InventLocationId;
            $estado         = $estatusCode;
            // $estado = Application_Model_StatuswhsworksModel::$WorkStatus[$DataWorkLine->WorkStatus];

            $body .= '<tr>';
            $body .= '    <td>'.$lineNum.'</td>';
            $body .= '    <td>'.$workType.'</td>';
            $body .= '    <td>'.$codigoArticulo.'</td>';
            $body .= '    <td>'.$descripcion.'</td>';
            $body .= '    <td>'.$cantidad.'</td>';
            $body .= '    <td>'.$unidad.'</td>';
            $body .= '    <td>'.$sitio.'</td>';
            $body .= '    <td>'.$almacen.'</td>';
            $body .= '    <td>'.$estado.'</td>';
            $body .= '</tr>';
        }
        if ($body == ''){$body = 'nodata';}
        return $body;
    }
}