$(document).ready(function() {
    gaTrigger('Index','Ready',userBranchOffice);
});
let labelOvClicked = '';
$(document).on('focusin','.generarOV',function(){
    $(this).removeClass('darken-3');
    $(this).addClass('darken-5');
});
$(document).on('focusout','.generarOV',function(){
    $(this).removeClass('darken-5');
    $(this).addClass('darken-3');
});
$(document).on('focusin','.generarCTZN',function(){
    $(this).removeClass('darken-3');
    $(this).addClass('darken-5');
});
$(document).on('focusout','.generarCTZN',function(){
    $(this).removeClass('darken-5');
    $(this).addClass('darken-3');
});        
$('#breadInicio').hide();
$('#breadArticulos').hide();        
$('#breadResumen').hide();
$('#editarDocument').hide();
if ($('#divInicioSesion').hasClass('s5')){
    $('#divInicioSesion').removeClass('s5');
    $('#divInicioSesion').addClass('s9');
}else if ($('#divInicioSesion').hasClass('s9')){
   $('#divInicioSesion').removeClass('s9');
   $('#divInicioSesion').addClass('s9');
}

$("#todas").on("click", function(){
    fTodasOv(0,0);                
});

$("#usuarioov").on("click", function(){
    $('#pasaForm').html('');
    $("nav").find(".active").removeClass("active");
    $(this).parent().addClass("active"); 
    $('#modalLoading').openModal();
    $.ajax({
        url: "index/ovUser2",type: "POST",dataType: 'json',
        beforeSend: function (xhr) {
            $('#modalLoading').openModal({dismissible: false});
            checkTableisDataTable();
        },
        success: function (data, textStatus, jqXHR) {
            $('#infoPalette').show();
            $('#ov').DataTable({
                "destroy": true,
                "search":true,
                "paging": true, 
                "info": false,
                "order": [[ 1, "desc" ]],
                data: data,
                columns: [ 
                    {title: "Detalle"},
                    {title: "Orden de Venta"},
                    {title: "Fecha"},
                    {title: "Código Cliente"},
                    {title: "Cliente"},
                    {title: "Sitio"},
                    {title: "Almacén"},
                    {title: "Modo de Entrega"},
                    {title: "Vendedor"},
                    {title: "Estado de Entrega"},
                    {title: "Generar Remisión"},
                    {title: "Imprimir"}
                ]
            });
            $('#modalLoading').closeModal();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $('#modalLoading').closeModal();
        }
    });                
});

$("#misCot").on("click", function(){
    $('#pasaForm').html('');
    $("nav").find(".active").removeClass("active");
    $(this).parent().addClass("active"); 
    $('#modalLoading').openModal();
    $.ajax({
        url: "index/misCOT2",type: "POST",dataType: 'json',
        beforeSend: function (xhr) {
            $('#modalLoading').openModal({dismissible: false});
            checkTableisDataTable();
        },
        success: function (data, textStatus, jqXHR) {
            
            $('#ov').DataTable({
                "destroy": true,
                "search":true,
                "paging": true, 
                "info": false,
                "order": [[ 1, "desc" ]],
                data: data,
                columns: [ 
                    {title: "Detalle"},
                    {title: "Cotización"},
                    {title: "Fecha"},
                    {title: "Código Cliente"},
                    {title: "Cliente"},
                    {title: "Sitio"},
                    {title: "Almacén"},
                    {title: "Modo de Entrega"},
                    {title: "Estado"},
                    {title: "Convertir OV"}
                ]
            });
            $('#modalLoading').closeModal();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $('#modalLoading').closeModal();
        }
    });         
});

$("#todasCot").on("click", function(){
    fTodasCot(0,0);
});

$('#misCotOff').on('click', function(){
    $.ajax({
        url        : 'index/misCotOff',
        type       : 'POST',
        dataType   : 'json',
        beforeSend : function(){
            $('body').attr('data-msj', 'Procesando Petición ...');
            $('body').addClass('load-ajax');
        },
        success    : function(data){
            if (data != 'online'){
                $('#ov').DataTable({
                    "destroy"   : true,
                    "search"    : true,
                    "paging"    : true, 
                    "info"      : false,
                    "order"     : [[ 1, "desc" ]],
                    data        : data,
                    columns     : [ 
                                    {title: "Cotización"},
                                    {title: "Fecha"},
                                    {title: "Código Cliente"},
                                    {title: "Cliente"},
                                    {title: "Sitio"},
                                    {title: "Almacén"},
                                    {title: "Modo de Entrega"},
                                    {title: "Convertir OV"},
                                    {title: "OV-Relacionada"}
                                ],
                    createdRow  : function( row, data, dataIndex ) {
                                        if(data[9] == 2){
                                            $(row).css('background-color','#5E35B1');
                                            $(row).css('color','white');
                                        }
                                    }
                });
                $('body').removeClass('load-ajax');
            }else{
                swal("Atencion.","Esta funcion esta disponible solo cuando el sistema este en linea nuevamente:","error");
                $('body').removeClass('load-ajax');
            }
        }
    });
});

/* FUNCIONES */

function checarDatosCliente(){
        var bandDtCte = true;
        $('.datoEtiquetaCliente').each(function(){
            valor = $(this).val();
            if ($(this).val() === '' || $(this).val() === 'null' || $(this).type === 'undefined'){
                bandDtCte = false;
                return bandDtCte;
            }
        });
        return bandDtCte;
}
function setPayMode(payMent,payMode){
    var payArr=[];
        if(payMent==='CONTADO'){
            $.each(payModeList,function (i,v){
                if(v.PAYMMODE!='99'){
                   payArr.push(v); 
                }
            });            
        }
        else{
            payArr.push({PAYMMODE: "99", name: "OTROS"}); 
        }
    var html='';
    $('#cargo').html(html);
    $.each(payArr,function (i,v){
        var sel='';
        if(v.PAYMMODE===payMode){sel='selected';}
        html+='<option value="0.00" data-paymmode="'+v.PAYMMODE+'" '+sel+'>'+v.name+'</option>';
    });
    $('#cargo').html(html);
}
function factura2(ov,rem,entrega){
    $('#domicilioF').val(entrega);
    $('#ovLbl').html(ov);
    $('#remLbl').html(rem);
    $('#btnDiarioIndex').remove();
    var factura=$('#loadFacturaSt').text();
    if(factura!==""){
        $('#loadFacturaSt').html('');
        $('#btnFacturar').show();
    }
    $.ajax({url:"index/get-Data-Ov",type: 'POST',data: {ov:ov},dataType: 'json',
        beforeSend: function (xhr) {
        },
        success: function (data, textStatus, jqXHR) {
            var htmlFP='';
            $.each(payTerm,function (i,v){
                var sel='';
                if(v.PAYMTERMID===data[0].PAYMENT || v.PAYMTERMID==='CONTADO' || v.PAYMTERMID==='CONTADO PD'){
                    if(v.PAYMTERMID===data[0].PAYMENT){sel='selected=""';}                
                    htmlFP+='<option  value="'+v.PAYMTERMID+'" '+sel+'>'+v.PAYMTERMID+' - '+v.DESCRIPTION+'</option>';
                }
            });
            $('#entregaF').html(htmlFP);
            paymode=data[0].PAYMMODE;
            setPayMode(data[0].PAYMENT,paymode);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            Materialize.toast("hubo un error "+catchError(jqXHR,errorThrown),'red');
        }
    });    
    $.ajax({
            url:"inicio/get-Direcciones",type:"post", data:{ov:ov},dataType: 'json',
            beforeSend: function (xhr) {
                $('#btnFacturar').hide();
            },
            success: function (data, textStatus, jqXHR) {
                var str="";
                $('#direccionF').html(str);
                $.each(data,function (i,v){
                    str+='<option value="'+v.RECID+'">'+v.ADDRESS+'</option>';
                });
                $('#direccionF').html(str);
                $('#btnFacturar').show();
            }            
        });
        $('#modalFactura').openModal({dismissible: false});
}
function facturarClose(){
    var factura=$('#loadFacturaSt').text();
    if(factura!==""){
        $('#usuarioov').click();
    }
    $('#modalFactura').closeModal();
}
function facturar(){
    if(havePermision(14) && $('#remLbl').text()===''){
        var ov=$('#ovLbl').text();
        ValidarLimiteCreditoRemision(ov,usuario,'');
    }
    else{
        if(havePermision(1)){
            $.ajax({
                url:"inicio/facturar",type:"post", data:{
                    ov:$('#ovLbl').text(),
                    remision:$('#remLbl').text(),
                    ordenCliente:$('#OrdenCliente').val(),
                    refCliente:$('#ReferenciaCliente').val(),
                    comentariosCabecera:$('#comentariosCabecera').val(),
                    direccion:$('#direccionF').val(),
                    usoCFDi:$('#usoCFDI option:selected').val(),
                    pagoModo:$('#tipoCobro option:selected').attr('data-paymmode'),
                    pago:$('#entregaF').val()
                },dataType: 'json',
                beforeSend: function (xhr) {
                    $('#loadFacturaSt').html('<img src="../application/assets/img/cargando.gif" style="width: 1em;">');
                    $("#btnFacturar").hide();
                },
                success: function (data, textStatus, jqXHR) {
                    if(data.resultado=="ok"){
                        Materialize.toast("Factura "+data.respuesta,10000);
                        $('#loadFacturaSt').html('<a href="http://svr02:8989/FacturacionCajas/PDFFactura.php?ov='+$('#ovLbl').text()+'&amp;tipo=CLIENTE" target="_blank">'+data.respuesta+'</a>');
                        $("#btnFacturar").hide();
                        if(havePermision(12)){
                                $('#footerFactura').append('<a onclick="mostrarModalPago()" id="btnDiarioIndex" class="waves-effect light-blue darken-4 white-text btn-flat" style="margin-right: 13px;">Asociar Factura a Diario de Pago</a>');
                            }
                    }
                    else if(data.resultado=="bad"){
                        $('#loadFacturaSt').html('<label style="color:red;">'+data.respuesta+'</label>');
                        $("#btnFacturar").show();
                    }
                    else{
                        $('#loadFacturaSt').html('<a>'+data.respuesta+'</a>');
                        $("#btnFacturar").show();
                    }                
                }            
            });
        }
        else{
            alert('No tiene permisos para facturar');
        }
    }
}
function archivoAdjunto(id,tipo){
    var restriccion='';
    $.ajax({
        url:"inicio/get-Archivo-Adjunto",type: "GET",dataType: "JSON", data: {id:id,transaction:tipo},
        success: function (res){
            console.log(res);
            $('#formDatosAdj')[0].reset();
            $('#descAdj').css('height','100%');
            if (res.length>0){
                $('#modalDatosAdjuntos #fechaAdj').val(res[0].CREATEDDATETIME);
                $('#modalDatosAdjuntos #tipoAdj').val(res[0].TYPEID);
                $('#modalDatosAdjuntos #descAdj').val(res[0].NAME);
                if ( res[0].RESTRICTION == '0' ){
                    restriccion = 'Interno';
                }
                $('#modalDatosAdjuntos #restAdj').val(restriccion);
                $('#modalDatosAdjuntos #notasAdj').val(res[0].NOTES);
                $('#modalDatosAdjuntos').openModal();
            }
            else{
                swal({
                    title: ""+id,
                    text: " no contiene adjuntos",
                    icon: "info",
                    button: "Cerrar"
                 });
            }
            
        }
    });
}
function getGuiaPaq(id,tipo){
    var restriccion='';
    $.ajax({
        url:"inicio/get-Archivo-Adjunto",type: "GET",dataType: "JSON", data: {id:id,transaction:tipo},
        success: function (res){
            console.log(res);
            $('#formDatosAdj')[0].reset();
            $('#descAdj').css('height','100%');
            if (res.length>0){
                $('#modalDatosAdjuntos #fechaAdj').val(res[0].CREATEDDATETIME);
                $('#modalDatosAdjuntos #tipoAdj').val(res[0].TYPEID);
                $('#modalDatosAdjuntos #descAdj').val(res[0].NAME);
                if(res[0].TYPEID=="Paqueteria"){
                    $('#modalDatosAdjuntos #notasAdj').val(res[0].NAME);
                }
                if ( res[0].RESTRICTION == '0' ){
                    restriccion = 'Interno';
                }
                $('#modalDatosAdjuntos #restAdj').val(restriccion);
                $('#modalDatosAdjuntos #notasAdj').val(res[0].NOTES);
                $('#modalDatosAdjuntos').openModal();
            }
            else{
                swal({
                    title: ""+id,
                    text: " no contiene adjuntos",
                    icon: "info",
                    button: "Cerrar"
                 });
            }
            
        }
    });
}
function convertirCot(cot,boton){
    $('#modalLoading').openModal();
    $.ajax({url: "inicio/convertirCotOV",type: "post",dataType: "json",
        data: { "cotizacion": cot },
        success: function (data){
            if(data.status != 'Fallo'){
                $('#misCot').click();
                Materialize.toast('Cotización convertida a Orden de Venta!', 3000);
            }else{
                Materialize.toast('Error en dynamics:'+data.msg, 3000);
            }
            $('#modalLoading').closeModal();
        },
        error: function (data){
            $('#modalLoading').closeModal();
            Materialize.toast('WebService Error!.', 3000);
        }
    });
}
function mostrarModalPaq(ov,cliente){
    $('#paqOv').val(ov);
    $('#clientePaq').val(cliente);
    $('#modalPaqueteria h4').html(ov);
    $.ajax({url: "inicio/paqueterias",type: "post",dataType: "json",
        data: {ov:ov},
        success: function (data){
            console.log(data);
            if (data != '') {
                $('#paqOv').val(data[0].ov);
                $('#paqPaqueteria').val(data[0].paqueteria);
                $('#paqGuia').val(data[0].guia);
                $('#paqDescripcion').val(data[0].descripcion);
                $('#guardarGuia').hide();
            }
            else {
                $('#paqPaqueteria').val('');
                $('#paqGuia').val('');
                $('#paqDescripcion').val('');
                $('#guardarGuia').show();
            }
        },
        error: function (data){
            Materialize.toast('WebService Error!.', 3000);
        }
    });

    $('#modalPaqueteria').openModal();
}

