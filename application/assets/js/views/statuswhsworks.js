$(document).on('keydown','#ordenVta',function(event){
	if (event.which == 13){
		$('#tablaOVsLiberadas tbody tr').removeClass('selected');
		ov = 'OV-' + $(this).val();
		$.ajax({ 
            url: "statuswhsworks/getdataworklines",
            type: "POST", 
            dataType: "json", 
            data: { ov : ov},
            beforeSend : function(){
            	$('#modalLoading').openModal();
            },
        	success: function (data){
        		if (data != 'nodata'){
	        		$('#tablaDetalleLineas tbody').html(data);
	        		var row = $('*[data-ov="'+ov+'"]').closest('tr');
	        		$(row).trigger('click');
	        		$(row).get(0).scrollIntoView();
	        		$('#modalLoading').closeModal();
	        	}else{
	        		swal('Aviso','La OV no tiene trabajos.','warning');
	        		$('#modalLoading').closeModal();
	        	}
	        	$('#ordenVta').val('');
        	}
    	});
	}
});

function cargarTabla(){
	$.ajax({ 
            url: "statuswhsworks/getdataov",
            type: "POST", 
            dataType: "json", 
            data: {},
            beforeSend : function(){
            	$('#modalLoading').openModal();
            },
        	success: function (data){
        		if (data != 'nodata'){
	        		$('#tablaOVsLiberadas tbody').html(data);
	        		$('#modalLoading').closeModal();
	        	}else{
	        		swal('Aviso','La OV no tiene trabajos.','warning');
	        	}
        	}
    	});
}

function showWorks(ov){
	$('#ordenVta').val('');
	$.ajax({ 
            url: "statuswhsworks/getdataworklines",
            type: "POST", 
            dataType: "json", 
            data: { ov : ov},
            beforeSend : function(){
            	$('#modalLoading').openModal();
            },
        	success: function (data){
        		if (data != 'nodata'){
	        		$('#tablaDetalleLineas tbody').html(data);
	        		$('#modalLoading').closeModal();
	        	}else{
	        		swal('Aviso','La OV no tiene trabajos.','warning');
	        		$('#modalLoading').closeModal();
	        	}
        	}
    	});
}

function refreshWorks(){
	var ov = $('#tablaOVsLiberadas tbody tr.selected').children('.SalesOrderNumber').data('ov');
	if (ov !== '' && typeof(ov) !== 'undefined'){
		showWorks(ov);		
	}else{
		swal('Atencion!','Debe Seleccionar una ov.','warning');
	}
}