<?php

class Application_Model_Userinfo{
    var $sitios;
    var $usuarios;
    public $db;
    public $_adapter;
 
    public function __construct(array $options = null){
        if (is_array($options)) {
            $this->setOptions($options);
        }
        $this->db = new Application_Model_UserinfoMapper();
        $this->_adapter = $this->db->getAdapter();
        $this->_adapter->query(ANSI_NULLS);
        $this->_adapter->query(ANSI_WARNINGS);
        return $this->_adapter;
    }
    public function Sitios($company){
        // $query = $this->_adapter->prepare(SITIOS);
        // $query->bindParam(1,$company);
        // $query->execute();
        // $result=$query->fetchAll();
        // if(empty($result)){
        //     $sitios[0] = "NoResults";
        // }else{
        //     $sitios = $result;
        // }
        // return  $sitios;
        $sitiosEnt = $this->getSitiosEntity($company);
        $sitios = [];
        foreach($sitiosEnt as $Data){
            $siteid = $Data->SiteId;
            $name = $Data->SiteName;
            array_push($sitios, array(
                                        'SITEID' => $siteid,
                                        'NAME' => $name
                                    ));
        }
        return $sitios;
    }

    public function getSitiosEntity($company){
        $token = new Token();
        $tokenTemp = $token->getToken();
        if (!$_SESSION['offline']){
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => "https://".DYNAMICS365."/Data/OperationalSites?%24select=SiteId%2CSiteName&%24filter=dataAreaId%20eq%20'".$company."'%20and%20SiteId%20ne%20'CEDSCHI'",
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

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);
        }else{
            $response = [];
            $response['value'] = array(array('SiteId' => 'AGSC', 'SiteName' => 'AGUASCALIENTES'),
                                       array('SiteId' => 'CHIH', 'SiteName' => 'CHIHUAHUA'),
                                       array('SiteId' => 'CULN', 'SiteName' => 'CULIACAN'),
                                       array('SiteId' => 'DURN', 'SiteName' => 'DURANGO'),
                                       array('SiteId' => 'EDMX', 'SiteName' => 'ESTADO DE MEXICO'),
                                       array('SiteId' => 'GDLJ', 'SiteName' => 'GUADALAJARA'),
                                       array('SiteId' => 'HERM', 'SiteName' => 'HERMOSILLO'),
                                       array('SiteId' => 'JURZ', 'SiteName' => 'JUAREZ'),
                                       array('SiteId' => 'LEON', 'SiteName' => 'LEON'),
                                       array('SiteId' => 'MEXL', 'SiteName' => 'MEXICALI'),
                                       array('SiteId' => 'MTRY', 'SiteName' => 'MONTERREY'),
                                       array('SiteId' => 'OBRG', 'SiteName' => 'OBREGON'),
                                       array('SiteId' => 'PBLA', 'SiteName' => 'PUEBLA'),
                                       array('SiteId' => 'QRTO', 'SiteName' => 'QUERETARO'),
                                       array('SiteId' => 'SALT', 'SiteName' => 'SALTILLO'),
                                       array('SiteId' => 'SLPS', 'SiteName' => 'SAN LUIS POTOSI'),
                                       array('SiteId' => 'TJNA', 'SiteName' => 'TIJUANA'),
                                       array('SiteId' => 'TORN', 'SiteName' => 'TORREON'),
                                       array('SiteId' => 'TXLA', 'SiteName' => 'TUXTLA'),
                                       array('SiteId' => 'VCRZ', 'SiteName' => 'VERACRUZ'),
                                       array('SiteId' => 'ZACS', 'SiteName' => 'ZACATECAS')
                                    );
            $response = json_encode($response);
        }

