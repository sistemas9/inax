<?php

require_once (LIBRARY_PATH.'/includes/dompdf/dompdf_config.inc.php');
require_once (LIBRARY_PATH.'/includes/code128.php');

class ImpresionReciboController extends Zend_Controller_Action{
    
    public function init(){
        if(empty(COMPANY)){
            $this->_redirect('/login');
        }
    }

    public function indexAction(){

        $subtotalX = 0;
	    $body      = '';
        $flag=false;
        if(COMPANY=="ATP"){$flag=true;}
        // action body
        $this->_helper->layout()->disableLayout();
        $model = new Application_Model_ReciboModel();

        $log = new Application_Model_Userinfo();
        date_default_timezone_set('America/Chihuahua');



        if(isset($_GET['id'])){
            $ImpDiario      = filter_input(INPUT_GET,'id');
            $ImpCotizacion  = filter_input(INPUT_GET,'diario');
        }
        else {

        }


        if(empty($ImpCotizacion)){ echo "NO SE RECIBIO EL FOLIO DE COTIZACION"; exit();}
        // $cargo=$model->getCargo($ImpCotizacion);
        // $cargo=  round($cargo[0]['VALUE'], 2);
        // $cabecera=$model->getCabezeraCotizacion($ImpCotizacion);
        $cabecera = $model->getCabezeraCotizacion($ImpCotizacion);
        $cargo = $cabecera['cargos'];
        $idDirec=$cabecera['vendedor'];
        $vendedor=$model->getVendedor($idDirec);
        //$Cotizacion = $model->getDireccion($ImpCotizacion);
        //$items =$model->getItems($ImpCotizacion);
        $items =$cabecera['LINES'];
        $pago =$cabecera['PAGO'];
        //$almacen=$model->getAlmacenCotiza($items[0]['almacen']);
	    $date2    = new DateTime();
        $date =$date2->format('d/m/Y');
        //$moneda=$items[0]['Moneda'];
        $moneda=$cabecera['Moneda'];
       	$tipoCambio = $_SESSION['tipoC'];
	    $directorio=__DIR__."/../assets/img";

        $fecha = explode('T',$cabecera['Fecha']);
        $fechaYMD = explode('-',$fecha[0]);
        $fechaHMS = explode('Z',$fecha[1]);

        $fecha2 = explode('T',$cabecera['FechaVencimiento']);
        $fechaYMD2 = explode('-',$fecha2[0]);
        $fechaHMS2 = explode('Z',$fecha2[1]);


        $fechaCot = $fechaYMD[2] . '/' . $fechaYMD[1] . '/' . $fechaYMD[0];//. ' ' . $fechaHMS[0], //"15/11/2018 14:59:34",
        $fechaVenc = $fechaYMD2[2] . '/' . $fechaYMD2[1] . '/' . $fechaYMD2[0];//. ' ' . $fechaHMS[0], //"15/11/2018 14:59:34",

        $body.='<br>';

        $body.='<table style="width: 100%">
                <tr>';
        if($flag){
         $body.='<td style="width: 15%;"><img src="'.$directorio.'/verticalLogo.jpg" width="150px;" height="150px;"></td>
                <td style="font-size: 12px;align-content: center;">
                    <label ><b>Avance y Tecnología en Plásticos SA de CV</b></label><br>
                    AV. WASHINGTON #3701 <br> COMPLEJO INDUSTRIAL LAS AMERICAS<br> CHIHUAHUA,CHIH,MEX 31114<br>
                    01 614 432 6100<br>
                    <a href="www.avanceytec.com.mx">www.avanceytec.com.mx</a><br>
                </td>';
        }
        else{
            $body.='<td style="width: 15%;"><img src="'.$directorio.'/lideart200x200.jpg" width="150px;" height="150px;"></td>
                    <td style="font-size: 12px;align-content: center;">
                        <label ><b>LIDEART INNOVACIÓN S DE R.L DE C.V</b></label><br>
                        <img src="'.$directorio.'/imagepdf/location_on_1x.jpg"> CALLE WASHINGTON #3701 INT. 48-H <br> COMPLEJO INDUSTRIAL LAS AMERICAS<br> CHIHUAHUA,CHIH,MEX 31114<br>
                        <img src="'.$directorio.'/imagepdf/call_1x.jpg"> 01 614 432 6122<br>
                        <img src="'.$directorio.'/imagepdf/public_1x.jpg"><a href="https://lideart.com.mx/">lideart.com.mx/</a><br>
                    </td>';
        }

        $body.='
        <td style="font-size: 12px;width: 5%">&nbsp;</td>
        <td style="font-size: 12px;width: 25%">
            <b>Direccion de envío:</b><br>
            <label >'.$cabecera['NombreEntrega'].'</label><br>
            <label >'.$cabecera['CALLE'].'</label><br>
            <label >'.$cabecera['COLONIA'].'</label><br>
            <label >'.$cabecera['CIUDAD'].','. $cabecera['ESTADO'] . ', ' . $cabecera['PAIS'].'</label><br>
            <label>'.$cabecera['ZIPCODE'].'</label>

        </td>

        <td style="font-size: 12px;width: 25%">
                            <label style="width: 20%; font-size: 18px;"><b>*RECIBO</b></label><br>
                            <label>Número: </label><label>'.$ImpCotizacion.'</label><br>
                            <label>Fecha: </label><label>'.$fechaCot.'</label><br>
                            <label>Fecha de Vencimiento: </label><label>'.$fechaVenc.'</label><br>
                            <label>Pago: </label><label>'.$cabecera['Pago'].'</label>
                        </td>
                    </tr>
                </table>
                <br><br>
                        <table style="width:100% ;font-size: 12px;">'; 


            for ($i=0; $i < count($items) ; $i++) {
                $punitario=round($items[$i]->SalesPrice,2);
                $importe=round($items[$i]->LineAmount,2);
                if($cargo==0){
                    $punitario=$items[$i]->SalesPrice;
                    $importe=$items[$i]->LineAmount;
                }
        		
                $subtotalX += $importe;
	       }          

$reciboSubTotal = round($subtotalX,2);
$reciboIva = round($subtotalX*0.16,2);
$reciboTotal = round($subtotalX*1.16,3);
$reciboVendedor = $vendedor['NAME'];

    $body.='
        <table style="width: 25%; font-size:10px;" border="0">
        <tr><th style="text-align:left">Subtotal</th><td> ' . $reciboSubTotal . '</td></tr>
        <tr><th style="text-align:left">IVA</th><td> ' . $reciboIva . '</td></tr>
        <tr><th style="text-align:left">TOTAL</th><td> ' . $reciboTotal . '</td></tr>
        <tr><th style="text-align:left">Vendedor</th><td> ' . $reciboVendedor . '</td></tr>
        </table>
    ';

    if($flag){    
        $body.='<table style="width: 100%">
                <tr>
                    <td style="width: 100%;">
                    </td> 
                </tr><tr>
                    <td style="page-break-after:always;width: 35%;font-size: 11px;">
                        <p></p>
                    </td>
                </tr>
            </table>';
    }
        else{
        }

        $body.= '
            <br><br>
            <table style="width:100%; font-size: 12px; font-family:calibri;">
            <tr>
                <th style="text-align:left">Fecha</th>
                <th style="text-align:left">Voucher</th>
                <th style="text-align:left">Empresa</th>
                <th style="text-align:left">Monto</th>
                <th style="text-align:left">Moneda</th>
                
                
            </tr>
        ';

        for ($i=0; $i < count($pago) ; $i++) {
                $transactiondate = $pago[$i]->TransactionDate;

                $transactiondateE = explode('-', $transactiondate);
                $transactiondateE2 = explode('T', $transactiondateE[2]);                
                $transactiondate = $transactiondateE2[0] . '-' . $transactiondateE[1] . '-' . $transactiondateE[0];

                $voucher = $pago[$i]->Voucher;
                $dataareaid = $pago[$i]->dataAreaId;
                $creditamount = $pago[$i]->CreditAmount;
                $currencycode = $pago[$i]->CurrencyCode;
                $offsetaccounttype = $pago[$i]->OffsetAccountType;
                $offsetaccountdisplayvalue = $pago[$i]->OffsetAccountDisplayValue;

                // <td>' . $offsetaccounttype . '</td>
                // <td>' . $offsetaccountdisplayvalue . '</td>
                $body.= '
                <tr>
                    <td>' . $transactiondate . '</td>
                    <td>' . $voucher . '</td>
                    <td>' . $dataareaid . '</td>
                    <td>' . $creditamount . '</td>
                    <td>' . $currencycode . '</td>
                
                </tr>
                ';
           }
        $body.= '</table>';
                // print_r($body);
                // exit();

                spl_autoload_register('DOMPDF_autoload');
                //$filename=$ImpCotizacion."pdf";
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

