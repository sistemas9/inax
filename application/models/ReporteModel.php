<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of reporteModel
 *
 * @author sistemas10    
 */

class Application_Model_ReporteModel {
    public $db;
    public $_adapter;
    
    public function __construct(array $options = null){
        if (is_array($options)) {
            $this->setOptions($options);
        }
        $this->db = new Application_Model_UserinfoMapper();
        $this->_adapter = $this->db->getAdapter();
        $query=$this->_adapter->query(ANSI_NULLS);
        $query=$this->_adapter->query(ANSI_WARNINGS);
        $query->execute();
        return $this->_adapter;
    }
    public function sqlPrepare($q, $array=array()) {
       $query = $this->_adapter->prepare($q);
       $query->execute($array);
       $result=$query->rowCount();
       return $result ;
    }
    /**
     * 
     * @return ARRAY
     */
    public function getData2Array($q,$array=array()) {
       $query = $this->_adapter->prepare($q);
       $query->execute($array);
       $result=$query->fetchAll();
       return $result ;
    }
    /**
     * 
     * @return type
     */
    public function getUsrListPermiso() {
        $query = $this->_adapter->prepare(USER_LIST_ASIGNED);
       $query->execute();
       $result=$query->fetchAll();
       return $result ;
    }
    /**
     * 
     * @return type
     */
    public function getUsoReportTable(){
       $query = $this->_adapter->prepare(KARDEX);
       $query->execute();
       $result=$query->fetchAll();
       return $result ;
    }
    
    /**
     * Regresa resulset de la tabla de kardex con los campos filtrados, los cuales estan definidos por parametros
     * los parametros: $param$ y $param5 son asignados a fecha en el formato "20170821" en ves de "2017-08-21"
     * @param String $kardexFolio
     * @param String $usuario
     * @param String $nombre
     * @param String $fecha1
     * @param String $fecha2
     * @param String $movimiento
     * @param String $sucursal
     * 
     * @return ResultSet content kardex table
     */
    
    public function getUsoReportTableFilter($kardexFolio,$usuario,$nombre,$fecha1,$fecha2,$movimiento,$sucursal) {
       //$query = $this->_adapter->prepare(KARDEX_FILTER);
       $query=$this->_adapter->query("select t1.id_kardex,t1.usuario,t1.nombre,t1.fecha,t1.movimiento,t2.nombre as sucursal from dbo.AYT_Kardex t1 right join dbo.AYT_Sucursal t2 on t2.idsuc=t1.sucursal where t1.id_kardex like '$kardexFolio%' and t1.usuario like '%$usuario%' and t1.nombre like '%$nombre%' and t1.fecha between '$fecha1' and '$fecha2' and t1.movimiento like '$movimiento%' and t2.nombre like '%$sucursal%'  order by t1.fecha desc;");
        $query->bindParam(1,$kardexFolio);
       $query->bindParam(2,$usuario);
       $query->bindParam(3,$nombre);
       $query->bindParam(4,$fecha1);
       $query->bindParam(5,$fecha2);
       $query->bindParam(6,$movimiento);
       $query->bindParam(7,$sucursal);
       $query->execute();
       $result=$query->fetchAll();
       return $result ;        
    }
    public function getUsoReportGrafico(){
       $query = $this->_adapter->prepare(ESTADISTICO_LOGIN);
       $query->execute();
       $result=$query->fetchAll();
       return $result ;
    }
    public function getCTZN_VS_VTAS(){
       // $query = $this->_adapter->prepare(CTZN_VS_VTAS);
       // $query->execute();
       // $result=$query->fetchAll();  
       // return $result ;
      return null;
    }
    public function getConfirmadosVsCreados(){
       // $query = $this->_adapter->prepare(CONFIRMADOS_VS_CREADOS);
       // $query->execute();
       // $result=$query->fetchAll();  
       // return $result ;
      return null;
    }
    public function getConfirmadosVsCreadosPorUsuario(){
       // $query = $this->_adapter->prepare(CONFIRMADOS_VS_CREADOS_POR_USUSRIO);
       // $query->execute();
       // $result=$query->fetchAll();  
       // return $result ;
      return null;
    }
    public function getDataNegadosFilter($f1,$f2,$vendedor) {
      (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $queryStr = "SELECT T0.VENDEDOR AS '0',
                          CODIGO_ARTICULO AS '1',
                          NOMBRE AS '2',
                          T2.ORGANIZATIONNAME AS '3',
                          SITIO AS '4',
                          CANTIDAD_NEGADA AS '5',
                          UOM AS '6',
                          ISNULL(CONVERT(VARCHAR(255),COMENTARIO),'SIN COMENTARIOS') AS '7',
                          CONVERT(VARCHAR,FECHA,103) AS '8' 
                  FROM AYT_NEGADOS T0 
                  INNER JOIN CLIENTESINAX T2 ON (T2.CUSTOMERACCOUNT COLLATE DATABASE_DEFAULT = T0.CLIENTE COLLATE DATABASE_DEFAULT)
                  WHERE T0.VENDEDOR != 'SISTEMAS07' AND FECHA BETWEEN '$f1' AND '$f2' 
                  AND T0.VENDEDOR LIKE '$vendedor'
                  GROUP BY CODIGO_ARTICULO,VENDEDOR,NOMBRE,CLIENTE,SITIO,CANTIDAD_NEGADA,UOM,CONVERT(VARCHAR(255),COMENTARIO),FECHA,T2.ORGANIZATIONNAME  
                  ORDER BY 1,9,5;";
      $query = $conn->prepare($queryStr);
      // $query->bindParam(1,$f1);
      // $query->bindParam(2,$f2);
      // $query->bindParam(3,$vendedor);
      $query->execute();
      $result = $query->fetchAll();
      return $result;
    }
    public function getGraficaUSO($f1,$f2){
       // $query = $this->_adapter->prepare(GRAFICA_SUCURSAL); 
       // $query->bindParam(1,$f1);
       // $query->bindParam(2,$f2);
       $str = "select t2.nombre,COUNT(sucursal) as conteo from dbo.AYT_Kardex t1 right join dbo.AYT_Sucursal t2 on t1.sucursal=t2.idsuc and fecha BETWEEN '$f1' AND '$f2' group by sucursal, t2.nombre order by conteo desc;";
       $query = $this->_adapter->prepare($str); 
       $query->execute();
       $result=$query->fetchAll();
       return $result ;
    }
    public function getDataSemaforo(){
       $query = $this->_adapter->prepare(SEMAFORO); 
       $query->execute();
       $result=$query->fetchAll();
       return $result ;
    }
    public function updateDataSemaforo($id) {
       $query1= $this->_adapter->query(SEMAFORO_RESET);
       $query1->execute();
       $query = $this->_adapter->prepare(SEMAFORO_UPDATE); 
       $query->bindParam(1,$id);
       $result=$query->execute();
       return $result; 
    }
    function getStatusSemaforo() {
    $query = $this->_adapter->prepare(SEMAFORO_ACTUAL); 
       $query->execute();
       $result=$query->fetchAll();
       $res="";
       foreach ($result as $key => $value) {
           $res=$value['color'];
       }
       return $res ;
    }
}
