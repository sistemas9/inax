<?php
class Application_Model_ConfigpuntoslealtadModel {
	public static function getArticulosEntity(){
		(CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport(); 
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT);
        $query = $conn->prepare("   SELECT  T0.ItemNumber,T0.ProductSearchName,T0.AplicaPuntos,ISNULL(T1.PuntosAvance,0) AS PuntosAvance
									FROM articulos T0
									LEFT JOIN ArticulosPuntosAvance T1 ON (T1.ItemNumber = T0.ItemNumber)
									ORDER BY T0.ItemNumber;");
        
        $query->execute();
        $resultadoSinUTF = $query->fetchAll(PDO::FETCH_ASSOC);
        return $resultadoSinUTF;
    }

    public static function updateAplica($codigo){
    	(CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport(); 
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = $conn->prepare("   UPDATE
									articulos
									SET AplicaPuntos = 1
									WHERE itemNumber = '".$codigo."';");
        $query->execute();
        $updateArticulo = $query->rowCount();
        if ($updateArticulo > 0){
	        $updateArti = true;
	    }
	    if ($updateArti){
	    	$result = array('estatus' => 1,'msg' => 'Exito');
	    }else{
	    	$result = array('estatus' => 0,'msg' => 'Fallo');
	    }
        return $result;
    }

    public static function updateArticulo($codigo,$puntos){
    	(CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport(); 
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	$query = $conn->prepare("EXECUTE dbo.articulosPremia '".$codigo."',".$puntos.";");
        $query->execute();
        $updateArticulosPuntosAvance = $query->fetchAll(PDO::FETCH_ASSOC);
        if ($updateArticulosPuntosAvance[0]['Mensaje'] != ''){
	        $updateArtiPuntos = true;
	    }
	    if ($updateArtiPuntos){
	    	$result = array('estatus' => 1,'msg' => $updateArticulosPuntosAvance[0]['Mensaje']);
	    }else{
	    	$result = array('estatus' => 0,'msg' => 'Fallo');
	    }
        return $result;
    }

    public static function removePuntosArticulo($codigo){
    	(CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport(); 
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = $conn->prepare("   UPDATE
									articulos
									SET AplicaPuntos = 0
									WHERE itemNumber = '".$codigo."';");
        $query->execute();
        $updateArticulo = $query->rowCount();
        if ($updateArticulo > 0){
        	return '1';
        }
        return '0';
    }

    public static function removePuntos($codigo){
    	(CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport(); 
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = $conn->prepare("   DELETE
									ArticulosPuntosAvance
									WHERE itemNumber = '".$codigo."';");
        $query->execute();
        $updateArticulo = $query->rowCount();
        if ($updateArticulo > 0){
        	return '1';
        }
        return '0';
    }
}