function guardaguia(ov,paqueteria,guia,descripcion){
        $('#guardarGuia').hide();
        $('#modalLoading').openModal();
    if (ov == '' || paqueteria == '' || guia == '' || descripcion == '') {
        swal('Atención','Completa todos los campos','warning');
    } else {
        $.ajax({url: "inicio/saveguide",type: "post",dataType: "json",
            data: {ov:ov,paqueteria:paqueteria,guia:guia,descripcion:descripcion,cliente:cliente},
            success: function (data){
                swal('Excelente','Se guardo la guía a la ' + ov + '','success');
                $('#guardarGuia').hide();
                $('#modalLoading').closeModal();
            },
            error: function (data){
                // $('#modalLoading').closeModal();
                Materialize.toast('WebService Error!.', 3000);
                $('#modalLoading').closeModal();
            }
        });
    }

}

function fTodasOv(x,y){
    gaTrigger('SalesOrderHeadersV2','entity',userBranchOffice);

    var data = {findThis: x,inThis: y};
    $('.tooltipped').tooltip();
    var col2 = '<br><div><input type="text"><button class="btn-small todasOvsearch" data-id="SalesOrderNumber">Buscar</button></div>';
    var col4 = '<br><div><input type="text"><button class="btn-small todasOvsearch" data-id="InvoiceCustomerAccountNumber">Buscar</button></div>';
    var col5 = '<br><div><input type="text"><button class="btn-small todasOvsearch" data-id="SalesOrderName">Buscar</button></div>';

    $('#pasaForm').html('');
    $('#modalLoading').openModal();
    $("nav").find(".active").removeClass("active");
    $(this).parent().addClass("active");                        
    $.ajax({
        url: "index/todasOV2",
        data: data,
        type: "POST",
        dataType: 'json',
        beforeSend: function (xhr) {
            $('#modalLoading').openModal();
            checkTableisDataTable();
        },
        success: function (data, textStatus, jqXHR) {
            $('#infoPalette').show();
            $('#ov').DataTable({
                "destroy": true,
                "search":true,
                "paging": true, 
                "info": false,
                "order": [[ 1, "desc" ]],
                data: data,
                columns: [ 
                    {title: "Detalle"},
                    {title: "Orden de Venta" + col2},
                    {title: "Fecha"},
                    {title: "Código Cliente" + col4},
                    {title: "Cliente" + col5},
                    {title: "Sitio"},
                    {title: "Almacén"},
                    {title: "Modo de Entrega"},
                    {title: "Vendedor"},
                    {title: "Estatus de la liberación"},
                    {title: "Estado de la OV"},
                    {title: "Estado de Pago"},
                    {title: "Imprimir"},
                    {title: "Diario Pago Inax"},
                ]
            });
            $('#modalLoading').closeModal();
            $('.tooltipped').tooltip();
        },
        error: function (jqXHR, textStatus, errorThrown) {
        }
    });
}
function fTodasCot(x,y){
    gaTrigger('SalesQuotationHeaders','entity',userBranchOffice);
    var data = {findThis: x,inThis: y};
        $('#pasaForm').html('');
        $('#modalLoading').openModal();
        $("nav").find(".active").removeClass("active");
        $(this).parent().addClass("active");  
        $.ajax({
        url: "index/todasCOT2",
        data: data,
        type: "POST",dataType: 'json',
        beforeSend: function (xhr) {
           $('#modalLoading').openModal();
            checkTableisDataTable();
       },
       success: function (data, textStatus, jqXHR) {
           $('#ov').DataTable({
               "destroy": true,
               "search":true,
               "paging": true, 
               "info": false,
               "order": [[ 1, "desc" ]],
               data: data,
               columns: [ 
                   {title: 'Detalle'},
                   {title: 'Cotización <br><div id="cotizaciónFilter"><input type="text" class="form-control"><button class="btn btn-primary btn-xs todasCotsearch" data-id="SalesQuotationNumber">Buscar</button></div>'},
                   {title: 'Fecha'},
                   {title: 'Código Cliente <br><div id="códigoFilter"><input type="text" class="form-control"><button class="btn btn-primary btn-xs todasCotsearch" data-id="InvoiceCustomerAccountNumber">Buscar</button></div>'},
                   {title: 'Cliente <br><div id="clienteFilter"><input type="text" class="form-control"><button class="btn btn-primary btn-xs todasCotsearch" data-id="DeliveryAddressName">Buscar</button></div>'},
                   {title: 'Sitio'},
                   {title: 'Almacén'},
                   {title: 'Modo de Entrega'},
                   {title: 'Vendedor'},
                   {title: 'Estado'},
                   {title: 'Convertir OV'},
                   {title: 'Imprimirn'}
               ]
           });
           $('#modalLoading').closeModal();
       },
       error: function (jqXHR, textStatus, errorThrown) {
       }
   }); 
}

$(document).on('click','.todasCotsearch',function(){
    var findThis = $(this).siblings().val();
    var inThis = $(this).data('id');
    console.log('Busca esto: ' + findThis + ' En: ' + inThis);
    fTodasCot(findThis,inThis);
});

$(document).on('click','.todasOvsearch',function(){
    var findThis = $(this).siblings().val();
    var inThis = $(this).data('id');
    console.log('Busca esto: ' + findThis + ' En: ' + inThis);
    fTodasOv(findThis,inThis);
});

