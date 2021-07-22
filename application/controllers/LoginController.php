<?php
// server should keep session data for AT LEAST 1 hour
ini_set('session.gc_maxlifetime', 86400);
// each client should remember their session id for EXACTLY 1 hour
session_set_cookie_params(86400);

class LoginController extends Zend_Controller_Inax
{

    public $error;

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction(){
        // action body
        $this->_helper->layout()->disableLayout();
        $this->view->headTitle('Avance y Tecnología en Plásticos');
        $log = new Application_Model_Login();
        $history = new Application_Model_Userinfo();
        $this->view->error = 0;
        $config = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        $env = $config->getOption('app_redirect_url_login');
        if ($this->getRequest()->isPost() && (isset($_POST['code'])) ){
          $rawData = file_get_contents("php://input");
          $code = explode('&',$rawData);
          $code = explode('=',$code[0]);          
          $code = $code[1];
          $token = $this->getAccessToken($code);
          $userData = $this->getUserData($token);
          $result = $this->userAuthenticatedSSO($log,$userData);
          if ($result == 3){
            $log->loginKardex($_SESSION['userInax'],$_SESSION['nomuser']);
          }
          $this->view->ssoauthenticated = $result;
        }

        if ( (isset($_POST['userLogin']) && isset($_POST['userPassword'])) || ( (isset($_POST['state'])) && $_POST['state'] != 'logout' ) ) {
            $fechaIni = new Datetime();
            $parametros = array('usuario'            => $_POST['userLogin'],
                                'evento'             => 'Login',
                                'pantalla'           => 'Login',
                                'documentoCreado'    => 'N/A',
                                'fechaInicio'        => $fechaIni,
                                'idPOV'              => $_SESSION['idProcesoVenta']
                            );
            $history->saveHistory($parametros);
            if ($this->getRequest()->isPost()) {
                $user = $_POST['userLogin'];
                $password = $_POST['userPassword'];
                //$result = $log->authenticate($user, $password);
                switch ($result) {
                    case 1:
                        $this->view->error = 1;
                        print_r($this->view->error);
                        // return $this->view->error;
                        break;
                    case 3:
                        // todo bien
                        $log->loginKardex($_SESSION['userInax'],$_SESSION['nomuser']);
                        print_r($result);
                        // return $this->_helper->redirector->gotoUrl('../public/');
                        break;
                    case 4:
                        $this->view->error = 4;
                        print_r($this->view->error);
                        // return $this->view->error;
                        break;
                    default:
                        $this->view->error = 1;
                        print_r($this->view->error);
                        // return $this->view->error;
                        break;
                }
            }
        } else {
            $this->view->error = 0;
            $redirect = false;

            if ( !(isset($_POST['code'])) && $_GET['state'] == 'logout' ){
                // destroy session
                $_SESSION = array();
                unset($_SESSION['userInax'], $_SESSION['access']);
                Zend_Session::destroy();
                $redirect = true;
            }
            if ($redirect){
                //localhost:8989%2FinaxGitTest%2Finax%2Fpublic%2Flogin

                $redirectUrl = 'https://login.microsoftonline.com/97ef83be-75fe-4de2-93cd-21df978b24c1/oauth2/authorize?client_id=d8e2e311-b455-4e00-be57-4006ae7d9adc&redirect_uri='.urlencode($env).'&response_type=code&scope=openid%20profile&response_mode=form_post&nonce=yes&state=login&x-client-SKU=ID_NETSTANDARD2_0&x-client-ver=5.5.0.0&sso_reload=true';
              $this->redirect($redirectUrl);
            }
            return $this->view->error;
        }
    }

    public function loginAction(){
        // action body
    }
    public function getClientsAction() {
        $this->_helper->layout->disableLayout();
        $model= new Application_Model_InicioModel();
        echo $model->getClients('');
        exit();
    }

