<?php

require_once (LIBRARY_PATH.'/includes/dompdf/dompdf_config.inc.php');
require_once (LIBRARY_PATH.'/includes/code128.php');
setlocale(LC_MONETARY, 'es_MX');

class ImpresionCotizacionController extends Zend_Controller_Action{
    public $OS;
    public $company;
    public function init(){
        $this->OS      = substr(PHP_OS,0,3);
        $this->company = isset($_GET['company']) ? $_GET['company']:'';
        $tipo          = isset($_GET['tipo']) ? $_GET['tipo']:'';
        $offline       = isset($_GET['offline']) ? $_GET['offline']:'';
        if ($offline != ''){
            $_SESSION['offline'] = (boolean) $offline;
        }
        if(empty(COMPANY)){
            if ($tipo == ''){
                $this->_redirect('/login');
            }
        }
    }

    public function toMoney($val,$symbol='$',$r=2,$currency)
    {
        $n = $val; 
        $c = is_float($n) ? 1 : number_format($n,$r);
        $d = '.';
        $t = ',';
        $sign = ($n < 0) ? '-' : '';
        $i = $n=number_format(abs($n),$r); 
        $j = (($j = $i.length) > 3) ? $j % 3 : 0; 

       return  $currency.$sign .($j ? substr($i,0, $j) + $t : '').preg_replace('/(\d{3})(?=\d)/',"$1" + $t,substr($i,$j)) ;

    }

