/* funcion para cargar la tabla de msi */
function cargarDatosMSI(){
	var table = $('#tablaMSI').DataTable({
                    destroy        : true,
                    search         : true,
                    paging         : true, 
                    info           : false,
                    ajax           : {
			        	beforeSend : function(){
			        		$('#modalLoading').openModal();
			        	},
			        	processing : true,
        				serverSide : true,
			        	url        : 'msi/loadmsidata'
			        },
			        initComplete   : function(settings, json){
				        	$('#modalLoading').closeModal();
				        },
                    columns        : [
                        { title    : 'TIPO PAGO' },
                        { title    : 'DESCRIPCIÓN' },
                        { title    : 'VALOR % (CARGO)' },
                        { title    : 'MONTO MINIMO' },
                        { title    : 'ACCIONES' }
                    ],
                    language       : {
                        url        : "../application/assets/js/spanish.json"
                    }
                });
}
/* Esta funcion es para obtener los valores que se van a editar del porcentaje de cargo*/
function editarTP(id_tp,tipo,valor,monto_minimo){
	html  = '';
	html += '<div class="row">'
	html += '	<div class="col l6 m6 s6">';
	html += '		<label style="font-size:12px;">TIPO</label>';
	html += '		<input type="text" value="'+tipo+'" style="font-size:12px;" />';
	html += '	</div>';
	html += '	<div class="col l3 m3 s3">';
	html += '		<label>CARGO %</label>';
	html += '		<input type="text" id="porc_cargo" value="'+valor+'"/>';
	html += '		<input type="hidden" id="id_tp" value="'+id_tp+'"/>';
	html += '	</div>';
	html += '	<div class="col l3 m3 s3">';
	html += '		<label>MONTO MINIMO</label>';
	html += '		<input type="text" id="monto_minimo" value="'+monto_minimo+'"/>';
	html += '	</div>';
	html += '</div>';
	swal({
            title: 'Ingrese un cargo',
            html:   html,
            allowOutsideClick: false,
        	allowEnterKey: false,
            showCancelButton: true,
            confirmButtonText:'Modificar cargo',
            cancelButtonText:'Cancelar',
            preConfirm: function(res){
            	var cargo = $('#porc_cargo').val();
            	return new Promise( function (resolve, reject) {
            		if (cargo != ''){
            			resolve(true);
            		}else{
            			reject('¡Debe ingresar una cantidad en el campo cargo.!');
            		}
            	});
            }
        }).then(function(result){
        	if (result){
        		var cargo = $('#porc_cargo').val();
        		var monto = $('#monto_minimo').val();
        		setMsiCargos(id_tp,cargo,monto);
        	}
        },
        function (dismiss){
        	if (dismiss == 'cancel'){
        		console.log('cancel cagos');
        	}
        });
}
/*Esta funcion es para asignar los valores de pordentaje de cargo en los tipos de pago*/
function setMsiCargos(id_tp,cargo,monto){
	$.ajax({
        url: "msi/setcargo",
        type: "post",
        dataType: "json", 
        data: { 'id_tp'   : id_tp
                ,'cargo'  : cargo
                ,'monto'  : monto
            },
        beforeSend: function (xhr){
            $('#modalLoading').openModal();
        },
        success: function (data){
            $('#modalLoading').closeModal();
            if (data.status){
            	swal({
            		title: '!Exito.!',
            		text: 'Cargo modificado exitosamente.',
            		type: 'success',
            		allowOutsideClick: false,
        			allowEnterKey: false,
            	}).then(function(result){
            		if (result){  $('#tablaMSI').DataTable().ajax.reload(); $('#modalLoading').closeModal(); }
            	});
            }else{
            	swal({
            		title: '!Error.!',
            		text: 'Ocurrio un error al modificar el cargo.',
            		type: 'error',
            		allowOutsideClick: false,
        			allowEnterKey: false,
            	}).then(function(result){
            		if (result){  $('#tablaMSI').DataTable().ajax.reload(); $('#modalLoading').closeModal(); }
            	});
            }
        }
    });
}