function checkTableisDataTable(){
    if ($.fn.dataTable.isDataTable('#ov')) {
        $('#ov').DataTable().destroy();
        $('#ov').empty();
    }
}
        function GenerarRemisionBtn(condiEntrega,ov,usuario){
            ValidarLimiteCredito(ov,usuario,condiEntrega);
        }
        function imprimirRemision(Remision,edoentrega){
            $('#rem').val(Remision);
            $('#edoentrega').val(edoentrega);
            $('#imprimirRemision').submit();
        }

        function imprimirCotizacion(Cotizacion,NumCotizaciones){
            $('#DCotizacion').val(Cotizacion);
            $('#CountCotizacion').val(NumCotizaciones);
            $('#imprimirCotizacion').submit();
        }
        
       function generarEtiquetas(sitio,ov,proposito,recid){        
        $.ajax({
            url : "index/datosEtiqueta",type : "POST",dataType : "JSON",
            data : {"sitio":sitio,"ov":ov},
            beforeSend: function (xhr) {
                $("#loading").html('<img src="../application/assets/img/cargando.gif" style="width: 1em;">');
            },
            success : function(res){                
                datosSucu = res.datosSucu;
                datosClte = res.datosCte[0];
                datosDirs = res.datosDirs;
                datosMonto = res.datosMonto;
                // datosGuia = res.datosguia[0];
                direccionCliente(proposito,datosDirs,recid);
                $(datosSucu).each(function(){
                    $('#tablaEtiqueta .calle').val(this.calle);
                    $('#tablaEtiqueta .colonia').val(this.colonia);
                    $('#tablaEtiqueta .estado').val(this.estado);
                    $('#tablaEtiqueta .telefono').val(this.telefono);
                });                

                clteCompleto = ov + ' - ' + datosClte.CUSTACCOUNT + ' - ' + datosClte.NOMBRECLIENTE;
                var fecha = new Date();
                
                var monto = '';
                $('#tablaEtiqueta .monto').val(datosMonto.substr(0,( datosMonto.indexOf('.')+3 ) )).formatCurrency({roundToDecimalPlace:'2'});
                monto = $('#tablaEtiqueta .monto').val();
                $('#tablaEtiqueta .monto').val('Monto: ' + monto);
                $('#tablaEtiqueta .cliente-top').val(clteCompleto);
                //$('#tablaEtiqueta .calle-cte').val(datosDirs[0].CALLE);
                //$('#tablaEtiqueta .colonia-cte').val(datosDirs[0].COLONIA);
                //$('#tablaEtiqueta .estado-cte').val("Estado: "+datosDirs[0].ESTADO);
                // $('#tablaEtiqueta .comentario').val(datosGuia.descripcion);
                // $('#tablaEtiqueta .paqyflet').val(datosGuia.paqueteria+": "+datosGuia.guia);
                $('#tablaEtiqueta .rfc-cte').val('RFC: ' + datosClte.RFC);
                $('#tablaEtiqueta .correo-cte').val('EMAIL: ' + datosClte.EMAIL);
                $('#tablaEtiqueta .tel-cte').val('Telefono: ' + datosClte.TELEFONO + ' ext: ' + datosClte.EXTENSION);
                $('#tablaEtiqueta .cliente-bot').val(clteCompleto);
                $('#tablaEtiqueta .userEti').html('Usuario: '+user+' ('+datosClte.NOMBREVENDEDOR+')');
                $('#tablaEtiqueta .fechaEti').html('Fecha de creacion: '+fecha.toLocaleDateString()+' - '+fecha.toLocaleTimeString());
                $('#tablaEtiqueta .rfc-cte').removeAttr('readonly');
                $('#tablaEtiqueta .tel-cte').removeAttr('readonly');
                $('#tablaEtiqueta').show();
                $("#loading").html('');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $("#loading").html("Ocurrio un error favor de comunicarlo al dpto de sistemas.");
            }
        });
    }
        function setComparativa(){
            $.ajax({
                url:'index',type:'GET',dataType:'JSON',data:{'token':'comparativa'},
                success: function(res){
                    if (res[0] != 'NoResults'){
                        /////////////////////////////////ventas///////////////////////////////////////////////////
                        if (res['WS_VTA'] != '0'){
                            porcentajeVentaWS = eval(res['WS_VTA']) / eval(res['TOTAL_VTA']);
                        }else{
                            porcentajeVentaWS = 0;
                        }
                        blue = porcentajeVentaWS.toFixed(4);
                        if (res['DYN_VTA'] != '0'){
                            porcentajeVentaDYN = eval(res['DYN_VTA']) / eval(res['TOTAL_VTA']);
                        }else{
                            porcentajeVentaDYN = 0;
                        }
                        red = porcentajeVentaDYN.toFixed(4);
                        $('#red').css('color','red');
                        $('#red').css('text-align','center');
                        $('#red').css('width','100%');
                        $('#red').css('height','75px'); 
                        $('#red').css('bottom','0px');
                        $('#red').css('font-size','11px');
                        $('#red').css('background','-webkit-linear-gradient(white '+( (1-red)*100 )+'%, #8A4B08, #8A4B08, #8A4B08');
                        $('#red').html( (red*100).toFixed(2) + '%' );
                        $('#redLabelVta').html('Dynamics<br/><b>('+res['DYN_VTA']+')</b>');
                        $('#blue').css('color','red');
                        $('#blue').css('text-align','center');
                        $('#blue').css('width','100%');
                        $('#blue').css('height','75px'); 
                        $('#blue').css('bottom','0px');
                        $('#blue').css('font-size','11px');
                        $('#blue').css('background','-webkit-linear-gradient(white '+( (1-blue)*100 )+'%, #0404B4, #0404B4, #0404B4');
                        $('#blue').html( (blue*100).toFixed(2) + '%' );
                        $('#blueLabelVta').html('inAX<br/><b>('+res['WS_VTA']+')</b>');
                        ////////////////////cotizaciones/////////////////////////////////////////////////////////////////////
                        if (res['WS_COT'] != '0'){
                            porcentajeCotiWS = eval(res['WS_COT']) / eval(res['TOTAL_COT']);
                        }else{
                            porcentajeCotiWS = 0;
                        }
                        blueCoti = porcentajeCotiWS.toFixed(4);
                        if (res['DYN_COT'] != '0'){
                            porcentajeCotiDYN = eval(res['DYN_COT']) / eval(res['TOTAL_COT']);
                        }else{
                            porcentajeCotiDYN = 0;
                        }
                        redCoti = porcentajeCotiDYN.toFixed(4);
                        $('#redCoti').css('color','red');
                        $('#redCoti').css('text-align','center');
                        $('#redCoti').css('width','100%');
                        $('#redCoti').css('height','75px'); 
                        $('#redCoti').css('bottom','0px');
                        $('#redCoti').css('font-size','11px');
                        $('#redCoti').css('background','-webkit-linear-gradient(white '+( (1-redCoti)*100 )+'%, #8A4B08, #8A4B08, #8A4B08');
                        $('#redCoti').html( (redCoti*100).toFixed(2) + '%' );
                        $('#redLabelCoti').html('Dynamics<br/><b>('+res['DYN_COT']+')</b>');
                        $('#blueCoti').css('color','red');
                        $('#blueCoti').css('text-align','center');
                        $('#blueCoti').css('width','100%');
                        $('#blueCoti').css('height','75px'); 
                        $('#blueCoti').css('bottom','0px');
                        $('#blueCoti').css('font-size','11px');
                        $('#blueCoti').css('background','-webkit-linear-gradient(white '+( (1-blueCoti)*100 )+'%, #0404B4, #0404B4, #0404B4');
                        $('#blueCoti').html( (blueCoti*100).toFixed(2) + '%' );
                        $('#blueLabelCoti').html('inAX<br/><b>('+res['WS_COT']+')</b>');
                    }
                }
            });
        }
function detalleVenta2(ov,trans){
    $('#modalLoading').openModal();
        var html  = '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px; border-top: solid 1px; border-right: solid 1px; border-left: solid 1px; border-bottom: solid 1px;">';
        html += '<thead>';
        html += '    <th># Linea</th><th>Folio</th><th>Codigo de Articulo</th><th>Nombre</th><th>Cantidad</th><th>Unidad</th>';
        html += '</thead>';
        html += '<tbody>';
        $.ajax({ url:"inicio/detalleVenta", type: "POST", dataType: "json",data: {"ov":ov,"transaction":trans},
            success: function (res){
                $.each(res,function(i,d){
                    var numlinea = eval(d.LINENUM);
                    var qty      = eval(d.QTYORDERED);
                    html += '<tr style="border-bottom:solid 1px">';
                    html += '   <td>'+d.LineCreationSequenceNumber+'</td>';
                    html += '   <td>'+(typeof(d.SalesOrderNumber)== 'undefined' ? d.SalesQuotationNumber : d.SalesOrderNumber)+'</td>';
                    html += '   <td>'+d.ItemNumber+'</td>';
                    html += '   <td>'+d.LineDescription+'</td>';
                    html += '   <td>'+(typeof(d.OrderedSalesQuantity) == 'undefined' ? d.RequestedSalesQuantity : d.OrderedSalesQuantity)+'</td>';
                    html += '   <td>'+d.SalesUnitSymbol+'</td>';
                    html += '</tr>';
                });
                html += '</tbody>';
                html += '</table>';
                $("#detalleDoc").html('');
                $("#modalDocumentContent").html('');
                $('#modalLoading').closeModal();
                $("#detalleDoc").html(ov);
                $("#modalDocumentContent").append(''+html);
                $("#modalDetalleDoc").openModal();
            }
        });
        
}
function detalleVenta(ov,obj,tipo,trans){
            var tr    = $(obj).closest('tr');
            if (tipo == 'ovuser'){
                var table = $('#ov').DataTable();
            }else if (tipo == 'todasov'){
                var table = $('#todasov').DataTable();
            }else if (tipo == 'usercot'){
                var table = $('#usuariocot').DataTable();
            }else if (tipo == 'todascot'){
                var id = $(obj).closest('table').attr('id');
                var table = $('#'+id).DataTable();
            }
            var row   = table.row(tr);            
            if ( row.child.isShown() ) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
                $(obj).html('add_circle');
                $(obj).attr('style','color:green;cursor:pointer');
            }
            else {
                // Open this row
                $(obj).html('remove_circle');
                $(obj).attr('style','color:red;cursor:pointer');
                $('#modalLoading').openModal();
                var html  = '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px; border-top: solid 1px; border-right: solid 1px; border-left: solid 1px; border-bottom: solid 1px;">';
                html += '<thead>';
                html += '    <th># Linea</th>';
                if ( trans == 'ORDVTA' ){
                    html += '    <th>Orden de Venta</th>';
                }else{
                    html += '    <th>Cotizacion</th>';
                }
                html += '    <th>Codigo de Articulo</th>';
                html += '    <th>Nombre</th>';
                html += '    <th>Cantidad</th>';
                html += '    <th>Unidad</th>';
                html += '</thead>';
                html += '<tbody>';
                $.ajax({ url:"inicio/detalleVenta", type: "POST", dataType: "json",
                    data: {"ov":ov,"transaction":trans},
                    success: function (res){
                        $(res).each(function(){
                            numlinea = eval(this.LINENUM);
                            qty      = eval(this.QTYORDERED);
                            html += '<tr style="border-bottom:solid 1px">';
                            html += '   <td>'+numlinea.toFixed(2)+'</td>';
                            html += '   <td>'+this.SALESID+'</td>';
                            html += '   <td>'+this.ITEMID+'</td>';
                            html += '   <td>'+this.NAME+'</td>';
                            html += '   <td>'+qty.toFixed(2)+'</td>';
                            html += '   <td>'+this.SALESUNIT+'</td>';
                            html += '</tr>';
                        });
                        html += '</tbody>';
                        html += '</table>';
                        row.child(html).show();
                        tr.addClass('shown');
                        $('#modalLoading').closeModal();
                    }
                });                
            }            
        }

function editarOV(DocumentId,cliente){
    $('#editarCotForm #documentType').val('ORDVTA');
    $('#editarCotForm #DocumentId').val(DocumentId);
    $('#editarCotForm #cliente').val(cliente);
    $('#editarCotForm #editar').val('1');
    $('#editarCotForm').submit();
}
function editarCot(DocumentId,cliente){
    $('#editarCotForm #documentType').val('CTZN');
    $('#editarCotForm #DocumentId').val(DocumentId);
    $('#editarCotForm #cliente').val(cliente);
    $('#editarCotForm #editar').val('1');
    $('#editarCotForm').submit();
}

