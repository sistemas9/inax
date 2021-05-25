function cargarClientes(data){
	//console.log("cargar clientes", data);
    clients = [];
    $.map(data, function (item){		
        var info = {label: "No hay Resultados",value: " "};
        info["label"] = $.trim(item.ClaveCliente) + " - " + $.trim(item.Nombre);
        info["value"] = $.trim(item.ClaveCliente);
        info["nombre"] = $.trim(item.Nombre);
        info["ConjuntoPrecios"] = $.trim(item.ConjuntoPrecios);
        info["DescuentoLinea"] = $.trim(item.DescuentoLinea);
        clients.push(info);
    });
}

function cargarGrupos(data){
    listaGrupos = [];  
   $.map(data, function (item){     
        var info = {label: "No hay Resultados",value: " "};
        info["label"] = $.trim(item.claveGrupo) + " - " + $.trim(item.Nombre);
        info["value"] = $.trim(item.claveGrupo);
        listaGrupos.push(info);
    });
}

function cargarFamilias(data){
    //console.log("cargar Familias", data);
    listaFamilias = [];  
   $.map(data, function (item){     
        var info = {label: "No hay Resultados",value: " "};   
        info["label"] = $.trim(item.ClaveFamilia) + " - " + $.trim(item.Nombre);
        info["value"] = $.trim(item.ClaveFamilia);
        listaFamilias.push(info);
    });
}


function mapeoRelacion(valor,arreglo,request){
       // console.log("mapeoFam", arregloFam);
   return $.map(arreglo,function(Relacion){ 
   //console.log("claveFam",claveFam);   
        posRelacion = Relacion.value.indexOf(valor.toUpperCase());   
        if (request.term.indexOf('*') < 0){
                if (posRelacion >= 0){
                    return Relacion;
                }
        }
        else{
            if ( (posRelacion >= 0) ){
              return Relacion;
            }
        }
    });
}



function mapeoFamilia(valor,arreglo,request){
       // console.log("mapeoFam", arregloFam);
   return $.map(arreglo,function(familia){ 
   //console.log("claveFam",claveFam);   
        posFamilia = familia.value.indexOf(valor.toUpperCase());   
        if (request.term.indexOf('*') < 0){
                if (posFamilia >= 0){
                    return familia;
                }
        }
        else{
            if ( (posFamilia >= 0) ){
              return familia;
            }
        }
    });
}


function mapeoGrupo(valor,arreglo,request){    
    return $.map(arreglo,function(grupo){    
        posGrupo = grupo.value.indexOf(valor.toUpperCase());   
        if (request.term.indexOf('*') < 0){
                if (posGrupo >= 0){
                    return grupo;
                }
        }
        else{
            if ( (posGrupo >= 0) ){
              return grupo;
            }
        }
    });
  }

function showModa(){
    if($('#claveclte').val() != "" && $('#vendedor').val() != ""){
      $('#claveGrupo').val("");
      $('#familia').html('');
      $('#claveFam').val("");    

    $('#busquedaGrupoFamiliaModal').openModal({
        dismissible: false
    });    
    }else{
      Materialize.toast('Favor de seleccionar un cliente/vendedor',3000,'red');
        // alert("Favor de seleccionar un cliente");
    }
}

