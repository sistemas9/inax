<?php
require_once (LIBRARY_PATH.'/includes/dompdf/dompdf_config.inc.php');
require_once (LIBRARY_PATH.'/includes/code128.php');
setlocale(LC_MONETARY, 'es_MX');
// function money_format(){
//     return ':)';
// }
// Cotizacion

class ImpresionOrdenController extends Zend_Controller_Action{
    
    public function init(){
        if(empty(COMPANY)){
            $this->_redirect('/login');
        }
    }

    public function getPaymModeName($paymMode){
        $paymentArray = Application_Model_Userinfo::getPayModeInController();
        $paymentDescription = array_filter( $paymentArray->value, function($pay) use($paymMode){
            if($pay->Name === $paymMode){
                return $pay;
            }            
        });
        $paymentDescription = array_values($paymentDescription);
        return $paymentDescription;
    }

    public function indexAction(){

        $db = new Application_Model_UserinfoMapper();
        $adapter = $db->getAdapter();

        $subtotalX  =   0;
        $body   =   '';
        $flag   =   false;
        
        if(COMPANY=="ATP"){
            $flag=true;
        }

        $this->_helper->layout()->disableLayout();
        $model = new Application_Model_OrdenModel();
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

        $cabecera   =   $model->getCabezeraOrden(strtoupper($ImpCotizacion));

        $startWith = substr($ImpCotizacion, 0, 2);
        if ($startWith == 'OV' || $startWith == 'AT') {
            $ImpCotizacion = $cabecera['QUOTATIONNUMBER'];
            $tipoDoc = 'ORDEN DE VENTA';
        }
        else {
            $tipoDoc = 'ORDEN DE VENTA.';
        }

        if ($ImpCotizacion == '') {
            $ImpCotizacion = 'Sin Cotización';
        }
        // $impuestos  =   $cabecera['impuestos'];
        $impuestos   =  $cabecera['LINES'][0]->SalesTaxGroupCode;
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
            $tipoDoc = 'ORDEN DE VENTA .';
        }
        else {
            $tipoDoc = 'ORDEN DE VENTA';
        }

	    $date2          =   new DateTime();
        $date           =   $date2->format('d/m/Y');
        $moneda         =   $cabecera['Moneda'];
       	$tipoCambio     =   $_SESSION['tipoC'];
	    $directorio     =   __DIR__."/../assets/img";
        $fecha          =   explode('T',$cabecera['Fecha']);
        $fechaYMD       =   explode('-',$fecha[0]);
        $fechaHMS       =   explode('Z',$fecha[1]);
        $fecha2         =   explode('T',$cabecera['FechaVencimiento']);
        $fechaYMD2      =   explode('-',$fecha2[0]);
        $fechaHMS2      =   explode('Z',$fecha2[1]);
        $fechaCot       =   $fechaYMD[2] . '/' . $fechaYMD[1] . '/' . $fechaYMD[0];
        $fechaVenc      =   $fechaYMD2[2] . '/' . $fechaYMD2[1] . '/' . $fechaYMD2[0];
        $payDescription = self::getPaymModeName($cabecera['PaymentMethodName']);


        /* VISTA */

        if ($flag) {
        
            $trBank = '
               <!-- <tr>
                    <td style="width: 30%; font-size: 12px;">SANTANDER</td>
                    <td style="width: 30%; font-size: 12px;">65174302218</td>
                    <td style="width: 40%; font-size: 12px;">014150651743022180</td>
                     <td style="width: 40%; font-size: 12px;"></td>
                    <td style="width: 40%; font-size: 12px;"></td>
                </tr>-->
                <tr>
                    <td style="width: 30%; font-size: 12px;">SANTANDER</td>
                    <td style="width: 30%; font-size: 12px;">65504316526</td>
                    <td style="width: 40%; font-size: 12px;">014150655043165264</td>
                    <td style="width: 40%; font-size: 12px;">'.$organizationNumber.'</td>
                </tr>';

            $liWhatsApp = ' o WhatsApp (+52 614 432 6111)';

            $imgHeader = $directorio.'/verticalLogo.jpg';

            $labelHeader  = '
                <label style="font-family:Helvetica;">
                    <b>Avance y Tecnología en Plásticos SA de CV</b>
                </label>
                <br>
                AV. WASHINGTON #3701 <br> COMPLEJO INDUSTRIAL LAS AMERICAS<br> CHIHUAHUA,CHIH,MEX 31114<br>
                01 614 432 6100<br>
                <a href="www.avanceytec.com.mx">www.avanceytec.com.mx</a><br>';

        }else {

            $trBank = '
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
                </tr>';

            $liWhatsApp = '';

            $imgHeader = $directorio.'/lideart200x200.jpg';

            $labelHeader  = '
                <label>
                    <b>LIDEART INNOVACIÓN S DE R.L DE C.V</b>
                </label><br>
                <img src="'.$directorio.'/imagepdf/location_on_1x.jpg"> CALLE WASHINGTON #3701 INT. 48-H <br>COMPLEJO INDUSTRIAL LAS AMERICAS<br> CHIHUAHUA,CHIH,MEX 31114<br>
                <img src="'.$directorio.'/imagepdf/call_1x.jpg"> 01 614 432 6122<br>
                <img src="'.$directorio.'/imagepdf/public_1x.jpg"><a href="https://lideart.com.mx/">lideart.com.mx/</a><br>';
        }


