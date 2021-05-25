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

class Application_Model_MermaModel {
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
    
    /**
     * 
     */
    public function getAlmacenesMerma(){
       //$query = $this->_adapter->prepare(GET_ALMACENES_MERMA);
        $conn = new DB_ConexionExport();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = $conn->query(" SELECT DISTINCT WAREHOUSEID AS INVENTLOCATIONID ,WAREHOUSENAME AS NAME
                                FROM InventWarehouseStaging
                                WHERE WAREHOUSEID LIKE '%MRMA%'
                                ORDER BY WAREHOUSEID;");
       $query->execute();
       $result=$query->fetchAll(PDO::FETCH_ASSOC);
       return $result ;
    }
    public function exist($itemId, $almacen,$porcentaje,$local) {
     
        $data = $this->getVentamermaData($itemId, $almacen,$local);
        
        if(!empty($data)){
            $sql="UPDATE dbo.AYT_VentaMerma SET porcentaje=? where itemid= ? and almacen=? and loc = ?;";
            $query2 = $this->_adapter->prepare($sql);
            $query2->bindParam(1,$porcentaje);
            $query2->bindParam(2,$itemId);
            $query2->bindParam(3,$almacen);
            $query2->bindParam(4,$local);
            $id = $data["ID"];
            $data = $query2->execute();
        }
        else{
            $sql="INSERT INTO dbo.AYT_VentaMerma (itemid,porcentaje,almacen,loc) VALUES (?,?,?,?);";
            $query2 = $this->_adapter->prepare($sql);
            $query2->bindParam(1,$itemId);
            $query2->bindParam(2,$porcentaje);
            $query2->bindParam(3,$almacen);
            $query2->bindParam(4,$local);
            $query2->execute();
            $data = $this->getVentamermaData($itemId, $almacen,$local);
            $id = $data["ID"];
        }
        
        $this->createJournal($id, $_SESSION['userInax'], $porcentaje);
      
        return $data;
    }
    
    public function getVentamermaData($itemId, $almacen,$local) {
        $queryStr = "SELECT * FROM dbo.AYT_VentaMerma where itemid= ? and almacen= ? and loc = ? ;";
        $query = $this->_adapter->prepare($queryStr);
        $query->bindParam(1,$itemId);
        $query->bindParam(2,$almacen);
        $query->bindParam(3,$local);
        $query->execute();
        $data = $query->fetch();
        
        return $data;
    }

        public function getFamilies($company){
        // $queryStr = "SELECT T31.NAME AS 'FAMILIA'
        //             FROM ECORESPRODUCT T1
        //             LEFT JOIN ECORESPRODUCTCATEGORY T2 ON (T1.RECID = T2.PRODUCT) AND (T2.CATEGORYHIERARCHY = '5637146826') -- SEGMENTACION DE GRUPO
        //                     LEFT JOIN ECORESCATEGORY T21 ON (T2.CATEGORY = T21.RECID)
        //             LEFT JOIN ECORESPRODUCTCATEGORY T3 ON (T1.RECID = T3.PRODUCT) AND (T3.CATEGORYHIERARCHY = '5637146828') -- SEGMENTACION DE FAMILIA
        //                     LEFT JOIN ECORESCATEGORY T31 ON (T3.CATEGORY = T31.RECID)
        //             LEFT JOIN ECORESPRODUCTCATEGORY T4 ON (T1.RECID = T4.PRODUCT) AND (T4.CATEGORYHIERARCHY = '5637146827') -- SEGMENTACION DE DIVISION
        //                     LEFT JOIN ECORESCATEGORY T41 ON (T4.CATEGORY = T41.RECID)
        //             LEFT JOIN ECORESPRODUCTCATEGORY T5 ON (T1.RECID = T5.PRODUCT) AND (T5.CATEGORYHIERARCHY = '5637147576') -- SEGMENTACION DE LINEA
        //                     LEFT JOIN ECORESCATEGORY T51 ON (T5.CATEGORY = T51.RECID)
        //             LEFT JOIN INVENTSUM T6 ON (T1.DISPLAYPRODUCTNUMBER = T6.ITEMID)
        //             WHERE T6.DATAAREAID= ?
        //             AND T31.name is not null 
        //             group by T31.NAME order by T31.NAME ;";
        // $query = $this->_adapter->prepare($queryStr);
        // $query->bindParam(1,$company);
        $conn = new DB_ConexionExport();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = $conn->query(" SELECT DISTINCT PRODUCTCATEGORYNAME AS FAMILIA
                                FROM EcoResProductCategoryAssignmentStaging T1
                                WHERE T1.PRODUCTCATEGORYHIERARCHYNAME = 'FAMILIA'
                                ORDER BY T1.PRODUCTCATEGORYNAME;");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getChildsItems($almacen,$family){
        
        $almacenIn = join("','",$almacen);
        $familyIn = join("','",$family);  
   
        $conn = new DB_ConexionExport();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //         // $this->_adapter->query("SELECT ( SUM(T0.postedvalue) - ( CAST(SUM(T0.physicalvalue) AS DECIMAL(10, 2)) *- 1 ) ) / IIF(( SUM(T0.postedqty) + SUM(T0.received - T0.deducted) ) + SUM(T0.registered - T0.picked) = 0, 1 ,( SUM(T0.postedqty) + SUM(T0.received - T0.deducted) ) + SUM( T0.registered - T0.picked)) AS 'COSTO', 
        //         //                    T0.itemid,t1.INVENTSITEID
        //         //             INTO   ##T3
        //         //             FROM   inventsum T0 LEFT JOIN inventdim T1 ON T0.inventdimid = T1.inventdimid 
        //         //             WHERE T1.dataareaid = '".COMPANY."' 
        //         //             AND T0.dataareaid = '".COMPANY."' 
        //         //             GROUP  BY T1.INVENTSITEID,T0.itemid");
        //         // $this->_adapter->query("SELECT Price AS COSTO
        //         //                         , ItemNumber AS itemid
        //         //                         , PriceSiteId AS INVENTSITEID
        //         //                         INTO ##T3
        //         //                         FROM averageCostInax;");
        //         $conn->query("SELECT    AVAILABLEONHANDQUANTITY,
        //                                 ITEMNUMBER,
        //                                 INVENTORYSITEID,
        //                                 INVENTORYWAREHOUSEID,
        //                                 '' AS LOCALIDAD
        //                         INTO ##T3
        //                         FROM InventWarehouseInventoryStatusOnHandStaging
        //                         WHERE INVENTORYSTATUSID = 'Disponible';");
        //         // $this->_adapter->query("SELECT SUM(existencia) 'Existencia', 
        //         //                    articulo, 
        //         //                       sitio,
        //         //                    almacen, 
        //         //                    localidad 
        //         //             INTO   ##T2 
        //         //             FROM   (SELECT T0.itemid                       'Articulo', 
        //         //                            SUM(T0.availphysical)           'Existencia', 
        //         //                            T1.inventsiteid                 'Sitio', 
        //         //                            T1.inventlocationid             'Almacen', 
        //         //                            T1.wmslocationid                'Localidad', 
        //         //                            (SELECT Max(expdate) 
        //         //                             FROM   inventbatch 
        //         //                             WHERE  inventbatchid = T1.inventbatchid 
        //         //                                    AND itemid = T0.itemid) 'fechavenci', 
        //         //                            ( CASE 
        //         //                                WHEN ( (SELECT expdate 
        //         //                                        FROM   inventbatch 
        //         //                                        WHERE  inventbatchid = T1.inventbatchid 
        //         //                                               AND itemid = T0.itemid 
        //         //                                               AND dataareaid = '".COMPANY."') > GETDATE() 
        //         //                                        OR 
        //         //                                                       (SELECT expdate 
        //         //                                            FROM   inventbatch 
        //         //                                            WHERE  inventbatchid = T1.inventbatchid 
        //         //                                                   AND itemid = T0.itemid 
        //         //                                                   AND dataareaid = '".COMPANY."') IS NULL ) 
        //         //                                           THEN 0 
        //         //                                ELSE 1 
        //         //                              END ) AS caduco 
        //         //                     FROM   inventsum T0 
        //         //                            INNER JOIN inventdim T1 ON T0.inventdimid = T1.inventdimid AND T1.dataareaid = '".COMPANY."' 
        //         //                     WHERE  T0.dataareaid = '".COMPANY."' 
        //         //                                    AND T1.inventlocationid IN ( '$almacenIn' )
        //         //                     GROUP BY T0.itemid,T1.inventsiteid,T1.inventlocationid,T1.wmslocationid,T1.inventbatchid) x 
        //         //             GROUP BY x.articulo,x.sitio,x.almacen,x.localidad;");
        //         $con->query("SELECT AVAILABLEONHANDQUANTITY,
        //                             ITEMNUMBER,
        //                             INVENTORYSITEID,
        //                             INVENTORYWAREHOUSEID,
        //                             '' AS LOCALIDAD
        //                     INTO ##T2
        //                     FROM InventWarehouseInventoryStatusOnHandStaging
        //                     WHERE INVENTORYSTATUSID = 'Disponible';");

        //         // $this->_adapter->query("SELECT T0.itemid           AS 'CODIGO', 
        //         //                    searchname          AS 'NOMBRE', 
        //         //                    T1.inventlocationid AS 'ALMACEN', 
        //         //                    t31.NAME            AS 'FAMILY', 
        //         //                    T1.wmslocationid    AS 'LOC' ,
        //         //                       T1.inventsiteid     AS 'SITIO'
        //         //             INTO   ##T1
        //         //             FROM   inventsum t0 
        //         //                    LEFT JOIN inventdim T1 ON T0.inventdimid = T1.inventdimid 
        //         //                    LEFT JOIN ecoresproduct T2 ON T2.displayproductnumber = T0.itemid 
        //         //                    LEFT JOIN ecoresproductcategory T3 ON ( T2.recid = T3.product ) AND ( T3.categoryhierarchy = '5637146828' ) 
        //         //                    LEFT JOIN ecorescategory T31 ON ( T3.category = T31.recid ) 
        //         //                    LEFT JOIN inventsum T6 ON ( T2.displayproductnumber = T6.itemid ) 
        //         //             WHERE  T1.inventlocationid IN ( '$almacenIn' ) 
        //         //                    AND t31.NAME IN ( '$familyIn' ) 
        //         //                    AND T6.dataareaid = '".COMPANY."'
        //         //             GROUP BY T0.itemid,searchname,T1.inventlocationid, t31.NAME, T1.wmslocationid,T1.inventsiteid");
                
        //         $conn->query("SELECT  ITEMNUMBER,
        //                                 SEARCHNAME,
        //                                 '".$almacen."' AS ALMACEN,
        //                                 PRODUCTGROUPID,
        //                                 '' AS LOC,
        //                                 CASE WHEN (SUBSTRING('".$almacen."',0,LEN('".$almacen."')-3) = 'CEDS') THEN 'CEDSCHI' ELSE SUBSTRING('".$almacen."',0,LEN('".$almacen."')-3) END AS SITIO
        //                         INTO ##T3
        //                         FROM EcoResReleasedProductV2Staging
        //                         WHERE PRODUCTGROUPID IN ('".$family."');");
        //         $query = $this->_adapter->prepare("SELECT *
        //                     FROM ##T3 T1 
        //                     LEFT JOIN ##T2 T2 ON T1.INVENTSITEID = T2.Sitio AND T1.ITEMID = T2.Articulo
        //                     LEFT JOIN ##T1 T3 ON T3.SITIO = T2.Sitio AND T2.Articulo = T3.CODIGO AND T2.Localidad = T3.LOC
        //                     WHERE Existencia IS NOT NULL 
        //                     AND Existencia > 0  
        //                     AND CODIGO IS NOT NULL");
        //         $query->bindParam(1,$almacen);
        // //        $query->bindParam(2,$familyIn);
        //         $query->execute();
        //         $result = $query->fetchAll();
        // $this->_adapter->query("DROP TABLE ##T1,##T2,##T3");
        $query = $conn->query(" SELECT  T3.PRICE AS COSTO
                                        ,T3.ITEMNUMBER AS 'CODIGO'
                                        ,T3.PRICESITEID AS 'SITIO'
                                        ,T2.INVENTORYWAREHOUSEID AS 'ALMACEN'
                                        ,T2.AVAILABLEONHANDQUANTITY AS Existencia
                                        ,T1.PRODUCTGROUPID AS 'FAMILIA'
                                        ,'MRMA' AS LOC
                                        ,IIF(( T5.DESCRIPTION = '' OR T5.DESCRIPTION IS NULL ),T1.ProductSearchName,T5.DESCRIPTION) AS NOMBRE
                                FROM  EcoResReleasedProductV2Staging T1 
                                LEFT JOIN InventWarehouseInventoryStatusOnHandStaging T2 ON (T2.ITEMNUMBER = T1.ITEMNUMBER)
                                LEFT JOIN InventItemPriceStaging T3 ON (T3.ITEMNUMBER = T1.ITEMNUMBER AND T3.PRICESITEID = T2.INVENTORYSITEID)
                                LEFT JOIN EcoResProductCategoryAssignmentStaging T4 ON (T4.PRODUCTNUMBER = T1.ITEMNUMBER AND T4.PRODUCTCATEGORYHIERARCHYNAME = 'FAMILIA')
                                LEFT JOIN AYT_InventTableStaging T5 ON (T5.ITEMID = T1.ItemNumber)
                                WHERE T2.INVENTORYWAREHOUSEID IN ('".$almacenIn."')
                                AND T4.PRODUCTCATEGORYNAME IN ('".$familyIn."')
                                AND T3.ITEMNUMBER IS NOT NULL
                                AND T2.AVAILABLEONHANDQUANTITY > 0
                                ORDER BY T3.PRICESITEID;");
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    
    public function getStock($item,$inventLocationId,$loc){        
        $queryStr = "SELECT SUM(Existencia) 'Existencia'
			FROM (
			SELECT
			T0.ITEMID 'Articulo',
			SUM(T0.AVAILPHYSICAL) 'Existencia',
			T1.INVENTSITEID 'Sitio',
			T1.INVENTLOCATIONID 'Almacen',
			T1.WMSLOCATIONID 'Localidad',
			(SELECT MAX(EXPDATE) FROM INVENTBATCH WHERE INVENTBATCHID = T1.INVENTBATCHID AND ITEMID = ? ) 'fechavenci',
			(CASE WHEN ( (SELECT EXPDATE FROM INVENTBATCH WHERE INVENTBATCHID = T1.INVENTBATCHID AND ITEMID = ? AND DATAAREAID = '".COMPANY."') > GETDATE() 
                        OR (SELECT EXPDATE FROM INVENTBATCH WHERE INVENTBATCHID = T1.INVENTBATCHID AND ITEMID = ? AND DATAAREAID = '".COMPANY."') IS NULL)
			THEN 0 ELSE 1 END
			) as caduco
			FROM INVENTSUM T0
			INNER JOIN INVENTDIM T1 ON T0.INVENTDIMID=T1.INVENTDIMID AND T1.DATAAREAID = '".COMPANY."'
			WHERE T0.ITEMID = ? 
			AND T1.INVENTLOCATIONID = ?
			AND T1.WMSLOCATIONID = ?
			AND T0.DATAAREAID='".COMPANY."'
			GROUP BY T0.ITEMID,
			T1.INVENTSITEID,
			T1.INVENTLOCATIONID, 
			T1.WMSLOCATIONID,
			T1.INVENTBATCHID )x
			GROUP BY x.Articulo,x.Sitio,x.Almacen,x.Localidad;";
        
        $query = $this->_adapter->prepare($queryStr);    
        $query->bindParam(1,$item);
        $query->bindParam(2,$item);
        $query->bindParam(3,$item);
        $query->bindParam(4,$item);
        $query->bindParam(5,$inventLocationId);
        $query->bindParam(6,$loc);
        $query->execute();
        return $query->fetch();
        
    }
    
    public function getCostoPromedio($itemID,$site){
        try {
            if($site == "CEDS"){
                $site .= "CHI";
            }
        // $query = $this->_adapter->prepare(COSTO_PROMEDIO);
        // $query->bindParam(1,$itemID);
        // $query->bindParam(2,$site);
        // $query->execute();
        $conn = new DB_ConexionExport();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //$query = $this->_adapter->prepare("SELECT Price AS COSTO FROM averageCostInax WHERE PriceSiteId = '".$site."';");
        $query = $conn->prepare("IF EXISTS(
                                    SELECT Price AS COSTO 
                                    FROM dbo.InventItemPriceStaging 
                                    WHERE PriceSiteId = '".$site."' 
                                    AND ItemNumber = '".$itemID."' 
                                )
                                BEGIN
                                    SELECT Price AS COSTO 
                                    FROM dbo.InventItemPriceStaging 
                                    WHERE PriceSiteId = '".$site."' 
                                    AND ItemNumber = '".$itemID."'
                                END
                                ELSE 
                                BEGIN   
                                    IF EXISTS(
                                        SELECT Price AS COSTO 
                                        FROM dbo.InventItemPriceStaging 
                                        WHERE PriceSiteId = 'CEDSCHI' 
                                        AND ItemNumber = '0270-0160-0280'
                                    )
                                    BEGIN
                                        SELECT Price AS COSTO 
                                        FROM dbo.InventItemPriceStaging 
                                        WHERE PriceSiteId = 'CEDSCHI' 
                                        AND ItemNumber = '".$itemID."'
                                    END
                                    ELSE
                                    BEGIN
                                        SELECT TOP 1 Price AS COSTO
                                        FROM dbo.InventItemPriceStaging
                                        WHERE ITEMNUMBER = '".$itemID."'
                                        ORDER BY PRICECREATEDDATETIME DESC
                                    END
                                END;");
        // $query = $conn->prepare("   SELECT Price AS COSTO 
        //                             FROM dbo.InventItemPriceStaging 
        //                             WHERE PriceSiteId = '".$site."' 
        //                             AND ItemNumber = '".$itemID."' ;");
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
        
        } catch (Exception $e){
            return 0;
        }
    }
    public function getUtilidadMermaItem($itemID,$site,$loc){
        $queryStr = "SELECT PORCENTAJE FROM dbo.AYT_VentaMerma WHERE ITEMID='".$itemID."' AND ALMACEN='".$site."' AND LOC='MRMA';";
        $query = $this->_adapter->prepare($queryStr);
        // $query->bindParam(1,$itemID);
        // $query->bindParam(2,$site);
        // $query->bindParam(3,$loc);
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);
        $result = (empty($data))?array('PORCENTAJE' => 0):$data;
        return $result;
    }
    public function getVentaMerma($id = false){
        $id ? $where = " WHERE itemid like '%$id%'" : $where;
        $queryStr = "SELECT * FROM dbo.AYT_VentaMerma $where;";
        $query = $this->_adapter->prepare($queryStr);
        $query->execute();
        return $query->fetchAll();
    }
    
    public function getVentaMermaAlmacen($almacen = FALSE){
        $almacen ? $where = " WHERE ALMACEN in ('". join("','",$almacen)."')" : $where;
        $queryStr = "SELECT * FROM dbo.AYT_VentaMerma $where;";
        $query = $this->_adapter->prepare($queryStr);
        $query->execute();
        return $query->fetchAll();
    }
    
    public function getItem($itemid){
        $queryStr = "SELECT * FROM ECORESPRODUCT where DISPLAYPRODUCTNUMBER = ? ;";
        $query = $this->_adapter->prepare($queryStr);
        $query->bindParam(1,$itemid);
        $query->execute();
        return $query->fetch();
    }
    
    public function getPicturesKeys($almacen){
        $almacenIn = join("','",$almacen);
        $queryStr = "SELECT ITEMID,ALMACEN,LOC FROM dbo.AYT_MermaImg WHERE ALMACEN in ('$almacenIn') GROUP BY ITEMID,ALMACEN,LOC ;";
        $query = $this->_adapter->prepare($queryStr);
        $query->execute();
        return $query->fetchAll();
    }
    
    public function getPictures($itemid,$almacen,$loc){
        $queryStr = "SELECT * FROM dbo.AYT_MermaImg WHERE ITEMID = ? AND ALMACEN = ? AND LOC = ?;";
        $query = $this->_adapter->prepare($queryStr);
        $query->bindParam(1,$itemid);
        $query->bindParam(2,$almacen);
        $query->bindParam(3,$loc);
        $query->execute();
        return $query->fetchAll();
    }
    
    public function getPicture($id){
        $queryStr = "SELECT * FROM dbo.AYT_MermaImg WHERE ID = ? ;";
        $query = $this->_adapter->prepare($queryStr);
        $query->bindParam(1,$id);
        $query->execute();
        return $query->fetch();
    }
    
    public function postPicture($itemid,$base64,$almacen,$loc){
        $queryStr = "INSERT INTO dbo.AYT_MermaImg (ITEMID,RUTA,ALMACEN,COMENTARIOS,LOC) VALUES (?,?,?,'',?); ";
        $query = $this->_adapter->prepare($queryStr);
        $query->bindParam(1,$itemid);
        $query->bindParam(2,$base64);
        $query->bindParam(3,$almacen);
        $query->bindParam(4,$loc);
        $query->execute();
    }
    
    public function deletePicture($id){
        $queryStr = "DELETE dbo.AYT_MermaImg WHERE ID = ?; ";
        $query = $this->_adapter->prepare($queryStr);
        $query->bindParam(1,$id);
        $query->execute();
    }
    
    public function updatePicture($id,$comment){
        $queryStr = "update dbo.AYT_MermaImg SET COMENTARIOS = ? WHERE ID = ?; ";
        $query = $this->_adapter->prepare($queryStr);
        $query->bindParam(1,$comment);
        $query->bindParam(2,$id);
        $query->execute();
    }
    
    public function getJournals(){
        $queryStr = "SELECT * FROM ".INTERNA.".dbo.JOURNAL_MERMA jm "
                . "JOIN dbo.AYT_VentaMerma v on v.ID = jm.ID_VENTAMERMA "
                . "ORDER BY jm.ID_VENTAMERMA; ";
        $query = $this->_adapter->prepare($queryStr);
        $query->execute();
         return $query->fetchAll();
    }
    
    public function createJournal($idventamerma,$username,$data){
        $queryStr = "INSERT INTO ".INTERNA.".dbo.JOURNAL_MERMA (ID_VENTAMERMA,USERNAME,DATA) VALUES (?,?,?); ";
        $query = $this->_adapter->prepare($queryStr);
        $query->bindParam(1,$idventamerma);
        $query->bindParam(2,$username);
        $query->bindParam(3,$data);
        $query->execute();
    }
}
