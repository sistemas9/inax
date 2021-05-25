<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DB_Conexion
 *
 * @author efrain.campa
 */
class DB_Conexion extends PDO {

   private $tipo_de_base   = 'odbc';
   private $driver   = 'ODBC Driver 13 for SQL Server';
   // private $tipo_de_base   = 'SQL Server';
   // private $host           = 'SQL03\DB03';
   private $host           = 'ayt.database.windows.net';
   // private $nombre_de_base = 'AXTEST';
   private $nombre_de_base = 'aytProd';
   // private $usuario        = 'sa';
   private $usuario        = 'aytuser';
   // private $contrasena     = 'avanceytec';
   private $contrasena     = '$r%ER2aY#wBD3cDP';
   private $db             = '';
   public function __construct() {
      //Sobreescribo el método constructor de la clase PDO.
      try{         
         $dbh = parent::__construct($this->tipo_de_base.':Driver='.$this->driver.';server='.$this->host.';Database='.$this->nombre_de_base, $this->usuario, $this->contrasena);         
         return $dbh;
         //exit('odbc:Driver={'.$this->tipo_de_base.'};Server='.$this->host.';Database='.$this->nombre_de_base);
         //parent::__construct('odbc:Driver={'.$this->tipo_de_base.'};Server='.$this->host.';Database='.$this->nombre_de_base, 'reports', 'avanceytec');
      }
      catch(PDOException $e){
         echo 'Ha surgido un error y no se puede conectar a la base de datos. Detalle: ' . $e->getMessage();
         exit;
      }
   }
}

class DB_ConexionExport extends PDO {

   private $tipo_de_base   = 'odbc';
   private $driver   = 'ODBC Driver 13 for SQL Server';
   // private $tipo_de_base   = 'SQL Server';
   // private $host           = 'SQL03\DB03';
   private $host           = 'ayt.database.windows.net';
   // private $nombre_de_base = 'AXTEST';
   private $nombre_de_base = 'aytExport';
   // private $usuario        = 'sa';
   private $usuario        = 'aytuser';
   // private $contrasena     = 'avanceytec';
   private $contrasena     = '$r%ER2aY#wBD3cDP';
   private $db             = '';
   public function __construct() {
      //Sobreescribo el método constructor de la clase PDO.
      try{         
         $dbh = parent::__construct($this->tipo_de_base.':Driver='.$this->driver.';server='.$this->host.';Database='.$this->nombre_de_base, $this->usuario, $this->contrasena);         
         return $dbh;
         //exit('odbc:Driver={'.$this->tipo_de_base.'};Server='.$this->host.';Database='.$this->nombre_de_base);
         //parent::__construct('odbc:Driver={'.$this->tipo_de_base.'};Server='.$this->host.';Database='.$this->nombre_de_base, 'reports', 'avanceytec');
      }
      catch(PDOException $e){
         echo 'Ha surgido un error y no se puede conectar a la base de datos. Detalle: ' . $e->getMessage();
         exit;
      }
   }
}