        $body.='
            <table style="width: 100%" border="0; font-family:Helvetica">
                <tr>
                    <td style="width: 10%;">
                        <img src="' . $imgHeader . '" width="150px;" height="150px;">
                    </td>
                    <td style="font-size: 12px;align-content: center;">
                     ' . $labelHeader . '
                    </td>
                    <td style="font-size: 11px;width: 30%">
                        <label style="width: 30%; font-size: 18px;"><b>'.$tipoDoc.'</b></label><br>
                        <label>Orden: </label><label>'.$ov.'</label><br>
                        <label>Cotización: </label><label>'.$ImpCotizacion.'</label><br>
                        <label>Fecha: </label><label>'.$fechaCot.'</label><br>
                        <label>Pago: </label><label>'.$cabecera['Pago'].'</label><br>
                        <label><u>Modo de Entrega: </u></label><label style="font-weight:bolder;">'.$cabecera['DeliveryModeCode'].'</label><br>
                        <label><u>Modo de Pago: </u></label><label>'.$payDescription[0]->Description.'</label>
                    </td>
                </tr>
                <tr>
                <td colspan=3>
                    <br>
                </td>
                </tr>
                <tr>
                    <td style="font-size: 11px;">
                        <b>Direccion de envío:</b>
                        <br>
                        <p>
                            <label >'.$sohInvoiceCustomerAccountNumber. ' '. $cabecera['NombreEntrega'].'</label><br>
                            <label >'.$cabecera['CALLE'].'</label><br>
                            <label >'.$cabecera['COLONIA'].'</label>
                            <label >'.$cabecera['CIUDAD'].','. $cabecera['ESTADO'] . ', ' . $cabecera['PAIS'].'</label>
                            <label>'.$cabecera['ZIPCODE'].'</label>
                        </p>
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
                    <th style="width: 15%; border-bottom: 2px solid black; text-align: left">
                        Código de artículo
                    </th>
                    <th style="width: 40%; border-bottom: 2px solid black; text-align: left">
                        Descripción
                    </th>
                    <th style="width:  5%; border-bottom: 2px solid black; text-align: right;">
                        Cantidad
                    </th>
                    <th style="width: 10%; border-bottom: 2px solid black; text-align: left;">
                        Unidad
                    </th>
                    <th style="width: 10%; border-bottom: 2px solid black; text-align: right;">
                        P.Unitario
                    </th>
                    <th style="width:  5%; border-bottom: 2px solid black; text-align: right;">
                        Descuento
                    </th>
                    <th style="width: 10%; border-bottom: 2px solid black; text-align: right;">
                        Importe
                    </th>
                </tr>'; 

