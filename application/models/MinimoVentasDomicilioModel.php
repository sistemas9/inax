<?php
	error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
	ini_set('display_errors', '1'); 
	set_time_limit(0);
	date_default_timezone_set('America/Chihuahua');

	class Application_Model_MinimoVentasDomicilioModel {
		public static function getDataMinimos(){
			(CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();
  			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  			$queryMinimos = "SELECT nombreSucursal as '0',montoMinimoEfectivo as '1',montoMinimoTarjeta as '2',status as '3', id_minimo as '4' FROM AYT_MontosMinimosSucursal;";
  			$query = $conn->query($queryMinimos);
		    $query->execute();
		    $resultMinimos = $query->fetchAll(PDO::FETCH_ASSOC);
		    return $resultMinimos;
		}

		public static function updateMinimos($montoEfe,$montoTarj,$idMonto){
			(CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();
  			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  			$queryMinimos = "UPDATE AYT_MontosMinimosSucursal SET montoMinimoEfectivo = $montoEfe, montoMinimoTarjeta = $montoTarj WHERE id_minimo = $idMonto;";
  			$query = $conn->query($queryMinimos);
		    $query->execute();
		    $resultMinimos = $query->rowCount();
		    return $resultMinimos;
		}

		public static function updateStatus($idMonto,$status){
			(CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();
  			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  			$queryMinimos = "UPDATE AYT_MontosMinimosSucursal SET status = $status WHERE id_minimo = $idMonto;";
  			$query = $conn->query($queryMinimos);
		    $query->execute();
		    $resultMinimos = $query->rowCount();
		    return $resultMinimos;
		}

		public static function getMininosVentasData($sitio){
			(CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();
  			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  			$queryMinimos = "SELECT sucursal,montoMinimoEfectivo,montoMinimoTarjeta FROM AYT_MontosMinimosSucursal WHERE status = 1 AND sucursal = '$sitio';";
  			$query = $conn->query($queryMinimos);
		    $query->execute();
		    $resultMinimos = $query->fetchAll(PDO::FETCH_ASSOC);
		    if (empty($resultMinimos)){
		    	return 'SinResultado';
		    }
		    return array('sucursal' => $resultMinimos[0]['sucursal'], 'montoMinimoEfectivo' => $resultMinimos[0]['montoMinimoEfectivo'], 'montoMinimoTarjeta' => $resultMinimos[0]['montoMinimoTarjeta'] );
		}
	}
?>