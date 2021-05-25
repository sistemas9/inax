var puntosCliente = 0;
var codigoCliente = '';
var nombreCliente = '';
var Cotizacion = {
	header : {
		'CurrencyCode': ''
        ,'LanguageId':''
        ,'dataAreaId': ''
        ,'DefaultShippingSiteId': ''
        ,'RequestingCustomerAccountNumber': ''
        ,'QuotationTakerPersonnelNumber' : ''
        ,'QuotationResponsiblePersonnelNumber' : ''
        ,'SalesOrderOriginCode' : ''
        ,'SalesTaxGroupCode': ''
        ,'DeliveryModeCode' : ''
        ,'CustomersReference' : ''
        ,'CustomerPaymentMethodName' : ''
        ,'SalesQuotationNumber' : ''
	},
    lines : [],
    setHeader : function(data){
        this.header.CurrencyCode                          = 'MXN';
        this.header.LanguageId                            = 'es-MX';
        this.header.dataAreaId                            = 'ATP';
        this.header.DefaultShippingSiteId                 = 'CHIH';
        this.header.RequestingCustomerAccountNumber       = data.RequestingCustomerAccountNumber;
        this.header.QuotationTakerPersonnelNumber         = data.QuotationTakerPersonnelNumber;
        this.header.QuotationResponsiblePersonnelNumber   = data.QuotationResponsiblePersonnelNumber;
        this.header.SalesOrderOriginCode                  = 'MOTV_00012';
        this.header.SalesTaxGroupCode                     = 'VTAS';
        this.header.DeliveryModeCode                      = 'RECIBE';
        this.header.CustomerPaymentMethodName             = '01';
        this.lines = [];
    },
    setLines : function(data){
        lineData = {};
        lineData.SalesQuotationNumber   = data.SalesQuotationNumber;
        lineData.dataAreaId             = 'ATP';
        lineData.ItemNumber             = data.ItemNumber;
        lineData.RequestedSalesQuantity = '1';
        lineData.ShippingWarehouseId    = 'CHIHCONS';
        lineData.FixedPriceCharges      = 0;
        lineData.SalesTaxGroupCode      = 'VTAS';
        lineData.STF_Category           = 'CONSUMIBLES';
        lineData.SalesPrice             = 0;
        this.lines.push(lineData);
    },
    removeLines : function(data){
        $(this.lines).each(function(index){
            if (this.ItemNumber === data.ItemNumber){
                Cotizacion.lines.splice(index,1);
            }
        });
    },
    createQuotationDynamics : function(){
        $.ajax({
            beforeSend  : function(){
                $('body').attr('data-msj', 'Procesando Petición ...');
                $('body').addClass('load-ajax');
            },
            url         : "PuntosLealtad/newdocument",
            type        : "POST",
            dataType    : "JSON",
            data        : Cotizacion.header,
            success: function (data){
                if (data.estatus === 'Exito'){
                    Cotizacion.header.SalesQuotationNumber = data.CTZN;
                    $(Cotizacion.lines).each(function(){
                        this.SalesQuotationNumber = data.CTZN;
                    });
                    Cotizacion.createQuotationLinesDynamics();
                }
                //$('body').removeClass('load-ajax');
            }
        });
    },
    createQuotationLinesDynamics : function(){
        $.ajax({
            beforeSend  : function(){
                $('body').attr('data-msj', 'Procesando Petición ...');
                $('body').addClass('load-ajax');
            },
            url         : "PuntosLealtad/setlines",
            type        : "POST",
            dataType    : "JSON",
            data        : {'lines' : Cotizacion.lines},
            success: function (data){
                updatePuntosCliente();
                $('body').removeClass('load-ajax');
            }
        });
    }
}
/**
** funcion para cargar los clientes y renderizar el select para autocomplete
**/
function cargarClientes(data){
	clients = [];
    $.map(data, function (item){
        var info 				= {label: "No hay Resultados",value: " "};
        info['label'] 			= $.trim(item.ClaveCliente) + " - " + $.trim(item.Nombre);
        info['value'] 			= $.trim(item.ClaveCliente);
        info['nombre'] 			= $.trim(item.Nombre);
        info['descuentoLinea'] 	= $.trim(item.DescuentoLinea);
        info['conjuntoPrecios'] = $.trim(item.ConjuntoPrecios);
        info['puntosAvance'] 	= $.trim(item.PuntosAvanceLealtad);
        clients.push(info);
    });
    seleccion 		= [{label: "Seleccione un cliente",value:""}];
    clienteTotal 	= seleccion.concat(clients);
    $('#clientes').selectize({
        persist     : false,
        options     : clients,
        labelField  : "label",
        valueField  : "value",
        searchField : ['label','value'],
        placeholder : "Selecione un cliente...",
        render      : {
            item    : function(item, escape) {
                    return  '<div style="font-size:11px;" data-linedisc="'+item.descuentoLinea+'" data-conjuntoprecios="'+item.conjuntoPrecios+'" data-puntos="'+item.puntosAvance+'" data-nombrecli="'+item.nombre+'">' +
                                (item.label ? '<span>' + escape(item.label) + '</span>' : '') +
                            '</div>';
                },
            option  : function(item, escape) {
                var label   = item.value || item.value;
                var caption = item.label ? item.label : null;
                return  '<div>' +
                            '<i class="fa fa-address-book" aria-hidden="true" style="color:#337AB7;padding: 5px;"></i>' +
                            (caption ? '&nbsp<span class="caption" style="font-size:11px">' + escape(caption) + '</span>' : '') +
                        '</div>';
            }
        },
        onChange    : function(item,escape){
        	codigoCliente  = item;
            option 		   = $('#clientes').siblings('.selectize-control').children('.selectize-input').children('div');
            puntos 		   = $(option).data('puntos');
            nombreCliente  = $(option).data('nombrecli');
            puntosCliente  = puntos;
            $('#puntosRestantes').html('<b><u>Puntos Restantes</u></b>: '+puntosCliente);
            $('#puntos').html(puntos);
            data = {'RequestingCustomerAccountNumber' : codigoCliente, 'QuotationTakerPersonnelNumber' : personnelNumber, 'QuotationResponsiblePersonnelNumber' : personnelNumber}
            Cotizacion.setHeader(data);
            $('.itemContainer').each(function(){
            	$(this).removeClass('itemContainerSelected');
            	puntosIt = $(this).data('puntos');
            	if (puntos >= puntosIt){
            		$(this).removeClass('itemContainerDis');
            		$(this).addClass('itemContainerAvail');
            		childImgn = $(this).children('.imgContainer');
            		childDesc = $(this).children('.descContainer');
            		$(childImgn).removeClass('itemDisabled');
            		$(childImgn).addClass('itemAvailable');
            		$(childDesc).removeClass('itemDisabled');
            		$(childDesc).addClass('itemAvailable');
            	}else{
            		$(this).removeClass('itemContainerAvail');
            		$(this).addClass('itemContainerDis');
            		childImgn = $(this).children('.imgContainer');
            		childDesc = $(this).children('.descContainer');
            		$(childImgn).removeClass('itemAvailable');
            		$(childImgn).addClass('itemDisabled');
					$(childDesc).removeClass('itemAvailable');
					$(childDesc).addClass('itemDisabled');
            	}
            });
        }
    });
}