            ////////////Consultar si tiene comentario la la cotizacion//////////////////////////
            (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();  
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $queryStrCom = "SELECT COUNT(*) AS cuantas FROM AYT_ComentariosLineas WHERE documentId = '".$cabecera['OV']."' AND comentario != '';";
            $query = $conn->prepare($queryStrCom);
            $query->execute();
            $resultCuantosCom = $query->fetchAll(PDO::FETCH_ASSOC);
            $conComentarios = false;
            if ($resultCuantosCom[0]['cuantas'] > 0){
                $conComentarios = true;
                $queryStrIDLI = "SELECT inventoryLotId FROM AYT_ComentariosLineas WHERE documentId = '".$cabecera['OV']."' AND comentario != '';";
                $queryIDLI = $conn->prepare($queryStrIDLI);
                $queryIDLI->execute();
                $resultIDLI = $queryIDLI->fetchAll(PDO::FETCH_ASSOC);
            };
            ///////////////////////////////////////////////////////////////////////////////////
            for ($i=0; $i < count($items) ; $i++) {

                $lineSequenceNumber = $items[$i]->LineCreationSequenceNumber;
                $inventoryLotId = $items[$i]->InventoryLotId;
                
                if ($conComentarios && in_array(array('inventoryLotId' => $inventoryLotId), $resultIDLI)){
                    $qC = "SELECT * FROM dbo.AYT_ComentariosLineas WHERE LineSequenceNumber = $lineSequenceNumber AND inventoryLotId = '$inventoryLotId'";
                    $q2 = $adapter->query($qC);
                    $q2->execute();
                    $result2 = $q2->fetchAll(PDO::FETCH_ASSOC);
                    $cabecera['ComentarioLinea'] = $result2[0]['comentario'];
                }else{
                    $cabecera['ComentarioLinea'] = '';
                }
                
                $punitario=round($items[$i]->SalesPrice,2);
                $importe=round($items[$i]->LineAmount,2);
                if($cargo==0){
                    $punitario=$items[$i]->SalesPrice;
                    $importe=$items[$i]->LineAmount;
                }

                $fpunitario = money_format('%i', round($punitario,2));
                $fimporte = money_format('%i', round($importe,2));
                
                $fpunitarioE = explode('MXN',$fpunitario);
                $fimporteE = explode('MXN',$fimporte);
                $cant = 0;
                $cant = $items[$i]->OrderedSalesQuantity;

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
                            <b>Cantidad:</b> '.$cant.' <b>Sitio:</b> '.$items[$i]->ShippingSiteId.' <b>Almacen:</b> '.$items[$i]->ShippingWarehouseId.' <br>
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
                
                // print_r($res);
                // print_r('<hr>');
                // print_r($importeConsul);
                // print_r('<hr>');
                // print_r($importeManual);
                // print_r('<hr>');
                // print_r($cargoValidate);
                // print_r('<hr>');
                // print_r(round($items[$i]->LineDiscountPercentage,2)/100);                

                if ($res <= 1.5 && $res >= -1.5) {
                 
                }
                else {
                #   exit('<p style="text-align:center; font-family: calibri;">Favor de refrescar la página<p><hr>');
                }

                $subtotalX += $importe;
	       }          
// exit();
        $iva = 1.16;   
        if($impuestos=='FRONT'){
              $iva = 1.08;
        } 
        $subTotalCot = money_format('%i', $subtotalX);
        $ivaTotalCot = money_format('%i', round($subtotalX*($iva-1),2));
        $totalCot = money_format('%i', round($subtotalX*$iva,3));

        $subTotalCotE = explode('MXN',$subTotalCot);
        $ivaTotalCotE = explode('MXN',$ivaTotalCot);
        $totalCotE = explode('MXN',$totalCot);

        $body.='
                <tr>
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

            $body.='
            <table style="width: 100%">
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
                                <td style="width: 40%; font-size: 12px; background-color: #BDBDBD">REFERENCIA BANCARIA</td>
                            </tr>' . $trBank . '
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="page-break-after:always;width: 35%;font-size: 11px;">
                        <p>NOTA.-** Para pago en cajero/otro servicios utilizar el convenio #4944</p>
                        <p>
                            <b>Observaciones importantes:</b>
                        </p>
                        <ul>
                            <li>Le solicitamos por favor antes de realizar su pago confirmar existencias.</li>
                            <li>Si su pago va a ser por transferencia, nos puede mandar la imagen por correo' . $liWhatsApp . ', para embarcar su material, si se recibe antes de las 14:00 horas, se embarca ese mismo dia.</li>
                            <li>Si su material viaja por paqueteria será bajo la responsabilidad del destinatario.</li>
                        </ul>
                    </td>
                </tr>
            </table>';

                spl_autoload_register('DOMPDF_autoload');
                
                $pdf=new DOMPDF();
                $pdf->load_html(utf8_decode($body));
                $pdf->set_paper('a4','portrait');
                $pdf->render();
                $pdf->stream($ImpCotizacion.".pdf",array( 'Attachment' => 0 ));
                $log->kardexLog("Impresion cotizacion: ".$ImpCotizacion, $ImpCotizacion,$ImpCotizacion,1,"Impresion cotizacion");
                exit();
        }
}