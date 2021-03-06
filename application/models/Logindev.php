<?php

/**
 * 
 * @see Inicio login
 * @uses autenticate() verifica si el usuario existe en Ldap para brindar acceso a Inax con las credenciales de active Directory. 
 */

class Application_Model_Logindev {
    public function loginKardex($usuario,$nombre) {
        $modelo = new Application_Model_Userinfo();
        
        //$modelo->setLoginKardex($usuario, $nombre);
    }
    function getSucursales($company) {
        /* carga los sitios para la impresion de la etiqueta*///////////////////////////////////////
        $datosinicio = new Application_Model_Userinfo();
        $sitio = $datosinicio->Sitios($company);
        $optSitio = '<option value="">Selecciona...</option>';
        if ($sitio[0] != 'NoResults') {
            foreach ($sitio as $Data) {
                $optSitio .= '<option value="' . $Data['SITEID'] . '">' . $Data['NAME'] . '</option>';
            }
        } else {
            $optSitio = '<option value="">Sin Sucursales</option>';
        }
        $_SESSION['sucursales'] = $optSitio;
        /////////////////////////////////////////////////////////////////////////////////////////
    }
    function getCuentaName($user) {
        $cuenta = new Application_Model_Userinfo();
        $cuenta2 = new Application_Model_InicioModel();
        $cuenta->_adapter->query(ANSI_NULLS);
        $cuenta->_adapter->query(ANSI_WARNINGS);        
        //$_SESSION['diarioCuentasPago']=$cuenta->getCuentasPago();
        $cuentas = $cuenta2->getCuentaContrapartida();
        $cuentasBancos = $cuenta2->getCuentaContrapartidaLinea(' ');
        $_SESSION['diarioCuentasPago']=$cuentas['JournalName']->value;
        return $cuenta->getCuentaPagoMostrador($user);
    }
    function getUsuarios($user) {
        $datosinicio = new Application_Model_Userinfo();
        $_SESSION['usuarios'] = $datosinicio->usuarios($user);
    }
    function getTP(){
        $model= new Application_Model_InicioModel();
        $_SESSION['tipoC']=$model->getTipoCambio();
    }
    function getPermisions() {
        $model = new Application_Model_Userinfo();
        $_SESSION['factura']=$model->getPermissions();
    }
    function userExist(){
        $model = new Application_Model_Userinfo();
        $model->userExist();
    }


    public function authenticate($user, $password){
        $company="";
        //$ldap_host = "DC01";
         $ldap_host = "187.141.228.93";
        $ldap_dn = "DC=atp,DC=local";
        $ldap_usr_dom = "@atp.local";
        $ldap = ldap_connect($ldap_host);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0) or die('Unable to set LDAP opt referrals');
        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3) or die('Unable to set LDAP           protocol     version');
        $user = explode('@', $user);
        $oser = $user[1];
        $user = $user[0];
        $bind = ldap_bind($ldap, $user . $ldap_usr_dom, $password);
        if ($bind) {        
            // valid check presence in groups
            /* */
            $filter = "(sAMAccountName=" . $oser . ")";
            $result = ldap_search($ldap, $ldap_dn, $filter) or exit("Unable to search LDAP server");
            $entries = ldap_get_entries($ldap, $result);
            ldap_unbind($ldap);
            $data = explode(',', $entries[0]['dn']);
            $data2 = explode('=', $data[1]);
            $fullName = explode('=', $data[0]);
            $cuenta=$this->getCuentaName($oser);
            $_SESSION['cuentaMostrador']=$cuenta[0][0];
            $_SESSION['email'] = $entries[0]["mail"][0];
            $_SESSION['userInax'] = $oser;
            $_SESSION['pasw']=$password;
            $_SESSION['nomuser'] = $fullName[1];
            $_SESSION['depto'] = $data2[1];
            $_SESSION['fullname'] = $fullName[1];
            $_SESSION['sucursal'] = strtoupper($entries[0]['physicaldeliveryofficename'][0]);
            if($entries[0]['description'][0]=="Lideart" || $oser == 'sistemas07'){
                $company.="LIN";
                $_SESSION['company_name']="LIDEART INNOVACI??N S DE R.L DE C.V";
                $_SESSION['company_rfc']="LIN-170626-FQ2";
            }
            else{
                $company.="ATP";
                $_SESSION['company_name']="AVANCE Y TECNOLOGIA EN PLASTICOS";
                $_SESSION['company_rfc']="ATP-880818-2P4";
            }
            $_SESSION['company']=$company;
            $this->getSucursales($company);
            $this->getUsuarios($oser);
            $this->getTP();
            $this->getPermisions();
             $flag=false;
            $n=4;
            $this->userExist();
            foreach ($_SESSION['factura'] as $key => $value) {
                if($value==3){
                    $n=3;
                    $flag=true;
                }
            }
            if ($flag) {
                return $n;
            }
            else{
                return 4;
            }
        } else {
            return 1;
        }
    }
}

