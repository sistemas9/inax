<?php
class Application_Model_AdministracionSectoresModel {
    public static function getSegment(){
        error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
        $conn = new DB_Conect(); 
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT);
        $query = $conn->prepare("SELECT DESCRIPTION as description, BUSINESSSECTORID as id FROM AYT_smmBusSectorGroupStaging;");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    public static function getFamily(){
        error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
        $conn = new DB_Conect(); 
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT);
        $query = $conn->prepare("SELECT DISTINCT T31.RECID AS IDFAMILIA, ISNULL(T31.NAME,'OTROS') AS 'FAMILIA'
	                                FROM ECORESPRODUCTCATEGORY T3
	                                LEFT JOIN ECORESCATEGORY2 T31 ON (T3.CATEGORY = T31.RECID)
	                                WHERE T3.CATEGORYHIERARCHY = '5637145330' ORDER BY FAMILIA");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    public static function getSegmentFamilies($segment){
        error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
        $conn = new DB_Conect(); 
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT);
        $query = $conn->prepare("SELECT SegmentoCliente,Familia FROM AYT_RelacionCustSegmentFamily WHERE SegmentoCliente = '{$segment}';");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_OBJ);
    } 

    public static function setRelation($segmentId,$familyString){
        error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
        $userId = $_SESSION['userInax'];
        $conn = new DB_Conect();
        $hasRelation = $conn->prepare("SELECT SegmentoCliente,Familia FROM AYT_RelacionCustSegmentFamily WHERE SegmentoCliente = '{$segmentId}';");
        $hasRelation->execute();
        if($hasRelation->fetchAll(PDO::FETCH_OBJ)){
            echo('Simona');
            $today = date('Y-m-d H:i:s'); 
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT);
            $query = $conn->prepare("UPDATE AYT_RelacionCustSegmentFamily 
                                        SET Familia = '{$familyString}', UsuarioModificacion = '{$userId}', FechaModificacion = GETDATE()
                                        WHERE SegmentoCliente = '{$segmentId}';");
            print_r($query);          
            $query->execute();
        }else {
            echo'Nel';
            $today = date('Y-m-d H:i:s'); 
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT);
            $query = $conn->prepare("INSERT INTO AYT_RelacionCustSegmentFamily 
                                        VALUES ('{$segmentId}','{$familyString}','{$userId}',' ',GETDATE(),GETDATE());");
            print_r($query);          
            $query->execute();
        }
    } 



}