    public function indexAction(){
        $db = new Application_Model_UserinfoMapper();
        $adapter = $db->getAdapter();

        $subtotalX  =   0;
        $body   =   '';
        $flag   =   false;

        if(COMPANY == "ATP"){
            $flag = true;
        }else{
            if ($this->company != ''){
                session_start();
            }
            if ($this->company == 'ATP'){
                $flag = true;
            }
        }

        $this->_helper->layout()->disableLayout();
        $model = new Application_Model_CotizacionModel();
        $log = new Application_Model_Userinfo();
        date_default_timezone_set('America/Chihuahua');

        if(isset($_GET['id'])){
            $ImpCotizacion = filter_input(INPUT_GET,'id');
        }
        else {
            $ImpCotizacion = filter_input(INPUT_POST,'QuotationId');
        }

        if(empty($ImpCotizacion)){
            echo "NO SE RECIBIO EL FOLIO DE COTIZACION";
            exit();
        }
        
        $cabecera   =   $model->getCabezeraCotizacion(strtoupper($ImpCotizacion),$this->company);

            $startWith = substr($ImpCotizacion, 0, 2);
            if ($startWith == 'OV' || $startWith == 'AT') {
                $ImpCotizacion = $cabecera['QUOTATIONNUMBER'];
                $tipoDoc = 'COTIZACIÓN';
            }
            else {
                $tipoDoc = 'COTIZACIÓN';
            }

        // $impuestos  =   $cabecera['impuestos'];
        $impuestos   =   $cabecera['LINES'][0]->SalesTaxGroupCode;
        $cargo       =   $cabecera['cargos'];
        $idDirec     =   $cabecera['vendedor'];
        $vendedor    =   $model->getVendedor($idDirec);
        $items       =   $cabecera['LINES'];
        $comentarios =   $cabecera['COMENTARIOS'];
        $works       =   $cabecera['WORKERSID'];
        $ov          =   $cabecera['OV'];
        $sohInvoiceCustomerAccountNumber         =   $cabecera['InvoiceCustomerAccountNumber'];
        $organizationNumber = $cabecera['OrganizationNumber'];

        if ($ov == 'SIN OV') {
            $tipoDoc = 'COTIZACIÓN';
        }
        else {
            $tipoDoc = 'COTIZACIÓN';
        }

	    $date2      = new DateTime();
        $date       = $date2->format('d/m/Y');
        $moneda     = $cabecera['Moneda'];
       	$tipoCambio = $_SESSION['tipoC'];
	    $directorio = __DIR__."/../assets/img";
        $fecha      = explode('T',$cabecera['Fecha']);
        $fechaYMD   = explode('-',$fecha[0]);
        $fechaHMS   = explode('Z',$fecha[1]);
        $fecha2     = explode('T',$cabecera['FechaVencimiento']);
        $fechaYMD2  = explode('-',$fecha2[0]);
        $fechaHMS2  = explode('Z',$fecha2[1]);
        if (!$_SESSION['offline']){
            $fechaCot   = $fechaYMD[2] . '/' . $fechaYMD[1] . '/' . $fechaYMD[0];
            $fechaVenc  = $fechaYMD2[2] . '/' . $fechaYMD2[1] . '/' . $fechaYMD2[0];
        }else{
            $fechaCot   = new DateTime($cabecera['Fecha']);
            $fechaCot   = $fechaCot->format('d/m/Y');
            $fechaVenc  = $fechaYMD2[2] . '/' . $fechaYMD2[1] . '/' . $fechaYMD2[0];
        }

        $body.='<table style="width: 100%" border="0">
                <tr>';
        if($flag){
         $body.='<td style="width: 10%;"><img src="'.$directorio.'/verticalLogo.jpg" width="150px;" height="150px;"></td>
                <td style="font-size: 12px;align-content: center;">
                    <label ><b>Avance y Tecnología en Plásticos SA de CV</b></label><br>
                    AV. WASHINGTON #3701 <br> COMPLEJO INDUSTRIAL LAS AMERICAS<br> CHIHUAHUA,CHIH,MEX 31114<br>
                    01 614 432 6100<br>
                    <a href="www.avanceytec.com.mx">www.avanceytec.com.mx</a><br>
                </td>';
        }
        else{
            $body.='<td style="width: 10%;"><img src="'.$directorio.'/lideart200x200.jpg" width="150px;" height="150px;"></td>
                    <td style="font-size: 12px;align-content: center;">
                        <label ><b>LIDEART INNOVACIÓN S DE R.L DE C.V</b></label><br>
                        <img src="'.$directorio.'/imagepdf/location_on_1x.jpg"> CALLE WASHINGTON #3701 INT. 48-H <br> COMPLEJO INDUSTRIAL LAS AMERICAS<br> CHIHUAHUA,CHIH,MEX 31114<br>
                        <img src="'.$directorio.'/imagepdf/call_1x.jpg"> 01 614 432 6122<br>
                        <img src="'.$directorio.'/imagepdf/public_1x.jpg"><a href="https://lideart.com.mx/">lideart.com.mx/</a><br>
                    </td>';
        }
        $body.='<td style="font-size: 11px;width: 30%">
                            <label style="width: 30%; font-size: 18px;"><b>'.$tipoDoc.'</b></label><br>
                            
                            <br><label><strong>No: </strong></label><label>'.$ImpCotizacion.'</label><br>
                            <label><strong>Fecha: </strong></label><label>'.$fechaCot.'</label><br>
                            <label><strong>Fecha de Vencimiento: </strong></label><label>'.$fechaVenc.'</label><br>
                            <label><strong>Pago: </strong></label><label>'.$cabecera['Pago'].'</label>
                        </td>
                    </tr>
                    <tr><td colspan=3><br></td></tr>
                    <tr>
                        <td style="font-size: 11px;">
                            <b>Direccion de envío:</b><br>
                            <label >'.$sohInvoiceCustomerAccountNumber. ' '. $cabecera['NombreEntrega'].'</label><br>
                            <label >'.$cabecera['CALLE'].'</label><br>
                            <label >'.$cabecera['COLONIA'].'</label>
                            <label >'.$cabecera['CIUDAD'].','. $cabecera['ESTADO'] . ', ' . $cabecera['PAIS'].'</label>
                            <label>'.$cabecera['ZIPCODE'].'</label>
                        </td>
                        <td style="font-size: 11px;">';
                        
                        if ($comentarios != '') {
                            $body.='
                            <b>Comentarios:</b><br>
                            <p>'.$comentarios.'</p>';    
                        }
                        
                        
                        $body.='
                        </td>
                        <td style="font-size: 11px;">
                            <label><b>Observaciones:</b></label><br>
                            <label>El tipo de cambio del dia '.$date.' es de  '.$tipoCambio.' por USD</label>
                        </td>
                            </tr>
                        </table>
                <br><br>
                        <table style="width:100% ;font-size: 12px;" border="0">
                            <tr>
                                <td style="width: 15%;border-bottom: 2px solid black;">Código de artículo</td>
                                <td style="width: 40%;border-bottom: 2px solid black; text-align: left">Descripción</td>
                                <td style="width: 5%;border-bottom: 2px solid black; text-align: right;">Cantidad</td>
                                <td style="width: 10%;border-bottom: 2px solid black;">Unidad</td>
                                <td style="width: 10%;border-bottom: 2px solid black; text-align: right;"> P.Unitario</td>
                                <td style="width: 5%;border-bottom: 2px solid black; text-align: right;"> Descuento</td>
                                <td style="width: 10%;border-bottom: 2px solid black; text-align: right;">Importe</td>
                            </tr>';
            ////////////Consultar si tiene comentario la la cotizacion//////////////////////////
            (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $queryStrCom = "SELECT COUNT(*) AS cuantas FROM AYT_ComentariosLineas WHERE documentId = '".$cabecera['QUOTATIONNUMBER']."' AND comentario != '';";
            $query = $conn->prepare($queryStrCom);
            $query->execute();
            $resultCuantosCom = $query->fetchAll(PDO::FETCH_ASSOC);
            $conComentarios = false;
            if ($resultCuantosCom[0]['cuantas'] > 0){
                $conComentarios = true;
                $queryStrIDLI = "SELECT inventoryLotId FROM AYT_ComentariosLineas WHERE documentId = '".$cabecera['QUOTATIONNUMBER']."' AND comentario != '';";
                $queryIDLI = $conn->prepare($queryStrIDLI);
                $queryIDLI->execute();
                $resultIDLI = $queryIDLI->fetchAll(PDO::FETCH_ASSOC);
            };
            ///////////////////////////////////////////////////////////////////////////////////
            for ($i=0; $i < count($items) ; $i++) {

                $lineSequenceNumber = $items[$i]->LineCreationSequenceNumber;
                $inventoryLotId = $items[$i]->InventoryLotId;

                if ($conComentarios && in_array(array('inventoryLotId' => $inventoryLotId), $resultIDLI)){
                    $q2 = $adapter->query("SELECT * FROM dbo.AYT_ComentariosLineas WHERE LineSequenceNumber = $lineSequenceNumber AND inventoryLotId = '$inventoryLotId' AND documentId = '".$cabecera['QUOTATIONNUMBER']."';");
                    $q2->execute();
                    $result2 = $q2->fetchAll(PDO::FETCH_ASSOC);
                    $cabecera['ComentarioLinea'] = $result2[0]['comentario'];
                }else{
                    $cabecera['ComentarioLinea'] = '';
                }
                
                //$punitario=round($items[$i]['PrecioUnitario'],2)+($items[$i]['PrecioUnitario']*($cargo/100));
                $punitario=round($items[$i]->SalesPrice,2);
                //$importe=round($items[$i]['Importe'],2)+($items[$i]['Importe']*($cargo/100));

                $importe=round($items[$i]->LineAmount,2);


                if($cargo==0){
                    $punitario=$items[$i]->SalesPrice;
                    $importe=$items[$i]->LineAmount;
                }

                if ($this->OS != 'WIN'){
                    $fpunitario = money_format('%i', round($punitario,2));
                    $fimporte = money_format('%i', round($importe,2));
                }else{
                    $fpunitario = $this->toMoney(round($punitario,2),'$',2,$moneda);
                    $fimporte = $this->toMoney(round($importe,2),'$',2,$moneda);
                }

                $fpunitarioE = explode('MXN',$fpunitario);
                $fimporteE = explode('MXN',$fimporte);
                $cant = $items[$i]->RequestedSalesQuantity;

        		$body.= '
                    <tr>
                        <td style="vertical-align:top;">'.$items[$i]->ItemNumber.'</td>
                        <td style="vertical-align:top;">'.$items[$i]->LineDescription.' - ' . $works[$i] . '</td>
                        <td style="text-align: right;vertical-align:top;">' . $cant . '</td>
                        <td style="vertical-align:top;">'.$items[$i]->SalesUnitSymbol.'</td>
                        <td style="text-align:right;vertical-align:top;">$'.$fpunitarioE[1].'</td>
                        <td style="text-align:right;vertical-align:top;">'.round($items[$i]->LineDiscountPercentage,2).'%</td>
                        <td style="text-align:right;vertical-align:top;">$'.$fimporteE[1].'</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="6">
                            <b>Cantidad:</b> '.$cant.' 
                            <b>Sitio:</b> '.$items[$i]->ShippingSiteId.' 
                            <b>Almacen:</b> '.$items[$i]->ShippingWarehouseId.' <br>
                            <b>Comentario:</b> '.$cabecera['ComentarioLinea'].'
                        </td>
                    </tr>';

                $validatePun = str_replace(',','',$fpunitarioE[1]);
                $cargoValidate = 0;
                $cargoValidate = round($items[$i]->FixedPriceCharges,2);
                if ($cargoValidate == '') {
                    $cargoValidate = 0;
                }
                $importeManual = (($validatePun*$cant)+$cargoValidate)-((($validatePun*$cant)+$cargoValidate)*(round($items[$i]->LineDiscountPercentage,2)/100));
                $importeConsul = round($importe,2);
                $res = $importeConsul - $importeManual;
                $sitiotax = $items[$i]->ShippingSiteId;
                // print_r(round($items[$i]->LineDiscountPercentage,2));
                // print_r('<br>');
                // print_r('$cargoValidate: ' . $cargoValidate);
                // print_r('<br>');
                // print_r('$res: ' . $res);
                // print_r('<br>');
                // print_r('$importeConsul: ' . $importeConsul);
                // print_r('<br>');
                // print_r('$importeManual: ' . $importeManual);
                // print_r('<hr>');

                if ($res <= 2.5 && $res >= -2.5) {
                 
                }
                else {
                    // exit('<p style="text-align:center; font-family: calibri;">Favor de refrescar la página<p><hr>');
                }

                $subtotalX += $importe;
	       }          
        // exit('ooooo');

        # round($subtotalX,2)
        # round($subtotalX*0.16,2)
        # round($subtotalX*1.16,3)
        $iva = 1.16;   
        if($impuestos=='FRONT'){
              $iva = 1.08;
        } 
        if ($this->OS != 'WIN'){
            $subTotalCot = money_format('%i', $subtotalX);
            $ivaTotalCot = money_format('%i', round($subtotalX*($iva-1),2));
            $totalCot = money_format('%i', round($subtotalX*$iva,3));
        }else{
            $subTotalCot = $this->toMoney($subtotalX,'$',2,$moneda);
            $ivaTotalCot = $this->toMoney(round($subtotalX*($iva-1),2),'$',2,$moneda);
            $totalCot = $this->toMoney(round($subtotalX*$iva,3),'$',3,$moneda);
        }

        $subTotalCotE = explode('MXN',$subTotalCot);
        $ivaTotalCotE = explode('MXN',$ivaTotalCot);
        $totalCotE = explode('MXN',$totalCot);

        $body.='<tr>
                    <td colspan="5" style="text-align: right;"><b>Subtotal '.$moneda.'</b></td>
                    <td colspan="2" style="text-align: right;">$' . $subTotalCotE[1] . '</td>
                </tr>
                <tr>
                    <td colspan="5" style="text-align: right;"><b>IVA '.$moneda.'</b></td>
                    <td colspan="2" style="text-align: right;">$' . $ivaTotalCotE[1] . '</td>
                </tr>
                <tr>
                    <td colspan="5" style="text-align: right;"><b>Total '.$moneda.'</b></td>
                    <td colspan="2" style="text-align: right;">$' . $totalCotE[1] . '</td>
                </tr>
                <tr>
                    <td colspan="7">Vendedor: <b>'.$vendedor['NAME'].'</td>
                </tr>
            </table>';
    if($flag){    
        $body.='<table style="width: 100%">
                <tr>
                    <td style="width: 100%;">
                        <table  style="width: 100%" cellpading="0" cellspacing="0">
                            <tr>
                                <td colspan="3"><b>Para su mayor comodidad, puede depositarnos en:</b></td>
                            </tr>
                            <tr>
                                <td style="width: 30%; font-size: 12px; background-color: #BDBDBD">BANCO</td>
                                <td style="width: 20%; font-size: 12px; background-color: #BDBDBD">CUENTA</td>
                                <td style="width: 25%; font-size: 12px; background-color: #BDBDBD">CLABE INTERBANCARIA</td>
                                <!--<td style="width: 25%; font-size: 12px; background-color: #BDBDBD">CONVENIO</td>-->
                                <td style="width: 20%; font-size: 12px; background-color: #BDBDBD">CONCEPTO</td>
                            </tr>
                            <!--<tr>
                                <td style="width: 30%; font-size: 12px;">BANCOMER</td>
                                <td style="width: 20%; font-size: 12px;">0442666073</td>
                                <td style="width: 25%; font-size: 12px;">012150004426660735</td>
                                <td style="width: 20%; font-size: 12px;"></td>
                            </tr>
                            <tr>
                                <td style="width: 30%; font-size: 12px;">BANCOMER DOLARES</td>
                                <td style="width: 20%; font-size: 12px;">0442666065</td>
                                <td style="width: 25%; font-size: 12px;">012180004426660653</td>
                                <td style="width: 20%; font-size: 12px;"></td>
                            </tr>
                            <tr>
                                <td style="width: 30%; font-size: 12px;">BANAMEX</td>
                                <td style="width: 20%; font-size: 12px;">673 SUC 4305</td>
                                <td style="width: 25%; font-size: 12px;">002150430500006734</td>
                                <td style="width: 20%; font-size: 12px;"></td>
                            </tr>
                            <tr>
                                <td style="width: 30%; font-size: 12px;">SANTANDER</td>
                                <td style="width: 20%; font-size: 12px;">65174302218</td>
                                <td style="width: 25%; font-size: 12px;">014150651743022180</td>
                                <td style="width: 20%; font-size: 12px;"></td>
                                <td style="width: 20%; font-size: 12px;"></td>
                            </tr>-->
                            <tr>
                                <td style="width: 30%; font-size: 12px;">SANTANDER</td>
                                <td style="width: 20%; font-size: 12px;">65504316526</td>
                                <td style="width: 25%; font-size: 12px;">014150655043165264</td>
                                <!--<td style="width: 20%; font-size: 12px;"></td>-->
                                <td style="width: 20%; font-size: 12px;">'.$organizationNumber.'</td>
                            </tr>
                        </table>
                    </td> 
                </tr><tr>
                    <td style="page-break-after:always;width: 35%;font-size: 11px;">
                        <p style="font-size:10;">NOTA.-** Para pago en cajero/otro servicios utilizar el convenio #4944</<p>
                        </br>
                        <p><b>Observaciones importantes:</b></p>
                        <ul>
                            <li>Le solicitamos por favor antes de realizar su pago confirmar existencias.</li>
                            <li>Si su pago va a ser por transferencia, nos puede mandar
                                la imagen por correo o WhatsApp (+52 614 432 6111), para
                                embarcar su material, si se recibe antes de las
                                14:00 horas, se embarca ese mismo dia.</li>
                            <li>Si su material viaja por paqueteria será bajo la responsabilidad del destinatario.</li>
                            <li>Le recordamos qué las cotizaciones solo cuentan con vigencia de 24 horas, posterior a esto se deberá de cotizar de nuevo forzosamente.</li>
                        </ul>
                    </td>
                </tr>
            </table>';        
    }
        else{
            $body.='<table style="width: 100%">
                        <tr>
                            <td style="width: 100%;">
                                <table  style="width: 100%" cellpading="0" cellspacing="0">
                                    <tr>
                                        <td colspan="3"><b>Para su mayor comodidad, puede depositarnos en:</b></td>
                                    </tr>
                                    <tr>
                                        <td style="width: 30%; font-size: 12px; background-color: #BDBDBD">BANCO</td>
                                        <td style="width: 30%; font-size: 12px; background-color: #BDBDBD">CUENTA</td>
                                        <td style="width: 40%; font-size: 12px; background-color: #BDBDBD">CLABE INTERBANCARIA</td>
                                    </tr>
                                    <tr>
                                        <td style="width: 30%; font-size: 12px;">SANTANDER</td>
                                        <td style="width: 30%; font-size: 12px;">65506305031</td>
                                        <td style="width: 40%; font-size: 12px;">014150655063050317</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3"><b>Para pago en OXXO:</b></td>
                                    </tr>
                                    <tr>
                                        <td style="width: 30%; font-size: 12px; background-color: #BDBDBD">BANCO</td>
                                        <td colspan="2" style="width: 70%; font-size: 12px; background-color: #BDBDBD">CUENTA</td>
                                    </tr>
                                    <tr>
                                        <td style="width: 30%; font-size: 12px;">SANTANDER</td>
                                        <td colspan="2" style="width: 70%; font-size: 12px;">4913277000212656</td>
                                    </tr>
                                </table>
                            </td>
                        </tr><tr>
                            <td style="page-break-after:always;width: 35%;font-size: 11px;">
                                <p><b>Observaciones importantes:</b></p>
                                <ul>
                                    <li>Le solicitamos por favor antes de realizar su pago confirmar existencias.</li>
                                    <li>Si su pago va a ser por transferencia, nos puede mandar
                                        la imagen por correo, para
                                        embarcar su material, si se recibe antes de las
                                        14:00 horas, se embarca ese mismo dia.</li>
                                    <li>Si su material viaja por paqueteria será bajo la responsabilidad del destinatario.</li>
                                </ul>
                            </td>
                        </tr>
                    </table>';
        }
                
                spl_autoload_register('DOMPDF_autoload');
                //$filename=$ImpCotizacion."pdf";
                // print_r($body);exit();
                $pdf=new DOMPDF();
                //$content=$this->view->render($this->getViewScript());
                $pdf->load_html(utf8_decode($body));
                $pdf->set_paper('a4','portrait');
                $pdf->render();
                $pdf->stream($ImpCotizacion.".pdf",array( 'Attachment' => 0 ));
                $log->kardexLog("Impresion cotizacion: ".$ImpCotizacion, $ImpCotizacion,$ImpCotizacion,1,"Impresion cotizacion");
                exit();
        }/*fin de action*/
}