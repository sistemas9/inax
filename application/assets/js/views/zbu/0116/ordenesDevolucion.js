
$(document).ready(function() {
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
});
//var clientes ="";


function enviar(){
	//swal("Hola");
var cliente = $('#cliente').val();//'ATP-000001';
var factura = $('#factura').val();//'ATP-000058';
$.ajax({
	url: "Ordenesdevolucion/get-Return-Order-Headers",
	type: "POST",
	cache: false,
	dataType: 'json',
	data: {cliente: cliente,factura:factura},
	beforeSend: function (xhr) {
		console.log(xhr);
	},
	success: function (data) {
		var html ='';
		var html2 = '';
		var html3 = '';
		$('#artis').html(html);
		$('#artis2').html(html);
		console.log(data.value);
		$.each(data.value,function (i,v){
		html=html + '<input name="lineas['+i+']" id="lineas['+i+']" class="form-control" value="'+v.LineDescription+'"></input>';//$("#lineas-1").data("inventory")
		html2=html2 + '<input name="cantidad['+i+']" id="cantidad['+i+']" class="form-control" value="'+v.OrderedSalesQuantity+'"></input>';
		html3=html3 + '<input name="lote['+i+']" id="lote['+i+']" class="form-control" value="'+v.InventoryLotId+'" readonly></input>';
		});

		$('#artis').append(html);
		$('#artis2').append(html2);
		$('#artis3').append(html3);
		$('#devolver').show();
	},
	error: function (x){
		console.log(x);
	}
});
}


function devolvers(){
	var datos = $('#devolucion').serializeArray();

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
		swal(data);
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