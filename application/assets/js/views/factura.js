/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$("#barraProgreso").remove();
$("#menuSuperior").remove();

function consulta(){
    var folio=$('input[name=factura]').val();
    var tipo=$('select[name=tipo]').val();
    if(folio!=''){
        switch(tipo) {
            
            case 'c':
                  $("#resultado").removeClass();
                  $("#resultado").addClass("alert alert-success alert-dismissable");

                  var res = folio.split('-');
                  console.log(res[0]);
                  if (res[0] == 'OV') {
                    $("#resultado").html('Necesita ingresar una Cotización');
                  }
                  else {
                    $("#resultado").html('¡Cotización encontrada! podras consultar en el siguiente enlace.  <a class="btn btn-warning" href="./impresion-cotizacion/?id='+folio+'" target="_blank">'+folio+'</a>');
                  }


                break;
            case 'r':
                $("#resultado").removeClass();
                $("#resultado").addClass("alert alert-success alert-dismissable");
                $("#resultado").html('¡Remisión encontrada! podras consultar en el siguiente enlace.  <a class="btn btn-warning" href="./impresion-orden/?id='+folio+'" target="_blank">'+folio+'</a>');
                // $("#resultado").html('¡Remisión encontrada! podras consultar en el siguiente enlace.  <a class="btn btn-warning" href="./impresion/?PackingSlipId='+folio+'" target="_blank">'+folio+'</a>');
                break;
            case 'f':
                $.ajax({
                    url: "factura/consulta",type: "POST",dataType: 'json', data: {'folio': folio},
                    beforeSend: function (xhr) {
                        $("#resultado").removeClass();
                        $("#resultado").addClass("alert alert-info");
                        $("#resultado").html('procesando datos... ');
                    },
                    success: function (data) {
                        console.log(data);
                        // console.log((fecha.getMonth()+1)+"/"+fecha.getDate()+"/"+fecha.getFullYear());
                        if(data.InvoiceNumber !== ''){
                            $("#resultado").removeClass();
                            $("#resultado").addClass(" ");
                            var htmlResult = '';
                            data.value.forEach(element => {
                                fecha = new Date(element.InvoiceDate);
                                fecha = ((fecha.getMonth() + 1) + "/" + fecha.getDate() + "/" + fecha.getFullYear());
                                htmlResult += '<div class="alert alert-success alert-dismissable">¡Factura encontrada! podras consultar la factura en el siguiente enlace.  <a class="btn btn-warning" href="http://inax.aytcloud.com/Facturacion365/Facturacion365.php?ov=' + folio + '&tipo=inax&invoiceId=' + element.InvoiceNumber + '&transDate=' + fecha + '&custInvoiceAccount=' + element.InvoiceCustomerAccountNumber + '" target="_blank">' + element.InvoiceNumber + '</a></div>';
                            });
                            $("#resultado").html(htmlResult);
                        }                                                                                                                           
                        else{
                            $("#resultado").removeClass();
                            $("#resultado").addClass("alert alert-danger alert-dismissable");
                            $("#resultado").html('¡Factura <b>no</b> encontrada! Favor de verificar el folio de factura');

                        }
                    },
                    error: function (x){
                        $("#resultado").removeClass();
                        $("#resultado").addClass("alert alert-danger alert-dismissable");
                        $("#resultado").html('<b>Error</b> la petición favor de intentar de nuevo si el problema persiste favor de reportarlo a sistemas');
                    }
                });
            break;
            case 'x':
                $.ajax({
                    url: "factura/getxml",type: "POST",dataType: 'json', data: {'folio': folio},
                    beforeSend: function (xhr) {
                        $("#resultado").removeClass();
                        $("#resultado").addClass("alert alert-info");
                        $("#resultado").html('procesando datos... ');
                    },
                    success: function (data) {
                        console.log(data);
                           $("#resultado").removeClass();
                            $("#resultado").addClass("alert alert-success alert-dismissable");
                            $("#resultado").html('¡Factura encontrada! podras consultar la factura en el siguiente enlace.  <a class="btn btn-warning" href="http://svr02:8989/Facturacion365/Facturacion365.php?ov='+folio+'&tipo=inax&invoiceId='+data.value[0].InvoiceNumber+'&transDate='+fecha+'&custInvoiceAccount='+data.value[0].InvoiceCustomerAccountNumber+'&type=xml" target="_blank" download>'+data.value[0].InvoiceNumber+'</a>');
                            $("#resultado").html('¡Factura encontrada! podras consultar la factura en el siguiente enlace.  <a class="btn btn-warning" href="'+data+'" target="_blank" download>Descargar XML</a>');
                             
                    },
                    error: function (x){
                        $("#resultado").removeClass();
                        $("#resultado").addClass("alert alert-danger alert-dismissable");
                        $("#resultado").html('<b>Error</b> la petición favor de intentar de nuevo si el problema persiste favor de reportarlo a sistemas');
                    }
                });
                break;
            default:
                
        }
        if(tipo=='c'){
            
        }
        else{
            
        }
    }
    else{
        $("#resultado").removeClass();
        $("#resultado").addClass("alert alert-warning alert-dismissable");
        $("#resultado").html('<b>¡Alto!</b> Folio de documento no debe estar en blanco');
        $('input[name=factura]').focus();
    }
}