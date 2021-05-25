<?php

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
ini_set('display_errors', '1'); 
set_time_limit(0);
date_default_timezone_set('America/Chihuahua');

class Application_Model_CodigospostalesModel {
	public static function loadZipCodeData(){
		(CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();      
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$queryZipCodes = "SELECT	codigoPostal,
									REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(asentamiento,'á','a'),'é','e'),'í','i'),'ó','o'),'ú','u') AS asentamiento,
									tipoAsentamiento,
									municipio,
									ciudad,
									status,
									idPostal
							FROM AYT_CodigosPostalesInAX;";
		$query = $conn->prepare($queryZipCodes);
		$query->execute();
		$result = $query->fetchAll(PDO::FETCH_ASSOC);

		$body = 'nodata';
		$switch = '';
		if (!empty($result)){
			$checked = '';
			foreach ($result as $Data){
				$checked = ($Data['status'] == 1)?'checked':'';
				$status  = ($Data['status'] == 1)?'0':'1';
				$switch .= '<div class="row">';
				$switch  = '	<div class="switch col l6 m6 s6">';
				$switch .= '		<label>';
				$switch .= '			<span style="font-size:9px;">No disp.</span>';
				$switch .= '			<input type="checkbox" '.$checked.' onclick="javascript:cambiarStatus(\''.$Data['codigoPostal'].'\',\''.$Data['asentamiento'].'\',\''.$status.'\')">';
				$switch .= '			<span class="lever"></span>';
				$switch .= '			<span style="font-size:9px;">Disp.</span>';
				$switch .= '		</label>';
				$switch .= '	</div>';
				$switch .= '	<div class="col l6 m6 s6">';
				$switch .= '		<i class="material-icons dp48" style="cursor:pointer;" onclick="javascript:actualizarCodigo(\''.$Data['idPostal'].'\',\''.$Data['asentamiento'].'\',\''.$Data['tipoAsentamiento'].'\',\''.$Data['codigoPostal'].'\',\''.$Data['municipio'].'\',\''.$Data['ciudad'].'\')">edit</i>&nbsp;Editar';
				$switch .= '	</div>';
				$switch .= '</div>';
				$dataZip[] = array(
					0 => $Data['codigoPostal'],
					1 => $Data['asentamiento'],
					2 => $Data['tipoAsentamiento'],
					3 => $Data['municipio'],
					4 => $Data['ciudad'],
					5 => $switch
				);
			}
		}
		$dataZipCompleto = array('data'=>$dataZip);
		return $dataZipCompleto;
	}

	public static function updateZipCodeData($zipcode,$asentamiento,$status){
		(CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();      
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$queryZipCodes = "UPDATE AYT_CodigosPostalesInAX SET status = '$status' WHERE asentamiento = '$asentamiento' AND codigoPostal = '$zipcode'; ";
		$query = $conn->prepare($queryZipCodes);
		$query->execute();
		$result = $query->rowCount();

		if ($result > 0){ print_r(json_encode('exito')); }else{print_r(json_encode('fallo')); } ;
		exit();
	}

	public static function updateZipCodeDataAll($codigoPostal,$colonia,$tipoCol,$municipio,$ciudad,$idCodigo){
		(CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();      
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$queryZipCodes = "UPDATE AYT_CodigosPostalesInAX SET codigoPostal = '$codigoPostal', asentamiento = '$colonia', tipoAsentamiento = '$tipoCol', municipio = '$municipio', ciudad = '$ciudad' WHERE idPostal = $idCodigo AND codigoPostal = '$codigoPostal'; ";
		$query = $conn->prepare($queryZipCodes);
		$query->execute();
		$result = $query->rowCount();

		if ($result > 0){ return 'exito'; }else{ return 'fallo'; } ;
	}

	public static function saveZipCodeData($codigoPostal,$colonia,$tipoCol,$municipio,$ciudad){
		(CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();      
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$queryZipCodes = "INSERT INTO AYT_CodigosPostalesInAX VALUES('$colonia','$tipoCol','$codigoPostal','$municipio','$ciudad','','1');";
		$query = $conn->prepare($queryZipCodes);
		$query->execute();
		$result = $query->rowCount();

		if ($result > 0){ return 'exito'; }else{ return 'fallo'; };
	}
}
