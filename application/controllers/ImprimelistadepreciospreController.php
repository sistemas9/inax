<?php
 ini_set("memory_limit", "1024M");
 require_once (LIBRARY_PATH.'/includes/dompdf/dompdf_config.inc.php');
 require_once (LIBRARY_PATH.'/includes/code128.php');
 setlocale(LC_MONETARY, 'es_MX');

class ImprimeListadepreciospreController  extends Zend_Controller_Inax{

 public function showpdfAction(){ 	 
          	  
   $idActual = $_POST['idactual']; 
   $idVendedor = $_POST['idvendedor']; 
   $idCliente = $_POST['idcliente'];
   $nameCliente = $_POST['namecliente'];
   $addresscliente = $_POST['addrescliente'];
   $date2      = new DateTime();
   $date       = $date2->format('d/m/Y');
   // print_r($date);
   //  exit();
   $directorio = __DIR__."/../assets/img";


             (CONFIG==DESARROLLO) ? $conn = new DB_Conexion():$conn = new DB_ConexionExport();
               $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
               $query = $conn->query("SELECT  codigo AS CODIGO, descripcion AS DESCRIPTION, unidad AS UNIDAD, precio AS PRECIO, moneda as MONEDA, CONCEPTO as CONCEPTO
                                          FROM listaPrecios WHERE idIdentificador='".$idActual."'");
               $query->execute();
               $resultadoSinUTF = $query->fetchAll(PDO::FETCH_ASSOC);
                 $consultLista = [];
                   foreach ($resultadoSinUTF as $Data){
                     array_push($consultLista, array('codigo'=>$Data['CODIGO'],
                                 'descrip'=>utf8_encode($Data['DESCRIPTION']),
                                 'unidad'=>$Data['UNIDAD'],
                                 'precio'=>$Data['PRECIO'],
                                 'moneda'=>$Data['MONEDA'],
                                 'organizacionNum'=>$Data['CONCEPTO']                     
                                    ));
                          }



           $body.='<table style="width: 100%" border="0"><tr>';
           $body.='<td style="width: 10%;"><img src="'.$directorio.'/verticalLogo.jpg" width="150px;" height="150px;"></td>
                 <td style="font-size: 12px;align-content: center;">
                    <label ><b>Avance y Tecnología en Plásticos SA de CV</b></label><br>
                     AV. WASHINGTON #3701 <br> COMPLEJO INDUSTRIAL LAS AMERICAS<br> CHIHUAHUA,CHIH,MEX 31114<br>
                     01 614 432 6100<br>
                     <a href="www.avanceytec.com.mx">www.avanceytec.com.mx</a><br>
                    </td>';

             $body.='<td style="font-size: 11px;width: 30%">
                            <label style="width: 30%; font-size: 18px;"><b>Lista de precios</b></label><br> 
                            <label><strong>Fecha: </strong></label><label>'.$date.'</label><br>                         
                        </td>
                    </tr>
                    <tr><td colspan=3><br></td></tr>
                    <tr>
                        <td style="font-size: 11px;">
                            <b>Información del cliente</b><br>
                            <label><strong>Código del cliente: </strong></label><label> <span>&nbsp;'.$idCliente.'</span></label><br>
                            <label><strong>Nombre del cliente: </strong></label><label> <span>&nbsp;'.$nameCliente.'</span></label><br>
                            <label><strong>Dirección: </strong></label><label> <span>&nbsp;'.$addresscliente.'</span></label><br>
                        </td>
                        <td style="font-size: 11px;">';
            

            // if ($comentarios != '') {
            //                 $body.='
            //                 <b>Comentarios:</b><br>
            //                 <p>'.$comentarios.'</p>';    
            //             }
                        
                        
                //         $body.='
                //         </td>
                //         <td style="font-size: 11px;">
                //             <label><b>Observaciones:</b></label><br>
                //             <label>El tipo de cambio del dia '.$date.' es de  '.$tipoCambio.' por USD</label>
                //         </td>
                //             </tr>
                //         </table>
                // <br><br>';
                       

               $body.='</table>
                <br><br>
                        <table style="width:100% ;font-size: 12px;" border="0">
                            <tr>
                                <td style="width: 15%;border-bottom: 2px solid black;">Código de artículo</td>
                                <td style="width: 40%;border-bottom: 2px solid black; text-align: left">Descripción</td>
                                <td style="width: 10%;border-bottom: 2px solid black;">Unidad de venta</td>
                                <td style="width: 10%;border-bottom: 2px solid black;"> Precio</td>
                                <td style="width: 10%;border-bottom: 2px solid black;">Moneda</td>
                            </tr>';

              foreach ($consultLista as $item) {    
              $body.= '
                    <tr>
                        <td style="vertical-align:top;">'.$item['codigo'].'</td>
                        <td style="vertical-align:top;">'.$item['descrip'].'</td>
                        <td style="vertical-align:top;">'.$item['unidad'].'</td>
                        <td style="text-align:left">'.$item['precio'].'</td>
                        <td style="text-align:left">'.$item['moneda'].'</td>
                    </tr>';    
               }

            $body.='</table>
                  <br><br>
                  <br><br>
                        <table style="width:100% ;font-size: 12px;" border="0">
                            <tr>
                               <td colspan="7">Vendedor: <b>'.$idVendedor.'</td>
                            </tr>';


              // $body.='<tr>
              //       <td colspan="5" style="text-align: right;"><b>Subtotal '.$moneda.'</b></td>
              //       <td colspan="2" style="text-align: right;">$' . $subTotalCotE[1] . '</td>
              //           </tr>
              //           <tr>
              //               <td colspan="5" style="text-align: right;"><b>IVA '.$moneda.'</b></td>
              //               <td colspan="2" style="text-align: right;">$' . $ivaTotalCotE[1] . '</td>
              //           </tr>
              //           <tr>
              //               <td colspan="5" style="text-align: right;"><b>Total '.$moneda.'</b></td>
              //               <td colspan="2" style="text-align: right;">$' . $totalCotE[1] . '</td>
              //           </tr>
              //       </table>';

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
                                <td style="width: 20%; font-size: 12px;">'.$item['organizacionNum'].'</td>
                            </tr>
                        </table>
                    </td> 
                </tr><tr>
                    <td style="page-break-after:always;width: 35%;font-size: 11px;">
                        <p style="font-size:10;">NOTA.-** Para pago en cajero/otro servicios utilizar el convenio #4944, los precios no incluyen IVA </<p>
                        </br>
                        <p><b>Observaciones importantes:</b></p>
                        <ul>
                            <li>Le solicitamos por favor antes de realizar su pago confirmar existencias.</li>
                            <li>Si su pago va a ser por transferencia, nos puede mandar
                                la imagen por correo o WhatsApp (+52 614 432 6100), para
                                embarcar su material, si se recibe antes de las
                                14:00 horas, se embarca ese mismo dia.</li>
                            <li>Si su material viaja por paqueteria será bajo la responsabilidad del destinatario.</li>
                            <li>Le recordamos qué las cotizaciones solo cuentan con vigencia de 24 horas, posterior a esto se deberá de cotizar de nuevo forzosamente.</li>
                        </ul>
                    </td>
                </tr>
            </table>';     


                spl_autoload_register('DOMPDF_autoload');
                 $filename="listaPrecios";
                 // print_r($body);exit();
                 $pdf=new DOMPDF();
                 //$content=$this->view->render($this->getViewScript());
                 $pdf->load_html(utf8_decode($body));
                 $pdf->set_paper('a4','portrait');
                 $pdf->render();
                $pdf->stream("ejemplo.pdf",array( 'Attachment' => 0 ));
                // $log->kardexLog("Impresion cotizacion: ".$ImpCotizacion, $ImpCotizacion,$ImpCotizacion,1,"Impresion cotizacion");
                exit();

  }
}


?>