/**
** funcion para renderizar los articulos que se van a mostrar para intercambiar puntos
**/
function renderArticulosCanvas(){
	var row 	  = 0	
	var rowArray  = [];
	var numOfRows = Math.ceil(items.length / 6) - 1;
	/////////////////construccion del canvas/////////////////////////////////////////////////////////////////////////////////////////
	$('#contenedorArticulos').empty();
	var cont = 0;
	for(row == 1;row<=numOfRows;row++){
		var col 	 = 0;
		var rowHtml  = '';
		var colArray = [];
		rowHtml      += '<div class="col-md-12" style="margin:3px !important">';
		for (col = 1; col <= 6; col++) {
			if ( typeof(items[cont]) !== 'undefined' ){
				rowHtml += '<div class="col-md-2">';
				rowHtml += '	<div class="row itemContainerDis itemContainer" style="padding:20px;margin : 1px;" data-puntos="'+items[cont].PuntosAvance+'" data-itemnumber="'+items[cont].ItemNumber+'" data-descripcion="'+items[cont].Descripcion+'">';
				rowHtml += '        <span class="cuantosArtis" style="position: absolute; top: 7px; right: 20px; background-color: #FF5B57; color: white; border-radius: 20px; font-weight: bolder; display: none;">&nbsp;0&nbsp;</span>';
                rowHtml += '		<div class="col-md-12 itemDisabled imgContainer" style="text-align:center">';
				rowHtml += '				<img src="../application/assets/img/imagepdf/noimage.png" style="width: 50%; display: block; margin: 0 auto;">';
				rowHtml += '			<span style="font-size: 20px;font-weight:bolder;color:red;">'+items[cont].PuntosAvance+' puntos</span>';
				rowHtml += '		</div>';
				rowHtml += '		<div class="col-md-12 itemDisabled descContainer" style="padding:0px">';
				rowHtml += '			<div class="col-md-12" style="margin-top:5px;">';
				rowHtml += '				<div style="font-size: 11px;height:25px;overflow:auto"><span><b><u>Codigo</u></b>: '+items[cont].ItemNumber+'</span></div>';
				rowHtml += '			</div>';
				rowHtml += '			<div class="col-md-12">';
				rowHtml += '				<div style="font-size: 11px;height:45px;overflow:auto"><span><b><u>Descripcion</u></b>: '+items[cont].Descripcion+'</span></div>';
				rowHtml += '			</div>';
				rowHtml += '		</div>';
				rowHtml += '	</div>';
				rowHtml += '</div>';
				cont++;
			}
		}
		rowHtml 	 += '</div>';
		colArray.push(rowHtml);
		rowArray.push(colArray);
	}

	$(rowArray).each(function(){
		$(this).each(function(){
			$('#contenedorArticulos').append(this);
		});
	});
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

/**
** funcion para recalcular puntos y setear clase de seleccionado al item
**/
function recalcularPuntos(puntos,action){
	if ( action === 'reduce' ){
		puntosCliente -= puntos;
	}else if ( action === 'add' ){
		puntosCliente += puntos;
	}
	$('#puntosRestantes').html('<b><u>Puntos Restantes</u></b>: '+puntosCliente);
}

/**
** funcion para confirmar intercambio y crear la cotizacion
**/
function confirmExchange(){
    html  = '';
    html += '<table class="table" style="width: 100%;border-collapse: collapse;" border="1">';
    html += '    <thead style="font-size: 12px;background-color: #01579B; color: white">';
    html += '        <tr>';
    html += '           <th>CANT.</th>';
    html += '           <th>COD. ART.</th>';
    html += '           <th>DESCRIPCION</th>';
    html += '        </tr>';
    html += '    </thead>';
    html += '    <tbody>';
    $('.itemContainerSelected').each(function(){
        ItemNumber  = $(this).data('itemnumber');
        Descripcion = $(this).data('descripcion');
        html += '<tr style="font-size: 10px">';
        html += '    <td>1</td>';
        html += '    <td>'+ItemNumber+'</td>';
        html += '    <td style="text-align: left">'+Descripcion+'</td>';
        html += '</tr>';
    });
    html += '    </tbody>';
    html += '</table>';
    swal({
        title:'<i>CONFIRMACIÓN</i>',
        html:'<p>Esta seguro de confirmar el siguiente intercambio para el cliente: <b>'+codigoCliente+' - '+nombreCliente+'<b></p><br>'+html,
    }).then((result) => {
        if( result ){
            Cotizacion.createQuotationDynamics();
        }
    });
}

/**
** Funcion para actualizar los puntos del cliente en la base de datos
**/
function updatePuntosCliente(){
    $.ajax({
        url         : "PuntosLealtad/updatepuntos",
        type        : "POST",
        dataType    : "JSON",
        data        : {'puntosRestantes' : puntosCliente, 'cliente' : codigoCliente},
        success: function (data){
            if( data.estatus === 'Exito' ){
                detallePuntos = '<br><span>Le quedan un total de <b><u>'+puntosCliente+' puntos</u></b> restantes y se creo la cotizacion: '+Cotizacion.header.SalesQuotationNumber+'</span>';
                swal('Felicidades!',data.msg+detallePuntos,'success');
                $('#puntos').html(puntosCliente);
                location.reload();
            }else{
                swal('Atencion!',data.msg,'error');
            }
        }
    });
}