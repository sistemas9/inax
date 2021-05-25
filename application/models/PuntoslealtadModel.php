<?php
class Application_Model_PuntoslealtadModel {
	public static function getClientes(){
        $conn = new DB_Conexion();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = $conn->prepare(" SELECT T0.*,ISNULL(T1.puntosAvance,0) AS puntosAvance 
                FROM clientesInax  T0
                LEFT JOIN PuntosAvanceLealtad T1 ON (T0.CustomerAccount = T1.CodigoCliente);");
        $query->execute();
        $resultadoSinUTF = $query->fetchAll(PDO::FETCH_ASSOC);
        return $resultadoSinUTF;
    }

    public static function getArticulosEntity(){
        $conn = new DB_Conexion();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = $conn->prepare("   SELECT ItemNumber,Descripcion,PuntosAvance 
                                    FROM ArticulosPuntosAvance
                                    ORDER BY ItemNumber;");
        $query->execute();
        $resultadoSinUTF = $query->fetchAll(PDO::FETCH_ASSOC);
        return $resultadoSinUTF;
    }

    public static function setHeader($params){
        $token = new Token();
        $sucursales = array(
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
                 'VEQP' =>   "UN_00999", 
                  );
        curl_setopt_array(CURL1, array(
            CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesQuotationHeaders",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{ 'CurrencyCode': '".$params['CurrencyCode']."'
                                    ,'LanguageId':'".$params['LanguageId']."'
                                    ,'dataAreaId': '".$params['dataAreaId']."'
                                    ,'DefaultShippingSiteId': '".$params['DefaultShippingSiteId']."'
                                    ,'RequestingCustomerAccountNumber':'".$params['RequestingCustomerAccountNumber']."'
                                    ,'QuotationTakerPersonnelNumber' : '".$params['QuotationTakerPersonnelNumber']."'
                                    ,'QuotationResponsiblePersonnelNumber' : '".$params['QuotationResponsiblePersonnelNumber']."'
                                    ,'SalesOrderOriginCode' : '".$params['SalesOrderOriginCode']."'
                                    ,'SalesTaxGroupCode': '".$params['SalesTaxGroupCode']."'
                                    ,'DeliveryModeCode' : '".$params['DeliveryModeCode']."'
                                    ,'CustomersReference' : '".$params['CustomersReference']."'
                                    ,'CustomerPaymentMethodName' : '".$params['CustomerPaymentMethodName']."'
                                  }",
            CURLOPT_HTTPHEADER => array(
              "authorization: Bearer ".$token->getToken()[0]->Token."",
              "content-type: application/json"
            ),
        ));

        $response = curl_exec(CURL1);
        $err = curl_error(CURL1);
        $response2=json_decode($response);

        curl_setopt_array(CURL1, array(
            CURLOPT_URL => "https://".DYNAMICS365."/api/services/STF_INAX/STF_Cotizacion/SetHeaderDefaultDimension",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{
                '_dimensionName': 'BusinessUnits'
                ,'_dimensionValue': '".$sucursales[$response2->DefaultShippingSiteId]."'
                ,'_quotationId': '".$response2->SalesQuotationNumber."'
                ,'_dataAreaId': 'ATP'}",
             CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$token->getToken()[0]->Token."",
             "content-type: application/json"
            ),
        ));
        $response55 = curl_exec(CURL1);
        $err55 = curl_error(CURL1);

        if (isset($response2->SalesQuotationNumber)){
            $result = array('CTZN' => $response2->SalesQuotationNumber,'estatus' => 'Exito','error' => $err,'error2' => $err55);
        }else{
            if ($err || $err55){
                $result = array('CTZN' => $response2,'estatus' => 'Fallo','error' => $err, 'error2' => $err55);
            }else{
                $result = array('CTZN' => $response2,'estatus' => 'Fallo','error' => $err, 'error2' => $err55);
            }
        }

        return $result;
    }

    public static function setLines($params){
        $token = new Token();
        curl_setopt_array(CURL1, array(
            CURLOPT_URL => "https://".DYNAMICS365."/Data/SalesQuotationLines",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{'SalesQuotationNumber' : '".$params['SalesQuotationNumber']."'
                                    ,'dataAreaId': '".$params['dataAreaId']."'
                                    ,'ItemNumber' : '".$params['ItemNumber']."'
                                    ,'RequestedSalesQuantity' : ".$params['RequestedSalesQuantity']."
                                    ,'ShippingWarehouseId' : '".$params['ShippingWarehouseId']."'
                                    ,'FixedPriceCharges': ".$params['FixedPriceCharges']."
                                    ,'SalesTaxGroupCode': '".$params['SalesTaxGroupCode']."'
                                    ,'STF_Category' : '".$params['STF_Category']."'
                                  }",
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer ".$token->getToken()[0]->Token."",
                "content-type: application/json"
            ),
        ));

        $response = curl_exec(CURL1);
        $err = curl_error(CURL1);

        if ($err){
            $result = "cURL Error #:".$err;
        }else{
            $resultado = json_decode($response);
            $result = array('SalesQuotationNumber' => $resultado->SalesQuotationNumber,'dataAreaId' => $resultado->dataAreaId,'InventoryLotId' => $resultado->InventoryLotId);
        }
        return $result;
    }

    public static function updatePuntos($cliente,$puntos){
        $conn   = new DB_Conexion();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query  = $conn->prepare("UPDATE PuntosAvanceLealtad SET puntosAvance = ".$puntos." WHERE codigoCliente = '".$cliente."';");
        $query->execute();
        $result = $query->rowCount();

        if ($result > 0){
            $resultado = array('estatus' => 'Exito', 'msg' => 'Cliente actualizado con exito.');
        }else{
            $resultado = array('estatus' => 'Fallo', 'msg' => 'Hubo un problema al actualizar el cliente.');
        }
        return $resultado;
    }
}