
$(document).ready(function() {

	$('#cliente').val('');
	$('#factura').val('');

    $.ajax({
	url: "Ordenesdevolucion/clientes",
	type: "POST",
	cache: false,
	dataType: 'json',
	beforeSend: function (xhr) {
		console.log(xhr);
	},
	success: function (data) {
		console.log(data);
		//clientes=data;
		cargarClientes(data);
		//swal(data);
	},
	error: function (x){
		console.log(x);
	}
	});
$.ajax({
	url: "Ordenesdevolucion/razones",
	type: "POST",
	cache: false,
	dataType: 'json',
	beforeSend: function (xhr) {
		console.log(xhr);
	},
	success: function (data) {
		console.log(data.value);
		html="";
		$.each(data.value,function (i,v){
                html+='<option value="'+v.ReasonCodeId+'">'+v.Description+'</option>';
            });
		$('#razon').append(html);
	},
	error: function (x){
		console.log(x);
	}
	});





});
//var clientes ="";


function enviar() {
	var factura = $('#factura').val();//'ATP-000058';
	validateReturnOrder(factura).then(function(res){
		$('#ordenDevolucionCreada').html('');
		if (res.status == 'exito'){
			$('#cliente').val(res.msg[1]);
			$('#error_msg').remove();
			var cliente = $('#cliente').val();//'ATP-000001';
			$('#ovOrigen').val(res.msg[2]);
			$.ajax({
				url: "Ordenesdevolucion/get-Return-Order-Headers",
				type: "POST",
				cache: false,
				dataType: 'json',
				data: {cliente: cliente,factura:factura},
				beforeSend: function (xhr) {
					$('body').attr('data-msj', 'Procesando petición ...');
   					$('body').addClass('load-ajax');
				},
				success: function (data) {
					$('body').removeClass('load-ajax');
					var html ='';
					var html2 = '';
					var html3 = '';
					$('#artis').html(html);
					$('#artis2').html(html);
					$('#tblOrdenesdevolucion').html('');
					var heade;
					var row;
					heade="<tr><th>Articulos</th><th>Cantidad</th><th>Lote</th></tr>";
					$('#tblOrdenesdevolucion').append(heade);
					$.each(data['datos'].value,function (i,v){
						html=html + '<input name="lineas['+i+']" id="lineas['+i+']" class="form-control inputText365N" value="'+v.LineDescription+'"></input>';//$("#lineas-1").data("inventory")
						html2=html2 + '<input type="number" name="cantidad['+i+']" id="cantidad['+i+']" class="form-control inputText365N" value="'+data['totales'][i]+'"></input>';
						html3=html3 + '<input name="lote['+i+']" id="lote['+i+']" class="form-control inputText365N" value="'+v.InventoryLotId+'" readonly></input>';
						row = '<tr><td>' + html + '</td><td>' + html2 + '</td><td>' + html3 + '</td></tr>';
						$('#tblOrdenesdevolucion').append(row);
						row = '';
						html = '';
						html2 = '';
						html3 = '';
					});
					let selectHtml = '';
					data.employees.value.forEach(employee => {
						selectHtml += `<option value="${employee.PersonnelNumber}">${employee.Name}</option>`;
					});
					$('#secretarioventa').append(selectHtml);
					$('#responsableventa').append(selectHtml);
					$('#secretarioventa').selectize();
					$('#responsableventa').selectize();
					$('#secretariodiv').show();
					$('#responsablediv').show();
					$('#firsRow').hide();
					$('#devolver').show();
				},
				error: function (x){
					console.log(x);
				}
			});
		}
	}, function dismiss(dismiss){
		if (dismiss.status == 'error'){
			$('#error_msg').remove();
			var divParent = $('#ordenDevolucionCreada').parent('div');
			html = '';
			html += '<div id="error_msg" class="alert alert-danger" role="alert" style="text-align: center; font-size: 32px;">';
			html += dismiss.msg;
			html += '	<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
			html += '		<span aria-hidden="true">&times;</span>';
			html += '	</button>';
			html += '</div>';

			$(divParent).append(html);

			$('#tblOrdenesdevolucion').html('');
			var heade;
			var row;
			heade="<tr><th>Articulos</th><th>Cantidad</th><th>Lote</th></tr>";
			$('#tblOrdenesdevolucion').append(heade);
		}
	});
}


function devolvers(){
	var datos = $('#devolucion').serializeArray();
	$('#ordenDevolucionCreada').html('');
	$('#devolver').prop('disabled',true);
	$.ajax({
	url: "Ordenesdevolucion/devolver",
	type: "POST",
	cache: false,
	dataType: 'json',
	data: datos,
	beforeSend: function (xhr) {
		console.log(xhr);
	},
	success: function (data) {
		swal('Atención', data['respuesta'], 'warning');
		var respo=data['respuesta'];
		
		if(!respo.includes("La cantidad")){
		$('#ordenDevolucionCreada').html("La orden de devolucion creada es: "+data['ordenventa']);
		$('#cliente').val('');
		$('#factura').val('');
		$('#tblOrdenesdevolucion').html('');
		}else{
			$('#devolver').prop('disabled',false);
		}
	},
	error: function (x){
		console.log(x);
	}
	});
}

function mapeoClte(valor,arreglo,request){
    return $.map(arreglo,function(clte){
        posNombre = clte.nombre.indexOf(valor.toUpperCase());
    	posArticu = clte.value.indexOf(valor.toUpperCase());
    	if (request.term.indexOf('*') < 0){
                if ( (posNombre >= 0) || (posArticu >= 0) ){
                    return clte;
                }
    	}
        else{
            if ( (posNombre >= 0) ){
		      return clte;
            }
    	}
    });
}
clients =[];


function cargarClientes(data){
    clients = [];
    $.map(data, function (item){
        var info = {label: "No hay Resultados",value: " "};
        info["label"] = $.trim(item.ClaveCliente) + " - " + $.trim(item.Nombre);
        info["value"] = $.trim(item.ClaveCliente);
        info["nombre"] = $.trim(item.Nombre);
        clients.push(info);
    });
}


var factValida = false;
function validateReturnOrder(factura){
	return new Promise(
		function (resolve,reject){
			$.ajax({
				url: "Ordenesdevolucion/validate",
				type: "POST",
				cache: false,
				dataType: 'json',
				data: {factura:factura},
				beforeSend: function (xhr) {
					$('body').attr('data-msj', 'Validando factura ...');
   					$('body').addClass('load-ajax');
				},
				success: function (data) {
					$('body').removeClass('load-ajax');
					if (data.status == 'exito'){
						factValida = true;
						resolve({status:'exito', factValida, 'msg': data.msg});
					}else{
						reject({status:'error','msg':data.msg,'stacktrace': Error(data.msg)});
					}
				},
				error: function(xhr){
					console.log(xhr);
				}
			});
		});
}

$("#cliente").autocomplete({
            autoFocus: true,
            minLength: 3,
            source: function (request, response) {
            	//clients = [];
            	console.log(request);
                var buscar = request.term.split('*');
                itemClte = [];
                $(buscar).each(function (index, valor) {
                    if (index == 0) {
                    	console.log(valor);
                        itemClte = mapeoClte(valor, clients, request);
                    } else {
                        itemClte = mapeoClte(valor, itemClte, request);
                    }
                });                
          response(itemClte);
  }});