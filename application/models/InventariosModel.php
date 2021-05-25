<?php

class Application_Model_inventariosModel{
    public $db;
    public $_adapter;

    public function getInventorySitesOnHand(){
        $curl = curl_init();
        $token = new Token();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://".DYNAMICS365."/data/WarehousesOnHand?%24filter=(InventoryWarehouseId%20ne%20''%20)%20and%20dataAreaId%20eq%20'".COMPANY."'&%24select=ItemNumber%2COnHandQuantity%2CInventorySiteId%2CInventoryWarehouseId&%24top=9999",
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

        print_r($response);
        exit();

        $err = curl_error($curl);

        curl_close($curl);
        if ($err) {
          return "cURL Error #:" . $err;
        } else {
          return json_decode($response);
        }
    }
}