function direccionCliente(proposito,direcciones,recid){
        var dirMuestra = $.map(direcciones,function(dirs){
            if(dirs.PROPOSITO.includes(proposito)){
                //dirTemp   = dirs.ADDRESS.split("\n");
                dirCalle  = dirs.CALLE;
                dirColon  = dirs.COLONIA;
                dirEstad  = dirs.ESTADO;
                dirCiudad = dirs.CIUDAD;
                dirCodPo  = dirs.CODIGOPOSTAL;
                dirPais   = dirs.PAIS;
                direccion = {'calle':dirCalle,'colonia':dirColon,'estado':dirEstad,'ciudad':dirCiudad,'cp':dirCodPo,'pais':dirPais};
                return direccion;
            }
        });
        $(dirMuestra).each(function(){
            if (this.calle != 'NoDefinido'){
                $('#tablaEtiqueta .calle-cte').val(this.calle + ' C.P. '+ this.cp);
            }else{
                $('#tablaEtiqueta .calle-cte').removeAttr('readonly');
            }
            if (this.colonia != 'NoDefinido'){
                $('#tablaEtiqueta .colonia-cte').val(this.colonia);
            }else{
                $('#tablaEtiqueta .colonia-cte').removeAttr('readonly');
            }
            if (this.estado){
                $('#tablaEtiqueta .estado-cte').val( this.ciudad+','+this.estado + ',' +this.pais.toUpperCase() );
            }else{
                $('#tablaEtiqueta .estado-cte').removeAttr('readonly');
            }
        });
    }

function mostrarModalEti(ov,sitio,imgPath){
    labelOvClicked = ov;
    $('#modalEtiquetas').openModal({
        ready : function(){
            if ( $.fn.DataTable.isDataTable( '#tablaEtiquetasPaqueteria' ) ){
                $('#tablaEtiquetasPaqueteria').dataTable().fnClearTable();
                    $('#tablaEtiquetasPaqueteria').dataTable().fnDestroy();
                    $('#tablaEtiquetasPaqueteria tbody').remove();
                    $('.paqyflet').val('');
                    $('.tipoentreseg').val('');
                    $('.comentario').val('');
            }
            if ( $('#previsEtiqueta').is(':checked') ){
                    $('#previsEtiqueta').removeProp('checked');
                    $('#tablaEtiquetasPaqueteria tbody').remove();
                    $('.paqyflet').val('');
                    $('.tipoentreseg').val('');
                    $('.comentario').val('');
                    $('#tablaEtiqueta').hide();
            }
        $('#modalEtiquetas #OVEti').val(ov);
        $('#sitioEtiquetas').html(sitiosList);
        $('#sitioEtiquetas').val(sitio);
        $('#sitioEtiquetas').material_select();
        $('#propositoEtiquetas').material_select('destroy');
        $('#propositoEtiquetas').attr('disabled', 'disabled');
        $('#propositoEtiquetas').html('<option value="">Selecciona...</option>');
        $('#propositoEtiquetas').material_select();
        $.ajax({ url : "index/datosPropo"
                ,type: "POST"
                ,dataType : "JSON"
                ,data : {"ov":ov},
                beforeSend: function (xhr){
                        $("#loadheaderid").html('<img src="'+imgPath+'/cargando.gif" style="width: 1em;">');
                    },
                success : function(res){
                    $('#propositoEtiquetas').html(res.optPropo);
                    $('#propositoEtiquetas').removeAttr('disabled');
                    $('#propositoEtiquetas').material_select();
                    var div = $('#propositoEtiquetas').parent('.select-wrapper');
                    var ul = $(div).children('ul').children('li');
                    $(ul).each(function(index){
                        $(this).children('span').css('font-size','14px');
                        $(this).children('span').css('text-transform','uppercase');
                        if ($(this)[0].innerText != 'Selecciona...' && $(this)[0].innerText != 'Otro' ){
                            var proposito = $(this)[0].innerText;
                            var temp  = $('#propositoEtiquetas option')[index];
                            //var recid = $(temp).attr('data-recid');
                            var dirMuestra = $.map(res.datosDirs,function(dirs){  
                                if(dirs.PROPOSITO.includes(proposito)){
                                    //dirs.STREET   = dirs.STREET.replace(/[\r\n]/g,' | ');
                                    var direccion = '<strong><u>Calle: </u></strong>' + dirs.CALLE + ' <strong><u>Colonia: </u></strong>' + dirs.COLONIA + ' <strong><u>Estado: </u></strong>' + dirs.ESTADO + ' <strong><u>Ciudad: </u></strong>' + dirs.CIUDAD + ' <strong><u>CodigoPostal: </u></strong>' + dirs.CODIGOPOSTAL;
                                    return direccion;
                                }
                            });
                            var content = '<div class="row">'+
                                    '<div class="col l12 m12 s 12">'+
                                    '   <span style="color:#B4B7B7;font-size:12px;">' + dirMuestra[0] + '</span>'+
                                    '</div></div>';                                                        
                            $(this).append(content);
                        }
                    });
                    $("#loadheaderid").html('');
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $("#loading").html("Ocurrio un error favor de comunicarlo al dpto de sistemas.");
                }
            });
        }
    });
}
$('#genArchEti').on('click',function(){
        var valido = checarDatosCliente();
        if (valido){
            Materialize.toast('Todo valido puede continuar', 3000,'green');
            var tbody = $('<tbody></tbody>');
            $('#tablaEtiqueta tbody tr').each(function(){
                var td = $(this).find('td').clone();
                var tr = $('<tr></tr');
                $(td).each(function(){
                    if ( !$(this).children().is('input') ){
                        $(tr).append(td);
                    }else{
                        var dato = $(this).children('input').val();
                        $(this).find('input').remove();
                        $(this).html(dato);
                        $(tr).append(this);
                    }
                    $(tbody).append(tr);
                });
            });
            var tr2 = '<tr><td style="width:1%"> </td><td style="width:49%"> </td><td style="width:50%"> </td></tr>';
            var tr3 = '<tr><td style="width:1%"> </td><td style="width:49%">'+company+'</td><td style="width:50%"> </td></tr>';
            var tbodyTemp = $(tbody).html();
            tbodyTemp += tr2;
            tbodyTemp += tr3;
            tbodyTemp += $(tbody).html();
            tbodyTemp += tr2;
            tbodyTemp += tr3;
            tbodyTemp += $(tbody).html();
            $(tbody).html(tbodyTemp);
            $('#tablaEtiquetasPaqueteria tbody').remove();
            $('#tablaEtiquetasPaqueteria').append(tbody);
            dataTableEti();
            tbodyTemp = '';
            $('.btn-etiquetas').trigger('click');
        }else{
            Materialize.toast('EL tipo de entrega esta incompleto, favor de ingresarlo', 3000,'red');
        }
});

$('#sitioEtiquetas,#propositoEtiquetas').on('change',function(){
    $('#previsEtiqueta').removeProp('checked');
    $('#tablaEtiqueta').hide();
});

$('#previsEtiqueta').on('change',function(){
    if ($(this).is(':checked')){            
        var sitio = $('#sitioEtiquetas :selected').val();
        var proposito = $('#propositoEtiquetas :selected').val();
        var ov = $('#modalEtiquetas #OVEti').val();
        var recid = $('#propositoEtiquetas :selected').attr('data-recid');
        generarEtiquetas(sitio,ov,proposito,recid);
        if (proposito == 'Otro'){
            $('.datoEtiquetaCliente').not('.tipoentreseg,.paqyflet,.comentario').removeProp('readonly');
            $('.datoEtiquetaCliente').val('');
        }else{
            $('.datoEtiquetaCliente').not('.tipoentreseg,.paqyflet,.comentario').prop('readonly','readonly');
        }            
    }else{
        $('#tablaEtiqueta').hide();
    }
});

function descargarExcel(variable_conTabla){
    var htmls = "";
    var uri = 'data:application/vnd.ms-excel;base64,';
    var template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'; 
    var base64 = function(s) {
        return window.btoa(unescape(encodeURIComponent(s)))
    };

    var format = function(s, c) {
        return s.replace(/{(\w+)}/g, function(m, p) {
            return c[p];
        })
    };

    htmls = variable_conTabla;

    var ctx = {
        worksheet : 'Worksheet',
        table : htmls
    }


    var link = document.createElement("a");
    link.download = "Etiqueta.xls";
    link.href = uri + base64(format(template, ctx));
    link.click();
}
function dataTableEti(){
    if ( $.fn.DataTable.isDataTable( '#tablaEtiquetasPaqueteria' ) ){
            $('#tablaEtiquetasPaqueteria').dataTable().fnDestroy();
    }
    $('#etiquetasPaqueteria .dt-buttons').remove();
    $('#etiquetasPaqueteria .dataTables_filter').remove();
    $('#etiquetasPaqueteria .dataTables_info').remove();
    $('#etiquetasPaqueteria .dataTables_paginate').remove();
    var valido = checarDatosCliente();
    if (valido){
        Materialize.toast('Todo valido puede continuar', 3000,'green');
        var tbody = $('<tbody></tbody>');
        $('#tablaEtiqueta tbody tr').each(function(){
            var td = $(this).find('td').clone();
            var tr = $('<tr></tr>');
            $(td).each(function(){
                if ( !$(this).children().is('input') ){
                    $(tr).append(td);
                }else{
                    var dato = $(this).children('input').val();
                    $(this).find('input').remove();
                    $(this).html(dato);
                    $(tr).append(this);
                }
                $(tbody).append(tr);
            });
        });
        var tr2 = '<tr style="height:20px"><td style="width:49%;height:100%;font-size:12px"></td><td style="width:50%;height:100%;font-size:12px"></td></tr>';
        var tr3 = '<tr style="height:20px"><td style="background-color: #01579B; color: #FFFFFF; border-left: 1px solid black; border-top: 1px solid black; border-bottom: 1px solid black">'+company+'</td><td style="background-color: #01579B; color: #FFFFFF; border-left: 1px solid black; border-top: 1px solid black; border-bottom: 1px solid black"> </td></tr>';
        var tbodyTemp = $(tbody).html();
        tbodyTemp += tr2;
        tbodyTemp += tr3;
        tbodyTemp += $(tbody).html();
        tbodyTemp += tr2;
        tbodyTemp += tr3;
        tbodyTemp += $(tbody).html();
        $(tbody).html(tbodyTemp);
        $('#tablaEtiquetasPaqueteria tbody').remove();
        $('#tablaEtiquetasPaqueteria').append(tbody);
        tbodyTemp = '';
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('border','1px solid black');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('border','1px solid black');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('height','22px');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('height','22px');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('text-transform','uppercase');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('text-transform','uppercase');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('font-size','10px');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('font-size','10px');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('padding-left','5px');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('padding-left','5px');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('padding-right','5px');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('padding-right','5px');
            var style = '<style>'+
                    '   @media print { body { -webkit-print-color-adjust: exact; } }'+
                    '   #tablaEtiquetasPaqueteria tbody tr:nth-child(15) td:nth-child(2){'+
                    '       border-bottom: 1px solid black;'+
                    '       border-top: 1px solid black;'+
                    '       border-left: 1px solid black;'+
                    '       background-color:#01579B;'+
                    '       color: #FFFFFF'+
                    '   }'+
                    '   #tablaEtiquetasPaqueteria tbody tr:nth-child(15) td:nth-child(3){'+
                    '       border-bottom: 1px solid black;'+
                    '       border-top: 1px solid black;'+
                    '       border-right: 1px solid black;'+
                    '       background-color:#01579B;'+
                    '       color: #FFFFFF'+
                    '   }'+
                    '   #tablaEtiquetasPaqueteria tbody tr:nth-child(30) td:nth-child(2){'+
                    '       border-bottom: 1px solid black;'+
                    '       border-top: 1px solid black;'+
                    '       border-left: 1px solid black;'+
                    '       background-color:#01579B;'+
                    '       color: #FFFFFF'+
                    '   }'+
                    '   #tablaEtiquetasPaqueteria tbody tr:nth-child(30) td:nth-child(3){'+
                    '       border-bottom: 1px solid black;'+
                    '       border-top: 1px solid black;'+
                    '       border-right: 1px solid black;'+
                    '       background-color:#01579B;'+
                    '       color: #FFFFFF'+
                    '   }'+
                    '   #tablaEtiquetasPaqueteria tbody tr:nth-child(30) td:nth-child(3){'+
                    '       border-bottom: 1px solid black;'+
                    '       border-top: 1px solid black;'+
                    '       border-right: 1px solid black;'+
                    '       background-color:#01579B;'+
                    '       color: #FFFFFF'+
                    '   }'+
                    '</style>';
        var html = $('#etiquetasPaqueteria').html();
        descargarExcel(html);
    }else{
        Materialize.toast('EL tipo de entrega esta incompleto, favor de ingresarlo', 3000,'red');
    }
}