        if ($err) {
          $result = "cURL Error #:" . $err;
        } else {
          $result = json_decode($response);
        }
        return $result->value;
    }

    public function getCuentaPagoMostrador($user) {
        //return $this->db->Query(USUARIO_DIARIO,[":usuario"=>$user]);
        return array();
    }
    public function getCuentasPago(){
        return $this->db->Query(CUENTAS_PAGO);
    }

    public function userExist() {
        // $query = $this->_adapter->prepare("select * from ".INTERNA.".dbo.usrUsr where Usuario=? ");
        // $query->bindParam(1,$_SESSION['userInax']);
        // $query->execute();
        // $result=$query->fetchAll();
        $result = array(
            array('idUsr' => '3','Usuario' => 'sistemas07')
        );
        if(empty($result)){
            // $query = $this->_adapter->prepare("insert into ".INTERNA.".dbo.usrUsr(Usuario)values(?)");
            // $query->bindParam(1,$_SESSION['userInax']);
            // $query->execute();
        }
        
    }
    
    /**
     * 
     * @author Javier Delgado <packo6300@gmail.com>
     * @see Generar cotizacion,Generar Orden de Venta
     * @uses getOrigenesVenta() obtiene la lista de origenes de la venta. 
     */
    public function getOrigenesVenta(){
        try{
            $querySTR = "SELECT DATAAREAID AS dataAreaID,ORIGINDESCRIPTION AS Description, ORIGINCODE AS OriginId FROM SalesOrderOriginStaging WHERE dataAreaId = '".COMPANY."';";
            $query = $this->_adapter->query($querySTR);
            $query->execute();
            $res = $query->fetchAll(PDO::FETCH_ASSOC);
            
            // $res = $this->getSalesOriginEntity();
            $result = [];
            foreach($res as $Data){
                //array_push($result, array('ORIGINID' => $Data->OriginId, 'DESCRIPTION' => $Data->Description));//para entity
                array_push($result, array('ORIGINID' => $Data['OriginId'], 'DESCRIPTION' => $Data['Description']));
            }
            return json_encode($result);
        }
        catch (PDOException $e){
            echo $e->getMessage();
            exit();
        }
    }

    public function getSalesOriginEntity(){
        $token = new Token();
        $tokenTemp = $token->getToken();
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://".DYNAMICS365."/Data/STF_SalesOriginEntity?%24select=OriginId%2CDescription",
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

    public function usuarios($user){
        try{
            // $query =$this->_adapter->prepare("SELECT  T2.NAME,T3.RECID
            //    , CASE WHEN (T3.RECID = DBO.getRecidDynamicUser('$user')) THEN 'selected' ELSE '' END AS SEL
            //    , CASE WHEN (DBO.getRecidDynamicUser('$user') IS NOT NULL) THEN 'registrado' ELSE 'noRegistrado' END AS REGISTRADO
            // FROM DIRPARTYTABLE T2  INNER JOIN HCMWORKER T3 ON T2.RECID=T3.PERSON ORDER BY T2.NAME;");
            // $query ->bindParam(1,$user);
            // $query ->bindParam(2,$user);
            // $query->execute();
            // $result=$query->fetchAll();
           // $model= new Application_Model_InicioModel();
            $result = Application_Model_InicioModel::getUsuariosEntity();
            $optSitio='<option value="">Selecciona un usuario...</option>';
            $flag=0;
            $res=array("response"=>"ok","reg"=>$flag,"res"=>$optSitio);
            if(!empty($result)){
                foreach ($result as $data) {
            // print_r($data);exit();
                    $sel = '';
                    if($data->EDUCATION == $user){$sel = 'selected';}
                    //print_r($data->Education);echo ' - ';print_r($user);echo '<br>';
                    $optSitio .= '<option value="' . $data->PERSONNELNUMBER . '" '.$sel.'>' . strtoupper($data->NAMEALIAS) . '</option>';
                }
                $res=array("response"=>"ok","reg"=>$flag,"res"=>$optSitio);
            };
            return  $res;
        }
        catch (Exception $e){
            echo $e->getMessage();
            exit();
        }
    }

        public static function Cargos(){
        // $query = $this->_adapter->query(QUERY_CARGOS);
        // $query->execute();
        // $result=$query->fetchAll();
        // if(empty($result)){
        //     $cargos[0] = "NoResults";
        // }
        // foreach ($result as $k => $v){
        //     $cargos[$k]=$v;
        // }

        (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $queryComunes = "SELECT *, ( valor_porc* ((iva/100)+1) ) AS cargoMSI FROM AYT_CargoTarjetaCredito;";
        $query = $conn->query($queryComunes);
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        $TC3MSI = array_filter($result,
            function($cargo){
                if($cargo['tipo_pago'] == '04-1'){ return $cargo;};
            });
        $TC6MSI = array_filter($result,
            function($cargo){
                if($cargo['tipo_pago'] == '04-2'){ return $cargo;};
            });
        $TC9MSI = array_filter($result,
            function($cargo){
                if($cargo['tipo_pago'] == '04-3'){ return $cargo;};
            });
        $TC12MSI = array_filter($result,
            function($cargo){
                if($cargo['tipo_pago'] == '04-4'){ return $cargo;};
            });
        $cargos = array(
            array('PaymMode' => '01','STF_PercentageCharges' => '0.0000000000000000', 'NAME' =>  'EFECTIVO'),
            array('PaymMode' => '02','STF_PercentageCharges' => '0.0000000000000000', 'NAME' =>  'CHEQUE NOMINATIVO'),
            array('PaymMode' => '03','STF_PercentageCharges' => '0.0000000000000000', 'NAME' =>  'TRANFERENCIA ELECTRONICA'),
            array('PaymMode' => '04','STF_PercentageCharges' => '0.0000000000000000', 'NAME' =>  'TARJETA DE CREDITO'),
            array('PaymMode' => '04-1','STF_PercentageCharges' => $TC3MSI[4]['cargoMSI'], 'NAME' =>  'TARJETA DE CREDITO 3 MSI'),
            array('PaymMode' => '04-2','STF_PercentageCharges' => $TC6MSI[5]['cargoMSI'], 'NAME' =>  'TARJETA DE CREDITO 6 MSI'),
            array('PaymMode' => '04-3','STF_PercentageCharges' => $TC9MSI[6]['cargoMSI'], 'NAME' =>  'TARJETA DE CREDITO 9 MSI'),
            array('PaymMode' => '04-4','STF_PercentageCharges' => $TC12MSI[7]['cargoMSI'], 'NAME' =>  'TARJETA DE CREDITO 12 MSI'),
            array('PaymMode' => '28','STF_PercentageCharges' => '0.0000000000000000', 'NAME' =>  'TARJETA DE DEBITO'),
            array('PaymMode' => '99','STF_PercentageCharges' => '0.0000000000000000', 'NAME' =>  'OTROS')
        );
        return  $cargos;
    }

    public function ModosEntrega(){
        // $query = $this->_adapter->query(FORMA_ENTREGA);
        // $query->execute();
        // $result=$query->fetchAll();
        // if(empty($result)){
        //     $modosentrega[0] = "NoResults";
        // }
        // foreach ($result as $k => $v){
        //     $modosentrega[$k]=$v;
        // }
        $modosentrega = array(
            array('CODE' => 'DOMICILIO',  'TXT' => 'DOMICILIO'),
            array('CODE' => 'EMBARQUE',   'TXT' => 'EMBARQUE'),
            array('CODE' => 'NC',         'TXT' => 'NOTA DE CREDITO'),
            array('CODE' => 'PAQUETERIA', 'TXT' => 'PAQUETERIA'),
            array('CODE' => 'PASA',       'TXT' => 'CLIENTE PASA'),
            array('CODE' => 'ENTREGADO',  'TXT' => 'MATERIAL ENTREGADO'),
            array('CODE' => 'PROMESA',    'TXT' => 'PROMESA DE PAGO'),
            array('CODE' => 'C&R',        'TXT' => 'COMPRA Y RECOGE'),
            array('CODE' => 'RECIBE',     'TXT' => 'CLIENTE RECIBE')
        );
        return  $modosentrega;
    }
    public function ov2rem($param) {
        
    }
    public function ImprimirRemision($remision){
        $query=$this->_adapter->query(ANSI_NULLS);
        $query=$this->_adapter->query(ANSI_WARNINGS);
        $query = $this->_adapter->prepare(QUERY_REMISION2);
        $query->bindParam(1,$remision);  
        $query->execute();      
        $result=$query->fetchAll();
        if(empty($result)){ $Datosremision[0] = "NoResults";}
        foreach ($result as $k => $v){ $Datosremision[$k]=$v;}
        return  $result;
    }
    /**
     * 
     * @param type $param
     */
    public function getRevision($param) {
         $query=$this->_adapter->query(ANSI_NULLS);
        $query=$this->_adapter->query(ANSI_WARNINGS);
        $query = $this->_adapter->prepare(GET_OV_REV);
        $query->bindParam(1,$param);  
        $query->execute();      
        $result=$query->fetchAll();
        $r=$result[0]['PACKINGSLIPID'];
        return  $r;
    }
    
    public function setLoginKardex($usuario,$nombre) {
       try{
            $t='inicio de sesion';
            // $query=$this->_adapter->query(ANSI_NULLS);
            // $query=$this->_adapter->query(ANSI_WARNINGS);
            // $query = $this->_adapter->prepare(INSERT_KARDEX);
            // $query->bindParam(1,$usuario);
            // $query->bindParam(2,$nombre);
            // $query->bindParam(3,$t);
            // $query->execute();
       }catch (PDOException $err){
           echo $err;
           exit();
       }
    }

    public function getConteoNegados(){
        $user = $_SESSION['userInax'];
        $query=$this->_adapter->query(ANSI_NULLS);
        $query=$this->_adapter->query(ANSI_WARNINGS);
        $query = $this->_adapter->prepare(NEGADOS);
        $query->bindParam(1,$user);
        $query->execute();
        $result = $query->fetchAll();
        return $result[0]['conteoNegado'];
    }
    public function kardexLog($txt,$parametros,$response,$status,$mov) {
        try {
            $ip = $_SERVER["REMOTE_ADDR"];
            $query = $this->_adapter->prepare("INSERT INTO DBO.AYT_LogInax (USUARIO,FECHA,TIPO,IP,COMPANY,PARAMETROS,RESPONSE,ESTATUS) VALUES ('".$_SESSION['userInax']."',GETDATE(),'".$mov."','".$ip."','".COMPANY."','".json_encode($parametros)."','".$response."',".$status.");");
            $query->execute();
        } catch (Exception $exc) {
             throw new Exception ($exc);
        }         
    }
    public function guardarguia($ov,$paqueteria,$guia,$descripcion) {
        try {
            $query = $this->_adapter->prepare("INSERT INTO inax365_paqueterias (ov,paqueteria,guia,descripcion,usuarioCreacion,fechaCreacion) 
                    VALUES ('$ov','$paqueteria','$guia','$descripcion','".$_SESSION['userInax']."',GETDATE());");
            $query->execute();
        } catch (Exception $exc) {
             throw new Exception ($exc);
        }         
    }

    public function test($newArr){
        // $query = $this->_adapter->query("insert into ".INTERNA.".dbo.LogInax "
        //             ."(USUARIO,FECHA,TIPO,IP,COMPANY,PARAMETROS,RESPONSE,ESTATUS) VALUES "
        //             ."('".$newArr["user"]."','".$newArr["date"]."','".$newArr["action"]."','".$newArr["ip"]."','".$newArr["company"]."','".iconv('','UTF-8',$newArr["parametros"])."','".iconv('','UTF-8',$newArr["resultado"])."',".$newArr["estatus"].");");
        //     $query->execute();
    }
    public function getPermissions() {

        $query = $this->_adapter->prepare(USER_PERMISSIONS);
        //$query = $this->_adapter->prepare("SELECT GETDATE()");
        $query->bindParam(1,$_SESSION['userInax']);
        $query->execute();
        $r= $query->fetchAll();
        $arr=array();
        foreach ($r as $key => $v) {
            $arr[]= (int)$v['idRoll'];
        }
        // $arr = array(
        //     '1',
        //     '3',
        //     '12',
        //     '1',
        //     '10',
        //     '1',
        //     '14',
        //     '12',
        //     '2'
        // );
        return $arr;
    }
    function setNotificacionEspera($params=array()){
        $notificacion=':D';
        if(!empty($params)){
            $nombre_archivo = $_SESSION['user'].'.ini';
            $dir = __DIR__.'/../alerts/';
            $notificacion=$this->escribe_ini($params, $dir.$nombre_archivo);
}
        return $notificacion;            
    }
    function getNotificacionUser(){
        $nombre_archivo = $_SESSION['user'].'.ini';
        $dir = __DIR__.'/../alerts/';
        return parse_ini_file($dir.$nombre_archivo);
    }
    function escribe_ini($matriz, $archivo, $multi_secciones = true, $modo = 'w') {
        $salida = '';
        define('SALTO', "\n"); 
        if (!is_array(current($matriz))) {
            $tmp = $matriz;
            $matriz['tmp'] = $tmp; # no importa el nombre de la sección, no se usará
            unset($tmp);
        }
        foreach($matriz as $clave => $matriz_interior) {
            if ($multi_secciones) {
                $salida .= '['.$clave.']'.SALTO;
            }
            foreach($matriz_interior as $clave2 => $valor){
                $salida .= $clave2.' = "'.$valor.'"'.SALTO;
            }
            if ($multi_secciones) {
                $salida .= SALTO;
            }
        }
        $puntero_archivo = fopen($archivo, $modo);
        if ($puntero_archivo !== false) {
            $escribo = fwrite($puntero_archivo, $salida);
            if ($escribo === false) {
                $devolver = -2;
            } else {
                $devolver = $escribo;
            }
            fclose($puntero_archivo);
        } 
        else {
            $devolver = -1;
        } 
    return $devolver;
 }
     function leer_ini($param) {
         
     }

    public function saveHistory($parametros){
    try{
        $usuario            = $parametros['usuario'];
        $evento             = $parametros['evento'];
        $pantalla           = $parametros['pantalla'];
        $documentoCreado    = $parametros['documentoCreado'];
        $fechaInicio        = $parametros['fechaInicio'];
        $idPOV              = $parametros['idPOV'];
        $fechaFin           = new Datetime();
        $tiempoTrans        = date_diff($fechaInicio, $fechaFin);
        $querySTR           = "INSERT INTO dbo.inAXHistory (usuario,evento,pantalla,documentoCreado,fechaInicio,fechaFin,tiempoTrans,tiempoTransTime,idPOV)
                                VALUES('".$usuario."','".$evento."','".$pantalla."','".$documentoCreado."','".$fechaInicio->format('Y-m-d H:i:s')."',GETDATE(),'".$tiempoTrans->format('%d Dias %H Horas %i Minutos %s Segundos')."','".$tiempoTrans->format('%H:%i:%s')."','".$idPOV."');";
        $query = $this->_adapter->prepare($querySTR);
        $query->execute();
    }catch(Exception $e){
        return $e->getMessage();
    }
    return true;
    }
    
    public function updateHistory($idPOV,$documento){
        try{
            $querySTR = "UPDATE inAXHistory SET documentoCreado = '".$documento."' WHERE idPOV = '".$idPOV."';";
            $query = $this->_adapter->prepare($querySTR);
            $query->execute();
        }catch(Exception $e){
            return $e->getMessage();
        }
    }

    public static function getPayModeInController(){
        $token = new Token();
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
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

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            $result = "cURL Error #:" . $err;
        } else {
            $result = json_decode($response);
        }

        return $result;
    }
}
