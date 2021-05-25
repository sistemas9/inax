//** funcion para cargar los datos de
//** los codigos en la tabla

function cargarDatosTabla(){
	var table = $('#tablaCodigos').DataTable({
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
			        	url        : 'codigospostales/loadzipcodedata'
			        },
			        initComplete   : function(settings, json){
				        	$('#modalLoading').closeModal();
				        },
                    columns        : [
                        { title    : 'C.P.' },
                        { title    : 'COLONIA' },
                        { title    : 'TIPO COLONIA' },
                        { title    : 'MUNICIPIO' },
                        { title    : 'CIUDAD' },
                        { tittle   : 'ESTADO' }
                    ],
                    language       : {
                        url        : "../application/assets/js/spanish.json"
                    }
                });
}

//** Funcion para actualizar el status de 
//** los codigos postales en la tabla

function cambiarStatus(zipCode,asentamiento,status){
	$.ajax({
	    type     : 'POST',
	    url      : 'codigospostales/updatezipcodedata',
	    data     : {zipCode,asentamiento,status},
	    dataType : 'json',
	    beforeSend: function (xhr) {
	       	$('#modalLoading').openModal();
	    },
	    success: function (data) {
	    	$('#modalLoading').closeModal();
	    },
	    error : function(xhr,status){
	    	console.log('error');
	    	$('#modalLoading').closeModal();
	    }
	});
}

//** funcion para actualizar datos
//** de un registro de codigo postal

function actualizarCodigo(idCodigo,colonia,tipoColonia,codigoPostal,municipio,ciudad){
	html  = '';
	html += '<div class="col l2 m2 s2">';
	html += '	<label for="codigoPostal">COD. POSTAL</label>';
	html += '	<input type="text" name="codigoPostalModal" id="codigoPostalModal" class="dataZipCode" value="'+codigoPostal+'" readonly>';
	html += '</div>';
	html += '<div class="col l2 m2 s2">';
	html += '	<label for="colonia">COLONIA</label>';
	html += '	<input type="text" name="coloniaModal" id="coloniaModal" class="dataZipCode" value="'+colonia+'">';
	html += '</div>';
	html += '<div class="col l2 m2 s2">';
	html += '	<label for="tipoColonia">TIPO COLONIA</label>';
	html += '	<select name="tipoColoniaModal" id="tipoColoniaModal" class="browser-default" style="background-color: #eaeaea;border: solid 1px #ddd">';
	html += '		<option value="'+tipoColonia+'" selected>'+tipoColonia+'</option>';
	html += '		<option value="Condominio">Condominio</option>';
	html += '		<option value="Ranchería">Ranchería</option>';
	html += '		<option value="Equipamiento">Equipamiento</option>';
	html += '		<option value="Ejido">Ejido</option>';
	html += '		<option value="Poblado comunal">Poblado comunal</option>';
	html += '		<option value="Aeropuerto">Aeropuerto</option>';
	html += '		<option value="Conjunto habitacional">Conjunto habitacional</option>';
	html += '		<option value="Colonia">Colonia</option>';
	html += '		<option value="Zona industrial">Zona industrial</option>';
	html += '		<option value="Pueblo">Pueblo</option>';
	html += '		<option value="Estación">Estación</option>';
	html += '		<option value="Fraccionamiento">Fraccionamiento</option>';
	html += '		<option value="Rancho">Rancho</option>';
	html += '		<option value="Residencial">Residencial</option>';
	html += '		<option value="Gran usuario">Gran usuario</option>';
	html += '		<option value="Unidad habitacional">Unidad habitacional</option>';
	html += '		<option value="Club de golf">Club de golf</option>';
	html += '		<option value="Parque industrial">Parque industrial</option>';
	html += '		<option value="Zona federal">Zona federal</option>';
	html += '		<option value="Zona comercial">Zona comercial</option>';
	html += '		<option value="Otro">Otro</option>';
	html += '	</select>';
	html += '</div>';
	html += '<div class="col l2 m2 s2">';
	html += '	<label for="municipio">MUNICIPIO</label>';
	html += '	<input type="text" name="municipioModal" id="municipioModal" class="dataZipCode" value="'+municipio+'">';
	html += '</div>';
	html += '<div class="col l2 m2 s2">';
	html += '	<label for="ciudad">CIUDAD</label>';
	html += '	<input type="text" name="ciudadModal" id="ciudadModal" class="dataZipCode" value="'+ciudad+'">';
	html += '</div>';
	swal({
        type: 'info',
        title: "ACTUALIZAR CODIGO POSTAL",
        html: html,
        showCancelButton  : true,
        confirmButtonText : 'Actualizar',
        cancelButtonText  : 'Cancelar',
        allowOutsideClick: false,
        preConfirm : function(result){
            return new Promise(function(resolve,reject){
                            codigoPostal = $('#codigoPostalModal').val();
                            colonia = $('#coloniaModal').val();
                            tipoColonia = $('#tipoColoniaModal').val();
                            municipio = $('#municipioModal').val();
                            ciudad = $('#ciudadModal').val();
                            if(codigoPostal == '' || colonia == '' || tipoColonia == '' || municipio == '' || ciudad == ''){
                                reject('Alguno de los campos esta vacio, favor de asegurarse de llenar todos los campos.');
                            }
                            resolve(result);
                        });
        }
    }).then(function (result){ 
        if(result){
            codigoPostal = $('#codigoPostalModal').val();
            colonia = $('#coloniaModal').val();
            tipoColonia = $('#tipoColoniaModal').val();
            municipio = $('#municipioModal').val();
            ciudad = $('#ciudadModal').val();
            $.ajax({
			    type     : 'POST',
			    url      : 'codigospostales/updatezipcodedataall',
			    data     : {codigoPostal,colonia,tipoColonia,municipio,ciudad,idCodigo},
			    dataType : 'json',
			    beforeSend: function (xhr) {
			       	$('#modalLoading').openModal();
			    },
			    success: function (data) {
			    	$('#tablaCodigos').DataTable().ajax.reload( function(data) { $('#modalLoading').closeModal(); });
			    },
			    error : function(xhr,status){
			    	console.log('error');
			    	$('#modalLoading').closeModal();
			    }
			});
        }
    })
}