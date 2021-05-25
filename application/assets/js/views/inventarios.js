$(document).ready(function() {
   
});

function getInventory(){
	 $.ajax({
		url: "Inventarios/get-Inventory-Sites-On-Hand",
		type: "POST",
		cache: false,
		dataType: 'json',
		beforeSend: function (xhr) {
			console.log('beforeSend');
			console.log(xhr);
		},
		success: function (data) {

			var row = '';
			$.each(data.value, function(i,v){
				row += '<tr>';
				row += '<td>' + v.InventorySiteId + '</td>';
				row += '<td>' + v.InventoryWarehouseId + '</td>';
				row += '<td>' + v.ItemNumber + '</td>';
				row += '<td>' + v.OnHandQuantity + '</td>';
				row += '</tr>';
			});
			$('#tblInventarios').append(row);
			$('#tblInventarios').DataTable( {
		        "language": {
            		"url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
        		}
    		});

			console.log(data.value);
			
		},
		error: function (x){
			console.log('error');
			console.log(x);
		}
	});	
}