$('#impArchEti').on('click',function(){
        
        if ( $.fn.DataTable.isDataTable( '#tablaEtiquetasPaqueteria' ) ){
            $('#tablaEtiquetasPaqueteria').dataTable().fnDestroy();
        }
        $('#etiquetasPaqueteria .dt-buttons').remove();
        $('#etiquetasPaqueteria .dataTables_filter').remove();
        $('#etiquetasPaqueteria .dataTables_info').remove();
        $('#etiquetasPaqueteria .dataTables_paginate').remove();
        var valido = checarDatosCliente();
        if (valido){
            Materialize.toast('Todo valido puede continuar', 3000,'green');
            var tbody = $('<tbody></tbody>');
            $('#tablaEtiqueta tbody tr').each(function(){
                var td = $(this).find('td').clone();
                var tr = $('<tr></tr');
                $(td).each(function(){
                    if ( !$(this).children().is('input') ){
                        $(tr).append(td);
                    }else{
                        var dato = $(this).children('input').val();
                        $(this).find('input').remove();
                        $(this).html(dato);
                        $(tr).append(this);
                    }
                    $(tbody).append(tr);
                });
            });
            var tr2 = '<tr><td colspan="2">.</td></tr>';
            var tr3 = '<tr><td style="background-color: #01579B; color: #FFFFFF; border-left: 1px solid black; border-top: 1px solid black; border-bottom: 1px solid black">' + company + '</td><td style="background-color: #01579B; color: #FFFFFF; border-right: 1px solid black; border-top: 1px solid black; border-bottom: 1px solid black"></td></tr>';
            var tbodyTemp = $(tbody).html();
            tbodyTemp += tr2;
            tbodyTemp += tr3;
            tbodyTemp += $(tbody).html();
            tbodyTemp += tr2;
            tbodyTemp += tr3;
            tbodyTemp += $(tbody).html();
            $(tbody).html(tbodyTemp);
            $('#tablaEtiquetasPaqueteria tbody').remove();
            $('#tablaEtiquetasPaqueteria').append(tbody);
            tbodyTemp = '';
            $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('border','1px solid black');
            $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('border','1px solid black');
            $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('height','22px');
            $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('height','22px');
            $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('text-transform','uppercase');
            $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('text-transform','uppercase');
            $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('font-size','10px');
            $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('font-size','10px');
            $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('padding-left','5px');
            $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('padding-left','5px');
            $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('padding-right','5px');
            $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('padding-right','5px');
            var style = '<style>'+
                        '   @media print { body { -webkit-print-color-adjust: exact; } }'+
                        '   #tablaEtiquetasPaqueteria tbody tr:nth-child(15) td:nth-child(1){'+
                        '       border-bottom: 1px solid black;'+
                        '       border-top: 1px solid black;'+
                        '       border-left: 1px solid black;'+
                        '       background-color:#01579B;'+
                        '       color: #FFFFFF'+
                        '   }'+
                        '   #tablaEtiquetasPaqueteria tbody tr:nth-child(15) td:nth-child(2){'+
                        '       border-bottom: 1px solid black;'+
                        '       border-top: 1px solid black;'+
                        '       border-right: 1px solid black;'+
                        '       background-color:#01579B;'+
                        '       color: #FFFFFF'+
                        '   }'+
                        '   #tablaEtiquetasPaqueteria tbody tr:nth-child(30) td:nth-child(1){'+
                        '       border-bottom: 1px solid black;'+
                        '       border-top: 1px solid black;'+
                        '       border-left: 1px solid black;'+
                        '       background-color:#01579B;'+
                        '       color: #FFFFFF'+
                        '   }'+
                        '   #tablaEtiquetasPaqueteria tbody tr:nth-child(30) td:nth-child(2){'+
                        '       border-bottom: 1px solid black;'+
                        '       border-top: 1px solid black;'+
                        '       border-right: 1px solid black;'+
                        '       background-color:#01579B;'+
                        '       color: #FFFFFF'+
                        '   }'+
                        '   #tablaEtiquetasPaqueteria tbody tr:nth-child(30) td:nth-child(1){'+
                        '       border-bottom: 1px solid black;'+
                        '       border-top: 1px solid black;'+
                        '       border-right: 1px solid black;'+
                        '       background-color:#01579B;'+
                        '       color: #FFFFFF'+
                        '   }'+
                        '</style>';
            //var w = window.open('ETIQUETAS','Etiquetas');
            var html = $('#etiquetasPaqueteria').html();
            html = html.replace('<thead>', '');
            html = html.replace('</thead>', '');
            html = html.replace('<tbody>', '');
            html = html.replace('</tbody>', '');
            html = html.replace('<th', '<td');
            html = html.replace('<th', '<td');
            html = html.replace('</th>', '</td>');
            html = html.replace('</th>', '</td>');
            //w.document.write(style+html);
            //w.print();
            //w.close();
            $.post('inicio/postlabel', { ov: labelOvClicked, html: style + html }).done(function (data) {
                data = JSON.parse(data);
                console.log(data);
                if (data.success == 'true') {
                    Materialize.toast('Etiqueta generadas correctamente', 3000, 'green');
                    parcelLabelSubmited = true;
                    $('#modalEtiquetas').closeModal();
                }
            });
        }else{
           Materialize.toast('EL tipo de entrega esta incompleto, favor de ingresarlo', 3000,'red');
        }
});
var el_monto;
var descri = $('#descripcion').val();
function mostrarModalPago(ordenVenta,customer,paymentmode){
    $('#diarioGuardarBtn').hide();
    gaTrigger('STF_CustTrans','entity',userBranchOffice);
    gaTrigger('Customers','entity',userBranchOffice);
   var factura=ordenVenta;//$("#loadFacturaSt a").text();
    /*falta agregar el monto total*/
    $.ajax({url:'inicio/review-Credit-Limit',type: 'POST',data: {factura:factura, customer:customer,tipopago:'PPD'},
        beforeSend: function (xhr) {
            Materialize.toast('Revisando si el cliente puede facturar', 3000);
            console.log('Revisando limite de Credito');
            $("#diarioPagoForm").trigger('reset');                
        },
        success: function (data, textStatus, jqXHR) {
            if(data.status){
            Materialize.toast('Si puede facturar', 3000);
            formaPagoFactura="";
            $('#descripcion').val(descri+" , "+ordenVenta);
            $('#folioDiario').html('');
            $('#modalFactura').closeModal();
            $('#diarioPago').openModal({dismissible: false});
            $('#folioDiario').val('');
            $('#diarioMontoFactura').val('');
            $('#customerDiario').val(customer);                
            gaTrigger('SalesOrderLines','entity',userBranchOffice);
   $.ajax({url:'inicio/factura-Lines',type: 'POST',data: {ov:ordenVenta},
        beforeSend: function (xhr) {
            $('#process').html('<img src="../application/assets/img/cargando.gif" style="width:1em;">');
        },
        success: function (data, textStatus, jqXHR) {
            $('#process').html('');
            var totalAmount=0;
            var sitio ="";
            $.each(data.value,function (i,v){
                totalAmount+=v.LineAmount;
                sitio = v.ShippingSiteId;
            });
            var tax = 1.16;
            if(sitio.indexOf('TJNA')>=0 ||sitio.indexOf('MEXL')>=0 ||sitio.indexOf('JURZ')>=0){
                       tax = 1.08;
            }
            totalAmount=totalAmount*tax;
            
            totalAmount=totalAmount.toFixed(2);
            $('#diarioMontoFactura').val(Number(totalAmount));
            var html='';
            $.each(payModeList.value,function (i,v){
                var sel='';
                if(paymentmode==v.Description){sel='selected="selected"';}
                html+='<option value="'+v.Name+'" '+sel+'>'+v.Description+'</option>';
            });
            formaPagoFactura=paymentmode;
            $('#diarioFPago').html(html);
            var selec = $('#diarioFPago option:selected').text();
            
            momenActual = new Date();
            if(formaPagoFactura=="03"){
                descri = $('#descripcion').val();
                $('#referenciap').show();
                $('#labelreferencia').show();
                descri = descri.replace("Cobros","");
                $('#descripcion').val(descri);
            }


            gaTrigger('BankAccounts','entity',userBranchOffice);
            $.ajax({
            url:'inicio/cuenta-Contrapartida-Linea',type: 'POST',data: {selec:selec},
            beforeSend: function (xhr) {
                $('#process').html('<img src="../application/assets/img/cargando.gif" style="width:1em;">');
            },
            success: function (data, textStatus, jqXHR) {
            console.log(new Date());
                var html='';
                $('#process').html('');
                $('#diarioCuentaContra').html(html);
                console.log(data.value);
                $.each(data.value,function (i,v){
                    html+='<option value="'+v.BankAccountId+'">'+v.Name+'</option>'; 
                });
                $('#diarioCuentaContra').html(html);
                $('#diarioGuardarBtn').show();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $('#process').html('<img src="../application/assets/img/error.png" style="width:1em;">');
                $('#diarioResult').html(jqXHR.status);
            }
            });
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $('#process').html('<img src="../application/assets/img/error.png" style="width:1em;">');
            $('#diarioResult').html(jqXHR.status);
        }
        });
   
    $('#diarioFacturaFolio').val(factura);
    gaTrigger('BankAccounts','entity',userBranchOffice);
    $.ajax({url:'inicio/cuenta-Contrapartida',type: 'GET',contentType: 'json',
        beforeSend: function (xhr) {
            $('#process').html('<img src="../application/assets/img/cargando.gif" style="width:1em;">');
        },
        success: function (data, textStatus, jqXHR) {
            $('#process').html('');
            $('#contraPartida').html('');
           ///console.log(data.JournalName.value);
            $.each(data.JournalName.value,function (i,v){
                var selected='';
                 $.each(data.GroupUser.value,function (e,x){                        
                if(x.groupId==v.DocumentNumber){
                $("#contraPartida").append('<option value="'+v.Name+'" '+selected+'>'+v.Name+"</option>");
                }
                });
            });
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $('#process').html('<img src="../application/assets/img/error.png" style="width:1em;">');
            $('#diarioResult').html(jqXHR.status);
        }
    });
            }
            else{
            Materialize.toast('No cuenta con credito', 3000);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
        }
    });        
    
}

function mostrarModalPago2(ordenVenta,customer,paymentmode){
    gaTrigger('BankAccounts','entity',userBranchOffice);
    $.ajax({url:'inicio/cuenta-Contrapartida',type: 'GET',contentType: 'json',
        beforeSend: function (xhr) {
            $('#process').html('<img src="../application/assets/img/cargando.gif" style="width:1em;">');
        },
        success: function (data, textStatus, jqXHR) {
            $('#process').html('');
            $('#contraPartida').html('');
           console.log(data.JournalName.value);
            $.each(data.JournalName.value,function (i,v){
                var selected='';
                //console.log(mostrador);
                 $.each(data.GroupUser.value,function (e,x){                        
                if(x.groupId==v.PrivateForUserGroup){
                    //selected='selected="" ';
                $("#contraPartida").append('<option value="'+v.Name+'" '+selected+'>'+v.Name+"</option>");
                }
                });
            });
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $('#process').html('<img src="../application/assets/img/error.png" style="width:1em;">');
            $('#diarioResult').html(jqXHR.status);
        }
    });
}
function revisionMonto(monto){
    rango = (el_monto-(el_monto*1.5))*(-1);
    console.log(monto);
    console.log(el_monto);
    console.log(el_monto-rango);
    console.log(Number(el_monto) + Number(rango));
    if(monto >= (Number(el_monto) + Number(rango)) || monto <= (Number(el_monto) - Number(rango))){
        $('#diarioMontoFactura').val('');
        swal('No coincide','El monto no coincide con la orden de venta','error');
    }
}

function diarioFormaPago(fp,sucu){
    var selec = $('#diarioFPago option:selected').text();
    gaTrigger('BankAccounts','entity',userBranchOffice);
            $.ajax({
            url:'inicio/cuenta-Contrapartida-Linea',type: 'POST',data: {selec:selec},
            beforeSend: function (xhr) {
                $('#process').html('<img src="../application/assets/img/cargando.gif" style="width:1em;">');
            },
            success: function (data, textStatus, jqXHR) {
                var html='';
                $('#diarioCuentaContra').html(html);
                console.log(data.value);
                $.each(data.value,function (i,v){
                    html+='<option value="'+v.BankAccountId+'">'+v.Name+'</option>'; 
                });
                $('#diarioCuentaContra').html(html);
                if(fp=="03"){ 
                    descri = $('#descripcion').val();
                    descri = descri.replace("Cobros","");
                    descri = descri.replace(sucu,"");
                    $('#descripcion').val(descri);                   
                    $('#referenciap').show();
                    $('#labelreferencia').show();
                    el_monto = $('#diarioMontoFactura').val();
                    $('#diarioMontoFactura').val('');
                }else{
                    $('#referenciap').hide();
                    $('#labelreferencia').hide(); 
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $('#process').html('<img src="../application/assets/img/error.png" style="width:1em;">');
                $('#diarioResult').html(jqXHR.status);
            }
            });  
}
function crearDiario(){
    try{
        if($('#diarioFPago').val()=="03"){
        const {value: text0} = swal({
            type: 'info',
            title: "Ingrese de nuevo la cantidad",
            text: "para validación",
            input:'text',
            allowOutsideClick: false
        }).then(function (result){ 
        if(result==$('#diarioMontoFactura').val()){
            
            $('#diarioGuardarBtn').hide();
            var formData = $('#diarioPagoForm').serializeArray();       

            $.ajax({
                type: 'POST',
                url:'inicio/diario',
                data: formData,
                dataType: 'json',
                beforeSend: function (xhr) {
                    $('#process').html('<img src="../application/assets/img/cargando.gif" style="width:1em;">');
                    $('#diarioGuardarBtn').hide();
                },
                success: function (data, textStatus, jqXHR) {
                     $('#process').html('');
                     $('#diarioGuardarBtn').show();


                     console.log(data.JournalBatchNumber);
                     if(data.JournalBatchNumber!=undefined){
                        if(data.JournalBatchNumber!=''){
                            totalDiario=data.CreditAmount;
                            if(totalDiario<=0){
                                totalDiario=0;
                                $('#diarioGuardarBtn').hide();
                            }                    
                            $("#folioDiario").val($("#folioDiario").val()+' '+data.JournalBatchNumber);
                            swal("Guardado","Diario creado con exito con folio:"+data.JournalBatchNumber,"info");
                            $('#diarioMontoFactura').val(totalDiario);
                       } 
                     }else{
                            swal("No se creeo el diario","Usted no tiene acceso a esta cuenta de contrapartida","warning");
                            $('#process').html(data.JournalBatchNumber);
                            $('#process').attr("style","color:red");
                    }
                        
                        $('#diarioGuardarBtn').hide();
                    },
                error: function (jqXHR, textStatus, errorThrown) {
                    $('#process').html('<img src="../application/assets/img/error.png" style="width:1em;">');
                    $('#diarioResult').html(jqXHR.status);
                    $('#diarioGuardarBtn').show();
                }
            });
        }else{
            swal('Las cantidades no coinciden','Intente de nuevo','error');
            $('#diarioMontoFactura').val('');
        }

        });
    }else{
        $('#diarioGuardarBtn').hide();
            var formData = $('#diarioPagoForm').serializeArray();
            $.ajax({
                type: 'POST',
                url:'inicio/diario',
                data: formData,
                dataType: 'json',
                beforeSend: function (xhr) {
                    $('#process').html('<img src="../application/assets/img/cargando.gif" style="width:1em;">');
                    $('#diarioGuardarBtn').hide();
                },
                success: function (data, textStatus, jqXHR) {
                     $('#process').html('');
                     $('#diarioGuardarBtn').show();


                     console.log(data.JournalBatchNumber);
                     if(data.JournalBatchNumber!=undefined){
                        if(data.JournalBatchNumber!=''){
                            totalDiario=data.CreditAmount;
                            if(totalDiario<=0){
                                totalDiario=0;
                                $('#diarioGuardarBtn').hide();
                            }                    
                            $("#folioDiario").val($("#folioDiario").val()+' '+data.JournalBatchNumber);
                            swal("Guardado","Diario creado con exito con folio:"+data.JournalBatchNumber,"info");
                            $('#diarioMontoFactura').val(totalDiario);
                       } 
                     }else{
                            swal("No se creeo el diario","Usted no tiene acceso a esta cuenta de contrapartida","warning");
                            $('#process').html(data.JournalBatchNumber);
                            $('#process').attr("style","color:red");
                    }
                        
                        $('#diarioGuardarBtn').hide();
                    },
                error: function (jqXHR, textStatus, errorThrown) {
                    $('#process').html('<img src="../application/assets/img/error.png" style="width:1em;">');
                    $('#diarioResult').html(jqXHR.status);
                    $('#diarioGuardarBtn').show();
                }
            });
        }
    }
    catch (e){
        console.log(e);
    }        
}

function showPasa(){
    checkTableisDataTable();
    $("#pasaForm").html('<div class="col s4"><label>Nombre De cliente</label><input id="clienteNPasa"></div>\n\
                        <div class="col s4"><label>Orden de venta</label><input id="ovPasa"></div>\n\
                        <div class="col s4"><button class="btn" style="margin-top: 21px;" onclick="buscarPasa();"><i class="fa fa-search" ></i></button></div>\n\
                        <div class="col s6"><label style="color:red;"><b>***El total NO contiene cargos extras, en caso de ser cheque o tarjeta de crédito</b></label></div>');
    $('#ov').DataTable({
        "destroy": true, 
        "search":true,
        "paging": true, 
        "info": false,
        "order": [[ 0 , "desc" ]],
        data: [],
        columns: [ 
            {title: "Detalle"},
            {title: "Folio"},
            {title: "Fecha"},
            {title: "Código Cliente"},
            {title: "Cliente"},
            {title: "Sitio"},
            {title: "Almacén"},
            {title: "Modo de Entrega"},
            {title: "Estado"},
            {title: "Facturar"}
        ]
    });
}

function buscarPasa(){
    var nombre=$('#clienteNPasa').val();
    var ov=$('#ovPasa').val();
    if(nombre!=="" || ov !==""){
       $('#modalLoading').openModal();
       $("nav").find(".active").removeClass("active");
       $(this).parent().addClass("active");  
       $.ajax({
       url: "index/cliente-Pasa",type: "POST",dataType: 'json',data:{nombre:nombre,ov:ov},
       beforeSend: function (xhr) {
           $('#modalLoading').openModal();
            checkTableisDataTable();
       },
       success: function (data, textStatus, jqXHR) {
           $('#ov').DataTable({
               "destroy": true,
               "search":true,
               "paging": true, 
               "info": false,
               "order": [[ 0, "desc" ]],
               data: data,
               columns: [ 
                   {title: "OV"},
                   {title: "Fecha"},
                   {title: "Código Cliente"},
                   {title: "Cliente"},
                   {title: "Sitio"},
                   {title: "Almacén"},
                   {title: "Modo De Entrega"},
                   {title: "Vendedor"},
                   {title: "Estado"},
                   {title: "Remisión"},
                   {title: "Factura"},
                   {title: "Detalle"}, 
                   {title: "Total"}
               ]
           });
           $('#modalLoading').closeModal();
       },
       error: function (jqXHR, textStatus, errorThrown) {

       }
   }); 
    }
    else{
        swal('Alto!','Debe proporcionar un nombre de cliente o folio de OV','info');
    }
}


function convertirCotOffline(ov,cot,btn){
    $.ajax({
        url        : 'index/convertirMisCotOff',
        type       : 'POST',
        dataType   : 'json',
        data       : {ov: ov,cot: cot},
        beforeSend : function(){
            $('body').attr('data-msj', 'Procesando peticion ...');
            $('body').addClass('load-ajax'); 
        },
        success    : function(data){
            if (data.estatus != 'exito'){
                Materialize.toast('Algo salio mal: no se pudo convertir la cotizacion', 5000,'red');
            }else{
                if (data.SalesOrder.status != 'Fallo' ){
                    var row = $(btn).closest('tr');
                    $(row).css('background-color','#5E35B1');
                    $(row).css('color','white');
                    $(btn).attr('disabled','disabled');
                    $(row).find('input#OVConvertida').val('OV Relacionada: '+data.SalesOrder.msg);
                    $(row).find('input#COTConvertida').val('Cotizacion Relacionada: '+data.SalesQuotationHeader.SalesQuotationNumber);
                }else{
                   Materialize.toast('Algo salio mal: '+data.SalesOrder.msg, 5000,'red'); 
                }
            }
            $('body').removeClass('load-ajax');
            $('#misCotOff').click();
        }
    });
}

/** Funcion para mandar coreeo con pdf de factura
*** Recibe como parametro la url,cliente,factura,nombre de cliente y correo
**/
function EnviarPDFCliente(url,cliente,factura,nombreClte,email,transdate){
    swal({
        title             : 'Atencion',
        type              : 'info',
        input             : 'text',
        inputValue        : email,
        html              : '<span>Esta a punto de mandar el formato de factura: <span style="font-weight:799"><i><u>'+factura+'</u></i></span> al cliente <span><i><u>'+nombreClte+'</i></u></span>('+ cliente+')</span>',
        showCancelButton  : true,
        confirmButtonText : 'Enviar correo',
        cancelButtonText  : 'Cancelar',
        allowOutsideClick : false,
        preConfirm : function(result){
            return new Promise(function(resolve,reject){
                            if(result == ''){
                                reject('El campo no puede ir vacio.');
                            }
                            if(!validateEmail(result)){
                                reject('Ingrese una direccion de correo valida.');
                            }
                            resolve(result);
                        });
        }
    }).then(function(result){
        if (result){
            $.ajax({
                url        : 'index/enviarCorreoFactura',
                type       : 'POST',
                dataType   : 'json',
                data       : {url : url,transdate : transdate,email : result},
                beforeSend : function(){
                    $('body').attr('data-msj', 'Procesando peticion ...');
                    $('body').addClass('load-ajax'); 
                },
                success    : function(data){
                        $('body').removeClass('load-ajax');
                        if (data == 'enviado'){
                            Materialize.toast('Factura enviada!',5000,'green');
                        }else{
                            Materialize.toast('Ocurrio un error al enviar el correo: '+data,5000,'red');
                        }
                },
                error : function(res){
                    Materialize.toast('Ocurrio un error al enviar el correo: '+res.msg,3000,'red');
                    console.log(res);
                }
            });
        }
    });
}

/** Funcion para validar email con una expresion regular
*** acepta String y devuelve boolean
*** hace un split de los correos por el ';'
**/
function validateEmail(email) {
    var emailTest = email.split(';');
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    var valido = true;
    $(emailTest).each(function(){
        valido = re.test(String(this).toLowerCase());
        if (!valido){
            return valido;
        }
    });
    
    return valido;
}

/** Funcion para mandar pdf de cotizacion al cliente
*** Recibe como parametro la url del pdf
**/
function EnviarPDFClienteCot(url,cliente,cotizacion,nombreClte,email,fecha){
    swal({
        title             : 'Atencion',
        type              : 'info',
        input             : 'text',
        inputValue        : email,
        html              : '<span>Esta a punto de mandar el formato de cotizacion: <span style="font-weight:799"><i><u>'+cotizacion+'</u></i></span> al cliente <span><i><u>'+nombreClte+'</i></u></span>('+ cliente+')</span>',
        showCancelButton  : true,
        confirmButtonText : 'Enviar correo',
        cancelButtonText  : 'Cancelar',
        allowOutsideClick : false,
        preConfirm : function(result){
            return new Promise(function(resolve,reject){
                            if(result == ''){
                                reject('El campo no puede ir vacio.');
                            }
                            if(!validateEmail(result)){
                                reject('Ingrese una direccion de correo valida.');
                            }
                            resolve(result);
                        });
        }
    }).then(function(result){
        if (result){
            $.ajax({
                url        : 'index/enviarCorreoCotizacion',
                type       : 'POST',
                dataType   : 'json',
                data       : {url : url,email : result, fecha : fecha,cotizacion:cotizacion},
                beforeSend : function(){
                    $('body').attr('data-msj', 'Procesando peticion ...');
                    $('body').addClass('load-ajax'); 
                },
                success    : function(data){
                        $('body').removeClass('load-ajax');
                        if (data == 'enviado'){
                            Materialize.toast('Factura enviada!',5000,'green');
                        }else{
                            Materialize.toast('Ocurrio un error al enviar el correo: '+data,5000,'red');
                        }
                },
                error : function(res){
                    Materialize.toast('Ocurrio un error al enviar el correo: '+res.msg,3000,'red');
                    console.log(res);
                }
            });
        }
    });
}

/////////////////////calendario de tecnicos///////////////////////////////////////
function getEstados(){
    $.ajax({
        url: 'inicio/getestados',
        type: 'POST',
        dataType: "JSON",
        data: {  },
        beforeSend: function () {
            $('body').attr('data-msj', 'Procesando petición ...');
            $('body').addClass('load-ajax');
            $('body').append('<div class="lean-overlay"></div>');
        },
        success: function (data) {
            $('body').removeClass('load-ajax');
            $('body .lean-overlay').remove();
            $('#estado').material_select('destroy');
            $('#estado').html(data);
            $('#estado').prop('disabled',false);
            $('#estado').material_select();
        },
        error: function(res,xhr){
            console.log('res',res);
            console.log('xhr',xhr);
        }
    });
}

function getCiudades(){
    var idEstado = $('#estado option:selected').val();
    $.ajax({
        url: 'inicio/getciudades',
        type: 'POST',
        dataType: "JSON",
        data: { idEstado },
        beforeSend: function () {
            $('body').attr('data-msj', 'Procesando petición ...');
            $('body').addClass('load-ajax');
            $('body').append('<div class="lean-overlay"></div>');
        },
        success: function (data) {
            $('body').removeClass('load-ajax');
            $('body .lean-overlay').remove();
            $('#ciudad').material_select('destroy');
            $('#ciudad').html(data);
            $('#ciudad').prop('disabled',false);
            $('#ciudad').material_select();
        },
        error: function(res,xhr){
            console.log('res',res);
            console.log('xhr',xhr);
        }
    });
}

function getServicios(){
    $.ajax({
        url: 'inicio/getservicios',
        type: 'POST',
        dataType: "JSON",
        data: {  },
        beforeSend: function () {
            $('body').attr('data-msj', 'Procesando petición ...');
            $('body').addClass('load-ajax');
            $('body').append('<div class="lean-overlay"></div>');
        },
        success: function (data) {
            $('body').removeClass('load-ajax');
            $('body .lean-overlay').remove();
            $('#servicio').material_select('destroy');
            $('#servicio').html(data);
            $('#servicio').prop('disabled',false);
            $('#servicio').material_select();
        },
        error: function(res,xhr){
            console.log('res',res);
            console.log('xhr',xhr);
        }
    });
}

function getTecnicos(){
    $.ajax({
        url: 'inicio/gettecnicos',
        type: 'POST',
        dataType: "JSON",
        data: {  },
        beforeSend: function () {
            $('body').attr('data-msj', 'Procesando petición ...');
            $('body').addClass('load-ajax');
            $('body').append('<div class="lean-overlay"></div>');
        },
        success: function (data) {
            $('body').removeClass('load-ajax');
            $('body .lean-overlay').remove();
            $('#tecnico').material_select('destroy');
            $('#tecnico').html(data);
            $('#tecnico').prop('disabled',false);
            $('#tecnico').material_select();
        },
        error: function(res,xhr){
            console.log('res',res);
            console.log('xhr',xhr);
        }
    });
}

/*** 
 *funcion para validar la fecha de inicio del servicio
 */
function validateDatesIni() {
  var fecha_ini = new Date($('#fecha_ini').val() + ' 23:59:59.00');
  var fecha_act = new Date();
  if (fecha_ini < fecha_act && fecha_ini != '') {
    $('#fecha_ini').val('');
    swal('Atencion!', 'Se debe seleccionar una fecha igual o mayor a la actual', 'warning');
  }
}

/** 
 * funcion para validar la fecha de fin del servicio
 */
function validateDatesFin() {
  var fecha_fin = new Date($('#fecha_fin').val());
  var fecha_ini = new Date($('#fecha_ini').val());
  if (fecha_fin < fecha_ini && (fecha_ini != '' && fecha_fin != '')) {
    $('#fecha_fin').val('');
    swal('Atencion!', 'Se debe seleccionar una fecha igual o mayor a la de inicio', 'warning');
  }
}

/** 
 * funcion para validar los campos del formulario.
 */
function validateFields() {
  var valido = true;
  $('.required').each(function () {
    if (this.value == '' || this.value == 0) {
      valido = false;
      $(this).css('border', 'solid 1px red');
      if(this.nodeName == 'SELECT'){
        $(this).parent('div.select-wrapper').css('border', 'solid 1px red');
      }
    } else {
      $(this).css('border', '1px solid #ced4da')
      if(this.nodeName == 'SELECT'){
        $(this).parent('div.select-wrapper').css('border', 'solid 1px #ced4da');
      }
    }
  });
  return valido;
}

var service = {
  tecnico: '',
  nombre_tecnico: '',
  fecha_ini: '',
  fecha_fin: '',
  vendedor: '',
  nombre_vendedor: '',
  equipo: '',
  codigoCliente: '',
  nombreCliente: '',
  estado: '',
  ciudad: '',
  factura: '',
  comentario: '',
  tipoServicio: ''
}

/**
 * Funcion para traer los datos de la factura se laimenta con la factura.
 * 
 * @param {any} invoiceObj
 */
function changeInvoice(invoiceObj) {
  var invoiceNumber = $(invoiceObj).val();
  $.ajax({
    url: 'inicio/getinvoice',
    type: 'POST',
    dataType: "JSON",
    data: { invoiceNumber },
    beforeSend: function () {
      $('body').attr('data-msj', 'Procesando petición ...');
      $('body').addClass('load-ajax');
      $('body').append('<div class="lean-overlay"></div>');
    },
    success: function (data) {
      $('body').removeClass('load-ajax');
      $('body .lean-overlay').remove();
      var result = data.data;
      var existe = data.status;
      if (existe) {
        if(!data.data.inUse){
            var fechaFact = new Date(result.fechaFactura);
            $('#fechaFact').val(fechaFact.getDate() + "/" + (fechaFact.getMonth()+1) + "/" + fechaFact.getFullYear());
            $('#clienteServ').val(result.cliente);
            $('#vendedorServ').val(result.vendedor);
            $('#equipo').html(result.equipo);
            $('#equipo').material_select();
            service.tecnico = 'waiting...';
            service.nombre_tecnico = 'waiting...';
            service.fecha_ini = 'waiting...';
            service.fecha_fin = 'waiting...';
            service.vendedor = result.codigoVendedor;
            service.nombre_vendedor = result.vendedor;
            service.equipo = 'waiting...';
            service.codigoCliente = result.codigoCliente;
            service.nombreCliente = result.cliente;
            service.estado = 'waiting...';
            service.ciudad = 'waiting...';
            service.factura = $('#factura').val();
            service.comentario = 'waiting...';
            service.tipoServicio = 'waiting...';
        }else{
            swal('Atencion!.', 'La factura no esta aprobada aun, si desea utilizarla de nuevo es necesario el estatus de aprobada, favor de consultar a su supervisor', 'warning')
        }
      } else {
        swal('Atencion!.', 'La factura no existe o no tiene asociada una orden de venta, favor de consultar a su supervisor', 'warning')
          .then(function (res) {
            if (res) {
              location.reload();
            }
          });
      }
      $('#progress').remove();
    },
    error: function (xHrRes) {
      console.log(xHrRes);
    }
  });
}

/** 
 * funcion para guardar los servicios
 */
function saveService() {
    var valid = validateFields();
    if (valid) {
        service.tecnico = $('#tecnico option:selected').val();
        service.nombre_tecnico = $('#tecnico option:selected').text();
        service.equipo = $('#equipo option:selected').text();
        service.fecha_ini = $('#fecha_ini').val()+" 08:00:00.000";
        service.fecha_fin = $('#fecha_fin').val()+" 18:00:00.000";
        service.estado = $('#estado').val();
        service.ciudad = $('#ciudad').val();
        service.comentario = $('#comentarios').val();
        service.tipoServicio = $('#servicio option:selected').val();
        ov = $('#ovServ').val();
        var formData = new FormData();
        formData.append('service',JSON.stringify(service));
        var formDataEmail = new FormData();
        var imagesEmail = drop_Adjuntos.files;
        $(imagesEmail).each(function (index) {
            formDataEmail.append('file'+index, this);
        });
        $.ajax({
            url: 'inicio/saveservice',
            type: 'POST',
            data: formData,
            mimeType: "multipart/form-data",
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function () {
                $('body').attr('data-msj', 'Procesando petición ...');
                $('body').addClass('load-ajax');
                $('body').append('<div class="lean-overlay"></div>');
            },
            success: function (data) {
                $('body').removeClass('load-ajax');
                $('body .lean-overlay').remove();
                data = JSON.parse(data);
                if (data.status == 1) {                
                    generarAvisoMail(ov,formDataEmail);
                    swal('Exito!', data.mensaje, 'success')
                    .then(function (res) {
                      if (res) {
                        location.reload();
                      }
                    });
                } else if (data.status == 0) {
                    swal('Atencion!', data.mensaje, 'warning');
                } else {
                    swal('Error', data.mensaje, 'error');
                }
            }
        });
    } else {
        swal('Atencion!.', 'Debe capturar los campos obligatorios.', 'warning');
    }
}


/**
    funcion para calcular el tamaño de los 
    archivos agregados
**/
function getTotalPreviousUploadedFilesSize(){
   var totalSize = 0;
   drop_Adjuntos.getFilesWithStatus(Dropzone.SUCCESS).forEach(function(file){
      totalSize = totalSize + file.size;
   });
   return totalSize;
}    

/** 
    funcion para mostrar el modal de servicio
**/
var drop_Adjuntos;
var totalSizeLimit = 15*1024*1024; //15MB
// var totalSizeLimit = 50*1024; //50Kb
Dropzone.autodiscover = false;
function mostrarModalServicio(invoice,ov){
    html = '';
    html += '<div class="row">';
    html += '  <div class="col s3 md3 l3">';
    html += '    <label for="factura">Factura</label>';
    html += '    <input type="text" class="form-control required" id="factura" onchange="changeInvoice(this)" value="'+invoice+'">';
    html += '    <input type="hidden" id="ovServ">';
    html += '  </div>';
    html += '  <div class="col s3 md3 l3">';
    html += '    <label for="fechaFact">Fecha Factura</label>';
    html += '    <input type="text" class="form-control required" id="fechaFact" readonly="">';
    html += '  </div>';
    html += '  <div class="col s3 md3 l3">';
    html += '    <label for="clienteServ">Cliente</label>';
    html += '    <input type="text" class="form-control required" id="clienteServ" readonly="">';
    html += '  </div>';
    html += '  <div class="col s3 md3 l3">';
    html += '    <label for="vendedorServ">Vendedor</label>';
    html += '    <input type="text" class="form-control required" id="vendedorServ" readonly="">';
    html += '  </div>';
    html += '</div>';
    html += '<div class="row">';
    html += '  <div class="col s6 md6 l6">';
    html += '    <label for="equipo">Equipo</label>';
    html += '    <select class="form-control required" id="equipo">';
    html += '        <option value="0">Seleccione Equipo...</option>';
    html += '    </select>';
    html += '  </div>';
    html += '  <div class="col s6 md6 l6">';
    html += '    <label for="servicio">Servicio</label>';
    html += '    <select class="form-control required" id="servicio" disabled>';
    html += '        <option value="0">Seleccione Servicio...</option>';
    html += '    </select>';
    html += '  </div>';
    html += '  <div class="col s4 md4 l4" style="display:none">';
    html += '    <label for="tecnico">Tecnico</label>';
    html += '    <select class="form-control" id="tecnico" disabled>';
    html += '        <option value="0">Seleccione Tecnico...</option>';
    html += '    </select>';
    html += '  </div>';
    html += '</div>';
    html += '<div class="row">';
    html += '  <div class="col s6 md6 l6">';
    html += '    <label for="estado">Estado</label>';
    html += '    <select class="form-control required" id="estado" onchange="getCiudades()" disabled>';
    html += '        <option value="0">Seleccione Estado...</option>';
    html += '    </select>';
    html += '  </div>';
    html += '  <div class="col s6 md6 l6">';
    html += '    <label for="ciudad">Ciudad</label>';
    html += '    <select class="form-control required" id="ciudad" disabled>';
    html += '      <option value="0">Seleccione Ciudad...</option>';
    html += '    </select>';
    html += '  </div>';
    html += '  <div class="col s3 md3 l3" style="display:none">';
    html += '    <label for="fecha_ini">Fecha Inicio</label>';
    html += '    <input type="date" id="fecha_ini" class="form-control" onchange="validateDatesIni()" disabled>';
    html += '  </div>';
    html += '  <div class="col s3 md3 l3" style="display:none">';
    html += '    <label for="fecha_fin">Fecha Fin</label>';
    html += '    <input type="date" id="fecha_fin" class="form-control" onchange="validateDatesFin()" disabled>';
    html += '  </div>';
    html += '</div>';
    html += '<div class="row">';
    html += '  <div class="col s4 md4 l4"></div>';
    html += '  <div class="col s4 md4 l4">';
    html += '    <label for="comentarios">Comentarios</label>';
    html += '    <textarea class="form-control" id="comentarios" rows="5"></textarea>';
    html += '  </div>';
    html += '  <div class="col s4 md4 l4">';
    html += '   <div id="drop-adjuntoTecnicos">';
    html += '       <form action="inicio/checkfile" class="dropzone" id="dropzoneAdjuntos">';
    html += '       </form>';
    html += '   </div>';
    html += '  </div>';
    html += '</div>';
    html +='<div class="row" id="progress">';
    html +='    <div class="progress">';
    html +='        <div class="indeterminate"></div>';
    html +='    </div>';
    html +='</div>';
    swal({
        type:'question',
        text: '',
        title: 'SOLICITAR SERVICIO',
        html: html,
        allowOutsideClick: false,
        showCancelButton: true,
        width: 1500,
        allowEnterKey: false,
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Guardar Servicio',
        onOpen: function(){
            getServicios();
            getTecnicos();
            getEstados();
            $('#ciudad').material_select();
            $('#equipo').material_select();
            $('#ovServ').val(ov);
            $('#factura').trigger('change');
            drop_Adjuntos = new Dropzone("#dropzoneAdjuntos",
                                        {
                                            autoProcessQueue:true,
                                            addRemoveLinks: true,
                                            maxFilesize: 15,
                                            dictDefaultMessage: 'Arrastre archivos para agregar o de click aqui', 
                                            dictFileTooBig: 'Se ha alcanzado el limite de archivos (limite 2 archivos)',
                                            dictUploadCanceled : 'Se alcanzo la couta de bytes por enviar (limite 15MB)',
                                            dictRemoveFile : 'Eliminar archivo',
                                            thumbnailWidth : 50, thumbnailHeight: 50, resizeWidth: 50,
                                            resize: function(file) {
                                                var resizeInfo = {
                                                    srcX: 0,
                                                    srcY: 0,
                                                    trgX: 0,
                                                    trgY: 0,
                                                    srcWidth: file.width,
                                                    srcHeight: file.height,
                                                    trgWidth: this.options.thumbnailWidth,
                                                    trgHeight: this.options.thumbnailHeight
                                                };
                                                return resizeInfo;
                                            },
                                            init: function () {
                                                this.on('uploadprogress', function (file,progress,bytesSent) {
                                                    var alreadyUploadedTotalSize = getTotalPreviousUploadedFilesSize();
                                                    if((alreadyUploadedTotalSize + bytesSent) > totalSizeLimit){
                                                        this.disable();
                                                    }
                                                });
                                                this.on('removedfile', function(file){
                                                    this.enable();
                                                });
                                                this.on("success", function(file){   
                                                    $(".dz-success-mark svg").css("background", "green");
                                                    $(".dz-success-mark svg").css("width", "40%");
                                                    $(".dz-success-mark svg").css("border-radius", "40%");
                                                    $(".dz-error-mark").css("display", "none");
                                                });
                                                this.on("error", function(file) {
                                                    $(".dz-error-mark svg").css("background", "red");
                                                    $(".dz-error-mark svg").css("width", "40%");
                                                    $(".dz-error-mark svg").css("border-radius", "40%");
                                                    $(".dz-success-mark").css("display", "none");
                                                });
                                            }
                                        });
        },
        preConfirm: function(res){
            var valid = validateFields();
            var validFilesNotEmpty = true;
            var acceptedFiles = $.map(drop_Adjuntos.files, function(file){
                if (file.status == 'success'){
                    return File
                }
            });

            var errorFiles = $.map(drop_Adjuntos.files, function(file){
                if (file.status == 'canceled'){
                    return File
                }
            });

            if (acceptedFiles.length == 0){
                validFilesNotEmpty = false;
            }
            return new Promise(function(resolve,reject){
                if (!valid) {
                  reject('El campo no puede ir vacio.');
                }
                if (!validFilesNotEmpty){
                    reject('Agregar al menos un archivo a la solicitud');
                }
                resolve(true);
            });
        }
    }).then(
        function (res){
            if (res){
                saveService();
            }
        },
        function (dismiss){
            if (dismiss == 'cancel'){
                console.log('Cancelo guardar');
            }
        }
    );
}

// $(document).on("uploadprogress",'#dropzoneAdjuntos', function(file, progress, bytesSent) {
//     var alreadyUploadedTotalSize = getTotalPreviousUploadedFilesSize();
//     console.log('already',alreadyUploadedTotalSize);
//     console.log('total',totalSizeLimit);
//     if((alreadyUploadedTotalSize + bytesSent) > totalSizeLimit){
//       this.disable();
//     }
// });
///////////////////////////////////////////////////////////////////////////////////////////