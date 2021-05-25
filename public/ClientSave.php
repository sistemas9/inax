<?php
// print_r('expression');exit();
class ClientSave{
	public function guardaCliente($Name,$CustomerAccount){

		// ini_set('display_errors', 'On');
  //   error_reporting(E_ALL);

    $jsonObject = file_get_contents('clientes.json',true);   
    // $file = fopen("clientes.json", 'w');
    $json = json_decode($jsonObject);
    $Name;
    $CustomerAccount;
    $var = '{
        "@odata.etag":"Single",
        "OrganizationName":"'.$Name.'",
        "CustomerAccount":"'.$CustomerAccount.'"
    }';
    array_push($json->value, json_decode($var));
    // print_r($json);

    file_put_contents('clientes.json', json_encode($json),LOCK_EX);
    // fwrite($file, json_encode($json));
    // print_r($file);
    // fclose($file);
    // $jsonObject = file_get_contents('clientes.json',true);   
    // print_r($jsonObject);exit();
		// $token = file_get_contents('http://ayt-inax.eastus.cloudapp.azure.com/inax/library/includes/tokenClass/Token.json',true);
		// return json_decode($token);

	}
}