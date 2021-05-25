<?php
class Application_Model_BuscarPreciosModel {
	public static function getPriceData($articulo,$cantidad,$lineaDesc,$conjuntoPrecios){
		////////////////consultar el precio y demas datos en base de datos/////////////////////////////
        try{
          $db = new Application_Model_UserinfoMapper();
          $adapter = $db->getAdapter();
          $query = $adapter->query(ANSI_NULLS);
          $query = $adapter->query(ANSI_WARNINGS);
          $query = $adapter->prepare("EXECUTE dbo.AYT_GetPriceData '".$conjuntoPrecios."','".$lineaDesc."','".$articulo."',".$cantidad.";");
          $query->execute();
          $priceData = $query->fetchAll(PDO::FETCH_ASSOC);
          return $priceData[0];
        }catch(Exception $e){
          print_r($e->getMessage());
          exit();
        }
        /////////////////////////////////////////////////////////////////////////////////////
	}
}