/** 
*** funcion para cargar los articulos en el select
**/
function cargarArticulos(data){
	articulos = [];
    $.map(data, function (item){
        var info 				= {label: "No hay Resultados",value: " "};
        info['label'] 			= $.trim(item.ItemNumber) + " - " + $.trim(item.ProductSearchName);
        info['value'] 			= $.trim(item.ItemNumber);
        info['descripcion'] 	= $.trim(item.ProductSearchName);
        info['aplicaPuntos'] 	= $.trim(item.AplicaPuntos);
        articulos.push(info);
    });
    seleccion 		= [{label: "Seleccione un articulo",value:""}];
    articulosTotal 	= seleccion.concat(articulos);
    $('#articulos').selectize({
        persist     : false,
        options     : articulos,
        labelField  : "label",
        valueField  : "value",
        searchField : ['label','value'],
        placeholder : "Selecione un articulo...",
        render      : {
            item    : function(item, escape) {
                    return  '<div style="font-size:11px;">' +
                                (item.label ? '<span>' + escape(item.label) + '</span>' : '') +
                            '</div>';
                },
            option  : function(item, escape) {
                var label   = item.value || item.value;
                var caption = item.label ? item.label : null;
                return  '<div>' +
                            '<i class="fa fa-cubes" aria-hidden="true" style="color:#337AB7;padding: 5px;"></i>' +
                            (caption ? '&nbsp<span class="caption" style="font-size:11px">' + escape(caption) + '</span>' : '') +
                        '</div>';
            }
        },
        onChange    : function(item,escape){
        }
    });
}

/** 
*** Funcion para cargar dataTable con datos de articulos
**/
function cargarDataTable(data){
	var tableAplica = $('#tablaAplicaPuntos').DataTable({
						destroy        : true,
				        search         : true,
				        paging         : true, 
				        info           : false,
				        order          : [[ 0, "asc" ]],
				        ajax           : {
				        	beforeSend : function(){
				        		$('body').attr('data-msj', 'Procesando Petición ...');
				        		$('body').addClass('load-ajax');
				        	},
				        	processing : true,
            				serverSide : true,
				        	url        : 'Configpuntoslealtad/getdatosdatatable'
				        },
				        drawCallback   : function(settings) {
						   $('body').removeClass('load-ajax');
						},
				        initComplete   : function(settings, json){
				        	$('body').removeClass('load-ajax');
				        },
				        createdRow     : function(row, data, dataIndex){
				        	if (data[4] == '1' && data[5] > '0'){
				        		$(row).addClass( 'aplicaPuntosCanje' );
				        	}else if (data[4] == '0' && data[5] > '0'){
				        		$(row).addClass( 'soloCanje' );
				        	}else if (data[4] == '1' && data[5] == '0'){
				        		$(row).addClass( 'soloAplica' );
				        	}
				        },
				        columns        : [ 
				            {title     : "CODIGO ARTICULO",width: '15%'},
				            {title     : "DESCRIPCION",width: '60%'},
				            {title     : "APLICA PUNTOS",width: '15%'},
				            {title     : "PUNTOS CANJE",width: '10%'}
				        ],
				        language       : {
				                url    : "../application/assets/js/spanish.json"
				            }
					});
}

/**
*** Funcion para quitar aplicapuntos de la tabla articulos
**/
function removeAplica(codigo){
	$.ajax({
	    type     : 'POST',
	    url      :'Configpuntoslealtad/removeAplicaPuntos',
	    data     : {codigo:codigo},
	    dataType : 'json',
	    beforeSend: function (xhr) {
	       	$('body').attr('data-msj', 'Procesando Petición ...');
			$('body').addClass('load-ajax');
	    },
	    success: function (data) {
	    	if (data == '1'){
	    		$('body').removeClass('load-ajax');
	    		$('#tablaAplicaPuntos').DataTable().ajax.reload();
	    	}
	    }
	});
}

/**
*** Funcion para quitar puntos de canje de la tabla articulos
**/
function removePuntos(codigo){
	$.ajax({
	    type     : 'POST',
	    url      :'Configpuntoslealtad/removePuntos',
	    data     : {codigo:codigo},
	    dataType : 'json',
	    beforeSend: function (xhr) {
	       	$('body').attr('data-msj', 'Procesando Petición ...');
			$('body').addClass('load-ajax');
	    },
	    success: function (data) {
	    	if (data == '1'){
	    		$('body').removeClass('load-ajax');
	    		$('#tablaAplicaPuntos').DataTable().ajax.reload();
	    	}
	    }
	});
}