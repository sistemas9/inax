<?php
 ini_set("memory_limit", "1024M");
 require_once (LIBRARY_PATH.'/includes/dompdf/dompdf_config.inc.php');
 require_once (LIBRARY_PATH.'/includes/code128.php');
 setlocale(LC_MONETARY, 'es_MX');

	class ListadePreciosPreController extends Zend_Controller_Inax{


		public function indexAction(){
			$this->view->cliente 	= json_encode($this->getClients());
			$this->view->art 		= json_encode(Application_Model_InicioModel::getItems($sucu));
      $this->view->art2 		= json_encode(Application_Model_InicioModel::getItemsCommon());
      $this->view->listaGrupo = json_encode($this->getGrupo());
      $this->view->listaFam   = json_encode($this->getfamilias());
            // $this->view->listaRelacion   = json_encode($this->relationgrupfamAction($grup));
		}

    public function imprimepdfAction(){     
         $arrayItem = $_POST['dataarray'];
         $directorio = __DIR__."/../assets/img";
         // $vendedor    =   $model->getVendedor($idDirec);
                (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                   
                    $query = $conn->query("select max(idIdentificador) as lastid from listaPrecios");
                    $query->execute();
                    $identificador = $query->fetchAll(PDO::FETCH_ASSOC);
                    $idActual = $identificador[0]['lastid'] + 1;

                foreach ($_POST['dataarray'] as $items){
                    $query = $conn->prepare("INSERT INTO listaPrecios(codigo,descripcion,unidad,precio,moneda,idIdentificador,concepto) 
                    VALUES('".$items['codigo']."','".$items['descrip']."','".$items['unidad']."','".$items['precio']."','".$items['moneda']."','".$idActual."','".$items['cliente']."');");
                    $query->execute();
                } 
                  print_r(json_encode($idActual));
                  exit();           
          }
 

		public function minimoventaAction(){
			$item 		 	= filter_input(INPUT_POST, 'item');
			$minimoVenta 	= Application_Model_InicioModel::getMinimoVenta($item);
			$resultMinimo 	= $minimoVenta;
			if (empty($minimoVenta)){
				$resultMinimo = array('ITEMID' => $item,'INVENTSITEID' => 'CHIH','MULTIPLEQTY' => '1');
			}
			print_r(json_encode($resultMinimo));
			exit();
		}


		  public function datosprecioAction(){
      $articulo      = filter_input(INPUT_POST, 'articulo');
      $linedisc      = filter_input(INPUT_POST, 'linedisc');
      $conjuntoprecios = filter_input(INPUT_POST, 'conjuntoprecios');
      $cantidad      = filter_input(INPUT_POST, 'cantidad');
      $data        = Application_Model_BuscarPreciosModel::getPriceData($articulo,$cantidad,$linedisc,$conjuntoprecios);
      print_r(json_encode($data));
      exit();
    }


       public function postprice($idCliente,$idArticulo,$cantidad,$fecha,$moneda,$idInvent,$locationIn,$company){
                   

                 $jsonField ="{ 
                                      'LanguageId':'es-MX'
                                      ,'CustAccount':'".$idCliente."'
                                      ,'ItemId' : '".$idArticulo."'
                                      ,'amountQty' : '".$cantidad."'
                                      ,'transDate': '".$fecha."'
                                      ,'currencyCode' : '".$moneda."'
                                      ,'InventSiteId' : '".$idInvent."'
                                      ,'InventLocationId' : '".$locationIn."'
                                      ,'company' : '".$company."'
                                      ,'PercentCharges' : 0
                                    }";
                      

               $token = new Token();
                curl_setopt_array(CURL1, array(
                CURLOPT_URL => "https://".DYNAMICS365."/api/services/STF_INAX/STF_ItemSalesPrice/getSalesPrice",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $jsonField,///arreglo json
                CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$token->getToken()[0]->Token."",
            "content-type: application/json"
          ),
        ));
        $response = curl_exec(CURL1);
        $error = curl_error(CURL1);
         if ($error) {
          $result = "cURL Error #:" . $error;
        } else {
          $result = $response;
        }
        return $result;
        }


        
        public function relationgrupfamAction(){
                  $grup = $_POST['catalogo'];
                   $conn = new DB_ConexionExport();
                   $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                   $query = $conn->query("SELECT DISTINCT PRODUCTCATEGORYNAME AS FAMILIA 
                                FROM EcoResProductCategoryAssignmentStaging T1       
                                WHERE T1.PRODUCTCATEGORYHIERARCHYNAME = '".$grup."'
                                ORDER BY T1.PRODUCTCATEGORYNAME;");
                    $query->execute();

               $resultadoSinUTF = $query->fetchAll(PDO::FETCH_ASSOC);

               $resultRelacion = [];
                 foreach ($resultadoSinUTF as $Data){
                          array_push($resultRelacion, array('ClaveRelacion' => utf8_encode($Data['FAMILIA'])                     
                      ));
            }
           print_r(json_encode($resultRelacion));
           exit();  
        }

        public function listaarticulosAction(){
          $familias = $_POST['familia'];
          $cliente = $_POST['cliente'];
          //separa la matriz por dos opciones
            // print_r($familia);
            //   exit();  
          if(is_array($familias)){
            $familia = implode("','", $familias);
          }else{

            $familia = $familias;
          }
                          //consulta en SQL donde regresa los articulos a las cuales pertenece la familia
                   $conn = new DB_ConexionExport();
                   $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                   $query = $conn->query("SELECT DISTINCT T2.ItemNumber AS 'CLAVE',
                             IIF(( T3.DESCRIPTION = '' OR T3.DESCRIPTION IS NULL ),T2.ProductSearchName,T3.DESCRIPTION) AS 'DESCRIPCION',
                             T2.SALESUNITSYMBOL AS 'UNIDAD DE VENTA' FROM EcoResReleasedProductV2Staging T2
                          LEFT JOIN AYT_InventTableStaging T3 ON T3.ITEMID  = T2.ItemNumber
                          LEFT JOIN EcoResProductCategoryAssignmentStaging T4 ON T4.PRODUCTNUMBER = T2.ItemNumber
                          WHERE T4.PRODUCTCATEGORYNAME IN ( '".$familia."' ) AND  ProductSearchName NOT LIKE '%LIQUI%' AND ProductSearchName
                   NOT LIKE '%S/PED%' AND ProductSearchName NOT LIKE '%DESCONTINUADO%';");
                     //   print_r($query);
                     // exit(); 
                    $query->execute();


                   $jsonField ="{ 
                                'CustomerAccount' : '".$cliente."',
                                       }";
                                    
               $token = new Token();
                curl_setopt_array(CURL1, array(
                CURLOPT_URL => "https://".DYNAMICS365."/Data/CustomersV3?%24select=OrganizationNumber&%24filter=CustomerAccount%20eq%20'".$cliente."'",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_POSTFIELDS => $jsonField,///arreglo json
                CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$token->getToken()[0]->Token."",
            "content-type: application/json"
          ),
        ));
        $response = curl_exec(CURL1);      
        $response = json_decode($response);
        // $response->value[0]->OrganizationNumber;
        $error = curl_error(CURL1);
         if ($error) {
          $result = "cURL Error #:" . $error;
        } else {
          $result = $response->value[0]->OrganizationNumber;
        }                      
      
               $resultadoSinUTF = $query->fetchAll(PDO::FETCH_ASSOC);
               $resultlistaArticulos = [];
                 foreach ($resultadoSinUTF as $Data){
                      $idCliente = $cliente;
                      $idArticulo = $Data['CLAVE'];
                      $cantidad = 1;
                      $fecha = new DateTime();
                      $fecha2 = $fecha->format('m/d/yy');
                      $moneda ='MXN';
                      $idInvent = 'CHIH';
                      $locationIn = 'CHICONS';
                      $company = 'atp';
                      $organizacionNumber = $result;
                      $precio = $this->postprice($idCliente,$idArticulo,$cantidad,$fecha2,$moneda,$idInvent,$locationIn,$company);
                      $precio = number_format($precio,2);
                          array_push($resultlistaArticulos, array('idArticulo' =>$Data['CLAVE'],
                                                  'Nombre'=> utf8_encode($Data['DESCRIPCION']),
                                                  'unidad' => $Data['UNIDAD DE VENTA'], 
                                                  'precio' => $precio,
                                                  'moneda' => $moneda,
                                                  'organizacionNum' =>$organizacionNumber,                                      
                                            ));
            }
           print_r(json_encode($resultlistaArticulos));
           exit();  
        }

      //Muestra  la lista que le pertenece a cada familia
      public function getfamilias(){
        $conn = new DB_ConexionExport();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = $conn->query("SELECT DISTINCT PRODUCTCATEGORYNAME AS FAMILIA
                                FROM EcoResProductCategoryAssignmentStaging T1
                                WHERE T1.PRODUCTCATEGORYHIERARCHYNAME = '".$grup."'         
                                ORDER BY T1.PRODUCTCATEGORYNAME;");
        $query->execute();

        $resultadoSinUTF = $query->fetchAll(PDO::FETCH_ASSOC);
         //print_r($resultadoSinUTF);
         //exit();
        $resultFamilia = [];
        foreach ($resultadoSinUTF as $Data){
                          array_push($resultFamilia, array('ClaveFamilia' => utf8_encode($Data['FAMILIA'])                      
                      ));
            }
           // print_r(json_encode($resultFamilia));
           // exit(); 
          return $resultFamilia;
      }


       public function clavefamiliasAction(){
        $conn = new DB_ConexionExport();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = $conn->query("SELECT DISTINCT PRODUCTCATEGORYNAME AS FAMILIA
                                FROM EcoResProductCategoryAssignmentStaging T1          
                                ORDER BY T1.PRODUCTCATEGORYNAME;");
        $query->execute();

        $resultadoSinUTF = $query->fetchAll(PDO::FETCH_ASSOC);
         //print_r($resultadoSinUTF);
         //exit();
        $resultFamilia = [];
        foreach ($resultadoSinUTF as $Data){
                          array_push($resultFamilia, array('ClaveFamilia' => utf8_encode($Data['FAMILIA'])                      
                      ));
            }
           print_r(json_encode($resultFamilia));
           exit(); 
      }

                            //Muestra las categorias de todos de familias grupos etc...
      public function getGrupo(){
        $conn = new DB_ConexionExport();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = $conn->query("SELECT DISTINCT PRODUCTCATEGORYHIERARCHYNAME AS CATEGORIAS
                                FROM EcoResProductCategoryAssignmentStaging T1      
                                WHERE T1.PRODUCTCATEGORYHIERARCHYNAME IN ('FAMILIA','GRUPO');");
        $query->execute();

        $resultadoSinUTF = $query->fetchAll(PDO::FETCH_ASSOC);
        $resultGrupo = [];
        foreach ($resultadoSinUTF as $Data){
                          array_push($resultGrupo, array('claveGrupo' => utf8_encode($Data['CATEGORIAS'])                      
                      ));
            }
           return $resultGrupo; 
          }

                //SE trae la lista de todas las categorias donde viven (grupos y familias)
      public function clavegrupoAction(){
                      $conn = new DB_ConexionExport();
                     $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $query = $conn->query("SELECT DISTINCT PRODUCTCATEGORYHIERARCHYNAME AS GRUPO
                                FROM EcoResProductCategoryAssignmentStaging T1      
                                ORDER BY T1.PRODUCTCATEGORYHIERARCHYNAME;");
        $query->execute();

        $resultadoSinUTF = $query->fetchAll(PDO::FETCH_ASSOC);
        $resultGrupo = [];
        foreach ($resultadoSinUTF as $Data){
                          array_push($resultGrupo, array('claveGrupo' => utf8_encode($Data['GRUPO'])                      
                      ));
            }
          print_r(json_encode($resultGrupo));
           exit(); 
      }
    

		public function getClients(){
			$conn = new DB_ConexionExport();
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $query = $conn->prepare("SELECT OrganizationName,CustomerAccount,LineDiscountCode,DiscountPriceGroupId FROM [aytExport].[dbo].[CustCustomerV3Staging] WHERE DATAAREAID ='ATP'");
            $query->execute();
            $resultadoSinUTF = $query->fetchAll(PDO::FETCH_ASSOC);

			$resultClientes = [];
        	foreach($resultadoSinUTF as $Data){
                array_push($resultClientes, array('ClaveCliente' =>$Data['CustomerAccount'],
                                                  'Nombre'=> utf8_encode($Data['OrganizationName']),
                                                  'DescuentoLinea' => $Data['LineDiscountCode'],
                                                  'ConjuntoPrecios' => $Data['DiscountPriceGroupId']
                                            ));
            }
            return $resultClientes;
		}
  }
?>