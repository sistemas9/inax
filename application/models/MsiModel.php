<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
ini_set('display_errors', '1'); 
set_time_limit(0);
date_default_timezone_set('America/Chihuahua');

class Application_Model_MsiModel {
	public static function LoadMsiData(){
		(CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = "SELECT *,( valor_porc* ((iva/100)+1) ) AS cargoMSI FROM AYT_CargoTarjetaCredito;";
        $resultMSIStatement = $conn->query($query);
        $resultMSIStatement->execute();
        $resultMSI = $resultMSIStatement->fetchAll(PDO::FETCH_ASSOC);
        $resDataT = [];
        foreach($resultMSI as $Data){
        	$tipo_desc = $Data['tipo_pago'].' - '.$Data['descripcion'];
	        $dataTable = array(
	        				$Data['tipo_pago'],
	        				$Data['descripcion'],
	        				(float)$Data['valor_porc'],
	        				(float)$Data['monto_minimo'],
	        				'<span><button class="btn btn-info" onclick="editarTP(\''.$Data['id_tp'].'\',\''.$tipo_desc.'\',\''.(float)$Data['valor_porc'].'\',\''.(float)$Data['monto_minimo'].'\')" style="height:20px;line-height:20px;"><i class="fa fa-pencil" aria-hidden="true" style="font-size:10px;"></i> Modificar</button></span>'
	        			);
	        array_push($resDataT,$dataTable);
	    }
        return $resDataT;
	}

	public static function SetCargo($id_tp,$cargo,$monto){
		(CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = "UPDATE AYT_CargoTarjetaCredito SET valor_porc = $cargo, monto_minimo = '$monto' WHERE id_tp = $id_tp;";
        $resultUpdStmt = $conn->prepare($query);
        $resultUpdStmt->execute();
        $result = $resultUpdStmt->rowCount();

        if ($result > 0){
        	return array('status' => true);
        }else{
        	return array('status' => false);
        }
	}
}