    //////////////////////////////funciones de sso//////////////////////////
    public function getAccessToken($code){
        $config = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        $env = $config->getOption('app_redirect_url_login');
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://login.microsoftonline.com/avanceytec.onmicrosoft.com/oauth2/v2.0/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "grant_type=authorization_code&client_secret=6i2lBY~~Ay5818lVIQhfx6XtTp.o7-mZF0&client_id=d8e2e311-b455-4e00-be57-4006ae7d9adc&scope=https%3A%2F%2Fgraph.microsoft.com%2F.default&code=".$code."&redirect_uri=".urlencode($env),
            CURLOPT_COOKIE => "buid=0.ASwAvoPvl_514k2TzSHfl4skwRHj4thVtABOvldABq59mtwsAAA.AQABAAEAAAB2UyzwtQEKR7-rWbgdcBZI5HLcgZvvlyoRj0xfNKbomJQ-GvL8FSZ7dmvwYUFj9yO6GIdWDRN6GADbcZe2KGJJSAUymVrEY7SI74LqI_MbAStT2rjbwxH8XorBQhqlL0MgAA; esctx=AQABAAAAAAC5una0EUFgTIF8ElaxtWjTNXpOWurGzgj0ub7jSiqDGBWUPAjaJTpWG70E2FkaMlTaeWcg4l03j_S424PvzeXnZsKh4z9loqST0cKYq0ydiBxjuuqUjkIXp2Q5OJ67kSfvkbfcRGHYFS9c-bLIOm_fwr3L_aRplozxmP4OtxEVbQSUT68q5k4b6cN6iImvdfYgAA; x-ms-gateway-slice=estsfd; stsservicecookie=ests; ExternalIdpStateHash=iBVYjaTNQwGHlIx-N-WsTIn_g47G3PRuq2Pfcc1BWww; fpc=AhJeOq5-OPlCkaHc0TfMlOZ9XFtOAwAAAPFyGdcOAAAAvuGMQQIAAADwcxnXDgAAAA",
            CURLOPT_HTTPHEADER => [
              "accept: application/json",
              "content-type: application/x-www-form-urlencoded",
              "prefer: return=representation"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
        return "cURL Error #:" . $err;
        } else {
        $response = json_decode($response);
        $response = (object) $response;
        }

        return $response->access_token;
    }

    public function getUserData($token){
      $curl = curl_init();

      curl_setopt_array($curl, [
        CURLOPT_URL => "https://graph.microsoft.com/v1.0/me",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => [
          "accept: application/json",
          "authorization: Bearer ".$token
        ],
      ]);

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);

      if ($err) {
        return "cURL Error #:" . $err;
      } else {
        $response = json_decode($response);
        $response = (object) $response;
      }

      return $response;
    }

    public function userAuthenticatedSSO($model,$userData){
      $user = explode('@',$userData->userPrincipalName);
      $user = $user[0];
      $cuenta=Application_Model_Login::getCuentaName($user);         
      $_SESSION['cuentaMostrador']=$cuenta[0][0];
      $_SESSION['email'] = $userData->mail;
      $_SESSION['userInax'] = $user;
      //$_SESSION['pasw'] = $password;
      $_SESSION['nomuser'] = $userData->userPrincipalName;
      //$_SESSION['depto'] = $data2[1];
      $_SESSION['fullname'] = $userData->userPrincipalName;
      $_SESSION['sucursal'] = strtoupper($userData->officeLocation);
      $company = "ATP";
      $_SESSION['company_name'] = "AVANCE Y TECNOLOGIA EN PLASTICOS";
      $_SESSION['company_rfc'] = "ATP-880818-2P4";
      $_SESSION['company'] = $company;
      
      $model->getSucursales($company);
      $model->getUsuarios($user);
      
      $model->getTP();
      $model->getPermisions();
      $flag=false;
      $n=4;
      $model->userExist();
      foreach ($_SESSION['factura'] as $key => $value) {
          if($value==3){
              $n=3;
              $flag=true;
          }
      }
      if ($flag) {
          return $n;
      }else{
        return 4;
      }
    }
    ////////////////////////////////////////////////////////////////////////
}