function detallePrecio(){
           $.ajax({
                url:"Listadepreciospre/postprice",
                type:"post", 
                data:{idCliente: idCliente,idArticulo: idArticulo,
                cantidad: cantidad,fecha: fecha,moneda: moneda,
                idInvent: idInvent,locationIn: locationIn,company: company,percentCharge: percentCharge},             
                dataType: 'json',
                beforeSend: function (xhr) {
                },
                success: function (data, textStatus, jqXHR) {
                    
                    }                                  
         });

      }
 
  //carga los resultados de busqueda de familia y devuelve los aritculos.
 $(document).on('change','#familia',function(){
              var cliente = $("#claveclte").val();
              var familia = $(this).val();
              // $('#familia').prop('disabled',true); w
        $.ajax({
                url:"Listadepreciospre/listaarticulos",
                type:"post", 
                data:{cliente: cliente, familia: familia},             
                dataType: 'json',
                beforeSend: function (xhr) {
                       $('#preloader').show();
                },
                success: function (data) {
                  $('#preloader').hide();
                   if(data.length > 0){  
                     $('#ResultBusqueda').DataTable().destroy();
                     $("#ResultBusqueda > tbody").html("");
                      // $("#addItem > tbody").html("");
                    if (!data["noresult"]){
                        for (var i=0;i<data.length;i++){
                            $("#ResultBusqueda > tbody").append('<tr><td>'
                             + data[i].idArticulo + '</td><td>' 
                             + data[i].Nombre +'</td><td>' 
                             + data[i].unidad +'</td><td>' 
                             + "$" + data[i].precio +'</td><td>' 
                             + data[i].moneda +'</td>'
                           + '<td><input type="checkbox" data-idArticulo="'+data[i].idArticulo+'" data-Nombre="'+data[i].Nombre+'" data-unidad="'+data[i].unidad+'" data-precio="'+data[i].precio+'" data-idArticulo="'+data[i].moneda+'" class="col l1 s1 m1 checkFam" id="itemCheckFam'+i+'"/><label for="itemCheckFam'+i+'"></label></td></tr>');                     
                          $('#itemCheckFam'+i+'').prop('checked', 'true');  
                          if (!encontrarItem(data[i].idArticulo)){
                            $("#addItem > tbody").append('<tr data-idart="'+data[i].idArticulo+'" data-cliente="'+ cliente +'"><td>'
                               + data[i].idArticulo + '</td><td>' 
                               + data[i].Nombre  +'</td><td>' 
                               + data[i].unidad +'</td><td>' 
                               + data[i].precio +'</td><td>' 
                               + data[i].moneda +'</td>');
                          }                       
                        }                    
                    }
                    else{
                        $("#ResultBusqueda > tbody").append('<tr><td colspan="5" class="center-align">'+ data["noresult"] +'</td></tr>');
                    }

                 $('#ResultBusqueda').DataTable({
                    "destroy": true,
                    "bPaginate": true,
                    "bLengthChange": false,
                    "bFilter": true,
                    "bInfo": false,
                   "bAutoWidth": false
                  }).draw();                   
                }
              }, error: function(err){
                    console.log("error, ", JSON.stringify(err));
                }                                            
         });
 })

 function encontrarItem(item){
    var encontrado = false;
    $('#addItem tbody tr').each(function(){
        var tds = $(this).find('td');
        var tdArt = $(tds)[0];
        var itemId = $(tdArt).html();
        if (itemId == item){
            encontrado = true;
        }
    });
    return encontrado;
  }


  $(document).on('click','#BuscarFam',function(){
              var cliente = $("#claveclte").val();
              var familia = $("#claveFam").val();
              // console.log("valorfamilia",familia);
              // console.log("valorcliente",cliente);
              $('#BuscarFam').prop('disabled',true); 
        $.ajax({
                url:"Listadepreciospre/listaarticulos",
                type:"post", 
                data:{cliente: cliente, familia: familia},             
                dataType: 'json',
                beforeSend: function (xhr) {
                  $('#preloader').show();

                },
                success: function (data) {
                   $('#preloader').hide();
                   if(data.length > 0){  
                     $('#ResultBusqueda').DataTable().destroy();
                     $("#ResultBusqueda > tbody").html("");
                    if (!data["noresult"]){
                        for (var i=0;i<data.length;i++){
                            $("#ResultBusqueda > tbody").append('<tr data-cliente="'+$("#claveclte").val()+'"><td>'
                             + data[i].idArticulo + '</td><td>' 
                             + data[i].Nombre +'</td><td>' 
                             + data[i].unidad +'</td><td>' 
                             + data[i].precio +'</td><td>' 
                             + data[i].moneda +'</td>'
                           + '<td><input type="checkbox" data-idArticulo="'+data[i].idArticulo+'" data-Nombre="'+data[i].Nombre+'" data-unidad="'+data[i].unidad+'" data-precio="'+data[i].precio+'" data-idArticulo="'+data[i].moneda+'" class="col l1 s1 m1 checkFam" id="itemCheckFam'+i+'"/><label for="itemCheckFam'+i+'"></label></td></tr>');                     
                           $('.checkFam').prop('checked', 'true');   

                        }                    
                    }
                    else{
                        $("#ResultBusqueda > tbody").append('<tr><td colspan="5" class="center-align">'+ data["noresult"] +'</td></tr>');
                    }

                 $('#ResultBusqueda').DataTable({
                    "destroy": true,
                    "bPaginate": true,
                    "bLengthChange": false,
                    "bFilter": true,
                    "bInfo": false,
                   "bAutoWidth": false
                  }).draw();                   
                }
              }, error: function(err){
                    console.log("error, ", JSON.stringify(err));
                }                                            
         });
 })

  $(document).on('change', ".checkFam",function(e){

            if($(this).is(":checked")) {          
                var row = $(this).closest('tr');
                var cliente = $(row).data('cliente');
                var tds = $(row).find("td"); 
                var idArticulo = $(tds)[0]
                var nombre = $(tds)[1]
                var unidad = $(tds)[2]
                var precio = $(tds)[3]
                var moneda = $(tds)[4]
                // console.log($(articulos).html());
                $("#addItem > tbody").append('<tr data-idart="'+$(idArticulo).html()+'" data-cliente="'+ cliente +'"><td>'
                 + $(idArticulo).html() + '</td><td>' 
                 + $(nombre).html() +'</td><td>' 
                 + $(unidad).html() +'</td><td>' 
                 + $(precio).html() +'</td><td>' 
                 + $(moneda).html() +'</td>');
            }else{
              var row = $(this).closest('tr');
                var tds = $(row).find("td"); 
                var idArticulo = $(tds)[0];
                var element = document.querySelectorAll("[data-idart='"+$(idArticulo).html()+"']");
                $(element).remove();
            }
  })


