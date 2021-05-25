<?php
	error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
	ini_set('display_errors', '1'); 
	set_time_limit(0);
	date_default_timezone_set('America/Chihuahua');

	class Application_Model_ZPLTemplateModel {
		public static function setZPLData($dataZPL,$docId){
	        $zplTemplate = file_get_contents('../public/ZPLTemplate.tpl');
	        $zplTplTemp  = str_replace('{OvCompleto}', $dataZPL['ovCompleto'], $zplTemplate);
	        $zplTplTemp  = str_replace('{RFCAYT}', $dataZPL['RFCCompany'], $zplTplTemp);
	        $zplTplTemp  = str_replace('{AddressAYT}', $dataZPL['calleCompany'], $zplTplTemp);
	        $zplTplTemp  = str_replace('{CountyAYT}', $dataZPL['colCompany'], $zplTplTemp);
	        $zplTplTemp  = str_replace('{CityAYT}', $dataZPL['estadoCompany'], $zplTplTemp);
	        $zplTplTemp  = str_replace('{PhoneAYT}', $dataZPL['telCompany'], $zplTplTemp);
	        $zplTplTemp  = str_replace('{UserSell}', $dataZPL['usuarioVenta'], $zplTplTemp);
	        $zplTplTemp  = str_replace('{SellTime}', $dataZPL['creacionFecVenta'], $zplTplTemp);
	        $zplTplTemp  = str_replace('{AmountSell}', $dataZPL['montoVenta'], $zplTplTemp);
	        $zplTplTemp  = str_replace('{MaterialResistance}', $dataZPL['precaucion'], $zplTplTemp);
	        $zplTplTemp  = str_replace('{EmailClient}', $dataZPL['emailCliente'], $zplTplTemp);
	        $zplTplTemp  = str_replace('{Comentarios}', $dataZPL['comentarios'], $zplTplTemp);
	        $zplTplTemp  = str_replace('{RFCClient}', $dataZPL['RFCCliente'], $zplTplTemp);
	        $zplTplTemp  = str_replace('{AddressClient}', $dataZPL['calleCliente'], $zplTplTemp);
	        $zplTplTemp  = str_replace('{CountyClient}', $dataZPL['colCliente'], $zplTplTemp);
	        $zplTplTemp  = str_replace('{CityClient}', $dataZPL['estadoCliente'], $zplTplTemp);
	        $zplTplTemp  = str_replace('{PhoneClient}', $dataZPL['telefonoCliente'], $zplTplTemp);
	        $zplTplTemp  = str_replace('{Service}', $dataZPL['paqueteriaVenta'], $zplTplTemp);
	        $zplTplTemp  = str_replace('{Type}', $dataZPL['seguroVenta'], $zplTplTemp);

	        // file_put_contents('../public/'.$docId.'.zpl', $zplTplTemp);//local
	        file_put_contents('/var/www/html/paqueteriasOv/'.$docId.'.zpl', $zplTplTemp);//nube
	        return true;
	    }
	}
?>