//agrega los articulos seleccionados
$(document).on('click','#agregarItemsReport',function(e){

           var dataObject = {};
           var dataarray = [];
           $("#addItem tbody tr").each(function(){
               var row = $(this).closest('tr');
               var tds = $(this).find('td');
               var articulo = $(tds).html()
              // console.log(articulo);
              var codigo = $(tds)[0];
              var descrip = $(tds)[1];
              var unidad = $(tds)[2];
              var precio = $(tds)[3];
              var moneda = $(tds)[4];
              var cliente = $(row).data('cliente');
              dataObject = { codigo : $(codigo).html(),
                          descrip : $(descrip).html(),
                          unidad  : $(unidad).html(),
                          precio  : $(precio).html(),
                          moneda  : $(moneda).html()};
           dataarray.push(dataObject);
          });
          //  var dataObject = {};
          //  var dataarray = [];
          //  $('.checkFam:checked').each(function(){
          //  var row = $(this).closest('tr');
          //  var tds = $(row).find('td');
          //  var codigo = $(tds)[0];
          //  var descrip = $(tds)[1];
          //  var unidad = $(tds)[2];
          //  var precio = $(tds)[3];
          //  var moneda = $(tds)[4];
          //  var cliente = $(row).data('cliente');
          //  dataObject = { codigo : $(codigo).html(),
          //                 descrip : $(descrip).html(),
          //                 unidad  : $(unidad).html(),
          //                 precio  : $(precio).html(),
          //                 moneda  : $(moneda).html(),
          //                 cliente: cliente};
          //  dataarray.push(dataObject);
          // });
       if(dataarray.length > 0 || $('#addItem tbody').html() == ""){    
      $.ajax({  
          url:"Listadepreciospre/imprimepdf",
          type:"post",
          data:{dataarray: dataarray},
          dataType: 'json',
           beforeSend: function (xhr) {
                  $('#preloader').show();
                },
          success: function (data) {
            $('#preloader').hide();
            $('#idactual').val(data);
            var idvendedor = $('#vendedor option:selected').text();
            $('#idvendedor').val(idvendedor);
            var idcliente = $("#claveclte").val();
            $('#idcliente').val(idcliente);
            var namecliente = $("#cliente").val();
            $('#namecliente').val(namecliente);
            var addresscliente = $("#direccion").val();
            $('#addrescliente').val(addresscliente);
            $('#pdf').submit();
             window.location.reload();
         }
       }); 
       }else{
         alert("Favor de seleccionar minimo un artículo /o lista de artículos");
       }       
    });

     


function mapeoClte(valor,arreglo,request){
    //console.log("mapeoClie", arreglo);
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

function buscarFamilia(){
    var Catalogo = $('#claveGrupo').val();
       if(Catalogo != ""){
             $.ajax({
                url:"Listadepreciospre/relationgrupfam",
                type:"post", 
                data:{catalogo: Catalogo},
                dataType: 'json',
                beforeSend: function (xhr) {
                },
                success: function (data, textStatus, jqXHR) {
                  listaResult = [];  
                  $('#familia').prop('disabled'); 
                  $('#familia').html('');
                   $(data).each(function(){
                      var info = {label: "No hay Resultados",value: " "};
                      info["label"] = $.trim(this.ClaveRelacion);
                      listaResult.push(info);        
                      $('#familia').append('<option value="'+this.ClaveRelacion+'">'+this.ClaveRelacion+'</option>'); 
                    });                                  
               }
             })
         }
     }

   function scrollinDropdown() {
  //console.log("scroll");
    var selects = $('select').not('#vendedor');
    $(selects).each(function(){   
        $(this).material_select(function() {
      console.log("selecciona vendedor");
            $('input.select-dropdown').trigger('close');
        });
    var onMouseDown = function(e) {
    if (e.clientX >= e.target.clientWidth || e.clientY >= e.target.clientHeight) {e.preventDefault();}};
    $(this).siblings('input.select-dropdown').on('mousedown', onMouseDown);
    });
}

function cargarUsuarios(data){ 
    if(data.response=="ok"){
        $('#vendedor').html(data.res);
        $('#vendedor').select2(); 
      }      
}




 